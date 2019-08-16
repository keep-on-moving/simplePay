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
}