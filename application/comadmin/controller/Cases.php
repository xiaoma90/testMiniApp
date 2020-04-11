<?php
namespace app\comadmin\controller;

use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;

class Cases extends Controller
{
    public function index(){

        if(check_login()){
            $this->assign("page",1);
            $item = Db::name('wd_xcx_com_cases')->order("num desc,id desc")->paginate(10);
            $count = Db::name('wd_xcx_com_cases')->count();
            $this->assign("count",$count);
            $this->assign("list",$item);
            return $this->fetch('index');
        }else{
            $this->redirect('Index/Login/index');
        }
    }
    public function add(){
        if(check_login()){
            $newsid = input("newsid");
            if($newsid){
               $item = Db::name('wd_xcx_com_cases')->where("id",$newsid)->find();
            }else{
                $item = "";
            }
            $this->assign("page",1);
            $this->assign('newsid',$newsid);
            $this->assign('newsinfo',$item);
            return $this->fetch('add');
        }else{
            $this->redirect('Index/login/index');
        }
    }
    public function save(){
        $data = array();

        //排序
        $num = input('num');

        if($num){

            $data['num'] = intval($num);

        }

        //上下架
        $flag = input('flag');

        if($flag){

            $data['flag'] = intval($flag);

        }

        //推荐

        $recommend = input("recommend");

        if($recommend){

            $data['recommend'] = intval($recommend);

        }else{

            $data['recommend'] = 2;

        }

        //访问量

        $hits = input('hits');

        if($hits){

            $data['hits'] = intval($hits);

        }

        $ewm = input('commonuploadpic1');

        if($ewm){

            $data['ewm'] = $ewm;

        }
        $pic = input('commonuploadpic2');

        if($pic){

            $data['pic'] = $pic;

        }



        //标题

        $title = input('title');

        if($title){

            $data['title'] = $title;

        }
        $casetype = input('casetype');

        if($casetype){

            $data['casetype'] = $casetype;

        }

        $text = input('text');
        if($text){
            $data['text'] = htmlspecialchars_decode($text);
        }

        $newsid = input("newsid");

        if($newsid){
            $res = Db::name('wd_xcx_com_cases')->where("id",$newsid)->update($data);

        }else{
            $data['createtime'] = time();
            $res = Db::name('wd_xcx_com_cases')->insert($data);
        }

        if($res){
           $this->success('更新成功！', Url('Cases/index'));

        }else{

          $this->error('更新失败，没有修改项！');

          exit;

        }

    }
    public function del(){
       $newsid = input("newsid");
        if($newsid){
           $res = Db::name('wd_xcx_com_cases')->where("id",$newsid)->delete();
           if($res){
               $this->success('删除成功');
            }else{
              $this->error('删除失败');
              exit;
            }
        } 
    }
}