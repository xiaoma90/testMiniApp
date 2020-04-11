<?php
$host = $_SERVER['DOCUMENT_ROOT'];
$host = substr($host, 0, strlen($host)-6);
$mysql_conf = require_once $host.'application/database.php';
$uniacid = $_SESSION['uniacid'];
$mysql_conn = @new mysqli($mysql_conf['hostname'].':'.$mysql_conf['hostport'], $mysql_conf['username'], $mysql_conf['password']);
if ($mysql_conn->connect_errno) {
    die("could not connect to the database:\n" . $mysqli->connect_error);//诊断连接错误
}
$mysql_conn->query("set names 'utf8'");
$select_db = $mysql_conn->select_db($mysql_conf['database']);
if (!$select_db) {
    die("could not connect to the db:\n" .  $mysql_conn->error);
}
$prefix = $mysql_conf['prefix'];
$sql = "select ali_h5_id, ali_h5_public_key, ali_h5_private_key, id from {$prefix}wd_xcx_applet where id = ".$uniacid;
$res = $mysql_conn->query($sql);
if (!$res) {
    die("sql error:\n" . $mysqli->error);
}
$row = $res->fetch_assoc();

$config = array (	
		//应用ID,您的APPID。
		'app_id' => $row['ali_h5_id'],

		//商户私钥，您的原始格式RSA私钥
		'merchant_private_key' => $row['ali_h5_private_key'],
		
		//异步通知地址
		'notify_url' => "https://".$_SERVER['HTTP_HOST']."/alipay.php",
		
		//同步跳转
		'return_url' => "https://".$_SERVER['HTTP_HOST']."/h5/index.html?id=".$uniacid,

		//编码格式
		'charset' => "UTF-8",

		//签名方式
		'sign_type'=>"RSA2",

		//支付宝网关
		'gatewayUrl' => "https://openapi.alipay.com/gateway.do",

		//支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
		'alipay_public_key' => $row['ali_h5_public_key'],

);
$mysql_conn->close();