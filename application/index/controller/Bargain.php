<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;
header("Content-type: text/html; charset=utf-8");
class Bargain extends Base
{
    public function set(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);

                $bargain = Db::name('wd_xcx_bargain_set')->where('uniacid', $appletid)->find();
                $allimg = [];
                $newsid = 0;
                if($bargain > 0){
                    $allimg = Db::name('wd_xcx_products_url')->where("randid",$bargain['onlyid'])->select();
                    foreach ($allimg as $key => &$value) {
                        $value['url'] = remote($appletid,$value['url'],1);
                    }
                    $newsid = $bargain['id'];
                }
                $this->assign('allimg',$allimg);
                $this->assign('newsid',$newsid);
                $this->assign('bargain', $bargain);
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
            return $this->fetch('set');
        }else{
            $this->redirect('Login/index');
        }
    }
    public function saveSet()
    {
        $appletid = input("appletid");
        $data = [
            'uniacid' => $appletid,
            'rules' => htmlspecialchars_decode(input('rules')),
            'shareTitle' => input('shareTitle'),
            'emailStatus' => input('emailStatus') ? input('emailStatus') : 2
        ];
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
        $imgs = Db::name('wd_xcx_products_url')->where('randid',$onlyid)->select();
        $imgtext = array();
        foreach($imgs as $k => $v){
            array_push($imgtext,$v['url']);
        }
        $data['slides'] = $imgtext ? serialize($imgtext) : '';
        $is = Db::name('wd_xcx_bargain_set')->where('uniacid', $appletid)->find();
        if($is){
            $res = Db::name('wd_xcx_bargain_set')->where('uniacid', $appletid)->update($data);
        }else{
            $res = Db::name('wd_xcx_bargain_set')->insert($data);
        }
        if($res){
           $this->success('基础设置更新成功！',Url('Bargain/set').'?appletid='.$data['uniacid']);
        }else{
          $this->error('基础设置更新失败，没有修改项！');
          exit;
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
                $cates = Db::name('wd_xcx_bargain_cate')->where('uniacid', $appletid)->order('id desc')->paginate(10, false, ['query' => ['appletid' => $appletid]]);
                $counts = Db::name('wd_xcx_bargain_cate')->where("uniacid",$appletid)->count();
                $this->assign('counts',$counts);
                $this->assign('cates',$cates);
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
    public function addcate(){
        $appletid = input("appletid");
        $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
        if(!$res){
            $this->error("找不到对应的小程序！");
        }
        $this->assign('applet',$res);

        $cateid = intval(input("cateid"));
        $cate = [];
        if($cateid > 0){
            $cate = Db::name('wd_xcx_bargain_cate')->where('uniacid', $appletid)->where('id', $cateid)->find();
        }
        $this->assign('cate',$cate);
        $this->assign('cateid',$cateid);
        return $this->fetch('addcate');
    }
    public function msg_bargain(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);

                $base = Db::name('wd_xcx_message')->where("uniacid",$appletid)->where('flag',13)->find();
                $this->assign('base',$base);

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

            return $this->fetch('msg_bargain');
        }else{
            $this->redirect('Login/index');
        }
        
    }


    public function save_bargain(){

        $data = array();
        $uniacid = input("appletid");
        //消息模板id
        $pay_id = input("pay_id");
        $data['mid'] = trim($pay_id);

        $url = input("url");
        $data['url'] = trim($url);


        $count = Db::name('wd_xcx_message')->where("uniacid",$uniacid)->where('flag',13)->count();

        if($count>0){
            $res = Db::name('wd_xcx_message')->where("uniacid",$uniacid)->where('flag',13)->update($data);
        }else{
            $data['flag'] = 13;
            $data['uniacid'] = $uniacid;
            $res = Db::name('wd_xcx_message')->insert($data);
        }

        if($res){
          $this->success('砍价订单通知更新成功！');
        }else{
          $this->error('砍价订单通知更新失败，没有修改项！');
          exit;
        }
    }
    public function savecate(){
        $appletid = input("appletid");
        $cateid = intval(input("cateid"));
        $data = [];
        $data['uniacid'] = $appletid; 
        $data['title'] = input('title'); 
        if($cateid > 0){
            $res = Db::name('wd_xcx_bargain_cate')->where('id', $cateid)->update($data);
        }else{
            $res = Db::name('wd_xcx_bargain_cate')->insert($data);
        }
        if($res){
           $this->success('栏目添加/更新成功！',Url('Bargain/cate').'?appletid='.$data['uniacid']);
        }else{
          $this->error('栏目更新失败，没有修改项！');
          exit;
        }
    }

    private function ww(){
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

    public function delcate(){
        $appletid = input("appletid");
        $cateid = intval(input("cateid"));

        $count = Db::name('wd_xcx_bargain_pro')->where('cateId', $cateid)->count();
        if($count){
            $this->error('栏目下存在商品，删除失败');
        }
        $is = Db::name('wd_xcx_bargain_cate')->where('id', $cateid)->find();
        if($is){
            $res = Db::name('wd_xcx_bargain_cate')->where('id', $cateid)->delete();
            if($res){
               $this->success('栏目删除成功！',Url('Bargain/cate').'?appletid='.$appletid);
            }else{
              $this->error('栏目删除失败，栏目不存在或已删除！');
              exit;
            }
        }else{
            $this->error('栏目删除失败，栏目不存在或已删除！');
        }
    }
    public function prolist(){
        $appletid = input("appletid");
        $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
        if(!$res){
            $this->error("找不到对应的小程序！");
        }
        $this->assign('applet',$res);

        $cid = input("cid") ? input("cid") : 0;
        $title = input("key");

        $where = [];
        if($cid > 0){
            $where['cateId'] = $cid;
        }

        if($title){
            $where['title'] = ['like',"%".$title."%"];
        }
        $pro = Db::name('wd_xcx_bargain_pro')->where('uniacid', $appletid)->where($where)->order('id desc')->paginate(10, false, ['query' => ['appletid' => $appletid]]);
        $prolist = $pro->all();
        foreach ($prolist as $key => &$value) {
            $value['thumb'] = remote($appletid,$value['thumb'],1);
            $value['catename'] = Db::name('wd_xcx_bargain_cate')->where('id',$value['cateId'])->value('title');
        }
        $counts = Db::name('wd_xcx_bargain_pro')->where('uniacid', $appletid)->where($where)->count();

        $cate = Db::name('wd_xcx_bargain_cate')->where('uniacid',$appletid)->select();
        $this->assign('key',$title);
        $this->assign('cid',$cid);
        $this->assign('cate',$cate);

        $this->assign('counts',$counts);
        $this->assign('prolist',$prolist);
        $this->assign('pro',$pro);
        return $this->fetch('prolist');
    }
    public function addpro(){
        $appletid = input("appletid");
        $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
        if(!$res){
            $this->error("找不到对应的小程序！");
        }
        $this->assign('applet',$res);

        $products = [];
        $id = intval(input('pid'));
        if($id > 0){
            $products = Db::name('wd_xcx_bargain_pro')->where('id', $id)->find(); 
            $products['thumb'] = remote($appletid,$products['thumb'],1);

            $products['vipConfig'] = unserialize($products['vipConfig']);
            $products['activeRule'] = unserialize($products['activeRule']);
        }
        $yunfei_gg_list = Db::name('wd_xcx_freight')->where('uniacid', $appletid)->where('is_delete', 0)->select();
        $cateAll = Db::name('wd_xcx_bargain_cate')->where('uniacid', $appletid)->select();
        $forms = Db::name('wd_xcx_formlist')->where("uniacid",$appletid) ->order('id desc')->select();
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

        $allimg = [];
        $onlyid = 0;
        if($id > 0){
            $allimg = Db::name('wd_xcx_products_url')->where("randid",$products['onlyid'])->select();
            foreach ($allimg as $key => &$value) {
                $value['url'] = remote($appletid,$value['url'],1);
            }
            $onlyid = $id;
        }
        $stores=Db::name("wd_xcx_store")->where("uniacid",$appletid)->select();
        $this->assign('stores',$stores);
        $this->assign('yunfei_gg_list',$yunfei_gg_list);
        $this->assign('cateAll',$cateAll);
        $this->assign('products',$products);
        $this->assign('id',$id);
        $this->assign('onlyid',$onlyid);
        $this->assign('allimg',$allimg);
        $this->assign('grade_arr',$grade_arr);
        $this->assign('forms',$forms);
        return $this->fetch('addpro');
    }
    public function savepro(){
        $appletid = input("appletid");
        $id = input("pid");

        $num = intval(input('num'));
        $cateId = intval(input('cid'));
        $title = input('title');
        $status = input('status') ? input('status') : 1;
        $hot = input('hot') ? input('hot') : 2;
        $kuaidi = input('kuaidi') ? input('kuaidi') : 3;
        $freightId = intval(input('freightId'));
        $form_id = input('form_id');
        $price = input('price');
        $kc = intval(input('kc'));
        $virtualSaleVolume = input('virtualSaleVolume');
        $thumb = input("commonuploadpic1");
        if($thumb){
            $thumb = remote($appletid,$thumb,2);
        }
        //分享图
        $shareThumb = input("commonuploadpic2");
        if($shareThumb){
            $shareThumb = remote($appletid,$shareThumb,2);
        }
        $descs = input('descs');
        $labels = input('labels');
        $texts = htmlspecialchars_decode(input('texts'));
        $set1 = intval(input('set1'));
        $set2 = intval(input('set2'));
        $set3 = intval(input('set3'));
        $vipConfig = [
            'set1' => $set1,
            'set2' => $set2,
            'set3' => $set3
        ];
        $vipConfig = serialize($vipConfig);
        $miniPrice = input('miniPrice');
        $activeBinTime = strtotime(input('activeBinTime'));
        $activeEndTime = strtotime(input('activeEndTime'));
        $activeHours = input('activeHours');
        $aPersons = input('aPersons');
        $aBinPersons = input('aBinPersons');
        $aBargainOne = input('aBargainOne');
        $aBargainTwo = input('aBargainTwo');
        $aBargainThree = input('aBargainThree');
        $aBargainFour = input('aBargainFour');
        $activeRule = [
            'aPersons' => $aPersons,
            'aBinPersons' => $aBinPersons,
            'aBargainOne' => $aBargainOne,
            'aBargainTwo' => $aBargainTwo,
            'aBargainThree' => $aBargainThree,
            'aBargainFour' => $aBargainFour
        ];
        $stores = $kuaidi != 1 ? input("stores") : '';
        $data = [
            'uniacid' => $appletid,
            'num' => $num,
            'cateId' => $cateId,
            'title' => $title,
            'status' => $status,
            'hot' => $hot,
            'kuaidi' => $kuaidi,
            'freightId' => $freightId,
            'form_id' => $form_id,
            'price' => $price,
            'kc' => $kc,
            'virtualSaleVolume' => $virtualSaleVolume,
            'descs' => $descs,
            'labels' => $labels,
            'texts' => $texts,
            'vipConfig' => $vipConfig,
            'miniPrice' => $miniPrice,
            'activeBinTime' => $activeBinTime,
            'activeEndTime' => $activeEndTime,
            'activeHours' => $activeHours,
            'activeRule' => serialize($activeRule),
            'video' => input('video'),
            'stores' => $stores ? $stores : '',
        ];

        if($thumb){
            $data['thumb'] = $thumb;
        }
        if($shareThumb){
            $data['shareThumb'] = $shareThumb;
        }
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
        }
        $imgs = Db::name('wd_xcx_products_url')->where('randid',$onlyid)->select();
        $imgtext = array();
        foreach($imgs as $k => $v){
            array_push($imgtext,$v['url']);
        }
        $data['onlyid'] = $onlyid;
        $data['slides'] = $imgtext ? serialize($imgtext) : '';
        if ($id > 0) {
            $res = Db::name('wd_xcx_bargain_pro')->where('id', $id)->update($data);
        } else {
            $res = Db::name('wd_xcx_bargain_pro')->insertGetid($data);
        }
        if($res){
           $this->success('产品添加/更新成功！',Url('Bargain/prolist').'?appletid='.$data['uniacid']);
        }else{
          $this->error('产品更新失败，没有修改项！');
          exit;
        }
    }
    public function delpro(){
        $appletid = input('appletid');
        $pid = input('pid');
        $is = Db::name('wd_xcx_bargain_pro')->where('id', $pid)->find();
        if($is){
            $res = Db::name('wd_xcx_bargain_pro')->where('id', $pid)->delete();
            if($res){
               $this->success('商品删除成功！',Url('Bargain/prolist').'?appletid='.$appletid);
            }else{
              $this->error('商品删除成失败，商品不存在或已删除！');
              exit;
            }
        }else{
            $this->error('商品删除成失败，商品不存在或已删除！');
        }
    }
    public function bargain(){
        $appletid = input("appletid");
        $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
        if(!$res){
            $this->error("找不到对应的小程序！");
        }
        $this->assign('applet',$res);

        $counts = Db::name('wd_xcx_bargain_bargain_order')->alias('a')->join('wd_xcx_bargain_pro b','a.proid = b.id')->where('a.uniacid', $appletid)->count();
        $list = Db::name('wd_xcx_bargain_bargain_order')->alias('a')->join('wd_xcx_bargain_pro b','a.proid = b.id')->where('a.uniacid', $appletid)->field('a.*,b.title,b.thumb,b.price,b.miniPrice')->order('a.id desc')->paginate(10, false, ['query' => ['appletid' => $appletid]]);
        $lists = $list->toArray()['data'];
        foreach ($lists as $key => $value) {
            $receive = Db::name('wd_xcx_bargain_receive')->alias('a')->join('wd_xcx_superuser b','a.suid = b.id')->where('a.uniacid', $appletid)->where('b.uniacid', $appletid)->where('bargain_id', $value['id'])->field('b.id')->order('a.id asc')->select();
            if(!$receive){
                $receive[0]['nickname'] = '用户已不存在';
                $receive[0]['avatar'] = '用户已不存在';
            }else{
                foreach ($receive as $ks => &$vs) {
                    $user = getNameAvatar($vs['id'], $appletid);
                    $vs['nickname'] = $user['nickname'];
                    $vs['avatar'] = $user['avatar'];
                }
            }
            $lists[$key]['receive'] = $receive;
            $lists[$key]['thumb'] = remote($appletid, $value['thumb'], 1);
            $lists[$key]['createtime'] = date("Y-m-d H:i:s", $value['createtime']);
            $lists[$key]['overtime'] = date("Y-m-d H:i:s", $value['overtime']);
        }
        $this->assign('counts',$counts);
        $this->assign('list',$list);
        $this->assign('lists',$lists);
        return $this->fetch('bargain');
    }
    public function orderlist(){
        $appletid = input("appletid");
        $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
        if(!$res){
            $this->error("找不到对应的小程序！");
        }
        $this->assign('applet',$res);

        // 处理30分钟未付款的订单
        $wforders = Db::name("wd_xcx_bargain_order")->where("uniacid", $appletid)->where("flag", 0)->where("overtime", "lt", time())->field('order_id, pid')->select();
        foreach($wforders as $rsi){
            $wf_pro = Db::name("wd_xcx_bargain_pro")->where("uniacid", $appletid)->where("id", $rsi['pid'])->field("kc, realSaleVolume")->find();
            $kc = $wf_pro['kc'] != -1 ? $wf_pro['kc'] + 1 : -1; 
            $realSaleVolume = $wf_pro['realSaleVolume'] - 1 < 0 ? 0 : $wf_pro['realSaleVolume'] - 1; 
            $wf_data = [
                        'kc' => $kc,
                        'realSaleVolume' => $realSaleVolume,
                        ];
            Db::name("wd_xcx_bargain_order")->where("uniacid", $appletid)->where("order_id", $rsi['order_id'])->update(['flag' => 3]);
            Db::name("wd_xcx_bargain_pro")->where("uniacid", $appletid)->where("id", $rsi['pid'])->update($wf_data);
        }

        $this->ww();

        $order_id = input('order_id');
        $op = input('op');
        if($op == "hx"){
            $id = input('id');
            $data['hxtime'] = time();
            $data['flag'] = 2;
            $data['hxinfo'] = 'a:1:{i:0;i:1;}';
            $res = Db::name("wd_xcx_bargain_order")->where("id", $id)->update($data);
            if($res){
                $info = Db::name("wd_xcx_bargain_order")->where("id", $id)->find();
                add_all_pay($appletid, round($info['wx_price'] + $info['yue_price'], 2), $info['suid']);
                check_vip_grade($appletid, $info['suid']);
                if($info['source'] == 1){
                    $openid = Db::name("wd_xcx_user")->where("suid", $info['suid'])->value('openid');
                    $jsons = [
                        'fprice' => $info['true_price']
                    ];
                    $jsons = serialize($jsons);
                    sendSubscribe($appletid, 2, $openid, $jsons);
                }
                $this->success("核销成功！");
            }else{
                $this->error("核销失败，请重新核销！");
            }
        }else if($op == "confirmtk" || $op == "quxiao"){
            $id = input('id');
            $now = time();
            $out_refund_no = date("Y",$now).date("m",$now).date("d",$now).date("H",$now).date("i",$now).date("s",$now).rand(1000,9999);
            if(input('qxbeizhu')){
                $data['qxMsg'] = input('qxbeizhu');
            }
            $data['th_orderid'] = $out_refund_no;
            $res = Db::name("wd_xcx_bargain_order")->where("id", $id)->update($data);
            $order = Db::name("wd_xcx_bargain_order")->where("id", $id)->find();
            if($order['wx_price'] > 0){
                $app = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if($order['paytype'] == 1){
                    if($order['source'] == 1){
                        $mchid = $app['mchid'];   //商户号
                        $apiKey = $app['signkey'];    //商户的秘钥
                        $appid = $app['appID'];                 //小程序的id
                        $SSLCERT_PATH = ROOT_PATH.'public/Cert/'.$appletid.'/apiclient_cert.pem';//证书路径
                        $SSLKEY_PATH =  ROOT_PATH.'public/Cert/'.$appletid.'/apiclient_key.pem';//证书路径
                    }elseif($order['source'] == 3){
                        $mchid = $app['wx_h5_mchid'];   //商户号
                        $apiKey = $app['wx_h5_signkey'];    //商户的秘钥
                        $appid = $app['wx_h5_appid'];                 //小程序的id
                        $SSLCERT_PATH = ROOT_PATH.'public/Cert/'.$appletid.'/h5_apiclient_cert.pem';//证书路径
                        $SSLKEY_PATH =  ROOT_PATH.'public/Cert/'.$appletid.'/h5_apiclient_key.pem';//证书路径
                    }elseif($order['source'] == 5){
                        $mchid = $app['bdance_h5_mchid'];   //商户号
                        $apiKey = $app['bdance_h5_signkey'];    //商户的秘钥
                        $appid = $app['bdance_h5_appid'];                 //小程序的id
                        $SSLCERT_PATH = ROOT_PATH.'public/Cert/'.$appletid.'/bdance_apiclient_cert.pem';//证书路径
                        $SSLKEY_PATH =  ROOT_PATH.'public/Cert/'.$appletid.'/bdance_apiclient_key.pem';//证书路径
                    }
                    
                    $appkey = $app['appSecret'];            //小程序的秘钥
                    $openid= $order['openid'];    //申请者的openid
                    $outTradeNo =$order['order_id'];
                    $totalFee= intval($order['wx_price'] * 100);  //申请了提现多少钱
                    $outRefundNo = $order['order_id']; //商户订单号
                    $refundFee= intval($order['wx_price'] * 100);  //申请了提现多少钱
                    
                    $opUserId = $mchid;//商户号
                    include "WinXinRefund.php";
                    $weixinpay = new WinXinRefund($openid,$outTradeNo,$totalFee,$outRefundNo,$refundFee,$SSLCERT_PATH,$SSLKEY_PATH,$opUserId,$appid,$apiKey);
                    $return = $weixinpay->refund();

                    if(!$return){
                        $this->error('退货失败!请检查系统设置->小程序设置和支付设置');
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
                    $request->setBizContent("{'refund_amount':".$order['wx_price'].", 'out_trade_no': ".$order['order_id']."}");
                    $result = $aop->execute ( $request); 
                    $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
                    $resultCode = $result->$responseNode->code;
                    if(!empty($resultCode)&&$resultCode == 10000){
                        $return = true;
                    } else {
                        $this->error('退款失败!请检查系统设置->支付宝小程序设置');
                        exit;
                    }
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
                    $res = _Postrequest($url, http_build_query($params));
                    $res = json_decode($res, true);
                    if($res){
                        if($res['errno'] == 0){
                            $return = true;
                        }else{
                            $this->error('退款失败!请检查系统设置->百度小程序设置');exit;
                        }
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
                    $refund_fee = $order['wx_price']*100;
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

                }elseif($order['paytype'] == 5){
                    $mchid = $app['mchid'];   //商户号
                    $apiKey = $app['signkey'];    //商户的秘钥
                    $appid = $app['appID'];                 //小程序的id
                    $appkey = $app['appSecret'];            //小程序的秘钥
                    $openid= $order['openid'];    //申请者的openid
                    $outTradeNo =$order['order_id'];
                    $totalFee= intval($order['wx_price'] * 100);  //申请了提现多少钱
                    $outRefundNo = $order['order_id']; //商户订单号
                    $refundFee= intval($order['wx_price'] * 100);  //申请了提现多少钱
                    $SSLCERT_PATH = ROOT_PATH.'public/Cert/'.$appletid.'/apiclient_cert.pem';//证书路径
                    $SSLKEY_PATH =  ROOT_PATH.'public/Cert/'.$appletid.'/apiclient_key.pem';//证书路径
                    $opUserId = $mchid;//商户号
                    include "WinXinRefund.php";
                    $weixinpay = new WinXinRefund($openid,$outTradeNo,$totalFee,$outRefundNo,$refundFee,$SSLCERT_PATH,$SSLKEY_PATH,$opUserId,$appid,$apiKey);
                    $return = $weixinpay->refund();

                    if(!$return){
                        $this->error('退货失败!请检查系统设置->小程序设置和支付设置');
                    }
                }

                if(!$return){
                    $this->error("退款失败 请检查证书是否正常");
                }else{
                    //更新订单状态
                    $is = Db::name('wd_xcx_bargain_order') ->where('uniacid', $appletid) ->where('th_orderid', $out_refund_no)->where('flag',5) ->find();
                    if(!$is){
                        $res = Db::name('wd_xcx_bargain_order') ->where('uniacid', $appletid) ->where('th_orderid', $out_refund_no) ->update(['flag' => 5]);
                    }

                    //金钱流水
                    $xfmoney = array(
                        "uniacid" => $appletid,
                        "orderid" => $order['order_id'],
                        "suid" => $order['suid'],
                        "type" => "add",
                        "score" => $order['wx_price'],
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
                    Db::name("wd_xcx_money")->insert($xfmoney);

                    $tk_je = $order['yue_price']; //退回余额
                    if($tk_je > 0){
                        $xfmoney1 = array(
                            "uniacid" => $appletid,
                            "orderid" => $order['order_id'],
                            "suid" => $order['suid'],
                            "type" => "add",
                            "score" => $tk_je,
                            "message" => "退款退回余额",
                            "creattime" => time()
                        );
                        Db::name("wd_xcx_money")->insert($xfmoney1);
                        Db::execute("update {$this->prefix}wd_xcx_superuser set money=money+".$tk_je." where id=".$order['suid']);
                    }
                    Db::execute("update {$this->prefix}wd_xcx_bargain_pro set kc = kc + 1, realSaleVolume = realSaleVolume - 1 where id=".$order['pid']);
                    if($order['source'] != 3){
                        $jsons['orderid'] = $order['order_id'];
                        $jsons['ftitle'] = $order['title'];
                        $jsons['fprice'] = "实付：".$order['true_price'];

                        
                        if($order['source'] == 1){
                            $openid = Db::name('wd_xcx_user')->where('suid', $order['suid'])->value('openid');
                            $jsons = [
                                'order_id' => $order['order_id'],
                                'fprice' => $order['true_price'],
                                'msg' => "退款成功",
                            ];
                            $jsons = serialize($jsons);
                            sendSubscribe($appletid, 3, $openid, $jsons);
                        }else if($order['source'] == 6){
                            if($order['yue_price'] > 0){
                                $jsons['refund_type'] = "退回QQ：￥".$order['wx_price']."元，退回余额：￥".$order['yue_price'];
                            }else{
                                $jsons['refund_type'] = "退回QQ：￥".$order['true_price']."元";
                            }
                            $jsons = serialize($jsons);

                            $openid = Db::name('wd_xcx_qq_user')->where('suid', $order['suid'])->value('openid');
                            tpl_send($appletid, 8, $openid, $order['source'], $order['qx_formid'], $jsons);
                        }else if($order['source'] == 5){

                            if($order['yue_price'] > 0){
                                $jsons['refund_type'] = "退回微信：￥".$order['wx_price']."元，退回余额：￥".$order['yue_price'];
                            }else{
                                $jsons['refund_type'] = "退回微信：￥".$order['true_price']."元";
                            }
                            $jsons = serialize($jsons);

                            $openid = Db::name('wd_xcx_toutiao_user')->where('suid', $order['suid'])->value('openid');
                            tpl_send($appletid, 8, $openid, $order['source'], $order['qx_formid'], $jsons);
                        }
                    }
                }
            }else{
                if($op == "confirmtk"){
                    Db::name("wd_xcx_bargain_order")->where("uniacid",$appletid)->where("th_orderid",$out_refund_no)->update(array("flag"=>8));
                }else{
                    Db::name("wd_xcx_bargain_order")->where("uniacid",$appletid)->where("th_orderid",$out_refund_no)->update(array("flag"=>5));
                }
                //金钱流水
                if($order['price'] > 0){
                    $xfmoney = array(
                        "uniacid" => $appletid,
                        "orderid" => $order['order_id'],
                        "suid" => $order['suid'],
                        "type" => "add",
                        "score" => $order['true_price'],
                        "message" => "退款退回余额",
                        "creattime" => time()
                    );
                    Db::name("wd_xcx_money")->insert($xfmoney);
                }
                Db::execute("update {$this->prefix}wd_xcx_superuser set money=money+".$order['true_price']." where id=".$order['suid']);
                Db::execute("update {$this->prefix}wd_xcx_bargain_pro set kc = kc + 1, realSaleVolume = realSaleVolume - 1 where id=".$order['pid']);
                if($order['source'] != 3){
                        $jsons['orderid'] = $order['order_id'];
                        $jsons['ftitle'] = $order['title'];
                        $jsons['fprice'] = "实付：".$order['true_price'];
                        $jsons['refund_type'] = "退回余额：￥".$order['true_price']."元";
                        $jsons = serialize($jsons);
                        if($order['source'] == 1){
                            $openid = Db::name('wd_xcx_user')->where('suid', $order['suid'])->value('openid');
                            $jsons = [
                                'order_id' => $order['order_id'],
                                'fprice' => $order['true_price'],
                                'msg' => "退款成功",
                            ];
                            $jsons = serialize($jsons);
                            sendSubscribe($appletid, 3, $openid, $jsons);
                        }else if($order['source'] == 6 && $order['qx_formid']){
                            $openid = Db::name('wd_xcx_qq_user')->where('suid', $order['suid'])->value('openid');
                            tpl_send($appletid, 8, $openid, $order['source'], $order['prepayid'], $jsons);
                        }else if($order['source'] == 5 && $order['qx_formid']){
                            $openid = Db::name('wd_xcx_toutiao_user')->where('suid', $order['suid'])->value('openid');
                            tpl_send($appletid, 8, $openid, $order['source'], $order['qx_formid'], $jsons);
                        }
                }
            }
            $this->success("取消成功");
        }else if($op == "fahuo"){
            $id = input('id');
            $data['hxtime'] = time();
            $data['kuaidi'] = input('kuaidi');
            $data['kuaidihao'] = input('kuaidihao');
            $data['flag'] = 4;
            $res = Db::name("wd_xcx_bargain_order")->where("id", $id)->update($data);
            if($res){
                $info = Db::name("wd_xcx_bargain_order")->where("id", $id)->find();
                if($info['source'] == 1){
                    $openid = Db::name("wd_xcx_user")->where("suid", $info['suid'])->value('openid');
                    $jsons = [
                        'order_id' => $info['order_id']
                    ];
                    $jsons = serialize($jsons);
                    sendSubscribe($appletid, 1, $openid, $jsons);
                }
                $this->success("发货成功！");
            }else{
                $this->success("发货失败！");
            }
        }else if($op == 'refuseqx'){
            $id = input("id");
            Db::name('wd_xcx_bargain_order')->where("uniacid",$appletid)->where("id",$id)->update(array("flag"=>1));
            $info = Db::name("wd_xcx_bargain_order")->where("id", $id)->find();
            if($info['source'] == 1){
                $openid = Db::name("wd_xcx_user")->where("suid", $info['suid'])->value('openid');
                $jsons = [
                    'order_id' => $info['order_id'],
                    'fprice' => $info['true_price'],
                    'msg' => "退款被拒",
                ];
                $jsons = serialize($jsons);
                sendSubscribe($appletid, 3, $openid, $jsons);
            }
            $this->success('拒绝取消成功!');
        }



        $search_flag = input('search_flag');
        $search_keys = input('search_keys');
        $search_type = input('search_type');
        $start_get = input('start_get') ? strtotime(input('start_get')) : '';
        $end_get = input('end_get') ? strtotime(input('end_get')) : '';
        $where = '';
        if ($search_flag != "") {
            if($search_flag == 1){
                $where .= " a.flag = 1 and a.nav = 1";
            }elseif($search_flag == 10){
                $where .= " a.flag = 1 and a.nav = 2";
            }else{
                $where.= " a.flag = ".$search_flag;
            }
            
        }

        if ($start_get && $end_get) {
            $where.= " a.creattime > ".$start_get." and a.createtime < ".$end_get;
        }else if($start_get){
            $where.= " a.creattime >= ".$start_get;
        }else if ($end_get) {
            $where.= " a.creattime <= ".$start_get;
        }

        if ($search_type && $search_keys) {
            if ($search_type == 1) {
                $where.= " a.order_id like '%".$search_keys."%'";
            }
            if ($search_type == 2) {
                $where.= " b.name like '%".$search_keys."%'";
            }
            if ($search_type == 3) {
                $where.= " b.mobile like '%".$search_keys."%'";
            }
            if ($search_type == 4) {
                $where.= " b.address like '%".$search_keys."%'";
            }
        }
        $counts = Db::name('wd_xcx_bargain_order')->alias("a")->join("wd_xcx_duo_products_address b",'a.addressId=b.id','left')->where('a.uniacid', $appletid)->where($where)->count();

        $orders = Db::name("wd_xcx_bargain_order")->alias("a")->join("wd_xcx_duo_products_address b",'a.addressId=b.id','left')->where("a.uniacid", $appletid)->where($where)->order("a.creattime desc")->field("a.*")->paginate(10, false, ["query" => ["appletid" => $appletid,'search_flag'=>$search_flag,"search_type"=>$search_type,"search_keys"=>$search_keys,"start_get"=>$start_get,"end_get"=>$end_get]]);

        $orderlist = $orders->toArray()['data'];
        foreach ($orderlist as $key => &$res) {
            if(!empty($res['self_taking_info'])){
                $self_taking_info= unserialize($res['self_taking_info']);
                $self_taking_shop_info = unserialize($self_taking_info['self_taking_shop_info']);
                $self_taking_info['self_taking_shop_info'] = $self_taking_shop_info;           
                $res['self_taking_info'] = $self_taking_info;
            }
                    
            // 获取万能表单信息
            if ($res['form_id']) {
                $arr2ss = Db::name("wd_xcx_formcon")->where("uniacid", $appletid)->where("id", $res['form_id'])->value("val");
                $res['val'] = unserialize($arr2ss);
             }
            $res['thumb'] = remote($appletid, $res['thumb'], 1);
            if($res['hxinfo'] != "" && $res['hxinfo'] != 'NULL'){
                $res['hxinfo'] = unserialize($res['hxinfo']);
                
                if($res['hxinfo'][0]==1){
                     $res['hxinfoText']="系统核销";
                }else if($res['hxinfo'][0] == '密码核销' || $res['hxinfo'][0] == '管理员核销'){
                   $res['hxinfoText'] = $res['hxinfo'][0];
                }else if($res['hxinfo'][0]=='核销员核销'){
                    $res['hxinfoText']=$res['hxinfo'][1].'核销';
                }
            }else{
                $res['hxinfoText']="";
            }
            $res['creattime'] = date("Y-m-d H:i:s",$res['creattime']);
            $res['hxtime'] = $res['hxtime'] == 0?"无":date("Y-m-d H:i:s",$res['hxtime']);
            
            if($res['hxtime'] == 0 && $res['nav'] == 1){
                $res['hxtime'] = "无";
            }
            $res['userinfo'] = Db::name("wd_xcx_superuser")->where("uniacid", $appletid)->where("id", $res['suid'])->find();
             // 获取万能表单信息
            $res['val'] = [];
            if ($res['form_id']) {
                $arr2ss = Db::name("wd_xcx_formcon")->where("uniacid", $appletid)->where("id", $res['form_id'])->value("val");
                $res['val'] = unserialize($arr2ss);
            }
            // 转换地址

            if(empty($res['m_address'])){
                if($res['nav'] == 1 && $res['addressId'] > 0){
                    $res['address_get'] = Db::name("wd_xcx_duo_products_address")->where("uniacid", $appletid)->where("id", $res['addressId'])->where("suid", $res['suid'])->find();
                }else{
                    $res['addressId'] = 0;
                }
            }else{
                $res['address_get'] = unserialize($res['m_address']);
            }
        }

        if($start_get){
            $start_get = date("Y-m-d H:i:s", $start_get);
        }
        if($end_get){
            $end_get = date("Y-m-d H:i:s", $end_get);
        }
        $this->assign("search_flag", $search_flag);
        $this->assign("search_type", $search_type);
        $this->assign("search_keys", $search_keys);
        $this->assign("start_get", $start_get);
        $this->assign("end_get", $end_get);

        $this->assign('counts',$counts);
        $this->assign('orders',$orders);
        $this->assign('orderlist',$orderlist);
        return $this->fetch('orderlist');
    }
    public function excel(){
        $appletid = input("appletid");
        $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
        if(!$res){
            $this->error("找不到对应的小程序！");
        }
        $this->assign('applet',$res);
        
        require_once ROOT_PATH.'public/plugin/PHPExcel/PHPExcel.php';
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()->setCreator("导出订单列表")
                ->setLastModifiedBy("订单列表")
                ->setTitle("导出订单列表")
                ->setSubject("导出订单列表")
                ->setDescription("导出订单列表")
                ->setKeywords("导出订单列表")
                ->setCategory("导出订单列表");
        $objPHPExcel->getActiveSheet()->setCellValue('A1', '下单时间');
        $objPHPExcel->getActiveSheet()->setCellValue('B1', '订单编号');
        $objPHPExcel->getActiveSheet()->setCellValue('C1', '订单类型');
        $objPHPExcel->getActiveSheet()->setCellValue('D1', '商品标题');
        $objPHPExcel->getActiveSheet()->setCellValue('E1', '商品单价');
        $objPHPExcel->getActiveSheet()->setCellValue('F1', '支付详情');
        $objPHPExcel->getActiveSheet()->setCellValue('G1', '核销时间');
        $objPHPExcel->getActiveSheet()->setCellValue('H1', '姓名');
        $objPHPExcel->getActiveSheet()->setCellValue('I1', '联系方式');
        $objPHPExcel->getActiveSheet()->setCellValue('J1', '邮编');
        $objPHPExcel->getActiveSheet()->setCellValue('K1', '地址');
        $objPHPExcel->getActiveSheet()->setCellValue('L1', '状态');

        $search_flag = input('search_flag');
        $search_keys = input('search_keys');
        $search_type = input('search_type');
        $start_get = input('start_get') ? strtotime(input('start_get')) : '';
        $end_get = input('end_get') ? strtotime(input('end_get')) : '';
        $where = [];
        if ($search_flag != "") {
            $where['flag'] = $search_flag;
        }

        if ($start_get && $end_get) {
            $where['creattime'] = ['between', [$start_get,$end_get]];
        }else if($start_get){
            $where['creattime'] = ['>=', $start_get];
        }else if ($end_get) {
            $where['creattime'] = ['<=', $end_get];
        }

        if ($search_type) {
            if ($search_type == 1) {
                $where['order_id'] = ["like", "%".$search_keys ."%"];
            }
            if ($search_type == 2) {
                $where['name'] = ["like", "%".$search_keys ."%"];
            }
            if ($search_type == 3) {
                $where['mobile'] = ["like", "%".$search_keys ."%"];
            }
            if ($search_type == 4) {
                $where['address'] = ["like", "%".$search_keys ."%"];
            }
        }
        $orders=Db::name("wd_xcx_bargain_order")->where("uniacid",$appletid)->where($where)->order("creattime DESC")->select();

        foreach ($orders as $key => &$res) {
            $res['creattime'] = date("Y-m-d H:i:s",$res['creattime']);
            $res['hxtime'] = $res['hxtime'] == 0?"无核销信息":date("Y-m-d H:i:s",$res['hxtime']);
            if(intval($res['hxtime']) == 0 && $res['nav'] == 1){
                $res['hxtime'] = "无";
            }
            
            // 转换地址
            if($res['m_address']){
                $res['address_get'] = unserialize($res['m_address']);
            }else{
                $res['address_get'] = []; 
                $res['address_get']['name'] = "";
                $res['address_get']['mobile'] = "";
                $res['address_get']['postalcode'] = "";
                $res['address_get']['address'] = "";
            }

            $num=$key+2;
            if($res['flag'] == 0){
                $res['flag1'] = '未付款';
            }else if($res['flag'] == 1 && $res['nav'] == 1){
                $res['flag1'] = '已付款待发货';
            }else if($res['flag'] == 1 && $res['nav'] == 2){
                $res['flag1'] = '已付款待消费';
            }else if($res['flag'] == 2){
                $res['flag1'] = '已完成';
            }else if($res['flag'] == 3){
                $res['flag1'] = '已过期';
            }else if($res['flag'] == 4){
                $res['flag1'] = '已发货';
            }else if($res['flag'] == 5){
                $res['flag1'] = '已取消';
            }else if($res['flag'] == 6){
                $res['flag1'] = '取消中';
            }else if($res['flag'] == 7){
                $res['flag1'] = '退货中';
            }else if($res['flag'] == 8){
                $res['flag1'] = '退货成功';
            }else if($res['flag'] == 9){
                $res['flag1'] = '退货失败';
            }else if($res['flag'] == -1){
                $res['flag1'] = '已关闭';
            }else if($res['flag'] == -2){
                $res['flag1'] = '订单无效';
            }
            $priceInfo = "原价：￥".$res['price']."\r\n微信：￥".$res['wx_price']."\r\n余额支付：￥".$res['yue_price']."\r\n运费：￥".$res['yunfei']."\r\n实付：￥".$res['true_price'];
            if($res['nav'] == 1){
                $orderType = "发货订单";
            }else{
                $orderType = "到店自取订单";
            }
          
            $objPHPExcel->getActiveSheet()->getStyle("F".$num)->getAlignment()->setWrapText(TRUE); 

            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValueExplicit('A'.$num, $res['creattime'],'s')
                        ->setCellValueExplicit('B'.$num, $res['order_id'],'s')
                        ->setCellValueExplicit('C'.$num, $orderType,'s')
                        ->setCellValueExplicit('D'.$num, $res['title'],'s')
                        ->setCellValueExplicit('E'.$num, $res['price'],'s')
                        ->setCellValueExplicit('F'.$num, $priceInfo,'s')
                        ->setCellValueExplicit('G'.$num, $res['hxtime'], 's')
                        ->setCellValueExplicit('H'.$num, $res['address_get']['name'], 's')
                        ->setCellValueExplicit('I'.$num, $res['address_get']['mobile'], 's')
                        ->setCellValueExplicit('J'.$num, $res['address_get']['postalcode'], 's')
                        ->setCellValueExplicit('K'.$num, $res['address_get']['address'], 's')
                        ->setCellValueExplicit('L'.$num, $res['flag1'], 's');
        }
        $objPHPExcel->getActiveSheet()->setTitle('导出砍价订单列表');
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="砍价订单列表.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
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
