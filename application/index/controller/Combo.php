<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/13
 * Time: 17:51
 */
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;
class Combo extends Controller
{
    //管理套餐组首页
    public function index(){
        if(check_login()){
            if(check_group()){
                if(input('keyworld')){
                    //$res = Db::name('wd_xcx_applet')->where('name','like','%'.input("keyworld").'%')->where($where)->order('id desc')->paginate(10,false,[ 'query' => array('keyworld'=>input("keyworld"))]);
                    //$count = Db::name('wd_xcx_applet')->where('name','like','%'.input("keyworld").'%')->where($where)->count();
                    $combo = Db::name('wd_xcx_combo') ->where('name','like','%'.input("keyworld").'%') ->paginate(10,false,[ 'query' => array('keyworld'=>input("keyworld"))]);
                    $c = Db::name('wd_xcx_combo') ->where('name','like','%'.input("keyworld").'%') ->count();
                }else{
                    //获取套餐列表数据
                    $combo = Db::name('wd_xcx_combo')->order('id desc') -> paginate(10);
                    //查询套餐总数
                    $c = Db::name('wd_xcx_combo')->order('id desc') ->count();
                }

                //获取开通的平台
                //include_once 'Ordinary.php';
               // $or = new \Ordinary();
                //$plat = $or ->checkPlat();
               // $this ->assign('plat', $plat);
                $this->assign('combo', $combo);
                $this->assign('c', $c);
                return $this->fetch('index');
            }else{
                $this->error("您没有权限操作该模块！",'Applet/applet');
            }
        }else{
            $this->redirect('Login/index');
        }
    }
    //添加套餐组页面
    public function add_combo(){
        if(check_login()){
            if(check_group()){
                return $this->fetch('add_combo');
            }
        }else{
            $this->redirect('Login/index');
        }
    }
    //添加套餐组
    public function save_combo(){
        $name = $_POST['name'];
        $icon = $_POST['icon'];
        $wx_price = input('wx_price');
        $baidu_price = input('baidu_price');
        $ali_price = input('ali_price');
        $h5_price = input('h5_price');
        $pc_price = input('pc_price');
        $qq_price = input('qq_price');
        $bdance_price = input('bdance_price');
        $combo_desc = $_POST['combo_desc'];
        //判断套餐名
        if($name){
            $data['name'] = $name;
        }else{
            $this->error('请输入套餐名称');
        }
        //判断图标
        if($icon){
            $data['icon'] = moveurl($icon);
        }
        //判断套餐微信价格
        if($wx_price){
            if(is_numeric($wx_price)){
                $data['wx_price'] = $wx_price;
            }else{
                $this->error('请输入正确的套餐微信小程序价格');
            }
        }else{
            $this->error('请输入套餐微信小程序价格');
        }
        //判断套餐百度价格
        if($baidu_price){
            if(is_numeric($baidu_price)){
                $data['baidu_price'] = $baidu_price;
            }else{
                $this->error('请输入正确的套餐百度小程序价格');
            }
        }else{
            $this->error('请输入套餐百度小程序价格');
        }

        //include_once 'Ordinary.php';
       // $or = new \Ordinary();
        //$plat = $or ->checkPlat();

        //判断套餐阿里价格
//        if($plat['ali']){
            if($ali_price){
                if(is_numeric($ali_price)){
                    $data['ali_price'] = $ali_price;
                }else{
                    $this->error('请输入正确的套餐支付宝小程序价格');
                }
            }else{
                $this->error('请输入套餐支付宝小程序价格');
            }
//        }


        //判断套餐H5价格
//        if($plat['h5']){
            if($h5_price){
                if(is_numeric($h5_price)){
                    $data['h5_price'] = $h5_price;
                }else{
                    $this->error('请输入正确的套餐H5应用价格');
                }
            }else{
                $this->error('请输入套餐H5应用价格');
            }
//        }


        //判断套餐头条价格
//        if($plat['byte']){
            if($bdance_price){
                if(is_numeric($bdance_price)){
                    $data['bdance_price'] = $bdance_price;
                }else{
                    $this->error('请输入正确的套餐字节跳动小程序价格');
                }
            }else{
                $this->error('请输入套餐字节跳动小程序价格');
            }
//        }


        //判断套餐头条价格
//        if($plat['qq']){
            if($qq_price){
                if(is_numeric($qq_price)){
                    $data['qq_price'] = $qq_price;
                }else{
                    $this->error('请输入正确的套餐QQ小程序价格');
                }
            }else{
                $this->error('请输入套餐QQ小程序价格');
            }
//        }

        
        //判断套餐PC价格
//        if($plat['pc']){
            if($pc_price){
                if(is_numeric($pc_price)){
                    $data['pc_price'] = $pc_price;
                }else{
                    $this->error('请输入正确的套餐PC网站价格');
                }
            }else{
                $this->error('请输入套餐PC网站价格');
            }
//        }


        $data['combo_desc'] = $combo_desc;
        //默认权限
        $data['node_id'] = 'a:5:{i:0;s:1:"1";i:1;s:1:"2";i:2;s:3:"104";i:3;s:3:"105";i:4;s:3:"137";}';
        //创建时间
        $data['createtime'] = time();
        //插入数据库
        $res = Db::name('wd_xcx_combo') -> insert($data);
        if($res){
            $this->success('套餐添加成功！','Combo/');
        }else {
            $this->error('套餐添加失败！');
            exit;
        }
    }
    //修改套餐组页面
    public function edit_combo(){
        if(check_login()){
            if(check_group()){
                $id = $_GET['id'];
                $combo = Db::name('wd_xcx_combo') -> where('id', $id) ->find();
                $this ->assign('combo', $combo);
                return $this->fetch('edit_combo');
            }
        }else{
            $this->redirect('Login/index');
        }
    }
    //保存修改套餐组数据
    public function save_edit_combo(){
        $id = $_POST['id'];
        $name = $_POST['name'];
        $adds = $_POST['icon'];
        if( $adds != null){
            $icon = $adds ;
            // echo 'a';
        }else{
            $res = Db::name('wd_xcx_combo') -> where('id', $id) ->field('icon') ->find();
            $icon = $res['icon'];
        }
        $wx_price = input('wx_price');
        $baidu_price = input('baidu_price');
        $ali_price = input('ali_price');
        $h5_price = input('h5_price');
        $qq_price = input('qq_price');
        $pc_price = input('pc_price');
        $bdance_price = input('bdance_price');

        $combo_desc = $_POST['combo_desc'];
        //判断套餐名
        if($name){
            $data['name'] = $name;
        }else{
            $this->error('请输入套餐名称');
        }
        //判断套餐微信价格
        if($wx_price){
            if(is_numeric($wx_price)){
            }else{
                $this->error('请输入正确的套餐微信价格');
            }
        }else{
            $this->error('请输入套餐微信价格');
        }

        //判断套餐百度价格
        if($baidu_price){
            if(is_numeric($baidu_price)){
            }else{
                $this->error('请输入正确的套餐百度价格');
            }
        }else{
            $this->error('请输入套餐百度价格');
        }

//        include_once 'Ordinary.php';
       // $or = new \Ordinary();
       // $plat = $or ->checkPlat();

        //判断套餐支付宝小程序价格
//        if($plat['ali']){
            if($ali_price){
                if(is_numeric($ali_price)){
                }else{
                    $this->error('请输入正确的套餐支付宝小程序价格');
                }
            }else{
                $this->error('请输入套餐支付宝小程序价格');
            }
//        }


        //判断套餐H5应用价格
//        if($plat['h5']){
            if($h5_price){
                if(is_numeric($h5_price)){
                }else{
                    $this->error('请输入正确的套餐H5应用价格');
                }
            }else{
                $this->error('请输入套餐H5应用价格');
            }
//        }


        //判断套餐字节跳动小程序价格
//        if($plat['byte']){
            if($bdance_price){
                if(is_numeric($bdance_price)){
                }else{
                    $this->error('请输入正确的套餐字节跳动小程序价格');
                }
            }else{
                $this->error('请输入套餐字节跳动小程序价格');
            }
//        }


        //判断套餐头条价格
//        if($plat['qq']){
            if($qq_price){
                if(is_numeric($qq_price)){
                }else{
                    $this->error('请输入正确的套餐QQ小程序价格');
                }
            }else{
                $this->error('请输入套餐QQ小程序价格');
            }
//        }


        //判断套餐PC网站价格
//        if($plat['pc']){
            if($pc_price){
                if(is_numeric($pc_price)){
                }else{
                    $this->error('请输入正确的套餐PC网站价格');
                }
            }else{
                $this->error('请输入套餐PC网站价格');
            }
//        }

        //更新数据
        $res = Db::name('wd_xcx_combo') ->where('id', $id) ->update([
            'name'    => $name,
            'wx_price'   => $wx_price,
            'icon'     => moveurl($icon),
            'baidu_price'   => $baidu_price,
            'ali_price'   => $ali_price,
            'h5_price'   => $h5_price,
            'qq_price'   => $qq_price,
            'pc_price'   => $pc_price,
            'bdance_price'   => $bdance_price,
            'combo_desc' => $combo_desc
        ]);
        if($res !== false){
            $this->success('套餐修改成功！','Combo/index');
        }else {
            $this->error('套餐修改失败！');
            exit;
        }
    }
    //删除套餐
    public function del_combo(){
        $id = $_POST['id'];
        //删除
        $res = Db::name('wd_xcx_combo') -> where('id',$id) ->delete();
        if($res){
            return 1;
        }else {
            return 2;
        }
    }
    //分配权限页面
    public function rule(){
        //获取id
        $id = $_GET['id'];
        //根据ID 查询当前套餐具有的权限
        $combo = Db::name('wd_xcx_combo')->where('id', $id) -> field('node_id') ->find();
        $combo = unserialize($combo['node_id']);
        if(!$combo){
            $combo = array();
        }
        //查询所有的权限
        $parm = include('Parameter.php');
        $plugin_rule = $parm['rule_id'];
        $other = [76, 215];
        //查询插件授权
//        include_once 'Ordinary.php';
        //$or = new \Ordinary();
       // $auth = $or ->checkPlugin();
        $rule_controller = [];
        foreach ($auth as $k => $au){
            if(!$au){
                $rule_controller = array_merge($rule_controller, $plugin_rule[$k]);
            }
        }
        $rule_controller = array_merge($rule_controller, $other);
        $rule = Db::name('wd_xcx_rule') ->where('id', 'NOT IN', $rule_controller) ->select();
        $rule_count = count($rule);
        $list = $this -> getTree($rule);
        $test = $this ->change($list);
        //array_shift($test);

        $this ->assign('rule', $test);
        $this ->assign('id', $id);
        $this ->assign('combo', $combo);
        $this ->assign('rule_count', $rule_count + 1);
        return $this->fetch('rule');
    }
    //tets
    public function test(){
        //查询所有的权限
        $rule = Db::name('wd_xcx_rule')->select();
        $test = $this ->change($rule);
        $this ->assign('test', $test);
        return $this ->fetch('test');
    }
    //保存权限到套餐组
    public function save_rule(){
        $id = $_POST['id'];
        $rule = $_POST['temp'];
        $rule = serialize($rule);
        $res = Db::name('wd_xcx_combo') ->where('id', $id) -> update(['node_id' => $rule]);
        if($res){
            return 1;
        }else{
            return 2;
        }
    }
    //树形结构
    private function getTree($data, $pid=0, $level=0){
        static $list = array();
        foreach ($data as $k => $v){
            if($v['pid'] == $pid){
                $v['level'] = $level;
                $list[] = $v;
                //查找子类
                $this->getTree($data, $v['id'], $level+1);
            }
        }
        return $list;
    }
    //获取子类型id
    public function getChild($data, $id, $isClear=false){
        static $child = array();
        if($isClear){
            $child = array();
        }
        foreach ($data as $k => $v){
            if($v['pid'] == $id){
                $child[] = $v['id'];
                $this ->getChild($data, $v['id']);
            }
        }
        return $child;
    }
    //单个图片上传操作
    function onepic_uploade($file){
        $thumb = request()->file($file);
        if(isset($thumb)){
            $dir = upload_img();
            $info = $thumb->move($dir);
            if($info){
                $imgurl = ROOT_HOST."/upimages/".date("Ymd",time())."/".$info->getFilename();
                return $imgurl;
            }
        }
    }
    //将一维根据级别转换
    public function change($data){
        static $list = array();
        foreach ($data as $k => $v){
            if($v['pid'] == 0){
                foreach ($data as $k1 => $v1){
                    if($v1['pid'] == $v['id']){
                        foreach ($data as $k2 => $v2){
                            if($v2['pid'] == $v1['id']){
                                $v1['child'][] = $v2;
                            }
                        }
                        $v['child'][] = $v1;
                    }
                }
                $list[] = $v;
            }
        }
        return $list;
    }
    //获取套餐名
    public function combo_name(){
        $id = $_POST['id'];
        $res = Db::name('wd_xcx_combo') ->where('id', $id) ->field('name') ->find();
        return $res['name'];
    }
    //根据ID获取combo内容,编辑
    public function combo_info(){
        $id = $_POST['id'];
        $res = Db::name('wd_xcx_combo') ->where('id', $id) ->find();
        return $res;
    }
}