<?php
namespace app\comadmin\controller;

use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;

vendor('Qiniu.autoload');
use Qiniu\Auth as Auth;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;

class Globalremote extends Controller
{
	public function index(){

		if(check_login()){
            $base = Db::name('wd_xcx_com_about')->where("id",1)->field("globalremote")->find();
            if(!$base){
                $base['globalremote'] = 1;
            }
            $remote2 = Db::name('wd_xcx_remote')->where("uniacid",-1)->where("type",2)->find();
            $remote3 = Db::name('wd_xcx_remote')->where("uniacid",-1)->where("type",3)->find();
            
            $this->assign('base',$base);
            $this->assign('remote2',$remote2);
            $this->assign('remote3',$remote3);
            
            return $this->fetch('index');
        }else{
            $this->redirect('Login/index');
        }

	}


	public function remotesave(){
		if(check_login()){
			$globalremote = input("globalremote");
	        if($globalremote == 1){
	            $base_remote = Db::name('wd_xcx_com_about')->where("id",1)->update(array("globalremote"=>$globalremote));
	            $res = 1;
	        }else if($globalremote == 2){
	            $base_remote = Db::name('wd_xcx_com_about')->where("id",1)->update(array("globalremote"=>$globalremote));
	            $data = array();
	            
	            if(input("bucket2")){
	                $data['bucket'] = input("bucket2");
	            }else{
	                $this->error("存储空间名称(Bucket)不能为空");
	            }
	            if(input("domain2")){
	                $data['domain'] = input("domain2");
	            }else{
	                $this->error("绑定域名（或测试域名）不能为空");
	            }
	            if(input("ak2")){
	                $data['ak'] = input("ak2");
	            }else{
	                $this->error("AccessKey（AK）不能为空");
	            }
	            if(input("sk2")){
	                $data['sk'] = input("sk2");
	            }else{
	                $this->error("SecretKey（SK）不能为空");
	            }
	            // $data['imgstyle'] = input("imgstyle2");
	            $data['type'] = $globalremote;
	            $is = Db::name("wd_xcx_remote")->where("uniacid",-1)->where("type",2)->find();
	            if($is){
	                $res = Db::name("wd_xcx_remote")->where("uniacid",-1)->where("type",2)->update($data);
	            }else{
	                $data['uniacid'] = -1;
	                $res = Db::name("wd_xcx_remote")->insert($data);
	            }
	        }else if($globalremote == 3){
	            $base_remote = Db::name('wd_xcx_com_about')->where("id",1)->update(array("globalremote"=>$globalremote));
	            $data = array();
	            
	            if(input("bucket3")){
	                $data['bucket'] = input("bucket3");
	            }else{
	                $this->error("存储空间名称(Bucket)不能为空");
	            }
	            if(input("domain3")){
	                $data['domain'] = input("domain3");
	            }else{
	                $this->error("Endpoint（或自定义域名）不能为空");
	            }
	            $data['domain_bind'] = input("domain_bind");

	            if(input("ak3")){
	                $data['ak'] = input("ak3");
	            }else{
	                $this->error("Access Key ID不能为空");
	            }
	            if(input("sk3")){
	                $data['sk'] = input("sk3");
	            }else{
	                $this->error("Access Key Secret不能为空");
	            }
	            $data['imgstyle'] = input("imgstyle3");
	            $data['domainIs'] = input("domainIs");
	            $data['type'] = $globalremote;
	            $is = Db::name("wd_xcx_remote")->where("uniacid",-1)->where("type",3)->find();
	            if($is){
	                $res = Db::name("wd_xcx_remote")->where("uniacid",-1)->where("type",3)->update($data);
	            }else{
	                $data['uniacid'] = -1;
	                $res = Db::name("wd_xcx_remote")->insert($data);
	            }
	        }
	        if($res || $base_remote){
	          $this->success('全局远程附件设置成功');
	        }else{
	          $this->error('全局远程附件设置失败，没有修改项！');
	        }

		}else{
			$this->redirect('Login/index');
		}
	}
}