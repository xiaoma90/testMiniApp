<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;
class Wxuser extends Base
{
    public function index(){
        if(check_login()){
            if(powerget()){
                $id = input("appletid");

                $vip = input('vip')?input('vip'):0;
                $user_info = input('user_info');
                
                $res = Db::table('applet')->where("id",$id)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);

                $grade_arr = Db::table('ims_sudu8_page_vipgrade')->where('uniacid', $id)->order('grade asc')->select();

                $where = '';

                if($user_info && $vip){
                    if($vip == 'isvip'){
                        $where = " vipid is not null and (nickname like '%".$user_info."%' or mobile like '%".$user_info."%')";
                    }else if($vip == 'notvip'){
                        $where = " vipid is null and (nickname like '%".$user_info."%' or mobile like '%".$user_info."%')";
                    }else{
                        $where = "nickname like '%".$user_info."%' or mobile like '%".$user_info."%'";
                    }

                    if($vip > 0){
                        $where .= ' vipid is not null and vipid != "" and grade = '.$vip." and (nickname like '%".$user_info."%' or mobile like '%".$user_info."%')";
                    }
                }else if(!$user_info && $vip){
                    if($vip == 'isvip'){
                        $where = " vipid is not null";
                    }else if($vip == 'notvip'){
                        $where = " vipid is null";
                    }
                    if($vip > 0){
                        $where .= ' vipid is not null and vipid != "" and grade = '.$vip;
                    }
                }
                $grade = input('grade');
                if(intval($grade) > 0){
                    $where .= " grade = {$grade}";
                }

                $user = Db::table('ims_sudu8_page_user')->where("uniacid",$id)-> where($where) ->order('id desc')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"), 'vip' => $vip)]);
                $newuser = $user->toArray();
                foreach ($newuser['data'] as $k => &$row) {
                    foreach ($grade_arr as $key => $value) {
                        $newuser['data'][$k]['vipname'] = '';
                        if($row['grade'] > 0 && $row['grade'] == $value['grade']){
                            $newuser['data'][$k]['vipname'] = $value['name'];
                            break;
                        }
                    }
                    $row['realpay'] = round($row['allpay'] + $row['virtualpay'], 2);                    
                    $orders =  Db::table('ims_sudu8_page_order')->where("uniacid",$id)->where("openid",$row['openid'])->count();
                    $row['orders'] = $orders;
                    $row['createtime'] = $row['vipcreatetime']? date("Y-m-d H:i:s",$row['vipcreatetime']) : '未注册';
                    $row2 = Db::table('ims_sudu8_page_coupon_user')->where("uniacid",$id)->where("flag",0)->where("uid",$row['id'])->count();
                    $row['coupon'] =$row2;
                    if(!$row['mobile'] || $row['mobile']==""){
                        $row['mobile'] = "暂未获取到该用户手机号";
                    }
                    $row['nickname'] = rawurldecode($row['nickname']);
                }
                $count = Db::table('ims_sudu8_page_user')->where("uniacid",$id)-> where($where)->order('id desc')->count();
                $this->assign('user',$newuser);
                $this->assign('userold',$user);
                $this->assign('counts',$count);
                $this->assign('grade_arr',$grade_arr);
                $this->assign('vip',$vip);
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
    public function delete(){
        $uniacid = input("appletid");
        $id = input("id");
        $user = Db::table("ims_sudu8_page_user")->where("uniacid",$uniacid)->where("id",$id) ->find();
        //删除用户表记录
        $res = Db::table("ims_sudu8_page_user")->where("uniacid",$uniacid)->where("id",$id)->delete();
        //删除用户分销商申请记录
        $sq = Db::table('ims_sudu8_page_fx_sq')->where("uniacid", $uniacid)->where('openid', $user['openid'])->delete();
        //删除用户会员申请记录
        if($user['vipid']){
            Db::table('ims_sudu8_page_vip_apply') ->where('uniacid', $uniacid) ->where('openid', $user['openid']) ->delete();
        }

        if($res){
            $this->success("删除成功！");
        }else{
            $this->error("删除失败！");
        }
    }
    public function cz(){
        $uniacid = input("appletid");
        $type = input("type")?input("type"):1;
        $id = input("id");
        $res = Db::table('applet')->where("id",$uniacid)->find();
        if(!$res){
            $this->error("找不到对应的小程序！");
        }
        $this->assign('applet',$res);
        $user = Db::table('ims_sudu8_page_user')->where("uniacid",$uniacid)->where("id",$id)->find();
        $user['nickname'] = rawurldecode($user['nickname']);
        $user['createtime'] = date("Y-m-d H:i:s",$user['createtime']);
        if(!$user['mobile'] || $user['mobile']==""){
            $user['mobile'] = "暂未获取到该用户手机号";
        }
        $user['birth'] = strtotime($user['birth']);
        $this->assign('item',$user);
        $this->assign('type',$type);
        $op = input("op");
        if($op == "cz"){
            $types= input("types");
            if($types == 1){
                $czjf_change = input("czjf_change");
                if($czjf_change == 0){
                    $data['score'] = input("scoreNum") + $user['score'];
                }
                if($czjf_change == 1){
                    $score = $user['score'] - input("scoreNum");
                    if($score<0){
                        $data['score'] = 0;
                    }else{
                        $data['score'] = $score;
                    }
                }
                if($czjf_change == 2){
                   $data['score'] = input("scoreNum"); 
                }
                if(input("scoreNum") != $user['score']){
                    $score_data = array(
                        "uniacid" => $uniacid,
                        "uid" => $id,
                        "type" => "add",
                        "score" => input("scoreNum"),
                        "creattime" => time()
                    );
                    if($czjf_change == '0'){
                        $score_data['message'] = '后台增加积分';
                    }else if($czjf_change == '1'){
                        $score_data['message'] = '后台减少积分';
                    }else{
                        $score_data['message'] = '后台最终积分';
                    }
                    Db::table('ims_sudu8_page_score')->insert($score_data);
                }
            }
            if($types == 2){
                $czye_change = input("czye_change");
                if($czye_change == 0){
                    $data['money'] = input("yueNum") + $user['money'];
                }
                if($czye_change == 1){
                    $data['money'] = $user['money'] - input("yueNum");
                    if($data['money'] < 0){
                        $data['money'] = 0;
                    }
                }
                if($czye_change == 2){
                   $data['money'] = input("yueNum"); 
                }
                if(input("yueNum") != $user['money']){
                    $xfmoney = array(
                        "uniacid" => $uniacid,
                        "uid" => $id,
                        "type" => "add",
                        "score" => input("yueNum"),
                        "creattime" => time()
                    );
                    if($czye_change == '0'){
                        $xfmoney['message'] = '后台增加余额';
                    }else if($czye_change == '1'){
                        $xfmoney['message'] = '后台减少余额';
                    }else{
                        $xfmoney['message'] = '后台最终余额';
                    }
                    Db::table('ims_sudu8_page_money')->insert($xfmoney);
                }
            }
            $res = Db::table("ims_sudu8_page_user")->where("uniacid",$uniacid)->where("id",$id)->update($data);
            if($res){

                $this->success("充值成功");
            }else{
                $this->error("充值失败");
            }
        }
        return $this->fetch('cz');
    }
    public function post(){
        $uniacid = input("appletid");
        $res = Db::table('applet')->where("id",$uniacid)->find();
        if(!$res){
            $this->error("找不到对应的小程序！");
        }
        $this->assign('applet',$res);
        $id = input("id");
        $user = Db::table('ims_sudu8_page_user')->where("uniacid",$uniacid)->where("id",$id)->find();
        if($user['vipcreatetime'] > 0){
            $user['createtime'] = date("Y-m-d H:i:s",$user['vipcreatetime']);
        }else{
            $user['createtime'] = "未注册会员";
        }
        if(!$user['mobile'] || $user['mobile']==""){
            $user['mobile'] = "暂未获取到该用户手机号";
        }
        $user['nickname'] = rawurldecode($user['nickname']);
        $user['birth'] = strtotime($user['birth']);
        $user['realpay'] = round($user['allpay'] + $user['virtualpay'], 2); 
        $this->assign('item',$user);
        $op = input("op");
        if($op == 'save'){
            $realname = input('realname');
            $mobile = input('mobile');
            $birth = input('birth');
            $virtualpay = input('virtualpay');
            $realpay = round($user['allpay'] + $virtualpay, 2);
            if($realpay < 0){
                $data['virtualpay'] = -$user['allpay'];
            }else{
                $data['virtualpay'] = $virtualpay;
            }

            

            if(empty($realname)){
                $this->error("真实姓名不能为空！");
                exit;
            }else{
                $data['realname'] = $realname;
            }
            if(empty($mobile)){
                $this->error("手机号不能为空");
                exit;
            }else{
                $data['mobile'] = $mobile;
            }
            if(empty($birth)){
                $this->error("生日不能为空！");
                exit;
            }else{
                $data['birth'] = $birth;
            }

            $result = Db::table('ims_sudu8_page_user')->where("uniacid",$uniacid)->where("id",$id)->update($data);
            if($user['vipid']){ //当为会员时判断会员等级
                check_vip_grade($uniacid, $user['openid']);
            }
            if($result){
                $this->success("修改成功！");
            }else{
                $this->error("修改失败！");
                exit;
            }
        }
        return $this->fetch('post');
    }
 

    public function moneyturnove(){
        if(check_login()){
            if(powerget()){
                $id = input("appletid");
                $res = Db::table('applet')->where("id",$id)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $op=input("op");
                if($op == "display"){
                    $count = Db::table('ims_sudu8_page_money')->alias("a")->join("ims_sudu8_page_user b","a.uid = b.id")->where("a.uniacid",$id)->count();
                    $scorelist = Db::table('ims_sudu8_page_money')->alias("a")->join("ims_sudu8_page_user b","a.uid = b.id")->where("a.uniacid",$id)->order('a.creattime desc')->field("a.*,b.avatar,b.nickname")->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"),"op" => "display")]);
                    
                }
                if($op=="get"){
                    $count = Db::table('ims_sudu8_page_money')->alias("a")->join("ims_sudu8_page_user b","a.uid = b.id")->where("a.uniacid",$id)->where("a.score",'gt',0)->where("a.type",'add')->order('a.creattime desc')->field("a.*,b.avatar,b.nickname")->count();
                    $scorelist = Db::table('ims_sudu8_page_money')->alias("a")->join("ims_sudu8_page_user b","a.uid = b.id")->where("a.uniacid",$id)->where("a.score",'gt',0)->where("a.type",'add')->order('a.creattime desc')->field("a.*,b.avatar,b.nickname")->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"),"op" => "get")]);
                }
                if($op=="spend"){
                    $count = Db::table('ims_sudu8_page_money')->alias("a")->join("ims_sudu8_page_user b","a.uid = b.id")->where("a.uniacid",$id)->where("a.score",'gt',0)->where("a.type",'del')->where("a.message",'消费')->count();
                    $scorelist = Db::table('ims_sudu8_page_money')->alias("a")->join("ims_sudu8_page_user b","a.uid = b.id")->where("a.uniacid",$id)->where("a.score",'gt',0)->where("a.type",'del')->where("a.message",'消费')->order('a.creattime desc')->field("a.*,b.avatar,b.nickname")->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"),"op" => "spend")]);
                }
                if($op=="store"){
                    $count = Db::table('ims_sudu8_page_money')->alias("a")->join("ims_sudu8_page_user b","a.uid = b.id")->where("a.uniacid",$id)->where("a.type",'del')->where("a.orderid",1001)->count();
                    $scorelist = Db::table('ims_sudu8_page_money')->alias("a")->join("ims_sudu8_page_user b","a.uid = b.id")->where("a.uniacid",$id)->where("a.type",'del')->where("a.orderid",1001)->order('a.creattime desc')->field("a.*,b.avatar,b.nickname")->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"),"op" => "store")]);
                }
                if($op=="forum"){
                    $msgarr = ['评论插件信息发布','论坛信息发布'];
                    $count = Db::table('ims_sudu8_page_money')->alias("a")->join("ims_sudu8_page_user b","a.uid = b.id")->where("a.uniacid",$id)->where("a.score",'gt',0)->where("a.type",'del')->where("a.message", 'in', $msgarr)->count();
                    $scorelist = Db::table('ims_sudu8_page_money')->alias("a")->join("ims_sudu8_page_user b","a.uid = b.id")->where("a.uniacid",$id)->where("a.score",'gt',0)->where("a.type",'del')->where("a.message", 'in', $msgarr)->order('a.creattime desc')->field("a.*,b.avatar,b.nickname")->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"),"op" => "forum")]);
                }
                
                $list = $scorelist ->toArray();
                if(count($list['data']) > 0){
                    foreach ($list['data'] as $key => &$value) {
                        $value['nickname'] = rawurldecode($value['nickname']);
                    }
                }
                $this->assign('scorelist',$list['data']);
                $this->assign('counts',$count);
                $this->assign('page', $scorelist->render());
                $this->assign('op',$op);
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
            return $this->fetch('moneyturnove');
        }else{
            $this->redirect('Login/index');
        }
    }
    public function scoreturnove(){
        if(check_login()){
            if(powerget()){
                $id = input("appletid");
                $res = Db::table('applet')->where("id",$id)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $op=input("op");
                if($op == "display"){
                    $count = Db::table('ims_sudu8_page_score')->alias("a")->join("ims_sudu8_page_user b","a.uid = b.id")->where("a.uniacid",$id)->count();
                    $scorelist = Db::table('ims_sudu8_page_score')->alias("a")->join("ims_sudu8_page_user b","a.uid = b.id")->where("a.uniacid",$id)->order('a.creattime desc')->field("a.*,b.avatar,b.nickname")->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"),"op" => "display")]);
                }
                if($op=="cz"){
                    $count = Db::table('ims_sudu8_page_score')->alias("a")->join("ims_sudu8_page_user b","a.uid = b.id")->where("a.uniacid",$id)->where("a.score",'gt',0)->where("a.message",'充值送积分')->order('a.creattime desc')->field("a.*,b.avatar,b.nickname")->count();
                    $scorelist = Db::table('ims_sudu8_page_score')->alias("a")->join("ims_sudu8_page_user b","a.uid = b.id")->where("a.uniacid",$id)->where("a.score",'gt',0)->where("a.message",'充值送积分')->order('a.creattime desc')->field("a.*,b.avatar,b.nickname")->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"),"op" => "cz")]);
                }
                if($op=="xf"){
                    $count = Db::table('ims_sudu8_page_score')->alias("a")->join("ims_sudu8_page_user b","a.uid = b.id")->where("a.uniacid",$id)->where("a.score",'gt',0)->where("a.message",'消费')->count();
                    $scorelist = Db::table('ims_sudu8_page_score')->alias("a")->join("ims_sudu8_page_user b","a.uid = b.id")->where("a.uniacid",$id)->where("a.score",'gt',0)->where("a.message",'消费')->order('a.creattime desc')->field("a.*,b.avatar,b.nickname")->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"),"op" => "xf")]);
                }
                if($op=="qd"){
                    $count = Db::table('ims_sudu8_page_score')->alias("a")->join("ims_sudu8_page_user b","a.uid = b.id")->where("a.uniacid",$id)->where("a.score",'gt',0)->where("a.message",'签到增加积分')->count();
                    $scorelist = Db::table('ims_sudu8_page_score')->alias("a")->join("ims_sudu8_page_user b","a.uid = b.id")->where("a.uniacid",$id)->where("a.score",'gt',0)->where("a.message",'签到增加积分')->order('a.creattime desc')->field("a.*,b.avatar,b.nickname")->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"),"op" => "qd")]);
                }
                if($op=="fx"){
                    $count = Db::table('ims_sudu8_page_score')->alias("a")->join("ims_sudu8_page_user b","a.uid = b.id")->where("a.uniacid",$id)->where("a.score",'gt',0)->where("a.message", 'like','%分享%')->count();
                    $scorelist = Db::table('ims_sudu8_page_score')->alias("a")->join("ims_sudu8_page_user b","a.uid = b.id")->where("a.uniacid",$id)->where("a.score",'gt',0)->where("a.message",'like','%分享%')->order('a.creattime desc')->field("a.*,b.avatar,b.nickname")->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"),"op" => "fx")]);
                }
                if($op=="store"){
                    $count = Db::table('ims_sudu8_page_score')->alias("a")->join("ims_sudu8_page_user b","a.uid = b.id")->where("a.uniacid",$id)->where("a.orderid",1001)->count();
                    $scorelist = Db::table('ims_sudu8_page_score')->alias("a")->join("ims_sudu8_page_user b","a.uid = b.id")->where("a.uniacid",$id)->where("a.orderid",1001)->order('a.creattime desc')->field("a.*,b.avatar,b.nickname")->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"),"op" => "fx")]);
                }
                $list = $scorelist ->toArray();
                if(count($list['data']) > 0){
                    foreach ($list['data'] as $key => &$value) {
                        $value['nickname'] = rawurldecode($value['nickname']);
                    }
                }


                $this->assign('scorelist',$list['data']);
                $this->assign('counts',$count);
                $this->assign('page', $scorelist->render());
                $this->assign('op',$op);
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
            return $this->fetch('scoreturnove');
        }else{
            $this->redirect('Login/index');
        }
    }
    public function registerrecord(){
        if(check_login()){
            if(powerget()){
                $id = input("appletid");
                $res = Db::table('applet')->where("id",$id)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $list = Db::table('ims_sudu8_page_user')->where("uniacid",$id)->where("vipid",'gt',0)->order('vipcreatetime desc')->field("nickname,vipid,vipcreatetime,avatar")->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                $count = Db::table('ims_sudu8_page_user')->where("uniacid",$id)->where("vipid",'gt',0)->order('vipcreatetime desc')->field("nickname,vipid,vipcreatetime,avatar")->count();
                $users = $list -> toArray();
                foreach ($users['data'] as $key => &$value) {
                    $value['nickname'] = rawurldecode($value['nickname']);
                }
                $this->assign('list',$users['data']);
                $this->assign('page', $list->render());
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
            return $this->fetch('registerrecord');
        }else{
            $this->redirect('Login/index');
        }
    }
    public function apply(){
        if(check_login()){
            if(powerget()){
                $id = input("appletid");
                $res = Db::table('applet')->where("id",$id)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $list = Db::table('ims_sudu8_page_vip_apply')->alias('a')->join('ims_sudu8_page_user b', 'a.openid = b.openid')->where("a.uniacid", $id)->where("b.uniacid", $id)->order("a.id desc")->field("a.*,b.nickname,b.avatar")->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                $count = Db::table('ims_sudu8_page_vip_apply')->alias('a')->join('ims_sudu8_page_user b', 'a.openid = b.openid')->where("a.uniacid", $id)->where("b.uniacid", $id)->count();
                $users = $list -> toArray();
                foreach ($users['data'] as $key => &$value) {
                    $value['nickname'] = rawurldecode($value['nickname']);
                }
                $this->assign('list',$users['data']);
                $this->assign('counts',$count);
                $this->assign('page', $list->render());
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
            return $this->fetch('apply');
        }else{
            $this->redirect('Login/index');
        }
    }
    public function applydel(){
        $id = input("appletid");
        $res = Db::table('applet')->where("id",$id)->find();
        if(!$res){
            $this->error("找不到对应的小程序！");
        }
        $this->assign('applet',$res);
        $newsid = intval(input('newsid'));
        $res = Db::table("ims_sudu8_page_vip_apply")->where("uniacid",$id)->where("id",$newsid)->delete();
        if($res){
            $this->success("删除成功");
        }else{
            $this->error("删除失败");
        }    
    }
    public function applyinfo(){
        $id = input("appletid");
        $res = Db::table('applet')->where("id",$id)->find();
        if(!$res){
            $this->error("找不到对应的小程序！");
        }
        $this->assign('applet',$res);
        $newsid = intval(input('newsid'));
        $item = Db::table("ims_sudu8_page_vip_apply")->alias("a")->join("ims_sudu8_page_user b","a.openid = b.openid")->where("a.id", $newsid)->where("a.uniacid", $id)->where("b.uniacid", $id)->field("a.*,b.nickname,b.avatar,b.realname,b.birth,b.address,b.mobile")->find();
        $forminfo = Db::table("ims_sudu8_page_formcon")->alias('a')->join("ims_sudu8_page_formlist b", "a.fid = b.id")->where("a.id", $item['fid'])->where("a.uniacid", $id)->field("a.*,b.formname as title")->find();
        if($forminfo){
            if(isset($forminfo['val'])){
                $forminfo['val'] = unserialize($forminfo['val']);
            }else{
                $forminfo['val'] = "";
            }
        }
        $this->assign("item", $item);
        $this->assign("forminfo", $forminfo);
        return $this->fetch("applyinfo");
    }
    public function shenhe(){
        $id = intval(input('newsid'));
        $uniacid = input("appletid");
        $row = Db::table("ims_sudu8_page_vip_apply")->where("id", $id)->where("uniacid", $uniacid)->find();
        if (!$row) {
            $this->error("申请不存在或是已经被删除！");
            exit;
        }
        $flag = intval(input('flag'));
        $examinetime = date("Y-m-d H:i:s", time());
        $beizhu = "";
        $jieguo = "";
        if($flag == 2){
            $jieguo = "会员卡申请审核不通过";
            $beizhu = input('beizhu');
        }
   
        $res = Db::table('ims_sudu8_page_vip_apply')->where("id", $id)->where("uniacid", $uniacid)->update(array("flag" => $flag, "examinetime" => $examinetime, "beizhu" => $beizhu));
        if($flag == 1){
            $jieguo = "会员卡申请审核通过";
            $result1 = Db::table('ims_sudu8_page_vipgrade')->where('uniacid', $uniacid)->where('grade', 1)->find();
            if($result1){
                $userinfo = Db::table('ims_sudu8_page_user')->where('uniacid', $uniacid)->where('openid', $row['openid'])->find();
                $receive = [];
                $receive['vid'] = $result1['id'];
                $receive['uniacid'] = $uniacid;
                $score = $userinfo['score'];
                if($result1['score_feedback_flag'] == 1){
                    if($result1['score_feedback'] > 0){
                        $receive['score'] = $result1['score_feedback'];
                        $score = $userinfo['score'] + $result1['score_feedback'];
                        $score_data = array(
                            "uniacid" => $uniacid,
                            "orderid" => '',
                            "uid" => $userinfo['id'],
                            "type" => "add",
                            "score" => $result1['score_feedback'],
                            "message" => "会员等级回馈积分",
                            "creattime" => time()
                        );
                        Db::table('ims_sudu8_page_score')->insert($score_data);
            
                    }
                }
                if($result1['coupon_flag'] == 1){
                    $coupon_give = unserialize($result1['coupon_give']);
                    if(count($coupon_give) > 0){
                        $receive['coupon'] = $result1['coupon_give'];
                        foreach ($coupon_give as $k => $v) {
                            $coup_info = [];
                            for($i = 0;$i<$v['coupon_num'];$i++){
                                $coup = [];
                                $cid = $v['coupon_id'];
                                if(count($coup_info) == 0){
                                    $coup_info = Db::table('ims_sudu8_page_coupon')->where('uniacid', $uniacid)->where('id', $cid)->find();
                                }

                                $coup['uniacid'] = $uniacid;
                                $coup['uid'] = $userinfo['id'];
                                $coup['cid'] = $cid;
                                $coup['btime'] = $coup_info['btime'];
                                $coup['etime'] = $coup_info['etime'];
                                $coup['ltime'] = time();
                                Db::table('ims_sudu8_page_coupon_user')->insert($coup);

                            }
                        }
                    }
                }
                $receive['openid'] = $row['openid'];
                Db::table('ims_sudu8_page_vip_receive')->insert($receive);
            }
            Db::table('ims_sudu8_page_user')->where("openid", $row['openid'])->where("uniacid", $uniacid)->update(array("vipid" => $row['vipid'], "vipcreatetime" => time(),'grade' => 1,'score' => $score));
        }
        $applet = Db::table('applet')->where("id",$uniacid)->find();
        $appid = $applet['appID'];
        $appsecret = $applet['appSecret'];
        if($applet)
        {
        $mid = Db::table('ims_sudu8_page_message')->where("uniacid",$uniacid)->where("flag", 12)->find();
            if($mid)
            {
                if($mid['mid']!="")
                {
                    $mids = $mid['mid'];
                    $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$appsecret;
                    $a_token = $this->_requestGetcurl($url);
                    if($a_token)
                    {
                        $url_m="https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=".$a_token['access_token'];
                        $formId=$row['formid'];
                        $applytime = $row['applytime'];
                        $openid=$row['openid'];
                        $furl = $mid['url'];
                        $post_info = '{
                                  "touser": "'.$openid.'",  
                                  "template_id": "'.$mids.'", 
                                  "page": "'.$furl.'",          
                                  "form_id": "'.$formId.'",         
                                  "data": {
                                      "keyword1": {
                                          "value": "'.$jieguo.'", 
                                          "color": "#173177"
                                      },
                                      "keyword2": {
                                          "value": "'.$applytime.'", 
                                          "color": "#173177"
                                      },
                                      "keyword3": {
                                          "value": "'.$examinetime.'", 
                                          "color": "#173177"
                                      }
                                  },
                                  "emphasis_keyword": "" 
                                }';
                        $this->_requestPost($url_m,$post_info);
                    }
                }
            }
        }
        $this->success("审核成功");
    }
    function _requestGetcurl($url){
        //curl完成  
        $curl = curl_init();  
        //设置curl选项  
        $header = array(  
            "authorization: Basic YS1sNjI5dmwtZ3Nocmt1eGI2Njp1TlQhQVFnISlWNlkySkBxWlQ=",
            "content-type: application/json",
            "cache-control: no-cache",
            "postman-token: cd81259b-e5f8-d64b-a408-1270184387ca" 
        );
        curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER  , $header); 
        curl_setopt($curl, CURLOPT_URL, $url);//URL  
        curl_setopt($curl, CURLOPT_HEADER, 0);             // 0：不返回头信息
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);   
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);//设置超时时间  
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); 
        //强制IPv4
        curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        // 发出请求  
        $response = curl_exec($curl);
        if (false === $response) {  
            echo '<br>', curl_error($curl), '<br>';  
            return false;  
        }  
        curl_close($curl);  
        $forms = stripslashes(html_entity_decode($response));
        $forms = json_decode($forms,TRUE);
        return $forms;  
    }
                //不带报头的curl
    function _requestPost($url, $data, $ssl=true) {  
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
            //强制IPv4
            curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            // 发出请求  
            $response = curl_exec($curl);
            if (false === $response) {  
                echo '<br>', curl_error($curl), '<br>';  
                return false;  
            }  
            curl_close($curl);  
            return $response;  
    }
    
}