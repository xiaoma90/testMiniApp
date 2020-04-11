<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;
class Copyright extends Base
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
                $bases = Db::name('wd_xcx_base')->where("uniacid",$appletid)->find();
                if(!$bases){
                    $this->error('请先进行基础配置!');
                }

                if($bases['copyimg']){
                    $bases['copyimg'] = remote($appletid,$bases['copyimg'],1);
                }
                $cp_con = Db::name('wd_xcx_copyright')->where("id",$appletid)->field('copycon') ->find();
                $bases['copycon'] = $cp_con['copycon'];
                $this->assign('bases',$bases);
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
    public function comment(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $bases = Db::name('wd_xcx_copyright')->where("id",$appletid)->find();
                $this->assign('bases',$bases);
            }else{
                $usergroup = Session::get('usergroup');
                if($usergroup==1){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/applet');
                }
                if($usergroup==2){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/index');
                }
                
            }
            return $this->fetch('comment');
        }else{
            $this->redirect('Login/index');
        }
        
    }
    public function delimg(){
        $uniacid = input('uniacid');
        $copyimg = "";
        $res = Db::name('wd_xcx_base')->where('uniacid',$uniacid)->update(array('copyimg'=>$copyimg));
        return json_encode($res);
    }
    public function save(){
        $appletid = input("appletid");
        //版权打开方式
        $copy_do = $_POST['copy_do'];
        if($copy_do){
            $data['copy_do'] = $copy_do;
        }else{
            $data['copy_do'] = 0;
        }
        //版权图片
        $copyimg = input("commonuploadpic");
        if($copyimg){
            $data['copyimg'] = remote($appletid,$copyimg,2);
        }
        //版权名称
        $copyright = $_POST['copyright'];
        if($copyright){
            $data['copyright'] = $copyright;
        }
        //版权电话
        $tel_b = $_POST['tel_b'];
        if($tel_b){
            $data['tel_b'] = $tel_b;
        }

        $r = true;
        //版权名称
        if(input("copycon")){
            $copycon = input("copycon");
            $cp = Db::name('wd_xcx_copyright')->where("id",$appletid)->count();
            if($cp>0){
                $r = Db::name('wd_xcx_copyright')->where("id",$appletid)->update(['copycon'=>$copycon]);
            }else{
                $r = Db::name('wd_xcx_copyright')->insert(['id'=> $appletid, 'copycon'=>$copycon]);
            }
        }


        $bases = Db::name('wd_xcx_base')->where("uniacid",$appletid)->count();
        if($bases>0){
            $res = Db::name('wd_xcx_base')->where("uniacid",$appletid)->update($data);
        }else{
            $data['uniacid'] = $appletid;
            $res = Db::name('wd_xcx_base')->insert($data);
        }
        if($res !== false && $r !== false){
          $this->success('基础信息更新成功！');
        }else{
          $this->error('基础信息更新失败，没有修改项！');
          exit;
        }
        
    }
    public function savecomment(){
        $appletid = input("appletid");
        if(input("copycon")){
            $data['copycon'] = input("copycon");
        }
        $bases = Db::name('wd_xcx_copyright')->where("id",$appletid)->count();
        if($bases>0){
            $res = Db::name('wd_xcx_copyright')->where("id",$appletid)->update($data);
        }else{
            $data['id'] = $appletid;
            $res = Db::name('wd_xcx_copyright')->insert($data);
        }
        if($res){
          $this->success('版权内容更新成功！');
        }else{
          $this->error('版权内容更新失败，没有修改项！');
          exit;
        }
    }
    public function mail(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $forminfo = Db::name('wd_xcx_forms_config')->where("uniacid",$appletid)->find();
                if(!$forminfo){
                    $forminfo['mail_user'] = '';
                    $forminfo['mail_password'] = '';
                    $forminfo['mail_user_name'] = '';
                    $forminfo['mail_sendto'] = '';
                }
                $base = Db::name('wd_xcx_base')->where("uniacid",$appletid)->field('sms_is, sms_code, sms_tels')->find();
                if($base){
                    $forminfo['sms_is'] = $base['sms_is'];
                    $forminfo['sms_code'] = $base['sms_code'];
                    $forminfo['sms_tels'] = $base['sms_tels'];
                }else{
                    $forminfo['sms_is'] = 2;
                    $forminfo['sms_code'] = '';
                    $forminfo['sms_tels'] = '';
                }
                $this->assign('forminfo',$forminfo);
            }else{
                $usergroup = Session::get('usergroup');
                if($usergroup==1){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/applet');
                }
                if($usergroup==2){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/index');
                }
            }
            return $this->fetch('mail');
        }else{
            $this->redirect('Login/index');
        }
    }
    public function savemail(){
        $appletid = input("appletid");
        $data['uniacid'] = $appletid;
        $data['mail_user'] = input("mail_user");
        $data['mail_password'] = input("mail_password");
        $data['mail_user_name'] = input("mail_user_name");
        $data['mail_sendto'] = input("mail_sendto");
        $data2['sms_is'] = input("sms_is");
        $data2['sms_code'] = input("sms_code");
        $data2['sms_tels'] = trim(input("sms_tels"));
        if($data2['sms_tels']){
            $sms_tels = explode(',', $data2['sms_tels']);
            foreach ($sms_tels as $k => $v) {
                if(!preg_match("/^1[3456789]\d{9}$/", $v)){
                    $this->error('通知设置更新失败，存在不合法手机号！');
                }
            }
        }
        $data["img1not"]="";
        $count = Db::name('wd_xcx_forms_config')->where("uniacid",$appletid)->count();
        if ($count==0) {
            $res = Db::name('wd_xcx_forms_config')->where("uniacid",$appletid)->insert($data);
        } else {
            $res = Db::name('wd_xcx_forms_config')->where("uniacid",$appletid)->update($data);
        }
        $res2 = Db::name('wd_xcx_base')->where("uniacid",$appletid)->update($data2);
        if($res || $res2){
          $this->success('通知设置更新成功！');  
        }else{
          $this->error('通知设置更新失败，没有修改项！');
          exit;
        }
    }
    public function demo(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
            }else{
                $usergroup = Session::get('usergroup');
                if($usergroup==1){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/applet');
                }
                if($usergroup==2){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/index');
                }
                
            }
            return $this->fetch('demo');
        }else{
            $this->redirect('Login/index');
        }
        
    }
    public function demosave(){
        $uniacid = input("appletid");
        $making_tmp = input("making_tmp");
        include 'making.php';
        $making = new Making();  
        $return=$making->making_do($uniacid,$making_tmp);  
        if($return == 1){
            $this->success("一键制作成功!");
        }
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
    // 编辑器中的上传图片
    public function imgupload(){
        $files = request()->file('');  
        foreach($files as $file){        
            // 移动到框架应用根目录/public/upimages/ 目录下        
            $info = $file->move(ROOT_PATH . 'public' . DS . 'upimages');
            if($info){
                $url =  ROOT_HOST."/upimages/".date("Ymd",time())."/".$info->getFilename();
                $arr = array("url"=>$url);
                return json_encode($arr);
            }else{
                // 上传失败获取错误信息
                return $this->error($file->getError()) ;
            }    
        }
    }
}