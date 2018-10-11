<?php
//引入官方解密文件
include_once "wxBizDataCrypt.php";
//获取小程序参数
$appid =$_GET['appid'];
$secret =$_GET['secret'];
$js_code=$_GET['code'];
$iv = ($_GET['iv']);
$encryptedData=($_GET['encryptedData']);
$grant_type='authorization_code';
//请求官方API
$objSession=http_curl("https://api.weixin.qq.com/sns/jscode2session?appid=$appid&secret=$secret&js_code=$js_code&grant_type=$grant_type");
$session_key = json_decode($objSession)->session_key;

$decodeData = new WXBizDataCrypt($appid, $session_key);
$errCode = $decodeData->decryptData($encryptedData, $iv, $data );

if ($errCode == 0) {
    print($data . "\n");
} else {
    print($errCode . "\n");
}

//模拟https
function http_curl($url){
    $curl = curl_init();
    //设置抓取的url
    curl_setopt($curl,CURLOPT_URL,$url);
    curl_setopt($curl,CURLOPT_CONNECTTIMEOUT,30);
    //设置获取的信息以文件流的形式返回，而不是直接输出。
    curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
    // https请求 不验证证书和hosts
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    //执行命令
    $response=curl_exec($curl);
    //关闭URL请求
    curl_close($curl);
    //返回数据
    return $response;
}

?>