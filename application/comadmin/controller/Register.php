<?php
namespace app\comadmin\controller;

use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;

class Register extends Controller
{

      /*配置页*/
    public function index(){
        if(check_login()){
//            include ROOT_PATH.'application/index/controller/Ordinary.php';
           // $or = new \Ordinary();
          //  $license = $or ->checkAuth();
//            $this->assign("license", $license);

            $combo = Db::name('wd_xcx_combo') ->select();
            $this->assign('combo', $combo);

            $config = Db::name('wd_xcx_register') ->find();
            $type_arr = $config['projects'] ? unserialize($config['projects']) : [];
            $this->assign('config', $config);
            $this->assign('type_arr', $type_arr);

            return $this->fetch('index');
        }else{
            $this->redirect('Index/Login/index');
        }
    }

    public function save(){
        $shortmsg = input('shortmsg');
        $day = input('day');
        $type = input('type/a');
        $combo_id = input('combo');
        $data = [
            'shortmsg' => $shortmsg,
            'day' => $day,
            'projects' => serialize($type),
            'combo_id' => $combo_id,
            'flag' => input('flag')
        ];
        $is = Db::name('wd_xcx_register')->find();
        if($is){
            $res = Db::name('wd_xcx_register')->where('id', 1)->update($data);
        }else{
            $res = Db::name('wd_xcx_register')->insert($data);
        }
        if($res){
            $this->success('注册设置完成');
        }else{
            $this->error('无修改项');
        }
    }

}