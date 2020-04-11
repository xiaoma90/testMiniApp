<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;
class Nav extends Base
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
        		
                $bases = Db::name('wd_xcx_nav')->where("uniacid",$id)->find();
                $this->assign('bases',$bases);
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
    	$appletid = input("appletid");
        
        $data = array();
        //小程序ID
        $data['uniacid'] = input("appletid");
        //导航栏目
        $url = input("url");
        if($url){
            $data['url'] = $url;
        }
        //首页顶部样式
        $statue = input("statue");
        if($statue){
            $data['statue'] = $statue;
        }else{
            $data['statue'] = 0;
        }
        //导航板块中文名称
        $name = input("name");
        if($name){
            $data['name'] = $name;
        }
        //导航板块英文名称
        $ename = input("ename");
        if($ename){
            $data['ename'] = $ename;
        }
        //首页顶部样式
        $name_s = input("name_s");
        if($name_s){
            $data['name_s'] = $name_s;
        }else{
            $data['name_s'] = 0;
        }
        //导航上下边距
        $box_p_tb = input("box_p_tb");
        if($box_p_tb){
            $data['box_p_tb'] = $box_p_tb;
        }
        //导航左右边距
        $box_p_lr = input("box_p_lr");
        if($box_p_lr){
            $data['box_p_lr'] = $box_p_lr;
        }
        //每排显示数量
        $number = input("number");
        if($number){
            $data['number'] = $number;
        }else{
            $data['number'] = 5;
        }
        //图标占比
        $img_size = input("img_size");
        if(!empty($img_size)){
            $data['img_size'] = $img_size;
        }
        //标题样式
        $title_position = input("title_position");
        if($title_position){
            $data['title_position'] = $title_position;
        }else{
            $data['title_position'] = 0;
        }
        //标题颜色
        $title_color = input("title_color");
        if($title_color){
            $data['title_color'] = "#".$title_color;
        }
        //标题背景颜色
        $title_bg = input("title_bg");
        if($title_bg){
            $data['title_bg'] = "#".$title_bg;
        }
       
        
        $bases = Db::name('wd_xcx_nav')->where("uniacid",$appletid)->count();
        if($bases>0){
            $res = Db::name('wd_xcx_nav')->where("uniacid",$appletid)->update($data);
        }else{
            $data['uniacid'] = $appletid;
            $res = Db::name('wd_xcx_nav')->insert($data);
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
            $info = $thumb->validate(['ext'=>'jpg,png,gif,jpeg'])->move($dir); 
            if($info){  
                $imgurl = ROOT_HOST."/upimages/".date("Ymd",time())."/".$info->getFilename();
                return $imgurl;
            }  
        }
    }
    public function addnav(){
        if(check_login()){
            if(powerget()){
                $id = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
    
                $bases = Db::name('wd_xcx_nav')->where("uniacid",$id)->find();
                
                $cid = input("id");
                $nav = array();
                if($cid){
                    $nav = Db::name('wd_xcx_navlist')->where("uniacid",$id)->where("id",$cid)->find();
                    if($nav['pic']){
                        $nav['pic'] = remote($id,$nav['pic'],1);
                    }
                }
                $this->assign('nav',$nav);
                $this->assign('cid',$cid);
                $this->assign('bases',$bases);
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
            return $this->fetch('addnav');
        }else{
            $this->redirect('Login/index');
        }
    }
    public function savenav(){
        $data = array();
        //小程序ID
        $data['uniacid'] = input("appletid");
        $num = input("num");
        if($num){
            $data['num'] = $num;
        }
        $flag = input("flag");
        if($flag!==false){
            $data['flag'] = intval($flag);
        }
        $type = input("type");
        $data['type'] = $type;
        $title = input("title");
        if($title){
            $data['title'] = $title;
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
        $url2 = input("url2");
        if($url2){
            $data['url2'] = $url2;
        }
        $id = input("id");
        
        if ($id) {
            $res = Db::name('wd_xcx_navlist')->where("id",$id)->update($data);
        } else {
            $res = Db::name('wd_xcx_navlist')->insert($data);
        }
        if($res){
          $this->success('自定义导航更新成功！',Url('Nav/navlist').'?appletid='.$data['uniacid']);
        }else{
          $this->error('自定义导航更新失败，没有修改项！');
          exit;
        }
    }
    public function navlist(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
    
                $bases = Db::name('wd_xcx_nav')->where("uniacid",$appletid)->find();
                $nav = Db::name('wd_xcx_navlist')->where("uniacid",$appletid)->order('num desc')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                if($nav->toArray()){
                    $list = $nav->toArray()['data'];
                    foreach ($list as $key => &$value) {
                            if($value['pic']){
                               $value['pic'] = remote($appletid,$value['pic'],1);
                            }else{
                                $value['pic']=remote($appletid,"/image/noimage.jpg",1);
                            }
                    }
                }
                $count = Db::name('wd_xcx_navlist')->where("uniacid",$appletid)->order('num desc')->count();
                
                $this->assign('list',$list);
                $this->assign('nav',$nav);
                $this->assign('counts',$count);
                $this->assign('bases',$bases);
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
            return $this->fetch('navlist');
        }else{
            $this->redirect('Login/index');
        }    
    }
    public function delete(){
        $appletid = input("appletid");
        $id = input("id");
        $data = array(
            "uniacid"=>$appletid,
            "id"=>$id
        );
        $res = Db::name('wd_xcx_navlist')->where($data)->delete();
        if($res){
            $this->success('删除成功');
        }else{
            $this->success('删除失败');
        }
    }
}