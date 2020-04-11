<?php
    $_SESSION['uniacid'] = $_GET['uniacid'];
/* *
 * 功能：支付宝手机网站支付接口(alipay.trade.wap.pay)接口调试入口页面
 * 版本：2.0
 * 修改日期：2016-11-01
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 请确保项目文件有可写权限，不然打印不了日志。
 */

// header("Content-type: text/html; charset=utf-8");

// require_once 'driver/Redis.php';

require_once dirname ( __FILE__ ).DIRECTORY_SEPARATOR.'service/AlipayTradeService.php';
require_once dirname ( __FILE__ ).DIRECTORY_SEPARATOR.'buildermodel/AlipayTradeWapPayContentBuilder.php';

if (!empty($_POST['WIDout_trade_no'])&& trim($_POST['WIDout_trade_no'])!=""){
    require dirname ( __FILE__ ).DIRECTORY_SEPARATOR.'./../config.php';
    //商户订单号，商户网站订单系统中唯一订单号，必填
    $out_trade_no = $_POST['WIDout_trade_no'];

    //订单名称，必填
    $subject = $_POST['WIDsubject'];

    //付款金额，必填
    $total_amount = $_POST['WIDtotal_amount'];

    $type = $_POST['type'];
    if($type == 1){
        //商品描述，可空
        $body = 'duo|'.$_POST['uniacid'].'|'. $_POST['WIDbody'].'|'.$_POST['suid'];
    }elseif ($type == 2) {
        $body = 'recharge|'.$_POST['uniacid'].'|'. $_POST['WIDbody'].'|'.$_POST['suid'];
    }elseif($type == 3){
        $body = 'fabu|'.$_POST['uniacid'].'|'. $_POST['WIDbody'].'|'.$_POST['suid'];
    }elseif($type == 4){
        $body = 'settop|'.$_POST['uniacid'].'|'. $_POST['WIDbody'].'|'.$_POST['suid'];
    }
    

    //超时时间
    $timeout_express="1m";

    $payRequestBuilder = new AlipayTradeWapPayContentBuilder();
    $payRequestBuilder->setBody($body);
    $payRequestBuilder->setSubject($subject);
    $payRequestBuilder->setOutTradeNo($out_trade_no);
    $payRequestBuilder->setTotalAmount($total_amount);
    $payRequestBuilder->setTimeExpress($timeout_express);

    $payResponse = new AlipayTradeService($config);
    $result=$payResponse->wapPay($payRequestBuilder,$config['return_url'],$config['notify_url']);

    return ;
}

?>

<!DOCTYPE html>
<html>
	<head>
	<title>支付宝手机网站支付接口</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<style>
    *{
        margin:0;
        padding:0;
    }
    ul,ol{
        list-style:none;
    }
    body{
        font-family: "Helvetica Neue",Helvetica,Arial,"Lucida Grande",sans-serif;
    }
    .hidden{
        display:none;
    }
    .new-btn-login-sp{
        padding: 1px;
        display: inline-block;
        width: 75%;
    }
    .new-btn-login {
        background-color: #02aaf1;
        color: #FFFFFF;
        font-weight: bold;
        border: none;
        width: 100%;
        height: 30px;
        border-radius: 5px;
        font-size: 16px;
    }
    #main{
        width:100%;
        margin:0 auto;
        font-size:14px;
    }
    .red-star{
        color:#f00;
        width:10px;
        display:inline-block;
    }
    .null-star{
        color:#fff;
    }
    .content{
        margin-top:5px;
    }
    .content dt{
        width:100px;
        display:inline-block;
        float: left;
        margin-left: 20px;
        color: #666;
        font-size: 13px;
        margin-top: 8px;
    }
    .content dd{
        margin-left:120px;
        margin-bottom:5px;
    }
    .content dd input {
        width: 85%;
        height: 28px;
        border: 0;
        -webkit-border-radius: 0;
        -webkit-appearance: none;
    }
    #foot{
        margin-top:10px;
        position: absolute;
        bottom: 15px;
        width: 100%;
    }
    .foot-ul{
        width: 100%;
    }
    .foot-ul li {
        width: 100%;
        text-align:center;
        color: #666;
    }
    .note-help {
        color: #999999;
        font-size: 12px;
        line-height: 130%;
        margin-top: 5px;
        width: 100%;
        display: block;
    }
    #btn-dd{
        margin: 20px;
        text-align: center;
    }
    .foot-ul{
        width: 100%;
    }
    .one_line{
        display: block;
        height: 1px;
        border: 0;
        border-top: 1px solid #eeeeee;
        width: 100%;
        margin-left: 20px;
    }
    .am-header {
        display: -webkit-box;
        display: -ms-flexbox;
        display: box;
        width: 100%;
        position: relative;
        padding: 7px 0;
        -webkit-box-sizing: border-box;
        -ms-box-sizing: border-box;
        box-sizing: border-box;
        background: #1D222D;
        height: 50px;
        text-align: center;
        -webkit-box-pack: center;
        -ms-flex-pack: center;
        box-pack: center;
        -webkit-box-align: center;
        -ms-flex-align: center;
        box-align: center;
    }
    .am-header h1 {
        -webkit-box-flex: 1;
        -ms-flex: 1;
        box-flex: 1;
        line-height: 18px;
        text-align: center;
        font-size: 18px;
        font-weight: 300;
        color: #fff;
    }
</style>
</head>
<body text=#000000 bgColor="#ffffff" leftMargin=0 topMargin=4 id='main_body'>
<header class="am-header">
        <h1>支付宝支付订单</h1>
</header>
<div id="main" >
        <form name=alipayment action='' method=post target="_blank">
            <div id="body" style="clear:left">
                <dl class="content">
                    <dt>订单号
：</dt>
                    <dd>
                        <input id="WIDout_trade_no" name="WIDout_trade_no" />
                    </dd>
                    <hr class="one_line">
                    <dt>订单名称
：</dt>
                    <dd>
                        <input id="WIDsubject" name="WIDsubject" />
                    </dd>
                    <hr class="one_line">
                    <dt>付款金额
：</dt>
                    <dd>
                        <input id="WIDtotal_amount" name="WIDtotal_amount" />
                    </dd>
                    <hr class="one_line">
                    <dt>商品描述：</dt>
                    <dd>
                        <input id="WIDbody" name="WIDbody" />
                    </dd>
                    <hr class="one_line">
                    <dt></dt>
                    <dd id="btn-dd">
                        <input type="hidden" name="uniacid" id="uniacid" value="">
                        <input type="hidden" name="suid" id="suid" value="">
                        <input type="hidden" name="type" id="type" value="">
                        <span class="new-btn-login-sp">
                            <button class="new-btn-login" type="submit" style="text-align:center;" onclick="dopay();">确 认</button>
                        </span>
                        <span class="note-help">如果您点击“确认”按钮，即表示您同意该次的执行操作。</span>
                    </dd>
                </dl>
            </div>
		</form>
        <div id="foot">
			<ul class="foot-ul">
				<li>
					 
				</li>
			</ul>
		</div>
	</div>
</body>
<script language="javascript">
	function GetDateNow() {
        var orderinfo = <?php 
            $redis = new redis();
            $redis->connect('127.0.0.1', 6379);
            $id = $_GET['id'];
            
            $orderinfo = $redis ->get($id);
            echo $orderinfo;
        ?>;
        var type = <?php 
            $type = $_GET['type'];
            echo $type;
        ?>;
		document.getElementById("WIDout_trade_no").value =  orderinfo['order_id'];
		document.getElementById("WIDsubject").value = "支付订单";
		document.getElementById("WIDtotal_amount").value = orderinfo['payprice'];
        document.getElementById("WIDbody").value = orderinfo['goods_title'];
        document.getElementById("uniacid").value = orderinfo['uniacid'];
        document.getElementById("suid").value = orderinfo['suid'];
        document.getElementById("type").value = type;
	}
	GetDateNow();
    function dopay(){
        console.log(999999)
        document.body.setAttribute('style','display:none')
    }

</script>
</html>

