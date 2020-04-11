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

$data = explode('&', $data);

$result = array();
foreach ($data as $key => $value) {
	$temp = explode('=', $value);
	$result[$temp[0]] = $temp[1];

}
 file_put_contents(__DIR__.'/test1111.txt', $result);
$out_trade_no = $result['out_trade_no'];

$payprice = $result['buyer_pay_amount'];

$attach = explode("|", $result['body']);

$types = $attach[0];

$uniacid = $attach[1];

$suid = $attach[3];

$paytype = 2;   //支付宝支付
// file_put_contents(__DIR__.'/test222.txt', $types);
if($types == 'duo' || $types == 'fabu' || $types == 'settop' || $types == 'supfabu' || $types == 'supsettop' || $types == 'miaosha' || $types == 'reserve' || $types == 'pt' || $types == 'art' || $types == 'food' || $types == 'shoppay' || $types == 'bargain' || $types == 'vipgrade'){
    $url = getUrl() . "/api/Wxapps/doPagepaynotify?uniacid=".$uniacid."&flag=1&out_trade_no=".$out_trade_no."&payprice=".$payprice."&types=".$types."&paytype=".$paytype;
    file_put_contents(__DIR__.'/test333.txt', $url);
    $result = file_get_contents($url);
    if($result == ""){
        echo 'success';
        return ;
    }
}

if($types == 'recharge'){
    $url = getUrl() . "/api/Wxapps/doPagePay_cz?uniacid=".$uniacid."&suid=".$suid."&order_id=".$out_trade_no."&money=".$payprice."&types=".$types;
    // file_put_contents(__DIR__.'/test333.txt', $url);
    $result = file_get_contents($url);
    if($result == ""){
        echo 'success';
        return ;
    }
}

if($types == 'mainShop'){
    $url = getUrl() . "/api/Wxapps/payCallBackNotify?uniacid=".$uniacid."&flag=1&order_id=".$out_trade_no."&payprice=".$payprice."&types=".$types."&paytype=".$paytype."&suid=".$suid;
     file_put_contents(__DIR__.'/test333.txt', $url);
    $result = file_get_contents($url);
    if($result == ""){
        echo 'success';
        return ;
    }
}





echo 'success';
return 'success';
