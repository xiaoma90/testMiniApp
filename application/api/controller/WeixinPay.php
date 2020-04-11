<?php  

namespace app\api\controller;

use Decode\Decode\Decode;

use think\Request;

use think\Controller;

use think\Db;



class WeixinPay extends Controller {  

    protected $appid;  

    protected $mch_id;  

    protected $key;  

    protected $openid;  

    protected $out_trade_no; 

    protected $body;  

    protected $total_fee;  

    protected $identity;

    protected $sub_mchid;

    protected $attach;

    function __construct($appid, $openid, $mch_id, $key,$out_trade_no,$body,$total_fee,$identity,$sub_mchid,$attach="") {  

        $this->appid = $appid;  

        $this->openid = $openid;  

        $this->mch_id = $mch_id;  

        $this->key = $key;  

        $this->out_trade_no = $out_trade_no;  

        $this->body = $body;  

        $this->total_fee = $total_fee; 

        $this->identity = $identity;  

        $this->sub_mchid = $sub_mchid;   

        $this->attach = $attach;

    } 

     

    public function pay() {  

        //统一下单接口  

        $return = $this->weixinapp();  

        return $return;  

    }  

    //统一下单接口  

    private function unifiedorder() {  

        $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';  

        $parameters = array(  

            'appid' => $this->appid, //小程序ID  

                'body' => $this->body,

                'mch_id' => $this->mch_id, //商户号  

                'nonce_str' => $this->createNoncestr(), //随机字符串  

//            'body' => 'test', //商品描述  

            //   'out_trade_no' => '2015450806125348', //商户订单号  

            //  'total_fee' => floatval(0.01 * 100), //总金额 单位 分    

            //  'spbill_create_ip' => $_SERVER['REMOTE_ADDR'], //终端IP  

            //  'spbill_create_ip' => '192.168.0.161', //终端IP  

                'notify_url' => $this->getNotifyUrl('/pay.php'), //通知地址  确保外网能正常访问  

                'out_trade_no'=> $this->out_trade_no,  
                
                'total_fee' => $this->total_fee,

                'trade_type' => 'JSAPI'//交易类型  

            );  

        if($this->identity == 1){

            $parameters['openid'] = $this->openid; //用户id  
        }
        if($this->identity == 2){
            $parameters['sub_appid'] = $this->appid; //小程序ID            
            $parameters['sub_mch_id'] = $this->mch_id; //商户号  
            $parameters['sub_openid'] = $this->openid; //用户id  
        }
        
        //自定义参数 （里面是订单类型|表单id|小程序id）
        if($this->attach){
            $parameters['attach'] = $this->attach;
        }


        //统一下单签名  

        $parameters['sign'] = $this->getSign($parameters);  
        $xmlData = $this->arrayToXml($parameters);  

        $return = $this->xmlToArray($this->postXmlCurl($xmlData, $url, 60)); 
        return $return;  

    }  

    protected function getNotifyUrl($url){
        // $current_url='http://';
        // if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on'){
        //     $current_url='https://';
        // }

        // if($_SERVER['SERVER_PORT']!='80'){
        //     $current_url .= $_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].$url;
        // }else{
        //     $current_url .= $_SERVER['SERVER_NAME'].$url;
        // }
        $current_url = $_SERVER['HTTP_HOST']. STATIC_ROOT .$url;

        // file_put_contents(__DIR__."/debug33.txt",$current_url);

        return $current_url;
    }

    private static function postXmlCurl($xml, $url, $second = 30)   

    {  

        $ch = curl_init();  

        //设置超时  

        curl_setopt($ch, CURLOPT_TIMEOUT, $second);  

        curl_setopt($ch, CURLOPT_URL, $url);  

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);  

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); //严格校验  

        //设置header  

        curl_setopt($ch, CURLOPT_HEADER, FALSE);  

        //要求结果为字符串且输出到屏幕上  

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);  

        //post提交方式  

        curl_setopt($ch, CURLOPT_POST, TRUE);  

        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);  

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);  

        curl_setopt($ch, CURLOPT_TIMEOUT, 40);  

        set_time_limit(0);  

        //运行curl  

        $data = curl_exec($ch); 

        //返回结果  

        if ($data) {  

            curl_close($ch);  

            return $data;  

        } else {  

            $error = curl_errno($ch);  

            curl_close($ch);  

            throw new WxPayException("curl出错，错误码:$error");  

        }  

    }  

    //数组转换成xml  

    protected function arrayToXml($arr) {  

        $xml = "<root>";  

        foreach ($arr as $key => $val) {  

            if (is_array($val)) {  

                $xml .= "<" . $key . ">" . arrayToXml($val) . "</" . $key . ">";  

            } else {  

                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";  

            }  

        }  

        $xml .= "</root>";  

        return $xml;  

    }  

    //xml转换成数组  

    protected function xmlToArray($xml) {  

        //禁止引用外部xml实体   

        libxml_disable_entity_loader(true);  

        $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);  

        $val = json_decode(json_encode($xmlstring), true);  

        return $val;  

    }  

    //微信小程序接口  

    private function weixinapp() {  

        //统一下单接口  

        $unifiedorder = $this->unifiedorder(); 

        if($unifiedorder['return_code'] == 'FAIL'){
            return $unifiedorder;
        }

//        print_r($unifiedorder);  

        $parameters = array(  

            'appId' => $this->appid, //小程序ID  

            'timeStamp' => '' . time() . '', //时间戳  

            'nonceStr' => $this->createNoncestr(), //随机串  

            'package' => 'prepay_id=' . $unifiedorder['prepay_id'], //数据包  

            'signType' => 'MD5'//签名方式  

        );  

        //签名  

        $parameters['paySign'] = $this->getSign($parameters);  

        return $parameters;  

    }  

    //作用：产生随机字符串，不长于32位  

    protected function createNoncestr($length = 32) {  

        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";  

        $str = "";  

        for ($i = 0; $i < $length; $i++) {  

            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);  

        }  

        return $str;  

    }  



    //作用：生成签名  

    protected function getSign($Obj) {  

        foreach ($Obj as $k => $v) {  

            $Parameters[$k] = $v;  

        }  

        //签名步骤一：按字典序排序参数  

        ksort($Parameters);  

        $String = $this->formatBizQueryParaMap($Parameters, false);  

        //签名步骤二：在string后加入KEY  

        $String = $String . "&key=" . $this->key;  

        //签名步骤三：MD5加密  

        $String = md5($String);  

        //签名步骤四：所有字符转为大写  

        $result_ = strtoupper($String);  

        return $result_;  

    }  



    ///作用：格式化参数，签名过程需要使用  

    protected function formatBizQueryParaMap($paraMap, $urlencode) {  

        $buff = "";  

        ksort($paraMap);  

        foreach ($paraMap as $k => $v) {  

            if ($urlencode) {  

                $v = urlencode($v);  

            }  

            $buff .= $k . "=" . $v . "&";  

        }  

        $reqPar;  

        if (strlen($buff) > 0) {  

            $reqPar = substr($buff, 0, strlen($buff) - 1);  

        }  

        return $reqPar;  

    } 


    private function unifiedorderh5() {  
        $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';  

        $parameters = array(  

                'appid' => $this->appid, //小程序ID 

                'body' => $this->body,

                'mch_id' => $this->mch_id, //商户号  

                'nonce_str' => $this->createNoncestr(), //随机字符串  

                // 'notify_url' => $this->getNotifyUrl('/pay.php'), //通知地址  确保外网能正常访问  
                'notify_url' => $this->getNotifyUrl('/pay.php'), //通知地址  确保外网能正常访问  

                'out_trade_no'=> $this->out_trade_no,  
                
                'total_fee' => $this->total_fee,

                'trade_type' => 'MWEB', //交易类型  

                'attach' => $this->attach,  

                'spbill_create_ip'=> $this->get_client_ip(),

                'scene_info' => '{"h5_info": {"type":"Wap","wap_url": "https://four.nttrip.cn","wap_name": "商品支付"}}'

            );  
        
        //统一下单签名  

        $parameters['sign'] = $this->getSign($parameters);  


        $xmlData = $this->arrayToXml($parameters);  


        $return = $this->xmlToArray($this->postXmlCurl($xmlData, $url, 60)); 


        return $return;  

    }  


    private function get_client_ip() {
        if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
            $ip = getenv('HTTP_CLIENT_IP');
        } elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
            $ip = getenv('REMOTE_ADDR');
        } elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return preg_match ( '/[\d\.]{7,15}/', $ip, $matches ) ? $matches [0] : '';
    }


    private function weixinapph5() {  

        //统一下单接口  

        $unifiedorder = $this->unifiedorderh5(); 

        return $unifiedorder;
    }


    public function h5pay() {  

        //统一下单接口  

        $return = $this->weixinapph5();  

        return $return;  

    }

}  
