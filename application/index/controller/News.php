<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;
class News extends Base
{
    public function cate(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $listV2 = Db::name('wd_xcx_cate')->where("uniacid",$appletid)->where("cid",0)->where("type", "showArt")->order('num desc,id desc')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                $count = Db::name('wd_xcx_cate')->where("uniacid",$appletid)->where("cid",0)->where("type", "showArt")->count();
                $listV = $listV2->toArray();
                $listAll = array();
                foreach($listV['data'] as $key=>$val) {
                    $id = intval($val['id']);                 
                    $listP = Db::name('wd_xcx_cate')->where("uniacid",$appletid)->where("id",$id)->where("type", "showArt")->order('num desc,id desc')->select(); 
                    foreach($listP as $k => $v){
                        if($v['catepic']){
                            $listP[$k]['catepic'] = remote($appletid,$v['catepic'],1);
                        }else{
                            $pic="/image/noimage_1.png";
                            $listP[$k]['catepic'] =  remote($appletid,$pic,1);
                        }
                    }
                    $listS = Db::name('wd_xcx_cate')->where("uniacid",$appletid)->where("cid",$id)->where("type", "showArt")->order('num desc,id desc')->select(); 
                    
                    foreach ($listS as $ki => $vi) {
                        if($vi['catepic']){
                            $listS[$ki]['catepic'] = remote($appletid,$vi['catepic'],1);
                        }else{
                            $pic2="/image/noimage_1.png";
                            $listS[$ki]['catepic'] =  remote($appletid,$pic2,1);
                        }
                    }
                   
                    //子集数据量
                    $zjcount = Db::name('wd_xcx_cate')->where("uniacid",$appletid)->where("cid",$id)->where("type", "showArt")->order('num desc')->count(); 
                    $listP['data'] = $listS;
                    $listP['zcount'] = $zjcount;
                    array_push($listAll,$listP);
                }
                $this->assign('cates',$listAll);
                $this->assign('news',$listV2);
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
            return $this->fetch('cate');
        }else{
            $this->redirect('Login/index');
        }
        
    }
    public function cateadd(){
        if(check_login()){
            if(powerget()){
                $id = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $cate=array();

                $cateurlid = 0;
                $cateinfo = array();
                $cateid = input("cateid");
                $allimg = array();
                $cate = Db::name('wd_xcx_cate')->where("uniacid",$id)->where('type', "showArt")->select();
                if($cateid){
                    //有栏目号时，先判断该栏目是不是属于该小程序！
                    $lanmu = Db::name('wd_xcx_cate')->where("id",$cateid)->find();
                    if($lanmu['catepic']){
                        $lanmu['catepic'] = remote($id,$lanmu['catepic'],1);
                    }
                    if($lanmu['randid']){
                        $allimg = Db::name('wd_xcx_products_url')->where("randid",$lanmu['randid'])->select();
                        foreach($allimg as $k => &$v){
                            $allimg[$k]['url'] = remote($id,$v['url'],1);
                        }
                    }else{
                        $allimg=[];
                    }
                    if($lanmu['uniacid']==$id){
                        $cateinfo = $lanmu;
                        $cateinfo['cateconf'] = unserialize($cateinfo['cateconf']);
                        if($lanmu['cid']==0){
                            $cateurlid = 1;
                        }
                    }else{
                        $usergroup = Session::get('usergroup');
                        if($usergroup==1){
                            $this->error("找不到该栏目，或者该栏目不属于本小程序");
                        }
                        if($usergroup==2){
                            $this->error("找不到该栏目，或者该栏目不属于本小程序");
                        }
                    }
                }else{
                    $cateid=0;
                }

                $this->assign('cate',$cate);
                $this->assign('allimg',$allimg);
                $this->assign('cateid',$cateid);
                $this->assign('cateinfo',$cateinfo);
                $this->assign('cateurlid',$cateurlid);
                
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
            return $this->fetch('cateadd');
        }else{
            $this->redirect('Login/index');
        }
        
    }
    public function catesave(){
        $data = array();
        //小程序ID
        $data['uniacid'] = input("appletid");
        //排序
        $num = input("num");
        if($num){
            $data['num'] = $num;
        }
        $onlyid = input("onlyid");
        $imgsrcs = input("imgsrcs/a");
        if($imgsrcs){
            $imgarr = array();
            foreach ($imgsrcs as $k => $v) {
                $imgarr['randid'] = $onlyid;
                $imgarr['appletid'] = $data['uniacid'];
                $imgarr['url'] = remote($data['uniacid'],$v,2);
                $imgarr['dateline'] = time();
                $is = Db::name('wd_xcx_products_url')->insert($imgarr);
            }
        }else{
            $is = 1;
        }
        $data['randid'] = $onlyid;
        
        //启用
        $statue = input("statue");
        if($statue === false){
            $data['statue'] = 1;
        }else{
            $data['statue'] = (int)$statue;
        }
        //启用
        $slide_is = input("slide_is");
        if($slide_is){
            $data['slide_is'] = (int)$slide_is;
        }else{
            $data['slide_is'] = 2;
        }
        //所属栏目
        $cid = input("cid");
        if($cid){
            $data['cid'] = $cid;
        }else{
            $data['cid'] = 0;
        }
        //栏目名称
        $name = input("name");
        if($name){
            $data['name'] = $name;
        }else{
            $this->error("请填写栏目名称！");
        }
        //英文栏目名
        $ename = input("ename");
        if($ename){
            $data['ename'] = $ename;
        }
        $remote = Db::name("wd_xcx_base")->where("uniacid",$data['uniacid'])->field("remote")->find()['remote'];
        //栏目缩略图
        $catepic = input("commonuploadpic");
        if($catepic){
            $data['catepic'] = remote($data['uniacid'],$catepic,2);
        }
        //简介
        $cdesc = input("cdesc");
        if($cdesc){
            $data['cdesc'] = $cdesc;
        }
        //每页数量
        $pagenum = input("pagenum");
        if($pagenum){
            $data['pagenum'] = $pagenum;
        }
        //首页显示
        $show_i = input("show_i");
        if($show_i ){
            $data['show_i'] = $show_i;
        }else{
            $data['show_i'] = 0;
        }
        //首页标题样式
        $list_tstyle = input("list_tstyle");
        if($list_tstyle){
            $data['list_tstyle'] = $list_tstyle;
        }else{
            $data['list_tstyle'] = 0;
        }
        //列表标题样式
        $list_tstylel = input("list_tstylel");
        if($list_tstylel){
            $data['list_tstylel'] = $list_tstylel;
        }else{
            $data['list_tstylel'] = 0;
        }
        $list_style_more = input("list_style_more");
        if($list_style_more){
            $data['list_style_more'] = input('list_style_more');
        }else{
            $data['list_style_more'] = 1;
        }
        //列表类型
        $list_type = input("list_type");
        if($list_type){
            if($cid == 0){
                $data['list_type'] = $list_type;
            }else{
                $data['list_type'] = 1;
            }
            
        }else{
            if($cid == 0){
                $data['list_type'] = 0;
            }else{
                $data['list_type'] = 1;
            }
        }
        //列表标题样式
        $list_stylet = input("list_stylet");
        if($list_stylet){
            $data['list_stylet'] = $list_stylet;
        }
        //文章页面样式
        $pic_page_btn = input("pic_page_btn");
        if($pic_page_btn){
            $data['pic_page_btn'] = $pic_page_btn;
        }else{
            $data['pic_page_btn'] = 0;
        }
        
        // $pic_page_btn_zt = input("pic_page_btn_zt");
        // if($pic_page_btn_zt){
        //     $data['pic_page_btn_zt'] = $pic_page_btn_zt;
        // }else{
            $data['pic_page_btn_zt'] = 2;
        // }
        //栏目类型
        // $type = input("type");
        // if($type){
            $data['type'] = "showArt";
        // }
        // if($type == 'page'){
        //     $data['list_style'] = 3;
        // }
        //内容列表样式
        $list_style = input("list_style");
        // if($type=="showArt"||$type=="showPic"||$type=="showWxapps"){
            if($list_style != 12){
                $data['list_style'] = $list_style;
            }else{
                $data['list_style']=2;
            }
        // }else if($type=="showPro"){
            // if($list_style){
            //     $data['list_style'] = $list_style;
            // }else{
            //     $data['list_style']=12;
            // }
        // }
        $pic_page_bg = input("pic_page_bg");
        if($pic_page_bg!==false && $pic_page_bg !==null){
            $data['pic_page_bg'] = $pic_page_bg;
        }else{
            $data['pic_page_bg'] = 0;
        }
        //栏目内容
        $content = input("content");
        if($content){
            $data['content'] = $content;
        }
        $cateConf = array(
            'pmarb' => input("pmarb"),
            'ptit' => input("ptit"),
        );

        $data['to_pc_index'] = input('to_pc_index');
        

        $data['cateconf'] = serialize($cateConf);
        $id = input("cateid");
        if($id!=0){
            $res = Db::name('wd_xcx_cate')->where("id",$id)->update($data);
            $list = Db::name("wd_xcx_products")->where("cid",$id)->select();
            foreach($list as $k => $v){
                if($data['cid'] == 0){
                    Db::name("wd_xcx_products")->where("cid",$id)->update(array("pcid"=>$id));
                }else{
                    Db::name("wd_xcx_products")->where("cid",$id)->update(array("pcid"=>$data['cid']));
                }
            }
        }else{
            $res = Db::name('wd_xcx_cate')->insert($data);
        }
        if($res || $is){
          $this->success('栏目信息更新成功！',Url('News/cate').'?appletid='.$data['uniacid']);
        }else{
          $this->error('栏目信息更新失败，没有修改项！');
          exit;
        }
    }
    public function catedel(){
        $data['id'] = input("cateid");
        $res = Db::name('wd_xcx_cate')->where($data)->delete();
        if($res){
            $this->success('删除成功');
        }else{
            $this->success('删除失败');
        }
    }
    //批量删除操作
    public function catedelall(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                 $array1=input('cateids');
                $arr=explode(',',$array1);

               $list2=Db::name('wd_xcx_cate')->where("uniacid",$appletid)->where("id","in",$arr)->order('num desc,id desc')->select();
                $num=0;
                 for($i=0;$i<count($list2);$i++){
                     $list1 = Db::name('wd_xcx_cate')->where("uniacid",$appletid)->where("cid",$list2[$i]['id'])->order('num desc,id desc')->select();
                     for($j=0;$j<count($list1);$j++){
                         if(!in_array($list1[$j]["id"],$arr)){
                             $num=$num+1;
                         }
                     }
                 }
                 if($num==0){
                    $res = Db::name('wd_xcx_cate')->where("uniacid",$appletid)->where('id',"in",$arr)->delete();
                    if($res){
                        $this->success('删除成功');
                    }else{
                        $this->error('删除失败');
                    }
                 }else{
                     $this->error('所需栏目存在子栏目未被选择，删除失败');
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
            return $this->fetch('cate');
        }else{
            $this->redirect('Login/index');
        }
    }
    public function index(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $cid=input("cid")?input("cid"):0;
                $key=input("key");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $listV = Db::name('wd_xcx_cate')->where("uniacid",$appletid)->where('type','showArt')->where("cid",0)->order('num desc')->select();
                foreach($listV as $k=>&$val) {
                    $listS = Db::name('wd_xcx_cate')->where("uniacid",$appletid)->where("cid",$val['id'])->order('num desc')->select();
                    //子集数据量
                    $val['data'] = $listS;
                }
                $this->assign('cate',$listV);
                $this->assign('applet',$res);
                $listallcate = Db::name('wd_xcx_cate')->where("uniacid",$appletid)->where("cid",$cid)->select();
                $array1=array();
                for($a=0;$a<count($listallcate);$a++){
                    array_push($array1,$listallcate[$a]['id']);
                }
                array_push($array1,$cid);

                if($cid==0&&$key==false){
                    $news = Db::name('wd_xcx_products')->where("uniacid",$appletid)->where("type","showArt")->where('art_type', 1)->order('num desc, id desc')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                    $count = Db::name('wd_xcx_products')->where("uniacid",$appletid)->where("type","showArt")->where('art_type', 1)->order('num desc')->count();
                    $newnews = $news->toArray();
                    foreach ($newnews['data'] as &$res) {
                        $lanmu = Db::name('wd_xcx_cate')->where("id",$res['cid'])->find();
                        $res['lanmu'] = $lanmu['name'];
//                        $res['thumb'] = remote($appletid,$res['thumb'],1);
//                        var_dump($res['thumb']);
                        if($res['thumb']){
                            $res['thumb'] = remote($appletid,$res['thumb'],1);
                        }else{
                            $res['thumb']=remote($appletid,"/image/noimage.jpg",1);
                        }
                    }
                }else if($cid>0&&$key==false){
                    $news = Db::name('wd_xcx_products')->where("uniacid",$appletid)->where("type","showArt")->where("cid","in",$array1)->where('art_type', 1)->order('num desc, id desc')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"), 'cid'=>$cid)]);
                    $count = Db::name('wd_xcx_products')->where("uniacid",$appletid)->where("type","showArt")->where("cid","in",$array1)->where('art_type', 1)->order('num desc')->count();
                    $newnews = $news->toArray();
                    foreach ($newnews['data'] as &$res) {
                        $lanmu = Db::name('wd_xcx_cate')->where("id",$res['cid'])->find();
                        $res['lanmu'] = $lanmu['name'];
//                        $res['thumb'] = remote($appletid,$res['thumb'],1);
//                        var_dump($res['thumb']);
                        if($res['thumb']){
                            $res['thumb'] = remote($appletid,$res['thumb'],1);
                        }else{
                            $res['thumb']=remote($appletid,"/image/noimage.jpg",1);
                        }
                    }
                }else if($cid>0&&$key!=false){
                    $news = Db::name('wd_xcx_products')->where("uniacid",$appletid)->where("type","showArt")->where("cid","in",$array1)->where("title","like","%".$key."%")->where("art_type", 1)->order('num desc, id desc')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"), 'cid'=>$cid, 'key'=>$key)]);
                    $count = Db::name('wd_xcx_products')->where("uniacid",$appletid)->where("type","showArt")->where("cid","in",$array1)->where("title","like","%".$key."%")->where("art_type", 1)->order('num desc')->count();
                    $newnews = $news->toArray();
                    foreach ($newnews['data'] as &$res) {
                        $lanmu = Db::name('wd_xcx_cate')->where("id",$res['cid'])->find();
                        $res['lanmu'] = $lanmu['name'];
//                        $res['thumb'] = remote($appletid,$res['thumb'],1);
//                        var_dump($res['thumb']);
                        if($res['thumb']){
                            $res['thumb'] = remote($appletid,$res['thumb'],1);
                        }else{
                            $res['thumb']=remote($appletid,"/image/noimage.jpg",1);
                        }
                    }
                }else if($cid==0&&$key!=false){
                    $news = Db::name('wd_xcx_products')->where("uniacid",$appletid)->where("type","showArt")->where("title","like","%".$key."%")->where("art_type", 1)->order('num desc, id desc')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"), 'cid'=>$cid, 'key'=>$key)]);
                    $count = Db::name('wd_xcx_products')->where("uniacid",$appletid)->where("type","showArt")->where("title","like","%".$key."%")->where("art_type", 1)->order('num desc')->count();
                    $newnews = $news->toArray();
                    foreach ($newnews['data'] as &$res) {
                        $lanmu = Db::name('wd_xcx_cate')->where("id",$res['cid'])->find();
                        $res['lanmu'] = $lanmu['name'];
//                        $res['thumb'] = remote($appletid,$res['thumb'],1);
//                        var_dump($res['thumb']);
                        if($res['thumb']){
                            $res['thumb'] = remote($appletid,$res['thumb'],1);
                        }else{
                            $res['thumb']=remote($appletid,"/image/noimage.jpg",1);
                        }
                    }
                }
//                exit();
                $this->assign('newnews',$newnews['data']);
                $this->assign('news',$news);
                $this->assign('counts',$count);
                $this->assign('cid',$cid);
                $this->assign('key',$key);
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
    public function navs(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $list = Db::name('wd_xcx_art_nav')->where("uniacid",$appletid)->select();
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
            return $this->fetch('navs');
        }else{
            $this->redirect('Login/index');
        }
    }
    public function navsadd(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $newsid = input("newsid");
                if($newsid){
                   $item = Db::name('wd_xcx_art_nav')->where("uniacid",$appletid)->where("id",$newsid)->find();
                }else{
                    $item = "";
                }
                $this->assign('newsid',$newsid);
                $this->assign('list',$item);
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
            return $this->fetch('navsadd');
        }else{
            $this->redirect('Login/index');
        }
    }
    public function navssave(){
        $data = array();
        $data['uniacid'] = input("appletid");
        $newsid = input("newsid");
        $flag = input("flag");
        $data['flag'] = $flag;
        if(input('num')){
            $data['num'] = input('num');
        }else{
            $data['num'] = 0;
        }
        if(input('title')){
            $data['title'] = input('title');
        }else{
            $this->error("导航组标题不能为空");
            exit;
        }
        $info = Db::name("wd_xcx_art_nav")->where("id",$newsid)->find();
        if($info){
            $res = Db::name("wd_xcx_art_nav")->where("id",$newsid)->update($data);
        }else{
            $res = Db::name("wd_xcx_art_nav")->insert($data);
        }
        if($res){
            $this->success("导航组添加/修改成功",Url('News/navs').'?appletid='.$data['uniacid']);
        }else{
            $this->success("导航组添加/修改失败，没有修改项");
        }
    }
    public function nav(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $list = Db::name('wd_xcx_art_navlist')->where("uniacid",$appletid)->select();
                foreach ($list as $key => $value) {
                    $list[$key]['cname'] = Db::name("wd_xcx_art_nav")->where("uniacid",$appletid)->where("id",$value['cid'])->find()['title'];
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
            return $this->fetch('nav');
        }else{
            $this->redirect('Login/index');
        }
    }
    public function navadd(){
        
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $newsid = input("newsid");
                if($newsid){
                   $item = Db::name('wd_xcx_art_navlist')->where("uniacid",$appletid)->where("id",$newsid)->find();
                   if($item['bgcolor']){
                        $item['bgcolor'] =  $this->RGBToHex($item['bgcolor']);
                   }
                   if($item['textcolor']){
                        $item['textcolor'] =  $this->RGBToHex($item['textcolor']);
                   }
                }else{
                    $item = "";
                }
                $cate = Db::name('wd_xcx_art_nav')->where("uniacid",$appletid)->where("flag",1)->order("num desc")->select();
                $this->assign('cate',$cate);
                $this->assign('newsid',$newsid);
                $this->assign('list',$item);
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
            return $this->fetch('navadd');
        }else{
            $this->redirect('Login/index');
        }
    }
    public function navsave(){
        $data = array();
        $data['uniacid'] = input("appletid");
        $newsid = input("newsid");
        $flag = input("flag");
        $data['flag'] = $flag;
        if(input('num')){
            $data['num'] = input('num');
        }else{
            $data['num'] = 0;
        }
        if(input('cid')){
            $data['cid'] = input('cid');
        }else{
            $this->error("请选择导航组");
            exit;
        }
        if(input('title')){
            $data['title'] = input('title');
        }else{
            $this->error("导航标题不能为空");
            exit;
        }
        $data['type'] = intval(input('type'));
        if($data['type'] == 3){
            $data['url'] = "";
        }else{
            $data['url'] = input('url');
        }
        $data['bgcolor'] = $this->hex2rgb(input('bgcolor'));
        $data['textcolor'] = $this->hex2rgb(input('textcolor'));
        $info = Db::name("wd_xcx_art_navlist")->where("id",$newsid)->find();
        if($info){
            $res = Db::name("wd_xcx_art_navlist")->where("id",$newsid)->update($data);
        }else{
            $res = Db::name("wd_xcx_art_navlist")->insert($data);
        }
        if($res){
            $this->success("导航添加/修改成功",Url('News/nav').'?appletid='.$data['uniacid']);
        }else{
            $this->success("导航添加/修改失败，没有修改项");
        }
    }
    public function navsdel(){
        $id = input("newsid");
        $res = Db::name('wd_xcx_art_nav')->where("id",$id)->delete();
        if($res){
            $this->success('删除成功');
        }else{
            $this->success('删除失败');
        }
    }
    public function navdel(){
        $id = input("newsid");
        $res = Db::name('wd_xcx_art_navlist')->where("id",$id)->delete();
        if($res){
            $this->success('删除成功');
        }else{
            $this->success('删除失败');
        }
    }
    public function searchs(){
            $keys=input('keys');
            $uniacid = input("appletid");
            $pros = Db::query("SELECT title,id FROM {$this->prefix}wd_xcx_products WHERE uniacid = ".$uniacid." and type ='showArt' and title like '%".$keys."%' ORDER BY num DESC,id DESC");
            echo json_encode($pros);
            exit;
        
    }
    public function getnews(){
            $id=input('id');
            $uniacid = input("appletid");
            $pros = Db::name('wd_xcx_products')->where("id",$id)->where("uniacid",$uniacid)->where('type','showArt')->field('title,id')->find();
            echo json_encode($pros);
            exit;
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
                $navlist = Db::name('wd_xcx_art_nav')->where("uniacid",$appletid)->where("flag",1)->order('num desc')->select();
                $this->assign("navlist",$navlist);
                $listV = Db::name('wd_xcx_cate')->where("uniacid",$appletid)->where("cid",0)->where('type', 'showArt')->order('num desc')->select();
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
                $cates = Db::name('wd_xcx_multicate')->where("uniacid",$appletid)->where('statue',1)->where("type","showArt")->select();
                $multipros = array();
                $newsid = input("newsid");
                $newsinfo=array();
                $glnews=array();
                if($newsid){
                    //有新闻号时，先判断该新闻是不是属于该小程序！
                    $newsget = Db::name('wd_xcx_products')->where("id",$newsid)->where("type","showArt")->find();
//                    print($newsget['music_art_info']);exit;
                    if($newsget['music_art_info'] == ""){
                        $newsget['music_art_info']['musicTitle'] = "";
                        $newsget['music_art_info']['music'] = "";
                        $newsget['music_art_info']['music_price'] = "";
                        $newsget['music_art_info']['autoPlay'] = "";
                        $newsget['music_art_info']['loopPlay'] = "";
                        $newsget['music_art_info']['art_price'] = "";
                        $newsget['music_art_info']['musictype'] = "";
                    }else{
                        $newsget['music_art_info'] = unserialize($newsget['music_art_info']);
                    }
                    if(stristr($newsget['share_score'], 'http') || stristr($newsget['share_score'], 'page')){
                        $newsget['weburl'] = $newsget['share_score'];
                        $newsget['share_score'] = "";
                    }else{
                        $newsget['weburl'] = '';
                    }
                    // if($newsget['edittime']==0){
                    //     $newsget['edittime'] = date("Y-m-d H:i:s",time());
                    //     var_dump($newsget['edittime']);exit;
                    // }else{
                    //     $newsget['edittime'] = date("Y-m-d H:i:s",$newsget['edittime']);
                    // }
                    if($newsget['uniacid']==$appletid){
                        if($newsget['thumb']){
                            $newsget['thumb'] = remote($appletid,$newsget['thumb'],1);
                        }
                        if($newsget['shareimg']){
                            $newsget['shareimg'] = remote($appletid,$newsget['shareimg'],1);
                        }
                        if($newsget['labels']){
                            $newsget['labels'] = remote($appletid,$newsget['labels'],1);
                        }
                        if($newsget['glnews']!=""){
                            $news = unserialize($newsget['glnews']);
                            foreach($news as $k => $v){
                                $glnews[$k] = Db::name('wd_xcx_products')->where("id",$v)->where("uniacid",$appletid)->find();
                            }
                        }
                        $newsinfo = $newsget;
                        $sons_keys =   Db::name('wd_xcx_multicates')->where("id",'in',$newsinfo['top_catas'])->select();
                        foreach ($sons_keys as $k => $v){
                            $sons_keys[$k]['sons'] = Db::name('wd_xcx_multicates')->where("pid",$v['id'])->select();
                        }
                    }else{
                        $usergroup = Session::get('usergroup');
                        if($usergroup==1){
                            $this->error("找不到该内容，或者该内容不属于本小程序");
                        }
                        if($usergroup==2){
                            $this->error("找不到该内容，或者该内容不属于本小程序");
                        }
                    }
                }else{
                    $newsid=0;
                    $cate_arr="";
                    $multipro_arr="";
                    $sons_keys = "";
                    foreach ($cates as $k => $v) {
                        $cates[$k]['flag'] = 0;
                    }
                }
                $jieguo = Db::name('wd_xcx_formlist')->where("uniacid",$appletid) ->order('id desc')->select();
                $this->assign('glnews',$glnews);
                $this->assign('sons_keys',$sons_keys);
                $this->assign('forms',$jieguo);
                $this->assign('cates',$cates);
                $this->assign('newsid',$newsid);
                $this->assign('newsinfo',$newsinfo);
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
        $num = input('num');
        if($num){
            $data['num'] = $num;
        }
        //所属栏目
        $cid = input("cid");
        if($cid){
            $data['cid'] = $cid;
            $lanmu = Db::name('wd_xcx_cate')->where("id",$cid)->find();
            $data['type'] = $lanmu['type'];
            $data['lanmu'] = $lanmu['name'];
        }
        $pcid = Db::name('wd_xcx_cate')->where("id",$cid)->where("uniacid",input("appletid"))->field("cid")->find();
        if($pcid['cid'] == 0){
            $data['pcid'] = $cid;
        }else{
            $data['pcid'] = $pcid['cid'];
        }
        //推荐到横排
        $type_x = input("type_x");
        if($type_x){
            $data['type_x'] = (int)$type_x;
        }else{
            $data['type_x'] = 0;
        }
        //推荐到竖排
        $type_y = input("type_y");
        if($type_y){
            $data['type_y'] = (int)$type_y;
        }else{
            $data['type_y'] = 0;
        }
        if(!is_null(input('choose/a'))){
            $data['glnews'] = serialize(array_values(array_unique(input('choose/a'))));
        }else{
            $data['glnews']="";
        }
        //推荐到首页栏目
        $type_i = input("type_i");
        if($type_i){
            $data['type_i'] = (int)$type_i;
        }else{
            $data['type_i'] = 0;
        }
        //访问量
        $hits = input('hits');
        if($hits){
            $data['hits'] = $hits;
        }
        //付费
        $art_price=input('art_price');
        if($art_price){
        }
        //标题
        $title = input('title');
        if($title){
            $data['title'] = $title;
        }
        //缩略图
        $thumb = input("commonuploadpic1");
        if($thumb){
            $data['thumb'] = remote($data['uniacid'],$thumb,2);
        }
        //分享
        $shareimg = input("commonuploadpic2");
        if($shareimg){
            $data['shareimg'] = remote($data['uniacid'],$shareimg,2);
        }
        //简介
        $desc = $_POST['desc'];
        if($desc){
            $data['desc'] = $desc;
        }
        $edittime = strtotime(input('edittime'));
        if($edittime==0){
            $data['edittime'] = time();
        }else{
            $data['edittime'] = $edittime;
        }
        //视频地址
        $video = input('video');
        $data['video'] = $video;
        //视频封面
        $labels = input("commonuploadpic3");
        if($labels){
            $data['labels'] = remote($data['uniacid'],$labels,2);
        }else{
            if($thumb){
                $data['labels'] = $thumb;
            }
        }
        $price = input('price');
        if($price){
            $data['price'] = $price;
        }else{
            $data['price'] = 0;
        }
        $market_price = input('market_price');
        if($market_price){
            $data['market_price'] = $market_price;
        }else{
            $data['market_price'] = "false";
        }
        //文章详情
        $text = input('text');
        if($text){
            $data['text'] = htmlspecialchars_decode($text);
        }
        //表单配置
        $formset = input('formset');
        if(isset($formset)){
            $data['formset'] = $formset;
        }
        // $data['pro_flag'] = input("pro_flag");
        $data['pro_flag'] = 0;
        
        $comment = input('comment');
        $data['comment'] = $comment;

     //分销设置
        $fx_uni = input('fx_uni');
        if($fx_uni == null){
            $data['fx_uni'] = 2;
        }else{
            $data['fx_uni'] = input('fx_uni');
        }
        $commission_type = input('commission_type');
        if($commission_type == null){
            $data['commission_type'] = 1;
        }else{
            $data['commission_type'] = $commission_type;
        }
        $data['commission_one'] = input('commission_one');
        $data['commission_two'] = input('commission_two');
        $data['commission_three'] = input('commission_three');

        // $share_gz = input('share_gz');
        // $data['share_gz'] = $share_gz;
        // $share_type = input('share_type');
        // $data['share_type'] = $share_type;
        // $share_score = input('share_score');
        // $data['share_score'] = $share_score;
        // $share_num = input('share_num');
        // $data['share_num'] = $share_num;
        $newsid = input("newsid");
        $top_catas = Db::name('wd_xcx_multicate')->where("id",input('mulitcataid'))->find();
        $data['sons_catas'] = input('sons/a')?implode(',',input('sons/a')):'';
        $data['top_catas'] = $top_catas['top_catas']?implode(',',unserialize($top_catas['top_catas'])):'';
        $data['mulitcataid'] = input('mulitcataid');
        $muiltcate = input("muiltcate");
        if($muiltcate!= "0"){
            $data['multi'] = 1;
        }else{
           $data['multi'] = 0; 
        }
        $data["get_share_gz"] = input('get_share_gz');
        // $data["get_share_gz"] = 2;
        $data["get_share_score"] = input('get_share_score');
        // $data["get_share_score"] = 0;
        $data["get_share_num"] = input('get_share_num');
        // $data["get_share_num"] = 0;
        $music_art_info = array(
            "musicTitle" =>input('musicTitle'),
            "art_price" => input('art_price'),
            "music" => input('music'),
            "music_price" => input('music_price'),
            "autoPlay" => input('autoPlay'),
            "loopPlay" => input('loopPlay'),
            "musictype" =>input('musictype')
        );
        $data['music_art_info'] = serialize($music_art_info);
        if(stristr(input('weburl'), 'http') || stristr(input('weburl'), 'page')){
            $page_type = input('weburl');
        }else{
            $page_type = input('share_score');
        }
        $data['share_score'] = $page_type;

        $data['art_type'] = 1;
        if($newsid){
            $data['etime'] = time();
            $res = Db::name('wd_xcx_products')->where("id",$newsid)->update($data);
        }else{
            $data['ctime'] = time();
            $res = Db::name('wd_xcx_products')->insert($data);
        }
        if($res){
           $this->success('文章添加/编辑成功！',Url('News/index').'?appletid='.$data['uniacid']);
        }else{
          $this->error('文章添加/编辑失败，没有修改项！');
          exit;
        }
    }







    public function hex2rgb($color){
        $color = str_replace('#', '', $color);
        if (strlen($color) > 3) {
            $title_bg = hexdec(substr($color, 0, 2)).",".hexdec(substr($color, 2, 2)).",".hexdec(substr($color, 4, 2));
        } else {
            $color = $color;
            $r = substr($color, 0, 1) . substr($color, 0, 1);
            $g = substr($color, 1, 1) . substr($color, 1, 1);
            $b = substr($color, 2, 1) . substr($color, 2, 1);
            $title_bg = hexdec($r).",".hexdec($g).",".hexdec($b);
        }
        return $title_bg;
    }
    public function RGBToHex($color){
        $color = "rgb(".$color.")";
        $regexp = "/^rgb\(([0-9]{0,3})\,\s*([0-9]{0,3})\,\s*([0-9]{0,3})\)/";
        $re = preg_match($regexp, $color, $match);
        $re = array_shift($match);
        $hexColor = "#";
        $hex = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F');
        for ($i = 0; $i < 3; $i++) {
            $r = null;
            $c = $match[$i];
            $hexAr = array();
            while ($c > 16) {
                $r = $c % 16;
                $c = ($c / 16) >> 0;
                array_push($hexAr, $hex[$r]);
            }
            array_push($hexAr, $hex[$c]);
            $ret = array_reverse($hexAr);
            $item = implode('', $ret);
            $item = str_pad($item, 2, '0', STR_PAD_LEFT);
            $hexColor .= $item;
        }
        return $hexColor;
    }
    // 删除操作
    public function del(){
        $data['id'] = input("newsid");
        $res = Db::name('wd_xcx_products')->where($data)->delete();
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
    //富文本图片上传
    public function imgupload(){
        $files = request()->file('');  
        foreach($files as $file){        
            // 移动到框架应用根目录/public/upimages/ 目录下        
            $info = $file->validate(['ext'=>'jpg,png,gif,jpeg'])->move(ROOT_PATH . 'public' . DS . 'upimages');
            if($info){
                $url =  ROOT_HOST."/upimages/".date("Ymd",time())."/".$info->getFilename();
                $arr = array("url"=>$url);
                return json_encode($arr);
            }else{
                // 上传失败获取错误信息
                return $this->error($file->getError()) ;
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
                $array1=input('news');
                $arr=explode(',',$array1);


                    $res = Db::name('wd_xcx_products')->where("uniacid",$appletid)->where('id',"in",$arr)->delete();
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
    //navs批量删除操作
    public function navsdelall(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $array1=input('navs');
                $arr=explode(',',$array1);


                $res = Db::name('wd_xcx_art_nav')->where("uniacid",$appletid)->where('id',"in",$arr)->delete();
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

    //navs批量删除操作
    public function navdelall(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $array1=input('nav');
                $arr=explode(',',$array1);


                $res = Db::name('wd_xcx_art_navlist')->where("uniacid",$appletid)->where('id',"in",$arr)->delete();
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


    //付费视频
    public function video(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $cid=input("cid")?input("cid"):0;
                $key=input("key");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $listV = Db::name('wd_xcx_cate')->where("uniacid",$appletid)->where('type','showArt')->where("cid",0)->order('num desc')->select();
                foreach($listV as $k=>&$val) {
                    $listS = Db::name('wd_xcx_cate')->where("uniacid",$appletid)->where("cid",$val['id'])->order('num desc')->select();
                    //子集数据量
                    $val['data'] = $listS;
                }
                $this->assign('cate',$listV);
                $this->assign('applet',$res);
                $listallcate = Db::name('wd_xcx_cate')->where("uniacid",$appletid)->where("cid",$cid)->select();
                $array1=array();
                for($a=0;$a<count($listallcate);$a++){
                    array_push($array1,$listallcate[$a]['id']);
                }
                array_push($array1,$cid);
                if($cid==0&&$key==false){
                    $news = Db::name('wd_xcx_products')->where("uniacid",$appletid)->where("type","showArt")->order('num desc')->where('art_type', 2)->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                    $count = Db::name('wd_xcx_products')->where("uniacid",$appletid)->where("type","showArt")->where('art_type', 2)->order('num desc')->count();
                    $newnews = $news->toArray();
                    foreach ($newnews['data'] as &$res) {
                        $lanmu = Db::name('wd_xcx_cate')->where("id",$res['cid'])->find();
                        $res['lanmu'] = $lanmu['name'];
                        if($res['thumb']){
                            $res['thumb'] = remote($appletid,$res['thumb'],1);
                        }else{
                            $res['thumb']=remote($appletid,"/image/noimage.jpg",1);
                        }
                    }
                }else if($cid>0&&$key==false){
                    $news = Db::name('wd_xcx_products')->where("uniacid",$appletid)->where("type","showArt")->where("cid","in",$array1)->where('art_type', 2)->order('num desc')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                    $count = Db::name('wd_xcx_products')->where("uniacid",$appletid)->where("type","showArt")->where("cid","in",$array1)->where('art_type', 2)->order('num desc')->count();
                    $newnews = $news->toArray();
                    foreach ($newnews['data'] as &$res) {
                        $lanmu = Db::name('wd_xcx_cate')->where("id",$res['cid'])->find();
                        $res['lanmu'] = $lanmu['name'];
                        if($res['thumb']){
                            $res['thumb'] = remote($appletid,$res['thumb'],1);
                        }else{
                            $res['thumb']=remote($appletid,"/image/noimage.jpg",1);
                        }
                    }
                }else if($cid>0&&$key!=false){
                    $news = Db::name('wd_xcx_products')->where("uniacid",$appletid)->where("type","showArt")->where("cid","in",$array1)->where("title","like","%".$key."%")->where('art_type', 2)->order('num desc')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                    $count = Db::name('wd_xcx_products')->where("uniacid",$appletid)->where("type","showArt")->where("cid","in",$array1)->where("title","like","%".$key."%")->where('art_type', 2)->order('num desc')->count();
                    $newnews = $news->toArray();
                    foreach ($newnews['data'] as &$res) {
                        $lanmu = Db::name('wd_xcx_cate')->where("id",$res['cid'])->find();
                        $res['lanmu'] = $lanmu['name'];
                        if($res['thumb']){
                            $res['thumb'] = remote($appletid,$res['thumb'],1);
                        }else{
                            $res['thumb']=remote($appletid,"/image/noimage.jpg",1);
                        }
                    }
                }else if($cid==0&&$key!=false){
                    $news = Db::name('wd_xcx_products')->where("uniacid",$appletid)->where("type","showArt")->where("title","like","%".$key."%")->where('art_type', 2)->order('num desc')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                    $count = Db::name('wd_xcx_products')->where("uniacid",$appletid)->where("type","showArt")->where("title","like","%".$key."%")->where('art_type', 2)->order('num desc')->count();
                    $newnews = $news->toArray();
                    foreach ($newnews['data'] as &$res) {
                        $lanmu = Db::name('wd_xcx_cate')->where("id",$res['cid'])->find();
                        $res['lanmu'] = $lanmu['name'];
                        if($res['thumb']){
                            $res['thumb'] = remote($appletid,$res['thumb'],1);
                        }else{
                            $res['thumb']=remote($appletid,"/image/noimage.jpg",1);
                        }
                    }
                }
                $this->assign('newnews',$newnews['data']);
                $this->assign('news',$news);
                $this->assign('counts',$count);
                $this->assign('cid',$cid);
                $this->assign('key',$key);
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
            return $this->fetch('video');
        }else{
            $this->redirect('Login/index');
        }
    }
    public function videoadd(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $navlist = Db::name('wd_xcx_art_nav')->where("uniacid",$appletid)->where("flag",1)->order('num desc')->select();
                $this->assign("navlist",$navlist);
                $listV = Db::name('wd_xcx_cate')->where("uniacid",$appletid)->where("cid",0)->where('type', 'showArt')->order('num desc')->select();
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
                $cates = Db::name('wd_xcx_multicate')->where("uniacid",$appletid)->where('statue',1)->where("type","showArt")->select();
                $multipros = array();
                $newsid = input("newsid");
                $newsinfo=array();
                $glnews=array();
                if($newsid){
                    //有新闻号时，先判断该新闻是不是属于该小程序！
                    $newsget = Db::name('wd_xcx_products')->where("id",$newsid)->where("type","showArt")->find();
                    if($newsget['music_art_info'] == ""){
                        $newsget['music_art_info']['musicTitle'] = "";
                        $newsget['music_art_info']['music'] = "";
                        $newsget['music_art_info']['music_price'] = "";
                        $newsget['music_art_info']['autoPlay'] = "";
                        $newsget['music_art_info']['loopPlay'] = "";
                        $newsget['music_art_info']['art_price'] = "";
                        $newsget['music_art_info']['musictype'] = "";
                    }else{
                        $newsget['music_art_info'] = unserialize($newsget['music_art_info']);
                    }
                    if(stristr($newsget['share_score'], 'http') || stristr($newsget['share_score'], 'sudu8_page')){
                        $newsget['weburl'] = $newsget['share_score'];
                        $newsget['share_score'] = "";
                    }else{
                        $newsget['weburl'] = '';
                    }
                    if($newsget['uniacid']==$appletid){
                        if($newsget['thumb']){
                            $newsget['thumb'] = remote($appletid,$newsget['thumb'],1);
                        }
                        if($newsget['shareimg']){
                            $newsget['shareimg'] = remote($appletid,$newsget['shareimg'],1);
                        }
                        if($newsget['labels']){
                            $newsget['labels'] = remote($appletid,$newsget['labels'],1);
                        }
                        if($newsget['glnews']!=""){
                            $news = unserialize($newsget['glnews']);
                            foreach($news as $k => $v){
                                $glnews[$k] = Db::name('wd_xcx_products')->where("id",$v)->where("uniacid",$appletid)->find();
                            }
                        }
                        $newsinfo = $newsget;
                        $sons_keys =   Db::name('wd_xcx_multicates')->where("id",'in',$newsinfo['top_catas'])->select();
                        foreach ($sons_keys as $k => $v){
                            $sons_keys[$k]['sons'] = Db::name('wd_xcx_multicates')->where("pid",$v['id'])->select();
                        }
                    }else{
                        $usergroup = Session::get('usergroup');
                        if($usergroup==1){
                            $this->error("找不到该内容，或者该内容不属于本小程序");
                        }
                        if($usergroup==2){
                            $this->error("找不到该内容，或者该内容不属于本小程序");
                        }
                    }
                }else{
                    $newsid=0;
                    $cate_arr="";
                    $multipro_arr="";
                    $sons_keys = "";
                    foreach ($cates as $k => $v) {
                        $cates[$k]['flag'] = 0;
                    }
                }
                $jieguo = Db::name('wd_xcx_formlist')->where("uniacid",$appletid)->select();
                $this->assign('glnews',$glnews);
                $this->assign('sons_keys',$sons_keys);
                $this->assign('forms',$jieguo);
                $this->assign('cates',$cates);
                $this->assign('newsid',$newsid);
                $this->assign('newsinfo',$newsinfo);
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
            return $this->fetch('videoadd');
        }else{
            $this->redirect('Login/index');
        }
    }

    public function videosave(){
        $data = array();
        //小程序ID
        $data['uniacid'] = input("appletid");
        //排序
        $num = input('num');
        if($num){
            $data['num'] = $num;
        }
        //所属栏目
        $cid = input("cid");
        if($cid){
            $data['cid'] = $cid;
            $lanmu = Db::name('wd_xcx_cate')->where("id",$cid)->find();
            $data['type'] = $lanmu['type'];
            $data['lanmu'] = $lanmu['name'];
        }
        $pcid = Db::name('wd_xcx_cate')->where("id",$cid)->where("uniacid",input("appletid"))->field("cid")->find();
        if($pcid['cid'] == 0){
            $data['pcid'] = $cid;
        }else{
            $data['pcid'] = $pcid['cid'];
        }
        //推荐到横排
        $type_x = input("type_x");
        if($type_x){
            $data['type_x'] = (int)$type_x;
        }else{
            $data['type_x'] = 0;
        }
        //推荐到竖排
        $type_y = input("type_y");
        if($type_y){
            $data['type_y'] = (int)$type_y;
        }else{
            $data['type_y'] = 0;
        }
        if(!is_null(input('choose/a'))){
            $data['glnews'] = serialize(array_values(array_unique(input('choose/a'))));
        }else{
            $data['glnews']="";
        }
        //推荐到首页栏目
        $type_i = input("type_i");
        if($type_i){
            $data['type_i'] = (int)$type_i;
        }else{
            $data['type_i'] = 0;
        }
        //访问量
        $hits = input('hits');
        if($hits){
            $data['hits'] = $hits;
        }
        //付费
        $art_price=input('art_price');
        if($art_price){
        }
        //标题
        $title = input('title');
        if($title){
            $data['title'] = $title;
        }
        //缩略图
        $thumb = input("commonuploadpic1");
        if($thumb){
            $data['thumb'] = remote($data['uniacid'],$thumb,2);
        }
        //分享
        $shareimg = input("commonuploadpic2");
        if($shareimg){
            $data['shareimg'] = remote($data['uniacid'],$shareimg,2);
        }
        //简介
        $desc = $_POST['desc'];
        if($desc){
            $data['desc'] = $desc;
        }
        $edittime = strtotime(input('edittime'));
        if($edittime==0){
            $data['edittime'] = time();
        }else{
            $data['edittime'] = $edittime;
        }
        //视频地址
        $video = input('video');
        $data['video'] = $video;
        //视频封面
        $labels = input("commonuploadpic3");
        if($labels){
            $data['labels'] = remote($data['uniacid'],$labels,2);
        }else{
            if($thumb){
                $data['labels'] = $thumb;
            }
        }
        $price = input('price');
        if($price){
            $data['price'] = $price;
        }else{
            $data['price'] = 0;
        }
        $market_price = input('market_price');
        if($market_price){
            $data['market_price'] = $market_price;
        }else{
            $data['market_price'] = "false";
        }
        //文章详情
        $text = input('text');
        if($text){
            $data['text'] = $text;
        }
        //表单配置
        $formset = input('formset');
        if(isset($formset)){
            $data['formset'] = $formset;
        }
        // $data['pro_flag'] = input("pro_flag");
        $data['pro_flag'] = 0;
        
        $comment = input('comment');
        $data['comment'] = $comment;

     //分销设置
        $fx_uni = input('fx_uni');
        if($fx_uni == null){
            $data['fx_uni'] = 2;
        }else{
            $data['fx_uni'] = input('fx_uni');
        }
        $commission_type = input('commission_type');
        if($commission_type == null){
            $data['commission_type'] = 1;
        }else{
            $data['commission_type'] = $commission_type;
        }
        $data['commission_one'] = input('commission_one');
        $data['commission_two'] = input('commission_two');
        $data['commission_three'] = input('commission_three');

        // $share_gz = input('share_gz');
        // $data['share_gz'] = $share_gz;
        // $share_type = input('share_type');
        // $data['share_type'] = $share_type;
        // $share_score = input('share_score');
        // $data['share_score'] = $share_score;
        // $share_num = input('share_num');
        // $data['share_num'] = $share_num;
        $newsid = input("newsid");
        $top_catas = Db::name('wd_xcx_multicate')->where("id",input('mulitcataid'))->find();
        $data['sons_catas'] = input('sons/a')?implode(',',input('sons/a')):'';
        $data['top_catas'] = $top_catas['top_catas']?implode(',',unserialize($top_catas['top_catas'])):'';
        $data['mulitcataid'] = input('mulitcataid');
        $muiltcate = input("muiltcate");
        if($muiltcate!= "0"){
            $data['multi'] = 1;
        }else{
           $data['multi'] = 0; 
        }
        $data["get_share_gz"] = input('get_share_gz');
        // $data["get_share_gz"] = 2;
        $data["get_share_score"] = input('get_share_score');
        // $data["get_share_score"] = 0;
        $data["get_share_num"] = input('get_share_num');
        // $data["get_share_num"] = 0;
        $music_art_info = array(
            "musicTitle" =>input('musicTitle'),
            "art_price" => input('art_price'),
            "music" => input('music'),
            "music_price" => input('music_price'),
            "autoPlay" => input('autoPlay'),
            "loopPlay" => input('loopPlay'),
            "musictype" =>input('musictype')
        );
        $data['music_art_info'] = serialize($music_art_info);
        if(stristr(input('weburl'), 'http') || stristr(input('weburl'), 'page')){
            $page_type = input('weburl');
        }else{
            $page_type = input('share_score');
        }
        $data['share_score'] = $page_type;

        $data['art_type'] = 2;
        if($newsid){
            $data['etime'] = time();
            $res = Db::name('wd_xcx_products')->where("id",$newsid)->update($data);
        }else{
            $data['ctime'] = time();
            $res = Db::name('wd_xcx_products')->insert($data);
        }
        if($res){
           $this->success('基础信息更新成功！',Url('News/video').'?appletid='.$data['uniacid']);
        }else{
          $this->error('基础信息更新失败，没有修改项！');
          exit;
        }
    }
    //批量删除操作
    public function videodelall(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $array1=input('news');
                $arr=explode(',',$array1);

                $res = Db::name('wd_xcx_products')->where("uniacid",$appletid)->where('id',"in",$arr)->delete();
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
            return $this->fetch('video');
        }else{
            $this->redirect('Login/index');
        }
    }

    //付费音频
    public function audio(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $cid=input("cid")?input("cid"):0;
                $key=input("key");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $listV = Db::name('wd_xcx_cate')->where("uniacid",$appletid)->where('type','showArt')->where("cid",0)->order('num desc')->select();
                foreach($listV as $k=>&$val) {
                    $listS = Db::name('wd_xcx_cate')->where("uniacid",$appletid)->where("cid",$val['id'])->order('num desc')->select();
                    //子集数据量
                    $val['data'] = $listS;
                }
                $this->assign('cate',$listV);
                $this->assign('applet',$res);
                $listallcate = Db::name('wd_xcx_cate')->where("uniacid",$appletid)->where("cid",$cid)->select();
                $array1=array();
                for($a=0;$a<count($listallcate);$a++){
                    array_push($array1,$listallcate[$a]['id']);
                }
                array_push($array1,$cid);
                if($cid==0&&$key==false){
                    $news = Db::name('wd_xcx_products')->where("uniacid",$appletid)->where("type","showArt")->order('num desc')->where('art_type', 3)->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                    $count = Db::name('wd_xcx_products')->where("uniacid",$appletid)->where("type","showArt")->where('art_type', 3)->order('num desc')->count();
                    $newnews = $news->toArray();
                    foreach ($newnews['data'] as &$res) {
                        $lanmu = Db::name('wd_xcx_cate')->where("id",$res['cid'])->find();
                        $res['lanmu'] = $lanmu['name'];
                        if($res['thumb']){
                            $res['thumb'] = remote($appletid,$res['thumb'],1);
                        }else{
                            $res['thumb']=remote($appletid,"/image/noimage.jpg",1);
                        }
                    }
                }else if($cid>0&&$key==false){
                    $news = Db::name('wd_xcx_products')->where("uniacid",$appletid)->where("type","showArt")->where("cid","in",$array1)->where('art_type', 3)->order('num desc')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                    $count = Db::name('wd_xcx_products')->where("uniacid",$appletid)->where("type","showArt")->where("cid","in",$array1)->where('art_type', 3)->order('num desc')->count();
                    $newnews = $news->toArray();
                    foreach ($newnews['data'] as &$res) {
                        $lanmu = Db::name('wd_xcx_cate')->where("id",$res['cid'])->find();
                        $res['lanmu'] = $lanmu['name'];
                        if($res['thumb']){
                            $res['thumb'] = remote($appletid,$res['thumb'],1);
                        }else{
                            $res['thumb']=remote($appletid,"/image/noimage.jpg",1);
                        }
                    }
                }else if($cid>0&&$key!=false){
                    $news = Db::name('wd_xcx_products')->where("uniacid",$appletid)->where("type","showArt")->where("cid","in",$array1)->where("title","like","%".$key."%")->where('art_type', 3)->order('num desc')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                    $count = Db::name('wd_xcx_products')->where("uniacid",$appletid)->where("type","showArt")->where("cid","in",$array1)->where("title","like","%".$key."%")->where('art_type', 3)->order('num desc')->count();
                    $newnews = $news->toArray();
                    foreach ($newnews['data'] as &$res) {
                        $lanmu = Db::name('wd_xcx_cate')->where("id",$res['cid'])->find();
                        $res['lanmu'] = $lanmu['name'];
                        if($res['thumb']){
                            $res['thumb'] = remote($appletid,$res['thumb'],1);
                        }else{
                            $res['thumb']=remote($appletid,"/image/noimage.jpg",1);
                        }
                    }
                }else if($cid==0&&$key!=false){
                    $news = Db::name('wd_xcx_products')->where("uniacid",$appletid)->where("type","showArt")->where("title","like","%".$key."%")->where('art_type', 3)->order('num desc')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                    $count = Db::name('wd_xcx_products')->where("uniacid",$appletid)->where("type","showArt")->where("title","like","%".$key."%")->where('art_type', 3)->order('num desc')->count();
                    $newnews = $news->toArray();
                    foreach ($newnews['data'] as &$res) {
                        $lanmu = Db::name('wd_xcx_cate')->where("id",$res['cid'])->find();
                        $res['lanmu'] = $lanmu['name'];
                        if($res['thumb']){
                            $res['thumb'] = remote($appletid,$res['thumb'],1);
                        }else{
                            $res['thumb']=remote($appletid,"/image/noimage.jpg",1);
                        }
                    }
                }
                $this->assign('newnews',$newnews['data']);
                $this->assign('news',$news);
                $this->assign('counts',$count);
                $this->assign('cid',$cid);
                $this->assign('key',$key);
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
            return $this->fetch('audio');
        }else{
            $this->redirect('Login/index');
        }
    }
    public function audioadd(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $navlist = Db::name('wd_xcx_art_nav')->where("uniacid",$appletid)->where("flag",1)->order('num desc')->select();
                $this->assign("navlist",$navlist);
                $listV = Db::name('wd_xcx_cate')->where("uniacid",$appletid)->where("cid",0)->where('type', 'showArt')->order('num desc')->select();
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
                $cates = Db::name('wd_xcx_multicate')->where("uniacid",$appletid)->where('statue',1)->where("type","showArt")->select();
                $multipros = array();
                $newsid = input("newsid");
                $newsinfo=array();
                $glnews=array();
                if($newsid){
                    //有新闻号时，先判断该新闻是不是属于该小程序！
                    $newsget = Db::name('wd_xcx_products')->where("id",$newsid)->where("type","showArt")->find();
                    if($newsget['music_art_info'] == ""){
                        $newsget['music_art_info']['musicTitle'] = "";
                        $newsget['music_art_info']['music'] = "";
                        $newsget['music_art_info']['music_price'] = "";
                        $newsget['music_art_info']['autoPlay'] = "";
                        $newsget['music_art_info']['loopPlay'] = "";
                        $newsget['music_art_info']['art_price'] = "";
                        $newsget['music_art_info']['musictype'] = "";
                    }else{
                        $newsget['music_art_info'] = unserialize($newsget['music_art_info']);
                    }
                    if(stristr($newsget['share_score'], 'http') || stristr($newsget['share_score'], 'sudu8_page')){
                        $newsget['weburl'] = $newsget['share_score'];
                        $newsget['share_score'] = "";
                    }else{
                        $newsget['weburl'] = '';
                    }
                    if($newsget['uniacid']==$appletid){
                        if($newsget['thumb']){
                            $newsget['thumb'] = remote($appletid,$newsget['thumb'],1);
                        }
                        if($newsget['shareimg']){
                            $newsget['shareimg'] = remote($appletid,$newsget['shareimg'],1);
                        }
                        if($newsget['labels']){
                            $newsget['labels'] = remote($appletid,$newsget['labels'],1);
                        }
                        if($newsget['glnews']!=""){
                            $news = unserialize($newsget['glnews']);
                            foreach($news as $k => $v){
                                $glnews[$k] = Db::name('wd_xcx_products')->where("id",$v)->where("uniacid",$appletid)->find();
                            }
                        }
                        $newsinfo = $newsget;
                        $sons_keys =   Db::name('wd_xcx_multicates')->where("id",'in',$newsinfo['top_catas'])->select();
                        foreach ($sons_keys as $k => $v){
                            $sons_keys[$k]['sons'] = Db::name('wd_xcx_multicates')->where("pid",$v['id'])->select();
                        }
                    }else{
                        $usergroup = Session::get('usergroup');
                        if($usergroup==1){
                            $this->error("找不到该内容，或者该内容不属于本小程序");
                        }
                        if($usergroup==2){
                            $this->error("找不到该内容，或者该内容不属于本小程序");
                        }
                    }
                }else{
                    $newsid=0;
                    $cate_arr="";
                    $multipro_arr="";
                    $sons_keys = "";
                    foreach ($cates as $k => $v) {
                        $cates[$k]['flag'] = 0;
                    }
                }
                $jieguo = Db::name('wd_xcx_formlist')->where("uniacid",$appletid)->select();
                $this->assign('glnews',$glnews);
                $this->assign('sons_keys',$sons_keys);
                $this->assign('forms',$jieguo);
                $this->assign('cates',$cates);
                $this->assign('newsid',$newsid);
                $this->assign('newsinfo',$newsinfo);
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
            return $this->fetch('audioadd');
        }else{
            $this->redirect('Login/index');
        }
    }

    public function audiosave(){
        $data = array();
        //小程序ID
        $data['uniacid'] = input("appletid");
        //排序
        $num = input('num');
        if($num){
            $data['num'] = $num;
        }
        //所属栏目
        $cid = input("cid");
        if($cid){
            $data['cid'] = $cid;
            $lanmu = Db::name('wd_xcx_cate')->where("id",$cid)->find();
            $data['type'] = $lanmu['type'];
            $data['lanmu'] = $lanmu['name'];
        }
        $pcid = Db::name('wd_xcx_cate')->where("id",$cid)->where("uniacid",input("appletid"))->field("cid")->find();
        if($pcid['cid'] == 0){
            $data['pcid'] = $cid;
        }else{
            $data['pcid'] = $pcid['cid'];
        }
        //推荐到横排
        $type_x = input("type_x");
        if($type_x){
            $data['type_x'] = (int)$type_x;
        }else{
            $data['type_x'] = 0;
        }
        //推荐到竖排
        $type_y = input("type_y");
        if($type_y){
            $data['type_y'] = (int)$type_y;
        }else{
            $data['type_y'] = 0;
        }
        if(!is_null(input('choose/a'))){
            $data['glnews'] = serialize(array_values(array_unique(input('choose/a'))));
        }else{
            $data['glnews']="";
        }
        //推荐到首页栏目
        $type_i = input("type_i");
        if($type_i){
            $data['type_i'] = (int)$type_i;
        }else{
            $data['type_i'] = 0;
        }
        //访问量
        $hits = input('hits');
        if($hits){
            $data['hits'] = $hits;
        }
        //付费
        $art_price=input('art_price');
        if($art_price){
        }
        //标题
        $title = input('title');
        if($title){
            $data['title'] = $title;
        }
        //缩略图
        $thumb = input("commonuploadpic1");
        if($thumb){
            $data['thumb'] = remote($data['uniacid'],$thumb,2);
        }
        //分享
        $shareimg = input("commonuploadpic2");
        if($shareimg){
            $data['shareimg'] = remote($data['uniacid'],$shareimg,2);
        }
        //简介
        $desc = $_POST['desc'];
        if($desc){
            $data['desc'] = $desc;
        }
        $edittime = strtotime(input('edittime'));
        if($edittime==0){
            $data['edittime'] = time();
        }else{
            $data['edittime'] = $edittime;
        }
        //视频地址
        $video = input('video');
        $data['video'] = $video;
        //视频封面
        $labels = input("commonuploadpic3");
        if($labels){
            $data['labels'] = remote($data['uniacid'],$labels,2);
        }else{
            if($thumb){
                $data['labels'] = $thumb;
            }
        }
        $price = input('price');
        if($price){
            $data['price'] = $price;
        }else{
            $data['price'] = 0;
        }
        $market_price = input('market_price');
        if($market_price){
            $data['market_price'] = $market_price;
        }else{
            $data['market_price'] = "false";
        }
        //文章详情
        $text = input('text');
        if($text){
            $data['text'] = $text;
        }
        //表单配置
        $formset = input('formset');
        if(isset($formset)){
            $data['formset'] = $formset;
        }
        // $data['pro_flag'] = input("pro_flag");
        $data['pro_flag'] = 0;
        
        $comment = input('comment');
        $data['comment'] = $comment;

     //分销设置
        $fx_uni = input('fx_uni');
        if($fx_uni == null){
            $data['fx_uni'] = 2;
        }else{
            $data['fx_uni'] = input('fx_uni');
        }
        $commission_type = input('commission_type');
        if($commission_type == null){
            $data['commission_type'] = 1;
        }else{
            $data['commission_type'] = $commission_type;
        }
        $data['commission_one'] = input('commission_one');
        $data['commission_two'] = input('commission_two');
        $data['commission_three'] = input('commission_three');

        // $share_gz = input('share_gz');
        // $data['share_gz'] = $share_gz;
        // $share_type = input('share_type');
        // $data['share_type'] = $share_type;
        // $share_score = input('share_score');
        // $data['share_score'] = $share_score;
        // $share_num = input('share_num');
        // $data['share_num'] = $share_num;
        $newsid = input("newsid");
        $top_catas = Db::name('wd_xcx_multicate')->where("id",input('mulitcataid'))->find();
        $data['sons_catas'] = input('sons/a')?implode(',',input('sons/a')):'';
        $data['top_catas'] = $top_catas['top_catas']?implode(',',unserialize($top_catas['top_catas'])):'';
        $data['mulitcataid'] = input('mulitcataid');
        $muiltcate = input("muiltcate");
        if($muiltcate!= "0"){
            $data['multi'] = 1;
        }else{
           $data['multi'] = 0; 
        }
        $data["get_share_gz"] = input('get_share_gz');
        // $data["get_share_gz"] = 2;
        $data["get_share_score"] = input('get_share_score');
        // $data["get_share_score"] = 0;
        $data["get_share_num"] = input('get_share_num');
        // $data["get_share_num"] = 0;
        $music_art_info = array(
            "musicTitle" =>input('musicTitle'),
            "art_price" => input('art_price'),
            "music" => input('music'),
            "music_price" => input('music_price'),
            "autoPlay" => input('autoPlay'),
            "loopPlay" => input('loopPlay'),
            "musictype" =>input('musictype')
        );
        $data['music_art_info'] = serialize($music_art_info);
        if(stristr(input('weburl'), 'http') || stristr(input('weburl'), 'sudu8_page')){
            $page_type = input('weburl');
        }else{
            $page_type = input('share_score');
        }
        $data['share_score'] = $page_type;

        $data['art_type'] = 3;
        if($newsid){
            $data['etime'] = time();
            $res = Db::name('wd_xcx_products')->where("id",$newsid)->update($data);
        }else{
            $data['ctime'] = time();
            $res = Db::name('wd_xcx_products')->insert($data);
        }
        if($res){
           $this->success('基础信息更新成功！',Url('News/audio').'?appletid='.$data['uniacid']);
        }else{
          $this->error('基础信息更新失败，没有修改项！');
          exit;
        }
    }
    //批量删除操作
    public function audiodelall(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $array1=input('news');
                $arr=explode(',',$array1);

                $res = Db::name('wd_xcx_products')->where("uniacid",$appletid)->where('id',"in",$arr)->delete();
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
            return $this->fetch('audio');
        }else{
            $this->redirect('Login/index');
        }
    }
}