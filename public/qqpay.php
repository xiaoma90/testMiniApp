<?php



function xmlToArray($xml) {

    libxml_disable_entity_loader(true);

    $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);

    $val = json_decode(json_encode($xmlstring), true);

    return $val;

}



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



$data = file_get_contents("php://input");  //异步通知的数据

      // file_put_contents(__DIR__."/debug11.txt",$data);

$PayData = xmlToArray($data);

file_put_contents(__DIR__."/debug22.txt",$PayData);

// if($PayData['result_code'] == "SUCCESS" && $PayData['return_code'] = "SUCCESS"){

    $out_trade_no = $PayData['out_trade_no'];

    $openid = $PayData['openid'];

    $payprice = floatval(number_format($PayData['total_fee'] / 100,2));

    $attach = explode("|", $PayData['attach']);

    $types = $attach[0];

    $formId = $attach[1];

    $uniacid = $attach[2];
    
    $suid = $attach[3];


    $paytype = 4;   //QQ支付

    if($types == 'duo' || $types == 'fabu' || $types == 'settop' || $types == 'supfabu' || $types == 'supsettop' || $types == 'miaosha' || $types == 'reserve' || $types == 'pt' || $types == 'art' || $types == 'food' || $types == 'shoppay' || $types == 'bargain' || $types == 'vipgrade'){

        $url = getUrl() . "/api/Wxapps/doPagepaynotify?uniacid=".$uniacid."&flag=1&out_trade_no=".$out_trade_no."&suid=".$suid."&openid=".$openid."&payprice=".$payprice."&types=".$types."&formId=" . $formId."&paytype=".$paytype;
            file_put_contents(__DIR__."/debug33.txt",$url);

        $result = file_get_contents($url);
        file_put_contents(__DIR__."/debug4.txt",$result);
        if($result == ""){
            $ch = curl_init();
            $timeout = 5; 
            curl_setopt ($ch, CURLOPT_URL,$url);
            curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 
            curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            $file_contents = curl_exec($ch);
            curl_close($ch);
        }
        // file_put_contents(__DIR__."/debug3.txt",$url);
    }

    //充值成功回调
    if($types == 'recharge'){
        $url = getUrl() . "/api/Wxapps/doPagePay_cz?uniacid=".$uniacid."&order_id=".$out_trade_no."&money=".$payprice."&suid=".$suid."&formId=" . $formId;
        // file_put_contents(__DIR__."/debug444.txt",$url);
        // file_put_contents(__DIR__."/debug445.txt",$attach);
        $result = file_get_contents($url);
        
    }

    if($types == 'mainShop'){
        $url = getUrl() . "/api/Wxapps/payCallBackNotify?uniacid=".$uniacid."&order_id=".$out_trade_no."&payprice=".$payprice."&suid=".$suid."&types=".$types."&formId=" . $formId."&paytype=".$paytype;
        file_put_contents(__DIR__."/debug444.txt",$url);
        // file_put_contents(__DIR__."/debug445.txt",$attach);
        $result = file_get_contents($url);

    }

   

    //include __DIR__ . '/payNotify.php';

    //$pn = new payNotify($out_trade_no, $openid, $payprice, $types);

    //$pn->notify();

    echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';

    return;

// }