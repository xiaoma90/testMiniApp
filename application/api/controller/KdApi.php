<?php  

namespace app\api\controller;

use Decode\Decode\Decode;

use think\Request;

use think\Controller;

use think\Db;



class KdApi extends Controller {  
	// define('EBusinessID', '1412090');
	// //电商加密私钥，快递鸟提供，注意保管，不要泄漏
	// define('AppKey', '9dc7f785-8ed0-4739-aaa0-ae619d449923');
	// //请求url
	// define('ReqURL', 'http://api.kdniao.cc/Ebusiness/EbusinessOrderHandle.aspx');
	// 
	


	function getOrderTracesByJson($uniacid, $kd_code, $kd_number){
		$set = Db::name('wd_xcx_duo_products_yunfei') ->where('uniacid', $uniacid) ->find();
		if($set){
			if($set['api_type'] == 2){
				$EBusinessID = $set['ebusinessid'];
				$AppKey = $set['appkey'];
			}else{
				$EBusinessID = '1412090';
				$AppKey = '9dc7f785-8ed0-4739-aaa0-ae619d449923';
			}
		}else{
			$EBusinessID = '1412090';
			$AppKey = '9dc7f785-8ed0-4739-aaa0-ae619d449923';
		}
		
		$ReqURL = 'http://api.kdniao.com/Ebusiness/EbusinessOrderHandle.aspx';
		$requestData= "{'OrderCode':'','ShipperCode':'$kd_code','LogisticCode':'$kd_number'}";
		
		$datas = array(
	        'EBusinessID' => $EBusinessID,
	        'RequestType' => '1002',
	        'RequestData' => urlencode($requestData) ,
	        'DataType' => '2',
	    );
	    $datas['DataSign'] = $this->encrypt($requestData, $AppKey);
		$result=$this->sendPost($ReqURL, $datas);	
		
		//根据公司业务处理返回的信息......
		
		return $result;
	}

	/**
	 *  post提交数据 
	 * @param  string $url 请求Url
	 * @param  array $datas 提交的数据 
	 * @return url响应返回的html
	 */
	function sendPost($url, $datas) {
	    $temps = array();	
	    foreach ($datas as $key => $value) {
	        $temps[] = sprintf('%s=%s', $key, $value);		
	    }	
	    $post_data = implode('&', $temps);
	    $url_info = parse_url($url);
		if(empty($url_info['port']))
		{
			$url_info['port']=80;	
		}
	    $httpheader = "POST " . $url_info['path'] . " HTTP/1.0\r\n";
	    $httpheader.= "Host:" . $url_info['host'] . "\r\n";
	    $httpheader.= "Content-Type:application/x-www-form-urlencoded\r\n";
	    $httpheader.= "Content-Length:" . strlen($post_data) . "\r\n";
	    $httpheader.= "Connection:close\r\n\r\n";
	    $httpheader.= $post_data;
	    $fd = fsockopen($url_info['host'], $url_info['port']);
	    fwrite($fd, $httpheader);
	    $gets = "";
		$headerFlag = true;
		while (!feof($fd)) {
			if (($header = @fgets($fd)) && ($header == "\r\n" || $header == "\n")) {
				break;
			}
		}
	    while (!feof($fd)) {
			$gets.= fread($fd, 128);
	    }
	    fclose($fd);  
	    
	    return $gets;
	}

	/**
	 * 电商Sign签名生成
	 * @param data 内容   
	 * @param appkey Appkey
	 * @return DataSign签名
	 */
	function encrypt($data, $appkey) {
	    return urlencode(base64_encode(md5($data.$appkey)));
	}


}