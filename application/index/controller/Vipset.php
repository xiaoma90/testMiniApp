<?php

namespace app\index\controller;

use think\Controller;

use think\Db;

use think\Request;

use think\Session;

use think\View;



class Vipset extends Base
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



                $forms = Db::name('wd_xcx_formlist')->where("uniacid",$appletid)->order("id desc")->select();
                $item = Db::name('wd_xcx_vip_config')->where("uniacid",$appletid)->find();
                if($item){
                    $item['bg_img'] = remote($appletid,$item['bg_img'],1);
                    if($item['equity']){
                        $item['equity'] = unserialize($item['equity']);
                        $item['equity'] = implode(',',$item['equity']);
                    }
                }                
                $msg = Db::name('wd_xcx_message')->where("uniacid",$appletid)->where("flag",12)->find();
                $this->assign('item',$item);
                $this->assign('forms',$forms);
                $this->assign('msg',$msg);
                $module_url = ROOT_HOST.STATIC_ROOT;
                $this->assign('module_url',$module_url);



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
    public function save(){
        $uniacid = input("appletid");
        $isopen = input("isopen");
        $temp_name = input('name');
        $bg_img = remote($uniacid,input('commonuploadpic1'),2);
        $equity = input('equity');
        if(!empty($equity)){
            $equity = array_filter(explode(",",$equity));
            $equity_arr = [];
            if(count($equity)>8){
                foreach ($equity as $key => $value) {
                    if($key < 8){
                        if($value != ""){
                            array_push($equity_arr,$value);
                        }
                    }
                }
            }else{
                $equity_arr = $equity;
            }
        }

        $data = array(

            "isopen" => input('isopen'),

            "recharge" => input('recharge'),

            'coupon'=>input('coupon'),

            'sign' => input('sign'),

            'exchange' => input('exchange'),

            'miaosha' => input('miaosha'),

            'duo' => input('duo'),

            'yuyue' => input('yuyue'),

            'pt' => input('pt'),

            'bargain' => input('bargain'),

            'formid' => input('formid'),

            'shenhe' => input('shenhe'),

            'name' => empty($temp_name)?'会员卡' : input('name'),

            'equity' => empty($equity) ? '' : serialize($equity_arr),

            'bg_img' => $bg_img,

            'form_status' => input('form_status')
        );


        // $msgdata = array(
        //     "uniacid" => input("appletid"),
        //     "mid" => input("mid"),
        //     "url" => input("url")
        //     );

        // $msg_is = Db::name('wd_xcx_message')->where("uniacid",$uniacid)->where("flag",12)->find();
        // if($msg_is){
        //     $msg = Db::name('wd_xcx_message')->where("uniacid",$uniacid)->where("flag",12)->update($msgdata);
        // }else{
        //     $msgdata["flag"] = 12;
        //     $msg = Db::name('wd_xcx_message')->insert($msgdata);
        // }
       

        $is = Db::name('wd_xcx_vip_config')->where("uniacid",$uniacid)->find();

        if($is){

            $res = Db::name('wd_xcx_vip_config')->where("uniacid",$uniacid)->update($data);

        }else{

            $data['uniacid'] = $uniacid;

            $res = Db::name('wd_xcx_vip_config')->insert($data);

        }

        if($res){

          $this->success('会员卡设置成功');

        }else{

          $this->error('会员卡设置更新失败，没有修改项！');

          exit;

        }

    }
    public function vipgrade(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);


                $grade_arr = Db::name("wd_xcx_vipgrade")->where('uniacid',$appletid)->order('grade asc')->select();
                $is = Db::name("wd_xcx_vipgrade")->where('uniacid',$appletid)->where('grade',1)->find();

                if(empty($is)){
                    $data = [
                        'uniacid' => $appletid,
                        'grade' => 1,
                        'name' => '大众会员',
                        'upgrade' => 0,
                        'price' => 0,
                        'status' => 1,
                        'bgcolor' => '#434550',
                        'card_img' => '/vipgrade/vip_card.png',
                        'descs' => '默认会员等级'
                    ];
                    Db::name('wd_xcx_vipgrade')->insert($data);
                }

                $usergrade = input('usergrade');
                $where = '';
                if($usergrade > 0){
                    $where = ' and grade = '.$usergrade;
                }
                $counts = Db::name('wd_xcx_vipgrade')->where('uniacid',$appletid)->where($where)->count();
                $list = Db::name('wd_xcx_vipgrade')->where('uniacid',$appletid)->where($where)->order('grade asc')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                $this->assign('list',$list->toArray()['data']);
                $this->assign('pager',$list->render());
                $this->assign('counts',$counts);
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



            return $this->fetch('vipgrade');

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

                $gid = input('gid');
                $grade_arr = [];
                for($i=1;$i<=50;$i++){
                    array_push($grade_arr,$i);
                }
                $item = Db::name('wd_xcx_vipgrade')->where('id',$gid)->where('uniacid',$appletid)->find();
                if($item){
                    $item['card_img'] = remote($appletid, $item['card_img'],1);
                    if($item['grade'] >1){
                        $prev = Db::name('wd_xcx_vipgrade')->where('grade', '<' ,$gid)->where('uniacid',$appletid)->field('upgrade')->order('grade desc')->find();
                        if($prev){
                            $item['prev'] = $prev['upgrade'];
                        }
                        $next = Db::name('wd_xcx_vipgrade')->where('grade', '>' ,$gid)->where('uniacid',$appletid)->field('upgrade')->order('grade desc')->find();
                        if($next){
                            $item['next'] = $prev['upgrade'];
                        }else{
                            $item['next'] = '';
                        }
                    }
                    if($item['bgcolor'] == ""){
	                    $item['bgcolor'] = '#434550';
	                }
                	$item['coupon_give'] = unserialize($item['coupon_give']);
                	// if($item['card_img']){
	                //     if(stristr($item['card_img'], 'vip_card.png') && !stristr($item['card_img'], 'http')){
	                //         $item['card_img'] = $item['card_img'];
	                //     }else{
	                //         $item['card_img'] = $item['card_img'];
	                //     }
	                // }

                }else{
                	$item['id'] = 0;
                	$item['grade'] = 0;
	                $item['bgcolor'] = '#434550';
                    $item['coupon_give'] = [];
                	$item['card_img'] = "/vipgrade/vip_card.png";
                }

                $yi = Db::name('wd_xcx_vipgrade')->where('uniacid', $appletid)->field('grade')->select();
                $changed = [];
                foreach ($yi as $k => $v) {
                    if($v['grade'] != intval($item['grade'])){
                        array_push($changed, $v['grade']);
                    }
                }

                $coupon = Db::name('wd_xcx_coupon')->where('uniacid', $appletid)->where('give_type = 0 or give_type = 1')->field('id,title,etime')->order("num desc, id desc")->select();
                foreach ($coupon as $ki => $vi) {
                    if($vi['etime'] > time() || $vi['etime'] == 0){
                        $coupon[$ki]['overdue'] = 1;
                    }else{
                        $coupon[$ki]['overdue'] = 0;
                    }
                }
                $this->assign('coupon',$coupon);
                $this->assign('item',$item);
                $this->assign('grade_arr',$grade_arr);
                $this->assign('changed',$changed);
                $module_url = ROOT_HOST;
                $this->assign('module_url',$module_url);
                $this->assign('grade',$item['grade']);
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
    public function post(){
        $uniacid = input("appletid");
        $gid = input("gid");
        $coupon_flag = input("coupon_flag");
        $coupon_give = [];

        if(!empty(input('coupon_id/a'))){
            $coupon_id_arr = input('coupon_id/a');
            $coupon_num_arr = input('coupon_num/a');
            $j = 0;
            $n = 0;
            foreach ($coupon_id_arr as $k => $v) {
                if($v != 0 && $coupon_num_arr[$k] >0){
                    ++$n;
                    $coupon_give[$j]['coupon_id'] = $v;
                    $coupon_give[$j]['coupon_num'] = $coupon_num_arr[$k];
                    $j++;
                }
            }
            if($n==0){
                $coupon_flag = 0;
            }
        }

        $card_img = remote($uniacid,input("commonuploadpic1"),2);
        // var_dump($card_img);exit;
        // if(stristr($card_img, '/vipgrade/vip_card.png')){
        //     $card_img = '/vipgrade/vip_card.png';
        // }
        // if(empty($card_img)){
        //     $this->error("会员卡图不能为空");
        // }else{
        //     if(stristr($card_img, 'http')){
        //         $card_img = explode(ROOT_HOST,$card_img)[1];
        //     }
        // }
        $grade = input('grade')?input('grade'):1;
        $coupon_id = input('coupon_id/a');
        $coupon_num = input('coupon_num/a');
        $is = Db::name('wd_xcx_vipgrade')->where('uniacid', $uniacid)->where('grade', $grade)->where('id','<>',$gid)->find();
        if($is){
            $this->error("操作失败，当前等级已存在", Url('Vipset/vipgrade').'?appletid='.$uniacid);
        }
        $data = array(
            'uniacid' => $uniacid,
            'grade' => intval($grade),
            'name' => input('name'),
            'upgrade' => input('upgrade'),
            'price_flag' => input('price_flag') ? input('price_flag') : 2,
            'price' => input('price'),
            'status' => input('status') != null ? input('status') : 1,
            'bgcolor' => input('bgcolor')?input('bgcolor'):'#434550',
            'card_img' => $card_img,
            'coupon_flag' => intval($coupon_flag),
            'coupon_give' => serialize($coupon_give),
            'free_package' => input('free_package')?input('free_package'):0,
            'discount_flag' => input('discount_grade') > 0 ? intval(input('discount_flag')) : 0,
            'discount_grade' => input('discount_grade'),
            'score_flag' => intval(input('score_bei')) > 0 ? intval(input('score_flag')) : 0,
            'score_bei' => intval(input('score_bei')),
            'score_feedback_flag' => input('score_feedback') > 0 ? intval(input('score_feedback_flag')) : 0,
            'score_feedback' => input('score_feedback'),
            'descs' => input('descs')
        );
        if($gid > 0){
            $before_upgrade = Db::name('wd_xcx_vipgrade')->where('uniacid', $uniacid)->where('id',$gid)->find()['upgrade'];  //得到之前升级金额
            $res = Db::name('wd_xcx_vipgrade')->where('id', $gid)->update($data);

            if($res){
                $cha = $before_upgrade > floatval(input('upgrade')) ? $before_upgrade - floatval(input('upgrade')) : 0;  //得到现在升级金额
                if($cha > 0 && $grade > 1){
                    $receive = [];
                    $receive['vid'] = $gid;
                    $receive['uniacid'] = $uniacid;
                    $receive['score'] = 0;
                    $receive['coupon'] = '';
                    $users = Db::query("SELECT * FROM {$this->prefix}wd_xcx_superuser WHERE uniacid = ".$uniacid." and (allpay + virtualpay) >= ".input('upgrade')." and (allpay + virtualpay) < ".$before_upgrade);
                    if(input('score_feedback_flag') == 1){
                        if(input('score_feedback') > 0){
                            $receive['score'] = input('score_feedback');
                            $score_data = array(
                                "uniacid" => $uniacid,
                                "orderid" => '',
                                "type" => "add",
                                "score" => input('score_feedback'),
                                "message" => "会员等级回馈积分",
                                "creattime" => time()
                            );
                            $uids = array_column($users, 'id');

                            $sql = array_map(function($user) use($score_data) {
                                return "('{$score_data['uniacid']}', '{$score_data['orderid']}', '{$user}', 'add', '{$score_data['score']}', '{$score_data['message']}','{$score_data['uniacid']}')";
                            }, $uids);
                            $sql = implode(', ', $sql);
                            if($sql){
	                            $sql = "INSERT INTO `{$this->prefix}wd_xcx_score` (`uniacid`, `orderid`, `uid`, `type`, `score`, `message`, `creattime`) VALUES " . $sql;
	                            $res1 = Db::query($sql);
                            }
                        }
                    }
                    if(input('coupon_flag') == 1){
                        if(count($coupon_give) > 0){
                            $receive['coupon'] = serialize($coupon_give);
                            foreach ($coupon_give as $k => $v) {
                                $coup_info = [];
                                for($i = 0;$i<$v['coupon_num'];$i++){
                                    $coup = [];
                                    $cid = $v['coupon_id'];
                                    if(count($coup_info) == 0){
                                        $coup_info = Db::name('wd_xcx_coupon')->where('uniacid', $uniacid)->where('id', $cid)->find();
                                    }

                                    $coup['uniacid'] = $uniacid;
                                    
                                    $coup['cid'] = $cid;
                                    $coup['btime'] = $coup_info['btime'];
                                    $coup['etime'] = $coup_info['etime'];
                                    $coup['ltime'] = time();
                                    foreach ($users as $key => $value) {
                                        $coup['uid'] = $value['id'];
                                    }
                                    Db::name('wd_xcx_coupon_user')->insert($coup);
                                }
                            }
                        }
                    }
                    $users = array_column($users, 'openid');
                    $sql = array_map(function($user) use($receive) {
                        return "('{$receive['vid']}', '{$receive['uniacid']}', '{$receive['score']}', '{$receive['coupon']}', '{$user}')";
                    }, $users);
                    $sql = implode(', ', $sql);

                    if($sql){
	                    $sql = "INSERT INTO `{$this->prefix}wd_xcx_vip_receive` (`vid`, `uniacid`, `score`, `coupon`, `openid`) VALUES " . $sql;
	                    $res3 = Db::query($sql);
                    }
                    $upgrade = input('upgrade');
                    $scoreback = intval(input('score_feedback'));
                    $str = "UPDATE `{$this->prefix}wd_xcx_superuser` set score = score + {$scoreback}, grade = {$grade} WHERE uniacid = {$uniacid} and (allpay + virtualpay) >= {$upgrade} and (allpay + virtualpay) < {$before_upgrade}";
                    $res4 = Db::query($str);
                }
            }
        }else{
            $res = Db::name('wd_xcx_vipgrade')->insert($data);
        }
        if($res){
            $this->success("操作成功", Url('Vipset/vipgrade').'?appletid='.$uniacid);
        }else{
            $this->error("操作失败，没有修改项");
        }
    }
    public function ajax(){
        $uniacid = input("appletid");
        $grade = input('grade');
        $prev = Db::name('wd_xcx_vipgrade')->where('uniacid', $uniacid)->where('grade', 'lt', $grade)->order("grade desc")->field('upgrade')->find();
        $item['prev'] = $prev['upgrade'];
        $next = Db::name('wd_xcx_vipgrade')->where('uniacid', $uniacid)->where('grade', '>', $grade)->order("grade asc")->field('upgrade')->find();
        $item['next'] = $next['upgrade']?$next['upgrade']:'';
        echo json_encode($item);
        exit;
    }
    public function updatestatus(){
        $uniacid = input("appletid");
        $gid = input('gid');
        $i = input('i');
        $data = [];
        if($i == 1){
            $data['status'] = 1;
        }else if($i == 2){
            $data['status'] = 0;
        }
        $is = Db::name('wd_xcx_vipgrade')->where('uniacid', $uniacid)->where('id', $gid)->find();
        if($is){
            $res = Db::name("wd_xcx_vipgrade")->where('uniacid', $uniacid)->where('id', $gid)->update($data);
            if($res){
                return 1;
            }
        }else{

        }
    }
    public function del(){
        $uniacid = input("appletid");
        $gid = input('gid');
        $row = Db::name('wd_xcx_vipgrade')->where('id', $gid)->where('uniacid', $uniacid)->field('grade')->find();
        if (empty($row)) {
            $this->error("会员等级不存在或是已经被删除！");
        }
        $userarr = Db::name('wd_xcx_superuser')->where('uniacid', $uniacid)->where('grade', $row['grade'])->field('id')->select();
        $grade = Db::name('wd_xcx_vipgrade')->where('uniacid', $uniacid)->where('grade', '<', $row['grade'])->where('status', 1)->order('grade desc')->field('grade')->find();
        if($grade['grade']){
            foreach ($userarr as $ks => $vs) {
                Db::name('wd_xcx_superuser')->where('uniacid', $uniacid)->where('id',$vs['id'])->update(array('grade' => $grade['grade']));
            }
        }
        $res = Db::name('wd_xcx_vipgrade')->where('uniacid', $uniacid)->where('id',$gid)->delete();
        if($res){
            $this->success("删除成功");
        }else{
            foreach ($userarr as $ks => $vs) {
                Db::name('wd_xcx_superuser')->where('uniacid', $uniacid)->where('id',$vs['id'])->update(array('grade' => $row['grade']));
            }
            $this->success("删除失败");
        }
    }
}