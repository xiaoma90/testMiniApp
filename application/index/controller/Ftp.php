<?php
header("Content-type: text/html; charset=utf-8");


class Ftp
{
    public function Secret(){

        $aa = dirname(dirname(__DIR__));
        $aa = $aa.DIRECTORY_SEPARATOR.'index'.DIRECTORY_SEPARATOR.'controller'.DIRECTORY_SEPARATOR.'License.php';
        $bb = 'License';
        $request_url = '/Secret.php';
        // if(!file_exists($bb)){

        //     if(!empty($_SERVER['HTTPS'])){
        //         $header = 'https';
        //     }else{
        //         $header = 'http';
        //     }
        //     //发出301头部
        //     header('HTTP/1.1 302 Moved Permanently');
        //     //跳转到你希望的地址格式
        //     $tt =  dirname( dirname(dirname(__FILE__))).'/backend/web/Secret.php';
        //     preg_match('~/addons/.*web~', $tt, $mc);
        //     if ($mc[0]==''){
        //         $mc[0] = "/backend/web";
        //     }
        //     $url_vist = $header.'://'.$_SERVER['HTTP_HOST'].$mc[0].$request_url;
        //     //$html = '<html><head><meta http-equiv="refresh" content="0.1;url='.$url_vist.'"> </head></html>';
        //     //echo $html;
        //     header("Location: $url_vist");
        //     //微擎兼容----------------------------------------------------------
        //     // echo '正在加载...';
        //     // echo $url_vist;

        //     // echo "<script>window.location.href='$url_vist';</script>";
        //     // return "<script>window.location.href='$url_vist';</script>";
        //     //header('Location: '.$url_vist);
        //     exit();


        // }else{

            $filename = $aa;

            $secret = '4508ec70b40927898bf88feab9df8c6c'; // md5('worldidc_sqtg');

            $key_content = include($filename);
            $key_content = $key_content['license'];
            $length = strlen($key_content);

            // 密钥长度小于 102 必然无效
            // if($length < 102) {
            //     die();
            // }

            $is = base64_decode(substr($key_content, 0, 6));

            if(substr($is, 0, 1) == '|'){
                $str_arr = unpack("C2", substr($is, 1));
                $key_content = substr($key_content, 6);
                $len1 = $str_arr[1];
                $len2 = $length - 6 - $len1 - $str_arr[2];
            }else{
                $len1 = 26;
                $len2 = $length - 102;
            }

            // 获取加密的 code
            $code = base64_decode(substr($key_content, $len1, $len2));

            $code_length = strlen($code);

            $round = $code_length / 32;
            $left = $code_length % 32;

            // 获取和 code 等长的 self_key
            $self_key = str_repeat($secret, $round) . substr($secret, 0, $left);

            // 这边不妨把两个都 unpack 下

            $decode = array_map(function($a, $b) {
                $c = $a - $b;
                return $c > 0 ? $c : $c + 256;
            }, unpack("C{$code_length}", $code), unpack("C{$code_length}", $self_key));

            $str = array_reduce($decode, function($sum, $code) {
                return $sum .= chr($code);
            }, '');

            //end
            if($str == $_SERVER['HTTP_HOST']){

                //通过
            }else{

                echo '密钥错误，请联系开发者获取正确密钥！';
                exit();
            }
        // }

    }

}
//end

$a = new Ftp();
$a->Secret();



