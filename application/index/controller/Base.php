<?php

/**

 * Created by PhpStorm.

 * User: Administrator

 * Date: 2018/8/16

 * Time: 9:22

 */
namespace app\index\controller;

use think\Controller;

use think\Request;

use think\Db;


class Base extends Controller

{
    protected $prefix = '';

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this -> is_overdue();
        if(!check_login()){

            check_group();

            $this->redirect('Login/index');
        };
        $this ->prefix = config('database.prefix');
        //include_once 'Ordinary.php';
        //$or = new \Ordinary();
       // $or ->Secret();

        //验证操作权限
        $appletid = input("appletid");
        if(!$appletid){
            $appletid = input("uniacid");
        }
        $controller = strtolower(Request::instance()->controller());
        $action = strtolower(Request::instance()->action());

        $route_name = $controller.'/'.$action;
        if($route_name != 'datashow/index' && $route_name != 'diypage/selecticon' && $route_name != 'modals/index'){
            if($route_name != 'index/index' && $route_name != 'index/save'){
                $this ->checkPluginEnable($route_name);
            }
            $this->check_rule($appletid, $route_name);
        }
    }

    //判断是否过期
    protected function is_overdue(){
        $id = input('appletid');
        $res = Db::name('wd_xcx_applet') -> where('id', $id) ->field('end_time') -> find();
        $overdue_date = $res['end_time'];
        $now = time();
        if($overdue_date != 0){
            if($overdue_date < $now){
                $this -> error('对不起,您的小程序使用权限已过期,请续费!');
            }
        }

    }


    //检验改操作的权限
    protected function check_rule($appletid, $route_name){
        $node_id = Db::name('wd_xcx_applet') ->alias('a') ->join('wd_xcx_combo b', 'a.combo_id = b.id') ->where('a.id', $appletid) ->field('node_id') ->find();
        $node_id = unserialize($node_id['node_id']);
        if($node_id){
            $result = Db::name('wd_xcx_rule') ->where('route_name', 'like', '%'.$route_name.'%') ->field('id') ->select();
            if(count($result)>0){
                $ids = [];
                foreach ($result as $key => $value) {
                    array_push($ids, $value['id']);
                }
                if(!array_intersect($node_id, $ids)){
                    $this->error('对不起，您没有该操作权限！');
                }
            }else{
                $this->error('对不起，您没有该操作权限！');
            }
        }else{
            $this->error('对不起，您没有该操作权限！');
        }
    }

    //校验插件的路由是否可以进入
    protected function checkPluginEnable($route_name){
        $par = include('Parameter.php');
        $plugin_route = $par['plugin_route'];

        $plugin_name = '';
        foreach ($plugin_route as $k => $item){
            if(stristr($item, $route_name)){
                $plugin_name = $k;
            }
        }
        if($plugin_name){
            //include_once 'Ordinary.php';
           // $or = new \Ordinary();
            //$plugin = $or ->checkAuth();
            //if(!$plugin[$plugin_name]){
           //     $this ->error('对不起，该插件未开通，请联系管理员！');
           // }
        }
    }


    public function getAuthToken(){
        $check_host = "http://120.27.216.9/";
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
}