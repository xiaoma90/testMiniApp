<?php
namespace app\comadmin\controller;

use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;

class Solution extends Controller
{
    public function index(){
        if(check_login()){
            $this->assign("page",1);
            $item = Db::name('wd_xcx_com_solution')->order("num desc,id desc")->paginate(10);
            $count = Db::name('wd_xcx_com_solution')->count();
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
               $item = Db::name('wd_xcx_com_solution')->where("id",$newsid)->find();
               if($item){
                    $item['slides'] = unserialize($item['slides']);
                    $typedesc = array_values(unserialize($item['typedesc']));
                    if(!$typedesc){
                        $item['icon1'] = "";
                        $item['icon2'] = "";
                        $item['icon3'] = "";
                        $item['icon4'] = "";
                        $item['icon5'] = "";
                        $item['title1'] = "";
                        $item['title2'] = "";
                        $item['title3'] = "";
                        $item['title4'] = "";
                        $item['title5'] = "";
                        $item['descs1'] = "";
                        $item['descs2'] = "";
                        $item['descs3'] = "";
                        $item['descs4'] = "";
                        $item['descs5'] = "";
                    }else{
                        $item['title1'] = $typedesc[0];
                        $item['icon1'] = $typedesc[1];
                        $item['descs1'] = $typedesc[2];
                        $item['title2'] = $typedesc[3];
                        $item['icon2'] = $typedesc[4];
                        $item['descs2'] = $typedesc[5];
                        $item['title3'] = $typedesc[6];
                        $item['icon3'] = $typedesc[7];
                        $item['descs3'] = $typedesc[8];
                        $item['title4'] = $typedesc[9];
                        $item['icon4'] = $typedesc[10];
                        $item['descs4'] = $typedesc[11];
                        $item['title5'] = $typedesc[12];
                        $item['icon5'] = $typedesc[13];
                        $item['descs5'] = $typedesc[14];
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

        $entitle = input('entitle');

        if($entitle){

            $data['entitle'] = $entitle;

        }

       
        //简介

        $desc = input('descs');

        if($desc){

            $data['descs'] = $desc;

        }



        $typedesc = [];
        $typedesc['title1'] = input('title1');
        $typedesc['icon1'] = input('icon1');
        $typedesc['descs1'] = input('descs1');
        $typedesc['title2'] = input('title2');
        $typedesc['icon2'] = input('icon2');
        $typedesc['descs2'] = input('descs2');
        $typedesc['title3'] = input('title3');
        $typedesc['icon3'] = input('icon3');
        $typedesc['descs3'] = input('descs3');
        $typedesc['title4'] = input('title4');
        $typedesc['icon4'] = input('icon4');
        $typedesc['descs4'] = input('descs4');
        $typedesc['title5'] = input('title5');
        $typedesc['icon5'] = input('icon5');
        $typedesc['descs5'] = input('descs5');
        $data['typedesc'] = serialize($typedesc);

        $imgsrcs = input("imgsrcs/a");
        if($imgsrcs){
            $data['slides'] = serialize($imgsrcs);
        }else{
            $data['slides'] = "";
        }
        $newsid = input("newsid");
        if($newsid){
            $res = Db::name('wd_xcx_com_solution')->where("id",$newsid)->update($data);

        }else{
            $data['createtime'] = time();
            $res = Db::name('wd_xcx_com_solution')->insert($data);
        }

        if($res){
           $this->success('功能展示更新成功！', Url('Solution/index'));

        }else{

          $this->error('功能展示更新失败，没有修改项！');

          exit;

        }

    }
    public function del(){
       $newsid = input("newsid");
        if($newsid){
           $res = Db::name('wd_xcx_com_solution')->where("id",$newsid)->delete();
           if($res){
               $this->success('删除成功');
            }else{
              $this->error('删除失败');
              exit;
            }
        } 
    }
}