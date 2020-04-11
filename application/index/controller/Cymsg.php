<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;

class Cymsg extends Base
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

                $base = Db::name('wd_xcx_message')->where("uniacid",$appletid)->where('flag',4)->find();
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


    public function save(){

        $data = array();
        $uniacid = input("appletid");
        //消息模板id
        $pay_id = input("pay_id");
        $data['mid'] = trim($pay_id);

        $url = input("url");
        $data['url'] = trim($url);


        $count = Db::name('wd_xcx_message')->where("uniacid",$uniacid)->where('flag',4)->count();

        if($count>0){
            $res = Db::name('wd_xcx_message')->where("uniacid",$uniacid)->where('flag',4)->update($data);
        }else{
            $data['flag'] = 4;
            $data['uniacid'] = $uniacid;
            $res = Db::name('wd_xcx_message')->insert($data);
        }

        if($res){
          $this->success('点餐通知更新成功！');
        }else{
          $this->error('点餐通知更新失败，没有修改项！');
          exit;
        }
    }
   
}