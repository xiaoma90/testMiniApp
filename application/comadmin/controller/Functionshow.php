<?php
namespace app\comadmin\controller;

use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;

class Functionshow extends Controller
{
    public function index(){

        if(check_login()){
            $this->assign("page",1);
            $item = Db::name('wd_xcx_com_func')->order("num desc,id desc")->paginate(10);
            $count = Db::name('wd_xcx_com_func')->count();
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
               $item = Db::name('wd_xcx_com_func')->where("id",$newsid)->find();
               if($item){
                    if($item['funcimg']){
                        $item['funcimg'] = unserialize($item['funcimg']);
                    }
               }
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

        $icon = input('icon');

        if($icon){

            $data['icon'] = $icon;

        }

        $listbg = input('listbg');

        if($listbg){

            $data['listbg'] = $listbg;

        }

        //标题

        $title = input('title');

        if($title){

            $data['title'] = $title;

        }

       
        //简介

        $desc = input('descs');

        if($desc){

            $data['descs'] = $desc;

        }

        $func = input('func');

        if($func){

            $data['func'] = $func;

        }
        $place = input('place');

        if($place){

            $data['place'] = $place;

        }

        $text = input('text');
        if($text){
            $data['text'] = $text;
        }



        $imgsrcs = input("imgsrcs/a");
        if($imgsrcs){
            $data['funcimg'] = serialize($imgsrcs);
        }else{
            $data['funcimg'] = "";
        }
        $newsid = input("newsid");
        if($newsid){
            $res = Db::name('wd_xcx_com_func')->where("id",$newsid)->update($data);

        }else{
            $data['createtime'] = time();
            $res = Db::name('wd_xcx_com_func')->insert($data);
        }

        if($res){
           $this->success('功能展示更新成功！', Url('Functionshow/index'));

        }else{

          $this->error('功能展示更新失败，没有修改项！');

          exit;

        }

    }
    public function del(){
       $newsid = input("newsid");
        if($newsid){
           $res = Db::name('wd_xcx_com_func')->where("id",$newsid)->delete();
           if($res){
               $this->success('删除成功');
            }else{
              $this->error('删除失败');
              exit;
            }
        } 
    }
}