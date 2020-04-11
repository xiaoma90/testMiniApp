<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;


class Customers extends Base
{
    public function index(){

        if(check_login()){


            if(powerget()){

                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);

                $base = Db::name('wd_xcx_customer_base')->where("uniacid",$appletid)->find();
                           
                $this->assign('base',$base);


            }else{
                $usergroup = Session::get('usergroup');
                if($usergroup==1){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/applet');
                }
                if($usergroup==2){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/index');
                }
                if($usergroup==3){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/index');
                }
                
            }

            return $this->fetch('index');
        }else{
            $this->redirect('Login/index');
        }
        
    }
    public function reply(){

        if(check_login()){


            if(powerget()){

                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);

                $nav = Db::name('wd_xcx_customer_reply')->where("uniacid",$appletid)->order('id desc')->select();
                foreach ($nav as $k => $v) {
                    if($v['type']==3){
                        $content = unserialize($v['content']);
                        $nav[$k]['content'] = "消息标题：".$content['title']."<br>小程序页面路径：".$content['pagepath']."<br>小程序卡片的封面图片media_id：".$content['picurl'];
                    }
                    if($v['type']==4){
                        $content = unserialize($v['content']);
                        $nav[$k]['content'] = "消息标题：".$content['title']."<br>描述：".$content['desc']."<br>图文消息链接：".$content['url']."<br>图文消息的图片地址：".$content['thumb_url'];
                    }
                    if($v['type']==2){
                        // $content = unserialize($v['content']);
                        $nav[$k]['content'] = "图片media_id：".$v['content'];
                    }
                }
                // var_dump($content);
                // exit;
                $this->assign('nav',$nav);



            }else{
                $usergroup = Session::get('usergroup');
                if($usergroup==1){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/applet');
                }
                if($usergroup==2){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/index');
                }
                if($usergroup==3){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/index');
                }
                
            }

            return $this->fetch('reply');
        }else{
            $this->redirect('Login/index');
        }
        
    }
    public function add(){
        if(check_login()){

            if(powerget()){
                $id = input("appletid");

                $res = Db::name('wd_xcx_applet')->where("id",$id)->find();

                if(!$res){
                    $this->error("找不到对应的小程序！");

                }
                $this->assign('applet',$res);

                $cid = input("id");

                $nav = array();

                if($cid){

                    $nav = Db::name('wd_xcx_customer_reply')->where("uniacid",$id)->where("id",$cid)->find();
                    if($nav['type'] == 1 || $nav['type'] ==2){
                         $nav['title'] = "";
                         $nav['pagepath'] = "";
                         $nav['picurl'] = "";
                         $nav['desc'] = "";
                         $nav['url'] = "";
                         $nav['thumb_url'] = "";
                    }
                    if($nav['type'] == 4){
                        $content = unserialize($nav['content']);
                        $nav['title'] = $content['title'];
                        $nav['desc'] = $content['desc'];
                        $nav['url'] = $content['url'];
                        $nav['thumb_url'] = $content['thumb_url'];
                        $nav['content'] = "";
                        $nav['pagepath'] = "";
                        $nav['picurl'] = "";
                        
                    }
                    if($nav['type'] == 3){
                        $content = unserialize($nav['content']);
                        $nav['title'] = $content['title'];
                        $nav['picurl'] = $content['picurl'];
                        $nav['pagepath'] = $content['pagepath'];
                        $nav['content'] = "";
                        $nav['desc'] = "";
                        $nav['url'] = "";
                        $nav['thumb_url'] = "";
                    }
                }

                $this->assign('nav',$nav);

                $this->assign('cid',$cid);

            }else{

                $usergroup = Session::get('usergroup');

                if($usergroup==1){

                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/applet');

                }

                if($usergroup==2){

                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/index');

                }

                if($usergroup==3){

                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/index');

                }

                

            }



            return $this->fetch('add');

        }else{

            $this->redirect('Login/index');

        }





    }



    public function save(){


        $data = array();
        //小程序ID
        $data['uniacid'] = input("appletid");
        $id = input("id");
        $openid = input("openid");
        if($openid){
            $data['openid'] = $openid;
        }

        
        if($id!=0){
            $res = Db::name('wd_xcx_customer_base')->where("uniacid",$data['uniacid'])->update($data);
        }else{
            $res = Db::name('wd_xcx_customer_base')->insert($data);
        }
        if($res){
          $this->success('客服设置信息更新成功！');
        }else{
          $this->error('客服设置更新失败，没有修改项！');
          exit;
        }
    }
    public function savereply(){
        $data = array();
        //小程序ID
        $data['uniacid'] = input("appletid");

        $id = intval(input("id"));
        // var_dump($id);exit;

        $type = input("type");
        if($type){
            $data['type'] = $type;
        }
        if($type == 1){
            $data['content']= input('content');
        }else if($type == 2){
            $picurl = input("picurl");
            $data['content'] = $picurl;
        }else if($type == 3){
            $data['content']['title'] = input("title");
            $data['content']['picurl'] = input("picurl");
            $data['content']['pagepath'] = input("pagepath");
            $data['content'] = serialize($data['content']);
        }else if($type == 4){
            $id = input("id");
            $data['content']['title'] = input("title");
            $data['content']['desc']= input("desc");
            $data['content']['url'] = input("url");
            $data['content']['thumb_url'] = input("thumb_url");
            $data['content'] = serialize($data['content']);
        }

        $flag = input("flag");
        if($flag){
            $data['flag'] = $flag;
        }

        if($id!=0){
            // var_dump($data);exit;
            $res = Db::name('wd_xcx_customer_reply')->where("uniacid",$data['uniacid'])->where("id",$id)->update($data);
        }else{
            $res = Db::name('wd_xcx_customer_reply')->insert($data);
        }
        if($res){
          $this->success('客服自动回复信息更新成功！');
        }else{
          $this->error('客服自动回复信息更新失败，没有修改项！');
          exit;
        }
    }
    public function http_post_data($url, $data_string) {    
    
        $ch = curl_init();    
        curl_setopt($ch, CURLOPT_POST, 1);    
        curl_setopt($ch, CURLOPT_URL, $url);    
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);    
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(    
            'Content-Type: application/json; charset=utf-8',    
            'Content-Length: ' . strlen($data_string))    
        );    
        ob_start();    
        curl_exec($ch);    
        $return_content = ob_get_contents();    
        //echo $return_content."<br>";  
        ob_end_clean();    
    
        $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);    
      //  return array($return_code, $return_content);    
      return  $return_content;  
    } 
    //单个图片上传操作
    function onepic_uploade($file){
        $thumb = request()->file($file);
        if(isset($thumb)){
            $dir = upload_img();
            $info = $thumb->move($dir); 
            if($info){  
                $imgurl = ROOT_HOST."/upimages/".date("Ymd",time())."/".$info->getFilename();
                return $imgurl;
            }  
        }
    }
    public function delete(){

        $appletid = input("appletid");

        $id = input("id");

        $data = array(

            "uniacid"=>$appletid,

            "id"=>$id

        );

        $res = Db::name('wd_xcx_customer_reply')->where($data)->delete();

        if($res){

            $this->success('删除成功');

        }else{

            $this->success('删除失败');

        }



    }
}