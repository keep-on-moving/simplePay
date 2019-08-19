<?php
require_once __DIR__ . '/../vendor/autoload.php';
use Payment\Wechat;

$wechat = new Wechat('APPID', 'mchid', 'key', 'appsecret');

// +----------------------------------------------------------------------
// | 微信JsApi支付前刷新获取微信公众号配置
// +----------------------------------------------------------------------
//$jsApiConfig = $wechat->getJsApiConfig();

// +----------------------------------------------------------------------
// | JsApi统一下单接口
// +----------------------------------------------------------------------
$data = array(
    'appid' => '自己的APPID',
    'mch_id' => trim('自己的商户号'),
    'nonce_str' => md5(rand(100000,999999)),//随机串
    'sign_type' => 'MD5',
    'body' => '这是商品描述',//商品描述
    'out_trade_no' => getOrderNum(),//订单号
    'total_fee' => 1,//总金额(金额计数单位为：分)
    'spbill_create_ip' => '127.0.0.1',//终端ip
    'notify_url' => 'http://'.$_SERVER['HTTP_HOST'].'/api/v2/payment/wechatNotify',//回到地址
    'trade_type' => 'JSAPI',
    'openid' => '当前下单人对于当前公众号的openID'//微信openID
);

$jsApiPayData = $wechat->jsApiOrder($data);
/**  返回示例
Array(
    [appId] => aaaaaaaaaaaaa
    [timeStamp] => 1566195877
    [nonceStr] => MCW5QQJbynatFkDG
    [package] => prepay_id=wx19142453170181d742e42f8e1703803700
    [signType] => MD5
    [sign] => 18EB52B78EF7D2EE9B34BD80153FC530
)
 */




/**
 * @do 获取订单编号
 * @return  number
 */
function getOrderNum()
{
    $year = date('Y') - 2018;
    $dayNum = getTodayInYearNum();
    $orderNum = ($year.$dayNum + 1111).rand('10000000', '99999999');

    return $orderNum;
}

function getTodayInYearNum(){
    $year = date('Y');
    $month = date('m');
    $day = date('d');
    $sum=-1;
    switch($month){
        case 1:$sum=0;break;
        case 2:$sum=31;break;
        case 3:$sum=59;break;
        case 4:$sum=90;break;
        case 5:$sum=120;break;
        case 6:$sum=151;break;
        case 7:$sum=181;break;
        case 8:$sum=212;break;
        case 9:$sum=243;break;
        case 10:$sum=273;break;
        case 11:$sum=304;break;
        case 12:$sum=334;break;
        default:echo '输入错误，请输入1-12之间的数';break;
    }
    if($sum>=0){
        $sum=$sum+$day;
        if($year%400==0||($year%4==0&&$year%100!=0)){
            $leap=1;
        }else{
            $leap=0;
        }
        if($leap==1&&$month==2){
            $sum++;
        }
    }

    $sum = sprintf("%03d", $sum);
    return $sum;
}