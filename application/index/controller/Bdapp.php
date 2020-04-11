<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;

class Bdapp extends Base
{
	public function bdset(){
		if(check_login()){
            if(powerget()){
				$id = input("appletid");
				$res = Db::name('wd_xcx_applet')->where("id",$id)->find();
		        if(!$res){
		            $this->error("找不到对应的小程序！");
		        }

		        $this->assign('applet',$res);

		        //获取基础信息
		        $base = Db::name('wd_xcx_bd_applet') ->where('uniacid', $id) ->find();
		        if(!$base){
		        	Db::name('wd_xcx_bd_applet') ->insert(['uniacid'=>$id]);
		        	$base = Db::name('wd_xcx_bd_applet') ->where('uniacid', $id) ->find();
		        }
		        $this->assign('base', $base);

				return $this->fetch('bdset');
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

	//保存基础信息
	public function save(){
		$uniacid = input('appletid');

		if(input('appid')){
			$data['appid'] = input('appid');
		}else{
			$this->error('请输入小程序AppID!');
		}

		if(input('appkey')){
			$data['appkey'] = input('appkey');
		}else{
			$this->error('请输入小程序AppKey!');
		}

		if(input('appsecret')){
			$data['appsecret'] = input('appsecret');
		}else{
			$this->error('请输入小程序AppSecret!');
		}
		
		$res = Db::name('wd_xcx_bd_applet')->where('uniacid', $uniacid) ->update($data);

		if($res){
			$this -> success('设置成功!');
		}else{
			$this -> error('发送未知错误,操作失败,请稍后重试!');
		}
	}
}