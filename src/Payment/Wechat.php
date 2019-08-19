<?php
namespace Payment;

use Payment\Simple\JSSDK;
class Wechat
{
    private $appId;
    private $mchid;
    private $key;
    private $appsecret;

    /**
     * @do 构造函数（配置初始化）
     * @param $appId 应用ID
     * @param $mchid 商户号
     * @param $key 加密key
     * @param $appsecret 秘钥
     * @return
    */
    public function __construct($appId, $mchid, $key, $appsecret)
    {
        $this->appId = $appId;
        $this->mchid = $mchid;
        $this->key = $key;
        $this->appsecret = $appsecret;
    }

    /**
     * @do 微信JsApi支付前刷新获取微信公众号配置
     * @param  null
     * @return array [appId] [nonceStr] [timestamp][url] [signature] [rawString]
     */
    public function getJsApiConfig()
    {
        $jssdk = new JSSDK($this->appId, $this->appsecret);
        $data = $jssdk->getSignPackage();

        return $data;
    }

    /**
     * @do 微信JsApi支付下单
     * @param data array 订单请求的数组
     * @return array
    */
    public function jsApiOrder($payData)
    {
        $payData['sign'] = $this->getWeixinSign($payData, $this->key); //签名
        $xml = "<xml>";
        foreach ($payData as $key=>$val) {
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";

        $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
        $returnData = $this->curlPost($url, $xml);
        $wxReturn = json_decode(json_encode(simplexml_load_string($returnData, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        if($wxReturn['return_code'] === 'FAIL'){
            return ['code' => 400, 'msg' => $wxReturn['return_msg'], 'data' => null];
        }

        //二次加密微信返回的会话id
        $wechat = array();
        //网页端接口请求参数列表（参数需要重新进行签名计算，参与签名的参数为：appId、timeStamp、nonceStr、package、signType，参数区分大小写。）
        $wechat['appId'] = $wxReturn['appid'];
        $wechat['timeStamp'] = time();
        $wechat['nonceStr'] = $wxReturn['nonce_str'];
        $wechat['package'] = 'prepay_id='.$wxReturn['prepay_id'];
        $wechat['signType'] = 'MD5';
        $wechat['sign'] = $this->getWeixinSign($wechat, $this->key);

        return ['code' => 200, 'msg' => '请求成功', 'data' => $wechat];
    }

    /**
     * @do 微信签名加密
     * @param 数据参数  加密key
     * @return 加密完数据
     */
    private function getWeixinSign($data,$key){
        ksort($data);
        $buff = "";
        foreach ($data as $k => $v){
            if($k != "sign" && $v != "" && !is_array($v)){
                $buff .= $k . "=" . $v . "&";
            }
        }
        $buff = trim($buff, "&") . "&key=".$key;
        $string = md5($buff);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);

        return $result;
    }

    private function curlPost($url, $xml)
    {
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch,CURLOPT_URL, $url);
//        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,TRUE);
//        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);//严格校验

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);

        return $data;
    }
}