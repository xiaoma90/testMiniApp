<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;
class Cycate extends Base
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
                $listV_s = Db::name('wd_xcx_food_cate')->where("uniacid",$appletid)->order('num desc')->paginate(10,false,['query' => ['appletid' => $appletid]]);
                $listV = $listV_s->toArray()['data'];
                $this->assign('cates',$listV);
                $this->assign('cates_list',$listV_s);
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
                $cateid = input("cateid");
                if($cateid){
                    //有栏目号时，先判断该栏目是不是属于该小程序！
                    $lanmu = Db::name('wd_xcx_food_cate')->where("id",$cateid)->find();
                    if($lanmu['uniacid']==$id){
                        $cateinfo = $lanmu;
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
                    $cateinfo = "";
                }
                $this->assign('cateid',$cateid);
                $this->assign('cateinfo',$cateinfo);

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
        $title = input("title");
        if($title){
            $data['title'] = $title;
        }

        $id = input("cateid");
        if($id!=0){
            $res = Db::name('wd_xcx_food_cate')->where("id",$id)->update($data);
        }else{
            $res = Db::name('wd_xcx_food_cate')->insert($data);
        }
        if($res){
            $this->success('点菜分类管理信息更新成功！',Url('Cycate/index').'?appletid='.$data['uniacid']);
        }else{
            $this->error('点菜分类管理信息更新失败，没有修改项！');
            exit;
        }
    }
    // 删除操作
    public function del(){
        $data['id'] = input("cateid");
        $is = Db::name("wd_xcx_food")->where('cid', input("cateid"))->find();
        if($is){
            $this->success('该分类下还有商品，删除失败');
        }
        $res = Db::name('wd_xcx_food_cate')->where($data)->delete();
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
    public function imgupload_duo(){
        $data['appletid'] = input("appletid");
        $files = request()->file('');
        foreach($files as $file){
            // 移动到框架应用根目录/public/upimages/ 目录下        
            $info = $file->move(ROOT_PATH . 'public' . DS . 'upimages');
            if($info){
                $url =  ROOT_HOST."/upimages/".date("Ymd",time())."/".$info->getFilename();
                $arr = array("url"=>$url);
                return json_encode($arr);
            }else{
                // 上传失败获取错误信息
                return $this->error($file->getError()) ;
            }
        }
    }
}