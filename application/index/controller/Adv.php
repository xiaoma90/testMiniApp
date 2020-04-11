<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;
class Adv extends Base
{
    public function adv(){
        if(check_login()){
        	if(powerget()){
        		$id = input("appletid");
        		$res = Db::name('wd_xcx_applet')->where("id",$id)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
        		$this->assign('applet',$res);
                $bases = Db::name('wd_xcx_nav')->where("uniacid",$id)->find();
                $this->assign('bases',$bases);
                $adv = array();
                $adv = Db::name('wd_xcx_banner')->where("uniacid",$id)->where("type","banner")->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                if($adv->toArray()){
                    $list = $adv->toArray()['data'];
                    foreach ($list as $key => &$value) {
                        if($value['pic']){
                             $value['pic'] = remote($id,$value['pic'],1);
                        }else{
                            $value['pic'] = remote($id,"/image/noimage.jpg",1);
                        }
                    }
                }
                $count = Db::name('wd_xcx_banner')->where("uniacid",$id)->where("type","banner")->count();
                $this->assign('counts',$count);
                $this->assign('list',$list);
                $this->assign('adv',$adv);
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
            return $this->fetch('adv');
        }else{
            $this->redirect('Login/index');
        }
        
    }
    public function kadv(){
        if(check_login()){
            if(powerget()){
                $id = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $bases = Db::name('wd_xcx_nav')->where("uniacid",$id)->find();
                $this->assign('bases',$bases);
                $adv = array();
                $adv = Db::name('wd_xcx_banner')->where("uniacid",$id)->where("type","bigad")->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                if($adv->toArray()){
                    $list = $adv->toArray()['data'];
                    foreach ($list as $key => &$value) {
                        if($value['pic']){
                        $value['pic'] = remote($id,$value['pic'],1);
                        }else{
                            $value['pic'] = remote($id,"/image/noimage.jpg",1);
                        }
                    }
                }
                $count = Db::name('wd_xcx_banner')->where("uniacid",$id)->where("type","bigad")->count();
                $this->assign('list',$list);
                $this->assign('counts',$count);
                $this->assign('adv',$adv);
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
            return $this->fetch('kadv');
        }else{
            $this->redirect('Login/index');
        }
        
    }
    public function sadv(){
        if(check_login()){
            if(powerget()){
                $id = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $bases = Db::name('wd_xcx_nav')->where("uniacid",$id)->find();
                $this->assign('bases',$bases);
                $adv = array();
                $adv = Db::name('wd_xcx_banner')->where("uniacid",$id)->where("type","miniad")->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                if($adv->toArray()){
                    $list = $adv->toArray()['data'];
                    foreach ($list as $key => &$value) {
//                        $value['pic'] = remote($id,$value['pic'],1);
                        if($value['pic']){
                            $value['pic'] = remote($id,$value['pic'],1);
                        }else{
                            $value['pic'] = remote($id,"/image/noimage.jpg",1);
                        }
                    }
                }
                $count = Db::name('wd_xcx_banner')->where("uniacid",$id)->where("type","miniad")->count();
                $this->assign('list',$list);
                $this->assign('counts',$count);
                $this->assign('adv',$adv);
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
            return $this->fetch('sadv');
        }else{
            $this->redirect('Login/index');
        }
        
    }
    public function tadv(){
        if(check_login()){
            if(powerget()){
                $id = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $bases = Db::name('wd_xcx_nav')->where("uniacid",$id)->find();
                $this->assign('bases',$bases);
                $adv = array();
                $adv = Db::name('wd_xcx_banner')->where("uniacid",$id)->where("type","indexad")->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                if($adv->toArray()){
                    $list = $adv->toArray()['data'];
                    foreach ($list as $key => &$value) {
                        if($value['pic']){
                            $value['pic'] = remote($id,$value['pic'],1);
                        }else{
                            $value['pic'] = remote($id,"/image/noimage.jpg",1);
                        }
                    }
                }
                $count = Db::name('wd_xcx_banner')->where("uniacid",$id)->where("type","indexad")->count();
                $this->assign('list',$list);
                $this->assign('counts',$count);
                $this->assign('adv',$adv);
                
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
            return $this->fetch('tadv');
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
                $bases = Db::name('wd_xcx_nav')->where("uniacid",$appletid)->find();
                $this->assign('bases',$bases);
                $id = input("picid");
                $pics = array();
                if($id){
                    $pics = Db::name('wd_xcx_banner')->where("id",$id)->find();
                    if($pics['pic']){
                        $pics['pic'] = remote($appletid,$pics['pic'],1);
                    }
                }else{
                    $id = 0;
                }
                $this->assign('idval',$id);
                $this->assign('pics',$pics);
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
            return $this->fetch('add');
        }else{
            $this->redirect('Login/index');
        }
        
    }
    public function save(){
        $data = array();
        $id = input("picid");
        //小程序ID
        $data['uniacid'] = input("appletid");
        //排序
        $num = $_POST['num'];
        if($num){
            $data['num'] = $num;
        }
        $type = input("type");
        if($type){
            $data['type'] = $type;
        }
        $flag = input("flag");
        if($flag !==false){
            $data['flag'] = $flag;
        }else{
            $data['flag'] =1;
        }
        //缩略图
        $pic = input("commonuploadpic");
        if($pic){
            $data['pic'] = remote($data['uniacid'],$pic,2);
        }
        $url = input("url");
        if($url){
            $data['url'] = $url;
        }
        $descp = input("descp");
        if($descp){
            $data['descp'] = $descp;
        }
        // echo "<pre>";
        // var_dump($data);
        // echo "</pre>";
        // die();
        if($id){
            $res = Db::name('wd_xcx_banner')->where("id",$id)->update($data);
        }else{
            $res = Db::name('wd_xcx_banner')->insert($data);
        }
        if($res){
          $this->success('基础信息更新成功！');
        }else{
          $this->error('基础信息更新失败，没有修改项！');
          exit;
        }
    }
    //单个图片上传操作
    function onepic_uploade($file){
        $thumb = request()->file($file);
        if(isset($thumb)){
            $dir = upload_img();
            $info = $thumb->move($dir); 
            if($info){  
                $imgurl = ROOT_HOST."/upimages/".date("Ymd",time())."/".$info->getFilename();
                return $imgurl;
            }  
        }
    }
    // 删除操作
    public function del(){
        $data['id'] = input("picid");
        $res = Db::name('wd_xcx_banner')->where($data)->delete();
        if($res){
            $this->success('删除成功');
        }else{
            $this->success('删除失败');
        }
    }
}