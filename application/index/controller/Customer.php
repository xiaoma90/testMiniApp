<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;

define("TOKEN", "kefu");

class Customer extends Controller
{
    public function index()
    {
       if (isset($_GET['echostr'])) {   //判断是不是首次验证
            $this->valid();
        }else{
            $this->responseMsg();
            
        }
    }
    public function valid()
    {
        $echoStr = $_GET["echostr"];
        if($this->checkSignature()){
            echo $echoStr;
            exit;
        }
    }
    public function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }
    function http_post_data($url, $xml,$second = 30)
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
    public function responseMsg() {
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"]; //获取数据
// var_dump(333);exit;
        $postObj = json_decode($postStr);
        $xcxId = $postObj->ToUserName;
        $res = Db::name('wd_xcx_applet')->where('xcxId',$xcxId)->find();
        $appID = $res['appID'];
        $appSecret = $res['appSecret'];
        $uniacid = $res['id'];
        $base = Db::name('wd_xcx_customer_base')->where("uniacid",$uniacid)->find();
        define("OPENID",$base['openid']);
        $access_token_url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appID."&secret=".$appSecret;
        $access_token_get = $this->http_post_data($access_token_url,'');
        $access_token = json_decode($access_token_get)->access_token;
        if (!empty($postStr)) {
            $fromUsername = $postObj->FromUserName; //openid
            $toUsername = $postObj->ToUserName;  //小程序原始id
            $MsgType = $postObj->MsgType;

            if($fromUsername == OPENID){
                if($MsgType == "text"){
                    $content = trim($postObj->Content);
                    $id = intval(substr($content,0,strpos($content, ':')));
                    if($id){
                        $user = Db::name('wd_xcx_user')->where('id',$id)->find();
                        $openid = $user['openid'];
                        $content = substr($content,strpos($content, ':')+1);
                    }
                    if($content == "发送图片"){
                        Db::name('wd_xcx_customer_pic')->insert(array('openid'=>$openid,'uniacid'=>$uniacid,'flag'=>1));
                        exit;
                    }else if($content == "获取图片id"){
                        Db::name('wd_xcx_customer_pic')->insert(array('openid'=>OPENID,'uniacid'=>$uniacid,'flag'=>3));
                        exit;
                    }else{

                        $url  = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$access_token;
                        $data = '{"touser":"'.$openid.'","msgtype":"text","text":{"content":"'.$content.'"}}';
                        $this->http_post_data($url,$data);
                    }
                }else if($MsgType == "image"){
                    $flag = Db::name('wd_xcx_customer_pic')->where('uniacid',$uniacid)->where('flag',1)->order('id desc')->find();
                    $url  = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$access_token;
                    if($flag){
                        $openid = $flag['openid'];
                        $data = '{"touser":"'.$openid.'","msgtype":"image","image":{"media_id":"'.trim($postObj->MediaId).'"}}';
                        $result = $this->http_post_data($url,$data);
                        if(json_decode($result)->errmsg == "ok"){
                            Db::name('wd_xcx_customer_pic')->where('uniacid',$uniacid)->where('flag',1)->order('id desc')->update(array('flag'=>2));
                        }
                    }else{
                       $flags = Db::name('wd_xcx_customer_pic')->where('uniacid',$uniacid)->where('flag',3)->order('id desc')->find();
                       if($flags){
                        $data = '{"touser":"'.OPENID.'","msgtype":"text","text":{"content":"'.$postObj->MediaId.'"}}';
                        $result = $this->http_post_data($url,$data);
                        if(json_decode($result)->errmsg == "ok"){
                            Db::name('wd_xcx_customer_pic')->where('uniacid',$uniacid)->where('flag',3)->order('id desc')->update(array('flag'=>2));
                        }
                       } 
                    }
                }else{

                }  
            }else{
                if(isset($postObj->SessionFrom)){  //如果存在表示刚接入客服会话，自动回复
                    $url  = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$access_token;
                    $reply = Db::name('wd_xcx_customer_reply')->where('uniacid',$uniacid)->where('flag',1)->select();

                    $task = array();

                    if($reply){
                        foreach ($reply as $k => $v) {
                            if($v['type']==1){
                                $content = $v['content'];
                                $data = '{"touser":"'.$fromUsername.'","msgtype":"text","text":{"content":"'.$content.'"}}';  
                                array_push($task, $data);
                                #$this->http_post_data($url,$data);
                            }
                            if($v['type'] == 2){
                                $media_id = $v['content'];
                                $data = '{"touser":"'.$fromUsername.'","msgtype":"image","image":{"media_id":"'.$media_id.'"}}';
                                array_push($task, $data);
                                #$this->http_post_data($url,$data);
                            } 
                            if($v['type'] == 3){
                                $content = unserialize($v['content']);
                                $title = $content['title'];
                                $pagepath = $content['pagepath'];
                                $thumb_media_id = $content['picurl'];
                                $data = '{"touser":"'.$fromUsername.'","msgtype":"miniprogrampage","miniprogrampage":{"title":"'.$title.'","pagepath":"'.$pagepath.'","thumb_media_id":"'.$thumb_media_id.'"}}';
                                array_push($task, $data);
                                #$this->http_post_data($url,$data);
                            } 
                            if($v['type'] == 4){
                                $content = unserialize($v['content']);
                                $title = $content['title'];
                                $description = $content['desc'];
                                $urls = $content['url'];
                                $thumb_url = $content['thumb_url'];
                                $data = '{"touser":"'.$fromUsername.'","msgtype":"link","link":{"title":"'.$title.'","description":"'.$description.'","url":"'.$urls.'","thumb_url":"'.$thumb_url.'"}}';
                                array_push($task, $data);
                                #$result = $this->http_post_data($url,$data);
                            } 
                        }

                        //执行发送任务
                        // ksort($task);
                        $result = true;
                        $path = dirname(__DIR__).'/test.txt';
                        $myfile = fopen($path, "w");
                        while ($result) {
                            $data = array_pop($task);
                            if(empty($data)){
                                $result = false;
                            }else{
                                $result = $this->http_post_data($url,$data);
                                fwrite($myfile, (String)$result);
                            }
                        }

                        fclose($myfile);
                    }else{
                        $data = '{"touser":"'.$fromUsername.'","msgtype":"text","text":{"content":"您好，有什么可以帮助您？"}}';
                        $this->http_post_data($url,$data);
                    }
                }else if($MsgType == "text"){
                        $content = trim($postObj->Content);
                        if($content == "openid"){
                            $url  = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$access_token;
                            $data = '{"touser":"'.$fromUsername.'","msgtype":"text","text":{"content":"'.$fromUsername.'"}';
                            $result = $this->http_post_data($url,$data);
                        }else{
                            $user = Db::name('wd_xcx_user')->where('openid',$fromUsername)->find();
                            $id = $user['id'];
                            $nickname = $user['nickname'];
                            $url  = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$access_token;
                            $data = '{"touser":"'.OPENID.'","msgtype":"text","text":{"content":"['.$id.']'.$nickname.'：'.$content.'"}}';
                            $this->http_post_data($url,$data);  
                        }
                }else if($MsgType == "image"){
                    $user = Db::name('wd_xcx_user')->where('openid',$fromUsername)->find();
                    $id = $user['id'];
                    $nickname = $user['nickname'];
                    $url  = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$access_token;
                    $data1 = '{"touser":"'.OPENID.'","msgtype":"text","text":{"content":"['.$id.']'.$nickname.'：用户图片如下"}}';
                    $data2 = '{"touser":"'.OPENID.'","msgtype":"image","image":{"media_id":"'.trim($postObj->MediaId).'"}}';
                    $this->http_post_data($url,$data1);
                    $this->http_post_data($url,$data2);
                }else{

                }  
            }
        }
    }
}