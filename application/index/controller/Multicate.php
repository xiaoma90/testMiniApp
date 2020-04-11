<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;


class Multicate extends Base
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

                $catelist = Db::name('wd_xcx_multicate')->where("uniacid",$id)->order('id desc')->select();
                $this->assign('newcoupon',$catelist);
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

                $id = input("appletid");

                $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);

                $cateid = input("cateid");
                if($cateid){
                    $cateinfo = Db::name('wd_xcx_multicate')->where("uniacid",$id)->where("id",$cateid)->find();
                    $cateinfo['top_catas'] = unserialize($cateinfo['top_catas']);
                }else{
                    $cateid = 0;
                    $cateinfo = "";
                }

            $top_catat = Db::name('wd_xcx_multicates')->where("uniacid",$id)->where("pid",0)->where('status',1)->select();

            $this->assign('cateinfo',$cateinfo);
            $this->assign('top_catat',$top_catat);
            $this->assign('cateid',$cateid);
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

    public function multikey(){

        if(check_login()){
            if(powerget()){

                $id = input("appletid");

                $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);

                $list = Db::name('wd_xcx_multicates')->where("pid",0)->where("uniacid",$id)->select();
                foreach ($list as $k => $v){
                    $data = Db::name('wd_xcx_multicates')->where("pid",$v['id'])->select();
                    $temp = [];
                    foreach ($data as $ks => $vs){
                        array_push($temp,$vs['varible']);
                    }

                    $list[$k]['content'] = implode(',',$temp);
                }

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

            return $this->fetch('multikey');
        }else{
            $this->redirect('Login/index');
        }
        
    }

    public function keyadd(){
        if(check_login()){
            if(powerget()){

                $id = input("appletid");

                $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);

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

            return $this->fetch('keyadd');
        }else{
            $this->redirect('Login/index');
        }
        
    }

    public function keyedit(){
        $id = input("appletid");
        $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
        if(!$res){
            $this->error("找不到对应的小程序！");
        }
        $this->assign('applet',$res);
        $id = input('cateid');
        $list = Db::name('wd_xcx_multicates')->where("id",$id)->find();
        $sons = Db::name('wd_xcx_multicates')->where("pid",$id)->select();
        $temp = [];
        foreach ($sons as $k => $v){
            array_push($temp,$v['varible']);
        }
        $list['content'] = implode(',',$temp);

        $this->assign('list',$list);
        $this->assign('sons',$sons);
        return $this->fetch('keyedit');
    }

    public function keyeditsave(){
        $uniacid = input("appletid");
        $cateid = intval(input("id"));
        $ids = input('ids/a');
        $varibles = input('varibles/a');
        $sort = input('sort');
        $name = input('name');
        $status = input('status');
        $pid=0;

        $count = Db::name('wd_xcx_multicates')->where("pid",$cateid)->count();
        Db::name('wd_xcx_multicates')->where("id",$cateid)->update(array('sort' =>$sort,'varible' =>$name,'status' => $status));
   
        for ($i = 0 ; $i < count($ids);$i++){
            if($ids[$i] > 0 && $varibles[$i] != ""){
                Db::name('wd_xcx_multicates')->where("id",$ids[$i])->update(array('varible' => $varibles[$i]));
            }else{
                if($varibles[$i] != ""){
                    Db::name('wd_xcx_multicates')->insert(array('sort' => $sort,'status' => $status,'varible' => $varibles[$i],'pid' => $cateid,'uniacid' => $uniacid));
                }
            }
        }
 

        $this->success("编辑成功",Url('Multicate/multikey').'?appletid='.$uniacid);
    }
        

    public function keysave(){
        $data = array();
        $data['uniacid'] = input("appletid");
        $data['sort'] = input("sort");
        
        if(input("status") == ""){
            $data['status'] = 0;
        }else{
            $data['status'] = input("status");
        }
        $data['varible'] = input("name");
        $content = input("content");

        if($content == '') {
             $this->error("筛选条件不能为空！");
            exit;
        }
        $pid_key = Db::name('wd_xcx_multicates')->insertGetId($data);

        if ($pid_key) {
            $varible = explode(',', $content);
            foreach ($varible as $v) {
                $pdata['status'] = $data['status'];
                $pdata['varible'] = $v;
                $pdata['pid'] = $pid_key;
                $pdata['uniacid'] = $data['uniacid'];
                Db::name('wd_xcx_multicates')->insert($pdata);
            }
            $this->success("添加成功",Url('Multicate/multikey').'?appletid='.$data['uniacid']);
        } else {
            $this->error("添加失败！");
        }
    }

    public function getcate(){
        $type = $_POST['type'];
        $uniacid = $_POST['uniacid'];
        $catelist = Db::name('wd_xcx_cate')->where("uniacid",$uniacid)->where('cid',0)->where("type",$type)->where('statue',1)->field('id,name')->select();
        
        return $catelist;
    }
    public function getcates(){
        $id = $_POST['id'];

        $catelists = Db::name('wd_xcx_cate')->whereOr('cid',$id)->whereOr('id',$id)->where('statue',1)->field('id,name')->order('id asc')->select();
       
        return $catelists;
    }
    

    public function save(){
        $uniacid = input("appletid");
        $id = input("cateid");
        if(input('name')=="") {
            $this->error('请输入栏目名称！');
            exit;
        }
        if(is_null(input('statue'))){
            $statue = 1;
        }else{
            $statue = input('statue');
        }

        $type = input('type');
        if($type =="showArt" || $type == "showPic"){
            if(is_null(input('list_style'))){
                $list_style = 2;
            }else{
                $list_style = intval(input('list_style'));
            }
        }else{
            if(is_null(input('list_style'))){
                $list_style = 12;
            }else{
                $list_style = intval(input('list_style'));
            }
        }

        if(is_null(input('list_stylet'))){
            $list_stylet = 'tl';
        }else{
            $list_stylet = intval(input('list_stylet'));
        }

        if(input('top_cats/a')==null){
            $this->error('请选择顶级栏目！');
            exit;
        }

        $data = array(
            'uniacid' => $uniacid,
            'name' => input('name'),
            'type' => $type,
            'statue' => $statue,
            'list_style' => $list_style,
            'list_stylet' => $list_stylet,
            'top_catas' => serialize(input('top_cats/a')),
        );
        if (empty($id)) {
           $res = Db::name('wd_xcx_multicate')->insert($data);
        } else {
           $res = Db::name('wd_xcx_multicate')->where('id' ,$id)->where('uniacid', $uniacid)->update($data);
        }
        if($res){
          $this->success('模块更新成功！',Url('Multicate/index').'?appletid='.$uniacid);
        }else{
          $this->error('模块更新失败，没有修改项！');
          exit;
        }


    }

    //单个图片上传操作
    public function onepic_uploade($file){
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

    public function del(){
        $id = input("cateid");
        $res = Db::name('wd_xcx_multicate')->where('id',$id)->delete();
        if($res){
            $this->success('删除成功');
        }else{
            $this->success('删除失败');
        }
    }
    public function keydel(){
        $id = input("cateid");
        $res = Db::name('wd_xcx_multicates')->whereOr('id',$id)->whereOr('pid',$id)->delete();
        if($res){
            $this->success('删除成功');
        }else{
            $this->success('删除失败');
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
                $array1=input('multicates');
                $arr=explode(',',$array1);

                    $res = Db::name('wd_xcx_multicate')->where("uniacid",$appletid)->where('id',"in",$arr)->delete();
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

    //批量删除操作
    public function keydelall(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $array1=input('keys');
                $arr=explode(',',$array1);

                $res = Db::name('wd_xcx_multicates')->where("uniacid",$appletid)->where('id',"in",$arr)->delete();
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