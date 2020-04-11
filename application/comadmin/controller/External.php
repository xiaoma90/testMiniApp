<?php
namespace app\comadmin\controller;

use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;

class External extends Controller
{   
    /*配置页*/
    public function index(){
        if(check_login()){

            $config = Db::name('wd_xcx_external_config') ->where('id', 1) ->find();
            $this->assign('config', $config);

            return $this->fetch('index');
        }else{
            $this->redirect('Index/Login/index');
        }
    }

    /*保存基础配置*/
    public function confSave(){
        $pdd_client_id = input("pdd_client_id");
        $pdd_client_secret = input("pdd_client_secret");
        $jd_appkey = input("jd_appkey");
        $jd_secretkey = input("jd_secretkey");
        $jd_siteid = input("jd_siteid");
        $jd_key = input("jd_key");
        $jd_unionId = input("jd_unionId");
        $data = [];
        if($pdd_client_id){
            $data['pdd_client_id'] = trim($pdd_client_id);
        }else{
            $data['pdd_client_id'] = '';
        }
        if($pdd_client_secret){
            $data['pdd_client_secret'] = trim($pdd_client_secret);
        }else{
            $data['pdd_client_secret'] = '';
        }
        if($jd_appkey){
            $data['jd_appkey'] = trim($jd_appkey);
        }else{
            $data['jd_appkey'] = '';
        }
        if($jd_secretkey){
            $data['jd_secretkey'] = trim($jd_secretkey);
        }else{
            $data['jd_secretkey'] = '';
        }
        if($jd_siteid){
            $data['jd_siteid'] = trim($jd_siteid);
        }else{
            $data['jd_siteid'] = '';
        }
        if($jd_key){
            $data['jd_key'] = trim($jd_key);
        }else{
            $data['jd_key'] = '';
        }
        if($jd_unionId){
            $data['jd_unionId'] = trim($jd_unionId);
        }else{
            $data['jd_unionId'] = '';
        }
        $res = Db::name('wd_xcx_external_config') ->where('id', 1) ->update($data);
        if($res){
            $this->success('保存成功！');
        }else{
            $this->error('保存失败！');
        }

    }


    /*订单页面*/
    public function order(){
        
        if(check_login()){

            $orders = Db::name('wd_xcx_external_order') ->order('order_create_time desc') ->paginate(10);
            $has = false;
            if(count($orders->toArray()['data'])>0){
                $has = true;
            }
            $this ->assign('has', $has);
            $this -> assign('orders', $orders);
            $count = Db::name('wd_xcx_external_order') ->count();
            $this ->assign('count', $count);

            return $this->fetch('order');
        }else{
            $this->redirect('Index/Login/index');
        }

    }


    /*所有佣金提现申请记录*/
    public function money(){
        if(check_login()){

            $tx_ls = Db::name('wd_xcx_external_fanyong_tx') ->alias('a') ->join('wd_xcx_applet b', 'a.uniacid = b.id') ->order('id desc') ->field('a.*, b.name as applet_name') ->paginate(10);
            $has = false;
            if(count($tx_ls->toArray()['data'])){
                $has = true;
            }
            $count = Db::name('wd_xcx_external_fanyong_tx') ->count();

            $this ->assign('tx_ls', $tx_ls);
            $this ->assign('count', $count);
            $this ->assign('has', $has);

            return $this->fetch('money');
        }else{
            $this->redirect('Index/Login/index');
        }
    }

    /*处理提现申请*/
    public function shenHeTx(){
        if(check_login()){
            $uniacid = input('uniacid');
            $shen = input('shen');
            $id = input('id');

            $ls = Db::name('wd_xcx_external_fanyong_tx') ->where([
                    'id' => $id,
                    'uniacid' => $uniacid,
                    'flag' => 1,
                ]) ->find();
            if($ls){
                $res = Db::name('wd_xcx_external_fanyong_tx') ->where([
                        'id' => $id,
                        'uniacid' => $uniacid,
                    ]) ->update(['flag' => $shen]);
                if($res){
                    $this->success('操作成功！');
                }else{
                    $this->error('操作失败，请稍后再试！');
                }
            }else{
                $this->error('申请记录不存在！');
            }

            
        }else{
            $this->redirect('Index/Login/index');
        }
    }
}