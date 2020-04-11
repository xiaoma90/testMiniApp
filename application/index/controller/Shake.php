<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;
class Shake extends Base
{
    public function index(){
        if(check_login()){
            if(powerget()){
                $appid = input('appletid');
                $applet = Db::name('wd_xcx_applet') ->where('id', $appid) ->find();
                $this->assign('applet', $applet);
                //查询所有的活动
                $res = Db::name('wd_xcx_lottery_activity') ->where('uniacid', $appid)->order('id desc') ->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                $data = $res -> toArray()['data'];
                foreach ($data as $key => &$value) {
                    $value['thumb'] = remote($appid, $value['thumb'], 1);
                }

                $count = Db::name('wd_xcx_lottery_activity') ->where('uniacid', $appid) ->count();
                $this->assign('lists', $res);
                $this->assign('activity', $data);
                $this->assign('count', $count);
                return $this->fetch('index');
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
    public function add(){
         if(powerget()){
            $appid = input('appletid');
            $applet = Db::name('wd_xcx_applet') ->where('id', $appid) ->find();
            $this->assign('applet', $applet);
            return $this->fetch('add');
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
    }
    public function setsave(){
        $appletid = input('appletid');
        $data['uniacid'] = $appletid;
        $title = input('title');
        if($title){
            $data['title'] = $title;
        }else{
            $this->error('请输入活动名称');
        }
        $begin = input('begin');
        if($begin){
            $arr = date_parse_from_format('Y年m月d日 H:i:s',$begin);
            $time = mktime($arr['hour'],$arr['minute'],$arr['second'],$arr['month'],$arr['day'],$arr['year']);
            $data['begin'] = $time;
        }else{
            $this->error('请输入活动开始时间');
        }
        $end = input('end');
        if($end){
            $arr = date_parse_from_format('Y年m月d日 H:i:s',$end);
            $time = mktime($arr['hour'],$arr['minute'],$arr['second'],$arr['month'],$arr['day'],$arr['year']);
            $data['end'] = $time;
        }else{
            $this->error('请输入活动结束时间');
        }
        $descp = input('descp');
        if($descp){
            $data['descp'] = $descp;
        }else{
            $this->error('请输入活动规则');
        }
        $thumb = input('commonuploadpic1');
        if($thumb){
            $data['thumb'] = remote($appletid,$thumb,2);
        }else{
            $this->error('请选择活动缩略图');
        }
        $bg = input('commonuploadpic2');
        if($bg){
            $data['bg'] = remote($appletid,$bg,2);
        }else{
            $this->error('请选择背景图');
        }
        $text_img1 = input('commonuploadpic3');
        if($text_img1){
            $data['text_img1'] = remote($appletid,$text_img1,2);
        }else{
            $this->error('请选择点击模式标题图');
        }
        $text_img2 = input('commonuploadpic4');
        if($text_img2){
            $data['text_img2'] = remote($text_img2,$bg,2);
        }else{
            $this->error('请选择摇一摇模式标题图');
        }
        
        $data['nav_color'] = '#'.input('nav_color');
        $data['status'] = input('status');
        $data['createtime'] = time();
        $baseinfo['means'] = input('means');
        if(input('jifen')){
            $baseinfo['jifen'] = input('jifen');
        }else{
            $baseinfo['jifen'] = 10;
        }
        if(input('every_join')){
            $baseinfo['every_join'] = input('every_join');
        }else{
            $baseinfo['every_join'] = 3;
        }
        if(input('just_one')){
            $baseinfo['just_one'] = input('just_one');
        }else{
            $baseinfo['just_one'] = 0;
        }
        if(input('users_type')){
            $baseinfo['users_type'] = input('users_type');
        }else{
            $baseinfo['users_type'] = 0;
        }
        if(input('fill_time')){
            $baseinfo['fill_time'] = input('fill_time');
        }else{
            $baseinfo['fill_time'] = 0;
        }
        if(input('share_add')){
            $baseinfo['share_add'] = input('share_add');
        }else{
            $baseinfo['share_add'] = 1;
        }
        if(input('everyday_share')){
            $baseinfo['everyday_share'] = input('everyday_share');
        }else{
            $baseinfo['everyday_share'] = 1;
        }
        if(input('total_share')){
            $baseinfo['total_share'] = input('total_share');
        }else{
            $baseinfo['total_share'] = 1;
        }


        $data['base'] = serialize($baseinfo);
        $r = Db::name('wd_xcx_lottery_activity')->insert($data);
        if($r){
            $this ->success('活动创建成功!',Url('shake/index').'?appletid='.$appletid);
        }else{
            $this ->error('发生网络错误,活动创建失败!');
        }
    }
    public function edit(){
         if(powerget()){
            $aid = input('aid');
            $appletid = input('appletid');
            $applet = Db::name('wd_xcx_applet') ->where('id', $appletid) ->find();
            $this->assign('applet', $applet);


            $res = Db::name('wd_xcx_lottery_activity') ->where('id', $aid) ->find();
            $res['thumb'] = remote($appletid, $res['thumb'], 1);
            $res['bg'] = remote($appletid, $res['bg'], 1);
            $res['text_img1'] = remote($appletid, $res['text_img1'], 1);
            $res['text_img2'] = remote($appletid, $res['text_img2'], 1);
            $base = unserialize($res['base']);
            $this->assign('activity', $res);
            $this->assign('base', $base);
            return $this->fetch('edit');
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
    }
    public function setedit(){
        $appletid = input('appletid');
        $aid = input('aid');
        $data['uniacid'] = $appletid;
        $title = input('title');
        if($title){
            $data['title'] = $title;
        }else{
            $this->error('请输入活动名称');
        }
        $begin = input('begin');
        if($begin){
            // $arr = date_parse_from_format('Y年m月d日 H:i:s',$begin);
            $time = $begin ? strtotime($begin) : 0;
            $data['begin'] = $time;
        }else{
            $this->error('请输入活动开始时间');
        }
        $end = input('end');
        if($end){
            // $arr = date_parse_from_format('Y年m月d日 H:i:s',$end);
            $time = $end ? strtotime($end) : 0;
            // $time = mktime($arr['hour'],$arr['minute'],$arr['second'],$arr['month'],$arr['day'],$arr['year']);
            $data['end'] = $time;
        }else{
            $this->error('请输入活动结束时间');
        }
        $descp = input('descp');
        if($descp){
            $data['descp'] = htmlspecialchars_decode($descp);
        }else{
            $this->error('请输入活动规则');
        }
        $thumb = input('commonuploadpic1');
        if($thumb){
            $data['thumb'] = $thumb;
        }else{
            $this->error('请选择活动缩略图');
        }
        $bg = input('commonuploadpic2');
        if($bg){
            $data['bg'] = $bg;
        }else{
            $this->error('请选择背景图');
        }
        $text_img1 = input('commonuploadpic3');
        if($text_img1){
            $data['text_img1'] = $text_img1;
        }else{
            $this->error('请选择点击模式标题图');
        }
        $text_img2 = input('commonuploadpic4');
        if($text_img2){
            $data['text_img2'] = $text_img2;
        }else{
            $this->error('请选择摇一摇模式标题图');
        }
        
        $data['nav_color'] = '#'.input('nav_color');
        $data['status'] = input('status');
        $baseinfo['means'] = input('means');
        $jifen = input('jifen');
        if(isset($jifen)){
            $baseinfo['jifen'] = $jifen;
        }else{
            $baseinfo['jifen'] = 10;
        }
        if(input('every_join')){
            $baseinfo['every_join'] = input('every_join');
        }else{
            $baseinfo['every_join'] = 3;
        }
        if(input('just_one')){
            $baseinfo['just_one'] = input('just_one');
        }else{
            $baseinfo['just_one'] = 0;
        }
        if(input('users_type')){
            $baseinfo['users_type'] = input('users_type');
        }else{
            $baseinfo['users_type'] = 0;
        }
        if(input('fill_time')){
            $baseinfo['fill_time'] = input('fill_time');
        }else{
            $baseinfo['fill_time'] = 0;
        }
        if(input('share_add')){
            $baseinfo['share_add'] = input('share_add');
        }else{
            $baseinfo['share_add'] = 1;
        }
        if(input('everyday_share')){
            $baseinfo['everyday_share'] = input('everyday_share');
        }else{
            $baseinfo['everyday_share'] = 1;
        }
        if(input('total_share')){
            $baseinfo['total_share'] = input('total_share');
        }else{
            $baseinfo['total_share'] = 1;
        }
        $data['zjtext']=input('zjtext');
        $data['fxtext']=input('fxtext');
        $data['base'] = serialize($baseinfo);
        $data['share_url']=input('share_url');
        $res = Db::name('wd_xcx_lottery_activity') ->where('id', $aid) ->update($data);
        if($res !== false){
            $this ->success('活动编辑成功!', Url('shake/index').'?appletid='.$appletid);
        }else{
            $this ->error('发生网络错误,活动编辑失败!');
        }
    }
    //删除活动
    public function delActivity(){
        $aid = input('id');
        $appletid = input('appletid');
        $r = Db::name('wd_xcx_lottery_activity') ->where('uniacid', $appletid) ->where('id', $aid) ->delete();
        if($r){
            return 1;
        }else{
            return 2;
        }
    }
    //设置奖品页面
    public function setprize(){
        if(powerget()){
            $aid = input('aid');
            $appletid = input('appletid');
            $applet = Db::name('wd_xcx_applet') ->where('id', $appletid) ->find();
            $this->assign('applet', $applet);
            $res = Db::name('wd_xcx_lottery_activity') ->where('id', $aid) ->find();
            $base = unserialize($res['base']);
            $this->assign('activity', $res);
            $this->assign('base', $base);
            //根据appletid获取优惠劵
            $coupons = Db::name('wd_xcx_coupon') ->where('give_type = 0 or give_type = 1') ->where('uniacid', $appletid) ->select();
            $this->assign('coupons', $coupons);
            //获取已设置的奖品
            $prizes = Db::name('wd_xcx_lottery_prize') ->where('uniacid', $appletid) ->where('aid', $aid) ->select();
            foreach ($prizes as $key => &$value) {
                if($value['types'] == 4){
                    $detail = Db::name('wd_xcx_coupon') ->where('id', $value['detail']) ->find();
                    $value['detail'] = $detail['title'];
                }
            }
            $this->assign('prizes', $prizes);
            //获取已设置的奖品
            $selectedPrizes = array();
            $prizes_set = array();
            $total_num_sum = 0;
            $total_chance_sum = 0;
            for($i = 1; $i <= 8; $i++){
                // $selectedPrizes[$i] = pdo_fetch("SELECT * FROM ".tablename('sudu8_page_lottery_prize')." WHERE uniacid = :uniacid and aid = :aid and num like '%".$i."%'",
                //             array(':uniacid'=>$uniacid, ':aid'=>$id));
                $selectedPrizes[$i] = Db::name('wd_xcx_lottery_prize') ->where('uniacid', $appletid) ->where('aid', $aid) ->where('num', 'like', "%$i%") ->find();
                $flag = true;
                if(!empty($selectedPrizes[$i])){
                    if(!empty($prizes_set)){
                        foreach ($prizes_set as $p) {
                            if($p['id'] == $selectedPrizes[$i]['id']){
                                $flag = false;
                            }
                        }    
                    }
                    
                    if($flag){
                        $prizes_set[] = $selectedPrizes[$i];
                    }    
                }
                
            }
            foreach ($prizes_set as $k => &$v) {
                $length = count(explode("|", $v['num']));
                $v['total_num'] = $v['total'] * $length;
                $v['total_chance'] = $v['chance'] * $length / 100;
                if($v['types'] == '4'){
                    // $v['detail'] = pdo_fetchcolumn("SELECT title FROM ".tablename('sudu8_page_coupon')." WHERE uniacid = :uniacid and id = :id",
                    //                     array(":uniacid"=>$uniacid, ":id"=>$v['detail']));
                    $title = Db::name('wd_xcx_coupon') ->where('uniacid', $appletid) ->where('id', $v['detail']) ->field('title') ->find();
                    $v['detail'] = $title['title'];
                }
                if($v['types'] == '1'){
                    $v['detail'] .= '积分';
                }
                if($v['types'] == '2'){
                    $v['detail'] .= '元';
                }
                $total_num_sum += $v['total_num'];
                $total_chance_sum += $v['total_chance'];
            }

            $this->assign('selectedPrizes', $selectedPrizes);
            $this->assign('prizes_set', $prizes_set);
            $this->assign('total_num_sum', $total_num_sum);
            $this->assign('total_chance_sum', $total_chance_sum);
            return $this->fetch('setprize');
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
    }
    public function changeMeans(){
        $aid = input('id');
        $res = Db::name('wd_xcx_lottery_activity') ->where('id', $aid) ->find();
        $base = unserialize($res['base']);
        if($base['means'] == 1){
            $base['means'] = 0;
        }else{
            $base['means'] = 1;
        }
        $data['base'] = serialize($base);
        $r = Db::name('wd_xcx_lottery_activity') ->where('id', $aid) ->update($data);
        if($r){
            return $base['means'];
        }
    }
    //添加奖品
    public function addPrize(){
        if(powerget()){
                $uniacid = input('appletid');
                 $pid = input('pid');
                $data = array(
                        'title' => input('title'),
                        'thumb' => input('thumb'),
                        'types' => input('types'),
                        'total' => input('total'),
                        'detail' => input('detail'),
                        'chance' => input('chance'),
                );
                
                if(!empty($pid)){
                    //$prize = pdo_get("sudu8_page_lottery_prize", array("uniacid"=>$uniacid, "id"=>$pid));
                    $prize = Db::name('wd_xcx_lottery_prize') ->where('uniacid', $uniacid) ->where('id', $pid) ->find();
                    $data['storage'] = intval(input('total')) - intval($prize['total']) + intval($prize['storage']);
                    if($data['storage'] < 0) $data['storage'] = 0; 
                    //pdo_update('sudu8_page_lottery_prize', $data, array('uniacid'=>$uniacid, 'id'=>$pid));
                    Db::name('wd_xcx_lottery_prize') ->where('uniacid', $uniacid) ->where('id', $pid) ->update($data);
                    $data['id'] = $pid;
                    $data['flag'] = 'modify';
                }else{
                    $data['uniacid'] = $uniacid;
                    $data['aid'] = input('aid');
                    $data['createtime'] = time();
                    $data['storage'] = $data['total'];
                    $data['num'] = '';
                
                    //pdo_insert('sudu8_page_lottery_prize', $data);
                    Db::name('wd_xcx_lottery_prize')->insert($data);
                    $data['id'] = Db::name('wd_xcx_lottery_prize')->getLastInsID();
                    $data['flag'] = 'add';
                 }
                
                if(strpos($data['thumb'],'http')===false){
                    $data['thumb'] = $data['thumb'];
                }
                if($data['types'] == '4'){
                    // $data['detail'] = pdo_fetchcolumn("SELECT title FROM ".tablename('sudu8_page_coupon')." WHERE uniacid = :uniacid and id = :id",
                    //                                         array(":uniacid"=>$uniacid, ":id"=>$data['detail']));
                    $res = Db::name('wd_xcx_coupon') ->where('uniacid', $uniacid) ->where('id', $data['detail']) ->field('title') ->find();                             
                    $data['detail'] = $res['title'];
                }
                
         
                $data['chance'] /= 100;
                return json_encode($data, JSON_UNESCAPED_UNICODE); 
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
    }
    //选中设定的奖品项
    public function selectPrize(){
        if(powerget()){
                $uniacid = input('appletid');
                $id = input('id');
                $aid = input('aid');
                $num = input('num');
                // $res = pdo_fetch("SELECT * FROM ".tablename('sudu8_page_lottery_prize')." WHERE uniacid = :uniacid and aid = :aid and num like '%".$_GPC['num']."%'",
                //             array(":uniacid" => $uniacid, ':aid' => $aid));
                $res = Db::name('wd_xcx_lottery_prize') ->where('uniacid', $uniacid) ->where('aid', $aid) ->where('num', 'like', "%$num%") ->find();
                //检测总概率之和有没有超过100%，超过则操作失败
                if(empty($res) || $res['id'] != $id){
                    $chance_sum = 0;
                    for($i = 1; $i <= 8; $i++){
                        if($i != $num){
                            // $chance_sum += pdo_fetchcolumn("SELECT chance FROM ".tablename('sudu8_page_lottery_prize')." WHERE uniacid = :uniacid and aid = :aid and num like '%".$i."%'", array(":uniacid" => $uniacid, ':aid' => $aid));
                            $r = Db::name('wd_xcx_lottery_prize') ->where('uniacid', $uniacid) ->where('aid', $aid) ->where('num', 'like', "%$i%") ->find();
                            $chance_sum += $r['chance'];
                        }
                    }
                    // $chance_num = pdo_fetchcolumn("SELECT chance FROM ".tablename('sudu8_page_lottery_prize')." WHERE uniacid = :uniacid and aid = :aid and id = :id", array(":uniacid" => $uniacid, ':aid' => $aid, ":id" => $id));
                    $chance_num = Db::name('wd_xcx_lottery_prize') ->where('uniacid', $uniacid) ->where('aid', $aid) -> where('id', $id) ->field('chance') ->find();
                    $chance_num = $chance_num['chance'];
                    if($chance_sum + $chance_num >= 10000){
                        $response = array('flag'=>1, 'warning' => '添加失败，总概率必须小于100%');
                        return json_encode($response, JSON_UNESCAPED_UNICODE);
                    }
                }
                //如果设置了每人只能中一次奖，则至多添加7个奖品
                // $base = pdo_fetchcolumn("SELECT base FROM ".tablename("sudu8_page_lottery_activity")." WHERE uniacid = :uniacid and id = :aid", array(':uniacid'=>$uniacid, ':aid'=>$aid));
                $base = Db::name('wd_xcx_lottery_activity') ->where('uniacid', $uniacid) ->where('id', $aid) ->field('base')->find();
                $base = unserialize($base['base']);
                if($base['just_one'] == '1' && empty($res)){
                    $flag = false;
                    for($i = 1; $i <= 8; $i++){
                        if($i != $num){
                            // $result = pdo_fetchcolumn("SELECT id FROM ".tablename('sudu8_page_lottery_prize')." WHERE uniacid = :uniacid and aid = :aid and num like '%".$i."%'", array(":uniacid" => $uniacid, ':aid' => $aid));
                            $result = Db::name('wd_xcx_lottery_prize') ->where('uniacid', $uniacid) ->where('aid', $aid) ->where('num', 'like', "%$i%") ->find();
                            if(empty($result)){
                                $flag = true;
                            }
                        }
                    }
                    if(!$flag){
                        $response = array('flag'=>2, 'warning' => '因每人只能中一次奖，8格不能全设');
                        return json_encode($response, JSON_UNESCAPED_UNICODE);
                    }
                }
                //从之前设置的奖品的num中删除当前选择的格子序号num
                if(!empty($res) && $res['id'] != $id){
                    $temp = explode('|', $res['num']);
                    foreach ($temp as $key => $value) {
                        if($value == $num){
                            unset($temp[$key]);
                        }
                    }
                    $temp = implode('|', $temp);
                    $data1 = array('num' => $temp);
                    //pdo_update('sudu8_page_lottery_prize', $data1, array('uniacid'=>$uniacid, 'aid'=>$aid, 'id'=>$res['id']));
                    Db::name('wd_xcx_lottery_prize') ->where('uniacid', $uniacid) ->where('aid', $aid) ->where('id', $res['id']) ->update($data1);
                }
                //添加当前选择的格子序号num到当前选择的奖品的num中
                if($res['id'] != $id){
                    // $now = pdo_fetch("SELECT * FROM ".tablename('sudu8_page_lottery_prize')." WHERE uniacid = :uniacid and aid = :aid and id = :id", array(":uniacid"=>$uniacid, ":aid"=>$aid, ":id"=>$id));
                    $now = Db::name('wd_xcx_lottery_prize') ->where('uniacid', $uniacid) ->where('aid', $aid) ->where('id', $id) ->find();
                    if(!empty($now['num'])){
                        $now['num'] .= '|' . $num;
                    }else{
                        $now['num'] = $num;
                    }
                    $data2 = array('num' => $now['num']);
                    // pdo_update('sudu8_page_lottery_prize', $data2, array('uniacid'=>$uniacid, 'aid'=>$aid, 'id'=>$id));
                    Db::name('wd_xcx_lottery_prize') ->where('uniacid', $uniacid) ->where('aid', $aid) ->where('id', $id) ->update($data2);
                }
                                
                // $prize = pdo_fetch("SELECT * FROM ".tablename('sudu8_page_lottery_prize')." WHERE uniacid = :uniacid and aid = :aid and id = :id",
                //             array(":uniacid"=>$uniacid, ":aid"=>$aid, ":id"=>$id));
                $prize = Db::name('wd_xcx_lottery_prize') ->where('uniacid', $uniacid) ->where('aid', $aid) ->where('id', $id) ->find();
                // if(strpos($prize['thumb'],'http')===false){
                //     $prize['thumb_https'] = HTTPSHOST.$prize['thumb'];
                // }else{
                    $prize['thumb_https'] = $prize['thumb'];
                // }
                return json_encode($prize, JSON_UNESCAPED_UNICODE);
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
    }
    //删除设置的奖项
    public function deletePrize(){
        if(powerget()){
            $uniacid = input('appletid');
            $id = input('id');
            $index = input('index');
            // $res = pdo_fetch("SELECT * FROM ".tablename('sudu8_page_lottery_prize')." WHERE uniacid = :uniacid and aid = :aid and num like '%".$index."%'",
            //             array(":uniacid" => $uniacid, ':aid' => $id));
            $res = Db::name('wd_xcx_lottery_prize') ->where('uniacid', $uniacid) ->where('aid', $id) ->where('num', 'like', "%$index%")->find();
            if(!empty($res)){
                $temp = explode('|', $res['num']);
                foreach ($temp as $key => $value) {
                    if($value == $index){
                        unset($temp[$key]);
                    }
                }
                $temp = implode('|', $temp);
                $data1 = array('num' => $temp);
                // pdo_update('sudu8_page_lottery_prize', $data1, array('uniacid'=>$uniacid, 'aid'=>$id, 'id'=>$res['id']));
                Db::name('wd_xcx_lottery_prize') ->where('uniacid', $uniacid) ->where('aid', $id) ->where('id', $res['id'])->update($data1);
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
    }
    //获取指定奖品信息
    public function getPrize(){
        $id = input('id');
        $res = Db::name('wd_xcx_lottery_prize') ->where('id', $id) ->find();
        return json_encode($res);
    }
    //删除奖项
    public function delPrize(){
        $id = input('id');
        $uniacid = input('appletid');
        $res = Db::name('wd_xcx_lottery_prize') ->where('id', $id) ->where('uniacid', $uniacid) ->delete();
        return json_encode($res);
    }
    //抽奖记录
    public function record(){
        if(powerget()){
            $appletid = input('appletid');
            $applet = Db::name('wd_xcx_applet') ->where('id', $appletid) ->find();
            $this->assign('applet', $applet);
            $aid = input('aid');
            $prizesSet = array();
            for($i = 1; $i <= 8; $i++){
                $temp = Db::name('wd_xcx_lottery_prize') ->where('uniacid', $appletid) ->where('aid', $aid) ->where('num', 'like', "%$i%") ->find();
                if(!empty($temp) && !in_array($temp,  $prizesSet)){
                    $prizesSet[] = $temp;
                }
            }
            foreach ($prizesSet as $key => &$value) {
                if($value['types'] == '1'){
                    $value['detail'] = '积分：' . $value['detail'] . '积分';
                }
                if($value['types'] == '2'){
                    $value['detail'] = '余额：' . $value['detail'] . '元';
                }
                if($value['types'] == '3'){
                    $value['detail'] = '实物：' . $value['detail'];
                }
                if($value['types'] == '4'){
                    $title = Db::name('wd_xcx_coupon') ->where('uniacid', $appletid) ->where('id', $value['detail']) ->field('title') ->find();
                    $value['detail'] = '优惠券：' . $title['title'];
                }
            }
            $where = '';
            $select_pid = '';
            $select_status = '';
            if(!empty(input('select_pid')) || in_array(input('select_status'), ['0','1','2'])){
                if(!empty(input('select_pid'))){
                    $select_pid = input('select_pid');
                    $where .= ' pid = '.$select_pid;
                    if(in_array(input('select_status'),['0','1','2'])){
                        $select_status = input('select_status');
                        $where .= ' and status = '.$select_status;
                    }
                }else{
                    if(in_array(input('select_status'),['0','1','2'])){
                        $select_status = input('select_status');
                        $where .= ' status = '.$select_status;
                    }
                }
                
            }
            $this ->assign('select_pid', $select_pid);
            $this ->assign('select_status', $select_status);
            //查询中奖记录
            $total = Db::name('wd_xcx_lottery_record') ->where('uniacid', $appletid) ->where('aid', $aid) ->where($where) ->count();
            $record = Db::name('wd_xcx_lottery_record') ->where('uniacid', $appletid) ->where('aid', $aid) ->where($where) ->order('createtime desc') ->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"), 'aid'=>input('aid'))]);
            $excel_records = Db::name('wd_xcx_lottery_record') ->where('uniacid', $appletid) ->where('aid', $aid) ->where($where) ->order('createtime desc') ->select();
            
            $page = $record ->render();
            $records = $record ->toArray();
            Session::set('excel_records', $excel_records); 
            foreach ($records['data'] as $k => &$v) {
                $user = Db::name('wd_xcx_superuser') ->where('uniacid', $appletid) ->where('id', $v['suid']) ->find();
                $v['realname'] = $user['truename'] ? $user['truename'] : '暂无';
                $v['mobile'] = $user['phone'] ? $user['phone'] : '暂无';
                $v['address'] = $user['address'] ? $user['address'] : '暂无';
                $v['createtime'] = date("Y-m-d H:i:s", $v['createtime']);
                if($v['status'] != '0'){
                    $prize = Db::name('wd_xcx_lottery_prize') ->where('uniacid', $appletid) ->where('id', $v['pid']) ->find();
                    $v['types'] = $prize['types'];
                    if($prize['types'] == '1'){
                        $v['prize_detail'] = $prize['detail'] . '积分';
                    }else if($prize['types'] == '2'){
                        $v['prize_detail'] = $prize['detail'] . '元';
                    }else if($prize['types'] == '4'){
                        $title = Db::name('wd_xcx_coupon') ->where('uniacid', $appletid) ->where('id', $prize['detail']) ->field('title') ->find();
                        $v['prize_detail'] = $title['title'];
                    }else{
                        $v['prize_detail'] = $prize['detail'];
                    }
                }
            }
            $this ->assign('prizesSet', $prizesSet);
            $this ->assign('records', $records);
            $this ->assign('page', $page);
            return $this->fetch('record');
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
    }
    //导出excel
    public function toExcel(){
        $aid = input('aid');
        $uniacid = input('appletid');
        $excel_records = Session::get('excel_records');
        require_once ROOT_PATH.'public/plugin/PHPExcel/PHPExcel.php';
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()->setCreator("导出抽奖记录")
                ->setLastModifiedBy("抽奖记录")
                ->setTitle("导出抽奖记录")
                ->setSubject("导出抽奖记录")
                ->setDescription("导出抽奖记录")
                ->setKeywords("导出抽奖记录")
                ->setCategory("导出抽奖记录");
        $objPHPExcel->getActiveSheet()->setCellValue('A1', '抽奖人姓名');
        $objPHPExcel->getActiveSheet()->setCellValue('B1', '手机号');
        $objPHPExcel->getActiveSheet()->setCellValue('C1', '地址');
        $objPHPExcel->getActiveSheet()->setCellValue('D1', '中奖状态');
        $objPHPExcel->getActiveSheet()->setCellValue('E1', '中奖奖品');
        $objPHPExcel->getActiveSheet()->setCellValue('F1', '抽奖时间');
        
        foreach($excel_records as $k => $v){
            $num=$k+2;
            $user = Db::name('wd_xcx_superuser') ->where('uniacid', $uniacid) ->where('id', $v['suid']) ->find();
            switch ($v['status']) {
                case '0':
                    $status = '未中奖';
                    break;
                
                case '1':
                    $status = '待领取';
                    break;
                case '2':
                    $status = '已领取';
                    break;
            }
            
            $content = "";
            if($v['status'] != '0'){
                $prize = Db::name('wd_xcx_lottery_prize') ->where('uniacid', $uniacid) ->where('id', $v['pid']) ->find();
                if($prize['types'] == '1'){
                    $content = $prize['detail'] . '积分';
                }else if($prize['types'] == '2'){
                    $content = $prize['detail'] . '元';
                }else if($prize['types'] == '4'){
                    $title = Db::name('wd_xcx_coupon') ->where('uniacid', $uniacid) ->where('id', $prize['detail']) ->field('title') ->find();
                    $content = $title['title'];
                }else{
                    $content = $prize['detail'];
                }
            }
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$num, $user['truename'],'s');
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$num, $user['phone'],'s');
            $objPHPExcel->getActiveSheet()->setCellValue('C'.$num, $user['address'],'s');
            $objPHPExcel->getActiveSheet()->setCellValue('D'.$num, $status,'s');
            $objPHPExcel->getActiveSheet()->setCellValue('E'.$num, $content, 's');
            $objPHPExcel->getActiveSheet()->setCellValue('F'.$num, date("Y-m-d H:i:s",$v['createtime']),'s');
        }
        $objPHPExcel->getActiveSheet()->setTitle('导出抽奖信息');
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        
        header('Content-Disposition: attachment;filename="抽奖信息导出表.xls"');
        
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
    //审核发货
    public function shenhe(){
        $uniacid = input('appletid');
        $rid = input('rid');
        $shenhe = array(
            'status' => 2
        );
        $r = Db::name('wd_xcx_lottery_record') ->where('uniacid', $uniacid) ->where('id', $rid) ->update($shenhe);
        if($r){
            return 1;
        }else{
            return 2;
        }
    }
    //积分规则
    public function jfrule(){
        if(powerget()){
            $appid = input('appletid');
            $applet = Db::name('wd_xcx_applet') ->where('id', $appid) ->find();
            $this->assign('applet', $applet);
            $rules = Db::name('wd_xcx_score_get')->where('uniacid', $appid) ->paginate(10, false, ['query' => ['appletid' => $appid]]);
            if(!$rules){
                $data[] = [
                        'title' => '签到获取积分',
                        'descp' => '点击进入签到页面，每日签到都可增加积分，快去试试吧！',
                        'score' => '10',
                        'link' => '/sudu8_page_plugin_sign/index/index',
                        'flag' => 1,
                        'fixed' => 1,
                        'uniacid' => $appid,
                ];
                $data[] = [
                        'title' => '充值送积分',
                        'descp' => '充值不仅送积分，还会送余额和优惠券哦！',
                        'score' => '10',
                        'link' => '/sudu8_page/recharge/recharge',
                        'flag' => 1,
                        'fixed' => 2,
                        'uniacid' => $appid,
                ];
                $data[] = [
                        'title' => '分享文章送积分',
                        'descp' => '分享文章页面给好友，马上可增加积分，好友点击还会获得更多积分，快去转发吧！',
                        'score' => '10',
                        'link' => '',
                        'flag' => 1,
                        'fixed' => 3,
                        'uniacid' => $appid,
                ];
                 $data[] = [
                        'title' => '购买送积分',
                        'descp' => '购买商品后可以获得一定比例的积分！',
                        'score' => '10',
                        'link' => '',
                        'flag' => 1,
                        'fixed' => 4,
                        'uniacid' => $appid,
                ];
                Db::name('wd_xcx_score_get') ->insertAll($data);
                $rules = Db::name('wd_xcx_score_get')->where('uniacid', $appid) ->paginate(10, false, ['query' => ['appletid' => $appid]]);
            }

            $this->assign('rules_list', $rules);
            $this->assign('rules', $rules->toArray()['data']);
        
            return $this->fetch('jfrule');
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
    }
    //添加积分规则
    public function addrule(){
         if(powerget()){
            $appid = input('appletid');
            $applet = Db::name('wd_xcx_applet') ->where('id', $appid) ->find();
            $this->assign('applet', $applet);
           
        
            return $this->fetch('addrule');
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
    }
    //保存添加规则
    public function saverule(){
        $uniacid = input('appletid');
        $data['uniacid'] = $uniacid;
        $title = input('title');
        if($title){
            $data['title'] = $title;
        }else{
            $this->error('请填写积分规则!');
        }
        $descp = input('descp');
        if($descp){
            $data['descp'] = $descp;
        }else{
            $this->error('请填写积分简介!');
        }
        $score = input('score');
        if($score !== null ){
            $data['score'] = $score;
        }else{
            $this->error('请填写积分数!');
        }
        $link = input('link');
        if($link){
            $data['link'] = $link;
        }else{
            $this->error('请填写链接!');
        }
        $data['flag'] = input('flag');
        $res = Db::name('wd_xcx_score_get') ->insert($data);
        if($res){
            $this->success('积分规则添加成功!', Url('shake/jfrule').'?appletid='.$uniacid);
        }else{
            $this->error('发送未知错误,添加失败!');
        }
    }
    //删除积分规则
    public function delrule(){
        $uniacid = input('appletid');
        $id = input('id');
        $res = Db::name('wd_xcx_score_get') ->where('uniacid', $uniacid) ->where('id', $id) ->delete(); 
        if($res){
            $this->success('删除成功!');
        }else{
            $this ->error('删除失败!');
        }
    }
    //编辑积分规则
    public function editrule(){
        $uniacid = input('appletid');
        $id = input('id');
        $applet = Db::name('wd_xcx_applet') ->where('id', $uniacid) ->find();
        $this->assign('applet', $applet);
        $rule = Db::name('wd_xcx_score_get') ->where('uniacid', $uniacid) ->where('id', $id) ->find();
        $this->assign('rule', $rule);
        return $this ->fetch('editrule');
    }
    //保存编辑
    public function save_editrule(){
        $uniacid = input('appletid');
        $id = input('id');
        $title = input('title');
        if($title){
            $data['title'] = $title;
        }else{
            $this->error('请填写积分规则!');
        }
        $descp = input('descp');
        if($descp){
            $data['descp'] = $descp;
        }else{
            $this->error('请填写积分简介!');
        }
        $score = input('score');
        if($score !== null){
            $data['score'] = $score;
        }else{
            $this->error('请填写积分数!');
        }
        $link = input('link');
        if($link){
            $data['link'] = $link;
        }else{
            $this->error('请填写链接!');
        }
        $data['flag'] = input('flag');
        $res = Db::name('wd_xcx_score_get') ->where('uniacid', $uniacid) ->where('id', $id) ->update($data);
        if($res !== false){
            $this->success('编辑成功!', Url('shake/jfrule').'?appletid='.$uniacid);
        }else{
            $this->error('编辑失败!');
        }
    }
}