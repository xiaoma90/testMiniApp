<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;
class Wxapps extends Base
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

                $cid=input("cid")?input("cid"):0;
                $keys=input("key");

                $listV = Db::name('wd_xcx_cate')->where("uniacid",$id)->where("type",'showWxapps')->where("cid",0)->order('num desc')->select();
                $listAll = array();
                foreach($listV as $key=>$val) {
                    $cids = intval($val['id']);
                    $listP = Db::name('wd_xcx_cate')->where("uniacid",$id)->where("id",$cids)->order('num desc')->select(); 
                    $listS = Db::name('wd_xcx_cate')->where("uniacid",$id)->where("cid",$cids)->order('num desc')->select(); 
                    //子集数据量
                    $zjcount = Db::name('wd_xcx_cate')->where("uniacid",$id)->where("cid",$cids)->order('num desc')->count(); 
                    $listP['data'] = $listS;
                    $listP['zcount'] = $zjcount;
                    array_push($listAll,$listP);
                }
                $this->assign('cate',$listAll);
                //获取子集
                $listallcate=Db::name('wd_xcx_cate')->where("cid",$cid)->select();
                $array1=array();
                for($a=0;$a<count($listallcate);$a++){
                    array_push($array1,$listallcate[$a]['id']);
                }
                array_push($array1,$cid);

                if($cid>0 && $keys != ''){
                    $wxapps = Db::name('wd_xcx_wxapps')->where("uniacid",$id)->where("cid","in",$array1)->where("title","like","%".$keys."%")->order('num desc')->paginate(10,false,[ 'query' => array('appletid'=>$id)]);
                    $count = Db::name('wd_xcx_wxapps')->where("uniacid",$id)->where("cid","in",$array1)->where("title","like","%".$keys."%")->order('num desc')->count();
                }else if($cid > 0 && $keys == ''){
                    $wxapps = Db::name('wd_xcx_wxapps')->where("uniacid",$id)->where("cid","in",$array1)->order('num desc')->paginate(10,false,[ 'query' => array('appletid'=>$id)]);
                    $count = Db::name('wd_xcx_wxapps')->where("uniacid",$id)->where("cid","in",$array1)->order('num desc')->count();
                }else if($cid == 0 && $keys != ''){
                    $wxapps = Db::name('wd_xcx_wxapps')->where("uniacid",$id)->where("title","like","%".$keys."%")->order('num desc')->paginate(10,false,[ 'query' => array('appletid'=>$id)]);
                    $count = Db::name('wd_xcx_wxapps')->where("uniacid",$id)->where("title","like","%".$keys."%")->order('num desc')->count();
                }else{
                    $wxapps = Db::name('wd_xcx_wxapps')->where("uniacid",$id)->order('num desc')->paginate(10,false,[ 'query' => array('appletid'=>$id)]);
                    $count = Db::name('wd_xcx_wxapps')->where("uniacid",$id)->order('num desc')->count();
                }
                

                $newwxapps = $wxapps->toArray();
                foreach ($newwxapps['data'] as &$res) {
                    $yhqs = Db::name('wd_xcx_cate')->where("uniacid",$id)->where("id",$res['cid'])->find();
                    $res['cid'] = $yhqs['name'];
                    if($res['thumb']) {
                        $res['thumb'] = remote($id,$res['thumb'],1);
                    }else{
                        $res['thumb']=remote($id,"/image/noimage.jpg",1);
                    }
                }
    
                // echo "<pre>";
                // var_dump($wxapps['data']);
                // echo "</pre>";
                // die();
                $this->assign('wxapps',$newwxapps);
                $this->assign('counts',$count);
                
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
                
                // 该小程序的栏目
                $listV = Db::name('wd_xcx_cate')->where("uniacid",$appletid)->where("cid",0)->where("type","showWxapps")->order('num desc')->select();
                $listAll = array();
                foreach($listV as $key=>$val) {
                    $id = intval($val['id']);
                    
                    $listP = Db::name('wd_xcx_cate')->where("uniacid",$appletid)->where("id",$id)->order('num desc')->select(); 
                    $listS = Db::name('wd_xcx_cate')->where("uniacid",$appletid)->where("cid",$id)->order('num desc')->select(); 
                    //子集数据量
                    $zjcount = Db::name('wd_xcx_cate')->where("uniacid",$appletid)->where("cid",$id)->order('num desc')->count(); 
                    $listP['data'] = $listS;
                    $listP['zcount'] = $zjcount;
                    array_push($listAll,$listP);
                }
                $this->assign('cate',$listAll);
                // 如果有小程序ID 获取对应信息
                $wxappsid = input("wxappsid");
                $this->assign('wxappsid',$wxappsid);
                $wxappsinfo = "";
                if($wxappsid){
                    $wxappsinfo = Db::name('wd_xcx_wxapps')->where("uniacid",$appletid)->where("id",$wxappsid)->find(); 
                    if($wxappsinfo['thumb']){
                        $wxappsinfo['thumb'] = remote($appletid,$wxappsinfo['thumb'],1);
                    }
                }
                $this->assign('wxappsinfo',$wxappsinfo);
                // var_dump($couponinfo);
                // die();
                
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
        //小程序ID
        $data['uniacid'] = input("appletid");
        //排序
        $data['num'] = input('num');
        //栏目
        $data['cid'] = input('cid');
        //推荐到首页
        $data['type_i'] = input('type_i');
        //名称
        $data['title'] = input('title');
        
        //缩略图
        $thumb = input("commonuploadpic");
        if($thumb){
            $data['thumb'] = remote($data['uniacid'],$thumb,2);
        }
        //appid
        $data['appId'] = input('appId');
        // 打开路径
        $data['path'] = input('path');
        // 简介
        $data['desc'] = input('desc');
        $wxappsid = input("wxappsid");
        $cid = input("cid");
        $pcid = Db::name('wd_xcx_cate')->where("id",$cid)->where("uniacid",input("appletid"))->field("cid")->find();
        
        if($pcid['cid'] == 0){
            $data['pcid'] = $cid;
        }else{
            $data['pcid'] = $pcid['cid'];
        }
        
        if($wxappsid){
            $res = Db::name('wd_xcx_wxapps')->where("id",$wxappsid)->update($data);
        }else{
            $res = Db::name('wd_xcx_wxapps')->insert($data);
        }
        if($res){
          $this->success('小程序信息更新成功！',Url('Wxapps/index').'?appletid='.$data['uniacid']);
        }else{
          $this->error('小程序信息更新失败，没有修改项！');
          exit;
        }
    }
    public function del(){
        $data['id'] = input("wxappsid");
        $res = Db::name('wd_xcx_wxapps')->where($data)->delete();
        if($res){
            $this->success('删除成功');
        }else{
            $this->success('删除失败');
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
                $array1=input('wxapps');
                $arr=explode(',',$array1);

                    $res = Db::name('wd_xcx_wxapps')->where("uniacid",$appletid)->where('id',"in",$arr)->delete();
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