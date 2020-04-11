<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;


class Signlist extends Base
{


    public function index(){

        if(check_login()){
        	if(powerget()){

        		$id = input("appletid");
        		$res = Db::name('wd_xcx_applet')->where("id",$id)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
        		$this->assign('applet',$res);

                $list = Db::name('wd_xcx_sign')->alias('a')->join('wd_xcx_user b','a.openid = b.openid')->where("b.uniacid",$id)->field('b.nickname,b.avatar,a.*')->paginate(10);
                $counts = count($list);
                $sign = $list->all();
                // var_dump($sign);exit;
                foreach ($sign as $key => &$value) {
                    $sign[$key]['creattime'] = date('Y-m-d H:i:s',$value['creattime']);
                    $value['nickname'] = rawurldecode($value['nickname']);
                }

                $this->assign('counts',$counts);
                $this->assign('page',$list->render());
        		$this->assign('sign',$sign);

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



    public function save(){
        $appletid = input("appletid");
        $info = Db::name('wd_xcx_sign_con')->where("uniacid",$appletid)->find();

        $score = input("score");
        $max_score = input("max_score");

        if(!$score){
            $this->error('随机积分区间不能为空！');
            exit;
        }
        if(!$max_score){
            $this->error('最大积分不能为空！');
            exit;
        }
        $data = array(
            "score" => $score,
            "max_score" => $max_score,
            "uniacid" => $appletid
        );
        if(!$info){
           $res = Db::name('wd_xcx_sign_con')->insert($data);
        }else{
            $res = Db::name('wd_xcx_sign_con')->where("uniacid",$appletid)->update($data);
        }

        if($res){
          $this->success('积分签到基本配置更新成功！');
        }else{
          $this->error('积分签到基本配置更新失败，没有修改项！');
          exit;
        }

    }


	





    //单个图片上传操作
    function onepic_uploade($file){
    	$thumb = request()->file($file);
        if(isset($thumb)){
            $dir = upload_img();
            $info = $thumb->validate(['ext'=>'jpg,png,gif,jpeg'])->move($dir); 
            if($info){  
                $imgurl = ROOT_HOST."/upimages/".date("Ymd",time())."/".$info->getFilename();
                return $imgurl;
            }  
        }
    }

    //上传成功后获取图片
    public function getimg(){
    	$id = $_POST['id'];  	
    	$allimg = Db::name('wd_xcx_image_url')->where("appletid",$id)->select();
    	if($allimg){
    		return $allimg;
    	}
		
    }

    public function del(){
        $id = input("id");
        $res = Db::name('wd_xcx_image_url')->where('id', $id)->delete();
        if($res){
            $this->success('删除成功');
        }else{
            $this->error("删除失败！");
        }
    }

}