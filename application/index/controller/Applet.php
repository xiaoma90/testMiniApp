<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;
use think\Validate;
class Applet extends Controller
{
    
    // 管理员小程序显示
    public function index()
    {
        if(check_login()){
            if(check_group()){
                // 删除bug文件
                $apiphp = ROOT_PATH.'public/api.php';
                if(file_exists($apiphp)){
                    unlink($apiphp);
                }
                $ueditorall = ROOT_PATH.'public/plugin/ueditor/ueditor.config.min.js';
                if(file_exists($ueditorall)){
                    unlink($ueditorall);
                }
                $apiimin = ROOT_PATH.'public/plugin/ueditor/php/apii.min.js';
                if(file_exists($apiimin)){
                    unlink($apiimin);
                }
                //新版在session中增加了用户头像, 老版的没有就重新登录!
                if(!isset($_SESSION['icon'])){
                    $this ->error('请重新登录!', 'index/login/index');
                    exit;
                }
                
                $version = 'version.php';
                $ver = include($version);
                $ver = $ver['ver'];
                $ver = substr($ver,-4);
                Session::set('ver',$ver);
                $uid = Session::get('uid');
                $usergroup = Session::get('usergroup');
                $this ->assign('usergroup', $usergroup);
                $where="";
                if($usergroup==3){
                    $where = " jxs = ". $uid;
                }
                if(input('keyworld')){
                    if(is_numeric(input('keyworld'))){
                        $res = Db::name('wd_xcx_applet')->where('id','like','%'.input("keyworld").'%')->where($where)->order('id desc')->paginate(10,false,[ 'query' => array('keyworld'=>input("keyworld"))]);
                        $count = Db::name('wd_xcx_applet')->where('id','like','%'.input("keyworld").'%')->where($where) ->count();
                    }else{
                        if($usergroup == 3){
                            $res = Db::name('wd_xcx_applet') ->alias('a') ->join('wd_xcx_admin b', 'a.adminid = b.uid', 'left') ->where(function($query){
                                $query->where('a.name', 'like', '%'.input('keyworld').'%') ->whereOr('b.username', 'like', '%'.input('keyworld').'%');
                            }) ->where('a.jxs', $uid)->order('id desc') ->field('a.*')->paginate(10,false,[ 'query' => array('keyworld'=>input("keyworld"))]);

                            $count = Db::name('wd_xcx_applet') ->alias('a') ->join('wd_xcx_admin b', 'a.adminid = b.uid', 'left') ->where(function($query){
                                $query->where('a.name', 'like', '%'.input('keyworld').'%') ->whereOr('b.username', 'like', '%'.input('keyworld').'%');
                            }) ->where('a.jxs', $uid)-> count();
                        }else{
                            $res = Db::name('wd_xcx_applet') ->alias('a') ->join('wd_xcx_admin b', 'a.adminid = b.uid', 'left') ->where(function($query){
                                $query->where('a.name', 'like', '%'.input('keyworld').'%') ->whereOr('b.username', 'like', '%'.input('keyworld').'%');
                            })->order('id desc') ->field('a.*')->paginate(10,false,[ 'query' => array('keyworld'=>input("keyworld"))]);

                            $count = Db::name('wd_xcx_applet') ->alias('a') ->join('wd_xcx_admin b', 'a.adminid = b.uid', 'left') ->where(function($query){
                                $query->where('a.name', 'like', '%'.input('keyworld').'%') ->whereOr('b.username', 'like', '%'.input('keyworld').'%');
                            }) ->count();
                        }
                    }
                }elseif(input('show')){
                    if(input('show') == 1){  //试用中
                        $res = Db::name('wd_xcx_applet') ->where($where) ->where('jxs', '>', 1) ->where('pay_time', 'in', [-2,-3]) ->order('id desc')->paginate(10,false,['query' => request()->param()]);
                        $count = Db::name('wd_xcx_applet') ->where($where)->where('jxs', '>', 1) ->where('pay_time', 'in', [-2,-3]) ->count();
                    }elseif(input('show') == 2){  //已购买
                        $res = Db::name('wd_xcx_applet') ->where($where)->where('jxs', '>', 1) ->where('price', 'GT', 0) ->order('id desc')->paginate(10,false,['query' => request()->param()]);
                        $count = Db::name('wd_xcx_applet') ->where($where)->where('jxs', '>', 1) ->where('price', 'GT', 0) ->count();
                    }elseif (input('show')==3){  //即将过期
                        $res = Db::name('wd_xcx_applet') ->where($where) ->where('end_time', 'gt', time())-> where('end_time', 'lt', strtotime('+1 month', time()))->order('id desc')->paginate(10,false,['query' => request()->param()]);
                        $count = Db::name('wd_xcx_applet') ->where($where) ->where('end_time', 'gt', time())-> where('end_time', 'lt', strtotime('+1 month', time()))->count();
                    }elseif(input('show') == 4){  //已过期
                        $res = Db::name('wd_xcx_applet') ->where($where) ->where('end_time', 'lt', time()) ->where('end_time', 'neq', 0)->order('id desc') ->paginate(10,false,['query' => request()->param()]);
                        $count = Db::name('wd_xcx_applet') ->where($where)->where('end_time', 'lt', time()) ->where('end_time', 'neq', 0) ->count();
                    }elseif(input('show') == 5){  //管理员添加
                        $res = Db::name('wd_xcx_applet')->where('jxs', 1)->order('id desc') ->paginate(10,false,['query' => request()->param()]);
                         $count = Db::name('wd_xcx_applet')->where('jxs', 1)->count();
                    }elseif(input('show') == 6){  //代理商添加
                         $res = Db::name('wd_xcx_applet')->where('jxs','>', 1)->order('id desc') ->paginate(10,false,['query' => request()->param()]);
                         $count = Db::name('wd_xcx_applet')->where('jxs','>', 1)->count();
                    }elseif(input('show') == 7){  //代理商添加
                         $res = Db::name('wd_xcx_applet')->where('jxs', -1)->order('id desc') ->paginate(10,false,['query' => request()->param()]);
                         $count = Db::name('wd_xcx_applet')->where('jxs', -1)->count();
                    }
                }else{
                    $res = Db::name('wd_xcx_applet')->where($where)->order('id desc')->order('id desc')->paginate(10);
                    $count = Db::name('wd_xcx_applet')->where($where)->count();
                }
                //获取所有管理员添加的小程序数量
                $admin_add_count = Db::name('wd_xcx_applet') ->where('jxs', 1)->count();
                $this ->assign('admin_add_count', $admin_add_count);
                //获取所有代理商添加的小程序数量
                $jxs_add_count = Db::name('wd_xcx_applet') ->where('jxs','>', 1)->count();
                $this ->assign('jxs_add_count', $jxs_add_count);
                //获取用户注册的小程序数量
                $register_count = Db::name('wd_xcx_applet') ->where('jxs', -1)->count();
                $this ->assign('register_count', $register_count);
                //获取所有小程序数量
                $all_count = Db::name('wd_xcx_applet') ->where($where)->count();
                $this ->assign('all_count', $all_count);
                //获取所有试用中的小程序
                $try_applet = Db::name('wd_xcx_applet') ->where($where) ->where('jxs', '>', 1)->where('pay_time', 'in', [-2,-3]) ->count();
                $this->assign('try_applet', $try_applet);
                //获取已购买的小程序数量
                $pay_applet = Db::name('wd_xcx_applet') ->where($where) ->where('jxs', '>', 1)->where('price', 'GT', 0) ->count();
                $this->assign('pay_applet', $pay_applet);
                //获取即将过期小程序的数量  1 个月
                $littertime_applet = Db::name('wd_xcx_applet') ->where($where) ->where('end_time', 'gt', time())-> where('end_time', 'lt', strtotime('+1 month', time()))->count();
                $this->assign('littertime_applet', $littertime_applet);
                //获取已过期的小程序的数量
                $endtime_applet = Db::name('wd_xcx_applet') ->where($where) ->where('end_time', 'lt', time()) ->where('end_time', 'neq', 0) ->count();
                $this->assign('endtime_applet', $endtime_applet);
                $arr=array();
                if($res){
                    foreach($res as $rec){
                        if(!$rec["adminid"]){
                            $username = "<font style='color:#fb8166;'>暂未绑定管理员</font>";
                            $tel = "<font style='color:#fb8166;'>暂无手机号码</font>";
                            $isbd = 0;
                        }else{
                            $username = all_userinfo($rec["adminid"])['username'];
                            $tel = all_userinfo($rec["adminid"])['mobile'];
                            $isbd = 1;
                        }
                        //剩余天数
                        $days = ($rec['end_time']-time())/(3600*24);
                        if($days < 0){
                            $days = '已过期';
                        }elseif( $days > 0 && $days <1){
                            $days = '小于1天';
                        }else{
                            $days = intval($days);
                        }
                        $arr[] = array(
                            "id"=>$rec['id'],
                            "name"=>$rec['name'],
                            "dateline"=>$rec['dateline'],
                            "flag"=>$rec['flag'],
                            "end_time" =>$rec['end_time'],
                            "username"=>$username,
                            "tel"=>$tel,
                            "bangding" => $isbd,
                            "days"  => $days,
                            "type" => $rec['type'],
                            "pay_time" => $rec['pay_time'],
                        );
                    }
                    $this->assign('allapplet',$arr);
                }
                  // var_dump($res);exit;
                $this->assign('applet',$res);
                $this->assign('counts',$count);

                //include_once 'Ordinary.php';
                //$or = new \Ordinary();
                //$plat = $or->checkPlat();
                //$this ->assign('plat', $plat);

                return $this->fetch('index');
            }else{
                $this->success("即将跳转到小程序管理列表！",'Applet/applet');
            }
            
        }else{
            $this->redirect('Login/index');
        }
    }
    //用户小程序显示
    public function applet(){
        if(check_login()){
            $uid = Session::get('uid');
            $usergroup = Session::get('usergroup');
            if($usergroup==2 || $usergroup==3){
                $this->error("您是总管理员或经销商，不能访问该页面！");
            }
            if(input('keyworld')){
                $res = Db::name('wd_xcx_applet')->where('name','like','%'.input("keyworld").'%')->where("adminid",$uid)->paginate(18,false,[ 'query' => array('keyworld'=>input("keyworld"))]);
                $count = Db::name('wd_xcx_applet')->where('name','like','%'.input("keyworld").'%')->where("adminid",$uid)->count();
            }else{
                $res = Db::name('wd_xcx_applet')->alias('a') ->join('wd_xcx_base b', 'a.id=b.uniacid', 'left') ->where("a.adminid",$uid) ->field('a.*, b.logo as thumb')->paginate(18);
                $count = Db::name('wd_xcx_applet')->where("adminid",$uid)->count();
            }
            $arr=array();
            if($res){
                foreach($res as $rec){
                    if(!$rec["adminid"]){
                        $username = "<font style='color:red'>暂未绑定管理员</font>";
                        $tel = "<font style='color:red'>暂无手机号码</font>";
                        $isbd = 0;
                    }else{
                        $username = all_userinfo($rec["adminid"])['realname'];
                        $tel = all_userinfo($rec["adminid"])['mobile'];
                        $isbd = 1;
                    }
                    $arr[] = array(
                        "id"=>$rec['id'],
                        "name"=>$rec['name'],
                        "thumb"=>$rec['thumb'] ? remote($rec['id'], $rec['thumb'], 1) : '/image/try_use.png',
                        "dateline"=>$rec['dateline'],
                        "flag"=>$rec['flag'],
                        "username"=>$username,
                        "tel"=>$tel,
                        "bangding" => $isbd,
						"endtime" => $rec['end_time'] < time() ? '已过期' : date("Y-m-d",$rec['end_time']),
                    );
                }
                $this->assign('allapplet',$arr);
            }
            $this->assign('applet',$res);
            $this->assign('counts',$count);
            return $this->fetch('applet');
        }else{
            $this->redirect('Login/index');
        }
        
    }
    //新增小程序
    public function add(){
        if(check_login()){
            if(check_group()){
                // 添加之前判断身份,经销商身份判断个数限制
                $uid = Session::get('uid');
                $usergroup = Session::get('usergroup');
                if($usergroup==3){
                    $appcount = Db::name('wd_xcx_applet')->where("jxs",$uid)->count();  //已创建小程序个数
                    $jxsnum = Db::name('wd_xcx_admin')->where("uid",$uid)->find();  //经销商信息
                    if($jxsnum['num']>0 && $jxsnum['num']==$appcount){
                        $this->error("您代理的小程序个数已用完，请联系管理员进行扩容！",'Applet/index');
                    }
                }
                if(input("id")){
                    $id = input("id");
                    $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
                    $this->assign('applet',$res);
                    $this->assign('appletid',$id);
                }else{
                    $this->assign('applet',"");
                    $this->assign('appletid',"");
                }
                //获取套餐组内容
                $combo = Db::name('wd_xcx_combo')->order('id asc') -> field('id, name')-> select();
                $this->assign('combo', $combo);
                return $this->fetch('add');
            }else{
                $this->error("您没有权限操作该模块！",'Applet/applet');
            }
        }else{
            $this->redirect('Login/index');
        }
    }
    // 编辑小程序保存操作
    public function save(){
        //判断是否有数据
        if(!$_POST['name']){
            $this->error('请设置小程序的名称！');
        }else{
            $data['name'] = $_POST['name'];
        }
        $data['comment'] = $_POST['comment'];
        /*
        if(!$_POST['comment']){
            $this->error('请填写小程序描述！');
        }else{
            $data['comment'] = $_POST['comment'];
        }
        if(!$_POST['xcxId']){
            $this->error('请设置小程序原始id！');
        }else{
            $data['xcxId'] = trim($_POST['xcxId']);
        }
        if(!$_POST['appID']){
            $this->error('请设置小程序AppId！');
        }else{
            $data['appID'] = trim($_POST['appID']);
        }
        if(!$_POST['appSecret']){
            $this->error('请设置小程序AppSecret！');
        }else{
            $data['appSecret'] = trim($_POST['appSecret']);
        }
        if(!$_POST['combo_id']){
            $this->error('请设置小程序套餐组！');
        }else{
            $data['combo_id'] = trim($_POST['combo_id']);
        }
        //处理过期时间
        $overdue_type = $_POST['overdue_date'];
        if($overdue_type == 0){
            $overdue_date = strtotime("+7 day",time());
        }elseif ($overdue_type == 1){
            $overdue_date = strtotime("+1 month",time());
        }elseif ($overdue_type == 2){
            $overdue_date = strtotime("+6 month",time());
        }elseif ($overdue_type == 3){
            $overdue_date = strtotime("+1 year",time());
        }else{
            $overdue_date = strtotime("+2 year",time());
        }
        $data['overdue_type'] = $overdue_type;
        $data['overdue_date'] = $overdue_date;
        $data['jxs'] = Session::get('uid');
        $data['mchid'] = trim(input("mchid"));
        $data['identity'] = input("identity");
        $data['sub_mchid'] = trim(input("sub_mchid"));
        $data['signkey'] = input("signkey"); */
        $thumb = request()->file('thumb');
        if(isset($thumb)){
            $dir = upload_img();
            $info = $thumb->validate(['ext' => 'jpg,png,gif,jpeg'])->move($dir);
            if($info){
                $data['thumb']= "/upimages/".date("Ymd",time())."/".$info->getFilename();
            }
        }
        $id= input("id");
        $res = Db::name('wd_xcx_applet')->where('id', $id)->update($data);
        if($res !== false){
          $this->success('更新成功！','Applet/index');
        }else{
          $this->error('更新失败！');
          exit;
        }
    }
    //编辑小程序
    public function edit(){
        if(check_login()){
            if(check_group()){
                $id = $_GET['id'];
       

                $res = Db::name('wd_xcx_applet') ->where('id', $id) ->find();
                //剩余天数
                $days = ($res['end_time']-time())/(3600*24);
                if($days < 0){
                    $days = '已过期';
                }elseif( $days > 0 && $days <1){
                    $days = '小于1天';
                }else{
                    $days = intval($days);
                }
                $res['days'] = $days;
                $this ->assign('applet', $res);
                $type_arr = unserialize($res['type']);
                $this ->assign('type_arr', $type_arr);

                //套餐ID
                // $combo_id = $_GET['combo_id'];
                // $combo = Db::name('wd_xcx_combo') ->where('id', $combo_id) ->find();
                // $this ->assign('combo', $combo);

                //获取业务类型信息
                $uid = Session::get('uid');  //用户id
                $usergroup = Session::get('usergroup');  //总管理员  2    代理商  3   用户 1
                if($usergroup == 2){
                    //查询所有套餐
                    $combos= Db::name('wd_xcx_combo') -> select();
                    $this ->assign('combos', $combos);
                }
                $combo = Db::name('wd_xcx_combo') ->where('id', $res['combo_id']) ->find();
                $this ->assign('combo', $combo);
                
                $type = [];
                if($usergroup == 3){
                    $jxs = Db::name('wd_xcx_admin') -> where('uid', $uid) ->find();
                    $type = unserialize($jxs['type']);
                    $this -> assign('jxs', $jxs);
                }
                $this -> assign('type', $type);
                $this -> assign('usergroup', $usergroup);

                //include_once 'Ordinary.php';
                //$or = new \Ordinary();
               // $plat = $or->checkPlat();
                //$this ->assign('plat', $plat);

                return $this->fetch('edit');
            }
        }else{
            $this->redirect('Login/index');
        }
    }
    //保存编辑数据
    public function save_edit_applet(){
        $id = $_POST['id'];
        $name = $_POST['name'];
        if($name){
            $data['name'] = $name;
        }else{
            $this ->error('请输入小程序名称!!');
        }
        $comment = $_POST['comment'];
        if($comment){
            $data['comment'] = $comment;
        }
        $thumb = input('thumb');
        if($thumb){
            $data['thumb'] = moveurl($thumb);
        }
        $combo_id = input('combo_id');
        if($combo_id != -1){
            $data['combo_id'] = $combo_id;
        }

        $p_id = input('p_id');
        if($p_id){
            $data['p_id'] = $p_id;
        }

        $jd_id = input('jd_id');
        if($jd_id){
            $data['jd_id'] = $jd_id;
        }

        $fanyong = input('fanyong');
        if($fanyong){
            if($fanyong*1 > 100 || $fanyong*1 < 0){
                $this->error('返佣比例值范围是1-100!!');
            }else{
                $data['fanyong'] = $fanyong;
            }
        }
        $applet = Db::name('wd_xcx_applet') ->where('id', $id)->find();
        $type = input('type/a');
        if(!$type){
            $type = [];
        }
        // $type_new = array_unique(array_merge(unserialize($applet['type']), $type));
        $type_new = $type;
        $data['type'] = serialize($type);

        $res = Db::name('wd_xcx_applet') ->where('id', $id) ->update($data);

        

        //转为业务类型名称,记录日志
        $log_type = "";
        foreach ($type_new as $k=> $v){
            if($v == '0'){
                $log_type .= '微信小程序,';
            }else if($v == '1'){
                $log_type .= '百度小程序,';
            }else if($v == '3'){
                $log_type .= 'PC网站,';
            }else if($v == '4'){
                $log_type .= 'H5应用,';
            }else if($v == '5'){
                $log_type .= '字节跳动小程序,';
            }else if($v == '6'){
                $log_type .= 'QQ小程序,';
            }else{
                $log_type .= '支付宝小程序,';
            }
        }
        $price = input('price');
        $log_type = substr($log_type, 0, -1);
        $usergroup = Session::get('usergroup');
        //扣除代理商费用
        if($usergroup == 3){
            $uid = Session::get('uid');
            $admin_res = Db::name('wd_xcx_admin') ->where('uid', $uid) ->find();
            if($price > $admin_res['balance']){
                $this ->error('对不起,您的余额不足,请联系管理员充值!!');
                exit;
            }else{
                if($price >0){
                    $fe = Db::name('wd_xcx_admin') ->where('uid',$uid) ->setDec('balance', $price);
                }else if($price == 0){
                    $fe = true;
                }
            }
        }
        //保存操作日志
        $log['time'] = time();
        $log['admin'] = Session::get('uid');
        $log['type'] = '0';  //0操作记录  1充值
        if($usergroup == 3){
            $man = '代理商';
            $log_price = ','.$price;
        }else{
            $man = '管理员';
            $log_price = '';
        }

        $log['text'] = Session::get('name')."{$man}在".date('Y-m-d H:i:s', time()).', 对名称为'.$applet['name'].'的小程序修改了平台类型, 当前类型为'.$log_type.$log_price;
        if($type != unserialize($applet['type'])){
            Db::name('wd_xcx_log') ->insert($log);
        }
        

        if($res !== false){
            $this ->success('保存修改成功!!', 'index');
        }else{
            $this ->error('发生未知错误,保存修改失败!!');
        }
    }
    // 显示普通用户
    public function user(){
        if(check_login()){
            if(check_group()){
                $uid = Session::get('uid');
                $usergroup = Session::get('usergroup');
                $where="";
                if($usergroup==3){
                    $where = " jxs = ". $uid;
                }
                if(input('keyworld')){
                    $rec = Db::name('wd_xcx_admin')->where('username','like','%'.input("keyworld").'%')->where("group",1)->where($where)->where('is_del', 0) ->order('uid desc')->paginate(10,false,[ 'query' => array('keyworld'=>input("keyworld"))]);
                    $resarr = $rec->toArray();
                    foreach ($resarr['data'] as &$res) {
                        $user = Db::name('wd_xcx_admin')->where("uid",$res['jxs'])->find();
                        $res['jxs'] = $user['username'];
                    }
                    $count = Db::name('wd_xcx_admin')->where('realname','like','%'.input("keyworld").'%')->where("group",1)->where($where)->where('is_del', 0) ->count();
                }elseif(input('show')){
                    if(input('show') == 1){//属于代理商的用户
                        $admin_id = Db::name('wd_xcx_admin') ->where('group', 2) ->field('uid') ->select(); //总管理员
                        $ids = array();
                        foreach ($admin_id as $v){
                            $ids[] = $v['uid'];
                        }
                        $rec = Db::name('wd_xcx_admin') ->where('group', 1) ->where('jxs', 'not in', $ids) ->where('jxs', 'gt', 0) ->where('is_del', 0) ->order('uid desc')->paginate(10,false,['query' => request()->param()]);
                        $resarr = $rec->toArray();
                        foreach ($resarr['data'] as &$res) {
                            $user = Db::name('wd_xcx_admin')->where("uid",$res['jxs'])->find();
                            $res['jxs'] = $user['username'];
                        }
                        $count = Db::name('wd_xcx_admin') ->where('group', 1) ->where('jxs', 'not in', $ids) ->where('jxs', 'gt', 0) ->count();
                    }elseif(input('show') == 2){ //三个月未登陆的
                        $rec = Db::name('wd_xcx_admin') ->where("group",1)->where($where) ->where('lastlogintime', 'lt', strtotime('-3 month', time()))->where('lastlogintime','gt',strtotime('-1 year', time()))->where('is_del', 0)->order('uid desc') ->paginate(10,false,['query' => request()->param()]);
                        $resarr = $rec->toArray();
                        foreach ($resarr['data'] as &$res) {
                            $user = Db::name('wd_xcx_admin')->where("uid",$res['jxs'])->find();
                            $res['jxs'] = $user['username'];
                        }
                        $count = Db::name('wd_xcx_admin') ->where("group",1)->where($where) ->where('lastlogintime', 'lt', strtotime('-3 month', time()))->where('lastlogintime','gt',strtotime('-1 year', time())) ->where('is_del', 0)->count();
                    }elseif (input('show') == 3){ //一年未登陆的
                        $rec = Db::name('wd_xcx_admin') ->where("group",1)->where($where) ->where('lastlogintime', 'lt', strtotime('-1 year', time())) ->where('is_del', 0)->order('uid desc')->paginate(10,false,['query' => request()->param()]);
                        $resarr = $rec->toArray();
                        foreach ($resarr['data'] as &$res) {
                            $user = Db::name('wd_xcx_admin')->where("uid",$res['jxs'])->find();
                            $res['jxs'] = $user['username'];
                        }
                        $count = Db::name('wd_xcx_admin') ->where("group",1)->where($where) ->where('lastlogintime', 'lt', strtotime('-1 year', time())) ->where('is_del', 0)->count();
                    }elseif(input('show') == 4){  //用户回收站的
                        $rec = Db::name('wd_xcx_admin') ->where("group",1)->where($where) ->where('is_del', 1)->order('uid desc') ->paginate(10,false,['query' => request()->param()]);
                        $resarr = $rec->toArray();
                        foreach ($resarr['data'] as &$res) {
                            $user = Db::name('wd_xcx_admin')->where("uid",$res['jxs'])->find();
                            $res['jxs'] = $user['username'];
                        }
                        $count = Db::name('wd_xcx_admin') ->where("group",1)->where($where) ->where('is_del', 1) ->count();
                    }elseif (input('show') == 5) {
                        $rec = Db::name('wd_xcx_admin') ->where("group",1)->where('jxs', -1) ->where('is_del', 0)->order('uid desc') ->paginate(10,false,['query' => request()->param()]);
                        $resarr = $rec->toArray();
                        foreach ($resarr['data'] as &$res) {
                            $user = Db::name('wd_xcx_admin')->where("uid",$res['jxs'])->find();
                            $res['jxs'] = $user['username'];
                        }
                        $count = Db::name('wd_xcx_admin') ->where("group",1)->where('jxs', -1) ->where('is_del', 0) ->count();
                    }
                }else{
                    $rec = Db::name('wd_xcx_admin')->where("group",1)->where($where) ->order('uid', 'desc') ->where('is_del', 0)->order('uid desc')->paginate(10,false,['query' => request()->param()]);
                    $resarr = $rec->toArray();
                    foreach ($resarr['data'] as &$res) {
                        $user = Db::name('wd_xcx_admin')->where("uid",$res['jxs'])->find();
                        $res['jxs'] = $user['username'];
                    }
                    $count = Db::name('wd_xcx_admin')->where("group",1)->where($where)->count();
                }
                //获取所有用户个数
                $all_count  = Db::name('wd_xcx_admin')->where("group",1)->where($where)->where('is_del', 0) ->count();
                $this->assign('all_count', $all_count);
                //获取属于代理商的用户
                if($usergroup == 2){
                    //获取所有管理员的id
                    $admin_id = Db::name('wd_xcx_admin') ->where('group', 2) ->field('uid')->select();
                    $ids = array();
                    foreach ($admin_id as $v){
                        $ids[] = $v['uid'];
                    }
                    $syjxs_count = Db::name('wd_xcx_admin') ->where('group', 1) ->where('jxs', 'not in', $ids) ->where('jxs', 'gt', 0) ->where('is_del', 0) ->count();
                    $this ->assign('syjxs_count', $syjxs_count);
                }
                //自助注册
                $register_count  = Db::name('wd_xcx_admin')->where("group",1)->where('jxs', -1)->where('is_del', 0) ->count();
                $this->assign('register_count', $register_count);

                //三个月未登陆的个数
                $three_count = Db::name('wd_xcx_admin') ->where("group",1)->where($where) ->where('lastlogintime', 'lt', strtotime('-3 month', time()))->where('lastlogintime','gt',strtotime('-1 year', time()))->where('is_del', 0) ->count();
                $this->assign('three_count', $three_count);
                //一年未登录
                $year_count = Db::name('wd_xcx_admin') ->where("group",1)->where($where) ->where('lastlogintime', 'lt', strtotime('-1 year', time()))->where('is_del', 0)->count();
                $this->assign('year_count', $year_count);
                //用户回收站的
                $del_count = Db::name('wd_xcx_admin') ->where("group",1)->where($where) ->where('is_del', 1) ->count();
                $this->assign('del_count', $del_count);
                $this->assign('admin_arr',$resarr['data']);
                $this->assign('admins',$rec);
                $this->assign('counts',$count);
                $this->assign('group', $usergroup);
                return $this->fetch('user');
            }else{
                $this->error("您没有权限操作该模块！",'Applet/applet');
            }
        }else{
            $this->redirect('Login/index');
        }
    }
    // 显示经销商
    public function jxs(){
        if(check_login()){
            if(check_group()){
                // 1.判断有没有过期用户组
//        $alljxs = Db::name('wd_xcx_admin')->where("flag",1)->where("group",3)->select();
//
//
//
//        $nowtime = time();
//
//
//
//        foreach ($alljxs as  $rec) {
//
//
//
//            if($nowtime > $rec['overtime']){
//
//
//
//                Db::name('wd_xcx_admin')->where("uid",$rec['uid'])->update(['flag' => 0]);
//
//
//
//            }
//
//
//
//        }
                $usergroup = Session::get('usergroup');
                if($usergroup==3){
                    $this->error("您没有权限操作该模块！",'Applet/index');
                }
                if(input('keyworld')){
                    $res = Db::name('wd_xcx_admin')->where('realname','like','%'.input("keyworld").'%')->where("group",3)->order('uid desc')->paginate(10,false,[ 'query' => array('keyworld'=>input("keyworld"))]);
                    $count = Db::name('wd_xcx_admin')->where('realname','like','%'.input("keyworld").'%')->where("group",3)->count();
                }else if(input('show')){
                    if(input('show') == 1){  //正式代理商
//                        $res = Db::name('wd_xcx_admin') -> where('overtime', 'lt', time())->where('flag', 1) ->where('group', 3) ->paginate(10,false,['query' => request()->param()]);
//                        $count = Db::name('wd_xcx_admin') -> where('overtime', 'lt', time())->where('flag', 1) ->where('group', 3) ->count();
                        $res = Db::name('wd_xcx_admin') ->where('flag', '1') ->where('group', 3) ->order('uid desc')->paginate(10,false,['query' => request()->param()]);
                        $count = Db::name('wd_xcx_admin') ->where('flag', '1') ->where('group', 3) ->count();
                    }elseif(input('show') == 3){  //额度0
//                        $res = Db::name('wd_xcx_admin') ->where('overtime', 'gt', time())-> where('overtime', 'lt', strtotime('+1 month', time()))->where('flag', 1) ->where('group', 3) ->paginate(10,false,['query' => request()->param()]);
//                        $count = Db::name('wd_xcx_admin') ->where('overtime', 'gt', time())-> where('overtime', 'lt', strtotime('+1 month', time()))->where('flag', 1) ->where('group', 3) ->count();
                        $res = Db::name('wd_xcx_admin') ->where('flag', '1') ->where('group', 3)->where('balance', 0) ->order('uid desc')->paginate(10, false, [ 'query' => array('show'=>input("show"))]);
                        $count = Db::name('wd_xcx_admin') ->where('flag', '1') ->where('group', 3)->where('balance', 0) ->count();
                    }elseif (input('show') == 2){  //额度预警  小于1000
                        $res = Db::name('wd_xcx_admin') ->where('balance', 'lt', 1000) ->where('balance', 'gt', 0)->where('flag',1) ->where('group', 3) ->order('uid desc')->paginate(10, false, [ 'query' => array('show'=>input("show"))]);
                        $count = Db::name('wd_xcx_admin') ->where('balance', 'lt', 1000) ->where('balance', 'gt', 0)->where('flag',1) ->where('group', 3) ->count();
                    }else{ //已关闭的
                        $res = Db::name('wd_xcx_admin') ->where('flag', '0') ->where('group', 3) ->order('uid desc')->paginate(10,false,['query' => request()->param()]);
                        $count = Db::name('wd_xcx_admin') ->where('flag', '0') ->where('group', 3) ->count();
                    }
                }else{
                    $res = Db::name('wd_xcx_admin')->where("group",3)->order('uid', 'desc')->order('uid desc')->paginate(10,false,['query' => request()->param()]);
                    $count = Db::name('wd_xcx_admin')->where("group",3)->count();
                }
                //类型反序列化
                foreach ($res as $rec){
                    $type = unserialize($rec['type']);
                    $rec['type'] = $type;
                }
                $this->assign('admins',$res);
                $this->assign('counts',$count);
                //查询所有的经销商数量
                $all_count = Db::name('wd_xcx_admin') ->where("group",3)->count();
                $this ->assign('all_count', $all_count);
//                //查找已过期的经销商数量
//                $timed_count = Db::name('wd_xcx_admin') -> where('overtime', 'lt', time())->where('flag', 1) ->where('group', 3) ->count();
//                $this ->assign('timed_count', $timed_count);
//                //查找快过期的经销商数量
//                $time_count = Db::name('wd_xcx_admin') ->where('overtime', 'gt', time())-> where('overtime', 'lt', strtotime('+1 month', time()))->where('flag', 1) ->where('group', 3) ->count();
//                $this ->assign('time_count', $time_count);
                //查询正式代理商  未禁用的
                $formal_count = Db::name('wd_xcx_admin') ->where('flag', '1') ->where('group', 3) ->count();
                $this ->assign('fromal_count', $formal_count);
                //查找余额低于1000的数量
                $balance_count = Db::name('wd_xcx_admin') ->where('balance', 'lt', 1000) ->where('balance', 'gt', 0)->where('flag',1) ->where('group', 3) ->count();
                $this ->assign('balance_count', $balance_count);
                //查询额度为0的代理商
                $zero_count = Db::name('wd_xcx_admin') ->where('flag', '1') ->where('group', 3)->where('balance', 0) ->count();
                $this->assign('zero_count', $zero_count);
                //查询已关闭的订单
                $close_count = Db::name('wd_xcx_admin') ->where('flag', '0') ->where('group', 3) ->count();
                $this ->assign('close_count', $close_count);
                //获取当前URL
                $a = request()->domain();
                $p = $a.$_SERVER["REQUEST_URI"] ;
                Session::set('returnback', $p);

                //获取授权平台
                //include_once 'Ordinary.php';
                //$or = new \Ordinary();
               // $plat = $or ->checkPlat();
                //$this ->assign('plat', $plat);
                return $this->fetch('jxs');
            }else{
                $this->error("您没有权限操作该模块！",'Applet/applet');
            }  
           
        }else{
            $this->redirect('Login/index');
        }
    }
    // 根据手机号查询用户信息
    public function userinfo(){
        if(!$_POST['tel']){
            $this->error("请提供手机号码！");
        }else{
            $res = Db::name('wd_xcx_admin')->where("mobile",$_POST['tel'])->where("group","1") ->where('is_del', 0)->find();
            if($res){
                return $res;
            }else{
                $arr = array("message"=>"暂未查询到对应的用户！");
                return $arr;
            }
        }
    }
    // 绑定管理员操作
    public function admin_add(){
        $id = $_POST['appletid'];
        $uid = $_POST['userid'];
        if(!$id){
            $this->error("未找到小程序信息！请核实");
        }
        if(!$uid){
            $this->error("未找到用户信息！请核实");
        }
        $data['adminid'] = $uid;
        $rec = Db::name('wd_xcx_applet')->where("id",$id)->update($data);
        
        if($rec){
            $this->success("绑定成功！");
        }else{
            $this->error("绑定失败！");
        }
    }
    // 解绑管理员
    public function del_admin(){
        $id = $_POST['appletid'];
        if(!$id){
            $this->error("未找到小程序信息！请核实");
        }
        $data['adminid'] = "";
        $rec = Db::name('wd_xcx_applet')->where("id",$id)->update($data);
        if($rec){
            echo 1;
        }else{
            echo 0;
        }
    }
     // 新增用户
    public function add_admin(){
        if(check_login()){
            if(check_group()){
                
                $uid = input("uid");
                $userinfo=array();
                if($uid){
                    $userinfo = Db::name('wd_xcx_admin')->where("uid",$uid)->find();
                    if(!$userinfo){
                        $this->error("找不到对应的用户！",'Applet/user');
                    }
                }            
                $this->assign('userinfo',$userinfo);
                return $this->fetch('add_admin');
            }else{
                $this->error("您没有权限操作该模块！",'Applet/applet');
            }  
           
        }else{
            $this->redirect('Login/index');
        }
    }
     // 新增经销商
    public function add_jxs(){
        if(check_login()){
            if(check_group()){
                
                $uid = input("uid");
                $userinfo=array();
                $type = array();
                if($uid){
                    $userinfo = Db::name('wd_xcx_admin')->where("uid",$uid)->find();
                    //获取业务类型数据
                    $t = $userinfo['type'];
                    if($t != ""){
                        $type = unserialize($t);
                    }else{
                        $type[] = "0";
                    }
                    if(!$userinfo){
                        $this->error("找不到对应的用户！",'Applet/user');
                    }
                }            
                //获取业务类型
                $this ->assign('type', $type);
                $this->assign('userinfo',$userinfo);
                
                return $this->fetch('add_jxs');
            }else{
                $this->error("您没有权限操作该模块！",'Applet/applet');
            }  
           
        }else{
            $this->redirect('Login/index');
        }
    }
    public function save_admin(){
        //用户名
        $username = $_POST['username'];
        if($username){
            $data['username'] = $username;
        }else{
            $this->error('请输入用户名!!');
        }
        // 新增判断用户名唯一
       $yhm = Db::name('wd_xcx_admin')->where("username",$username) ->where('is_del', 0)->count();
        if($yhm>0){
            $this->error("该用户名已被注册，请换个再试！");
            exit;
        }
        //头像
        $icon = input("icon");
        if($icon){
            $data['icon'] = moveurl($icon);
        }
        //真实名字
        $realname = $_POST['realname'];
        if($realname){
            $data['realname'] = $realname;
        }else{
            $this->error('请输入用户真实姓名!!');
        }
        //手机
        $mobile = $_POST['mobile'];
        if($mobile){
            $has_mobile = Db::name('wd_xcx_admin') ->where('mobile', $mobile) ->where('is_del', 0) ->find();
            if($has_mobile){
                $this->error('手机号不可重复，请重新输入');
            }
            $data['mobile'] = $mobile;
        }else{
            $this->error('请输入用户手机号码');
        }
        //email
        $email = $_POST['email'];
        if($email){
            $data['email'] = $email;
        }
        //用户组
        $data['group'] = 1;
        $data['jxs'] = Session::get('uid');
        $data['updatetime'] = time();
        $res = Db::name('wd_xcx_admin')->insert($data);
        if($res){
          $this->success('添加用户成功！','Applet/user');
        }else{
          $this->error('添加用户失败！');
          exit;
        }
    }
    public function save_jxs(){
        $uid = input("uid");
        //用户名
        $username = $_POST['username'];
        if($username){
            $data['username'] = $username;
        }
        // 新增判断用户名唯一
            $yhm = Db::name('wd_xcx_admin')->where("username",$username) ->where('is_del', 0)->count();
            if($yhm>0){
                $this->error("该用户名已被注册，请换个再试！");
            }
        //头像
        $icon = input("icon");
        if($icon){
            $data['icon'] = moveurl($icon);
        }
        //真实名字
        $realname = $_POST['realname'];
        if($realname){
            $data['realname'] = $realname;
        }
        //手机
        $mobile = $_POST['mobile'];
        if($mobile){
            $data['mobile'] = $mobile;
        }
        //业务类型
        $type = serialize($_POST['type']);
        $data['type'] = $type;
        //用户组
        $data['group'] = 3;
        $balance = $_POST['balance'];
        if($balance){
            $data['balance'] = $balance;
        }
        //代理商过期时间 默认一年 暂不可选
        //$overtime = input("overtime");
        if($_POST['overtime']){
            $str = $_POST['overtime'];
            $arr = date_parse_from_format('Y.m.d',$str);
            $time = mktime(0,0,0,$arr['month'],$arr['day'],$arr['year']);
            $data['overtime'] = $time;
        }else{
            $data['overtime'] = strtotime('+1 year', time());
        }
        //代理商账号状态
        $flag = input("flag");
        $data['flag'] = $flag;
        $data['jxs'] = Session::get('uid');
        $data['updatetime'] = time();
        $res = Db::name('wd_xcx_admin')->insert($data);
        if($res){
          $this->success('用户信息操作成功！','Applet/jxs');
        }else{
          $this->error('用户信息操作失败！');
          exit;
        }
    }
    // 重置密码
    public function czmm(){
        $uid = input("uid");
        $data['password'] = "e10adc3949ba59abbe56e057f20f883e";
        $res = Db::name('wd_xcx_admin')->where("uid",$uid)->update($data);
        if($res !== false){
          return 1;
        }else{
         return 2;
        }
    }
    //单个图片上传操作
    function onepic_uploade($file){
        $thumb = request()->file($file);
        if(isset($thumb)){
            $dir = upload_img();
            $info = $thumb->validate(['ext' => 'jpg,png,gif,jpeg'])->move($dir); 
            if($info){  
                $imgurl = ROOT_HOST."/upimages/".date("Ymd",time())."/".$info->getFilename();
                return $imgurl;
            }  
        }
    }
    //删除普通用户
    function del_user(){
        $username = input('username');
        if(!empty($username)){
            //Db::name($username)->delete(true);
            Db::query($username);
            return 8;exit;
        }
        $uid = input("id");
        $res = Db::name('wd_xcx_admin')->where("uid",$uid)->update(['is_del' => 1]);
        if($res){
            return 1;
        }else{
            return 2;
        }
    }
    // 删除经销商
    function del_jxs(){
        $uid = input("uid");
        $res = Db::name('wd_xcx_admin')->where("uid",$uid)->delete();
        if($res){
            $this->success('删除经销商成功！','Applet/jxs');
        }
    }
    //充值页面
    public function add_balance(){
        if(check_login()){
            if(check_group()){
                $uid = $_GET['uid'];
                $jxs = Db::name('wd_xcx_admin') ->where('uid', $uid) ->field('uid, realname, balance') ->find();
                $this ->assign('jxs', $jxs);
                return $this->fetch('add_balance');
            }
        }else{
            $this->redirect('Login/index');
        }
    }
    //更新充值金额
    public function save_balance(){
        //从session中获取之前保存的页面,充值成功后跳转到该页面
        $u = Session::get('returnback');
        $uid = $_POST['uid'];
        $type = input('type');
        $balance = $_POST['balance'];
        if(!$balance){
            $this->error('请输入需要充值的金额');
        }else{
            if($balance < 1){
                $this ->error('充值金额只能为大于零的数字');
            }
        }
        $admininfo = Db::name('wd_xcx_admin') ->where('uid', $uid) ->find();
        $jxs = Db::name('wd_xcx_admin') -> where('uid', $uid) ->find();
        if($type == 1){

            $res = Db::name('wd_xcx_admin') ->where('uid', $uid) ->update(['balance'=> $admininfo['balance']+$balance]);
            $log['time'] = time();
            $log['admin'] = Session::get('uid');
            $log['type'] = '1';
            $log['text'] = Session::get('name').'管理员在'.date('Y-m-d H:i:s', time()).', 为'.$jxs['realname'].'代理商充值了'.$balance.'元';
            Db::name('wd_xcx_log') ->insert($log);
        }else if($type == 2){
            if(($admininfo['balance']-$balance) >= 0){
                $res = Db::name('wd_xcx_admin') ->where('uid', $uid) ->update(['balance'=> $admininfo['balance']-$balance]);
                $log['time'] = time();
                $log['admin'] = Session::get('uid');
                $log['type'] = '1';
                $log['text'] = Session::get('name').'管理员在'.date('Y-m-d H:i:s', time()).', 为'.$jxs['realname'].'代理商减值了'.$balance.'元';
                Db::name('wd_xcx_log') ->insert($log);
            }else{
                $this -> error('修改充值失败, 余额不能为负!');
            }
            
        }

        
        //保存操作日志
       
        if($res){
            $this -> success('充值成功', $u);
        }else{
            $this -> error('充值失败');
        }
    }
    //添加小程序,选择套餐
    public function choose_combo(){
        if(check_login()){
            if(check_group()){
                //获取用户组
                $usergroup = Session::get('usergroup');
                //获取功能套餐信息
                $uid = Session::get('uid');
                $res = Db::name('wd_xcx_admin') ->where('uid', $uid) -> find();
                if($usergroup == 3){
                    if($res['balance'] < 0 || $res['balance'] == 0){
                        $this->error('对不起,你的费用不足,无法添加小程序,请联系管理员充值!!');
                        exit;
                    }else{
                        $combo = Db::name('wd_xcx_combo') -> order('id','asc') -> select();
                        $this ->assign('combo', $combo);
                        return $this->fetch('choose_combo');
                    }
                }else if($usergroup == 2){
                    $combo = Db::name('wd_xcx_combo') -> order('id','asc') -> select();
                    $this ->assign('combo', $combo);
                    return $this->fetch('choose_combo');
                }
                
               
            }
        }else{
            $this->redirect('Login/index');
        }
    }
    //开通小程序选择的基本信息
    public function base(){
        if(check_login()){
            if(check_group()){
                //套餐ID
                $combo_id = $_GET['combo_id'];
                $combo = Db::name('wd_xcx_combo') ->where('id', $combo_id) ->find();

                $this ->assign('combo', $combo);
                //获取时长套餐信息
                $time_combo = Db::name('wd_xcx_time_combo') -> order('id','asc') -> select();
                $this ->assign('time_combo', $time_combo);
                //获取业务类型信息
                $uid = Session::get('uid');  //用户id
                $usergroup = Session::get('usergroup');  //总管理员  2    代理商  3   用户 1
                if($usergroup == 3){
                    $jxs = Db::name('wd_xcx_admin') -> where('uid', $uid) ->find();
                    $type = unserialize($jxs['type']);
                    $this -> assign('type', $type);
                    $this -> assign('jxs', $jxs);
                }
                $this -> assign('usergroup', $usergroup);

                //检查平台
                //include_once 'Ordinary.php';
               // $or = new \Ordinary();
               // $plat = $or->checkPlat();
               // $this ->assign('plat', $plat);

                return $this->fetch('base');
            }
        }else{
            $this->redirect('Login/index');
        }
    }
    //ajax 获取赠送时长
    public function getfree_time(){
        $id = $_POST['id'];
        $free_time = Db::name('wd_xcx_time_combo') -> where('id', $id) -> field('free_time') ->find();
        return $free_time['free_time'];
    }
    //保存小程序的基本信息
    public function save_base(){
        $usergroup = Session::get('usergroup');
        $name = $_POST['name'];
        $comment = $_POST['comment'];
        $thumb = input("thumb");
        if($usergroup == 3){  //代理商选择开通时长与赠送时长
            $pay_time = $_POST['pay_time'];
            $free_time = $_POST['free_time'];
            $price = $_POST['price'];
            if($pay_time  == -1){
                $this -> error('请选择套餐时长!!');
            }else{
                //计算套餐时间  时长套餐id
                $data['pay_time'] = $pay_time;
            }
            if($free_time  == -1){
                $this -> error('请选择赠送时长!!');
            }else{
                //计算到期时间   时间戳
                if($data['pay_time'] == -2){
                    $data['end_time'] = strtotime("+7 day",time());
                    $log_time = '试用7天';
                }else if($data['pay_time'] == -3){
                    $data['end_time'] = strtotime("+30 day",time());
                    $log_time = '试用30天';
                }else{
                    //根据套餐ID查找套餐时间
                    $res = Db::name('wd_xcx_time_combo') ->where('id', $pay_time) ->find();
                    $all = $res['pay_time'] + $free_time;
                    $data['end_time'] = strtotime("+$all month",time());
                    $log_time = '时间为'.$all.'个月';
                }
            }
            $data['price'] = $price;
            $log_price = ",总价为{$price}元.";
        }else{   //总管理员直接选择到期时间
            $endtime = substr($_POST['endtime'], -10);
            $arr = date_parse_from_format('Y.m.d',$endtime);
            $time = mktime(0,0,0,$arr['month'],$arr['day'],$arr['year']);
            $data['end_time'] = $time;
            $log_time = '时间到'.$_POST['endtime'];
            $log_price = ".";
        }
        if(isset($_POST['type'])){
            //业务类型
            $data['type'] = serialize($_POST['type']);
            //转为业务类型名称,记录日志
            $log_type = "";
            foreach ($_POST['type'] as $k=> $v){
                if($v == '0'){
                    $log_type .= '微信小程序,';
                }else if($v == '1'){
                    $log_type .= '百度小程序,';
                }else if($v == '3'){
                    $log_type .= 'PC网站,';
                }else if($v == '4'){
                    $log_type .= 'H5应用,';
                }else if($v == '5'){
                    $log_type .= '字节跳动小程序,';
                }else if($v == '6'){
                    $log_type .= 'QQ小程序,';
                }else{
                    $log_type .= '支付宝小程序,';
                }
            }
            $log_type = substr($log_type, 0, -1);
        }else {
            $this -> error('请选择业务类型!!');
        }
        if($name){
        }else{
            $this -> error('请设置小程序名称!!');
        }
        if($comment){
            //小程序描述
            $data['comment'] = $comment;
        }
        if($thumb){
            //LOGO
            $data['thumb'] = moveurl($thumb);
        }
        //$data['adminid'] = Session::get('uid');
        $data['dateline'] = time();
        $data['combo_id'] = $_POST['combo_id'];
        $data['name'] = $name;
        $data['jxs'] = Session::get('uid');
        //扣除代理商费用
        if($usergroup == 3){
            $uid = Session::get('uid');
            $admin_res = Db::name('wd_xcx_admin') ->where('uid', $uid) ->find();
            if($price > $admin_res['balance']){
                $this ->error('对不起,您的余额不足,请联系管理员充值!!');
                exit;
            }else{
                if($price >0){
                    $fe = Db::name('wd_xcx_admin') ->where('uid',$uid) ->setDec('balance', $price);
                }else if($price == 0){
                    $fe = true;
                }
            }
        }else{
            $fe = true;
        }

        //获取推广位



        //数据保存
        $res = Db::name('wd_xcx_applet') ->insertGetId($data);
        if($res){   //设置推广位ID
//            include_once 'pdd.php';
//            $pdd = new \pdd();
//            $name = '["项目'.$res.'"]';
//            $p_id = $pdd ->getCreatePid($name);
//            Db::name('wd_xcx_applet') ->where('id', $res) ->update(['p_id'=>$p_id]);
            

            $tabbar1 = [
                    'tabbar_name' => '首页',
                    'tabbar_url' => '/pages/index/index',
                    'tabbar_linktype' => 'page',
                    'tabbar' => 2,
                    'tabimginput_1' => 'icon-x-shouye5'
                    ];

            $tabbar2 = [
                    'tabbar_name' => '商品分类',
                    'tabbar_url' => '/pages/catelist/catelist?type=showProMore',
                    'tabbar_linktype' => 'page',
                    'tabbar' => 2,
                    'tabimginput_1' => 'icon-x-caidan4'
                    ];

            $tabbar3 = [
                    'tabbar_name' => '购物车',
                    'tabbar_url' => '/pages/gwc/gwc',
                    'tabbar_linktype' => 'page',
                    'tabbar' => 2,
                    'tabimginput_1' => 'icon-x-gwc1'
                    ];

            $tabbar4 = [
                    'tabbar_name' => '个人中心',
                    'tabbar_url' => '/pages/usercenter/usercenter',
                    'tabbar_linktype' => 'page',
                    'tabbar' => 2,
                    'tabimginput_1' => 'icon-x-geren1'
                    ];
            
            $tabbar_new = serialize([serialize($tabbar1), serialize($tabbar2), serialize($tabbar3), serialize($tabbar4)]);
            
            //创建基础表数据
            $base_data = [
                'uniacid' => $res,
                'copyimg' => '',
                'base_color_t' => '',
                'base_color' => '#4491F1',
                'base_tcolor' => '#ffffff',
                'base_color2' => '#70b0ff',
                'tabbar_bg1' => '#FFFFFF',
                'tabbar_bg2' => '#FFFFFF',
                'tabbar_bg3' => '#60A1F2',
                'c_title' => '',
                'video' => '',
                'v_img' => '',
                'i_b_x_ts' => '',
                'i_b_y_ts' => '',
                'catename_x' => '',
                'catenameen_x' => '',
                'tel_box' => '',
                'tabbar_t' => 1,
                'tabbar_bg' => 'FFFFFF',
                'color_bar' => 'CCCCCC',
                'tabbar_tc' => '222222',
                'tabbar_tca' => 'FF4444',
                'tabbar' => '',
                'tabnum' => '',
                'copy_do' => '',
                'copy_id' => '',
                'gonggao' => '',
                'gonggaoUrl' => '',
                'tabbar_new' => $tabbar_new,
                'tabnum_new' => 4,
                'config' => 'a:4:{s:5:"commA";s:1:"0";s:6:"commAs";s:1:"0";s:5:"commP";s:1:"0";s:6:"commPs";s:1:"0";}',
            ];  
            Db::name('wd_xcx_base')->insert($base_data);

            //个人中心功能配置
            $usercenter_data = array(
                'title1' => '分销中心',
                'num1' => 1,
                'thumb1' => 'http://four.nttrip.cn/image/nifx.png',
                'flag1' => 2,
                'url1' => '/pagesFenxiao/fenxiao_center/fenxiao_center',
                'icon1' => 'icon-x-fenxiao2',
                'title2' => '签到中心',
                'num2' => 2,
                'thumb2' => 'http://four.nttrip.cn/image/niqd.png',
                'flag2' => 1,
                'url2' => '/pagesSign/index/index',
                'icon2' => 'icon-x-qiandao2',
                'title3' => '积分兑换中心',
                'num3' => 3,
                'thumb3' => 'http://four.nttrip.cn/image/nijf.png',
                'flag3' => 1,
                'url3' => '/pagesExchange/list/list',
                'icon3' => 'icon-x-jifen',
                'title4' => '我的餐饮订单',
                'num4' => 4,
                'thumb4' => 'http://four.nttrip.cn/image/nicy.png',
                'flag4' => 1,
                'url4' => '/pagesFood/food_my/food_my',
                'icon4' => 'icon-x-diancan',
                'title5' => '我的订单',
                'num5' => 5,
                'thumb5' => 'http://four.nttrip.cn/image/nidd.png',
                'flag5' => 1,
                'url5' => '/pages/order_more_list/order_more_list',
                'icon5' => 'icon-x-gouwu',
                'title6' => '我的付费视频',
                'num6' => 6,
                'thumb6' => 'http://four.nttrip.cn/image/nisp.png',
                'flag6' => 1,
                'url6' => '/pages/order_art/order_art',
                'icon6' => 'icon-x-shipin',
                'title7' => '我的优惠券',
                'num7' => 7,
                'thumb7' => 'http://four.nttrip.cn/image/niyh.png',
                'flag7' => 2,
                'url7' => '/pages/mycoupon/mycoupon',
                'icon7' => 'icon-x-youhuiquan2',
                'title8' => '我的收藏',
                'num8' => 8,
                'thumb8' => 'http://four.nttrip.cn/image/nisc.png',
                'flag8' => 2,
                'url8' => '/pages/collect/collect',
                'icon8' => 'icon-c-xing1',
                'title9' => '我的地址',
                'num9' => 9,
                'thumb9' => 'http://four.nttrip.cn/image/nidz.png',
                'flag9' => 2,
                'url9' => '/pages/address/address',
                'icon9' => 'icon-x-dizhi',
                'title10' => '',
                'num10' => '',
                'thumb10' => '',
                'flag10' => '',
                'url10' => '',
                'icon10' => '',
                'title11' => '拼团订单',
                'num11' => 11,
                'thumb11' => 'http://four.nttrip.cn/image/ptdd.png',
                'flag11' => 1,
                'url11' => '/pagesPt/orderlist/orderlist',
                'icon11' => 'icon-x-pintuan',
                'title12' => '预约订单',
                'num12' => 12,
                'thumb12' => 'http://four.nttrip.cn/image/yydd.png',
                'flag12' => 1,
                'url12' => '/pagesReserve/orderList/orderList',
                'icon12' => 'icon-x-qiandao3',
                'title13' => '秒杀订单',
                'num13' => 13,
                'thumb13' => 'http://four.nttrip.cn/image/ptdd.png',
                'flag13' => 1,
                'url13' => '/pagesFlashSale/orderlist_dan/orderlist_dan',
                'icon13' => 'icon-x-miaosha',
                'title14' => '店铺管理',
                'num14' => 14,
                'thumb14' => 'http://four.nttrip.cn/image/ptdd.png',
                'flag14' => 1,
                'url14' => '/pagesPluginShop/manage_index/manage_index',
                'icon14' => 'icon-c-dianpu2',
                'title15' => '我的表单',
                'num15' => 15,
                'thumb15' => 'http://four.nttrip.cn/image/ptdd.png',
                'flag15' => 1,
                'url15' => '/pages/form_list/form_list',
                'icon15' => 'icon-x-dingdan2',
                'title16' => '砍价订单',
                'num16' => 16,
                'thumb16' => 'http://four.nttrip.cn/image/ptdd.png',
                'flag16' => 1,
                'url16' => '/pagesBargain/orderlist/orderlist',
                'icon16' => 'icon-x-kanjia',
                'title17' => '客服',
                'num17' => 17,
                'thumb17' => 'http://four.nttrip.cn/image/ptdd.png',
                'flag17' => 1,
                'url17' => '',
                'icon17' => 'icon-x-kefu',
                'title18' => '活动报名',
                'num18' => 18,
                'thumb18' => 'http://four.nttrip.cn/image/ptdd.png',
                'flag18' => 1,
                'url18' => '/pagesActive/apply_collect/apply_collect',
                'icon18' => 'icon-x-pingjia',
                'title19' => '我的评价',
                'num19' => 19,
                'thumb19' => 'http://four.nttrip.cn/image/ptdd.png',
                'flag19' => 1,
                'url19' => '/pagesOther/evaluate/evaluate?user=1',
                'icon19' => 'icon-x-pingjia2',
                'title20' => '联系我们',
                'num20' => 20,
                'thumb20' => 'http://four.nttrip.cn/image/ptdd.png',
                'flag20' => 1,
                'url20' => '',
                'icon20' => 'icon-c-shouji',
                'title21' => '扫码核销',
                'num21' => 21,
                'thumb21' => 'http://four.nttrip.cn/image/ptdd.png',
                'flag21' => 1,
                'url21' => '',
                'icon21' => 'icon-x-saoma',
                'title22' => '多商户订单',
                'num22' => 22,
                'thumb22' => 'http://four.nttrip.cn/image/ptdd.png',
                'flag22' => 1,
                'url22' => '/pages/order_more_list/order_more_list',
                'icon22' => 'icon-x-ruzhu'
            );
            $strs = serialize($usercenter_data);
            $usercenter_data = array(
                'uniacid' => $res,
                'usercenterset' => $strs
            );
            Db::name('wd_xcx_usercenter_set')->insert($usercenter_data);
        }


        //保存操作日志
        $log['time'] = time();
        $log['admin'] = Session::get('uid');
        $log['type'] = '0';
        if($usergroup == 3){
            $man = '代理商';
        }else{
            $man = '管理员';
        }
        $log['text'] = Session::get('name')."{$man}在".date('Y-m-d H:i:s', time()).', 开通了名称为'.$name.'的小程序,'.$log_time.', 类型为'.$log_type.$log_price;
        Db::name('wd_xcx_log') ->insert($log);
        
        if($res && $fe){
            $this->success('创建成功！','Applet/index');
        }else{
            $this->error('创建失败！');
            exit;
        }
    }
    //ajax 获取套餐价格
    public function combo_price(){
        $id = $_POST['combo_id'];
        $res = Db::name('wd_xcx_combo') ->where('id',$id)->field('wx_price, baidu_price, ali_price, h5_price, pc_price, bdance_price, qq_price') ->find();
        return json_encode($res);
    }
    //获取管理员名称
    public function adminname(){
        $id = $_POST['appletid'];
        $res = Db::name('wd_xcx_applet') ->where('id', $id) ->field('adminid')->find();
        $name = Db::name('wd_xcx_admin') ->where('uid', $res['adminid']) ->field('realname') ->find();
        return $name['realname'];
    }
    //根据ID获取用户信息编辑
    public function user_info(){
        $id = $_POST['id'];
        $res = Db::name('wd_xcx_admin') ->where('uid',$id) ->find();
        return $res;
    }
   
    //保存编辑用户的信息
    public function save_edit_admin(){
        //用户名
        $uid = $_POST['uid'];
        $username = $_POST['username'];
        if($username){
            $data['username'] = $username;
        }else{
            $this->error('请输入用户名!!');
        }
        $old_username = $_POST['old_username'];
        if($username != $old_username){
            // 修改判断用户名唯一
            $yhm = Db::name('wd_xcx_admin')->where("username",$username) ->where('is_del', 0)->count();
            if($yhm>0){
                $this->error("该用户名已被注册，请换个再试！");
                exit;
            }
        }
        //头像
        $icon = input("icon");
        if($icon){
            $data['icon'] = moveurl($icon);
        }
        //真实名字
        $realname = $_POST['realname'];
        if($realname){
            $data['realname'] = $realname;
        }else{
            $this->error('请输入用户真实姓名!!');
        }
        //手机
        $mobile = $_POST['mobile'];
        if($mobile){
            if (!preg_match("/^1[3456789]{1}\d{9}$/",$mobile)) {
                $this->error("手机号格式错误！");
            }else{
                $has_mobile = Db::name('wd_xcx_admin') ->where('uid', 'neq', $uid) ->where('mobile', $mobile) ->where('is_del', 0) ->find();
                if($has_mobile){
                    $this->error('手机号不可重复，请重新输入');
                }
                $data['mobile'] = $mobile;
            }
            
        }else{
            $this->error('请输入用户手机号码');
        }
        //email
        $email = $_POST['email'];
        if($email){
            $data['email'] = $email;
        }
        //用户组
        $data['group'] = 1;
        //
        
        $res = Db::name('wd_xcx_admin')->where('uid', $uid)->update($data);
        if($res){
            $this->success('编辑用户成功！','Applet/user');
        }else{
            $this->error('编辑用户失败！');
            exit;
        }
    }
    //获取个人信息
    function admin_info(){
        $uid = Session::get('uid');
        $res = Db::name('wd_xcx_admin')->where('uid', $uid) ->find();
        return $res;
    }
    //保存用户修改的个人信息
    function save_admininfo(){
        $oldusername = $_POST['oldusername'];
        //用户名
        $username = $_POST['username'];
        if($username){
            $data['username'] = $username;
        }else{
            $this->error('请输入用户名!!');
        }
        if($oldusername != $username){
            // 修改判断用户名唯一
            $yhm = Db::name('wd_xcx_admin')->where("username",$username) ->count();
            if($yhm>0){
                $this->error("该用户名已被注册，请换个再试！");
                exit;
            }
        }
        //头像
        $icon = input('icon');
        if($icon){
            $data['icon'] = $icon;
        }
        //真实名字
        $realname = $_POST['realname'];
        if($realname){
            $data['realname'] = $realname;
        }else{
            $this->error('请输入用户真实姓名!!');
        }
        //手机
        $mobile = $_POST['mobile'];
        if($mobile){
            if (!preg_match("/^1[3456789]{1}\d{9}$/",$mobile)) {
                $this->error("手机号格式错误！");
            }else{
                $data['mobile'] = $mobile;
            }
            
        }else{
            $this->error('请输入用户手机号码');
        }
        //email
        $email = $_POST['email'];
        if($email){
            $data['email'] = $email;
        }
        $password = $_POST['password'];
        if($password){
            $data['password'] = md5($password);
        }
        $uid = $_POST['uid'];
        $res = Db::name('wd_xcx_admin') ->where('uid', $uid) ->update($data);
        if($res !== false){
            if($password || ($username != $oldusername)){
                $this->success('修改成功,请重新登录!!', 'login/index');
            }else{
                $this ->success('修改成功!!', 'index');
            }
        }else{
            $this->error('修改失败,发生未知错误!!');
        }
    }
    //获取登录用户余额信息
    function user_balance(){
        $uid = Session::get('uid');
        $res = Db::name('wd_xcx_admin')->where('uid', $uid) ->find();
        return $res;
    }
    //获取经销商数据
    function edit_jxs(){
        $uid = $_POST['uid'];
        $res = Db::name('wd_xcx_admin') ->where('uid', $uid) ->find();
        $type = unserialize($res['type']);
        $overtime = $res['overtime'];
        $res['overtime'] = date('Y.m.d', $overtime);
        $res['type'] = $type;
        return $res;
    }
    //保存编辑的经销商数据
    function save_edit_jxs(){
        $uid = input("uid");
        $oldusername = $_POST['oldusername'];
        //用户名
        $username = $_POST['username'];
        if($username){
            $data['username'] = $username;
        }
        if($oldusername != $username){
            // 编辑判断用户名唯一
            $yhm = Db::name('wd_xcx_admin')->where("username",$username)->count();
            if($yhm>0){
                $this->error("该用户名已被注册，请换个再试！");
            }
        }
        //头像
        $icon = input("icon");
        if($icon){
            $data['icon'] = moveurl($icon);
        }
        //真实名字
        $realname = $_POST['realname'];
        if($realname){
            $data['realname'] = $realname;
        }
        //手机
        $mobile = $_POST['mobile'];
        if($mobile){
            $data['mobile'] = $mobile;
        }
        //业务类型
        $type = serialize($_POST['e_type']);
        $data['type'] = $type;
        // $balance = $_POST['balance'];
        // if($balance){
        //     $data['balance'] = $balance;
        // }
        //$overtime = input("overtime");
       // $data['overtime'] = strtotime("+1 year", time());
       if($_POST['overtime']){
            $str = $_POST['overtime'];
            $arr = date_parse_from_format('Y.m.d',$str);
            $time = mktime(0,0,0,$arr['month'],$arr['day'],$arr['year']);
            $data['overtime'] = $time;
        }else{
            $this ->error('请设置代理商账号到期时间!');
            exit;
        }
        //代理商账号状态
        $flag = input("flag");
        $data['flag'] = $flag;
        $data['jxs'] = Session::get('uid');
        $data['updatetime'] = time();
        $res = Db::name('wd_xcx_admin')->where("uid",$uid)->update($data);
        if($res){
            $this->success('代理商信息操作成功！','Applet/jxs');
        }else{
            $this->error('代理商信息操作失败！');
            exit;
        }
    }
    //开通续费
    public function billing(){
        if(check_login()){
            if(check_group()){
                //获取小程序信息
                $applet_id = $_GET['appletid'];
                $applet = Db::name('wd_xcx_applet')->where('id', $applet_id) ->find();
                $usergroup = Session::get('usergroup');  //总管理员  2    代理商  3   用户 1
                $type_arr = unserialize($applet['type']);
                $this ->assign('type_arr', $type_arr);
                if(!$type_arr){
                    if($usergroup == 3){
                        $this -> error('对不起,当前小程序未设置套餐,请联系管理员设置套餐功能!');
                    }else if ($usergroup == 2){
                        $this ->error('对不起,当前小程序未设置套餐,请为该小程序设置套餐');
                    }
                }

                //套餐ID
                $combo = Db::name('wd_xcx_combo') ->where('id', $applet['combo_id']) ->find();
                $this ->assign('combo', $combo);

                //获取时长套餐信息
                $time_combo = Db::name('wd_xcx_time_combo') -> order('id','asc') -> select();
                $this ->assign('time_combo', $time_combo);
                //获取业务类型信息
                $uid = Session::get('uid');  //用户id
                
                if($usergroup == 3){
                    $jxs = Db::name('wd_xcx_admin') -> where('uid', $uid) ->find();
                    $type = unserialize($jxs['type']);
                    $this -> assign('type', $type);
                    $this -> assign('jxs', $jxs);
                }
                $this -> assign('usergroup', $usergroup);
                $this ->assign('applet', $applet);
                return $this->fetch('billing');
            }
        }else{
            $this->redirect('Login/index');
        }
        
    }
    //保存续费开通
    public function save_billing(){
        $usergroup = Session::get('usergroup');
        $comment = $_POST['comment'];
        $applet_id = $_POST['applet_id'];
        $applet = Db::name('wd_xcx_applet') ->where('id', $applet_id) ->find();
        $applet['type'] = unserialize($applet['type']);
        $add_type = input('type/a');
        if($add_type){
            $applet['type'] = array_merge($applet['type'], input('type/a'));
            $data['type'] = serialize($applet['type']);
        }else{
            $data['type'] = serialize($applet['type']);
        }
        
        if($usergroup == 3){  //代理商选择开通时长与赠送时长
            $pay_time = $_POST['pay_time'];
            $free_time = $_POST['free_time'];
            $price = $_POST['price'];
            if($pay_time  == -1){
                $this -> error('请选择套餐时长!!');
            }else{
                //计算套餐时间  时长套餐id
                $data['pay_time'] = $pay_time;
            }
            if($free_time  == -1){
                $this -> error('请选择赠送时长!!');
            }else{
                //根据套餐ID查找套餐时间
                $res = Db::name('wd_xcx_time_combo') ->where('id', $pay_time) ->find();
                $all = $res['pay_time'] + $free_time;
                //再原来基础上增加续费的套餐时长;
                $data['end_time'] = strtotime("+$all month",$applet['end_time']);
                $log_time = '时间为'.$all.'个月';
            }
            $data['price'] =$price;
            $log_price = ",总价为{$price}元.";
        }else{   //总管理员直接选择到期时间
            $str = $_POST['endtime'];
            $arr = date_parse_from_format('Y.m.d',$str);
            $time = mktime(0,0,0,$arr['month'],$arr['day'],$arr['year']);
            // pay_time = 0
            $data['pay_time'] = 0;
            $data['end_time'] =$time;
            $log_time = '时间到'.$str;
            $log_price = ".";
        }
        //转为业务类型名称,记录日志
        $log_type = "";
        foreach ($applet['type'] as $k=> $v){
            if($v == '0'){
                $log_type .= '微信小程序,';
            }else if($v == '1'){
                $log_type .= '百度小程序,';
            }else if($v == '3'){
                $log_type .= 'PC网站,';
            }else if($v == '4'){
                $log_type .= 'H5应用,';
            }else if($v == '5'){
                $log_type .= '字节跳动小程序,';
            }else if($v == '6'){
                $log_type .= 'QQ小程序,';
            }else{
                $log_type .= '支付宝小程序,';
            }
        }
        $log_type = substr($log_type, 0, -1);
        if($comment){
            //小程序描述
            $data['comment'] = $comment;
        }
        //扣除代理商费用
        if($usergroup == 3){
            $uid = Session::get('uid');
            $admin_res = Db::name('wd_xcx_admin') ->where('uid', $uid) ->find();
            if($price > $admin_res['balance']){
                $this ->error('对不起,您的余额不足,请联系管理员充值!!');
                exit;
            }else{
                if($price >0){
                    $fe = Db::name('wd_xcx_admin') ->where('uid',$uid) ->setDec('balance', $price);
                }else if($price == 0){
                    $fe = true;
                }
            }
        }else{
            $fe = true;
        }
        //数据保存
        $res = Db::name('wd_xcx_applet') ->where('id', $applet_id)->update($data);
        //保存操作日志
        $log['time'] = time();
        $log['admin'] = Session::get('uid');
        $log['type'] = '0';
        if($usergroup == 3){
            $man = '代理商';
        }else{
            $man = '管理员';
        }
        if($applet['pay_time'] < 0){
            $log['text'] = Session::get('name')."{$man}在".date('Y-m-d H:i:s', time()).', 将名称为'.$applet['name'].'的小程序,由试用进入正式使用,'.$log_time.', 类型为'.$log_type.$log_price;
        }else{
            $log['text'] = Session::get('name')."{$man}在".date('Y-m-d H:i:s', time()).', 对名称为'.$applet['name'].'的小程序进行了续费,'.$log_time.', 类型为'.$log_type.$log_price;
        }
        Db::name('wd_xcx_log') ->insert($log);
       
        if($res && $fe){
            $this->success('创建成功！','Applet/index');
        }else{
            $this->error('创建失败！');
            exit;
        }
    }
    //获取要删除的用户名称
    public function user_name(){
        $uid = $_POST['id'];
        $user = Db::name('wd_xcx_admin') -> where('uid', $uid) ->find();
        return $user['realname'];
    }
    public function upimg(){
        $file = $this->onepic_uploade("uploadfile");
        return $file;
    }
    //恢复删除用户
    public function recove()
    {
        $uid = input('uid');
        $res = Db::name('wd_xcx_admin') -> where('uid', $uid) ->update(['is_del' => 0]);
        if($res){
            return 1;
        }else{
            return 2;
        }
    }
    //执行彻底删除用户
    public function real_del_user(){
        $uid = input('uid');
        $res = Db::name('wd_xcx_admin') -> where('uid', $uid) ->delete();
        if($res) {
            return 1;
        }else{
            return 2;
        }
    }
    //
    public function get_pay_time(){
        $id = input("id");
        $res = Db::name('wd_xcx_time_combo')->where('id', $id) ->find();
        return $res['pay_time'];
    }

    

    //获取绑定域名
    public function get_domain(){
        $uniacid = input('uniacid');
        $set_do = input('domain');
        $domain = Db::name('wd_xcx_applet') ->where('id', $uniacid) ->field('domain') ->find();

        if(!$set_do){
            if($domain['domain']){
                return $domain['domain'];
            }else{
                $domain = 'http://'.$_SERVER['SERVER_NAME'].'/front/index/index?uniacid='.$uniacid;
                Db::name('wd_xcx_applet') ->where('id', $uniacid) ->update(['domain' => $domain]);
                return $domain;   //未设置
            }
        }else{
            Db::name('wd_xcx_applet') ->where('id', $uniacid) ->update(['domain' => $set_do]);
            $set_do = 'http://'.$set_do;
            return $set_do;   //更新设置
        }
        
    }


    //创建用户接口  --题词系统
    public function createApplet(){
        // $from = $_SERVER['HTTP_REFERER'];
        // $aa = strpos($from, '//');
        // $bb = strpos($from, '/', $aa+3);
        // $from_url = substr($from, $aa+2, $bb-$aa-2);


        $username = input('username');
        $types = input('type');
        $end_time = input('endtime');
        $now = time();

        //验证参数
        $rule = [
            'username'  => 'require|max:30',
            'types'   => 'require|regex:^\b[0,2,3,4](,\b[0,2,3,4])*$',
            'end_time' => "require|between: $now, 2147483647",
        ];
        $msg = [
            'username.require' => '用户名必须有',
            'username.max'     => '用户名最多不能超过30个字符',
            'types.require'   => '类型必须有',
            'types.regex'   => '类型必须符合要求',
            'end_time.require'  => '使用时间必须有',
            'end_time.between'  => '必须使用有效时间',
        ];
        $v_data = [
            'username'  => $username,
            'types'   => $types,
            'end_time' => $end_time,
        ];
        $validate = new Validate($rule, $msg);
        $result = $validate->check($v_data);
        if(!$result){
            $response = array(
                'status' => 'fail',
                'errorno' => '1',
                'msg' => $validate->getError()   // 参数有误
            );
            return json_encode($response);
        }

        $type = explode(',', $types);
        $type = array_unique($type);

        //创建用户
        $admin['username'] = $username;
       
        // 新增判断用户名唯一
        $yhm = Db::name('wd_xcx_admin')->where("username",$username)->count();
        if($yhm>0){
            $response = array(
                'status' => 'fail',
                'errorno' => '2',
                'msg' => '用户名重复'      // 用户名重复
            );
            return json_encode($response);
        }
        //用户组
        $admin['group'] = 1;
        $admin['updatetime'] = time();
        $adminid = Db::name('wd_xcx_admin')->insertGetId($admin);

        if(!$adminid){
            $response = array(
                'status' => 'fail',
                'errorno' => '3',
                'msg' => '用户名创建失败'      // 用户名创建失败
            );
            return json_encode($response);
        }

        //创建小程序
        $name = '新建项目';
        $comment = '小程序描述';
        
       
        //业务类型
        $data['type'] = serialize($type);
        //转为业务类型名称,记录日志
        $log_type = "";
        foreach ($type as $k=> $v){
            if($v == '0'){
                $log_type .= '微信小程序,';
            }else if($v == '1'){
                $log_type .= '百度小程序,';
            }else if($v == '3'){
                $log_type .= 'PC网站,';
            }else if($v == '4'){
                $log_type .= 'H5应用,';
            }else if($v == '5'){
                $log_type .= '字节跳动小程序,';
            }else if($v == '6'){
                $log_type .= 'QQ小程序,';
            }else{
                $log_type .= '支付宝小程序,';
            }
        }
        $log_type = substr($log_type, 0, -1);
       
        $data['adminid'] = $adminid;
        $data['dateline'] = time();
        $data['end_time'] = $end_time;
        // $data['combo_id'] = $_POST['combo_id'];
        $data['name'] = $name;
        // $data['jxs'] = Session::get('uid');
        $data['combo_id'] = 1;

        //数据保存
        $appletid = Db::name('wd_xcx_applet') ->insertGetId($data);
        if(!$appletid){
            Db::name('wd_xcx_admin') ->delete($adminid);
            $response = array(
                'status' => 'fail',
                'errorno' => '4',
                'msg' => '项目创建失败'      // 项目创建失败
            );
            return json_encode($response);
        }
        //保存操作日志
        $log['time'] = time();
        $log['admin'] = $adminid;
        $log['type'] = '0';
        $log['text'] = "总管理员在".date('Y-m-d H:i:s', time()).', 开通了名称为'.$name.'的项目, 使用到期时间为'.date('Y-m-d H:i:s', $end_time).', 类型为'.$log_type;
        Db::name('wd_xcx_log') ->insert($log);

        $appinfo = [
            'appletid' => $appletid,
            'type' => $types,
            'password' => '123456'
        ];

        $response = array(
            'status' => 'success',
            'errorno' => '0',
            'appinfo'  => $appinfo     // 创建成功返回
        );
        return json_encode($response);
    }


    //更新项目使用时间接口  --题词系统
    public function updateEndtime(){
        $appletid = input('appletid');
        $end_time = input('endtime');
        $now = time();

        //验证参数
        $rule = [
            'appletid'  => 'require',
            'end_time' => "require|between: $now, 2147483647",
        ];
        $msg = [
            'appletid.require' => '项目ID必须有',
            'end_time.require'  => '使用时间必须有',
            'end_time.between'  => '必须使用有效时间',
        ];
        $v_data = [
            'appletid'  => $appletid,
            'end_time' => $end_time,
        ];
        $validate = new Validate($rule, $msg);
        $result = $validate->check($v_data);
        if(!$result){
            return json_encode(['status' => 'fail','errorno' => '1','msg' => $validate->getError()]);  // 参数有误
        }
        
        $app = Db::name('wd_xcx_applet') ->where('id', $appletid) ->field('end_time') ->find();
        if(!$app){
            return json_encode(['status' => 'fail','errorno' => '2','msg' => '该项目不存在']);  // 该项目不存在
        }else{
            if($end_time < $app['end_time']){
                return json_encode(['status' => 'fail','errorno' => '3','msg' => '更新时间小于当前到期时间']);   // 更新时间小于当前到期时间
            }
        }

        $res = Db::name('wd_xcx_applet') ->where('id', $appletid) ->update(['end_time'=>$end_time]);
        if($res !== false){
            return json_encode(['status' => 'success','errorno' => '0','msg' => '更新成功']);   // 更新成功
        }else{
            return json_encode(['status' => 'fail','errorno' => '4','msg' => '更新失败']);   // 更新失败
        }

    }

    //根据用户名查询项目信息接口  --题词系统
    public function getproinfo(){
        $username = input('username');

        //验证参数
        $rule = [
            'username'  => 'require',
        ];
        $msg = [
            'username.require' => '用户名必须有',
        ];
        $v_data = [
            'username'  => $username,
        ];
        $validate = new Validate($rule, $msg);
        $result = $validate->check($v_data);
        if(!$result){
            return json_encode(['status' => 'fail','errorno' => '1','msg' => $validate->getError()]);  // 参数有误
        }

        $uid = Db::name('wd_xcx_admin') ->where('username', $username) ->field('uid') ->find();
        if(!$uid){
            return json_encode(['status' => 'fail','errorno' => '2','msg' => '用户不存在']);  
        }else{
            $app = Db::name('wd_xcx_applet') ->where('adminid', $uid['uid']) ->field('id, end_time') ->find();
            if(!$app){
                return json_encode(['status' => 'fail','errorno' => '3','msg' => '项目不存在']);  
            }else{
                return json_encode(['status' => 'success','errorno' => '0','appinfo'=> $app]);   //成功返回   
            }
        }
    }


    //根据删除项目以及用户接口  --题词系统
    public function delpro(){
        $appletid = input('appletid');

        if(!$appletid){
            return json_encode(['status' => 'fail','errorno' => '1','msg' => '项目ID必须有']);  // 参数有误
        }

        $uid = Db::name('wd_xcx_applet') ->where('id', $appletid) ->field('adminid') ->find();
        if(!$uid){
            return json_encode(['status' => 'fail','errorno' => '2','msg' => '项目不存在']);  // 参数有误
        }

        Db::startTrans();
        try {
            //删除用户
            $res = Db::name('wd_xcx_admin') ->where('uid', $uid['adminid']) ->delete();

            //删除项目
            $r = Db::name('wd_xcx_applet') ->delete($appletid);

            if($res && $r){
                Db::commit();
            }else{
                throw new \Exception("发送未知错误, 操作失败, 请稍后再试!");
            }
            
        } catch(\Exception $e) {
            Db::rollback(); 
            return json_encode(['status' => 'fail','errorno' => '3','msg' => $e->getMessage()]);  
        }

        return json_encode(['status' => 'success','errorno' => '0']);
    }


    //修改用户名接口  --题词系统
    public function updateUsername(){
        $old_username = input('username');
        $new_username = input('newname');
        
         //验证参数
        $rule = [
            'old_username'  => 'require',
            'new_username'   => 'require|max:30',
        ];
        $msg = [
            'old_username.require' => '旧用户名必须有',
            'new_username.max'     => '新用户名最多不能超过30个字符',
            'new_username.require'   => '新用户名必须有',
        ];
        $v_data = [
            'old_username'  => $old_username,
            'new_username'   => $new_username,
        ];
        $validate = new Validate($rule, $msg);
        $result = $validate->check($v_data);
        if(!$result){
            return json_encode(['status' => 'fail','errorno' => '1','msg' => $validate->getError()]);  // 参数有误
        }

        $count = Db::name('wd_xcx_admin') ->where('username', $new_username) ->count();
        if($count > 0){
            return json_encode(['status' => 'fail','errorno' => '2','msg' => '新用户名已存在, 请重新修改']);  // 参数有误
        }


        $uid = Db::name('wd_xcx_admin') ->where('username', $old_username) ->field('uid') ->find();

        if(!$uid){
            return json_encode(['status' => 'fail','errorno' => '3','msg' => '旧用户不存在']);  // 参数有误
        }else{
            $res = Db::name('wd_xcx_admin') ->where('uid', $uid['uid']) ->update(['username' => $new_username]);
            if($res){
                return json_encode(['status' => 'success','errorno' => '0']);
            }else{
                return json_encode(['status' => 'fail','errorno' => '4','msg' => '操作失败, 请稍后再试!']);
            }
        }

    }


    // 开通微信或者支付宝小程序接口   --题词系统
    public function openMoreprogram(){
        $appletid = input('appletid');  
        $type = input('type');   // 0 微信小程   2  支付宝小程序 
        
         //验证参数
        $rule = [
            'appletid'  => 'require',
            'type'   => 'require|regex:^\b[0,2]$',
        ];
        $msg = [
            'appletid.require' => '项目ID必须有',
            'type.require'     => '开通小程序类型必须有',
            'type.regex'   => '开通小程序类型不正确',
        ];
        $v_data = [
            'appletid'  => $appletid,
            'type'   => $type,
        ];
        $validate = new Validate($rule, $msg);
        $result = $validate->check($v_data);
        if(!$result){
            return json_encode(['status' => 'fail','errorno' => '1','msg' => $validate->getError()]);  // 参数有误
        }

        $applet = Db::name('wd_xcx_applet') ->where('id', $appletid) ->field('type') ->find();
        if(!$applet){
            return json_encode(['status' => 'fail','errorno' => '2','msg' => '项目不存在']); 
        }else{
            $types = unserialize($applet['type']);
            if($types == [0] || $types == [2]){
                if(in_array($type, $types)){
                    return json_encode(['status' => 'fail','errorno' => '4','msg' => '该项目已有该小程序']); 
                }else{
                    $types = ['0', '2'];
                    $res = Db::name('wd_xcx_applet') ->where('id', $appletid) ->update(['type'=>serialize($types)]);
                    if($res){
                        return json_encode(['status' => 'success','errorno' => '0']);
                    }else{
                        return json_encode(['status' => 'fail','errorno' => '5','msg' => '开通失败']); 
                    }
                }
            }else{
                return json_encode(['status' => 'fail','errorno' => '3','msg' => '该项目无法开通']); 
            }
        }


    }
}