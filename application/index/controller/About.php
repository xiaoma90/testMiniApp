<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;


class About extends Base
{
    public function index(){

        if(check_login()){
            if(powerget()){

                $id = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $abouts = Db::name('wd_xcx_about')->where("uniacid",$id)->find();
                $this->assign('abouts',$abouts);
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

    public function save(){
        $data['content'] = $_POST['content'];
        $data['header'] = input("header");
        $data['tel_box'] = input("tel_box");
        $data['serv_box'] = input("serv_box");
        $appletid = input("appletid");

        $abouts = Db::name('wd_xcx_about')->where("uniacid",$appletid)->count();
        if($abouts>0){
            $res = Db::name('wd_xcx_about')->where("uniacid",$appletid)->update($data);
            if($res){
              $this->success('公司介绍更新成功！');
            }else{
              $this->error('公司介绍更新失败，没有修改项！');
            }
        }else{
            $data['uniacid'] = $appletid;
            $res = Db::name('wd_xcx_about')->insert($data);
            if($res){
              $this->success('公司介绍更新成功！');
            }else{
              $this->error('公司介绍更新失败，没有修改项！');
            }
        }
    }

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