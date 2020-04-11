<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;
class Coupon extends Base
{   
    // a 多规格   b 秒杀  c 预约预定  d 拼团  e多商户  gpay 店内支付 
    public function index(){
        if(check_login()){
            if(powerget()){
                $id = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $coupon = Db::name('wd_xcx_coupon')->where("uniacid",$id)->order('num desc, id desc')->paginate(10,false,[ 'query' => array('appletid'=>$id)]);
                $count = Db::name('wd_xcx_coupon')->where("uniacid",$id)->count();
                $newcoupon = $coupon->toArray();
                $time = time();
                foreach ($newcoupon['data'] as $k => &$v) {
                    if($v['etime']==0){
                        $v['status'] = 1;
                    }else{
                        if($v['etime']>$time){
                            $v['status'] = 1;
                        }else{
                            $v['status'] = 2;
                        }
                    }
                }

                $this->assign('coupon',$newcoupon);
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
            return $this->fetch('index');
        }else{
            $this->redirect('Login/index');
        }

    }
    public function userrecord(){
        if(check_login()){
            if(powerget()) {
                $id = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id", $id)->find();
                if (!$res) {
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet', $res);
                $search_type=input('search_type');
                $search_flag=input('search_flag');
                $search_keys=input('search_keys');
                $start_use=input('start_use');
                $end_use=input('end_use');
                $start_get=input('start_get');
                $end_get=input('end_get');
                $uid=input('uid');
                $where='';
                if(!empty($uid)){
                    if($where==''){
                        $where .= ' a.suid = '.$uid;
                    }else{
                        $where .= ' and a.suid = '.$uid;
                    }
                }
                if($search_flag!= null){ //类型 全部3、待使用0、已使用1、已过期2
                    if($search_flag !=3 ){
                        if($where==''){
                            $where .= ' a.flag = '.$search_flag;
                        }else{
                            $where .= ' and a.flag = '.$search_flag;
                        }
                    }
                }
                if(!empty($start_get)){//领取时间开始
                    if($where==''){
                        $where .= ' a.ltime >= '.strtotime($start_get);
                    }else{
                        $where .= ' and a.ltime >= '.strtotime($start_get);
                    }

                }
                if(!empty($end_get)){//领取时间结束
                    if($where==''){
                        $where .= ' a.ltime <= '.strtotime($end_get);
                    }else{
                        $where .= ' and a.ltime <= '.strtotime($end_get);
                    }
                }
                if(!empty($start_use)){
                    if($where==''){
                        $where .= ' a.utime >= '.strtotime($start_use);
                    }else{
                        $where .= ' and a.utime >= '.strtotime($start_use);
                    }

                }
                if(!empty($end_use)){
                    if($where==''){
                        $where .= ' a.utime <= '.strtotime($end_use);
                    }else{
                        $where .= ' and a.utime <= '.strtotime($end_use);
                    }

                }
                if(!empty($search_keys)){
                    if(!empty($search_type)){
                        if($where==''){
                            if($search_type == 1){
                                $where .= ' b.title like "%'.$search_keys.'%"';
                            }else{
                                $where .= ' c.nickname like "%'.$search_keys.'%"';
                            }
                        }else{
                            if($search_type == 1){
                                $where .= ' and b.title like "%'.$search_keys.'%"';
                            }else{
                                $where .= ' and c.nickname like "%'.$search_keys.'%"';
                            }
                        }
                    }
                }

                $coupon = Db::name("wd_xcx_coupon_user")->alias("a")->join("wd_xcx_coupon b", "a.cid = b.id", 'left')->join("wd_xcx_superuser c", "a.suid = c.id", 'left')->where('a.uniacid', $id)->where($where)->order("a.ltime desc,b.num desc")->field("a.*,b.title")->paginate(10, false, ['query' => array('appletid' => input("appletid"),'uid'=>$uid,'search_flag'=>$search_flag,'search_type'=>$search_type,'search_keys'=>$search_keys,'start_use'=>$start_use,'end_use'=>$end_use,'start_get'=>$start_get,'end_get'=>$end_get)]);
                $count = Db::name("wd_xcx_coupon_user")->alias("a")->join("wd_xcx_coupon b", "a.cid = b.id", 'left')->join("wd_xcx_superuser c", "a.suid = c.id", 'left')->where('a.uniacid', $id)->where($where)->order("a.cid desc,a.ltime desc,b.num desc")->count();
                $array = $coupon->toArray();
                $coupontwo = $array['data'];
                if ($coupontwo) {
                    foreach ($coupontwo as $k => $res) {
                        $user = getNameAvatar($res['suid'], $id);
                        $coupontwo[$k]['nickname'] = $user['nickname'];
                        $coupontwo[$k]['ltime']=date("Y-m-d H:i:s", $res['ltime']);
                        if ($res['utime'] != 0) {
                            $coupontwo[$k]['utimetwo'] = date("Y-m-d H:i:s", $res['utime']);
                        } else {
                            $coupontwo[$k]['utimetwo'] = 0;
                        }
                    }
                }
                $this->assign('coupon', $coupon);
                $this->assign('coupontwo', $coupontwo);
                $this->assign('counts', $count);
                $this->assign('search_type',$search_type);
                $this->assign('search_flag',$search_flag);
                $this->assign('search_keys',$search_keys);
                $this->assign('start_use',$start_use);
                $this->assign('end_use',$end_use);
                $this->assign('start_get',$start_get);
                $this->assign('end_get',$end_get);
                $this->assign('uid',$uid);
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
            return $this->fetch('userrecord');
        }else{
            $this->redirect('Login/index');
        }
    }

    public function coupondown(){
        $id = input("appletid");
        $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
        if(!$res){
            $this->error("找不到对应的小程序！");
        }
        $this->assign('applet',$res);
        $coupontwo = Db::name("wd_xcx_coupon_user")->alias("a")->join("wd_xcx_coupon b", "a.cid = b.id", 'left')->join("wd_xcx_superuser c", "a.suid = c.id", 'left')->where('a.uniacid', $id)->order("a.cid desc,a.ltime desc,b.num desc")->field("a.*,b.title")->select();
        if ($coupontwo) {
            foreach ($coupontwo as $k => $res) {
                $user = getNameAvatar($res['suid'], $id, 1);
                $coupontwo[$k]['nickname'] = $user['nickname'];
                $coupontwo[$k]['ltime']=date("Y-m-d H:i:s", $res['ltime']);
                if ($res['utime'] != 0) {
                    $coupontwo[$k]['utimetwo'] = date("Y-m-d H:i:s", $res['utime']);
                } else {
                    $coupontwo[$k]['utimetwo'] = 0;
                }
            }
        }
        require_once ROOT_PATH.'public/plugin/PHPExcel/PHPExcel.php';
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()->setCreator("导出优惠劵领取列表")
            ->setLastModifiedBy("导出优惠劵列表")
            ->setTitle("导出优惠劵领取列表")
            ->setSubject("导出优惠劵领取列表")
            ->setDescription("导出优惠劵领取列表")
            ->setKeywords("导出优惠劵领取列表")
            ->setCategory("导出优惠劵领取列表");
        $objPHPExcel->getActiveSheet()->setCellValue('A1', '优惠券id');
        $objPHPExcel->getActiveSheet()->setCellValue('B1', '标题');
        $objPHPExcel->getActiveSheet()->setCellValue('C1', '用户昵称');
        $objPHPExcel->getActiveSheet()->setCellValue('D1', '领取时间');
        $objPHPExcel->getActiveSheet()->setCellValue('E1', '使用时间');
        $objPHPExcel->getActiveSheet()->setCellValue('F1', '状态');
        foreach($coupontwo as $k => $v){
            $num=$k+2;
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('A'.$num, $v['id'],'s');
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$num, $v['title']);
            $objPHPExcel->getActiveSheet()->setCellValue('C'.$num, $v['nickname']);
            $objPHPExcel->getActiveSheet()->setCellValue('D'.$num, $v['ltime']);
            $objPHPExcel->getActiveSheet()->setCellValue('E'.$num, $v['utimetwo']);
            $flag='';
            if($v['flag']==0){
                $flag = "未使用";
            }
            if($v['flag']==1){
                $flag = "已使用";
            }
            if($v['flag']==2){
                $flag = "已过期";
            }
            $objPHPExcel->getActiveSheet()->setCellValue('F'.$num, $flag);
        }
        $objPHPExcel->getActiveSheet()->setTitle('导出优惠劵领取列表');
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="优惠劵领取列表.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }


    public function userrecorddel(){
        $id = input('id');
        $res = Db::name('wd_xcx_coupon_user')->where("id",$id)->delete();
        if($res){
            $this->success("优惠券领取记录删除成功");
        }else{
            $this->error("优惠券领取记录删除失败");
        }
    }
    public function userrecordhx(){
        $id = input('id');
        $data['utime'] = time();
        $data['flag'] = 1;
        $data['utime']=time();
        $res = Db::name('wd_xcx_coupon_user')->where("id",$id)->update($data);
        if($res){
            $this->success("核销成功");
        }else{
            $this->error("核销失败");
        }
    }
    public function set(){
        if(check_login()){
            if(powerget()){
                $id = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                // 开启验证手机
                $set = Db::name('wd_xcx_coupon_set')->where("uniacid",$id)->find();

                $this->assign("set",$set);

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
    public function setsave(){
        $data = array();
        //小程序ID
        $uniacid = input("appletid");
        $flag = input('flag');
        if(!$flag){
            $flag = 0;
        }
        $data = array(
            "flag" => $flag,
            "uniacid" => $uniacid
        );
        $set = Db::name('wd_xcx_coupon_set')->where("uniacid",$uniacid)->find();
        if($set){
            $res = Db::name('wd_xcx_coupon_set')->where("uniacid",$uniacid)->update($data);
        }else{
            $res = Db::name('wd_xcx_coupon_set')->insert($data);
        }
        if($res){
            $this->success("设置修改成功");
        }else{
            $this->error("设置修改失败，没有修改项");
        }
    }
    public function hxmm(){
        if(check_login()){
            if(powerget()){
                $id = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $hxmm = Db::name('wd_xcx_base')->where("uniacid",$id)->find();
                $hxuser = Db::name('wd_xcx_hx_user')->where("uniacid",$id)->find();
                if($hxuser){
                    if($hxuser['hxuser']){
                        $hxuser['hxuser'] = unserialize($hxuser['hxuser']);
                    }
                }
                $users = Db::name('wd_xcx_superuser')->where("uniacid",$id)->field('id')->select();
                foreach ($users as $key => &$value) {
                    $nickname = getNameAvatar($value['id'], $id)['nickname'];
                    if(!$nickname){
                        unset($users[$key]);
                    }else{
                        $value['nickname'] = $nickname;
                    }
                }
                $this->assign('users',$users);
                $this->assign('hxuser',$hxuser);
                $this->assign('hxmm',$hxmm);
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
            return $this->fetch('hxmm');
        }else{
            $this->redirect('Login/index');
        }
    }
    public function hxsave(){
        $data = array();
        //小程序ID
        $uniacid = input("appletid");
        $data['hxmm'] = input('hxmm');

        $hxuser = [];
        $user_id = input("user_id/a");
        $names = input("names/a");
        if (count($user_id) != count(array_unique($user_id))) {   
            $this->error('核销员不可以重复添加');
        }
        foreach ($user_id as $key => $value) {
            if($value == 0){
                $this->error('核销员为必选！');
            }else{
                if($names[$key] == ''){
                    $this->error('核销员名称为必填！');
                }else{
                    $hxuser[$key]['suid'] = $value;
                    $hxuser[$key]['name'] = trim($names[$key]);
                }
            }
        }

        $id = Db::name('wd_xcx_hx_user')->where('uniacid', $uniacid)->field('id')->find();
        if($id){
            $res1 = Db::name('wd_xcx_hx_user')->where('uniacid', $uniacid)->update(['hxuser' => serialize($hxuser)]);
        }else{
            $arr = [
                'uniacid' => $uniacid,
                'hxuser' => serialize($hxuser)
            ];
            $res1 = Db::name('wd_xcx_hx_user')->where('uniacid', $uniacid)->insert($arr);
        }
        $res = Db::name('wd_xcx_base')->where("uniacid",$uniacid)->update($data);
        if($res || $res1){
            $this->success('核销信息更新成功！');
        }else{
            $this->error('核销信息更新失败，没有修改项！');
            exit;
        }
    }

    public function add(){
        if(check_login()){
            if(powerget()){
                $id = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $couponid = input("couponid");
                $couponinfo = "";
                if($couponid){
                    $couponinfo = Db::name('wd_xcx_coupon')->where("uniacid",$id)->where("id",$couponid)->find();
                    if(!$couponinfo['use_contents']){ //兼容老优惠券
                        $use_contents = [
                            'use_type' => 0,
                            'use_time' => $couponinfo['btime'].','.$couponinfo['etime'],
                        ];
                        $use_goods_contents = [
                            'type' => 0,
                            'contents' => '',
                        ];
                        $upd = [
                            'btime' => 0,
                            'etime' => 0,
                            'use_contents' => serialize($use_contents),
                            'use_goods_contents' => serialize($use_goods_contents),
                        ];
                        Db::name('wd_xcx_coupon')->where("uniacid",$id)->where("id",$couponid)->update($upd);
                        $couponinfo['use_type'] = 0;
                        $couponinfo['use_time'] = [
                                'use_btime' => 0,
                                'use_etime' => 0,
                            ];
                        $couponinfo['use_goods_contents'] = [
                                'type' => 0,
                                'contents' => [],
                            ];
                    }else{
                        $use_contents = unserialize($couponinfo['use_contents']);
                        $couponinfo['use_goods_contents'] = unserialize($couponinfo['use_goods_contents']);
                        $couponinfo['use_type'] = $use_contents['use_type'];

                        if($use_contents['use_type'] > 0) {
                            $couponinfo['use_time'] = $use_contents['use_time'];
                        }else{
                            $use_time = explode(',', $use_contents['use_time']);
                            $couponinfo['use_time'] = [
                                'use_btime' => $use_time[0],
                                'use_etime' => $use_time[1],
                            ];
                        }
                    }
                }else{
                    $couponid = 0;
                }

                $cate_duo = Db::name('wd_xcx_cate')->where("uniacid",$id)->where('statue', 1)->order('num desc,id desc')->field("id,name")->select();
                $cate_miaosha = Db::name('wd_xcx_flashsale_cate')->where("uniacid",$id)->where('statue', 1)->where("catefor", 'flashsale')->order('num desc,id desc')->field("id,name")->select();
                $cate_yuyue = Db::name('wd_xcx_flashsale_cate')->where("uniacid",$id)->where("statue", 1)->where("catefor", "reserve")->order("num desc,id desc")->field("id,name")->select();
                $cate_pt = Db::name('wd_xcx_pt_cate')->where("uniacid",$id)->order('num desc,id desc')->field("id,title")->select();
                $cate_shops = Db::name('wd_xcx_goods_cate')->where("uniacid",$id)->where("flag", 1)->order('num desc,id desc')->field("id,name")->select();
                $this->assign('cate_duo',$cate_duo);
                $this->assign('cate_miaosha',$cate_miaosha);
                $this->assign('cate_yuyue',$cate_yuyue);
                $this->assign('cate_pt',$cate_pt);
                $this->assign('cate_shops',$cate_shops);

                
                $this->assign('couponid',$couponid);
                $this->assign('couponinfo',$couponinfo);
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
        $data['num'] = input('num');
        //标题
        if(!input("title")){
            $this->error("标题不能为空！");
        }
        $data['title'] = input('title');
        //颜色
        $data['color'] = "#".input('color');
        //优惠价
        if(!input("price")){
            $this->error("优惠价不能为空！");
        }
        $data['price'] = input('price');
        //使用价
        $data['pay_money'] = input('pay_money');

        //优惠券总数
        if(!input("counts")){
            $data['counts'] = -1;
        }else{
            $data['counts'] = input('counts');
        }

        //每人限领数
        $data['xz_count'] = input('xz_count');
        

        $use_type = input('use_type');
        if($use_type == 1){
            $data['use_contents']['use_time'] = intval(input('today_after'));
        }else if($use_type == 2){
            $data['use_contents']['use_time'] = intval(input('yes_after'));
        }else{
            //开始时间
            if(input("use_btime")){
                $use_btime = strtotime(input('use_btime'));
            }else{
                $use_btime = 0;
            }

            //结束时间
            if(input("use_etime")){
                $use_etime = strtotime(input('use_etime'));
            }else{
                $use_etime = 0;
            }
            $data['use_contents']['use_time'] = $use_btime.','.$use_etime;
        }

        $data['use_contents']['use_type'] = $use_type;

        $data['use_contents'] = serialize($data['use_contents']);

        $data['use_goods_contents']['type'] = input('use_goods_type');
        $data['use_goods_contents']['contents'] = input('use_goods_type') == 1 ? input('stores') : [];
        $data['use_goods_contents'] = serialize($data['use_goods_contents']);
        //是否关闭
        $flag = input('flag');
        if(!$flag){
            $flag = 0;
        }
        $data['flag'] = $flag;
        $couponid = input("couponid");

        if($couponid){
            $res = Db::name('wd_xcx_coupon')->where("id",$couponid)->update($data);
        }else{
            $data['give_type'] = input('give_type');
            $res = Db::name('wd_xcx_coupon')->insert($data);
        }
        if($res){
            $this->success('优惠券更新成功！',Url('Coupon/index').'?appletid='.$data['uniacid']);
        }else{
            $this->error('优惠券更新失败，没有修改项！');
            exit;
        }
    }
    public function del(){
        $id = input("couponid");
        $res = Db::name('wd_xcx_coupon')->where("id",$id)->delete();
        Db::name('wd_xcx_coupon_user')->where("cid",$id)->delete();
        if($res){
            $this->success('删除成功');
        }else{
            $this->success('删除失败');
        }
    }
}