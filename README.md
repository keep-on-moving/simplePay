# simplePay
基于tp5.1的简单支付（包含微信和支付宝）

#composer 安装

`composer require simple-pay/simple-pay` 

#使用方法
- 引入微信包

```
require_once __DIR__ . '/../vendor/autoload.php';
use Payment\Wechat;

$wechat = new Wechat('自己的APPid', '商户号', 'key', 'appsecret');

```
- Jsapi支付前获取微信公众号的基本配置
```
$jsApiConfig = $wechat->getJsApiConfig();
/**  返回示例：
Array(
    [appId] => wxc593a8ebd05
    [nonceStr] => KtDFZk6OiJzNC4aA
    [timestamp] => 1566198080
    [url] => http://
    [signature] => bdbf2662b06c6f1e89480af1f38571ad126425fa
    [rawString] => jsapi_ticket=HoagFKDcsGMVCIY2vOjf9jz92X0lEpl4qscVOAj0yMzD1v4a
    pgH53sBtVGRhWvDTXJGtchYzMnFr6CSqRnpaVQ&noncestr=KtDFZk6OiJzNC4aA&timestamp=15661
    98080&url=http://
)
 */
```
- jsapi下订单
```
$data = array(
    'appid' => '公众号APPID',
    'mch_id' => trim('商户号'),
    'nonce_str' => md5(rand(100000,999999)),//随机串
    'sign_type' => 'MD5',
    'body' => '这是商品描述',//商品描述
    'out_trade_no' => getOrderNum(),//订单号
    'total_fee' => 1,//总金额(金额计数单位为：分)
    'spbill_create_ip' => '127.0.0.1',//终端ip
    'notify_url' => 'http://'.$_SERVER['HTTP_HOST'].'/api/v2/payment/wechatNotify',//回到地址
    'trade_type' => 'JSAPI',//下订单类型
    'openid' => 'oH1nC1MgvPDgxlrht_BUGOGKwgGk'//微信openID
);

$jsApiPayData = $wechat->jsApiOrder($data);
/**  返回示例
Array
(
    [code] => 200
    [msg] => 请求成功
    [data] => Array
        (
            [appId] => aaaaaaaaaaaaa
            [timeStamp] => 1566195877
            [nonceStr] => MCW5QQJbynatFkDG
            [package] => prepay_id=wx19142453170181d742e42f8e1703803700
            [signType] => MD5
            [sign] => 18EB52B78EF7D2EE9B34BD80153FC530
        )
)
 */
```

- app内微信下单
```
$data = array(
    'appid' => 'wx2decb75684523216',
    'mch_id' => trim('15012329821'),
    'nonce_str' => md5(rand(100000,999999)),//随机串
    'sign_type' => 'MD5',
    'body' => '这是商品描述',//商品描述
    'out_trade_no' => getOrderNum(),//订单号
    'total_fee' => 1,//总金额(金额计数单位为：分)
    'spbill_create_ip' => '127.0.0.1',//终端ip
    'notify_url' => 'http://'.$_SERVER['HTTP_HOST'].'/api/v2/payment/wechatNotify',//回到地址
    'trade_type' => 'APP',//下订单类型
);

$appPayData = $wechat->appOrder($data);
/** 返回示例：
Array
(
    [code] => 200
    [msg] => 请求成功
    [data] => Array
        (
            [appid] => wx2decb75232533316
            [partnerid] => 1501389821
            [prepayid] => wx19151434358615bb561723f21570864400
            [package] => Sign=WXPay
            [noncestr] => 8hmmnF9rfVML5xCR
            [timestamp] => 1566198858
        )

)
 */
```

- 微信回调(包含jsapi和APP)
```
$testxml  = file_get_contents("php://input");  //接收微信发送的支付成功信息
$result = XMLDataParse($testxml);
/** 数据示例
$result = array(
    "appid" => "wx2421b63211370ec43b",
    "attach" =>  "支付测试",
    "bank_type" =>"CFT",
    "fee_type" =>  "CNY",
    "is_subscribe" =>  "Y",
    "mch_id" =>  "100099100",
    "nonce_str" => "5d2b6c2a8db53831f7eda20af46e531c",
    "openid" =>  "oUpF8uMEb4qRXf22hE3X68TekukE",
    "out_trade_no" =>  "s129929113168",//外部订单号（即自己平台的订单号）
    "result_code" =>  "SUCCESS",
    "return_code" => "SUCCESS",
    "sign" =>  "B552ED6B279343CB493C5DD0D78AB241",
    "sub_mch_id" =>  "10000100",
    "time_end" =>  "20140903131540",
    "total_fee" =>  "1",
    "coupon_fee" =>  "10",
    "coupon_count" =>  "1",
    "coupon_type" => "CASH",
    "coupon_id" => "10000",
    "coupon_fee_0" =>  "100",
    "trade_type" =>  "JSAPI",
    "transaction_id" => "1004400740201409030005092168",//流水号
);*/
if($result['trade_type'] == 'jsapi'){
    $wechat = new Wechat('jsapi的APPID', 'jsapi的mchid', 'jsapi的key', 'jsapi的appsecret');
}else{
    $wechat = new Wechat('app绑定微信的APPID', 'app绑定微信的mchid', 'app绑定微信的key', 'app绑定微信的appsecret');
}
$data = $wechat->notify($result);
if($data['code'] == 200){
    //todo  继续逻辑
}else{
    //todo 回调异常处理
}
```
