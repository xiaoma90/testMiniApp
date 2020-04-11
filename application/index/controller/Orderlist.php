<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;
class Orderlist extends Base
{
    public function index(){
        if(check_login()){
            if(powerget()){
                $id = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);

                $search_flag=input("search_flag");
                $search_type=input("search_type");
                $search_keys=input("search_keys");
                $start_get=input("start_get");
                $end_get=input("end_get");

                $where = "";
                if($search_flag != null && $search_flag != 'undefined'){
                    if($where==""){
                        $where .= "a.flag = ".$search_flag;
                    }else{
                        $where .= "and a.flag = ".$search_flag;
                    }

                }
//
                if(!empty($start_get)){//时间开始
                    if($where!=""){
                        $where .= ' and a.creattime >= '.strtotime($start_get);
                    }else{
                        $where .= ' a.creattime >= '.strtotime($start_get);
                    }

                }
                if(!empty($end_get)){//时间结束
                    if($where!=""){
                        $where .= ' and a.creattime <= '.strtotime($end_get);
                    }else{
                        $where .= ' a.creattime <= '.strtotime($end_get);
                    }

                }
                if(!empty($search_keys)){
                    if($where!=""){
                        if(!empty($search_type)){
                            if($search_type == 1){ //订单号
                                $where .= " and a.order_id like '%".trim($search_keys)."%'";
                            }else if($search_type == 2){
                                $where .= " and b.name like '%".trim($search_keys)."%'";
                            }else if($search_type == 3){
                                $where .= " and b.mobile like '%".trim($search_keys)."%'";
                            }else if($search_type == 4){
                                $where .= " and (b.address like '%".trim($search_keys)."%' or b.more_address like '%".trim($search_keys)."%')";
                            }
                        }
                    }else{
                        if(!empty($search_type)){
                            if($search_type == 1){ //订单号
                                $where .= "  a.order_id like '%".trim($search_keys)."%'";
                            }else if($search_type == 2){
                                $where .= " b.name like '%".trim($search_keys)."%'";
                            }else if($search_type == 3){
                                $where .= "  b.mobile like '%".trim($search_keys)."%'";
                            }else if($search_type == 4){
                                $where .= " (b.address like '%".trim($search_keys)."%' or b.more_address like '%".trim($search_keys)."%')";
                            }
                        }
                    }

                }
                 $order=Db::name("wd_xcx_order")->alias("a")->join("wd_xcx_duo_products_address b",'a.address=b.id','left')->where('a.is_more',0)->where("a.uniacid",$id)->where($where)->order("a.creattime","DESC")->field("a.*,b.name,b.mobile,b.address,b.more_address")->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"),'search_flag'=>$search_flag,"search_type"=>$search_type,"search_keys"=>$search_keys,"start_get"=>$start_get,"end_get"=>$end_get)]);
                 $count=Db::name("wd_xcx_order")->alias("a")->join("wd_xcx_duo_products_address b",'a.address=b.id','left')->where('a.is_more',0)->where("a.uniacid",$id)->where($where)->count();

                $neworder = $order->toArray();
                
                foreach($neworder['data'] as &$row){
                    if(!$row['name']){
                        if($row['m_address']){
                            $row['m_address']=unserialize($row['m_address']);
                            $row['name']=$row['m_address']['name'];
                            $row['mobile']=$row['m_address']['mobile'];
                            $row['address']=$row['m_address']['address'];
                        }
                    }
                    if($row['formid']){
                        $arr2=Db::name('wd_xcx_formcon')->where('uniacid',$id)->where('id',$row['formid'])->find();
                        $arr2['val']=unserialize($arr2['val']);

                        $row['forminfo']=$arr2['val'];

                    }else{
                        $row['forminfo']='';
                    }

                    if($row['custime']){
                        $row['custime']=date("Y-m-d H:i:s",$row['custime']);
                    }else{
                        $row['custime']="";
                    }
                    $row['thumb'] =  remote($id,$row['thumb'],1);
                    if($row['hxinfo'] == ""){
                       $row['hxinfo2']="暂无核销信息";
                    }else{
                        $row['hxinfo'] = unserialize($row['hxinfo']);
                         if($row['hxinfo'][0]==1){
                             $row['hxinfo2']="系统核销";
                         }else{
                            $store=Db::name('wd_xcx_store')->where("id",$row['hxinfo'][1])->where("uniacid",$id)->find();
                            $staff=Db::name('wd_xcx_staff')->where("id",$row['hxinfo'][2])->where("uniacid",$id)->find();
                            $row['hxinfo2']="门店：".$store['title']."</br>员工：".$staff['realname'];
                         }
                    }
                    //获取联系方式
                    $row['creattime']=date("Y-m-d H:i:s",$row['creattime']);
                    $user = Db::name('wd_xcx_user')->where("uniacid",$row['uniacid'])->where("id",$row['uid'])->find();
                    if($user['nickname']){
                        $row['nickname'] = $user['nickname'];
                    }else{
                        $row['nickname'] = "";
                    }

                    if($row['beizhu']!=''||$row['beizhu_val']!=''){
                        $row['beizhu']=empty($row['beizhu'])?$row['beizhu']:$row['beizhu_val'];
                    }else{
                        $row['beizhu']='';
                    }

                    //查询优惠劵

                    $row['order_duo'] = unserialize($row['order_duo']);
                    $row['yhInfo_msg'] = array();
                    if(!empty($row['yhinfo'])){
                        $yhInfo = unserialize($row['yhinfo']);
                        $row['yhInfo_msg']['yhInfo_yunfei'] = $yhInfo['yunfei'];
                        $row['yhInfo_msg']['yhInfo_score'] = $yhInfo['score'];
                        $row['yhInfo_msg']['yhInfo_yhq'] = $yhInfo['yhq'];
                        $row['yhInfo_msg']['yhInfo_mj'] = $yhInfo['mj'];
                    }else{
                        $row['yhInfo_msg']['yhInfo_yunfei'] = 0;
                        if($row['dkscore'] > 0){
                            // $jfgz = pdo_get("sudu8_page_rechargeconf", array("uniacid"=>$uniacid));
                            $jfgz = Db::name('wd_xcx_rechargeconf') ->where('uniacid', $id) ->find();
                            $row['yhInfo_msg']['yhInfo_score']['msg'] = $row['dkscore']."抵扣".floatval($row['dkscore']) * floatval($jfgz['money']) / floatval($jfgz['score']);
                            $row['yhInfo_msg']['yhInfo_score']['money'] = floatval($row['dkscore']) * floatval($jfgz['money']) / floatval($jfgz['score']);
                        }else{
                            $row['yhInfo_msg']['yhInfo_score']['msg'] = "未使用积分";
                            $row['yhInfo_msg']['yhInfo_score']['money'] = 0;
                        }
                        if($row['coupon']){
                            //查询优惠劵
                            // $arr[$k]['couponinfo'] = pdo_fetch("SELECT b.title,b.price FROM ".tablename('sudu8_page_coupon_user')." as a LEFT JOIN  ".tablename('sudu8_page_coupon')." as b on a.cid = b.id WHERE a.uniacid = :uniacid and a.flag = 1 and a.id=:coupon",array(":uniacid"=>$uniacid,":coupon"=>$res['coupon']));
                            $coupon = Db::name('wd_xcx_coupon_user') ->alias('a') ->join('wd_xcx_coupon b', 'a.cid = b.id', 'left') ->where('a.uniacid', $id) ->where('a.flag', 1) ->field('b.title,b.price') ->find(); 
                            $row['yhInfo_msg']['yhInfo_yhq']['msg'] = $coupon['title'];
                            $row['yhInfo_msg']['yhInfo_yhq']['money'] = $coupon['price'];
                        }else{
                            $row['yhInfo_msg']['yhInfo_yhq']['msg'] = "未使用优惠券";
                            $row['yhInfo_msg']['yhInfo_yhq']['money'] = 0;
                        }
                        $row['yhInfo_msg']['yhInfo_mj']['msg'] = "";
                        $row['yhInfo_msg']['yhInfo_mj']['money'] = 0;
                    }
                    if(!$row['custime']){
                        $row['custime'] = "未消费";
                    }
                }
                $this->assign('neworder',$neworder);
                $this->assign('order',$order);
                $this->assign('counts',$count);
                $this->assign("search_flag",$search_flag);
                $this->assign("search_type",$search_type);
                $this->assign("search_keys",$search_keys);
                $this->assign("start_get",$start_get);
                $this->assign("end_get",$end_get);

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

    public function yuyue(){
        if(check_login()){
            if(powerget()){
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

                $order = Db::name('wd_xcx_order')->where("uniacid",$id)->where("is_more",1)->where($where)->order('creattime desc')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"),'order'=>$order,'end_datetimepicker2'=>$end_datetimepicker2,'end_datetimepicker'=>$end_datetimepicker,'select_state'=>$select_state,'datetimepicker'=>$datetimepicker,'datetimepicker3'=>$datetimepicker3)]);
                $count = Db::name('wd_xcx_order')->where("uniacid",$id)->where("is_more",1)->where($where)->order('creattime desc')->count();

                $neworder = $order->toArray();
                    foreach ($neworder['data'] as &$row) {
                        if ($row['custime']) {
                            $row['custime'] = date("Y-m-d H:i:s", $row['custime']);
                        } else {
                            $row['custime'] = "";
                        }

                        $row['thumb'] = remote($id, $row['thumb'], 1);

                        $row['creattime'] = date("Y-m-d H:i:s", $row['creattime']);
                        $user = Db::name('wd_xcx_user')->where("uniacid", $row['uniacid'])->where("id", $row['uid'])->find();
                        if ($user['nickname']) {
                            $row['nickname'] = $user['nickname'];
                        } else {
                            $row['nickname'] = "";
                        }
                        if ($user['mobile']) {
                            $row['mobile'] = $user['mobile'];
                        } else {
                            $row['mobile'] = "";
                        }
                        if ($row['is_more'] == 0) {
                            $row['beizhu'] = "姓名：" . $row['pro_user_name'] . ",电话：" . $row['pro_user_tel'] . "地址：" . $row['pro_user_add'] . ",备注：" . $row['pro_user_txt'];
                        }
                        $row['order_duo'] = unserialize($row['order_duo']);
                        if ($row['hxinfo'] == "") {
                            $row['hxinfo2'] = "暂无核销信息";
                        } else {
                            $row['hxinfo'] = unserialize($row['hxinfo']);
                            if ($row['hxinfo'][0] == 1) {
                                $row['hxinfo2'] = "系统核销";
                            } else {
                                $store = Db::name('wd_xcx_store')->where("id", $row['hxinfo'][1])->where("uniacid", $id)->find();
                                $staff = Db::name('wd_xcx_staff')->where("id", $row['hxinfo'][2])->where("uniacid", $id)->find();
                                $row['hxinfo2'] = "门店：" . $store['title'] . "</br>员工：" . $staff['realname'];
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
                                // $arr[$k]['couponinfo'] = pdo_fetch("SELECT b.title,b.price FROM ".tablename('sudu8_page_coupon_user')." as a LEFT JOIN  ".tablename('sudu8_page_coupon')." as b on a.cid = b.id WHERE a.uniacid = :uniacid and a.flag = 1 and a.id=:coupon",array(":uniacid"=>$uniacid,":coupon"=>$res['coupon']));
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
                     // dump($neworder['data']);die;
                $this->assign('neworder',$neworder);
                $this->assign('order',$order);
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
            return $this->fetch('yuyue');
        }else{
            $this->redirect('Login/index');
        }
    }

    public function video(){
        if(check_login()){
            if(powerget()){
                $id = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                if(input('order')){
                    $order = Db::name('wd_xcx_video_pay')->alias("a")->join('wd_xcx_products b','a.pid = b.id')->join("wd_xcx_superuser c","a.suid = c.id")->where("a.uniacid",$id)->where("c.uniacid",$id)->where("a.orderid",'like',"%".input('order')."%")->order('a.creattime desc')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                    $count = Db::name('wd_xcx_video_pay')->alias("a")->join('wd_xcx_products b','a.pid = b.id')->join("wd_xcx_superuser c","a.suid = c.id")->where("a.uniacid",$id)->where("c.uniacid",$id)->where("a.orderid",'like',"%".input('order')."%")->count();
                }else{
                   $order = Db::name('wd_xcx_video_pay')->alias("a")->join('wd_xcx_products b','a.pid = b.id')->join("wd_xcx_superuser c","a.suid = c.id")->where("a.uniacid",$id)->where("c.uniacid",$id)->order('a.creattime desc')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                    $count = Db::name('wd_xcx_video_pay')->alias("a")->join('wd_xcx_products b','a.pid = b.id')->join("wd_xcx_user c","a.suid = c.id")->where("a.uniacid",$id)->where("c.uniacid",$id)->count(); 
                }
                $orderList = $order->toArray();
                if(count($orderList['data']) > 0){
                    foreach ($orderList['data'] as $key => &$value) {
                        $infos = getNameAvatar($value['suid'], $id);
                        $value['nickname'] = $infos['nickname'];
                        $value['avatar'] = $infos['avatar'];
                    }
                }
                
                $this->assign('order', $orderList['data']);
                $this->assign('page',$order->render());
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
            return $this->fetch('video');
        }else{
            $this->redirect('Login/index');
        }
    }
    public function hexiao(){
        $order = input("order");
        $data['custime'] = time();
        $data['flag'] = 2;
        $data['hxinfo'] = 'a:1:{i:0;i:1;}';
        $res = Db::name('wd_xcx_order')->where('order_id', $order)->update($data);
        if($res){
            $this->success("核销成功！");
        }
    }
    //发货  核销  取消订单
    public function order(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $order = input('orderid');
                $op = input('op');
                if($op == 'hx'){
                    $data['custime'] = time();
                    $data['flag'] = 2;
                    $data['hxinfo'] = 'a:1:{i:0;i:1;}';
                    $res = Db::name('wd_xcx_order')->where('id', $order)->update($data);
                    if($res){
                        $this->success("核销成功！");
                    }
                }
                if($op== 'fahuo'){
                    $data['custime'] = time();
                    $data['kuaidi'] = input('kuaidi');
                    $data['kuaidihao'] = input('kuaidihao');
                    $data['flag'] = 4;
                    $res = Db::name('wd_xcx_order')->where("id",$order)->update($data);

                    if($res){
                        $this->success("发货成功");
                    }
                    
                }
                //取消订单   confirmtk   取消订单
                if($op == "qx" || $op == "confirmtk"){
                    $order_id = input('orderid');
                    if(input('qxbeizhu')){
                        $data['qxbeizhu'] = input('qxbeizhu');
                    }
                    $now = time();
                    $out_refund_no = date("Y",$now).date("m",$now).date("d",$now).date("H",$now).date("i",$now).date("s",$now).rand(1000,9999);
                    $data['th_orderid'] = $out_refund_no;
                    // pdo_update("sudu8_page_order", $data, array("uniacid"=>$uniacid, "id"=>$order_id));
                    Db::name('wd_xcx_order') ->where('uniacid', $appletid) ->where('id', $order_id) ->update($data);
                    // $order = pdo_get("sudu8_page_order", array("uniacid"=>$uniacid, "id"=>$order_id));
                    $order = Db::name('wd_xcx_order') ->where('uniacid', $appletid) ->where('id', $order_id) ->find();
                    // $types = ($op == "confirmtk") ? "dantk" : "danqx";
                    $order_product = Db::name('wd_xcx_products') ->where('id', $order['pid']) ->find();
                    if($order['pay_price'] > 0){
                        $app = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                        $mchid = $app['mchid'];   //商户号
                        $apiKey = $app['signkey'];    //商户的秘钥
                        $appid = $app['appID'];                 //小程序的id
                        $appkey = $app['appSecret'];            //小程序的秘钥
                         // 更新信息
                        $sqtx = Db::name('wd_xcx_order')->where("uniacid",$appletid)->where("id",$order_id)->find();
                        $openid= $sqtx['openid'];    //申请者的openid
                        $outTradeNo = $sqtx['order_id'];
                        $totalFee= $sqtx['pay_price']*100;  //申请了提现多少钱
                        $outRefundNo = $sqtx['order_id']; //商户订单号
                        $refundFee= $sqtx['pay_price']*100;  //申请了提现多少钱
                        $SSLCERT_PATH = ROOT_PATH.'public/Cert/'.$appletid.'/apiclient_cert.pem';//证书路径
                        $SSLKEY_PATH =  ROOT_PATH.'public/Cert/'.$appletid.'/apiclient_key.pem';//证书路径
                        $opUserId = $mchid;//商户号
                        include "WinXinRefund.php";
                        $weixinpay = new WinXinRefund($openid,$outTradeNo,$totalFee,$outRefundNo,$refundFee,$SSLCERT_PATH,$SSLKEY_PATH,$opUserId,$appid,$apiKey);
                        $return = $weixinpay->refund();
                        if(!$return){
                            $this->error('退货失败!请检查系统设置->小程序设置和支付设置');
                        }else{
                            Db::name('wd_xcx_order') ->where('uniacid', $appletid) ->where('th_orderid', $out_refund_no) ->update(['flag' => 5]);
                            
                            //金钱流水
                            $xfmoney = array(
                                "uniacid" => $appletid,
                                "orderid" => $order['order_id'],
                                "uid" => $order['uid'],
                                "type" => "add",
                                "score" => $order['pay_price'],
                                "message" => "退款退回微信",
                                "creattime" => time()
                            );
                            // pdo_insert("sudu8_page_money", $xfmoney);
                            Db::name('wd_xcx_money') ->insert($xfmoney);
                            $tk_je = $order['true_price'] - $order['pay_price']; //退回余额
                            if($tk_je > 0){
                                $xfmoney1 = array(
                                    "uniacid" => $appletid,
                                    "orderid" => $order['order_id'],
                                    "uid" => $order['uid'],
                                    "type" => "add",
                                    "score" => $tk_je,
                                    "message" => "退款退回余额",
                                    "creattime" => time()
                                );
                                // pdo_insert("sudu8_page_money", $xfmoney1);
                                Db::name('wd_xcx_money') ->insert($xfmoney1);
                                Db::execute("UPDATE {$this->prefix}wd_xcx_user set money = money + ".$tk_je." where uniacid = ".$appletid." and id = ".$order['uid']);
                            }
                            if($order['coupon']){
                                // pdo_update("sudu8_page_coupon_user", array("flag"=>0), array("uniacid"=>$uniacid, "uid"=>$order['uid'], "id"=>$order['coupon']));
                                Db::name('wd_xcx_coupon_user') ->where('uniacid', $appletid) ->where('uid', $order['uid']) ->where('id', $order['coupon']) ->update(array('flag' => 0,"utime"=>0));
                            }
                            if($order['dkscore']){
                                // pdo_query("UPDATE ".tablename("sudu8_page_user")." SET score = score + ".$order['dkscore']." WHERE uniacid = :uniacid and id = :id", array(":uniacid"=>$uniacid, ":id"=>$order["uid"]));
                                Db::execute("UPDATE {$this->prefix}wd_xcx_user set score = score + ".$order['dkscore']." where uniacid = ".$appletid." and id = ".$order['uid']);
                                $score_data = array(
                                    "uniacid" => $appletid,
                                    "orderid" => $order['order_id'],
                                    "uid" => $order['uid'],
                                    "type" => "add",
                                    "score" => $order['dkscore'],
                                    "message" => "退款退回抵扣积分",
                                    "creattime" => time()
                                );
                                // pdo_insert("sudu8_page_score", $score_data);
                                Db::name('wd_xcx_score') ->insert($score_data);
                            }
                            //处理库存与真实销量
                            if($order_product['pro_kc'] == -1){ //无限量库存
                                if($order['num'] > 0){
                                    Db::execute("UPDATE {$this->prefix}wd_xcx_products set sale_tnum = sale_tnum - ".$order['num']." where uniacid = ".$appletid." and id = ".$order['pid']);
                                }
                            }else{   //有限量库存
                                if($order['num'] > 0){
                                    // pdo_query("UPDATE ".tablename("sudu8_page_products")." SET pro_kc = pro_kc + ".$order['num']." WHERE uniacid = :uniacid and id = :id", array(":uniacid"=>$uniacid, ":id"=>$order['pid']));
                                    Db::execute("UPDATE {$this->prefix}wd_xcx_products set pro_kc = pro_kc - ".$order['num']." where uniacid = ".$appletid." and id = ".$order['pid']);
                                    Db::execute("UPDATE {$this->prefix}wd_xcx_products set sale_tnum = sale_tnum - ".$order['num']." where uniacid = ".$appletid." and id = ".$order['pid']);
                                }
                            }
                        }
                    }else{
                        // if($op == "confirmtk"){
                        //     // pdo_update("sudu8_page_order", array("flag"=>8), array("uniacid"=>$uniacid, "th_orderid"=>$out_refund_no));
                        //     Db::name('wd_xcx_order') ->where('uniacid', $appletid) ->where('th_orderid', $out_refund_no) ->update(['flag' => 8]);
                        // }else{
                            // pdo_update("sudu8_page_order", array("flag"=>5), array("uniacid"=>$uniacid, "th_orderid"=>$out_refund_no));
                            Db::name('wd_xcx_order') ->where('uniacid', $appletid) ->where('th_orderid', $out_refund_no) ->update(['flag' => 5]);
                        // }
                        //金钱流水
                        if($order['true_price'] > 0){
                            $xfmoney = array(
                                "uniacid" => $appletid,
                                "orderid" => $order['order_id'],
                                "uid" => $order['uid'],
                                "type" => "add",
                                "score" => $order['true_price'],
                                "message" => "退款退回余额",
                                "creattime" => time()
                            );
                            // pdo_insert("sudu8_page_money", $xfmoney);
                            Db::name('wd_xcx_money') ->insert($xfmoney);
                        }
                        // $order = pdo_get("sudu8_page_order", array("uniacid"=>$uniacid, "th_orderid"=>$out_refund_no));
                        $order = Db::name('wd_xcx_order') ->where('uniacid', $appletid) ->where('th_orderid', $out_refund_no) ->find();
                        // pdo_query("UPDATE ".tablename("sudu8_page_user")." SET money = money + ".$order['true_price']." WHERE uniacid = :uniacid and id = :id", array(":uniacid"=>$uniacid, ":id"=>$order["uid"]));
                        
                        Db::execute("UPDATE {$this->prefix}wd_xcx_user set money = money + ".$order['true_price']." where uniacid = ".$appletid." and id = ".$order['uid']);
                        if($order['coupon']){
                            // pdo_update("sudu8_page_coupon_user", array("flag"=>0), array("uniacid"=>$uniacid, "uid"=>$order['uid'], "id"=>$order['coupon']));
                            Db::name('wd_xcx_coupon_user') ->where('uniacid', $appletid) ->where('uid', $order['uid']) ->where('id', $order['coupon']) ->update(array('flag' => 0,"utime"=>0));
                        }
                        if($order['dkscore']){
                            // pdo_query("UPDATE ".tablename("sudu8_page_user")." SET score = score + ".$order['dkscore']." WHERE uniacid = :uniacid and id = :id", array(":uniacid"=>$uniacid, ":id"=>$order["uid"]));
                            Db::execute("UPDATE {$this->prefix}wd_xcx_user set score = score + ".$order['dkscore']." where uniacid = ".$appletid." and id = ".$order['uid']);
                            $score_data = array(
                                "uniacid" => $appletid,
                                "orderid" => $order['order_id'],
                                "uid" => $order['uid'],
                                "type" => "add",
                                "score" => $order['dkscore'],
                                "message" => "退款退回抵扣积分",
                                "creattime" => time()
                            );
                            // pdo_insert("sudu8_page_score", $score_data);
                            Db::name('wd_xcx_score') ->insert($score_data);
                        }
                        // $scoreback = pdo_get("sudu8_page_score", array("uniacid"=>$uniacid, "uid"=>$order["uid"], "orderid"=>$order['order_id'], "type"=>"add", "message"=>"买送积分"));
                        $scoreback = Db::name('wd_xcx_score') ->where('uniacid', $appletid) ->where('uid', $order['uid']) ->where('orderid', $order['order_id']) ->where('message', ',买送积分') ->find();
                        if($scoreback){
                            // pdo_query("UPDATE ".tablename("sudu8_page_user")." SET score = score - ".$scoreback['score']." WHERE uniacid = :uniacid and id = :id", array(":uniacid"=>$uniacid, ":id"=>$order["uid"]));
                            Db::execute("UPDATE {$this->prefix}wd_xcx_user set score = score - ".$scoreback['score']." where uniacid = ".$appletid." and id = ".$order['uid']);
                            $score_data2 = array(
                                "uniacid" => $appletid,
                                "orderid" => $order['order_id'],
                                "uid" => $order['uid'],
                                "type" => "del",
                                "score" => $scoreback['score'],
                                "message" => "退款扣除买送积分",
                                "creattime" => time()
                            );
                            // pdo_insert("sudu8_page_score", $score_data2);
                            Db::name('wd_xcx_score') ->insert($score_data2);
                        }
                        //处理库存与真实销量
                        if($order_product['pro_kc'] == -1){ //无限量库存
                            if($order['num'] > 0){
                                Db::execute("UPDATE {$this->prefix}wd_xcx_products set sale_tnum = sale_tnum - ".$order['num']." where uniacid = ".$appletid." and id = ".$order['pid']);
                            }
                        }else{   //有限量库存
                            if($order['num'] > 0){
                                // pdo_query("UPDATE ".tablename("sudu8_page_products")." SET pro_kc = pro_kc + ".$order['num']." WHERE uniacid = :uniacid and id = :id", array(":uniacid"=>$uniacid, ":id"=>$order['pid']));
                                Db::execute("UPDATE {$this->prefix}wd_xcx_products set pro_kc = pro_kc - ".$order['num']." where uniacid = ".$appletid." and id = ".$order['pid']);
                                Db::execute("UPDATE {$this->prefix}wd_xcx_products set sale_tnum = sale_tnum - ".$order['num']." where uniacid = ".$appletid." and id = ".$order['pid']);
                            }
                        }
                        
                    }
                    $this ->success('取消成功!');
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
            return $this->fetch('video');
        }else{
            $this->redirect('Login/index');
        }
    }
    
    public function queren(){
        $order = input("order");
        $data['custime'] = time();
        $data['flag'] = 1;
        $data['emp_id'] = input('emp');
        $res = Db::name('wd_xcx_order')->where('id', $order)->update($data);
        if($res){
            $this->success("确认成功！");
        }
    }
    public function orderdown(){
        $id = input("appletid");
        $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
        if(!$res){
            $this->error("找不到对应的小程序！");
        }
        $this->assign('applet',$res);
        $is_more = input("is_more");
        if($is_more==0) {
            $search_flag = input("search_flag");
            $search_type = input("search_type");
            $search_keys = input("search_keys");
            $start_get = input("start_get");
            $end_get = input("end_get");

            $where = "";
            if ($search_flag != null && $search_flag != 'undefined') {
                if ($where != "") {
                    $where .= "and ";
                }

                if($search_flag == 1){  //待发货
                    $where .= "a.flag = 1 && a.nav = 1";
                }elseif($search_flag == 10){   //待消费
                    $where .= "a.flag = 1 && a.nav = 2";
                }else{
                    $where .= "and a.flag = " . $search_flag;
                }

            }
            if (!empty($start_get)) {//时间开始
                if ($where != "") {
                    $where .= ' and a.creattime >= ' . strtotime($start_get);
                } else {
                    $where .= ' a.creattime >= ' . strtotime($start_get);
                }

            }
            if (!empty($end_get)) {//时间结束
                if ($where != "") {
                    $where .= ' and a.creattime <= ' . strtotime($end_get);
                } else {
                    $where .= ' a.creattime <= ' . strtotime($end_get);
                }

            }
            if (!empty($search_keys)) {
                if ($where != "") {
                    if (!empty($search_type)) {
                        if ($search_type == 1) { //订单号
                            $where .= " and a.order_id like '%" . trim($search_keys) . "%'";
                        } else if ($search_type == 2) {
                            $where .= " and b.name like '%" . trim($search_keys) . "%'";
                        } else if ($search_type == 3) {
                            $where .= " and b.mobile like '%" . trim($search_keys) . "%'";
                        } else if ($search_type == 4) {
                            $where .= " and (b.address like '%" . trim($search_keys) . "%' or b.more_address like '%" . trim($search_keys) . "%')";
                        }
                    }
                } else {
                    if (!empty($search_type)) {
                        if ($search_type == 1) { //订单号
                            $where .= "  a.order_id like '%" . trim($search_keys) . "%'";
                        } else if ($search_type == 2) {
                            $where .= " b.name like '%" . trim($search_keys) . "%'";
                        } else if ($search_type == 3) {
                            $where .= "  b.mobile like '%" . trim($search_keys) . "%'";
                        } else if ($search_type == 4) {
                            $where .= " (b.address like '%" . trim($search_keys) . "%' or b.more_address like '%" . trim($search_keys) . "%')";
                        }
                    }
                }

            }
        }else{
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

        }
        $order=Db::name("wd_xcx_order")->alias("a")->join("wd_xcx_duo_products_address b",'a.address=b.id','left')->where('a.is_more',$is_more)->where("a.uniacid",$id)->where($where)->order("a.creattime","DESC")->field("a.*,b.name,b.mobile,b.address,b.more_address")->select();
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
            $user = Db::name('wd_xcx_user')->where("uniacid",$row['uniacid'])->where("id",$row['uid'])->find();
//            if($user['nickname']){
//                $row['nickname'] = rawurldecode($user['nickname']);
//            }else{
//                $row['nickname'] = "";
//            }
            if(!$row['mobile']){
                $row['mobile'] = $user['mobile'];
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
//        $objPHPExcel->getActiveSheet()->setCellValue('D1', '产品分类');
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
//        $objPHPExcel->getActiveSheet()->setCellValue('N1', '用户昵称');
        $objPHPExcel->getActiveSheet()->setCellValue('N1', '快递');
        $objPHPExcel->getActiveSheet()->setCellValue('O1', '快递号');
        foreach($order as $k => $v){
            $num=$k+2;
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('A'.$num, $v['order_id'],'s');
            // // 图片生成
            // $objDrawing[$k] = new \PHPExcel_Worksheet_Drawing();
            // $objDrawing[$k]->setPath(ROOT_PATH.'public/upimages/20180516/355184cf8a623d73a1b04c989b7bbe2c756.png');
            // // 设置宽度高度
            // $objDrawing[$k]->setHeight(80);//照片高度
            // $objDrawing[$k]->setWidth(80); //照片宽度
            // /*设置图片要插入的单元格*/
            // $objDrawing[$k]->setCoordinates('B'.$k);
            // // 图片偏移距离
            // $objDrawing[$k]->setOffsetX(12);
            // $objDrawing[$k]->setOffsetY(12);
            // $objDrawing[$k]->setWorksheet($objPHPExcel->getActiveSheet());
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$num, remote($v['uniacid'],$v['thumb'],1));
            if($is_more==0){
                $objPHPExcel->getActiveSheet()->setCellValue('C'.$num, $v['product']);
            }
            if($is_more==1){
                $objPHPExcel->getActiveSheet()->setCellValue('C'.$num, $v['product']."-".$v['order_duo'][0][0]);
            }
//            $objPHPExcel->getActiveSheet()->setCellValue('D'.$num, $v['name']);
            if($is_more==0){
                $objPHPExcel->getActiveSheet()->setCellValue('D'.$num, $v['price']."*".$v['num']);
                $objPHPExcel->getActiveSheet()->setCellValue('E'.$num, $v['price']*$v['num']);
            }
            if($is_more==1){
                $objPHPExcel->getActiveSheet()->setCellValue('D'.$num, $v['order_duo'][0][1]."*".$v['order_duo'][0][4]);
                $objPHPExcel->getActiveSheet()->setCellValue('E'.$num, $v['true_price']);
            }
            if($is_more==0){
            $objPHPExcel->getActiveSheet()->setCellValue('F'.$num, $v['name']);
            $objPHPExcel->getActiveSheet()->setCellValue('G'.$num, $v['mobile']);
            }
            if($is_more==1){
                $objPHPExcel->getActiveSheet()->setCellValue('F'.$num, $v['pro_user_name']);
                $objPHPExcel->getActiveSheet()->setCellValue('G'.$num, $v['pro_user_tel']);
            }
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
            if($v['flag']==2 || $v['flag'] == 8){
                $flag = "已完成";
            }
            if($v['flag']==3){
                $flag = "确认订单";
            }
            if($v['flag'] == 4){
                $flag = "已发货";
            }
            if($v['flag'] == 5){
                $flag = "已取消";
            }
            $objPHPExcel->getActiveSheet()->setCellValue('I'.$num, $flag);
            $objPHPExcel->getActiveSheet()->setCellValue('J'.$num, $v['creattime']);
            $objPHPExcel->getActiveSheet()->setCellValue('K'.$num, $v['beizhu']);
            if($is_more==0){
            $objPHPExcel->getActiveSheet()->setCellValue('L'.$num, $v['address'].''.$v['more_address']);
            }
            if($is_more==1){
//
                $objPHPExcel->getActiveSheet()->setCellValue('L'.$num, $v['address'].''.$v['pro_user_add']);
            }
            $objPHPExcel->getActiveSheet()->setCellValue('M'.$num, $v['uniacid']);
//            $objPHPExcel->getActiveSheet()->setCellValue('N'.$num, $v['nickname']);
            $objPHPExcel->getActiveSheet()->setCellValue('N'.$num, $v['kuaidi']);
            $objPHPExcel->getActiveSheet()->setCellValue('O'.$num, $v['kuaidihao']);
        }
        $objPHPExcel->getActiveSheet()->setTitle('导出订单列表');
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        if($is_more==0){
            header('Content-Disposition: attachment;filename="秒杀订单列表.xls"');
        }
        if($is_more==1){
            header('Content-Disposition: attachment;filename="预约预定订单列表.xls"');
        }
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        
    }
    public function videodown(){
        $id = input("appletid");
        $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
        if(!$res){
            $this->error("找不到对应的小程序！");
        }
        $this->assign('applet',$res); 
        $is_more = input("is_more");
        $order = Db::name('wd_xcx_video_pay')->alias("a")->join('wd_xcx_products b','a.pid = b.id')->join("wd_xcx_superuser c","a.suid = c.id")->join('wd_xcx_cate d','b.cid = d.id')->where("a.uniacid",$id)->where("c.uniacid",$id)->order('a.creattime desc')->field("a.uniacid,a.suid,a.orderid,a.paymoney,a.creattime,b.title,b.thumb,b.art_type,d.name")->select();
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
        $objPHPExcel->getActiveSheet()->setCellValue('C1', '文章名称');
        $objPHPExcel->getActiveSheet()->setCellValue('D1', '文章类型');
        $objPHPExcel->getActiveSheet()->setCellValue('E1', '产品分类');
        $objPHPExcel->getActiveSheet()->setCellValue('F1', '价格');
        $objPHPExcel->getActiveSheet()->setCellValue('G1', '用户昵称');
        $objPHPExcel->getActiveSheet()->setCellValue('H1', '下单时间');
        $objPHPExcel->getActiveSheet()->setCellValue('I1', '小程序uniacid');
        foreach($order as $k => $v){
            $num=$k+2;
            $infos = getNameAvatar($v['suid'], $id, 1);
            $v['nickname'] = $infos['nickname'];

            if($v['art_type'] == 1){
                $v['art_type'] = "付费文章";
            }else if($v['art_type'] == 2){
                $v['art_type'] = "付费视频";
            }else if($v['art_type'] == 3){
                $v['art_type'] = "付费音频";
            }

            if(strpos($v['thumb'], 'http') === false){
                $v['thumb'] = remote($id, $v['thumb'], 1);
            }

            $creattimes = date("Y-m-d H:i:s", $v['creattime']);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('A'.$num, $v['orderid'],'s');
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$num, $v['thumb']);
            $objPHPExcel->getActiveSheet()->setCellValue('C'.$num, $v['title']);
            $objPHPExcel->getActiveSheet()->setCellValue('D'.$num, $v['art_type']);
            $objPHPExcel->getActiveSheet()->setCellValue('E'.$num, $v['name']);
            $objPHPExcel->getActiveSheet()->setCellValue('F'.$num, $v['paymoney']);
            $objPHPExcel->getActiveSheet()->setCellValue('G'.$num, $v['nickname']);
            $objPHPExcel->getActiveSheet()->setCellValue('H'.$num, $creattimes);
            $objPHPExcel->getActiveSheet()->setCellValue('I'.$num, $v['uniacid']);
        }
        $objPHPExcel->getActiveSheet()->setTitle('导出订单列表');
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="付费视频订单列表.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        
    }
    //修改时间
    public function changedate(){
        $uniacid = input('appletid');
        $newdate = input('newdate');
        $id = input('id');
        // pdo_update("sudu8_page_order", array("appoint_date"=>strtotime($newdate)), array("uniacid"=>$uniacid, "id"=>$id));
        $res = Db::name('wd_xcx_order') ->where('uniacid', $uniacid) ->where('id', $id) ->update(['appoint_date' => strtotime($newdate)]);
        if($res){
            $this->success("修改成功");
        }
                    
    }
    //预约预定取消订单
    public function quxiao(){
        $id = input('order');
        $uniacid = input('appletid');
        $opt = input('opt');
        $now = time();
        $out_refund_no = date("Y",$now).date("m",$now).date("d",$now).date("H",$now).date("i",$now).date("s",$now).rand(1000,9999);
        // pdo_update("sudu8_page_order", array("th_orderid" => $out_refund_no), array("uniacid"=>$uniacid, "id"=>$id));
        Db::name('wd_xcx_order') ->where('uniacid', $uniacid) ->where('order_id', $id) ->update(['th_orderid' => $out_refund_no]);
        // $order = pdo_get("sudu8_page_order", array("uniacid"=>$uniacid, "id"=>$id));
        $order = Db::name('wd_xcx_order') ->where('uniacid', $uniacid) ->where('order_id', $id) ->find();
        // $beforedays = pdo_getcolumn("sudu8_page_products", array("uniacid"=>$uniacid, "id"=>$order['pid']), "beforedays");
        // $beforedays = Db::name('wd_xcx_products') ->where('uniacid', $uniacid) ->where('id', $order['pid']) ->field('beforedays') ->find();
        // $beforedays = $beforedays['beforedays'];
        // $types = ($opt == "confirmqx") ? "yuyue" : "yuyueqx";
        $product = Db::name('wd_xcx_products') ->where('uniacid', $uniacid) ->where('id', $order['pid']) ->find();
        if($order['pay_price'] > 0){
            $app = Db::name('wd_xcx_applet')->where("id",$uniacid)->find();
            $mchid = $app['mchid'];   //商户号
            $apiKey = $app['signkey'];    //商户的秘钥
            $appid = $app['appID'];                 //小程序的id
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
                $this->error('退货失败!请检查系统设置->小程序设置和支付设置');
            }else{
                if($opt == "confirmqx"){
                     //pdo_update("sudu8_page_order", array("flag"=>8), array("uniacid"=>$uniacid, "th_orderid"=>$out_refund_no));
                    Db::name('wd_xcx_order') ->where('uniacid', $uniacid) ->where('th_orderid', $out_refund_no) ->update(['flag' => 8]);
                }else{
                    // pdo_update("sudu8_page_order", array("flag"=>5), array("uniacid"=>$uniacid, "th_orderid"=>$out_refund_no));
                    Db::name('wd_xcx_order') ->where('uniacid', $uniacid) ->where('th_orderid', $out_refund_no) ->update(['flag' => 5]);
                }
                //金钱流水
                $xfmoney = array(
                    "uniacid" => $uniacid,
                    "orderid" => $order['order_id'],
                    "uid" => $order['uid'],
                    "type" => "add",
                    "score" => $order['pay_price'],
                    "message" => "退款退回微信",
                    "creattime" => time()
                );
                // pdo_insert("sudu8_page_money", $xfmoney);
                Db::name('wd_xcx_money') ->insert($xfmoney);
                $tk_je = $order['true_price'] - $order['pay_price']; //退回余额
                if($tk_je > 0){
                    $xfmoney1 = array(
                        "uniacid" => $uniacid,
                        "orderid" => $order['order_id'],
                        "uid" => $order['uid'],
                        "type" => "add",
                        "score" => $tk_je,
                        "message" => "退款退回余额",
                        "creattime" => time()
                    );
                    // pdo_insert("sudu8_page_money", $xfmoney1);
                    Db::name('wd_xcx_money') ->insert($xfmoney1);
                }
                if($order['coupon']){
                    // pdo_update("sudu8_page_coupon_user", array("flag"=>0), array("uniacid"=>$uniacid, "uid"=>$order['uid'], "id"=>$order['coupon']));
                    Db::name('wd_xcx_coupon_user') ->where('uniacid', $uniacid) ->where('uid', $order['uid']) ->where('id', $order['coupon']) ->update(array('flag' => 0,"utime"=>0));
                }
                if($order['dkscore']){
                    // pdo_query("UPDATE ".tablename("sudu8_page_user")." SET score = score + ".$order['dkscore']." WHERE uniacid = :uniacid and id = :id", array(":uniacid"=>$uniacid, ":id"=>$order["uid"]));
                    Db::execute("UPDATE {$this->prefix}wd_xcx_user set score = score + ".$order['dkscore']." where uniacid = ".$appletid." and id = ".$order['uid']);
                    $score_data = array(
                        "uniacid" => $uniacid,
                        "orderid" => $order['order_id'],
                        "uid" => $order['uid'],
                        "type" => "add",
                        "score" => $order['dkscore'],
                        "message" => "退款退回抵扣积分",
                        "creattime" => time()
                    );
                    // pdo_insert("sudu8_page_score", $score_data);
                    Db::name('wd_xcx_score') ->insert($score_data);
                }
                // //处理库存与真实销量
                // Db::execute("UPDATE {$this->prefix}wd_xcx_products set pro_kc = pro_kc - ".$order['num']." where uniacid = ".$uniacid." and id = ".$order['pid']);
                // Db::execute("UPDATE {$this->prefix}wd_xcx_products set sale_tnum = sale_tnum - ".$order['num']." where uniacid = ".$uniacid." and id = ".$order['pid']);
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
                    // pdo_update("sudu8_page_products", array("sale_tnum"=>$sale_tnum, "more_type_num" => serialize($more_type_num),"more_type" => serialize($more_type)), array("uniacid"=>$uniacid, "id"=>$product['id']));
                    Db::name('wd_xcx_products') ->where('uniacid', $uniacid) ->where('id', $product['id']) ->update(['sale_tnum' => $sale_tnum, 'more_type_num' => serialize($more_type_num), 'more_type'=> serialize($more_type)]);
                }else{
                    //更新选择座位状态
                    //Db::name('wd_xcx_tableselect') ->where('uniacid', $uniacid) ->where('pid', $id) ->where('appoint_date', $date) ->where('flag', 1) 
                    Db::name('wd_xcx_tableselect')->where('id', $order['tsid']) ->update(['flag' => 2]);
                    $table_select = Db::name('wd_xcx_tableselect') ->where('id', $order['tsid']) ->find();
                    $temp_select = explode(',', $table_select['select_str']);
                    $count = count($temp_select);

                    $sale_tnum = $product['sale_tnum'] - $count;
                    Db::name('wd_xcx_products') ->where('id', $product['id']) ->update(['sale_tnum'=>$sale_tnum]);
                }
            }
        }else{
            if($opt == "confirmqx"){
                 //pdo_update("sudu8_page_order", array("flag"=>8), array("uniacid"=>$uniacid, "th_orderid"=>$out_refund_no));
                Db::name('wd_xcx_order') ->where('uniacid', $uniacid) ->where('th_orderid', $out_refund_no) ->update(['flag' => 8]);
            }else{
                // pdo_update("sudu8_page_order", array("flag"=>5), array("uniacid"=>$uniacid, "th_orderid"=>$out_refund_no));
                Db::name('wd_xcx_order') ->where('uniacid', $uniacid) ->where('th_orderid', $out_refund_no) ->update(['flag' => 5]);
            }
            //金钱流水
            if($order['true_price'] > 0){
                $xfmoney = array(
                    "uniacid" => $uniacid,
                    "orderid" => $order['order_id'],
                    "uid" => $order['uid'],
                    "type" => "add",
                    "score" => $order['true_price'],
                    "message" => "退款退回余额",
                    "creattime" => time()
                );
                // pdo_insert("sudu8_page_money", $xfmoney);
                Db::name('wd_xcx_money') ->insert($xfmoney);
            }
            // $order = pdo_get("sudu8_page_order", array("uniacid"=>$uniacid, "th_orderid"=>$out_refund_no));
            $order = Db::name('wd_xcx_order') ->where('uniacid', $uniacid) ->where('th_orderid', $out_refund_no) ->find();
            
            // pdo_query("UPDATE ".tablename("sudu8_page_user")." SET money = money + ".$order['true_price']." WHERE uniacid = :uniacid and id = :id", array(":uniacid"=>$uniacid, ":id"=>$order["uid"]));
            Db::execute("UPDATE {$this->prefix}wd_xcx_user set money = money + ".$order['true_price']." where uniacid = ".$uniacid." and id = ".$order['uid']);
            if($order['tsid'] > 0){
                // pdo_update("sudu8_page_tableselect", array("flag"=>2), array("uniacid"=>$uniacid, "id"=>$order['tsid']));
                Db::name('wd_xcx_tableselect') ->where('uniacid', $uniacid) ->where('id', $order['tsid']) ->update(['flag'=>2]);
            }else{
                // $pro = pdo_get("sudu8_page_products", array("uniacid"=>$uniacid, "id"=>$order['pid']));
                $pro = Db::name('wd_xcx_products') ->where('uniacid', $uniacid) ->where('id', $order['pid']) ->find();
                $more_type_num = unserialize($pro['more_type_num']);
                $order_duo = unserialize($order['order_duo']);
                foreach ($order_duo as $key => &$value) {
                    $more_type_num[$key]['shennum'] += $value[4];
                }
                $more_type_num = serialize($more_type_num);
                // pdo_update("sudu8_page_products", array("more_type_num"=>$more_type_num), array("uniacid"=>$uniacid, "id"=>$order['pid']));
                Db::name('wd_xcx_products') ->where('uniacid', $uniacid) ->where('id', $order['pid']) ->update(['more_type_num' => $more_type_num]);
            }
            if($order['coupon']){
                // pdo_update("sudu8_page_coupon_user", array("flag"=>0,"utime" => 0), array("uniacid"=>$uniacid, "uid"=>$order['uid'], "id"=>$order['coupon']));
                Db::name('wd_xcx_coupon_user') ->where('uniacid', $uniacid) ->where('uid', $order['uid']) ->where('id', $order['coupon']) ->update(['flag'=>0, 'utime'=> 0]); 
            }
            
            if($order['dkscore']){
                // pdo_query("UPDATE ".tablename("sudu8_page_user")." SET score = score + ".$order['dkscore']." WHERE uniacid = :uniacid and id = :id", array(":uniacid"=>$uniacid, ":id"=>$order["uid"]));
                Db::execute("UPDATE {$this->prefix}wd_xcx_user set score = score + ".$order['dkscore']." where uniacid = ".$uniacid." and id = ".$order['uid']);
                $score_data = array(
                    "uniacid" => $uniacid,
                    "orderid" => $order['order_id'],
                    "uid" => $order['uid'],
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
                // pdo_update("sudu8_page_products", array("sale_tnum"=>$sale_tnum, "more_type_num" => serialize($more_type_num),"more_type" => serialize($more_type)), array("uniacid"=>$uniacid, "id"=>$product['id']));
                Db::name('wd_xcx_products') ->where('uniacid', $uniacid) ->where('id', $product['id']) ->update(['sale_tnum' => $sale_tnum, 'more_type_num' => serialize($more_type_num), 'more_type'=> serialize($more_type)]);
            }else{
                //更新选择座位状态
                //Db::name('wd_xcx_tableselect') ->where('uniacid', $uniacid) ->where('pid', $id) ->where('appoint_date', $date) ->where('flag', 1) 
                Db::name('wd_xcx_tableselect')->where('id', $order['tsid']) ->update(['flag' => 2]);
                //减去销量
                $table_select = Db::name('wd_xcx_tableselect') ->where('id', $order['tsid']) ->find();
                $temp_select = explode(',', $table_select['select_str']);
                $count = count($temp_select);

                $sale_tnum = $product['sale_tnum'] - $count;
                Db::name('wd_xcx_products') ->where('id', $product['id']) ->update(['sale_tnum'=>$sale_tnum]);

            }
           
        }
        $this ->success('取消成功!');
    }
    //确认修改
    public function acceptmodify(){
        $id = input('order');
        $uniacid = input('appletid');
        // $order = pdo_get("sudu8_page_order", array("uniacid"=>$uniacid, "id"=>$id));
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
        // pdo_update("sudu8_page_order", $data, array("uniacid"=>$uniacid, "id"=>$id));
        $res = Db::name('wd_xcx_order') ->where('uniacid', $uniacid) ->where('order_id', $id) ->update($data);
        if($res){
            $this ->success('客户修改申请已通过!');
        }
        // $data = array('op'=>'yyyd','cateid'=>input('cateid'),'chid'=>input('chid')));
        // return json_encode($data);
        // message('客户修改申请已通过!', $this->createWebUrl('Orderset', array('op'=>'yyyd','cateid'=>$_GPC['cateid'],'chid'=>$_GPC['chid'])), 'success');
    }
    //拒绝修改
    public function refusemodify(){
        $id = input('order');
        $uniacid = input('appletid');
        // $modify_info = pdo_getcolumn("sudu8_page_order", array("uniacid"=>$uniacid, "id"=>$id), "modify_info");
        $modify_info = Db::name('wd_xcx_order') ->where('uniacid', $uniacid) ->where('order_id', $id) ->field('modify_info') ->find();
        $modify_info = $modify_info['modify_info'];
        $modify_info = unserialize($modify_info);
        $modify_info['flag'] = 3;
        $modify_info = serialize($modify_info);
        // pdo_update("sudu8_page_order", array("modify_info"=>$modify_info), array("uniacid"=>$uniacid, "id"=>$id));
        $res = Db::name('wd_xcx_order') ->where('uniacid', $uniacid) ->where('order_id', $id) ->update(['modify_info' =>$modify_info]);
        // // message('客户修改申请已拒绝!', $this->createWebUrl('Orderset', array('op'=>'yyyd','cateid'=>$_GPC['cateid'],'chid'=>$_GPC['chid'])), 'success');
        // $data = array('op'=>'yyyd','cateid'=>input('cateid'),'chid'=>input('chid')));
        // return json_encode($data); 
        if($res){
            $this ->success('客户修改申请已拒绝!');
        }
    }

    public function refuseqx(){
        $id = input('order');
        $uniacid = input('appletid');
        // $pid = pdo_getcolumn("sudu8_page_order", array("uniacid"=>$uniacid, "id"=>$id), "pid");
        $pid = Db::name('wd_xcx_order') ->where('uniacid', $uniacid) ->where('order_id', $id) ->field('pid') ->find();
        // $pro_flag_ding = pdo_getcolumn("sudu8_page_products", array("uniacid"=>$uniacid, "id"=>$pid), "pro_flag_ding");
        $pro_flag_ding = Db::name('wd_xcx_products') ->where('uniacid', $uniacid) ->where('id', $pid['pid']) ->find();
        $flag = ($pro_flag_ding['pro_flag_ding'] == '0') ? 1 : 3;
        // pdo_update("sudu8_page_order", array("flag"=>$flag), array("uniacid"=>$uniacid, "id"=>$id));
        $res =  Db::name('wd_xcx_order') ->where('uniacid', $uniacid) ->where('order_id', $id) ->update(['flag' => $flag]);
        if($res){
            $this ->success('客户退款申请已拒绝!');
        }
        // message('已拒绝取消!', $this->createWebUrl('Orderset', array('op'=>'yyyd','cateid'=>$_GPC['cateid'],'chid'=>$_GPC['chid'])), 'success');
    }

    //快递鸟物流查询
    public function getwuliu(){
        $uniacid = input('uniacid');
        $kuaidi = input('kuaidi');
        $kuaidihao = input('kuaidihao');
        //获取物流接口设置
        $set = Db::name('wd_xcx_duo_products_yunfei') ->where('uniacid', $uniacid) ->find();
        if($set['api_type'] == 3){
            if($set['appcode']){
                $res  = $this -> getAliwuliu($set['appcode'], $kuaidi, $kuaidihao);
                return $res;
                exit;
            }else{
                return json_encode(array('type'=>'ali', 'list'=>'', 'status'=> -1));
            }
            
        }

        $kd_code = array(
            '顺丰速运' => 'SF',
            '韵达' => 'YD',
            '天天' => 'HHTT',
            '申通' => 'HLWL',
            '圆通' => 'YTO',
            '中通' => 'ZTO',
            '国通' => 'GTO',
            '百世汇通' => 'HTKY',
            'EMS'  => 'EMS',
            '邮政' => 'YZPY',
            'FEDEX联邦(国内件)' => 'FEDEX',
            '宅急送' => 'ZJS',
            '安捷快递' => 'AJ',
            '大田物流' => 'DTWL',
            '百福东方' => 'BFDF',
            '德邦' => 'DBLKY',
            'D速物流' => 'DSWL',
            'COE东方快递' => 'COE',
            '共速达' => 'GSD',
            '佳怡物流' => 'JYWL',
            '京广速递' => 'JGSD',
            '急先达' => 'JXD',
            '加运美' => 'JYM',
            '晋越快递' => 'JYKD',
            '全晨快递' => 'QCKD',
            '民航快递' => 'MHKD',
            '龙邦快递' => 'LB',
            '联昊通速递' => 'LHT',
            '全一快递' => 'UAPEX',
            '如风达' => 'RFD',
            '速尔快递' => 'SURE',
            '盛丰物流' => 'SFWL',
            '天地华宇' => 'HOAU',
            'TNT快递' => 'TNT',
            'UPS' => 'UPS',
            '万家物流' => 'WJWL',
            '信丰物流' => 'XFEX',
            '亚风快递' => 'YFSD',
            '优速' => 'UC',
            '远成物流' => 'YCWL',
            '运通快递' => 'YTKD',
            '源安达快递' => 'YADEX',
            '中铁快运' => 'ZTKY',
            '中邮快递' => 'ZYKD',
            '安能物流' => 'ANE',
            '九曳供应链' => 'JIUYE',
            '晟邦物流'=>'SBWL',
            '东骏快捷'=>'DJKJWL'
        );
        
        include 'KdApi.php';
        
        if(isset($kd_code[$kuaidi])){
            if($kuaidi){
                $kuaidi = $kd_code[$kuaidi];
            }
            $kd = new KdApi();
            $res = $kd->getOrderTracesByJson($uniacid, $kuaidi, $kuaidihao);
            // $data['data'] = $res;
            $res = json_decode($res, true);
            if($res['Success']){
                if(count($res['Traces']) > 0){
                    $status = 0;
                    $info = array_reverse($res['Traces']);
                }else{
                    $status = -1;
                    $info = '';
                }
            }else{
                $status = -1;
                $info = '';
            }
        }else{
            $status = -1;
            $info = '';
        }
        return json_encode(array('type'=>'kdniao', 'list'=>$info, 'status'=> $status));
    }

    //阿里云市场上的物流查询
    public function getAliwuliu($appcode, $kuaidi, $kuaidihao){
        // $kuaidi = input('kuaidi');
        // $kuaidihao = input('kuaidihao');
        
        $kd_code = array(
            '顺丰速运' => 'SFEXPRESS',
            '韵达' => 'YUNDA',
            '天天' => 'TTKDEX',
            '申通' => 'STO',
            '圆通' => 'YTO',
            '中通' => 'ZTO',
            '国通' => 'GTO',
            '百世汇通' => 'HTKY',
            'EMS'  => 'EMS',
            '邮政' => 'CHINAPOST',
            'FEDEX联邦(国内件)' => 'FEDEX',
            '宅急送' => 'ZJS',
            '安捷快递' => 'ANJELEX',
            '大田物流' => 'DTW',
            '百福东方' => 'EES',
            '德邦' => 'DEPPON',
            'D速物流' => 'DEXP',
            'COE东方快递' => 'COE',
            '共速达' => 'GSD',
            '佳怡物流' => 'JIAYI',
            '京广速递' => 'KKE',
            '急先达' => 'JOUST',
            '加运美' => 'TMS',
            '晋越快递' => 'PEWKEE',
            '全晨快递' => 'QCKD',
            '民航快递' => 'CAE',
            '龙邦快递' => 'LBEX',
            '联昊通速递' => 'LTS',
            '全一快递' => 'APEX',
            '如风达' => 'RFD',
            '速尔快递' => 'SURE',
            '盛丰物流' => 'SFWL',
            '天地华宇' => 'HOAU',
            'TNT快递' => 'TNT',
            'UPS' => 'UPS',
            '万家物流' => 'WANJIA',
            '信丰物流' => 'XFEXPRESS',
            '亚风快递' => 'BROADASIA',
            '优速' => 'UC56',
            '远成物流' => 'YCGWL',
            '运通快递' => 'YTEXPRESS',
            '源安达快递' => 'YADEX',
            '中铁快运' => 'CRE',
            '中邮快递' => 'CNPL',
            '安能物流' => 'ANE',
            '九曳供应链' => 'JIUYESCM',
            '东骏快捷'=>'DJ56',
            '万象'=>'EWINSHINE',
            '芝麻开门'=>'ZMKMEX'
        );
        $kuaidi = $kd_code[$kuaidi];
        // $data = $_POST;
        $host = "https://wuliu.market.alicloudapi.com";//api访问链接
        $path = "/kdi";//API访问后缀
        $method = "GET";
        $appcode = $appcode;  //阿里云云市场购买的 appcode
        $headers = array();
        array_push($headers, "Authorization:APPCODE " . $appcode);
        $querys = "no=$kuaidihao&type=$kuaidi";  //参数写在这里
        $bodys = "";
        $url = $host . $path . "?" . $querys;//url拼接

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        if (1 == strpos("$".$host, "https://"))
        {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        $data = curl_exec($curl);
        
        curl_close($curl);

        $res = json_decode($data, true);
        if($res['status'] == 0){
            if(count($res['result']['list']) > 0){
                $status = 0;
                $info = $res['result']['list'];
            }else{
                $status = -1;
                $info = '';
            }
        }else{
            $status = -1;
            $info = '';
        }
        return json_encode(array('type'=>'ali', 'list'=>$info, 'status'=> $status));
    }

}