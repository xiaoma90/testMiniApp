<?php
namespace app\comadmin\controller;

use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;

class Shortmsg extends Controller
{
	public function index(){

		if(check_login()){
            $base = Db::name('wd_xcx_com_about')->where("id",1)->field("shortmsg")->find();
            if(!$base){
                $base['shortmsg'] = 1;
            }
            $sms_set = Db::name('wd_xcx_sms')->where("uniacid",-1)->where("type",1)->find(); //腾讯云
            $sms_set2 = Db::name('wd_xcx_sms')->where("uniacid",-1)->where("type",2)->find(); //阿里云
            if($sms_set2){
            	$sms_set2['ali_access_id'] = $sms_set2['tx_access_id'];
            	$sms_set2['ali_access_secret'] = $sms_set2['tx_access_secret'];
            	$sms_set2['ali_code_tpl'] = $sms_set2['tx_code_tpl'];
            	$sms_set2['ali_sign'] = $sms_set2['tx_sign'];
            }
            
            $this->assign('base',$base);
            $this->assign('sms_set',$sms_set);
            $this->assign('sms_set2',$sms_set2);
            return $this->fetch('index');
        }else{
            $this->redirect('Login/index');
        }

	}


	public function save(){
		if(check_login()){
			$shortmsg = input("shortmsg");
			if($shortmsg == 1){
	            $base_remote = Db::name('wd_xcx_com_about')->where("id",1)->update(array("shortmsg"=>$shortmsg));
	            $data = array();
	            $data = array(
		            "tx_access_id" => trim(input("tx_access_id")),
		            "tx_access_secret" => trim(input("tx_access_secret")),
		            "tx_code_tpl" => trim(input("tx_code_tpl")),
		            "tx_sign" => trim(input("tx_sign"))
		        );
		        $sms_set = Db::name('wd_xcx_sms')->where("uniacid", -1)->where("type", 1)->count();//腾讯云短信
		        if($sms_set > 0){
		            $sms_res = Db::name('wd_xcx_sms')->where("uniacid", -1)->where("type", 1)->update($data);
		        }else{
		            $data['uniacid'] = -1;
		            $data['type'] = 1;
		            $sms_res = Db::name('wd_xcx_sms')->insert($data);
		        }
	        }else if($shortmsg == 2){
	        	$base_remote = Db::name('wd_xcx_com_about')->where("id",1)->update(array("shortmsg"=>$shortmsg));
	            $data = array();
	            $data = array(
		            "tx_access_id" => trim(input("ali_access_id")),
		            "tx_access_secret" => trim(input("ali_access_secret")),
		            "tx_code_tpl" => trim(input("ali_code_tpl")),
		            "tx_sign" => trim(input("ali_sign"))
		        );
		        $sms_set = Db::name('wd_xcx_sms')->where("uniacid", -1)->where("type", 2)->count();//阿里云短信
		        if($sms_set > 0){
		            $sms_res = Db::name('wd_xcx_sms')->where("uniacid", -1)->where("type", 2)->update($data);
		        }else{
		            $data['uniacid'] = -1;
		            $data['type'] = 2;
		            $sms_res = Db::name('wd_xcx_sms')->insert($data);
		        }
	        }
	        if($sms_res || $base_remote){
	          $this->success('全局短信设置成功');
	        }else{
	          $this->error('全局短信设置失败，没有修改项！');
	        }

		}else{
			$this->redirect('Login/index');
		}
	}
}