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

                $vip = input('vip')?input('vip'):'all';
                $user_info = input('user_info');
                
                $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);

                $grade_arr = Db::name('wd_xcx_vipgrade')->where('uniacid', $id)->order('grade asc')->select();

                $where = '';

                if($user_info && $vip){
                    if($vip == 'isvip'){
                        $where = " vipid is not null and (truename like '%".$user_info."%' or phone like '%".$user_info."%')";
                    }else if($vip == 'notvip'){
                        $where = " vipid is null and (truename like '%".$user_info."%' or phone like '%".$user_info."%')";
                    }else{
                        $where = "truename like '%".$user_info."%' or phone like '%".$user_info."%'";
                    }

                    if($vip > 0){
                        $where .= ' vipid is not null and vipid != "" and grade = '.$vip." and (truename like '%".$user_info."%' or phone like '%".$user_info."%')";
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
                    $vip = $grade;
                    $where .= " grade = {$grade}";
                }

                $user = Db::name('wd_xcx_superuser')->where("uniacid",$id)-> where($where) ->order('id desc')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"), 'vip' => $vip)]);
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
                    $orders =  Db::name('wd_xcx_order')->where("uniacid",$id)->where("suid",$row['id'])->count();
                    $row['orders'] = $orders;
                    $row['createtime'] = date("Y-m-d H:i:s",$row['createtime']);
                    $row['vipcreatetime'] = $row['vipcreatetime']? date("Y-m-d H:i:s",$row['vipcreatetime']) : '未申请';
                    $row2 = Db::name('wd_xcx_coupon_user')->where("uniacid",$id)->where("flag",0)->where("suid",$row['id'])->count();
                    $row['coupon'] =$row2;
                    if(!$row['phone'] || $row['phone']==""){
                        $row['phone'] = "暂未获取到该用户手机号";
                    }
                    $userinfo = getNameAvatar($row['id'], $id);
                    $row['nickname'] = $userinfo['nickname'];
                    $row['avatar'] = $userinfo['avatar'];
                }
                $count = Db::name('wd_xcx_superuser')->where("uniacid",$id)-> where($where)->order('id desc')->count();
                $this->assign('user',$newuser);
                $this->assign('userold',$user);
                $this->assign('counts',$count);
                $this->assign('grade_arr',$grade_arr);
                $this->assign('vip',$vip);
                $this ->bbcc();
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

    private function bbcc(){
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

    public function delete(){
        $uniacid = input("appletid");
        $id = input("id");
        $user = Db::name("wd_xcx_superuser")->where("uniacid",$uniacid)->where("id",$id) ->find();


        //删除用户表记录
        Db::name("wd_xcx_superuser")->where("uniacid",$uniacid)->where("id",$id)->delete();
        $wx_info = Db::name("wd_xcx_user")->where("uniacid",$uniacid)->where("suid",$id) ->find();
        if($wx_info){
            Db::name("wd_xcx_user")->where("uniacid",$uniacid)->where("suid",$id) ->delete();
        }
        $ali_info = Db::name("wd_xcx_ali_user")->where("uniacid",$uniacid)->where("suid",$id) ->find();
        if($ali_info){
            Db::name("wd_xcx_ali_user")->where("uniacid",$uniacid)->where("suid",$id) ->delete();
        }
        $bd_info = Db::name("wd_xcx_baidu_user")->where("uniacid",$uniacid)->where("suid",$id) ->find();
        if($bd_info){
            Db::name("wd_xcx_baidu_user")->where("uniacid",$uniacid)->where("suid",$id) ->delete();
        }

        $bdance_info = Db::name("wd_xcx_toutiao_user")->where("uniacid",$uniacid)->where("suid",$id) ->find();
        if($bdance_info){
            Db::name("wd_xcx_toutiao_user")->where("uniacid",$uniacid)->where("suid",$id) ->delete();
        }

        $qq_info = Db::name("wd_xcx_qq_user")->where("uniacid",$uniacid)->where("suid",$id) ->find();
        if($qq_info){
            Db::name("wd_xcx_qq_user")->where("uniacid",$uniacid)->where("suid",$id) ->delete();
        }

        //删除用户分销商申请记录
        $sq = Db::name('wd_xcx_fx_sq')->where("uniacid", $uniacid)->where('suid', $id)->delete();
        //删除用户会员申请记录
        if($user['vipid']){
            Db::name('wd_xcx_vip_apply') ->where('uniacid', $uniacid) ->where('suid', $id) ->delete();
            Db::name('wd_xcx_vip_receive') ->where('uniacid', $uniacid) ->where('suid', $id) ->delete();
        }


        $this->success("删除成功！");

    }
    public function cz(){
        $uniacid = input("appletid");
        $type = input("type")?input("type"):1;
        $id = input("id");
        $res = Db::name('wd_xcx_applet')->where("id",$uniacid)->find();
        if(!$res){
            $this->error("找不到对应的小程序！");
        }
        $this->assign('applet',$res);
        $user = Db::name('wd_xcx_superuser')->where("uniacid",$uniacid)->where("id",$id)->find();
        $userinfo = getNameAvatar($id, $uniacid);
        $user['nickname'] = $userinfo['nickname'];
        $user['avatar'] = $userinfo['avatar'];
        $user['createtime'] = date("Y-m-d H:i:s",$user['createtime']);
        if(!$user['phone'] || $user['phone']==""){
            $user['phone'] = "暂未获取到该用户手机号";
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
                        "suid" => $id,
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
                    Db::name('wd_xcx_score')->insert($score_data);
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
                        "suid" => $id,
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
                    Db::name('wd_xcx_money')->insert($xfmoney);
                }
            }
            $res = Db::name("wd_xcx_superuser")->where("uniacid",$uniacid)->where("id",$id)->update($data);
            if($res){

                $this->success("充值成功",Url('Wxuser/index').'?appletid='.$uniacid);
            }else{
                $this->error("充值失败", Url('Wxuser/index').'?appletid='.$uniacid);
            }
        }
        return $this->fetch('cz');
    }
    public function post(){
        $uniacid = input("appletid");
        $res = Db::name('wd_xcx_applet')->where("id",$uniacid)->find();
        if(!$res){
            $this->error("找不到对应的小程序！");
        }
        $this->assign('applet',$res);
        $id = input("id");
        $user = Db::name('wd_xcx_superuser')->where("uniacid",$uniacid)->where("id",$id)->find();
        if($user['vipcreatetime'] > 0){
            $user['createtime'] = date("Y-m-d H:i:s",$user['vipcreatetime']);
        }else{
            $user['createtime'] = "未注册会员";
        }
        if(!$user['phone'] || $user['phone']==""){
            $user['phone'] = "暂未获取到该用户手机号";
        }
        $userinfo = getNameAvatar($id, $uniacid);
        $user['nickname'] = $userinfo['nickname'];
        $user['avatar'] = $userinfo['avatar'];
        $user['birth'] = strtotime($user['birth']);
        $user['realpay'] = round($user['allpay'] + $user['virtualpay'], 2); 
        $this->assign('item',$user);
        $op = input("op");
        if($op == 'save'){
            $truename = input('truename');
            $phone = input('phone');
            $birth = input('birth');
            $virtualpay = input('virtualpay');
            $realpay = round($user['allpay'] + $virtualpay, 2);
            if($realpay < 0){
                $data['virtualpay'] = -$user['allpay'];
            }else{
                $data['virtualpay'] = $virtualpay;
            }

            if(empty($truename)){
                $this->error("真实姓名不能为空！");
                exit;
            }else{
                $data['truename'] = $truename;
            }
            if(empty($phone)){
                $this->error("手机号不能为空");
                exit;
            }else{
                $has = Db::name('wd_xcx_superuser') ->where('id', 'neq', $id) ->where('phone', $phone) ->find();
                if($has){
                    $this->error('手机号码已存在，请重新填写！');
                }
                $data['phone'] = $phone;
            }
            if(empty($birth)){
                $this->error("生日不能为空！");
                exit;
            }else{
                $data['birth'] = $birth;
            }

            $result = Db::name('wd_xcx_superuser')->where("uniacid",$uniacid)->where("id",$id)->update($data);
            if($user['vipid']){ //当为会员时判断会员等级
                check_vip_grade($uniacid, $user['id']);
            }
            if($result){
                $this->success("修改成功！", Url('Wxuser/index').'?appletid='.$uniacid);
            }else{
                $this->error("修改失败！", Url('Wxuser/index').'?appletid='.$uniacid);
                exit;
            }
        }
        return $this->fetch('post');
    }
 

    public function moneyturnove(){
        if(check_login()){
            if(powerget()){
                $id = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $op=input("op");
                if($op == "display"){
                    $count = Db::name('wd_xcx_money')->alias("a")->join("wd_xcx_superuser b","a.suid = b.id")->where("a.uniacid",$id)->count();
                    $scorelist = Db::name('wd_xcx_money')->alias("a")->join("wd_xcx_superuser b","a.suid = b.id")->where("a.uniacid",$id)->order('a.creattime desc')->field("a.*,b.id as userid")->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"),"op" => "display")]);
                }
                if($op=="get"){
                    $count = Db::name('wd_xcx_money')->alias("a")->join("wd_xcx_superuser b","a.suid = b.id")->where("a.uniacid",$id)->where("a.score",'gt',0)->where("a.type",'add')->order('a.creattime desc')->count();
                    $scorelist = Db::name('wd_xcx_money')->alias("a")->join("wd_xcx_superuser b","a.suid = b.id")->where("a.uniacid",$id)->where("a.score",'gt',0)->where("a.type",'add')->order('a.creattime desc')->field("a.*,b.id as userid")->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"),"op" => "get")]);
                }
                if($op=="spend"){
                    $count = Db::name('wd_xcx_money')->alias("a")->join("wd_xcx_superuser b","a.suid = b.id")->where("a.uniacid",$id)->where("a.score",'gt',0)->where("a.type",'del')->count();
                    $scorelist = Db::name('wd_xcx_money')->alias("a")->join("wd_xcx_superuser b","a.suid = b.id")->where("a.uniacid",$id)->where("a.score",'gt',0)->where("a.type",'del')->order('a.creattime desc')->field("a.*,b.id as userid")->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"),"op" => "spend")]);
                }
                if($op=="store"){
                    $count = Db::name('wd_xcx_money')->alias("a")->join("wd_xcx_superuser b","a.suid = b.id")->where("a.uniacid",$id)->where("a.type",'del')->where("a.orderid",1001)->count();
                    $scorelist = Db::name('wd_xcx_money')->alias("a")->join("wd_xcx_superuser b","a.suid = b.id")->where("a.uniacid",$id)->where("a.type",'del')->where("a.orderid",1001)->order('a.creattime desc')->field("a.*,b.id as userid")->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"),"op" => "store")]);
                }
                if($op=="forum"){
                    $msgarr = ['评论插件信息发布','论坛信息发布'];
                    $count = Db::name('wd_xcx_money')->alias("a")->join("wd_xcx_superuser b","a.suid = b.id")->where("a.uniacid",$id)->where("a.score",'gt',0)->where("a.type",'del')->where("a.message", 'in', $msgarr)->count();
                    $scorelist = Db::name('wd_xcx_money')->alias("a")->join("wd_xcx_superuser b","a.suid = b.id")->where("a.uniacid",$id)->where("a.score",'gt',0)->where("a.type",'del')->where("a.message", 'in', $msgarr)->order('a.creattime desc')->field("a.*,b.id as userid")->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"),"op" => "forum")]);
                }
                
                $list = $scorelist ->toArray();
                if(count($list['data']) > 0){
                    foreach ($list['data'] as $key => &$value) {
                        $userinfo = getNameAvatar($value['userid'], $id);
                        $value['nickname'] = $userinfo['nickname'];
                        $value['avatar'] = $userinfo['avatar'];
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
                $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $op=input("op");
                if($op == "display"){
                    $count = Db::name('wd_xcx_score')->alias("a")->join("wd_xcx_superuser b","a.suid = b.id")->where("a.uniacid",$id)->count();
                    $scorelist = Db::name('wd_xcx_score')->alias("a")->join("wd_xcx_superuser b","a.suid = b.id")->where("a.uniacid",$id)->order('a.creattime desc')->field("a.*,b.id as userid")->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"),"op" => "display")]);
                }
                if($op=="cz"){
                    $count = Db::name('wd_xcx_score')->alias("a")->join("wd_xcx_superuser b","a.suid = b.id")->where("a.uniacid",$id)->where("a.score",'gt',0)->where("a.message",'充值送积分')->order('a.creattime desc')->field("a.*,b.id as userid")->count();
                    $scorelist = Db::name('wd_xcx_score')->alias("a")->join("wd_xcx_superuser b","a.suid = b.id")->where("a.uniacid",$id)->where("a.score",'gt',0)->where("a.message",'充值送积分')->order('a.creattime desc')->field("a.*,b.id as userid")->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"),"op" => "cz")]);
                }
                if($op=="get"){
                    $count = Db::name('wd_xcx_score')->alias("a")->join("wd_xcx_superuser b","a.suid = b.id")->where("a.uniacid",$id)->where("a.score",'gt',0)->where("a.type",'add')->order('a.creattime desc')->field("a.*,b.id as userid")->count();
                    $scorelist = Db::name('wd_xcx_score')->alias("a")->join("wd_xcx_superuser b","a.suid = b.id")->where("a.uniacid",$id)->where("a.score",'gt',0)->where("a.type",'add')->order('a.creattime desc')->field("a.*,b.id as userid")->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"),"op" => "get")]);
                }
                if($op=="xf"){
                    $count = Db::name('wd_xcx_score')->alias("a")->join("wd_xcx_superuser b","a.suid = b.id")->where("a.uniacid",$id)->where("a.score",'gt',0)->where("a.type",'del')->count();
                    $scorelist = Db::name('wd_xcx_score')->alias("a")->join("wd_xcx_superuser b","a.suid = b.id")->where("a.uniacid",$id)->where("a.score",'gt',0)->where("a.type",'del')->order('a.creattime desc')->field("a.*,b.id as userid")->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"),"op" => "xf")]);
                }
                if($op=="qd"){
                    $count = Db::name('wd_xcx_score')->alias("a")->join("wd_xcx_superuser b","a.suid = b.id")->where("a.uniacid",$id)->where("a.score",'gt',0)->where("a.message",'签到增加积分')->count();
                    $scorelist = Db::name('wd_xcx_score')->alias("a")->join("wd_xcx_superuser b","a.suid = b.id")->where("a.uniacid",$id)->where("a.score",'gt',0)->where("a.message",'签到增加积分')->order('a.creattime desc')->field("a.*,b.id as userid")->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"),"op" => "qd")]);
                }
                if($op=="fx"){
                    $count = Db::name('wd_xcx_score')->alias("a")->join("wd_xcx_superuser b","a.suid = b.id")->where("a.uniacid",$id)->where("a.score",'gt',0)->where("a.message", 'like','%分享%')->count();
                    $scorelist = Db::name('wd_xcx_score')->alias("a")->join("wd_xcx_superuser b","a.suid = b.id")->where("a.uniacid",$id)->where("a.score",'gt',0)->where("a.message",'like','%分享%')->order('a.creattime desc')->field("a.*,b.id as userid")->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"),"op" => "fx")]);
                }
                if($op=="store"){
                    $count = Db::name('wd_xcx_score')->alias("a")->join("wd_xcx_superuser b","a.suid = b.id")->where("a.uniacid",$id)->where("a.orderid",1001)->count();
                    $scorelist = Db::name('wd_xcx_score')->alias("a")->join("wd_xcx_superuser b","a.suid = b.id")->where("a.uniacid",$id)->where("a.orderid",1001)->order('a.creattime desc')->field("a.*,b.id as userid")->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"),"op" => "fx")]);
                }

                $list = $scorelist ->toArray();
                if(count($list['data']) > 0){
                    foreach ($list['data'] as $key => &$value) {
                        $userinfo = getNameAvatar($value['userid'], $id);
                        $value['nickname'] = $userinfo['nickname'];
                        $value['avatar'] = $userinfo['avatar'];
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
                $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $list = Db::name('wd_xcx_superuser')->where("uniacid",$id)->where("vipid",'gt',0)->order('vipcreatetime desc')->field("id,vipid,vipcreatetime")->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                $count = Db::name('wd_xcx_superuser')->where("uniacid",$id)->where("vipid",'gt',0)->field("id")->count();
                $users = $list -> toArray();
                foreach ($users['data'] as $key => &$value) {
                    $userinfo = getNameAvatar($value['id'], $id);
                    $value['nickname'] = $userinfo['nickname'];
                    $value['avatar'] = $userinfo['avatar'];
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
                $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $list = Db::name('wd_xcx_vip_apply')->alias('a')->join('wd_xcx_superuser b', 'a.suid = b.id')->where("a.uniacid", $id)->where("b.uniacid", $id)->order("a.id desc")->field("a.*,b.id as userid")->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                $count = Db::name('wd_xcx_vip_apply')->alias('a')->join('wd_xcx_superuser b', 'a.suid = b.id')->where("a.uniacid", $id)->where("b.uniacid", $id)->count();
                $users = $list -> toArray();
                foreach ($users['data'] as $key => &$value) {
                        $userinfo = getNameAvatar($value['userid'], $id);
                        $value['nickname'] = $userinfo['nickname'];
                        $value['avatar'] = $userinfo['avatar'];
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
        $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
        if(!$res){
            $this->error("找不到对应的小程序！");
        }
        $this->assign('applet',$res);
        $newsid = intval(input('newsid'));
        $res = Db::name("wd_xcx_vip_apply")->where("uniacid",$id)->where("id",$newsid)->delete();
        if($res){
            $this->success("删除成功");
        }else{
            $this->error("删除失败");
        }    
    }
    public function applyinfo(){
        $id = input("appletid");
        $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
        if(!$res){
            $this->error("找不到对应的小程序！");
        }
        $this->assign('applet',$res);
        $newsid = intval(input('newsid'));
        $item = Db::name("wd_xcx_vip_apply")->alias("a")->join("wd_xcx_superuser b","a.suid = b.id")->where("a.id", $newsid)->where("a.uniacid", $id)->where("b.uniacid", $id)->field("a.*,b.id as userid,b.truename as realname,b.birth,b.address,b.phone as mobile")->find();
        $userinfo = getNameAvatar($item['userid'], $id);
        $item['nickname'] = $userinfo['nickname'];
        $item['avatar'] = $userinfo['avatar'];

        $forminfo = Db::name("wd_xcx_formcon")->alias('a')->join("wd_xcx_formlist b", "a.fid = b.id")->where("a.id", $item['fid'])->where("a.uniacid", $id)->field("a.*,b.formname as title")->find();
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
        $row = Db::name("wd_xcx_vip_apply")->where("id", $id)->where("uniacid", $uniacid)->find();
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
   
        $res = Db::name('wd_xcx_vip_apply')->where("id", $id)->where("uniacid", $uniacid)->update(array("flag" => $flag, "examinetime" => $examinetime, "beizhu" => $beizhu));
        if($flag == 1){
            $jieguo = "会员卡申请审核通过";
            $result1 = Db::name('wd_xcx_vipgrade')->where('uniacid', $uniacid)->where('grade', 1)->find();
            if($result1){
                $userinfo = Db::name('wd_xcx_superuser')->where('uniacid', $uniacid)->where('id', $row['suid'])->find();
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
                            "suid" => $userinfo['id'],
                            "type" => "add",
                            "score" => $result1['score_feedback'],
                            "message" => "会员等级回馈积分",
                            "creattime" => time()
                        );
                        Db::name('wd_xcx_score')->insert($score_data);
            
                    }
                }
                if($result1['coupon_flag'] == 1){
                    $coupon_give = unserialize($result1['coupon_give']);
                    if(count($coupon_give) > 0){
                        $receive['coupon'] = [];
                        foreach ($coupon_give as $k => $v) {
                            for ($i = 0; $i < $v['coupon_num']; $i++) {
                                $coup = [];
                                $cid = $v['coupon_id'];
                                //判断优惠券是否为系统发放
                                $couponinfo = Db::name('wd_xcx_coupon')->where('uniacid', $uniacid)->where('id', $cid)->where('give_type', 'neq', 2)->find();
                                if($couponinfo){
                                    $use_contents = unserialize($couponinfo['use_contents']);

                                    $couponinfo['use_goods_contents'] = unserialize($couponinfo['use_goods_contents']);
                                    $couponinfo['use_type'] = $use_contents['use_type'];
                                    $coup['uniacid'] = $uniacid;
                                    $coup['suid'] = $userinfo['id'];
                                    $coup['cid'] = $cid;
                                    if($use_contents['use_type'] == 1) {
                                        $coup['btime'] = strtotime(date("Y-m-d")); //当天开始时间戳
                                        $coup['etime'] = strtotime(date("Y-m-d")) + 3600 * 24 * $use_contents['use_time'];
                                    }else if($use_contents['use_type'] == 2){
                                        $coup['btime'] = strtotime(date("Y-m-d")) + 3600 * 24; //次日开始时间戳为今天开始时间戳加一天时间戳
                                        $coup['etime'] = strtotime(date("Y-m-d")) + 3600 * 24 * ($use_contents['use_time'] + 1); //今天0点时间戳加上n+1天的时间戳
                                    }else{
                                        $use_time = explode(',', $use_contents['use_time']);
                                        $coup['btime'] = $use_time[0];
                                        $coup['etime'] = $use_time[1];
                                    }
                                    $coup['title'] = $couponinfo['title'];
                                    $coup['pay_money'] = $couponinfo['pay_money'];
                                    $coup['price'] = $couponinfo['price'];
                                    $coup['color'] = $couponinfo['color'];
                                    $coup['ltime'] = time();
                                    $coupon_id = Db::name('wd_xcx_coupon_user')->insertGetId($coup);
                                }
                            }
                            $receive['coupon'][$k]['coupon_id'] = $coupon_id;
                            $receive['coupon'][$k]['coupon_num'] = $v['coupon_num'];
                        }
                        $receive['coupon'] = serialize($receive['coupon']);
                    }
                }
                $receive['suid'] = $row['suid'];
                Db::name('wd_xcx_vip_receive')->insert($receive);
            }
            Db::name('wd_xcx_superuser')->where("id", $row['suid'])->where("uniacid", $uniacid)->update(array("vipid" => $row['vipid'], "vipcreatetime" => time(),'grade' => 1,'score' => $score));

        }
        if($row['source'] != 3){
            $jsons['jieguo'] = $jieguo;
            $jsons['applytime'] = $row['applytime'];
            $jsons = serialize($jsons);
            if($row['source'] == 1){ //微信
                $user_info = Db::name('wd_xcx_user')->where('suid', $row['suid'])->find();
                $jsons = [];
                $jsons['nickname'] = $user_info['nickname'];
                $userinfo = Db::name('wd_xcx_superuser')->where('uniacid', $uniacid)->where('id', $row['suid'])->find();
                $jsons['tel'] = $userinfo['phone'];
                $jsons = serialize($jsons);
                sendSubscribe($uniacid, 8, $user_info['openid'], $jsons);  //模板消息发送
            }else if($row['source'] == 6){
                $openid = Db::name('wd_xcx_qq_user')->where('suid', $row['suid'])->value('openid');
                tpl_send($uniacid, 4, $openid, $row['source'], $row['formid'], $jsons);
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
        // 发出请求  ap
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