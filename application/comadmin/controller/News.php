<?php
namespace app\comadmin\controller;

use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;

class News extends Controller
{
    public function index(){

        if(check_login()){
            $this->assign("page",1);
            $item = Db::name('wd_xcx_com_news')->where("type",1)->order("num desc,id desc")->paginate(10);
            $count = Db::name('wd_xcx_com_news')->where("type",1)->count();
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
               $item = Db::name('wd_xcx_com_news')->where("id",$newsid)->find();
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

        $tips = input('tips');

        if($tips){

            $data['tips'] = $tips;

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


        $remarks = input('remarks');

        if($remarks){

            $data['remarks'] = $remarks;

        }

        $text = input('text');
        if($text){
            $data['text'] = htmlspecialchars_decode($text);
        }

        $newsid = input("newsid");

        $data['type'] = 1;
        if($newsid){
            $createtime = strtotime(input("createtime"));
            if($createtime>0){
                $data['createtime'] = $createtime;
            }
            $res = Db::name('wd_xcx_com_news')->where("id",$newsid)->update($data);

        }else{
            $createtime = strtotime(input("createtime"));
            if($createtime>0){
                $data['createtime'] = $createtime;
            }else{
                $data['createtime'] = time();
            }
            $res = Db::name('wd_xcx_com_news')->insert($data);
        }

        if($res){
           $this->success('更新成功！', Url('News/index'));

        }else{

          $this->error('更新失败，没有修改项！');

          exit;

        }

    }
    public function del(){
       $newsid = input("newsid");
        if($newsid){
           $res = Db::name('wd_xcx_com_news')->where("id",$newsid)->delete();
           if($res){
               $this->success('删除成功');
            }else{
              $this->error('删除失败');
              exit;
            }
        } 
    }
    public function gg(){

        if(check_login()){
            $this->assign("page",1);
            $item = Db::name('wd_xcx_com_news')->where("type",2)->order("num desc,id desc")->paginate(10);
            $count = Db::name('wd_xcx_com_news')->where("type",2)->count();
            $this->assign("count",$count);
            $this->assign("list",$item);
            return $this->fetch('gg');
        }else{
            $this->redirect('Index/Login/index');
        }
    }
    public function addgg(){
        if(check_login()){
            $newsid = input("newsid");
            if($newsid){
               $item = Db::name('wd_xcx_com_news')->where("id",$newsid)->find();
            }else{
                $item = "";
            }
            $this->assign("page",1);
            $this->assign('newsid',$newsid);
            $this->assign('newsinfo',$item);
            return $this->fetch('addgg');
        }else{
            $this->redirect('Index/login/index');
        }
    }
    public function savegg(){
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

        $tips = input('tips');

        if($tips){

            $data['tips'] = $tips;

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


        $remarks = input('remarks');

        if($remarks){

            $data['remarks'] = $remarks;

        }

        $text = input('text');
        if($text){
            $data['text'] = htmlspecialchars_decode($text);
        }

        $newsid = input("newsid");

        $data['type'] = 2;
        if($newsid){
            $createtime = strtotime(input("createtime"));
            if($createtime>0){
                $data['createtime'] = $createtime;
            }
            $res = Db::name('wd_xcx_com_news')->where("id",$newsid)->update($data);

        }else{
            $createtime = strtotime(input("createtime"));
            if($createtime>0){
                $data['createtime'] = $createtime;
            }else{
                $data['createtime'] = time();
            }
            $res = Db::name('wd_xcx_com_news')->insert($data);
        }

        if($res){
           $this->success('更新成功！', Url('News/gg'));

        }else{

          $this->error('更新失败，没有修改项！');

          exit;

        }

    }
    public function update(){

        if(check_login()){
            $this->assign("page",1);
            $item = Db::name('wd_xcx_com_news')->where("type",3)->order("num desc,id desc")->paginate(10);
            $count = Db::name('wd_xcx_com_news')->where("type",3)->count();
            $this->assign("count",$count);
            $this->assign("list",$item);
            return $this->fetch('update');
        }else{
            $this->redirect('Index/Login/index');
        }
    }
    public function addupdate(){
        if(check_login()){
            $newsid = input("newsid");
            if($newsid){
               $item = Db::name('wd_xcx_com_news')->where("id",$newsid)->find();
            }else{
                $item = "";
            }
            $this->assign("page",1);
            $this->assign('newsid',$newsid);
            $this->assign('newsinfo',$item);
            return $this->fetch('addupdate');
        }else{
            $this->redirect('Index/login/index');
        }
    }
    public function saveupdate(){
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

            $data['recommend'] = 3;

        }

        //访问量

        $hits = input('hits');

        if($hits){

            $data['hits'] = intval($hits);

        }

        $tips = input('tips');

        if($tips){

            $data['tips'] = $tips;

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


        $remarks = input('remarks');

        if($remarks){

            $data['remarks'] = $remarks;

        }

        $text = input('text');
        if($text){
            $data['text'] = htmlspecialchars_decode($text);
        }

        $newsid = input("newsid");

        $data['type'] = 3;
        if($newsid){
            $createtime = strtotime(input("createtime"));
            if($createtime>0){
                $data['createtime'] = $createtime;
            }
            $res = Db::name('wd_xcx_com_news')->where("id",$newsid)->update($data);
        }else{
            $createtime = strtotime(input("createtime"));
            if($createtime>0){
                $data['createtime'] = $createtime;
            }else{
                $data['createtime'] = time();
            }
            $res = Db::name('wd_xcx_com_news')->insert($data);
        }

        if($res){
           $this->success('更新成功！', Url('News/update'));

        }else{

          $this->error('更新失败，没有修改项！');

          exit;

        }

    }

}