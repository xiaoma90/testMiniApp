<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;


class Modals extends Base
{
    public function index(){

        if(check_login()){


            if(powerget()){

                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);

                //include_once 'Ordinary.php';
               // $or = new \Ordinary();
                //$plat = $or->checkPlugin();
                //$this ->assign('plat', $plat);

            }else{
                $usergroup = Session::get('usergroup');
                if($usergroup==1){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/applet');
                }
                if($usergroup==2){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/index');
                }
                if($usergroup==3){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/index');
                }
                
            }
            return $this->fetch('index');
        }else{
            $this->redirect('Login/index');
        }
        
    }



    public function add(){

        if(check_login()){


            if(powerget()){

                $id = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);

                $cate=array();
                $cate = Db::name('wd_xcx_cate')->where("uniacid",$id)->select();
                $this->assign('cate',$cate);

                $cateurlid = 0;
                $cateinfo=array();
                $cateid = input("cateid");
                $allimg = array();
                if($cateid){
                    //有栏目号时，先判断该栏目是不是属于该小程序！
                    $lanmu = Db::name('wd_xcx_cate')->where("id",$cateid)->find();
                    if($lanmu['catepic']){
                        $lanmu['catepic'] = remote($id,$lanmu['catepic'],1);
                    }
                    if($lanmu['onlyid']){
                        $allimg = Db::name('wd_xcx_products_url')->where("randid",$lanmu['onlyid'])->select();
                        foreach($allimg as $k => &$v){
                            $allimg[$k]['url'] = remote($id,$v['url'],1);
                        }
                    }else{
                        $allimg=[];
                    }
                    if($lanmu['uniacid']==$id){
                        $cateinfo = $lanmu;
                        $cateinfo['cateconf'] = unserialize($cateinfo['cateconf']);
                        if($lanmu['cid']==0){
                            $cateurlid = 1;
                        }
                    }else{
                        $usergroup = Session::get('usergroup');
                        if($usergroup==1){
                            $this->error("找不到该栏目，或者该栏目不属于本小程序");
                        }
                        if($usergroup==2){
                            $this->error("找不到该栏目，或者该栏目不属于本小程序");
                        }
                    }
                    
                    
                }else{
                    $cateid=0;
                }
                $this->assign('allimg',$allimg);
                $this->assign('cateid',$cateid);
                $this->assign('cateinfo',$cateinfo);
                $this->assign('cateurlid',$cateurlid);
                



            }else{
                $usergroup = Session::get('usergroup');
                if($usergroup==1){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/applet');
                }
                if($usergroup==2){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/index');
                }
                if($usergroup==3){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/index');
                }
                
            }



            return $this->fetch('add');
        }else{
            $this->redirect('Login/index');
        }
        
    }

    public function save(){
        $data = array();
        //小程序ID
        $data['uniacid'] = input("appletid");
        //排序
        $num = input("num");
        if($num){
            $data['num'] = $num;
        }

        $onlyid = input("onlyid");
        $imgsrcs = input("imgsrcs/a");
        if($imgsrcs){
            $imgarr = array();
            foreach ($imgsrcs as $k => $v) {
                $imgarr['randid'] = $onlyid;
                $imgarr['appletid'] = $data['uniacid'];
                $imgarr['url'] = remote($data['uniacid'],$v,2);
                $imgarr['dateline'] = time();
                $is = Db::name('wd_xcx_products_url')->insert($imgarr);
            }
        }else{
            $is = 1;
        }
        $data['onlyid'] = $onlyid;
        
        //启用
        $statue = input("statue");
        if($statue === false){
            $data['statue'] = 1;
        }else{
            $data['statue'] = (int)$statue;
        }

        //启用
        $slide_is = input("slide_is");
        if($slide_is){
            $data['slide_is'] = (int)$slide_is;
        }else{
            $data['slide_is'] = 2;
        }

        //所属栏目
        $cid = input("cid");
        if($cid){
            $data['cid'] = $cid;
        }else{
            $data['cid'] = 0;
        }
        //栏目名称
        $name = input("name");
        if($name){
            $data['name'] = $name;
        }else{
            $this->error("请填写栏目名称！");
        }

        //英文栏目名
        $ename = input("ename");
        if($ename){
            $data['ename'] = $ename;
        }
        $remote = Db::name("wd_xcx_base")->where("uniacid",$data['uniacid'])->field("remote")->find()['remote'];
        //栏目缩略图
        $catepic = input("commonuploadpic");
        if($catepic){
            $data['catepic'] = remote($data['uniacid'],$catepic,2);
        }
        //简介
        $cdesc = input("cdesc");
        if($cdesc){
            $data['cdesc'] = $cdesc;
        }
        //每页数量
        $pagenum = input("pagenum");
        if($pagenum){
            $data['pagenum'] = $pagenum;
        }
        //首页显示
        $show_i = input("show_i");
        if($show_i ){
            $data['show_i'] = $show_i;
        }else{
            $data['show_i'] = 0;
        }
        //首页标题样式
        $list_tstyle = input("list_tstyle");
        if($list_tstyle){
            $data['list_tstyle'] = $list_tstyle;
        }else{
            $data['list_tstyle'] = 0;
        }
        //列表标题样式
        $list_tstylel = input("list_tstylel");
        if($list_tstylel){
            $data['list_tstylel'] = $list_tstylel;
        }else{
            $data['list_tstylel'] = 0;
        }
        $list_style_more = input("list_style_more");
        if($list_style_more){
            $data['list_style_more'] = input('list_style_more');
        }else{
            $data['list_style_more'] = 1;
        }
        //列表类型
        $list_type = input("list_type");

        if($list_type){
            if($cid == 0){
                $data['list_type'] = $list_type;
            }else{
                $data['list_type'] = 1;
            }
            
        }else{
            if($cid == 0){
                $data['list_type'] = 0;
            }else{
                $data['list_type'] = 1;
            }
        }
        //内容列表样式
        $list_style = input("list_style");
        if($list_style){
            $data['list_style'] = $list_style;
        }
        //列表标题样式
        $list_stylet = input("list_stylet");
        if($list_stylet){
            $data['list_stylet'] = $list_stylet;
        }

        //文章页面样式
        $pic_page_btn = input("pic_page_btn");
        if($pic_page_btn){
            $data['pic_page_btn'] = $pic_page_btn;
        }else{
            $data['pic_page_btn'] = 0;
        }
        

        $pic_page_btn_zt = input("pic_page_btn_zt");
        if($pic_page_btn_zt){
            $data['pic_page_btn_zt'] = $pic_page_btn_zt;
        }else{
            $data['pic_page_btn_zt'] = 0;
        }

        //栏目类型
        $type = input("type");
        if($type){
            $data['type'] = $type;
        }
        if($type == 'page'){
            $data['list_style'] = 3;
        } 

        $pic_page_bg = input("pic_page_bg");


        if($pic_page_bg!==false && $pic_page_bg !==null){
            $data['pic_page_bg'] = $pic_page_bg;
        }else{
            $data['pic_page_bg'] = 0;
        }

        //栏目内容
        $content = input("content");
        if($content){
            $data['content'] = $content;
        }

        $cateConf = array(
            'pmarb' => input("pmarb"),
            'ptit' => input("ptit"),
        );

        $data['cateconf'] = serialize($cateConf);

        $id = input("cateid");

        if($id!=0){
            $res = Db::name('wd_xcx_cate')->where("id",$id)->update($data);
            $list = Db::name("wd_xcx_products")->where("cid",$id)->select();
            foreach($list as $k => $v){
                if($data['cid'] == 0){
                    Db::name("wd_xcx_products")->where("cid",$id)->update(array("pcid"=>$id));
                }else{
                    Db::name("wd_xcx_products")->where("cid",$id)->update(array("pcid"=>$data['cid']));
                }
            }

        }else{
            $res = Db::name('wd_xcx_cate')->insert($data);
        }



        if($res || $is){
          $this->success('栏目信息更新成功！');
        }else{
          $this->error('基础信息更新失败，没有修改项！');
          exit;
        }



    }

    // 删除操作
    public function del(){
        $data['id'] = input("cateid");
        $res = Db::name('wd_xcx_cate')->where($data)->delete();
        if($res){
            $this->success('删除成功');
        }else{
            $this->success('删除失败');
        }
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

    //多图片上传

    public function imgupload(){

        $data['randid'] = $_GET['randid'];
        $files = request()->file('');    

        foreach($files as $file){        

            // 移动到框架应用根目录/public/upimages/ 目录下        

            $info = $file->validate(['ext'=>'jpg,png,gif,jpeg'])->move(ROOT_PATH . 'public' . DS . 'upimages');

           if($info){

                $data['url'] =  ROOT_HOST."/upimages/".date("Ymd",time())."/".$info->getFilename();

                $data['dateline'] = time();

                $res = Db::name('wd_xcx_products_url')->insert($data);

            }else{

                // 上传失败获取错误信息

                return $this->error($file->getError()) ;

            }    

        }

    }

    //上传成功后获取图片

    public function getimg(){

        $id = $_POST['id'];     

        $allimg = Db::name('wd_xcx_products_url')->where("randid",$id)->select();

        if($allimg){

            return $allimg;

        }
    }

    // //多图片上传
    // public function imgupload_duo(){

    //     $data['appletid'] = input("appletid");
    //     $files = request()->file('');  
    //     foreach($files as $file){        
    //         // 移动到框架应用根目录/public/upimages/ 目录下        
    //         $info = $file->move(ROOT_PATH . 'public' . DS . 'upimages');
    //         if($info){
    //             $url =  ROOT_HOST."/upimages/".date("Ymd",time())."/".$info->getFilename();
    //             $arr = array("url"=>$url);
    //             return json_encode($arr);
    //         }else{
    //             // 上传失败获取错误信息
    //             return $this->error($file->getError()) ;
    //         }    
    //     }
    // }
}