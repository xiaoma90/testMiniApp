<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>全端云 - 授权页面</title>
</head>
<body>
<style>
    *{margin:0;padding:0;}
    h1{text-align:center;color:#43B350;padding-top:100px;}
    h2{color:#ee3333;font-size: 12px;text-align: center;margin: 20px 0}
    .box{width:50%;margin:30px auto 0;}
    .textarea{width: 100%;padding:10px;box-sizing: border-box;}
    .button{background-color:#43B350;border:none;color:#fff;padding:6px 20px;font-size:16px;border-radius:4px;margin: 30px auto 0;display: block;}
    .copyright{color:#999;text-align: center;font-size: 12px;margin-top: 50px}
</style>
<h1>全端云 - 授权页面</h1>
<h2>请填写授权密钥，填写错误将导致系统无法运行，请联系第二世界官方客服获取！</h2>
<div class="box">
    <form name="form1" enctype="multipart/form-data" action="#" method="post">
        <div class="row"><textarea id="textarea" class="textarea" required="required" name="textarea" id="" cols="30" rows="10"></textarea></div>
        <div class="row"><input id="postSecret" class="button" type="submit" value="确认" />
    </form>
</div>
<p class="copyright">第二世界网络 全端云系统 4006-871-025</p>
</body>
</html>

<?php
function trimall($str)
{
    $qian = array(" ", "　", "\t", "\n", "\r");
    return str_replace($qian, '', $str);
}

    function curl($url, $data = '', $method = 'GET'){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
//        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        if($method=='POST') {
            curl_setopt($curl, CURLOPT_POST, 1);
            if ($data != '')
            {
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            }
        }
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }

if(!$_POST){
    // $is_exists = file_exists(sw_get_magic_dir().'/License.php');
    // if($is_exists){
    //     if(!empty($_SERVER['HTTPS'])){
    //         $header = 'https';
    //     }else{
    //         $header = 'http';
    //     }
    //     session_start();
    //     $url_require = $_SESSION["we7_require_url"];
    //     $data = curl($url_require);
    //     $_SESSION["we7_login"] = $data;
    //     //发出301头部
    //     header('HTTP/1.1 302 Moved Permanently');
    //     //跳转到你希望的地址格式
    //     $url_vist = $_SESSION['we7_url'];
    //     header('Location: '.$url_vist);
    //     exit();
    // }
}

if($_POST){
    $textarea = $_POST["textarea"];
    $textarea = trimall($textarea);
    $textarea1 = "<?php".PHP_EOL."return array(".PHP_EOL." 'license' => '".$textarea."'".PHP_EOL.");";
    $writeOk = file_put_contents($_SERVER['DOCUMENT_ROOT'].'/../application/index/controller/License.php', $textarea1);
    if($writeOk){
        if(!empty($_SERVER['HTTPS'])){
            $header = 'https';
        }else{
            $header = 'http';
        }
        $url_require = 'https://'.$_SERVER['SERVER_NAME'];

        //发出301头部
        header('HTTP/1.1 302 Moved Permanently');
        //跳转到你希望的地址格式
        header('Location: '.$url_require);
        exit();
    }
}
?>