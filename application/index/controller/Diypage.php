<?php
namespace app\index\controller;

use think\Controller;

use think\Db;

use think\Request;

use think\Session;

use think\View;

class Diypage extends Base
{
    public function index(){

        if(check_login()){

            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                $a=Db::name('wd_xcx_base')->where("uniacid",$appletid)->find();
                $bg_music=$a['diy_bg_music'];
                if(!$res){

                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);

                $moban_class = Db::name('wd_xcx_diypagetpl_sys_class')->select();
                $this->assign("moban_class",$moban_class);

                $op=input("op");

                $tplid=input("tplid");

                $this ->ccDD();

                if($op){

                    if($op=="setindex"){

                        $val = input('v');

                        $key_id = input('key_id');

                        if(empty($key_id)){

                            return false;
                        }

                        if($val == 1){

                            Db::name('wd_xcx_diypage')->where("uniacid",$appletid)->update(array("index"=>0));
                            $result = Db::name('wd_xcx_diypage')->where("uniacid",$appletid)->where("id",$key_id)->update(array("index"=>1));
                        }else{
                            $result = Db::name('wd_xcx_diypage')->where("uniacid",$appletid)->where("id",$key_id)->update(array("index"=>0));
                        }

                        if($result){

                            return  json_encode(['status' => 1,'result' => ['returndata' => 1]]);

                        }else{

                            return json_encode(['status' => 0]);

                        }
                    }
                    if($op == "query"){

                        $type = input('type');

                        $kw = input('kw');

                        switch ($type){

                            case 'news':


                                $list = Db::name('wd_xcx_products')->where("uniacid",$appletid)->where("type","showArt")->where("title","like","%".$kw."%")->field("id,title")->select();



                                $html = '';


                                if($list){
                                    foreach ($list as $k => $v){

                                        $html .= '<div class="line">

                                                    <div class="icon icon-link1"></div>

                                                    <nav data-href="/sudu8_page/showArt/showArt?id='.$v['id'].'" data-linktype="page" class="btn btn-default btn-sm" title="选择">选择</nav>

                                                    <div class="text"><span class="label lable-default">普通</span>'.$v['title'].'</div>

                                                </div>';

                                    }
                                }else{
                                    $html = '<div class="line">

                                            无相关搜索结果

                                        </div>';
                                }

                                break;
                            case 'pic':

                                $list = Db::name('wd_xcx_products')->where("uniacid",$appletid)->where("type","showPic")->where("title","like","%".$kw."%")->field("id,title")->select();



                                $html = '';


                                if($list){

                                    foreach ($list as $k => $v){

                                        $html .= '<div class="line">

                                                    <div class="icon icon-link1"></div>

                                                    <nav data-href="/sudu8_page/showPic/showPic?id='.$v['id'].'" data-linktype="page" class="btn btn-default btn-sm" title="选择">选择</nav>

                                                    <div class="text"><span class="label lable-default">普通</span>'.$v['title'].'</div>

                                                </div>';

                                    }
                                }else{
                                    $html = '<div class="line">

                                            无相关搜索结果

                                        </div>';
                                }

                                break;

                            case 'goods':


                                $list = Db::name('wd_xcx_products')->where("uniacid",$appletid)->where("type","neq","showArt")->where("type","neq","showPic")->where("type","neq","wxapp")->where("title","like","%".$kw."%")->field("id,title,price,pro_kc,pro_flag")->select();

                                $html = '';


                                if($list){
                                    foreach ($list as $k => $v){

                                        if($v['pro_flag'] == 2){

                                            $url = "/sudu8_page/showProMore/showProMore?id=".$v['id'];

                                            $g = "多规格";

                                        }else{

                                            $url = "/sudu8_page/showPro/showPro?id=".$v['id'];

                                            $g = "单规格";

                                        }

                                        $html .= '<div class="line">

                                                    <div class="icon icon-link1"></div>

                                                    <nav data-href="'.$url.'" data-linktype="page" class="btn btn-default btn-sm" title="选择">选择</nav>

                                                    <div class="text"><span class="label lable-default">普通</span>'.$g.' - 商品名称：'.$v['title'].' &nbsp; 价格：'.$v['price'].' &nbsp; 库存：'.$v['pro_kc'].'</div>

                                                </div>';

                                    }
                                }else{
                                    $html = '<div class="line">

                                            无相关搜索结果

                                        </div>';
                                }

                                break;
                        }

                        echo $html;
                        exit;
                    }
                    if ($op == 'delpage'){
                        $tpl_id = input("tplid");
                        $tpl_pages = Db::name('wd_xcx_diypagetpl')->where("uniacid",$appletid)->where("id",$tpl_id)->find()['pageid'];

                        $tpl_pages_arr = explode(",",$tpl_pages);
                        $tpl_pages_count = Db::name('wd_xcx_diypage')->where("uniacid",$appletid)->where("id","in",$tpl_pages_arr)->count();
                        if($tpl_pages_count == 1){
                            $this->error('删除失败，模板必须保留一个页面');

                            exit;
                        }



                        $id = input('id') ? intval(input('id')) : 0;

                        if($id == 0){

                            $this->error('参数错误');

                            exit;

                        }

                        $is_index = Db::name('wd_xcx_diypage')->where("uniacid",$appletid)->where("id",$id)->where("index",1)->find();
                        if($is_index){
                            $this->error("当前页面为首页不可删除");
                            exit;
                        }
                        $result = Db::name('wd_xcx_diypage')->where("uniacid",$appletid)->where("id",$id)->delete();

                        if($result){
                            $this->success("删除成功", Url('Diypage/index').'?appletid='.$appletid.'&tplid='.$tplid);

                        }else{
                            $this->error('删除失败');

                        }

                    }
                    if($op == "setsave"){
                        // $pid = input('key_id');
                        $is = Db::name('wd_xcx_diypageset')->where("uniacid",$appletid)->find();
                        // $is = Db::name('wd_xcx_diypageset')->where("uniacid",$appletid)->where("pid",$pid)->find();
                        $go_home = input('go_home');
                        $kp = input('kp');
                        $kp_is = input('kp_is');
                        $kp_m = input('kp_m');
                        $kp_url = input('kp_url');
                        $kp_urltype = input('kp_urltype');
                        $tc_is = input('tc_is');
                        $tc = input('tc');
                        $tc_url = input('tc_url');
                        $tc_urltype = input('tc_urltype');
                        $foot_is = input('foot_is');
                        $bg_music = input('bg_music');
                        $data = array(
                            // "pid"=>$pid,
                            "go_home"=>$go_home,
                            "kp"=>remote($appletid,$kp,2),
                            "kp_is"=>intval($kp_is),
                            "kp_m"=>intval($kp_m),
                            "kp_url"=>$kp_url,
                            "kp_urltype"=>$kp_urltype,
                            "tc_is"=>$tc_is,
                            "tc"=>remote($appletid,$tc,2),
                            "tc_url"=>$tc_url,
                            "tc_urltype"=>$tc_urltype,
                            "foot_is"=>$foot_is,
                        );
                        Db::name("wd_xcx_base")->where("uniacid",$appletid)->update(array("diy_bg_music"=>$bg_music));
                        if($is){
                            $res = Db::name('wd_xcx_diypageset')->where("uniacid",$appletid)->update($data);
                        }else{
                            $data['uniacid'] = $appletid;
                            $res = Db::name('wd_xcx_diypageset')->insert($data);
                        }
                        if($res==1){
                            return 1;
                        }else{
                            return 2;
                        }
                    }
                    if ($op == 'add'){

                        $data = $_POST;
                        $data['data'] = json_decode($data['data'], true);
                        // var_dump($data);exit;
                        if($data['id'] == 0){
                            unset($data['data']['page']['novisit']);
                            if(count($data['data']['items']) == 0){
                                unset($data['data']['items']);
                            }
                        }

                        if(isset($data['data']['page']['url']) && $data['data']['page']['url'] != ""){
                            $data['data']['page']['url'] = remote($appletid,$data['data']['page']['url'],2);
                        }

                        if(isset($data['data']['page']['name']) && $data['data']['page']['name'] != ''){

                            $sd = [];

                            $sd['tpl_name'] = $data['data']['page']['name'];
                            if(isset($data['data']['page']['url']) && $data['data']['page']['url'] != ""){
                                $data['data']['page']['url'] = remote($appletid,$data['data']['page']['url'],2);
                            }
                            $sd['page'] = serialize($data['data']['page']);

                            if(strpos($sd['page'], "\\") !== false){
                                echo json_encode(['status' => -1,'message' => '保存失败，请去除特殊字符“\”再保存'],JSON_UNESCAPED_UNICODE);
                                exit;
                            }
                            

                            if(isset($data['data']['items'])){
                                foreach($data['data']['items'] as $ki => $vi){
                                    if($vi['id'] == "video" ){
                                        if(!empty($vi['params']['videourl'])){
                                            if(strpos($vi['params']['videourl'],"</iframe>") !== false || strpos($vi['params']['videourl'],"</embed>") !== false){
                                                $data['data']['items'][$ki]['params']['videourl'] = "";
                                            }
                                        }
                                    }
                                    if($vi['id'] == "yuyin" ){
                                        if(!empty($vi['params']['linkurl'])){
                                            if(strpos($vi['params']['linkurl'],"</iframe>") !== false || strpos($vi['params']['linkurl'],"</embed>") !== false){
                                                $data['data']['items'][$ki]['params']['linkurl'] = "";
                                            }
                                        }
                                        if(!isset($vi['params']['backgroundimg'])){
                                            $data['data']['items'][$ki]['params']['backgroundimg'] = '';
                                        }
                                    }
                                }
                            }
                 
                            if(isset($data['data']['items']) && $data['data']['items'] != ""){
                                foreach ($data['data']['items'] as $k => &$v) {
                                    if($v['id'] == 'title2' || $v['id'] == 'title' || $v['id'] == 'line' || $v['id'] == 'blank' || $v['id'] == 'anniu' || $v['id'] == 'notice' || $v['id'] == 'service' || $v['id'] == 'listmenu' || $v['id'] == 'joblist' || $v['id'] == 'personlist' || $v['id'] == 'msmk' || $v['id'] == 'multiple' || $v['id'] == 'mlist' || $v['id'] == 'goods' || $v['id'] == 'tabbar' || $v['id'] == 'cases' || $v['id'] == 'listdesc' || $v['id'] == 'pt' || $v['id'] == 'dt' || $v['id'] == 'ssk' || $v['id'] == 'xnlf' || $v['id'] == 'yhq' || $v['id'] == 'dnfw' || $v['id'] == 'yuyin' || $v['id'] == 'feedback' || $v['id'] == 'yuyin'){
                                        if($v['params']['backgroundimg'] != ""){
                                            $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],2);
                                        }
                                    }
                                    if($v['id'] == 'bigimg' || $v['id'] == 'classfit' || $v['id'] == 'banner' || $v['id'] == 'menu' || $v['id'] == 'picture' || $v['id'] == 'picturew'){

                                        if($v['params']['backgroundimg'] != ""){
                                            $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],2);
                                        }

                                        if($v['data']){
                                            foreach ($v['data'] as $ki => $vi) {
                                                if($vi['imgurl'] != ""){
                                                    $v['data'][$ki]['imgurl'] = remote($appletid,$vi['imgurl'],2);
                                                }
                                            }
                                        }
                                    }
                                    if($v['id'] == 'contact'){

                                        if($v['params']['backgroundimg'] != ""){
                                            $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],2);

                                        }
                                        if($v['params']['src'] != ""){
                                            $v['params']['src'] = remote($appletid,$v['params']['src'],2);
                                        }
                                        if($v['params']['ewm'] != ""){
                                            $v['params']['ewm'] = remote($appletid,$v['params']['ewm'],2);
                                        }
                                    }
                                    if($v['id'] == 'video'){

                                        if($v['params']['backgroundimg'] != ""){
                                            $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],2);
                                        }

                                        if($v['params']['poster'] != ""){
                                            $v['params']['poster'] = remote($appletid,$v['params']['poster'],2);
                                        }
                                    }
                                    if($v['id'] == 'logo' || $v['id'] == 'dp'){

                                        if($v['params']['backgroundimg'] != ""){
                                            $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],2);

                                        }
                                        if($v['params']['src'] != ""){
                                            $v['params']['src'] = remote($appletid,$v['params']['src'],2);
                                        }
                                    }
                                    if($v['id'] == 'footmenu'){
                                        if($v['data']){
                                            foreach ($v['data'] as $ki => $vi) {
                                                if($vi['imgurl'] != ""){
                                                    $v['data'][$ki]['imgurl'] = remote($appletid,$vi['imgurl'],2);
                                                }
                                            }
                                        }
                                    }
                                }
                                $sd['items'] = serialize($data['data']['items']);
                                if(strpos($sd['items'], "\\") !== false){
                                    echo json_encode(['status' => -1,'message' => '保存失败，请去除特殊字符“\”再保存'],JSON_UNESCAPED_UNICODE);
                                    exit;
                                }
                            }else{
                                $sd['items'] = "";
                            }


                            $sd['uniacid'] = $appletid;



                            if(intval($data['id']) == 0){

                                // $tplid = input('tplid');


                                /*新创建*/

                                $idata = Db::name('wd_xcx_diypage')->where("uniacid",$appletid)->where("tpl_name",$sd['tpl_name'])->find();

                                if($idata){

                                    echo json_encode(['status' => 0,'message' => '创建页面名称重复','id' => 0],JSON_UNESCAPED_UNICODE);exit;

                                }
                                $is = Db::name('wd_xcx_diypage')->where('uniacid',$appletid)->find();
                                if(!$is){
                                    $sd['index'] = 1;
                                }
                                $result = Db::name('wd_xcx_diypage')->insert($sd);

                                $key = Db::name('wd_xcx_diypage')->getLastInsID();

                                if($tplid>0){
                                    $pageid =  Db::name('wd_xcx_diypagetpl')->where("uniacid",$appletid)->where("id",$tplid)->field("pageid")->find()['pageid'];
                                    Db::name('wd_xcx_diypagetpl')->where("uniacid",$appletid)->where("id",$tplid)->update(array("pageid"=>$pageid.",".$key));
                                }


                            }else{

                                $result = Db::name('wd_xcx_diypage')->where("uniacid",$appletid)->where("id",$data['id'])->update($sd);

                                $key = $data['id'];

                            }
                            if($result){

                                echo json_encode(['status' => 0,'message' => '保存成功','id' => $key],JSON_UNESCAPED_UNICODE);
                                exit;
                            }else{

                                echo json_encode(['status' => -1,'message' => '保存成功，本次保存未做修改'],JSON_UNESCAPED_UNICODE);
                                exit;
                            }
                        }
                    }
                    //另存为模板
                    if ($op == 'settemplate') {
                        $pageid = input('ids/a');
                        $pageids = "";
                        foreach ($pageid as $key => $value) {
                            $info = Db::name("wd_xcx_diypage")->where("id",$value)->find();
                            $info['page'] = unserialize($info['page']);
                            if(isset($info['page']['url']) && $info['page']['url'] != ""){
                                $info['page']['url'] = remote($appletid,$info['page']['url'],2);
                            }

                            $items = unserialize($info['items']);
                            if($items){
                                foreach ($items as $k => $v) {
                                    if($v['id'] == 'title2' || $v['id'] == 'title' || $v['id'] == 'line' || $v['id'] == 'blank' || $v['id'] == 'anniu' || $v['id'] == 'notice' || $v['id'] == 'service' || $v['id'] == 'listmenu' || $v['id'] == 'joblist' || $v['id'] == 'personlist' || $v['id'] == 'msmk' || $v['id'] == 'multiple' || $v['id'] == 'mlist' || $v['id'] == 'goods' || $v['id'] == 'tabbar' || $v['id'] == 'cases' || $v['id'] == 'listdesc' || $v['id'] == 'pt' || $v['id'] == 'dt' || $v['id'] == 'ssk' || $v['id'] == 'xnlf' || $v['id'] == 'yhq' || $v['id'] == 'dnfw' || $v['id'] == 'yuyin' || $v['id'] == 'feedback' || $v['id'] == 'yuyin'){
                                        if($v['params']['backgroundimg'] != ""){
                                            $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],2);
                                        }
                                    }
                                    if($v['id'] == 'bigimg' || $v['id'] == 'classfit' || $v['id'] == 'banner' || $v['id'] == 'menu' || $v['id'] == 'picture' || $v['id'] == 'picturew'){
                                        if($v['params']['backgroundimg'] != ""){
                                            $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],2);
                                        }
                                        if($v['data']){
                                            foreach ($v['data'] as $ki => $vi) {
                                                if($vi['imgurl'] != ""){
                                                    $v['data'][$ki]['imgurl'] = remote($appletid,$vi['imgurl'],2);
                                                }
                                            }
                                        }
                                    }
                                    if($v['id'] == 'contact'){
                                        if($v['params']['backgroundimg'] != ""){
                                            $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],2);
                                        }
                                        if($v['params']['src'] != ""){
                                            $v['params']['src'] = remote($appletid,$v['params']['src'],2);
                                        }
                                        if($v['params']['ewm'] != ""){
                                            $v['params']['ewm'] = remote($appletid,$v['params']['ewm'],2);
                                        }
                                    }
                                    if($v['id'] == 'video'){
                                        if($v['params']['backgroundimg'] != ""){
                                            $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],2);
                                        }
                                        if($v['params']['poster'] != ""){
                                            $v['params']['poster'] = remote($appletid,$v['params']['poster'],2);
                                        }
                                    }
                                    if($v['id'] == 'logo' || $v['id'] == 'dp'){
                                        if($v['params']['backgroundimg'] != ""){
                                            $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],2);
                                        }
                                        if($v['params']['src'] != ""){
                                            $v['params']['src'] = remote($appletid,$v['params']['src'],2);
                                        }
                                    }
                                    if($v['id'] == 'footmenu'){
                                        if($v['data']){
                                            foreach ($v['data'] as $ki => $vi) {
                                                if($vi['imgurl'] != ""){
                                                    $v['data'][$ki]['imgurl'] = remote($appletid,$vi['imgurl'],2);
                                                }
                                            }
                                        }
                                    }

                                    //去除栏目信息
                                    //notice(公告) msmk(秒杀模块) goods(产品组) feedback(表单) pt(拼团) listdesc(文章) cases(图文)

                                    if ($v['id'] == 'notice' || $v['id'] == 'msmk' || $v['id'] == 'goods' || $v['id'] == 'feedback' || $v['id'] == 'pt' || $v['id'] == 'listdesc' || $v['id'] == 'cases') {
                                        $items[$k]['params']['sourceid'] = '';
                                    }
                                }
                            }
                            $insert_id = Db::name('wd_xcx_diypage_sys')->insertGetId(array(
                                'index' => $info['index'],
                                'page' => serialize($info['page']),
                                'items' => serialize($items),
                                'tpl_name' => $info['tpl_name'],
                            ));
                            $pageids = $pageids .','. $insert_id;
                        }
                        $pageids = substr($pageids,1);
                        $data = [
                            'pageid' => $pageids,
                            'template_name' => input('name'),
                            'thumb' => input('preview'),
                            'classid' => input('classid'),
                            'create_time' => time()
                        ];


                        $key_id = Db::name("wd_xcx_diypagetpl_sys")->insertGetId($data);

                        echo json_encode(['status' => 1,'id' => $key_id,'message' => '保存成功'],JSON_UNESCAPED_UNICODE);
                        exit;

                    }
                    if ($op == 'settemp') {
                        $template_id = input('templateid');

                        if($template_id > 0){

                            $data = [

                                // 'pageid' => implode(',',input('ids/a')),

                                'template_name' => input('name'),

                                'thumb' => remote($appletid,input('preview'),2),

                                'uniacid' => $appletid,

                                // 'create_time' => time()

                            ];

                            $res = Db::name("wd_xcx_diypagetpl")->where("id",$template_id)->update($data);

                            if($res){
                                echo json_encode(['status' => 1],JSON_UNESCAPED_UNICODE);
                                exit;
                            }else{
                                echo json_encode(['status' => 0],JSON_UNESCAPED_UNICODE);
                                exit;
                            }
                        }
                    }
                }else{
                    //页面设置
                    $setsave = Db::name("wd_xcx_diypageset")->where("uniacid",$appletid)->find();
                    if(!$setsave){
                        $foot_is = 1;
                        $setsave = [];
                    }else{
                        if($setsave['kp']){
                            $setsave['kp'] = remote($appletid,$setsave['kp'],1);
                        }
                        if($setsave['tc']){
                            $setsave['tc'] = remote($appletid,$setsave['tc'],1);
                        }
                        $foot_is = 0;
                    }

                    //查出当前模板关联页面id
                    $type = input('type');
                    if($type){
                        $temp = Db::name("wd_xcx_diypagetpl_sys")->where("id",$tplid)->find();


                        if($temp['thumb']){
                            $temp['thumb'] = remote($appletid,$temp['thumb'],1);
                        }
                        if($temp['pageid'] == ""){
                            $pageid = Db::name("wd_xcx_diypage_sys")->insertGetId(array(
                                'uniacid' => $appletid,
                                'index' => 1,
                                'page' => 'a:7:{s:10:"background";s:7:"#f1f1f1";s:13:"topbackground";s:7:"#ffffff";s:8:"topcolor";s:1:"1";s:9:"styledata";s:1:"0";s:5:"title";s:21:"小程序页面标题";s:4:"name";s:18:"后台页面名称";s:10:"visitlevel";a:2:{s:6:"member";s:0:"";s:10:"commission";s:0:"";}}',
                                'items' => '',
                                'tpl_name' => '后台页面名称',
                            ));
                            Db::name("wd_xcx_diypagetpl_sys")->where("id",$tplid)->update(array("pageid"=>$pageid));
                            $temp = Db::name("wd_xcx_diypagetpl_sys")->where("id",$tplid)->find();
                        }

                        $pageidArray = explode(',',$temp['pageid']);


                        //查出当前模板所有的页面
                        $list = Db::name("wd_xcx_diypage_sys")->where("id","in",$pageidArray)->field("id,tpl_name,index")->select();

                        //页面操作
                        $diypage = Db::name("wd_xcx_diypage_sys")->where("id","in",$pageidArray)->where("index",1)->find();
                        if($diypage == null){
                            $diypageone = Db::name("wd_xcx_diypage_sys")->where("id","in",$pageidArray)->find();
                            Db::name("wd_xcx_diypage_sys")->where("id",$diypageone['id'])->where("index",0)->update(array("index" => 1));
                            $diypage['id'] = $diypageone['id'];
                        }
                        $key_id = input('key_id') ? input('key_id') : $diypage['id'];  //显示页面id
                        if($key_id>0){

                            $data = Db::name("wd_xcx_diypage_sys")->where("id",$key_id)->find();
                            $data['page'] = unserialize($data['page']);
                            if(isset($data['page']['url']) && $data['page']['url'] != ""){
                                $data['page']['url'] = remote($appletid,$data['page']['url'],1);
                            }
                            $data['items'] = unserialize($data['items']);
                            if($data['items'] != ""){
                                if(isset($data['items']) && $data['items'] != ""){
                                    foreach ($data['items'] as $k => &$v) {
                                        if($v['id'] == 'title2' || $v['id'] == 'title' || $v['id'] == 'line' || $v['id'] == 'blank' || $v['id'] == 'anniu' || $v['id'] == 'notice' || $v['id'] == 'service' || $v['id'] == 'listmenu' || $v['id'] == 'joblist' || $v['id'] == 'personlist' || $v['id'] == 'msmk' || $v['id'] == 'multiple' || $v['id'] == 'mlist' || $v['id'] == 'goods' || $v['id'] == 'tabbar' || $v['id'] == 'cases' || $v['id'] == 'listdesc' || $v['id'] == 'pt' || $v['id'] == 'dt' || $v['id'] == 'ssk' || $v['id'] == 'xnlf' || $v['id'] == 'yhq' || $v['id'] == 'dnfw' || $v['id'] == 'yuyin' || $v['id'] == 'feedback' || $v['id'] == 'yuyin'){
                                            if($v['params']['backgroundimg'] != ""){
                                                $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],1);
                                            }
                                        }
                                        if($v['id'] == 'bigimg' || $v['id'] == 'classfit' || $v['id'] == 'banner' || $v['id'] == 'menu' || $v['id'] == 'picture' || $v['id'] == 'picturew'){

                                            if($v['params']['backgroundimg'] != ""){
                                                $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],1);
                                            }
                                            if($v['data']){
                                                foreach ($v['data'] as $ki => $vi) {
                                                    if($vi['imgurl'] != "" && strpos($vi['imgurl'],"diypage/resource") === false){
                                                        $v['data'][$ki]['imgurl'] = remote($appletid,$vi['imgurl'],1);

                                                    }
                                                }
                                            }
                                        }
                                        if($v['id'] == 'contact'){
                                            if($v['params']['backgroundimg'] != ""){
                                                $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],1);
                                            }
                                            if($v['params']['src'] != ""  && strpos($v['params']['src'],"diypage/resource") === false){
                                                $v['params']['src'] = remote($appletid,$v['params']['src'],1);
                                            }
                                            if($v['params']['ewm'] != ""  && strpos($v['params']['ewm'],"diypage/resource") === false){
                                                $v['params']['ewm'] = remote($appletid,$v['params']['ewm'],1);
                                            }
                                        }
                                        if($v['id'] == 'video'){
                                            if($v['params']['backgroundimg'] != ""){
                                                $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],1);
                                            }
                                            if($v['params']['poster'] != "" && strpos($v['params']['poster'],"diypage/resource") === false){
                                                $v['params']['poster'] = remote($appletid,$v['params']['poster'],1);
                                            }
                                        }
                                        if($v['id'] == 'logo' || $v['id'] == 'dp'){
                                            if($v['params']['backgroundimg'] != ""){
                                                $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],1);
                                            }
                                            if($v['params']['src'] != ""  && strpos($v['params']['src'],"diypage/resource") === false){
                                                $v['params']['src'] = remote($appletid,$v['params']['src'],1);
                                            }
                                        }
                                        if($v['id'] == 'footmenu'){
                                            if($v['data']){
                                                foreach ($v['data'] as $ki => $vi) {
                                                    if($vi['imgurl'] != "" && strpos($vi['imgurl'],"diypage/resource") === false){
                                                        $v['data'][$ki]['imgurl'] = remote($appletid,$vi['imgurl'],1);
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            $page = $data['page'];
                            if(isset($page['url']) && $page['url'] != ""){
                                $page['url'] = remote($appletid,$page['url'],1);
                            }
                            $diyform = Db::name("wd_xcx_formlist")->where("uniacid",$appletid)->field("id,formname as title")->select();
                            $data['diyform'] = $diyform;
                            $data = json_encode($data, JSON_UNESCAPED_UNICODE);
                            $data = preg_replace("/\'/", "\'", $data);
                            $data = preg_replace('/(\\\n)/', "<br>", $data);

                        }

                    }else{
                    
                        $temp = Db::name("wd_xcx_diypagetpl")->where("id",$tplid)->find();
                        if($temp['thumb']){
                            $temp['thumb'] = remote($appletid,$temp['thumb'],1);
                        }
                        if($temp['pageid'] == ""){
                            $pageid = Db::name("wd_xcx_diypage")->insertGetId(array(
                                'uniacid' => $appletid,
                                'index' => 1,
                                'page' => 'a:7:{s:10:"background";s:7:"#f1f1f1";s:13:"topbackground";s:7:"#ffffff";s:8:"topcolor";s:1:"1";s:9:"styledata";s:1:"0";s:5:"title";s:21:"小程序页面标题";s:4:"name";s:18:"后台页面名称";s:10:"visitlevel";a:2:{s:6:"member";s:0:"";s:10:"commission";s:0:"";}}',
                                'items' => '',
                                'tpl_name' => '后台页面名称',
                            ));
                            Db::name("wd_xcx_diypagetpl")->where("id",$tplid)->update(array("pageid"=>$pageid));
                            $temp = Db::name("wd_xcx_diypagetpl")->where("id",$tplid)->find();
                        }

                        //改变原来的模板状态为不启用
                        $tpls = Db::name("wd_xcx_diypagetpl")->where('uniacid',$appletid)->select();
                        if($tpls){
                            foreach ($tpls as $k => $v) {
                                Db::name("wd_xcx_diypagetpl")->where('uniacid',$appletid)->update(array('status' => 2));
                            }
                        }
                        Db::name("wd_xcx_diypagetpl")->where("id",$tplid)->update(array("status"=>1));

                        $pageidArray = explode(',',$temp['pageid']);

                        //查出当前模板所有的页面
                        $list = Db::name("wd_xcx_diypage")->where("uniacid",$appletid)->where("id","in",$pageidArray)->field("id,tpl_name,index")->select();
                        


                        //页面操作
                        $diypage = Db::name("wd_xcx_diypage")->where("uniacid",$appletid)->where("id","in",$pageidArray)->where("index",1)->find();
                        if($diypage == null){
                            $diypageone = Db::name("wd_xcx_diypage")->where("uniacid",$appletid)->where("id","in",$pageidArray)->find();
                            Db::name("wd_xcx_diypage")->where("uniacid",$appletid)->where("id",$diypageone['id'])->where("index",0)->update(array("index" => 1));
                            $diypage['id'] = $diypageone['id'];
                        }
                        $key_id = input('key_id') ? input('key_id') : $diypage['id'];  //显示页面id
                                                
                        if($key_id>0){
                            $data = Db::name("wd_xcx_diypage")->where("id",$key_id)->where("uniacid",$appletid)->find();
                            $data['page'] = unserialize($data['page']);
                            if(isset($data['page']['url']) && $data['page']['url'] != ""){
                                $data['page']['url'] = remote($appletid,$data['page']['url'],1);
                            }
                            $data['items'] = unserialize($data['items']);
                            if($data['items'] != ""){
                                if(isset($data['items']) && $data['items'] != ""){
                                    foreach ($data['items'] as $k => &$v) {
                                        if($v['id'] == 'title2' || $v['id'] == 'title' || $v['id'] == 'line' || $v['id'] == 'blank' || $v['id'] == 'anniu' || $v['id'] == 'notice' || $v['id'] == 'service' || $v['id'] == 'listmenu' || $v['id'] == 'joblist' || $v['id'] == 'personlist' || $v['id'] == 'msmk' || $v['id'] == 'multiple' || $v['id'] == 'mlist' || $v['id'] == 'goods' || $v['id'] == 'tabbar' || $v['id'] == 'cases' || $v['id'] == 'listdesc' || $v['id'] == 'pt' || $v['id'] == 'dt' || $v['id'] == 'ssk' || $v['id'] == 'xnlf' || $v['id'] == 'yhq' || $v['id'] == 'dnfw' || $v['id'] == 'feedback'  || $v['id'] == 'yuyin'){

                                            if($v['params']['backgroundimg'] != ""){
                                                $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],1);
                                            }
                                        }
                                        if($v['id'] == 'bigimg' || $v['id'] == 'classfit' || $v['id'] == 'banner' || $v['id'] == 'menu' || $v['id'] == 'picture' || $v['id'] == 'picturew'){
                                            if($v['params']['backgroundimg'] != ""){
                                                $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],1);
                                            }
                                            if($v['data']){
                                                foreach ($v['data'] as $ki => $vi) {
                                                    if($vi['imgurl'] != "" && strpos($vi['imgurl'],"diypage/resource") === false){
                                                        $v['data'][$ki]['imgurl'] = remote($appletid,$vi['imgurl'],1);

                                                    }
                                                }
                                            }
                                        }
                                        if($v['id'] == 'contact'){
                                            if($v['params']['backgroundimg'] != ""){
                                                $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],1);
                                            }
                                            if($v['params']['src'] != ""  && strpos($v['params']['src'],"diypage/resource") === false){
                                                $v['params']['src'] = remote($appletid,$v['params']['src'],1);
                                            }
                                            if($v['params']['ewm'] != ""  && strpos($v['params']['ewm'],"diypage/resource") === false){
                                                $v['params']['ewm'] = remote($appletid,$v['params']['ewm'],1);
                                            }
                                        }
                                        if($v['id'] == 'video'){
                                            if($v['params']['backgroundimg'] != ""){
                                                $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],1);
                                            }
                                            if($v['params']['poster'] != "" && strpos($v['params']['poster'],"diypage/resource") === false){
                                                $v['params']['poster'] = remote($appletid,$v['params']['poster'],1);
                                            }
                                        }
                                        if($v['id'] == 'logo' || $v['id'] == 'dp'){
                                            if($v['params']['backgroundimg'] != ""){
                                                $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],1);
                                            }
                                            if($v['params']['src'] != ""  && strpos($v['params']['src'],"diypage/resource") === false){
                                                $v['params']['src'] = remote($appletid,$v['params']['src'],1);
                                            }
                                        }
                                        if($v['id'] == 'footmenu'){
                                            if($v['data']){
                                                foreach ($v['data'] as $ki => $vi) {
                                                    if($vi['imgurl'] != "" && strpos($vi['imgurl'],"diypage/resource") === false){
                                                        $v['data'][$ki]['imgurl'] = remote($appletid,$vi['imgurl'],1);
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            $page = $data['page'];
                            if(isset($page['url']) && $page['url'] != ""){
                                $page['url'] = remote($appletid,$page['url'],1);
                            }
                            $diyform = Db::name("wd_xcx_formlist")->where("uniacid",$appletid)->field("id,formname as title")->select();
                            $data['diyform'] = $diyform;
                        
							
                            $data = json_encode($data, JSON_UNESCAPED_UNICODE);
                            $data = preg_replace("/\'/", "\'", $data);
                            $data = preg_replace('/(\\\n)/', "<br>", $data);
                            $data = preg_replace('/\"/', '\\"', $data);

                        }


                    }

//                    include_once 'Ordinary.php';
                    //$or = new \Ordinary();
                   // $plugin = $or ->checkPlugin();
                    //$this ->assign('plugin', $plugin);
                    $this->assign("page",$page);
                    $this->assign("template_id",$tplid);
                    $this->assign("key_id",$key_id);
                    $this->assign("list",$list);
                    $this->assign("data",$data);
                    $this->assign("setsave",$setsave);
                    $this->assign("foot_is",$foot_is);
                    $this->assign("temp",$temp);
                    $this->assign("bg_music",$bg_music);
                    $this->assign("siteurl",ROOT_HOST);
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
    public function addclass(){
        $classname = input("classname");
        $res = Db::name('wd_xcx_diypagetpl_sys_class')->insert(['name' => $classname]);
        if($res){
            $this->success("模板分类添加成功");
        }else{
            $this->error("模板分类添加失败");
        }
    }
    public function delclass(){
        $id = input("id");
        $res = Db::name('wd_xcx_diypagetpl_sys_class')->where('id', $id)->delete();
        if($res){
            $this->success("模板分类删除成功");
        }else{
            $this->error("模板分类删除失败");
        }
    }
    public function selectUrl(){
        $uniacid = input('appletid');
        $tplid = input('tplid_only'); //模板id
        if(!$tplid){
            $tplid = Db::name('wd_xcx_diypagetpl')->where("uniacid",$uniacid)->where("status",1)->find()['id'];
        }
        $pageid = explode(",",Db::name('wd_xcx_diypagetpl')->where("uniacid",$uniacid)->where("id",$tplid)->field("pageid")->find()['pageid']); //当前模板拥有的页面id
        $diypage = Db::name('wd_xcx_diypage')->where("uniacid",$uniacid)->where("id","in",$pageid)->field("id,tpl_name")->select();

//        include_once 'Ordinary.php';
        //$or = new \Ordinary();
        //$plugin = $or ->checkPlugin();
        //$this ->assign('plugin', $plugin);


        $article = Db::name('wd_xcx_products')->where("uniacid",$uniacid)->where("type","showArt")->field("id,title")->select();
        $where = [];
        if(!$plugin['ms']){
            $where['is_more'] = ['neq', 0];
        }
        if(!$plugin['yu']){
            $where['is_more'] = ['neq', 1];
        }

        $pro = Db::name('wd_xcx_products')->where("uniacid",$uniacid)->where("type","neq","showArt") ->where(function ($query) use($plugin){
            if(!$plugin['ms']){
                $query ->where('is_more', 'neq', 0);
            }
            if(!$plugin['yu']){
                $query ->where('is_more', 'neq', 1);
            }
        }) ->where("type","neq","showPic")->where("type","neq","wxapp") ->where('is_sale', 0) ->field("id,title,type,is_more")->select();
        if($pro){
            foreach ($pro as $k => $v) {
                if($v['is_more'] == 0){
                    $pro[$k]['type'] = "flashsale";
                }
                if($v['is_more'] == 1){
                    $pro[$k]['type'] = "reserve";
                }
                if($v['is_more'] == 3){
                    $pro[$k]['type'] = "showProMore";
                }
            }
        }
        $pic = Db::name('wd_xcx_products')->where("uniacid",$uniacid)->where("type","showPic")->field("id,title")->select();
        $cates = Db::name('wd_xcx_cate')->where("uniacid",$uniacid)->where("cid",0)->field("id,name,type")->select();
        if($cates){
            foreach ($cates as $k => $v) {
                if($v['type'] == "showPro"){
                    $cates[$k]['show_type'] = "showPro";
                    $cates[$k]['type'] = "catelist";
                }
                if($v['type'] == "showPic" || $v['type'] == "showArt"){
                    $cates[$k]['show_type'] = $v['type'];
                    $cates[$k]['type'] = "listPic";
                }
                $subcate = Db::name('wd_xcx_cate')->where("uniacid",$uniacid)->where("cid",$v['id'])->field("id,name,type")->select();
                foreach ($subcate as $ki=> $vi) {
                    if($vi['type'] == "showPro"){
                        $subcate[$ki]['show_type'] = "showPro";
                        $subcate[$ki]['type'] = "checkPro";
                    }
                    if($vi['type'] == "showPic" || $vi['type'] == "showArt"){
                        $subcate[$ki]['show_type'] = $vi['type'];
                        $subcate[$ki]['type'] = "listPic";
                    }
                }
                $cates[$k]['subcate'] = $subcate;
            }
        }


        $this->assign("diypage",$diypage);
        $this->assign("article",$article);
        $this->assign("pro",$pro);
        $this->assign("pic",$pic);
        $this->assign("cates",$cates);
        $this->assign("uniacid",$uniacid);
        return $this->fetch('selecturl');
    }
    public function selectsource(){

        $uniacid = input("appletid");

        $type = input('type');

        switch ($type){

            case 'noticcate':

                $list = Db::name("wd_xcx_cate")->where("uniacid",$uniacid)->where("type","showArt")->where("cid",0)->field("id,name")->select();
                foreach ($list as $key => &$value) {
                    $subcate = Db::name("wd_xcx_cate")->where("uniacid",$uniacid)->where("type","showArt")->where("cid",$value['id'])->field("id,name")->select();
                    $value['subcate'] = $subcate;
                }
                break;

            case 'goodscate':

                $list = Db::name("wd_xcx_cate")->where("uniacid",$uniacid)->where("type","showPro")->where("cid",0)->field("id,name")->select();
                foreach ($list as $key => &$value) {
                    $subcate = Db::name("wd_xcx_cate")->where("uniacid",$uniacid)->where("type","showPro")->where("cid",$value['id'])->field("id,name")->select();
                    $value['subcate'] = $subcate;
                }
                break;

            case 'piccate':


                $list = Db::name("wd_xcx_cate")->where("uniacid",$uniacid)->where("type","showPic")->where("cid",0)->field("id,name")->select();
                foreach ($list as $key => &$value) {
                    $subcate = Db::name("wd_xcx_cate")->where("uniacid",$uniacid)->where("type","showPic")->where("cid",$value['id'])->field("id,name")->select();
                    $value['subcate'] = $subcate;
                }
                break;

            case 'picartcate':

                $list = Db::query("SELECT id,name,type FROM {$this->prefix}wd_xcx_cate WHERE `uniacid` = {$uniacid} AND `cid` = 0 AND (`type` = 'showPic' or `type` = 'showArt')");
                foreach ($list as $key => &$value) {
                    $subcate = Db::query("SELECT id,name,type FROM {$this->prefix}wd_xcx_cate WHERE `uniacid` = {$uniacid} AND (`type` = 'showPic' or `type` = 'showArt') AND cid = {$value['id']}");
                    $value['subcate'] = $subcate;
                }
                break;

            case 'articlecate':


                $list = Db::name("wd_xcx_cate")->where("uniacid",$uniacid)->where("type","showArt")->where("cid",0)->field("id,name")->select();
                foreach ($list as $key => &$value) {
                    $subcate = Db::name("wd_xcx_cate")->where("uniacid",$uniacid)->where("type","showArt")->where("cid",$value['id'])->field("id,name")->select();
                    $value['subcate'] = $subcate;
                }
                break;
            case 'ptcate':

                $list = Db::name("wd_xcx_pt_cate")->where("uniacid",$uniacid)->field("id,title as name")->select();
                foreach ($list as $key => &$value) {
                    $value['subcate'] = "";
                }
                break;
                
            case 'formcate':

                $list = Db::name("wd_xcx_formlist")->where("uniacid",$uniacid)->field("id,formtitle as name")->order('id desc')->select();
                foreach ($list as $key => &$value) {
                    $value['subcate'] = "";
                }
                break;

            case 'flashsale':

                $list = Db::name("wd_xcx_flashsale_cate")->where("uniacid",$uniacid)->where("catefor","flashsale")->where("cid",0)->field("id,name")->select();
                foreach ($list as $key => &$value) {
                    $subcate = Db::name("wd_xcx_flashsale_cate")->where("uniacid",$uniacid)->where("catefor","flashsale")->where("cid",$value['id'])->field("id,name")->select();
                    $value['subcate'] = $subcate;
                }
                break;


            case 'reservecate':

                $list = Db::name("wd_xcx_flashsale_cate")->where("uniacid",$uniacid)->where("catefor","reserve")->where("cid",0)->field("id,name")->select();
                foreach ($list as $key => &$value) {
                    $subcate = Db::name("wd_xcx_flashsale_cate")->where("uniacid",$uniacid)->where("catefor","reserve")->where("cid",$value['id'])->field("id,name")->select();
                    $value['subcate'] = $subcate;
                }
                break;

            case 'assemblecate':

                $list = Db::name("wd_xcx_flashsale_cate")->where("uniacid",$uniacid)->where("catefor","assemble")->where("cid",0)->field("id,name")->select();
                foreach ($list as $key => &$value) {
                    $subcate = Db::name("wd_xcx_flashsale_cate")->where("uniacid",$uniacid)->where("catefor","assemble")->where("cid",$value['id'])->field("id,name")->select();
                    $value['subcate'] = $subcate;
                }
                break;
                
            case 'bargain':

                $list = Db::name("wd_xcx_bargain_cate")->where("uniacid",$uniacid)->field("id,title as name")->select();
                foreach ($list as $key => &$value) {
                    $value['subcate'] = "";
                }
                break;

        }
        $this->assign("type",$type);

        $this->assign("list",$list);

        $this->assign("uniacid",$uniacid);

        return $this->fetch('selectsource');

    }

    public function selecticon(){
        return $this->fetch('icon');
    }

    public function imgupload(){
        $uniacid = input("uniacid");
        $groupid = 0;
        $url = getRemoteType($uniacid, $groupid, 3);
        return $url;
    }

    public function imguploadLocal(){
        $uniacid = input('uniacid');
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

    public function moban(){
        if(check_login()){
            if(powerget()){

                $appletid = input("appletid");

                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();

                $usergroup = Session::get('usergroup');

                $this->assign('usergroup', $usergroup);


                if(!$res){

                    $this->error("找不到对应的小程序！");
                }

                $this->assign('applet',$res);


                $is = Db::name("wd_xcx_diypagetpl")->where('uniacid',$appletid)->select();

                //将原有页面放到一个模板中
                if(!$is){
                    $pages = Db::name("wd_xcx_diypage")->where('uniacid',$appletid)->field('id')->select();
                    if($pages){
                        $pageids = '';
                        foreach ($pages as $key => $value) {
                            $pageids .= ','.$value['id'];
                        }
                        $pageids = substr($pageids,1);
                        $data = [
                            'pageid' => $pageids,
                            'uniacid' => $appletid,
                            'template_name' => '原有页面模板',
                            'thumb' => "/diypage/img/blank.jpg",
                            'status' => 1,
                            'create_time' => time()
                        ];
                        Db::name("wd_xcx_diypagetpl")->insert($data);
                    }
                }
                
                $moban = Db::name("wd_xcx_diypagetpl")->where('uniacid',$appletid)->select();
                foreach ($moban as $key => &$value) {
                    $value['thumb'] = remote($appletid, $value['thumb'], 1);
                }

                $classid = input('classid');
                if($classid == 0){
                    $moban_sys = Db::name("wd_xcx_diypagetpl_sys")->select();
                }else{
                    $moban_sys = Db::name("wd_xcx_diypagetpl_sys")->where('classid', $classid)->select();
                }
                foreach ($moban_sys as $key => &$value) {
                    // $value['thumb'] = remote($appletid, $value['thumb'], 1);
                }

                $moban_class = Db::name('wd_xcx_diypagetpl_sys_class')->select();
                $usergroup = Session::get('usergroup');  //总管理员  2    代理商  3   用户 1
                
                // dump($moban);die;
                $this->assign("usergroup",$usergroup);
                $this->assign("moban_class",$moban_class);
                $this->assign("classid",$classid);
                $this->assign("moban_sys",$moban_sys);
                $this->assign("moban",$moban);

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
            return $this->fetch('moban');
        }else{

            $this->redirect('Login/index');

        }
    }
    public function moban_copy(){
        $id = input("id");
        $uniacid = input("appletid");
        //改变原来的模板状态为不启用
        $tpls = Db::name("wd_xcx_diypagetpl")->where('uniacid',$uniacid)->select();
        if($tpls){
            foreach ($tpls as $k => $v) {
                Db::name("wd_xcx_diypagetpl")->where('uniacid',$uniacid)->update(array('status' => 2));
            }
        }
        if($id == 'm_shops'){
            $page1 = [];
            $page1['uniacid'] = $uniacid;
            $page1['index'] = 1;
            $item1 = 'a:10:{s:14:"M1543281981683";a:4:{s:4:"icon";s:22:"iconfont2 icon-sousuo1";s:6:"params";a:7:{s:5:"value";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:12:{s:9:"textalign";s:4:"left";s:10:"background";s:7:"#ff3420";s:2:"bg";s:4:"#fff";s:12:"borderradius";s:1:"5";s:6:"boxpdh";s:2:"10";s:6:"boxpdz";s:2:"15";s:7:"padding";s:1:"5";s:8:"fontsize";s:2:"13";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"color";s:7:"#c0c0c0";}s:2:"id";s:3:"ssk";}s:14:"M1543281975902";a:5:{s:4:"icon";s:28:"iconfont2 icon-tuoyuankaobei";s:6:"params";a:10:{s:5:"totle";s:1:"2";s:8:"navstyle";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:9:"navstyle2";s:1:"0";s:4:"imgh";s:3:"134";}s:5:"style";a:18:{s:8:"dotstyle";s:5:"round";s:8:"dotalign";s:4:"left";s:10:"paddingtop";s:1:"0";s:11:"paddingleft";s:1:"0";s:10:"background";s:7:"#ffffff";s:13:"backgroundall";s:7:"#ffffff";s:9:"leftright";s:1:"5";s:6:"bottom";s:2:"10";s:7:"opacity";s:3:"0.8";s:10:"text_color";s:4:"#fff";s:2:"bg";s:7:"#000000";s:9:"jsq_color";s:3:"red";s:3:"pdh";s:1:"0";s:3:"pdw";s:1:"0";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"speed";s:1:"5";}s:4:"data";a:2:{s:14:"C1543281975902";a:5:{s:6:"imgurl";s:67:"https://four.nttrip.cn/template_img/template_shop/index/banner1.png";s:7:"linkurl";s:0:"";s:6:"single";s:1:"1";s:4:"text";s:12:"文字描述";s:8:"linktype";s:4:"page";}s:14:"C1543281975903";a:5:{s:6:"imgurl";s:67:"https://four.nttrip.cn/template_img/template_shop/index/banner1.png";s:7:"linkurl";s:0:"";s:6:"single";s:1:"2";s:4:"text";s:12:"文字描述";s:8:"linktype";s:4:"page";}}s:2:"id";s:6:"banner";}s:14:"M1543282017516";a:5:{s:4:"icon";s:22:"iconfont2 icon-anniuzu";s:6:"params";a:8:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"picicon";s:1:"1";s:8:"textshow";s:1:"1";}s:5:"style";a:14:{s:8:"navstyle";s:0:"";s:10:"background";s:7:"#ffffff";s:6:"rownum";s:1:"4";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"8";s:7:"showdot";s:1:"1";s:7:"padding";s:1:"0";s:11:"paddingleft";s:2:"10";s:2:"mt";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:6:"iconfz";s:2:"14";s:9:"iconcolor";s:7:"#434343";s:8:"imgwidth";s:2:"30";}s:4:"data";a:8:{s:14:"C1543282017516";a:6:{s:6:"imgurl";s:65:"https://four.nttrip.cn/template_img/template_shop/index/menu1.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"美妆护肤";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";s:8:"linktype";s:4:"page";}s:14:"C1543282017517";a:6:{s:6:"imgurl";s:65:"https://four.nttrip.cn/template_img/template_shop/index/menu2.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"家具家纺";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";s:8:"linktype";s:4:"page";}s:14:"C1543282017518";a:6:{s:6:"imgurl";s:65:"https://four.nttrip.cn/template_img/template_shop/index/menu3.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"母婴用品";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";s:8:"linktype";s:4:"page";}s:14:"C1543282017519";a:6:{s:6:"imgurl";s:65:"https://four.nttrip.cn/template_img/template_shop/index/menu4.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"生活美食";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";s:8:"linktype";s:4:"page";}s:14:"M1543282019893";a:6:{s:6:"imgurl";s:65:"https://four.nttrip.cn/template_img/template_shop/index/menu5.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"图书音像";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";s:8:"linktype";s:4:"page";}s:14:"M1543282021620";a:6:{s:6:"imgurl";s:65:"https://four.nttrip.cn/template_img/template_shop/index/menu6.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"数码家电";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";s:8:"linktype";s:4:"page";}s:14:"M1543282022851";a:6:{s:6:"imgurl";s:65:"https://four.nttrip.cn/template_img/template_shop/index/menu7.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"衣帽服饰";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";s:8:"linktype";s:4:"page";}s:14:"M1543282024044";a:6:{s:6:"imgurl";s:65:"https://four.nttrip.cn/template_img/template_shop/index/menu8.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"查看更多";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";s:8:"linktype";s:4:"page";}}s:2:"id";s:4:"menu";}s:14:"M1543282028006";a:5:{s:4:"icon";s:19:"iconfont icon-c-pdf";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:6:{s:10:"background";s:7:"#ffffff";s:3:"pdw";s:1:"0";s:3:"pdh";s:1:"0";s:2:"mt";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";}s:4:"data";a:4:{s:14:"C1543282028006";a:7:{s:6:"imgurl";s:69:"https://four.nttrip.cn/template_img/template_shop/index/classfit1.png";s:5:"title";s:12:"新品预约";s:4:"text";s:6:"More >";s:3:"bg1";s:7:"#ffffff";s:3:"bg2";s:7:"#ffffff";s:7:"linkurl";s:0:"";s:8:"linktype";s:4:"page";}s:14:"C1543282028007";a:7:{s:6:"imgurl";s:69:"https://four.nttrip.cn/template_img/template_shop/index/classfit2.png";s:5:"title";s:12:"特惠秒杀";s:4:"text";s:6:"More >";s:3:"bg1";s:7:"#ffffff";s:3:"bg2";s:7:"#ffffff";s:7:"linkurl";s:0:"";s:8:"linktype";s:4:"page";}s:14:"C1543282028008";a:7:{s:6:"imgurl";s:69:"https://four.nttrip.cn/template_img/template_shop/index/classfit3.png";s:5:"title";s:12:"人气爆款";s:4:"text";s:6:"More >";s:3:"bg1";s:7:"#ffffff";s:3:"bg2";s:7:"#ffffff";s:7:"linkurl";s:0:"";s:8:"linktype";s:4:"page";}s:14:"C1543282028009";a:7:{s:6:"imgurl";s:69:"https://four.nttrip.cn/template_img/template_shop/index/classfit4.png";s:5:"title";s:9:"拼团购";s:4:"text";s:6:"More >";s:3:"bg1";s:7:"#ffffff";s:3:"bg2";s:7:"#ffffff";s:7:"linkurl";s:0:"";s:8:"linktype";s:4:"page";}}s:2:"id";s:8:"classfit";}s:14:"M1543282043688";a:6:{s:4:"icon";s:28:"iconfont2 icon-tuoyuankaobei";s:6:"params";a:10:{s:5:"totle";s:1:"2";s:8:"navstyle";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:9:"navstyle2";s:1:"0";s:4:"imgh";s:2:"80";}s:5:"style";a:18:{s:8:"dotstyle";s:5:"round";s:8:"dotalign";s:4:"left";s:10:"paddingtop";s:2:"15";s:11:"paddingleft";s:2:"10";s:10:"background";s:7:"#f1f1f1";s:13:"backgroundall";s:7:"#ffffff";s:9:"leftright";s:1:"5";s:6:"bottom";s:1:"5";s:7:"opacity";s:3:"0.8";s:10:"text_color";s:4:"#fff";s:2:"bg";s:7:"#000000";s:9:"jsq_color";s:3:"red";s:3:"pdh";s:1:"0";s:3:"pdw";s:1:"0";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"speed";s:1:"5";}s:4:"data";a:2:{s:14:"C1543282043688";a:5:{s:6:"imgurl";s:67:"https://four.nttrip.cn/template_img/template_shop/index/banner2.png";s:7:"linkurl";s:0:"";s:6:"single";s:1:"1";s:4:"text";s:12:"文字描述";s:8:"linktype";s:4:"page";}s:14:"C1543282043689";a:5:{s:6:"imgurl";s:67:"https://four.nttrip.cn/template_img/template_shop/index/banner2.png";s:7:"linkurl";s:0:"";s:6:"single";s:1:"2";s:4:"text";s:12:"文字描述";s:8:"linktype";s:4:"page";}}s:2:"id";s:6:"banner";s:5:"index";s:3:"NaN";}s:14:"M1543282557409";a:4:{s:4:"icon";s:32:"iconfont2 icon-liebiao_biaotilan";s:6:"params";a:9:{s:5:"title";s:12:"热销推荐";s:6:"title2";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:5:"style";s:1:"1";}s:5:"style";a:11:{s:10:"background";s:7:"#ffffff";s:6:"colorz";s:7:"#434343";s:6:"colorf";s:7:"#838383";s:9:"linecolor";s:7:"#000000";s:9:"fontsizez";s:2:"18";s:9:"fontsizef";s:2:"12";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:1:"2";}s:2:"id";s:6:"title2";}s:14:"M1543282585056";a:5:{s:4:"icon";s:22:"iconfont2 icon-chanpin";s:6:"params";a:31:{s:11:"goodsscroll";s:1:"0";s:9:"showtitle";s:1:"1";s:9:"showprice";s:1:"1";s:7:"showtag";s:1:"0";s:9:"goodsdata";s:1:"1";s:6:"cateid";s:0:"";s:8:"catename";s:0:"";s:7:"groupid";s:0:"";s:9:"groupname";s:0:"";s:9:"goodssort";s:1:"0";s:8:"goodsnum";s:1:"4";s:8:"showicon";s:1:"0";s:12:"iconposition";s:8:"left top";s:12:"productprice";s:1:"1";s:16:"showproductprice";s:1:"0";s:9:"showsales";s:1:"1";s:16:"productpricetext";s:6:"原价";s:9:"salestext";s:6:"销量";s:16:"productpriceline";s:1:"0";s:7:"saleout";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"imgh_is";s:1:"1";s:4:"imgh";s:3:"100";s:7:"con_key";s:1:"2";s:8:"con_type";s:1:"4";s:8:"sourceid";s:0:"";}s:5:"style";a:20:{s:10:"background";s:7:"#ffffff";s:9:"liststyle";s:5:"block";s:8:"buystyle";s:0:"";s:9:"goodsicon";s:9:"recommand";s:9:"iconstyle";s:8:"triangle";s:10:"pricecolor";s:7:"#ff5555";s:17:"productpricecolor";s:7:"#999999";s:14:"iconpaddingtop";s:1:"0";s:15:"iconpaddingleft";s:1:"0";s:11:"buybtncolor";s:7:"#ff5555";s:8:"iconzoom";s:2:"50";s:10:"titlecolor";s:7:"#000000";s:13:"tagbackground";s:7:"#fe5455";s:10:"salescolor";s:7:"#999999";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:8:"showtype";s:1:"0";}s:4:"data";a:4:{s:14:"C1543282585056";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:3:"des";s:21:"这里是产品描述";}s:14:"C1543282585057";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1543282585058";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1543282585059";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}}s:2:"id";s:5:"goods";}s:14:"M1543282618278";a:4:{s:4:"icon";s:32:"iconfont2 icon-liebiao_biaotilan";s:6:"params";a:9:{s:5:"title";s:12:"当季新品";s:6:"title2";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:5:"style";s:1:"1";}s:5:"style";a:11:{s:10:"background";s:7:"#ffffff";s:6:"colorz";s:7:"#434343";s:6:"colorf";s:7:"#838383";s:9:"linecolor";s:7:"#000000";s:9:"fontsizez";s:2:"18";s:9:"fontsizef";s:2:"12";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:2:"10";}s:2:"id";s:6:"title2";}s:14:"M1544057042087";a:6:{s:4:"icon";s:21:"iconfont2 icon-tupian";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:7:{s:10:"paddingtop";s:1:"8";s:11:"paddingleft";s:2:"10";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"background";s:7:"#ffffff";s:12:"borderradius";s:1:"0";}s:4:"data";a:1:{s:14:"C1544057042087";a:5:{s:6:"imgurl";s:67:"https://four.nttrip.cn/template_img/template_shop/index/bigimg1.png";s:7:"linkurl";s:0:"";s:5:"title";s:0:"";s:4:"text";s:0:"";s:8:"linktype";s:4:"page";}}s:2:"id";s:6:"bigimg";s:5:"index";s:3:"NaN";}s:14:"M1544057043069";a:6:{s:4:"icon";s:21:"iconfont2 icon-tupian";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:7:{s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"background";s:7:"#ffffff";s:12:"borderradius";s:1:"0";}s:4:"data";a:1:{s:14:"C1544057043069";a:5:{s:6:"imgurl";s:67:"https://four.nttrip.cn/template_img/template_shop/index/bigimg2.png";s:7:"linkurl";s:0:"";s:5:"title";s:0:"";s:4:"text";s:0:"";s:8:"linktype";s:4:"page";}}s:2:"id";s:6:"bigimg";s:5:"index";s:3:"NaN";}}';
            $item1 = unserialize($item1);
            foreach($item1 as $k => &$v){
                if(isset($v['data'])){
                    foreach($v['data'] as $kk => &$vv){
                        if(isset($vv['imgurl']) && strpos($vv['imgurl'],'https://four.nttrip.cn/') !== false){
                            $vv['imgurl'] = ROOT_HOST."/diypage/".explode('https://four.nttrip.cn/', $vv['imgurl'])[1];
                        }
                        if(isset($vv['thumb']) && strpos($vv['thumb'],'/diypage/resource/images/diypage/default/2.jpg') !== false){
                            $vv['thumb'] = ROOT_HOST."/diypage/resource/images/diypage/default/2.jpg";
                        }
                    }
                }
            }
            $page1['items'] = serialize($item1);
            $page1['page'] = 'a:7:{s:10:"background";s:7:"#f1f1f1";s:13:"topbackground";s:7:"#ff3420";s:8:"topcolor";s:1:"2";s:9:"styledata";s:1:"0";s:5:"title";s:6:"商城";s:4:"name";s:6:"首页";s:10:"visitlevel";a:2:{s:6:"member";s:0:"";s:10:"commission";s:0:"";}}';
            $page1['tpl_name'] = "首页";

            $page2 = [];
            $page2['uniacid'] = $uniacid;
            $page2['index'] = 0;
            $item2 = 'a:3:{s:14:"M1543283129002";a:5:{s:4:"icon";s:19:"iconfont2 icon-zutu";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:6:{s:10:"paddingtop";s:1:"0";s:11:"paddingleft";s:1:"0";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"background";s:7:"#ffffff";}s:4:"data";a:1:{s:14:"C1543283129002";a:2:{s:6:"imgurl";s:67:"https://four.nttrip.cn/template_img/template_shop/page/picture2.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:7:"picture";}s:14:"M1543283143522";a:5:{s:4:"icon";s:22:"iconfont2 icon-chanpin";s:6:"params";a:31:{s:11:"goodsscroll";s:1:"0";s:9:"showtitle";s:1:"1";s:9:"showprice";s:1:"1";s:7:"showtag";s:1:"0";s:9:"goodsdata";s:1:"1";s:6:"cateid";s:0:"";s:8:"catename";s:0:"";s:7:"groupid";s:0:"";s:9:"groupname";s:0:"";s:9:"goodssort";s:1:"0";s:8:"goodsnum";s:1:"2";s:8:"showicon";s:1:"0";s:12:"iconposition";s:8:"left top";s:12:"productprice";s:1:"1";s:16:"showproductprice";s:1:"0";s:9:"showsales";s:1:"1";s:16:"productpricetext";s:6:"原价";s:9:"salestext";s:6:"销量";s:16:"productpriceline";s:1:"0";s:7:"saleout";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"imgh_is";s:1:"1";s:4:"imgh";s:3:"100";s:7:"con_key";s:1:"2";s:8:"con_type";s:1:"4";s:8:"sourceid";s:0:"";}s:5:"style";a:20:{s:10:"background";s:7:"#f3f3f3";s:9:"liststyle";s:5:"block";s:8:"buystyle";s:0:"";s:9:"goodsicon";s:7:"hotsale";s:9:"iconstyle";s:7:"echelon";s:10:"pricecolor";s:7:"#ff5555";s:17:"productpricecolor";s:7:"#999999";s:14:"iconpaddingtop";s:1:"0";s:15:"iconpaddingleft";s:1:"0";s:11:"buybtncolor";s:7:"#ff5555";s:8:"iconzoom";s:2:"50";s:10:"titlecolor";s:7:"#000000";s:13:"tagbackground";s:7:"#fe5455";s:10:"salescolor";s:7:"#999999";s:2:"mt";s:1:"7";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"paddingtop";s:1:"0";s:11:"paddingleft";s:1:"4";s:8:"showtype";s:1:"0";}s:4:"data";a:4:{s:14:"C1543283143523";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:3:"des";s:21:"这里是产品描述";}s:14:"C1543283143524";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1543283143525";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1543283143526";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}}s:2:"id";s:5:"goods";}s:14:"M1544152252605";a:5:{s:4:"icon";s:22:"iconfont2 icon-chanpin";s:6:"params";a:31:{s:11:"goodsscroll";s:1:"0";s:9:"showtitle";s:1:"1";s:9:"showprice";s:1:"1";s:7:"showtag";s:1:"0";s:9:"goodsdata";s:1:"1";s:6:"cateid";s:0:"";s:8:"catename";s:0:"";s:7:"groupid";s:0:"";s:9:"groupname";s:0:"";s:9:"goodssort";s:1:"0";s:8:"goodsnum";s:1:"2";s:8:"showicon";s:1:"0";s:12:"iconposition";s:8:"left top";s:12:"productprice";s:1:"1";s:16:"showproductprice";s:1:"0";s:9:"showsales";s:1:"1";s:16:"productpricetext";s:6:"原价";s:9:"salestext";s:6:"销量";s:16:"productpriceline";s:1:"0";s:7:"saleout";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"imgh_is";s:1:"1";s:4:"imgh";s:3:"100";s:7:"con_key";s:1:"1";s:8:"con_type";s:1:"1";s:8:"sourceid";s:0:"";}s:5:"style";a:20:{s:10:"background";s:7:"#f3f3f3";s:9:"liststyle";s:5:"block";s:8:"buystyle";s:0:"";s:9:"goodsicon";s:7:"hotsale";s:9:"iconstyle";s:7:"echelon";s:10:"pricecolor";s:7:"#ff5555";s:17:"productpricecolor";s:7:"#999999";s:14:"iconpaddingtop";s:1:"0";s:15:"iconpaddingleft";s:1:"0";s:11:"buybtncolor";s:7:"#ff5555";s:8:"iconzoom";s:2:"50";s:10:"titlecolor";s:7:"#000000";s:13:"tagbackground";s:7:"#fe5455";s:10:"salescolor";s:7:"#999999";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"paddingtop";s:1:"0";s:11:"paddingleft";s:1:"4";s:8:"showtype";s:1:"0";}s:4:"data";a:4:{s:14:"C1544152252605";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:3:"des";s:21:"这里是产品描述";}s:14:"C1544152252606";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1544152252607";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1544152252608";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}}s:2:"id";s:5:"goods";}}';
            $item2 = unserialize($item2);
            foreach($item2 as &$vi){
                if(isset($vi['data'])){
                    foreach($vi['data'] as &$vvi){
                        if(isset($vvi['imgurl']) && strpos($vvi['imgurl'],'https://four.nttrip.cn/') !== false){
                            $vvi['imgurl'] = ROOT_HOST."/diypage/".explode('https://four.nttrip.cn/', $vvi['imgurl'])[1];
                        }
                        if(isset($vvi['thumb']) && strpos($vvi['thumb'],'/diypage/resource/images/diypage/default/2.jpg') !== false){
                            $vvi['thumb'] = ROOT_HOST."/diypage/resource/images/diypage/default/2.jpg";
                        }
                    }
                }
            }
            $page2['items'] = serialize($item2);
            $page2['page'] = 'a:7:{s:10:"background";s:7:"#f1f1f1";s:13:"topbackground";s:7:"#ff3420";s:8:"topcolor";s:1:"2";s:9:"styledata";s:1:"0";s:5:"title";s:12:"人气爆款";s:4:"name";s:12:"人气爆款";s:10:"visitlevel";a:2:{s:6:"member";s:0:"";s:10:"commission";s:0:"";}}';
            $page2['tpl_name'] = "人气爆款";

            $page3 = [];
            $page3['uniacid'] = $uniacid;
            $page3['index'] = 0;
            $item3 = 'a:2:{s:14:"M1543283208353";a:5:{s:4:"icon";s:19:"iconfont2 icon-zutu";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:6:{s:10:"paddingtop";s:1:"0";s:11:"paddingleft";s:1:"0";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"background";s:7:"#ffffff";}s:4:"data";a:1:{s:14:"C1543283208353";a:2:{s:6:"imgurl";s:67:"https://four.nttrip.cn/template_img/template_shop/page/picture1.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:7:"picture";}s:14:"M1543992326902";a:5:{s:4:"icon";s:34:"iconfont2 icon-pintuanweixuanzhong";s:6:"params";a:11:{s:8:"navstyle";s:1:"1";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:8:"goodsnum";s:1:"2";s:7:"con_key";s:1:"1";s:8:"con_type";s:1:"1";s:8:"sourceid";s:0:"";}s:5:"style";a:8:{s:10:"background";s:7:"#ffffff";s:3:"pdw";s:2:"10";s:3:"pdh";s:2:"10";s:2:"mb";s:2:"10";s:2:"mt";s:1:"1";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:4:"pich";s:1:"1";}s:4:"data";a:3:{s:14:"C1543992326902";a:11:{s:6:"imgurl";s:47:"/diypage/resource/images/diypage/default/11.jpg";s:5:"title";s:18:"拼团商品标题";s:11:"description";s:18:"拼团商品简介";s:5:"count";s:3:"927";s:5:"price";s:2:"21";s:6:"person";s:1:"2";s:2:"tz";s:2:"92";s:3:"tgr";s:3:"427";s:3:"tgj";s:5:"17.20";s:7:"linkurl";s:0:"";s:8:"linktype";s:0:"";}s:14:"C1543992326903";a:11:{s:6:"imgurl";s:47:"/diypage/resource/images/diypage/default/11.jpg";s:5:"title";s:18:"拼团商品标题";s:11:"description";s:18:"拼团商品简介";s:5:"count";s:3:"927";s:5:"price";s:2:"21";s:6:"person";s:1:"2";s:2:"tz";s:2:"92";s:3:"tgr";s:3:"427";s:3:"tgj";s:4:"17.2";s:7:"linkurl";s:0:"";s:8:"linktype";s:0:"";}s:14:"C1543992326904";a:11:{s:6:"imgurl";s:47:"/diypage/resource/images/diypage/default/11.jpg";s:5:"title";s:18:"拼团商品标题";s:11:"description";s:18:"拼团商品简介";s:5:"count";s:3:"927";s:5:"price";s:2:"21";s:6:"person";s:1:"2";s:2:"tz";s:2:"92";s:3:"tgr";s:3:"427";s:3:"tgj";s:4:"17.2";s:7:"linkurl";s:0:"";s:8:"linktype";s:0:"";}}s:2:"id";s:2:"pt";}}';
            $item3 = unserialize($item3);
            foreach($item3 as &$vi){
                if(isset($vi['data'])){
                    foreach($vi['data'] as &$vvi){
                        if(isset($vvi['imgurl']) && strpos($vvi['imgurl'],'https://four.nttrip.cn/') !== false){
                            $vvi['imgurl'] = ROOT_HOST."/diypage/".explode('https://four.nttrip.cn/', $vvi['imgurl'])[1];
                        }
                        if(isset($vvi['imgurl']) && strpos($vvi['imgurl'],'/diypage/resource/images/diypage/default/11.jpg') !== false){
                            $vvi['imgurl'] = ROOT_HOST."/diypage/resource/images/diypage/default/11.jpg";
                        }
                    }
                }
            }
            $page3['items'] = serialize($item3);
            $page3['page'] = 'a:7:{s:10:"background";s:7:"#f1f1f1";s:13:"topbackground";s:7:"#ff3420";s:8:"topcolor";s:1:"2";s:9:"styledata";s:1:"0";s:5:"title";s:9:"拼团购";s:4:"name";s:9:"拼团购";s:10:"visitlevel";a:2:{s:6:"member";s:0:"";s:10:"commission";s:0:"";}}';
            $page3['tpl_name'] = "拼团购";

            $page4 = [];
            $page4['uniacid'] = $uniacid;
            $page4['index'] = 0;
            $item4 = 'a:1:{s:14:"M1543283679197";a:6:{s:4:"icon";s:19:"iconfont2 icon-zutu";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:6:{s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"15";s:2:"mt";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"background";s:7:"#f1f1f1";}s:4:"data";a:4:{s:14:"C1543283679197";a:3:{s:6:"imgurl";s:67:"https://four.nttrip.cn/template_img/template_shop/page/picture5.png";s:7:"linkurl";s:0:"";s:8:"linktype";s:4:"page";}s:14:"M1543283680688";a:3:{s:6:"imgurl";s:67:"https://four.nttrip.cn/template_img/template_shop/page/picture6.png";s:7:"linkurl";s:0:"";s:8:"linktype";s:4:"page";}s:14:"M1543283681989";a:3:{s:6:"imgurl";s:67:"https://four.nttrip.cn/template_img/template_shop/page/picture7.png";s:7:"linkurl";s:0:"";s:8:"linktype";s:4:"page";}s:14:"M1543283716630";a:3:{s:6:"imgurl";s:67:"https://four.nttrip.cn/template_img/template_shop/page/picture8.png";s:7:"linkurl";s:0:"";s:8:"linktype";s:4:"page";}}s:2:"id";s:7:"picture";s:5:"index";s:3:"NaN";}}';
            $item4 = unserialize($item4);
            foreach($item4 as &$vi){
                if(isset($vi['data'])){
                    foreach($vi['data'] as &$vvi){
                        if(isset($vvi['imgurl']) && strpos($vvi['imgurl'],'https://four.nttrip.cn/') !== false){
                            $vvi['imgurl'] = ROOT_HOST."/diypage/".explode('https://four.nttrip.cn/', $vvi['imgurl'])[1];
                        }
                    }
                }
            }
            $page4['items'] = serialize($item4);
            $page4['page'] = 'a:7:{s:10:"background";s:7:"#f1f1f1";s:13:"topbackground";s:7:"#ff3420";s:8:"topcolor";s:1:"2";s:9:"styledata";s:1:"0";s:5:"title";s:12:"优惠活动";s:4:"name";s:12:"优惠活动";s:10:"visitlevel";a:2:{s:6:"member";s:0:"";s:10:"commission";s:0:"";}}';
            $page4['tpl_name'] = "优惠活动";

            $page5 = [];
            $page5['uniacid'] = $uniacid;
            $page5['index'] = 0;
            $item5 = 'a:3:{s:14:"M1544150247611";a:5:{s:4:"icon";s:19:"iconfont2 icon-zutu";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:6:{s:10:"paddingtop";s:1:"0";s:11:"paddingleft";s:1:"0";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"background";s:7:"#ffffff";}s:4:"data";a:1:{s:14:"C1544150247611";a:2:{s:6:"imgurl";s:67:"https://four.nttrip.cn/template_img/template_shop/page/picture4.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:7:"picture";}s:14:"M1544150260723";a:5:{s:4:"icon";s:22:"iconfont2 icon-chanpin";s:6:"params";a:31:{s:11:"goodsscroll";s:1:"0";s:9:"showtitle";s:1:"1";s:9:"showprice";s:1:"1";s:7:"showtag";s:1:"0";s:9:"goodsdata";s:1:"1";s:6:"cateid";s:0:"";s:8:"catename";s:0:"";s:7:"groupid";s:0:"";s:9:"groupname";s:0:"";s:9:"goodssort";s:1:"0";s:8:"goodsnum";s:1:"6";s:8:"showicon";s:1:"0";s:12:"iconposition";s:8:"left top";s:12:"productprice";s:1:"1";s:16:"showproductprice";s:1:"0";s:9:"showsales";s:1:"1";s:16:"productpricetext";s:6:"原价";s:9:"salestext";s:6:"销量";s:16:"productpriceline";s:1:"0";s:7:"saleout";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"imgh_is";s:1:"1";s:4:"imgh";s:3:"100";s:7:"con_key";s:1:"1";s:8:"con_type";s:1:"1";s:8:"sourceid";s:0:"";}s:5:"style";a:20:{s:10:"background";s:7:"#f3f3f3";s:9:"liststyle";s:5:"block";s:8:"buystyle";s:0:"";s:9:"goodsicon";s:7:"bigsale";s:9:"iconstyle";s:8:"triangle";s:10:"pricecolor";s:7:"#ff5555";s:17:"productpricecolor";s:7:"#999999";s:14:"iconpaddingtop";s:1:"0";s:15:"iconpaddingleft";s:1:"0";s:11:"buybtncolor";s:7:"#ff5555";s:8:"iconzoom";s:2:"50";s:10:"titlecolor";s:7:"#000000";s:13:"tagbackground";s:7:"#fe5455";s:10:"salescolor";s:7:"#999999";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"paddingtop";s:1:"4";s:11:"paddingleft";s:1:"4";s:8:"showtype";s:1:"0";}s:4:"data";a:4:{s:14:"C1544150260723";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:3:"des";s:21:"这里是产品描述";}s:14:"C1544150260724";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1544150260725";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1544150260726";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}}s:2:"id";s:5:"goods";}s:14:"M1544152626046";a:5:{s:4:"icon";s:22:"iconfont2 icon-chanpin";s:6:"params";a:31:{s:11:"goodsscroll";s:1:"0";s:9:"showtitle";s:1:"1";s:9:"showprice";s:1:"1";s:7:"showtag";s:1:"0";s:9:"goodsdata";s:1:"1";s:6:"cateid";s:0:"";s:8:"catename";s:0:"";s:7:"groupid";s:0:"";s:9:"groupname";s:0:"";s:9:"goodssort";s:1:"0";s:8:"goodsnum";s:1:"6";s:8:"showicon";s:1:"0";s:12:"iconposition";s:8:"left top";s:12:"productprice";s:1:"1";s:16:"showproductprice";s:1:"0";s:9:"showsales";s:1:"1";s:16:"productpricetext";s:6:"原价";s:9:"salestext";s:6:"销量";s:16:"productpriceline";s:1:"0";s:7:"saleout";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"imgh_is";s:1:"1";s:4:"imgh";s:3:"100";s:7:"con_key";s:1:"1";s:8:"con_type";s:1:"1";s:8:"sourceid";s:0:"";}s:5:"style";a:20:{s:10:"background";s:7:"#f3f3f3";s:9:"liststyle";s:5:"block";s:8:"buystyle";s:0:"";s:9:"goodsicon";s:9:"recommand";s:9:"iconstyle";s:8:"triangle";s:10:"pricecolor";s:7:"#ff5555";s:17:"productpricecolor";s:7:"#999999";s:14:"iconpaddingtop";s:1:"0";s:15:"iconpaddingleft";s:1:"0";s:11:"buybtncolor";s:7:"#ff5555";s:8:"iconzoom";s:2:"50";s:10:"titlecolor";s:7:"#000000";s:13:"tagbackground";s:7:"#fe5455";s:10:"salescolor";s:7:"#999999";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"paddingtop";s:1:"0";s:11:"paddingleft";s:1:"4";s:8:"showtype";s:1:"0";}s:4:"data";a:4:{s:14:"C1544152626046";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:3:"des";s:21:"这里是产品描述";}s:14:"C1544152626047";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1544152626048";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1544152626049";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}}s:2:"id";s:5:"goods";}}';
            $item5 = unserialize($item5);
            foreach($item5 as &$vi){
                if(isset($vi['data'])){
                    foreach($vi['data'] as &$vvi){
                        if(isset($vvi['imgurl']) && strpos($vvi['imgurl'],'https://four.nttrip.cn/') !== false){
                            $vvi['imgurl'] = ROOT_HOST."/diypage/".explode('https://four.nttrip.cn/', $vvi['imgurl'])[1];
                        }
                        if(isset($vvi['thumb']) && strpos($vvi['thumb'],'/diypage/resource/images/diypage/default/2.jpg') !== false){
                            $vvi['thumb'] = ROOT_HOST."/diypage/resource/images/diypage/default/2.jpg";
                        }
                    }
                }
            }
            $page5['items'] = serialize($item5);
            $page5['page'] = 'a:7:{s:10:"background";s:7:"#f1f1f1";s:13:"topbackground";s:7:"#ff3420";s:8:"topcolor";s:1:"2";s:9:"styledata";s:1:"0";s:5:"title";s:12:"新品预约";s:4:"name";s:12:"新品预约";s:10:"visitlevel";a:2:{s:6:"member";s:0:"";s:10:"commission";s:0:"";}}';
            $page5['tpl_name'] = "新品预约";

            $page6 = [];
            $page6['uniacid'] = $uniacid;
            $page6['index'] = 0;
            $item6 = 'a:2:{s:14:"M1544150311503";a:5:{s:4:"icon";s:19:"iconfont2 icon-zutu";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:6:{s:10:"paddingtop";s:1:"0";s:11:"paddingleft";s:1:"0";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"background";s:7:"#ffffff";}s:4:"data";a:1:{s:14:"C1544150311503";a:2:{s:6:"imgurl";s:67:"https://four.nttrip.cn/template_img/template_shop/page/picture3.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:7:"picture";}s:14:"M1544150324081";a:6:{s:3:"max";s:1:"1";s:4:"icon";s:23:"iconfont2 icon-shandian";s:6:"params";a:12:{s:8:"navstyle";s:1:"2";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:9:"goodsdata";s:1:"1";s:8:"goodsnum";s:1:"4";s:7:"con_key";s:1:"1";s:8:"con_type";s:1:"1";s:8:"sourceid";s:0:"";}s:5:"style";a:9:{s:10:"background";s:7:"#ffffff";s:3:"pdw";s:1:"6";s:3:"pdh";s:2:"10";s:2:"mb";s:2:"10";s:2:"mt";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:4:"pich";s:1:"1";s:8:"showtype";s:1:"0";}s:4:"data";a:3:{s:14:"C1544150324081";a:14:{s:6:"imgurl";s:47:"/diypage/resource/images/diypage/default/11.jpg";s:5:"title";s:18:"秒杀商品标题";s:11:"description";s:18:"秒杀商品简介";s:5:"count";s:3:"927";s:5:"price";s:2:"21";s:4:"hour";s:2:"10";s:3:"min";s:2:"11";s:6:"second";s:2:"12";s:6:"person";s:1:"2";s:2:"tz";s:2:"92";s:3:"tgr";s:3:"427";s:3:"tgj";s:4:"17.2";s:7:"linkurl";s:0:"";s:8:"linktype";s:0:"";}s:14:"C1544150324082";a:14:{s:6:"imgurl";s:47:"/diypage/resource/images/diypage/default/11.jpg";s:5:"title";s:18:"秒杀商品标题";s:11:"description";s:18:"秒杀商品简介";s:5:"count";s:3:"927";s:5:"price";s:2:"21";s:4:"hour";s:2:"10";s:3:"min";s:2:"11";s:6:"second";s:2:"12";s:6:"person";s:1:"2";s:2:"tz";s:2:"92";s:3:"tgr";s:3:"427";s:3:"tgj";s:4:"17.2";s:7:"linkurl";s:0:"";s:8:"linktype";s:0:"";}s:14:"C1544150324083";a:14:{s:6:"imgurl";s:47:"/diypage/resource/images/diypage/default/11.jpg";s:5:"title";s:18:"秒杀商品标题";s:11:"description";s:18:"秒杀商品简介";s:5:"count";s:3:"927";s:5:"price";s:2:"21";s:4:"hour";s:2:"10";s:3:"min";s:2:"11";s:6:"second";s:2:"12";s:6:"person";s:1:"2";s:2:"tz";s:2:"92";s:3:"tgr";s:3:"427";s:3:"tgj";s:4:"17.2";s:7:"linkurl";s:0:"";s:8:"linktype";s:0:"";}}s:2:"id";s:4:"msmk";}}';
            $item6 = unserialize($item6);
            foreach($item6 as &$vi){
                if(isset($vi['data'])){
                    foreach($vi['data'] as &$vvi){
                        if(isset($vvi['imgurl']) && strpos($vvi['imgurl'],'https://four.nttrip.cn/') !== false){
                            $vvi['imgurl'] = ROOT_HOST."/diypage/".explode('https://four.nttrip.cn/', $vvi['imgurl'])[1];
                        }
                        if(isset($vvi['imgurl']) && strpos($vvi['imgurl'],'/diypage/resource/images/diypage/default/11.jpg') !== false){
                            $vvi['imgurl'] = ROOT_HOST."/diypage/resource/images/diypage/default/11.jpg";
                        }
                    }
                }
            }
            $page6['items'] = serialize($item6);
            $page6['page'] = 'a:7:{s:10:"background";s:7:"#f1f1f1";s:13:"topbackground";s:7:"#ff3420";s:8:"topcolor";s:1:"2";s:9:"styledata";s:1:"0";s:5:"title";s:12:"特惠秒杀";s:4:"name";s:12:"特惠秒杀";s:10:"visitlevel";a:2:{s:6:"member";s:0:"";s:10:"commission";s:0:"";}}';
            $page6['tpl_name'] = "特惠秒杀";

            $page1_id = Db::name('wd_xcx_diypage')->insertGetId($page1);

            $page2_id = Db::name('wd_xcx_diypage')->insertGetId($page2);
            $page3_id = Db::name('wd_xcx_diypage')->insertGetId($page3);
            $page4_id = Db::name('wd_xcx_diypage')->insertGetId($page4);
            $page5_id = Db::name('wd_xcx_diypage')->insertGetId($page5);
            $page6_id = Db::name('wd_xcx_diypage')->insertGetId($page6);
            $pageids = $page1_id.",".$page2_id.",".$page3_id.",".$page4_id.",".$page5_id.",".$page6_id;


            $is = Db::name('wd_xcx_diypageset')->where('uniacid',$uniacid)->find();
            if(empty($is)){
                $page_set = [
                    'uniacid' => $uniacid,
                    'kp' => ROOT_HOST."/diypage/resource/images/diypage/default/default_start.jpg",
                    'kp_is' => 2,
                    'kp_m' => 2,
                    'tc' => ROOT_HOST."/diypage/resource/images/diypage/default/tcgg.jpg",
                    'tc_is' => 2,
                    'foot_is' => 2
                ];
                Db::name('wd_xcx_diypageset')->insert($page_set);
            }else{
                $res = Db::name('wd_xcx_diypageset')->where('uniacid',$uniacid)->update(array('foot_is' => 2));
            }
            $data = [
                'uniacid' => $uniacid,
                'pageid' => $pageids,
                'template_name' => '综合商城模板01',
                'thumb' => "/diypage/template_img/template_shop/cover.png",
                'status' => '1',
                'create_time' => time()
            ];
            $tplid = Db::name('wd_xcx_diypagetpl')->insertGetId($data);
            if($tplid){
                return $tplid;
            }
        }elseif($id == 'm_shop02'){
            $page1 = [];
            $page1['uniacid'] = $uniacid;
            $page1['index'] = 1;
            $item1 = 'a:14:{s:14:"M1564126665648";a:4:{s:4:"icon";s:22:"iconfont2 icon-sousuo1";s:6:"params";a:7:{s:5:"value";s:0:"";s:9:"styledata";s:1:"1";s:6:"repeat";s:9:"no-repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"1";s:13:"backgroundimg";s:76:"https://four.nttrip.cn/diypage/template_img/template_shop02/index/ssk_bg.png";}s:5:"style";a:12:{s:9:"textalign";s:4:"left";s:10:"background";s:7:"#f1f1f1";s:2:"bg";s:4:"#fff";s:12:"borderradius";s:2:"20";s:6:"boxpdh";s:2:"20";s:6:"boxpdz";s:2:"20";s:7:"padding";s:1:"5";s:8:"fontsize";s:2:"13";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"color";s:0:"";}s:2:"id";s:3:"ssk";}s:14:"M1564129560208";a:6:{s:4:"icon";s:28:"iconfont2 icon-tuoyuankaobei";s:6:"params";a:10:{s:5:"totle";s:1:"2";s:8:"navstyle";s:1:"0";s:9:"styledata";s:1:"1";s:6:"repeat";s:9:"no-repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"1";s:13:"backgroundimg";s:79:"https://four.nttrip.cn/diypage/template_img/template_shop02/index/banner_bg.png";s:9:"navstyle2";s:1:"0";s:4:"imgh";s:3:"160";}s:5:"style";a:18:{s:8:"dotstyle";s:5:"round";s:8:"dotalign";s:4:"left";s:10:"paddingtop";s:1:"0";s:11:"paddingleft";s:2:"20";s:10:"background";s:7:"#ffffff";s:13:"backgroundall";s:7:"#ffffff";s:9:"leftright";s:1:"5";s:6:"bottom";s:1:"5";s:7:"opacity";s:3:"0.8";s:10:"text_color";s:4:"#fff";s:2:"bg";s:7:"#000000";s:9:"jsq_color";s:3:"red";s:3:"pdh";s:1:"0";s:3:"pdw";s:1:"0";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"speed";s:1:"5";}s:4:"data";a:1:{s:14:"C1564129560208";a:4:{s:6:"imgurl";s:76:"https://four.nttrip.cn/diypage/template_img/template_shop02/index/banner.png";s:7:"linkurl";s:0:"";s:6:"single";s:1:"1";s:4:"text";s:12:"文字描述";}}s:2:"id";s:6:"banner";s:5:"index";s:3:"NaN";}s:14:"M1564129685438";a:5:{s:4:"icon";s:19:"iconfont2 icon-fuwu";s:6:"params";a:10:{s:8:"hidetext";s:1:"0";s:8:"showtype";s:1:"0";s:6:"rownum";s:1:"3";s:7:"showbtn";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:12:{s:10:"background";s:7:"#ffffff";s:10:"paddingtop";s:1:"5";s:11:"paddingleft";s:2:"10";s:6:"iconfz";s:2:"18";s:7:"tbcolor";s:7:"#e58960";s:5:"color";s:0:"";s:4:"tbbg";s:0:"";s:3:"pdl";s:1:"5";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:8:"fontsize";s:2:"14";}s:4:"data";a:3:{s:14:"C1564129685438";a:2:{s:9:"iconclass";s:11:"icon-x-dui1";s:4:"text";s:12:"品质严选";}s:14:"C1564129685439";a:2:{s:9:"iconclass";s:11:"icon-x-dui1";s:4:"text";s:12:"闪电发货";}s:14:"M1564129702648";a:2:{s:9:"iconclass";s:11:"icon-x-dui1";s:4:"text";s:12:"无忧退货";}}s:2:"id";s:4:"dnfw";}s:14:"M1564129897404";a:5:{s:4:"icon";s:22:"iconfont2 icon-anniuzu";s:6:"params";a:8:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"picicon";s:1:"1";s:8:"textshow";s:1:"1";}s:5:"style";a:14:{s:8:"navstyle";s:0:"";s:10:"background";s:7:"#ffffff";s:6:"rownum";s:1:"5";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"8";s:7:"showdot";s:1:"1";s:7:"padding";s:1:"0";s:11:"paddingleft";s:2:"10";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:6:"iconfz";s:2:"14";s:9:"iconcolor";s:7:"#434343";s:8:"imgwidth";s:2:"45";}s:4:"data";a:5:{s:14:"C1564129897404";a:5:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_shop02/index/menu1.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"超市便利";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1564129897405";a:5:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_shop02/index/menu2.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"新鲜蔬果";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1564129897406";a:5:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_shop02/index/menu3.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"母婴百货";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1564129897407";a:5:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_shop02/index/menu4.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"鲜花绿植";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"M1564130493247";a:5:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_shop02/index/menu5.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"健康医药";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}}s:2:"id";s:4:"menu";}s:14:"M1564130628198";a:6:{s:4:"icon";s:19:"iconfont2 icon-zutu";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:6:{s:10:"paddingtop";s:1:"0";s:11:"paddingleft";s:2:"15";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"background";s:7:"#ffffff";}s:4:"data";a:1:{s:14:"C1564130628199";a:2:{s:6:"imgurl";s:77:"https://four.nttrip.cn/diypage/template_img/template_shop02/index/picture.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:7:"picture";s:5:"index";s:3:"NaN";}s:14:"M1564131240005";a:4:{s:4:"icon";s:24:"iconfont2 icon-xiankuang";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:5:{s:6:"height";s:1:"1";s:10:"background";s:7:"#ffffff";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";}s:2:"id";s:5:"blank";}s:14:"M1564131161925";a:5:{s:4:"icon";s:32:"iconfont2 icon-liebiao_biaotilan";s:6:"params";a:9:{s:5:"title";s:12:"超市必买";s:6:"title2";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:5:"style";s:1:"1";}s:5:"style";a:11:{s:10:"background";s:7:"#ffffff";s:6:"colorz";s:7:"#434343";s:6:"colorf";s:7:"#838383";s:9:"linecolor";s:7:"#ff4558";s:9:"fontsizez";s:2:"18";s:9:"fontsizef";s:2:"12";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:1:"0";}s:2:"id";s:6:"title2";s:5:"index";s:3:"NaN";}s:14:"M1564131291021";a:5:{s:4:"icon";s:21:"iconfont2 icon-tupian";s:6:"params";a:9:{s:3:"row";s:1:"3";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"2";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:8:{s:10:"paddingtop";s:1:"5";s:11:"paddingleft";s:2:"10";s:7:"showdot";s:1:"0";s:7:"showbtn";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:1:"0";s:10:"background";s:7:"#ffffff";}s:4:"data";a:3:{s:14:"C1564131291021";a:2:{s:6:"imgurl";s:81:"https://four.nttrip.cn/diypage/template_img/template_shop02/index/picturew1_1.png";s:7:"linkurl";s:0:"";}s:14:"C1564131291022";a:2:{s:6:"imgurl";s:81:"https://four.nttrip.cn/diypage/template_img/template_shop02/index/picturew1_2.png";s:7:"linkurl";s:0:"";}s:14:"C1564131291023";a:2:{s:6:"imgurl";s:81:"https://four.nttrip.cn/diypage/template_img/template_shop02/index/picturew1_3.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:8:"picturew";}s:14:"M1564131482655";a:5:{s:4:"icon";s:24:"iconfont2 icon-xiankuang";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:5:{s:6:"height";s:1:"1";s:10:"background";s:7:"#ffffff";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";}s:2:"id";s:5:"blank";s:5:"index";s:3:"NaN";}s:14:"M1564131474216";a:5:{s:4:"icon";s:32:"iconfont2 icon-liebiao_biaotilan";s:6:"params";a:9:{s:5:"title";s:12:"精选店铺";s:6:"title2";s:24:"好的生活没那么贵";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:5:"style";s:1:"3";}s:5:"style";a:11:{s:10:"background";s:7:"#ffffff";s:6:"colorz";s:7:"#000000";s:6:"colorf";s:7:"#838383";s:9:"linecolor";s:7:"#ff4558";s:9:"fontsizez";s:2:"16";s:9:"fontsizef";s:2:"14";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:1:"0";}s:2:"id";s:6:"title2";s:5:"index";s:3:"NaN";}s:14:"M1564131559485";a:6:{s:4:"icon";s:21:"iconfont2 icon-tupian";s:6:"params";a:9:{s:3:"row";s:1:"2";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"2";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:8:{s:10:"paddingtop";s:1:"5";s:11:"paddingleft";s:2:"10";s:7:"showdot";s:1:"0";s:7:"showbtn";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:1:"0";s:10:"background";s:7:"#ffffff";}s:4:"data";a:4:{s:14:"C1564131559485";a:2:{s:6:"imgurl";s:81:"https://four.nttrip.cn/diypage/template_img/template_shop02/index/picturew2_1.png";s:7:"linkurl";s:0:"";}s:14:"C1564131559486";a:2:{s:6:"imgurl";s:81:"https://four.nttrip.cn/diypage/template_img/template_shop02/index/picturew2_2.png";s:7:"linkurl";s:0:"";}s:14:"C1564131559487";a:2:{s:6:"imgurl";s:81:"https://four.nttrip.cn/diypage/template_img/template_shop02/index/picturew2_3.png";s:7:"linkurl";s:0:"";}s:14:"C1564131559488";a:2:{s:6:"imgurl";s:81:"https://four.nttrip.cn/diypage/template_img/template_shop02/index/picturew2_4.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:8:"picturew";s:5:"index";s:3:"NaN";}s:14:"M1564131614319";a:5:{s:4:"icon";s:24:"iconfont2 icon-xiankuang";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:5:{s:6:"height";s:1:"1";s:10:"background";s:7:"#ffffff";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";}s:2:"id";s:5:"blank";s:5:"index";s:3:"NaN";}s:14:"M1564131620413";a:5:{s:4:"icon";s:32:"iconfont2 icon-liebiao_biaotilan";s:6:"params";a:9:{s:5:"title";s:12:"口碑商家";s:6:"title2";s:24:"诚信经营值得信赖";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:5:"style";s:1:"3";}s:5:"style";a:11:{s:10:"background";s:7:"#ffffff";s:6:"colorz";s:7:"#000000";s:6:"colorf";s:7:"#838383";s:9:"linecolor";s:7:"#ff4558";s:9:"fontsizez";s:2:"16";s:9:"fontsizef";s:2:"14";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:1:"0";}s:2:"id";s:6:"title2";s:5:"index";s:3:"NaN";}s:14:"M1564131676821";a:5:{s:4:"icon";s:22:"iconfont2 icon-anniuzu";s:6:"params";a:9:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"picicon";s:1:"1";s:6:"counts";s:1:"4";s:12:"content_type";s:1:"1";}s:5:"style";a:14:{s:8:"navstyle";s:0:"";s:10:"background";s:7:"#ffffff";s:6:"rownum";s:1:"4";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"8";s:7:"showdot";s:1:"1";s:7:"padding";s:1:"0";s:11:"paddingleft";s:2:"10";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:6:"iconfz";s:2:"14";s:9:"iconcolor";s:7:"#434343";s:8:"imgwidth";s:2:"50";}s:4:"data";a:4:{s:14:"C1564131676821";a:5:{s:6:"imgurl";s:51:"/diypage/resource/images/diypage/default/icon-1.png";s:7:"linkurl";s:0:"";s:4:"text";s:10:"多商户1";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1564131676822";a:5:{s:6:"imgurl";s:51:"/diypage/resource/images/diypage/default/icon-2.png";s:7:"linkurl";s:0:"";s:4:"text";s:10:"多商户2";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1564131676823";a:5:{s:6:"imgurl";s:51:"/diypage/resource/images/diypage/default/icon-3.png";s:7:"linkurl";s:0:"";s:4:"text";s:10:"多商户3";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1564131676824";a:5:{s:6:"imgurl";s:51:"/diypage/resource/images/diypage/default/icon-4.png";s:7:"linkurl";s:0:"";s:4:"text";s:10:"多商户4";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}}s:2:"id";s:8:"multiple";}}';
            $item1 = unserialize($item1);
            foreach($item1 as $k => &$v){
                if(isset($v['data'])){
                    foreach($v['data'] as $kk => &$vv){
                        if(isset($vv['imgurl']) && strpos($vv['imgurl'],'https://four.nttrip.cn') !== false){
                            $vv['imgurl'] = ROOT_HOST.explode('https://four.nttrip.cn', $vv['imgurl'])[1];
                        }
                        if(isset($vv['thumb']) && strpos($vv['thumb'],'/diypage/resource/images/diypage/default/icon-1.png') !== false){
                            $vv['thumb'] = ROOT_HOST."/diypage/resource/images/diypage/default/icon-1.png";
                        }
                        if(isset($vv['thumb']) && strpos($vv['thumb'],'/diypage/resource/images/diypage/default/icon-2.png') !== false){
                            $vv['thumb'] = ROOT_HOST."/diypage/resource/images/diypage/default/icon-2.png";
                        }
                        if(isset($vv['thumb']) && strpos($vv['thumb'],'/diypage/resource/images/diypage/default/icon-3.png') !== false){
                            $vv['thumb'] = ROOT_HOST."/diypage/resource/images/diypage/default/icon-3.png";
                        }
                        if(isset($vv['thumb']) && strpos($vv['thumb'],'/diypage/resource/images/diypage/default/icon-4.png') !== false){
                            $vv['thumb'] = ROOT_HOST."/diypage/resource/images/diypage/default/icon-4.png";
                        }
                    }
                }
            }
            $page1['items'] = serialize($item1);
            $page1['page'] = 'a:7:{s:10:"background";s:7:"#f1f1f1";s:13:"topbackground";s:7:"#ffffff";s:8:"topcolor";s:1:"1";s:9:"styledata";s:1:"0";s:5:"title";s:6:"首页";s:4:"name";s:6:"首页";s:10:"visitlevel";a:2:{s:6:"member";s:0:"";s:10:"commission";s:0:"";}}';
            $page1['tpl_name'] = "首页";

            $page1_id = Db::name('wd_xcx_diypage')->insertGetId($page1);
            $pageids = $page1_id;

            $is = Db::name('wd_xcx_diypageset')->where('uniacid',$uniacid)->find();
            if(empty($is)){
                $page_set = [
                    'uniacid' => $uniacid,
                    'kp' => ROOT_HOST."/diypage/resource/images/diypage/default/default_start.jpg",
                    'kp_is' => 2,
                    'kp_m' => 2,
                    'tc' => ROOT_HOST."/diypage/resource/images/diypage/default/tcgg.jpg",
                    'tc_is' => 2,
                    'foot_is' => 2
                ];
                Db::name('wd_xcx_diypageset')->insert($page_set);
            }else{
                Db::name('wd_xcx_diypageset')->where('uniacid',$uniacid)->update(array('foot_is' => 2));
            }
            $data = [
                'uniacid' => $uniacid,
                'pageid' => $pageids,
                'template_name' => '综合商城类模板02',
                'thumb' => "/diypage/template_img/template_shop02/cover.png",
                'status' => '1',
                'create_time' => time()
            ];
            $tplid = Db::name('wd_xcx_diypagetpl')->insertGetId($data);
            if($tplid){
                return $tplid;
            }
        }elseif($id == 'm_shop03'){
            $page1 = [];
            $page1['uniacid'] = $uniacid;
            $page1['index'] = 1;
            $item1 = 'a:7:{s:14:"M1564132394598";a:4:{s:4:"icon";s:22:"iconfont2 icon-sousuo1";s:6:"params";a:7:{s:5:"value";s:0:"";s:9:"styledata";s:1:"1";s:6:"repeat";s:9:"no-repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"1";s:13:"backgroundimg";s:76:"https://four.nttrip.cn/diypage/template_img/template_shop03/index/ssk_bg.png";}s:5:"style";a:12:{s:9:"textalign";s:4:"left";s:10:"background";s:7:"#f1f1f1";s:2:"bg";s:7:"#f1f1f1";s:12:"borderradius";s:2:"20";s:6:"boxpdh";s:2:"20";s:6:"boxpdz";s:2:"20";s:7:"padding";s:1:"3";s:8:"fontsize";s:2:"13";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"color";s:0:"";}s:2:"id";s:3:"ssk";}s:14:"M1564132397780";a:5:{s:4:"icon";s:28:"iconfont2 icon-tuoyuankaobei";s:6:"params";a:10:{s:5:"totle";s:1:"2";s:8:"navstyle";s:1:"0";s:9:"styledata";s:1:"1";s:6:"repeat";s:9:"no-repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"1";s:13:"backgroundimg";s:79:"https://four.nttrip.cn/diypage/template_img/template_shop03/index/banner_bg.png";s:9:"navstyle2";s:1:"0";s:4:"imgh";s:3:"130";}s:5:"style";a:18:{s:8:"dotstyle";s:5:"round";s:8:"dotalign";s:4:"left";s:10:"paddingtop";s:1:"0";s:11:"paddingleft";s:2:"20";s:10:"background";s:7:"#ffffff";s:13:"backgroundall";s:7:"#ffffff";s:9:"leftright";s:1:"5";s:6:"bottom";s:1:"5";s:7:"opacity";s:3:"0.8";s:10:"text_color";s:4:"#fff";s:2:"bg";s:7:"#000000";s:9:"jsq_color";s:3:"red";s:3:"pdh";s:1:"0";s:3:"pdw";s:1:"0";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"speed";s:1:"5";}s:4:"data";a:1:{s:14:"C1564132397780";a:4:{s:6:"imgurl";s:76:"https://four.nttrip.cn/diypage/template_img/template_shop03/index/banner.png";s:7:"linkurl";s:0:"";s:6:"single";s:1:"1";s:4:"text";s:12:"文字描述";}}s:2:"id";s:6:"banner";}s:14:"M1564133065992";a:5:{s:4:"icon";s:22:"iconfont2 icon-anniuzu";s:6:"params";a:8:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"picicon";s:1:"1";s:8:"textshow";s:1:"1";}s:5:"style";a:14:{s:8:"navstyle";s:0:"";s:10:"background";s:7:"#ffffff";s:6:"rownum";s:1:"4";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"8";s:7:"showdot";s:1:"1";s:7:"padding";s:2:"10";s:11:"paddingleft";s:2:"10";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:6:"iconfz";s:2:"14";s:9:"iconcolor";s:7:"#434343";s:8:"imgwidth";s:2:"54";}s:4:"data";a:4:{s:14:"C1564133065992";a:5:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_shop03/index/menu1.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"每日优鲜";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1564133065993";a:5:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_shop03/index/menu2.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"果蔬超市";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1564133065994";a:5:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_shop03/index/menu3.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"生鲜超市";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1564133065995";a:5:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_shop03/index/menu4.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"乳品粮油";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}}s:2:"id";s:4:"menu";}s:14:"M1564133317525";a:5:{s:4:"icon";s:22:"iconfont2 icon-gonggao";s:6:"params";a:12:{s:7:"iconurl";s:15:"icon-x-gonggao3";s:10:"noticedata";s:1:"0";s:5:"speed";s:1:"4";s:9:"noticenum";s:1:"5";s:8:"navstyle";s:1:"1";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:8:"sourceid";s:0:"";}s:5:"style";a:9:{s:10:"background";s:7:"#ffffff";s:9:"iconcolor";s:7:"#fd5454";s:5:"color";s:7:"#666666";s:11:"bordercolor";s:7:"#e2e2e2";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";}s:4:"data";a:2:{s:14:"C1564133317525";a:2:{s:5:"title";s:42:"这里是第一条自定义公告的标题";s:7:"linkurl";s:0:"";}s:14:"C1564133317526";a:2:{s:5:"title";s:42:"这里是第二条自定义公告的标题";s:7:"linkurl";s:0:"";}}s:2:"id";s:6:"notice";}s:14:"M1564133546303";a:5:{s:4:"icon";s:21:"iconfont2 icon-tupian";s:6:"params";a:9:{s:3:"row";s:1:"2";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"2";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:8:{s:10:"paddingtop";s:1:"8";s:11:"paddingleft";s:1:"8";s:7:"showdot";s:1:"0";s:7:"showbtn";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:1:"0";s:10:"background";s:7:"#ffffff";}s:4:"data";a:2:{s:14:"C1564133546303";a:2:{s:6:"imgurl";s:79:"https://four.nttrip.cn/diypage/template_img/template_shop03/index/picturew1.png";s:7:"linkurl";s:0:"";}s:14:"C1564133546304";a:2:{s:6:"imgurl";s:79:"https://four.nttrip.cn/diypage/template_img/template_shop03/index/picturew2.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:8:"picturew";}s:14:"M1564133604182";a:6:{s:4:"icon";s:19:"iconfont2 icon-zutu";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:6:{s:10:"paddingtop";s:1:"5";s:11:"paddingleft";s:2:"10";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"background";s:7:"#ffffff";}s:4:"data";a:1:{s:14:"C1564133604182";a:2:{s:6:"imgurl";s:78:"https://four.nttrip.cn/diypage/template_img/template_shop03/index/picture1.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:7:"picture";s:5:"index";s:3:"NaN";}s:14:"M1564133659599";a:6:{s:4:"icon";s:19:"iconfont2 icon-zutu";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:6:{s:10:"paddingtop";s:2:"20";s:11:"paddingleft";s:2:"10";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"background";s:7:"#ffffff";}s:4:"data";a:2:{s:14:"C1564133659599";a:2:{s:6:"imgurl";s:80:"https://four.nttrip.cn/diypage/template_img/template_shop03/index/picture2_1.png";s:7:"linkurl";s:0:"";}s:14:"M1564133673921";a:2:{s:6:"imgurl";s:80:"https://four.nttrip.cn/diypage/template_img/template_shop03/index/picture2_2.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:7:"picture";s:5:"index";s:3:"NaN";}}';
            $item1 = unserialize($item1);
            foreach($item1 as $k => &$v){
                if(isset($v['data'])){
                    foreach($v['data'] as $kk => &$vv){
                        if(isset($vv['imgurl']) && strpos($vv['imgurl'],'https://four.nttrip.cn') !== false){
                            $vv['imgurl'] = ROOT_HOST.explode('https://four.nttrip.cn', $vv['imgurl'])[1];
                        }
                    }
                }
            }
            $page1['items'] = serialize($item1);
            $page1['page'] = 'a:7:{s:10:"background";s:7:"#f1f1f1";s:13:"topbackground";s:7:"#ffffff";s:8:"topcolor";s:1:"1";s:9:"styledata";s:1:"0";s:5:"title";s:6:"首页";s:4:"name";s:6:"首页";s:10:"visitlevel";a:2:{s:6:"member";s:0:"";s:10:"commission";s:0:"";}}';
            $page1['tpl_name'] = "首页";

            $page1_id = Db::name('wd_xcx_diypage')->insertGetId($page1);
            $pageids = $page1_id;

            $is = Db::name('wd_xcx_diypageset')->where('uniacid',$uniacid)->find();
            if(empty($is)){
                $page_set = [
                    'uniacid' => $uniacid,
                    'kp' => ROOT_HOST."/diypage/resource/images/diypage/default/default_start.jpg",
                    'kp_is' => 2,
                    'kp_m' => 2,
                    'tc' => ROOT_HOST."/diypage/resource/images/diypage/default/tcgg.jpg",
                    'tc_is' => 2,
                    'foot_is' => 2
                ];
                Db::name('wd_xcx_diypageset')->insert($page_set);
            }else{
                Db::name('wd_xcx_diypageset')->where('uniacid',$uniacid)->update(array('foot_is' => 2));
            }
            $data = [
                'uniacid' => $uniacid,
                'pageid' => $pageids,
                'template_name' => '综合商城类模板03',
                'thumb' => "/diypage/template_img/template_shop03/cover.png",
                'status' => '1',
                'create_time' => time()
            ];
            $tplid = Db::name('wd_xcx_diypagetpl')->insertGetId($data);
            if($tplid){
                return $tplid;
            }
        }elseif($id == 'm_shop04'){
            $page1 = [];
            $page1['uniacid'] = $uniacid;
            $page1['index'] = 1;
            $item1 = 'a:9:{s:14:"M1564210593589";a:4:{s:4:"icon";s:22:"iconfont2 icon-sousuo1";s:6:"params";a:7:{s:5:"value";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:12:{s:9:"textalign";s:4:"left";s:10:"background";s:7:"#5bd358";s:2:"bg";s:7:"#f5f5f5";s:12:"borderradius";s:2:"20";s:6:"boxpdh";s:2:"10";s:6:"boxpdz";s:2:"15";s:7:"padding";s:1:"5";s:8:"fontsize";s:2:"13";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"color";s:7:"#999999";}s:2:"id";s:3:"ssk";}s:14:"M1564210596157";a:5:{s:4:"icon";s:28:"iconfont2 icon-tuoyuankaobei";s:6:"params";a:10:{s:5:"totle";s:1:"2";s:8:"navstyle";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:9:"navstyle2";s:1:"0";s:4:"imgh";s:3:"160";}s:5:"style";a:18:{s:8:"dotstyle";s:5:"round";s:8:"dotalign";s:4:"left";s:10:"paddingtop";s:1:"0";s:11:"paddingleft";s:1:"0";s:10:"background";s:7:"#ffffff";s:13:"backgroundall";s:7:"#ffffff";s:9:"leftright";s:1:"5";s:6:"bottom";s:1:"5";s:7:"opacity";s:3:"0.8";s:10:"text_color";s:4:"#fff";s:2:"bg";s:7:"#000000";s:9:"jsq_color";s:3:"red";s:3:"pdh";s:1:"0";s:3:"pdw";s:1:"0";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"speed";s:1:"5";}s:4:"data";a:1:{s:14:"C1564210596157";a:4:{s:6:"imgurl";s:76:"https://four.nttrip.cn/diypage/template_img/template_shop04/index/banner.jpg";s:7:"linkurl";s:0:"";s:6:"single";s:1:"1";s:4:"text";s:12:"文字描述";}}s:2:"id";s:6:"banner";}s:14:"M1564210832572";a:5:{s:4:"icon";s:22:"iconfont2 icon-anniuzu";s:6:"params";a:8:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"picicon";s:1:"1";s:8:"textshow";s:1:"1";}s:5:"style";a:14:{s:8:"navstyle";s:0:"";s:10:"background";s:7:"#ffffff";s:6:"rownum";s:1:"4";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"8";s:7:"showdot";s:1:"1";s:7:"padding";s:1:"5";s:11:"paddingleft";s:2:"10";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:6:"iconfz";s:2:"14";s:9:"iconcolor";s:7:"#434343";s:8:"imgwidth";s:2:"51";}s:4:"data";a:4:{s:14:"C1564210832572";a:5:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_shop04/index/menu1.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"每日优选";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1564210832573";a:5:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_shop04/index/menu2.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"果疏超市";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1564210832574";a:5:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_shop04/index/menu3.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"生鲜超市";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1564210832575";a:5:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_shop04/index/menu4.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"乳品粮油";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}}s:2:"id";s:4:"menu";}s:14:"M1564210951551";a:5:{s:4:"icon";s:19:"iconfont2 icon-zutu";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:6:{s:10:"paddingtop";s:1:"0";s:11:"paddingleft";s:1:"5";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"background";s:7:"#ffffff";}s:4:"data";a:1:{s:14:"C1564210951551";a:2:{s:6:"imgurl";s:78:"https://four.nttrip.cn/diypage/template_img/template_shop04/index/picture1.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:7:"picture";}s:14:"M1564210974734";a:6:{s:4:"icon";s:19:"iconfont2 icon-zutu";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:6:{s:10:"paddingtop";s:1:"0";s:11:"paddingleft";s:1:"0";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"background";s:7:"#ffffff";}s:4:"data";a:1:{s:14:"C1564210974734";a:2:{s:6:"imgurl";s:78:"https://four.nttrip.cn/diypage/template_img/template_shop04/index/picture2.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:7:"picture";s:5:"index";s:3:"NaN";}s:14:"M1564211099252";a:5:{s:4:"icon";s:21:"iconfont2 icon-tupian";s:6:"params";a:9:{s:3:"row";s:1:"1";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"2";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:8:{s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:7:"showdot";s:1:"0";s:7:"showbtn";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:1:"0";s:10:"background";s:7:"#ffffff";}s:4:"data";a:3:{s:14:"C1564211099252";a:2:{s:6:"imgurl";s:79:"https://four.nttrip.cn/diypage/template_img/template_shop04/index/picturew1.png";s:7:"linkurl";s:0:"";}s:14:"C1564211099253";a:2:{s:6:"imgurl";s:79:"https://four.nttrip.cn/diypage/template_img/template_shop04/index/picturew2.png";s:7:"linkurl";s:0:"";}s:14:"C1564211099254";a:2:{s:6:"imgurl";s:79:"https://four.nttrip.cn/diypage/template_img/template_shop04/index/picturew3.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:8:"picturew";}s:14:"M1564211274085";a:6:{s:4:"icon";s:19:"iconfont2 icon-zutu";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:6:{s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"background";s:7:"#ffffff";}s:4:"data";a:5:{s:14:"C1564211274085";a:2:{s:6:"imgurl";s:80:"https://four.nttrip.cn/diypage/template_img/template_shop04/index/picture3_1.png";s:7:"linkurl";s:0:"";}s:14:"M1564211286063";a:2:{s:6:"imgurl";s:80:"https://four.nttrip.cn/diypage/template_img/template_shop04/index/picture3_2.png";s:7:"linkurl";s:0:"";}s:14:"M1564211288023";a:2:{s:6:"imgurl";s:80:"https://four.nttrip.cn/diypage/template_img/template_shop04/index/picture3_3.png";s:7:"linkurl";s:0:"";}s:14:"M1564211288767";a:2:{s:6:"imgurl";s:80:"https://four.nttrip.cn/diypage/template_img/template_shop04/index/picture3_4.png";s:7:"linkurl";s:0:"";}s:14:"M1564211289743";a:2:{s:6:"imgurl";s:80:"https://four.nttrip.cn/diypage/template_img/template_shop04/index/picture3_5.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:7:"picture";s:5:"index";s:3:"NaN";}s:14:"M1564211249949";a:4:{s:4:"icon";s:32:"iconfont2 icon-liebiao_biaotilan";s:6:"params";a:9:{s:5:"title";s:12:"每日爆款";s:6:"title2";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:5:"style";s:1:"1";}s:5:"style";a:11:{s:10:"background";s:7:"#ffffff";s:6:"colorz";s:7:"#000000";s:6:"colorf";s:7:"#838383";s:9:"linecolor";s:7:"#ffffff";s:9:"fontsizez";s:2:"18";s:9:"fontsizef";s:2:"12";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:1:"0";}s:2:"id";s:6:"title2";}s:14:"M1564211373379";a:6:{s:3:"max";s:1:"1";s:4:"icon";s:23:"iconfont2 icon-shandian";s:6:"params";a:12:{s:8:"navstyle";s:1:"1";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:9:"goodsdata";s:1:"1";s:8:"goodsnum";s:1:"2";s:7:"con_key";s:1:"1";s:8:"con_type";s:1:"1";s:8:"sourceid";s:0:"";}s:5:"style";a:9:{s:10:"background";s:7:"#ffffff";s:3:"pdw";s:2:"10";s:3:"pdh";s:2:"10";s:2:"mb";s:2:"10";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:4:"pich";s:1:"1";s:8:"showtype";s:1:"0";}s:4:"data";a:3:{s:14:"C1564211373379";a:14:{s:6:"imgurl";s:47:"/diypage/resource/images/diypage/default/11.jpg";s:5:"title";s:18:"秒杀商品标题";s:11:"description";s:18:"秒杀商品简介";s:5:"count";s:3:"927";s:5:"price";s:2:"21";s:4:"hour";s:2:"10";s:3:"min";s:2:"11";s:6:"second";s:2:"12";s:6:"person";s:1:"2";s:2:"tz";s:2:"92";s:3:"tgr";s:3:"427";s:3:"tgj";s:4:"17.2";s:7:"linkurl";s:0:"";s:8:"linktype";s:0:"";}s:14:"C1564211373380";a:14:{s:6:"imgurl";s:47:"/diypage/resource/images/diypage/default/11.jpg";s:5:"title";s:18:"秒杀商品标题";s:11:"description";s:18:"秒杀商品简介";s:5:"count";s:3:"927";s:5:"price";s:2:"21";s:4:"hour";s:2:"10";s:3:"min";s:2:"11";s:6:"second";s:2:"12";s:6:"person";s:1:"2";s:2:"tz";s:2:"92";s:3:"tgr";s:3:"427";s:3:"tgj";s:4:"17.2";s:7:"linkurl";s:0:"";s:8:"linktype";s:0:"";}s:14:"C1564211373381";a:14:{s:6:"imgurl";s:47:"/diypage/resource/images/diypage/default/11.jpg";s:5:"title";s:18:"秒杀商品标题";s:11:"description";s:18:"秒杀商品简介";s:5:"count";s:3:"927";s:5:"price";s:2:"21";s:4:"hour";s:2:"10";s:3:"min";s:2:"11";s:6:"second";s:2:"12";s:6:"person";s:1:"2";s:2:"tz";s:2:"92";s:3:"tgr";s:3:"427";s:3:"tgj";s:4:"17.2";s:7:"linkurl";s:0:"";s:8:"linktype";s:0:"";}}s:2:"id";s:4:"msmk";}}';
            $item1 = unserialize($item1);
            foreach($item1 as $k => &$v){
                if(isset($v['data'])){
                    foreach($v['data'] as $kk => &$vv){
                        if(isset($vv['imgurl']) && strpos($vv['imgurl'],'https://four.nttrip.cn') !== false){
                            $vv['imgurl'] = ROOT_HOST.explode('https://four.nttrip.cn', $vv['imgurl'])[1];
                        }
                        if(isset($vv['thumb']) && strpos($vv['thumb'],'/diypage/resource/images/diypage/default/11.jpg') !== false){
                            $vv['thumb'] = ROOT_HOST."/diypage/resource/images/diypage/default/11.jpg";
                        }
                    }
                }
            }
            $page1['items'] = serialize($item1);
            $page1['page'] = 'a:7:{s:10:"background";s:7:"#f1f1f1";s:13:"topbackground";s:7:"#ffffff";s:8:"topcolor";s:1:"1";s:9:"styledata";s:1:"0";s:5:"title";s:6:"首页";s:4:"name";s:6:"首页";s:10:"visitlevel";a:2:{s:6:"member";s:0:"";s:10:"commission";s:0:"";}}';
            $page1['tpl_name'] = "首页";

            $page1_id = Db::name('wd_xcx_diypage')->insertGetId($page1);
            $pageids = $page1_id;

            $is = Db::name('wd_xcx_diypageset')->where('uniacid',$uniacid)->find();
            if(empty($is)){
                $page_set = [
                    'uniacid' => $uniacid,
                    'kp' => ROOT_HOST."/diypage/resource/images/diypage/default/default_start.jpg",
                    'kp_is' => 2,
                    'kp_m' => 2,
                    'tc' => ROOT_HOST."/diypage/resource/images/diypage/default/tcgg.jpg",
                    'tc_is' => 2,
                    'foot_is' => 2
                ];
                Db::name('wd_xcx_diypageset')->insert($page_set);
            }else{
                Db::name('wd_xcx_diypageset')->where('uniacid',$uniacid)->update(array('foot_is' => 2));
            }
            $data = [
                'uniacid' => $uniacid,
                'pageid' => $pageids,
                'template_name' => '综合商城类模板04',
                'thumb' => "/diypage/template_img/template_shop04/cover.png",
                'status' => '1',
                'create_time' => time()
            ];
            $tplid = Db::name('wd_xcx_diypagetpl')->insertGetId($data);
            if($tplid){
                return $tplid;
            }
        }elseif($id == 'm_education'){
            $page1 = [];
            $page1['uniacid'] = $uniacid;
            $page1['index'] = 1;
            $item1 = 'a:10:{s:14:"M1544003458527";a:4:{s:4:"icon";s:22:"iconfont2 icon-sousuo1";s:6:"params";a:7:{s:5:"value";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:12:{s:9:"textalign";s:4:"left";s:10:"background";s:7:"#f26e47";s:2:"bg";s:7:"#f57953";s:12:"borderradius";s:2:"20";s:6:"boxpdh";s:2:"10";s:6:"boxpdz";s:2:"15";s:7:"padding";s:1:"5";s:8:"fontsize";s:2:"13";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"color";s:7:"#ffffff";}s:2:"id";s:3:"ssk";}s:14:"M1544003461117";a:5:{s:4:"icon";s:28:"iconfont2 icon-tuoyuankaobei";s:6:"params";a:10:{s:5:"totle";s:1:"2";s:8:"navstyle";s:1:"0";s:9:"styledata";s:1:"1";s:6:"repeat";s:9:"no-repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"1";s:13:"backgroundimg";s:76:"https://four.nttrip.cn/diypage/template_img/template_edu/index/banner_bg.jpg";s:9:"navstyle2";s:1:"0";s:4:"imgh";s:3:"135";}s:5:"style";a:18:{s:8:"dotstyle";s:5:"round";s:8:"dotalign";s:4:"left";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"25";s:10:"background";s:7:"#ffffff";s:13:"backgroundall";s:7:"#ffffff";s:9:"leftright";s:1:"5";s:6:"bottom";s:1:"5";s:7:"opacity";s:3:"0.8";s:10:"text_color";s:4:"#fff";s:2:"bg";s:7:"#000000";s:9:"jsq_color";s:3:"red";s:3:"pdh";s:1:"0";s:3:"pdw";s:1:"0";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"speed";s:1:"5";}s:4:"data";a:2:{s:14:"C1544003461118";a:5:{s:6:"imgurl";s:66:"https://four.nttrip.cn/template_img/template_edu/index/banner1.png";s:7:"linkurl";s:0:"";s:6:"single";s:1:"1";s:4:"text";s:12:"文字描述";s:8:"linktype";s:4:"page";}s:14:"C1544003461119";a:5:{s:6:"imgurl";s:66:"https://four.nttrip.cn/template_img/template_edu/index/banner1.png";s:7:"linkurl";s:0:"";s:6:"single";s:1:"2";s:4:"text";s:12:"文字描述";s:8:"linktype";s:4:"page";}}s:2:"id";s:6:"banner";}s:14:"M1544003712049";a:5:{s:4:"icon";s:22:"iconfont2 icon-anniuzu";s:6:"params";a:8:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"picicon";s:1:"1";s:8:"textshow";s:1:"1";}s:5:"style";a:14:{s:8:"navstyle";s:0:"";s:10:"background";s:7:"#ffffff";s:6:"rownum";s:1:"4";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"8";s:7:"showdot";s:1:"1";s:7:"padding";s:1:"0";s:11:"paddingleft";s:2:"10";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:6:"iconfz";s:2:"14";s:9:"iconcolor";s:7:"#434343";s:8:"imgwidth";s:2:"30";}s:4:"data";a:4:{s:14:"C1544003712049";a:6:{s:6:"imgurl";s:64:"https://four.nttrip.cn/template_img/template_edu/index/menu1.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"视频课堂";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";s:8:"linktype";s:4:"page";}s:14:"C1544003712050";a:6:{s:6:"imgurl";s:64:"https://four.nttrip.cn/template_img/template_edu/index/menu2.png";s:7:"linkurl";s:0:"";s:4:"text";s:9:"展示墙";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";s:8:"linktype";s:4:"page";}s:14:"C1544003712051";a:6:{s:6:"imgurl";s:64:"https://four.nttrip.cn/template_img/template_edu/index/menu3.png";s:7:"linkurl";s:0:"";s:4:"text";s:9:"讨论区";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";s:8:"linktype";s:4:"page";}s:14:"C1544003712052";a:6:{s:6:"imgurl";s:64:"https://four.nttrip.cn/template_img/template_edu/index/menu4.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"活动中心";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";s:8:"linktype";s:4:"page";}}s:2:"id";s:4:"menu";}s:14:"M1544003790109";a:5:{s:4:"icon";s:22:"iconfont2 icon-gonggao";s:6:"params";a:12:{s:7:"iconurl";s:15:"icon-x-gonggao4";s:10:"noticedata";s:1:"0";s:5:"speed";s:1:"4";s:9:"noticenum";s:1:"5";s:8:"navstyle";s:1:"1";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:8:"sourceid";s:0:"";}s:5:"style";a:9:{s:10:"background";s:7:"#ffffff";s:9:"iconcolor";s:7:"#ff9b64";s:5:"color";s:7:"#666666";s:11:"bordercolor";s:7:"#e2e2e2";s:2:"mt";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";}s:4:"data";a:2:{s:14:"C1544003790109";a:2:{s:5:"title";s:42:"这里是第一条自定义公告的标题";s:7:"linkurl";s:0:"";}s:14:"C1544003790110";a:2:{s:5:"title";s:42:"这里是第二条自定义公告的标题";s:7:"linkurl";s:0:"";}}s:2:"id";s:6:"notice";}s:14:"M1544084374773";a:5:{s:4:"icon";s:25:"iconfont2 icon-youhuiquan";s:6:"params";a:10:{s:8:"hidetext";s:1:"0";s:8:"showtype";s:1:"0";s:6:"rownum";s:1:"3";s:7:"showbtn";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:10:{s:10:"background";s:7:"#f6f6f6";s:5:"yhqbg";s:7:"#f26e47";s:6:"yhqbg2";s:7:"#f57953";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:5:"color";s:7:"#ffffff";s:2:"mt";s:1:"3";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:6:"counts";s:1:"3";}s:4:"data";a:3:{s:14:"C1544084374773";a:3:{s:7:"linkurl";s:0:"";s:5:"title";s:3:"100";s:4:"text";s:15:"满500元可用";}s:14:"C1544084374774";a:3:{s:7:"linkurl";s:0:"";s:5:"title";s:3:"100";s:4:"text";s:15:"满500元可用";}s:14:"C1544084374775";a:3:{s:7:"linkurl";s:0:"";s:5:"title";s:3:"100";s:4:"text";s:15:"满500元可用";}}s:2:"id";s:3:"yhq";}s:14:"M1544004061030";a:5:{s:4:"icon";s:23:"iconfont2 icon-daohang1";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:10:{s:9:"margintop";s:1:"0";s:10:"background";s:7:"#ffffff";s:9:"iconcolor";s:7:"#999999";s:9:"textcolor";s:7:"#666666";s:11:"remarkcolor";s:7:"#888888";s:5:"sizew";s:2:"20";s:11:"paddingleft";s:2:"10";s:7:"padding";s:1:"7";s:5:"sizeh";s:2:"20";s:9:"linecolor";s:7:"#ffffff";}s:4:"data";a:1:{s:14:"C1544004061030";a:6:{s:4:"text";s:12:"课程直击";s:7:"linkurl";s:0:"";s:9:"iconclass";s:0:"";s:6:"remark";s:4:"more";s:6:"dotnum";s:0:"";s:8:"linktype";s:4:"page";}}s:2:"id";s:8:"listmenu";}s:14:"M1544004158643";a:5:{s:4:"icon";s:19:"iconfont2 icon-zutu";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:6:{s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"background";s:7:"#ffffff";}s:4:"data";a:1:{s:14:"C1544004158643";a:3:{s:6:"imgurl";s:66:"https://four.nttrip.cn/template_img/template_edu/index/banner2.png";s:7:"linkurl";s:0:"";s:8:"linktype";s:4:"page";}}s:2:"id";s:7:"picture";}s:14:"M1544003465567";a:6:{s:3:"max";s:1:"1";s:4:"icon";s:23:"iconfont2 icon-shandian";s:6:"params";a:12:{s:8:"navstyle";s:1:"1";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:9:"goodsdata";s:1:"1";s:8:"goodsnum";s:1:"3";s:7:"con_key";s:1:"1";s:8:"con_type";s:1:"1";s:8:"sourceid";s:0:"";}s:5:"style";a:9:{s:10:"background";s:7:"#ffffff";s:3:"pdw";s:2:"10";s:3:"pdh";s:2:"10";s:2:"mb";s:2:"10";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:4:"pich";s:1:"1";s:8:"showtype";s:1:"0";}s:4:"data";a:3:{s:14:"C1544003465567";a:14:{s:6:"imgurl";s:47:"/diypage/resource/images/diypage/default/11.jpg";s:5:"title";s:18:"秒杀商品标题";s:11:"description";s:18:"秒杀商品简介";s:5:"count";s:3:"927";s:5:"price";s:2:"21";s:4:"hour";s:2:"10";s:3:"min";s:2:"11";s:6:"second";s:2:"12";s:6:"person";s:1:"2";s:2:"tz";s:2:"92";s:3:"tgr";s:3:"427";s:3:"tgj";s:4:"17.2";s:7:"linkurl";s:0:"";s:8:"linktype";s:0:"";}s:14:"C1544003465568";a:14:{s:6:"imgurl";s:47:"/diypage/resource/images/diypage/default/11.jpg";s:5:"title";s:18:"秒杀商品标题";s:11:"description";s:18:"秒杀商品简介";s:5:"count";s:3:"927";s:5:"price";s:2:"21";s:4:"hour";s:2:"10";s:3:"min";s:2:"11";s:6:"second";s:2:"12";s:6:"person";s:1:"2";s:2:"tz";s:2:"92";s:3:"tgr";s:3:"427";s:3:"tgj";s:4:"17.2";s:7:"linkurl";s:0:"";s:8:"linktype";s:0:"";}s:14:"C1544003465569";a:14:{s:6:"imgurl";s:47:"/diypage/resource/images/diypage/default/11.jpg";s:5:"title";s:18:"秒杀商品标题";s:11:"description";s:18:"秒杀商品简介";s:5:"count";s:3:"927";s:5:"price";s:2:"21";s:4:"hour";s:2:"10";s:3:"min";s:2:"11";s:6:"second";s:2:"12";s:6:"person";s:1:"2";s:2:"tz";s:2:"92";s:3:"tgr";s:3:"427";s:3:"tgj";s:4:"17.2";s:7:"linkurl";s:0:"";s:8:"linktype";s:0:"";}}s:2:"id";s:4:"msmk";}s:14:"M1544004194084";a:6:{s:4:"icon";s:23:"iconfont2 icon-daohang1";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:10:{s:9:"margintop";s:1:"8";s:10:"background";s:7:"#ffffff";s:9:"iconcolor";s:7:"#999999";s:9:"textcolor";s:7:"#666666";s:11:"remarkcolor";s:7:"#888888";s:5:"sizew";s:2:"20";s:11:"paddingleft";s:2:"10";s:7:"padding";s:1:"7";s:5:"sizeh";s:2:"20";s:9:"linecolor";s:7:"#ffffff";}s:4:"data";a:1:{s:14:"C1544004194084";a:6:{s:4:"text";s:12:"名师在线";s:7:"linkurl";s:0:"";s:9:"iconclass";s:0:"";s:6:"remark";s:4:"more";s:6:"dotnum";s:0:"";s:8:"linktype";s:4:"page";}}s:2:"id";s:8:"listmenu";s:5:"index";s:3:"NaN";}s:14:"M1544004241306";a:6:{s:4:"icon";s:23:"iconfont2 icon-wenzhang";s:6:"params";a:19:{s:9:"showstyle";s:4:"row1";s:7:"newsnum";s:1:"4";s:8:"newsdata";s:1:"0";s:5:"title";s:21:"请选择文章分类";s:7:"titleid";s:1:"0";s:8:"navstyle";s:1:"2";s:5:"show1";s:1:"1";s:5:"show2";s:1:"0";s:5:"show3";s:1:"0";s:5:"show4";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"con_key";s:1:"1";s:8:"con_type";s:1:"1";s:8:"sourceid";s:0:"";}s:5:"style";a:11:{s:10:"background";s:4:"#fff";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:12:"marginbottom";s:2:"10";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"color";s:7:"#434343";s:6:"radius";s:1:"0";s:4:"pich";s:1:"1";s:8:"showtype";s:1:"0";}s:4:"data";a:3:{s:14:"C1544004241306";a:8:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/3.jpg";s:2:"id";s:0:"";s:5:"title";s:15:"这里是标题";s:4:"time";s:16:"2017年10月1日";s:5:"intro";s:21:"简介1简介1简介1";s:3:"ydl";s:2:"10";s:3:"dzl";s:2:"10";s:3:"pll";s:2:"10";}s:14:"C1544004241307";a:8:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/3.jpg";s:2:"id";s:0:"";s:5:"title";s:15:"这里是标题";s:4:"time";s:16:"2017年10月1日";s:5:"intro";s:15:"这里是简介";s:3:"ydl";s:2:"10";s:3:"dzl";s:2:"10";s:3:"pll";s:2:"10";}s:14:"C1544004241308";a:8:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/3.jpg";s:2:"id";s:0:"";s:5:"title";s:15:"这里是标题";s:4:"time";s:16:"2017年10月1日";s:5:"intro";s:15:"这里是简介";s:3:"ydl";s:2:"10";s:3:"dzl";s:2:"10";s:3:"pll";s:2:"10";}}s:2:"id";s:8:"listdesc";s:5:"index";s:3:"NaN";}}';
            $item1 = unserialize($item1);
            foreach($item1 as $k => &$v){
                if(isset($v['data'])){
                    foreach($v['data'] as $kk => &$vv){
                        if(isset($vv['imgurl']) && strpos($vv['imgurl'],'https://four.nttrip.cn/') !== false){
                            $vv['imgurl'] = ROOT_HOST."/diypage/".explode('https://four.nttrip.cn/', $vv['imgurl'])[1];
                        }
                        if(isset($vv['imgurl']) && strpos($vv['imgurl'],'/diypage/resource/images/diypage/default/11.jpg') !== false){
                            $vv['imgurl'] = ROOT_HOST."/diypage/resource/images/diypage/default/11.jpg";
                        }
                        if(isset($vv['thumb']) && strpos($vv['thumb'],'/diypage/resource/images/diypage/default/3.jpg') !== false){
                            $vv['thumb'] = ROOT_HOST."/diypage/resource/images/diypage/default/3.jpg";
                        }
                    }
                }
            }
            $page1['items'] = serialize($item1);
            $page1['page'] = 'a:7:{s:10:"background";s:7:"#f1f1f1";s:13:"topbackground";s:7:"#f26e47";s:8:"topcolor";s:1:"2";s:9:"styledata";s:1:"0";s:5:"title";s:12:"教育首页";s:4:"name";s:6:"首页";s:10:"visitlevel";a:2:{s:6:"member";s:0:"";s:10:"commission";s:0:"";}}';
            $page1['tpl_name'] = "首页";

            $page2 = [];
            $page2['uniacid'] = $uniacid;
            $page2['index'] = 0;
            $item2 = 'a:1:{s:14:"M1544004346958";a:6:{s:4:"icon";s:21:"iconfont2 icon-tupian";s:6:"params";a:9:{s:3:"row";s:1:"2";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"2";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:8:{s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:7:"showdot";s:1:"0";s:7:"showbtn";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:1:"0";s:10:"background";s:7:"#ffffff";}s:4:"data";a:6:{s:14:"C1544004346959";a:2:{s:6:"imgurl";s:68:"https://four.nttrip.cn/template_img/template_edu/page1/picturew1.png";s:7:"linkurl";s:0:"";}s:14:"C1544004346960";a:2:{s:6:"imgurl";s:68:"https://four.nttrip.cn/template_img/template_edu/page1/picturew2.png";s:7:"linkurl";s:0:"";}s:14:"C1544004346961";a:2:{s:6:"imgurl";s:68:"https://four.nttrip.cn/template_img/template_edu/page1/picturew3.png";s:7:"linkurl";s:0:"";}s:14:"C1544004346962";a:2:{s:6:"imgurl";s:68:"https://four.nttrip.cn/template_img/template_edu/page1/picturew4.png";s:7:"linkurl";s:0:"";}s:14:"M1544004361449";a:2:{s:6:"imgurl";s:68:"https://four.nttrip.cn/template_img/template_edu/page1/picturew5.png";s:7:"linkurl";s:0:"";}s:14:"M1544004363193";a:2:{s:6:"imgurl";s:68:"https://four.nttrip.cn/template_img/template_edu/page1/picturew6.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:8:"picturew";s:5:"index";s:3:"NaN";}}';
            $item2 = unserialize($item2);
            foreach($item2 as $k => &$v){
                if(isset($v['data'])){
                    foreach($v['data'] as $kk => &$vv){
                        if(isset($vv['imgurl']) && strpos($vv['imgurl'],'https://four.nttrip.cn/') !== false){
                            $vv['imgurl'] = ROOT_HOST."/diypage/".explode('https://four.nttrip.cn/', $vv['imgurl'])[1];
                        }
                    }
                }
            }
            $page2['items'] = serialize($item2);
            $page2['page'] = 'a:7:{s:10:"background";s:7:"#f1f1f1";s:13:"topbackground";s:7:"#f26e47";s:8:"topcolor";s:1:"2";s:9:"styledata";s:1:"0";s:5:"title";s:9:"展示墙";s:4:"name";s:9:"展示墙";s:10:"visitlevel";a:2:{s:6:"member";s:0:"";s:10:"commission";s:0:"";}}';
            $page2['tpl_name'] = "展示墙";

            $page3 = [];
            $page3['uniacid'] = $uniacid;
            $page3['index'] = 0;
            $item3 = 'a:3:{s:14:"M1544004589716";a:5:{s:4:"icon";s:19:"iconfont2 icon-zutu";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:6:{s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"background";s:7:"#ffffff";}s:4:"data";a:1:{s:14:"C1544004589717";a:3:{s:6:"imgurl";s:67:"https://four.nttrip.cn/template_img/template_edu/page2/picture1.png";s:7:"linkurl";s:0:"";s:8:"linktype";s:4:"page";}}s:2:"id";s:7:"picture";}s:14:"M1544004632347";a:6:{s:4:"icon";s:19:"iconfont2 icon-zutu";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:6:{s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"background";s:7:"#ffffff";}s:4:"data";a:1:{s:14:"C1544004632347";a:3:{s:6:"imgurl";s:67:"https://four.nttrip.cn/template_img/template_edu/page2/picture2.png";s:7:"linkurl";s:0:"";s:8:"linktype";s:4:"page";}}s:2:"id";s:7:"picture";s:5:"index";s:3:"NaN";}s:14:"M1544004632867";a:6:{s:4:"icon";s:19:"iconfont2 icon-zutu";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:6:{s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"background";s:7:"#ffffff";}s:4:"data";a:1:{s:14:"C1544004632867";a:3:{s:6:"imgurl";s:67:"https://four.nttrip.cn/template_img/template_edu/page2/picture3.png";s:7:"linkurl";s:0:"";s:8:"linktype";s:4:"page";}}s:2:"id";s:7:"picture";s:5:"index";s:3:"NaN";}}';
            $item3 = unserialize($item3);
            foreach($item3 as $k => &$v){
                if(isset($v['data'])){
                    foreach($v['data'] as $kk => &$vv){
                        if(isset($vv['imgurl']) && strpos($vv['imgurl'],'https://four.nttrip.cn/') !== false){
                            $vv['imgurl'] = ROOT_HOST."/diypage/".explode('https://four.nttrip.cn/', $vv['imgurl'])[1];
                        }
                    }
                }
            }
            $page3['items'] = serialize($item3);
            $page3['page'] = 'a:7:{s:10:"background";s:7:"#f1f1f1";s:13:"topbackground";s:7:"#f26e47";s:8:"topcolor";s:1:"2";s:9:"styledata";s:1:"0";s:5:"title";s:12:"活动中心";s:4:"name";s:12:"活动中心";s:10:"visitlevel";a:2:{s:6:"member";s:0:"";s:10:"commission";s:0:"";}}';
            $page3['tpl_name'] = "活动中心";

            $page4 = [];
            $page4['uniacid'] = $uniacid;
            $page4['index'] = 0;
            $item4 = 'a:5:{s:14:"M1544004750684";a:5:{s:4:"icon";s:25:"iconfont2 icon-wenzianniu";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:6:{s:9:"margintop";s:1:"0";s:10:"background";s:7:"#f26e47";s:5:"sizew";s:2:"20";s:11:"paddingleft";s:2:"10";s:7:"padding";s:2:"10";s:5:"sizeh";s:2:"20";}s:4:"data";a:2:{s:14:"C1544004750684";a:6:{s:4:"text";s:12:"关于我们";s:9:"iconclass";s:0:"";s:9:"textcolor";s:7:"#ffffff";s:9:"iconcolor";s:7:"#666666";s:7:"linkurl";s:33:"/sudu8_page/index/index?pageid=38";s:8:"linktype";s:4:"page";}s:14:"C1544004750685";a:6:{s:4:"text";s:9:"分校区";s:9:"iconclass";s:0:"";s:9:"textcolor";s:7:"#ffffff";s:9:"iconcolor";s:7:"#666666";s:7:"linkurl";s:23:"/sudu8_page/store/store";s:8:"linktype";s:4:"page";}}s:2:"id";s:5:"menu2";}s:14:"M1544004811551";a:5:{s:4:"icon";s:28:"iconfont2 icon-tuoyuankaobei";s:6:"params";a:10:{s:5:"totle";s:1:"2";s:8:"navstyle";s:1:"0";s:9:"styledata";s:1:"1";s:6:"repeat";s:9:"no-repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"1";s:13:"backgroundimg";s:68:"https://four.nttrip.cn/template_img/template_edu/page3/banner_bg.jpg";s:9:"navstyle2";s:1:"0";s:4:"imgh";s:3:"135";}s:5:"style";a:18:{s:8:"dotstyle";s:5:"round";s:8:"dotalign";s:4:"left";s:10:"paddingtop";s:2:"25";s:11:"paddingleft";s:2:"20";s:10:"background";s:7:"#ffffff";s:13:"backgroundall";s:7:"#ffffff";s:9:"leftright";s:1:"5";s:6:"bottom";s:1:"5";s:7:"opacity";s:3:"0.8";s:10:"text_color";s:4:"#fff";s:2:"bg";s:7:"#000000";s:9:"jsq_color";s:3:"red";s:3:"pdh";s:1:"0";s:3:"pdw";s:1:"0";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"speed";s:1:"5";}s:4:"data";a:2:{s:14:"C1544004811551";a:4:{s:6:"imgurl";s:65:"https://four.nttrip.cn/template_img/template_edu/page3/banner.png";s:7:"linkurl";s:0:"";s:6:"single";s:1:"1";s:4:"text";s:12:"文字描述";}s:14:"C1544004811552";a:4:{s:6:"imgurl";s:65:"https://four.nttrip.cn/template_img/template_edu/page3/banner.png";s:7:"linkurl";s:0:"";s:6:"single";s:1:"2";s:4:"text";s:12:"文字描述";}}s:2:"id";s:6:"banner";}s:14:"M1544005009205";a:5:{s:3:"max";s:1:"5";s:4:"icon";s:23:"iconfont2 icon-fuwenben";s:6:"params";a:1:{s:7:"content";s:552:"PHA+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTZweDsiPjxzdHJvbmc+6L6+5YaF5Z+56K6t5py65p6EPC9zdHJvbmc+PC9zcGFuPjxici8+PC9wPjxwIHN0eWxlPSJtYXJnaW4tdG9wOiAxMHB4OyI+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTRweDsgY29sb3I6IHJnYigxMjcsIDEyNywgMTI3KTsiPui+vuWGheaXtuS7o+enkeaKgOmbhuWbouaciemZkOWFrOWPuOaIkOeri+S6jjIwMDLlubQ55pyI44CCMjAxNOW5tDTmnIgz5pel5oiQ5Yqf5Zyo576O5Zu957qz5pav6L6+5YWL5LiK5biC77yM6J6N6LWEMeS6vzPljYPkuIfnvo7lhYPjgILmiJDkuLrkuK3lm73otbTnvo7lm73kuIrluILnmoTogYzkuJrmlZnogrLlhazlj7jvvIzkuZ/mmK/lvJXpoobooYzkuJrnmoTogYzkuJrmlZnogrLlhazlj7jjgII8L3NwYW4+PC9wPg==";}s:5:"style";a:3:{s:10:"background";s:7:"#ffffff";s:7:"padding";s:2:"10";s:9:"margintop";s:1:"0";}s:2:"id";s:8:"richtext";}s:14:"M1544005052741";a:4:{s:4:"icon";s:32:"iconfont2 icon-liebiao_biaotilan";s:6:"params";a:10:{s:5:"title";s:46:"南通市人民东路228号东方广场2号楼";s:4:"icon";s:12:"icon-x-dizhi";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:4:"link";s:88:"32.023880,120.906530##达内培训机构##南通市人民东路228号东方广场2号楼";s:8:"linktype";s:3:"map";}s:5:"style";a:9:{s:10:"background";s:7:"#ffffff";s:5:"color";s:7:"#666666";s:9:"textalign";s:4:"left";s:8:"fontsize";s:2:"14";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:1:"0";}s:2:"id";s:5:"title";}s:14:"M1544005180683";a:4:{s:4:"icon";s:32:"iconfont2 icon-liebiao_biaotilan";s:6:"params";a:10:{s:5:"title";s:22:"电话：0513-26278273";s:4:"icon";s:15:"icon-x-dianhua1";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:4:"link";s:17:"tel:0513-26278273";s:8:"linktype";s:3:"tel";}s:5:"style";a:9:{s:10:"background";s:7:"#ffffff";s:5:"color";s:7:"#666666";s:9:"textalign";s:4:"left";s:8:"fontsize";s:2:"14";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:1:"0";}s:2:"id";s:5:"title";}}';
            $item4 = unserialize($item4);
            foreach($item4 as $k => &$v){
                if(isset($v['data'])){
                    foreach($v['data'] as $kk => &$vv){
                        if(isset($vv['imgurl']) && strpos($vv['imgurl'],'https://four.nttrip.cn/') !== false){
                            $vv['imgurl'] = ROOT_HOST."/diypage/".explode('https://four.nttrip.cn/', $vv['imgurl'])[1];
                        }
                    }
                }
            }
            $page4['items'] = serialize($item4);
            $page4['page'] = 'a:7:{s:10:"background";s:7:"#f1f1f1";s:13:"topbackground";s:7:"#f26e47";s:8:"topcolor";s:1:"2";s:9:"styledata";s:1:"0";s:5:"title";s:12:"学校介绍";s:4:"name";s:12:"学校介绍";s:10:"visitlevel";a:2:{s:6:"member";s:0:"";s:10:"commission";s:0:"";}}';
            $page4['tpl_name'] = "学校介绍";

            $page5 = [];
            $page5['uniacid'] = $uniacid;
            $page5['index'] = 0;
            $item5 = 'a:9:{s:14:"M1544062245674";a:5:{s:4:"icon";s:19:"iconfont2 icon-zutu";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:6:{s:10:"paddingtop";s:2:"15";s:11:"paddingleft";s:2:"15";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"background";s:7:"#ffffff";}s:4:"data";a:1:{s:14:"C1544062245674";a:3:{s:6:"imgurl";s:67:"https://four.nttrip.cn/template_img/template_edu/page4/picture1.png";s:7:"linkurl";s:0:"";s:8:"linktype";s:4:"page";}}s:2:"id";s:7:"picture";}s:14:"M1544062259958";a:4:{s:4:"icon";s:32:"iconfont2 icon-liebiao_biaotilan";s:6:"params";a:10:{s:5:"title";s:37:"插画师养成记-初期练习指南";s:4:"icon";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:4:"link";s:0:"";s:8:"linktype";s:4:"page";}s:5:"style";a:9:{s:10:"background";s:7:"#ffffff";s:5:"color";s:7:"#666666";s:9:"textalign";s:4:"left";s:8:"fontsize";s:2:"14";s:10:"paddingtop";s:1:"3";s:11:"paddingleft";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:1:"0";}s:2:"id";s:5:"title";}s:14:"M1544062263803";a:5:{s:4:"icon";s:23:"iconfont2 icon-daohang1";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:10:{s:9:"margintop";s:1:"0";s:10:"background";s:7:"#ffffff";s:9:"iconcolor";s:7:"#999999";s:9:"textcolor";s:7:"#666666";s:11:"remarkcolor";s:7:"#888888";s:5:"sizew";s:2:"20";s:11:"paddingleft";s:2:"10";s:7:"padding";s:1:"0";s:5:"sizeh";s:2:"20";s:9:"linecolor";s:7:"#ffffff";}s:4:"data";a:1:{s:14:"C1544062263803";a:6:{s:4:"text";s:21:"11月21日11:30开课";s:7:"linkurl";s:0:"";s:9:"iconclass";s:15:"icon-c-shijian2";s:6:"remark";s:15:"讲师：王蓉";s:6:"dotnum";s:0:"";s:8:"linktype";s:4:"page";}}s:2:"id";s:8:"listmenu";}s:14:"M1544061563099";a:6:{s:4:"icon";s:19:"iconfont2 icon-zutu";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:6:{s:10:"paddingtop";s:2:"15";s:11:"paddingleft";s:2:"15";s:2:"mt";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"background";s:7:"#ffffff";}s:4:"data";a:1:{s:14:"C1544061563099";a:3:{s:6:"imgurl";s:67:"https://four.nttrip.cn/template_img/template_edu/page4/picture2.png";s:7:"linkurl";s:0:"";s:8:"linktype";s:4:"page";}}s:2:"id";s:7:"picture";s:5:"index";s:3:"NaN";}s:14:"M1544061568609";a:4:{s:4:"icon";s:32:"iconfont2 icon-liebiao_biaotilan";s:6:"params";a:10:{s:5:"title";s:21:"语文写作进阶班";s:4:"icon";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:4:"link";s:0:"";s:8:"linktype";s:4:"page";}s:5:"style";a:9:{s:10:"background";s:7:"#ffffff";s:5:"color";s:7:"#666666";s:9:"textalign";s:4:"left";s:8:"fontsize";s:2:"14";s:10:"paddingtop";s:1:"3";s:11:"paddingleft";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:1:"0";}s:2:"id";s:5:"title";}s:14:"M1544061573342";a:5:{s:4:"icon";s:23:"iconfont2 icon-daohang1";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:10:{s:9:"margintop";s:1:"0";s:10:"background";s:7:"#ffffff";s:9:"iconcolor";s:7:"#999999";s:9:"textcolor";s:7:"#666666";s:11:"remarkcolor";s:7:"#888888";s:5:"sizew";s:2:"20";s:11:"paddingleft";s:2:"10";s:7:"padding";s:1:"0";s:5:"sizeh";s:2:"20";s:9:"linecolor";s:7:"#ffffff";}s:4:"data";a:1:{s:14:"C1544061573342";a:6:{s:4:"text";s:21:"11月22日16:30开课";s:7:"linkurl";s:0:"";s:9:"iconclass";s:15:"icon-c-shijian2";s:6:"remark";s:15:"讲师：张亮";s:6:"dotnum";s:0:"";s:8:"linktype";s:4:"page";}}s:2:"id";s:8:"listmenu";}s:14:"M1544059901146";a:5:{s:4:"icon";s:19:"iconfont2 icon-zutu";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:6:{s:10:"paddingtop";s:2:"15";s:11:"paddingleft";s:2:"15";s:2:"mt";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"background";s:7:"#ffffff";}s:4:"data";a:1:{s:14:"C1544059901146";a:3:{s:6:"imgurl";s:67:"https://four.nttrip.cn/template_img/template_edu/page4/picture3.png";s:7:"linkurl";s:0:"";s:8:"linktype";s:4:"page";}}s:2:"id";s:7:"picture";}s:14:"M1544058945116";a:4:{s:4:"icon";s:32:"iconfont2 icon-liebiao_biaotilan";s:6:"params";a:10:{s:5:"title";s:20:"IT技术基础课程";s:4:"icon";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:4:"link";s:0:"";s:8:"linktype";s:4:"page";}s:5:"style";a:9:{s:10:"background";s:7:"#ffffff";s:5:"color";s:7:"#666666";s:9:"textalign";s:4:"left";s:8:"fontsize";s:2:"14";s:10:"paddingtop";s:1:"3";s:11:"paddingleft";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:1:"0";}s:2:"id";s:5:"title";}s:14:"M1544058949612";a:5:{s:4:"icon";s:23:"iconfont2 icon-daohang1";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:10:{s:9:"margintop";s:1:"0";s:10:"background";s:7:"#ffffff";s:9:"iconcolor";s:7:"#999999";s:9:"textcolor";s:7:"#666666";s:11:"remarkcolor";s:7:"#888888";s:5:"sizew";s:2:"20";s:11:"paddingleft";s:2:"10";s:7:"padding";s:1:"0";s:5:"sizeh";s:2:"20";s:9:"linecolor";s:7:"#ffffff";}s:4:"data";a:1:{s:14:"C1544058949612";a:6:{s:4:"text";s:21:"11月23日19:30开课";s:7:"linkurl";s:0:"";s:9:"iconclass";s:15:"icon-c-shijian2";s:6:"remark";s:15:"讲师：周钰";s:6:"dotnum";s:0:"";s:8:"linktype";s:4:"page";}}s:2:"id";s:8:"listmenu";}}';
            $item5 = unserialize($item5);
            foreach($item5 as $k => &$v){
                if(isset($v['data'])){
                    foreach($v['data'] as $kk => &$vv){
                        if(isset($vv['imgurl']) && strpos($vv['imgurl'],'https://four.nttrip.cn/') !== false){
                            $vv['imgurl'] = ROOT_HOST."/diypage/".explode('https://four.nttrip.cn/', $vv['imgurl'])[1];
                        }
                    }
                }
            }
            $page5['items'] = serialize($item5);
            $page5['page'] = 'a:7:{s:10:"background";s:7:"#ffffff";s:13:"topbackground";s:7:"#f26e47";s:8:"topcolor";s:1:"2";s:9:"styledata";s:1:"0";s:5:"title";s:12:"视频课堂";s:4:"name";s:12:"视频课堂";s:10:"visitlevel";a:2:{s:6:"member";s:0:"";s:10:"commission";s:0:"";}}';
            $page5['tpl_name'] = "视频课堂";

            $page1_id = Db::name('wd_xcx_diypage')->insertGetId($page1);
            $page2_id = Db::name('wd_xcx_diypage')->insertGetId($page2);
            $page3_id = Db::name('wd_xcx_diypage')->insertGetId($page3);
            $page4_id = Db::name('wd_xcx_diypage')->insertGetId($page4);
            $page5_id = Db::name('wd_xcx_diypage')->insertGetId($page5);
            $pageids = $page1_id.",".$page2_id.",".$page3_id.",".$page4_id.",".$page5_id;

            $is = Db::name('wd_xcx_diypageset')->where('uniacid',$uniacid)->find();
            if(empty($is)){
                $page_set = [
                    'uniacid' => $uniacid,
                    'kp' => ROOT_HOST."/diypage/resource/images/diypage/default/default_start.jpg",
                    'kp_is' => 2,
                    'kp_m' => 2,
                    'tc' => ROOT_HOST."/diypage/resource/images/diypage/default/tcgg.jpg",
                    'tc_is' => 2,
                    'foot_is' => 2
                ];
                Db::name('wd_xcx_diypageset')->insert($page_set);
            }else{
                Db::name('wd_xcx_diypageset')->where('uniacid',$uniacid)->update(array('foot_is' => 2));
            }
            $data = [
                'uniacid' => $uniacid,
                'pageid' => $pageids,
                'template_name' => '教育类模板01',
                'thumb' => "/diypage/template_img/template_edu/cover.jpg",
                'status' => '1',
                'create_time' => time()
            ];
            $tplid = Db::name('wd_xcx_diypagetpl')->insertGetId($data);
            if($tplid){
                return $tplid;
            }
        }elseif($id == 'm_edu02'){
            $page1 = [];
            $page1['uniacid'] = $uniacid;
            $page1['index'] = 1;
            $item1 = 'a:7:{s:14:"M1564021327430";a:5:{s:4:"icon";s:28:"iconfont2 icon-tuoyuankaobei";s:6:"params";a:10:{s:5:"totle";s:1:"2";s:8:"navstyle";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:9:"navstyle2";s:1:"0";s:4:"imgh";s:3:"180";}s:5:"style";a:18:{s:8:"dotstyle";s:5:"round";s:8:"dotalign";s:4:"left";s:10:"paddingtop";s:1:"0";s:11:"paddingleft";s:1:"0";s:10:"background";s:7:"#ffffff";s:13:"backgroundall";s:7:"#ffffff";s:9:"leftright";s:1:"5";s:6:"bottom";s:1:"5";s:7:"opacity";s:1:"0";s:10:"text_color";s:4:"#fff";s:2:"bg";s:7:"#000000";s:9:"jsq_color";s:3:"red";s:3:"pdh";s:1:"0";s:3:"pdw";s:1:"0";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"speed";s:1:"5";}s:4:"data";a:1:{s:14:"C1564021327430";a:4:{s:6:"imgurl";s:76:"https://four.nttrip.cn/diypage/template_img/template_edu02/index/banner1.png";s:7:"linkurl";s:0:"";s:6:"single";s:1:"1";s:4:"text";s:12:"文字描述";}}s:2:"id";s:6:"banner";}s:14:"M1564021370236";a:5:{s:4:"icon";s:22:"iconfont2 icon-anniuzu";s:6:"params";a:8:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"picicon";s:1:"1";s:8:"textshow";s:1:"1";}s:5:"style";a:14:{s:8:"navstyle";s:0:"";s:10:"background";s:7:"#ffffff";s:6:"rownum";s:1:"4";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"8";s:7:"showdot";s:1:"1";s:7:"padding";s:1:"0";s:11:"paddingleft";s:2:"10";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:6:"iconfz";s:2:"14";s:9:"iconcolor";s:7:"#434343";s:8:"imgwidth";s:2:"44";}s:4:"data";a:8:{s:14:"C1564021370236";a:5:{s:6:"imgurl";s:74:"https://four.nttrip.cn/diypage/template_img/template_edu02/index/menu1.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"班级通讯";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1564021370237";a:5:{s:6:"imgurl";s:74:"https://four.nttrip.cn/diypage/template_img/template_edu02/index/menu2.png";s:7:"linkurl";s:0:"";s:4:"text";s:9:"课程表";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1564021370238";a:5:{s:6:"imgurl";s:74:"https://four.nttrip.cn/diypage/template_img/template_edu02/index/menu3.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"家庭作业";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1564021370239";a:5:{s:6:"imgurl";s:74:"https://four.nttrip.cn/diypage/template_img/template_edu02/index/menu4.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"测验成绩";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"M1564021856942";a:5:{s:6:"imgurl";s:74:"https://four.nttrip.cn/diypage/template_img/template_edu02/index/menu5.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"校园缴费";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"M1564021858221";a:5:{s:6:"imgurl";s:74:"https://four.nttrip.cn/diypage/template_img/template_edu02/index/menu6.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"校园快讯";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"M1564021859285";a:5:{s:6:"imgurl";s:74:"https://four.nttrip.cn/diypage/template_img/template_edu02/index/menu7.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"校园风采";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"M1564021860229";a:5:{s:6:"imgurl";s:74:"https://four.nttrip.cn/diypage/template_img/template_edu02/index/menu8.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"教育摘要";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}}s:2:"id";s:4:"menu";}s:14:"M1564022197702";a:4:{s:4:"icon";s:21:"iconfont2 icon-dianpu";s:6:"params";a:13:{s:3:"src";s:74:"https://four.nttrip.cn/diypage/template_img/template_edu02/index/logo1.png";s:5:"title";s:12:"学校通知";s:5:"intro";s:0:"";s:7:"linkurl";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"1";s:13:"backgroundimg";s:0:"";s:8:"phonenum";s:0:"";s:5:"style";s:1:"3";s:8:"urltitle";s:6:"更多";}s:5:"style";a:16:{s:3:"pdh";s:2:"10";s:3:"pdz";s:2:"10";s:2:"bg";s:7:"#ffffff";s:2:"mt";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"titlecolor";s:7:"#000000";s:10:"introcolor";s:7:"#ffffff";s:7:"phonebg";s:7:"#ffffff";s:10:"phonecolor";s:4:"#fff";s:4:"ljfz";s:2:"15";s:7:"introfz";s:2:"12";s:4:"dpfz";s:2:"16";s:8:"imgwidth";s:2:"25";s:10:"phonenumfz";s:2:"12";s:7:"ljcolor";s:7:"#808080";}s:2:"id";s:4:"logo";}s:14:"M1564022791091";a:6:{s:4:"icon";s:28:"iconfont2 icon-tuoyuankaobei";s:6:"params";a:10:{s:5:"totle";s:1:"2";s:8:"navstyle";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:9:"navstyle2";s:1:"0";s:4:"imgh";s:3:"180";}s:5:"style";a:18:{s:8:"dotstyle";s:5:"round";s:8:"dotalign";s:4:"left";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"25";s:10:"background";s:7:"#ffffff";s:13:"backgroundall";s:7:"#ffffff";s:9:"leftright";s:1:"5";s:6:"bottom";s:1:"5";s:7:"opacity";s:3:"0.5";s:10:"text_color";s:4:"#fff";s:2:"bg";s:7:"#000000";s:9:"jsq_color";s:3:"red";s:3:"pdh";s:1:"0";s:3:"pdw";s:1:"0";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"speed";s:1:"5";}s:4:"data";a:3:{s:14:"C1564022791091";a:4:{s:6:"imgurl";s:78:"https://four.nttrip.cn/diypage/template_img/template_edu02/index/banner2_1.png";s:7:"linkurl";s:0:"";s:6:"single";s:1:"1";s:4:"text";s:12:"文字描述";}s:14:"C1564022791092";a:4:{s:6:"imgurl";s:78:"https://four.nttrip.cn/diypage/template_img/template_edu02/index/banner2_2.png";s:7:"linkurl";s:0:"";s:6:"single";s:1:"2";s:4:"text";s:12:"文字描述";}s:14:"M1564022837902";a:4:{s:6:"imgurl";s:78:"https://four.nttrip.cn/diypage/template_img/template_edu02/index/banner2_3.png";s:7:"linkurl";s:0:"";s:6:"single";s:1:"1";s:4:"text";s:12:"文字描述";}}s:2:"id";s:6:"banner";s:5:"index";s:3:"NaN";}s:14:"M1564022876796";a:5:{s:4:"icon";s:19:"iconfont2 icon-zutu";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:6:{s:10:"paddingtop";s:1:"0";s:11:"paddingleft";s:1:"5";s:2:"mt";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"background";s:7:"#f1f1f1";}s:4:"data";a:1:{s:14:"C1564022876797";a:2:{s:6:"imgurl";s:76:"https://four.nttrip.cn/diypage/template_img/template_edu02/index/picture.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:7:"picture";}s:14:"M1564023004708";a:5:{s:4:"icon";s:21:"iconfont2 icon-dianpu";s:6:"params";a:13:{s:3:"src";s:74:"https://four.nttrip.cn/diypage/template_img/template_edu02/index/logo2.png";s:5:"title";s:12:"教育资源";s:5:"intro";s:0:"";s:7:"linkurl";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"1";s:13:"backgroundimg";s:52:"/diypage/resource/images/diypage/default/shop_bg.png";s:8:"phonenum";s:0:"";s:5:"style";s:1:"3";s:8:"urltitle";s:6:"更多";}s:5:"style";a:16:{s:3:"pdh";s:2:"10";s:3:"pdz";s:2:"10";s:2:"bg";s:7:"#ffffff";s:2:"mt";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"titlecolor";s:7:"#000000";s:10:"introcolor";s:7:"#ffffff";s:7:"phonebg";s:7:"#ffffff";s:10:"phonecolor";s:7:"#ffffff";s:4:"ljfz";s:2:"15";s:7:"introfz";s:2:"12";s:4:"dpfz";s:2:"16";s:8:"imgwidth";s:2:"25";s:10:"phonenumfz";s:2:"12";s:7:"ljcolor";s:7:"#808080";}s:2:"id";s:4:"logo";s:5:"index";s:3:"NaN";}s:14:"M1564023151635";a:5:{s:4:"icon";s:21:"iconfont2 icon-tupian";s:6:"params";a:9:{s:3:"row";s:1:"2";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"2";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:8:{s:10:"paddingtop";s:1:"6";s:11:"paddingleft";s:1:"6";s:7:"showdot";s:1:"0";s:7:"showbtn";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:1:"0";s:10:"background";s:7:"#ffffff";}s:4:"data";a:6:{s:14:"C1564023151635";a:2:{s:6:"imgurl";s:78:"https://four.nttrip.cn/diypage/template_img/template_edu02/index/picturew1.png";s:7:"linkurl";s:0:"";}s:14:"C1564023151636";a:2:{s:6:"imgurl";s:78:"https://four.nttrip.cn/diypage/template_img/template_edu02/index/picturew2.png";s:7:"linkurl";s:0:"";}s:14:"C1564023151637";a:2:{s:6:"imgurl";s:78:"https://four.nttrip.cn/diypage/template_img/template_edu02/index/picturew3.png";s:7:"linkurl";s:0:"";}s:14:"C1564023151638";a:2:{s:6:"imgurl";s:78:"https://four.nttrip.cn/diypage/template_img/template_edu02/index/picturew4.png";s:7:"linkurl";s:0:"";}s:14:"M1564023194759";a:2:{s:6:"imgurl";s:78:"https://four.nttrip.cn/diypage/template_img/template_edu02/index/picturew5.png";s:7:"linkurl";s:0:"";}s:14:"M1564023195989";a:2:{s:6:"imgurl";s:78:"https://four.nttrip.cn/diypage/template_img/template_edu02/index/picturew6.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:8:"picturew";}}';
            $item1 = unserialize($item1);
            foreach($item1 as $k => &$v){
                if(isset($v['data'])){
                    foreach($v['data'] as $kk => &$vv){
                        if(isset($vv['imgurl']) && strpos($vv['imgurl'],'https://four.nttrip.cn') !== false){
                            $vv['imgurl'] = ROOT_HOST.explode('https://four.nttrip.cn', $vv['imgurl'])[1];
                        }
                        if(isset($vv['thumb']) && strpos($vv['thumb'],'/diypage/resource/images/diypage/default/shop_bg.png') !== false){
                            $vv['thumb'] = ROOT_HOST."/diypage/resource/images/diypage/default/shop_bg.png";
                        }
                    }
                }
            }
            $page1['items'] = serialize($item1);
            $page1['page'] = 'a:7:{s:10:"background";s:7:"#f1f1f1";s:13:"topbackground";s:7:"#fdc20e";s:8:"topcolor";s:1:"1";s:9:"styledata";s:1:"0";s:5:"title";s:6:"首页";s:4:"name";s:6:"首页";s:10:"visitlevel";a:2:{s:6:"member";s:0:"";s:10:"commission";s:0:"";}}';
            $page1['tpl_name'] = "首页";

            $page1_id = Db::name('wd_xcx_diypage')->insertGetId($page1);
            $pageids = $page1_id;

            $is = Db::name('wd_xcx_diypageset')->where('uniacid',$uniacid)->find();
            if(empty($is)){
                $page_set = [
                    'uniacid' => $uniacid,
                    'kp' => ROOT_HOST."/diypage/resource/images/diypage/default/default_start.jpg",
                    'kp_is' => 2,
                    'kp_m' => 2,
                    'tc' => ROOT_HOST."/diypage/resource/images/diypage/default/tcgg.jpg",
                    'tc_is' => 2,
                    'foot_is' => 2
                ];
                Db::name('wd_xcx_diypageset')->insert($page_set);
            }else{
                Db::name('wd_xcx_diypageset')->where('uniacid',$uniacid)->update(array('foot_is' => 2));
            }
            $data = [
                'uniacid' => $uniacid,
                'pageid' => $pageids,
                'template_name' => '教育类模板02',
                'thumb' => "/diypage/template_img/template_edu02/cover.png",
                'status' => '1',
                'create_time' => time()
            ];
            $tplid = Db::name('wd_xcx_diypagetpl')->insertGetId($data);
            if($tplid){
                return $tplid;
            }
        }elseif($id == 'm_edu03'){
            $page1 = [];
            $page1['uniacid'] = $uniacid;
            $page1['index'] = 1;
            $item1 = 'a:22:{s:14:"M1564033791872";a:4:{s:4:"icon";s:22:"iconfont2 icon-sousuo1";s:6:"params";a:7:{s:5:"value";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:12:{s:9:"textalign";s:4:"left";s:10:"background";s:7:"#ffffff";s:2:"bg";s:7:"#f3f4f6";s:12:"borderradius";s:1:"5";s:6:"boxpdh";s:2:"10";s:6:"boxpdz";s:2:"15";s:7:"padding";s:1:"5";s:8:"fontsize";s:2:"13";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"color";s:7:"#a1a1a1";}s:2:"id";s:3:"ssk";}s:14:"M1564033794295";a:5:{s:4:"icon";s:28:"iconfont2 icon-tuoyuankaobei";s:6:"params";a:10:{s:5:"totle";s:1:"2";s:8:"navstyle";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:9:"navstyle2";s:1:"0";s:4:"imgh";s:3:"160";}s:5:"style";a:18:{s:8:"dotstyle";s:5:"round";s:8:"dotalign";s:4:"left";s:10:"paddingtop";s:1:"0";s:11:"paddingleft";s:1:"0";s:10:"background";s:7:"#ffffff";s:13:"backgroundall";s:7:"#ffffff";s:9:"leftright";s:1:"5";s:6:"bottom";s:1:"5";s:7:"opacity";s:1:"0";s:10:"text_color";s:4:"#fff";s:2:"bg";s:7:"#000000";s:9:"jsq_color";s:3:"red";s:3:"pdh";s:1:"0";s:3:"pdw";s:1:"0";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"speed";s:1:"5";}s:4:"data";a:1:{s:14:"C1564033794295";a:4:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_edu03/index/banner.jpg";s:7:"linkurl";s:0:"";s:6:"single";s:1:"1";s:4:"text";s:12:"文字描述";}}s:2:"id";s:6:"banner";}s:14:"M1564033993832";a:5:{s:4:"icon";s:22:"iconfont2 icon-gonggao";s:6:"params";a:12:{s:7:"iconurl";s:14:"icon-c-gonggao";s:10:"noticedata";s:1:"0";s:5:"speed";s:1:"4";s:9:"noticenum";s:1:"5";s:8:"navstyle";s:1:"1";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:8:"sourceid";s:0:"";}s:5:"style";a:9:{s:10:"background";s:7:"#ffffff";s:9:"iconcolor";s:7:"#fd5454";s:5:"color";s:7:"#666666";s:11:"bordercolor";s:7:"#e2e2e2";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";}s:4:"data";a:2:{s:14:"C1564033993832";a:2:{s:5:"title";s:42:"这里是第一条自定义公告的标题";s:7:"linkurl";s:0:"";}s:14:"C1564033993833";a:2:{s:5:"title";s:42:"这里是第二条自定义公告的标题";s:7:"linkurl";s:0:"";}}s:2:"id";s:6:"notice";}s:14:"M1564034042785";a:5:{s:4:"icon";s:22:"iconfont2 icon-anniuzu";s:6:"params";a:8:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"picicon";s:1:"1";s:8:"textshow";s:1:"1";}s:5:"style";a:14:{s:8:"navstyle";s:0:"";s:10:"background";s:7:"#ffffff";s:6:"rownum";s:1:"5";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"8";s:7:"showdot";s:1:"1";s:7:"padding";s:1:"5";s:11:"paddingleft";s:2:"10";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:6:"iconfz";s:2:"14";s:9:"iconcolor";s:7:"#434343";s:8:"imgwidth";s:2:"41";}s:4:"data";a:5:{s:14:"C1564034042785";a:5:{s:6:"imgurl";s:80:"https://four.nttrip.cn/diypage/template_img/template_edu03/index/picturew1_1.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"编程语言";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1564034042786";a:5:{s:6:"imgurl";s:80:"https://four.nttrip.cn/diypage/template_img/template_edu03/index/picturew1_2.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"办公软件";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1564034042787";a:5:{s:6:"imgurl";s:80:"https://four.nttrip.cn/diypage/template_img/template_edu03/index/picturew1_3.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"英文外语";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1564034042788";a:5:{s:6:"imgurl";s:80:"https://four.nttrip.cn/diypage/template_img/template_edu03/index/picturew1_4.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"市场营销";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"M1564034357026";a:5:{s:6:"imgurl";s:80:"https://four.nttrip.cn/diypage/template_img/template_edu03/index/picturew1_5.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"设计制作";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}}s:2:"id";s:4:"menu";}s:14:"M1564034697552";a:5:{s:4:"icon";s:19:"iconfont2 icon-zutu";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:6:{s:10:"paddingtop";s:1:"0";s:11:"paddingleft";s:1:"0";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"background";s:7:"#ffffff";}s:4:"data";a:1:{s:14:"C1564034697552";a:2:{s:6:"imgurl";s:77:"https://four.nttrip.cn/diypage/template_img/template_edu03/index/picture1.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:7:"picture";}s:14:"M1564034728190";a:4:{s:4:"icon";s:32:"iconfont2 icon-liebiao_biaotilan";s:6:"params";a:9:{s:5:"title";s:12:"热门栏目";s:6:"title2";s:18:"充实你的生活";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:5:"style";s:1:"1";}s:5:"style";a:11:{s:10:"background";s:7:"#ffffff";s:6:"colorz";s:7:"#434343";s:6:"colorf";s:7:"#838383";s:9:"linecolor";s:7:"#ffffff";s:9:"fontsizez";s:2:"18";s:9:"fontsizef";s:2:"13";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:1:"0";}s:2:"id";s:6:"title2";}s:14:"M1564034887438";a:6:{s:4:"icon";s:22:"iconfont2 icon-anniuzu";s:6:"params";a:8:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"picicon";s:1:"1";s:8:"textshow";s:1:"1";}s:5:"style";a:14:{s:8:"navstyle";s:6:"radius";s:10:"background";s:7:"#ffffff";s:6:"rownum";s:1:"2";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"8";s:7:"showdot";s:1:"1";s:7:"padding";s:2:"12";s:11:"paddingleft";s:2:"10";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:6:"iconfz";s:2:"14";s:9:"iconcolor";s:7:"#434343";s:8:"imgwidth";s:3:"165";}s:4:"data";a:4:{s:14:"C1564034887438";a:5:{s:6:"imgurl";s:80:"https://four.nttrip.cn/diypage/template_img/template_edu03/index/picturew2_1.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"投资理财";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1564034887439";a:5:{s:6:"imgurl";s:80:"https://four.nttrip.cn/diypage/template_img/template_edu03/index/picturew2_2.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"运动健康";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1564034887440";a:5:{s:6:"imgurl";s:80:"https://four.nttrip.cn/diypage/template_img/template_edu03/index/picturew2_3.png";s:7:"linkurl";s:0:"";s:4:"text";s:6:"日语";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1564034887441";a:5:{s:6:"imgurl";s:80:"https://four.nttrip.cn/diypage/template_img/template_edu03/index/picturew2_4.png";s:7:"linkurl";s:0:"";s:4:"text";s:8:"UI设计";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}}s:2:"id";s:4:"menu";s:5:"index";s:3:"NaN";}s:14:"M1564035110319";a:4:{s:4:"icon";s:20:"iconfont2 icon-anniu";s:6:"params";a:8:{s:4:"icon";s:9:"icon-home";s:5:"title";s:12:"查看更多";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:12:{s:11:"paddingleft";s:3:"130";s:10:"paddingtop";s:2:"10";s:3:"pdz";s:1:"5";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"fs";s:2:"16";s:10:"background";s:7:"#ffffff";s:11:"bordercolor";s:7:"#2F74FD";s:5:"btnbg";s:7:"#ffffff";s:5:"color";s:7:"#2f74fd";s:12:"borderradius";s:2:"10";}s:2:"id";s:5:"anniu";}s:14:"M1564035209838";a:5:{s:4:"icon";s:32:"iconfont2 icon-liebiao_biaotilan";s:6:"params";a:9:{s:5:"title";s:12:"精选推荐";s:6:"title2";s:21:"发现学习的乐趣";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:5:"style";s:1:"1";}s:5:"style";a:11:{s:10:"background";s:7:"#ffffff";s:6:"colorz";s:7:"#434343";s:6:"colorf";s:7:"#838383";s:9:"linecolor";s:7:"#ffffff";s:9:"fontsizez";s:2:"18";s:9:"fontsizef";s:2:"13";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:2:"15";}s:2:"id";s:6:"title2";s:5:"index";s:3:"NaN";}s:14:"M1564035367534";a:6:{s:4:"icon";s:19:"iconfont2 icon-zutu";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:6:{s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"background";s:7:"#ffffff";}s:4:"data";a:1:{s:14:"C1564035367534";a:2:{s:6:"imgurl";s:77:"https://four.nttrip.cn/diypage/template_img/template_edu03/index/picture2.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:7:"picture";s:5:"index";s:3:"NaN";}s:14:"M1564035398079";a:4:{s:4:"icon";s:32:"iconfont2 icon-liebiao_biaotilan";s:6:"params";a:8:{s:5:"title";s:12:"前端开发";s:4:"icon";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:9:{s:10:"background";s:7:"#ffffff";s:5:"color";s:7:"#000000";s:9:"textalign";s:4:"left";s:8:"fontsize";s:2:"18";s:10:"paddingtop";s:1:"3";s:11:"paddingleft";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:1:"0";}s:2:"id";s:5:"title";}s:14:"M1564035449463";a:5:{s:4:"icon";s:32:"iconfont2 icon-liebiao_biaotilan";s:6:"params";a:8:{s:5:"title";s:39:"HTML/CSS、JavaScript、Web全栈开发";s:4:"icon";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:9:{s:10:"background";s:7:"#ffffff";s:5:"color";s:7:"#666666";s:9:"textalign";s:4:"left";s:8:"fontsize";s:2:"14";s:10:"paddingtop";s:1:"0";s:11:"paddingleft";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:1:"0";}s:2:"id";s:5:"title";s:5:"index";s:3:"NaN";}s:14:"M1564035577936";a:5:{s:4:"icon";s:24:"iconfont2 icon-xiankuang";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:5:{s:6:"height";s:1:"1";s:10:"background";s:7:"#ffffff";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";}s:2:"id";s:5:"blank";s:5:"index";s:3:"NaN";}s:14:"M1564035568423";a:6:{s:4:"icon";s:19:"iconfont2 icon-zutu";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:6:{s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"background";s:7:"#ffffff";}s:4:"data";a:1:{s:14:"C1564035568423";a:2:{s:6:"imgurl";s:77:"https://four.nttrip.cn/diypage/template_img/template_edu03/index/picture3.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:7:"picture";s:5:"index";s:3:"NaN";}s:14:"M1564035615510";a:5:{s:4:"icon";s:32:"iconfont2 icon-liebiao_biaotilan";s:6:"params";a:8:{s:5:"title";s:12:"出国留学";s:4:"icon";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:9:{s:10:"background";s:7:"#ffffff";s:5:"color";s:7:"#000000";s:9:"textalign";s:4:"left";s:8:"fontsize";s:2:"18";s:10:"paddingtop";s:1:"3";s:11:"paddingleft";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:1:"0";}s:2:"id";s:5:"title";s:5:"index";s:3:"NaN";}s:14:"M1564035616175";a:5:{s:4:"icon";s:32:"iconfont2 icon-liebiao_biaotilan";s:6:"params";a:8:{s:5:"title";s:48:"雅思、托福、研究生留学、留学指导";s:4:"icon";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:9:{s:10:"background";s:7:"#ffffff";s:5:"color";s:7:"#666666";s:9:"textalign";s:4:"left";s:8:"fontsize";s:2:"14";s:10:"paddingtop";s:1:"0";s:11:"paddingleft";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:1:"0";}s:2:"id";s:5:"title";s:5:"index";s:3:"NaN";}s:14:"M1564035689645";a:5:{s:4:"icon";s:24:"iconfont2 icon-xiankuang";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:5:{s:6:"height";s:1:"1";s:10:"background";s:7:"#ffffff";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";}s:2:"id";s:5:"blank";s:5:"index";s:3:"NaN";}s:14:"M1564035694318";a:6:{s:4:"icon";s:19:"iconfont2 icon-zutu";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:6:{s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"background";s:7:"#ffffff";}s:4:"data";a:1:{s:14:"C1564035694318";a:2:{s:6:"imgurl";s:77:"https://four.nttrip.cn/diypage/template_img/template_edu03/index/picture4.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:7:"picture";s:5:"index";s:3:"NaN";}s:14:"M1564035709189";a:5:{s:4:"icon";s:32:"iconfont2 icon-liebiao_biaotilan";s:6:"params";a:8:{s:5:"title";s:12:"公职考试";s:4:"icon";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:9:{s:10:"background";s:7:"#ffffff";s:5:"color";s:7:"#000000";s:9:"textalign";s:4:"left";s:8:"fontsize";s:2:"18";s:10:"paddingtop";s:1:"3";s:11:"paddingleft";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:1:"0";}s:2:"id";s:5:"title";s:5:"index";s:3:"NaN";}s:14:"M1564035709653";a:5:{s:4:"icon";s:32:"iconfont2 icon-liebiao_biaotilan";s:6:"params";a:8:{s:5:"title";s:39:"公务员、事业单位、教师考试";s:4:"icon";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:9:{s:10:"background";s:7:"#ffffff";s:5:"color";s:7:"#666666";s:9:"textalign";s:4:"left";s:8:"fontsize";s:2:"14";s:10:"paddingtop";s:1:"0";s:11:"paddingleft";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:1:"0";}s:2:"id";s:5:"title";s:5:"index";s:3:"NaN";}s:14:"M1564035804430";a:5:{s:4:"icon";s:24:"iconfont2 icon-xiankuang";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:5:{s:6:"height";s:1:"1";s:10:"background";s:7:"#ffffff";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";}s:2:"id";s:5:"blank";s:5:"index";s:3:"NaN";}s:14:"M1564035793206";a:5:{s:4:"icon";s:20:"iconfont2 icon-anniu";s:6:"params";a:8:{s:4:"icon";s:9:"icon-home";s:5:"title";s:12:"查看更多";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:12:{s:11:"paddingleft";s:3:"130";s:10:"paddingtop";s:2:"10";s:3:"pdz";s:1:"5";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"fs";s:2:"16";s:10:"background";s:7:"#ffffff";s:11:"bordercolor";s:7:"#2F74FD";s:5:"btnbg";s:7:"#ffffff";s:5:"color";s:7:"#2f74fd";s:12:"borderradius";s:2:"10";}s:2:"id";s:5:"anniu";s:5:"index";s:3:"NaN";}}';
            $item1 = unserialize($item1);
            foreach($item1 as $k => &$v){
                if(isset($v['data'])){
                    foreach($v['data'] as $kk => &$vv){
                        if(isset($vv['imgurl']) && strpos($vv['imgurl'],'https://four.nttrip.cn') !== false){
                            $vv['imgurl'] = ROOT_HOST.explode('https://four.nttrip.cn', $vv['imgurl'])[1];
                        }
                    }
                }
            }
            $page1['items'] = serialize($item1);
            $page1['page'] = 'a:7:{s:10:"background";s:7:"#f1f1f1";s:13:"topbackground";s:7:"#ffffff";s:8:"topcolor";s:1:"1";s:9:"styledata";s:1:"0";s:5:"title";s:6:"首页";s:4:"name";s:6:"首页";s:10:"visitlevel";a:2:{s:6:"member";s:0:"";s:10:"commission";s:0:"";}}';
            $page1['tpl_name'] = "首页";

            $page1_id = Db::name('wd_xcx_diypage')->insertGetId($page1);
            $pageids = $page1_id;

            $is = Db::name('wd_xcx_diypageset')->where('uniacid',$uniacid)->find();
            if(empty($is)){
                $page_set = [
                    'uniacid' => $uniacid,
                    'kp' => ROOT_HOST."/diypage/resource/images/diypage/default/default_start.jpg",
                    'kp_is' => 2,
                    'kp_m' => 2,
                    'tc' => ROOT_HOST."/diypage/resource/images/diypage/default/tcgg.jpg",
                    'tc_is' => 2,
                    'foot_is' => 2
                ];
                Db::name('wd_xcx_diypageset')->insert($page_set);
            }else{
                Db::name('wd_xcx_diypageset')->where('uniacid',$uniacid)->update(array('foot_is' => 2));
            }
            $data = [
                'uniacid' => $uniacid,
                'pageid' => $pageids,
                'template_name' => '教育类模板03',
                'thumb' => "/diypage/template_img/template_edu03/cover.png",
                'status' => '1',
                'create_time' => time()
            ];
            $tplid = Db::name('wd_xcx_diypagetpl')->insertGetId($data);
            if($tplid){
                return $tplid;
            }
        }elseif($id == 'm_edu04'){
            $page1 = [];
            $page1['uniacid'] = $uniacid;
            $page1['index'] = 1;
            $item1 = 'a:12:{s:14:"M1564386034968";a:4:{s:4:"icon";s:22:"iconfont2 icon-sousuo1";s:6:"params";a:7:{s:5:"value";s:0:"";s:9:"styledata";s:1:"1";s:6:"repeat";s:9:"no-repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"1";s:13:"backgroundimg";s:75:"https://four.nttrip.cn/diypage/template_img/template_edu04/index/ssk_bg.png";}s:5:"style";a:12:{s:9:"textalign";s:6:"center";s:10:"background";s:7:"#ffffff";s:2:"bg";s:7:"#e1f4ff";s:12:"borderradius";s:2:"20";s:6:"boxpdh";s:2:"10";s:6:"boxpdz";s:2:"15";s:7:"padding";s:1:"5";s:8:"fontsize";s:2:"13";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"color";s:7:"#ffffff";}s:2:"id";s:3:"ssk";}s:14:"M1564386037214";a:5:{s:4:"icon";s:28:"iconfont2 icon-tuoyuankaobei";s:6:"params";a:10:{s:5:"totle";s:1:"2";s:8:"navstyle";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:9:"navstyle2";s:1:"0";s:4:"imgh";s:3:"200";}s:5:"style";a:18:{s:8:"dotstyle";s:5:"round";s:8:"dotalign";s:4:"left";s:10:"paddingtop";s:1:"0";s:11:"paddingleft";s:1:"0";s:10:"background";s:7:"#ffffff";s:13:"backgroundall";s:7:"#ffffff";s:9:"leftright";s:1:"5";s:6:"bottom";s:2:"10";s:7:"opacity";s:3:"0.5";s:10:"text_color";s:4:"#fff";s:2:"bg";s:7:"#000000";s:9:"jsq_color";s:3:"red";s:3:"pdh";s:1:"0";s:3:"pdw";s:1:"0";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"speed";s:1:"5";}s:4:"data";a:2:{s:14:"C1564386037214";a:4:{s:6:"imgurl";s:78:"https://four.nttrip.cn/diypage/template_img/template_edu04/index/banner1_1.jpg";s:7:"linkurl";s:0:"";s:6:"single";s:1:"1";s:4:"text";s:12:"文字描述";}s:14:"C1564386037215";a:4:{s:6:"imgurl";s:78:"https://four.nttrip.cn/diypage/template_img/template_edu04/index/banner1_2.png";s:7:"linkurl";s:0:"";s:6:"single";s:1:"2";s:4:"text";s:12:"文字描述";}}s:2:"id";s:6:"banner";}s:14:"M1564388888598";a:5:{s:4:"icon";s:22:"iconfont2 icon-gonggao";s:6:"params";a:12:{s:7:"iconurl";s:15:"icon-x-gonggao4";s:10:"noticedata";s:1:"0";s:5:"speed";s:1:"4";s:9:"noticenum";s:1:"5";s:8:"navstyle";s:1:"1";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:8:"sourceid";s:0:"";}s:5:"style";a:9:{s:10:"background";s:7:"#ffffff";s:9:"iconcolor";s:7:"#fd5454";s:5:"color";s:7:"#666666";s:11:"bordercolor";s:7:"#e2e2e2";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";}s:4:"data";a:2:{s:14:"C1564388888598";a:2:{s:5:"title";s:42:"这里是第一条自定义公告的标题";s:7:"linkurl";s:0:"";}s:14:"C1564388888599";a:2:{s:5:"title";s:42:"这里是第二条自定义公告的标题";s:7:"linkurl";s:0:"";}}s:2:"id";s:6:"notice";}s:14:"M1564388918232";a:4:{s:4:"icon";s:32:"iconfont2 icon-liebiao_biaotilan";s:6:"params";a:9:{s:5:"title";s:12:"校内通道";s:6:"title2";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:5:"style";s:1:"1";}s:5:"style";a:11:{s:10:"background";s:7:"#ffffff";s:6:"colorz";s:7:"#000000";s:6:"colorf";s:7:"#838383";s:9:"linecolor";s:7:"#ffffff";s:9:"fontsizez";s:2:"18";s:9:"fontsizef";s:1:"9";s:10:"paddingtop";s:1:"5";s:11:"paddingleft";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:2:"10";}s:2:"id";s:6:"title2";}s:14:"M1564389005328";a:6:{s:4:"icon";s:28:"iconfont2 icon-tuoyuankaobei";s:6:"params";a:10:{s:5:"totle";s:1:"2";s:8:"navstyle";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:9:"navstyle2";s:1:"0";s:4:"imgh";s:3:"180";}s:5:"style";a:18:{s:8:"dotstyle";s:5:"round";s:8:"dotalign";s:4:"left";s:10:"paddingtop";s:1:"6";s:11:"paddingleft";s:2:"30";s:10:"background";s:7:"#ffffff";s:13:"backgroundall";s:7:"#ffffff";s:9:"leftright";s:1:"5";s:6:"bottom";s:2:"10";s:7:"opacity";s:3:"0.5";s:10:"text_color";s:4:"#fff";s:2:"bg";s:7:"#000000";s:9:"jsq_color";s:3:"red";s:3:"pdh";s:1:"0";s:3:"pdw";s:1:"0";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"speed";s:1:"5";}s:4:"data";a:4:{s:14:"C1564389005328";a:4:{s:6:"imgurl";s:78:"https://four.nttrip.cn/diypage/template_img/template_edu04/index/banner2_1.png";s:7:"linkurl";s:0:"";s:6:"single";s:1:"1";s:4:"text";s:12:"文字描述";}s:14:"C1564389005329";a:4:{s:6:"imgurl";s:78:"https://four.nttrip.cn/diypage/template_img/template_edu04/index/banner2_2.png";s:7:"linkurl";s:0:"";s:6:"single";s:1:"2";s:4:"text";s:12:"文字描述";}s:14:"M1564389067515";a:4:{s:6:"imgurl";s:78:"https://four.nttrip.cn/diypage/template_img/template_edu04/index/banner2_3.png";s:7:"linkurl";s:0:"";s:6:"single";s:1:"1";s:4:"text";s:12:"文字描述";}s:14:"M1564389068890";a:4:{s:6:"imgurl";s:78:"https://four.nttrip.cn/diypage/template_img/template_edu04/index/banner2_4.png";s:7:"linkurl";s:0:"";s:6:"single";s:1:"1";s:4:"text";s:12:"文字描述";}}s:2:"id";s:6:"banner";s:5:"index";s:3:"NaN";}s:14:"M1564389717384";a:5:{s:4:"icon";s:32:"iconfont2 icon-liebiao_biaotilan";s:6:"params";a:9:{s:5:"title";s:12:"精挑细选";s:6:"title2";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:5:"style";s:1:"1";}s:5:"style";a:11:{s:10:"background";s:7:"#ffffff";s:6:"colorz";s:7:"#000000";s:6:"colorf";s:7:"#838383";s:9:"linecolor";s:7:"#ffffff";s:9:"fontsizez";s:2:"18";s:9:"fontsizef";s:1:"9";s:10:"paddingtop";s:1:"5";s:11:"paddingleft";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:2:"10";}s:2:"id";s:6:"title2";s:5:"index";s:3:"NaN";}s:14:"M1564389767206";a:5:{s:4:"icon";s:21:"iconfont2 icon-tupian";s:6:"params";a:9:{s:3:"row";s:1:"1";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"2";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:8:{s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:7:"showdot";s:1:"0";s:7:"showbtn";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:1:"0";s:10:"background";s:7:"#ffffff";}s:4:"data";a:3:{s:14:"C1564389767206";a:2:{s:6:"imgurl";s:78:"https://four.nttrip.cn/diypage/template_img/template_edu04/index/picturew1.png";s:7:"linkurl";s:0:"";}s:14:"C1564389767207";a:2:{s:6:"imgurl";s:78:"https://four.nttrip.cn/diypage/template_img/template_edu04/index/picturew2.png";s:7:"linkurl";s:0:"";}s:14:"C1564389767208";a:2:{s:6:"imgurl";s:78:"https://four.nttrip.cn/diypage/template_img/template_edu04/index/picturew3.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:8:"picturew";}s:14:"M1564390396118";a:5:{s:4:"icon";s:19:"iconfont2 icon-zutu";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:6:{s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"background";s:7:"#f1f1f1";}s:4:"data";a:1:{s:14:"C1564390396118";a:2:{s:6:"imgurl";s:76:"https://four.nttrip.cn/diypage/template_img/template_edu04/index/picture.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:7:"picture";}s:14:"M1564389903144";a:5:{s:4:"icon";s:32:"iconfont2 icon-liebiao_biaotilan";s:6:"params";a:9:{s:5:"title";s:12:"每日推荐";s:6:"title2";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:5:"style";s:1:"1";}s:5:"style";a:11:{s:10:"background";s:7:"#ffffff";s:6:"colorz";s:7:"#000000";s:6:"colorf";s:7:"#838383";s:9:"linecolor";s:7:"#ffffff";s:9:"fontsizez";s:2:"18";s:9:"fontsizef";s:1:"9";s:10:"paddingtop";s:1:"6";s:11:"paddingleft";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:1:"0";}s:2:"id";s:6:"title2";s:5:"index";s:3:"NaN";}s:14:"M1564390163682";a:5:{s:4:"icon";s:22:"iconfont2 icon-chanpin";s:6:"params";a:31:{s:11:"goodsscroll";s:1:"0";s:9:"showtitle";s:1:"1";s:9:"showprice";s:1:"1";s:7:"showtag";s:1:"0";s:9:"goodsdata";s:1:"1";s:6:"cateid";s:0:"";s:8:"catename";s:0:"";s:7:"groupid";s:0:"";s:9:"groupname";s:0:"";s:9:"goodssort";s:1:"0";s:8:"goodsnum";s:1:"6";s:8:"showicon";s:1:"0";s:12:"iconposition";s:8:"left top";s:12:"productprice";s:1:"1";s:16:"showproductprice";s:1:"0";s:9:"showsales";s:1:"1";s:16:"productpricetext";s:6:"原价";s:9:"salestext";s:6:"销量";s:16:"productpriceline";s:1:"0";s:7:"saleout";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"imgh_is";s:1:"1";s:4:"imgh";s:3:"100";s:7:"con_key";s:1:"1";s:8:"con_type";s:1:"1";s:8:"sourceid";s:0:"";}s:5:"style";a:20:{s:10:"background";s:7:"#ffffff";s:9:"liststyle";s:5:"block";s:8:"buystyle";s:0:"";s:9:"goodsicon";s:9:"recommand";s:9:"iconstyle";s:8:"triangle";s:10:"pricecolor";s:7:"#ff5555";s:17:"productpricecolor";s:7:"#999999";s:14:"iconpaddingtop";s:1:"0";s:15:"iconpaddingleft";s:1:"0";s:11:"buybtncolor";s:7:"#ff5555";s:8:"iconzoom";s:2:"50";s:10:"titlecolor";s:7:"#000000";s:13:"tagbackground";s:7:"#fe5455";s:10:"salescolor";s:7:"#999999";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"paddingtop";s:1:"5";s:11:"paddingleft";s:2:"10";s:8:"showtype";s:1:"0";}s:4:"data";a:4:{s:14:"C1564390163682";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:3:"des";s:21:"这里是产品描述";}s:14:"C1564390163683";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1564390163684";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1564390163685";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}}s:2:"id";s:5:"goods";}s:14:"M1564390340445";a:5:{s:4:"icon";s:32:"iconfont2 icon-liebiao_biaotilan";s:6:"params";a:9:{s:5:"title";s:12:"校园快讯";s:6:"title2";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:5:"style";s:1:"1";}s:5:"style";a:11:{s:10:"background";s:7:"#ffffff";s:6:"colorz";s:7:"#000000";s:6:"colorf";s:7:"#838383";s:9:"linecolor";s:7:"#ffffff";s:9:"fontsizez";s:2:"18";s:9:"fontsizef";s:1:"9";s:10:"paddingtop";s:1:"5";s:11:"paddingleft";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:2:"10";}s:2:"id";s:6:"title2";s:5:"index";s:3:"NaN";}s:14:"M1564390387918";a:5:{s:4:"icon";s:23:"iconfont2 icon-wenzhang";s:6:"params";a:19:{s:9:"showstyle";s:4:"row1";s:7:"newsnum";s:1:"3";s:8:"newsdata";s:1:"0";s:5:"title";s:21:"请选择文章分类";s:7:"titleid";s:1:"0";s:8:"navstyle";s:1:"1";s:5:"show1";s:1:"1";s:5:"show2";s:1:"1";s:5:"show3";s:1:"1";s:5:"show4";s:1:"1";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"con_key";s:1:"1";s:8:"con_type";s:1:"1";s:8:"sourceid";s:0:"";}s:5:"style";a:11:{s:10:"background";s:4:"#fff";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:12:"marginbottom";s:2:"10";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"color";s:7:"#434343";s:6:"radius";s:1:"0";s:4:"pich";s:1:"1";s:8:"showtype";s:1:"0";}s:4:"data";a:3:{s:14:"C1564390387918";a:8:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/3.jpg";s:2:"id";s:0:"";s:5:"title";s:15:"这里是标题";s:4:"time";s:16:"2017年10月1日";s:5:"intro";s:21:"简介1简介1简介1";s:3:"ydl";s:2:"10";s:3:"dzl";s:2:"10";s:3:"pll";s:2:"10";}s:14:"C1564390387919";a:8:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/3.jpg";s:2:"id";s:0:"";s:5:"title";s:15:"这里是标题";s:4:"time";s:16:"2017年10月1日";s:5:"intro";s:15:"这里是简介";s:3:"ydl";s:2:"10";s:3:"dzl";s:2:"10";s:3:"pll";s:2:"10";}s:14:"C1564390387920";a:8:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/3.jpg";s:2:"id";s:0:"";s:5:"title";s:15:"这里是标题";s:4:"time";s:16:"2017年10月1日";s:5:"intro";s:15:"这里是简介";s:3:"ydl";s:2:"10";s:3:"dzl";s:2:"10";s:3:"pll";s:2:"10";}}s:2:"id";s:8:"listdesc";}}';
            $item1 = unserialize($item1);
            foreach($item1 as $k => &$v){
                if(isset($v['data'])){
                    foreach($v['data'] as $kk => &$vv){
                        if(isset($vv['imgurl']) && strpos($vv['imgurl'],'https://four.nttrip.cn') !== false){
                            $vv['imgurl'] = ROOT_HOST.explode('https://four.nttrip.cn', $vv['imgurl'])[1];
                        }
                        if(isset($vv['thumb']) && strpos($vv['thumb'],'/diypage/resource/images/diypage/default/2.jpg') !== false){
                            $vv['thumb'] = ROOT_HOST."/diypage/resource/images/diypage/default/2.jpg";
                        }
                        if(isset($vv['thumb']) && strpos($vv['thumb'],'/diypage/resource/images/diypage/default/3.jpg') !== false){
                            $vv['thumb'] = ROOT_HOST."/diypage/resource/images/diypage/default/3.jpg";
                        }
                    }
                }
            }
            $page1['items'] = serialize($item1);
            $page1['page'] = 'a:7:{s:10:"background";s:7:"#f1f1f1";s:13:"topbackground";s:7:"#63c4ff";s:8:"topcolor";s:1:"2";s:9:"styledata";s:1:"0";s:5:"title";s:6:"首页";s:4:"name";s:6:"首页";s:10:"visitlevel";a:2:{s:6:"member";s:0:"";s:10:"commission";s:0:"";}}';
            $page1['tpl_name'] = "首页";

            $page1_id = Db::name('wd_xcx_diypage')->insertGetId($page1);
            $pageids = $page1_id;

            $is = Db::name('wd_xcx_diypageset')->where('uniacid',$uniacid)->find();
            if(empty($is)){
                $page_set = [
                    'uniacid' => $uniacid,
                    'kp' => ROOT_HOST."/diypage/resource/images/diypage/default/default_start.jpg",
                    'kp_is' => 2,
                    'kp_m' => 2,
                    'tc' => ROOT_HOST."/diypage/resource/images/diypage/default/tcgg.jpg",
                    'tc_is' => 2,
                    'foot_is' => 2
                ];
                Db::name('wd_xcx_diypageset')->insert($page_set);
            }else{
                Db::name('wd_xcx_diypageset')->where('uniacid',$uniacid)->update(array('foot_is' => 2));
            }
            $data = [
                'uniacid' => $uniacid,
                'pageid' => $pageids,
                'template_name' => '教育类模板04',
                'thumb' => "/diypage/template_img/template_edu04/cover.png",
                'status' => '1',
                'create_time' => time()
            ];
            $tplid = Db::name('wd_xcx_diypagetpl')->insertGetId($data);
            if($tplid){
                return $tplid;
            }
        }elseif($id == 'm_food'){
            $page1 = [];
            $page1['uniacid'] = $uniacid;
            $page1['index'] = 1;
            $item1 = 'a:9:{s:14:"M1545274837699";a:6:{s:4:"icon";s:19:"iconfont2 icon-zutu";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:6:{s:10:"paddingtop";s:1:"0";s:11:"paddingleft";s:1:"0";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"background";s:7:"#ffffff";}s:4:"data";a:1:{s:14:"C1545274837699";a:2:{s:6:"imgurl";s:68:"https://four.nttrip.cn/template_img/template_food/index/picture1.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:7:"picture";s:5:"index";s:3:"NaN";}s:14:"M1545275020421";a:5:{s:4:"icon";s:19:"iconfont2 icon-zutu";s:6:"params";a:6:{s:9:"styledata";s:1:"1";s:6:"repeat";s:9:"no-repeat";s:9:"positionx";s:5:"right";s:9:"positiony";s:3:"top";s:4:"size";s:1:"1";s:13:"backgroundimg";s:79:"https://four.nttrip.cn/diypage/template_img/template_food/index/picture2_bg.jpg";}s:5:"style";a:6:{s:10:"paddingtop";s:1:"0";s:11:"paddingleft";s:2:"10";s:2:"mt";s:1:"0";s:5:"sizew";s:3:"100";s:5:"sizeh";s:3:"100";s:10:"background";s:7:"#ffffff";}s:4:"data";a:1:{s:14:"C1545275020421";a:3:{s:6:"imgurl";s:68:"https://four.nttrip.cn/template_img/template_food/index/picture2.png";s:7:"linkurl";s:25:"/sudu8_page/coupon/coupon";s:8:"linktype";s:4:"page";}}s:2:"id";s:7:"picture";}s:14:"M1545275145163";a:5:{s:4:"icon";s:21:"iconfont2 icon-tupian";s:6:"params";a:9:{s:3:"row";s:1:"2";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"2";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:8:{s:10:"paddingtop";s:1:"5";s:11:"paddingleft";s:2:"10";s:7:"showdot";s:1:"0";s:7:"showbtn";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:1:"9";s:10:"background";s:7:"#f1f1f1";}s:4:"data";a:2:{s:14:"C1545275145163";a:3:{s:6:"imgurl";s:71:"https://four.nttrip.cn/template_img/template_food/index/picturew1-1.png";s:7:"linkurl";s:33:"/sudu8_page_plugin_food/food/food";s:8:"linktype";s:4:"page";}s:14:"C1545275145164";a:3:{s:6:"imgurl";s:71:"https://four.nttrip.cn/template_img/template_food/index/picturew1-2.png";s:7:"linkurl";s:27:"/sudu8_page/shoppay/shoppay";s:8:"linktype";s:4:"page";}}s:2:"id";s:8:"picturew";}s:14:"M1545275242490";a:5:{s:4:"icon";s:23:"iconfont2 icon-daohang1";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:10:{s:9:"margintop";s:2:"10";s:10:"background";s:7:"#ffffff";s:9:"iconcolor";s:7:"#999999";s:9:"textcolor";s:7:"#333333";s:11:"remarkcolor";s:7:"#888888";s:5:"sizew";s:2:"20";s:11:"paddingleft";s:2:"15";s:7:"padding";s:1:"4";s:5:"sizeh";s:2:"20";s:9:"linecolor";s:7:"#ffffff";}s:4:"data";a:1:{s:14:"C1545275242490";a:5:{s:4:"text";s:12:"今日特惠";s:7:"linkurl";s:0:"";s:9:"iconclass";s:0:"";s:6:"remark";s:4:"more";s:6:"dotnum";s:0:"";}}s:2:"id";s:8:"listmenu";}s:14:"M1545275327781";a:6:{s:4:"icon";s:21:"iconfont2 icon-tupian";s:6:"params";a:9:{s:3:"row";s:1:"1";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"2";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:8:{s:10:"paddingtop";s:1:"5";s:11:"paddingleft";s:2:"10";s:7:"showdot";s:1:"0";s:7:"showbtn";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:1:"0";s:10:"background";s:7:"#ffffff";}s:4:"data";a:3:{s:14:"C1545275327781";a:2:{s:6:"imgurl";s:71:"https://four.nttrip.cn/template_img/template_food/index/picturew2-1.png";s:7:"linkurl";s:0:"";}s:14:"C1545275327782";a:2:{s:6:"imgurl";s:71:"https://four.nttrip.cn/template_img/template_food/index/picturew2-2.png";s:7:"linkurl";s:0:"";}s:14:"C1545275327783";a:2:{s:6:"imgurl";s:71:"https://four.nttrip.cn/template_img/template_food/index/picturew2-3.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:8:"picturew";s:5:"index";s:3:"NaN";}s:14:"M1545275449068";a:6:{s:4:"icon";s:23:"iconfont2 icon-daohang1";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:10:{s:9:"margintop";s:1:"6";s:10:"background";s:7:"#ffffff";s:9:"iconcolor";s:7:"#999999";s:9:"textcolor";s:7:"#666666";s:11:"remarkcolor";s:7:"#888888";s:5:"sizew";s:2:"20";s:11:"paddingleft";s:2:"15";s:7:"padding";s:1:"4";s:5:"sizeh";s:2:"20";s:9:"linecolor";s:7:"#ffffff";}s:4:"data";a:1:{s:14:"C1545275449068";a:5:{s:4:"text";s:12:"招牌推荐";s:7:"linkurl";s:0:"";s:9:"iconclass";s:0:"";s:6:"remark";s:4:"more";s:6:"dotnum";s:0:"";}}s:2:"id";s:8:"listmenu";s:5:"index";s:3:"NaN";}s:14:"M1545275581762";a:6:{s:4:"icon";s:21:"iconfont2 icon-tupian";s:6:"params";a:9:{s:3:"row";s:1:"3";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"2";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:8:{s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:7:"showdot";s:1:"0";s:7:"showbtn";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:1:"0";s:10:"background";s:7:"#ffffff";}s:4:"data";a:3:{s:14:"C1545275581762";a:2:{s:6:"imgurl";s:71:"https://four.nttrip.cn/template_img/template_food/index/picturew3-1.png";s:7:"linkurl";s:0:"";}s:14:"C1545275581763";a:2:{s:6:"imgurl";s:71:"https://four.nttrip.cn/template_img/template_food/index/picturew3-2.png";s:7:"linkurl";s:0:"";}s:14:"C1545275581764";a:2:{s:6:"imgurl";s:71:"https://four.nttrip.cn/template_img/template_food/index/picturew3-3.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:8:"picturew";s:5:"index";s:3:"NaN";}s:14:"M1545275868484";a:5:{s:4:"icon";s:23:"iconfont2 icon-daohang1";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:10:{s:9:"margintop";s:1:"6";s:10:"background";s:7:"#ffffff";s:9:"iconcolor";s:7:"#999999";s:9:"textcolor";s:7:"#666666";s:11:"remarkcolor";s:7:"#888888";s:5:"sizew";s:2:"20";s:11:"paddingleft";s:2:"15";s:7:"padding";s:1:"4";s:5:"sizeh";s:2:"20";s:9:"linecolor";s:7:"#ffffff";}s:4:"data";a:1:{s:14:"C1545275868484";a:5:{s:4:"text";s:12:"精选套餐";s:7:"linkurl";s:0:"";s:9:"iconclass";s:0:"";s:6:"remark";s:4:"more";s:6:"dotnum";s:0:"";}}s:2:"id";s:8:"listmenu";}s:14:"M1545275913667";a:5:{s:4:"icon";s:19:"iconfont icon-c-pdf";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:9:"no-repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"1";s:13:"backgroundimg";s:0:"";}s:5:"style";a:6:{s:10:"background";s:7:"#ffffff";s:3:"pdw";s:1:"4";s:3:"pdh";s:1:"4";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";}s:4:"data";a:4:{s:14:"C1545275913667";a:5:{s:6:"imgurl";s:69:"https://four.nttrip.cn/template_img/template_food/index/classfit1.png";s:5:"title";s:24:"藤椒嫩笋堡薯条餐";s:4:"text";s:5:"30元";s:3:"bg1";s:7:"#ffffff";s:3:"bg2";s:7:"#ffffff";}s:14:"C1545275913668";a:5:{s:6:"imgurl";s:69:"https://four.nttrip.cn/template_img/template_food/index/classfit2.png";s:5:"title";s:22:"藤椒卷堡3人套餐";s:4:"text";s:5:"69元";s:3:"bg1";s:7:"#ffffff";s:3:"bg2";s:7:"#ffffff";}s:14:"C1545275913669";a:5:{s:6:"imgurl";s:69:"https://four.nttrip.cn/template_img/template_food/index/classfit3.png";s:5:"title";s:24:"藤椒嫩笋卷辣翅餐";s:4:"text";s:5:"35元";s:3:"bg1";s:7:"#ffffff";s:3:"bg2";s:7:"#ffffff";}s:14:"C1545275913670";a:5:{s:6:"imgurl";s:69:"https://four.nttrip.cn/template_img/template_food/index/classfit4.png";s:5:"title";s:24:"藤椒嫩笋卷辣翅餐";s:4:"text";s:5:"30元";s:3:"bg1";s:7:"#ffffff";s:3:"bg2";s:7:"#ffffff";}}s:2:"id";s:8:"classfit";}}';
            $item1 = unserialize($item1);
            foreach($item1 as $k => &$v){
                if(isset($v['data'])){
                    foreach($v['data'] as $kk => &$vv){
                        if(isset($vv['imgurl']) && strpos($vv['imgurl'],'https://four.nttrip.cn/') !== false){
                            $vv['imgurl'] = ROOT_HOST."/diypage/".explode('https://four.nttrip.cn/', $vv['imgurl'])[1];
                        }
                    }
                }
            }
            $page1['items'] = serialize($item1);
            $page1['page'] = 'a:7:{s:10:"background";s:7:"#f1f1f1";s:13:"topbackground";s:7:"#ffcc00";s:8:"topcolor";s:1:"1";s:9:"styledata";s:1:"0";s:5:"title";s:12:"餐饮首页";s:4:"name";s:6:"首页";s:10:"visitlevel";a:2:{s:6:"member";s:0:"";s:10:"commission";s:0:"";}}';
            $page1['tpl_name'] = "首页";

            $page2 = [];
            $page2['uniacid'] = $uniacid;
            $page2['index'] = 0;
            $item2 = 'a:5:{s:14:"M1545293933396";a:6:{s:4:"icon";s:40:"iconfont2 icon-icon_xuanxiangqiayangshi-";s:3:"max";s:1:"2";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:11:{s:10:"background";s:7:"#ffffff";s:5:"color";s:7:"#666666";s:16:"activebackground";s:7:"#ffffff";s:11:"activecolor";s:7:"#ffcc00";s:12:"activeborder";s:7:"#ffcc00";s:7:"padding";s:1:"6";s:11:"paddingleft";s:1:"8";s:2:"mt";s:1:"0";s:9:"scrollnum";s:1:"3";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";}s:4:"data";a:2:{s:14:"C1545293933396";a:2:{s:4:"text";s:12:"品牌故事";s:7:"linkurl";s:0:"";}s:14:"C1545293933397";a:3:{s:4:"text";s:12:"门店导航";s:7:"linkurl";s:23:"/sudu8_page/store/store";s:8:"linktype";s:4:"page";}}s:2:"id";s:6:"tabbar";}s:14:"M1545293752099";a:5:{s:4:"icon";s:28:"iconfont2 icon-tuoyuankaobei";s:6:"params";a:10:{s:5:"totle";s:1:"2";s:8:"navstyle";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:9:"navstyle2";s:1:"0";s:4:"imgh";s:3:"250";}s:5:"style";a:18:{s:8:"dotstyle";s:5:"round";s:8:"dotalign";s:4:"left";s:10:"paddingtop";s:1:"0";s:11:"paddingleft";s:1:"9";s:10:"background";s:7:"#ffffff";s:13:"backgroundall";s:7:"#ffffff";s:9:"leftright";s:1:"5";s:6:"bottom";s:1:"5";s:7:"opacity";s:3:"0.8";s:10:"text_color";s:4:"#fff";s:2:"bg";s:7:"#000000";s:9:"jsq_color";s:3:"red";s:3:"pdh";s:1:"0";s:3:"pdw";s:1:"0";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"speed";s:1:"5";}s:4:"data";a:2:{s:14:"C1545293752099";a:4:{s:6:"imgurl";s:66:"https://four.nttrip.cn/template_img/template_food/page/banner1.png";s:7:"linkurl";s:0:"";s:6:"single";s:1:"1";s:4:"text";s:12:"文字描述";}s:14:"C1545293752100";a:4:{s:6:"imgurl";s:66:"https://four.nttrip.cn/template_img/template_food/page/banner2.png";s:7:"linkurl";s:0:"";s:6:"single";s:1:"2";s:4:"text";s:12:"文字描述";}}s:2:"id";s:6:"banner";}s:14:"M1545288146922";a:5:{s:3:"max";s:1:"5";s:4:"icon";s:23:"iconfont2 icon-fuwenben";s:6:"params";a:1:{s:7:"content";s:608:"PHAgc3R5bGU9ImxpbmUtaGVpZ2h0OiAxLjVlbTsgbWFyZ2luLWJvdHRvbTogMTBweDsiPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDE4cHg7Ij48c3Ryb25nPuiCr+W+t+Wfujwvc3Ryb25nPjwvc3Bhbj48YnIvPjwvcD48cCBzdHlsZT0ibGluZS1oZWlnaHQ6IDEuNzVlbTsiPjxzcGFuIHN0eWxlPSJjb2xvcjogcmdiKDE2NSwgMTY1LCAxNjUpOyBmb250LXNpemU6IDE0cHg7Ij7mmK/nvo7lm73ot6jlm73ov57plIHppJDljoXkuYvkuIDvvIzkuZ/mmK/kuJbnlYznrKzkuozlpKfpgJ/po5/lj4rmnIDlpKfngrjpuKHov57plIHkvIHkuJrvvIwxOTUy5bm055Sx5Yib5aeL5Lq65ZOI5YWw4oCi5bGx5b635aOr5Yib5bu677yM5Li76KaB5Ye65ZSu54K46bih44CB5rGJ5aCh44CB6Jav5p2h44CB55uW6aWt44CB6JuL5oye44CB5rG95rC0562J6auY54Ot6YeP5b+r6aSQ6aOf5ZOB44CCPC9zcGFuPjwvcD4=";}s:5:"style";a:3:{s:10:"background";s:7:"#ffffff";s:7:"padding";s:2:"18";s:9:"margintop";s:1:"0";}s:2:"id";s:8:"richtext";}s:14:"M1545288892439";a:4:{s:4:"icon";s:32:"iconfont2 icon-liebiao_biaotilan";s:6:"params";a:10:{s:5:"title";s:26:"连锁电话：400-3625870";s:4:"icon";s:15:"icon-x-dianhua5";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:4:"link";s:15:"tel:400-3625870";s:8:"linktype";s:3:"tel";}s:5:"style";a:9:{s:10:"background";s:7:"#ffffff";s:5:"color";s:7:"#808080";s:9:"textalign";s:4:"left";s:8:"fontsize";s:2:"14";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"23";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:1:"0";}s:2:"id";s:5:"title";}s:14:"M1545288895832";a:5:{s:4:"icon";s:32:"iconfont2 icon-liebiao_biaotilan";s:6:"params";a:8:{s:5:"title";s:27:"营业时间：8:00 - 20:00";s:4:"icon";s:15:"icon-x-shijian2";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:9:{s:10:"background";s:7:"#ffffff";s:5:"color";s:7:"#808080";s:9:"textalign";s:4:"left";s:8:"fontsize";s:2:"14";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"23";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:1:"0";}s:2:"id";s:5:"title";s:5:"index";s:3:"NaN";}}';
            $item2 = unserialize($item2);
            foreach($item2 as $k => &$v){
                if(isset($v['data'])){
                    foreach($v['data'] as $kk => &$vv){
                        if(isset($vv['imgurl']) && strpos($vv['imgurl'],'https://four.nttrip.cn/') !== false){
                            $vv['imgurl'] = ROOT_HOST."/diypage/".explode('https://four.nttrip.cn/', $vv['imgurl'])[1];
                        }
                    }
                }
            }
            $page2['items'] = serialize($item2);
            $page2['page'] = 'a:7:{s:10:"background";s:7:"#f1f1f1";s:13:"topbackground";s:7:"#ffcc00";s:8:"topcolor";s:1:"1";s:9:"styledata";s:1:"0";s:5:"title";s:6:"门店";s:4:"name";s:6:"门店";s:10:"visitlevel";a:2:{s:6:"member";s:0:"";s:10:"commission";s:0:"";}}';
            $page2['tpl_name'] = "门店";


            $page1_id = Db::name('wd_xcx_diypage')->insertGetId($page1);
            $page2_id = Db::name('wd_xcx_diypage')->insertGetId($page2);

            $pageids = $page1_id.",".$page2_id;

            $is = Db::name('wd_xcx_diypageset')->where('uniacid',$uniacid)->find();
            if(empty($is)){
                $page_set = [
                    'uniacid' => $uniacid,
                    'kp' => ROOT_HOST."/diypage/resource/images/diypage/default/default_start.jpg",
                    'kp_is' => 2,
                    'kp_m' => 2,
                    'tc' => ROOT_HOST."/diypage/resource/images/diypage/default/tcgg.jpg",
                    'tc_is' => 2,
                    'foot_is' => 2
                ];
                Db::name('wd_xcx_diypageset')->insert($page_set);
            }else{
                Db::name('wd_xcx_diypageset')->where('uniacid',$uniacid)->update(array('foot_is' => 2));
            }
            $data = [
                'uniacid' => $uniacid,
                'pageid' => $pageids,
                'template_name' => '餐饮类模板01',
                'thumb' => "/diypage/template_img/template_food/cover.png",
                'status' => '1',
                'create_time' => time()
            ];
            $tplid = Db::name('wd_xcx_diypagetpl')->insertGetId($data);
            if($tplid){
                return $tplid;
            }
        }elseif($id == 'm_food02'){
            $page1 = [];
            $page1['uniacid'] = $uniacid;
            $page1['index'] = 1;
            $item1 = 'a:8:{s:14:"M1564038421158";a:4:{s:4:"icon";s:22:"iconfont2 icon-sousuo1";s:6:"params";a:7:{s:5:"value";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:12:{s:9:"textalign";s:6:"center";s:10:"background";s:7:"#333333";s:2:"bg";s:7:"#d6d6d6";s:12:"borderradius";s:1:"5";s:6:"boxpdh";s:2:"10";s:6:"boxpdz";s:2:"15";s:7:"padding";s:1:"5";s:8:"fontsize";s:2:"13";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"color";s:7:"#595959";}s:2:"id";s:3:"ssk";}s:14:"M1564038413734";a:5:{s:4:"icon";s:28:"iconfont2 icon-tuoyuankaobei";s:6:"params";a:10:{s:5:"totle";s:1:"2";s:8:"navstyle";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:9:"navstyle2";s:1:"0";s:4:"imgh";s:3:"200";}s:5:"style";a:18:{s:8:"dotstyle";s:5:"round";s:8:"dotalign";s:4:"left";s:10:"paddingtop";s:1:"0";s:11:"paddingleft";s:1:"0";s:10:"background";s:7:"#ffffff";s:13:"backgroundall";s:7:"#ffffff";s:9:"leftright";s:1:"5";s:6:"bottom";s:2:"10";s:7:"opacity";s:3:"0.5";s:10:"text_color";s:4:"#fff";s:2:"bg";s:7:"#000000";s:9:"jsq_color";s:3:"red";s:3:"pdh";s:1:"0";s:3:"pdw";s:1:"0";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"speed";s:1:"5";}s:4:"data";a:2:{s:14:"C1564038413734";a:4:{s:6:"imgurl";s:77:"https://four.nttrip.cn/diypage/template_img/template_food02/index/banner1.jpg";s:7:"linkurl";s:0:"";s:6:"single";s:1:"1";s:4:"text";s:12:"文字描述";}s:14:"C1564038413735";a:4:{s:6:"imgurl";s:77:"https://four.nttrip.cn/diypage/template_img/template_food02/index/banner2.jpg";s:7:"linkurl";s:0:"";s:6:"single";s:1:"2";s:4:"text";s:12:"文字描述";}}s:2:"id";s:6:"banner";}s:14:"M1564038672205";a:5:{s:4:"icon";s:22:"iconfont2 icon-anniuzu";s:6:"params";a:8:{s:9:"styledata";s:1:"1";s:6:"repeat";s:9:"no-repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"1";s:13:"backgroundimg";s:77:"https://four.nttrip.cn/diypage/template_img/template_food02/index/menu_bg.png";s:7:"picicon";s:1:"1";s:8:"textshow";s:1:"1";}s:5:"style";a:14:{s:8:"navstyle";s:0:"";s:10:"background";s:7:"#ffffff";s:6:"rownum";s:1:"4";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"8";s:7:"showdot";s:1:"1";s:7:"padding";s:2:"10";s:11:"paddingleft";s:2:"10";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:6:"iconfz";s:2:"14";s:9:"iconcolor";s:7:"#434343";s:8:"imgwidth";s:2:"40";}s:4:"data";a:4:{s:14:"C1564038672205";a:5:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_food02/index/menu1.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"手卷刺身";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1564038672206";a:5:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_food02/index/menu2.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"海鲜刺身";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1564038672207";a:5:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_food02/index/menu3.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"素食刺身";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1564038672208";a:5:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_food02/index/menu4.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"军舰刺身";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}}s:2:"id";s:4:"menu";}s:14:"M1564039296407";a:4:{s:4:"icon";s:32:"iconfont2 icon-liebiao_biaotilan";s:6:"params";a:9:{s:5:"title";s:12:"新人专享";s:6:"title2";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:5:"style";s:1:"3";}s:5:"style";a:11:{s:10:"background";s:7:"#ffffff";s:6:"colorz";s:7:"#434343";s:6:"colorf";s:7:"#838383";s:9:"linecolor";s:7:"#ee8019";s:9:"fontsizez";s:2:"14";s:9:"fontsizef";s:2:"12";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:2:"10";}s:2:"id";s:6:"title2";}s:14:"M1564039767359";a:5:{s:4:"icon";s:25:"iconfont2 icon-youhuiquan";s:6:"params";a:10:{s:8:"hidetext";s:1:"0";s:8:"showtype";s:1:"0";s:6:"rownum";s:1:"3";s:7:"showbtn";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:10:{s:10:"background";s:7:"#ffffff";s:5:"yhqbg";s:7:"#ee8019";s:6:"yhqbg2";s:7:"#f8c698";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:5:"color";s:7:"#ffffff";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:6:"counts";s:1:"2";}s:4:"data";a:3:{s:14:"C1564039767359";a:3:{s:7:"linkurl";s:0:"";s:5:"title";s:3:"100";s:4:"text";s:15:"满500元可用";}s:14:"C1564039767360";a:3:{s:7:"linkurl";s:0:"";s:5:"title";s:3:"100";s:4:"text";s:15:"满500元可用";}s:14:"C1564039767361";a:3:{s:7:"linkurl";s:0:"";s:5:"title";s:3:"100";s:4:"text";s:15:"满500元可用";}}s:2:"id";s:3:"yhq";}s:14:"M1564039825228";a:5:{s:4:"icon";s:32:"iconfont2 icon-liebiao_biaotilan";s:6:"params";a:9:{s:5:"title";s:12:"今日推荐";s:6:"title2";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:5:"style";s:1:"3";}s:5:"style";a:11:{s:10:"background";s:7:"#ffffff";s:6:"colorz";s:7:"#434343";s:6:"colorf";s:7:"#838383";s:9:"linecolor";s:7:"#ee8019";s:9:"fontsizez";s:2:"14";s:9:"fontsizef";s:2:"12";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:2:"10";}s:2:"id";s:6:"title2";s:5:"index";s:3:"NaN";}s:14:"M1564039847852";a:5:{s:4:"icon";s:19:"iconfont2 icon-zutu";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:6:{s:10:"paddingtop";s:1:"0";s:11:"paddingleft";s:1:"0";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"background";s:7:"#ffffff";}s:4:"data";a:1:{s:14:"C1564039847852";a:2:{s:6:"imgurl";s:77:"https://four.nttrip.cn/diypage/template_img/template_food02/index/picture.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:7:"picture";}s:14:"M1564039876885";a:5:{s:4:"icon";s:22:"iconfont2 icon-chanpin";s:6:"params";a:31:{s:11:"goodsscroll";s:1:"0";s:9:"showtitle";s:1:"1";s:9:"showprice";s:1:"1";s:7:"showtag";s:1:"0";s:9:"goodsdata";s:1:"1";s:6:"cateid";s:0:"";s:8:"catename";s:0:"";s:7:"groupid";s:0:"";s:9:"groupname";s:0:"";s:9:"goodssort";s:1:"0";s:8:"goodsnum";s:1:"6";s:8:"showicon";s:1:"0";s:12:"iconposition";s:8:"left top";s:12:"productprice";s:1:"1";s:16:"showproductprice";s:1:"0";s:9:"showsales";s:1:"1";s:16:"productpricetext";s:6:"原价";s:9:"salestext";s:6:"销量";s:16:"productpriceline";s:1:"0";s:7:"saleout";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"imgh_is";s:1:"1";s:4:"imgh";s:3:"100";s:7:"con_key";s:1:"1";s:8:"con_type";s:1:"1";s:8:"sourceid";s:0:"";}s:5:"style";a:20:{s:10:"background";s:7:"#ffffff";s:9:"liststyle";s:5:"block";s:8:"buystyle";s:0:"";s:9:"goodsicon";s:9:"recommand";s:9:"iconstyle";s:8:"triangle";s:10:"pricecolor";s:7:"#ff5555";s:17:"productpricecolor";s:7:"#999999";s:14:"iconpaddingtop";s:1:"0";s:15:"iconpaddingleft";s:1:"0";s:11:"buybtncolor";s:7:"#ff5555";s:8:"iconzoom";s:2:"50";s:10:"titlecolor";s:7:"#000000";s:13:"tagbackground";s:7:"#fe5455";s:10:"salescolor";s:7:"#999999";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:8:"showtype";s:1:"0";}s:4:"data";a:4:{s:14:"C1564039876885";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:3:"des";s:21:"这里是产品描述";}s:14:"C1564039876886";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1564039876887";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1564039876888";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}}s:2:"id";s:5:"goods";}}';
            $item1 = unserialize($item1);
            foreach($item1 as $k => &$v){
                if(isset($v['data'])){
                    foreach($v['data'] as $kk => &$vv){
                        if(isset($vv['imgurl']) && strpos($vv['imgurl'],'https://four.nttrip.cn') !== false){
                            $vv['imgurl'] = ROOT_HOST.explode('https://four.nttrip.cn', $vv['imgurl'])[1];
                        }
                        if(isset($vv['thumb']) && strpos($vv['thumb'],'/diypage/resource/images/diypage/default/2.jpg') !== false){
                            $vv['thumb'] = ROOT_HOST."/diypage/resource/images/diypage/default/2.jpg";
                        }
                    }
                }
            }
            $page1['items'] = serialize($item1);
            $page1['page'] = 'a:7:{s:10:"background";s:7:"#f1f1f1";s:13:"topbackground";s:7:"#ffffff";s:8:"topcolor";s:1:"1";s:9:"styledata";s:1:"0";s:5:"title";s:6:"首页";s:4:"name";s:6:"首页";s:10:"visitlevel";a:2:{s:6:"member";s:0:"";s:10:"commission";s:0:"";}}';
            $page1['tpl_name'] = "首页";

            $page1_id = Db::name('wd_xcx_diypage')->insertGetId($page1);
            $pageids = $page1_id;

            $is = Db::name('wd_xcx_diypageset')->where('uniacid',$uniacid)->find();
            if(empty($is)){
                $page_set = [
                    'uniacid' => $uniacid,
                    'kp' => ROOT_HOST."/diypage/resource/images/diypage/default/default_start.jpg",
                    'kp_is' => 2,
                    'kp_m' => 2,
                    'tc' => ROOT_HOST."/diypage/resource/images/diypage/default/tcgg.jpg",
                    'tc_is' => 2,
                    'foot_is' => 2
                ];
                Db::name('wd_xcx_diypageset')->insert($page_set);
            }else{
                Db::name('wd_xcx_diypageset')->where('uniacid',$uniacid)->update(array('foot_is' => 2));
            }
            $data = [
                'uniacid' => $uniacid,
                'pageid' => $pageids,
                'template_name' => '餐饮类模板02',
                'thumb' => "/diypage/template_img/template_food02/cover.png",
                'status' => '1',
                'create_time' => time()
            ];
            $tplid = Db::name('wd_xcx_diypagetpl')->insertGetId($data);
            if($tplid){
                return $tplid;
            }
        }elseif($id == 'm_food03'){
            $page1 = [];
            $page1['uniacid'] = $uniacid;
            $page1['index'] = 1;
            $item1 = 'a:5:{s:14:"M1564042290494";a:4:{s:4:"icon";s:22:"iconfont2 icon-sousuo1";s:6:"params";a:7:{s:5:"value";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:12:{s:9:"textalign";s:6:"center";s:10:"background";s:7:"#ffffff";s:2:"bg";s:7:"#f1f2f3";s:12:"borderradius";s:1:"5";s:6:"boxpdh";s:2:"10";s:6:"boxpdz";s:2:"15";s:7:"padding";s:1:"5";s:8:"fontsize";s:2:"13";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"color";s:7:"#bea499";}s:2:"id";s:3:"ssk";}s:14:"M1564042295660";a:5:{s:4:"icon";s:28:"iconfont2 icon-tuoyuankaobei";s:6:"params";a:10:{s:5:"totle";s:1:"2";s:8:"navstyle";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:9:"navstyle2";s:1:"0";s:4:"imgh";s:3:"150";}s:5:"style";a:18:{s:8:"dotstyle";s:5:"round";s:8:"dotalign";s:4:"left";s:10:"paddingtop";s:1:"0";s:11:"paddingleft";s:1:"0";s:10:"background";s:7:"#ffffff";s:13:"backgroundall";s:7:"#ffffff";s:9:"leftright";s:1:"5";s:6:"bottom";s:1:"5";s:7:"opacity";s:1:"0";s:10:"text_color";s:4:"#fff";s:2:"bg";s:7:"#000000";s:9:"jsq_color";s:3:"red";s:3:"pdh";s:1:"0";s:3:"pdw";s:1:"0";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"speed";s:1:"5";}s:4:"data";a:1:{s:14:"C1564042295660";a:4:{s:6:"imgurl";s:76:"https://four.nttrip.cn/diypage/template_img/template_food03/index/banner.jpg";s:7:"linkurl";s:0:"";s:6:"single";s:1:"1";s:4:"text";s:12:"文字描述";}}s:2:"id";s:6:"banner";}s:14:"M1564042361637";a:5:{s:4:"icon";s:21:"iconfont2 icon-tupian";s:6:"params";a:9:{s:3:"row";s:1:"1";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"2";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:8:{s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:7:"showdot";s:1:"0";s:7:"showbtn";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:2:"10";s:10:"background";s:7:"#ffffff";}s:4:"data";a:4:{s:14:"C1564042361637";a:2:{s:6:"imgurl";s:79:"https://four.nttrip.cn/diypage/template_img/template_food03/index/picturew1.png";s:7:"linkurl";s:0:"";}s:14:"C1564042361638";a:2:{s:6:"imgurl";s:79:"https://four.nttrip.cn/diypage/template_img/template_food03/index/picturew2.png";s:7:"linkurl";s:0:"";}s:14:"C1564042361639";a:2:{s:6:"imgurl";s:79:"https://four.nttrip.cn/diypage/template_img/template_food03/index/picturew3.png";s:7:"linkurl";s:0:"";}s:14:"C1564042361640";a:2:{s:6:"imgurl";s:79:"https://four.nttrip.cn/diypage/template_img/template_food03/index/picturew4.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:8:"picturew";}s:14:"M1564042483987";a:5:{s:4:"icon";s:19:"iconfont2 icon-zutu";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:6:{s:10:"paddingtop";s:1:"0";s:11:"paddingleft";s:1:"0";s:2:"mt";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"background";s:7:"#ffffff";}s:4:"data";a:1:{s:14:"C1564042483987";a:2:{s:6:"imgurl";s:77:"https://four.nttrip.cn/diypage/template_img/template_food03/index/picture.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:7:"picture";}s:14:"M1564042558876";a:5:{s:4:"icon";s:19:"iconfont icon-c-pdf";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:6:{s:10:"background";s:7:"#ffffff";s:3:"pdw";s:1:"5";s:3:"pdh";s:1:"5";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";}s:4:"data";a:4:{s:14:"C1564042558876";a:5:{s:6:"imgurl";s:79:"https://four.nttrip.cn/diypage/template_img/template_food03/index/classfit1.png";s:5:"title";s:15:"炼奶黑咖啡";s:4:"text";s:15:"好评度：95%";s:3:"bg1";s:7:"#ffffff";s:3:"bg2";s:7:"#ffffff";}s:14:"C1564042558877";a:5:{s:6:"imgurl";s:79:"https://four.nttrip.cn/diypage/template_img/template_food03/index/classfit2.png";s:5:"title";s:15:"焦糖玛奇朵";s:4:"text";s:15:"好评度：95%";s:3:"bg1";s:7:"#ffffff";s:3:"bg2";s:7:"#ffffff";}s:14:"C1564042558878";a:5:{s:6:"imgurl";s:79:"https://four.nttrip.cn/diypage/template_img/template_food03/index/classfit3.png";s:5:"title";s:15:"可可巧克力";s:4:"text";s:15:"好评度：95%";s:3:"bg1";s:7:"#ffffff";s:3:"bg2";s:7:"#ffffff";}s:14:"C1564042558879";a:5:{s:6:"imgurl";s:79:"https://four.nttrip.cn/diypage/template_img/template_food03/index/classfit4.png";s:5:"title";s:15:"意大利拿铁";s:4:"text";s:15:"好评度：95%";s:3:"bg1";s:7:"#ffffff";s:3:"bg2";s:7:"#ffffff";}}s:2:"id";s:8:"classfit";}}';
            $item1 = unserialize($item1);
            foreach($item1 as $k => &$v){
                if(isset($v['data'])){
                    foreach($v['data'] as $kk => &$vv){
                        if(isset($vv['imgurl']) && strpos($vv['imgurl'],'https://four.nttrip.cn') !== false){
                            $vv['imgurl'] = ROOT_HOST.explode('https://four.nttrip.cn', $vv['imgurl'])[1];
                        }
                    }
                }
            }
            $page1['items'] = serialize($item1);
            $page1['page'] = 'a:7:{s:10:"background";s:7:"#f1f1f1";s:13:"topbackground";s:7:"#bea499";s:8:"topcolor";s:1:"2";s:9:"styledata";s:1:"0";s:5:"title";s:6:"首页";s:4:"name";s:6:"首页";s:10:"visitlevel";a:2:{s:6:"member";s:0:"";s:10:"commission";s:0:"";}}';
            $page1['tpl_name'] = "首页";

            $page1_id = Db::name('wd_xcx_diypage')->insertGetId($page1);
            $pageids = $page1_id;

            $is = Db::name('wd_xcx_diypageset')->where('uniacid',$uniacid)->find();
            if(empty($is)){
                $page_set = [
                    'uniacid' => $uniacid,
                    'kp' => ROOT_HOST."/diypage/resource/images/diypage/default/default_start.jpg",
                    'kp_is' => 2,
                    'kp_m' => 2,
                    'tc' => ROOT_HOST."/diypage/resource/images/diypage/default/tcgg.jpg",
                    'tc_is' => 2,
                    'foot_is' => 2
                ];
                Db::name('wd_xcx_diypageset')->insert($page_set);
            }else{
                Db::name('wd_xcx_diypageset')->where('uniacid',$uniacid)->update(array('foot_is' => 2));
            }
            $data = [
                'uniacid' => $uniacid,
                'pageid' => $pageids,
                'template_name' => '餐饮类模板03',
                'thumb' => "/diypage/template_img/template_food03/cover.png",
                'status' => '1',
                'create_time' => time()
            ];
            $tplid = Db::name('wd_xcx_diypagetpl')->insertGetId($data);
            if($tplid){
                return $tplid;
            }
        }elseif($id == 'm_food04'){
            $page1 = [];
            $page1['uniacid'] = $uniacid;
            $page1['index'] = 1;
            $item1 = 'a:6:{s:14:"M1564195391972";a:4:{s:4:"icon";s:22:"iconfont2 icon-sousuo1";s:6:"params";a:7:{s:5:"value";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:12:{s:9:"textalign";s:6:"center";s:10:"background";s:7:"#ff5f4b";s:2:"bg";s:7:"#ffffff";s:12:"borderradius";s:1:"5";s:6:"boxpdh";s:2:"15";s:6:"boxpdz";s:2:"20";s:7:"padding";s:1:"5";s:8:"fontsize";s:2:"13";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"color";s:7:"#c8c8c8";}s:2:"id";s:3:"ssk";}s:14:"M1564193949546";a:5:{s:4:"icon";s:22:"iconfont2 icon-anniuzu";s:6:"params";a:8:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"picicon";s:1:"1";s:8:"textshow";s:1:"1";}s:5:"style";a:14:{s:8:"navstyle";s:0:"";s:10:"background";s:7:"#ffffff";s:6:"rownum";s:1:"4";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"8";s:7:"showdot";s:1:"1";s:7:"padding";s:2:"10";s:11:"paddingleft";s:2:"10";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:6:"iconfz";s:2:"14";s:9:"iconcolor";s:7:"#434343";s:8:"imgwidth";s:2:"45";}s:4:"data";a:8:{s:14:"C1564193949546";a:5:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_food04/index/menu1.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"食味美食";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1564193949547";a:5:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_food04/index/menu2.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"优惠超市";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1564193949548";a:5:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_food04/index/menu3.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"生鲜果蔬";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1564193949549";a:5:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_food04/index/menu4.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"甜点饮品";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"M1564196190638";a:5:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_food04/index/menu5.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"正餐优选";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"M1564196191836";a:5:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_food04/index/menu6.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"下午茶品";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"M1564196192973";a:5:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_food04/index/menu7.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"丰富宵夜";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"M1564196194189";a:5:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_food04/index/menu8.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"吃货点评";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}}s:2:"id";s:4:"menu";}s:14:"M1564197533346";a:6:{s:4:"icon";s:28:"iconfont2 icon-tuoyuankaobei";s:6:"params";a:10:{s:5:"totle";s:1:"2";s:8:"navstyle";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:9:"navstyle2";s:1:"0";s:4:"imgh";s:3:"120";}s:5:"style";a:18:{s:8:"dotstyle";s:5:"round";s:8:"dotalign";s:4:"left";s:10:"paddingtop";s:1:"0";s:11:"paddingleft";s:1:"0";s:10:"background";s:7:"#ffffff";s:13:"backgroundall";s:7:"#ffffff";s:9:"leftright";s:1:"5";s:6:"bottom";s:1:"5";s:7:"opacity";s:1:"0";s:10:"text_color";s:4:"#fff";s:2:"bg";s:7:"#000000";s:9:"jsq_color";s:3:"red";s:3:"pdh";s:1:"0";s:3:"pdw";s:1:"0";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"speed";s:1:"5";}s:4:"data";a:1:{s:14:"C1564197533346";a:4:{s:6:"imgurl";s:76:"https://four.nttrip.cn/diypage/template_img/template_food04/index/banner.png";s:7:"linkurl";s:0:"";s:6:"single";s:1:"1";s:4:"text";s:12:"文字描述";}}s:2:"id";s:6:"banner";s:5:"index";s:3:"NaN";}s:14:"M1564197635395";a:5:{s:4:"icon";s:19:"iconfont2 icon-zutu";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:6:{s:10:"paddingtop";s:1:"5";s:11:"paddingleft";s:1:"0";s:2:"mt";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"background";s:7:"#ffffff";}s:4:"data";a:1:{s:14:"C1564197635395";a:2:{s:6:"imgurl";s:77:"https://four.nttrip.cn/diypage/template_img/template_food04/index/picture.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:7:"picture";}s:14:"M1564197690987";a:5:{s:4:"icon";s:21:"iconfont2 icon-tupian";s:6:"params";a:9:{s:3:"row";s:1:"2";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"2";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:8:{s:10:"paddingtop";s:1:"5";s:11:"paddingleft";s:1:"5";s:7:"showdot";s:1:"0";s:7:"showbtn";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:1:"0";s:10:"background";s:7:"#ffffff";}s:4:"data";a:2:{s:14:"C1564197690989";a:2:{s:6:"imgurl";s:81:"https://four.nttrip.cn/diypage/template_img/template_food04/index/picturew1_1.png";s:7:"linkurl";s:0:"";}s:14:"C1564197690990";a:2:{s:6:"imgurl";s:81:"https://four.nttrip.cn/diypage/template_img/template_food04/index/picturew1_2.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:8:"picturew";}s:14:"M1564197743396";a:6:{s:4:"icon";s:21:"iconfont2 icon-tupian";s:6:"params";a:9:{s:3:"row";s:1:"4";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"2";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:8:{s:10:"paddingtop";s:1:"4";s:11:"paddingleft";s:1:"5";s:7:"showdot";s:1:"0";s:7:"showbtn";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:1:"0";s:10:"background";s:7:"#ffffff";}s:4:"data";a:4:{s:14:"C1564197743396";a:2:{s:6:"imgurl";s:81:"https://four.nttrip.cn/diypage/template_img/template_food04/index/picturew2_1.png";s:7:"linkurl";s:0:"";}s:14:"C1564197743397";a:2:{s:6:"imgurl";s:81:"https://four.nttrip.cn/diypage/template_img/template_food04/index/picturew2_2.png";s:7:"linkurl";s:0:"";}s:14:"C1564197743398";a:2:{s:6:"imgurl";s:81:"https://four.nttrip.cn/diypage/template_img/template_food04/index/picturew2_3.png";s:7:"linkurl";s:0:"";}s:14:"C1564197743399";a:2:{s:6:"imgurl";s:81:"https://four.nttrip.cn/diypage/template_img/template_food04/index/picturew2_4.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:8:"picturew";s:5:"index";s:3:"NaN";}}';
            $item1 = unserialize($item1);
            foreach($item1 as $k => &$v){
                if(isset($v['data'])){
                    foreach($v['data'] as $kk => &$vv){
                        if(isset($vv['imgurl']) && strpos($vv['imgurl'],'https://four.nttrip.cn') !== false){
                            $vv['imgurl'] = ROOT_HOST.explode('https://four.nttrip.cn', $vv['imgurl'])[1];
                        }
                    }
                }
            }
            $page1['items'] = serialize($item1);
            $page1['page'] = 'a:7:{s:10:"background";s:7:"#f1f1f1";s:13:"topbackground";s:7:"#ff5f4b";s:8:"topcolor";s:1:"2";s:9:"styledata";s:1:"0";s:5:"title";s:6:"首页";s:4:"name";s:6:"首页";s:10:"visitlevel";a:2:{s:6:"member";s:0:"";s:10:"commission";s:0:"";}}';
            $page1['tpl_name'] = "首页";

            $page1_id = Db::name('wd_xcx_diypage')->insertGetId($page1);
            $pageids = $page1_id;

            $is = Db::name('wd_xcx_diypageset')->where('uniacid',$uniacid)->find();
            if(empty($is)){
                $page_set = [
                    'uniacid' => $uniacid,
                    'kp' => ROOT_HOST."/diypage/resource/images/diypage/default/default_start.jpg",
                    'kp_is' => 2,
                    'kp_m' => 2,
                    'tc' => ROOT_HOST."/diypage/resource/images/diypage/default/tcgg.jpg",
                    'tc_is' => 2,
                    'foot_is' => 2
                ];
                Db::name('wd_xcx_diypageset')->insert($page_set);
            }else{
                Db::name('wd_xcx_diypageset')->where('uniacid',$uniacid)->update(array('foot_is' => 2));
            }
            $data = [
                'uniacid' => $uniacid,
                'pageid' => $pageids,
                'template_name' => '餐饮类模板04',
                'thumb' => "/diypage/template_img/template_food04/cover.png",
                'status' => '1',
                'create_time' => time()
            ];
            $tplid = Db::name('wd_xcx_diypagetpl')->insertGetId($data);
            if($tplid){
                return $tplid;
            }
        }elseif($id == 'm_travel'){
            $page1 = [];
            $page1['uniacid'] = $uniacid;
            $page1['index'] = 1;
            $item1 = 'a:11:{s:14:"M1554195434216";a:5:{s:4:"icon";s:28:"iconfont2 icon-tuoyuankaobei";s:6:"params";a:10:{s:5:"totle";s:1:"2";s:8:"navstyle";s:1:"0";s:9:"styledata";s:1:"1";s:6:"repeat";s:9:"no-repeat";s:9:"positionx";s:6:"center";s:9:"positiony";s:6:"center";s:4:"size";s:1:"1";s:13:"backgroundimg";s:79:"https://four.nttrip.cn/diypage/template_img/template_travel/index/banner_bg.png";s:9:"navstyle2";s:1:"0";s:4:"imgh";s:3:"180";}s:5:"style";a:18:{s:8:"dotstyle";s:5:"round";s:8:"dotalign";s:4:"left";s:10:"paddingtop";s:2:"50";s:11:"paddingleft";s:2:"35";s:10:"background";s:7:"#ffffff";s:13:"backgroundall";s:7:"#ffffff";s:9:"leftright";s:1:"5";s:6:"bottom";s:1:"5";s:7:"opacity";s:3:"0.8";s:10:"text_color";s:4:"#fff";s:2:"bg";s:7:"#000000";s:9:"jsq_color";s:3:"red";s:3:"pdh";s:1:"0";s:3:"pdw";s:1:"0";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"speed";s:1:"5";}s:4:"data";a:3:{s:14:"C1554195434217";a:4:{s:6:"imgurl";s:69:"https://four.nttrip.cn/template_img/template_travel/index/banner1.png";s:7:"linkurl";s:0:"";s:6:"single";s:1:"1";s:4:"text";s:12:"文字描述";}s:14:"C1554195434218";a:4:{s:6:"imgurl";s:69:"https://four.nttrip.cn/template_img/template_travel/index/banner2.png";s:7:"linkurl";s:0:"";s:6:"single";s:1:"2";s:4:"text";s:12:"文字描述";}s:14:"M1554195630103";a:4:{s:6:"imgurl";s:69:"https://four.nttrip.cn/template_img/template_travel/index/banner3.png";s:7:"linkurl";s:0:"";s:6:"single";s:1:"1";s:4:"text";s:12:"文字描述";}}s:2:"id";s:6:"banner";}s:14:"M1554196897428";a:5:{s:4:"icon";s:22:"iconfont2 icon-anniuzu";s:6:"params";a:8:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"picicon";s:1:"1";s:8:"textshow";s:1:"1";}s:5:"style";a:14:{s:8:"navstyle";s:0:"";s:10:"background";s:7:"#ffffff";s:6:"rownum";s:1:"4";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"8";s:7:"showdot";s:1:"1";s:7:"padding";s:1:"0";s:11:"paddingleft";s:2:"10";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:6:"iconfz";s:2:"14";s:9:"iconcolor";s:7:"#434343";s:8:"imgwidth";s:2:"30";}s:4:"data";a:4:{s:14:"C1554196897428";a:6:{s:6:"imgurl";s:67:"https://four.nttrip.cn/template_img/template_travel/index/menu1.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"专属向导";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";s:9:"iconclass";s:14:"icon-x-shouye2";}s:14:"C1554196897429";a:6:{s:6:"imgurl";s:67:"https://four.nttrip.cn/template_img/template_travel/index/menu2.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"住在桂林";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";s:9:"iconclass";s:14:"icon-x-shouye2";}s:14:"C1554196897430";a:6:{s:6:"imgurl";s:67:"https://four.nttrip.cn/template_img/template_travel/index/menu3.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"吃在桂林";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";s:9:"iconclass";s:14:"icon-x-shouye2";}s:14:"C1554196897431";a:6:{s:6:"imgurl";s:67:"https://four.nttrip.cn/template_img/template_travel/index/menu4.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"订购门票";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";s:9:"iconclass";s:14:"icon-x-shouye2";}}s:2:"id";s:4:"menu";}s:14:"M1554197194229";a:5:{s:4:"icon";s:22:"iconfont2 icon-gonggao";s:6:"params";a:12:{s:7:"iconurl";s:15:"icon-x-gonggao5";s:10:"noticedata";s:1:"0";s:5:"speed";s:1:"4";s:9:"noticenum";s:1:"5";s:8:"navstyle";s:1:"1";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:8:"sourceid";s:0:"";}s:5:"style";a:9:{s:10:"background";s:7:"#ffffff";s:9:"iconcolor";s:7:"#fd5454";s:5:"color";s:7:"#666666";s:11:"bordercolor";s:7:"#e2e2e2";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";}s:4:"data";a:2:{s:14:"C1554197194229";a:2:{s:5:"title";s:42:"这里是第一条自定义公告的标题";s:7:"linkurl";s:0:"";}s:14:"C1554197194230";a:2:{s:5:"title";s:42:"这里是第二条自定义公告的标题";s:7:"linkurl";s:0:"";}}s:2:"id";s:6:"notice";}s:14:"M1554197223649";a:5:{s:4:"icon";s:19:"iconfont2 icon-zutu";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:6:{s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:2:"mt";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"background";s:7:"#ffffff";}s:4:"data";a:1:{s:14:"C1554197223649";a:3:{s:6:"imgurl";s:72:"https://four.nttrip.cn/template_img/template_travel/index/picture1_1.png";s:7:"linkurl";s:34:"/sudu8_page/index/index?pageid=226";s:8:"linktype";s:4:"page";}}s:2:"id";s:7:"picture";}s:14:"M1554197522751";a:5:{s:4:"icon";s:23:"iconfont2 icon-daohang1";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:10:{s:9:"margintop";s:2:"10";s:10:"background";s:7:"#ffffff";s:9:"iconcolor";s:7:"#999999";s:9:"textcolor";s:7:"#666666";s:11:"remarkcolor";s:7:"#888888";s:5:"sizew";s:2:"20";s:11:"paddingleft";s:2:"10";s:7:"padding";s:1:"5";s:5:"sizeh";s:2:"20";s:9:"linecolor";s:7:"#ffffff";}s:4:"data";a:1:{s:14:"C1554197522751";a:5:{s:4:"text";s:12:"特产热卖";s:7:"linkurl";s:0:"";s:9:"iconclass";s:0:"";s:6:"remark";s:0:"";s:6:"dotnum";s:0:"";}}s:2:"id";s:8:"listmenu";}s:14:"M1554197601280";a:5:{s:4:"icon";s:22:"iconfont2 icon-chanpin";s:6:"params";a:31:{s:11:"goodsscroll";s:1:"0";s:9:"showtitle";s:1:"1";s:9:"showprice";s:1:"1";s:7:"showtag";s:1:"0";s:9:"goodsdata";s:1:"1";s:6:"cateid";s:0:"";s:8:"catename";s:0:"";s:7:"groupid";s:0:"";s:9:"groupname";s:0:"";s:9:"goodssort";s:1:"0";s:8:"goodsnum";s:1:"6";s:8:"showicon";s:1:"0";s:12:"iconposition";s:8:"left top";s:12:"productprice";s:1:"1";s:16:"showproductprice";s:1:"0";s:9:"showsales";s:1:"1";s:16:"productpricetext";s:6:"原价";s:9:"salestext";s:6:"销量";s:16:"productpriceline";s:1:"0";s:7:"saleout";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"imgh_is";s:1:"1";s:4:"imgh";s:3:"100";s:7:"con_key";s:1:"1";s:8:"con_type";s:1:"1";s:8:"sourceid";s:0:"";}s:5:"style";a:20:{s:10:"background";s:7:"#ffffff";s:9:"liststyle";s:5:"block";s:8:"buystyle";s:0:"";s:9:"goodsicon";s:9:"recommand";s:9:"iconstyle";s:8:"triangle";s:10:"pricecolor";s:7:"#ff5555";s:17:"productpricecolor";s:7:"#999999";s:14:"iconpaddingtop";s:1:"0";s:15:"iconpaddingleft";s:1:"0";s:11:"buybtncolor";s:7:"#ff5555";s:8:"iconzoom";s:2:"50";s:10:"titlecolor";s:7:"#000000";s:13:"tagbackground";s:7:"#fe5455";s:10:"salescolor";s:7:"#999999";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"paddingtop";s:1:"5";s:11:"paddingleft";s:2:"10";s:8:"showtype";s:1:"0";}s:4:"data";a:4:{s:14:"C1554197601280";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:3:"des";s:21:"这里是产品描述";}s:14:"C1554197601281";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1554197601282";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1554197601283";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}}s:2:"id";s:5:"goods";}s:14:"M1554197512364";a:4:{s:4:"icon";s:32:"iconfont2 icon-liebiao_biaotilan";s:6:"params";a:8:{s:5:"title";s:12:"玩在桂林";s:4:"icon";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:9:{s:10:"background";s:7:"#ffffff";s:5:"color";s:7:"#666666";s:9:"textalign";s:4:"left";s:8:"fontsize";s:2:"14";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:2:"10";}s:2:"id";s:5:"title";}s:14:"M1554283977623";a:5:{s:4:"icon";s:21:"iconfont2 icon-tupian";s:6:"params";a:9:{s:3:"row";s:1:"2";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"2";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:8:{s:10:"paddingtop";s:1:"8";s:11:"paddingleft";s:1:"8";s:7:"showdot";s:1:"0";s:7:"showbtn";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:1:"0";s:10:"background";s:7:"#ffffff";}s:4:"data";a:4:{s:14:"C1554283977623";a:2:{s:6:"imgurl";s:71:"https://four.nttrip.cn/template_img/template_travel/index/picturew1.png";s:7:"linkurl";s:0:"";}s:14:"C1554283977624";a:2:{s:6:"imgurl";s:71:"https://four.nttrip.cn/template_img/template_travel/index/picturew2.png";s:7:"linkurl";s:0:"";}s:14:"C1554283977625";a:2:{s:6:"imgurl";s:71:"https://four.nttrip.cn/template_img/template_travel/index/picturew3.png";s:7:"linkurl";s:0:"";}s:14:"C1554283977626";a:2:{s:6:"imgurl";s:71:"https://four.nttrip.cn/template_img/template_travel/index/picturew4.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:8:"picturew";}s:14:"M1554284025822";a:6:{s:4:"icon";s:19:"iconfont2 icon-zutu";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:6:{s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:2:"mt";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"background";s:7:"#ffffff";}s:4:"data";a:1:{s:14:"C1554284025822";a:2:{s:6:"imgurl";s:72:"https://four.nttrip.cn/template_img/template_travel/index/picture2_1.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:7:"picture";s:5:"index";s:3:"NaN";}s:14:"M1554284038143";a:4:{s:4:"icon";s:32:"iconfont2 icon-liebiao_biaotilan";s:6:"params";a:8:{s:5:"title";s:20:"— 我们承诺 —";s:4:"icon";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:9:{s:10:"background";s:7:"#ffffff";s:5:"color";s:7:"#949494";s:9:"textalign";s:6:"center";s:8:"fontsize";s:2:"14";s:10:"paddingtop";s:1:"5";s:11:"paddingleft";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:1:"0";}s:2:"id";s:5:"title";}s:14:"M1554284099976";a:5:{s:4:"icon";s:19:"iconfont2 icon-fuwu";s:6:"params";a:10:{s:8:"hidetext";s:1:"0";s:8:"showtype";s:1:"0";s:6:"rownum";s:1:"3";s:7:"showbtn";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:12:{s:10:"background";s:7:"#ffffff";s:10:"paddingtop";s:1:"3";s:11:"paddingleft";s:2:"10";s:6:"iconfz";s:2:"16";s:7:"tbcolor";s:7:"#9ac2cf";s:5:"color";s:7:"#888888";s:4:"tbbg";s:0:"";s:3:"pdl";s:1:"5";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:8:"fontsize";s:2:"12";}s:4:"data";a:3:{s:14:"C1554284099976";a:2:{s:9:"iconclass";s:12:"icon-x-gouwu";s:4:"text";s:12:"拒绝购物";}s:14:"C1554284099977";a:2:{s:9:"iconclass";s:15:"icon-c-shangjia";s:4:"text";s:12:"认证保障";}s:14:"C1554284099978";a:2:{s:9:"iconclass";s:11:"icon-x-kefu";s:4:"text";s:12:"售后无忧";}}s:2:"id";s:4:"dnfw";}}';
            $item1 = unserialize($item1);
            foreach($item1 as $k => &$v){
                if(isset($v['data'])){
                    foreach($v['data'] as $kk => &$vv){
                        if(isset($vv['imgurl']) && strpos($vv['imgurl'],'https://four.nttrip.cn/') !== false){
                            $vv['imgurl'] = ROOT_HOST."/diypage/".explode('https://four.nttrip.cn/', $vv['imgurl'])[1];
                        }
                    }
                }
            }
            $page1['items'] = serialize($item1);
            $page1['page'] = 'a:7:{s:10:"background";s:7:"#f1f1f1";s:13:"topbackground";s:7:"#ffa844";s:8:"topcolor";s:1:"2";s:9:"styledata";s:1:"0";s:5:"title";s:6:"首页";s:4:"name";s:6:"首页";s:10:"visitlevel";a:2:{s:6:"member";s:0:"";s:10:"commission";s:0:"";}}';
            $page1['tpl_name'] = "首页";

            $page2 = [];
            $page2['uniacid'] = $uniacid;
            $page2['index'] = 0;
            $item2 = 'a:1:{s:14:"M1554281037772";a:5:{s:3:"max";s:1:"1";s:4:"icon";s:23:"iconfont2 icon-8636f874";s:6:"params";a:10:{s:8:"navstyle";s:1:"1";s:9:"styledata";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"btntext";s:6:"提交";s:4:"tslx";s:1:"0";s:6:"repeat";s:1:"0";s:9:"positionx";s:1:"0";s:9:"positiony";s:1:"0";s:4:"size";s:1:"0";s:8:"sourceid";s:0:"";}s:5:"style";a:17:{s:10:"background";s:7:"#ffffff";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:12:"inputbgcolor";s:7:"#ffffff";s:10:"inputcolor";s:7:"#000000";s:7:"inputmt";s:2:"10";s:12:"borderradius";s:1:"3";s:11:"bordercolor";s:4:"#eee";s:15:"btnborderradius";s:2:"30";s:8:"btncolor";s:7:"#ffffff";s:10:"btnbgcolor";s:7:"#ffbc55";s:13:"btnpaddingtop";s:2:"10";s:12:"btnmargintop";s:2:"15";s:14:"btnmarginright";s:2:"10";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";}s:2:"id";s:8:"feedback";}}';
            $item2 = unserialize($item2);
            foreach($item2 as $k => &$v){
                if(isset($v['data'])){
                    foreach($v['data'] as $kk => &$vv){
                        if(isset($vv['imgurl']) && strpos($vv['imgurl'],'https://four.nttrip.cn/') !== false){
                            $vv['imgurl'] = ROOT_HOST."/diypage/".explode('https://four.nttrip.cn/', $vv['imgurl'])[1];
                        }
                    }
                }
            }
            $page2['items'] = serialize($item2);
            $page2['page'] = 'a:7:{s:10:"background";s:7:"#f1f1f1";s:13:"topbackground";s:7:"#ffa844";s:8:"topcolor";s:1:"2";s:9:"styledata";s:1:"0";s:5:"title";s:12:"专属包车";s:4:"name";s:12:"专属包车";s:10:"visitlevel";a:2:{s:6:"member";s:0:"";s:10:"commission";s:0:"";}}';
            $page2['tpl_name'] = "专属包车";

            $page3 = [];
            $page3['uniacid'] = $uniacid;
            $page3['index'] = 0;
            $item3 = 'a:1:{s:14:"M1554284275665";a:4:{s:4:"icon";s:19:"iconfont2 icon-zutu";s:6:"params";a:7:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:12:"content_type";s:1:"1";}s:5:"style";a:7:{s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"background";s:7:"#ffffff";s:9:"viewcount";s:2:"10";}s:2:"id";s:5:"mlist";}}';
            $item3 = unserialize($item3);
            foreach($item3 as $k => &$v){
                if(isset($v['data'])){
                    foreach($v['data'] as $kk => &$vv){
                        if(isset($vv['imgurl']) && strpos($vv['imgurl'],'https://four.nttrip.cn/') !== false){
                            $vv['imgurl'] = ROOT_HOST."/diypage/".explode('https://four.nttrip.cn/', $vv['imgurl'])[1];
                        }
                    }
                }
            }
            $page3['items'] = serialize($item3);
            $page3['page'] = 'a:7:{s:10:"background";s:7:"#f1f1f1";s:13:"topbackground";s:7:"#ffa844";s:8:"topcolor";s:1:"2";s:9:"styledata";s:1:"0";s:5:"title";s:9:"特产店";s:4:"name";s:9:"特产店";s:10:"visitlevel";a:2:{s:6:"member";s:0:"";s:10:"commission";s:0:"";}}';
            $page3['tpl_name'] = "特产店";

            $page1_id = Db::name('wd_xcx_diypage')->insertGetId($page1);
            $page2_id = Db::name('wd_xcx_diypage')->insertGetId($page2);
            $page3_id = Db::name('wd_xcx_diypage')->insertGetId($page3);

            $pageids = $page1_id.",".$page2_id.",".$page3_id;

            $is = Db::name('wd_xcx_diypageset')->where('uniacid',$uniacid)->find();
            if(empty($is)){
                $page_set = [
                    'uniacid' => $uniacid,
                    'kp' => ROOT_HOST."/diypage/resource/images/diypage/default/default_start.jpg",
                    'kp_is' => 2,
                    'kp_m' => 2,
                    'tc' => ROOT_HOST."/diypage/resource/images/diypage/default/tcgg.jpg",
                    'tc_is' => 2,
                    'foot_is' => 2
                ];
                Db::name('wd_xcx_diypageset')->insert($page_set);
            }else{
                Db::name('wd_xcx_diypageset')->where('uniacid',$uniacid)->update(array('foot_is' => 2));
            }
            $data = [
                'uniacid' => $uniacid,
                'pageid' => $pageids,
                'template_name' => '旅游类模板01',
                'thumb' => "/diypage/template_img/template_travel/cover.png",
                'status' => '1',
                'create_time' => time()
            ];
            $tplid = Db::name('wd_xcx_diypagetpl')->insertGetId($data);
            if($tplid){
                return $tplid;
            }
        }elseif($id == 'm_travel02'){
            $page1 = [];
            $page1['uniacid'] = $uniacid;
            $page1['index'] = 1;
            $item1 = 'a:6:{s:14:"M1564217141415";a:4:{s:4:"icon";s:22:"iconfont2 icon-sousuo1";s:6:"params";a:7:{s:5:"value";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:12:{s:9:"textalign";s:6:"center";s:10:"background";s:7:"#0e81b7";s:2:"bg";s:7:"#1a5d80";s:12:"borderradius";s:1:"5";s:6:"boxpdh";s:2:"10";s:6:"boxpdz";s:2:"15";s:7:"padding";s:1:"5";s:8:"fontsize";s:2:"13";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"color";s:7:"#ffffff";}s:2:"id";s:3:"ssk";}s:14:"M1564217145150";a:5:{s:4:"icon";s:28:"iconfont2 icon-tuoyuankaobei";s:6:"params";a:10:{s:5:"totle";s:1:"2";s:8:"navstyle";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:9:"navstyle2";s:1:"0";s:4:"imgh";s:3:"200";}s:5:"style";a:18:{s:8:"dotstyle";s:5:"round";s:8:"dotalign";s:4:"left";s:10:"paddingtop";s:1:"0";s:11:"paddingleft";s:1:"0";s:10:"background";s:7:"#ffffff";s:13:"backgroundall";s:7:"#ffffff";s:9:"leftright";s:1:"5";s:6:"bottom";s:2:"10";s:7:"opacity";s:3:"0.5";s:10:"text_color";s:4:"#fff";s:2:"bg";s:7:"#000000";s:9:"jsq_color";s:3:"red";s:3:"pdh";s:1:"0";s:3:"pdw";s:1:"0";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"speed";s:1:"5";}s:4:"data";a:3:{s:14:"C1564217145151";a:4:{s:6:"imgurl";s:78:"https://four.nttrip.cn/diypage/template_img/template_travel02/index/banner.jpg";s:7:"linkurl";s:0:"";s:6:"single";s:1:"1";s:4:"text";s:12:"文字描述";}s:14:"C1564217145152";a:4:{s:6:"imgurl";s:79:"https://four.nttrip.cn/diypage/template_img/template_travel02/index/banner2.jpg";s:7:"linkurl";s:0:"";s:6:"single";s:1:"2";s:4:"text";s:12:"文字描述";}s:14:"M1565248639541";a:4:{s:6:"imgurl";s:79:"https://four.nttrip.cn/diypage/template_img/template_travel02/index/banner3.jpg";s:7:"linkurl";s:0:"";s:6:"single";s:1:"1";s:4:"text";s:12:"文字描述";}}s:2:"id";s:6:"banner";}s:14:"M1564217392001";a:5:{s:4:"icon";s:22:"iconfont2 icon-anniuzu";s:6:"params";a:8:{s:9:"styledata";s:1:"1";s:6:"repeat";s:9:"no-repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"1";s:13:"backgroundimg";s:58:"/upimages/20190727/ef273407d42fd0780169d9ef1f2c293e464.png";s:7:"picicon";s:1:"1";s:8:"textshow";s:1:"1";}s:5:"style";a:14:{s:8:"navstyle";s:0:"";s:10:"background";s:7:"#0e81b7";s:6:"rownum";s:1:"4";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"8";s:7:"showdot";s:1:"1";s:7:"padding";s:1:"0";s:11:"paddingleft";s:2:"10";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:6:"iconfz";s:2:"14";s:9:"iconcolor";s:7:"#434343";s:8:"imgwidth";s:2:"45";}s:4:"data";a:4:{s:14:"C1564217392001";a:5:{s:6:"imgurl";s:77:"https://four.nttrip.cn/diypage/template_img/template_travel02/index/menu1.png";s:7:"linkurl";s:0:"";s:4:"text";s:6:"交通";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1564217392002";a:5:{s:6:"imgurl";s:77:"https://four.nttrip.cn/diypage/template_img/template_travel02/index/menu2.png";s:7:"linkurl";s:0:"";s:4:"text";s:6:"住宿";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1564217392003";a:5:{s:6:"imgurl";s:77:"https://four.nttrip.cn/diypage/template_img/template_travel02/index/menu3.png";s:7:"linkurl";s:0:"";s:4:"text";s:6:"门票";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1564217392004";a:5:{s:6:"imgurl";s:77:"https://four.nttrip.cn/diypage/template_img/template_travel02/index/menu4.png";s:7:"linkurl";s:0:"";s:4:"text";s:6:"攻略";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}}s:2:"id";s:4:"menu";}s:14:"M1564217995336";a:5:{s:4:"icon";s:21:"iconfont2 icon-tupian";s:6:"params";a:9:{s:3:"row";s:1:"2";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"2";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:8:{s:10:"paddingtop";s:1:"5";s:11:"paddingleft";s:1:"5";s:7:"showdot";s:1:"0";s:7:"showbtn";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:1:"0";s:10:"background";s:7:"#ffffff";}s:4:"data";a:2:{s:14:"C1564217995336";a:2:{s:6:"imgurl";s:81:"https://four.nttrip.cn/diypage/template_img/template_travel02/index/picturew1.png";s:7:"linkurl";s:0:"";}s:14:"C1564217995337";a:2:{s:6:"imgurl";s:81:"https://four.nttrip.cn/diypage/template_img/template_travel02/index/picturew2.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:8:"picturew";}s:14:"M1564218104511";a:4:{s:4:"icon";s:32:"iconfont2 icon-liebiao_biaotilan";s:6:"params";a:9:{s:5:"title";s:12:"推荐酒店";s:6:"title2";s:36:"花一样的钱，住特色的酒店";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:5:"style";s:1:"1";}s:5:"style";a:11:{s:10:"background";s:7:"#ffffff";s:6:"colorz";s:7:"#000000";s:6:"colorf";s:7:"#838383";s:9:"linecolor";s:7:"#4095d8";s:9:"fontsizez";s:2:"18";s:9:"fontsizef";s:2:"14";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:2:"10";}s:2:"id";s:6:"title2";}s:14:"M1564218215934";a:5:{s:4:"icon";s:22:"iconfont2 icon-chanpin";s:6:"params";a:30:{s:11:"goodsscroll";s:1:"0";s:9:"showtitle";s:1:"1";s:9:"showprice";s:1:"1";s:7:"showtag";s:1:"0";s:9:"goodsdata";s:1:"1";s:6:"cateid";s:0:"";s:8:"catename";s:0:"";s:7:"groupid";s:0:"";s:9:"groupname";s:0:"";s:9:"goodssort";s:1:"0";s:8:"goodsnum";s:1:"6";s:8:"showicon";s:1:"0";s:12:"iconposition";s:8:"left top";s:12:"productprice";s:1:"1";s:16:"showproductprice";s:1:"0";s:9:"showsales";s:1:"1";s:16:"productpricetext";s:6:"原价";s:9:"salestext";s:6:"销量";s:16:"productpriceline";s:1:"0";s:7:"saleout";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"imgh_is";s:1:"1";s:4:"imgh";s:3:"100";s:7:"con_key";s:1:"1";s:8:"con_type";s:1:"1";}s:5:"style";a:20:{s:10:"background";s:7:"#ffffff";s:9:"liststyle";s:5:"block";s:8:"buystyle";s:0:"";s:9:"goodsicon";s:9:"recommand";s:9:"iconstyle";s:8:"triangle";s:10:"pricecolor";s:7:"#ff5555";s:17:"productpricecolor";s:7:"#999999";s:14:"iconpaddingtop";s:1:"0";s:15:"iconpaddingleft";s:1:"0";s:11:"buybtncolor";s:7:"#ff5555";s:8:"iconzoom";s:2:"50";s:10:"titlecolor";s:7:"#000000";s:13:"tagbackground";s:7:"#fe5455";s:10:"salescolor";s:7:"#999999";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"paddingtop";s:1:"5";s:11:"paddingleft";s:2:"10";s:8:"showtype";s:1:"0";}s:4:"data";a:4:{s:14:"C1564218215934";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:3:"des";s:21:"这里是产品描述";}s:14:"C1564218215935";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1564218215936";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1564218215937";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}}s:2:"id";s:7:"reserve";}}';
            $item1 = unserialize($item1);
            foreach($item1 as $k => &$v){
                if(isset($v['data'])){
                    foreach($v['data'] as $kk => &$vv){
                        if(isset($vv['imgurl']) && strpos($vv['imgurl'],'https://four.nttrip.cn') !== false){
                            $vv['imgurl'] = ROOT_HOST.explode('https://four.nttrip.cn', $vv['imgurl'])[1];
                        }
                        if(isset($vv['thumb']) && strpos($vv['thumb'],'/diypage/resource/images/diypage/default/2.jpg') !== false){
                            $vv['thumb'] = ROOT_HOST."/diypage/resource/images/diypage/default/2.jpg";
                        }
                    }
                }
            }
            $page1['items'] = serialize($item1);
            $page1['page'] = 'a:7:{s:10:"background";s:7:"#f1f1f1";s:13:"topbackground";s:7:"#ffffff";s:8:"topcolor";s:1:"1";s:9:"styledata";s:1:"0";s:5:"title";s:6:"首页";s:4:"name";s:6:"首页";s:10:"visitlevel";a:2:{s:6:"member";s:0:"";s:10:"commission";s:0:"";}}';
            $page1['tpl_name'] = "首页";

            $page1_id = Db::name('wd_xcx_diypage')->insertGetId($page1);
            $pageids = $page1_id;

            $is = Db::name('wd_xcx_diypageset')->where('uniacid',$uniacid)->find();
            if(empty($is)){
                $page_set = [
                    'uniacid' => $uniacid,
                    'kp' => ROOT_HOST."/diypage/resource/images/diypage/default/default_start.jpg",
                    'kp_is' => 2,
                    'kp_m' => 2,
                    'tc' => ROOT_HOST."/diypage/resource/images/diypage/default/tcgg.jpg",
                    'tc_is' => 2,
                    'foot_is' => 2
                ];
                Db::name('wd_xcx_diypageset')->insert($page_set);
            }else{
                Db::name('wd_xcx_diypageset')->where('uniacid',$uniacid)->update(array('foot_is' => 2));
            }
            $data = [
                'uniacid' => $uniacid,
                'pageid' => $pageids,
                'template_name' => '旅游类模板02',
                'thumb' => "/diypage/template_img/template_travel02/cover.png",
                'status' => '1',
                'create_time' => time()
            ];
            $tplid = Db::name('wd_xcx_diypagetpl')->insertGetId($data);
            if($tplid){
                return $tplid;
            }
        }elseif($id == 'm_wedding'){
            $page1 = [];
            $page1['uniacid'] = $uniacid;
            $page1['index'] = 1;
            $item1 = 'a:9:{s:14:"M1564366187707";a:4:{s:4:"icon";s:22:"iconfont2 icon-sousuo1";s:6:"params";a:7:{s:5:"value";s:0:"";s:9:"styledata";s:1:"1";s:6:"repeat";s:9:"no-repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"1";s:13:"backgroundimg";s:77:"https://four.nttrip.cn/diypage/template_img/template_wedding/index/ssk_bg.jpg";}s:5:"style";a:12:{s:9:"textalign";s:4:"left";s:10:"background";s:7:"#ffffff";s:2:"bg";s:4:"#fff";s:12:"borderradius";s:2:"20";s:6:"boxpdh";s:2:"10";s:6:"boxpdz";s:2:"30";s:7:"padding";s:1:"5";s:8:"fontsize";s:2:"13";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"color";s:0:"";}s:2:"id";s:3:"ssk";}s:14:"M1564366190455";a:5:{s:4:"icon";s:28:"iconfont2 icon-tuoyuankaobei";s:6:"params";a:10:{s:5:"totle";s:1:"2";s:8:"navstyle";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:9:"navstyle2";s:1:"0";s:4:"imgh";s:3:"200";}s:5:"style";a:18:{s:8:"dotstyle";s:5:"round";s:8:"dotalign";s:4:"left";s:10:"paddingtop";s:1:"0";s:11:"paddingleft";s:1:"0";s:10:"background";s:7:"#ffffff";s:13:"backgroundall";s:7:"#ffffff";s:9:"leftright";s:1:"5";s:6:"bottom";s:1:"5";s:7:"opacity";s:3:"0.8";s:10:"text_color";s:4:"#fff";s:2:"bg";s:7:"#000000";s:9:"jsq_color";s:3:"red";s:3:"pdh";s:1:"0";s:3:"pdw";s:1:"0";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"speed";s:1:"5";}s:4:"data";a:1:{s:14:"C1564366190456";a:4:{s:6:"imgurl";s:77:"https://four.nttrip.cn/diypage/template_img/template_wedding/index/banner.jpg";s:7:"linkurl";s:0:"";s:6:"single";s:1:"2";s:4:"text";s:12:"文字描述";}}s:2:"id";s:6:"banner";}s:14:"M1564369573800";a:5:{s:4:"icon";s:22:"iconfont2 icon-anniuzu";s:6:"params";a:8:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"picicon";s:1:"1";s:8:"textshow";s:1:"1";}s:5:"style";a:14:{s:8:"navstyle";s:0:"";s:10:"background";s:7:"#ffffff";s:6:"rownum";s:1:"4";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"8";s:7:"showdot";s:1:"1";s:7:"padding";s:1:"0";s:11:"paddingleft";s:2:"10";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:6:"iconfz";s:2:"14";s:9:"iconcolor";s:7:"#434343";s:8:"imgwidth";s:2:"50";}s:4:"data";a:8:{s:14:"C1564369573800";a:5:{s:6:"imgurl";s:76:"https://four.nttrip.cn/diypage/template_img/template_wedding/index/menu1.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"婚纱拍摄";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1564369573801";a:5:{s:6:"imgurl";s:76:"https://four.nttrip.cn/diypage/template_img/template_wedding/index/menu2.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"全球旅拍";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1564369573802";a:5:{s:6:"imgurl";s:76:"https://four.nttrip.cn/diypage/template_img/template_wedding/index/menu3.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"婚宴酒店";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1564369573803";a:5:{s:6:"imgurl";s:76:"https://four.nttrip.cn/diypage/template_img/template_wedding/index/menu4.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"婚礼服务";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"M1564369603754";a:5:{s:6:"imgurl";s:76:"https://four.nttrip.cn/diypage/template_img/template_wedding/index/menu5.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"婚礼拍摄";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"M1564369604881";a:5:{s:6:"imgurl";s:76:"https://four.nttrip.cn/diypage/template_img/template_wedding/index/menu6.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"婚礼百科";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"M1564369605969";a:5:{s:6:"imgurl";s:76:"https://four.nttrip.cn/diypage/template_img/template_wedding/index/menu7.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"婚礼顾问";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"M1564369607025";a:5:{s:6:"imgurl";s:76:"https://four.nttrip.cn/diypage/template_img/template_wedding/index/menu8.png";s:7:"linkurl";s:0:"";s:4:"text";s:9:"新人说";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}}s:2:"id";s:4:"menu";}s:14:"M1564369875150";a:5:{s:4:"icon";s:23:"iconfont2 icon-daohang1";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:10:{s:9:"margintop";s:2:"10";s:10:"background";s:7:"#ffffff";s:9:"iconcolor";s:7:"#f25147";s:9:"textcolor";s:7:"#000000";s:11:"remarkcolor";s:7:"#888888";s:5:"sizew";s:2:"20";s:11:"paddingleft";s:2:"10";s:7:"padding";s:1:"5";s:5:"sizeh";s:2:"20";s:9:"linecolor";s:7:"#ffffff";}s:4:"data";a:1:{s:14:"C1564369875150";a:5:{s:4:"text";s:12:"婚纱拍摄";s:7:"linkurl";s:0:"";s:9:"iconclass";s:14:"icon-c-xiangji";s:6:"remark";s:6:"更多";s:6:"dotnum";s:0:"";}}s:2:"id";s:8:"listmenu";}s:14:"M1564378979150";a:5:{s:4:"icon";s:21:"iconfont2 icon-tupian";s:6:"params";a:9:{s:3:"row";s:1:"3";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"2";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:8:{s:10:"paddingtop";s:1:"5";s:11:"paddingleft";s:2:"10";s:7:"showdot";s:1:"0";s:7:"showbtn";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:1:"0";s:10:"background";s:7:"#ffffff";}s:4:"data";a:3:{s:14:"C1564378979150";a:2:{s:6:"imgurl";s:82:"https://four.nttrip.cn/diypage/template_img/template_wedding/index/picturew1_1.png";s:7:"linkurl";s:0:"";}s:14:"C1564378979151";a:2:{s:6:"imgurl";s:82:"https://four.nttrip.cn/diypage/template_img/template_wedding/index/picturew1_2.png";s:7:"linkurl";s:0:"";}s:14:"C1564378979152";a:2:{s:6:"imgurl";s:82:"https://four.nttrip.cn/diypage/template_img/template_wedding/index/picturew1_3.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:8:"picturew";}s:14:"M1564379063036";a:5:{s:4:"icon";s:23:"iconfont2 icon-daohang1";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:10:{s:9:"margintop";s:2:"10";s:10:"background";s:7:"#ffffff";s:9:"iconcolor";s:7:"#f25147";s:9:"textcolor";s:7:"#000000";s:11:"remarkcolor";s:7:"#888888";s:5:"sizew";s:2:"20";s:11:"paddingleft";s:2:"10";s:7:"padding";s:1:"5";s:5:"sizeh";s:2:"20";s:9:"linecolor";s:7:"#ffffff";}s:4:"data";a:1:{s:14:"C1564379063039";a:5:{s:4:"text";s:12:"全球旅拍";s:7:"linkurl";s:0:"";s:9:"iconclass";s:12:"icon-c-feiji";s:6:"remark";s:6:"更多";s:6:"dotnum";s:0:"";}}s:2:"id";s:8:"listmenu";}s:14:"M1564379188291";a:6:{s:4:"icon";s:21:"iconfont2 icon-tupian";s:6:"params";a:9:{s:3:"row";s:1:"3";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"2";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:8:{s:10:"paddingtop";s:1:"5";s:11:"paddingleft";s:2:"10";s:7:"showdot";s:1:"0";s:7:"showbtn";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:1:"0";s:10:"background";s:7:"#ffffff";}s:4:"data";a:3:{s:14:"C1564379188292";a:2:{s:6:"imgurl";s:82:"https://four.nttrip.cn/diypage/template_img/template_wedding/index/picturew2_1.png";s:7:"linkurl";s:0:"";}s:14:"C1564379188293";a:2:{s:6:"imgurl";s:82:"https://four.nttrip.cn/diypage/template_img/template_wedding/index/picturew2_2.png";s:7:"linkurl";s:0:"";}s:14:"C1564379188294";a:2:{s:6:"imgurl";s:82:"https://four.nttrip.cn/diypage/template_img/template_wedding/index/picturew2_3.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:8:"picturew";s:5:"index";s:3:"NaN";}s:14:"M1564379409884";a:6:{s:4:"icon";s:23:"iconfont2 icon-daohang1";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:10:{s:9:"margintop";s:2:"10";s:10:"background";s:7:"#ffffff";s:9:"iconcolor";s:7:"#f25147";s:9:"textcolor";s:7:"#000000";s:11:"remarkcolor";s:7:"#888888";s:5:"sizew";s:2:"20";s:11:"paddingleft";s:2:"10";s:7:"padding";s:1:"5";s:5:"sizeh";s:2:"20";s:9:"linecolor";s:7:"#ffffff";}s:4:"data";a:1:{s:14:"C1564379409886";a:5:{s:4:"text";s:12:"婚宴酒店";s:7:"linkurl";s:0:"";s:9:"iconclass";s:10:"icon-c-lou";s:6:"remark";s:6:"更多";s:6:"dotnum";s:0:"";}}s:2:"id";s:8:"listmenu";s:5:"index";s:3:"NaN";}s:14:"M1564379591181";a:5:{s:4:"icon";s:22:"iconfont2 icon-chanpin";s:6:"params";a:30:{s:11:"goodsscroll";s:1:"0";s:9:"showtitle";s:1:"1";s:9:"showprice";s:1:"1";s:7:"showtag";s:1:"0";s:9:"goodsdata";s:1:"1";s:6:"cateid";s:0:"";s:8:"catename";s:0:"";s:7:"groupid";s:0:"";s:9:"groupname";s:0:"";s:9:"goodssort";s:1:"0";s:8:"goodsnum";s:1:"6";s:8:"showicon";s:1:"0";s:12:"iconposition";s:8:"left top";s:12:"productprice";s:1:"1";s:16:"showproductprice";s:1:"0";s:9:"showsales";s:1:"1";s:16:"productpricetext";s:6:"原价";s:9:"salestext";s:6:"销量";s:16:"productpriceline";s:1:"0";s:7:"saleout";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"imgh_is";s:1:"1";s:4:"imgh";s:3:"100";s:7:"con_key";s:1:"1";s:8:"con_type";s:1:"1";}s:5:"style";a:20:{s:10:"background";s:7:"#ffffff";s:9:"liststyle";s:5:"block";s:8:"buystyle";s:0:"";s:9:"goodsicon";s:9:"recommand";s:9:"iconstyle";s:8:"triangle";s:10:"pricecolor";s:7:"#ff5555";s:17:"productpricecolor";s:7:"#999999";s:14:"iconpaddingtop";s:1:"0";s:15:"iconpaddingleft";s:1:"0";s:11:"buybtncolor";s:7:"#ff5555";s:8:"iconzoom";s:2:"50";s:10:"titlecolor";s:7:"#000000";s:13:"tagbackground";s:7:"#fe5455";s:10:"salescolor";s:7:"#999999";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:8:"showtype";s:1:"0";}s:4:"data";a:4:{s:14:"C1564379591181";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:3:"des";s:21:"这里是产品描述";}s:14:"C1564379591182";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1564379591183";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1564379591184";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}}s:2:"id";s:7:"reserve";}}';
            $item1 = unserialize($item1);
            foreach($item1 as $k => &$v){
                if(isset($v['data'])){
                    foreach($v['data'] as $kk => &$vv){
                        if(isset($vv['imgurl']) && strpos($vv['imgurl'],'https://four.nttrip.cn') !== false){
                            $vv['imgurl'] = ROOT_HOST.explode('https://four.nttrip.cn', $vv['imgurl'])[1];
                        }
                        if(isset($vv['thumb']) && strpos($vv['thumb'],'/diypage/resource/images/diypage/default/2.jpg') !== false){
                            $vv['thumb'] = ROOT_HOST."/diypage/resource/images/diypage/default/2.jpg";
                        }
                    }
                }
            }
            $page1['items'] = serialize($item1);
            $page1['page'] = 'a:7:{s:10:"background";s:7:"#f1f1f1";s:13:"topbackground";s:7:"#ffffff";s:8:"topcolor";s:1:"1";s:9:"styledata";s:1:"0";s:5:"title";s:6:"首页";s:4:"name";s:6:"首页";s:10:"visitlevel";a:2:{s:6:"member";s:0:"";s:10:"commission";s:0:"";}}';
            $page1['tpl_name'] = "首页";

            $page1_id = Db::name('wd_xcx_diypage')->insertGetId($page1);
            $pageids = $page1_id;

            $is = Db::name('wd_xcx_diypageset')->where('uniacid',$uniacid)->find();
            if(empty($is)){
                $page_set = [
                    'uniacid' => $uniacid,
                    'kp' => ROOT_HOST."/diypage/resource/images/diypage/default/default_start.jpg",
                    'kp_is' => 2,
                    'kp_m' => 2,
                    'tc' => ROOT_HOST."/diypage/resource/images/diypage/default/tcgg.jpg",
                    'tc_is' => 2,
                    'foot_is' => 2
                ];
                Db::name('wd_xcx_diypageset')->insert($page_set);
            }else{
                Db::name('wd_xcx_diypageset')->where('uniacid',$uniacid)->update(array('foot_is' => 2));
            }
            $data = [
                'uniacid' => $uniacid,
                'pageid' => $pageids,
                'template_name' => '婚纱摄影类模板01',
                'thumb' => "/diypage/template_img/template_wedding/cover.png",
                'status' => '1',
                'create_time' => time()
            ];
            $tplid = Db::name('wd_xcx_diypagetpl')->insertGetId($data);
            if($tplid){
                return $tplid;
            }
        }elseif($id == 'm_pet'){
            $page1 = [];
            $page1['uniacid'] = $uniacid;
            $page1['index'] = 1;
            $item1 = 'a:7:{s:14:"M1564560358617";a:5:{s:4:"icon";s:28:"iconfont2 icon-tuoyuankaobei";s:6:"params";a:10:{s:5:"totle";s:1:"2";s:8:"navstyle";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:9:"navstyle2";s:1:"0";s:4:"imgh";s:3:"130";}s:5:"style";a:18:{s:8:"dotstyle";s:5:"round";s:8:"dotalign";s:4:"left";s:10:"paddingtop";s:1:"0";s:11:"paddingleft";s:1:"0";s:10:"background";s:7:"#ffffff";s:13:"backgroundall";s:7:"#ffffff";s:9:"leftright";s:1:"5";s:6:"bottom";s:2:"10";s:7:"opacity";s:3:"0.4";s:10:"text_color";s:4:"#fff";s:2:"bg";s:7:"#000000";s:9:"jsq_color";s:3:"red";s:3:"pdh";s:1:"0";s:3:"pdw";s:1:"0";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"speed";s:1:"5";}s:4:"data";a:1:{s:14:"C1564560358617";a:4:{s:6:"imgurl";s:73:"https://four.nttrip.cn/diypage/template_img/template_pet/index/banner.jpg";s:7:"linkurl";s:0:"";s:6:"single";s:1:"1";s:4:"text";s:12:"文字描述";}}s:2:"id";s:6:"banner";}s:14:"M1564560457479";a:5:{s:4:"icon";s:22:"iconfont2 icon-anniuzu";s:6:"params";a:8:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"picicon";s:1:"1";s:8:"textshow";s:1:"1";}s:5:"style";a:14:{s:8:"navstyle";s:0:"";s:10:"background";s:7:"#ffffff";s:6:"rownum";s:1:"5";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"8";s:7:"showdot";s:1:"1";s:7:"padding";s:2:"10";s:11:"paddingleft";s:2:"10";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:6:"iconfz";s:2:"14";s:9:"iconcolor";s:7:"#434343";s:8:"imgwidth";s:2:"40";}s:4:"data";a:5:{s:14:"C1564560457479";a:5:{s:6:"imgurl";s:72:"https://four.nttrip.cn/diypage/template_img/template_pet/index/menu1.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"新品推荐";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1564560457480";a:5:{s:6:"imgurl";s:72:"https://four.nttrip.cn/diypage/template_img/template_pet/index/menu2.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"狗狗专题";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1564560457481";a:5:{s:6:"imgurl";s:72:"https://four.nttrip.cn/diypage/template_img/template_pet/index/menu3.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"品种百科";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1564560457482";a:5:{s:6:"imgurl";s:72:"https://four.nttrip.cn/diypage/template_img/template_pet/index/menu4.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"最新活动";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"M1564560756648";a:5:{s:6:"imgurl";s:72:"https://four.nttrip.cn/diypage/template_img/template_pet/index/menu5.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"狗友舆论";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}}s:2:"id";s:4:"menu";}s:14:"M1564561308533";a:5:{s:4:"icon";s:21:"iconfont2 icon-tupian";s:6:"params";a:9:{s:3:"row";s:1:"1";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"2";s:9:"styledata";s:1:"1";s:6:"repeat";s:9:"no-repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:58:"/upimages/20190731/8746660c669231cf6dc101633485c62f426.png";}s:5:"style";a:8:{s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:7:"showdot";s:1:"0";s:7:"showbtn";s:1:"0";s:5:"sizew";s:3:"100";s:5:"sizeh";s:3:"100";s:2:"mt";s:2:"10";s:10:"background";s:7:"#f1f1f1";}s:4:"data";a:4:{s:14:"C1564561308533";a:2:{s:6:"imgurl";s:78:"https://four.nttrip.cn/diypage/template_img/template_pet/index/picturew1_1.png";s:7:"linkurl";s:0:"";}s:14:"C1564561308534";a:2:{s:6:"imgurl";s:78:"https://four.nttrip.cn/diypage/template_img/template_pet/index/picturew1_2.png";s:7:"linkurl";s:0:"";}s:14:"C1564561308535";a:2:{s:6:"imgurl";s:78:"https://four.nttrip.cn/diypage/template_img/template_pet/index/picturew1_3.png";s:7:"linkurl";s:0:"";}s:14:"C1564561308536";a:2:{s:6:"imgurl";s:78:"https://four.nttrip.cn/diypage/template_img/template_pet/index/picturew1_4.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:8:"picturew";}s:14:"M1564564214841";a:5:{s:4:"icon";s:22:"iconfont2 icon-gonggao";s:6:"params";a:12:{s:7:"iconurl";s:14:"icon-c-gonggao";s:10:"noticedata";s:1:"0";s:5:"speed";s:1:"4";s:9:"noticenum";s:1:"5";s:8:"navstyle";s:1:"1";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:8:"sourceid";s:0:"";}s:5:"style";a:9:{s:10:"background";s:7:"#ffffff";s:9:"iconcolor";s:7:"#fd5454";s:5:"color";s:7:"#666666";s:11:"bordercolor";s:7:"#e2e2e2";s:2:"mt";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";}s:4:"data";a:2:{s:14:"C1564564214841";a:2:{s:5:"title";s:42:"这里是第一条自定义公告的标题";s:7:"linkurl";s:0:"";}s:14:"C1564564214842";a:2:{s:5:"title";s:42:"这里是第二条自定义公告的标题";s:7:"linkurl";s:0:"";}}s:2:"id";s:6:"notice";}s:14:"M1564564242168";a:5:{s:4:"icon";s:23:"iconfont2 icon-daohang1";s:6:"params";a:6:{s:9:"styledata";s:1:"1";s:6:"repeat";s:9:"no-repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:58:"/upimages/20190731/24624d16049c9c95e17be2d045040ef1376.png";}s:5:"style";a:10:{s:9:"margintop";s:2:"10";s:10:"background";s:7:"#f1f1f1";s:9:"iconcolor";s:7:"#999999";s:9:"textcolor";s:7:"#000000";s:11:"remarkcolor";s:7:"#888888";s:5:"sizew";s:3:"100";s:11:"paddingleft";s:2:"15";s:7:"padding";s:1:"5";s:5:"sizeh";s:3:"100";s:9:"linecolor";s:7:"#ffffff";}s:4:"data";a:1:{s:14:"C1564564242168";a:5:{s:4:"text";s:12:"新宠露脸";s:7:"linkurl";s:0:"";s:9:"iconclass";s:0:"";s:6:"remark";s:6:"更多";s:6:"dotnum";s:0:"";}}s:2:"id";s:8:"listmenu";}s:14:"M1564564321720";a:6:{s:4:"icon";s:21:"iconfont2 icon-tupian";s:6:"params";a:9:{s:3:"row";s:1:"3";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"2";s:9:"styledata";s:1:"1";s:6:"repeat";s:9:"no-repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:58:"/upimages/20190731/d8e7c6fcc881ab0c4a123503f846afca379.png";}s:5:"style";a:8:{s:10:"paddingtop";s:1:"8";s:11:"paddingleft";s:1:"8";s:7:"showdot";s:1:"0";s:7:"showbtn";s:1:"0";s:5:"sizew";s:3:"100";s:5:"sizeh";s:3:"100";s:2:"mt";s:1:"0";s:10:"background";s:7:"#f1f1f1";}s:4:"data";a:3:{s:14:"C1564564321721";a:2:{s:6:"imgurl";s:78:"https://four.nttrip.cn/diypage/template_img/template_pet/index/picturew2_1.png";s:7:"linkurl";s:0:"";}s:14:"C1564564321722";a:2:{s:6:"imgurl";s:78:"https://four.nttrip.cn/diypage/template_img/template_pet/index/picturew2_2.png";s:7:"linkurl";s:0:"";}s:14:"C1564564321723";a:2:{s:6:"imgurl";s:78:"https://four.nttrip.cn/diypage/template_img/template_pet/index/picturew2_3.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:8:"picturew";s:5:"index";s:3:"NaN";}s:14:"M1564566440897";a:4:{s:4:"icon";s:24:"iconfont2 icon-xiankuang";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:5:{s:6:"height";s:2:"20";s:10:"background";s:7:"#ffffff";s:2:"mt";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";}s:2:"id";s:5:"blank";}}';
            $item1 = unserialize($item1);
            foreach($item1 as $k => &$v){
                if(isset($v['data'])){
                    foreach($v['data'] as $kk => &$vv){
                        if(isset($vv['imgurl']) && strpos($vv['imgurl'],'https://four.nttrip.cn') !== false){
                            $vv['imgurl'] = ROOT_HOST.explode('https://four.nttrip.cn', $vv['imgurl'])[1];
                        }
                    }
                }
            }
            $page1['items'] = serialize($item1);
            $page1['page'] = 'a:7:{s:10:"background";s:7:"#f1f1f1";s:13:"topbackground";s:7:"#ffffff";s:8:"topcolor";s:1:"1";s:9:"styledata";s:1:"0";s:5:"title";s:6:"首页";s:4:"name";s:6:"首页";s:10:"visitlevel";a:2:{s:6:"member";s:0:"";s:10:"commission";s:0:"";}}';
            $page1['tpl_name'] = "首页";

            $page1_id = Db::name('wd_xcx_diypage')->insertGetId($page1);
            $pageids = $page1_id;

            $is = Db::name('wd_xcx_diypageset')->where('uniacid',$uniacid)->find();
            if(empty($is)){
                $page_set = [
                    'uniacid' => $uniacid,
                    'kp' => ROOT_HOST."/diypage/resource/images/diypage/default/default_start.jpg",
                    'kp_is' => 2,
                    'kp_m' => 2,
                    'tc' => ROOT_HOST."/diypage/resource/images/diypage/default/tcgg.jpg",
                    'tc_is' => 2,
                    'foot_is' => 2
                ];
                Db::name('wd_xcx_diypageset')->insert($page_set);
            }else{
                Db::name('wd_xcx_diypageset')->where('uniacid',$uniacid)->update(array('foot_is' => 2));
            }
            $data = [
                'uniacid' => $uniacid,
                'pageid' => $pageids,
                'template_name' => '宠物类模板01',
                'thumb' => "/diypage/template_img/template_pet/cover.png",
                'status' => '1',
                'create_time' => time()
            ];
            $tplid = Db::name('wd_xcx_diypagetpl')->insertGetId($data);
            if($tplid){
                return $tplid;
            }
        }elseif($id == 'm_renovation'){
            $page1 = [];
            $page1['uniacid'] = $uniacid;
            $page1['index'] = 1;
            $item1 = 'a:13:{s:14:"M1564391860406";a:5:{s:4:"icon";s:28:"iconfont2 icon-tuoyuankaobei";s:6:"params";a:10:{s:5:"totle";s:1:"2";s:8:"navstyle";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:9:"navstyle2";s:1:"0";s:4:"imgh";s:3:"200";}s:5:"style";a:18:{s:8:"dotstyle";s:5:"round";s:8:"dotalign";s:4:"left";s:10:"paddingtop";s:1:"0";s:11:"paddingleft";s:1:"0";s:10:"background";s:7:"#ffffff";s:13:"backgroundall";s:7:"#ffffff";s:9:"leftright";s:1:"5";s:6:"bottom";s:2:"10";s:7:"opacity";s:3:"0.5";s:10:"text_color";s:4:"#fff";s:2:"bg";s:7:"#000000";s:9:"jsq_color";s:3:"red";s:3:"pdh";s:1:"0";s:3:"pdw";s:1:"0";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"speed";s:1:"5";}s:4:"data";a:1:{s:14:"C1564391860406";a:4:{s:6:"imgurl";s:80:"https://four.nttrip.cn/diypage/template_img/template_renovation/index/banner.jpg";s:7:"linkurl";s:0:"";s:6:"single";s:1:"1";s:4:"text";s:12:"文字描述";}}s:2:"id";s:6:"banner";}s:14:"M1564392374788";a:5:{s:4:"icon";s:22:"iconfont2 icon-anniuzu";s:6:"params";a:8:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"picicon";s:1:"1";s:8:"textshow";s:1:"1";}s:5:"style";a:14:{s:8:"navstyle";s:0:"";s:10:"background";s:7:"#ffffff";s:6:"rownum";s:1:"4";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"8";s:7:"showdot";s:1:"1";s:7:"padding";s:2:"10";s:11:"paddingleft";s:2:"10";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:6:"iconfz";s:2:"14";s:9:"iconcolor";s:7:"#434343";s:8:"imgwidth";s:2:"51";}s:4:"data";a:4:{s:14:"C1564392374788";a:5:{s:6:"imgurl";s:79:"https://four.nttrip.cn/diypage/template_img/template_renovation/index/menu1.png";s:7:"linkurl";s:0:"";s:4:"text";s:9:"找设计";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1564392374789";a:5:{s:6:"imgurl";s:79:"https://four.nttrip.cn/diypage/template_img/template_renovation/index/menu2.png";s:7:"linkurl";s:0:"";s:4:"text";s:9:"找工队";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1564392374790";a:5:{s:6:"imgurl";s:79:"https://four.nttrip.cn/diypage/template_img/template_renovation/index/menu3.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"装修疑问";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1564392374791";a:5:{s:6:"imgurl";s:79:"https://four.nttrip.cn/diypage/template_img/template_renovation/index/menu4.png";s:7:"linkurl";s:0:"";s:4:"text";s:6:"晒家";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}}s:2:"id";s:4:"menu";}s:14:"M1564392638769";a:5:{s:4:"icon";s:19:"iconfont2 icon-zutu";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:6:{s:10:"paddingtop";s:1:"0";s:11:"paddingleft";s:2:"10";s:2:"mt";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"background";s:7:"#ffffff";}s:4:"data";a:1:{s:14:"C1564392638769";a:2:{s:6:"imgurl";s:82:"https://four.nttrip.cn/diypage/template_img/template_renovation/index/picture1.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:7:"picture";}s:14:"M1564392683265";a:4:{s:4:"icon";s:32:"iconfont2 icon-liebiao_biaotilan";s:6:"params";a:9:{s:5:"title";s:12:"装修攻略";s:6:"title2";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:5:"style";s:1:"3";}s:5:"style";a:11:{s:10:"background";s:7:"#ffffff";s:6:"colorz";s:7:"#000000";s:6:"colorf";s:7:"#838383";s:9:"linecolor";s:7:"#ffffff";s:9:"fontsizez";s:2:"18";s:9:"fontsizef";s:1:"9";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:2:"10";}s:2:"id";s:6:"title2";}s:14:"M1564392733819";a:5:{s:4:"icon";s:21:"iconfont2 icon-tupian";s:6:"params";a:9:{s:3:"row";s:1:"3";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"2";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:8:{s:10:"paddingtop";s:1:"5";s:11:"paddingleft";s:1:"8";s:7:"showdot";s:1:"0";s:7:"showbtn";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:1:"0";s:10:"background";s:7:"#ffffff";}s:4:"data";a:3:{s:14:"C1564392733820";a:2:{s:6:"imgurl";s:83:"https://four.nttrip.cn/diypage/template_img/template_renovation/index/picturew1.png";s:7:"linkurl";s:0:"";}s:14:"C1564392733821";a:2:{s:6:"imgurl";s:83:"https://four.nttrip.cn/diypage/template_img/template_renovation/index/picturew2.png";s:7:"linkurl";s:0:"";}s:14:"C1564392733822";a:2:{s:6:"imgurl";s:83:"https://four.nttrip.cn/diypage/template_img/template_renovation/index/picturew3.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:8:"picturew";}s:14:"M1564392684698";a:5:{s:4:"icon";s:32:"iconfont2 icon-liebiao_biaotilan";s:6:"params";a:9:{s:5:"title";s:12:"热门方案";s:6:"title2";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:5:"style";s:1:"3";}s:5:"style";a:11:{s:10:"background";s:7:"#ffffff";s:6:"colorz";s:7:"#000000";s:6:"colorf";s:7:"#838383";s:9:"linecolor";s:7:"#ffffff";s:9:"fontsizez";s:2:"18";s:9:"fontsizef";s:1:"9";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:2:"10";}s:2:"id";s:6:"title2";s:5:"index";s:3:"NaN";}s:14:"M1564392830483";a:5:{s:4:"icon";s:23:"iconfont2 icon-wenzhang";s:6:"params";a:19:{s:9:"showstyle";s:4:"row1";s:7:"newsnum";s:1:"3";s:8:"newsdata";s:1:"0";s:5:"title";s:21:"请选择文章分类";s:7:"titleid";s:1:"0";s:8:"navstyle";s:1:"2";s:5:"show1";s:1:"1";s:5:"show2";s:1:"1";s:5:"show3";s:1:"1";s:5:"show4";s:1:"1";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"con_key";s:1:"1";s:8:"con_type";s:1:"1";s:8:"sourceid";s:0:"";}s:5:"style";a:11:{s:10:"background";s:4:"#fff";s:10:"paddingtop";s:1:"8";s:11:"paddingleft";s:2:"10";s:12:"marginbottom";s:2:"10";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"color";s:7:"#434343";s:6:"radius";s:1:"3";s:4:"pich";s:1:"1";s:8:"showtype";s:1:"0";}s:4:"data";a:3:{s:14:"C1564392830483";a:8:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/3.jpg";s:2:"id";s:0:"";s:5:"title";s:15:"这里是标题";s:4:"time";s:16:"2017年10月1日";s:5:"intro";s:21:"简介1简介1简介1";s:3:"ydl";s:2:"10";s:3:"dzl";s:2:"10";s:3:"pll";s:2:"10";}s:14:"C1564392830484";a:8:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/3.jpg";s:2:"id";s:0:"";s:5:"title";s:15:"这里是标题";s:4:"time";s:16:"2017年10月1日";s:5:"intro";s:15:"这里是简介";s:3:"ydl";s:2:"10";s:3:"dzl";s:2:"10";s:3:"pll";s:2:"10";}s:14:"C1564392830485";a:8:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/3.jpg";s:2:"id";s:0:"";s:5:"title";s:15:"这里是标题";s:4:"time";s:16:"2017年10月1日";s:5:"intro";s:15:"这里是简介";s:3:"ydl";s:2:"10";s:3:"dzl";s:2:"10";s:3:"pll";s:2:"10";}}s:2:"id";s:8:"listdesc";}s:14:"M1564392852389";a:6:{s:4:"icon";s:19:"iconfont2 icon-zutu";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:6:{s:10:"paddingtop";s:1:"0";s:11:"paddingleft";s:1:"0";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"background";s:7:"#ffffff";}s:4:"data";a:1:{s:14:"C1564392852390";a:2:{s:6:"imgurl";s:58:"/upimages/20190729/935b94b4f22d6cde3b5936c6f9da91db456.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:7:"picture";s:5:"index";s:3:"NaN";}s:14:"M1564392892859";a:5:{s:4:"icon";s:32:"iconfont2 icon-liebiao_biaotilan";s:6:"params";a:9:{s:5:"title";s:9:"学设计";s:6:"title2";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:5:"style";s:1:"3";}s:5:"style";a:11:{s:10:"background";s:7:"#ffffff";s:6:"colorz";s:7:"#000000";s:6:"colorf";s:7:"#838383";s:9:"linecolor";s:7:"#ffffff";s:9:"fontsizez";s:2:"18";s:9:"fontsizef";s:1:"9";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:2:"10";}s:2:"id";s:6:"title2";s:5:"index";s:3:"NaN";}s:14:"M1564392920377";a:4:{s:4:"icon";s:22:"iconfont2 icon-shipin1";s:5:"style";a:5:{s:5:"ratio";s:1:"0";s:10:"background";s:7:"#ffffff";s:2:"mt";s:1:"0";s:11:"paddingleft";s:1:"0";s:10:"paddingtop";s:1:"0";}s:6:"params";a:6:{s:8:"videourl";s:0:"";s:6:"poster";s:79:"https://four.nttrip.cn/diypage/template_img/template_renovation/index/video.png";s:10:"videostyle";s:1:"0";s:9:"styledata";s:1:"0";s:13:"backgroundimg";s:0:"";s:8:"autoplay";s:1:"0";}s:2:"id";s:5:"video";}s:14:"M1564392968402";a:6:{s:4:"icon";s:19:"iconfont2 icon-zutu";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:6:{s:10:"paddingtop";s:1:"0";s:11:"paddingleft";s:1:"0";s:2:"mt";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"background";s:7:"#ffffff";}s:4:"data";a:1:{s:14:"C1564392968402";a:2:{s:6:"imgurl";s:82:"https://four.nttrip.cn/diypage/template_img/template_renovation/index/picture2.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:7:"picture";s:5:"index";s:3:"NaN";}s:14:"M1564392988586";a:5:{s:4:"icon";s:32:"iconfont2 icon-liebiao_biaotilan";s:6:"params";a:9:{s:5:"title";s:12:"好物推荐";s:6:"title2";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:5:"style";s:1:"3";}s:5:"style";a:11:{s:10:"background";s:7:"#ffffff";s:6:"colorz";s:7:"#000000";s:6:"colorf";s:7:"#838383";s:9:"linecolor";s:7:"#ffffff";s:9:"fontsizez";s:2:"18";s:9:"fontsizef";s:2:"12";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:2:"10";}s:2:"id";s:6:"title2";s:5:"index";s:3:"NaN";}s:14:"M1564393020519";a:5:{s:4:"icon";s:22:"iconfont2 icon-chanpin";s:6:"params";a:31:{s:11:"goodsscroll";s:1:"0";s:9:"showtitle";s:1:"1";s:9:"showprice";s:1:"1";s:7:"showtag";s:1:"0";s:9:"goodsdata";s:1:"1";s:6:"cateid";s:0:"";s:8:"catename";s:0:"";s:7:"groupid";s:0:"";s:9:"groupname";s:0:"";s:9:"goodssort";s:1:"0";s:8:"goodsnum";s:1:"6";s:8:"showicon";s:1:"0";s:12:"iconposition";s:8:"left top";s:12:"productprice";s:1:"1";s:16:"showproductprice";s:1:"0";s:9:"showsales";s:1:"1";s:16:"productpricetext";s:6:"原价";s:9:"salestext";s:6:"销量";s:16:"productpriceline";s:1:"0";s:7:"saleout";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"imgh_is";s:1:"1";s:4:"imgh";s:3:"100";s:7:"con_key";s:1:"1";s:8:"con_type";s:1:"1";s:8:"sourceid";s:0:"";}s:5:"style";a:20:{s:10:"background";s:7:"#ffffff";s:9:"liststyle";s:5:"block";s:8:"buystyle";s:0:"";s:9:"goodsicon";s:9:"recommand";s:9:"iconstyle";s:8:"triangle";s:10:"pricecolor";s:7:"#ff5555";s:17:"productpricecolor";s:7:"#999999";s:14:"iconpaddingtop";s:1:"0";s:15:"iconpaddingleft";s:1:"0";s:11:"buybtncolor";s:7:"#ff5555";s:8:"iconzoom";s:2:"50";s:10:"titlecolor";s:7:"#000000";s:13:"tagbackground";s:7:"#fe5455";s:10:"salescolor";s:7:"#999999";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"paddingtop";s:1:"0";s:11:"paddingleft";s:2:"10";s:8:"showtype";s:1:"0";}s:4:"data";a:4:{s:14:"C1564393020519";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:3:"des";s:21:"这里是产品描述";}s:14:"C1564393020520";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1564393020521";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1564393020522";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}}s:2:"id";s:5:"goods";}}';
            $item1 = unserialize($item1);
            foreach($item1 as $k => &$v){
                if(isset($v['data'])){
                    foreach($v['data'] as $kk => &$vv){
                        if(isset($vv['imgurl']) && strpos($vv['imgurl'],'https://four.nttrip.cn') !== false){
                            $vv['imgurl'] = ROOT_HOST.explode('https://four.nttrip.cn', $vv['imgurl'])[1];
                        }
                        if(isset($vv['thumb']) && strpos($vv['thumb'],'/diypage/resource/images/diypage/default/2.jpg') !== false){
                            $vv['thumb'] = ROOT_HOST."/diypage/resource/images/diypage/default/2.jpg";
                        }
                        if(isset($vv['thumb']) && strpos($vv['thumb'],'/diypage/resource/images/diypage/default/3.jpg') !== false){
                            $vv['thumb'] = ROOT_HOST."/diypage/resource/images/diypage/default/3.jpg";
                        }
                    }
                }
            }
            $page1['items'] = serialize($item1);
            $page1['page'] = 'a:7:{s:10:"background";s:7:"#f1f1f1";s:13:"topbackground";s:7:"#ffffff";s:8:"topcolor";s:1:"1";s:9:"styledata";s:1:"0";s:5:"title";s:6:"首页";s:4:"name";s:6:"首页";s:10:"visitlevel";a:2:{s:6:"member";s:0:"";s:10:"commission";s:0:"";}}';
            $page1['tpl_name'] = "首页";

            $page1_id = Db::name('wd_xcx_diypage')->insertGetId($page1);
            $pageids = $page1_id;

            $is = Db::name('wd_xcx_diypageset')->where('uniacid',$uniacid)->find();
            if(empty($is)){
                $page_set = [
                    'uniacid' => $uniacid,
                    'kp' => ROOT_HOST."/diypage/resource/images/diypage/default/default_start.jpg",
                    'kp_is' => 2,
                    'kp_m' => 2,
                    'tc' => ROOT_HOST."/diypage/resource/images/diypage/default/tcgg.jpg",
                    'tc_is' => 2,
                    'foot_is' => 2
                ];
                Db::name('wd_xcx_diypageset')->insert($page_set);
            }else{
                Db::name('wd_xcx_diypageset')->where('uniacid',$uniacid)->update(array('foot_is' => 2));
            }
            $data = [
                'uniacid' => $uniacid,
                'pageid' => $pageids,
                'template_name' => '装修类模板01',
                'thumb' => "/diypage/template_img/template_renovation/cover.png",
                'status' => '1',
                'create_time' => time()
            ];
            $tplid = Db::name('wd_xcx_diypagetpl')->insertGetId($data);
            if($tplid){
                return $tplid;
            }
        }elseif($id == 'm_mother'){
            $page1 = [];
            $page1['uniacid'] = $uniacid;
            $page1['index'] = 1;
            $item1 = 'a:9:{s:14:"M1564649635570";a:5:{s:4:"icon";s:28:"iconfont2 icon-tuoyuankaobei";s:6:"params";a:10:{s:5:"totle";s:1:"2";s:8:"navstyle";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:9:"navstyle2";s:1:"0";s:4:"imgh";s:3:"160";}s:5:"style";a:18:{s:8:"dotstyle";s:5:"round";s:8:"dotalign";s:4:"left";s:10:"paddingtop";s:1:"0";s:11:"paddingleft";s:1:"0";s:10:"background";s:7:"#ffffff";s:13:"backgroundall";s:7:"#ffffff";s:9:"leftright";s:1:"5";s:6:"bottom";s:2:"10";s:7:"opacity";s:3:"0.5";s:10:"text_color";s:4:"#fff";s:2:"bg";s:7:"#000000";s:9:"jsq_color";s:3:"red";s:3:"pdh";s:1:"0";s:3:"pdw";s:1:"0";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"speed";s:1:"5";}s:4:"data";a:1:{s:14:"C1564649635570";a:4:{s:6:"imgurl";s:76:"https://four.nttrip.cn/diypage/template_img/template_mother/index/banner.jpg";s:7:"linkurl";s:0:"";s:6:"single";s:1:"1";s:4:"text";s:12:"文字描述";}}s:2:"id";s:6:"banner";}s:14:"M1565342353305";a:4:{s:4:"icon";s:22:"iconfont2 icon-sousuo1";s:6:"params";a:7:{s:5:"value";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:12:{s:9:"textalign";s:6:"center";s:10:"background";s:7:"#ffffff";s:2:"bg";s:7:"#f0f0f0";s:12:"borderradius";s:1:"5";s:6:"boxpdh";s:2:"11";s:6:"boxpdz";s:2:"15";s:7:"padding";s:1:"3";s:8:"fontsize";s:2:"13";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"color";s:7:"#b8b8b8";}s:2:"id";s:3:"ssk";}s:14:"M1564649731376";a:5:{s:4:"icon";s:21:"iconfont2 icon-tupian";s:6:"params";a:9:{s:3:"row";s:1:"2";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"2";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:8:{s:10:"paddingtop";s:1:"5";s:11:"paddingleft";s:2:"10";s:7:"showdot";s:1:"0";s:7:"showbtn";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:1:"0";s:10:"background";s:7:"#ffffff";}s:4:"data";a:4:{s:14:"C1564649731377";a:2:{s:6:"imgurl";s:79:"https://four.nttrip.cn/diypage/template_img/template_mother/index/picturew1.png";s:7:"linkurl";s:0:"";}s:14:"C1564649731378";a:2:{s:6:"imgurl";s:79:"https://four.nttrip.cn/diypage/template_img/template_mother/index/picturew2.png";s:7:"linkurl";s:0:"";}s:14:"C1564649731379";a:2:{s:6:"imgurl";s:79:"https://four.nttrip.cn/diypage/template_img/template_mother/index/picturew3.png";s:7:"linkurl";s:0:"";}s:14:"M1564649737043";a:2:{s:6:"imgurl";s:79:"https://four.nttrip.cn/diypage/template_img/template_mother/index/picturew4.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:8:"picturew";}s:14:"M1564649824089";a:5:{s:4:"icon";s:22:"iconfont2 icon-gonggao";s:6:"params";a:12:{s:7:"iconurl";s:15:"icon-x-gonggao3";s:10:"noticedata";s:1:"0";s:5:"speed";s:1:"4";s:9:"noticenum";s:1:"5";s:8:"navstyle";s:1:"1";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:8:"sourceid";s:0:"";}s:5:"style";a:9:{s:10:"background";s:7:"#ffffff";s:9:"iconcolor";s:7:"#fd5454";s:5:"color";s:7:"#666666";s:11:"bordercolor";s:7:"#e2e2e2";s:2:"mt";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";}s:4:"data";a:2:{s:14:"C1564649824089";a:2:{s:5:"title";s:42:"这里是第一条自定义公告的标题";s:7:"linkurl";s:0:"";}s:14:"C1564649824090";a:2:{s:5:"title";s:42:"这里是第二条自定义公告的标题";s:7:"linkurl";s:0:"";}}s:2:"id";s:6:"notice";}s:14:"M1564649866181";a:6:{s:4:"icon";s:19:"iconfont2 icon-zutu";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:6:{s:10:"paddingtop";s:1:"0";s:11:"paddingleft";s:1:"0";s:2:"mt";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"background";s:7:"#ffffff";}s:4:"data";a:1:{s:14:"C1564649866181";a:2:{s:6:"imgurl";s:78:"https://four.nttrip.cn/diypage/template_img/template_mother/index/picture1.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:7:"picture";s:5:"index";s:3:"NaN";}s:14:"M1564649884532";a:6:{s:4:"icon";s:19:"iconfont2 icon-zutu";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:6:{s:10:"paddingtop";s:1:"0";s:11:"paddingleft";s:1:"0";s:2:"mt";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"background";s:7:"#ffffff";}s:4:"data";a:1:{s:14:"C1564649884532";a:2:{s:6:"imgurl";s:78:"https://four.nttrip.cn/diypage/template_img/template_mother/index/picture2.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:7:"picture";s:5:"index";s:3:"NaN";}s:14:"M1564649920522";a:5:{s:4:"icon";s:23:"iconfont2 icon-wenzhang";s:6:"params";a:19:{s:9:"showstyle";s:4:"row1";s:7:"newsnum";s:1:"3";s:8:"newsdata";s:1:"0";s:5:"title";s:21:"请选择文章分类";s:7:"titleid";s:1:"0";s:8:"navstyle";s:1:"1";s:5:"show1";s:1:"1";s:5:"show2";s:1:"0";s:5:"show3";s:1:"0";s:5:"show4";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"con_key";s:1:"1";s:8:"con_type";s:1:"1";s:8:"sourceid";s:0:"";}s:5:"style";a:11:{s:10:"background";s:4:"#fff";s:10:"paddingtop";s:1:"5";s:11:"paddingleft";s:2:"10";s:12:"marginbottom";s:2:"10";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"color";s:7:"#434343";s:6:"radius";s:1:"3";s:4:"pich";s:1:"1";s:8:"showtype";s:1:"0";}s:4:"data";a:3:{s:14:"C1564649920522";a:8:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/3.jpg";s:2:"id";s:0:"";s:5:"title";s:15:"这里是标题";s:4:"time";s:16:"2017年10月1日";s:5:"intro";s:21:"简介1简介1简介1";s:3:"ydl";s:2:"10";s:3:"dzl";s:2:"10";s:3:"pll";s:2:"10";}s:14:"C1564649920523";a:8:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/3.jpg";s:2:"id";s:0:"";s:5:"title";s:15:"这里是标题";s:4:"time";s:16:"2017年10月1日";s:5:"intro";s:15:"这里是简介";s:3:"ydl";s:2:"10";s:3:"dzl";s:2:"10";s:3:"pll";s:2:"10";}s:14:"C1564649920524";a:8:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/3.jpg";s:2:"id";s:0:"";s:5:"title";s:15:"这里是标题";s:4:"time";s:16:"2017年10月1日";s:5:"intro";s:15:"这里是简介";s:3:"ydl";s:2:"10";s:3:"dzl";s:2:"10";s:3:"pll";s:2:"10";}}s:2:"id";s:8:"listdesc";}s:14:"M1564649948449";a:6:{s:4:"icon";s:19:"iconfont2 icon-zutu";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:6:{s:10:"paddingtop";s:1:"0";s:11:"paddingleft";s:1:"0";s:2:"mt";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"background";s:7:"#ffffff";}s:4:"data";a:1:{s:14:"C1564649948449";a:2:{s:6:"imgurl";s:78:"https://four.nttrip.cn/diypage/template_img/template_mother/index/picture3.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:7:"picture";s:5:"index";s:3:"NaN";}s:14:"M1564649970601";a:6:{s:4:"icon";s:23:"iconfont2 icon-wenzhang";s:6:"params";a:19:{s:9:"showstyle";s:4:"row1";s:7:"newsnum";s:1:"4";s:8:"newsdata";s:1:"0";s:5:"title";s:21:"请选择文章分类";s:7:"titleid";s:1:"0";s:8:"navstyle";s:1:"2";s:5:"show1";s:1:"1";s:5:"show2";s:1:"0";s:5:"show3";s:1:"0";s:5:"show4";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"con_key";s:1:"1";s:8:"con_type";s:1:"1";s:8:"sourceid";s:0:"";}s:5:"style";a:11:{s:10:"background";s:4:"#fff";s:10:"paddingtop";s:1:"5";s:11:"paddingleft";s:2:"10";s:12:"marginbottom";s:2:"10";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"color";s:7:"#434343";s:6:"radius";s:1:"0";s:4:"pich";s:1:"1";s:8:"showtype";s:1:"0";}s:4:"data";a:3:{s:14:"C1564649970601";a:8:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/3.jpg";s:2:"id";s:0:"";s:5:"title";s:15:"这里是标题";s:4:"time";s:16:"2017年10月1日";s:5:"intro";s:21:"简介1简介1简介1";s:3:"ydl";s:2:"10";s:3:"dzl";s:2:"10";s:3:"pll";s:2:"10";}s:14:"C1564649970602";a:8:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/3.jpg";s:2:"id";s:0:"";s:5:"title";s:15:"这里是标题";s:4:"time";s:16:"2017年10月1日";s:5:"intro";s:15:"这里是简介";s:3:"ydl";s:2:"10";s:3:"dzl";s:2:"10";s:3:"pll";s:2:"10";}s:14:"C1564649970603";a:8:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/3.jpg";s:2:"id";s:0:"";s:5:"title";s:15:"这里是标题";s:4:"time";s:16:"2017年10月1日";s:5:"intro";s:15:"这里是简介";s:3:"ydl";s:2:"10";s:3:"dzl";s:2:"10";s:3:"pll";s:2:"10";}}s:2:"id";s:8:"listdesc";s:5:"index";s:3:"NaN";}}';
            $item1 = unserialize($item1);
            foreach($item1 as $k => &$v){
                if(isset($v['data'])){
                    foreach($v['data'] as $kk => &$vv){
                        if(isset($vv['imgurl']) && strpos($vv['imgurl'],'https://four.nttrip.cn') !== false){
                            $vv['imgurl'] = ROOT_HOST.explode('https://four.nttrip.cn', $vv['imgurl'])[1];
                        }
                        if(isset($vv['thumb']) && strpos($vv['thumb'],'/diypage/resource/images/diypage/default/3.jpg') !== false){
                            $vv['thumb'] = ROOT_HOST."/diypage/resource/images/diypage/default/3.jpg";
                        }
                    }
                }
            }
            $page1['items'] = serialize($item1);
            $page1['page'] = 'a:7:{s:10:"background";s:7:"#f1f1f1";s:13:"topbackground";s:7:"#ffffff";s:8:"topcolor";s:1:"1";s:9:"styledata";s:1:"0";s:5:"title";s:6:"首页";s:4:"name";s:6:"首页";s:10:"visitlevel";a:2:{s:6:"member";s:0:"";s:10:"commission";s:0:"";}}';
            $page1['tpl_name'] = "首页";

            $page1_id = Db::name('wd_xcx_diypage')->insertGetId($page1);
            $pageids = $page1_id;

            $is = Db::name('wd_xcx_diypageset')->where('uniacid',$uniacid)->find();
            if(empty($is)){
                $page_set = [
                    'uniacid' => $uniacid,
                    'kp' => ROOT_HOST."/diypage/resource/images/diypage/default/default_start.jpg",
                    'kp_is' => 2,
                    'kp_m' => 2,
                    'tc' => ROOT_HOST."/diypage/resource/images/diypage/default/tcgg.jpg",
                    'tc_is' => 2,
                    'foot_is' => 2
                ];
                Db::name('wd_xcx_diypageset')->insert($page_set);
            }else{
                Db::name('wd_xcx_diypageset')->where('uniacid',$uniacid)->update(array('foot_is' => 2));
            }
            $data = [
                'uniacid' => $uniacid,
                'pageid' => $pageids,
                'template_name' => '母婴类模板01',
                'thumb' => "/diypage/template_img/template_mother/cover.png",
                'status' => '1',
                'create_time' => time()
            ];
            $tplid = Db::name('wd_xcx_diypagetpl')->insertGetId($data);
            if($tplid){
                return $tplid;
            }
        }elseif($id == 'm_retail'){
            $page1 = [];
            $page1['uniacid'] = $uniacid;
            $page1['index'] = 1;
            $item1 = 'a:10:{s:14:"M1548408513705";a:5:{s:4:"icon";s:28:"iconfont2 icon-tuoyuankaobei";s:6:"params";a:10:{s:5:"totle";s:1:"2";s:8:"navstyle";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:9:"navstyle2";s:1:"0";s:4:"imgh";s:3:"150";}s:5:"style";a:18:{s:8:"dotstyle";s:5:"round";s:8:"dotalign";s:4:"left";s:10:"paddingtop";s:1:"0";s:11:"paddingleft";s:2:"15";s:10:"background";s:7:"#ffffff";s:13:"backgroundall";s:7:"#ffffff";s:9:"leftright";s:1:"5";s:6:"bottom";s:1:"5";s:7:"opacity";s:1:"0";s:10:"text_color";s:4:"#fff";s:2:"bg";s:7:"#000000";s:9:"jsq_color";s:3:"red";s:3:"pdh";s:1:"0";s:3:"pdw";s:1:"0";s:2:"mt";s:1:"4";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"speed";s:1:"5";}s:4:"data";a:2:{s:14:"C1548408513705";a:4:{s:6:"imgurl";s:71:"https://four.nttrip.cn/template_img/template_retail/index/banner1_1.jpg";s:7:"linkurl";s:0:"";s:6:"single";s:1:"1";s:4:"text";s:12:"文字描述";}s:14:"C1548408513706";a:4:{s:6:"imgurl";s:71:"https://four.nttrip.cn/template_img/template_retail/index/banner1_2.jpg";s:7:"linkurl";s:0:"";s:6:"single";s:1:"2";s:4:"text";s:12:"文字描述";}}s:2:"id";s:6:"banner";}s:14:"M1548408517561";a:5:{s:4:"icon";s:22:"iconfont2 icon-anniuzu";s:6:"params";a:8:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"picicon";s:1:"1";s:8:"textshow";s:1:"2";}s:5:"style";a:14:{s:8:"navstyle";s:0:"";s:10:"background";s:7:"#ffffff";s:6:"rownum";s:1:"4";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"8";s:7:"showdot";s:1:"1";s:7:"padding";s:1:"0";s:11:"paddingleft";s:2:"10";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:6:"iconfz";s:2:"14";s:9:"iconcolor";s:7:"#434343";s:8:"imgwidth";s:2:"64";}s:4:"data";a:4:{s:14:"C1548408517561";a:5:{s:6:"imgurl";s:69:"https://four.nttrip.cn/template_img/template_retail/index/menu1_1.png";s:7:"linkurl";s:0:"";s:4:"text";s:13:"按钮文字1";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1548408517562";a:5:{s:6:"imgurl";s:69:"https://four.nttrip.cn/template_img/template_retail/index/menu1_2.png";s:7:"linkurl";s:0:"";s:4:"text";s:13:"按钮文字2";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1548408517563";a:5:{s:6:"imgurl";s:69:"https://four.nttrip.cn/template_img/template_retail/index/menu1_3.png";s:7:"linkurl";s:0:"";s:4:"text";s:13:"按钮文字3";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1548408517564";a:5:{s:6:"imgurl";s:69:"https://four.nttrip.cn/template_img/template_retail/index/menu1_4.png";s:7:"linkurl";s:0:"";s:4:"text";s:13:"按钮文字4";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}}s:2:"id";s:4:"menu";}s:14:"M1548408542818";a:6:{s:4:"icon";s:28:"iconfont2 icon-tuoyuankaobei";s:6:"params";a:10:{s:5:"totle";s:1:"2";s:8:"navstyle";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:9:"navstyle2";s:1:"0";s:4:"imgh";s:2:"90";}s:5:"style";a:18:{s:8:"dotstyle";s:5:"round";s:8:"dotalign";s:4:"left";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:10:"background";s:7:"#ffffff";s:13:"backgroundall";s:7:"#ffffff";s:9:"leftright";s:1:"5";s:6:"bottom";s:1:"5";s:7:"opacity";s:1:"0";s:10:"text_color";s:4:"#fff";s:2:"bg";s:7:"#000000";s:9:"jsq_color";s:3:"red";s:3:"pdh";s:1:"0";s:3:"pdw";s:1:"0";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"speed";s:1:"5";}s:4:"data";a:1:{s:14:"C1548408542818";a:4:{s:6:"imgurl";s:69:"https://four.nttrip.cn/template_img/template_retail/index/banner2.png";s:7:"linkurl";s:0:"";s:6:"single";s:1:"1";s:4:"text";s:12:"文字描述";}}s:2:"id";s:6:"banner";s:5:"index";s:3:"NaN";}s:14:"M1548408545249";a:6:{s:4:"icon";s:22:"iconfont2 icon-anniuzu";s:6:"params";a:8:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"picicon";s:1:"1";s:8:"textshow";s:1:"1";}s:5:"style";a:14:{s:8:"navstyle";s:0:"";s:10:"background";s:7:"#ffffff";s:6:"rownum";s:1:"4";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"8";s:7:"showdot";s:1:"1";s:7:"padding";s:1:"0";s:11:"paddingleft";s:2:"10";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:6:"iconfz";s:2:"14";s:9:"iconcolor";s:7:"#434343";s:8:"imgwidth";s:2:"50";}s:4:"data";a:4:{s:14:"C1548408545249";a:5:{s:6:"imgurl";s:69:"https://four.nttrip.cn/template_img/template_retail/index/menu2_1.jpg";s:7:"linkurl";s:0:"";s:4:"text";s:12:"主题美食";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1548408545250";a:5:{s:6:"imgurl";s:69:"https://four.nttrip.cn/template_img/template_retail/index/menu2_2.jpg";s:7:"linkurl";s:0:"";s:4:"text";s:12:"美味寿司";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1548408545251";a:5:{s:6:"imgurl";s:69:"https://four.nttrip.cn/template_img/template_retail/index/menu2_3.jpg";s:7:"linkurl";s:0:"";s:4:"text";s:12:"能量西餐";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1548408545252";a:5:{s:6:"imgurl";s:69:"https://four.nttrip.cn/template_img/template_retail/index/menu2_4.jpg";s:7:"linkurl";s:0:"";s:4:"text";s:12:"蛋糕烘焙";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}}s:2:"id";s:4:"menu";s:5:"index";s:3:"NaN";}s:14:"M1548408545594";a:6:{s:4:"icon";s:22:"iconfont2 icon-anniuzu";s:6:"params";a:8:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"picicon";s:1:"1";s:8:"textshow";s:1:"1";}s:5:"style";a:14:{s:8:"navstyle";s:0:"";s:10:"background";s:7:"#ffffff";s:6:"rownum";s:1:"4";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"8";s:7:"showdot";s:1:"1";s:7:"padding";s:1:"0";s:11:"paddingleft";s:2:"10";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:6:"iconfz";s:2:"14";s:9:"iconcolor";s:7:"#434343";s:8:"imgwidth";s:2:"50";}s:4:"data";a:4:{s:14:"C1548408545594";a:5:{s:6:"imgurl";s:69:"https://four.nttrip.cn/template_img/template_retail/index/menu2_5.jpg";s:7:"linkurl";s:0:"";s:4:"text";s:12:"日本料理";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1548408545595";a:5:{s:6:"imgurl";s:69:"https://four.nttrip.cn/template_img/template_retail/index/menu2_6.jpg";s:7:"linkurl";s:0:"";s:4:"text";s:12:"奶茶饮品";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1548408545596";a:5:{s:6:"imgurl";s:69:"https://four.nttrip.cn/template_img/template_retail/index/menu2_7.jpg";s:7:"linkurl";s:0:"";s:4:"text";s:15:"甜品下午茶";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1548408545597";a:5:{s:6:"imgurl";s:69:"https://four.nttrip.cn/template_img/template_retail/index/menu2_8.jpg";s:7:"linkurl";s:0:"";s:4:"text";s:12:"舌尖烤肉";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}}s:2:"id";s:4:"menu";s:5:"index";s:3:"NaN";}s:14:"M1548408562807";a:6:{s:4:"icon";s:28:"iconfont2 icon-tuoyuankaobei";s:6:"params";a:10:{s:5:"totle";s:1:"2";s:8:"navstyle";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:9:"navstyle2";s:1:"0";s:4:"imgh";s:2:"90";}s:5:"style";a:18:{s:8:"dotstyle";s:5:"round";s:8:"dotalign";s:4:"left";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:10:"background";s:7:"#ffffff";s:13:"backgroundall";s:7:"#ffffff";s:9:"leftright";s:1:"5";s:6:"bottom";s:1:"5";s:7:"opacity";s:1:"0";s:10:"text_color";s:4:"#fff";s:2:"bg";s:7:"#000000";s:9:"jsq_color";s:3:"red";s:3:"pdh";s:1:"0";s:3:"pdw";s:1:"0";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"speed";s:1:"5";}s:4:"data";a:1:{s:14:"C1548408562807";a:4:{s:6:"imgurl";s:69:"https://four.nttrip.cn/template_img/template_retail/index/banner3.png";s:7:"linkurl";s:0:"";s:6:"single";s:1:"1";s:4:"text";s:12:"文字描述";}}s:2:"id";s:6:"banner";s:5:"index";s:3:"NaN";}s:14:"M1548409151424";a:4:{s:4:"icon";s:32:"iconfont2 icon-liebiao_biaotilan";s:6:"params";a:8:{s:5:"title";s:36:"网红馆—人气美食都在这儿";s:4:"icon";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:9:{s:10:"background";s:7:"#ffffff";s:5:"color";s:7:"#666666";s:9:"textalign";s:6:"center";s:8:"fontsize";s:2:"17";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:1:"0";}s:2:"id";s:5:"title";}s:14:"M1556418793802";a:5:{s:4:"icon";s:22:"iconfont2 icon-chanpin";s:6:"params";a:31:{s:11:"goodsscroll";s:1:"0";s:9:"showtitle";s:1:"1";s:9:"showprice";s:1:"1";s:7:"showtag";s:1:"0";s:9:"goodsdata";s:1:"1";s:6:"cateid";s:0:"";s:8:"catename";s:0:"";s:7:"groupid";s:0:"";s:9:"groupname";s:0:"";s:9:"goodssort";s:1:"0";s:8:"goodsnum";s:1:"6";s:8:"showicon";s:1:"1";s:12:"iconposition";s:8:"left top";s:12:"productprice";s:1:"1";s:16:"showproductprice";s:1:"0";s:9:"showsales";s:1:"1";s:16:"productpricetext";s:6:"原价";s:9:"salestext";s:6:"销量";s:16:"productpriceline";s:1:"0";s:7:"saleout";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"imgh_is";s:1:"1";s:4:"imgh";s:3:"100";s:7:"con_key";s:1:"1";s:8:"con_type";s:1:"1";s:8:"sourceid";s:0:"";}s:5:"style";a:20:{s:10:"background";s:7:"#ffffff";s:9:"liststyle";s:5:"block";s:8:"buystyle";s:0:"";s:9:"goodsicon";s:9:"recommand";s:9:"iconstyle";s:6:"circle";s:10:"pricecolor";s:7:"#ff5555";s:17:"productpricecolor";s:7:"#999999";s:14:"iconpaddingtop";s:1:"0";s:15:"iconpaddingleft";s:1:"0";s:11:"buybtncolor";s:7:"#ff5555";s:8:"iconzoom";s:2:"50";s:10:"titlecolor";s:7:"#000000";s:13:"tagbackground";s:7:"#fe5455";s:10:"salescolor";s:7:"#999999";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:8:"showtype";s:1:"0";}s:4:"data";a:4:{s:14:"C1556418793802";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:3:"des";s:21:"这里是产品描述";}s:14:"C1556418793803";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1556418793804";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1556418793805";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}}s:2:"id";s:5:"goods";}s:14:"M1548409277319";a:5:{s:4:"icon";s:32:"iconfont2 icon-liebiao_biaotilan";s:6:"params";a:8:{s:5:"title";s:33:"爆品馆—颜控吃货请打卡";s:4:"icon";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:9:{s:10:"background";s:7:"#ffffff";s:5:"color";s:7:"#666666";s:9:"textalign";s:6:"center";s:8:"fontsize";s:2:"17";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:1:"0";}s:2:"id";s:5:"title";s:5:"index";s:3:"NaN";}s:14:"M1556418806849";a:6:{s:4:"icon";s:22:"iconfont2 icon-chanpin";s:6:"params";a:31:{s:11:"goodsscroll";s:1:"0";s:9:"showtitle";s:1:"1";s:9:"showprice";s:1:"1";s:7:"showtag";s:1:"0";s:9:"goodsdata";s:1:"1";s:6:"cateid";s:0:"";s:8:"catename";s:0:"";s:7:"groupid";s:0:"";s:9:"groupname";s:0:"";s:9:"goodssort";s:1:"0";s:8:"goodsnum";s:1:"6";s:8:"showicon";s:1:"1";s:12:"iconposition";s:8:"left top";s:12:"productprice";s:1:"1";s:16:"showproductprice";s:1:"0";s:9:"showsales";s:1:"1";s:16:"productpricetext";s:6:"原价";s:9:"salestext";s:6:"销量";s:16:"productpriceline";s:1:"0";s:7:"saleout";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"imgh_is";s:1:"1";s:4:"imgh";s:3:"100";s:7:"con_key";s:1:"1";s:8:"con_type";s:1:"1";s:8:"sourceid";s:0:"";}s:5:"style";a:20:{s:10:"background";s:7:"#ffffff";s:9:"liststyle";s:5:"block";s:8:"buystyle";s:0:"";s:9:"goodsicon";s:7:"hotsale";s:9:"iconstyle";s:6:"circle";s:10:"pricecolor";s:7:"#ff5555";s:17:"productpricecolor";s:7:"#999999";s:14:"iconpaddingtop";s:1:"0";s:15:"iconpaddingleft";s:1:"0";s:11:"buybtncolor";s:7:"#ff5555";s:8:"iconzoom";s:2:"50";s:10:"titlecolor";s:7:"#000000";s:13:"tagbackground";s:7:"#fe5455";s:10:"salescolor";s:7:"#999999";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:8:"showtype";s:1:"0";}s:4:"data";a:4:{s:14:"C1556418806849";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:3:"des";s:21:"这里是产品描述";}s:14:"C1556418806850";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1556418806851";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1556418806852";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}}s:2:"id";s:5:"goods";s:5:"index";s:3:"NaN";}}';
            $item1 = unserialize($item1);
            foreach($item1 as $k => &$v){
                if(isset($v['data'])){
                    foreach($v['data'] as $kk => &$vv){
                        if(isset($vv['imgurl']) && strpos($vv['imgurl'],'https://four.nttrip.cn/') !== false){
                            $vv['imgurl'] = ROOT_HOST."/diypage/".explode('https://four.nttrip.cn/', $vv['imgurl'])[1];
                        }
                    }
                }
            }
            $page1['items'] = serialize($item1);
            $page1['page'] = 'a:7:{s:10:"background";s:7:"#f1f1f1";s:13:"topbackground";s:7:"#ffffff";s:8:"topcolor";s:1:"1";s:9:"styledata";s:1:"0";s:5:"title";s:6:"美食";s:4:"name";s:6:"首页";s:10:"visitlevel";a:2:{s:6:"member";s:0:"";s:10:"commission";s:0:"";}}';
            $page1['tpl_name'] = "首页";

            $page1_id = Db::name('wd_xcx_diypage')->insertGetId($page1);
            $pageids = $page1_id;

            $is = Db::name('wd_xcx_diypageset')->where('uniacid',$uniacid)->find();
            if(empty($is)){
                $page_set = [
                    'uniacid' => $uniacid,
                    'kp' => ROOT_HOST."/diypage/resource/images/diypage/default/default_start.jpg",
                    'kp_is' => 2,
                    'kp_m' => 2,
                    'tc' => ROOT_HOST."/diypage/resource/images/diypage/default/tcgg.jpg",
                    'tc_is' => 2,
                    'foot_is' => 2
                ];
                Db::name('wd_xcx_diypageset')->insert($page_set);
            }else{
                Db::name('wd_xcx_diypageset')->where('uniacid',$uniacid)->update(array('foot_is' => 2));
            }
            $data = [
                'uniacid' => $uniacid,
                'pageid' => $pageids,
                'template_name' => '零售类模板01',
                'thumb' => "/diypage/template_img/template_retail/cover.png",
                'status' => '1',
                'create_time' => time()
            ];
            $tplid = Db::name('wd_xcx_diypagetpl')->insertGetId($data);
            if($tplid){
                return $tplid;
            }
        }elseif($id == 'm_retail02'){
            $page1 = [];
            $page1['uniacid'] = $uniacid;
            $page1['index'] = 1;
            $item1 = 'a:4:{s:14:"M1564219210009";a:5:{s:4:"icon";s:28:"iconfont2 icon-tuoyuankaobei";s:6:"params";a:10:{s:5:"totle";s:1:"2";s:8:"navstyle";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:9:"navstyle2";s:1:"0";s:4:"imgh";s:3:"160";}s:5:"style";a:18:{s:8:"dotstyle";s:5:"round";s:8:"dotalign";s:4:"left";s:10:"paddingtop";s:1:"0";s:11:"paddingleft";s:1:"0";s:10:"background";s:7:"#ffffff";s:13:"backgroundall";s:7:"#ffffff";s:9:"leftright";s:1:"5";s:6:"bottom";s:1:"5";s:7:"opacity";s:1:"0";s:10:"text_color";s:4:"#fff";s:2:"bg";s:7:"#000000";s:9:"jsq_color";s:3:"red";s:3:"pdh";s:1:"0";s:3:"pdw";s:1:"0";s:2:"mt";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"speed";s:1:"5";}s:4:"data";a:1:{s:14:"C1564219210009";a:4:{s:6:"imgurl";s:78:"https://four.nttrip.cn/diypage/template_img/template_retail02/index/banner.jpg";s:7:"linkurl";s:0:"";s:6:"single";s:1:"1";s:4:"text";s:12:"文字描述";}}s:2:"id";s:6:"banner";}s:14:"M1564219251527";a:5:{s:4:"icon";s:21:"iconfont2 icon-tupian";s:6:"params";a:9:{s:3:"row";s:1:"3";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"2";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:8:{s:10:"paddingtop";s:1:"0";s:11:"paddingleft";s:1:"0";s:7:"showdot";s:1:"0";s:7:"showbtn";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:2:"10";s:10:"background";s:7:"#ffffff";}s:4:"data";a:3:{s:14:"C1564219251528";a:2:{s:6:"imgurl";s:83:"https://four.nttrip.cn/diypage/template_img/template_retail02/index/picturew1_1.png";s:7:"linkurl";s:0:"";}s:14:"C1564219251529";a:2:{s:6:"imgurl";s:83:"https://four.nttrip.cn/diypage/template_img/template_retail02/index/picturew1_2.png";s:7:"linkurl";s:0:"";}s:14:"C1564219251530";a:2:{s:6:"imgurl";s:83:"https://four.nttrip.cn/diypage/template_img/template_retail02/index/picturew1_3.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:8:"picturew";}s:14:"M1564219698920";a:4:{s:4:"icon";s:32:"iconfont2 icon-liebiao_biaotilan";s:6:"params";a:8:{s:5:"title";s:25:"RECOMMEND × 为你推荐";s:4:"icon";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:9:{s:10:"background";s:7:"#f1f1f1";s:5:"color";s:7:"#666666";s:9:"textalign";s:6:"center";s:8:"fontsize";s:2:"16";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:1:"5";}s:2:"id";s:5:"title";}s:14:"M1564219783936";a:5:{s:4:"icon";s:21:"iconfont2 icon-tupian";s:6:"params";a:9:{s:3:"row";s:1:"2";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"2";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:8:{s:10:"paddingtop";s:1:"8";s:11:"paddingleft";s:2:"10";s:7:"showdot";s:1:"0";s:7:"showbtn";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:1:"0";s:10:"background";s:7:"#ffffff";}s:4:"data";a:4:{s:14:"C1564219783936";a:2:{s:6:"imgurl";s:83:"https://four.nttrip.cn/diypage/template_img/template_retail02/index/picturew2_1.png";s:7:"linkurl";s:0:"";}s:14:"C1564219783937";a:2:{s:6:"imgurl";s:83:"https://four.nttrip.cn/diypage/template_img/template_retail02/index/picturew2_2.png";s:7:"linkurl";s:0:"";}s:14:"C1564219783938";a:2:{s:6:"imgurl";s:83:"https://four.nttrip.cn/diypage/template_img/template_retail02/index/picturew2_3.png";s:7:"linkurl";s:0:"";}s:14:"C1564219783939";a:2:{s:6:"imgurl";s:83:"https://four.nttrip.cn/diypage/template_img/template_retail02/index/picturew2_4.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:8:"picturew";}}';
            $item1 = unserialize($item1);
            foreach($item1 as $k => &$v){
                if(isset($v['data'])){
                    foreach($v['data'] as $kk => &$vv){
                        if(isset($vv['imgurl']) && strpos($vv['imgurl'],'https://four.nttrip.cn') !== false){
                            $vv['imgurl'] = ROOT_HOST.explode('https://four.nttrip.cn', $vv['imgurl'])[1];
                        }
                    }
                }
            }
            $page1['items'] = serialize($item1);
            $page1['page'] = 'a:7:{s:10:"background";s:7:"#f1f1f1";s:13:"topbackground";s:7:"#ffffff";s:8:"topcolor";s:1:"1";s:9:"styledata";s:1:"0";s:5:"title";s:6:"首页";s:4:"name";s:6:"首页";s:10:"visitlevel";a:2:{s:6:"member";s:0:"";s:10:"commission";s:0:"";}}';
            $page1['tpl_name'] = "首页";

            $page1_id = Db::name('wd_xcx_diypage')->insertGetId($page1);
            $pageids = $page1_id;

            $is = Db::name('wd_xcx_diypageset')->where('uniacid',$uniacid)->find();
            if(empty($is)){
                $page_set = [
                    'uniacid' => $uniacid,
                    'kp' => ROOT_HOST."/diypage/resource/images/diypage/default/default_start.jpg",
                    'kp_is' => 2,
                    'kp_m' => 2,
                    'tc' => ROOT_HOST."/diypage/resource/images/diypage/default/tcgg.jpg",
                    'tc_is' => 2,
                    'foot_is' => 2
                ];
                Db::name('wd_xcx_diypageset')->insert($page_set);
            }else{
                Db::name('wd_xcx_diypageset')->where('uniacid',$uniacid)->update(array('foot_is' => 2));
            }
            $data = [
                'uniacid' => $uniacid,
                'pageid' => $pageids,
                'template_name' => '零售类模板02',
                'thumb' => "/diypage/template_img/template_retail02/cover.png",
                'status' => '1',
                'create_time' => time()
            ];
            $tplid = Db::name('wd_xcx_diypagetpl')->insertGetId($data);
            if($tplid){
                return $tplid;
            }
        }elseif($id == 'm_retail03'){
            $page1 = [];
            $page1['uniacid'] = $uniacid;
            $page1['index'] = 1;
            $item1 = 'a:5:{s:14:"M1564220484241";a:4:{s:4:"icon";s:22:"iconfont2 icon-sousuo1";s:6:"params";a:7:{s:5:"value";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:12:{s:9:"textalign";s:6:"center";s:10:"background";s:7:"#242335";s:2:"bg";s:7:"#d3d3d7";s:12:"borderradius";s:1:"5";s:6:"boxpdh";s:2:"10";s:6:"boxpdz";s:2:"15";s:7:"padding";s:1:"5";s:8:"fontsize";s:2:"13";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"color";s:7:"#7f7f7f";}s:2:"id";s:3:"ssk";}s:14:"M1564220486680";a:5:{s:4:"icon";s:28:"iconfont2 icon-tuoyuankaobei";s:6:"params";a:9:{s:5:"totle";s:1:"2";s:8:"navstyle";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:9:"navstyle2";s:1:"0";}s:5:"style";a:18:{s:8:"dotstyle";s:5:"round";s:8:"dotalign";s:4:"left";s:10:"paddingtop";s:1:"0";s:11:"paddingleft";s:1:"0";s:10:"background";s:7:"#ffffff";s:13:"backgroundall";s:7:"#ffffff";s:9:"leftright";s:1:"5";s:6:"bottom";s:2:"10";s:7:"opacity";s:3:"0.5";s:10:"text_color";s:4:"#fff";s:2:"bg";s:7:"#000000";s:9:"jsq_color";s:3:"red";s:3:"pdh";s:1:"0";s:3:"pdw";s:1:"0";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"speed";s:1:"5";}s:4:"data";a:1:{s:14:"C1564220486680";a:4:{s:6:"imgurl";s:78:"https://four.nttrip.cn/diypage/template_img/template_retail03/index/banner.png";s:7:"linkurl";s:0:"";s:6:"single";s:1:"1";s:4:"text";s:12:"文字描述";}}s:2:"id";s:6:"banner";}s:14:"M1564220731688";a:5:{s:4:"icon";s:22:"iconfont2 icon-anniuzu";s:6:"params";a:8:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"picicon";s:1:"1";s:8:"textshow";s:1:"1";}s:5:"style";a:14:{s:8:"navstyle";s:0:"";s:10:"background";s:7:"#ffffff";s:6:"rownum";s:1:"5";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"8";s:7:"showdot";s:1:"1";s:7:"padding";s:1:"8";s:11:"paddingleft";s:2:"10";s:2:"mt";s:1:"2";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:6:"iconfz";s:2:"14";s:9:"iconcolor";s:7:"#434343";s:8:"imgwidth";s:2:"45";}s:4:"data";a:5:{s:14:"C1564220731688";a:5:{s:6:"imgurl";s:77:"https://four.nttrip.cn/diypage/template_img/template_retail03/index/menu1.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"精品推荐";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1564220731689";a:5:{s:6:"imgurl";s:77:"https://four.nttrip.cn/diypage/template_img/template_retail03/index/menu2.png";s:7:"linkurl";s:0:"";s:4:"text";s:9:"优惠券";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1564220731690";a:5:{s:6:"imgurl";s:77:"https://four.nttrip.cn/diypage/template_img/template_retail03/index/menu3.png";s:7:"linkurl";s:0:"";s:4:"text";s:6:"资讯";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1564220731691";a:5:{s:6:"imgurl";s:77:"https://four.nttrip.cn/diypage/template_img/template_retail03/index/menu4.png";s:7:"linkurl";s:0:"";s:4:"text";s:6:"活动";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"M1564220991953";a:5:{s:6:"imgurl";s:77:"https://four.nttrip.cn/diypage/template_img/template_retail03/index/menu5.png";s:7:"linkurl";s:0:"";s:4:"text";s:9:"买家秀";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}}s:2:"id";s:4:"menu";}s:14:"M1564221103002";a:5:{s:4:"icon";s:19:"iconfont2 icon-zutu";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:6:{s:10:"paddingtop";s:1:"0";s:11:"paddingleft";s:1:"0";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"background";s:7:"#ffffff";}s:4:"data";a:1:{s:14:"C1564221103002";a:2:{s:6:"imgurl";s:79:"https://four.nttrip.cn/diypage/template_img/template_retail03/index/picture.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:7:"picture";}s:14:"M1564221235255";a:5:{s:4:"icon";s:21:"iconfont2 icon-tupian";s:6:"params";a:9:{s:3:"row";s:1:"2";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"2";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:8:{s:10:"paddingtop";s:1:"8";s:11:"paddingleft";s:1:"8";s:7:"showdot";s:1:"0";s:7:"showbtn";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:1:"0";s:10:"background";s:7:"#ffffff";}s:4:"data";a:2:{s:14:"C1564221235255";a:2:{s:6:"imgurl";s:81:"https://four.nttrip.cn/diypage/template_img/template_retail03/index/picturew1.png";s:7:"linkurl";s:0:"";}s:14:"C1564221235256";a:2:{s:6:"imgurl";s:81:"https://four.nttrip.cn/diypage/template_img/template_retail03/index/picturew2.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:8:"picturew";}}';
            $item1 = unserialize($item1);
            foreach($item1 as $k => &$v){
                if(isset($v['data'])){
                    foreach($v['data'] as $kk => &$vv){
                        if(isset($vv['imgurl']) && strpos($vv['imgurl'],'https://four.nttrip.cn') !== false){
                            $vv['imgurl'] = ROOT_HOST.explode('https://four.nttrip.cn', $vv['imgurl'])[1];
                        }
                    }
                }
            }
            $page1['items'] = serialize($item1);
            $page1['page'] = 'a:7:{s:10:"background";s:7:"#f1f1f1";s:13:"topbackground";s:7:"#333333";s:8:"topcolor";s:1:"2";s:9:"styledata";s:1:"0";s:5:"title";s:6:"首页";s:4:"name";s:6:"首页";s:10:"visitlevel";a:2:{s:6:"member";s:0:"";s:10:"commission";s:0:"";}}';
            $page1['tpl_name'] = "首页";

            $page1_id = Db::name('wd_xcx_diypage')->insertGetId($page1);
            $pageids = $page1_id;

            $is = Db::name('wd_xcx_diypageset')->where('uniacid',$uniacid)->find();
            if(empty($is)){
                $page_set = [
                    'uniacid' => $uniacid,
                    'kp' => ROOT_HOST."/diypage/resource/images/diypage/default/default_start.jpg",
                    'kp_is' => 2,
                    'kp_m' => 2,
                    'tc' => ROOT_HOST."/diypage/resource/images/diypage/default/tcgg.jpg",
                    'tc_is' => 2,
                    'foot_is' => 2
                ];
                Db::name('wd_xcx_diypageset')->insert($page_set);
            }else{
                Db::name('wd_xcx_diypageset')->where('uniacid',$uniacid)->update(array('foot_is' => 2));
            }
            $data = [
                'uniacid' => $uniacid,
                'pageid' => $pageids,
                'template_name' => '零售类模板03',
                'thumb' => "/diypage/template_img/template_retail03/cover.png",
                'status' => '1',
                'create_time' => time()
            ];
            $tplid = Db::name('wd_xcx_diypagetpl')->insertGetId($data);
            if($tplid){
                return $tplid;
            }
        }elseif($id == 'm_retail04'){
            $page1 = [];
            $page1['uniacid'] = $uniacid;
            $page1['index'] = 1;
            $item1 = 'a:8:{s:14:"M1563962314902";a:4:{s:4:"icon";s:22:"iconfont2 icon-sousuo1";s:6:"params";a:7:{s:5:"value";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:12:{s:9:"textalign";s:4:"left";s:10:"background";s:7:"#ffffff";s:2:"bg";s:4:"#fff";s:12:"borderradius";s:1:"8";s:6:"boxpdh";s:2:"10";s:6:"boxpdz";s:2:"10";s:7:"padding";s:1:"4";s:8:"fontsize";s:2:"13";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"color";s:0:"";}s:2:"id";s:3:"ssk";}s:14:"M1563960026031";a:5:{s:4:"icon";s:28:"iconfont2 icon-tuoyuankaobei";s:6:"params";a:10:{s:5:"totle";s:1:"2";s:8:"navstyle";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:9:"navstyle2";s:1:"0";s:4:"imgh";s:3:"180";}s:5:"style";a:18:{s:8:"dotstyle";s:5:"round";s:8:"dotalign";s:4:"left";s:10:"paddingtop";s:1:"0";s:11:"paddingleft";s:1:"0";s:10:"background";s:7:"#ffffff";s:13:"backgroundall";s:7:"#ffffff";s:9:"leftright";s:1:"5";s:6:"bottom";s:1:"8";s:7:"opacity";s:3:"0.5";s:10:"text_color";s:4:"#fff";s:2:"bg";s:7:"#000000";s:9:"jsq_color";s:3:"red";s:3:"pdh";s:1:"0";s:3:"pdw";s:1:"0";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"speed";s:1:"5";}s:4:"data";a:2:{s:14:"C1563960026031";a:4:{s:6:"imgurl";s:81:"https://four.nttrip.cn/diypage/template_img/template_retail04/index/banner1_1.jpg";s:7:"linkurl";s:0:"";s:6:"single";s:1:"1";s:4:"text";s:12:"文字描述";}s:14:"C1563960026032";a:4:{s:6:"imgurl";s:81:"https://four.nttrip.cn/diypage/template_img/template_retail04/index/banner1_2.jpg";s:7:"linkurl";s:0:"";s:6:"single";s:1:"2";s:4:"text";s:12:"文字描述";}}s:2:"id";s:6:"banner";}s:14:"M1563960128406";a:5:{s:4:"icon";s:22:"iconfont2 icon-anniuzu";s:6:"params";a:8:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"picicon";s:1:"1";s:8:"textshow";s:1:"1";}s:5:"style";a:14:{s:8:"navstyle";s:0:"";s:10:"background";s:7:"#ffffff";s:6:"rownum";s:1:"4";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"8";s:7:"showdot";s:1:"1";s:7:"padding";s:1:"5";s:11:"paddingleft";s:2:"10";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:6:"iconfz";s:2:"14";s:9:"iconcolor";s:7:"#434343";s:8:"imgwidth";s:2:"40";}s:4:"data";a:4:{s:14:"C1563960128406";a:5:{s:6:"imgurl";s:79:"https://four.nttrip.cn/diypage/template_img/template_retail04/index/menu1_1.png";s:7:"linkurl";s:0:"";s:4:"text";s:6:"推荐";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1563960128407";a:5:{s:6:"imgurl";s:79:"https://four.nttrip.cn/diypage/template_img/template_retail04/index/menu1_2.png";s:7:"linkurl";s:0:"";s:4:"text";s:6:"家居";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1563960128408";a:5:{s:6:"imgurl";s:79:"https://four.nttrip.cn/diypage/template_img/template_retail04/index/menu1_3.png";s:7:"linkurl";s:0:"";s:4:"text";s:6:"家具";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1563960128409";a:5:{s:6:"imgurl";s:79:"https://four.nttrip.cn/diypage/template_img/template_retail04/index/menu1_4.png";s:7:"linkurl";s:0:"";s:4:"text";s:6:"礼品";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}}s:2:"id";s:4:"menu";}s:14:"M1563960953470";a:4:{s:4:"icon";s:32:"iconfont2 icon-liebiao_biaotilan";s:6:"params";a:9:{s:5:"title";s:12:"创新设计";s:6:"title2";s:21:"艺术精品生活馆";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:5:"style";s:1:"1";}s:5:"style";a:11:{s:10:"background";s:7:"#ffffff";s:6:"colorz";s:7:"#434343";s:6:"colorf";s:7:"#838383";s:9:"linecolor";s:7:"#ffffff";s:9:"fontsizez";s:2:"16";s:9:"fontsizef";s:2:"13";s:10:"paddingtop";s:2:"13";s:11:"paddingleft";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:2:"13";}s:2:"id";s:6:"title2";}s:14:"M1563961273903";a:6:{s:4:"icon";s:22:"iconfont2 icon-anniuzu";s:6:"params";a:8:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"picicon";s:1:"1";s:8:"textshow";s:1:"1";}s:5:"style";a:14:{s:8:"navstyle";s:0:"";s:10:"background";s:7:"#ffffff";s:6:"rownum";s:1:"4";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"8";s:7:"showdot";s:1:"1";s:7:"padding";s:1:"5";s:11:"paddingleft";s:2:"10";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:6:"iconfz";s:2:"14";s:9:"iconcolor";s:7:"#434343";s:8:"imgwidth";s:2:"65";}s:4:"data";a:4:{s:14:"C1563961273903";a:5:{s:6:"imgurl";s:79:"https://four.nttrip.cn/diypage/template_img/template_retail04/index/menu2_1.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"创意吊灯";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1563961273904";a:5:{s:6:"imgurl";s:79:"https://four.nttrip.cn/diypage/template_img/template_retail04/index/menu2_2.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"北欧沙发";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1563961273905";a:5:{s:6:"imgurl";s:79:"https://four.nttrip.cn/diypage/template_img/template_retail04/index/menu2_3.png";s:7:"linkurl";s:0:"";s:4:"text";s:15:"北欧电视柜";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1563961273906";a:5:{s:6:"imgurl";s:79:"https://four.nttrip.cn/diypage/template_img/template_retail04/index/menu2_4.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"北欧铁艺";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}}s:2:"id";s:4:"menu";s:5:"index";s:3:"NaN";}s:14:"M1563961111047";a:6:{s:4:"icon";s:22:"iconfont2 icon-chanpin";s:6:"params";a:31:{s:11:"goodsscroll";s:1:"0";s:9:"showtitle";s:1:"1";s:9:"showprice";s:1:"1";s:7:"showtag";s:1:"0";s:9:"goodsdata";s:1:"1";s:6:"cateid";s:0:"";s:8:"catename";s:0:"";s:7:"groupid";s:0:"";s:9:"groupname";s:0:"";s:9:"goodssort";s:1:"0";s:8:"goodsnum";s:1:"6";s:8:"showicon";s:1:"0";s:12:"iconposition";s:8:"left top";s:12:"productprice";s:1:"1";s:16:"showproductprice";s:1:"0";s:9:"showsales";s:1:"1";s:16:"productpricetext";s:6:"原价";s:9:"salestext";s:6:"销量";s:16:"productpriceline";s:1:"0";s:7:"saleout";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"imgh_is";s:1:"1";s:4:"imgh";s:3:"100";s:7:"con_key";s:1:"1";s:8:"con_type";s:1:"1";s:8:"sourceid";s:0:"";}s:5:"style";a:20:{s:10:"background";s:7:"#ffffff";s:9:"liststyle";s:5:"block";s:8:"buystyle";s:0:"";s:9:"goodsicon";s:9:"recommand";s:9:"iconstyle";s:6:"circle";s:10:"pricecolor";s:7:"#ff5555";s:17:"productpricecolor";s:7:"#999999";s:14:"iconpaddingtop";s:1:"0";s:15:"iconpaddingleft";s:1:"0";s:11:"buybtncolor";s:7:"#ff5555";s:8:"iconzoom";s:2:"50";s:10:"titlecolor";s:7:"#000000";s:13:"tagbackground";s:7:"#fe5455";s:10:"salescolor";s:7:"#999999";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"paddingtop";s:1:"5";s:11:"paddingleft";s:2:"10";s:8:"showtype";s:1:"0";}s:4:"data";a:4:{s:14:"C1563961111047";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:3:"des";s:21:"这里是产品描述";}s:14:"C1563961111048";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1563961111049";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1563961111050";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是产品描述";}}s:2:"id";s:5:"goods";s:5:"index";s:3:"NaN";}s:14:"M1563961895254";a:6:{s:4:"icon";s:25:"iconfont2 icon-youhuiquan";s:6:"params";a:30:{s:11:"goodsscroll";s:1:"0";s:9:"showtitle";s:1:"1";s:9:"showprice";s:1:"1";s:7:"showtag";s:1:"0";s:9:"goodsdata";s:1:"3";s:6:"cateid";s:0:"";s:8:"catename";s:0:"";s:7:"groupid";s:0:"";s:9:"groupname";s:0:"";s:9:"goodssort";s:1:"0";s:8:"goodsnum";s:1:"6";s:8:"showicon";s:1:"0";s:12:"iconposition";s:8:"left top";s:12:"productprice";s:1:"1";s:16:"showproductprice";s:1:"0";s:9:"showsales";s:1:"1";s:16:"productpricetext";s:6:"原价";s:9:"salestext";s:6:"销量";s:16:"productpriceline";s:1:"0";s:7:"saleout";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"imgh_is";s:1:"1";s:4:"imgh";s:3:"100";s:7:"con_key";s:1:"1";s:8:"con_type";s:1:"1";}s:5:"style";a:20:{s:10:"background";s:7:"#ffffff";s:9:"liststyle";s:5:"block";s:8:"buystyle";s:0:"";s:9:"goodsicon";s:9:"recommand";s:9:"iconstyle";s:8:"triangle";s:10:"pricecolor";s:7:"#ff5555";s:17:"productpricecolor";s:7:"#999999";s:14:"iconpaddingtop";s:1:"0";s:15:"iconpaddingleft";s:1:"0";s:11:"buybtncolor";s:7:"#ff5555";s:8:"iconzoom";s:2:"50";s:10:"titlecolor";s:7:"#000000";s:13:"tagbackground";s:7:"#fe5455";s:10:"salescolor";s:7:"#999999";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:8:"showtype";s:1:"0";}s:4:"data";a:4:{s:14:"C1563961895254";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是商品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:3:"des";s:21:"这里是商品描述";}s:14:"C1563961895255";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是商品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"1";s:4:"desc";s:21:"这里是产品描述";}s:14:"C1563961895256";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是商品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是商品描述";}s:14:"C1563961895257";a:10:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/2.jpg";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是商品标题";s:3:"gid";s:0:"";s:7:"bargain";s:1:"0";s:6:"credit";s:1:"0";s:5:"ctype";s:1:"0";s:4:"desc";s:21:"这里是商品描述";}}s:2:"id";s:8:"yhqgoods";s:5:"index";s:3:"NaN";}s:14:"M1563961161278";a:6:{s:4:"icon";s:28:"iconfont2 icon-tuoyuankaobei";s:6:"params";a:10:{s:5:"totle";s:1:"2";s:8:"navstyle";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:9:"navstyle2";s:1:"0";s:4:"imgh";s:3:"110";}s:5:"style";a:18:{s:8:"dotstyle";s:5:"round";s:8:"dotalign";s:4:"left";s:10:"paddingtop";s:1:"0";s:11:"paddingleft";s:1:"0";s:10:"background";s:7:"#ffffff";s:13:"backgroundall";s:7:"#ffffff";s:9:"leftright";s:1:"5";s:6:"bottom";s:1:"5";s:7:"opacity";s:3:"0.8";s:10:"text_color";s:4:"#fff";s:2:"bg";s:7:"#000000";s:9:"jsq_color";s:3:"red";s:3:"pdh";s:1:"0";s:3:"pdw";s:1:"0";s:2:"mt";s:2:"13";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"speed";s:1:"5";}s:4:"data";a:2:{s:14:"C1563961161278";a:4:{s:6:"imgurl";s:81:"https://four.nttrip.cn/diypage/template_img/template_retail04/index/banner2_1.png";s:7:"linkurl";s:0:"";s:6:"single";s:1:"1";s:4:"text";s:12:"文字描述";}s:14:"C1563961161279";a:4:{s:6:"imgurl";s:81:"https://four.nttrip.cn/diypage/template_img/template_retail04/index/banner2_2.jpg";s:7:"linkurl";s:0:"";s:6:"single";s:1:"2";s:4:"text";s:12:"文字描述";}}s:2:"id";s:6:"banner";s:5:"index";s:3:"NaN";}}';
            $item1 = unserialize($item1);
            foreach($item1 as $k => &$v){
                if(isset($v['data'])){
                    foreach($v['data'] as $kk => &$vv){
                        if(isset($vv['imgurl']) && strpos($vv['imgurl'],'https://four.nttrip.cn') !== false){
                            $vv['imgurl'] = ROOT_HOST.explode('https://four.nttrip.cn', $vv['imgurl'])[1];
                        }
                        if(isset($vv['thumb']) && strpos($vv['thumb'],'/diypage/resource/images/diypage/default/2.jpg') !== false){
                            $vv['thumb'] = ROOT_HOST."/diypage/resource/images/diypage/default/2.jpg";
                        }
                    }
                }
            }
            $page1['items'] = serialize($item1);
            $page1['page'] = 'a:7:{s:10:"background";s:7:"#f1f1f1";s:13:"topbackground";s:7:"#ffffff";s:8:"topcolor";s:1:"1";s:9:"styledata";s:1:"0";s:5:"title";s:6:"首页";s:4:"name";s:6:"首页";s:10:"visitlevel";a:2:{s:6:"member";s:0:"";s:10:"commission";s:0:"";}}';
            $page1['tpl_name'] = "首页";

            $page1_id = Db::name('wd_xcx_diypage')->insertGetId($page1);
            $pageids = $page1_id;

            $is = Db::name('wd_xcx_diypageset')->where('uniacid',$uniacid)->find();
            if(empty($is)){
                $page_set = [
                    'uniacid' => $uniacid,
                    'kp' => ROOT_HOST."/diypage/resource/images/diypage/default/default_start.jpg",
                    'kp_is' => 2,
                    'kp_m' => 2,
                    'tc' => ROOT_HOST."/diypage/resource/images/diypage/default/tcgg.jpg",
                    'tc_is' => 2,
                    'foot_is' => 2
                ];
                Db::name('wd_xcx_diypageset')->insert($page_set);
            }else{
                Db::name('wd_xcx_diypageset')->where('uniacid',$uniacid)->update(array('foot_is' => 2));
            }
            $data = [
                'uniacid' => $uniacid,
                'pageid' => $pageids,
                'template_name' => '零售类模板04',
                'thumb' => "/diypage/template_img/template_retail04/cover.png",
                'status' => '1',
                'create_time' => time()
            ];
            $tplid = Db::name('wd_xcx_diypagetpl')->insertGetId($data);
            if($tplid){
                return $tplid;
            }
        }elseif($id == 'm_retail05'){
            $page1 = [];
            $page1['uniacid'] = $uniacid;
            $page1['index'] = 1;
            $item1 = 'a:9:{s:14:"M1577942558242";a:4:{s:4:"icon";s:14:"#iconsousuolan";s:6:"params";a:7:{s:5:"value";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:12:{s:9:"textalign";s:6:"center";s:10:"background";s:7:"#000000";s:2:"bg";s:7:"#ffffff";s:12:"borderradius";s:1:"5";s:6:"boxpdh";s:2:"10";s:6:"boxpdz";s:2:"25";s:7:"padding";s:1:"5";s:8:"fontsize";s:2:"13";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"color";s:7:"#cccccc";}s:2:"id";s:3:"ssk";}s:14:"M1577942571864";a:5:{s:4:"icon";s:15:"#iconzutumokuai";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:6:{s:10:"paddingtop";s:1:"0";s:11:"paddingleft";s:1:"0";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"background";s:7:"#ffffff";}s:4:"data";a:1:{s:14:"C1577942571864";a:2:{s:6:"imgurl";s:71:"https://four.nttrip.cn/template_img/template_retail05/index/picture.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:7:"picture";}s:14:"M1577942576015";a:4:{s:4:"icon";s:16:"#iconzhufubiaoti";s:6:"params";a:9:{s:5:"title";s:16:"HOT 只要绝配";s:6:"title2";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:5:"style";s:1:"1";}s:5:"style";a:11:{s:10:"background";s:7:"#f1f1f1";s:6:"colorz";s:7:"#434343";s:6:"colorf";s:7:"#838383";s:9:"linecolor";s:7:"#ff0000";s:9:"fontsizez";s:2:"14";s:9:"fontsizef";s:2:"12";s:10:"paddingtop";s:1:"5";s:11:"paddingleft";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:2:"10";}s:2:"id";s:6:"title2";}s:14:"M1577942593651";a:5:{s:4:"icon";s:20:"#icontupianchuchuang";s:6:"params";a:9:{s:3:"row";s:1:"2";s:8:"showtype";i:0;s:7:"pagenum";s:1:"2";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:8:{s:10:"paddingtop";s:1:"5";s:11:"paddingleft";s:1:"5";s:7:"showdot";i:0;s:7:"showbtn";i:0;s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:1:"0";s:10:"background";s:7:"#ffffff";}s:4:"data";a:4:{s:14:"C1577942593651";a:2:{s:6:"imgurl";s:73:"https://four.nttrip.cn/template_img/template_retail05/index/picturew1.png";s:7:"linkurl";s:0:"";}s:14:"M1577942598324";a:2:{s:6:"imgurl";s:73:"https://four.nttrip.cn/template_img/template_retail05/index/picturew2.png";s:7:"linkurl";s:0:"";}s:14:"M1577942604451";a:2:{s:6:"imgurl";s:73:"https://four.nttrip.cn/template_img/template_retail05/index/picturew3.png";s:7:"linkurl";s:0:"";}s:14:"M1577942605471";a:2:{s:6:"imgurl";s:73:"https://four.nttrip.cn/template_img/template_retail05/index/picturew1.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:8:"picturew";}s:14:"M1577942623397";a:5:{s:4:"icon";s:16:"#iconzhufubiaoti";s:6:"params";a:9:{s:5:"title";s:16:"NEW 主推尖货";s:6:"title2";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:5:"style";s:1:"1";}s:5:"style";a:11:{s:10:"background";s:7:"#f1f1f1";s:6:"colorz";s:7:"#434343";s:6:"colorf";s:7:"#838383";s:9:"linecolor";s:7:"#0080ff";s:9:"fontsizez";s:2:"14";s:9:"fontsizef";s:2:"12";s:10:"paddingtop";s:1:"5";s:11:"paddingleft";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:2:"10";}s:2:"id";s:6:"title2";s:5:"index";N;}s:14:"M1577942626332";a:5:{s:4:"icon";s:18:"#iconchanpinmokuai";s:6:"params";a:31:{s:11:"goodsscroll";s:1:"0";s:9:"showtitle";s:1:"1";s:9:"showprice";s:1:"1";s:7:"showtag";s:1:"0";s:9:"goodsdata";s:1:"1";s:6:"cateid";s:0:"";s:8:"catename";s:0:"";s:7:"groupid";s:0:"";s:9:"groupname";s:0:"";s:9:"goodssort";s:1:"0";s:8:"goodsnum";s:1:"6";s:8:"showicon";s:1:"0";s:12:"iconposition";s:8:"left top";s:12:"productprice";s:1:"1";s:16:"showproductprice";s:1:"0";s:9:"showsales";s:1:"1";s:16:"productpricetext";s:6:"原价";s:9:"salestext";s:6:"销量";s:16:"productpriceline";s:1:"0";s:7:"saleout";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"imgh_is";s:1:"1";s:4:"imgh";s:3:"100";s:7:"con_key";s:1:"1";s:8:"con_type";s:1:"1";s:8:"sourceid";s:0:"";}s:5:"style";a:20:{s:10:"background";s:7:"#ffffff";s:9:"liststyle";s:5:"block";s:8:"buystyle";s:0:"";s:9:"goodsicon";s:9:"recommand";s:9:"iconstyle";s:6:"circle";s:10:"pricecolor";s:7:"#ff6f6e";s:17:"productpricecolor";s:7:"#999999";s:14:"iconpaddingtop";s:1:"0";s:15:"iconpaddingleft";s:1:"0";s:11:"buybtncolor";s:7:"#ff5555";s:8:"iconzoom";s:2:"50";s:10:"titlecolor";s:7:"#434343";s:13:"tagbackground";s:7:"#fe5455";s:10:"salescolor";s:7:"#939393";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:8:"showtype";s:1:"0";}s:4:"data";a:4:{s:14:"C1577942626332";a:10:{s:5:"thumb";s:54:"/diypage/resource/images/diypage/default/no_proimg.png";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";i:0;s:6:"credit";i:0;s:5:"ctype";i:1;s:4:"desc";s:21:"这里是产品描述";}s:14:"C1577942626333";a:10:{s:5:"thumb";s:54:"/diypage/resource/images/diypage/default/no_proimg.png";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"title";s:21:"这里是产品标题";s:5:"sales";s:1:"5";s:3:"gid";s:0:"";s:7:"bargain";i:0;s:6:"credit";i:0;s:5:"ctype";i:1;s:4:"desc";s:21:"这里是产品描述";}s:14:"C1577942626334";a:10:{s:5:"thumb";s:54:"/diypage/resource/images/diypage/default/no_proimg.png";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";i:0;s:6:"credit";i:0;s:5:"ctype";i:0;s:4:"desc";s:21:"这里是产品描述";}s:14:"C1577942626335";a:10:{s:5:"thumb";s:54:"/diypage/resource/images/diypage/default/no_proimg.png";s:5:"price";s:5:"20.00";s:12:"productprice";s:5:"99.00";s:5:"sales";s:1:"5";s:5:"title";s:21:"这里是产品标题";s:3:"gid";s:0:"";s:7:"bargain";i:0;s:6:"credit";i:0;s:5:"ctype";i:0;s:4:"desc";s:21:"这里是产品描述";}}s:2:"id";s:5:"goods";}s:14:"M1578030190290";a:4:{s:4:"icon";s:15:"#icondangeanniu";s:6:"params";a:8:{s:4:"icon";s:9:"icon-home";s:5:"title";s:21:"查看更多商品＞";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:12:{s:11:"paddingleft";s:2:"10";s:10:"paddingtop";s:1:"0";s:3:"pdz";s:2:"10";s:2:"mt";s:1:"1";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"fs";s:2:"14";s:10:"background";s:7:"#ffffff";s:11:"bordercolor";s:7:"#ffffff";s:5:"btnbg";s:7:"#ffffff";s:5:"color";s:7:"#000000";s:12:"borderradius";s:1:"5";}s:2:"id";s:5:"anniu";}s:14:"M1577942656291";a:5:{s:4:"icon";s:16:"#iconzhufubiaoti";s:6:"params";a:9:{s:5:"title";s:17:"SIFT 精选专题";s:6:"title2";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:5:"style";s:1:"1";}s:5:"style";a:11:{s:10:"background";s:7:"#f1f1f1";s:6:"colorz";s:7:"#434343";s:6:"colorf";s:7:"#838383";s:9:"linecolor";s:7:"#8080ff";s:9:"fontsizez";s:2:"14";s:9:"fontsizef";s:2:"12";s:10:"paddingtop";s:1:"5";s:11:"paddingleft";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:2:"10";}s:2:"id";s:6:"title2";s:5:"index";N;}s:14:"M1577942657413";a:5:{s:4:"icon";s:16:"#icondatuzhanshi";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:7:{s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:2:"mt";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"background";s:7:"#ffffff";s:12:"borderradius";s:1:"5";}s:4:"data";a:3:{s:14:"C1577942657413";a:4:{s:6:"imgurl";s:71:"https://four.nttrip.cn/template_img/template_retail05/index/bigimg1.png";s:7:"linkurl";s:0:"";s:5:"title";s:0:"";s:4:"text";s:0:"";}s:14:"M1578030271759";a:4:{s:6:"imgurl";s:71:"https://four.nttrip.cn/template_img/template_retail05/index/bigimg2.png";s:7:"linkurl";s:0:"";s:5:"title";s:0:"";s:4:"text";s:0:"";}s:14:"M1578030273453";a:4:{s:6:"imgurl";s:71:"https://four.nttrip.cn/template_img/template_retail05/index/bigimg3.png";s:7:"linkurl";s:0:"";s:5:"title";s:0:"";s:4:"text";s:0:"";}}s:2:"id";s:6:"bigimg";}}';
            $item1 = unserialize($item1);
            foreach($item1 as $k => &$v){
                if(isset($v['data'])){
                    foreach($v['data'] as $kk => &$vv){
                        if(isset($vv['imgurl']) && strpos($vv['imgurl'],'https://four.nttrip.cn') !== false){
                            $vv['imgurl'] = ROOT_HOST . "/diypage/" . explode('https://four.nttrip.cn', $vv['imgurl'])[1];
                        }
                        if(isset($vv['thumb']) && strpos($vv['thumb'],'https://four.nttrip.cn') !== false){
                            $vv['thumb'] = ROOT_HOST . "/diypage/" . explode('https://four.nttrip.cn', $vv['thumb'])[1];
                        }
                        if(isset($vv['thumb']) && strpos($vv['thumb'],'/diypage/resource/images/diypage/default/no_proimg.png') !== false){
                            $vv['thumb'] = ROOT_HOST."/diypage/resource/images/diypage/default/no_proimg.png";
                        }
                    }
                }
            }
            $page1['items'] = serialize($item1);
            $page1['page'] = 'a:15:{s:4:"type";i:1;s:5:"title";s:6:"首页";s:4:"name";s:15:"未命名页面";s:10:"background";s:18:"rgb(241, 241, 241)";s:13:"topbackground";s:7:"#333333";s:8:"topcolor";s:1:"2";s:9:"styledata";s:1:"0";s:6:"repeat";s:0:"";s:9:"positionx";s:1:"0";s:9:"positiony";s:1:"0";s:4:"size";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"visitlevel";a:2:{s:6:"member";N;s:10:"commission";N;}s:7:"novisit";a:0:{}}';
            $page1['tpl_name'] = "首页";

            $page1_id = Db::name('wd_xcx_diypage')->insertGetId($page1);
            $pageids = $page1_id;

            $is = Db::name('wd_xcx_diypageset')->where('uniacid',$uniacid)->find();
            if(empty($is)){
                $page_set = [
                    'uniacid' => $uniacid,
                    'kp' => ROOT_HOST."/diypage/resource/images/diypage/default/default_start.jpg",
                    'kp_is' => 2,
                    'kp_m' => 2,
                    'tc' => ROOT_HOST."/diypage/resource/images/diypage/default/tcgg.jpg",
                    'tc_is' => 2,
                    'foot_is' => 2
                ];
                Db::name('wd_xcx_diypageset')->insert($page_set);
            }else{
                Db::name('wd_xcx_diypageset')->where('uniacid',$uniacid)->update(array('foot_is' => 2));
            }
            $data = [
                'uniacid' => $uniacid,
                'pageid' => $pageids,
                'template_name' => '零售类模板05',
                'thumb' => "/diypage/template_img/template_retail04/cover.png",
                'status' => '1',
                'create_time' => time()
            ];
            $tplid = Db::name('wd_xcx_diypagetpl')->insertGetId($data);
            if($tplid){
                return $tplid;
            }
        }elseif($id == 'm_life'){
            $page1 = [];
            $page1['uniacid'] = $uniacid;
            $page1['index'] = 1;
            $item1 = 'a:7:{s:14:"M1563764585117";a:5:{s:4:"icon";s:22:"iconfont2 icon-sousuo1";s:6:"params";a:7:{s:5:"value";s:0:"";s:9:"styledata";s:1:"1";s:6:"repeat";s:9:"no-repeat";s:9:"positionx";s:6:"center";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:74:"https://four.nttrip.cn/diypage/template_img/template_life/index/ssk_bg.png";}s:5:"style";a:12:{s:9:"textalign";s:4:"left";s:10:"background";s:7:"#f3f3f3";s:2:"bg";s:4:"#fff";s:12:"borderradius";s:1:"5";s:6:"boxpdh";s:2:"30";s:6:"boxpdz";s:2:"15";s:7:"padding";s:1:"5";s:8:"fontsize";s:2:"13";s:2:"mt";s:1:"0";s:5:"sizew";s:3:"100";s:5:"sizeh";s:2:"79";s:5:"color";s:0:"";}s:2:"id";s:3:"ssk";s:5:"index";s:3:"NaN";}s:14:"M1563763550992";a:5:{s:4:"icon";s:19:"iconfont2 icon-zutu";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:6:{s:10:"paddingtop";s:1:"0";s:11:"paddingleft";s:1:"0";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"background";s:7:"#ffffff";}s:4:"data";a:1:{s:14:"C1563763550993";a:2:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_life/index/picture.jpg";s:7:"linkurl";s:0:"";}}s:2:"id";s:7:"picture";}s:14:"M1563765143528";a:6:{s:4:"icon";s:22:"iconfont2 icon-anniuzu";s:6:"params";a:8:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"picicon";s:1:"1";s:8:"textshow";s:1:"1";}s:5:"style";a:14:{s:8:"navstyle";s:0:"";s:10:"background";s:7:"#ffffff";s:6:"rownum";s:1:"4";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"8";s:7:"showdot";s:1:"1";s:7:"padding";s:1:"0";s:11:"paddingleft";s:2:"10";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:6:"iconfz";s:2:"14";s:9:"iconcolor";s:7:"#434343";s:8:"imgwidth";s:2:"58";}s:4:"data";a:8:{s:14:"C1563765143528";a:5:{s:6:"imgurl";s:73:"https://four.nttrip.cn/diypage/template_img/template_life/index/menu1.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"汽车服务";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1563765143530";a:5:{s:6:"imgurl";s:73:"https://four.nttrip.cn/diypage/template_img/template_life/index/menu2.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"丽人按摩";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1563765143531";a:5:{s:6:"imgurl";s:73:"https://four.nttrip.cn/diypage/template_img/template_life/index/menu3.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"搬家货运";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"M1563766961833";a:5:{s:6:"imgurl";s:73:"https://four.nttrip.cn/diypage/template_img/template_life/index/menu4.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"保姆月嫂";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"M1563766963360";a:5:{s:6:"imgurl";s:73:"https://four.nttrip.cn/diypage/template_img/template_life/index/menu5.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"安装维修";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"M1563766964606";a:5:{s:6:"imgurl";s:73:"https://four.nttrip.cn/diypage/template_img/template_life/index/menu6.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"洗衣修鞋";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"M1563766965631";a:5:{s:6:"imgurl";s:73:"https://four.nttrip.cn/diypage/template_img/template_life/index/menu7.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"开锁换锁";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"M1563766966695";a:5:{s:6:"imgurl";s:73:"https://four.nttrip.cn/diypage/template_img/template_life/index/menu8.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"全部分类";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}}s:2:"id";s:4:"menu";s:5:"index";s:3:"NaN";}s:14:"M1563779650040";a:4:{s:4:"icon";s:32:"iconfont2 icon-liebiao_biaotilan";s:6:"params";a:9:{s:5:"title";s:12:"常见服务";s:6:"title2";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:5:"style";s:1:"1";}s:5:"style";a:11:{s:10:"background";s:7:"#ffffff";s:6:"colorz";s:7:"#434343";s:6:"colorf";s:7:"#838383";s:9:"linecolor";s:7:"#c0c0c0";s:9:"fontsizez";s:2:"14";s:9:"fontsizef";s:2:"12";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:1:"7";}s:2:"id";s:6:"title2";}s:14:"M1563776960519";a:6:{s:4:"icon";s:21:"iconfont2 icon-tupian";s:6:"params";a:9:{s:3:"row";s:1:"3";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"2";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:8:{s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:7:"showdot";s:1:"0";s:7:"showbtn";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:1:"1";s:10:"background";s:7:"#ffffff";}s:4:"data";a:3:{s:14:"C1563776960519";a:2:{s:6:"imgurl";s:77:"https://four.nttrip.cn/diypage/template_img/template_life/index/picturew1.png";s:7:"linkurl";s:0:"";}s:14:"C1563776960520";a:2:{s:6:"imgurl";s:77:"https://four.nttrip.cn/diypage/template_img/template_life/index/picturew2.png";s:7:"linkurl";s:0:"";}s:14:"C1563776960521";a:2:{s:6:"imgurl";s:77:"https://four.nttrip.cn/diypage/template_img/template_life/index/picturew3.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:8:"picturew";s:5:"index";s:3:"NaN";}s:14:"M1563779691598";a:5:{s:4:"icon";s:32:"iconfont2 icon-liebiao_biaotilan";s:6:"params";a:9:{s:5:"title";s:12:"推荐资讯";s:6:"title2";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:5:"style";s:1:"1";}s:5:"style";a:11:{s:10:"background";s:7:"#ffffff";s:6:"colorz";s:7:"#434343";s:6:"colorf";s:7:"#838383";s:9:"linecolor";s:7:"#c0c0c0";s:9:"fontsizez";s:2:"14";s:9:"fontsizef";s:2:"12";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:2:"10";}s:2:"id";s:6:"title2";s:5:"index";s:3:"NaN";}s:14:"M1563779740051";a:5:{s:4:"icon";s:23:"iconfont2 icon-wenzhang";s:6:"params";a:19:{s:9:"showstyle";s:4:"row1";s:7:"newsnum";s:1:"3";s:8:"newsdata";s:1:"0";s:5:"title";s:21:"请选择文章分类";s:7:"titleid";s:1:"0";s:8:"navstyle";s:1:"1";s:5:"show1";s:1:"1";s:5:"show2";s:1:"1";s:5:"show3";s:1:"1";s:5:"show4";s:1:"1";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"con_key";s:1:"1";s:8:"con_type";s:1:"1";s:8:"sourceid";s:0:"";}s:5:"style";a:11:{s:10:"background";s:4:"#fff";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:12:"marginbottom";s:2:"10";s:2:"mt";s:1:"1";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"color";s:7:"#434343";s:6:"radius";s:1:"0";s:4:"pich";s:1:"1";s:8:"showtype";s:1:"0";}s:4:"data";a:3:{s:14:"C1563779740051";a:8:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/3.jpg";s:2:"id";s:0:"";s:5:"title";s:15:"这里是标题";s:4:"time";s:16:"2017年10月1日";s:5:"intro";s:21:"简介1简介1简介1";s:3:"ydl";s:2:"10";s:3:"dzl";s:2:"10";s:3:"pll";s:2:"10";}s:14:"C1563779740052";a:8:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/3.jpg";s:2:"id";s:0:"";s:5:"title";s:15:"这里是标题";s:4:"time";s:16:"2017年10月1日";s:5:"intro";s:15:"这里是简介";s:3:"ydl";s:2:"10";s:3:"dzl";s:2:"10";s:3:"pll";s:2:"10";}s:14:"C1563779740053";a:8:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/3.jpg";s:2:"id";s:0:"";s:5:"title";s:15:"这里是标题";s:4:"time";s:16:"2017年10月1日";s:5:"intro";s:15:"这里是简介";s:3:"ydl";s:2:"10";s:3:"dzl";s:2:"10";s:3:"pll";s:2:"10";}}s:2:"id";s:8:"listdesc";}}';
            $item1 = unserialize($item1);
            foreach($item1 as $k => &$v){
                if(isset($v['data'])){
                    foreach($v['data'] as $kk => &$vv){
                        if(isset($vv['imgurl']) && strpos($vv['imgurl'],'https://four.nttrip.cn') !== false){
                            $vv['imgurl'] = ROOT_HOST.explode('https://four.nttrip.cn', $vv['imgurl'])[1];
                        }
                        if(isset($vv['thumb']) && strpos($vv['thumb'],'/diypage/resource/images/diypage/default/3.jpg') !== false){
                            $vv['thumb'] = ROOT_HOST."/diypage/resource/images/diypage/default/3.jpg";
                        }
                    }
                }
            }
            $page1['items'] = serialize($item1);
            $page1['page'] = 'a:7:{s:10:"background";s:7:"#f1f1f1";s:13:"topbackground";s:7:"#ffffff";s:8:"topcolor";s:1:"1";s:9:"styledata";s:1:"0";s:5:"title";s:21:"小程序页面标题";s:4:"name";s:18:"后台页面名称";s:10:"visitlevel";a:2:{s:6:"member";s:0:"";s:10:"commission";s:0:"";}}';
            $page1['tpl_name'] = "首页";

            $page1_id = Db::name('wd_xcx_diypage')->insertGetId($page1);
            $pageids = $page1_id;

            $is = Db::name('wd_xcx_diypageset')->where('uniacid',$uniacid)->find();
            if(empty($is)){
                $page_set = [
                    'uniacid' => $uniacid,
                    'kp' => ROOT_HOST."/diypage/resource/images/diypage/default/default_start.jpg",
                    'kp_is' => 2,
                    'kp_m' => 2,
                    'tc' => ROOT_HOST."/diypage/resource/images/diypage/default/tcgg.jpg",
                    'tc_is' => 2,
                    'foot_is' => 2
                ];
                Db::name('wd_xcx_diypageset')->insert($page_set);
            }else{
                Db::name('wd_xcx_diypageset')->where('uniacid',$uniacid)->update(array('foot_is' => 2));
            }
            $data = [
                'uniacid' => $uniacid,
                'pageid' => $pageids,
                'template_name' => '生活服务类模板01',
                'thumb' => "/diypage/template_img/template_life/cover.png",
                'status' => '1',
                'create_time' => time()
            ];
            $tplid = Db::name('wd_xcx_diypagetpl')->insertGetId($data);
            if($tplid){
                return $tplid;
            }
        }elseif($id == 'm_life02'){
            $page1 = [];
            $page1['uniacid'] = $uniacid;
            $page1['index'] = 1;
            $item1 = 'a:12:{s:14:"M1563780233756";a:5:{s:4:"icon";s:19:"iconfont2 icon-zutu";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:6:{s:10:"paddingtop";s:1:"0";s:11:"paddingleft";s:1:"0";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"background";s:7:"#ffffff";}s:4:"data";a:1:{s:14:"C1563780233756";a:2:{s:6:"imgurl";s:78:"https://four.nttrip.cn/diypage/template_img/template_life02/index/picture1.jpg";s:7:"linkurl";s:0:"";}}s:2:"id";s:7:"picture";}s:14:"M1563780135799";a:4:{s:4:"icon";s:22:"iconfont2 icon-sousuo1";s:6:"params";a:7:{s:5:"value";s:0:"";s:9:"styledata";s:1:"1";s:6:"repeat";s:9:"no-repeat";s:9:"positionx";s:6:"center";s:9:"positiony";s:6:"center";s:4:"size";s:1:"1";s:13:"backgroundimg";s:76:"https://four.nttrip.cn/diypage/template_img/template_life02/index/ssk_bg.jpg";}s:5:"style";a:12:{s:9:"textalign";s:6:"center";s:10:"background";s:7:"#ffffff";s:2:"bg";s:4:"#fff";s:12:"borderradius";s:1:"5";s:6:"boxpdh";s:1:"7";s:6:"boxpdz";s:2:"27";s:7:"padding";s:1:"5";s:8:"fontsize";s:2:"13";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"color";s:0:"";}s:2:"id";s:3:"ssk";}s:14:"M1563780278073";a:5:{s:4:"icon";s:22:"iconfont2 icon-anniuzu";s:6:"params";a:8:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"picicon";s:1:"1";s:8:"textshow";s:1:"1";}s:5:"style";a:14:{s:8:"navstyle";s:0:"";s:10:"background";s:7:"#ffffff";s:6:"rownum";s:1:"5";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"8";s:7:"showdot";s:1:"1";s:7:"padding";s:1:"0";s:11:"paddingleft";s:2:"10";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:6:"iconfz";s:2:"14";s:9:"iconcolor";s:7:"#434343";s:8:"imgwidth";s:2:"62";}s:4:"data";a:10:{s:14:"C1563780278073";a:5:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_life02/index/menu1.png";s:7:"linkurl";s:0:"";s:4:"text";s:6:"家政";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1563780278074";a:5:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_life02/index/menu2.png";s:7:"linkurl";s:0:"";s:4:"text";s:6:"外卖";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1563780278075";a:5:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_life02/index/menu3.png";s:7:"linkurl";s:0:"";s:4:"text";s:6:"跑腿";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1563780278076";a:5:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_life02/index/menu4.png";s:7:"linkurl";s:0:"";s:4:"text";s:6:"帮手";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"M1563780573076";a:5:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_life02/index/menu5.png";s:7:"linkurl";s:0:"";s:4:"text";s:6:"酒店";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"M1563780938525";a:5:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_life02/index/menu6.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"生活预约";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"M1563780939503";a:5:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_life02/index/menu7.png";s:7:"linkurl";s:0:"";s:4:"text";s:6:"生鲜";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"M1563780940537";a:5:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_life02/index/menu8.png";s:7:"linkurl";s:0:"";s:4:"text";s:6:"超市";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"M1563780941303";a:5:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_life02/index/menu9.png";s:7:"linkurl";s:0:"";s:4:"text";s:9:"优惠券";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"M1563780942212";a:5:{s:6:"imgurl";s:76:"https://four.nttrip.cn/diypage/template_img/template_life02/index/menu10.png";s:7:"linkurl";s:0:"";s:4:"text";s:6:"汽车";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}}s:2:"id";s:4:"menu";}s:14:"M1563791630418";a:5:{s:4:"icon";s:19:"iconfont icon-c-pdf";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:6:{s:10:"background";s:7:"#f3f3f3";s:3:"pdw";s:1:"1";s:3:"pdh";s:1:"0";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";}s:4:"data";a:2:{s:14:"C1563791630419";a:5:{s:6:"imgurl";s:79:"https://four.nttrip.cn/diypage/template_img/template_life02/index/classfit1.png";s:5:"title";s:12:"物业服务";s:4:"text";s:8:"PROPERTY";s:3:"bg1";s:7:"#ffffff";s:3:"bg2";s:7:"#ffffff";}s:14:"C1563791630420";a:5:{s:6:"imgurl";s:79:"https://four.nttrip.cn/diypage/template_img/template_life02/index/classfit2.png";s:5:"title";s:12:"无忧商城";s:4:"text";s:8:"SHOPPING";s:3:"bg1";s:7:"#ffffff";s:3:"bg2";s:7:"#ffffff";}}s:2:"id";s:8:"classfit";}s:14:"M1563842962517";a:5:{s:4:"icon";s:22:"iconfont2 icon-gonggao";s:6:"params";a:12:{s:7:"iconurl";s:15:"icon-x-miaosha3";s:10:"noticedata";s:1:"0";s:5:"speed";s:1:"4";s:9:"noticenum";s:1:"5";s:8:"navstyle";s:1:"1";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:8:"sourceid";s:0:"";}s:5:"style";a:9:{s:10:"background";s:7:"#ffffff";s:9:"iconcolor";s:7:"#fd5454";s:5:"color";s:7:"#666666";s:11:"bordercolor";s:7:"#e2e2e2";s:2:"mt";s:1:"1";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";}s:4:"data";a:2:{s:14:"C1563842962517";a:2:{s:5:"title";s:42:"这里是第一条自定义公告的标题";s:7:"linkurl";s:0:"";}s:14:"C1563842962518";a:2:{s:5:"title";s:42:"这里是第二条自定义公告的标题";s:7:"linkurl";s:0:"";}}s:2:"id";s:6:"notice";}s:14:"M1563791704234";a:4:{s:4:"icon";s:32:"iconfont2 icon-liebiao_biaotilan";s:6:"params";a:9:{s:5:"title";s:12:"上门服务";s:6:"title2";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:5:"style";s:1:"2";}s:5:"style";a:11:{s:10:"background";s:7:"#ffffff";s:6:"colorz";s:7:"#434343";s:6:"colorf";s:7:"#838383";s:9:"linecolor";s:7:"#ffff00";s:9:"fontsizez";s:2:"15";s:9:"fontsizef";s:2:"12";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:2:"10";}s:2:"id";s:6:"title2";}s:14:"M1563844662562";a:5:{s:4:"icon";s:21:"iconfont2 icon-tupian";s:6:"params";a:9:{s:3:"row";s:1:"3";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"2";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:8:{s:10:"paddingtop";s:1:"7";s:11:"paddingleft";s:2:"10";s:7:"showdot";s:1:"0";s:7:"showbtn";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:1:"1";s:10:"background";s:7:"#ffffff";}s:4:"data";a:3:{s:14:"C1563844662562";a:2:{s:6:"imgurl";s:79:"https://four.nttrip.cn/diypage/template_img/template_life02/index/picturew1.png";s:7:"linkurl";s:0:"";}s:14:"C1563844662563";a:2:{s:6:"imgurl";s:79:"https://four.nttrip.cn/diypage/template_img/template_life02/index/picturew2.png";s:7:"linkurl";s:0:"";}s:14:"C1563844662564";a:2:{s:6:"imgurl";s:79:"https://four.nttrip.cn/diypage/template_img/template_life02/index/picturew3.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:8:"picturew";}s:14:"M1563844600853";a:5:{s:4:"icon";s:19:"iconfont2 icon-zutu";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:6:{s:10:"paddingtop";s:1:"8";s:11:"paddingleft";s:2:"10";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"background";s:7:"#ffffff";}s:4:"data";a:2:{s:14:"C1563844600853";a:2:{s:6:"imgurl";s:80:"https://four.nttrip.cn/diypage/template_img/template_life02/index/picture2_1.jpg";s:7:"linkurl";s:0:"";}s:14:"M1563844823274";a:2:{s:6:"imgurl";s:80:"https://four.nttrip.cn/diypage/template_img/template_life02/index/picture2_2.jpg";s:7:"linkurl";s:0:"";}}s:2:"id";s:7:"picture";}s:14:"M1563845138730";a:4:{s:4:"icon";s:32:"iconfont2 icon-liebiao_biaotilan";s:6:"params";a:9:{s:5:"title";s:12:"商家推荐";s:6:"title2";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:5:"style";s:1:"2";}s:5:"style";a:11:{s:10:"background";s:7:"#ffffff";s:6:"colorz";s:7:"#434343";s:6:"colorf";s:7:"#838383";s:9:"linecolor";s:7:"#ffff00";s:9:"fontsizez";s:2:"15";s:9:"fontsizef";s:2:"12";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:2:"10";}s:2:"id";s:6:"title2";}s:14:"M1563845436512";a:5:{s:4:"icon";s:22:"iconfont2 icon-anniuzu";s:6:"params";a:9:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"picicon";s:1:"1";s:6:"counts";s:1:"8";s:12:"content_type";s:1:"1";}s:5:"style";a:14:{s:8:"navstyle";s:6:"radius";s:10:"background";s:7:"#ffffff";s:6:"rownum";s:1:"4";s:8:"showtype";s:1:"1";s:7:"pagenum";s:1:"8";s:7:"showdot";s:1:"0";s:7:"padding";s:1:"0";s:11:"paddingleft";s:2:"10";s:2:"mt";s:1:"1";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:6:"iconfz";s:2:"14";s:9:"iconcolor";s:7:"#434343";s:8:"imgwidth";s:2:"50";}s:4:"data";a:4:{s:14:"C1563845436512";a:5:{s:6:"imgurl";s:51:"/diypage/resource/images/diypage/default/icon-1.png";s:7:"linkurl";s:0:"";s:4:"text";s:10:"多商户1";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1563845436513";a:5:{s:6:"imgurl";s:51:"/diypage/resource/images/diypage/default/icon-2.png";s:7:"linkurl";s:0:"";s:4:"text";s:10:"多商户2";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1563845436514";a:5:{s:6:"imgurl";s:51:"/diypage/resource/images/diypage/default/icon-3.png";s:7:"linkurl";s:0:"";s:4:"text";s:10:"多商户3";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1563845436515";a:5:{s:6:"imgurl";s:51:"/diypage/resource/images/diypage/default/icon-4.png";s:7:"linkurl";s:0:"";s:4:"text";s:10:"多商户4";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}}s:2:"id";s:8:"multiple";}s:14:"M1563845515101";a:5:{s:4:"icon";s:32:"iconfont2 icon-liebiao_biaotilan";s:6:"params";a:9:{s:5:"title";s:12:"供求资讯";s:6:"title2";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:5:"style";s:1:"2";}s:5:"style";a:11:{s:10:"background";s:7:"#ffffff";s:6:"colorz";s:7:"#434343";s:6:"colorf";s:7:"#838383";s:9:"linecolor";s:7:"#ffff00";s:9:"fontsizez";s:2:"15";s:9:"fontsizef";s:2:"12";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:2:"10";}s:2:"id";s:6:"title2";s:5:"index";s:3:"NaN";}s:14:"M1563845548294";a:5:{s:4:"icon";s:19:"iconfont icon-c-360";s:6:"params";a:20:{s:9:"showstyle";s:4:"row1";s:7:"newsnum";s:1:"3";s:8:"newsdata";s:1:"0";s:5:"title";s:21:"请选择文章分类";s:7:"titleid";s:1:"0";s:8:"navstyle";s:1:"1";s:5:"show1";s:1:"1";s:5:"show2";s:1:"1";s:5:"show3";s:1:"1";s:5:"show4";s:1:"1";s:10:"data_types";s:1:"1";s:6:"supply";s:1:"1";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"con_key";s:1:"1";s:8:"con_type";s:1:"1";}s:5:"style";a:11:{s:10:"background";s:4:"#fff";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:12:"marginbottom";s:2:"10";s:2:"mt";s:1:"1";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"color";s:7:"#434343";s:6:"radius";s:1:"0";s:4:"pich";s:1:"1";s:8:"showtype";s:1:"0";}s:4:"data";a:3:{s:14:"C1563845548294";a:8:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/3.jpg";s:2:"id";s:1:"1";s:5:"title";s:15:"这里是标题";s:4:"time";s:16:"2017年10月1日";s:5:"intro";s:21:"简介1简介1简介1";s:3:"ydl";s:2:"10";s:3:"dzl";s:2:"10";s:3:"pll";s:2:"10";}s:14:"C1563845548295";a:8:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/3.jpg";s:2:"id";s:1:"2";s:5:"title";s:15:"这里是标题";s:4:"time";s:16:"2017年10月1日";s:5:"intro";s:15:"这里是简介";s:3:"ydl";s:2:"10";s:3:"dzl";s:2:"10";s:3:"pll";s:2:"10";}s:14:"C1563845548296";a:8:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/3.jpg";s:2:"id";s:1:"3";s:5:"title";s:15:"这里是标题";s:4:"time";s:16:"2017年10月1日";s:5:"intro";s:15:"这里是简介";s:3:"ydl";s:2:"10";s:3:"dzl";s:2:"10";s:3:"pll";s:2:"10";}}s:2:"id";s:6:"supply";}}';
            $item1 = unserialize($item1);
            foreach($item1 as $k => &$v){
                if(isset($v['data'])){
                    foreach($v['data'] as $kk => &$vv){
                        if(isset($vv['imgurl']) && strpos($vv['imgurl'],'https://four.nttrip.cn') !== false){
                            $vv['imgurl'] = ROOT_HOST.explode('https://four.nttrip.cn', $vv['imgurl'])[1];
                        }
                        if(isset($vv['thumb']) && strpos($vv['thumb'],'/diypage/resource/images/diypage/default/icon-1.png') !== false){
                            $vv['thumb'] = ROOT_HOST."/diypage/resource/images/diypage/default/icon-1.png";
                        }
                        if(isset($vv['thumb']) && strpos($vv['thumb'],'/diypage/resource/images/diypage/default/icon-2.png') !== false){
                            $vv['thumb'] = ROOT_HOST."/diypage/resource/images/diypage/default/icon-2.png";
                        }
                        if(isset($vv['thumb']) && strpos($vv['thumb'],'/diypage/resource/images/diypage/default/icon-3.png') !== false){
                            $vv['thumb'] = ROOT_HOST."/diypage/resource/images/diypage/default/icon-3.png";
                        }
                        if(isset($vv['thumb']) && strpos($vv['thumb'],'/diypage/resource/images/diypage/default/icon-4.png') !== false){
                            $vv['thumb'] = ROOT_HOST."/diypage/resource/images/diypage/default/icon-4.png";
                        }
                        if(isset($vv['thumb']) && strpos($vv['thumb'],'/diypage/resource/images/diypage/default/3.jpg') !== false){
                            $vv['thumb'] = ROOT_HOST."/diypage/resource/images/diypage/default/3.jpg";
                        }
                    }
                }
            }
            $page1['items'] = serialize($item1);
            $page1['page'] = 'a:7:{s:10:"background";s:7:"#f1f1f1";s:13:"topbackground";s:7:"#6fdcd7";s:8:"topcolor";s:1:"2";s:9:"styledata";s:1:"0";s:5:"title";s:12:"平台首页";s:4:"name";s:12:"平台首页";s:10:"visitlevel";a:2:{s:6:"member";s:0:"";s:10:"commission";s:0:"";}}';
            $page1['tpl_name'] = "平台首页";

            $page1_id = Db::name('wd_xcx_diypage')->insertGetId($page1);
            $pageids = $page1_id;

            $is = Db::name('wd_xcx_diypageset')->where('uniacid',$uniacid)->find();
            if(empty($is)){
                $page_set = [
                    'uniacid' => $uniacid,
                    'kp' => ROOT_HOST."/diypage/resource/images/diypage/default/default_start.jpg",
                    'kp_is' => 2,
                    'kp_m' => 2,
                    'tc' => ROOT_HOST."/diypage/resource/images/diypage/default/tcgg.jpg",
                    'tc_is' => 2,
                    'foot_is' => 2
                ];
                Db::name('wd_xcx_diypageset')->insert($page_set);
            }else{
                Db::name('wd_xcx_diypageset')->where('uniacid',$uniacid)->update(array('foot_is' => 2));
            }
            $data = [
                'uniacid' => $uniacid,
                'pageid' => $pageids,
                'template_name' => '生活服务类模板02',
                'thumb' => "/diypage/template_img/template_life02/cover.png",
                'status' => '1',
                'create_time' => time()
            ];
            $tplid = Db::name('wd_xcx_diypagetpl')->insertGetId($data);
            if($tplid){
                return $tplid;
            }
        }elseif($id == 'm_life03'){
            $page1 = [];
            $page1['uniacid'] = $uniacid;
            $page1['index'] = 1;
            $item1 = 'a:11:{s:14:"M1563764585117";a:5:{s:4:"icon";s:22:"iconfont2 icon-sousuo1";s:6:"params";a:7:{s:5:"value";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:9:"no-repeat";s:9:"positionx";s:6:"center";s:9:"positiony";s:3:"top";s:4:"size";s:1:"1";s:13:"backgroundimg";s:58:"/upimages/20190725/3556d9cea51e92b9701f8df946481789280.png";}s:5:"style";a:12:{s:9:"textalign";s:6:"center";s:10:"background";s:7:"#5bdd85";s:2:"bg";s:4:"#fff";s:12:"borderradius";s:2:"10";s:6:"boxpdh";s:2:"15";s:6:"boxpdz";s:2:"15";s:7:"padding";s:1:"5";s:8:"fontsize";s:2:"14";s:2:"mt";s:1:"0";s:5:"sizew";s:3:"100";s:5:"sizeh";s:2:"79";s:5:"color";s:7:"#9cce6c";}s:2:"id";s:3:"ssk";s:5:"index";s:3:"NaN";}s:14:"M1564018053743";a:5:{s:4:"icon";s:28:"iconfont2 icon-tuoyuankaobei";s:6:"params";a:10:{s:5:"totle";s:1:"2";s:8:"navstyle";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:9:"navstyle2";s:1:"0";s:4:"imgh";s:3:"180";}s:5:"style";a:18:{s:8:"dotstyle";s:5:"round";s:8:"dotalign";s:4:"left";s:10:"paddingtop";s:1:"0";s:11:"paddingleft";s:1:"0";s:10:"background";s:7:"#ffffff";s:13:"backgroundall";s:7:"#ffffff";s:9:"leftright";s:1:"5";s:6:"bottom";s:1:"5";s:7:"opacity";s:1:"0";s:10:"text_color";s:4:"#fff";s:2:"bg";s:7:"#000000";s:9:"jsq_color";s:3:"red";s:3:"pdh";s:1:"0";s:3:"pdw";s:1:"0";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"speed";s:1:"5";}s:4:"data";a:1:{s:14:"C1564018053743";a:4:{s:6:"imgurl";s:76:"https://four.nttrip.cn/diypage/template_img/template_life03/index/banner.png";s:7:"linkurl";s:0:"";s:6:"single";s:1:"1";s:4:"text";s:12:"文字描述";}}s:2:"id";s:6:"banner";}s:14:"M1564018258421";a:5:{s:4:"icon";s:21:"iconfont2 icon-tupian";s:6:"params";a:9:{s:3:"row";s:1:"2";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"2";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:8:{s:10:"paddingtop";s:1:"8";s:11:"paddingleft";s:1:"5";s:7:"showdot";s:1:"0";s:7:"showbtn";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:1:"0";s:10:"background";s:7:"#f1f1f1";}s:4:"data";a:2:{s:14:"C1564018258421";a:2:{s:6:"imgurl";s:79:"https://four.nttrip.cn/diypage/template_img/template_life03/index/picturew1.png";s:7:"linkurl";s:0:"";}s:14:"C1564018258422";a:2:{s:6:"imgurl";s:79:"https://four.nttrip.cn/diypage/template_img/template_life03/index/picturew2.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:8:"picturew";}s:14:"M1564018106348";a:4:{s:4:"icon";s:32:"iconfont2 icon-liebiao_biaotilan";s:6:"params";a:9:{s:5:"title";s:12:"特色频道";s:6:"title2";s:3:"HOT";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:5:"style";s:1:"1";}s:5:"style";a:11:{s:10:"background";s:7:"#ffffff";s:6:"colorz";s:7:"#000000";s:6:"colorf";s:7:"#fca76f";s:9:"linecolor";s:7:"#ffffff";s:9:"fontsizez";s:2:"16";s:9:"fontsizef";s:2:"16";s:10:"paddingtop";s:1:"7";s:11:"paddingleft";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:1:"0";}s:2:"id";s:6:"title2";}s:14:"M1563765143528";a:6:{s:4:"icon";s:22:"iconfont2 icon-anniuzu";s:6:"params";a:8:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"picicon";s:1:"1";s:8:"textshow";s:1:"1";}s:5:"style";a:14:{s:8:"navstyle";s:0:"";s:10:"background";s:7:"#ffffff";s:6:"rownum";s:1:"5";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"8";s:7:"showdot";s:1:"1";s:7:"padding";s:1:"0";s:11:"paddingleft";s:2:"10";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:6:"iconfz";s:2:"14";s:9:"iconcolor";s:7:"#434343";s:8:"imgwidth";s:2:"58";}s:4:"data";a:10:{s:14:"C1563765143528";a:5:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_life03/index/menu1.png";s:7:"linkurl";s:0:"";s:4:"text";s:6:"美食";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1563765143530";a:5:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_life03/index/menu2.png";s:7:"linkurl";s:0:"";s:4:"text";s:6:"美发";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1563765143531";a:5:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_life03/index/menu3.png";s:7:"linkurl";s:0:"";s:4:"text";s:6:"酒店";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"M1563766961833";a:5:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_life03/index/menu4.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"休闲娱乐";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"M1563766963360";a:5:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_life03/index/menu5.png";s:7:"linkurl";s:0:"";s:4:"text";s:6:"丽人";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"M1563766964606";a:5:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_life03/index/menu6.png";s:7:"linkurl";s:0:"";s:4:"text";s:9:"周边游";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"M1563766965631";a:5:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_life03/index/menu7.png";s:7:"linkurl";s:0:"";s:4:"text";s:3:"KTV";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"M1563766966695";a:5:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_life03/index/menu8.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"母婴亲子";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"M1564018696048";a:5:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_life03/index/menu9.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"运动健身";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"M1564018704063";a:5:{s:6:"imgurl";s:76:"https://four.nttrip.cn/diypage/template_img/template_life03/index/menu10.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"全部频道";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}}s:2:"id";s:4:"menu";s:5:"index";s:3:"NaN";}s:14:"M1563779650040";a:4:{s:4:"icon";s:32:"iconfont2 icon-liebiao_biaotilan";s:6:"params";a:9:{s:5:"title";s:12:"为你推荐";s:6:"title2";s:4:"BEST";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:5:"style";s:1:"1";}s:5:"style";a:11:{s:10:"background";s:7:"#ffffff";s:6:"colorz";s:7:"#000000";s:6:"colorf";s:7:"#c2e132";s:9:"linecolor";s:7:"#ffffff";s:9:"fontsizez";s:2:"16";s:9:"fontsizef";s:2:"16";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:2:"15";}s:2:"id";s:6:"title2";}s:14:"M1564018940509";a:4:{s:4:"icon";s:19:"iconfont2 icon-zutu";s:6:"params";a:7:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:12:"content_type";s:1:"1";}s:5:"style";a:7:{s:10:"paddingtop";s:1:"5";s:11:"paddingleft";s:2:"10";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"background";s:7:"#ffffff";s:9:"viewcount";s:1:"4";}s:2:"id";s:5:"mlist";}s:14:"M1564019063460";a:4:{s:4:"icon";s:20:"iconfont2 icon-anniu";s:6:"params";a:8:{s:4:"icon";s:9:"icon-home";s:5:"title";s:12:"查看更多";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:12:{s:11:"paddingleft";s:3:"130";s:10:"paddingtop";s:2:"10";s:3:"pdz";s:1:"5";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"fs";s:2:"16";s:10:"background";s:7:"#ffffff";s:11:"bordercolor";s:7:"#abd682";s:5:"btnbg";s:7:"#ffffff";s:5:"color";s:7:"#abd682";s:12:"borderradius";s:1:"5";}s:2:"id";s:5:"anniu";}s:14:"M1563779691598";a:5:{s:4:"icon";s:32:"iconfont2 icon-liebiao_biaotilan";s:6:"params";a:9:{s:5:"title";s:12:"新店开张";s:6:"title2";s:4:"BEST";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:5:"style";s:1:"1";}s:5:"style";a:11:{s:10:"background";s:7:"#ffffff";s:6:"colorz";s:7:"#000000";s:6:"colorf";s:7:"#c2e132";s:9:"linecolor";s:7:"#ffffff";s:9:"fontsizez";s:2:"16";s:9:"fontsizef";s:2:"16";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:2:"15";}s:2:"id";s:6:"title2";s:5:"index";s:3:"NaN";}s:14:"M1564019403884";a:5:{s:4:"icon";s:19:"iconfont2 icon-zutu";s:6:"params";a:7:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:12:"content_type";s:1:"1";}s:5:"style";a:7:{s:10:"paddingtop";s:1:"5";s:11:"paddingleft";s:2:"10";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"background";s:7:"#ffffff";s:9:"viewcount";s:1:"4";}s:2:"id";s:5:"mlist";s:5:"index";s:3:"NaN";}s:14:"M1564019421924";a:5:{s:4:"icon";s:20:"iconfont2 icon-anniu";s:6:"params";a:8:{s:4:"icon";s:9:"icon-home";s:5:"title";s:12:"查看更多";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:12:{s:11:"paddingleft";s:3:"130";s:10:"paddingtop";s:2:"10";s:3:"pdz";s:1:"5";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"fs";s:2:"16";s:10:"background";s:7:"#ffffff";s:11:"bordercolor";s:7:"#abd682";s:5:"btnbg";s:7:"#ffffff";s:5:"color";s:7:"#abd682";s:12:"borderradius";s:1:"5";}s:2:"id";s:5:"anniu";s:5:"index";s:3:"NaN";}}';
            $item1 = unserialize($item1);
            foreach($item1 as $k => &$v){
                if(isset($v['data'])){
                    foreach($v['data'] as $kk => &$vv){
                        if(isset($vv['imgurl']) && strpos($vv['imgurl'],'https://four.nttrip.cn') !== false){
                            $vv['imgurl'] = ROOT_HOST.explode('https://four.nttrip.cn', $vv['imgurl'])[1];
                        }
                    }
                }
            }
            $page1['items'] = serialize($item1);
            $page1['page'] = 'a:7:{s:10:"background";s:7:"#f1f1f1";s:13:"topbackground";s:7:"#5bdd85";s:8:"topcolor";s:1:"2";s:9:"styledata";s:1:"0";s:5:"title";s:6:"首页";s:4:"name";s:6:"首页";s:10:"visitlevel";a:2:{s:6:"member";s:0:"";s:10:"commission";s:0:"";}}';
            $page1['tpl_name'] = "首页";

            $page1_id = Db::name('wd_xcx_diypage')->insertGetId($page1);
            $pageids = $page1_id;

            $is = Db::name('wd_xcx_diypageset')->where('uniacid',$uniacid)->find();
            if(empty($is)){
                $page_set = [
                    'uniacid' => $uniacid,
                    'kp' => ROOT_HOST."/diypage/resource/images/diypage/default/default_start.jpg",
                    'kp_is' => 2,
                    'kp_m' => 2,
                    'tc' => ROOT_HOST."/diypage/resource/images/diypage/default/tcgg.jpg",
                    'tc_is' => 2,
                    'foot_is' => 2
                ];
                Db::name('wd_xcx_diypageset')->insert($page_set);
            }else{
                Db::name('wd_xcx_diypageset')->where('uniacid',$uniacid)->update(array('foot_is' => 2));
            }
            $data = [
                'uniacid' => $uniacid,
                'pageid' => $pageids,
                'template_name' => '生活服务类模板03',
                'thumb' => "/diypage/template_img/template_life03/cover.png",
                'status' => '1',
                'create_time' => time()
            ];
            $tplid = Db::name('wd_xcx_diypagetpl')->insertGetId($data);
            if($tplid){
                return $tplid;
            }
        }elseif($id == 'm_life04'){
            $page1 = [];
            $page1['uniacid'] = $uniacid;
            $page1['index'] = 1;
            $item1 = 'a:8:{s:14:"M1564191168500";a:4:{s:4:"icon";s:22:"iconfont2 icon-sousuo1";s:6:"params";a:7:{s:5:"value";s:0:"";s:9:"styledata";s:1:"1";s:6:"repeat";s:9:"no-repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"1";s:13:"backgroundimg";s:76:"https://four.nttrip.cn/diypage/template_img/template_life04/index/ssk_bg.png";}s:5:"style";a:12:{s:9:"textalign";s:6:"center";s:10:"background";s:7:"#ffffff";s:2:"bg";s:7:"#dfdad7";s:12:"borderradius";s:1:"5";s:6:"boxpdh";s:2:"22";s:6:"boxpdz";s:2:"30";s:7:"padding";s:1:"5";s:8:"fontsize";s:2:"13";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"color";s:7:"#ffffff";}s:2:"id";s:3:"ssk";}s:14:"M1564191118104";a:5:{s:4:"icon";s:28:"iconfont2 icon-tuoyuankaobei";s:6:"params";a:10:{s:5:"totle";s:1:"2";s:8:"navstyle";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:9:"navstyle2";s:1:"0";s:4:"imgh";s:3:"140";}s:5:"style";a:18:{s:8:"dotstyle";s:5:"round";s:8:"dotalign";s:4:"left";s:10:"paddingtop";s:1:"0";s:11:"paddingleft";s:1:"0";s:10:"background";s:7:"#ffffff";s:13:"backgroundall";s:7:"#ffffff";s:9:"leftright";s:1:"5";s:6:"bottom";s:2:"10";s:7:"opacity";s:1:"0";s:10:"text_color";s:4:"#fff";s:2:"bg";s:7:"#000000";s:9:"jsq_color";s:3:"red";s:3:"pdh";s:1:"0";s:3:"pdw";s:1:"0";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"speed";s:1:"5";}s:4:"data";a:2:{s:14:"C1564191118104";a:4:{s:6:"imgurl";s:76:"https://four.nttrip.cn/diypage/template_img/template_life04/index/banner.jpg";s:7:"linkurl";s:0:"";s:6:"single";s:1:"1";s:4:"text";s:12:"文字描述";}s:14:"C1564191118105";a:4:{s:6:"imgurl";s:46:"/diypage/resource/images/diypage/default/5.jpg";s:7:"linkurl";s:0:"";s:6:"single";s:1:"2";s:4:"text";s:12:"文字描述";}}s:2:"id";s:6:"banner";}s:14:"M1564192146596";a:5:{s:4:"icon";s:22:"iconfont2 icon-anniuzu";s:6:"params";a:8:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"picicon";s:1:"1";s:8:"textshow";s:1:"1";}s:5:"style";a:14:{s:8:"navstyle";s:0:"";s:10:"background";s:7:"#ffffff";s:6:"rownum";s:1:"5";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"8";s:7:"showdot";s:1:"1";s:7:"padding";s:2:"10";s:11:"paddingleft";s:1:"5";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:6:"iconfz";s:2:"14";s:9:"iconcolor";s:7:"#434343";s:8:"imgwidth";s:2:"52";}s:4:"data";a:10:{s:14:"C1564192146596";a:5:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_life04/index/memu1.png";s:7:"linkurl";s:0:"";s:4:"text";s:9:"找全职";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1564192146597";a:5:{s:6:"imgurl";s:74:"https://four.nttrip.cn/diypage/template_img/template_life04/index/memu.png";s:7:"linkurl";s:0:"";s:4:"text";s:9:"找兼职";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1564192146598";a:5:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_life04/index/memu3.png";s:7:"linkurl";s:0:"";s:4:"text";s:9:"二手车";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1564192146599";a:5:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_life04/index/memu4.png";s:7:"linkurl";s:0:"";s:4:"text";s:9:"二手货";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"M1564192786571";a:5:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_life04/index/memu5.png";s:7:"linkurl";s:0:"";s:4:"text";s:9:"租房子";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"M1564192794443";a:5:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_life04/index/memu6.png";s:7:"linkurl";s:0:"";s:4:"text";s:9:"买房子";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"M1564192802883";a:5:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_life04/index/memu7.png";s:7:"linkurl";s:0:"";s:4:"text";s:6:"交友";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"M1564192818875";a:5:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_life04/index/memu8.png";s:7:"linkurl";s:0:"";s:4:"text";s:9:"找保姆";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"M1564192827036";a:5:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_life04/index/memu9.png";s:7:"linkurl";s:0:"";s:4:"text";s:6:"宠物";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"M1564192834082";a:5:{s:6:"imgurl";s:76:"https://four.nttrip.cn/diypage/template_img/template_life04/index/memu10.png";s:7:"linkurl";s:0:"";s:4:"text";s:6:"更多";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}}s:2:"id";s:4:"menu";}s:14:"M1564192941859";a:4:{s:4:"icon";s:32:"iconfont2 icon-liebiao_biaotilan";s:6:"params";a:9:{s:5:"title";s:12:"精选服务";s:6:"title2";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:5:"style";s:1:"1";}s:5:"style";a:11:{s:10:"background";s:7:"#ffffff";s:6:"colorz";s:7:"#000000";s:6:"colorf";s:7:"#838383";s:9:"linecolor";s:7:"#6abd78";s:9:"fontsizez";s:2:"18";s:9:"fontsizef";s:2:"12";s:10:"paddingtop";s:2:"15";s:11:"paddingleft";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:1:"0";}s:2:"id";s:6:"title2";}s:14:"M1564193119818";a:5:{s:4:"icon";s:21:"iconfont2 icon-tupian";s:6:"params";a:9:{s:3:"row";s:1:"3";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"2";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:8:{s:10:"paddingtop";s:1:"5";s:11:"paddingleft";s:1:"8";s:7:"showdot";s:1:"0";s:7:"showbtn";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:1:"0";s:10:"background";s:7:"#ffffff";}s:4:"data";a:3:{s:14:"C1564193119819";a:2:{s:6:"imgurl";s:81:"https://four.nttrip.cn/diypage/template_img/template_life04/index/picturew1_1.png";s:7:"linkurl";s:0:"";}s:14:"C1564193119820";a:2:{s:6:"imgurl";s:81:"https://four.nttrip.cn/diypage/template_img/template_life04/index/picturew1_2.png";s:7:"linkurl";s:0:"";}s:14:"C1564193119821";a:2:{s:6:"imgurl";s:81:"https://four.nttrip.cn/diypage/template_img/template_life04/index/picturew1_3.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:8:"picturew";}s:14:"M1564193431682";a:5:{s:4:"icon";s:19:"iconfont2 icon-zutu";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:6:{s:10:"paddingtop";s:2:"20";s:11:"paddingleft";s:2:"15";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"background";s:7:"#ffffff";}s:4:"data";a:1:{s:14:"C1564193431682";a:2:{s:6:"imgurl";s:77:"https://four.nttrip.cn/diypage/template_img/template_life04/index/picture.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:7:"picture";}s:14:"M1564193244857";a:5:{s:4:"icon";s:32:"iconfont2 icon-liebiao_biaotilan";s:6:"params";a:9:{s:5:"title";s:12:"甄选推荐";s:6:"title2";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:5:"style";s:1:"1";}s:5:"style";a:11:{s:10:"background";s:7:"#ffffff";s:6:"colorz";s:7:"#000000";s:6:"colorf";s:7:"#838383";s:9:"linecolor";s:7:"#6abd78";s:9:"fontsizez";s:2:"18";s:9:"fontsizef";s:2:"12";s:10:"paddingtop";s:2:"10";s:11:"paddingleft";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:1:"0";}s:2:"id";s:6:"title2";s:5:"index";s:3:"NaN";}s:14:"M1564193539458";a:6:{s:4:"icon";s:21:"iconfont2 icon-tupian";s:6:"params";a:9:{s:3:"row";s:1:"3";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"2";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:8:{s:10:"paddingtop";s:1:"5";s:11:"paddingleft";s:1:"8";s:7:"showdot";s:1:"0";s:7:"showbtn";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:1:"0";s:10:"background";s:7:"#ffffff";}s:4:"data";a:3:{s:14:"C1564193539459";a:2:{s:6:"imgurl";s:81:"https://four.nttrip.cn/diypage/template_img/template_life04/index/picturew2_1.png";s:7:"linkurl";s:0:"";}s:14:"C1564193539460";a:2:{s:6:"imgurl";s:81:"https://four.nttrip.cn/diypage/template_img/template_life04/index/picturew2_2.png";s:7:"linkurl";s:0:"";}s:14:"C1564193539461";a:2:{s:6:"imgurl";s:81:"https://four.nttrip.cn/diypage/template_img/template_life04/index/picturew2_3.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:8:"picturew";s:5:"index";s:3:"NaN";}}';
            $item1 = unserialize($item1);
            foreach($item1 as $k => &$v){
                if(isset($v['data'])){
                    foreach($v['data'] as $kk => &$vv){
                        if(isset($vv['imgurl']) && strpos($vv['imgurl'],'https://four.nttrip.cn') !== false){
                            $vv['imgurl'] = ROOT_HOST.explode('https://four.nttrip.cn', $vv['imgurl'])[1];
                        }
                        if(isset($vv['thumb']) && strpos($vv['thumb'],'/diypage/resource/images/diypage/default/5.jpg') !== false){
                            $vv['thumb'] = ROOT_HOST."/diypage/resource/images/diypage/default/5.jpg";
                        }
                    }
                }
            }
            $page1['items'] = serialize($item1);
            $page1['page'] = 'a:7:{s:10:"background";s:7:"#f1f1f1";s:13:"topbackground";s:7:"#ffffff";s:8:"topcolor";s:1:"1";s:9:"styledata";s:1:"0";s:5:"title";s:6:"首页";s:4:"name";s:6:"首页";s:10:"visitlevel";a:2:{s:6:"member";s:0:"";s:10:"commission";s:0:"";}}';
            $page1['tpl_name'] = "首页";

            $page1_id = Db::name('wd_xcx_diypage')->insertGetId($page1);
            $pageids = $page1_id;

            $is = Db::name('wd_xcx_diypageset')->where('uniacid',$uniacid)->find();
            if(empty($is)){
                $page_set = [
                    'uniacid' => $uniacid,
                    'kp' => ROOT_HOST."/diypage/resource/images/diypage/default/default_start.jpg",
                    'kp_is' => 2,
                    'kp_m' => 2,
                    'tc' => ROOT_HOST."/diypage/resource/images/diypage/default/tcgg.jpg",
                    'tc_is' => 2,
                    'foot_is' => 2
                ];
                Db::name('wd_xcx_diypageset')->insert($page_set);
            }else{
                Db::name('wd_xcx_diypageset')->where('uniacid',$uniacid)->update(array('foot_is' => 2));
            }
            $data = [
                'uniacid' => $uniacid,
                'pageid' => $pageids,
                'template_name' => '生活服务类模板04',
                'thumb' => "/diypage/template_img/template_life04/cover.png",
                'status' => '1',
                'create_time' => time()
            ];
            $tplid = Db::name('wd_xcx_diypagetpl')->insertGetId($data);
            if($tplid){
                return $tplid;
            }
        }elseif($id == 'm_life05'){
            $page1 = [];
            $page1['uniacid'] = $uniacid;
            $page1['index'] = 1;
            $item1 = 'a:8:{s:14:"M1564213934453";a:4:{s:4:"icon";s:22:"iconfont2 icon-sousuo1";s:6:"params";a:7:{s:5:"value";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:12:{s:9:"textalign";s:4:"left";s:10:"background";s:7:"#ff5c40";s:2:"bg";s:7:"#ffbeb3";s:12:"borderradius";s:1:"5";s:6:"boxpdh";s:2:"10";s:6:"boxpdz";s:2:"20";s:7:"padding";s:1:"4";s:8:"fontsize";s:2:"13";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"color";s:7:"#ffffff";}s:2:"id";s:3:"ssk";}s:14:"M1564213938286";a:5:{s:4:"icon";s:22:"iconfont2 icon-anniuzu";s:6:"params";a:8:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"picicon";s:1:"1";s:8:"textshow";s:1:"1";}s:5:"style";a:14:{s:8:"navstyle";s:0:"";s:10:"background";s:7:"#ffffff";s:6:"rownum";s:1:"4";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"8";s:7:"showdot";s:1:"1";s:7:"padding";s:1:"5";s:11:"paddingleft";s:2:"10";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:6:"iconfz";s:2:"14";s:9:"iconcolor";s:7:"#434343";s:8:"imgwidth";s:2:"51";}s:4:"data";a:8:{s:14:"C1564213938286";a:5:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_life05/index/menu1.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"二手物品";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1564213938287";a:5:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_life05/index/menu2.png";s:7:"linkurl";s:0:"";s:4:"text";s:9:"二手车";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1564213938288";a:5:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_life05/index/menu3.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"房屋出租";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"C1564213938289";a:5:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_life05/index/menu4.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"房屋出售";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"M1564214683416";a:5:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_life05/index/menu5.png";s:7:"linkurl";s:0:"";s:4:"text";s:12:"本地服务";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"M1564214684847";a:5:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_life05/index/menu6.png";s:7:"linkurl";s:0:"";s:4:"text";s:6:"招聘";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"M1564214685856";a:5:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_life05/index/menu7.png";s:7:"linkurl";s:0:"";s:4:"text";s:6:"宠物";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}s:14:"M1564214687047";a:5:{s:6:"imgurl";s:75:"https://four.nttrip.cn/diypage/template_img/template_life05/index/menu8.png";s:7:"linkurl";s:0:"";s:4:"text";s:6:"交友";s:5:"color";s:7:"#666666";s:4:"icon";s:14:"icon-x-shouye2";}}s:2:"id";s:4:"menu";}s:14:"M1564214806973";a:5:{s:4:"icon";s:19:"iconfont2 icon-zutu";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:6:{s:10:"paddingtop";s:1:"0";s:11:"paddingleft";s:1:"0";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"background";s:7:"#ffffff";}s:4:"data";a:1:{s:14:"C1564214806973";a:2:{s:6:"imgurl";s:78:"https://four.nttrip.cn/diypage/template_img/template_life05/index/picture1.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:7:"picture";}s:14:"M1564969015710";a:5:{s:4:"icon";s:22:"iconfont2 icon-gonggao";s:6:"params";a:12:{s:7:"iconurl";s:15:"icon-x-gonggao3";s:10:"noticedata";s:1:"0";s:5:"speed";s:1:"4";s:9:"noticenum";s:1:"5";s:8:"navstyle";s:1:"0";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:8:"sourceid";s:0:"";}s:5:"style";a:9:{s:10:"background";s:7:"#ffffff";s:9:"iconcolor";s:7:"#fd5454";s:5:"color";s:7:"#666666";s:11:"bordercolor";s:7:"#e2e2e2";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:10:"paddingtop";s:2:"14";s:11:"paddingleft";s:2:"10";}s:4:"data";a:2:{s:14:"C1564969015710";a:2:{s:5:"title";s:42:"这里是第一条自定义公告的标题";s:7:"linkurl";s:0:"";}s:14:"C1564969015711";a:2:{s:5:"title";s:42:"这里是第二条自定义公告的标题";s:7:"linkurl";s:0:"";}}s:2:"id";s:6:"notice";}s:14:"M1564215425214";a:5:{s:4:"icon";s:21:"iconfont2 icon-tupian";s:6:"params";a:9:{s:3:"row";s:1:"2";s:8:"showtype";s:1:"0";s:7:"pagenum";s:1:"2";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:8:{s:10:"paddingtop";s:1:"5";s:11:"paddingleft";s:1:"5";s:7:"showdot";s:1:"0";s:7:"showbtn";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:1:"0";s:10:"background";s:7:"#f1f1f1";}s:4:"data";a:6:{s:14:"C1564215425214";a:2:{s:6:"imgurl";s:79:"https://four.nttrip.cn/diypage/template_img/template_life05/index/picturew1.png";s:7:"linkurl";s:0:"";}s:14:"C1564215425215";a:2:{s:6:"imgurl";s:79:"https://four.nttrip.cn/diypage/template_img/template_life05/index/picturew2.png";s:7:"linkurl";s:0:"";}s:14:"C1564215425216";a:2:{s:6:"imgurl";s:79:"https://four.nttrip.cn/diypage/template_img/template_life05/index/picturew3.png";s:7:"linkurl";s:0:"";}s:14:"C1564215425217";a:2:{s:6:"imgurl";s:79:"https://four.nttrip.cn/diypage/template_img/template_life05/index/picturew4.png";s:7:"linkurl";s:0:"";}s:14:"M1564215491896";a:2:{s:6:"imgurl";s:79:"https://four.nttrip.cn/diypage/template_img/template_life05/index/picturew5.png";s:7:"linkurl";s:0:"";}s:14:"M1564215493528";a:2:{s:6:"imgurl";s:79:"https://four.nttrip.cn/diypage/template_img/template_life05/index/picturew6.png";s:7:"linkurl";s:0:"";}}s:2:"id";s:8:"picturew";}s:14:"M1564215652357";a:5:{s:4:"icon";s:24:"iconfont2 icon-xiankuang";s:6:"params";a:6:{s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";}s:5:"style";a:5:{s:6:"height";s:2:"20";s:10:"background";s:7:"#ffffff";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";}s:2:"id";s:5:"blank";s:5:"index";s:3:"NaN";}s:14:"M1564215556597";a:4:{s:4:"icon";s:32:"iconfont2 icon-liebiao_biaotilan";s:6:"params";a:9:{s:5:"title";s:12:"本地新闻";s:6:"title2";s:0:"";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:5:"style";s:1:"1";}s:5:"style";a:11:{s:10:"background";s:7:"#ffffff";s:6:"colorz";s:7:"#000000";s:6:"colorf";s:7:"#838383";s:9:"linecolor";s:7:"#ffffff";s:9:"fontsizez";s:2:"18";s:9:"fontsizef";s:2:"12";s:10:"paddingtop";s:1:"5";s:11:"paddingleft";s:2:"10";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:2:"mt";s:1:"0";}s:2:"id";s:6:"title2";}s:14:"M1564215665301";a:5:{s:4:"icon";s:23:"iconfont2 icon-wenzhang";s:6:"params";a:19:{s:9:"showstyle";s:4:"row1";s:7:"newsnum";s:1:"3";s:8:"newsdata";s:1:"0";s:5:"title";s:21:"请选择文章分类";s:7:"titleid";s:1:"0";s:8:"navstyle";s:1:"1";s:5:"show1";s:1:"1";s:5:"show2";s:1:"1";s:5:"show3";s:1:"1";s:5:"show4";s:1:"1";s:9:"styledata";s:1:"0";s:6:"repeat";s:6:"repeat";s:9:"positionx";s:4:"left";s:9:"positiony";s:3:"top";s:4:"size";s:1:"0";s:13:"backgroundimg";s:0:"";s:7:"con_key";s:1:"1";s:8:"con_type";s:1:"1";s:8:"sourceid";s:0:"";}s:5:"style";a:11:{s:10:"background";s:4:"#fff";s:10:"paddingtop";s:1:"5";s:11:"paddingleft";s:2:"10";s:12:"marginbottom";s:2:"10";s:2:"mt";s:1:"0";s:5:"sizew";s:2:"20";s:5:"sizeh";s:2:"20";s:5:"color";s:7:"#434343";s:6:"radius";s:1:"0";s:4:"pich";s:1:"1";s:8:"showtype";s:1:"0";}s:4:"data";a:3:{s:14:"C1564215665301";a:8:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/3.jpg";s:2:"id";s:0:"";s:5:"title";s:15:"这里是标题";s:4:"time";s:16:"2017年10月1日";s:5:"intro";s:21:"简介1简介1简介1";s:3:"ydl";s:2:"10";s:3:"dzl";s:2:"10";s:3:"pll";s:2:"10";}s:14:"C1564215665302";a:8:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/3.jpg";s:2:"id";s:0:"";s:5:"title";s:15:"这里是标题";s:4:"time";s:16:"2017年10月1日";s:5:"intro";s:15:"这里是简介";s:3:"ydl";s:2:"10";s:3:"dzl";s:2:"10";s:3:"pll";s:2:"10";}s:14:"C1564215665303";a:8:{s:5:"thumb";s:46:"/diypage/resource/images/diypage/default/3.jpg";s:2:"id";s:0:"";s:5:"title";s:15:"这里是标题";s:4:"time";s:16:"2017年10月1日";s:5:"intro";s:15:"这里是简介";s:3:"ydl";s:2:"10";s:3:"dzl";s:2:"10";s:3:"pll";s:2:"10";}}s:2:"id";s:8:"listdesc";}}';
            $item1 = unserialize($item1);
            foreach($item1 as $k => &$v){
                if(isset($v['data'])){
                    foreach($v['data'] as $kk => &$vv){
                        if(isset($vv['imgurl']) && strpos($vv['imgurl'],'https://four.nttrip.cn') !== false){
                            $vv['imgurl'] = ROOT_HOST.explode('https://four.nttrip.cn', $vv['imgurl'])[1];
                        }
                        if(isset($vv['thumb']) && strpos($vv['thumb'],'/diypage/resource/images/diypage/default/3.jpg') !== false){
                            $vv['thumb'] = ROOT_HOST."/diypage/resource/images/diypage/default/3.jpg";
                        }
                    }
                }
            }
            $page1['items'] = serialize($item1);
            $page1['page'] = 'a:7:{s:10:"background";s:7:"#f1f1f1";s:13:"topbackground";s:7:"#ff5c40";s:8:"topcolor";s:1:"2";s:9:"styledata";s:1:"0";s:5:"title";s:6:"首页";s:4:"name";s:6:"首页";s:10:"visitlevel";a:2:{s:6:"member";s:0:"";s:10:"commission";s:0:"";}}';
            $page1['tpl_name'] = "首页";

            $page1_id = Db::name('wd_xcx_diypage')->insertGetId($page1);
            $pageids = $page1_id;

            $is = Db::name('wd_xcx_diypageset')->where('uniacid',$uniacid)->find();
            if(empty($is)){
                $page_set = [
                    'uniacid' => $uniacid,
                    'kp' => ROOT_HOST."/diypage/resource/images/diypage/default/default_start.jpg",
                    'kp_is' => 2,
                    'kp_m' => 2,
                    'tc' => ROOT_HOST."/diypage/resource/images/diypage/default/tcgg.jpg",
                    'tc_is' => 2,
                    'foot_is' => 2
                ];
                Db::name('wd_xcx_diypageset')->insert($page_set);
            }else{
                Db::name('wd_xcx_diypageset')->where('uniacid',$uniacid)->update(array('foot_is' => 2));
            }
            $data = [
                'uniacid' => $uniacid,
                'pageid' => $pageids,
                'template_name' => '生活服务类模板05',
                'thumb' => "/diypage/template_img/template_life05/cover.png",
                'status' => '1',
                'create_time' => time()
            ];
            $tplid = Db::name('wd_xcx_diypagetpl')->insertGetId($data);
            if($tplid){
                return $tplid;
            }
        }
        elseif ($id == 'sys_blank') {//判断系统模板是否为空白模板
            //是
            //为空白模板添加页面
            $pageid = Db::name("wd_xcx_diypage")->insertGetId(array(
                'uniacid' => $uniacid,
                'index' => 1,
                'page' => 'a:7:{s:10:"background";s:7:"#f1f1f1";s:13:"topbackground";s:7:"#ffffff";s:8:"topcolor";s:1:"1";s:9:"styledata";s:1:"0";s:5:"title";s:21:"小程序页面标题";s:4:"name";s:18:"后台页面名称";s:10:"visitlevel";a:2:{s:6:"member";s:0:"";s:10:"commission";s:0:"";}}',
                'items' => '',
                'tpl_name' => '后台页面名称',
            ));

            $tplid = Db::name("wd_xcx_diypagetpl")->insertGetId(array(
                'uniacid' => $uniacid,
                'pageid' => $pageid,
                'template_name' => '空白模板',
                'thumb' => '/diypage/img/blank.jpg',
                'status' => '1',
                'create_time' => time(),
            ));

            if($tplid){
                return $tplid;
            }
        } elseif($id > 0) {
            $tplinfo = Db::name("wd_xcx_diypagetpl_sys")->where('id',$id)->find();
            $pageid = explode(",",$tplinfo['pageid']);
            $pageids = '';
            foreach ($pageid as $key => $value) {
                $info = Db::name("wd_xcx_diypage_sys")->where("id",$value)->find();
                $insert_id = Db::name('wd_xcx_diypage')->insertGetId(array(
                    'uniacid' => $uniacid,
                    'index' => $info['index'],
                    'page' => $info['page'],
                    'items' => $info['items'],
                    'tpl_name' => $info['tpl_name'],
                ));
                $pageids = $pageids .','. $insert_id;
            }
            $pageids = substr($pageids,1);
            $data = [
                'uniacid' => $uniacid,
                'pageid' => $pageids,
                'template_name' => $tplinfo['template_name'],
                'thumb' => $tplinfo['thumb'],
                'status' => '1',
                'create_time' => time()
            ];
            $tplid = Db::name("wd_xcx_diypagetpl")->insertGetId($data);
            if($tplid){
                return $tplid;
            }
        }
    }
    //删除模板
    public function del_moban(){
        $appletid = input("appletid");
        $id = input("id");
        $sql = Db::name("wd_xcx_diypagetpl")->where("uniacid",$appletid)->where("id",$id)->field('pageid')->find();
        $tplpages = $sql['pageid'];
        if($tplpages){
            $tplpagearr = explode(',',$tplpages);
            foreach ($tplpagearr as $key => $value) {
                Db::name('wd_xcx_diypage')->where("uniacid", $appletid)->where("id", $value)->delete();
            }
        }
        $res = Db::name("wd_xcx_diypagetpl")->where("uniacid",$appletid)->where("id",$id)->delete();
        if($res){
            return 1;
        }else{
            return 2;
        }
    }

    private function ccDD(){
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

    //删除系统模板
    public function del_sys_moban(){
        $appletid = input("appletid");
        $id = input("id");
        $sql = Db::name("wd_xcx_diypagetpl_sys")->where("uniacid",$appletid)->where("id",$id)->field('pageid')->find();
        $tplpages = $sql['pageid'];
        if($tplpages){
            $tplpagearr = explode(',',$tplpages);
            foreach ($tplpagearr as $key => &$value) {
                Db::name('wd_xcx_diypage_sys')->where("uniacid", $appletid)->where("id", $value)->delete();
            }
        }
        $res = Db::name("wd_xcx_diypagetpl_sys")->where("id",$id)->delete();
        if($res){
            return 1;
        }else{
            return 2;
        }
    }

    public function webmoban(){
        $appletid = input("appletid");

        $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();

        if(!$res){

            $this->error("找不到对应的小程序！");
        }

        $this->assign('applet',$res);

        return $this->fetch("webmoban");
    }
}