<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;


class Duoproductsset extends Base
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

                $yunfeidata = Db::name('wd_xcx_duo_products_yunfei')->where("uniacid",$id)->find();
                // 获取万能表单的情况
                $forms = Db::name('wd_xcx_formlist')->where("uniacid",$id)->order("id desc")->select();
                // 获取配置过的万能表单的情况
            
                $this->assign('yunfeidata',$yunfeidata);
        		$this->assign('forms',$forms);

                $stores=Db::name("wd_xcx_store")->where("uniacid",$id)->select();
                $this->assign('stores',$stores);
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
        // $yfei = input("yunfei");
        // if($yfei){
        //     $data['yfei'] = $yfei;
        // }else{
        //     $data['yfei'] = 0;
        // }
        $byou = input("baoyou");
        if($byou === ''){
            $data['byou'] = null;
        }else{
            $data['byou'] = $byou;
        }
        $data['formset'] = input("formset");
        $data['receiving'] = input("receiving");
        $data['support_time'] = input("support_time");
        $data['take_self'] = input('take_self');
        $stores = $data['take_self'] == 2 ? input("stores") : '';
        if($data['take_self'] == 2){
            $data['stores'] = $stores;
        }

        $is = Db::name('wd_xcx_duo_products_yunfei')->where("uniacid",$uniacid)->find();
        if(!$is){
            $data['uniacid'] = $uniacid;
            $res = Db::name('wd_xcx_duo_products_yunfei')->insert($data);
        }else{
            $res = Db::name('wd_xcx_duo_products_yunfei')->where("uniacid",$uniacid)->update($data);
        }
        if($res){
          $this->success('商品设置更新成功！');
        }else{
          $this->error('商品设置更新失败，没有修改项！');
          exit;
        }
    }


    public function wuliu(){
        if(check_login()){
            if(powerget()){

                $id = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$id)->find();

                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);

                $res = Db::name('wd_xcx_duo_products_yunfei') ->where('uniacid', $id) ->find();
                if(!$res){
                    Db::name('wd_xcx_duo_products_yunfei') ->insert(['uniacid'=> $id]);
                    $res = Db::name('wd_xcx_duo_products_yunfei') ->where('uniacid', $id) ->find();
                }
                $this->assign('set', $res);
               
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
            return $this->fetch('wuliu');
        }else{
            $this->redirect('Login/index');
        }
    }

    public function save_wuliu(){
        $uniacid = input('appletid');
        $api_type = input('api_type');
        $ebusinessid = input('ebusinessid');
        $appkey = input('appkey');
        $appcode = input('appcode');

        if($api_type == 2){
            if(!$ebusinessid){
                $this->error('用户ID必须填写!');
                exit;
            }

            if(!$appkey){
                $this->error('用户秘钥必须填写!');
                exit;
            }

            $data = array(
                    'api_type' => $api_type,
                    'ebusinessid' => $ebusinessid,
                    'appkey' => $appkey
                );
            $res = Db::name('wd_xcx_duo_products_yunfei') ->where('uniacid', $uniacid) ->update($data);
        }elseif ($api_type == 3) {
            if(!$appcode){
                $this->error('用户appcode必须填写!');
                exit;
            }
            $res = Db::name('wd_xcx_duo_products_yunfei') ->where('uniacid', $uniacid) ->update(['api_type'=> $api_type, 'appcode'=> $appcode]);
        }else{
            $res = Db::name('wd_xcx_duo_products_yunfei') ->where('uniacid', $uniacid) ->update(['api_type'=> $api_type]);
        }

        if($res){
            $this->success('操作成功!');
        }else{
            $this->error('发送未知错误/未修改, 操作失败!');
        }
    }
}