<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;
header("Content-type: text/html; charset=utf-8");
class Stylediy extends Base
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
                if($bases['c_b_bg']){
                   $bases['c_b_bg'] = remote($id,$bases['c_b_bg'],1);
                }
                if($bases['config'] == null){
                    $bases['config'] = "";
                }else{
                    $bases['config'] = unserialize($bases['config']);
                    if(!isset($bases['config']['commA'])){
                        $bases['config']['commA'] = 0; 
                    }
                    if(!isset($bases['config']['commAs'])){
                        $bases['config']['commAs'] = 0; 
                    }
                    if(!isset($bases['config']['commP'])){
                        $bases['config']['commP'] = 0; 
                    }    
                    if(!isset($bases['config']['commPs'])){
                        $bases['config']['commPs'] = 0; 
                    }
                    if(!isset($bases['config']['serverBtn'])){
                        $bases['config']['serverBtn'] = 1; 
                    }
                }
                $this->assign('bases',$bases);
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
    public function remote(){
        if(check_login()){
            if(powerget()){
                $id = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                
                $base = Db::name('wd_xcx_base')->where("uniacid",$id)->field("remote, use_remote")->find();
                if(!$base){
                    $base['remote'] = 1;
                    $base['use_remote'] = 1;
                }
                $remote2 = Db::name('wd_xcx_remote')->where("uniacid",$id)->where("type",2)->find();
                $remote3 = Db::name('wd_xcx_remote')->where("uniacid",$id)->where("type",3)->find();
                $this->assign('base',$base);
                $this->assign('remote2',$remote2);
                $this->assign('remote3',$remote3);
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
            return $this->fetch('remote');
        }else{
            $this->redirect('Login/index');
        }
    }
    public function remotesave(){
        $appletid = input("appletid");
        $remote = input("remote");
        $use_remote = input("use_remote");
        if(!$use_remote){
            $use_remote = 1;
        }
        $global_res = Db::name('wd_xcx_base')->where("uniacid",$appletid)->update(array("use_remote"=>$use_remote));
        $res = 1;
        if($remote == 1){
            $base_remote = Db::name('wd_xcx_base')->where("uniacid",$appletid)->update(array("remote"=>$remote));
            $res = 1;
        }else if($remote == 2 && $use_remote == 2){
            $base_remote = Db::name('wd_xcx_base')->where("uniacid",$appletid)->update(array("remote"=>$remote));
            $data = array();
            
            if(input("bucket2")){
                $data['bucket'] = input("bucket2");
            }else{
                $this->error("存储空间名称(Bucket)不能为空");
            }
            if(input("domain2")){
                $data['domain'] = input("domain2");
            }else{
                $this->error("绑定域名（或测试域名）不能为空");
            }
            if(input("ak2")){
                $data['ak'] = input("ak2");
            }else{
                $this->error("AccessKey（AK）不能为空");
            }
            if(input("sk2")){
                $data['sk'] = input("sk2");
            }else{
                $this->error("SecretKey（SK）不能为空");
            }
            // $data['imgstyle'] = input("imgstyle2");
            $data['type'] = $remote;
            $is = Db::name("wd_xcx_remote")->where("uniacid",$appletid)->where("type",2)->find();
            if($is){
                $res = Db::name("wd_xcx_remote")->where("uniacid",$appletid)->where("type",2)->update($data);
            }else{
                $data['uniacid'] = $appletid;
                $res = Db::name("wd_xcx_remote")->insert($data);
            }
        }else if($remote == 3 && $use_remote == 2){
            $base_remote = Db::name('wd_xcx_base')->where("uniacid",$appletid)->update(array("remote"=>$remote));
            $data = array();
            
            if(input("bucket3")){
                $data['bucket'] = input("bucket3");
            }else{
                $this->error("存储空间名称(Bucket)不能为空");
            }
            if(input("domain3")){
                $data['domain'] = input("domain3");
            }else{
                $this->error("Endpoint（或自定义域名）不能为空");
            }
            $data['domain_bind'] = input("domain_bind");
      
            if(input("ak3")){
                $data['ak'] = input("ak3");
            }else{
                $this->error("Access Key ID不能为空");
            }
            if(input("sk3")){
                $data['sk'] = input("sk3");
            }else{
                $this->error("Access Key Secret不能为空");
            }
            $data['imgstyle'] = input("imgstyle3");
            $data['domainIs'] = input("domainIs");
            $data['type'] = $remote;
            $is = Db::name("wd_xcx_remote")->where("uniacid",$appletid)->where("type",3)->find();
            if($is){
                $res = Db::name("wd_xcx_remote")->where("uniacid",$appletid)->where("type",3)->update($data);
            }else{
                $data['uniacid'] = $appletid;
                $res = Db::name("wd_xcx_remote")->insert($data);
            }
        }
        if($res || $base_remote || $global_res){
          $this->success('远程附件设置成功');
        }else{
          $this->error('远程附件设置失败，没有修改项！');
        }
        //小程序ID
        
        //小程序头底主色
        $base_color = input("base_color");
        if($base_color){
            $data['base_color'] = "#".$base_color;
        }
        //小程序头底文字色
        $base_tcolor = input("base_tcolor");
        if($base_tcolor){
            $data['base_tcolor'] = $base_tcolor;
        }
        // $homepage = input("homepage");
        $homepage = 2;
        // if($homepage){
        //     $data['homepage'] = $homepage;
        // }
        //小程序突出颜色
        $base_color2 = input("base_color2");
        if($base_color2){
            $data['base_color2'] = "#".$base_color2;
        }
        //默认样式标题颜色
        $base_color_t = input("base_color_t");
        if($base_color_t){
            $data['base_color_t'] = "#".$base_color_t;
        }
      
        $bases = Db::name('wd_xcx_base')->where("uniacid",$appletid)->count();
        if($bases>0){
            $res = Db::name('wd_xcx_base')->where("uniacid",$appletid)->update($data);
        }else{
            $data['uniacid'] = $appletid;
            $res = Db::name('wd_xcx_base')->insert($data);
        }
        if($res){
          $this->success('基础信息更新成功！');
        }else{
          $this->error('基础信息更新失败，没有修改项！');
        }
    }
    public function diyset(){
        if(check_login()){
            if(powerget()){
                $id = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $bases = Db::name('wd_xcx_base')->where("uniacid",$id)->find();
                if(!$bases){
                    $a = request()->domain();
                    $this ->error('请设置基本信息!', Url('Index/index').'?appletid='.$id);
                }else{
                    if($bases['config'] == null){
                        $bases['config'] = "";
                    }else{
                        $bases['config'] = unserialize($bases['config']);
                        if(!isset($bases['config']['commA'])){
                            $bases['config']['commA'] = 0; 
                        }
                        if(!isset($bases['config']['commAs'])){
                            $bases['config']['commAs'] = 0; 
                        }
                        if(!isset($bases['config']['commP'])){
                            $bases['config']['commP'] = 0; 
                        }    
                        if(!isset($bases['config']['commPs'])){
                            $bases['config']['commPs'] = 0; 
                        }
                        if(!isset($bases['config']['serverBtn'])){
                            $bases['config']['serverBtn'] = 1; 
                        }
                    }    
                }
                
                $this->assign('bases',$bases);
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
            return $this->fetch('diyset');
        }else{
            $this->redirect('Login/index');
        }
    }
    public function diysetsave(){
        $appletid = input("appletid");
        
        $data = array();
        //小程序ID
        $data['uniacid'] = input("appletid");
        //小程序头底主色
        $base_color = input("base_color");
        if($base_color){
            $data['base_color'] =$base_color;
        }
        //小程序头底文字色
        $base_tcolor = input("base_tcolor");
        if($base_tcolor){
            $data['base_tcolor'] = $base_tcolor;
        }else{
            $data['base_tcolor'] = '#000000';
        }
        $homepage = input("homepage");
        if($homepage){
            $data['homepage'] = $homepage;
        }
        //小程序突出颜色
        $base_color2 = input("base_color2");
        if($base_color2){
            $data['base_color2'] = $base_color2;
        }
        //默认样式标题颜色
        $base_color_t = input("base_color_t");
        if($base_color_t){
            $data['base_color_t'] = $base_color_t;
        }
         $tabbar_bg1=input('tabbar_bg1');
         if($tabbar_bg1){
             $data['tabbar_bg1']=$tabbar_bg1;
         }

        $tabbar_bg2=input('tabbar_bg2');
        if($tabbar_bg2){
            $data['tabbar_bg2']=$tabbar_bg2;
        }
        $tabbar_bg3=input('tabbar_bg3');
        if($tabbar_bg3){
            $data['tabbar_bg3']=$tabbar_bg3;
        }
        $data["copyimg"]="";
        $bases = Db::name('wd_xcx_base')->where("uniacid",$appletid)->count();
        if($bases>0){
            $res = Db::name('wd_xcx_base')->where("uniacid",$appletid)->update($data);
        }else{
            $data['uniacid'] = $appletid;
            $res = Db::name('wd_xcx_base')->insert($data);
        }
        if($res){
            getShareBackGroubd($appletid);
          $this->success('基础信息更新成功！');
        }else{
          $this->error('基础信息更新失败，没有修改项！');
        }
    }
    public function save(){
    	$appletid = input("appletid");
        
        $data = array();
        //小程序ID
        $data['uniacid'] = input("appletid");
      
        //首页顶部样式
        $index_style = input("index_style");
        if($index_style){
            $data['index_style'] = $index_style;
        }
        //首页电话、时间区块
        $tel_box = input("tel_box");
        if($tel_box){
            $data['tel_box'] = $tel_box;
        }else{
            $data['tel_box'] = 0;
        }
        //介绍板块栏目名
        $aboutCN = input("aboutCN");
        if($aboutCN){
            $data['aboutCN'] = $aboutCN;
        }else{
            $data['aboutCN'] = "";
        }
        //英文名称
        $aboutCNen = input("aboutCNen");
        if($aboutCNen){
            $data['aboutCNen'] = $aboutCNen;
        }else{
            $data['aboutCNen'] = "";
        }
        //介绍板块标题样式
        $index_about_title = input("index_about_title");
        if($index_about_title || $index_about_title==0){
            $data['index_about_title'] = $index_about_title;
        }else{
            $data['index_about_title'] = 9;
        }
        //横排推荐区中文名
        $catename_x = input("catename_x");
        if($catename_x){
            $data['catename_x'] = $catename_x;
        }else{
            $data['catename_x'] = "";
        }
        //英文名称
        $catenameen_x = input("catenameen_x");
        if($catenameen_x){
            $data['catenameen_x'] = $catenameen_x;
        }else{
            $data['catenameen_x'] = "";
        }
        //横排推荐区标题样式
        $i_b_x_ts = input("i_b_x_ts");
        if($i_b_x_ts || $i_b_x_ts==0){
            $data['i_b_x_ts'] = $i_b_x_ts;
        }else{
            $data['i_b_x_ts'] = 9;
        }
        //横排推荐区图片宽度
        $i_b_x_iw = input("i_b_x_iw");
        if($i_b_x_iw){
            $data['i_b_x_iw'] = $i_b_x_iw;
        }
        //英文名称
        $catename = input("catename");
        if($catename){
            $data['catename'] = $catename;
        }else{
            $data['catename'] = "";
        }
        $catenameen = input("catenameen");
        if($catenameen){
            $data['catenameen'] = $catenameen;
        }else{
            $data['catenameen'] = "";
        }
        //竖排推荐区标题
        $i_b_y_ts = input("i_b_y_ts");
        if($i_b_y_ts || $i_b_y_ts==0){
            $data['i_b_y_ts'] = $i_b_y_ts;
        }else{
            $data['i_b_y_ts'] = 9;
        }
        //竖排推荐区列表样式
        $index_pro_lstyle = input("index_pro_lstyle");
        if($index_pro_lstyle){
            $data['index_pro_lstyle'] = $index_pro_lstyle;
        }else{
            $data['index_pro_lstyle'] = 1;
        }
        //竖排推荐区列表标题
        $index_pro_ts_al = input("index_pro_ts_al");
        if($index_pro_ts_al){
            $data['index_pro_ts_al'] = $index_pro_ts_al;
        }else{
            $data['index_pro_ts_al'] = "";
        }
        //商品竖排推荐
        $spcatename = input("spcatename");
        if($spcatename){
            $data['spcatename'] = $spcatename;
        }else{
            $data['spcatename'] = "";
        }
        $spcatenameen = input("spcatenameen");
        if($spcatenameen){
            $data['spcatenameen'] = $spcatenameen;
        }else{
            $data['spcatenameen'] = "";
        }
        $sptj_max_sp = input("sptj_max_sp");
        if($sptj_max_sp){
            $data['sptj_max_sp'] = $sptj_max_sp;
        }else{
            $data['sptj_max_sp'] = 10;
        }
        $sptj_max = input("sptj_max");
        if($sptj_max){
            $data['sptj_max'] = $sptj_max;
        }else{
            $data['sptj_max'] = 10;
        }
        $sp_i_b_y_ts = input("sp_i_b_y_ts");
        if($sp_i_b_y_ts){
            $data['sp_i_b_y_ts'] = $sp_i_b_y_ts;
        }else{
            $data['sp_i_b_y_ts'] = 0;
        }
        //缩略图
        $c_b_bg = input("commonuploadpic");
        if($c_b_bg){
            $data['c_b_bg'] = remote($appletid,$c_b_bg,2);
        }
        //服务中心按钮样式
        $c_b_btn = input("c_b_btn");
        if($c_b_btn){
            $data['c_b_btn'] = $c_b_btn;
        }else{
            $data['c_b_btn'] = 0;
        }
        //首页多商户是否显示
        $duomerchants = input("duomerchants");
        if($duomerchants){
            $data['duomerchants'] = $duomerchants;
        }
        //文章页标题样式
        $c_title = input("c_title");
        if($c_title){
            $data['c_title'] = $c_title;
        }else{
            $data['c_title'] = 0;
        }
        //底部导航
        $form_index = input("form_index");
        if($form_index){
            $data['form_index'] = $form_index;
        }else{
            $data['form_index'] = 0;
        }
        //底部导航
        $footer_style = input("footer_style");
        if($footer_style){
            $data['footer_style'] = $footer_style;
        }else{
            $data['footer_style'] = "none";
        }
        $commA = input('commA');
        $commAs = input('commAs');
        $commP = input('commP');
        $commPs = input('commPs');
        $serverBtn = input('serverBtn');
        //评论
        if(is_null($commA)){
            $commA = 0;
        }
        if(is_null($commAs)){
            $commAs = 0;
        }
        if(is_null($commP)){
            $commP = 0;
        }
        if(is_null($commPs)){
            $commPs = 0;
        }
        if(is_null($serverBtn)){
            $serverBtn = 1;
        }
        $config = array(
            'newhead' => input("newhead"),
            'search' => input("search"),
            'bigadT' => input("bigadT"),
            'bigadC' => input("bigadC"),
            'bigadCTC' => input("bigadCTC"),
            'bigadCNN' => input("bigadCNN"),
            'miniadT' => input("miniadT"),
            'miniadC' => input("miniadC"),
            'miniadN' => input("miniadN"),
            'miniadB' => input("miniadB"),
            'copT' => input("copT"),
            'userFood' => input("userFood"),
            'commA' => $commA,
            'commAs' => $commAs,
            'commP' => $commP,
            'commPs' => $commPs,
            'serverBtn' => $serverBtn
        );
        $data['config'] = serialize($config);
        $data['ios'] = input('ios');
        $bases = Db::name('wd_xcx_base')->where("uniacid",$appletid)->count();
        if($bases>0){
            $res = Db::name('wd_xcx_base')->where("uniacid",$appletid)->update($data);
        }else{
            $data['uniacid'] = $appletid;
            $res = Db::name('wd_xcx_base')->insert($data);
        }
        if($res){
          $this->success('基础信息更新成功！');
        }else{
          $this->error('基础信息更新失败，没有修改项！');
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
}