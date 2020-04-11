<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;
vendor('Qiniu.autoload');
use Qiniu\Auth as Auth;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;

use app\index\model\WdXcxMainShopOrder;
class Duoproducts extends Controller
{   
    public function cateset(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);

                $catestyle = Db::name("wd_xcx_base")->where("uniacid",$appletid)->value("catestyle");
                if(!$catestyle){
                    $catestyle = 1;
                }

                $this->assign("catestyle", $catestyle);
      
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
            return $this->fetch('cateset');
        }else{
            $this->redirect('Login/index');
        }
    }
    public function catesetsave(){
        $uniacid = input("appletid");
        $data = [
            'catestyle' => input('catestyle')
        ];
        $base = Db::name('wd_xcx_base')->where('uniacid', $uniacid)->find();
        if($base){
            $res = Db::name('wd_xcx_base')->where('uniacid', $uniacid)->update($data);
        }else{
            $res = Db::name('wd_xcx_base')->insert($data);
        }
        if($res){
            $this->success("栏目设置成功");
        }else{
            $this->error("栏目设置失败");
        }
    }
    public function cate(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $listV2 = Db::name('wd_xcx_cate')->where("uniacid",$appletid)->where("cid",0)->where("type", "showPro")->order('num desc,id desc')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                $count = Db::name('wd_xcx_cate')->where("uniacid",$appletid)->where("cid",0)->where("type", "showPro")->count();
                $listV = $listV2->toArray();
                $listAll = array();
                foreach($listV['data'] as $key=>$val) {
                    $id = intval($val['id']);                 
                    $listP = Db::name('wd_xcx_cate')->where("uniacid",$appletid)->where("id",$id)->where("type", "showPro")->order('num desc,id desc')->select(); 
                    foreach($listP as $k => $v){
                        if($v['catepic']){
                            $listP[$k]['catepic'] = remote($appletid,$v['catepic'],1);
                        }else{
                            $pic="/image/noimage_1.png";
                            $listP[$k]['catepic'] =  remote($appletid,$pic,1);
                        }
                    }
                    $listS = Db::name('wd_xcx_cate')->where("uniacid",$appletid)->where("cid",$id)->where("type", "showPro")->order('num desc,id desc')->select(); 
                    
                    foreach ($listS as $ki => $vi) {
                        if($vi['catepic']){
                            $listS[$ki]['catepic'] = remote($appletid,$vi['catepic'],1);
                        }else{
                            $pic2="/image/noimage_1.png";
                            $listS[$ki]['catepic'] =  remote($appletid,$pic2,1);
                        }
                    }
                   
                    //子集数据量
                    $zjcount = Db::name('wd_xcx_cate')->where("uniacid",$appletid)->where("cid",$id)->where("type", "showPro")->order('num desc')->count(); 
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
                $is_top = 0;
                $cateinfo = array();
                $cateid = input("cateid");
                $allimg = array();
                $cate = Db::name('wd_xcx_cate')->where("uniacid",$id)->where('type', "showPro")->order('num desc,id desc')->select();
                if($cateid){
                    $is = Db::name('wd_xcx_cate')->where("id",$cateid)->where('cid', 0)->value('id');
                    if($is){
                        $is_top = 1;
                    }
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
                $this->assign('is_top',$is_top);
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
            $data['type'] = "showPro";
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

        $is = 0;
        $onlyid = input("onlyid");
        if($data['cid'] == 0){
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
        }
        $data['randid'] = $onlyid;
        
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
          $this->success('栏目信息更新成功！',Url('Duoproducts/cate').'?appletid='.$data['uniacid']);
        }else{
          $this->error('栏目信息更新失败，没有修改项！');
          exit;
        }
    }

    private function aaBB(){
        $secret = md5('worldidc_wnmd'); // md5('worldidc_wnmd');

        $key_content = include('License.php');
        $key_content = $key_content['license'];
        $length = strlen($key_content);

        // 密钥长度小于 102 必然无效
        // if($length < 102) {
        //     die();
        // }

        $is = base64_decode(substr($key_content, 0, 6));

        if(substr($is, 0, 1) == '|'){
            $str_arr = unpack("C2", substr($is, 1));
            $key_content = substr($key_content, 6);
            $len1 = $str_arr[1];
            $len2 = $length - 6 - $len1 - $str_arr[2];
        }else{
            $len1 = 26;
            $len2 = $length - 102;
        }

        // 获取加密的 code
        $code = base64_decode(substr($key_content, $len1, $len2));

        $code_length = strlen($code);

        $round = $code_length / 32;
        $left = $code_length % 32;

        // 获取和 code 等长的 self_key
        $self_key = str_repeat($secret, $round) . substr($secret, 0, $left);

        // 这边不妨把两个都 unpack 下

        $decode = array_map(function($a, $b) {
            $c = $a - $b;
            return $c > 0 ? $c : $c + 256;
        }, unpack("C{$code_length}", $code), unpack("C{$code_length}", $self_key));

        $str = array_reduce($decode, function($sum, $code) {
            return $sum .= chr($code);
        }, '');

        //end
        if($str == $_SERVER['HTTP_HOST']){

            //通过
        }else{

                  // echo '密钥错误，请联系开发者获取正确密钥!';
          //  exit();
        }
    }


    public function catedel(){
        $data['id'] = input("cateid");
        $is = Db::name("wd_xcx_cate_pro")->where('cate_id', $data['id'])->find();
        if($is){
            $this->error('删除失败，该栏目下还有商品，不可删除！');
        }

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
    // 最新购物车列表
    public function index(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);

                $keys=input("key");
                $listV = Db::name('wd_xcx_cate')->where("uniacid",$appletid)->where('type','showPro')->where("cid",0)->order('num desc')->select();
                foreach($listV as $k=>&$val) {
                    $listS = Db::name('wd_xcx_cate')->where("uniacid",$appletid)->where("cid",$val['id'])->order('num desc')->select();
                    //子集数据量
                    $val['data'] = $listS;
                }
                $this->assign('cate',$listV);

                $cid=input("cid") ? input("cid") : 0; //查询某个栏目的商品
                $listallcate = Db::name('wd_xcx_cate')->where("cid",$cid)->select();
                $array1=array();
                for($a=0;$a<count($listallcate);$a++){
                    array_push($array1,$listallcate[$a]['id']);
                }
                array_push($array1,$cid);

                $where = [];
                if($cid > 0){
                    $is_top = Db::name('wd_xcx_cate')->where('uniacid', $appletid)->where("id", $cid)->where('cid', 0)->field('id')->find(); //判断是否为顶级栏目
                    if($is_top){
                        $cate_arr = Db::name('wd_xcx_cate')->where("uniacid",$appletid)->where("cid",$cid)->field('id')->select();
                        $cate_arr = array_column($cate_arr, 'id');
                    }
                    if($keys && $is_top){
                        $product = Db::name('wd_xcx_products')->alias('a')->join('wd_xcx_cate_pro b', 'a.id = b.pid', 'LEFT')->where('a.uniacid', $appletid)->where('b.cate_id', 'in', $cate_arr)->where('a.is_more',3)->where('a.title', 'like', '%'.$keys.'%')->group("a.id")->order('a.num desc, a.id desc')->field('a.*')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"), 'cid'=>$cid, 'key'=>input('key'))]);
                        $count = Db::name('wd_xcx_products')->alias('a')->join('wd_xcx_cate_pro b', 'a.id = b.pid', 'LEFT')->where('a.uniacid', $appletid)->where('b.cate_id','in', $cid)->where('a.is_more',3)->where('a.title', 'like', '%'.$keys.'%')->group("a.id")->field('a.id')->count();
                    }else if(!$keys && $is_top){
                        $product = Db::name('wd_xcx_products')->alias('a')->join('wd_xcx_cate_pro b', 'a.id = b.pid', 'LEFT')->where('a.uniacid', $appletid)->where('b.cate_id', 'in', $cate_arr)->where('a.is_more',3)->group("a.id")->order('a.num desc, a.id desc')->field('a.*')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"), 'cid'=>$cid)]);
                        $count = Db::name('wd_xcx_products')->alias('a')->join('wd_xcx_cate_pro b', 'a.id = b.pid', 'LEFT')->where('a.uniacid', $appletid)->where('b.cate_id', 'in', $cid)->where('a.is_more',3)->group("a.id")->field('a.id')->count();
                    }else if($keys && !$is_top){
                        $product = Db::name('wd_xcx_products')->alias('a')->join('wd_xcx_cate_pro b', 'a.id = b.pid', 'LEFT')->where('a.uniacid', $appletid)->where('b.cate_id', $cid)->where('a.is_more',3)->where('a.title', 'like', '%'.$keys.'%')->group("a.id")->order('a.num desc, a.id desc')->field('a.*')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"), 'cid'=>$cid, 'key'=>input('key'))]);
                        $count = Db::name('wd_xcx_products')->alias('a')->join('wd_xcx_cate_pro b', 'a.id = b.pid', 'LEFT')->where('a.uniacid', $appletid)->where('b.cate_id', $cid)->where('a.is_more',3)->where('a.title', 'like', '%'.$keys.'%')->group("a.id")->field('a.id')->count();
                    }else if(!$keys && !$is_top){
                        $product = Db::name('wd_xcx_products')->alias('a')->join('wd_xcx_cate_pro b', 'a.id = b.pid', 'LEFT')->where('a.uniacid', $appletid)->where('b.cate_id', $cid)->where('a.is_more',3)->group("a.id")->order('a.num desc, a.id desc')->field('a.*')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"), 'cid'=>$cid)]);
                        $count = Db::name('wd_xcx_products')->alias('a')->join('wd_xcx_cate_pro b', 'a.id = b.pid', 'LEFT')->where('a.uniacid', $appletid)->where('b.cate_id', $cid)->where('a.is_more',3)->group("a.id")->field('a.id')->count();
                    }
                }else{
                    if($keys != ''){
                        $product = Db::name('wd_xcx_products')->alias('a')->join('wd_xcx_cate_pro b', 'a.id = b.pid', 'LEFT')->where('a.uniacid', $appletid)->where('a.title', 'like', '%'.$keys.'%')->where('a.is_more',3)->group("a.id")->order('a.num desc, a.id desc')->field('a.*')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"), 'key'=> $keys)]);
                        $count = Db::name('wd_xcx_products')->alias('a')->join('wd_xcx_cate_pro b', 'a.id = b.pid', 'LEFT')->where('a.uniacid', $appletid)->where('a.title', 'like', '%'.$keys.'%')->where('a.is_more',3)->group("a.id")->count();
                    }else{
                        $product = Db::name('wd_xcx_products')->alias('a')->join('wd_xcx_cate_pro b', 'a.id = b.pid', 'LEFT')->where('a.uniacid', $appletid)->where('a.is_more',3)->group('a.id')->order('a.num desc, a.id desc')->field('a.*')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                        $count = Db::name('wd_xcx_products')->alias('a')->join('wd_xcx_cate_pro b', 'a.id = b.pid', 'LEFT')->where('a.uniacid', $appletid)->where('a.is_more',3)->group('a.id')->count();
                    }
                }
                $products = $product->toArray();
                foreach ($products['data'] as $key => &$value) {
                    $value['catenames'] = '';
                    $catenames = Db::name('wd_xcx_cate_pro')->alias('a')->join('wd_xcx_cate b', 'a.cate_id = b.id', 'LEFT')->where("a.uniacid",$appletid)->where('a.pid', $value['id'])->order('b.num desc, b.id desc')->field('name')->select();
                    if($catenames){
                        $catenames = array_column($catenames, 'name');
                        $value['catenames'] = implode('，', $catenames);
                    }
                    if($value['thumb']){
                      $value['thumb'] = remote($appletid,$value['thumb'],1);
                    }else{
                        $value['thumb'] = remote($appletid,"/image/noimage.jpg",1);
                    }
                    //获取多规格
                    $value['type_values'] = Db::name('wd_xcx_duo_products_type_value') ->where('pid', $value['id']) ->order("id asc") ->select();
                }

                $this->assign('counts',$count);
                $this->assign('product',$product);
                $this->assign('products',$products);
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
    public function del(){
        $appletid = input("appletid");
        $id = intval(input('newsid'));
        $res1 = Db::name('wd_xcx_products')->where("uniacid",$appletid)->where('id',$id)->delete();
        $res2 = Db::name('wd_xcx_duo_products_type_value')->where('pid',$id)->delete();
        $res2 = Db::name('wd_xcx_cate_pro')->where('pid',$id)->delete();
        $this->success('删除成功');
    }
    public function save(){
        $appletid = input("appletid");
        $newsid = input("newsid");
        $cid = intval(input('cid'));
        $randid = input('randid');

        $imgsrcs = input("imgsrcs/a");
        if($imgsrcs){
            $imgarr = array();
            foreach ($imgsrcs as $k => $v) {
                $imgarr['randid'] = $randid;
                $imgarr['appletid'] = $appletid;
                $imgarr['url'] = remote($appletid,$v,2);
                $imgarr['dateline'] = time();
                $is = Db::name('wd_xcx_products_url')->insert($imgarr);
            }
        }else{
            $is = 1;
        }
        $data['randid'] = $randid;
        $imgs = Db::name('wd_xcx_products_url')->where('randid',$randid)->select();
        $imgtext = array();
        foreach($imgs as $k => $v){
            array_push($imgtext,$v['url']);
        }

        $set1 = input("set1");
        $set2 = input("set2");
        $set3 = input("set3");
        $vipconfig = array(
            "set1" => $set1,
            "set2" => $set2,
            "set3" => $set3
            );


        // $pcid = Db::name('wd_xcx_cate')->where('uniacid',$appletid)->where('id',$cid)->find();
        // if($pcid){
        //     if($pcid['cid'] == 0){
        //         $pcids = $cid;
        //     }else{
        //         $pcids = intval($pcid['cid']);
        //     }
        $is_sale=0;
        if(input("is_sale")){
            $is_sale=input("is_sale");
        }
        $kuaidi=input('kuaidi');

        $use_more = input('use_more');

        $data = array(
            "uniacid" => $appletid,
            "num" => input('num'),
            "cid" => input('cid'),
            // "pcid" => $pcids,
            "type_x" => input('type_x'),
            "type_y" => input('type_y'),
            "type_i" => input('type_i'),
            "is_sale"=>$is_sale,
            "title" => input('title'),
            "price" => input('price'),
            "market_price" => input('mark_priceq'),
            "desc" => input('desc'),
            'labels' => input('labels'),
            "score" => input('score'),
            "randid" => $randid,
            "product_txt" => htmlspecialchars_decode(input('product_txt')),
            "text" => serialize($imgtext),
            "is_more" => 3,
            "type" => "showProMore",
            "hits" => input("hits"),
            'scoreback'=> input('scoreback'),
            "vipconfig" => serialize($vipconfig),
            "kuaidi"=>$kuaidi,
            'video'=> input('video'),
            'use_more' => $use_more,
            'pro_kc' => input('pro_kc'),
            // 'sale_tnum' => input('sale_tnum'),
            'sale_num' => input('sale_num')
        );
        //会员折扣
        $discount_status  = input('discount_status');
        if(!$discount_status){
            $data['discount_status'] = 0;
        }else{
            $data['discount_status'] = input('discount_status');
        }
        

        $valarr = input('valarrs/a')?input('valarrs/a'):[];
        $discount = [];
        //会员等级
        $grade_arr = Db::name("wd_xcx_vipgrade")->where("uniacid", $appletid)->order('grade asc')->select();
        foreach ($grade_arr as $ki => $vi) {
            foreach ($valarr as $key => $value) {
                if($ki == $key){
                    $discount[$ki]['grade'] = $vi['grade'];
                    $discount[$ki]['discount'] = $value;
                    continue;
                }
            }
        }
        $data['discount'] = serialize($discount);

        //门店
        // $stores= $kuaidi > 0 ? input("stores") : '';
        // if($stores){
        //     $data['stores']=$stores;
        // }else{
        //     $data['stores']='';
        // }

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

        if($kuaidi != 1){
            $data['freight_type'] = input('freight_type');
            if($data['freight_type'] == 1){
                $data['yunfei_ggid'] = input('yunfei_ggid');
            }else{
                $data['freight_price'] = input('freight_price');
            }
        }

        $data["get_share_gz"] = input('get_share_gz');
        // $data["get_share_gz"] = 2;
        $data["get_share_score"] = input('get_share_score');
        // $data["get_share_score"] = 0;
        $data["get_share_num"] = input('get_share_num');
        // $data["get_share_num"] = 0;
        //缩略图
        $thumb = input("commonuploadpic1");
        if($thumb){
            $data['thumb'] = remote($appletid,$thumb,2);
        }
        $shareimg = input("commonuploadpic2");
        if($shareimg){
            $data['shareimg'] = remote($appletid,$shareimg,2);
        }

        $data["types"] = 2;
        $is_del = 0; //is_del 删除原规格值数据 0不删除 1删除
        $is_change = 0; //is_change 是否更换新 0不 1是

        if($newsid){
            $now_cates = input("cates");
            $now_cates = explode(",", $now_cates); //最新提交栏目id数组
            $before_cates = Db::name('wd_xcx_cate_pro')->where("pid",$newsid)->field("cate_id")->select();
            if(count($before_cates) > 0){
                $before_cates = array_column($before_cates, 'cate_id'); //编辑前所属栏目id数组
                $before_cates = explode(",", implode(',', $before_cates));
                $c = $before_cates;

                if(count($before_cates) != count($now_cates) || count(array_diff($before_cates, $now_cates)) > 0){ //两个数组存在差异
                    $res = Db::name('wd_xcx_cate_pro')->where('cate_id', 'in', $before_cates)->where("pid", $newsid)->delete();
                    foreach ($now_cates as $value) {
                        $now_arr = [
                                'uniacid' => $appletid,
                                'pid' => $newsid,
                                'cate_id' => $value,
                            ];
                        Db::name('wd_xcx_cate_pro')->insert($now_arr);
                    }
                }
            }else{
               foreach ($now_cates as $value) {
                    $now_arr = [
                            'uniacid' => $appletid,
                            'pid' => $newsid,
                            'cate_id' => $value,
                        ];
                    Db::name('wd_xcx_cate_pro')->insert($now_arr);
                } 
            }

            Db::name('wd_xcx_products')->where('id',$newsid)->update($data);

            if($use_more == 1){
                $vals = Db::name('wd_xcx_duo_products_type_value')->where('pid',$newsid)->field('comment, type1, type2, type3')->select();
                if($vals){
                    $comment_arr = explode(',', $vals[0]['comment']);

                    // 规格组长度
                    $typelen = input('typelen');

                    // 规格数组
                    $types = input('typesarr');
                    $typezz = $types;
                    $typesarr = explode(",", $types);
                    //判断规格组是否有更改
                    if(count($comment_arr) == $typelen){
                        if(count(array_diff($comment_arr, $typesarr)) == 0){ //规格组未改变
                            //得到数据表中原有规格组的规格值
                            $vals1=[];
                            $vals2=[];
                            $vals3=[];
                            foreach ($vals as $kz => $vz) {
                                if($typelen == 1){
                                    array_push($vals1, $vz['type1']);
                                }else if($typelen == 2){
                                    array_push($vals1, $vz['type1']);
                                    array_push($vals2, $vz['type2']);
                                }else if($typelen == 3){
                                    array_push($vals1, $vz['type1']);
                                    array_push($vals2, $vz['type2']);
                                    array_push($vals3, $vz['type3']);
                                }
                            }
                            $vals1 = array_unique($vals1);
                            $vals2 = array_unique($vals2);
                            $vals3 = array_unique($vals3);

                            $ggzjsons = stripslashes(html_entity_decode(input('ggzjsons')));
                            $ggzjsons = json_decode($ggzjsons,true);
                            if($typelen == 1){
                                if(count($vals1) == count($ggzjsons[$comment_arr[0]])){
                                    if(count(array_diff($vals1, $ggzjsons[$comment_arr[0]])) != 0){
                                        $is_del = 1;
                                    }
                                }else{
                                    $is_del = 1;
                                }
                            }else if($typelen == 2){
                                if(count($vals1) == count($ggzjsons[$comment_arr[0]])){
                                    if(count(array_diff($vals1, $ggzjsons[$comment_arr[0]])) != 0){
                                        $is_del = 1;
                                    }
                                }else{
                                    $is_del = 1;
                                }
                                if(count($vals2) == count($ggzjsons[$comment_arr[1]])){
                                    if(count(array_diff($vals2, $ggzjsons[$comment_arr[1]])) != 0){
                                        $is_del = 1;
                                    }
                                }else{
                                    $is_del = 1;
                                }
                            }else if($typelen == 3){
                                if(count($vals1) == count($ggzjsons[$comment_arr[0]])){
                                    if(count(array_diff($vals1, $ggzjsons[$comment_arr[0]])) != 0){
                                        $is_del = 1;
                                    }
                                }else{
                                    $is_del = 1;
                                }
                                if(count($vals2) == count($ggzjsons[$comment_arr[1]])){
                                    if(count(array_diff($vals2, $ggzjsons[$comment_arr[1]])) != 0){
                                        $is_del = 1;
                                    }
                                }else{
                                    $is_del = 1;
                                }
                                if(count($vals3) == count($ggzjsons[$comment_arr[2]])){
                                    if(count(array_diff($vals3, $ggzjsons[$comment_arr[2]])) != 0){
                                        $is_del = 1;
                                    }
                                }else{
                                    $is_del = 1;
                                }
                            }
                        }
                    }else{
                        $is_del = 1;
                    }
                    if($is_del == 1){
                        Db::name('wd_xcx_duo_products_type_value')->where('pid',$newsid)->delete();
                    }else{
                        $is_change = 1;
                    }
                }
            }else{
                $this ->success('商品更新成功', Url('Duoproducts/index').'?appletid='.$appletid);
            } 
        }else{
            if(!$thumb && !$shareimg){
                $this->error('商品缩略图与分享图请至少设置一张！');
            }
            $newsid = Db::name('wd_xcx_products')->insertGetId($data);

            $now_cates = input("cates");
            $now_cates = explode(",", $now_cates); //最新提交栏目id数组
            foreach ($now_cates as $value) {
                $now_arr = [
                        'uniacid' => $appletid,
                        'pid' => $newsid,
                        'cate_id' => $value,
                    ];
                Db::name('wd_xcx_cate_pro')->insert($now_arr);
            }
            if($use_more == 2){
                $this ->success('商品添加成功', Url('Duoproducts/index').'?appletid='.$appletid);
            }
        }

        //规格组添加或重构插入
        // 规格组长度
        $typelen = input('typelen');

        // 规格数组
        $types = input('typesarr');
        $typezz = $types;
        $typesarr = explode(",", $types);

        //规格值
        $ggarr = stripslashes(html_entity_decode(input('biaogedata')));
        $proarr = json_decode($ggarr,true);
        $count = 0;
        foreach ($proarr as $key => $rec) {
            if($typelen == 1){
                $type1 = $rec[$typesarr[0]];
                $type2 = "";
                $type3 = "";
            }
            if($typelen == 2){
                $type1 = $rec[$typesarr[0]];
                $type2 = $rec[$typesarr[1]];
                $type3 = "";
            }
            if($typelen == 3){
                $type1 = $rec[$typesarr[0]];
                $type2 = $rec[$typesarr[1]];
                $type3 = $rec[$typesarr[2]];
            }
            $datas = array(
                "pid" => $newsid,
                "type1" => $type1,
                "type2" => $type2,
                "type3" => $type3,
                "kc" => $rec['库存'],
                "price" => $rec['售价'],
                "hnum" => $rec['货号'],
                "salenum" => $rec['已售数量'],
                "thumb" => $rec['规格图片'],
                "comment" => $typezz,
                "vsalenum"=>$rec['虚拟销量']
            );
            if($is_del == 0 && $is_change == 1){ //更新
                $where['type1'] = $type1;
                $where['type2'] = $type2;
                $where['type3'] = $type3;
                Db::name("wd_xcx_duo_products_type_value")->where('pid', $newsid)->where($where)->update($datas);
                $count++;
                if($count == count($proarr)){
                    $minprice=Db::name('wd_xcx_duo_products_type_value')->where('pid',$newsid)->order("price*1 asc")->limit(1)->find();
                    Db::name("wd_xcx_products")->where("id",$newsid)->update(array("price"=>$minprice['price']));
                    $this->success('商品更新成功',Url('Duoproducts/index').'?appletid='.$appletid);
                }
            }else{
                $res = Db::name('wd_xcx_duo_products_type_value')->insert($datas);
                if($res){
                    $count++;
                    if($count == count($proarr)){
                        $minprice=Db::name('wd_xcx_duo_products_type_value')->where('pid',$newsid)->order("price*1 asc")->limit(1)->find();
                        Db::name("wd_xcx_products")->where("id",$newsid)->update(array("price"=>$minprice['price']));
                        $this->success('商品更新成功',Url('Duoproducts/index').'?appletid='.$appletid);
                    }
                }
            }
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

                //会员等级
                $grade_arr = Db::name("wd_xcx_vipgrade")->where("uniacid", $appletid)->order('grade asc')->select();
                if(empty($grade_arr)){
                    $data_s = [
                        'uniacid' => $appletid,
                        'grade' => 1,
                        'name' => '大众会员',
                        'upgrade' => 0,
                        'price' => 0,
                        'status' => 1,
                        'bgcolor' => '#434550',
                        'card_img' => ROOT_HOST.'/vipgrade/vip_card.png',
                        'descs' => '默认会员等级'
                    ];
                    $gid = Db::name("wd_xcx_vipgrade")->insertGetid($data_s);
                    $grade_arr[0]['name'] = '大众会员';
                    $grade_arr[0]['grade'] = 1;
                    $grade_arr[0]['id'] = $gid;
                }

                $yunfei_gg_list = Db::name("wd_xcx_freight")->where("uniacid",$appletid)->where("is_delete", 0)->select();
                $this->assign('yunfei_gg_list', $yunfei_gg_list);

                $id = input('newsid');
                // $stores=Db::name("wd_xcx_store")->where("uniacid",$appletid)->select();

                // $this->assign('stores',$stores);
                $listV = Db::name('wd_xcx_cate')->where("uniacid",$appletid)->where('type','showPro')->where('cid',0)->order('num desc')->order('id desc')->field('id, cid,name')->select();
                $listAll = array();
                foreach($listV as $key=>$val) {
                    $cid = intval($val['id']);
                    $listP = Db::name('wd_xcx_cate')->where("uniacid",$appletid)->where('type','showPro')->where('id',$cid)->order('num desc')->order('id desc')->field('id, cid,name')->find();
                    $listS = Db::name('wd_xcx_cate')->where("uniacid",$appletid)->where('type','showPro')->where('cid',$cid)->order('num desc')->order('id desc')->field('id, cid,name')->select();
                    $listP['data'] = $listS;
                    array_push($listAll,$listP);
                }
                $take_self = Db::name('wd_xcx_duo_products_yunfei')->where('uniacid', $appletid)->value('take_self');
                if(!$take_self){
                    $take_self = 1;
                }
                if($id){
                    $products = Db::name('wd_xcx_products')->where('is_more',3)->where("uniacid",$appletid)->where('id',$id)->find();

                    $products['cates'] = Db::name('wd_xcx_cate_pro')->where('pid', $id)->field('cate_id')->select();
                    $products['cates'] = array_column($products['cates'], 'cate_id');
                    if(!empty($products['vipconfig'])){
                        $products['vipconfig'] = unserialize($products['vipconfig']);
                        if(!isset($products['vipconfig']['set3'])){
                            $products['vipconfig']['set3'] = 0;
                        }
                    }
                    $products['discount'] = $products['discount'] ? unserialize($products['discount']):[]; 
                    foreach ($grade_arr as $key => $value) {
                        $grade_arr[$key]['discount'] = '';
                        if($products['discount']){
                            foreach ($products['discount'] as $ks => $vs) {
                                if($value['grade'] == $vs['grade']){
                                    $grade_arr[$key]['discount'] = $vs['discount'];
                                }
                            }
                        }
                    }
                    
                    if($products['thumb']){
                        $products['thumb'] = remote($appletid,$products['thumb'],1);
                    }
                    if($products['shareimg']){
                        $products['shareimg'] = remote($appletid,$products['shareimg'],1);
                    }
                    $allimg = Db::name('wd_xcx_products_url')->where('randid',$products['randid'])->select();    
                    foreach ($allimg as $key => &$value) {
                        $value['url'] = remote($appletid,$value['url'],1);
                    }
                    if($products['types']==2){
                        $proarr = Db::name('wd_xcx_duo_products_type_value')->where('pid',$id)->order('id asc')->select();
                        //构建规格组
                        $counttypes=0;
                        $typesarr=array();
                        $typesjson = [];
                        if($proarr){
                            $types = $proarr[0]['comment'];
                            // 构建规格组json
                            $typesarr = explode(",", $types);
                            $counttypes = count($typesarr);

                            foreach ($typesarr as $key => &$rec) {
                                $str = "type".($key+1);
                                $ziji = Db::name('wd_xcx_duo_products_type_value')->where('pid',$id)->order("id asc")->field($str)->select();
                                $xarr = array();
                                foreach ($ziji as $key => $res) {
                                    array_push($xarr, $res[$str]);
                                }
                                $typesjson[$rec] = $xarr;
                            }
                        }
                        // 构建对应的数值
                        $datajson = [];
                        foreach ($proarr as $key => &$rec) {
                            $strs = $rec['type1'].$rec['type2'].$rec['type3'];
                            $strv = $rec['kc'].",".$rec['price'].",".$rec['hnum'].",".$rec['salenum'].",".$rec['vsalenum'].",".$rec['thumb'];
                            $datajson[$strs]=$strv;
                        }
                        $datajson_keys = $datajson ? json_encode(array_keys($datajson),JSON_UNESCAPED_UNICODE) : [];
                        foreach ($typesjson as $key => &$value) {
                            $value = array_unique($value);
                        }
                    }
                    if($products['types']==1){
                        $proarr = Db::name('wd_xcx_duo_products_type_value')->where('pid',$id)->order("id asc")->find();
                        $products['kc'] = 1; 
                        $counttypes = 0;
                        $typesarr = [];
                        $typesjson = [];
                        $datajson = [];
                        $datajson_keys = [];
                    }
                }else{
                    $products = "";
                    $id = 0; 
                    $allimg = "";
                    $counttypes = 0;
                    $typesarr = [];
                    $typesjson = [];
                    $datajson = [];
                    $datajson_keys = [];
                    foreach ($grade_arr as $key => $value) {
                        $grade_arr[$key]['discount'] = '';
                    }
                }
                // var_dump($typesjson);
                // var_dump(array_keys($typesjson));
                // var_dump(count($typesjson[array_keys($typesjson)[0]]));
                // exit;
                $this->assign('counttypes',$counttypes);
                $this->assign('typesarr',$typesarr);
                $this->assign('typesjson',$typesjson);
                $this->assign('datajson',$datajson);
                $this->assign('datajson_keys',$datajson_keys);
                $this->assign('allimg',$allimg);
                $this->assign('id',$id);
                $this->assign('take_self',$take_self);
                $this->assign('products',$products);
                $this->assign('listAll',$listAll);
                $this->assign('grade_arr',$grade_arr);
                
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
    public function order(){
        if(check_login()){
            if(powerget()){
                $this ->aaBB();
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);

                // 处理已发货并且过了自动收货时间还没有确定收货的订单
                $rules = Db::name("wd_xcx_duo_products_yunfei")->where("uniacid",$appletid)->field('receiving, support_time')->find();
                $receiving = $rules['receiving'] ? $rules['receiving'] : 15; //自动收货时间
                $support_time = $rules['support_time'] ? $rules['support_time'] : 15;  //售后时间
                if($receiving > 0){
                    $st = 3600*24*$receiving;
                    $time = time() - $st;
                    $clorders = Db::name('wd_xcx_main_shop_order')->where('uniacid',$appletid)->where('status', 2)->where('delivery_type',1)->where('deliver_time', 'LT', $time)->select();
                    foreach ($clorders as $key => $res) {
                        $order_items = Db::name('wd_xcx_main_shop_order_item') ->where('order_id', $res['order_id']) ->where('status', 'gt', 0) ->select(); //自动收货子订单处理
                        $services = Db::name('wd_xcx_main_shop_order_service')->where("uniacid", $appletid)->where("order_item_id", "like", "%".$res['order_id']."%")->where("status", "in", [0, 1] )->where("refund_time", 0)->select(); //查出进行中和同意中未退货的订单
                        foreach ($services as $k => $v) {
                            $service_data = [
                                'status' => -1,
                                'revoke_time' => time(),
                            ];
                            Db::name('wd_xcx_main_shop_order_service')->where("uniacid", $appletid)->where("order_service_id", $v['order_service_id'])->update($service_data);
                        }

                        //处理子订单状态
                        foreach ($order_items as $item){
                            $order_item_logs = unserialize($item['order_item_log']);
                            $order_item_log = ['time'=>time(), 'log'=>'订单确认收货'];
                            array_push($order_item_logs, $order_item_log);
                            Db::name('wd_xcx_main_shop_order_item') ->where('id', $item['id']) ->update(['status'=>3, 'received_time' => time(), 'order_item_log' => serialize($order_item_logs)]);
                        }

                        $adata = array(
                            "check_time" => time(),
                            "status" => 3
                        );
                        Db::name('wd_xcx_main_shop_order')->where('id',$res['id'])->update($adata);
                    }
                }

                // 处理已完成并已过售后时间的订单
                if($support_time > 0){
                    $st = 3600*24*$support_time;
                    $time = time() - $st;
                    $support_orders = Db::name('wd_xcx_main_shop_order')->where([
                        'uniacid' => $appletid,
                        'check_time' => ['between time', [100,$time]],
                        'is_fanxian' => 0
                    ])->select();
                    foreach ($support_orders as $ksi => $vsi) {
                        $this ->dopagegivemoney($appletid, $vsi['suid'], $vsi['order_id']);
                    }
                }
          
                // 处理30分钟未付款的订单
                $wforders = Db::name('wd_xcx_main_shop_order')->where('uniacid',$appletid)->where('status',0)->select();
                foreach ($wforders as $key => $res) {
                    $st = $res['creat_time'] + 1800;
                    if($st < time()){
                        $adata = array(
                            "cancel_time" => time(),
                            "status" => -2,
                        );
                        $orderItems = Db::name('wd_xcx_main_shop_order_item')->where("uniacid",$appletid)->where("order_id",$res['order_id'])->select();
                        foreach ($orderItems as $kk => $item) {
                            $order_item_logs = unserialize($item['order_item_log']);
                            $order_item_log = ['time'=>time(), 'log'=>'订单未支付，取消订单'];
                            array_push($order_item_logs, $order_item_log);
                            Db::name('wd_xcx_main_shop_order_item') ->where('order_id',$res['order_id']) ->update([
                                'status' => -2,
                                'cancel_time' => time(),
                                'order_item_log' => serialize($order_item_logs)
                            ]);
                        }
                        Db::name('wd_xcx_main_shop_order')->where('id',$res['id'])->update($adata);
                    }
                }
   

                $where = [];
                $source = intval(input('source')); //订单来源：source:1微信 2支付宝 3H5 4百度 5字节跳动 6QQ
                if($source > 0){
                    $where['source'] = $source;
                }

                
                $status = intval(input('status')); // 订单状态：status -3 付款后取消  -2 未付款取消  -1 未支付过期 0 未支付  1 待发货 2 待核销(全部发货待收货) 3 全部核销收货  4 付款后总订单取消申请 5 完成 
                if($status > 0){
                    if($status == 1){ //前台选择 待付款
                        $where['status'] = 0;
                    }else if($status == 2){ //前台选择 待发货
                        $where['status'] = 1;
                    }else if($status == 3){ //前台选择 待收货
                        $where['status'] = 2;
                        $where['delivery_type'] = 1;
                    }else if($status == 4){ //前台选择 待核销
                        $where['status'] = 2;
                        $where['delivery_type'] = 2;
                    }else if($status == 5){ //前台选择 交易成功
                        $where['status'] = 5;
                    }else if($status == 6){ //前台选择 交易关闭
                        $where['status'] = ['in', [-3, -2, -1]];
                    }else if($status == 7){ //已收货 
                        $where['status'] = 3;
                        $where['delivery_type'] = 1;
                    }else if($status == 8){ //已核销
                        $where['status'] = 3;
                        $where['delivery_type'] = 2;
                    }
                }

                $delivery_type = intval(input('delivery_type')); //订单配送方式：delivery_type:1快递 2自取
                if($delivery_type > 0){
                    $where['delivery_type'] = $delivery_type;
                }

                $screen_starttime = input('screen_starttime');
                $screen_endtime = input('screen_endtime');
                if(!empty($screen_starttime) && !empty($screen_endtime)){//下单时间开始
                    $where['creat_time'] = ['between', strtotime($screen_starttime).','.strtotime($screen_endtime)];
                }else if(!empty($screen_starttime)){
                    $where['creat_time'] = ['>=', strtotime($screen_starttime)];
                }else if(!empty($screen_endtime)){//下单时间结束
                    $where['creat_time'] = ['<=', strtotime($screen_endtime)];
                }

                $screen_type = intval(input('screen_type')); //查询类型 1订单号 2手机号
                $screen_keys = trim(input('screen_keys'));
                if($screen_type > 0 && $screen_keys){
                    if($screen_type == 1){
                        $where['order_id'] = ['like', '%'.$screen_keys.'%'];
                    }else{
                        $where['buyer_mobile'] = ['like', '%'.$screen_keys.'%'];
                    }
                }

                //查询主订单列表信息
                $list = Db::name('wd_xcx_main_shop_order')->where('uniacid', $appletid)->where($where)->order('id desc')->paginate(10, false, ['query' => ['appletid' => $appletid, 'source' => $source, 'delivery_type' => $delivery_type, 'status' => $status, 'screen_starttime' => $screen_starttime, 'screen_endtime' => $screen_endtime, 'screen_type' => $screen_type, 'screen_keys' => $screen_keys]]);

                $counts = Db::name('wd_xcx_main_shop_order')->where('uniacid', $appletid)->where($where)->count();
                $received_time = Db::name('wd_xcx_duo_products_yunfei')->where('uniacid', $appletid)->value('support_time');
                $lists = $list->toArray()['data'];
                foreach ($lists as $k => $v) {

                    $lists[$k]['creat_time'] = date("Y-m-d H:i:s", $v['creat_time']);
                    $userinfo = getNameAvatar($v['suid'], $appletid);
                    $lists[$k]['nickname'] = $userinfo['nickname'];
                    if($v['delivery_type'] == 1){
                        $lists[$k]['address_info'] = unserialize($v['address_info']);
                    }else{
                        $lists[$k]['self_taking_info'] = unserialize($v['self_taking_info']);
                        $lists[$k]['self_taking_info']['self_taking_shop_info'] = unserialize($lists[$k]['self_taking_info']['self_taking_shop_info']);
                    }
                    $sub_list = Db::name('wd_xcx_main_shop_order_item')->where("uniacid", $appletid)->where('order_id', $v['order_id'])->select();
                    $lists[$k]['is_all_tui'] = 1; //合并退款
                    $lists[$k]['is_hx'] = 1; //合并退款

                    //判断是否有已发货子订单
                    $is_freight = 0;
                    if($v['freight_money'] > 0){
                        $is_freight = Db::name("wd_xcx_main_shop_order_item")->where('uniacid', $appletid)->where('order_id', $v['order_id'])->where('delivery_type', 1)->where('status', 'gt', 1)->find();
                    }

                    foreach ($sub_list as $ks => $vs) {
                        if($vs['status'] == 4 || $vs['status'] == 5){
                            $lists[$k]['is_hx'] = 2;
                        }
                        if($vs['status'] != 1){
                            $lists[$k]['is_all_tui'] = 0; //合并退款
                        }
                        $sub_list[$ks]['is_received'] = 0;
                        $sub_list[$ks]['num_ky'] = $vs['num'] - $vs['refund_num'];

                        if($v['status'] == 3 && $vs['delivery_type'] == 1){
                            $received_times = $vs['received_time'] + $received_time * 3600 * 24; //售后有效期结束时间戳
                            if($received_times < time()){
                                $sub_list[$ks]['is_received'] = 1;
                            }
                        }

                        $sub_list[$ks]['pro_thumb'] = remote($appletid, $vs['pro_thumb'], 1);
                        $sub_list[$ks]['pro_discounts_prices'] = number_format($vs['pro_discounts_price'] * $vs['num'], 2);  //折扣价
                        $sub_list[$ks]['pro_prices'] = number_format($vs['pro_price'] * $vs['num'], 2);  //原价
                        $sub_list[$ks]['is_add_freight'] = 0;
                        $sub_list[$ks]['is_last_refund'] = 0;
                        $orderItem_counts = Db::name("wd_xcx_main_shop_order_item")->where("uniacid",$appletid)->where("order_id", $v['order_id'])->count();

                            $refund_count = Db::name("wd_xcx_main_shop_order_item")->where("uniacid",$appletid)->where("order_id", $v['order_id'])->where("status", -4)->count();

                            if($orderItem_counts - $refund_count == 1){
                                $sub_list[$ks]['is_last_refund'] = 1;
                            }

                        if(!$is_freight){
                            $orderItem_count = Db::name("wd_xcx_main_shop_order_item")->where("uniacid",$appletid)->where("order_id", $v['order_id'])->where("status", 1)->count();
                            if($orderItem_count == 1){
                                $order_item_id = Db::name("wd_xcx_main_shop_order_item")->where("uniacid",$appletid)->where("order_id", $v['order_id'])->where("status", 1)->value('order_item_id');
                                if($vs['order_item_id'] == $order_item_id){
                                    $sub_list[$ks]['is_add_freight'] = 1;
                                }
                            }
                        }
                    }
                    $lists[$k]['sub_list'] = $sub_list;
                    $lists[$k]['sub_list_count'] = count($sub_list);
                }
                $this->assign('list', $list);
                $this->assign('lists', $lists);
                $this->assign('counts', $counts);

                $this->assign('source', $source);
                $this->assign('delivery_type', $delivery_type);
                $this->assign('status', $status);
                $this->assign('screen_starttime', $screen_starttime);
                $this->assign('screen_endtime', $screen_endtime);
                $this->assign('screen_type', $screen_type);
                $this->assign('screen_keys', $screen_keys);

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
            return $this->fetch('order');
        }else{
            $this->redirect('Login/index');
        }
    }
    
    /**
     * 分销给上级返钱
     */
    protected function dopagegivemoney($uniacid, $suid, $order_id){
        $prefix= config('database.prefix');
        $order_items = Db::name('wd_xcx_main_shop_order_item') ->where([
            'uniacid' => $uniacid,
            'suid' => $suid,
            'order_id' => $order_id,
            'status' => ['in', [3, 7]]
        ]) ->select();
        if(count($order_items)>0){
            foreach ($order_items as $item){
                $fx_ls = Db::name('wd_xcx_fx_ls') ->where([
                    'uniacid' => $uniacid,
                    'suid' => $suid,
                    'order_id' => $item['order_item_id']
                ]) ->find();
                if($fx_ls){
                    //父级
                    if($fx_ls['parent_id_get'] && $fx_ls['parent_id']){
                        $user = Db::name('wd_xcx_superuser') ->where('id', $fx_ls['parent_id']) ->field('fx_allmoney, fx_money') ->find();
                        if($user){
                            $res = Db::name('wd_xcx_superuser') ->where('id', $fx_ls['parent_id']) ->update([
                                'fx_allmoney' => $user['fx_allmoney'] + $fx_ls['parent_id_get'],
                                'fx_money' => $user['fx_money'] + $fx_ls['parent_id_get']
                            ]);
                        }
                    }
                    //父父级
                    if($fx_ls['p_parent_id_get'] && $fx_ls['p_parent_id']){
                        $user = Db::name('wd_xcx_superuser') ->where('id', $fx_ls['p_parent_id']) ->field('fx_allmoney, fx_money') ->find();
                        if($user){
                            $res = Db::name('wd_xcx_superuser') ->where('id', $fx_ls['p_parent_id']) ->update([
                                'fx_allmoney' => $user['fx_allmoney'] + $fx_ls['p_parent_id_get'],
                                'fx_money' => $user['fx_money'] + $fx_ls['p_parent_id_get']
                            ]);
                        }
                    }
                    //父父父级
                    if($fx_ls['p_p_parent_id_get'] && $fx_ls['p_p_parent_id']){
                        $user = Db::name('wd_xcx_superuser') ->where('id', $fx_ls['p_p_parent_id']) ->field('fx_allmoney, fx_money') ->find();
                        if($user){
                            $res = Db::name('wd_xcx_superuser') ->where('id', $fx_ls['p_p_parent_id']) ->update([
                                'fx_allmoney' => $user['fx_allmoney'] + $fx_ls['p_p_parent_id_get'],
                                'fx_money' => $user['fx_money'] + $fx_ls['p_p_parent_id_get']
                            ]);
                        }
                    }
                    //改变订单状态
                    Db::name('wd_xcx_fx_ls') ->where('id', $fx_ls['id']) ->update(['flag' => 2]);
                }
            }
            Db::name('wd_xcx_main_shop_order') ->where('order_id', $order_id) ->update(['is_fanxian' => 1]);
        }
    }

    public function getformval(){
        $appletid = input('uniacid');
        $order_id = input('order_id');
        $formval = Db::name('wd_xcx_main_shop_order')->where('uniacid',$appletid)->where('order_id', $order_id)->value('formlist_val');
        $formval = unserialize($formval);
        return json_encode($formval);
    }
    //订单操作
    public function func_operation(){
        $appletid = input('appletid');
        $op = input('op');
        $freight_type = input('freight_type'); //发货类型  1单独 2合并
        $order_sub = []; //微信订阅消息数组
        if($op == 'deliver'){ //发货
            $express = input('express');  //快递公司
            $express_no = input('express_no'); //快递单号
            $data = [
                'express' => $express,
                'express_no' => $express_no,
                'deliver_time' => time(),
                'status' => 2,               
            ];
            if($freight_type == 1){
                $order_item_id = input('order_id');
                $order_item_info = Db::name('wd_xcx_main_shop_order_item')->where('order_item_id', $order_item_id)->field('order_id, source, order_item_log, suid')->where('status', 1)->find();
                if($order_item_info){ //判断是否是未发货订单
                    $order_item_log = unserialize($order_item_info['order_item_log']);
                    $log = ['time'=>time(), 'log'=>'订单发货'];
                    array_push($order_item_log, $log);
                    $order_item_log = serialize($order_item_log);
                    $data['order_item_log'] = $order_item_log; //操作日志
                    Db::name('wd_xcx_main_shop_order_item')->where('order_item_id', $order_item_id)->where('uniacid', $appletid)->update($data);
                    $is = Db::name('wd_xcx_main_shop_order_item')->where('order_id', $order_item_info['order_id'])->where('status', 1)->find(); //判断是否无未发货订单
                    if(!$is){
                        $data['allow_all_refund'] = 2;
                        Db::name('wd_xcx_main_shop_order')->where('order_id', $order_item_info['order_id'])->where('uniacid', $appletid)->update(['status' => 2, 'allow_all_refund' => 2, 'deliver_time' => time()]); //修改主订单状态为已发货
                    }

                    $order_sub = $order_item_info;
                }else{
                    $this->error("发货失败，订单不存在或已发货", Url('Duoproducts/order').'?appletid='.$appletid);
                }
            }else{
                $order_id = input('order_id');
                $order_infos = Db::name('wd_xcx_main_shop_order')->where('order_id', $order_id)->where('uniacid', $appletid)->where('status', 1)->find();
                if($order_infos){ //判断是否是未发货订单
                    $data_top = $data;
                    $data_top['allow_all_refund'] = 2;

                    Db::name('wd_xcx_main_shop_order')->where('order_id', $order_id)->where('uniacid', $appletid)->update($data_top);
                    
                    //查询所有未发货订单，并修改为已发货，添加物流、操作日志
                    $order_item_infos = Db::name('wd_xcx_main_shop_order_item')->where('order_id', $order_id)->where('uniacid', $appletid)->where('status', 1)->select();
                    foreach ($order_item_infos as $k => $v) {
                        $order_item_log = unserialize($v['order_item_log']);
                        $log = ['time'=>time(), 'log'=>'订单发货'];
                        array_push($order_item_log, $log);
                        $order_item_log = serialize($order_item_log);
                        $data['order_item_log'] = $order_item_log; //操作日志
                        $data['status'] = 2;
                        Db::name('wd_xcx_main_shop_order_item')->where('order_item_id', $v['order_item_id'])->where('uniacid', $appletid)->update($data);
                    }

                    $order_sub = $order_infos;
                }else{
                    $this->error("发货失败，订单不存在或已发货", Url('Duoproducts/order').'?appletid='.$appletid);
                }
            }
            if($order_sub && isset($order_sub['source']) && $order_sub['source'] == 1){
                $openid = Db::name("wd_xcx_user")->where("suid", $order_sub['suid'])->value('openid');
                $jsons = [
                    'order_id' => $order_sub['order_id']
                ];
                $jsons = serialize($jsons);
                sendSubscribe($appletid, 1, $openid, $jsons);
            }
            $this->success("发货成功", Url('Duoproducts/order').'?appletid='.$appletid);
        }else if($op == 'hx'){ //核销
            $order_id = input('order_id'); //订单号
            $data = [
                'status' => 3,
                'check_time' => time(),
                'allow_all_refund' => 2
            ];
            $orderItems = Db::name("wd_xcx_main_shop_order_item")->where("uniacid", $appletid)->where("order_id", $order_id)->where("status", 2)->select();
            Db::startTrans();
            try {
                foreach ($orderItems as $k => $v) {
                    $order_item_log = unserialize($v['order_item_log']);
                    $log = ['time'=>time(), 'log'=>'订单核销，系统核销'];
                    array_push($order_item_log, $log);
                    $order_item_log = serialize($order_item_log);
                    $data_sub['order_item_log'] = $order_item_log; //操作日志
                    $data_sub['status'] = 3;
                    $data_sub['check_time'] = time();
                    $res = Db::name("wd_xcx_main_shop_order_item")->where("uniacid", $appletid)->where("order_item_id", $v['order_item_id'])->update($data_sub);
                    if(!$res){
                        throw new \Exception("数据表操作失败");
                    }
                }
                $res = Db::name("wd_xcx_main_shop_order")->where("uniacid", $appletid)->where("order_id", $order_id)->update($data);
                if(!$res){
                    throw new \Exception("数据表操作失败");
                }

                Db::commit();

            } catch (\Exception $e) {
                Db::rollback();
                $this->error('核销失败，' . $e->getMessage(), Url('Duoproducts/order').'?appletid='.$appletid);
            }
            $order = Db::name("wd_xcx_main_shop_order")->where("uniacid", $appletid)->where("order_id", $order_id)->field("pay_money, is_change_price, change_price, suid, total_can_tui_money, source")->find();
            $pay_money = $order['total_can_tui_money'];
            add_all_pay($appletid, $pay_money, $order['suid']);
            check_vip_grade($appletid, $order['suid']);

            if($order['source'] == 1){
                $openid = Db::name("wd_xcx_user")->where("suid", $order['suid'])->value('openid');
                $jsons = [
                    'fprice' => $pay_money
                ];
                $jsons = serialize($jsons);
                sendSubscribe($appletid, 2, $openid, $jsons);
            }
            $this->success('核销成功', Url('Duoproducts/order').'?appletid='.$appletid);
        }else if($op == 'refound'){ //退款
            $refound_price = floatval(input('refound_price')); //退款金额
            $refound_num = input('refound_num'); //退款数量
            $refound_msg = input('refound_msg'); //退款备注

            $data = []; //总订单修改数据
            $data_sub = []; //子订单修改数据
            $return = [];

            if($freight_type == 1){ //子订单
                $order_item_id = input('order_id');
                $order_item_infos = Db::name('wd_xcx_main_shop_order_item')->where('order_item_id', $order_item_id)->where('uniacid', $appletid)->find();
                $order_id = $order_item_infos['order_id'];
                $order_infos = Db::name('wd_xcx_main_shop_order')->where('order_id', $order_id)->where('uniacid', $appletid)->find();

                $userinfo = Db::name("wd_xcx_superuser")->where("uniacid",$appletid)->where('id', $order_infos['suid'])->find();
                $total_can_tui_money = $order_infos['total_can_tui_money'] - $refound_price;
                if($total_can_tui_money < 0){
                    $this->error("退款失败，可退金额不足", Url('Duoproducts/order').'?appletid='.$appletid);
                }
                $order_item_log = unserialize($order_item_infos['order_item_log']);
                $log = ['time'=>time(), 'log'=>'订单退部分金额：退款数量：'.$refound_num.'、退款金额：￥'.$refound_price];
                array_push($order_item_log, $log);
                $order_item_log = serialize($order_item_log);
                $data_sub['order_item_log'] = $order_item_log; //操作日志
                $data_sub['refund_num'] = $order_item_infos['refund_num'] + $refound_num;


                $order_service_id = 's'.date('YmdHi', time()).substr(microtime(), 2, 4).rand(1000,9999); //售后单号

                $data_sub['order_service_id'] = $order_service_id;
                $data_sub['cancel_time'] = time();
                $data_sub['tui_time'] = time();
                $data_sub['has_service'] = 1;
                //创建售后订单
                $order_service_data = [
                    'uniacid' => $appletid,
                    'suid' => $order_infos['suid'],
                    'source' => $order_infos['source'],
                    'order_service_id' => $order_service_id,
                    'order_item_id' => $order_item_id,
                    'num' => $refound_num,
                    'refund_money' => $refound_price,
                    'apply_remark' => $refound_msg,
                    'apply_type' => 0,
                    'status' => 3,
                    'creat_time' => time(),
                    'agree_time' => time(),
                    'is_item' => 1
                ];
                $order_sub = $order_item_infos;
            }else{ //总订单&全部子订单
                $order_id = input('order_id');
                $order_infos = Db::name('wd_xcx_main_shop_order')->where('order_id', $order_id)->where('uniacid', $appletid)->find();
                $total_can_tui_money = $order_infos['total_can_tui_money'] - $refound_price;
                if($total_can_tui_money < 0){
                    $this->error("退款失败，可退金额不足", Url('Duoproducts/order').'?appletid='.$appletid);
                }

                $order_service_id = 's'.date('YmdHi', time()).substr(microtime(), 2, 4).rand(1000,9999); //售后单号

                //创建售后订单
                $order_service_data = [
                    'uniacid' => $appletid,
                    'suid' => $order_infos['suid'],
                    'source' => $order_infos['source'],
                    'order_service_id' => $order_service_id,
                    'order_item_id' => $order_id,
                    'num' => $order_infos['total_num'],
                    'refund_money' => $refound_price,
                    'apply_remark' => $refound_msg,
                    'apply_type' => 0,
                    'status' => 3,
                    'creat_time' => time(),
                    'agree_time' => time(),
                    'is_item' => 2
                ];
                $order_service_data['refund_money'] = $refound_price;
                $order_item_infos = Db::name('wd_xcx_main_shop_order_item')->where('order_id', $order_id)->where('uniacid', $appletid)->select();

                //退款
                $userinfo = Db::name("wd_xcx_superuser")->where("uniacid",$appletid)->where('id', $order_infos['suid'])->find();
   
                // 退积分、添加流水
                if($order_infos['score_use'] > 0){
                    $return['score'] = $userinfo['money'] + $order_infos['score_use'];
                    $score_return = array(
                        "uniacid" => $appletid,
                        "orderid" => $order_id,
                        "suid" => $order_infos['suid'],
                        "type" => "add",
                        "score" => $order_infos['score_use'],
                        "message" => "订单退款",
                        "creattime" => time()
                    );
                }
   
                $data['status'] = -3;
                $order_sub = $order_infos;
            }
            $data['allow_all_refund'] = 2;
            $data['total_can_tui_money'] = $total_can_tui_money;
            $data['order_service_id'] = $order_service_id;
            $data['has_service'] = 1;

            //退金额
            if($refound_price > 0){
                $money_return = array(
                    "uniacid" => $appletid,
                    "suid" => $order_infos['suid'],
                    "type" => "add",
                    "score" => $refound_price,
                    "message" => "订单退款",
                    "creattime" => time()
                );
                if($order_infos['pay_type'] == 1){ //余额
                    $return['money'] = $userinfo['money'] + $refound_price;
                }
            }
            Db::startTrans();
            try {
                if($freight_type == 1){
                    $money_return["orderid"] = $order_item_id;
                    if($data_sub['refund_num'] == $order_item_infos['num']){ //退款数量等于购买数量时，子订单状态更改为单独退款、返佣订单状态更改为取消分成
                        $data_sub['status'] = -4;
                        $fx_ls = Db::name('wd_xcx_fx_ls')->where('uniacid',$appletid)->where("order_id", $order_item_id)->find(); //判断是否存在返佣订单
                        if($fx_ls){
                            $res = Db::name('wd_xcx_fx_ls')->where('uniacid',$appletid)->where("order_id", $order_item_id)->update([
                                    'parent_id_get' => 0,
                                    'p_parent_id_get' => 0,
                                    'p_p_parent_id_get' => 0,
                                    'flag' => 3,
                                ]); //返佣订单状态更改为取消分成
                            if(!$res){
                                throw new \Exception("数据表操作失败");
                            }
                        }
                    }else{
                        $fx_ls = Db::name('wd_xcx_fx_ls')->where('uniacid',$appletid)->where("order_id", $order_item_id)->find(); //判断是否存在返佣订单
                        if($fx_ls){
                            $refound_num = $order_item_infos['num'] - $data_sub['refund_num'] + $refound_num; //原剩余数量
                            $fx_one = sprintf("%01.2f",$fx_ls['parent_id_get'] / $refound_num); //单价 父级
                            $fx_two = sprintf("%01.2f",$fx_ls['p_parent_id_get'] / $refound_num); //单价 父父级
                            $fx_three = sprintf("%01.2f",$fx_ls['p_p_parent_id_get'] / $refound_num); //单价 父父父级
                            $upd = [
                                'parent_id_get' => sprintf("%01.2f",$fx_one * ($order_item_infos['num'] - $data_sub['refund_num'])),
                                'p_parent_id_get' => sprintf("%01.2f",$fx_one * ($order_item_infos['num'] - $data_sub['refund_num'])),
                                'p_p_parent_id_get' => sprintf("%01.2f",$fx_one * ($order_item_infos['num'] - $data_sub['refund_num'])),
                            ];
                            $res = Db::name('wd_xcx_fx_ls')->where('uniacid',$appletid)->where("order_id", $order_item_id)->update($upd); //返佣订单更改返佣金额
                            if(!$res){
                                throw new \Exception("数据表操作失败");
                            }
                        }
                    }
                    $res = Db::name('wd_xcx_main_shop_order_item')->where('order_item_id', $order_item_id)->where('uniacid', $appletid)->update($data_sub);

                    //退库存，减销量
                    $this ->toDealWithInventorySales($order_item_infos['pro_id'], $order_item_infos['pro_type_id'], $refound_num);

                    $is = Db::name('wd_xcx_main_shop_order_item')->where('order_id', $order_id)->where('uniacid', $appletid)->where('status','>', 0)->find();
                    if(!$is){
                        $data['status'] = -3;
                    }
                    if(!$res){
                        throw new \Exception("数据表操作失败");
                    }

                }else{
                    $money_return["orderid"] = $order_id;
                    foreach ($order_item_infos as $k => $v) {
                        $fx_ls = Db::name('wd_xcx_fx_ls')->where('uniacid',$appletid)->where("order_id", $v['order_item_id'])->find(); //判断是否存在返佣订单
                        if($fx_ls){
                            $res = Db::name('wd_xcx_fx_ls')->where('uniacid',$appletid)->where("order_id", $v['order_item_id'])->update([
                                    'flag' => 3
                                ]); //返佣订单状态更改为取消分成
                            if(!$res){
                                throw new \Exception("数据表操作失败");
                            }
                        }

                        $order_item_log = unserialize($v['order_item_log']);
                        $log = ['time'=>time(), 'log'=>'订单已支付，订单退全款'];
                        array_push($order_item_log, $log);
                        $order_item_log = serialize($order_item_log);
                        $data_sub['order_item_log'] = $order_item_log; //操作日志
                        $data_sub['refund_num'] = $v['num'];
                        $data_sub['status'] = -3;
                        $data_sub['order_service_id'] = $order_service_id;
                        $data_sub['cancel_time'] = time();
                        $res = Db::name('wd_xcx_main_shop_order_item')->where('order_item_id', $v['order_item_id'])->where('uniacid', $appletid)->update($data_sub);
                        if(!$res){
                            throw new \Exception("数据表操作失败");
                        }

                        //退库存
                        $this ->toDealWithInventorySales($v['pro_id'], $v['pro_type_id'], $v['num']);
                    }
                    
                    if($order_infos['score_use'] > 0){ //积分流水
                        Db::name('wd_xcx_score')->insert($score_return);
                        if(!$res){
                            throw new \Exception("数据表操作失败");
                        }
                    }
                    
                    if($order_infos['coupon_id'] > 0){
                        $coupon_info = Db::name("wd_xcx_coupon_user")->where("uniacid",$appletid)->where('id', $order_infos['coupon_id'])->find();
                        if($coupon_info['etime'] > time()){ //判断优惠券是否过期
                            $res = Db::name("wd_xcx_coupon_user")->where("uniacid",$appletid)->where('id', $order_infos['coupon_id'])->update(['flag' => 0, 'utime' => 0]);
                            if(!$res){
                                throw new \Exception("数据表操作失败");
                            }
                        }
                    }
                }
                if($return){
                    $res = Db::name("wd_xcx_superuser")->where("uniacid",$appletid)->where('id', $order_infos['suid'])->update($return);
                    if(!$res){
                        throw new \Exception("数据表操作失败");
                    }
                }
                if($refound_price > 0){ //金额流水记录
                    $res = Db::name('wd_xcx_money')->insert($money_return);
                    if(!$res){
                        throw new \Exception("数据表操作失败");
                    }
                    if($order_infos['pay_type'] == 2){ //线上退款

                        $pay_to = $order_infos['pay_to']; //支付到   1 微信 2 支付宝  3 百度 4 QQ
                        $app = Db::name('wd_xcx_applet')->where("id",$appletid)->find();

                        if($pay_to == 1){//支付到   1 微信
                            if($order_infos['is_change_price'] == 1){
                                $order_id_new = unserialize($order_infos['payment_info'])['order_id_new'];
                            }else{
                                $order_id_new = $order_id;
                            }
                            $source = $order_infos['source'];
                            if($source == 1){
                                $mchid = $app['mchid'];   //商户号
                                $apiKey = $app['signkey'];    //商户的秘钥
                                $appid = $app['appID'];                 //小程序的id
                                $SSLCERT_PATH = ROOT_PATH . 'public/Cert/' . $appletid . '/apiclient_cert.pem';//证书路径
                                $SSLKEY_PATH = ROOT_PATH . 'public/Cert/' . $appletid . '/apiclient_key.pem';//证书路径
                            }elseif($source == 3){
                                $mchid = $app['wx_h5_mchid'];   //商户号
                                $apiKey = $app['wx_h5_signkey'];    //商户的秘钥
                                $appid = $app['wx_h5_appid'];                 //小程序的id
                                $SSLCERT_PATH = ROOT_PATH.'public/Cert/'.$appletid.'/h5_apiclient_cert.pem';//证书路径
                                $SSLKEY_PATH =  ROOT_PATH.'public/Cert/'.$appletid.'/h5_apiclient_key.pem';//证书路径
                            }elseif($source == 5){
                                $mchid = $app['bdance_h5_mchid'];   //商户号
                                $apiKey = $app['bdance_h5_signkey'];    //商户的秘钥
                                $appid = $app['bdance_h5_appid'];                 //小程序的id
                                $SSLCERT_PATH = ROOT_PATH.'public/Cert/'.$appletid.'/bdance_apiclient_cert.pem';//证书路径
                                $SSLKEY_PATH =  ROOT_PATH.'public/Cert/'.$appletid.'/bdance_apiclient_key.pem';//证书路径
                            }
                            $now = time();
                            $refound_order_id = date("Y", $now) . date("m", $now) . date("d", $now) . date("H", $now) . date("i", $now) . date("s", $now) . rand(1000, 9999);
                            
                            $appkey = $app['appSecret'];            //小程序的秘钥
                            $openid = 'openid';    //申请者的openid
                            $outTradeNo = $order_id_new;
                            $totalFee = $order_infos['pay_money']*100;  //申请了提现多少钱
                            $outRefundNo = $refound_order_id; //商户退款订单号 分次退款需要不同的退款单号
                            $refundFee = $refound_price*100;  //申请了提现多少钱
                            
                            $opUserId = $mchid;//商户号
                            include "WinXinRefund.php";
                            $weixinpay = new WinXinRefund($openid, $outTradeNo, $totalFee, $outRefundNo, $refundFee, $SSLCERT_PATH, $SSLKEY_PATH, $opUserId, $appid, $apiKey);
                            $return = $weixinpay->refund();
                            if (!$return) {
                                throw new \Exception('微信退款失败， 请检查系统设置->微信小程序相关配置');
                            } 
                        }else if($pay_to == 2){//支付到   2 支付宝

                            Vendor('alipaysdk.aop.AopClient');
                            Vendor('alipaysdk.aop.request.AlipayTradeRefundRequest');

                            $now = time();
                            $refound_order_id = date("Y", $now) . date("m", $now) . date("d", $now) . date("H", $now) . date("i", $now) . date("s", $now) . rand(1000, 9999);

                            $aop = new \AopClient ();
                            $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
                            $aop->appId = $app['ali_appID'];
                            $aop->rsaPrivateKey = $app['ali_private_key'];
                            $aop->alipayrsaPublicKey = $app['ali_public_key'];
                            $aop->apiVersion = '1.0';
                            $aop->signType = 'RSA2';
                            $aop->postCharset = 'UTF-8';
                            $aop->format = 'json';
                            $request = new \AlipayTradeRefundRequest ();
                            $request->setBizContent("{'refund_amount':" . $refound_price . ", 'out_trade_no': " . $order_id . ", 'out_request_no': ".$refound_order_id."}");
                            $result = $aop->execute($request);
                            $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
                            $resultCode = $result->$responseNode->code;
                            if (!empty($resultCode) && $resultCode == 10000) {
                                $return = true;
                            } else {
                                throw new \Exception('支付宝退款失败， 请检查系统设置->支付宝小程序设置');
                            }
                        }else if($pay_to == 3){//支付到   3 百度
                             $pay_info = unserialize($order_infos['payment_info']);
                             require_once(ROOT_PATH.'application/api/controller/bdpay/Autoloader.php');
                             $now = time();
                             $refound_order_id = date("Y", $now) . date("m", $now) . date("d", $now) . date("H", $now) . date("i", $now) . date("s", $now) . rand(1000, 9999);
                             $params = [
                                 'method' => 'nuomi.cashier.applyorderrefund',
                                 'orderId' => intval($pay_info['orderId']),
                                 'userId' => intval($pay_info['userId']),
                                 'refundType' => '1',
                                 'refundReason' => '订单退款',
                                 'tpOrderId' => $order_id,
                                 'appKey' => $app['baidu_pay_appkey'],
                                 'applyRefundMoney' => $refound_price * 100,
                                 'bizRefundBatchId' => $refound_order_id
                             ];
                             $rsaSign = \NuomiRsaSign::genSignWithRsa($params, $app['baidu_private_key']);
                             $params['rsaSign'] = $rsaSign;
                             $url = 'https://nop.nuomi.com/nop/server/rest';
                             $res = _Postrequest($url, http_build_query($params));
                             $res = json_decode($res, true);
                             if($res['errno'] == 0){
                                 $return = true;
                             }else{
                                 $this->error('退款失败!请检查系统设置->百度小程序设置');exit;
                             }
                        }else if($pay_to == 4){//支付到   4 QQ
                            $chars = "abcdefghijklmnopqrstuvwxyz0123456789";  
                            $nonce_str = "";  
                            for($i = 0; $i < 32; $i++) {  
                                $nonce_str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
                            }
                            $op_user_passwd = MD5($app['qq_mchid_password']);
                            $appid = $app['qq_appid'];
                            $mch_id = $app['qq_mchid'];
                            $out_trade_no = $order_id;
                            $refund_fee = $refound_price;
                            $now = time();
                            $out_refund_no = date("Y", $now) . date("m", $now) . date("d", $now) . date("H", $now) . date("i", $now) . date("s", $now) . rand(1000, 9999);
                            $sign_str = "appid=".$appid."&mch_id=".$mch_id."&nonce_str=".$nonce_str."&op_user_id=".$mch_id."&op_user_passwd=".$op_user_passwd."&out_refund_no=".$out_refund_no."&out_trade_no=".$out_trade_no."&refund_fee=".$refund_fee;
                            $sign = $sign_str."&key=".$app['qq_mchid_key'];
                            $sign = strtoupper(MD5($sign));
                            $params = "<xml>
                                    <appid>".$appid."</appid>
                                    <mch_id>".$mch_id."</mch_id>
                                    <nonce_str>".$nonce_str."</nonce_str>
                                    <op_user_id>".$mch_id."</op_user_id>
                                    <op_user_passwd>".$op_user_passwd."</op_user_passwd>
                                    <out_refund_no>".$out_refund_no."</out_refund_no>
                                    <out_trade_no>".$out_trade_no."</out_trade_no>
                                    <refund_fee>".$refund_fee."</refund_fee>
                                    <sign>".$sign."</sign>
                                    </xml>";
                            $url = 'https://api.qpay.qq.com/cgi-bin/pay/qpay_refund.cgi';
                            $res = $this -> postXmlSSLCurl($params, $url, 30, $appletid);
                            $res = $this->xmlToArray($res);
                            if($res){
                                if($res['return_code'] == 'SUCCESS'){
                                    $return = true;
                                }else{
                                    $this->error('退款失败!请检查系统设置->QQ小程序设置');exit;
                                }
                            }else{
                                $this->error('退款失败!请检查系统设置->QQ小程序设置');exit;
                            }
                        }
                    }
                }

                $res = Db::name('wd_xcx_main_shop_order_service') ->insert($order_service_data);
                if(!$res){
                    throw new \Exception("数据表操作失败");
                }
                $res = Db::name('wd_xcx_main_shop_order')->where('order_id', $order_id)->where('uniacid', $appletid)->update($data);
                if(!$res){
                    throw new \Exception("数据表操作失败");
                }

                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                $this->error('退款失败，' . $e->getMessage(), Url('Duoproducts/order').'?appletid='.$appletid);
            }

            if($order_sub['source'] == 1){
                $openid = Db::name('wd_xcx_user')->where('uniacid', $appletid)->where('suid', $order_sub['suid'])->value('openid');
                $jsons = [
                    'order_id' => mb_substr($order_service_id, 1),
                    'fprice' => $refound_price,
                    'msg' => '退款成功',
                ];
                $jsons = serialize($jsons);
                sendSubscribe($appletid, 3, $openid, $jsons);
            }
            $this->success("退款成功", Url('Duoproducts/order').'?appletid='.$appletid);
        }else if($op == 'revision'){ //订单改价
            $order_id = input('order_id'); //主订单号
            $is = Db::name("wd_xcx_main_shop_order")->where("order_id", $order_id)->where("uniacid",$appletid)->where('status', 0)->find(); //判断是否存在未付款订单
            if($is){
                $change_price = input('change_price');
                $change_msg = input('change_msg');
                $data = [
                    'change_price' => $change_price,
                    'is_change_price' => 1,
                    'change_price_remark' => $change_msg,
                ];
                $res = Db::name("wd_xcx_main_shop_order")->where("order_id", $order_id)->where("uniacid",$appletid)->where('status', 0)->update($data);
                if($res){
                    $this->success('改价成功', Url('Duoproducts/order').'?appletid='.$appletid);
                }else{
                    $this->error('改价失败，未支付订单不存在', Url('Duoproducts/order').'?appletid='.$appletid);
                }
            }else{
                $this->error('改价失败，未支付订单不存在', Url('Duoproducts/order').'?appletid='.$appletid);
            }
        }else if($op == 'cancel'){ //订单取消
            $order_id = input('order_id'); //主订单号
            $is = Db::name("wd_xcx_main_shop_order")->where("order_id", $order_id)->where("uniacid",$appletid)->where('status', 0)->find(); //判断是否存在未付款订单
            if($is){
                $suid = $is['suid'];
                $source = $is['source'];
                if($is['score_use'] > 0){
                    $xfscore = array(
                        "uniacid" => $appletid,
                        "orderid" => $order_id,
                        'suid' => $suid,
                        'source' => $source,
                        "type" => "add",
                        "score" => $is['score_use'],
                        "message" => "订单取消退回积分",
                        "creattime" => time()
                    );
                }

                Db::startTrans();
                try{
                    //改变订单状态
                    $order_update = Db::name("wd_xcx_main_shop_order")->where('order_id', $order_id) ->update([
                        'status' => -2,
                        'cancel_time' => time()
                    ]);
                    if(!$order_update){
                        throw new \Exception('订单状态改变失败！');
                    }
                    //处理积分
                    if($is['score_use'] > 0){
                        $user_update = Db::name('wd_xcx_superuser') ->where('id', $suid) ->setInc('score', $score_use);
                        $back_score = Db::name('wd_xcx_score') ->insert($xfscore);
                        if(!$user_update || !$back_score){
                            throw new \Exception('积分返还失败！');
                        }
                    }

                    //处理优惠券
                    if($is['coupon_id'] > 0){
                        $cou_update = Db::name('wd_xcx_coupon_user') ->where('id', $is['coupon_id']) ->update([
                            'flag' => 0,
                            'utime' => 0
                        ]);
                        if(!$cou_update){
                            throw new \Exception('优惠券返还失败！');
                        }
                    }

                    $orderItems = Db::name("wd_xcx_main_shop_order_item")->where("uniacid",$appletid)->where("order_id",$order_id)->select();
                    //改变子订单状态
                    foreach ($orderItems as $item){
                        $order_item_logs = unserialize($item['order_item_log']);
                        $order_item_log = ['time'=>time(), 'log'=>'订单未支付，取消订单'];
                        array_push($order_item_logs, $order_item_log);
                        $update_item = Db::name('wd_xcx_main_shop_order_item') ->where('order_id', $order_id) ->update([
                            'status' => -2,
                            'cancel_time' => time(),
                            'order_item_log' => serialize($order_item_logs)
                        ]);
                        if(!$update_item){
                            throw new \Exception('子订单状态更新失败！');
                        }
                        //处理库存
                        $this ->toDealWithInventorySales($item['pro_id'], $item['pro_type_id'], $item['num']);
                    }
                    

                    Db::commit();
                }catch(\Exception $e){
                    Db::rollback();
                    return json_encode(['data' => ['error' => 3, 'msg' => $e ->getMessage()]]);
                }
                $this->success('取消成功', Url('Duoproducts/order').'?appletid='.$appletid);
                
            }else{
                $this->error('取消失败，未支付订单不存在', Url('Duoproducts/order').'?appletid='.$appletid);
            }
        }else if($op == 'deliver_change'){ //物流修改
            $express = input('express');  //快递公司
            $express_no = input('express_no'); //快递单号
            $data = [
                'express' => $express,
                'express_no' => $express_no,
            ];
            $order_item_id = input('order_id');
            $order_item_info = Db::name('wd_xcx_main_shop_order_item')->where('order_item_id', $order_item_id)->field('order_id, order_item_log')->find();
            $order_item_log = unserialize($order_item_info['order_item_log']);
            $log = ['time'=>time(), 'log'=>'物流修改'];
            array_push($order_item_log, $log);
            $order_item_log = serialize($order_item_log);
            $data['order_item_log'] = $order_item_log; //操作日志
            $res = Db::name('wd_xcx_main_shop_order_item')->where('order_item_id', $order_item_id)->where('uniacid', $appletid)->update($data);
            if($res){
                $this->success("发货成功", Url('Duoproducts/orderdetail').'?appletid='.$appletid.'&order_item_id='.$order_item_id);
            }else{
                $this->error("修改失败", Url('Duoproducts/orderdetail').'?appletid='.$appletid.'&order_item_id='.$order_item_id);
            }
        }else if($op == 'remark'){ //商家备注
            $business_remark = input('business_remark') ? input('business_remark') : '';  //快递公司
            $data = [
                'business_remark' => $business_remark,
            ];
            $order_item_id = input('order_id');
            $order_item_info = Db::name('wd_xcx_main_shop_order_item')->where('order_item_id', $order_item_id)->field('business_remark, order_item_log')->find();
            $order_item_log = unserialize($order_item_info['order_item_log']);
            $log = ['time'=>time(), 'log'=>'修改备注'];
            array_push($order_item_log, $log);
            $order_item_log = serialize($order_item_log);
            $data['order_item_log'] = $order_item_log; //操作日志
            $res = Db::name('wd_xcx_main_shop_order_item')->where('order_item_id', $order_item_id)->where('uniacid', $appletid)->update($data);
            if($res){
                $this->success("商家备注修改成功", Url('Duoproducts/orderdetail').'?appletid='.$appletid.'&order_item_id='.$order_item_id);
            }else{
                $this->error("商家备注修改失败", Url('Duoproducts/orderdetail').'?appletid='.$appletid.'&order_item_id='.$order_item_id);
            }
        }
    }

     /**
     * @param $pro_id
     * @param $type_id
     * @param $num
     * @param int $do
     * @throws Exception
     */
    private function toDealWithInventorySales($pro_id, $type_id, $num){
        if($type_id != -1){
            Db::name('wd_xcx_duo_products_type_value') ->where([
                'id' => $type_id,
                'salenum' => ['EGT', $num],
            ]) ->setDec('salenum', $num);  //减规格值销量
            Db::name('wd_xcx_duo_products_type_value') ->where([
                'id' => $type_id,
            ]) ->setInc('kc', $num);  //加规格值库存
            Db::name('wd_xcx_products') ->where([
                'id' => $pro_id,
                'sale_tnum' => ['EGT', $num],
            ]) ->setDec('sale_tnum', $num);  //减商品的真实销量
        }else{
            Db::name('wd_xcx_products') ->where([
                'id' => $pro_id,
                'sale_tnum' => ['EGT', $num],
            ]) ->setDec('sale_tnum', $num);  //减商品的真实销量
            Db::name('wd_xcx_products') ->where('id', $pro_id) ->setInc('pro_kc', $num);
        }
    }

    // 订单详情页
    public function orderdetail(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);

                $order_item_id = input('order_item_id');
                $orderItem = Db::name('wd_xcx_main_shop_order_item')->where("uniacid",$appletid)->where("order_item_id", $order_item_id)->find();
                if($orderItem){
                    $orderItem['order_item_log'] = array_reverse(unserialize($orderItem['order_item_log']));
                    $orderItem['pro_prices'] = sprintf("%01.2f", $orderItem['pro_price'] * $orderItem['num']);
                    $orderItem['pro_price_all'] = sprintf("%01.2f", $orderItem['pro_discounts_price'] * $orderItem['num']);
                    $orderItem['fx_info'] = Db::name('wd_xcx_fx_ls')->where("uniacid",$appletid)->where("order_id", $order_item_id)->find();
                    if($orderItem['fx_info']){
                        if($orderItem['fx_info']['parent_id'] > 0){
                            $orderItem['fx_info']['parent_info'] = getNameAvatar($orderItem['fx_info']['parent_id'], $appletid);
                        }
                        if($orderItem['fx_info']['p_parent_id'] > 0){
                            $orderItem['fx_info']['p_parent_info'] = getNameAvatar($orderItem['fx_info']['p_parent_id'], $appletid);
                        }
                        if($orderItem['fx_info']['p_p_parent_id'] > 0){
                            $orderItem['fx_info']['p_p_parent_info'] = getNameAvatar($orderItem['fx_info']['p_p_parent_id'], $appletid);
                        }
                    }
                    $order = Db::name('wd_xcx_main_shop_order')->where("uniacid",$appletid)->where("order_id", $orderItem['order_id'])->find();
                    $orderItem['pay_type'] = $order['pay_type'];
                    $orderItem['pay_to'] = $order['pay_to'];
                    $orderItem['pay_time'] = $order['pay_time'];
                    if($orderItem['delivery_type'] == 1){
                        $orderItem['address_info'] = unserialize($order['address_info']);
                    }else{
                        $orderItem['self_taking_info'] = unserialize($order['self_taking_info']);
                        $orderItem['self_taking_info']['self_taking_shop_info'] = unserialize($orderItem['self_taking_info']['self_taking_shop_info']);
                    }
                    $orderItem['pro_fx'] = unserialize($orderItem['pro_fx']);
                }else{
                    $this->error("订单已删除或不存在", Url('Duoproducts/order').'?appletid='.$appletid);
                }
                $this->assign('orderItem', $orderItem);
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
            return $this->fetch('orderdetail');
        }else{
            $this->redirect('Login/index');
        }
    }

    public function orderdown(){
        $appletid = input("appletid");
        $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
        if(!$res){
            $this->error("找不到对应的小程序！");
        }
        $this->assign('applet',$res);

        $where = [];
        $source = intval(input('source')); //订单来源：source:1微信 2支付宝 3H5 4百度 5字节跳动 6QQ
        if($source > 0){
            $where['source'] = $source;
        }

        
        $status = intval(input('status')); // 订单状态：status -3 付款后取消  -2 未付款取消  -1 未支付过期 0 未支付  1 待发货 2 待核销(全部发货待收货) 3 全部核销收货  4 付款后总订单取消申请 5 完成 
        if($status > 0){
            if($status == 1){ //前台选择 待付款
                $where['status'] = 0;
            }else if($status == 2){ //前台选择 待发货
                $where['status'] = 1;
            }else if($status == 3){ //前台选择 待收货
                $where['status'] = 2;
                $where['delivery_type'] = 1;
            }else if($status == 4){ //前台选择 待核销
                $where['status'] = 2;
                $where['delivery_type'] = 2;
            }else if($status == 5){ //前台选择 交易成功
                $where['status'] = 5;
            }else if($status == 6){ //前台选择 交易关闭
                $where['status'] = ['in', [-3, -2, -1]];
            }
        }

        $delivery_type = intval(input('delivery_type')); //订单配送方式：delivery_type:1快递 2自取
        if($delivery_type > 0){
            $where['delivery_type'] = $delivery_type;
        }

        $screen_starttime = input('screen_starttime');
        $screen_endtime = input('screen_endtime');
        if(!empty($screen_starttime) && !empty($screen_endtime)){//下单时间开始
            $where['creat_time'] = ['between', strtotime($screen_starttime).','.strtotime($screen_endtime)];
        }else if(!empty($screen_starttime)){
            $where['creat_time'] = ['>=', strtotime($screen_starttime)];
        }else if(!empty($screen_endtime)){//下单时间结束
            $where['creat_time'] = ['<=', strtotime($screen_endtime)];
        }

        $screen_type = intval(input('screen_type')); //查询类型 1订单号 2手机号
        $screen_keys = trim(input('screen_keys'));
        if($screen_type > 0 && $screen_keys){
            if($screen_type == 1){
                $where['order_id'] = ['like', '%'.$screen_keys.'%'];
            }else{
                $where['buyer_mobile'] = ['like', '%'.$screen_keys.'%'];
            }
        }

        require_once ROOT_PATH.'public/plugin/PHPExcel/PHPExcel.php';
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()->setCreator("导出订单列表")
                ->setLastModifiedBy("订单列表")
                ->setTitle("导出订单列表")
                ->setSubject("导出订单列表")
                ->setDescription("导出订单列表")
                ->setKeywords("导出订单列表")
                ->setCategory("导出订单列表");
        $objPHPExcel->getActiveSheet()->setCellValue('A1', '总订单号');
        $objPHPExcel->getActiveSheet()->setCellValue('B1', '下单时间');
        $objPHPExcel->getActiveSheet()->setCellValue('C1', '下单人');
        $objPHPExcel->getActiveSheet()->setCellValue('D1', '订单来源');
        $objPHPExcel->getActiveSheet()->setCellValue('E1', '配送信息');
        $objPHPExcel->getActiveSheet()->setCellValue('F1', '商品名称');
        $objPHPExcel->getActiveSheet()->setCellValue('G1', '单价*数量');
        $objPHPExcel->getActiveSheet()->setCellValue('H1', '总价');
        $objPHPExcel->getActiveSheet()->setCellValue('I1', '订单状态');
        $objPHPExcel->getActiveSheet()->setCellValue('J1', '优惠信息');
        $objPHPExcel->getActiveSheet()->setCellValue('K1', '订单实付');
        $objPHPExcel->getActiveSheet()->setCellValue('L1', '表单信息');
        $objPHPExcel->getActiveSheet()->setCellValue('M1', '订单备注');

        $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(20);//所有单元格（列）默认宽度
        $objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(60);//所有单元格（行）默认宽度
        //垂直居中
        $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        //查询主订单列表信息
        $orders = Db::name('wd_xcx_main_shop_order')->where('uniacid', $appletid)->where($where)->order('id desc')->select();
        $idx = 2;
        foreach($orders as $k => $v){
            $orderItems = Db::name('wd_xcx_main_shop_order_item')->where('uniacid', $appletid)->where('order_id', $v['order_id'])->order('id desc')->select();
            $count = count($orderItems);
            $userinfo = getNameAvatar($v['suid'], $appletid, 1);
            if($v['source'] == 1){
                $source = "微信";
            }else if($v['source'] == 2){
                $source = "支付宝";
            }else if($v['source'] == 3){
                $source = "H5";
            }else if($v['source'] == 4){
                $source = "百度";
            }else if($v['source'] == 5){
                $source = "字节跳动";
            }else if($v['source'] == 6){
                $source = "QQ";
            }

            $str = '';
            $yh = '';
            $forminfo = '';
            if($v['delivery_type'] == 1){ //快递配送
                $address_info = unserialize($v['address_info']);
                $address = explode(' ', $address_info['address']);
                $str ="快递配送\r\n";
                $str .= "姓名：".$address_info['name']."\r\n电话：".$address_info['mobile']."\r\n地址：".$address[0].$address[1].$address[2].$address_info['more_address'];
            }else{ //到店自取
                $self_taking_info = unserialize($v['self_taking_info']);
                $self_taking_shop_info = unserialize($self_taking_info['self_taking_shop_info']);

                $str ="到店自取\r\n";
                $str .= "门店名称：".$self_taking_shop_info['title']."\r\n门店电话：".$self_taking_shop_info['tel']."\r\n门店营业时间：".$self_taking_shop_info['times']."\r\n门店地址：".$self_taking_shop_info['province'].$self_taking_shop_info['city'].$self_taking_shop_info['country']."\r\n预留电话：".$self_taking_info['self_taking_contact']."\r\n自提时间：".$self_taking_info['self_taking_time'];

            }
            if($v['pay_type'] == 1){
                $type = "余额支付：";
            }else{
                if($v['pay_to'] == 1){
                    $type = "微信支付：";
                }else if($v['pay_to'] == 2){
                    $type = "支付宝支付：";
                }else if($v['pay_to'] == 3){
                    $type = "百度支付：";
                }else if($v['pay_to'] == 4){
                    $type = "QQ支付：";
                }
            }
            if($count > 1){
                $c = $idx + $count -1;
                $objPHPExcel->getActiveSheet()->mergeCells('A'.$idx.':A'.$c);//合并单元格（如果要拆分单元格是需要先合并再拆分的，否则程序会报错）

                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$idx, ' '.$v['order_id'], 's');

                $objPHPExcel->getActiveSheet()->mergeCells('B'.$idx.':B'.$c);//合并单元格（如果要拆分单元格是需要先合并再拆分的，否则程序会报错）
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B'.$idx, date('Y-m-d H:i:s', $v['creat_time']));  //下单时间

                $objPHPExcel->getActiveSheet()->mergeCells('C'.$idx.':C'.$c);//合并单元格（如果要拆分单元格是需要先合并再拆分的，否则程序会报错）
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C'.$idx, $userinfo['nickname']);  //用户

                $objPHPExcel->getActiveSheet()->mergeCells('D'.$idx.':D'.$c);//合并单元格（如果要拆分单元格是需要先合并再拆分的，否则程序会报错）
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('D'.$idx, $source);  //来源
                
                $objPHPExcel->getActiveSheet()->getStyle("E".$idx)->getAlignment()->setWrapText(TRUE);    //内容换行
                $objPHPExcel->getActiveSheet()->mergeCells('E'.$idx.':E'.$c);//合并单元格（如果要拆分单元格是需要先合并再拆分的，否则程序会报错）
				// $objPHPExcel->setActiveSheetIndex(0)->getStyle('E'. $idx)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        		$objPHPExcel->setActiveSheetIndex(0)->getStyle('E'. $idx)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);


                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('E'.$idx, $str);  //配送信息

                foreach ($orderItems as $ki => $vi) {
                    $n = $idx + $ki;
                    $pro = $vi['pro_title']."\r\n";
                    if($vi['pro_attr']){
                        $pro .= '规格：'.$vi['pro_attr'];
                    }
                    $objPHPExcel->setActiveSheetIndex(0)->getStyle('F'. $n)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    $objPHPExcel->getActiveSheet()->getStyle("F".$n)->getAlignment()->setWrapText(TRUE); 
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('F'.$n, $pro);  //产品标题 规格
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('G'.$n, $vi['pro_price'].'*'.$vi['num']);
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('H'.$n, sprintf("%01.2f", $vi['pro_discounts_price'] * $vi['num'])); 
                    if($vi['status'] == -4 || $vi['status'] == -3 || $vi['status'] == -2 || $vi['status'] == -1){
                        $status = '已取消';
                    }else if($vi['status'] == 0){
                        $status = '待付款';
                    }else if($vi['status'] == 1){
                        $status = '待发货';
                    }else if($vi['status'] == 2 && $vi['delivery_type'] == 1){
                        $status = '待收货';
                    }else if($vi['status'] == 2 && $vi['delivery_type'] == 2){
                        $status = '待核销';
                    }else if($vi['status'] == 3 && $vi['delivery_type'] == 1){
                        $status = '已收货';
                    }else if($vi['status'] == 3 && $vi['delivery_type'] == 2){
                        $status = '已核销';
                    }else if($vi['status'] == 4 || $vi['status'] == 5){
                        $status = '退款中';
                    }else if($vi['status'] == 6){
                        $status = '退款退货中';
                    }else if($vi['status'] == 7){
                        $status = '已完成';
                    }
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('I'.$n, $status); 
                }

                if($v['coupon_id'] > 0){
                    $yh .= '优惠券：减￥'.$v['coupon_use']."\r\n";
                }
                if($v['score_use'] > 0){
                    $yh .= '积分抵扣：'.$v['score_use'].'积分抵￥'.$v['score_money'];
                }
                $objPHPExcel->getActiveSheet()->mergeCells('J'.$idx.':J'.$c);//合并单元格（如果要拆分单元格是需要先合并再拆分的，否则程序会报错）
                $objPHPExcel->getActiveSheet()->getStyle("J".$idx)->getAlignment()->setWrapText(TRUE); 
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('J'.$idx, $yh); 

                $pay_money = $v['is_change_price'] ? $v['change_price'] : $v['pay_money'];
                $objPHPExcel->getActiveSheet()->mergeCells('K'.$idx.':K'.$c);//合并单元格（如果要拆分单元格是需要先合并再拆分的，否则程序会报错）
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('K'.$idx, $type.'￥'.$pay_money); 

                $form_arr = $v['formlist_val'] ? unserialize($v['formlist_val']) : '';
                if($form_arr){
                    foreach ($form_arr as $kk => $vv) {
                        if($vv['type']== 3){
                            $type3_info = "";
                            foreach ($vv['val'] as $key => $value) {
                                $type3_info = $type3_info.$value.",";
                            }

                            $forminfo = $forminfo.$vv['name'].":".$type3_info.";\r\n";
                        }
                        if($vv['type']== 5){
                            $type5_info = "";
                            foreach ($vv['z_val'] as $key => $value) {
                                $type5_info = $type5_info.remote($appletid, $value, 1).",";
                            }

                            $forminfo = $forminfo.$vv['name'].":".$type5_info.";\r\n";
                        }
                        if($vv['type'] != 5 && $vv['type'] != 3){
                            $forminfo = $forminfo.$vv['name']."：".$vv['val'].";\r\n";
                        }
                    }
                }

                $objPHPExcel->getActiveSheet()->getStyle("L".$idx)->getAlignment()->setWrapText(TRUE); 
                $objPHPExcel->setActiveSheetIndex(0)->getStyle('L'. $idx)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                $objPHPExcel->getActiveSheet()->mergeCells('L'.$idx.':L'.$c);//合并单元格（如果要拆分单元格是需要先合并再拆分的，否则程序会报错）
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('L'.$idx, $forminfo);  

                $objPHPExcel->getActiveSheet()->mergeCells('M'.$idx.':M'.$c);//合并单元格（如果要拆分单元格是需要先合并再拆分的，否则程序会报错）
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('M'.$idx, $v['user_remark']);  
                $idx = $c + 1;
            }else{
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('A'.$idx, ' '.$v['order_id'],'s');
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('B'.$idx, date('Y-m-d H:i:s', $v['creat_time']));
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('C'.$idx, $userinfo['nickname']); //用户
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('D'.$idx, $source);
                $objPHPExcel->getActiveSheet()->getStyle("E".$idx)->getAlignment()->setWrapText(TRUE);    //内容换行
				// $objPHPExcel->setActiveSheetIndex(0)->getStyle('E'. $idx)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        		$objPHPExcel->setActiveSheetIndex(0)->getStyle('E'. $idx)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                $objPHPExcel->getActiveSheet()->setCellValueExplicit('E'.$idx, $str);
                foreach ($orderItems as $ki => $vi) {
                    $pro = $vi['pro_title']."\r\n";
                    if($vi['pro_attr']){
                        $pro .= '规格：'.$vi['pro_attr'];
                    }
                    $objPHPExcel->setActiveSheetIndex(0)->getStyle('F'. $idx)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    $objPHPExcel->getActiveSheet()->getStyle("F".$idx)->getAlignment()->setWrapText(TRUE); 
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('F'.$idx, $pro);  //产品标题 规格
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('G'.$idx, $vi['pro_price'].'*'.$vi['num']);
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('H'.$idx, sprintf("%01.2f", $vi['pro_discounts_price'] * $vi['num'])); 
                    if($vi['status'] == -4 || $vi['status'] == -3 || $vi['status'] == -2 || $vi['status'] == -1){
                        $status = '已取消';
                    }else if($vi['status'] == 0){
                        $status = '待付款';
                    }else if($vi['status'] == 1){
                        $status = '待发货';
                    }else if($vi['status'] == 2 && $vi['delivery_type'] == 1){
                        $status = '待收货';
                    }else if($vi['status'] == 2 && $vi['delivery_type'] == 2){
                        $status = '待核销';
                    }else if($vi['status'] == 3 && $vi['delivery_type'] == 1){
                        $status = '已收货';
                    }else if($vi['status'] == 3 && $vi['delivery_type'] == 2){
                        $status = '已核销';
                    }else if($vi['status'] == 4 || $vi['status'] == 5){
                        $status = '退款中';
                    }else if($vi['status'] == 6){
                        $status = '退款退货中';
                    }else if($vi['status'] == 7){
                        $status = '已完成';
                    }

                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('I'.$idx, $status); 
                }
                if($v['coupon_id'] > 0){
                    $yh .= '优惠券：减￥'.$v['coupon_use']."\r\n";
                }
                if($v['score_use'] > 0){
                    $yh .= '积分抵扣：'.$v['score_use'].'积分抵'.$v['score_money'];
                }
                $objPHPExcel->getActiveSheet()->getStyle("J".$idx)->getAlignment()->setWrapText(TRUE); 
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('J'.$idx, $yh);

                $pay_money = $v['is_change_price'] ? $v['change_price'] : $v['pay_money'];
      
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('K'.$idx, $type.'￥'.$pay_money);
                $form_arr = $v['formlist_val'] ? unserialize($v['formlist_val']) : '';
                if($form_arr){
                    foreach ($form_arr as $kk => $vv) {
                        if($vv['type']== 3){
                            $type3_info = "";
                            foreach ($vv['val'] as $key => $value) {
                                $type3_info = $type3_info.$value.",";
                            }

                            $forminfo = $forminfo.$vv['name'].":".$type3_info.";\r\n";
                        }
                        if($vv['type']== 5){
                            $type5_info = "";
                            foreach ($vv['z_val'] as $key => $value) {
                                $type5_info = $type5_info.remote($appletid, $value, 1).",";
                            }

                            $forminfo = $forminfo.$vv['name'].":".$type5_info.";\r\n";
                        }
                        if($vv['type'] != 5 && $vv['type'] != 3){
                            $forminfo = $forminfo.$vv['name']."：".$vv['val'].";\r\n";
                        }
                    }
                }

                $objPHPExcel->getActiveSheet()->getStyle("L".$idx)->getAlignment()->setWrapText(TRUE); 
                $objPHPExcel->setActiveSheetIndex(0)->getStyle('L'. $idx)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('L'.$idx, $forminfo);
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('M'.$idx, $v['user_remark']);
                $idx++;
            }
        }
        $objPHPExcel->getActiveSheet()->setTitle('导出订单列表');
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="订单列表.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

    //售后订单
    public function service(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);

                $where = [];
                $source = intval(input('source')); //订单来源：source:1微信 2支付宝 3H5 4百度 5字节跳动 6QQ
                if($source > 0){
                    $where['source'] = $source;
                }
                
                $status = intval(input('status')); // 订单状态：-1主动撤销 0创建 1同意 2拒绝 
                if($status > 0){
                    if($status == 1){ //待处理
                        $where['status'] = 0;
                    }else if($status == 2){ //处理中
                        $where['status'] = 1;
                        $where['apply_type'] = 1;
                    }else if($status == 3){ //已完成
                        $where['status'] = ['in', [-1, 1, 2, 3]];
                    }
                }

                $screen_starttime = input('screen_starttime');
                $screen_endtime = input('screen_endtime');
                if(!empty($screen_starttime) && !empty($screen_endtime)){//下单时间开始
                    $where['creat_time'] = ['between', strtotime($screen_starttime).','.strtotime($screen_endtime)];
                }else if(!empty($screen_starttime)){
                    $where['creat_time'] = ['>=', strtotime($screen_starttime)];
                }else if(!empty($screen_endtime)){//下单时间结束
                    $where['creat_time'] = ['<=', strtotime($screen_endtime)];
                }

                $screen_keys = trim(input('screen_keys'));
                if(!empty($screen_keys)){
                    $where['order_service_id'] = ['like', '%'.$screen_keys.'%'];
                }
                $list = Db::name("wd_xcx_main_shop_order_service")->where($where)->where("uniacid",$appletid)->order("id desc")->paginate(10, false, ['query' => ['appletid' => $appletid, 'source' => $source, 'status' => $status, 'screen_starttime' => $screen_starttime, 'screen_endtime' => $screen_endtime, 'screen_keys' => $screen_keys]]);
          
                $lists = $list->toArray()['data'];
                foreach ($lists as $k => $v) {
                    $userinfo = getNameAvatar($v['suid'], $appletid);
                    $lists[$k]['nickname'] = $userinfo['nickname'];

                    $where2 = [];
                    if($v['is_item'] == 1){ //子订单
                        $where2['order_item_id'] = $v['order_item_id'];
                        $order_item = Db::name('wd_xcx_main_shop_order_item')->where("uniacid", $appletid)->where($where2)->find();
                        $order = Db::name('wd_xcx_main_shop_order')->where("uniacid", $appletid)->where('order_id', $order_item['order_id'])->find();
                    }else{ //主订单
                        $where2['order_id'] = $v['order_item_id'];
                        $order = Db::name('wd_xcx_main_shop_order')->where("uniacid", $appletid)->where($where2)->find();
                    }
                    $lists[$k]['total_can_tui_money'] = $order['total_can_tui_money'];

                    if($order['delivery_type'] == 1){ //发货
                        $address_info = unserialize($order['address_info']);
                        $lists[$k]['add_info'] = ['快递发货', $address_info['mobile']];
                    }else{ //自取
                        $self_taking_info = unserialize($order['self_taking_info']);
                        $lists[$k]['add_info'] = ['到店自取', $self_taking_info['self_taking_contact']];
                    }
                    $orderItems = Db::name('wd_xcx_main_shop_order_item')->where("uniacid", $appletid)->where($where2)->select();
                    $lists[$k]['orderItems_count'] = count($orderItems);
                    foreach ($orderItems as $ks => $vs) {
                        $orderItems[$ks]['pro_thumb'] = remote($appletid, $vs['pro_thumb'], 1);
                    }
                    $lists[$k]['orderItems'] = $orderItems;
                }

                $refund_address = Db::name("wd_xcx_refund_address")->where("uniacid", $appletid)->select();
                $this->assign('refund_address', $refund_address);

                $this->assign('list', $list);
                $this->assign('lists', $lists);
                
                $this->assign('source', $source);
                $this->assign('status', $status);
                $this->assign('screen_starttime', $screen_starttime);
                $this->assign('screen_endtime', $screen_endtime);
                $this->assign('screen_keys', $screen_keys);

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
            return $this->fetch('service');
        }else{
            $this->redirect('Login/index');
        }
    }

    public function servicedown(){
        $appletid = input("appletid");
        $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
        if(!$res){
            $this->error("找不到对应的小程序！");
        }
        $this->assign('applet',$res);

        $where = [];
        $source = intval(input('source')); //订单来源：source:1微信 2支付宝 3H5 4百度 5字节跳动 6QQ
        if($source > 0){
            $where['source'] = $source;
        }
        
        $status = intval(input('status')); // 订单状态：-1主动撤销 0创建 1同意 2拒绝 
        if($status > 0){
            if($status == 1){ //待处理
                $where['status'] = 0;
            }else if($status == 2){ //处理中
                $where['status'] = 1;
                $where['apply_type'] = 1;
            }else if($status == 3){ //已完成
                $where['status'] = ['in', [-1, 1, 2, 3]];
            }
        }

        $screen_starttime = input('screen_starttime');
        $screen_endtime = input('screen_endtime');
        if(!empty($screen_starttime) && !empty($screen_endtime)){//下单时间开始
            $where['creat_time'] = ['between', strtotime($screen_starttime).','.strtotime($screen_endtime)];
        }else if(!empty($screen_starttime)){
            $where['creat_time'] = ['>=', strtotime($screen_starttime)];
        }else if(!empty($screen_endtime)){//下单时间结束
            $where['creat_time'] = ['<=', strtotime($screen_endtime)];
        }

        $screen_keys = trim(input('screen_keys'));
        if(!empty($screen_keys)){
            $where['order_service_id'] = ['like', '%'.$screen_keys.'%'];
        }

        require_once ROOT_PATH.'public/plugin/PHPExcel/PHPExcel.php';
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()->setCreator("导出售后订单列表")
                ->setLastModifiedBy("售后订单")
                ->setTitle("导出售后订单列表")
                ->setSubject("导出售后订单列表")
                ->setDescription("导出售后订单列表")
                ->setKeywords("导出售后订单列表")
                ->setCategory("导出售后订单列表");
        $objPHPExcel->getActiveSheet()->setCellValue('A1', '售后单号');
        $objPHPExcel->getActiveSheet()->setCellValue('B1', '提交时间');
        $objPHPExcel->getActiveSheet()->setCellValue('C1', '商品信息');
        $objPHPExcel->getActiveSheet()->setCellValue('D1', '售后类型');
        $objPHPExcel->getActiveSheet()->setCellValue('E1', '退款金额');
        $objPHPExcel->getActiveSheet()->setCellValue('F1', '退款理由');
        $objPHPExcel->getActiveSheet()->setCellValue('G1', '联系方式');
        $objPHPExcel->getActiveSheet()->setCellValue('H1', '售后状态');

        $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(20);//所有单元格（列）默认宽度
        $objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(60);//所有单元格（行）默认宽度
        //垂直居中
        $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        //查询主订单列表信息
        $service_orders = Db::name("wd_xcx_main_shop_order_service")->where($where)->where("uniacid",$appletid)->order("id desc")->select();

        $idx = 2;
        foreach($service_orders as $k => $v){
            if($v['apply_type'] == 0){ //售后类型 0 仅退款  1  退货退款
                $type = "仅退款";
            }else{
                $type = "退货退款";
            }
            $count = 0;
            if($v['is_item'] == 1){ //1子订单
                $orderItem = Db::name('wd_xcx_main_shop_order_item')->where('uniacid', $appletid)->where('order_item_id', $v['order_item_id'])->find();
                $order = Db::name('wd_xcx_main_shop_order')->where('uniacid', $appletid)->where('order_id', $orderItem['order_id'])->find();
            }else{ //2主订单
                $order = Db::name('wd_xcx_main_shop_order')->where('uniacid', $appletid)->where('order_id', $v['order_item_id'])->find();
                $orderItems = Db::name('wd_xcx_main_shop_order_item')->where('uniacid', $appletid)->where('order_id', $v['order_item_id'])->select();
                $count = count($orderItems);
            }

            if($count > 1){
                $c = $idx + $count -1;

                //售后单号
                $objPHPExcel->getActiveSheet()->mergeCells('A'.$idx.':A'.$c);//合并单元格（如果要拆分单元格是需要先合并再拆分的，否则程序会报错）
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$idx, ' '.$v['order_service_id'], 's');

                //提交时间
                $objPHPExcel->getActiveSheet()->mergeCells('B'.$idx.':B'.$c);//合并单元格（如果要拆分单元格是需要先合并再拆分的，否则程序会报错）
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B'.$idx, date('Y-m-d H:i:s', $v['creat_time']));  //下单时间

                //商品信息
                foreach ($orderItems as $ki => $vi) {
                    $n = $idx + $ki;
                    $pro = $vi['pro_title']."\r\n";
                    if($vi['pro_attr']){
                        $pro .= '规格：'.$vi['pro_attr'];
                    }
                    $objPHPExcel->setActiveSheetIndex(0)->getStyle('C'. $n)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    $objPHPExcel->getActiveSheet()->getStyle("C".$n)->getAlignment()->setWrapText(TRUE); 
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C'.$n, $pro);  //产品标题 规格
                }

                //退款类型
                $objPHPExcel->getActiveSheet()->mergeCells('D'.$idx.':D'.$c);//合并单元格（如果要拆分单元格是需要先合并再拆分的，否则程序会报错）
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('D'.$idx, $type); 

                //退款金额
                $refund_money = $v['change_refund_money'] != 0.00 ? $v['change_refund_money'] : $v['refund_money'];
                $objPHPExcel->getActiveSheet()->mergeCells('E'.$idx.':E'.$c);//合并单元格（如果要拆分单元格是需要先合并再拆分的，否则程序会报错）
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('E'.$idx, '￥'.$refund_money); 

                //退款理由
                $objPHPExcel->getActiveSheet()->mergeCells('F'.$idx.':F'.$c);//合并单元格（如果要拆分单元格是需要先合并再拆分的，否则程序会报错）
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('F'.$idx, $v['apply_remark']); 

                //联系方式
                if($order['delivery_type'] == 1){ //订单配送方式  1 快递  2 自取
                    $address_info = unserialize($order['address_info']);
                    $mobile = $address_info['mobile'];
                }else{
                    $self_taking_info = unserialize($order['self_taking_info']);
                    $mobile = $self_taking_info['self_taking_contact'];
                }

                $objPHPExcel->getActiveSheet()->mergeCells('G'.$idx.':G'.$c);//合并单元格（如果要拆分单元格是需要先合并再拆分的，否则程序会报错）
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('G'.$idx, $mobile); 

                //售后状态
                if($v['status'] == 0){
                    $status = '待处理';
                }else if($v['status'] == -1 || $v['status'] == 1 || $v['status'] == 2 || $v['status'] == 3){
                    if($v['status'] == 1 && $v['apply_type'] == 1){
                        if($v['express']){
                            $status = '同意退款，买家退货已发货';
                        }else {
                            $status = '同意退款，买家退货未发货';
                        }
                    }else{
                        $status = '已完成';
                    }
                }
                $objPHPExcel->getActiveSheet()->mergeCells('H'.$idx.':H'.$c);//合并单元格（如果要拆分单元格是需要先合并再拆分的，否则程序会报错）
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('H'.$idx, $status); 

                $idx = $c + 1;
            }else{
                //售后单号
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('A'.$idx, ' '.$v['order_service_id'],'s');
                
                //提交时间
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('B'.$idx, date('Y-m-d H:i:s', $v['creat_time']));

                //商品信息
                $pro = $orderItem['pro_title']."\r\n";
                if($orderItem['pro_attr']){
                    $pro .= '规格：'.$orderItem['pro_attr'];
                }
                $objPHPExcel->setActiveSheetIndex(0)->getStyle('C'. $idx)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                $objPHPExcel->getActiveSheet()->getStyle("C".$idx)->getAlignment()->setWrapText(TRUE); 
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('C'.$idx, $pro);

                //退款类型
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('D'.$idx, $type);

                //退款金额
                $refund_money = $v['change_refund_money'] != 0.00 ? $v['change_refund_money'] : $v['refund_money'];
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('E'.$idx, '￥'.$refund_money); 

                //退款理由
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('F'.$idx, $v['apply_remark']); 

                //联系方式
                if($order['delivery_type'] == 1){ //订单配送方式  1 快递  2 自取
                    $address_info = unserialize($order['address_info']);
                    $mobile = $address_info['mobile'];
                }else{
                    $self_taking_info = unserialize($order['self_taking_info']);
                    $mobile = $self_taking_info['self_taking_contact'];
                }
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('G'.$idx, $mobile);

                //售后状态
                if($v['status'] == 0){
                    $status = '待处理';
                }else if($v['status'] == -1 || $v['status'] == 1 || $v['status'] == 2 || $v['status'] == 3){
                    if($v['status'] == 1 && $v['apply_type'] == 1){
                        if($v['express']){
                            $status = '同意退款，买家退货已发货';
                        }else {
                            $status = '同意退款，买家退货未发货';
                        }
                    }else{
                        $status = '已完成';
                    }
                }
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('H'.$idx, $status);

                $idx++;
            }
        }
        $objPHPExcel->getActiveSheet()->setTitle('导出售后订单列表');
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="售后订单列表.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

    //售后订单功能操作
    public function func_service(){
        $appletid = input('appletid');
        $order_service_id = input('order_service_id');
        $popup_type = input('popup_type'); //confirm确认收货  agreesale同意  refusesale拒绝
        if($popup_type == 'refusesale'){
            $refuse_sale = trim(input('refuse_sale'));
            $is = Db::name('wd_xcx_main_shop_order_service')->where('order_service_id', $order_service_id)->where('uniacid', $appletid)->where('status', 0)->find(); //判断处理中订单是否存在
            if($is){
                Db::startTrans();
                try {
                    if($is['is_item'] == 1){ //子订单
                        $orderItem = Db::name("wd_xcx_main_shop_order_item")->where("uniacid", $appletid)->where("order_item_id", $is["order_item_id"])->field("order_id, order_item_log, refund_num, delivery_type")->find();
                        $order_item_logs = unserialize($orderItem["order_item_log"]);
                        $order_item_log = ['time'=>time(), 'log'=>'售后订单拒绝退款，原因：'.$refuse_sale];
                        array_push($order_item_logs, $order_item_log);
                        // $refund_num = $orderItem['refund_num'] - $is['num']; //已退数量减去拒绝退款数量
                        $data = ['order_item_log' => serialize($order_item_logs), 'status' => $is['apply_status']];
                        if($orderItem['delivery_type'] == 2){
                            $is_item = Db::name("wd_xcx_main_shop_order_service")->where("uniacid", $appletid)->where("order_item_id", 'like' ,$orderItem["order_id"])->where("status", 1)->find();
                            if(!$is_item){
                                $res = Db::name("wd_xcx_main_shop_order")->where("uniacid",$appletid)->where('order_id', $orderItem['order_id'])->update(['allow_all_refund' => 1]); //主订单同意退款状态改为允许
                                if(!$res){
                                    throw new \Exception("数据表操作失败");
                                }
                            }
                        }
                        $res = Db::name('wd_xcx_main_shop_order_item')->where('order_item_id', $is["order_item_id"])->where('uniacid', $appletid)->update($data);
                        if(!$res){
                            throw new \Exception("数据表操作失败");
                        }
                    }else{ //主订单
                        $orderItem = Db::name("wd_xcx_main_shop_order_item")->where("uniacid", $appletid)->where("order_id", $is["order_item_id"])->field("order_item_log, order_item_id")->select(); //查出子订单号

                        foreach ($orderItem as $k => $v) { //子订单添加log
                            $order_item_logs = unserialize($v["order_item_log"]);
                            $order_item_log = ['time'=>time(), 'log'=>'售后订单拒绝退款，原因：'.$refuse_sale];
                            array_push($order_item_logs, $order_item_log);
                            $data = ['order_item_log' => serialize($order_item_logs), 'status' => $is['apply_status']];
                            $res = Db::name('wd_xcx_main_shop_order_item')->where('order_item_id', $v["order_item_id"])->where('uniacid', $appletid)->update($data);
                            if(!$res){
                                throw new \Exception("数据表操作失败");
                            }
                        }

                        $res = Db::name("wd_xcx_main_shop_order")->where("uniacid",$appletid)->where('order_id', $is['order_item_id'])->update(['allow_all_refund' => 1]); //主订单同意退款状态改为允许
                        if(!$res){
                            throw new \Exception("数据表操作失败");
                        }
                    }
                    $res = Db::name('wd_xcx_main_shop_order_service')->where('order_service_id', $order_service_id)->where('uniacid', $appletid)->where('status', 0)->update(['status' => 2, 'refuse_time' => time(), 'refuse_remark' => $refuse_sale]);
                    if(!$res){
                        throw new \Exception("数据表操作失败");
                    }
                    Db::commit();
                } catch (\Exception $e) {
                    Db::rollback();
                    $this->error('拒绝失败，' . $e->getMessage(), Url('Duoproducts/service').'?appletid='.$appletid);
                }

                if($is['source'] == 1){
                    $openid = Db::name("wd_xcx_user")->where("suid", $is['suid'])->value('openid');
                    $jsons = [
                        'order_id' => mb_substr($order_service_id, 1),
                        'fprice' => $is['change_refund_money'] > 0 ? $is['change_refund_money'] : $is['refund_money'],
                        'msg' => "退款被拒",
                    ];
                    $jsons = serialize($jsons);
                    sendSubscribe($appletid, 3, $openid, $jsons);
                }
                $this->success('拒绝成功', Url('Duoproducts/service').'?appletid='.$appletid);
            }else{
                $this->error("订单已操作或不存在", Url('Duoproducts/service').'?appletid='.$appletid);
            }
        }else if($popup_type == 'agreesale'){
            $agree_sale = trim(input('agree_sale'));
            $is = Db::name('wd_xcx_main_shop_order_service')->where('order_service_id', $order_service_id)->where('uniacid', $appletid)->where('status', 0)->find(); //判断处理中订单是否存在
                
            if($is){
                Db::startTrans();
                try {
                    $userinfo = Db::name("wd_xcx_superuser")->where("uniacid", $appletid)->where('id', $is['suid'])->find();
                    if(!$userinfo){
                        throw new \Exception("退款用户不存在");
                    }
                    $user_data = []; //用户金额/积分数组
                    $refund = []; //售后订单数组
                    $refund['status'] = 1;
                    $refund['agree_time'] = time();
                    
                    if($is['is_item'] == 1){ //子订单
                        $refund_money = input('refund_money');
                        if($is['refund_money'] != $refund_money){
                            $refund['change_refund_money'] = $refund_money;
                        }
                        $orderItem = Db::name("wd_xcx_main_shop_order_item")->where("uniacid", $appletid)->where("order_item_id", $is["order_item_id"])->field("pro_id, pro_type_id, order_item_log, num, refund_num, order_id")->find();  //判断当前子订单是否存在
                        //退款 只考虑总订单可退金额
                        $order = Db::name("wd_xcx_main_shop_order")->where("uniacid", $appletid)->where("order_id", $orderItem['order_id'])->find();
                        $refund_moneys = $order['total_can_tui_money']; //总订单可退金额
                        if($refund_money > $refund_moneys){
                            throw new \Exception("可退金额不足");
                        }

                        $order_item_logs = unserialize($orderItem["order_item_log"]);
                        $order_item_log = ['time'=>time(), 'log'=>'售后订单同意，原因：'.$agree_sale];
                        array_push($order_item_logs, $order_item_log);

                        $refund_num = $orderItem['refund_num'] + $is['num']; //已退数量加同意退款数量
                        $data = ['order_item_log' => serialize($order_item_logs), 'refund_num' => $refund_num];
                        $data['agree_tui_time'] = time();
                        $data['has_service'] = 1;
                        $data['refund_num'] = $refund_num;
                        $fx_ls = Db::name('wd_xcx_fx_ls')->where('uniacid',$appletid)->where("order_id", $is["order_item_id"])->find(); //判断是否存在返佣订单

                        if($is['apply_type'] == 0){ //售后类型 0 仅退款  1  退货退款
                            if($refund_num == $orderItem['num']){ //判断当前子订单是不是全部退款
                                $data['status'] = -4; 
                                $data['cancel_time'] = time();
                            }else{
                                $data['status'] = $is['apply_status']; 
                            }
                            $refund['refund_time'] = time();
                            $refund['refuse_time'] = time();
                        }else{
                            if($refund_num == $orderItem['num']){ //判断当前子订单是不是全部退款
                                $data['status'] = 6; 
                            }
                            $choose_add = input('choose_add');
                            $add = Db::name("wd_xcx_refund_address")->where("uniacid", $appletid)->where("id", $choose_add)->find();
                            if(!$add){
                                throw new \Exception("退货地址不存在");
                            }
                            $address = [
                                'name' => $add['name'],
                                'mobile' => $add['mobile'],
                                'address' => $add['province'].' '.$add['city'].' '.$add['area'],
                                'more_address' => $add['more_address'],
                            ];
                            $refund['apply_send_address'] = serialize($address);
                        }
                        $res = Db::name('wd_xcx_main_shop_order_item')->where('order_item_id', $is["order_item_id"])->where('uniacid', $appletid)->update($data);
                        if(!$res){
                            throw new \Exception("数据表操作失败");
                        }

                        if($is['apply_type'] == 0){ //售后类型 0 仅退款  1  退货退款
                            if($refund_num == $orderItem['num']){ //判断当前子订单是不是全部退款
                                if($fx_ls){
                                    $res = Db::name('wd_xcx_fx_ls')->where('uniacid',$appletid)->where("order_id", $is["order_item_id"])->update([
                                            'parent_id_get' => 0,
                                            'p_parent_id_get' => 0,
                                            'p_p_parent_id_get' => 0,
                                            'flag' => 3,
                                        ]); //返佣订单状态更改为取消分成
                                    if(!$res){
                                        throw new \Exception("数据表操作失败");
                                    }
                                }
                                $is1 = Db::name('wd_xcx_main_shop_order_item')->where('order_id', $orderItem['order_id'])->where('uniacid', $appletid)->where("status", "gt", 0)->find();  //判断主订单是否还有未退款订单

                                if(!$is1){  //订单全部退货
                                    $data_top['status'] = -3;

                                    //返用户金额/积分/优惠券
                                    // 退积分、添加流水
                                    if($order['score_use'] > 0){
                                        $user_data['score'] = $userinfo['score'] + $order['score_use'];
                                        $score_return = array(
                                            "uniacid" => $appletid,
                                            "orderid" => $order['order_id'],
                                            "suid" => $order['suid'],
                                            "type" => "add",
                                            "score" => $order['score_use'],
                                            "message" => "订单退款",
                                            "creattime" => time()
                                        );
                                        $res = Db::name('wd_xcx_score')->insert($score_return);
                                        if(!$res){
                                            throw new \Exception("数据表操作失败");
                                        }
                                    }

                                    if($order['coupon_id'] > 0){
                                        $coupon_info = Db::name("wd_xcx_coupon_user")->where("uniacid",$appletid)->where('id', $order['coupon_id'])->find();
                                        $res = Db::name("wd_xcx_coupon_user")->where("uniacid",$appletid)->where('id', $order['coupon_id'])->update(['flag' => 0, 'utime' => 0]);
                                        if(!$res){
                                            throw new \Exception("数据表操作失败");
                                        }
                                    }
                                }
                            }else{
                                if($fx_ls){
                                    $refound_num = $orderItem['num'] - $orderItem['refund_num'] + $is['num']; //原剩余数量
                                    $fx_one = sprintf("%01.2f",$fx_ls['parent_id_get'] / $refound_num); //单价 父级
                                    $fx_two = sprintf("%01.2f",$fx_ls['p_parent_id_get'] / $refound_num); //单价 父父级
                                    $fx_three = sprintf("%01.2f",$fx_ls['p_p_parent_id_get'] / $refound_num); //单价 父父父级
                                    $upd = [
                                        'parent_id_get' => sprintf("%01.2f",$fx_one * ($orderItem['num'] - $refund_num)),
                                        'p_parent_id_get' => sprintf("%01.2f",$fx_one * ($orderItem['num'] - $refund_num)),
                                        'p_p_parent_id_get' => sprintf("%01.2f",$fx_one * ($orderItem['num'] - $refund_num)),
                                    ];
                                    $res = Db::name('wd_xcx_fx_ls')->where('uniacid',$appletid)->where("order_id", $is["order_item_id"])->update($upd); //返佣订单更改返佣金额
                                    if(!$res){
                                        throw new \Exception("数据表操作失败");
                                    }
                                }
                            }
                            //退金额
                            if($refund_money > 0){
                                $money_return = array(
                                    "uniacid" => $appletid,
                                    "orderid" => $order['order_id'],
                                    "suid" => $order['suid'],
                                    "type" => "add",
                                    "score" => $refund_money,
                                    "message" => "订单退款",
                                    "creattime" => time()
                                );
                                $res = Db::name('wd_xcx_money')->insert($money_return);
                                if(!$res){
                                    throw new \Exception("数据表操作失败");
                                }
                            }
                            $data_top['total_can_tui_money'] = $refund_moneys - $refund_money;

                            $res = Db::name("wd_xcx_main_shop_order")->where("uniacid", $appletid)->where("order_id", $orderItem['order_id'])->update($data_top);
                            if(!$res){
                                throw new \Exception("数据表操作失败");
                            }

                            if($order['pay_type'] == 1 && $refund_money > 0){ //余额退款
                                $user_data['money'] = $userinfo['money'] + $refund_money;
                            }else{
                                $order_id = $order['order_id'];
                                $pay_to = $order['pay_to']; //支付到   1 微信 2 支付宝  3 百度 4 QQ
                                $app = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                                if($pay_to == 1){//支付到   1 微信
                                    if($order['is_change_price'] == 1){
                                        $order_id_new = unserialize($order['payment_info'])['order_id_new'];
                                    }else{
                                        $order_id_new = $order_id;
                                    }
                                    $source = $order['source'];
                                    if($source == 1){
                                        $mchid = $app['mchid'];   //商户号
                                        $apiKey = $app['signkey'];    //商户的秘钥
                                        $appid = $app['appID'];                 //小程序的id
                                        $SSLCERT_PATH = ROOT_PATH . 'public/Cert/' . $appletid . '/apiclient_cert.pem';//证书路径
                                        $SSLKEY_PATH = ROOT_PATH . 'public/Cert/' . $appletid . '/apiclient_key.pem';//证书路径
                                    }elseif($source == 3){
                                        $mchid = $app['wx_h5_mchid'];   //商户号
                                        $apiKey = $app['wx_h5_signkey'];    //商户的秘钥
                                        $appid = $app['wx_h5_appid'];                 //小程序的id
                                        $SSLCERT_PATH = ROOT_PATH.'public/Cert/'.$appletid.'/h5_apiclient_cert.pem';//证书路径
                                        $SSLKEY_PATH =  ROOT_PATH.'public/Cert/'.$appletid.'/h5_apiclient_key.pem';//证书路径
                                    }elseif($source == 5){
                                        $mchid = $app['bdance_h5_mchid'];   //商户号
                                        $apiKey = $app['bdance_h5_signkey'];    //商户的秘钥
                                        $appid = $app['bdance_h5_appid'];                 //小程序的id
                                        $SSLCERT_PATH = ROOT_PATH.'public/Cert/'.$appletid.'/bdance_apiclient_cert.pem';//证书路径
                                        $SSLKEY_PATH =  ROOT_PATH.'public/Cert/'.$appletid.'/bdance_apiclient_key.pem';//证书路径
                                    }

                                    $now = time();
                                    $refound_order_id = date("Y", $now) . date("m", $now) . date("d", $now) . date("H", $now) . date("i", $now) . date("s", $now) . rand(1000, 9999);

                                    $appkey = $app['appSecret'];            //小程序的秘钥
                                    $openid = 'openid';    //申请者的openid
                                    $outTradeNo = $order_id_new;
                                    $totalFee = $order['pay_money']*100;  //申请了退款多少钱
                                    $outRefundNo = $refound_order_id; //商户退款订单号
                                    $refundFee = $refund_money*100;  //申请了退款多少钱
                                    
                                    $opUserId = $mchid;//商户号
                                    include "WinXinRefund.php";
                                    $weixinpay = new WinXinRefund($openid, $outTradeNo, $totalFee, $outRefundNo, $refundFee, $SSLCERT_PATH, $SSLKEY_PATH, $opUserId, $appid, $apiKey);
                                    $return = $weixinpay->refund();
                                    if (!$return) {
                                        throw new \Exception('微信退款失败， 请检查系统设置->微信小程序相关配置');
                                    } 
                                }else if($pay_to == 2){//支付到   2 支付宝

                                    $now = time();
                                    $refound_order_id = date("Y", $now) . date("m", $now) . date("d", $now) . date("H", $now) . date("i", $now) . date("s", $now) . rand(1000, 9999);


                                    Vendor('alipaysdk.aop.AopClient');
                                    Vendor('alipaysdk.aop.request.AlipayTradeRefundRequest');
                                    $aop = new \AopClient ();
                                    $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
                                    $aop->appId = $app['ali_appID'];
                                    $aop->rsaPrivateKey = $app['ali_private_key'];
                                    $aop->alipayrsaPublicKey = $app['ali_public_key'];
                                    $aop->apiVersion = '1.0';
                                    $aop->signType = 'RSA2';
                                    $aop->postCharset = 'UTF-8';
                                    $aop->format = 'json';
                                    $request = new \AlipayTradeRefundRequest ();
                                    $request->setBizContent("{'refund_amount':" . $refund_money . ", 'out_trade_no': " . $order_id . ", 'out_request_no': ".$refound_order_id."}");
                                    $result = $aop->execute($request);
                                    $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
                                    $resultCode = $result->$responseNode->code;
                                    if (!empty($resultCode) && $resultCode == 10000) {
                                        $return = true;
                                    } else {
                                        throw new \Exception('支付宝退款失败， 请检查系统设置->支付宝小程序设置');
                                    }
                                }else if($pay_to == 3){//支付到   3 百度
                                     $pay_info = unserialize($order['payment_info']);
                                     require_once(ROOT_PATH.'application/api/controller/bdpay/Autoloader.php');
                                     $now = time();
                                     $refound_order_id = date("Y", $now) . date("m", $now) . date("d", $now) . date("H", $now) . date("i", $now) . date("s", $now) . rand(1000, 9999);

                                     $params = [
                                        'method' => 'nuomi.cashier.applyorderrefund',
                                        'orderId' => intval($pay_info['orderId']),
                                        'userId' => intval($pay_info['userId']),
                                        'refundType' => 1,
                                        'refundReason' => 'refundmoney',
                                        'tpOrderId' => strval($order_id),
                                        'appKey' => strval($app['baidu_pay_appkey']),
                                         'applyRefundMoney' => $refund_money * 100,
                                         'bizRefundBatchId' => $refound_order_id
                                     ];
                                     $rsaSign = \NuomiRsaSign::genSignWithRsa($params, $app['baidu_private_key']);
                                     $params['rsaSign'] = $rsaSign;

                                     $url = 'https://nop.nuomi.com/nop/server/rest';
                                     $res = _Postrequest($url, http_build_query($params));
                                     $res = json_decode($res, true);
                                     if($res['errno'] == 0){
                                         $return = true;
                                     }else{
                                         $this->error('退款失败!请检查系统设置->百度小程序设置');exit;
                                     }
                                }else if($pay_to == 4){//支付到   4 QQ
                                    $chars = "abcdefghijklmnopqrstuvwxyz0123456789";  
                                    $nonce_str = "";  
                                    for($i = 0; $i < 32; $i++) {  
                                        $nonce_str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
                                    }
                                    $op_user_passwd = MD5($app['qq_mchid_password']);
                                    $appid = $app['qq_appid'];
                                    $mch_id = $app['qq_mchid'];
                                    $out_trade_no = $order_id;
                                    $refund_fee = $refund_money;
                                    $now = time();
                                    $out_refund_no = date("Y", $now) . date("m", $now) . date("d", $now) . date("H", $now) . date("i", $now) . date("s", $now) . rand(1000, 9999);
                                    $sign_str = "appid=".$appid."&mch_id=".$mch_id."&nonce_str=".$nonce_str."&op_user_id=".$mch_id."&op_user_passwd=".$op_user_passwd."&out_refund_no=".$out_refund_no."&out_trade_no=".$out_trade_no."&refund_fee=".$refund_fee;
                                    $sign = $sign_str."&key=".$app['qq_mchid_key'];
                                    $sign = strtoupper(MD5($sign));
                                    $params = "<xml>
                                            <appid>".$appid."</appid>
                                            <mch_id>".$mch_id."</mch_id>
                                            <nonce_str>".$nonce_str."</nonce_str>
                                            <op_user_id>".$mch_id."</op_user_id>
                                            <op_user_passwd>".$op_user_passwd."</op_user_passwd>
                                            <out_refund_no>".$out_refund_no."</out_refund_no>
                                            <out_trade_no>".$out_trade_no."</out_trade_no>
                                            <refund_fee>".$refund_fee."</refund_fee>
                                            <sign>".$sign."</sign>
                                            </xml>";
                                    $url = 'https://api.qpay.qq.com/cgi-bin/pay/qpay_refund.cgi';
                                    $res = $this -> postXmlSSLCurl($params, $url, 30, $appletid);
                                    $res = $this->xmlToArray($res);
                                    if($res){
                                        if($res['return_code'] == 'SUCCESS'){
                                            $return = true;
                                        }else{
                                            $this->error('退款失败!请检查系统设置->QQ小程序设置');exit;
                                        }
                                    }else{
                                        $this->error('退款失败!请检查系统设置->QQ小程序设置');exit;
                                    }
                                }
                            }
                            
                            //退库存
                            $this ->toDealWithInventorySales($orderItem['pro_id'], $orderItem['pro_type_id'], $is['num']);
                        }
                      
                    }else{
                        $refund_money = $is['refund_money'];
                        $refund['refuse_time'] = time();
                        $refund['refund_time'] = time();


                        $order = Db::name("wd_xcx_main_shop_order")->where("uniacid", $appletid)->where("order_id", $is["order_item_id"])->find();
                        if($refund_money > $order['total_can_tui_money']){
                            throw new \Exception("可退金额不足");
                        }

                        // 退积分、添加流水
                        if($order['score_use'] > 0){
                            $user_data['score'] = $userinfo['score'] + $order['score_use'];
                            $score_return = array(
                                "uniacid" => $appletid,
                                "orderid" => $order['order_id'],
                                "suid" => $order['suid'],
                                "type" => "add",
                                "score" => $order['score_use'],
                                "message" => "订单退款",
                                "creattime" => time()
                            );
                            $res = Db::name('wd_xcx_score')->insert($score_return);
                            if(!$res){
                                throw new \Exception("数据表操作失败");
                            }
                        }

                        //退金额
                        if($refund_money > 0){
                            $money_return = array(
                                "uniacid" => $appletid,
                                "orderid" => $order['order_id'],
                                "suid" => $order['suid'],
                                "type" => "add",
                                "score" => $refund_money,
                                "message" => "订单退款",
                                "creattime" => time()
                            );
     
                            $res = Db::name('wd_xcx_money')->insert($money_return);
                            if(!$res){
                                throw new \Exception("数据表操作失败");
                            }
                        }

                        if($order['coupon_id'] > 0){
                            $coupon_info = Db::name("wd_xcx_coupon_user")->where("uniacid",$appletid)->where('id', $order['coupon_id'])->find();
                            $res = Db::name("wd_xcx_coupon_user")->where("uniacid",$appletid)->where('id', $order['coupon_id'])->update(['flag' => 0, 'utime' => 0]);
                            if(!$res){
                                throw new \Exception("数据表操作失败");
                            }
                        }

                        $orderItems = Db::name("wd_xcx_main_shop_order_item")->where("uniacid", $appletid)->where("order_id", $is["order_item_id"])->field("pro_id, pro_type_id, order_item_log, num, refund_num, order_item_id")->select();
                        $data_top['status'] = -3;
                        $data_top['cancel_time'] = time();
                        foreach ($orderItems as $k => $v) {
                            $order_item_logs = unserialize($v["order_item_log"]);
                            $order_item_log = ['time'=>time(), 'log'=>'售后订单同意，原因：'.$agree_sale];
                            array_push($order_item_logs, $order_item_log);
                            $data = [
                                'order_item_log' => serialize($order_item_logs),
                                'cancel_time' => time(),
                                'status' => -3
                            ];
                            $res = Db::name("wd_xcx_main_shop_order_item")->where("uniacid", $appletid)->where("order_item_id", $v["order_item_id"])->update($data);
                            if(!$res){
                                throw new \Exception("数据表操作失败");
                            }
                            $this ->toDealWithInventorySales($v['pro_id'], $v['pro_type_id'], $v['num']);
                        }

                        $res = Db::name("wd_xcx_main_shop_order")->where("uniacid", $appletid)->where("order_id", $is["order_item_id"])->update($data_top);
                        if(!$res){
                            throw new \Exception("数据表操作失败");
                        }
                        if($order['pay_type'] == 1){ //余额退款
                            $user_data['money'] = $userinfo['money'] + $refund_money;
                        }else{
                            $order_id = $order['order_id'];
                            $pay_to = $order['pay_to']; //支付到   1 微信 2 支付宝  3 百度 4 QQ
                            $app = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                            if($pay_to == 1){//支付到   1 微信
                                if($order['is_change_price'] == 1){
                                    $order_id_new = unserialize($order['payment_info'])['order_id_new'];
                                }else{
                                    $order_id_new = $order_id;
                                }
                                $source = $order['source'];
                                if($source == 1){
                                    $mchid = $app['mchid'];   //商户号
                                    $apiKey = $app['signkey'];    //商户的秘钥
                                    $appid = $app['appID'];                 //小程序的id
                                    $SSLCERT_PATH = ROOT_PATH . 'public/Cert/' . $appletid . '/apiclient_cert.pem';//证书路径
                                    $SSLKEY_PATH = ROOT_PATH . 'public/Cert/' . $appletid . '/apiclient_key.pem';//证书路径
                                }elseif($source == 3){
                                    $mchid = $app['wx_h5_mchid'];   //商户号
                                    $apiKey = $app['wx_h5_signkey'];    //商户的秘钥
                                    $appid = $app['wx_h5_appid'];                 //小程序的id
                                    $SSLCERT_PATH = ROOT_PATH.'public/Cert/'.$appletid.'/h5_apiclient_cert.pem';//证书路径
                                    $SSLKEY_PATH =  ROOT_PATH.'public/Cert/'.$appletid.'/h5_apiclient_key.pem';//证书路径
                                }elseif($source == 5){
                                    $mchid = $app['bdance_h5_mchid'];   //商户号
                                    $apiKey = $app['bdance_h5_signkey'];    //商户的秘钥
                                    $appid = $app['bdance_h5_appid'];                 //小程序的id
                                    $SSLCERT_PATH = ROOT_PATH.'public/Cert/'.$appletid.'/bdance_apiclient_cert.pem';//证书路径
                                    $SSLKEY_PATH =  ROOT_PATH.'public/Cert/'.$appletid.'/bdance_apiclient_key.pem';//证书路径
                                }
                                
                                $appkey = $app['appSecret'];            //小程序的秘钥
                                $openid = 'openid';    //申请者的openid
                                $outTradeNo = $order_id_new;
                                $totalFee = $refund_money*100;  //申请了退款多少钱
                                $outRefundNo = $order_id_new; //商户订单号
                                $refundFee = $refund_money*100;  //申请了退款多少钱
                                
                                $opUserId = $mchid;//商户号
                                include "WinXinRefund.php";
                                $weixinpay = new WinXinRefund($openid, $outTradeNo, $totalFee, $outRefundNo, $refundFee, $SSLCERT_PATH, $SSLKEY_PATH, $opUserId, $appid, $apiKey);
                                $return = $weixinpay->refund();
                                if (!$return) {
                                    throw new \Exception('微信退款失败， 请检查系统设置->微信小程序相关配置');
                                } 
                            }else if($pay_to == 2){//支付到   2 支付宝
                                Vendor('alipaysdk.aop.AopClient');
                                Vendor('alipaysdk.aop.request.AlipayTradeRefundRequest');
                                $aop = new \AopClient ();
                                $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
                                $aop->appId = $app['ali_appID'];
                                $aop->rsaPrivateKey = $app['ali_private_key'];
                                $aop->alipayrsaPublicKey = $app['ali_public_key'];
                                $aop->apiVersion = '1.0';
                                $aop->signType = 'RSA2';
                                $aop->postCharset = 'UTF-8';
                                $aop->format = 'json';
                                $request = new \AlipayTradeRefundRequest ();
                                $request->setBizContent("{'refund_amount':" . $refund_money . ", 'out_trade_no': " . $order_id . "}");
                                $result = $aop->execute($request);
                                $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
                                $resultCode = $result->$responseNode->code;
                                if (!empty($resultCode) && $resultCode == 10000) {
                                    $return = true;
                                } else {
                                    throw new \Exception('支付宝退款失败， 请检查系统设置->支付宝小程序设置');
                                }
                            }else if($pay_to == 3){//支付到   3 百度
                                 $pay_info = unserialize($order['payment_info']);
                                 require_once(ROOT_PATH.'application/api/controller/bdpay/Autoloader.php');
                                 $now = time();
                                 $refound_order_id = date("Y", $now) . date("m", $now) . date("d", $now) . date("H", $now) . date("i", $now) . date("s", $now) . rand(1000, 9999);
                                 $params = [
                                     'method' => 'nuomi.cashier.applyorderrefund',
                                     'orderId' => intval($pay_info['orderId']),
                                     'userId' => intval($pay_info['userId']),
                                     'refundType' => '1',
                                     'refundReason' => '订单退款',
                                     'tpOrderId' => $order_id,
                                     'appKey' => $app['baidu_pay_appkey'],
                                     'applyRefundMoney' => $refund_money * 100,
                                     'bizRefundBatchId' => $refound_order_id
                                 ];
                                 $rsaSign = \NuomiRsaSign::genSignWithRsa($params, $app['baidu_private_key']);
                                 $params['rsaSign'] = $rsaSign;
                                 $url = 'https://nop.nuomi.com/nop/server/rest';
                                 $res = _Postrequest($url, http_build_query($params));
                                 $res = json_decode($res, true);
                                 if($res['errno'] == 0){
                                     $return = true;
                                 }else{
                                     $this->error('退款失败!请检查系统设置->百度小程序设置');exit;
                                 }
                            }else if($pay_to == 4){//支付到   4 QQ
                                $chars = "abcdefghijklmnopqrstuvwxyz0123456789";  
                                $nonce_str = "";  
                                for($i = 0; $i < 32; $i++) {  
                                    $nonce_str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
                                }
                                $op_user_passwd = MD5($app['qq_mchid_password']);
                                $appid = $app['qq_appid'];
                                $mch_id = $app['qq_mchid'];
                                $out_trade_no = $order_id;
                                $refund_fee = $refund_money;
                                $now = time();
                                $out_refund_no = date("Y", $now) . date("m", $now) . date("d", $now) . date("H", $now) . date("i", $now) . date("s", $now) . rand(1000, 9999);
                                $sign_str = "appid=".$appid."&mch_id=".$mch_id."&nonce_str=".$nonce_str."&op_user_id=".$mch_id."&op_user_passwd=".$op_user_passwd."&out_refund_no=".$out_refund_no."&out_trade_no=".$out_trade_no."&refund_fee=".$refund_fee;
                                $sign = $sign_str."&key=".$app['qq_mchid_key'];
                                $sign = strtoupper(MD5($sign));
                                $params = "<xml>
                                        <appid>".$appid."</appid>
                                        <mch_id>".$mch_id."</mch_id>
                                        <nonce_str>".$nonce_str."</nonce_str>
                                        <op_user_id>".$mch_id."</op_user_id>
                                        <op_user_passwd>".$op_user_passwd."</op_user_passwd>
                                        <out_refund_no>".$out_refund_no."</out_refund_no>
                                        <out_trade_no>".$out_trade_no."</out_trade_no>
                                        <refund_fee>".$refund_fee."</refund_fee>
                                        <sign>".$sign."</sign>
                                        </xml>";
                                $url = 'https://api.qpay.qq.com/cgi-bin/pay/qpay_refund.cgi';
                                $res = $this -> postXmlSSLCurl($params, $url, 30, $appletid);
                                $res = $this->xmlToArray($res);
                                if($res){
                                    if($res['return_code'] == 'SUCCESS'){
                                        $return = true;
                                    }else{
                                        $this->error('退款失败!请检查系统设置->QQ小程序设置');exit;
                                    }
                                }else{
                                    $this->error('退款失败!请检查系统设置->QQ小程序设置');exit;
                                }
                            }
                        }
                    }
                    if($is['apply_type'] == 0 && $is['source'] == 1){//apply_type售后类型 0 仅退款  1 退货退款
                        $openid = Db::name('wd_xcx_user')->where('suid', $is['suid'])->value('openid');
                        $jsons = [
                            'order_id' => mb_substr($order_service_id, 1),
                            'fprice' => $refund_money,
                            'msg' => "退款成功",
                        ];
                        $jsons = serialize($jsons);
                        sendSubscribe($appletid, 3, $openid, $jsons);
                    }
                    $res = Db::name("wd_xcx_main_shop_order_service")->where("uniacid", $appletid)->where("order_service_id", $order_service_id)->update($refund);

                    if(!$res){
                        throw new \Exception("数据表操作失败");
                    }
                    if($user_data){
                        $res = Db::name("wd_xcx_superuser")->where("uniacid",$appletid)->where('id', $is['suid'])->update($user_data);
                        if(!$res){
                            throw new \Exception("数据表操作失败");
                        }
                    }
                    Db::commit();
                } catch (\Exception $e) {
                    Db::rollback();
                    $this->error('处理失败，' . $e->getMessage(), Url('Duoproducts/service').'?appletid='.$appletid);
                }
                

                $this->success('处理成功');
            }
        }else if($popup_type == 'confirm'){
            $is = Db::name('wd_xcx_main_shop_order_service')->where('order_service_id', $order_service_id)->where('uniacid', $appletid)->where('status', 1)->where('express', 'neq', '')->find();
            if($is){
                $refund['refund_time'] = time();
                $refund_num = $is['num'];
                $refund_money = $is['refund_money'];

                $userinfo = Db::name("wd_xcx_superuser")->where("uniacid", $appletid)->where('id', $is['suid'])->find();
                Db::startTrans();
                try {
                    if(!$userinfo){
                        throw new \Exception("退款用户不存在");
                    }
                    $orderItem = Db::name("wd_xcx_main_shop_order_item")->where("uniacid", $appletid)->where("order_item_id", $is["order_item_id"])->field("pro_id, pro_type_id, order_item_log, num, refund_num, order_id")->find();  //判断当前子订单是否存在
                    $order = Db::name("wd_xcx_main_shop_order")->where("uniacid", $appletid)->where("order_id", $orderItem['order_id'])->find();
                    $refund_moneys = $order['total_can_tui_money']; //总订单可退金额
                    if($refund_money > $refund_moneys){
                        throw new \Exception("可退金额不足");
                    }
                    $order_item_logs = unserialize($orderItem["order_item_log"]);
                    $order_item_log = ['time'=>time(), 'log'=>'售后订单卖家确认收货'];
                    array_push($order_item_logs, $order_item_log);

                    $data = ['order_item_log' => serialize($order_item_logs), ];
                    $fx_ls = Db::name('wd_xcx_fx_ls')->where('uniacid',$appletid)->where("order_id", $is["order_item_id"])->find(); //判断是否存在返佣订单
                   
           
                    if($refund_num == $orderItem['num']){ //判断当前子订单是不是全部退款
                        $data['status'] = -5;
                        $data['cancel_time'] = time();
                        $res = Db::name('wd_xcx_main_shop_order_item')->where('order_item_id', $is["order_item_id"])->where('uniacid', $appletid)->update($data);
                        if(!$res){
                            throw new \Exception("数据表操作失败");
                        }

                        if($fx_ls){
                            $res = Db::name('wd_xcx_fx_ls')->where('uniacid',$appletid)->where("order_id", $is["order_item_id"])->update([
                                    'parent_id_get' => 0,
                                    'p_parent_id_get' => 0,
                                    'p_p_parent_id_get' => 0,
                                    'flag' => 3,
                                ]); //返佣订单状态更改为取消分成
                            if(!$res){
                                throw new \Exception("数据表操作失败");
                            }
                        }

                        $is1 = Db::name('wd_xcx_main_shop_order_item')->where('order_id', $orderItem["order_id"])->where('uniacid', $appletid)->where("status", "gt", 0)->find();  //判断主订单是否还有未退款订单
                        if(!$is1){
                            $data_top['status'] = -3;
                        }
                        //返用户金额/积分/优惠券
                        // 退积分、添加流水
                        if($order['score_use'] > 0){
                            $user_data['score'] = $userinfo['money'] + $order['score_use'];
                            $score_return = array(
                                "uniacid" => $appletid,
                                "orderid" => $order['order_id'],
                                "suid" => $order['suid'],
                                "type" => "add",
                                "score" => $order['score_use'],
                                "message" => "订单退款",
                                "creattime" => time()
                            );
                            $res = Db::name('wd_xcx_score')->insert($score_return);
                            if(!$res){
                                throw new \Exception("数据表操作失败");
                            }
                        }

                        if($order['coupon_id'] > 0){
                            $coupon_info = Db::name("wd_xcx_coupon_user")->where("uniacid",$appletid)->where('id', $order['coupon_id'])->find();
                            if($coupon_info['etime'] > time()){ //判断优惠券是否过期
                                $res = Db::name("wd_xcx_coupon_user")->where("uniacid",$appletid)->where('id', $order['coupon_id'])->update(['flag' => 0, 'utime' => 0]);
                                if(!$res){
                                    throw new \Exception("数据表操作失败");
                                }
                            }
                        }
                    }else{
                        $data['status'] = $is['apply_status']; 
                        $res = Db::name('wd_xcx_main_shop_order_item')->where('order_item_id', $is["order_item_id"])->where('uniacid', $appletid)->update($data);
                        if(!$res){
                            throw new \Exception("数据表操作失败");
                        }
                        if($fx_ls){
                            $refound_num = $orderItem['num'] - $orderItem['refund_num'] + $is['num']; //原剩余数量
                            $fx_one = sprintf("%01.2f",$fx_ls['parent_id_get'] / $refound_num); //单价 父级
                            $fx_two = sprintf("%01.2f",$fx_ls['p_parent_id_get'] / $refound_num); //单价 父父级
                            $fx_three = sprintf("%01.2f",$fx_ls['p_p_parent_id_get'] / $refound_num); //单价 父父父级
                            $upd = [
                                'parent_id_get' => sprintf("%01.2f",$fx_one * ($orderItem['num'] - $refund_num)),
                                'p_parent_id_get' => sprintf("%01.2f",$fx_one * ($orderItem['num'] - $refund_num)),
                                'p_p_parent_id_get' => sprintf("%01.2f",$fx_one * ($orderItem['num'] - $refund_num)),
                            ];
                            $res = Db::name('wd_xcx_fx_ls')->where('uniacid',$appletid)->where("order_id", $is["order_item_id"])->update($upd); //返佣订单更改返佣金额
                            if(!$res){
                                throw new \Exception("数据表操作失败");
                            }
                        }
                    }

                    //退金额
                    if($refund_money > 0){
                        $money_return = array(
                            "uniacid" => $appletid,
                            "orderid" => $order['order_id'],
                            "suid" => $order['suid'],
                            "type" => "add",
                            "score" => $refund_money,
                            "message" => "订单退款",
                            "creattime" => time()
                        );
                        $res = Db::name('wd_xcx_money')->insert($money_return);
                        if(!$res){
                            throw new \Exception("数据表操作失败");
                        }
                    }

                    $data_top['total_can_tui_money'] = $refund_moneys - $refund_money;
                    $res = Db::name("wd_xcx_main_shop_order")->where("uniacid", $appletid)->where("order_id", $orderItem['order_id'])->update($data_top);
                    if(!$res){
                        throw new \Exception("数据表操作失败");
                    }
                    if($order['pay_type'] == 1){ //余额退款
                        $user_data['money'] = $userinfo['money'] + $refund_money;
                    }else{
                        $order_id = $order['order_id'];
                        $pay_to = $order['pay_to']; //支付到   1 微信 2 支付宝  3 百度 4 QQ
                        $app = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                        if($pay_to == 1){//支付到   1 微信
                            $source = $order['source'];
                            if($source == 1){
                                $mchid = $app['mchid'];   //商户号
                                $apiKey = $app['signkey'];    //商户的秘钥
                                $appid = $app['appID'];                 //小程序的id
                                $SSLCERT_PATH = ROOT_PATH . 'public/Cert/' . $appletid . '/apiclient_cert.pem';//证书路径
                                $SSLKEY_PATH = ROOT_PATH . 'public/Cert/' . $appletid . '/apiclient_key.pem';//证书路径
                            }elseif($source == 3){
                                $mchid = $app['wx_h5_mchid'];   //商户号
                                $apiKey = $app['wx_h5_signkey'];    //商户的秘钥
                                $appid = $app['wx_h5_appid'];                 //小程序的id
                                $SSLCERT_PATH = ROOT_PATH.'public/Cert/'.$appletid.'/h5_apiclient_cert.pem';//证书路径
                                $SSLKEY_PATH =  ROOT_PATH.'public/Cert/'.$appletid.'/h5_apiclient_key.pem';//证书路径
                            }elseif($source == 5){
                                $mchid = $app['bdance_h5_mchid'];   //商户号
                                $apiKey = $app['bdance_h5_signkey'];    //商户的秘钥
                                $appid = $app['bdance_h5_appid'];                 //小程序的id
                                $SSLCERT_PATH = ROOT_PATH.'public/Cert/'.$appletid.'/bdance_apiclient_cert.pem';//证书路径
                                $SSLKEY_PATH =  ROOT_PATH.'public/Cert/'.$appletid.'/bdance_apiclient_key.pem';//证书路径
                            }
                            
                            $appkey = $app['appSecret'];            //小程序的秘钥
                            $openid = 'openid';    //申请者的openid
                            $outTradeNo = $order_id;
                            $totalFee = $refund_money*100;  //申请了退款多少钱
                            $outRefundNo = $order_id; //商户订单号
                            $refundFee = $refund_money*100;  //申请了退款多少钱
                            
                            $opUserId = $mchid;//商户号
                            include "WinXinRefund.php";
                            $weixinpay = new WinXinRefund($openid, $outTradeNo, $totalFee, $outRefundNo, $refundFee, $SSLCERT_PATH, $SSLKEY_PATH, $opUserId, $appid, $apiKey);
                            $return = $weixinpay->refund();
                            if (!$return) {
                                throw new \Exception('微信退款失败， 请检查系统设置->微信小程序相关配置');
                            } 
                        }else if($pay_to == 2){//支付到   2 支付宝
                            Vendor('alipaysdk.aop.AopClient');
                            Vendor('alipaysdk.aop.request.AlipayTradeRefundRequest');
                            $aop = new \AopClient ();
                            $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
                            $aop->appId = $app['ali_appID'];
                            $aop->rsaPrivateKey = $app['ali_private_key'];
                            $aop->alipayrsaPublicKey = $app['ali_public_key'];
                            $aop->apiVersion = '1.0';
                            $aop->signType = 'RSA2';
                            $aop->postCharset = 'UTF-8';
                            $aop->format = 'json';
                            $request = new \AlipayTradeRefundRequest ();
                            $request->setBizContent("{'refund_amount':" . $refund_money . ", 'out_trade_no': " . $order_id . "}");
                            $result = $aop->execute($request);
                            $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
                            $resultCode = $result->$responseNode->code;
                            if (!empty($resultCode) && $resultCode == 10000) {
                                $return = true;
                            } else {
                                throw new \Exception('支付宝退款失败， 请检查系统设置->支付宝小程序设置');
                            }
                        }else if($pay_to == 3){//支付到   3 百度
                             $pay_info = unserialize($order['payment_info']);
                             require_once(ROOT_PATH.'application/api/controller/bdpay/Autoloader.php');
                             $now = time();
                             $refound_order_id = date("Y", $now) . date("m", $now) . date("d", $now) . date("H", $now) . date("i", $now) . date("s", $now) . rand(1000, 9999);
                             $params = [
                                 'method' => 'nuomi.cashier.applyorderrefund',
                                 'orderId' => intval($pay_info['orderId']),
                                 'userId' => intval($pay_info['userId']),
                                 'refundType' => '1',
                                 'refundReason' => '订单退款',
                                 'tpOrderId' => $order_id,
                                 'appKey' => $app['baidu_pay_appkey'],
                                 'applyRefundMoney' => $refund_money * 100,
                                 'bizRefundBatchId' => $refound_order_id
                             ];
                             $rsaSign = \NuomiRsaSign::genSignWithRsa($params, $app['baidu_private_key']);
                             $params['rsaSign'] = $rsaSign;
                             $url = 'https://nop.nuomi.com/nop/server/rest';
                             $res = _Postrequest($url, $params);
                             $res = json_decode($res, true);
                             if($res['errno'] == 0){
                                 $return = true;
                             }else{
                                 $this->error('退款失败!请检查系统设置->百度小程序设置');exit;
                             }
                        }else if($pay_to == 4){//支付到   4 QQ
                            $chars = "abcdefghijklmnopqrstuvwxyz0123456789";  
                            $nonce_str = "";  
                            for($i = 0; $i < 32; $i++) {  
                                $nonce_str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
                            }
                            $op_user_passwd = MD5($app['qq_mchid_password']);
                            $appid = $app['qq_appid'];
                            $mch_id = $app['qq_mchid'];
                            $out_trade_no = $order_id;
                            $refund_fee = $refund_money;
                            $now = time();
                            $out_refund_no = date("Y", $now) . date("m", $now) . date("d", $now) . date("H", $now) . date("i", $now) . date("s", $now) . rand(1000, 9999);
                            $sign_str = "appid=".$appid."&mch_id=".$mch_id."&nonce_str=".$nonce_str."&op_user_id=".$mch_id."&op_user_passwd=".$op_user_passwd."&out_refund_no=".$out_refund_no."&out_trade_no=".$out_trade_no."&refund_fee=".$refund_fee;
                            $sign = $sign_str."&key=".$app['qq_mchid_key'];
                            $sign = strtoupper(MD5($sign));
                            $params = "<xml>
                                    <appid>".$appid."</appid>
                                    <mch_id>".$mch_id."</mch_id>
                                    <nonce_str>".$nonce_str."</nonce_str>
                                    <op_user_id>".$mch_id."</op_user_id>
                                    <op_user_passwd>".$op_user_passwd."</op_user_passwd>
                                    <out_refund_no>".$out_refund_no."</out_refund_no>
                                    <out_trade_no>".$out_trade_no."</out_trade_no>
                                    <refund_fee>".$refund_fee."</refund_fee>
                                    <sign>".$sign."</sign>
                                    </xml>";
                            $url = 'https://api.qpay.qq.com/cgi-bin/pay/qpay_refund.cgi';
                            $res = $this -> postXmlSSLCurl($params, $url, 30, $appletid);
                            $res = $this->xmlToArray($res);
                            if($res){
                                if($res['return_code'] == 'SUCCESS'){
                                    $return = true;
                                }else{
                                    $this->error('退款失败!请检查系统设置->QQ小程序设置');exit;
                                }
                            }else{
                                $this->error('退款失败!请检查系统设置->QQ小程序设置');exit;
                            }
                        }
                    }

                    //退货加库存减销量
                    $this ->toDealWithInventorySales($orderItem['pro_id'], $orderItem['pro_type_id'], $is['num']);

                    Db::commit();

                } catch (\Exception $e) {
                    Db::rollback();
                    $this->error('处理失败，' . $e->getMessage(), Url('Duoproducts/service').'?appletid='.$appletid);
                }
                $res = Db::name("wd_xcx_main_shop_order_service")->where("uniacid", $appletid)->where("order_service_id", $order_service_id)->update($refund);
                if(!$res){
                    throw new \Exception("数据表操作失败");
                }
                if($user_data){
                    $res = Db::name("wd_xcx_superuser")->where("uniacid",$appletid)->where('id', $is['suid'])->update($user_data);
                    if(!$res){
                        throw new \Exception("数据表操作失败");
                    }
                }
                if($is['source'] == 1){
                    $openid = Db::name('wd_xcx_user')->where('suid', $is['suid'])->value('openid');
                    $jsons = [
                        'order_id' => mb_substr($order_service_id, 1),
                        'fprice' => $refund_money,
                        'msg' => "退款成功",
                    ];
                    $jsons = serialize($jsons);
                    sendSubscribe($appletid, 3, $openid, $jsons);
                }
                $this->success('收货成功');
            }else{
                $this->error('收货失败，待收货订单不存在');
            }
        }
    }

    //图片上传
    function imgup(){
        $picname = $_FILES['uploadfile']['name']; 
        $picsize = $_FILES['uploadfile']['size']; 
        if ($picname != "") { 
            if ($picsize > 10240000) { //限制上传大小 
                echo '{"status":0,"content":"图片大小不能超过2M"}';
                exit; 
            } 
            $type = strstr($picname, '.'); //限制上传格式 
            if ($type != ".gif" && $type != ".jpg" && $type != ".png") {
                echo '{"status":2,"content":"图片格式不对！"}';
                exit; 
            }
            $rand = rand(100, 999); 
            $pics = uniqid() . $type; //命名图片名称 
            //上传路径 
            $pic_path = ROOT_HOST."/upimages/".date("Ymd",time())."/". $pics; 
            move_uploaded_file($_FILES['uploadfile']['tmp_name'], $pic_path); 
        } 
        $size = round($picsize/1024,2); //转换成kb 
        echo '{"status":1,"name":"'.$picname.'","url":"'.$pic_path.'","size":"'.$size.'","content":"上传成功"}'; 
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
    public function imgupload_duo(){
        $data['randid'] = input('randid');
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
    public function del_img(){
        $id = input("id");
        $res = Db::name('wd_xcx_products_url')->where('id', $id)->delete();
        if($res){
            return 1;
        }else{
            $this->error("删除失败！");
        }
    }
    //规格图片上传
    public function imgupload(){
        $uniacid = input("uniacid");
        $url = getRemoteType($uniacid, 0, 2);
        return $url;


        // $remote = Db::name("wd_xcx_base")->where("uniacid",$uniacid)->field("remote")->find()['remote'];
        // if(!$remote){
        //     $remote = 1;
        // }
        // $groupid = 0;
        // if($remote == 1){
        //     $files = request()->file('');  
        //     foreach($files as $file){        
        //         // 移动到框架应用根目录/public/upimages/ 目录下        
        //         $info = $file->validate(['ext'=>'jpg,png,gif,jpeg'])->move(ROOT_PATH . 'public' . DS . 'upimages');
        //         if($info){
        //             $url =  "/upimages/".date("Ymd",time())."/".$info->getFilename();
        //             $arr = array("url"=>$url);
        //             return json_encode($arr);
        //         }else{
        //             // 上传失败获取错误信息
        //             return $this->error($file->getError()) ;
        //         }    
        //     }
        // }else if($remote == 2){
        //     $qiniu_info = Db::name("wd_xcx_remote")->where("type",2)->where("uniacid",$uniacid)->find();
        //     $file = $_FILES['uploadfile']['tmp_name'];
        //     $is_img = getimagesize($file);
        //     if($is_img){
        //     }
        //     $oringal_name = $_FILES['uploadfile']['name'];
           
        //     $pathinfo = pathinfo($oringal_name);
        //     // var_dump($pathinfo);exit;
        //     // 要上传图片的本地路径
        //     $ext = $pathinfo['extension'];
        //     $key = 'upimages/'.md5(uniqid(microtime(true),true)).'.'.$ext;
            
        //     // 需要填写你的 Access Key 和 Secret Key
        //     $accessKey = $qiniu_info['ak'];
        //     $secretKey = $qiniu_info['sk'];
        //     // 构建鉴权对象
        //     $auth = new Auth($accessKey, $secretKey);
        //     // 要上传的空间
        //     $bucket = $qiniu_info['bucket'];
        //     $domain = $qiniu_info['domain'];
        //     $token = $auth->uploadToken($bucket);
        //     // 初始化 UploadManager 对象并进行文件的上传
        //     $uploadMgr = new UploadManager();
        //     // 调用 UploadManager 的 putFile 方法进行文件的上传
        //     list($ret, $err) = $uploadMgr->putFile($token, $key, $file);
        //     if ($err !== null) {
        //         echo ["err"=>1,"msg"=>$err,"data"=>""];
        //     } else {
        //         $arr = array("url"=>$qiniu_info['domain'].'/'.$ret['key']);
        //         return json_encode($arr);
        //     }
        // }
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
                $array1=input('duoproducts');
                $arr=explode(',',$array1);
                $res1 = Db::name('wd_xcx_products')->where("uniacid",$appletid)->where('id',"in",$arr)->delete();
                $res2 = Db::name('wd_xcx_duo_products_type_value')->where('pid',"in",$arr)->delete();
                if($res1 || $res2){
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


    private function _Postrequest_Bd($url, $data, $ssl = true)
    {
        $header = [];
        $header[] = 'Content-Type:application/x-www-form-urlencoded';
        //curl完成
        $curl = curl_init();
        //设置curl选项
        curl_setopt($curl, CURLOPT_URL, $url);//URL
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:38.0) Gecko/20100101 Firefox/38.0 FirePHP/0.7.4';
        curl_setopt($curl, CURLOPT_USERAGENT, $user_agent);//user_agent，请求代理信息
        curl_setopt($curl, CURLOPT_AUTOREFERER, true);//referer头，请求来源
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);//设置超时时间
        //SSL相关
        if ($ssl) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//禁用后cURL将终止从服务端进行验证
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);//检查服务器SSL证书中是否存在一个公用名(common name)。
        }
        // 处理post相关选项
        curl_setopt($curl, CURLOPT_POST, true);// 是否为POST请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);// 处理请求数据
        // 处理响应结果
        curl_setopt($curl, CURLOPT_HEADER, false);//是否处理响应头
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);//curl_exec()是否返回响应结果
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        // 发出请求
        $response = curl_exec($curl);
        if (false === $response) {
            echo '<br>', curl_error($curl), '<br>';
            return false;
        }
        curl_close($curl);
        return $response;
    }



    private function _Postrequest($url, $data, $ssl = true)
    {
        //curl完成
        $curl = curl_init();
        //设置curl选项
        curl_setopt($curl, CURLOPT_URL, $url);//URL
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:38.0) Gecko/20100101 Firefox/38.0 FirePHP/0.7.4';
        curl_setopt($curl, CURLOPT_USERAGENT, $user_agent);//user_agent，请求代理信息
        curl_setopt($curl, CURLOPT_AUTOREFERER, true);//referer头，请求来源
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);//设置超时时间
        //SSL相关
        if ($ssl) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//禁用后cURL将终止从服务端进行验证
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);//检查服务器SSL证书中是否存在一个公用名(common name)。
        }
        // 处理post相关选项
        curl_setopt($curl, CURLOPT_POST, true);// 是否为POST请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);// 处理请求数据
        // 处理响应结果
        curl_setopt($curl, CURLOPT_HEADER, false);//是否处理响应头
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);//curl_exec()是否返回响应结果
        // 发出请求
        $response = curl_exec($curl);
        if (false === $response) {
            echo '<br>', curl_error($curl), '<br>';
            return false;
        }
        curl_close($curl);
        return $response;
    }
    //需要使用证书的请求
    function postXmlSSLCurl($xml,$url,$second=30,$uniacid)
    {
        $ch = curl_init();
        //超时时间
        curl_setopt($ch,CURLOPT_TIMEOUT,$second);
        //这里设置代理，如果有的话
        //curl_setopt($ch,CURLOPT_PROXY, '8.8.8.8');
        //curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
        //设置header
        curl_setopt($ch,CURLOPT_HEADER,FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
        //设置证书
        //使用证书：cert 与 key 分别属于两个.pem文件
        //默认格式为PEM，可以注释
        $SSLCERT_PATH = ROOT_PATH.'public/Cert/'.$uniacid.'/qq_apiclient_cert.pem';//证书路径
        $SSLKEY_PATH =  ROOT_PATH.'public/Cert/'.$uniacid.'/qq_apiclient_key.pem';//证书路径
        curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
        curl_setopt($ch,CURLOPT_SSLCERT, $SSLCERT_PATH);
        //默认格式为PEM，可以注释
        curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
        curl_setopt($ch,CURLOPT_SSLKEY, $SSLKEY_PATH);
        //post提交方式
        curl_setopt($ch,CURLOPT_POST, true);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$xml);
        $data = curl_exec($ch);
        //返回结果
        if($data){
            curl_close($ch);
            return $data;
        }
        else {
            $error = curl_errno($ch);
            echo "curl出错，错误码:$error"."<br>";
            curl_close($ch);
            return false;
        }
    }
    private function xmlToArray($xml) {  

        //禁止引用外部xml实体   

        libxml_disable_entity_loader(true);  

        $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);  

        $val = json_decode(json_encode($xmlstring), true);  

        return $val;  

    }
}