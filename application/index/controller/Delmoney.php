<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;
class Delmoney extends Base
{
	public function index(){
		if(check_login()){
			if(powerget()){
				$uniacid = input("appletid");
				$index = "0";
        		$res = Db::name('wd_xcx_applet')->where("id",$uniacid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
				$moneyoff = Db::name('wd_xcx_moneyoff')->where("uniacid",$uniacid)->order("reach asc")->select();
				
				if($moneyoff){
					$num = count($moneyoff);
				}else{
					$num = 0;
				};
				
				$this->assign('applet',$res);
				$this->assign('index',$index);
				$this->assign('money',$moneyoff);
				$this->assign('num',$num);
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
		$uniacid = input("appletid");
		$num = input("num");
		// dump($del);die;
		
		if($num>0){
			for($i = 1;$i <= $num;$i++){
				if(!empty(input("reach".$i)) && !empty(input("del".$i))){
					if(input("del".$i)<=input("reach".$i)){
						Db::name('wd_xcx_moneyoff')->where("uniacid",$uniacid)->delete();
						$data = array(
							'uniacid' => $uniacid,
							'reach' => input("reach".$i),
							'del' => input("del".$i)
						);
						$res = Db::name('wd_xcx_moneyoff')->insert($data);
					}else{
						$this->error('保存失败，减免的金额不能大于满减金额');
						exit;
					}
				}
				
			}
		}
		$res = Db::name('wd_xcx_moneyoff')->where("uniacid",$uniacid)->select();
		if($res){
			$this->success('信息更新成功！');
		}else{
			$this->error('信息更新失败');
			exit;
		};
	}
}