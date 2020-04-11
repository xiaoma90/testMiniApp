<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;


class Pinglun extends Base
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

                $lists = Db::name('wd_xcx_comment')->alias('a')->join('wd_xcx_products b','a.aid = b.id')->where("a.uniacid",$id)->order('b.id desc')->field('b.title,b.type,a.id,a.aid,a.text,a.flag,a.createtime')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                $counts=Db::name('wd_xcx_comment')->alias('a')->join('wd_xcx_products b','a.aid = b.id')->where("a.uniacid",$id)->count();
                 $list=$lists->toArray()['data'];
                foreach($list as $k => $v){
                    $list[$k]['createtime']= date("Y-m-d H:i:s",$v['createtime']);
                }

        		$this->assign('list',$list);
                $this->assign('lists',$lists);
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
    public function post(){

        if(check_login()){
            if(powerget()){

                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);

                $id = intval(input("id"));

                $list = Db::name('wd_xcx_comment')->alias('a')->join('wd_xcx_products b','a.aid = b.id')->where('a.id',$id)->order('b.id desc')->field('b.title,b.type,a.id,a.aid,a.text,a.flag,a.createtime')->find();
                $list['createtime']= date("Y-m-d H:i:s",$list['createtime']);
                $this->assign('list',$list);
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
            return $this->fetch('post');
        }else{
            $this->redirect('Login/index');
        }
    }

    public function del(){
        $id = input("id");
        // var_dump($id);exit;
        $appletid = input("appletid");
        $res = Db::name('wd_xcx_comment')->where('id', $id)->where('uniacid',$appletid)->delete();
        if($res){
            $this->success('删除成功');
        }else{
            $this->error("删除失败！");
        }
    }
    public function plsave(){
        $id = intval(input('id'));
        $appletid = input("appletid");
        $flag = intval(input('flag'));
        $data = array(
            'flag' => $flag         
        );
        $res = Db::name('wd_xcx_comment')->where('id', $id)->where('uniacid',$appletid)->update($data);
        if($res){
            $this->success('评论审核成功', Url('Pinglun/index').'?appletid='.$appletid);
        }else{
            $this->error("评论审核失败！", Url('Pinglun/index').'?appletid='.$appletid);
        }
    }
    //批量删除操作
    public function delall(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $array1=input('pingluns');
                $arr=explode(',',$array1);

                $res = Db::name('wd_xcx_comment')->where("uniacid",$appletid)->where('id',"in",$arr)->delete();
                if($res){
                    $this->success('删除成功');
                }else{
                    $this->error('删除失败');
                }
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

}