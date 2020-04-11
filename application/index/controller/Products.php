<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;
header("Content-type: text/html; charset=utf-8");
class Products extends Base
{
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

                $listV = Db::name('wd_xcx_cate')->where("uniacid",$appletid)->where("type",'showPro')->where("cid",0)->order('num desc')->select();
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
                //获取子集
                $listallcate=Db::name('wd_xcx_cate')->where("cid",$cid)->select();
                $array1=array();
                for($a=0;$a<count($listallcate);$a++){
                    array_push($array1,$listallcate[$a]['id']);
                }
                array_push($array1,$cid);
                if($cid>0 && $keys != ''){
                    $news = Db::name('wd_xcx_products')->where("uniacid",$appletid)->where("type","showPro")->where("is_more",1)->where("cid","in",$array1)->where("title","like","%".$keys."%")->order('num desc')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                    $count = Db::name('wd_xcx_products')->where("uniacid",$appletid)->where("type","showPro")->where("is_more",1)->where("cid","in",$array1)->where("title","like","%".$keys."%")->order('num desc')->count();
                }else if($cid>0 && $keys == ''){
                    $news = Db::name('wd_xcx_products')->where("uniacid",$appletid)->where("type","showPro")->where("is_more",1)->where("cid","in",$array1)->order('num desc')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                    $count = Db::name('wd_xcx_products')->where("uniacid",$appletid)->where("type","showPro")->where("is_more",1)->where("cid","in",$array1)->order('num desc')->count();
                }else if($cid == 0 && $keys != ''){
                    $news = Db::name('wd_xcx_products')->where("uniacid",$appletid)->where("type","showPro")->where("is_more",1)->where("title","like","%".$keys."%")->order('num desc')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                    $count = Db::name('wd_xcx_products')->where("uniacid",$appletid)->where("type","showPro")->where("is_more",1)->where("title","like","%".$keys."%")->order('num desc')->count();
                }else{
                    $news = Db::name('wd_xcx_products')->where("uniacid",$appletid)->where("type","showPro")->where("is_more",1)->order('num desc')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                    $count = Db::name('wd_xcx_products')->where("uniacid",$appletid)->where("type","showPro")->where("is_more",1)->order('num desc')->count();
                }

                if($news->toArray()){
                    $list = $news->toArray()['data'];
                }
                foreach ($list as $key => &$value) {
                    if($value['thumb']) {
                        $value['thumb'] = remote($appletid,$value['thumb'],1);
                    }else{
                        $pic="/image/noimage.jpg";
                        $value['thumb'] =  remote($appletid,$pic,1);
                    }
                }

                $this->assign('list',$list);
                $this->assign('news',$news);
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
    public function pro(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $cid=input("cid")?input("cid"):0;
                $title=input("key");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                //获取栏目
                $listV = Db::name('wd_xcx_cate')->where("uniacid",$appletid)->where("type",'showPro')->where("cid",0)->order('num desc')->select();
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
                //获取子集
                $listallcate=Db::name('wd_xcx_cate')->where("cid",$cid)->select();
                $array1=array();
                for($a=0;$a<count($listallcate);$a++){
                    array_push($array1,$listallcate[$a]['id']);
                }
                array_push($array1,$cid);
                if($cid==0 && $title == false){
                    $news = Db::name('wd_xcx_products')->where("uniacid",$appletid)->where("type","showPro")->where("is_more",0)->order('num desc')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                    $count = Db::name('wd_xcx_products')->where("uniacid",$appletid)->where("type","showPro")->where("is_more",0)->order('num desc')->count();
                }else if($cid>0&&$title==false){
                    $news = Db::name('wd_xcx_products')->where("uniacid",$appletid)->where("type","showPro")->where("cid","in",$array1)->where("is_more",0)->order('num desc')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                    $count = Db::name('wd_xcx_products')->where("uniacid",$appletid)->where("type","showPro")->where("cid","in",$array1)->where("is_more",0)->order('num desc')->count();
                }else if($cid>0&&$title!=false){
                    $news = Db::name('wd_xcx_products')->where("uniacid",$appletid)->where("type","showPro")->where("cid","in",$array1)->where("title","like","%".$title."%")->where("is_more",0)->order('num desc')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                    $count = Db::name('wd_xcx_products')->where("uniacid",$appletid)->where("type","showPro")->where("cid","in",$array1)->where("title","like","%".$title."%")->where("is_more",0)->order('num desc')->count();
                }else if($cid==0&&$title!=false){
                    $news = Db::name('wd_xcx_products')->where("uniacid",$appletid)->where("type","showPro")->where("title","like","%".$title."%")->where("is_more",0)->order('num desc')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                    $count = Db::name('wd_xcx_products')->where("uniacid",$appletid)->where("type","showPro")->where("title","like","%".$title."%")->where("is_more",0)->order('num desc')->count();
                }
                if($news->toArray()){
                    $list = $news->toArray()['data'];
                }
                foreach ($list as $key => &$value) {
                    if($value['thumb']) {
                        $value['thumb'] = remote($appletid,$value['thumb'],1);
                    }else{
                        $pic="/image/noimage.jpg";
                        $value['thumb'] =remote($appletid,$pic,1);
                    }
                }
                $this->assign('list',$list);
                $this->assign('news',$news);
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
            return $this->fetch('pro');
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
                
                $yunfei_gg_list = Db::name("wd_xcx_freight")->where("uniacid", $appletid)->where("is_delete", 0)->field("id,name")->select();
                $this->assign('yunfei_gg_list',$yunfei_gg_list);
                $listV = Db::name('wd_xcx_cate')->where("uniacid",$appletid)->where("cid",0)->order('num desc')->select();
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
                $stores=Db::name("wd_xcx_store")->where("uniacid",$appletid)->select();
                $this->assign('stores',$stores);
                $cates = Db::name('wd_xcx_multicate')->where("uniacid",$appletid)->where('statue',1)->where("type","showPro")->select();
                $multipros = array();
                $allimg = [];
                $newsid = input("newsid");
                $newsinfo=array();
                if($newsid){
                    //有新闻号时，先判断该新闻是不是属于该小程序！
                    $newsget = Db::name('wd_xcx_products')->where("id",$newsid)->where("type","showPro")->find();
                    if($newsget['uniacid']==$appletid){
                        if($newsget['thumb']){
                            $newsget['thumb'] = remote($appletid,$newsget['thumb'],1);
                        }
                        if($newsget['shareimg']){
                            $newsget['shareimg'] = remote($appletid,$newsget['shareimg'],1);
                        }
                        $newsget['text'] = unserialize($newsget['text']);
                        $allimg = Db::name('wd_xcx_products_url')->where("randid",$newsget['onlyid'])->select();
                        foreach ($allimg as $key => &$value) {
                            $value['url'] = remote($appletid,$value['url'],1);
                        }
                        $newsinfo = $newsget;
                        $sons_keys =   Db::name('wd_xcx_multicates')->where("id",'in',$newsinfo['top_catas'])->select();
                        foreach ($sons_keys as $k => $v){
                            $sons_keys[$k]['sons'] = Db::name('wd_xcx_multicates')->where("pid",$v['id'])->select();
                        }
                        if(!empty($newsinfo['vipconfig'])){
                            $newsinfo['vipconfig'] = unserialize($newsinfo['vipconfig']);
                        }
                        //图片集
                        
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

                $this->assign('cates',$cates);
                $this->assign('forms',$jieguo);
                $this->assign('allimg',$allimg);
                $this->assign('sons_keys',$sons_keys);
                $this->assign('imgcount',count($allimg));
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
        $num = $_POST['num'];
        if($num){
            $data['num'] = $num;
        }
        //所属栏目
        $cid = $_POST['cid'];
        if($cid){
            $data['cid'] = $cid;
            // 获取栏目具体信息
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
        //开启会员购买设置
        //
        //
        $set1 = input("set1");
        $set2 = input("set2");
        $vipconfig = array(
            "set1" => $set1,
            "set2" => $set2
            );
        $data['vipconfig']  = serialize($vipconfig);
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
        //填写姓名号码
        $pro_flag = input("pro_flag");
        if($pro_flag){
            $data['pro_flag'] = $pro_flag;
        }else{
            $data['pro_flag'] = 0;
        }
        //商品标签
        $labels = input("labels");
        if($labels){
            $data['labels'] = $labels;
        }
        //访问量
        $hits = $_POST['hits'];
        if($hits){
            $data['hits'] = $hits;
        }
        //标题
        $title = $_POST['title'];
        if($title){
            $data['title'] = $title;
        }
        //已售数量
        $sale_num = input("sale_num");
        if($sale_num){
            $data['sale_num'] = $sale_num;
        }
        //门店价
        $price = input("price");
        if($price!==false){
            $data['price'] = $price;
        }
        //市场价
        $market_price = input("market_price");
        if($market_price!==false){
            $data['market_price'] = $market_price;
        }
        //库存
        $pro_kc = input("pro_kc");
        if($pro_kc){
            $data['pro_kc'] = $pro_kc;
        }
        //每人限购数
        $pro_xz = input("pro_xz");
        if(!isset($pro_xz)){
            
        }else{
            $data['pro_xz'] = $pro_xz;
        }
        //秒杀开始时间
        $sale_time = input("sale_time");
        if($sale_time){
            $data['sale_time'] = strtotime($sale_time);
        }
        //秒杀结束时间
        $sale_end_time = input("sale_end_time");
        if($sale_end_time){
            $data['sale_end_time'] = strtotime($sale_end_time);
        }
        if($sale_time && $sale_end_time){
            if($data['sale_end_time'] < $data['sale_time']){
                $this->error('秒杀开始时间不能大于结束时间,请重新设置!');
            }
        }
        
        //是否填写电话
        $pro_flag_tel = input("pro_flag_tel");
        if($pro_flag_tel){
            $data['pro_flag_tel'] = $pro_flag_tel;
        }else{
            $data['pro_flag_tel'] = 0;
        }
        //是否填写地址
        $pro_flag_add = input("pro_flag_add");
        if($pro_flag_add){
            $data['pro_flag_add'] = $pro_flag_add;
        }else{
            $data['pro_flag_add'] = 0;
        }
        //门店
       $stores=input("stores");
        if($stores){
            $data['stores']=$stores;
        }else{
            $data['stores']=null;
        }
        //是否确认订单
        $pro_flag_ding = input("pro_flag_ding");
        if($pro_flag_ding){
            $data['pro_flag_ding'] = $pro_flag_ding;
        }else{
            $data['pro_flag_ding'] = 0;
        }
        //取商品方式
           $data['kuaidi']=input('kuaidi');
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
       $data['yunfei_ggid'] = input('yunfei_ggid');

        //评论分享
//        $comment = $_POST['comment'];
//
//        $data['comment'] = $comment;
//        $share_gz = input('share_gz');
//        if($share_gz){
//
//            $data['share_gz'] = $share_gz;
//
//        }else{
//
//            $data['share_gz'] = 1;
//
//        }
//        $share_type = $_POST['share_type'];
//        $data['share_type'] = $share_type;
//        $share_score = $_POST['share_score'];
//        $data['share_score'] = $share_score;
//
//        $share_num = $_POST['share_num'];
//
//        $data['share_num'] = $share_num;
        //onlyid
        $onlyid = $_POST['onlyid'];
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
            $data['onlyid'] = $onlyid;
        }
        // 处理幻灯片
        if(!$onlyid){
        }else{
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
        }
        //缩略图
        $thumb = input("commonuploadpic1");
        if($thumb){
            $data['thumb'] = remote($data['uniacid'],$thumb,2);
        }
        //分享图
        $shareimg = input("commonuploadpic2");
        if($shareimg){
            $data['shareimg'] = remote($data['uniacid'],$shareimg,2);
        }
        //简介
        $desc = $_POST['desc'];
        if($desc){
            $data['desc'] = $desc;
        }
        //自定义表单
        $formset = input("formset");
        if($formset){
            $data['formset'] = $formset;
        }else{
            $data['formset'] = 0;
        }
        //文章详情
        $product_txt = $_POST['product_txt'];
        if($product_txt){
            $data['product_txt'] = $product_txt;
        }
        $con2 = input('con2');
        if($con2){
            $data['con2'] = $con2;
        }
        $con3 = input('con3');
        if($con3){
            $data['con3'] = $con3;
        }
        $data['buy_type'] = input("buy_type");
        if($data['buy_type']==null){
           $data['buy_type']="购买";
        }
        $newsid = input("newsid");
        $top_catas = Db::name('wd_xcx_multicate')->where("id",input('mulitcataid'))->find();
        $data['sons_catas'] = input('sons/a')?implode(',',input('sons/a')):'';
        $data['top_catas'] = $top_catas['top_catas']?implode(',',unserialize($top_catas['top_catas'])):'';
        $data['mulitcataid'] = input('mulitcataid');
        $data["get_share_gz"] = input('get_share_gz');
        $data['score'] = input('score');
        $data["get_share_score"] = input('get_share_score');
        $data["get_share_num"] = input('get_share_num');
        $data['ctime'] = time();
       $data['is_sale']=input("is_sale");
       if(input("is_sale")){
           $data['is_sale']=input("is_sale");
       }else{
           $data['is_sale']=0;
       }
        $muiltcate = input("muiltcate");
        if($muiltcate!= "0"){
            $data['multi'] = 1;
        }else{
           $data['multi'] = 0; 
        }
        $multipros = array();
        if($newsid){
            $res = Db::name('wd_xcx_products')->where("id",$newsid)->update($data);
        }else{
            $res = Db::name('wd_xcx_products')->insert($data);
        }
        if($res){
           $this->success('基础信息更新成功！',Url('Products/pro').'?appletid='.$data['uniacid']);
        }else{
          $this->error('基础信息更新失败，没有修改项！');
          exit;
        }
    }
    public function add_more(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $stores=Db::name("wd_xcx_store")->where("uniacid",$appletid)->select();
                $this->assign('stores',$stores);
                $jieguo = Db::name('wd_xcx_formlist')->where("uniacid",$appletid)->select();
                $this->assign('forms',$jieguo);
                $listV = Db::name('wd_xcx_cate')->where("uniacid",$appletid)->where("cid",0)->order('num desc')->select();
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
                
                $cates = Db::name('wd_xcx_multicate')->where("uniacid",$appletid)->where('statue',1)->where("type","showPro")->select();
                $allimg = "";
                $newsid = input("newsid");
                $newsinfo=array();
                if($newsid){
                    //有新闻号时，先判断该新闻是不是属于该小程序！
                    $newsget = Db::name('wd_xcx_products')->where("id",$newsid)->where("type","showPro")->find();
                    if($newsget['uniacid']==$appletid){
                        $newsget['thumb'] = remote($appletid,$newsget['thumb'],1);
                        $newsget['shareimg'] = remote($appletid,$newsget['shareimg'],1);
                        $newsget['text'] = unserialize($newsget['text']);
                        $allimg = Db::name('wd_xcx_products_url')->where("randid",$newsget['onlyid'])->select();
                        foreach ($allimg as $key => &$value) {
                            $value['url'] = remote($appletid,$value['url'],1);
                        }
                        $multipro = Db::name('wd_xcx_multipro')->where("proid",$newsid)->select();
                        if($cates){
                            foreach ($cates as $k => $v) {
                                foreach ($multipro as $ki => $vi) {
                                    if($v['id'] == $vi['multi_id'] ){
                                        $cates[$k]['flag'] =1;
                                    }
                                }
                                if(!isset($cates[$k]['flag'])){
                                    $cates[$k]['flag'] =0;
                                }
                            }
                        }else{
                            $cates = "";
                        }
                        
                        foreach($multipro as $ki =>$vi){
                            $cate_info = Db::name('wd_xcx_cate')->where("id",$vi['cid'])->find();
                            $multipros[$ki]['id'] = $vi['cid'];
                            $multipros[$ki]['name'] = $cate_info['name'];
                        }
                        $multipro_arr=array();
                        $cate_arr = Db::name('wd_xcx_multipro')->where("proid",$newsid)->find();
                        if($cate_arr){
                            $cates_i = Db::name('wd_xcx_multicate')->where('statue',1)->where('id',$cate_arr['multi_id'])->find();
                            if($cates_i){
                                foreach(unserialize($cates_i['cid']) as $ki => $vi){
                                    $cate_infos = Db::name('wd_xcx_cate')->whereOr("id",$vi)->whereOr("cid",$vi)->field('id,name')->select();
                                    foreach($cate_infos as $k =>$v){
                                        foreach($multipros as $kii =>$vii){
                                            if($v['id'] == $vii['id']){
                                                $cate_infos[$k]['flag'] =1;
                                            }
                                        }
                                        if(!isset($cate_infos[$k]['flag'])){
                                             $cate_infos[$k]['flag'] =0;
                                        }
                                    }
                                    array_push($multipro_arr,$cate_infos);
                                }   
                            }
                        }
                        $newsget['more_type'] = unserialize($newsget['more_type']);
                        $newsget['labels'] = unserialize($newsget['labels']);
                        if(!empty($newsget['vipconfig'])){
                            $newsget['vipconfig'] = unserialize($newsget['vipconfig']);
                        }
                        if($newsget['pro_flag_data_name']){
                            $newsget['pro_flag_data_name'] = explode(";", $newsget['pro_flag_data_name']);
                            if(count($newsget['pro_flag_data_name'])>2){
                                $newsget['afterdays'] = $newsget['pro_flag_data_name'][1];
                                $newsget['beforedays'] = $newsget['pro_flag_data_name'][2];
                                $newsget['modifydays'] = $newsget['pro_flag_data_name'][3];
                            }else{
                                $newsget['afterdays'] = 0;
                                $newsget['beforedays'] = 0;
                                $newsget['modifydays'] = 0;
                            }
                            
                            $newsget['pro_flag_data_name'] = $newsget['pro_flag_data_name'][0];
                        }else{
                            $newsget['afterdays'] = 0;
                            $newsget['beforedays'] = 0;
                            $newsget['modifydays'] = 0;
                            $newsget['pro_flag_data_name'] = "";
                        }
                        
                        $newsinfo = $newsget;
                        //查找自定义选择图的信息
                         // $tablepro = pdo_fetch("SELECT * FROM ".tablename('sudu8_page_table')." WHERE proname = :proname and uniacid = :uniacid ", array(':proname' => $item['title'] ,':uniacid' => $uniacid));
                        $tablepro = Db::name('wd_xcx_table') ->where('uniacid', $appletid) ->where('proname', $newsget['title']) ->find();
                        $columnstr = $tablepro['columnstr'] ? $tablepro['columnstr'] : "yyy,";
                        $rowstr = $tablepro['rowstr'] ? $tablepro['rowstr'] : "xxx,";
                        $selectstr = $tablepro['selectstr'] ? $tablepro['selectstr'] : "";
                        $column_arr = explode(",", chop($columnstr, ",")) ? explode(",", chop($columnstr, ",")): array();
                        $column_num = $tablepro['columnstr'] ? count($column_arr) : 1;
                        $row_arr = explode(",", chop($rowstr, ","));
                        $row_num = $tablepro['rowstr'] ? count($row_arr) : 1;
                        $select_temp = explode(",", chop($selectstr, ","));
                        $select_arr = array();
                        if($selectstr){
                            for($i = 0; $i < count($select_temp); $i++){
                                $temp = explode("a", $select_temp[$i]);
                                $select_arr[intval($temp[0])][intval($temp[1])] = 1;
                            }
                        }
                        $this->assign('columnstr', $columnstr);
                        $this->assign('column_arr', $column_arr);
                        $this->assign('row_arr', $row_arr);
                        $this->assign('select_arr', $select_arr);
                        $this->assign('rowstr', $rowstr);
                        $this->assign('selectstr', $selectstr);
                        $this->assign('column_num', $column_num);
                        $this->assign('row_num', $row_num);
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
                    foreach ($cates as $k => $v) {
                        $cates[$k]['flag'] = 0;
                    }
                    $selectstr = "";
                    $select_temp = explode(",", chop($selectstr, ","));
                    $select_arr = array();
                    if($selectstr){
                        for($i = 0; $i < count($select_temp); $i++){
                            $temp = explode("a", $select_temp[$i]);
                            $select_arr[intval($temp[0])][intval($temp[1])] = 1;
                        }
                    }
                    
                    $this->assign('columnstr', 'yyy,');
                    $this->assign('column_arr', array('yyy'));
                    $this->assign('row_arr', array('xxx'));
                    $this->assign('select_arr', $select_arr);
                    $this->assign('rowstr', 'xxx,');
                    $this->assign('selectstr', '');
                    $this->assign('column_num', 1);
                    $this->assign('row_num', 1);
                }
                $this->assign('allimg',$allimg);
                $this->assign('imgcount',count($allimg));
                $this->assign('cate',$listAll);
                $this->assign("multipro",$multipro_arr);
                $this->assign('cates',$cates);
                $this->assign('forms',$jieguo);
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
            return $this->fetch('add_more');
        }else{
            $this->redirect('Login/index');
        }
    }
    public function save_more(){
        $uniacid = input("appletid");
        $data['uniacid'] = $uniacid;
        $newsid = input("newsid");
        $duogg = input("duogg");
        $duoggarr = explode(',',substr($duogg, 0,strlen($duogg)-1));
        $kkk = serialize($duoggarr);
        $dggarr = array_chunk($duoggarr,4);
        $mmm = serialize($dggarr);
        $tongji = array();
        // $item = pdo_fetch("SELECT * FROM ".tablename('sudu8_page_products')." WHERE id = :id and uniacid = :uniacid ", array(':id' => $id ,':uniacid' => $uniacid));
        $item = Db::name('wd_xcx_products') ->where('id', $newsid) ->where('uniacid', $uniacid) ->find();
        // $tablepro = pdo_fetch("SELECT * FROM ".tablename('sudu8_page_table')." WHERE proname = :proname and uniacid = :uniacid ", array(':proname' => $item['title'] ,':uniacid' => $uniacid));
        $tablepro = Db::name('wd_xcx_table') ->where('uniacid', $uniacid) ->where('proname', $item['title']) ->find();
        foreach ($dggarr as &$rec) {
            $tjs = array(
                        "allnum"=>$rec[2],
                        "salenum"=>0,
                        "shennum"=>$rec[2]
                    );   
            $tongji[] = $tjs; 
        
        }
        
        // dump($is_sale);die;
        // if(isset($is_sale)){
        //     $data['is_sale']=$is_sale;
        // }else{
        //     $data['is_sale']=0;
        // }
        $uuu = serialize($tongji);
        $lab = input("labels");
        $newlab = explode(',',substr($lab, 0, strlen($lab)-1));
        // var_dump($newlab);exit;
        $labs = array();
        foreach ($newlab as $rec) {
            $nnn = explode(':',$rec);
            $key = $nnn[0];
            $val = $nnn[1];
            $v = array("$key"=>$val);
            $labs = array_merge($labs,$v);
        }
        $vvv = serialize($labs);
        //所属栏目
        $cid = input("cid");
        if($cid){
            $data['cid'] = $cid;
            // 获取栏目具体信息
            $lanmu = Db::name('wd_xcx_cate')->where("id",$cid)->find();
            $lanmu = $lanmu['name'];
        }
        $cid = intval(input("cid"));
        $pcid = Db::name('wd_xcx_cate')->where("id",$cid)->where("uniacid",$uniacid)->field("cid")->find();
        if($pcid['cid'] == 0){
            $pcid = $cid;
        }else{
            $pcid = intval($pcid['cid']);
        }
         //是否填写地址
        $pro_flag_add = input("pro_flag_add");
        if($pro_flag_add){
            $pro_flag_add = $pro_flag_add;
        }else{
            $pro_flag_add = 0;
        }
        //是否填写地址
        $is_score = input("is_score");
        if($is_score){
            $is_score = $is_score;
        }else{
            $is_score = 0;
        }
        //自定义选择图
        $table = array(
            'uniacid' => $uniacid,
            'name' => input('tablename'),
            'columnstr' => input('columnstr'),
            'rowstr' => input('rowstr'),
            'selectstr'=>input('selectstr'),
            'proname' => input('title')
        );
        $tableid = '';
        if(input('tableis')==1){
            //插入选座的数据
            if($tablepro){
                // var_dump("pdo_update");
                // pdo_update('sudu8_page_table', $table, array('proname' => $item['title'] ,'uniacid' => $uniacid));
                Db::name('wd_xcx_table') ->where('uniacid', $uniacid) ->where('proname', $item['title']) ->update($table);
            }else{
                // var_dump("insert");
                // pdo_insert('sudu8_page_table', $table);
                $newtable_id =  Db::name('wd_xcx_table') ->where('uniacid', $uniacid) ->where('proname', $item['title']) ->insertGetid($table);
            }

            $tablepro2 = Db::name('wd_xcx_table') ->where('uniacid', $uniacid) ->where('proname', $item['title']) ->find();
             //第一次插入table
            if($tablepro2['id']){
                $tableid = $tablepro2['id'];
            }else{
                $tableid = $newtable_id;
            }
        }
        // $tablepro2 = pdo_fetch("SELECT * FROM ".tablename('sudu8_page_table')." WHERE proname = :proname and uniacid = :uniacid ", array(':proname' => $_GPC['title'] ,':uniacid' => $uniacid));
        
       
        $formset = input("formset");
        if($formset){
            $formset = $formset;
        }else{
            $formset = 0;
        }
        $score_num = input("score_num");
        if($score_num){
            $score_num = $score_num;
        }else{
            $score_num = 0;
        }
        $afterdays = input('afterdays') ? input('afterdays') : 0;
        $beforedays = input('beforedays') ? input('beforedays') : 0;
        $modifydays = input('modifydays') ? input('modifydays') : 0;
        $pro_flag_data_name = input('pro_flag_data_name') ? input('pro_flag_data_name') : '上门时间';
        $pro_flag_data_name = $pro_flag_data_name.';'. $afterdays . ";" . $beforedays . ";" . $modifydays;
        $data = array(
            'uniacid' => $uniacid,
            'cid' => intval(input('cid')),
            'pcid' => $pcid,
            'num' => intval(input('num')),
            'type' => 'showPro',
            'type_x' => intval(input('type_x')),
            'type_y' => intval(input('type_y')),
            'type_i' => intval(input('type_i')),
            'hits' => intval(input('hits')),
            'sale_num' => intval(input('sale_num')),
            'title' => addslashes(input('title')),
            'desc'=>input('desc'),
            'ctime' => time(),
            'price'=>input('price'),
            'market_price'=>input('market_price'),
            'score'=>input('score'),
            'pro_flag'=> input('pro_flag')? input('pro_flag'):0,
            'pro_flag_tel'=> input('pro_flag_tel')?input('pro_flag_tel'):0,
            'pro_flag_data'=> input('pro_flag_data')?input('pro_flag_data'):0,
            'pro_flag_data_name'=> $pro_flag_data_name,
            'pro_flag_time'=> input('pro_flag_time')?input('pro_flag_time'):0,
            'pro_flag_ding'=>input('pro_flag_ding')?input('pro_flag_ding'):0,
            'product_txt'=>htmlspecialchars_decode(input('product_txt'), ENT_QUOTES),
            'labels'=>$vvv,
            'is_more'=>1,
            'more_type'=>$kkk,
            "more_type_x"=>$mmm,
            "more_type_num"=>$uuu,
            'flag'=>input('flag'),
            'buy_type'=>input('buy_type'),
            'tableis'=>input('tableis'),
            'lanmu' => $lanmu,
            'pro_flag_add' => $pro_flag_add,
            'is_score' => $is_score,
            'score_num' => $score_num,
            "formset" => $formset,
            'seller_remind'=>input('seller_remind'),
            'foottitle'=>input('foottitle'),
            'tableid' => $tableid,
            'is_sale' => input('is_sale') ? input('is_sale') : 0,
        );
        //门店
       $stores=input("stores");
        if($stores){
            $data['stores']=$stores;
        }else{
            $data['stores']=null;
        }
        $data["get_share_gz"] = input('get_share_gz');
        $data["get_share_score"] = input('get_share_score');
        $data["get_share_num"] = input('get_share_num');
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
            $data['onlyid'] = $onlyid;
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
        //缩略图
        $thumb = input("commonuploadpic1");
        if($thumb){
            $data['thumb'] = remote($data['uniacid'],$thumb,2);
        }
        //分享图
        $shareimg = input("commonuploadpic2");
        if($shareimg){
            $data['shareimg'] = remote($data['uniacid'],$shareimg,2);
        }
        $muiltcate = input("muiltcate");
        if($muiltcate!= "0"){
            $data['multi'] = 1;
        }else{
           $data['multi'] = 0; 
        }
        //会员购买设置
        $set1 = input("set1");
        $set2 = input("set2");
        $vipconfig = array(
            "set1" => $set1,
            "set2" => $set2
            );
        $data['vipconfig']  = serialize($vipconfig);
        if (!$newsid) {
            $res = Db::name('wd_xcx_products')->insert($data);
        } else {
            $res = Db::name('wd_xcx_products')->where("id",$newsid)->where("uniacid",$uniacid)->update($data);
        }
        if($res){
            if($muiltcate!="0"){
                if($newsid){
                    $multi['proid'] = $newsid;
                    $proid = Db::name('wd_xcx_multipro')->where('proid',$newsid)->delete();
                }else{
                    $proid = Db::name('wd_xcx_products')->order('id desc')->field('id')->find();
                    $multi['proid'] = $proid['id'];
                }
                
                $multi['multi_id'] = intval($muiltcate);
                $cate_arr = $_POST["catearr"];
                foreach ($cate_arr as $key => $value) {
                    if($value!="0"){
                        $multi['cid'] = $value;
                        Db::name('wd_xcx_multipro')->insert($multi);
                    }
                }
               $multi['multi_id'] = $muiltcate;
            }
          $this->success('商品信息更新成功！',Url('Products/index').'?appletid='.$uniacid);
        }else{
          $this->error('商品信息更新失败，没有修改项！');
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

    //预约预定批量删除操作
    public function delall(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $array1=input('newslist');
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

    //秒杀批量删除操作
    public function delallm(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $array1=input('mpros');
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