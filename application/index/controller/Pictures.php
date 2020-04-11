<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;
class Pictures extends Base
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
                $listV2 = Db::name('wd_xcx_cate')->where("uniacid",$appletid)->where("cid",0)->where("type", "showPic")->order('num desc,id desc')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                $count = Db::name('wd_xcx_cate')->where("uniacid",$appletid)->where("cid",0)->where("type", "showPic")->count();
                $listV = $listV2->toArray();
                $listAll = array();
                foreach($listV['data'] as $key=>$val) {
                    $id = intval($val['id']);                 
                    $listP = Db::name('wd_xcx_cate')->where("uniacid",$appletid)->where("id",$id)->where("type", "showPic")->order('num desc,id desc')->select(); 
                    foreach($listP as $k => $v){
                        if($v['catepic']){
                            $listP[$k]['catepic'] = remote($appletid,$v['catepic'],1);
                        }else{
                            $pic="/image/noimage_1.png";
                            $listP[$k]['catepic'] =  remote($appletid,$pic,1);
                        }
                    }
                    $listS = Db::name('wd_xcx_cate')->where("uniacid",$appletid)->where("cid",$id)->where("type", "showPic")->order('num desc,id desc')->select(); 
                    
                    foreach ($listS as $ki => $vi) {
                        if($vi['catepic']){
                            $listS[$ki]['catepic'] = remote($appletid,$vi['catepic'],1);
                        }else{
                            $pic2="/image/noimage_1.png";
                            $listS[$ki]['catepic'] =  remote($appletid,$pic2,1);
                        }
                    }
                   
                    //子集数据量
                    $zjcount = Db::name('wd_xcx_cate')->where("uniacid",$appletid)->where("cid",$id)->where("type", "showPic")->order('num desc')->count(); 
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
                $cate = Db::name('wd_xcx_cate')->where("uniacid",$id)->where('type', "showPic")->select();
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
            $data['type'] = "showPic";
        // }
        // if($type == 'page'){
        //     $data['list_style'] = 3;
        // }
        //内容列表样式
        $list_style = input("list_style");
        // if($type=="showArt"||$type=="showPic"||$type=="showWxapps"){
        //     if($list_style != 12){
        //         $data['list_style'] = $list_style;
        //     }else{
        //         $data['list_style']=2;
        //     }
        // }else if($type=="showPro"){
            if($list_style){
                $data['list_style'] = $list_style;
            }else{
                $data['list_style']=12;
            }
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
          $this->success('栏目信息更新成功！',Url('Pictures/cate').'?appletid='.$data['uniacid']);
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
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);


                $cid=input("cid")?input("cid"):0;
                $keys=input("key");
                $listV = Db::name('wd_xcx_cate')->where("uniacid",$appletid)->where('type','showPic')->where("cid",0)->order('num desc')->select();
                foreach($listV as $k=>&$val) {
                    $listS = Db::name('wd_xcx_cate')->where("uniacid",$appletid)->where("cid",$val['id'])->order('num desc')->select();
                    //子集数据量
                    $val['data'] = $listS;
                }
                $this->assign('cate',$listV);

                $listallcate = Db::name('wd_xcx_cate')->where("cid",$cid)->select();
                $array1=array();
                for($a=0;$a<count($listallcate);$a++){
                    array_push($array1,$listallcate[$a]['id']);
                }
                array_push($array1,$cid);
                if($cid>0 && $keys != ''){
                    $news = Db::name('wd_xcx_products')->where("uniacid",$appletid)->where("type","showPic")->where("cid","in",$array1)->where("title","like","%".$keys."%")->order('num desc')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                    $count = Db::name('wd_xcx_products')->where("uniacid",$appletid)->where("type","showPic")->where("cid","in",$array1)->where("title","like","%".$keys."%")->order('num desc')->count();  
                }else if($cid>0 && $keys == ''){
                    $news = Db::name('wd_xcx_products')->where("uniacid",$appletid)->where("type","showPic")->where("cid","in",$array1)->order('num desc')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                    $count = Db::name('wd_xcx_products')->where("uniacid",$appletid)->where("type","showPic")->where("cid","in",$array1)->order('num desc')->count();  
                }else if($cid==0 && $keys != ''){
                    $news = Db::name('wd_xcx_products')->where("uniacid",$appletid)->where("type","showPic")->where("title","like","%".$keys."%")->order('num desc')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                    $count = Db::name('wd_xcx_products')->where("uniacid",$appletid)->where("type","showPic")->where("title","like","%".$keys."%")->order('num desc')->count();  
                }else{
                    $news = Db::name('wd_xcx_products')->where("uniacid",$appletid)->where("type","showPic")->order('num desc')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                    $count = Db::name('wd_xcx_products')->where("uniacid",$appletid)->where("type","showPic")->order('num desc')->count();
                }

                if($news->toArray()){
                    $list = $news->toArray()['data'];
                }
                foreach ($list as $key => &$value) {
                    if($value['thumb']) {
                        $value['thumb'] = remote($appletid,$value['thumb'],1);
                    }else{
                        $value['thumb']=remote($appletid,"/image/noimage.jpg",1);
                    }
                }
                $this->assign('news',$news);
                
                $this->assign('list',$list);
                $this->assign('counts',$count);
                $this->assign('cid',$cid);
                $this->assign('key',$keys);
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
    public function getcates (){
            $id = intval(input('id'));
             $top_catas = Db::name('wd_xcx_multicate')->where("id",$id)->field('top_catas')->find();
            $top_catalist = Db::name('wd_xcx_multicates')->where("id",'in',unserialize($top_catas['top_catas']))->select();
            foreach ($top_catalist as $k => $v){
                // $sql = "SELECT * FROM ".tablename('sudu8_page_multicates')." WHERE `pid` = {$v['id']}";
                $top_catalist[$k]['sons'] = Db::name('wd_xcx_multicates')->where("pid",$v['id'])->select();
            }
            $data= $top_catalist;
            return json_encode($data);
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
                $listV = Db::name('wd_xcx_cate')->where("uniacid",$appletid)->where("cid",0)->where('type', 'showPic')->order('num desc')->select();
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
                $cates = Db::name('wd_xcx_multicate')->where("uniacid",$appletid)->where('statue',1)->where("type","showPic")->select();
                // $multipros = array();
                $newsid = input("newsid");
                $newsinfo=array();
                $allimg = array();
                if($newsid){
                    //有新闻号时，先判断该新闻是不是属于该小程序！
                    $newsget = Db::name('wd_xcx_products')->where("id",$newsid)->where("type","showPic")->find();
                    if($newsget['uniacid']==$appletid){
                        if($newsget['thumb']){
                            $newsget['thumb'] = remote($appletid,$newsget['thumb'],1);
                        }
                        if($newsget['shareimg']){
                            $newsget['shareimg'] = remote($appletid,$newsget['shareimg'],1);
                        }
                        if($newsget['randid']){
                            $allimg = Db::name('wd_xcx_products_url')->where("randid",$newsget['randid'])->select();
                            foreach($allimg as $k => &$v){
                                $allimg[$k]['url'] = remote($appletid,$v['url'],1);
                            }
                        }
                        $newsinfo = $newsget;
                        $sons_keys =   Db::name('wd_xcx_multicates')->where("id",'in',$newsinfo['top_catas'])->select();
                        foreach ($sons_keys as $k => $v){
                            $sons_keys[$k]['sons'] = Db::name('wd_xcx_multicates')->where("pid",$v['id'])->select();
                        }
                        //图片集
                        
                    }else{
                        $usergroup = Session::get('usergroup');
                        if($usergroup==1){
                            $this->error("找不到该内容，或者该内容不属于本小程序",'Applet/applet');
                        }
                        if($usergroup==2){
                            $this->error("找不到该内容，或者该内容不属于本小程序",'Applet/index');
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
            
                $this->assign('allimg',$allimg);
                $this->assign('sons_keys',$sons_keys);
                $this->assign('imgcount',count($allimg));
                $this->assign('newsid',$newsid);
                $this->assign('newsinfo',$newsinfo);
                $this->assign('cates',$cates);
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
        $data = array();
        //小程序ID
        $data['uniacid'] = input("appletid");
        //排序
        $num = input('num');
        if($num){
            $data['num'] = $num;
        }
        //所属栏目
        $cid = input('cid');
        if($cid){
            $data['cid'] = $cid;
            // 获取栏目具体信息
            $lanmu = Db::name('wd_xcx_cate')->where("id",$cid)->find();
            $data['pcid'] = $lanmu['cid'];
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
        $shareimg = input("commonuploadpic2");
        if($shareimg){
            $data['shareimg'] = remote($data['uniacid'],$shareimg,2);
        }
        //简介
        $desc = input('desc');
        if($desc){
            $data['desc'] = $desc;
        }
        
        //onlyid
        $onlyid = input('onlyid');
        if($onlyid){
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
        }
        // 处理幻灯片
        
        $silde = Db::name('wd_xcx_products_url')->where("randid",$onlyid)->select();
        $arrsilde = array();
        if($silde){
            foreach ($silde as $rec) {
                $arrsilde[]=$rec['url'];
            }
            $data['text'] = serialize($arrsilde);
        }else{
            $data['text'] = "";
        }
        //评论分享
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
        $data["get_share_score"] = input('get_share_score');
        $data["get_share_num"] = input('get_share_num');
        if($newsid){
            $data['etime'] = time();
            $res = Db::name('wd_xcx_products')->where("id",$newsid)->update($data);
        }else{
            $data['ctime'] = time();
            $res = Db::name('wd_xcx_products')->insert($data);
        }
        if($res){
           $this->success('基础信息更新成功！',Url('Pictures/index').'?appletid='.$data['uniacid']);
        }else{
          $this->error('基础信息更新失败，没有修改项！');
          exit;
        }
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
    public function del_img(){
        $id = input("id");
        $res = Db::name('wd_xcx_products_url')->where('id', $id)->delete();
        if($res){
            return 1;
        }else{
            $this->error("删除失败！");
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
    //多图片上传
    public function imgupload(){
        $data['randid'] = $_GET['randid'];
        $files = request()->file('');    
        foreach($files as $file){        
            // 移动到框架应用根目录/public/upimages/ 目录下        
            $info = $file->validate(['ext'=>'jpg,png,gif,jpeg'])->move(ROOT_PATH . 'public' . DS . 'upimages');
           if($info){
                $data['url'] =  ROOT_HOST."/upimages/".date("Ymd",time())."/".$info->getFilename();
                $data['dateline'] = time();
                $res = Db::name('wd_xcx_products_url')->insert($data);
            }else{
                // 上传失败获取错误信息
                return $this->error($file->getError()) ;
            }    
        }
    }
    //上传成功后获取图片
    public function getimg(){
        $id = $_POST['id'];     
        $allimg = Db::name('wd_xcx_products_url')->where("randid",$id)->select();
        if($allimg){
            return $allimg;
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
                $array1=input('pictures');
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

}