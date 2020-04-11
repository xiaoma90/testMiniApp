<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/17
 * Time: 9:05
 */
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;
class timecombo extends Controller
{
    //时长套餐列表页
    public function index(){
        if(check_login()){
            if(check_group()){
                if(input('keyworld')){
                    // $res = Db::name('wd_xcx_applet')->where('name','like','%'.input("keyworld").'%')->where($where)->order('id desc')->paginate(10,false,[ 'query' => array('keyworld'=>input("keyworld"))]);
                    // $count = Db::name('wd_xcx_applet')->where('name','like','%'.input("keyworld").'%')->where($where)->count();
                   $combo = Db::name('wd_xcx_time_combo') ->where('name','like','%'.input("keyworld").'%') ->paginate(10,false,[ 'query' => array('keyworld'=>input("keyworld"))]);
                   $c = Db::name('wd_xcx_time_combo') ->where('name','like','%'.input("keyworld").'%') ->count();
                }else{
                    //获取套餐列表数据
                    $combo = Db::name('wd_xcx_time_combo')->order('id asc') -> paginate(10);
                    //查询套餐总数
                    $c = Db::name('wd_xcx_time_combo')->order('id desc') ->count();
                }
                $this->assign('time_combo', $combo);
                $this->assign('c', $c);
                return $this->fetch('index');
            }else{
                $this->error("您没有权限操作该模块！",'Applet/applet');
            }
        }else{
            $this->redirect('Login/index');
        }
    }
    //添加时长套餐页
    public function add(){
        if(check_login()){
            if(check_group()){
                return $this ->fetch('add');
            }
        }else{
            $this->redirect('Login/index');
        }
    }
    //保存添加套餐数据
    public function save_add(){
        //获取数据
        $name = $_POST['name'];
        //$type = $_POST['type'];
        $pay_time = $_POST['pay_time'];
        $free_time = $_POST['free_time'];
        //判断套餐名
        if($name){
            $data['name'] = $name;
        }else{
            $this->error('请输入套餐名称');
        }
        //判断套餐时长
        if($pay_time){
            $data['pay_time'] = $pay_time;
        }else{
            $this->error('请输入套餐时长');
        }
        //判断赠送时长
        if($free_time !== 0){
            $data['free_time'] = $free_time;
        }else{
            $this->error('请输入赠送时长');
        }
        //类型
        //$data['type'] = $type;
        //创建时间
        $data['createtime'] = time();
        //保存
        $r = Db::name('wd_xcx_time_combo') -> insert($data);
        if($r){
            $this ->success('时长套餐添加成功!', 'Timecombo/index');
        }else{
            $this ->error('添加失败!');
        }
    }
    //删除套餐
    public function del(){
        $id = $_POST['id'];
        $res = Db::name('wd_xcx_time_combo') -> where('id',$id) ->delete();
        if($res){
            return 1;
        }else {
            return 2;
            exit;
        }
    }
    //修改套餐页面
    public function edit(){
        if(check_login()){
            if(check_group()){
                $id = $_GET['id'];
                //获取当前套餐信息
                $res = Db::name('wd_xcx_time_combo') -> where('id', $id) ->find();
                $this ->assign('time_combo', $res);
                return $this ->fetch('edit');
            }
        }else{
            $this->redirect('Login/index');
        }
    }
    //保存修改套餐的数据
    public function save_edit(){
        //获取提交的数据
        $id = $_POST['id'];
        $name = $_POST['name'];
       // $type = $_POST['type'];
        $pay_time = $_POST['pay_time'];
        $free_time = $_POST['free_time'];
        //判断套餐名
        if(!$name){
            $this->error('请输入套餐名称');
        }
        //判断套餐名
        if(!$pay_time){
            $this->error('请输入套餐时长');
        }//判断套餐名
        if($free_time === 0){
            $this->error('请输入赠送时长');
        }
        $res = Db::name('wd_xcx_time_combo') ->where('id', $id) ->update([
                'name' => $name,
               // 'type' => $type,
                'pay_time' => $pay_time,
                'free_time' => $free_time
        ]);
        if($res !== false){
            $this->success('套餐修改成功！','Timecombo/index');
        }else {
            $this->error('套餐修改失败！');
            exit;
        }
    }
    //根据ID获取套餐名称
    public function combo_name(){
        $id = $_POST['id'];
        $res = Db::name('wd_xcx_time_combo') ->where('id', $id) ->field('name') ->find();
        return $res['name'];
    }
    //根据ID获取combo内容,编辑
    public function timecombo_info(){
        $id = $_POST['id'];
        $res = Db::name('wd_xcx_time_combo') ->where('id', $id) ->find();
        return $res;
    }
}