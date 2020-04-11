<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;



// vendor('aliyun.autoload');
// use OSS\OssClient;
// use OSS\Core\OssException;



class remote extends Controller
{
    public function index(){
        // if(check_login()){
                $appletid = input("appletid");
                $from = input('from');
                if(!$from){
                    $from = 0;
                }
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);

                $gid = input("gid");

                $type = input("type");
                $group = Db::name('wd_xcx_picgroup')->where("uniacid",$appletid)->order('id desc')->select();
                if($group){
                    foreach ($group as $k => $v) {
                        $group[$k]['count'] = Db::name("wd_xcx_pic")->where("gid",$v['id'])->count();
                    }
                }
                if($gid){
                    $all = Db::name('wd_xcx_pic')->where("uniacid",$appletid)->where("gid",$gid)->order('id desc')->paginate(12,false,[ 'query' => array('appletid'=>input("appletid"),'type'=>input("type"), 'gid'=>$gid)]);
                }else{
                    $all = Db::name('wd_xcx_pic')->where("uniacid",$appletid)->order('id desc')->paginate(12,false,[ 'query' => array('appletid'=>input("appletid"),'type'=>input("type"))]);
                    $gid = 0;
                }
                $list = $all->toArray();
                $remote_set = Db::name("wd_xcx_base")->where("uniacid",$appletid)->value('remote');
                $remote = Db::name('wd_xcx_remote') ->where("uniacid",$appletid)->where("type",$remote_set)->find();



                foreach($list['data'] as $k =>$v){
                    $list['data'][$k]['imgurl'] = remote($appletid, $v['imgurl'], 1);
                }
                $count = Db::name('wd_xcx_pic')->where("uniacid",$appletid)->count();
                $this->assign('type',$type);
                $this->assign('group',$group);
                $this->assign('gid',$gid);
                $this->assign('all',$all);
                $this->assign('list',$list['data']);
                $this->assign('uniacid',$appletid);
                $this->assign('count',$count);
                $this->assign('from', $from);

            return $this->fetch('index');
        // }else{
        //     $this->redirect('Login/index');
        // }
        
    }
    
    public function imgupload(){
        $uniacid = input("uniacid");
        $groupid = input("groupid");
        
        $url = getRemoteType($uniacid, $groupid, 1);
        return $url;
    }


    public function makegroup(){
        $uniacid = input("uniacid");
        $name = input("name");
        $is = Db::name("wd_xcx_picgroup")->where("uniacid",$uniacid)->where("name",$name)->find();
        if($is){
            echo json_encode(array("is"=>0));
        }else{
            $data = array();
            $data['uniacid'] = $uniacid;
            $data['name'] = $name;
            $id = Db::name("wd_xcx_picgroup")->insertGetId($data);
            if($id){
                echo json_encode(array("is"=>1,"id"=>$id));
            }else{
                echo json_encode(array("is"=>2));
            }
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

        $name = input("name");
        if($name){
            $data['name'] = $name;
        }

        //栏目图片
        $catepic = $this->onepic_uploade("catepic");
        if($catepic){
            $data['catepic'] = $catepic;
        }

        // var_dump($data);exit;
        
        
        $id = input("cateid");

        if($id!=0){
            $res = Db::name('wd_xcx_score_cate')->where("id",$id)->update($data);
        }else{
            $res = Db::name('wd_xcx_score_cate')->insert($data);
        }



        if($res){
          $this->success('栏目信息更新成功！');
        }else{
          $this->error('栏目信息更新失败，没有修改项！');
          exit;
        }



    }

    // 删除操作
    public function del(){
        $data['id'] = input("cateid");
        $res = Db::name('wd_xcx_score_cate')->where($data)->delete();
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

    //删除图片
    public function delpic(){
        $ids = input('ids');
        if($ids){
            $ids = explode(',', $ids);
            $res = Db::name('wd_xcx_pic') ->where('id', 'IN', $ids) ->delete();   //删除数据库
            if($res){
                return 1;
            }else{
                return 2;
            }
        }else{
            return 2;
        }
    }


    //修改相册名称
    public function changegname(){
        $gid = input('id');
        $gname = input('gname');
        $res =  Db::name('wd_xcx_picgroup')->where("id",$gid)->update(['name'=>$gname]);
        if($res){
            return 1;
        }else{
            return 2;
        }
    }
}