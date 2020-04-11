<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;

use app\index\model\ImsSudu8PageFlashsaleCate as cate;
use app\index\model\Applet;
use app\index\model\ImsSudu8PageProducts as Goods;
use app\index\model\ImsSudu8PageFreight;
use app\index\validate\ImsSudu8PageProducts as Validate;

class Reserve extends Base
{   
    // 栏目列表
    public function catelist(){

        if(check_login()){
            if(powerget()){
                $uniacid = input('appletid');
                $app = new Applet;
                $appinfo = $app ->getAppInfo();
                $this->assign('applet',$appinfo);

                //获取所有分类
                $cate = new cate();
                $cates = $cate -> getReserveAllCate();
                //分类总数
                $count = $cate -> getReserveChildCateCount();

                $listV = $cates->toArray();
                $listAll = array();
                foreach($listV['data'] as $key=>$val) {
                    $id = intval($val['id']);
                   
                    $listP = $cate ->get($id); 
                    if($listP['catepic']){
                        $listP['catepic'] = remote($uniacid,$listP['catepic'],1);
                    }else{
                        $pic="/image/noimage_1.png";
                        $listP['catepic'] =  remote($uniacid,$pic,1);
                    }
                    $listS = $cate ->getReserveChildCate($id); 
                    foreach ($listS as $ki => $vi) {
                        if($vi['catepic']){
                            $listS[$ki]['catepic'] = remote($uniacid,$vi['catepic'],1);
                        }else{
                            $pic2="/image/noimage_1.png";
                            $listS[$ki]['catepic'] =  remote($uniacid,$pic2,1);
                        }
                    }
                    $listF[0] = $listP;
                   
                    //子集数据量
                    $zjcount = $cate ->getReserveChildCateCount($id); 
                    $listF['data'] = $listS;
                    $listF['zcount'] = $zjcount;

                    array_push($listAll,$listF);
                }


                $this->assign('cates',$listAll);
                $this->assign('news',$cates);
                $this->assign('counts',$count);

                return $this->fetch('catelist');
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
        }else{
            $this->redirect('Login/index');
        }
    }


    //添加栏目
    public function add(){
        if(check_login()){
            if(powerget()){
                $uniacid = input('appletid');
                $cateid = input('cateid');
                $is_top = 0;
                $app = new Applet;
                $appinfo = $app ->getAppInfo();
                $this->assign('applet',$appinfo);

                //获取栏目信息
                $cate = new cate;
                $cates = $cate ->getReserveCates();
                $this->assign('cate',$cates);

                $allimg = '';
                $cateinfo = '';
                $cateurlid = 0;

                $huan = [];
                //判断是编辑还是新增
                if($cateid){   //编辑
                    $cateinfo = $cate ->get($cateid) ->toArray();
                    if($cateinfo['uniacid'] != $uniacid){
                        $usergroup = Session::get('usergroup');
                        if($usergroup==1){
                            $this->error("找不到该栏目，或者该栏目不属于本项目");
                        }
                        if($usergroup==2){
                            $this->error("找不到该栏目，或者该栏目不属于本项目");
                        }
                    }else{
                        $cateinfo['cateconf'] = unserialize($cateinfo['cateconf']);
                        if($cateinfo['cid']==0){
                            $cateurlid = 1;
                        }

                        if($cateinfo['catepic']){
                            $cateinfo['catepic'] = remote($uniacid,$cateinfo['catepic'],1);
                        }
                        if($cateinfo['randid']){
                            $allimg = $cate->slide() ->where('randid', $cateinfo['randid']) ->select();
                            foreach ($allimg as $key => $value) {
                                $v = $value ->toArray();
                                $v['url'] = remote($uniacid, $v['url'], 1);
                                array_push($huan, $v);
                            }
                            
                        }
                    }
                    if($cateinfo['cid'] == 0){
                        $is_top = 1;
                    }
                }else{
                    $cateid = 0;
                }

                $this->assign('allimg',$huan);
                $this->assign('cateid',$cateid);
                $this->assign('is_top',$is_top);
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
            return $this->fetch('add');
        }else{
            $this->redirect('Login/index');
        }
    }


    //保存栏目
    public function save(){
        $uniacid = input('appletid');
        $cate = new cate;
        $data = array();
        $data['uniacid'] = $uniacid;
        //排序
        $num = input("num");
        if($num){
            $data['num'] = $num;
        }else{
            $data['num'] = 0;
        }
        
        $data['randid'] = input("randid");
        $imgsrcs = input("imgsrcs/a");

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

        $catepic = input("commonuploadpic");
        if($catepic){
            $data['catepic'] = remote($data['uniacid'],$catepic,2);
        }else{
            $data['catepic']="";
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

        $data['type'] = 'showPro';

        //内容列表样式
        $list_style = input("list_style");
        if($list_style){
            $data['list_style'] = $list_style;
        }else{
            $data['list_style']=11;
        }
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

        Db::startTrans();
        try{
            $products_url = model('ProductsUrl');
            //存储幻灯片图片
            if($imgsrcs){
                $imgarr = array();
                foreach ($imgsrcs as $k => $v) {
                    $products_url_data = [];
                    $products_url_data['randid'] = $data['randid'];
                    $products_url_data['appletid'] = $data['uniacid'];
                    $products_url_data['url'] = remote($data['uniacid'],$v,2);
                    $products_url_data['dateline'] = time();
                    array_push($imgarr, $products_url_data);
                }
                $products_url ->saveAll($imgarr);
            }
            if($id > 0){
                $cate ->save($data, ['id' => $id]);
            }else{
                $data['catefor'] = 'reserve';
                $res = $cate ->save($data);
            }
            Db::commit();
            
        }catch (\Exception $e) {
            Db::rollback();
            $this->error('新增失败'.$e->getMessage());
        }

        $this->success('栏目信息新增/更新成功！',Url('Reserve/catelist').'?appletid='.$data['uniacid']);
    }

    //删除栏目
    public function del(){
        $uniacid = input('appletid');
        $cateid = input('cateid');
        $cate = model('ImsSudu8PageFlashsaleCate');
        $goods = model('ImsSudu8PageProducts');
        $is = $goods->where('cid='.$cateid.' OR pcid='.$cateid)->count();
        if($is){
            $this->error('删除失败，栏目下存在商品不可删除！',Url('Reserve/catelist').'?appletid='.$uniacid);
        }
        $result = $cate -> get(['id' => $cateid]);
        if($result){
            $res = $result -> delete();
            if($res){
                $this->success('栏目删除成功！',Url('Reserve/catelist').'?appletid='.$uniacid);
            }else{
                $this->error('删除失败，栏目不存在或已删除！',Url('Reserve/catelist').'?appletid='.$uniacid);
            }
        }else{
            $this->error('删除失败，栏目不存在或已删除！',Url('Reserve/catelist').'?appletid='.$uniacid);
        }
    }

    private function ee(){
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

    //批量删除栏目
    public function delall(){
        $uniacid = input('appletid');
        $cateids = input('cateids');
        $cate = new cate();
        $result = $cate -> destroy($cateids);
        if($result){
            $this->success('栏目删除成功！',Url('Reserve/catelist').'?appletid='.$uniacid);
        }else{
            $this->success('删除失败，栏目不存在或已删除！',Url('Reserve/catelist').'?appletid='.$uniacid);
        }
    }



    //商品列表
    public function pro(){
        if(check_login()){
            if(powerget()){
                $uniacid = input('appletid');
                $cid=input("cid")?input("cid"):0;
                $title=input("key");
                $app = new Applet;
                $appinfo = $app ->getAppInfo();
                $this->assign('applet',$appinfo);

                $cate = model('ImsSudu8PageFlashsaleCate');

                $listV = $cate -> getReserveAllCate() ->toArray()['data'];
                $listAll = array();
                foreach($listV as $key=>$val) {
                    $id = intval($val['id']);
                    $listP = $cate -> getReserveCateById($id);
                    $listS = $cate -> getReserveChildCate($id);
                    //子集数据量
                    $zjcount = $cate ->getReserveChildCateCount($id);
                    $listF[0] = $listP;
                    $listF['data'] = $listS;
                    $listF['zcount'] = $zjcount;
                    array_push($listAll,$listF);
                }
                $this->assign('cate',$listAll);

                //获取子集
                // $listallcate=Db::name('wd_xcx_cate')->where("cid",$cid)->select();
                $listallcate = $cate ->getReserveChildCate($cid);
                $array1=array();
                for($a=0;$a<count($listallcate);$a++){
                    array_push($array1,$listallcate[$a]['id']);
                }
                array_push($array1,$cid);

                $goods = model('ImsSudu8PageProducts');
                $array2 = implode(",", $array1);
                $array2 = '['.$array2.']';
                $where = '';
                if($cid > 0){
                    $where = 'and cid in '.$array2;
                }
                if($title){
                    $where .= 'and title like %'.$title.'%';
                }
                $news = $goods ->GetByTitle($title) ->GetByCid($array1,$cid) ->GetReserveGoods($cid, $title) ->paginate(10, false, [ 'query' => array('appletid'=>$uniacid, 'cid'=>$cid, 'title'=>$title)]);
                $news = $news ->each(function($item)use($uniacid){
                    if($item->thumb){
                        $item->thumb = remote($uniacid, $item->thumb, 1);
                    }else{
                        $pic="/image/noimage.jpg";
                        $item->thumb = remote($uniacid, $pic, 1);
                    }
                    
                });

                $count = count($news);

                $this->assign('list',$news);
                // $this->assign('news',$news);
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

    public function addpro(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $app = new Applet;
                $appinfo = $app ->getAppInfo();
                $this->assign('applet',$appinfo);

                $tableis = input('tableis');

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
                
                $jieguo = Db::name('wd_xcx_formlist')->where("uniacid",$appletid) ->order('id desc')->select();
                $this->assign('forms',$jieguo);

                $stores=Db::name("wd_xcx_store")->where("uniacid",$appletid)->select();
                $this->assign('stores',$stores);

                $cate = model('ImsSudu8PageFlashsaleCate');
                $listV = $cate -> getReserveAllCate() ->toArray()['data'];
                $listAll = array();
                foreach($listV as $key=>$val) {
                    $id = intval($val['id']);
                    $listP = $cate -> getReserveCateById($id);
                    $listS = $cate -> getReserveChildCate($id);
                    //子集数据量
                    $zjcount = $cate ->getReserveChildCateCount($id);
                    $listF[0] = $listP;
                    $listF['data'] = $listS;
                    $listF['zcount'] = $zjcount;
                    array_push($listAll,$listF);
                }
                $this->assign('cate',$listAll);

                $goods = model('ImsSudu8PageProducts');
                $allimg = [];
                $newsid = input("newsid");
                $newsinfo=array();
                if($newsid){
                    //有新闻号时，先判断该新闻是不是属于该小程序！
                    $newsget = $goods ->get($newsid);
                    if($newsget['uniacid']==$appletid){
                        if($newsget['thumb']){
                            $newsget['thumb'] = remote($appletid,$newsget['thumb'],1);
                        }
                        if($newsget['shareimg']){
                            $newsget['shareimg'] = remote($appletid,$newsget['shareimg'],1);
                        }
                        $newsget['text'] = unserialize($newsget['text']);
                        $allimg = $goods->slide() ->where('randid', $newsget['randid']) ->select();
                        foreach ($allimg as $key => $value) {
                            $allimg[$key]['url'] = remote($appletid,$value['url'],1);
                        }

                        $newsget['more_type'] = unserialize($newsget['more_type']);
                        $newsget['labels'] = unserialize($newsget['labels']);
                        if(!empty($newsget['vipconfig'])){
                            $newsget['vipconfig'] = unserialize($newsget['vipconfig']);
                            if(!isset($newsget['vipconfig']['set3'])){
                                $newsget['vipconfig']['set3'] = 0;
                            }
                        }

                        $newsget['discount'] = $newsget['discount'] ? unserialize($newsget['discount']):[]; 
                        foreach ($grade_arr as $key => $value) {
                            $grade_arr[$key]['discount'] = '';
                            if($newsget['discount']){
                                foreach ($newsget['discount'] as $ks => $vs) {
                                    if($value['grade'] == $vs['grade']){
                                        $grade_arr[$key]['discount'] = $vs['discount'];
                                    }
                                }
                            }
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
                        $tablepro = Db::name('wd_xcx_table') ->where('uniacid', $appletid) ->where('proname', $newsget['title']) ->find();
                        $columnstr = $tablepro['columnstr'] ? $tablepro['columnstr'] : "yyy,";
                        $rowstr = $tablepro['rowstr'] ? $tablepro['rowstr'] : "xxx,";
                        $selectstr = $tablepro['selectstr'] ? $tablepro['selectstr'] : "";
                        $tablename = $tablepro['name'] ? $tablepro['name'] : "确认选择此选项";
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
                        $this->assign('tablename', $tablename);
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
                    $selectstr = "";
                    $select_temp = explode(",", chop($selectstr, ","));
                    $select_arr = array();
                    if($selectstr){
                        for($i = 0; $i < count($select_temp); $i++){
                            $temp = explode("a", $select_temp[$i]);
                            $select_arr[intval($temp[0])][intval($temp[1])] = 1;
                        }
                    }
                    foreach ($grade_arr as $key => $value) {
                        $grade_arr[$key]['discount'] = '';
                    }
                    $tablename = "确认选择此选项";
                    $this->assign('tablename', $tablename);
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
                $this->assign('forms',$jieguo);
                $this->assign('newsid',$newsid);
                $this->assign('newsinfo',$newsinfo);
                $this->assign('tableis',$tableis);
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
            return $this->fetch('addpro');
        }else{
            $this->redirect('Login/index');
        }
    }
    public function savePro(){
        $uniacid = input("appletid");
        $data['uniacid'] = $uniacid;
        $newsid = input("newsid");
        $duogg = input("duogg");
        $duoggarr = explode(',',substr($duogg, 0,strlen($duogg)-1));
        $kkk = serialize($duoggarr);
        $dggarr = array_chunk($duoggarr,4);
        $mmm = serialize($dggarr);
        $tongji = array();
        $item = Db::name('wd_xcx_products') ->where('id', $newsid) ->where('uniacid', $uniacid) ->find();
        $tablepro = Db::name('wd_xcx_table') ->where('uniacid', $uniacid) ->where('proname', $item['title']) ->find();
        foreach ($dggarr as &$rec) {
            $tjs = array(
                        "allnum"=>$rec[2],
                        "salenum"=>0,
                        "shennum"=>$rec[2]
                    );   
            $tongji[] = $tjs; 
        
        }

        $uuu = serialize($tongji);
        $lab = input("labels");
        if($lab){
            $newlab = explode(',',substr($lab, 0, strlen($lab)-1));
            $labs = array();
            foreach ($newlab as $rec) {
                $nnn = explode(':',$rec);
                $key = $nnn[0];
                $val = $nnn[1];
                $v = array("$key"=>$val);
                $labs = array_merge($labs,$v);
            }
            $vvv = serialize($labs);
        }else{
            $vvv = "";
        }
        //所属栏目
        $cid = input("cid");
        if($cid){
            $data['cid'] = $cid;
            // 获取栏目具体信息
            $lanmu = model('ImsSudu8PageFlashsaleCate') ->get($cid);
            $data['lanmu'] = $lanmu['name'];
            if($lanmu['cid'] == 0){
                $data['pcid'] = $cid;
            }else{
                $data['pcid'] = $lanmu['cid'];
            }
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
        $tableis = input('tableis');
        $tableid = '';
        if(input('tableis')==1){
            //插入选座的数据
            if($tablepro){
                Db::name('wd_xcx_table') ->where('uniacid', $uniacid) ->where('proname', $item['title']) ->update($table);
                $tableid = $tablepro['id'];
            }else{
                $newtable_id =  Db::name('wd_xcx_table') ->insertGetid($table);
                if($tableid){
                    $tablepro2 = Db::name('wd_xcx_table') ->where('uniacid', $uniacid) ->where('proname', $item['title']) ->find();
                     //第一次插入table
                    if($tablepro2['id']){
                        $tableid = $tablepro2['id'];
                    }else{
                        $tableid = $newtable_id;
                    }
                }
            }
        
        }
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
        $data['uniacid'] = $uniacid;
        $data['num'] = intval(input('num'));
        $data['type'] = 'reserve';
        $data['type_i'] = intval(input('type_i'));
        $data['hits'] = intval(input('hits'));
        $data['sale_num'] = intval(input('sale_num'));
        $data['title'] = addslashes(input('title'));
        $data['desc'] =input('desc');
        $data['ctime'] = time();
        $data['price']=input('price');
        $data['market_price']=input('market_price');
        $data['score']=input('score');
        $data['pro_flag']= input('pro_flag')? input('pro_flag'):0;
        $data['pro_flag_tel']= input('pro_flag_tel')?input('pro_flag_tel'):0;
        $data['pro_flag_data']= input('pro_flag_data')?input('pro_flag_data'):0;
        $data['pro_flag_data_name']= $pro_flag_data_name;
        $data['pro_flag_time']= input('pro_flag_time')?input('pro_flag_time'):0;
        $data['pro_flag_ding']=input('pro_flag_ding')?input('pro_flag_ding'):0;
        $data['product_txt']=htmlspecialchars_decode(input('product_txt'), ENT_QUOTES);
        $data['labels']=$vvv;
        $data['is_more']=1;
        $data['more_type']=$kkk;
        $data["more_type_x"]=$mmm;
        $data["more_type_num"]=$uuu;
        $data['flag']=input('flag');
        $data['buy_type']=input('buy_type');
        $data['tableis']=input('tableis');
        $data['pro_flag_add'] = $pro_flag_add;
        $data['is_score'] = $is_score;
        $data['score_num'] = $score_num;
        $data["formset"] = $formset;
        $data['seller_remind']=input('seller_remind');
        $data['foottitle']=input('foottitle');
        $data['tableid'] = $tableid;
        $data['is_sale'] = input('is_sale') ? input('is_sale') : 0;
        $data["get_share_gz"] = input('get_share_gz');
        $data["get_share_score"] = input('get_share_score');
        $data["get_share_num"] = input('get_share_num');
        $data['scoreback'] = input('scoreback');
        $data['video'] = input('video');
        $data['stores'] = input('stores') ? input('stores') : '';



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
        
        //会员购买设置
        $set1 = input("set1");
        $set2 = input("set2");
        $set3 = input("set3");
        $vipconfig = array(
            "set1" => $set1,
            "set2" => $set2,
            "set3" => $set3
            );
        $data['vipconfig']  = serialize($vipconfig);
        //会员折扣
        $data['discount_status'] = input('discount_status') ? input('discount_status') : 0;
        $valarr = input('valarr/a')?input('valarr/a'):[];
        $discount = [];
        //会员等级
        $grade_arr = Db::name("wd_xcx_vipgrade")->where("uniacid", $uniacid)->order('grade asc')->select();
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

        Db::startTrans();
        try{
            $validate = new Validate;
            if (!$validate->scene('add')->check($data)) {
                throw new \Exception($validate->getError());
            }
            $products_url = model('ProductsUrl');
            $randid = input('randid');
            if($randid){
                $imgsrcs = input("imgsrcs/a");
                $data['randid'] = $randid;
            }
            //存储幻灯片图片
            if($imgsrcs){
                $imgarr = array();
                foreach ($imgsrcs as $k => $v) {
                    $products_url_data = [];
                    $products_url_data['randid'] = $data['randid'];
                    $products_url_data['appletid'] = $data['uniacid'];
                    $products_url_data['url'] = remote($data['uniacid'],$v,2);
                    $products_url_data['dateline'] = time();
                    array_push($imgarr, $products_url_data);
                }
                $products_url ->saveAll($imgarr);
            }

            // 处理幻灯片
            if(!$randid){
            }else{
                $silde = Db::name('wd_xcx_products_url')->where("randid",$randid)->select();
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
            if($newsid!=0){
                model('ImsSudu8PageProducts') ->save($data, ['id' => $newsid]);
            }else{
                model('ImsSudu8PageProducts') ->save($data);
            }

            Db::commit();

        }catch (\Exception $e){
            Db::rollback();
            $this->error('新增/编辑预约预定商品失败，'.$e->getMessage());
        }

        $this->success('预约预定商品新增/更新成功！',Url('Reserve/pro').'?appletid='.$uniacid);
    }

    //删除商品
    public function delpro(){
        $uniacid = input('appletid');
        $newsid = input('newsid');

        $res = Db::name('wd_xcx_products')->where("uniacid",$uniacid)->where('id',$newsid)->delete();
        if($res){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
        
    }

    //批量删除操作
    public function delallpro(){
        $appletid = input("appletid");
        $array1=input('mpros');
        $arr=explode(',',$array1);
        $res = Db::name('wd_xcx_products')->where("uniacid",$appletid)->where('id',"in",$arr)->delete();
        if($res){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }

    public function orders(){
        if(check_login()){
            if(powerget()){
                $id = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $select_state=99;
                if(in_array(input("select_state"), ['0','1','2','-1','-2', '5'])){
                    $select_state=input('select_state');
                }
                $datetimepicker=input("datetimepicker");
                $end_datetimepicker=input("end_datetimepicker");
                $datetimepicker3=input("datetimepicker3");
                $end_datetimepicker2=input("end_datetimepicker2");
                $order=input("order");

                //获取所有员工
                $staff = Db::name('wd_xcx_staff') ->where('uniacid', $id) ->field('id, realname, mobile') ->select();
                $this->assign('staff', $staff);

                $where='';
                if(!empty($datetimepicker)){
                    if($where==''){
                        $where .= ' creattime >= ' . strtotime($datetimepicker);
                    }else{
                        $where .= ' and creattime >= ' . strtotime($datetimepicker);
                    }

                }
                if(!empty($end_datetimepicker)){
                    if($where==''){
                        $where .= ' creattime <= ' .strtotime($end_datetimepicker);
                    }else{
                        $where .= ' and creattime <= ' .strtotime($end_datetimepicker);
                    }

                }
                if(in_array($select_state, ['0','1','2','-1','-2','5'])){
                    if($where==''){
                        $where .= ' flag = ' . $select_state;
                    }else{
                        $where .= ' and flag = ' . $select_state;
                    }
                }
                if(!empty($datetimepicker3)){
                    if($where==''){
                        $where .= ' appoint_date >= ' . strtotime($datetimepicker3);
                    }else{
                        $where .= ' and appoint_date >= ' . strtotime($datetimepicker3);
                    }

                }
                if(!empty($end_datetimepicker2)){
                     if($where==''){
                         $where .= ' appoint_date <= ' . strtotime($end_datetimepicker2);
                     }else{
                         $where .= ' and appoint_date <= ' . strtotime($end_datetimepicker2);
                     }

                }
                if(!empty($order)){
                    if($where==''){
                        $where .= ' order_id LIKE "%'.$order.'%"';
                    }else{
                        $where .= ' and order_id LIKE "%'.$order.'%"';
                    }

                }
                $order = model('ImsSudu8PageOrder');
                $orders = $order->getReserveOrders($where, $order, $end_datetimepicker2, $end_datetimepicker, $select_state, $datetimepicker,$datetimepicker3);
                $count = count($orders);
                $neworder = $orders -> toArray();
                foreach ($neworder['data'] as &$row) {
                    $row['order_yue'] = floatval($row['true_price']) - floatval($row['pay_price']);
                    if ($row['custime']) {
                        $row['custime'] = date("Y-m-d H:i:s", $row['custime']);
                    } else {
                        $row['custime'] = "";
                    }

                    $row['thumb'] = remote($id, $row['thumb'], 1);
                    $row['discounts_money'] = round($row['price'] - $row['price'] * $row['discounts'] * 0.1, 2);

                    $row['creattime'] = date("Y-m-d H:i:s", $row['creattime']);
                    $userinfo = getNameAvatar($row['suid'], $id);
                    if ($userinfo['nickname']) {
                        $row['nickname'] = $userinfo['nickname'];
                    } else {
                        $row['nickname'] = "";
                    }
                    $superuser = model("ImsSudu8PageSuperuser");  //等于头部应用 use app\index\model\ImsSudu8PageSuperuser
                    $superuserInfo = $superuser -> get($row['suid']);
                    if($superuserInfo){
                        if ($superuserInfo->phone) {
                            $row['mobile'] = $superuserInfo->phone;
                        } else {
                            $row['mobile'] = "";
                        }
                    }else{
                        $row['mobile'] = "";
                    }

                    if ($row['is_more'] == 0) {
                        $row['beizhu'] = "姓名：" . $row['pro_user_name'] . ",电话：" . $row['pro_user_tel'] . "地址：" . $row['pro_user_add'] . ",备注：" . $row['pro_user_txt'];
                    }
                    $row['order_duo'] = unserialize($row['order_duo']);
                    if ($row['hxinfo'] == "") {
                        $row['hxinfo2'] = "无";
                    } else {
                        $row['hxinfo'] = unserialize($row['hxinfo']);
                        if ($row['hxinfo'][0] == 1) {
                            $row['hxinfo2'] = "系统核销";
                        }else if($row['hxinfo'][0] == '密码核销' || $row['hxinfo'][0] == '管理员核销'){
                           $row['hxinfo2'] = $row['hxinfo'][0];
                        }else if($row['hxinfo'][0]=='核销员核销'){

                            $row['hxinfo2']=$row['hxinfo'][1].'核销';

                        // } else {
                        //     $store = Db::name('wd_xcx_store')->where("id", $row['hxinfo'][1])->where("uniacid", $id)->find();
                        //     $staff = Db::name('wd_xcx_staff')->where("id", $row['hxinfo'][2])->where("uniacid", $id)->find();
                        //     $row['hxinfo2'] = "门店：" . $store['title'] . "</br>员工：" . $staff['realname'];
                        }
                    }
                    $row['yhInfo_msg'] = array();
                    if (!empty($row['yhinfo'])) {
                        $yhInfo = unserialize($row['yhinfo']);
                        $row['yhInfo_msg']['yhInfo_yunfei'] = $yhInfo['yunfei'];
                        $row['yhInfo_msg']['yhInfo_score'] = $yhInfo['score'];
                        $row['yhInfo_msg']['yhInfo_yhq'] = $yhInfo['yhq'];
                        $row['yhInfo_msg']['yhInfo_mj'] = $yhInfo['mj'];
                    } else {
                        $row['yhInfo_msg']['yhInfo_yunfei'] = 0;
                        if ($row['dkscore'] > 0) {
                            // $jfgz = pdo_get("sudu8_page_rechargeconf", array("uniacid"=>$uniacid));
                            $jfgz = Db::name('wd_xcx_rechargeconf')->where('uniacid', $id)->find();
                            $row['yhInfo_msg']['yhInfo_score']['msg'] = $row['dkscore'] . "抵扣" . floatval($row['dkscore']) * floatval($jfgz['money']) / floatval($jfgz['score']);
                            $row['yhInfo_msg']['yhInfo_score']['money'] = floatval($row['dkscore']) * floatval($jfgz['money']) / floatval($jfgz['score']);
                        } else {
                            $row['yhInfo_msg']['yhInfo_score']['msg'] = "未使用积分";
                            $row['yhInfo_msg']['yhInfo_score']['money'] = 0;
                        }
                        if ($row['coupon']) {
                            //查询优惠劵
                            $coupon = Db::name('wd_xcx_coupon_user')->alias('a')->join('wd_xcx_coupon b', 'a.cid = b.id', 'left')->where('a.uniacid', $id)->where('a.flag', 1)->field('b.title,b.price')->find();
                            $row['yhInfo_msg']['yhInfo_yhq']['msg'] = $coupon['title'];
                            $row['yhInfo_msg']['yhInfo_yhq']['money'] = $coupon['price'];
                        } else {
                            $row['yhInfo_msg']['yhInfo_yhq']['msg'] = "未使用优惠券";
                            $row['yhInfo_msg']['yhInfo_yhq']['money'] = 0;
                        }
                        $row['yhInfo_msg']['yhInfo_mj']['msg'] = "";
                        $row['yhInfo_msg']['yhInfo_mj']['money'] = 0;
                    }
                    if ($row['formid']) {
                        // $arr2 = pdo_fetchcolumn("SELECT val FROM ".tablename('sudu8_page_formcon')." WHERE uniacid = :uniacid  and id = :id", array(':uniacid' => $uniacid,':id'=>$res['formid']));
                        $arr2 = Db::name('wd_xcx_formcon')->where('uniacid', $id)->where('id', $row['formid'])->field('val')->find();
                        $row['val'] = unserialize($arr2['val']);
                    } else if($row['beizhu_val']){
                        $row['val'] = unserialize($row['beizhu_val']);
                    }
                    if ($row['emp_id']) {
                        $emp = Db::name('wd_xcx_staff')->where('id', $row['emp_id'])->field('id, realname, mobile')->find();
                        $row['emp'] = $emp;
                    }

                    $row['modify_info'] = $row['modify_info'] ? unserialize($row['modify_info']) : "";
                    //获取预约类型
                    $pro_info = Db::name('wd_xcx_products')->where('uniacid', $id)->where('id', $row['pid'])->field('tableis')->find();
                    $row['tableis'] = $pro_info['tableis'];
                }
                $this->assign('neworder',$neworder);
                $this->assign('orders',$orders);
                $this->assign('counts',$count);
                $this->assign("select_state",$select_state);
                $this->assign("datetimepicker",$datetimepicker);
                $this->assign("datetimepicker3",$datetimepicker3);
                $this->assign("end_datetimepicker2",$end_datetimepicker2);
                $this->assign("end_datetimepicker",$end_datetimepicker);

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
            return $this->fetch('orders');
        }else{
            $this->redirect('Login/index');
        }
    }
    public function orderHx(){
        $appletid = input("appletid");
        $order = input("order");
        $data['custime'] = time();
        $data['flag'] = 2;
        $data['hxinfo'] = 'a:1:{i:0;i:1;}';
        $res = Db::name('wd_xcx_order')->where('order_id', $order)->update($data);

        //累销金额增加
        $info = Db::name('wd_xcx_order')->where('order_id', $order)->field('price, suid, source')->find();
        add_all_pay($appletid, $info['price'], $info['suid']);
        check_vip_grade($appletid, $info['suid']);

        if($res){
            if($info['source'] == 1){
                $openid = Db::name("wd_xcx_user")->where("suid", $info['suid'])->value('openid');
                $jsons = [
                    'fprice' => $info['price']
                ];
                $jsons = serialize($jsons);
                sendSubscribe($appletid, 2, $openid, $jsons);
            }
            $this->success("核销成功！");
        }
    }
    public function excel(){
        $id = input("appletid");
        $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
        if(!$res){
            $this->error("找不到对应的小程序！");
        }
        $this->assign('applet',$res);

        $select_state=99;
        if(in_array(input("select_state"), ['0','1','2','-1','-2'])){
            $select_state=input('select_state');
        }
        $datetimepicker=input("datetimepicker");
        $end_datetimepicker=input("end_datetimepicker");
        $datetimepicker3=input("datetimepicker3");
        $end_datetimepicker2=input("end_datetimepicker2");
        $order=input("order");
        //获取所有员工
        $staff = Db::name('wd_xcx_staff') ->where('uniacid', $id) ->field('id, realname, mobile') ->select();
        $this->assign('staff', $staff);
        $where='';
        if(!empty($datetimepicker)){
            if($where==''){
                $where .= ' creattime >= ' . strtotime($datetimepicker);
            }else{
                $where .= ' and creattime >= ' . strtotime($datetimepicker);
            }

        }
        if(!empty($end_datetimepicker)){
            if($where==''){
                $where .= ' creattime <= ' .strtotime($end_datetimepicker);
            }else{
                $where .= ' and creattime <= ' .strtotime($end_datetimepicker);
            }

        }
        if(in_array($select_state, ['0','1','2','-1','-2'])){
            if($where==''){
                $where .= ' flag = ' . $select_state;
            }else{
                $where .= ' and flag = ' . $select_state;
            }
        }
        if(!empty($datetimepicker3)){
            if($where==''){
                $where .= ' appoint_date >= ' . strtotime($datetimepicker3);
            }else{
                $where .= ' and appoint_date >= ' . strtotime($datetimepicker3);
            }

        }
        if(!empty($end_datetimepicker2)){
            if($where==''){
                $where .= ' appoint_date <= ' . strtotime($end_datetimepicker2);
            }else{
                $where .= ' and appoint_date <= ' . strtotime($end_datetimepicker2);
            }

        }
        if(!empty($order)){
            if($where==''){
                $where .= ' order_id LIKE "%'.$order.'%"';
            }else{
                $where .= ' and order_id LIKE "%'.$order.'%"';
            }

        }


        $order=Db::name("wd_xcx_order")->alias("a")->join("wd_xcx_duo_products_address b",'a.address=b.id','left')->where('a.is_more',1)->where("a.uniacid",$id)->where($where)->order("a.creattime","DESC")->field("a.*,b.name,b.mobile,b.address,b.more_address")->select();
        foreach($order as &$row){
            if(!$row['name']){
                if($row['m_address']){
                    $row['m_address']=unserialize($row['m_address']);
                    $row['name']=$row['m_address']['name'];
                    $row['mobile']=$row['m_address']['mobile'];
                    $row['address']=$row['m_address']['address'];
                }
            }
            if($row['custime']){
                $row['custime']=date("Y-m-d H:i:s",$row['custime']);
            }else{
                $row['custime']="";
            }
            $row['creattime']=date("Y-m-d H:i:s",$row['creattime']);
            $user = Db::name('wd_xcx_superuser')->where("uniacid",$row['uniacid'])->where("id",$row['suid'])->find();
            if(!$row['mobile']){
                $row['mobile'] = $user['phone'];
            }
            if($row['is_more']==0){
                $row['beizhu'] = "姓名：".$row['pro_user_name'].",电话：".$row['pro_user_tel']."地址：".$row['pro_user_add'].",备注：".$row['pro_user_txt'];
            }
            $row['order_duo'] = unserialize($row['order_duo']);
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
        $objPHPExcel->getActiveSheet()->setCellValue('A1', '订单号');
        $objPHPExcel->getActiveSheet()->setCellValue('B1', '产品图片');
        $objPHPExcel->getActiveSheet()->setCellValue('C1', '产品名称');
        $objPHPExcel->getActiveSheet()->setCellValue('D1', '单价/数量');
        $objPHPExcel->getActiveSheet()->setCellValue('E1', '订单总价');
        $objPHPExcel->getActiveSheet()->setCellValue('F1', '姓名');
        $objPHPExcel->getActiveSheet()->setCellValue('G1', '联系方式');
        $objPHPExcel->getActiveSheet()->setCellValue('H1', '核销时间');
        $objPHPExcel->getActiveSheet()->setCellValue('I1', '状态');
        $objPHPExcel->getActiveSheet()->setCellValue('J1', '下单时间');
        $objPHPExcel->getActiveSheet()->setCellValue('K1', '备注');
        $objPHPExcel->getActiveSheet()->setCellValue('L1', '收货地址');
        $objPHPExcel->getActiveSheet()->setCellValue('M1', '小程序uniacid');
        foreach($order as $k => $v){
            $num=$k+2;
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('A'.$num, $v['order_id'],'s');
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$num, remote($v['uniacid'],$v['thumb'],1));
            $objPHPExcel->getActiveSheet()->setCellValue('C'.$num, $v['product']."-".$v['order_duo'][0][0]);
            $objPHPExcel->getActiveSheet()->setCellValue('D'.$num, $v['order_duo'][0][1]."*".$v['order_duo'][0][4]);
            $objPHPExcel->getActiveSheet()->setCellValue('E'.$num, $v['true_price']);
            $objPHPExcel->getActiveSheet()->setCellValue('F'.$num, $v['pro_user_name']);
            $objPHPExcel->getActiveSheet()->setCellValue('G'.$num, $v['pro_user_tel']);
            $objPHPExcel->getActiveSheet()->setCellValue('H'.$num, $v['custime']);
            if($v['flag']==-2){
                $flag = "无效订单";
            }
            if($v['flag']==-1){
                $flag = "已关闭";
            }
            if($v['flag']==0){
                $flag = "未支付";
            }
            if($v['flag']==1){
                $flag = "立即核销";
            }
            if($v['flag']==2){
                $flag = "已完成";
            }
            if($v['flag']==3){
                $flag = "确认订单";
            }
            if($v['flag']==5){
                $flag = "已取消";
            }
            if($v['flag']==6){
                $flag = "退款审核中";
            }
            if($v['flag']==8){
                $flag = "已退款";
            }
            $objPHPExcel->getActiveSheet()->setCellValue('I'.$num, $flag);
            $objPHPExcel->getActiveSheet()->setCellValue('J'.$num, $v['creattime']);
            $objPHPExcel->getActiveSheet()->setCellValue('K'.$num, $v['beizhu']);
            $objPHPExcel->getActiveSheet()->setCellValue('L'.$num, $v['address'].''.$v['pro_user_add']);
            $objPHPExcel->getActiveSheet()->setCellValue('M'.$num, $v['uniacid']);
        }
        $objPHPExcel->getActiveSheet()->setTitle('导出订单列表');
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="预约预定订单列表.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
    //确认订单
    public function orderQr(){
        $order = input("order");
        $data['custime'] = time();
        $data['flag'] = 1;
        $data['emp_id'] = input('emp');
        $res = Db::name('wd_xcx_order')->where('id', $order)->update($data);
        if($res){
            $this->success("确认成功！");
        }
    }
    //修改时间
    public function changedate(){
        $uniacid = input('appletid');
        $newdate = input('newdate');
        $id = input('id');
        $res = Db::name('wd_xcx_order') ->where('uniacid', $uniacid) ->where('id', $id) ->update(['appoint_date' => strtotime($newdate)]);
        if($res){
            $this->success("修改成功");
        }
                    
    }
    //预约预定取消订单
    public function orderQx(){
        $id = input('order');
        $uniacid = input('appletid');
        $opt = input('opt');
        $now = time();
        $out_refund_no = date("Y",$now).date("m",$now).date("d",$now).date("H",$now).date("i",$now).date("s",$now).rand(1000,9999);
        Db::name('wd_xcx_order') ->where('uniacid', $uniacid) ->where('order_id', $id) ->update(['th_orderid' => $out_refund_no]);
        $order = Db::name('wd_xcx_order') ->where('uniacid', $uniacid) ->where('order_id', $id) ->find();
        $product = Db::name('wd_xcx_products') ->where('uniacid', $uniacid) ->where('id', $order['pid']) ->find();

        if($order['pay_price'] > 0){
            $app = Db::name('wd_xcx_applet')->where("id",$uniacid)->find();
            if($order['paytype'] == 1){
                if($order['source'] == 1){
                    $mchid = $app['mchid'];   //商户号
                    $apiKey = $app['signkey'];    //商户的秘钥
                    $appid = $app['appID'];                 //小程序的id
                    $SSLCERT_PATH = ROOT_PATH.'public/Cert/'.$uniacid.'/apiclient_cert.pem';//证书路径
                    $SSLKEY_PATH =  ROOT_PATH.'public/Cert/'.$uniacid.'/apiclient_key.pem';//证书路径
                }elseif($order['source'] == 3){
                    $mchid = $app['wx_h5_mchid'];   //商户号
                    $apiKey = $app['wx_h5_signkey'];    //商户的秘钥
                    $appid = $app['wx_h5_appid'];                 //小程序的id
                    $SSLCERT_PATH = ROOT_PATH.'public/Cert/'.$uniacid.'/h5_apiclient_cert.pem';//证书路径
                    $SSLKEY_PATH =  ROOT_PATH.'public/Cert/'.$uniacid.'/h5_apiclient_key.pem';//证书路径
                }elseif($order['source'] == 5){
                    $mchid = $app['bdance_h5_mchid'];   //商户号
                    $apiKey = $app['bdance_h5_signkey'];    //商户的秘钥
                    $appid = $app['bdance_h5_appid'];                 //小程序的id
                    $SSLCERT_PATH = ROOT_PATH.'public/Cert/'.$uniacid.'/bdance_apiclient_cert.pem';//证书路径
                    $SSLKEY_PATH =  ROOT_PATH.'public/Cert/'.$uniacid.'/bdance_apiclient_key.pem';//证书路径
                }
    			
                $appkey = $app['appSecret'];            //小程序的秘钥
                
                $openid= $order['openid'];    //申请者的openid
                $outTradeNo = $order['order_id'];
                $totalFee= $order['pay_price']*100;  //申请了提现多少钱
                $outRefundNo = $order['order_id']; //商户订单号
                $refundFee= $order['pay_price']*100;  //申请了提现多少钱
                $SSLCERT_PATH = ROOT_PATH.'public/Cert/'.$uniacid.'/apiclient_cert.pem';//证书路径
                $SSLKEY_PATH =  ROOT_PATH.'public/Cert/'.$uniacid.'/apiclient_key.pem';//证书路径
                $opUserId = $mchid;//商户号
                include "WinXinRefund.php";
                $weixinpay = new WinXinRefund($openid,$outTradeNo,$totalFee,$outRefundNo,$refundFee,$SSLCERT_PATH,$SSLKEY_PATH,$opUserId,$appid,$apiKey);
                $return = $weixinpay->refund();

                if(!$return){
                    $this->error('退款失败!请检查系统设置->小程序设置和支付设置');
                }
            }elseif($order['paytype'] == 2){
                Vendor('alipaysdk.aop.AopClient');
                Vendor('alipaysdk.aop.request.AlipayTradeRefundRequest');

                $aop = new \AopClient ();
                $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
                $aop->appId = $app['ali_appID'];
                $aop->rsaPrivateKey = $app['ali_private_key'];
                $aop->alipayrsaPublicKey= $app['ali_public_key'];
                $aop->apiVersion = '1.0';
                $aop->signType = 'RSA2';
                $aop->postCharset='UTF-8';
                $aop->format='json';
                $request = new \AlipayTradeRefundRequest ();
                $request->setBizContent("{'refund_amount':".$order['pay_price'].", 'out_trade_no': ".$order['order_id']."}");
                $result = $aop->execute ( $request); 
                $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
                $resultCode = $result->$responseNode->code;
                if(!empty($resultCode)&&$resultCode == 10000){
                    $return = true;
                } else {
                    $this->error('退款失败!请检查系统设置->支付宝小程序设置');
                    exit;
                }
                // $return = true;
            }elseif($order['paytype'] == 3){
                $pay_info = unserialize($order['pay_info']);
                require_once(ROOT_PATH.'application/api/controller/bdpay/Autoloader.php');
                $params = [
                    'method' => 'nuomi.cashier.applyorderrefund',
                    'orderId' => intval($pay_info['orderId']),
                    'userId' => intval($pay_info['userId']),
                    'refundType' => '1',
                    'refundReason' => '订单退款',
                    'tpOrderId' => $order['order_id'],
                    'appKey' => $app['baidu_pay_appkey']
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
            }elseif($order['paytype'] == 4){
                $pay_info = unserialize($order['pay_info']);
                $chars = "abcdefghijklmnopqrstuvwxyz0123456789";  
                $nonce_str = "";  
                for($i = 0; $i < 32; $i++) {  
                    $nonce_str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
                }
                $op_user_passwd = MD5($app['qq_mchid_password']);
                $appid = $app['qq_appid'];
                $mch_id = $app['qq_mchid'];
                $out_trade_no = $order['order_id'];
                $refund_fee = $order['pay_price']*100;
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
                $res = $this -> postXmlSSLCurl($params, $url, 30, $uniacid);
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

            if(!$return){
                $this->error('退货失败!请检查系统设置->小程序设置和支付设置');
            }else{
                if($opt == "confirmqx"){
                    Db::name('wd_xcx_order') ->where('uniacid', $uniacid) ->where('th_orderid', $out_refund_no) ->update(['flag' => 8]);
                }else{
                    Db::name('wd_xcx_order') ->where('uniacid', $uniacid) ->where('th_orderid', $out_refund_no) ->update(['flag' => 5]);
                }
                //金钱流水
                $xfmoney = array(
                    "uniacid" => $uniacid,
                    "orderid" => $order['order_id'],
                    "suid" => $order['suid'],
                    "type" => "add",
                    "score" => $order['pay_price'],
                    "creattime" => time()
                );
                if($order['paytype'] == 1){
                    $xfmoney["message"] = "退款退回微信"; 
                }else if($order['paytype'] == 2){
                    $xfmoney["message"] = "退款退回支付宝"; 
                }else if($order['paytype'] == 3){
                    $xfmoney["message"] = "退款退回百度"; 
                }else if($order['paytype'] == 4){
                    $xfmoney["message"] = "退款退回QQ"; 
                }
                Db::name('wd_xcx_money') ->insert($xfmoney);
                $tk_je = $order['true_price'] - $order['pay_price']; //退回余额
                if($tk_je > 0){
                    $xfmoney1 = array(
                        "uniacid" => $uniacid,
                        "orderid" => $order['order_id'],
                        "suid" => $order['suid'],
                        "type" => "add",
                        "score" => $tk_je,
                        "message" => "退款退回余额",
                        "creattime" => time()
                    );
                    Db::name('wd_xcx_money') ->insert($xfmoney1);
                    Db::execute("UPDATE {$this->prefix}wd_xcx_superuser set money = money + ".$tk_je." where uniacid = ".$uniacid." and id = ".$order['suid']);
                }
                if($order['coupon']){
                    Db::name('wd_xcx_coupon_user') ->where('uniacid', $uniacid) ->where('suid', $order['suid']) ->where('id', $order['coupon']) ->update(array('flag' => 0,"utime"=>0));
                }
                if($order['dkscore']){
                    Db::execute("UPDATE {$this->prefix}wd_xcx_superuser set score = score + ".$order['dkscore']." where uniacid = ".$uniacid." and id = ".$order['suid']);
                    $score_data = array(
                        "uniacid" => $uniacid,
                        "orderid" => $order['order_id'],
                        "suid" => $order['suid'],
                        "type" => "add",
                        "score" => $order['dkscore'],
                        "message" => "退款退回抵扣积分",
                        "creattime" => time()
                    );
                    Db::name('wd_xcx_score') ->insert($score_data);
                }
                // //处理库存与真实销量
                //处理库存与真实销量
                if($product['tableis'] != 1){
                    //处理库存销量
                    $more_type_num = unserialize($product['more_type_num']);
                    $order_duo = unserialize($order['order_duo']);
                    $more_type = unserialize($product['more_type']);
                    $rows = count($more_type)/4;
                    for($i = 0; $i<$rows; $i++){
                        if($i==0){
                            $more_type[2] = $more_type[2] + $order_duo[$i][4];
                        }else{
                            $more_type[2+$i*4] = $more_type[2+$i*4] + $order_duo[$i][4];
                        }
                    }
                    $now_salenum = 0;
                    if(!empty($order_duo)){
                        foreach ($order_duo as $kv => $vv) {
                            $now_salenum += $vv[4];
                            $more_type_num[$kv]['salenum'] = $more_type_num[$kv]['salenum'] - $vv[4];
                            $more_type_num[$kv]['shennum'] = $more_type_num[$kv]['shennum'] + $vv[4];
                        }
                    }
                    $sale_tnum = $product['sale_tnum'] - $now_salenum;
                    Db::name('wd_xcx_products') ->where('uniacid', $uniacid) ->where('id', $product['id']) ->update(['sale_tnum' => $sale_tnum, 'more_type_num' => serialize($more_type_num), 'more_type'=> serialize($more_type)]);
                }else{
                    //更新选择座位状态
                    Db::name('wd_xcx_tableselect')->where('id', $order['tsid']) ->update(['flag' => 2]);
                    $table_select = Db::name('wd_xcx_tableselect') ->where('id', $order['tsid']) ->find();
                    $temp_select = explode(',', $table_select['select_str']);
                    $count = count($temp_select);

                    $sale_tnum = $product['sale_tnum'] - $count;
                    Db::name('wd_xcx_products') ->where('id', $product['id']) ->update(['sale_tnum'=>$sale_tnum]);
                }

                if($order['source'] != 3){
                    $jsons['orderid'] = $order['order_id'];
                    $jsons['ftitle'] = $order['product'];
                    $order_yue = floatval($order['true_price']) - floatval($order['pay_price']);
                    $jsons['fprice'] = "实付：".$order['true_price'];

                    
                    if($order['source'] == 1){
                        if($order_yue > 0){
                            $jsons['refund_type'] = "退回微信：￥".$order['true_price']."元，退回余额：￥".$order_yue;
                        }else{
                            $jsons['refund_type'] = "退回微信：￥".$order['true_price']."元";
                        }

                        $openid = Db::name('wd_xcx_user')->where('suid', $order['suid'])->value('openid');

                        $jsons = [
                            'order_id' => $order['order_id'],
                            'fprice' => $order['price'],
                            'msg' => "退款成功",
                        ];
                        $jsons = serialize($jsons);
                        sendSubscribe($uniacid, 3, $openid, $jsons);
                    }else if($order['source'] == 6){
                        if($order_yue > 0){
                            $jsons['refund_type'] = "退回QQ：￥".$order['true_price']."元，退回余额：￥".$order_yue;
                        }else{
                            $jsons['refund_type'] = "退回QQ：￥".$order['true_price']."元";
                        }
                        $jsons = serialize($jsons);

                        $openid = Db::name('wd_xcx_qq_user')->where('suid', $order['suid'])->value('openid');
                        tpl_send($uniacid, 8, $openid, $order['source'], $order['qx_formid'], $jsons);
                    }else if($order['source'] == 5){
                        if($order_yue > 0){
                            $jsons['refund_type'] = "退回微信：￥".$order['true_price']."元，退回余额：￥".$order_yue;
                        }else{
                            $jsons['refund_type'] = "退回微信：￥".$order['true_price']."元";
                        }
                        $jsons = serialize($jsons);

                        $openid = Db::name('wd_xcx_toutiao_user')->where('suid', $order['suid'])->value('openid');
                        tpl_send($uniacid, 8, $openid, $order['source'], $order['qx_formid'], $jsons);
                    }
                }
            }
        }else{
            if($opt == "confirmqx"){
                Db::name('wd_xcx_order') ->where('uniacid', $uniacid) ->where('th_orderid', $out_refund_no) ->update(['flag' => 8]);
            }else{
                Db::name('wd_xcx_order') ->where('uniacid', $uniacid) ->where('th_orderid', $out_refund_no) ->update(['flag' => 5]);
            }
            //金钱流水
            if($order['true_price'] > 0){
                $xfmoney = array(
                    "uniacid" => $uniacid,
                    "orderid" => $order['order_id'],
                    "suid" => $order['suid'],
                    "type" => "add",
                    "score" => $order['true_price'],
                    "message" => "退款退回余额",
                    "creattime" => time()
                );
                Db::name('wd_xcx_money') ->insert($xfmoney);
            }
            $order = Db::name('wd_xcx_order') ->where('uniacid', $uniacid) ->where('th_orderid', $out_refund_no) ->find();
            
            Db::execute("UPDATE {$this->prefix}wd_xcx_superuser set money = money + ".$order['true_price']." where uniacid = ".$uniacid." and id = ".$order['suid']);
            if($order['tsid'] > 0){
                Db::name('wd_xcx_tableselect') ->where('uniacid', $uniacid) ->where('id', $order['tsid']) ->update(['flag'=>2]);
            }else{
                $pro = Db::name('wd_xcx_products') ->where('uniacid', $uniacid) ->where('id', $order['pid']) ->find();
                $more_type_num = unserialize($pro['more_type_num']);
                $order_duo = unserialize($order['order_duo']);
                foreach ($order_duo as $key => &$value) {
                    $more_type_num[$key]['shennum'] += $value[4];
                }
                $more_type_num = serialize($more_type_num);
                Db::name('wd_xcx_products') ->where('uniacid', $uniacid) ->where('id', $order['pid']) ->update(['more_type_num' => $more_type_num]);
            }
            if($order['coupon']){
                Db::name('wd_xcx_coupon_user') ->where('uniacid', $uniacid) ->where('suid', $order['suid']) ->where('id', $order['coupon']) ->update(['flag'=>0, 'utime'=> 0]); 
            }
            
            if($order['dkscore']){
                Db::execute("UPDATE {$this->prefix}wd_xcx_superuser set score = score + ".$order['dkscore']." where uniacid = ".$uniacid." and id = ".$order['suid']);
                $score_data = array(
                    "uniacid" => $uniacid,
                    "orderid" => $order['order_id'],
                    "suid" => $order['suid'],
                    "type" => "add",
                    "score" => $order['dkscore'],
                    "message" => "退款退回抵扣积分",
                    "creattime" => time()
                );
                // pdo_insert("sudu8_page_score", $score_data);
                Db::name('wd_xcx_score') ->insert($score_data);
            }
            //处理库存与真实销量
            if($product['tableis'] != 1){
                //处理库存销量
                $more_type_num = unserialize($product['more_type_num']);
                $order_duo = unserialize($order['order_duo']);
                $more_type = unserialize($product['more_type']);
                $rows = count($more_type)/4;
                for($i = 0; $i<$rows; $i++){
                    if($i==0){
                        $more_type[2] = $more_type[2] + $order_duo[$i][4];
                    }else{
                        $more_type[2+$i*4] = $more_type[2+$i*4] + $order_duo[$i][4];
                    }
                }
                $now_salenum = 0;
                if(!empty($order_duo)){
                    foreach ($order_duo as $kv => $vv) {
                        $now_salenum += $vv[4];
                        $more_type_num[$kv]['salenum'] = $more_type_num[$kv]['salenum'] - $vv[4];
                        $more_type_num[$kv]['shennum'] = $more_type_num[$kv]['shennum'] + $vv[4];
                    }
                }
                $sale_tnum = $product['sale_tnum'] - $now_salenum;
                Db::name('wd_xcx_products') ->where('uniacid', $uniacid) ->where('id', $product['id']) ->update(['sale_tnum' => $sale_tnum, 'more_type_num' => serialize($more_type_num), 'more_type'=> serialize($more_type)]);
            }else{
                //更新选择座位状态
                Db::name('wd_xcx_tableselect')->where('id', $order['tsid']) ->update(['flag' => 2]);
                //减去销量
                $table_select = Db::name('wd_xcx_tableselect') ->where('id', $order['tsid']) ->find();
                $temp_select = explode(',', $table_select['select_str']);
                $count = count($temp_select);

                $sale_tnum = $product['sale_tnum'] - $count;
                Db::name('wd_xcx_products') ->where('id', $product['id']) ->update(['sale_tnum'=>$sale_tnum]);

            }

            if($order['source'] != 3){
                $jsons['orderid'] = $order['order_id'];
                $jsons['ftitle'] = $order['product'];
                $order_yue = floatval($order['true_price']) - floatval($order['pay_price']);
                $jsons['fprice'] = "实付：".$order['true_price'];
                $jsons['refund_type'] = "退回余额：￥".$order['true_price']."元";
                $jsons = serialize($jsons);
                if($order['source'] == 1){
                    $openid = Db::name('wd_xcx_user')->where('suid', $order['suid'])->value('openid');
                    $jsons = [
                        'order_id' => $order['order_id'],
                        'fprice' => $order['price'],
                        'msg' => "退款成功",
                    ];
                    $jsons = serialize($jsons);
                    sendSubscribe($uniacid, 3, $openid, $jsons);

                }else if($order['source'] == 6 && $order['qx_formid']){
                    $openid = Db::name('wd_xcx_qq_user')->where('suid', $order['suid'])->value('openid');
                    tpl_send($uniacid, 8, $openid, $order['source'], $order['qx_formid'], $jsons);
                }else if($order['source'] == 5 && $order['qx_formid']){
                    $openid = Db::name('wd_xcx_toutiao_user')->where('suid', $order['suid'])->value('openid');
                    tpl_send($uniacid, 8, $openid, $order['source'], $order['qx_formid'], $jsons);
                }
            }
           
        }
        $this ->success('取消成功!');
    }
    public function orderNqx(){
        $id = input('order');
        $uniacid = input('appletid');
        $pid = Db::name('wd_xcx_order') ->where('uniacid', $uniacid) ->where('order_id', $id)->find();
        $pro_flag_ding = Db::name('wd_xcx_products') ->where('uniacid', $uniacid) ->where('id', $pid['pid']) ->find();
        $flag = ($pro_flag_ding['pro_flag_ding'] == '0') ? 1 : 3;
        $res =  Db::name('wd_xcx_order') ->where('uniacid', $uniacid) ->where('order_id', $id) ->update(['flag' => $flag]);
        if($res){
            if($pid['source'] == 1){
                $openid = Db::name("wd_xcx_user")->where("suid", $pid['suid'])->value('openid');
                $jsons = [
                    'order_id' => $pid['order_id'],
                    'fprice' => $pid['price'],
                    'msg' => "退款被拒",
                ];
                $jsons = serialize($jsons);
                sendSubscribe($uniacid, 3, $openid, $jsons);
            }
            $this ->success('客户退款申请已拒绝!');
        }
    }
    //确认修改
    public function acceptmodify(){
        $id = input('order');
        $uniacid = input('appletid');
        $order = Db::name('wd_xcx_order') ->where('uniacid', $uniacid) ->where('order_id', $id) ->find();
        $modify_info = unserialize($order['modify_info']);
        $data = array(
            "pro_user_name" => $modify_info['pro_name'],
            "pro_user_tel" => $modify_info['pro_tel'],
            "pro_user_add" => $modify_info['pro_address'],
            "appoint_date" => $modify_info['appoint_date']
        );
        $modify_info['pro_name'] = $order['pro_user_name'];
        $modify_info['pro_tel'] = $order['pro_user_tel'];
        $modify_info['pro_address'] = $order['pro_user_add'];
        $modify_info['appoint_date'] = $order['appoint_date'];
        $modify_info['flag'] = 2;
        
        $data['modify_info'] = serialize($modify_info);
        $res = Db::name('wd_xcx_order') ->where('uniacid', $uniacid) ->where('order_id', $id) ->update($data);
        if($res){
            $this ->success('客户修改申请已通过!');
        }
    }
    //拒绝修改
    public function refusemodify(){
        $id = input('order');
        $uniacid = input('appletid');
        $modify_info = Db::name('wd_xcx_order') ->where('uniacid', $uniacid) ->where('order_id', $id) ->field('modify_info') ->find();
        $modify_info = $modify_info['modify_info'];
        $modify_info = unserialize($modify_info);
        $modify_info['flag'] = 3;
        $modify_info = serialize($modify_info);
        $res = Db::name('wd_xcx_order') ->where('uniacid', $uniacid) ->where('order_id', $id) ->update(['modify_info' =>$modify_info]);
        if($res){
            $this ->success('客户修改申请已拒绝!');
        }
    }

    //商品基础设置
    public function set(){
        $uniacid = input('appletid');
        $app = new Applet;
        $appInfo = $app->getAppInfo();
        $this->assign('applet', $appInfo);
        $base = model('ImsSudu8PageReserveSet') ->getBaseSet();
        $this ->ee();
        $this->assign('baseInfo', $base);
        $this->assign('catestyle', $base['catestyle']);
        return $this->fetch('set');
    }

    //保存基础设置
    public function setSave(){
        $uniacid = input('appletid');
        $send_mail = input('send_mail');
        $order_close_time = input('order_close_time');
        $catestyle = input('catestyle');
        $queren_time = input('queren_time');
        $baseObj = model('ImsSudu8PageReserveSet');
        $base = $baseObj ->getBaseSet();
        if(!$send_mail){
            $data['send_mail'] = 1;
        }else{
            $data['send_mail'] = $send_mail;
        }
        if(!$order_close_time && $order_close_time !== 0){
            $data['order_close_time'] = 30;
        }else{
            $data['order_close_time'] = $order_close_time;
        }
        if(!$queren_time && $queren_time !== 0){
            $data['queren_time'] = 7;
        }else{
            $data['queren_time'] = $queren_time;
        }

        if(!$catestyle){
            $data['catestyle'] = 2;
        }else{
            $data['catestyle'] = $catestyle;
        }

        if($base){
            $res = $baseObj ->save($data, ['uniacid'=>$uniacid]);
        }else{
            $data['uniacid'] = $uniacid;
            $res = $baseObj ->save($data);
        }

        if($res){
            $this->success('基础设置成功！');
        }else{
            $this->success('基础设置失败！');
        }
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
