<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;


class Sharejf extends Base
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

                $bases = Db::name('wd_xcx_base')->where("uniacid",$id)->find();
        		$this->assign('base',$bases);

                $rechargeconf = Db::name('wd_xcx_rechargeconf') ->where('uniacid', $id) ->field('score, money, score_shoppay') ->find();

                $this->assign('rechargeconf', $rechargeconf);
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
        $sharejf = input("sharejf");
        $sharexz = input("sharexz");
        // $sharetype = input("sharetype");
        $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
        if(!$res){
            $this->error("找不到对应的小程序！");
        }

        if(!$sharejf){
            $this->error('分享积分不能为空！');
            exit;
        }
        if(!$sharexz){
            $this->error('分享次数限制不能为空！');
            exit;
        }


        $score = input("score");
        if($score){
            if($score<0){
                $redata['score'] = 0;
            }else{
                $redata['score'] = $score;
            }  
        }else{
            $redata['score'] = 0;
        }
       
        $money = input("money");
        if($money){
            if($money<0){
                $redata['money'] = 0;
            }else{
                $redata['money'] = $money;
            }   
        }else{
            $redata['money'] = 0;
        }

        $score_shoppay = input("score_shoppay");
        if($score_shoppay){
            if($money<0){
                $redata['score_shoppay'] = 0;
            }else{
                $redata['score_shoppay'] = $score_shoppay;
            }   
        }else{
            $redata['score_shoppay'] = 0;
        }

        $jifen = Db::name('wd_xcx_rechargeconf')->where("uniacid",$appletid)->find(); 

        if($jifen){
            $re = Db::name('wd_xcx_rechargeconf')->where("uniacid",$appletid)->update($redata); 
        }else{
            $redata['uniacid'] = $appletid;
            $re = Db::name('wd_xcx_rechargeconf')->insert($redata);
        }

        $data['sharejf'] = $sharejf;
        $data['sharexz'] = intval($sharexz);
        // $data['sharetype'] = intval($sharetype);
        $res = Db::name('wd_xcx_base')->where("uniacid",$appletid)->find();
        if(!$res){
            $res = Db::name('wd_xcx_base')->insert($data);
        }else{
            $res = Db::name('wd_xcx_base')->where("uniacid",$appletid)->update($data);
        }

        if($res || $re){
          $this->success('积分设置更新成功！');
        }else{
          $this->error('积分设置更新失败，没有修改项！');
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
            return 1;
        }else{
            $this->error("删除失败！");
        }
    }

}