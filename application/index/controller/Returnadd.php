<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;

class Returnadd extends Base
{   
    // 栏目列表
    public function index(){

        if(check_login()){
            if(powerget()){
                $id = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);

                $lists = Db::name('wd_xcx_refund_address')->where('uniacid', $id)->order('id desc')->paginate(10, false, ['query' => ['appletid' => $_GET['appletid']]]);
                $list = $lists -> toArray()['data'];
                $this->assign('lists', $lists);
                $this->assign('list', $list);
                return $this->fetch('index');
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
        }else{
            $this->redirect('Login/index');
        }
    }
    public function add(){
        $appletid = input("appletid");
        $id = intval(input("id"));
        $info = [];
        if($id){
            $info = Db::name('wd_xcx_refund_address')->where("uniacid",$appletid)->where('id', $id)->find();
        }
        $this->assign('info', $info);
        $this->assign('id', $id);
        return $this->fetch('add');
    }
    public function save(){
        $appletid = input("appletid");
        $id = intval(input("id"));
        $proid = input('province');
        $cityid = input('city');
        $areaid = input('area');
        $province =  input('pro') ? input('pro') : "";
        $city = input('cit') ? input('cit') : "";
        $area = input('are') ? input('are') : "";
        $data = [
            'name' => trim(input('name')),
            'mobile' => trim(input('mobile')),
            'province' => $province,
            'proid' => $proid,
            'city' => $city,
            'cityid' => $cityid,
            'area' => $area,
            'areaid' => $areaid,
            'more_address' => trim(input('more_address')),
            'remark' => trim(input('remark')),
        ];
        if($id){
            $res = Db::name('wd_xcx_refund_address')->where("uniacid",$appletid)->where('id', $id)->update($data);
        }else{
            $data['uniacid'] = $appletid;
            $data['creat_time'] = time();
            $res = Db::name('wd_xcx_refund_address')->insert($data);
        }
        if($res){
            $this->success('地址添加/编辑成功', Url('Returnadd/index').'?appletid='.$appletid);
        }else{
            $this->error('地址添加/编辑失败', Url('Returnadd/index').'?appletid='.$appletid);
        }
    }
    public function del(){
        $appletid = input("appletid");
        $id = intval(input("id"));
  
        $is = Db::name('wd_xcx_refund_address')->where("uniacid",$appletid)->where('id', $id)->find();

        if($is){
            $res = Db::name('wd_xcx_refund_address')->where("uniacid",$appletid)->where('id', $id)->delete();
            if($res){
                $this->success('地址删除成功', Url('Returnadd/index').'?appletid='.$appletid);
            }else{
                $this->error('地址删除失败：已删除或不存在', Url('Returnadd/index').'?appletid='.$appletid);
            }
        }else{
            $this->error('地址删除失败：已删除或不存在', Url('Returnadd/index').'?appletid='.$appletid);
        }
    }

    //批量删除操作
    public function delall(){
        $appletid = input("appletid");
        $array1=input('ids');
        $arr=explode(',',$array1);
        $res = Db::name('wd_xcx_refund_address')->where("uniacid",$appletid)->where('id',"in",$arr)->delete();
        if($res){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }
}
