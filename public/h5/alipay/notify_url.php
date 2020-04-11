<?php
/* *
 * 功能：支付宝服务器异步通知页面
 * 版本：2.0
 * 修改日期：2016-11-01
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。

 *************************页面功能说明*************************
 * 创建该页面文件时，请留心该页面文件中无任何HTML代码及空格。
 * 该页面不能在本机电脑测试，请到服务器上做测试。请确保外部可以访问该页面。
 * 如果没有收到该页面返回的 success 信息，支付宝会在24小时内按一定的时间策略重发通知
 */
require_once("config.php");
require_once 'wappay/service/AlipayTradeService.php';


// file_put_contents(__DIR__.'/res111.txt', $result);

$arr = $_SESSION['payresult'];
// file_put_contents(__DIR__.'/res222.txt', $arr);
$alipaySevice = new AlipayTradeService($config); 
$alipaySevice->writeLog(var_export($arr,true));
$res = $alipaySevice->check($arr);
// file_put_contents(__DIR__.'/res444.txt', $res);
/* 实际验证过程建议商户添加以下校验。
1、商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号，
2、判断total_amount是否确实为该订单的实际金额（即商户订单创建时的金额），
3、校验通知中的seller_id（或者seller_email) 是否为out_trade_no这笔单据的对应的操作方（有的时候，一个商户可能有多个seller_id/seller_email）
4、验证app_id是否为该商户本身。
*/
if($res) {//验证成功
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//请在这里加上商户的业务逻辑程序代

	
	//——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
	
    //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
	
	//商户订单号

	$out_trade_no = $arr['out_trade_no'];



	//支付宝交易号

	$trade_no = $arr['trade_no'];

	//交易状态
	$trade_status = $arr['trade_status'];


    if($arr['trade_status'] == 'TRADE_FINISHED') {

		//判断该笔订单是否在商户网站中已经做过处理
			//如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
			//请务必判断请求时的total_amount与通知时获取的total_fee为一致的
			//如果有做过处理，不执行商户的业务程序
				
		//注意：
		//退款日期超过可退款期限后（如三个月可退款），支付宝系统发送该交易状态通知
    }
    else if ($arr['trade_status'] == 'TRADE_SUCCESS') {
		//判断该笔订单是否在商户网站中已经做过处理
			//如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
			//请务必判断请求时的total_amount与通知时获取的total_fee为一致的
			//如果有做过处理，不执行商户的业务程序			
		//注意：
		//付款完成后，支付宝系统发送该交易状态通知
		//修改订单状态
		$mysql_conf = array(
		    'host'    => '172.17.0.13:3306', 
		    'db'      => 'app_airhtsc_com', 
		    'db_user' => 'app_airhtsc_com', 
		    'db_pwd'  => 'Ck^cH40muOt2', 
		    );
		$mysql_conn = @mysql_connect($mysql_conf['host'], $mysql_conf['db_user'], $mysql_conf['db_pwd']);
		if (!$mysql_conn) {
		  die("could not connect to the database:\n" . mysql_error());//诊断连接错误
		}
		mysql_query("set names 'utf8'");//编码转化
		$select_db = mysql_select_db($mysql_conf['db']);
		if (!$select_db) {
		  die("could not connect to the db:\n" .  mysql_error());
		}
		$time = time();
		$sql = "UPDATE htsc_pay_order SET pay_status = 1, pay_time = $time, trade_no = $trade_no WHERE yuyue_order_id = $out_trade_no";
		$res = mysql_query($sql);

		$sql_type = "UPDATE htsc_yuyue_order SET type = 1 WHERE order_id = $out_trade_no";
		$r = mysql_query($sql_type);

		// file_put_contents(__DIR__.'/res333.txt', $res);
    }
	//——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
        
	echo "success";		//请不要修改或删除
		
}else {
    //验证失败
    echo "fail";	//请不要修改或删除

}

?>

