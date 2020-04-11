<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;
class Printset extends Base
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
        		
                $list = Db::name('wd_xcx_print')->where("uniacid",$id)->paginate(10, false, ['query' => ['appletid' =>input('appletid')]]);
                $lists = $list->toArray()['data'];
                foreach ($lists as $key => &$value) {
                    $value['protype'] = unserialize($value['protype']);
                }
                $counts = Db::name('wd_xcx_print')->where("uniacid",$id)->count();
                $this->assign('lists',$lists);
                $this->assign('list',$list);
                $this->assign('counts',$counts);
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
    public function add(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);

                $id = intval(input('id'));
                $info = [];
                if($id > 0){
                    $info = Db::name('wd_xcx_print')->where("uniacid",$appletid)->where("id",$id)->find();
                    $info['protype'] = unserialize($info['protype']);
                }
                $this->assign('info',$info);
                $this->assign('id',$id);
            }else{
                $usergroup = Session::get('usergroup');
                if($usergroup==1){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/applet');
                }
                if($usergroup==2){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/index');
                }
                
            }
            return $this->fetch('add');
        }else{
            $this->redirect('Login/index');
        }
        
    }
    public function save(){
        $appletid = input("appletid");
    	$id = input("id");
        $data = array();
        //小程序ID
        $data['uniacid'] = input("appletid");
        $data['flag'] = input("flag");
        $data['pname'] = input("pname");
        $data['models'] = input("models");
        $data['nid'] = input("nid");
        $data['nkey'] = input("nkey");
        $data['uid'] = input("uid");
        $data['apikey'] = input("apikey");
        $data['title'] = input("title");
        $data['protype'] = serialize(input("protype/a"));
        $data['num'] = input("num");
        if($id > 0){
            $res = Db::name('wd_xcx_print')->where('id', $id)->update($data);
        }else{
            $res = Db::name('wd_xcx_print')->insert($data);
        }
        if($res){
            $this->success('添加/修改成功',Url('Printset/index').'?appletid='.$appletid);
        }else{
            $this->success('添加/修改失败');
        }
    }
    public function del(){
        $appletid = input("appletid");
        $id = input("id");
        $data = array(
            "uniacid"=>$appletid,
            "id"=>$id
        );
        $res = Db::name('wd_xcx_print')->where($data)->delete();
        if($res){
            $this->success('删除成功');
        }else{
            $this->success('删除失败');
        }
    }
    public function delall(){
        $appletid = input("appletid");
        $arr = input('arr');
        $arrs = explode(',',$arr);
        $res = Db::name('wd_xcx_print')->where("uniacid",$appletid)->where('id',"in",$arrs)->delete();
        if($res){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }
}
