<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;
class Evaluate extends Base
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
                $pid=input('proid');
                $type=input("type");
                $search_type = intval(input('search_type'));
                if($search_type!=0){
                    $lists=Db::name('wd_xcx_evaluate')->where('pid',$pid)->where('uniacid',$id)->where("assess",$search_type)->order('id','desc')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"), "proid"=> $pid, "type" => $type)]);
                    $count=Db::name("wd_xcx_evaluate")->where('pid',$pid)->where("assess",$search_type)->where('uniacid',$id)->count();
                }else{
                    $lists = Db::name('wd_xcx_evaluate')->where('pid',$pid)->where('uniacid',$id)->order('id','desc')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"), "proid"=> $pid, "type" => $type)]);
                    $count=Db::name("wd_xcx_evaluate")->where('pid',$pid)->where('uniacid',$id)->count();
                }

                $list = $lists->toArray();
                foreach ($list['data'] as $key => &$value) {
                    $userinfo = getNameAvatar($value['suid'], $id);
                    $value['nickname'] = $userinfo['nickname'];
                    $value['avatar'] = $userinfo['avatar'];
                    $value['imgs'] = unserialize($value['imgs']);
                }
                $this->assign('lists',$lists);
                $this->assign("type",$type);
                $this->assign('proid',$pid);
                $this->assign('list',$list);
                $this->assign('counts',$count);
                $this->assign('search_type',$search_type);
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

    public function detail(){
        if(check_login()){
            if(powerget()){
                $id = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $evid=input('evid');
                $proid=input("proid");
                $type=input("type");
                $op=input("op");
                if($op=="hf"){
                    $huifuid=input('huifuid');
                    $huifu = input('huifu');
                    $cishu = intval(input('cishu'));
                    $evaluate=Db::name("wd_xcx_evaluate")->where("id",$huifuid)->where("uniacid",$id)->find();
                    if($evaluate){
                        if((!$evaluate['reply_first'])&&$cishu==1){
                            $data=array(
                                "reply_first"=>$huifu,
                                "reply_first_time"=>date('Y-m-d H:i:s',time())
                            );
                            Db::name('wd_xcx_evaluate')->where("id",$huifuid)->where("uniacid",$id)->update($data);
                        }
                        if((!$evaluate['reply_second'])&&$cishu==2){
                            $data2=array(
                                "reply_second"=>$huifu,
                                "reply_second_time"=>date('Y-m-d H:i:s',time())
                            );
                            Db::name('wd_xcx_evaluate')->where("id",$huifuid)->where("uniacid",$id)->update($data2);
                        }
                        $this->success("回复成功");

                    }else{
                        $this->error("回复失败");
                    }
                }

                 $evaluate=Db::query("SELECT * FROM {$this->prefix}wd_xcx_evaluate WHERE id = {$evid} and uniacid = {$id}");
                 if($evaluate[0]){
                    $userinfo = getNameAvatar($evaluate[0]['suid'], $id);
                    $evaluate[0]['nickname'] = $userinfo['nickname'];
                    $evaluate[0]['avatar'] = $userinfo['avatar'];
                    if($evaluate[0]['imgs']){
                        $evaluate[0]['imgs'] = unserialize($evaluate[0]['imgs']);
                    }
                    if($evaluate[0]['append_imgs']){
                        $evaluate[0]['append_imgs'] = unserialize($evaluate[0]['append_imgs']);
                    }
                }
                $this->assign("evaluate",$evaluate[0]);
                $this->assign("proid",$proid);
                $this->assign("type",$type);
                $this->assign('evid',$evid);
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
            return $this->fetch('detail');
        }else{
            $this->redirect('Login/index');
        }

    }

    public function del(){
        if(check_login()){
            if(powerget()){
                $id = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $pid=input('proid');
                $type=input("type");
                $evid=input("evid");
                $row=Db::name("wd_xcx_evaluate")->where("id",$evid)->where("uniacid",$id)->find();
                if($row){
                    Db::name("wd_xcx_evaluate")->where("id",$evid)->where("uniacid",$id)->delete();
                }

                $this->assign("type",$type);
                $this->assign('proid',$pid);
                $this->success('删除成功');
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
                $res1 = Db::name('wd_xcx_evaluate')->where("uniacid",$appletid)->where('id',"in",$arr)->delete();
                if($res1){
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