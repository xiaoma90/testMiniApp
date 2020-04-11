<?php
function getUrl(){

    $current_url='https://';

    if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on'){

        $current_url='https://';

    }



    if($_SERVER['SERVER_PORT']!='80'){

        $current_url .= $_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'];

    }else{

        $current_url .= $_SERVER['SERVER_NAME'];

    }

    return $current_url;

}




$postdata = file_get_contents("php://input");
$data = urldecode($postdata);
// file_put_contents(__DIR__.'/bd__111.txt', $data);
$data = explode('&', $data);


$result = array();
foreach ($data as $key => $value) {
	$temp = explode('=', $value);
	$result[$temp[0]] = $temp[1];

}
 file_put_contents(__DIR__.'/bd1111.txt', $result);

$out_trade_no = $result['tpOrderId'];

$payprice = round($result['payMoney']/100, 2);

$userId = $result['userId'];
$orderId = $result['orderId'];

$body = json_decode($result['returnData'], true)['body'];

$attach = explode("|", $body);

$types = $attach[0];

$uniacid = $attach[1];

$suid = $attach[3];

$paytype = 3;   //百度支付

if($types == 'duo' || $types == 'fabu' || $types == 'settop' || $types == 'supfabu' || $types == 'supsettop' || $types == 'miaosha' || $types == 'reserve' || $types == 'pt' || $types == 'art' || $types == 'food' || $types == 'shoppay' || $types == 'bargain' || $types == 'vipgrade'){
    $url = getUrl() . "/api/Wxapps/doPagepaynotify?uniacid=".$uniacid."&flag=1&out_trade_no=".$out_trade_no."&payprice=".$payprice."&types=".$types."&paytype=".$paytype."&pay_userId=".$userId."&orderId=".$orderId;
    // file_put_contents(__DIR__.'/bd2222.txt', $url);
    $result = file_get_contents($url);
//    if($result == ""){
//        echo 'success';
//        return ;
//    }
}

if($types == 'recharge'){
    $url = getUrl() . "/api/Wxapps/doPagePay_cz?uniacid=".$uniacid."&suid=".$suid."&order_id=".$out_trade_no."&money=".$payprice."&types=".$types;
    // file_put_contents(__DIR__.'/bd3333.txt', $url);
    $result = file_get_contents($url);
//    if($result == ""){
//        echo 'success';
//        return ;
//    }
}

if($types == 'mainShop'){
    $url = getUrl() . "/api/Wxapps/payCallBackNotify?uniacid=".$uniacid."&suid=".$suid."&order_id=".$out_trade_no."&payprice=".$payprice."&types=".$types."&paytype=".$paytype."&pay_userId=".$userId."&orderId=".$orderId;
     file_put_contents(__DIR__.'/bd3333.txt', $url);
    $result = file_get_contents($url);
//    if($result == ""){
//        echo 'success';
//        return ;
//    }
}





echo '{"errno":0,"msg":"success","data":{"isConsumed":2}}';
return 'success';

