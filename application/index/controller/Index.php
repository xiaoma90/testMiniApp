<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;
class Index extends Base
{
    public function index(){
   
        if(check_login()){
            /*首页内容正式开始*/
            if(powerget()){
                $id = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }

                if($res['name']){
                    $_SESSION['app_name'] = $res['name'];
                }else{
                    $_SESSION['app_name'] = '智慧多端系统管理';
                }

                 //根据所属套装组获取权限
                $node_id = array();
                if($res['combo_id'] == 0){
                    $combos = Db::name('wd_xcx_rule') -> field('id') ->select();
                    foreach ($combos as $item) {
                        $node_id[] = (string)$item['id'];
                    }
                }else{
                    $combo = Db::name('wd_xcx_combo') ->where('id', $res['combo_id']) ->find();
                     if($combo){
                        if($combo['node_id']){
                            $node_id = unserialize($combo['node_id']);
                        }else{
                            $this->error('请联系管理员为功能套餐设置权限!');
                        }
                    }else{
                        $this->error('请联系管理员设置功能套餐!');
                        exit;
                    }
                }
                $_SESSION['node_id'] = $node_id;
                $this->assign('applet',$res);
                if($res['thumb']){
                    $_SESSION['app_icon'] = $res['thumb'];
                }else{
                    $_SESSION['app_icon'] = STATIC_ROOT . '/image/logo2.png';
                }

                
                $group = Session::get('usergroup');
                $this->assign('group', $group);
                //短信设置
                $sms_set = Db::name("wd_xcx_sms")->where("uniacid", $id)->where("type", 1)->find();
                $this->assign('sms_set', $sms_set);
                $sms_set2 = Db::name("wd_xcx_sms")->where("uniacid", $id)->where("type", 2)->find();
                if($sms_set2){
                    $sms_set2['ali_access_id'] = $sms_set2['tx_access_id'];
                    $sms_set2['ali_access_secret'] = $sms_set2['tx_access_secret'];
                    $sms_set2['ali_code_tpl'] = $sms_set2['tx_code_tpl'];
                    $sms_set2['ali_sign'] = $sms_set2['tx_sign'];
                }
                $this->assign('sms_set2', $sms_set2);

                //产品销量bug修复
                $prochsnum = Db::name('wd_xcx_products')->whereOr("type","showPro")->whereOr("type","showProMore")->where("sale_num",NULL)->where("uniacid",$id)->select();
                foreach ($prochsnum as $key => $value) {
                    Db::name("wd_xcx_products")->where("id",$value['id'])->update(array("sale_num"=>0));
                }
                $bases = Db::name('wd_xcx_base')->where("uniacid",$id)->find();
                if($bases){
                    if($bases['logo2']){
                        $bases['logo2'] = remote($id,$bases['logo2'],1);
                    }
                    if($bases['banner']){
                        $bases['banner'] = remote($id,$bases['banner'],1);
                    }
                    if($bases['logo']){
                        $bases['logo'] = remote($id,$bases['logo'],1);
                    }
                    if($bases['v_img']){
                        $bases['v_img'] = remote($id,$bases['v_img'],1);
                    }

                    if($res['banner']){

                        $bases['pc_banner'] = unserialize($res['banner']);
                    }else{
                        $bases['pc_banner'] = '';
                    }
                }
                
                $slide = Db::name('wd_xcx_image_url')->where("appletid",$id)->select();
                $slides = array();
                
                foreach ($slide as $k => $v) {
                    $slides[$k]['id'] = $v['id'];
                    $slides[$k]['slide'] = remote($id,$v['url'],1);
                }

                if($bases){
                    if(!$bases['config']){
                        $config = '';
                    }else{
                        $config = unserialize($bases['config']);
                        if(!isset($config['commA'])){
                            $config['commA'] = 0; 
                        }
                        if(!isset($config['commAs'])){
                            $config['commAs'] = 0; 
                        }
                        if(!isset($config['commP'])){
                            $config['commP'] = 0; 
                        }    
                        if(!isset($config['commPs'])){
                            $config['commPs'] = 0; 
                        }
                        if(!isset($config['serverBtn'])){
                            $config['serverBtn'] = 1; 
                        }
                    }
                }else{
                    $config = '';
                }

                $this->assign('slide',$slides);
                $this->assign('bases',$bases);
                $this->assign('config',$config);
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
        $res = Db::name('wd_xcx_base')->where("uniacid",$appletid)->find();
        $group = Session::get('usergroup');
        $shortmsg = input("shortmsg");
        $where = [];
        if($shortmsg == 1){
            $sms = array(
                "tx_access_id" => trim(input("tx_access_id")),
                "tx_access_secret" => trim(input("tx_access_secret")),
                "tx_code_tpl" => trim(input("tx_code_tpl")),
                "tx_sign" => trim(input("tx_sign"))
            );
            $sms['type'] = 1;
            $where['type'] = 1;
        }else{
            $sms = array(
                "tx_access_id" => trim(input("ali_access_id")),
                "tx_access_secret" => trim(input("ali_access_secret")),
                "tx_code_tpl" => trim(input("ali_code_tpl")),
                "tx_sign" => trim(input("ali_sign"))
            );
            $sms['type'] = 2;
            $where['type'] = 2;
        }
        
        $sms_set = Db::name('wd_xcx_sms')->where("uniacid", $appletid)->where($where)->count();
        if($sms_set > 0){
            $sms_res = Db::name('wd_xcx_sms')->where("uniacid", $appletid)->where($where)->update($sms);
        }else{
            $sms['uniacid'] = $appletid;
            $sms_res = Db::name('wd_xcx_sms')->insert($sms);
        }
        


        $data = array();
       
        //门店LOGO
        $logo = input("commonuploadpic3");
        if($logo){
            $data['logo'] = remote($appletid,$logo,2);
        }
        $banner = input("commonuploadpic2");
        if($banner){
            $data['banner'] = remote($appletid, $banner, 2);
        }
        
        //平台名称
        $name = $_POST['name'];
        if($name){
            $app_is = Db::name('wd_xcx_applet') ->where('id', $appletid) ->update(['name'=> $name]);
        }

        //门店名称
        $data['name'] = input('store_name');

        //门店电话
        $data['tel'] = input('tel');

        //门店地址
        $data['address'] = input('address');

        //门店简介
        $about = input('about');
        if($about){
            $data['about'] = $about;
        }

        $data['alish'] = input('alish');
        $data['shortmsg_type'] = input('shortmsg_type');
        $data['desc'] = input('desc');

        $commA = input('commA');
        $commAs = input('commAs');
        $commP = input('commP');
        $commPs = input('commPs');
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
        
        $config = array(
            'commA' => $commA,
            'commAs' => $commAs,
            'commP' => $commP,
            'commPs' => $commPs,
        );
        $data['config'] = serialize($config);
        $data['ios'] = input('ios');
        $data['recharge'] = input('recharge');
        $share_open = input('share_open');
        if(!$share_open){
            $data['share_open'] = 1;
        }else{
            $data['share_open'] = input('share_open');
        }
        $data['shortmsg_applet'] = input("shortmsg_applet");
        $data['shortmsg'] = $shortmsg;
        


        // $data['share_open'] = isset($share_open) ? input('share_open') : 1;
        
        $bases = Db::name('wd_xcx_base')->where("uniacid",$appletid)->count();
        if($bases>0){
            $res = Db::name('wd_xcx_base')->where("uniacid",$appletid)->update($data);
        }else{
            $data['uniacid'] = $appletid;
            $res = Db::name('wd_xcx_base')->insert($data);
        }
        if($res || $app_is || $sms_res){
          $this->success('基础信息更新成功！');
        }else{
          $this->error('基础信息更新失败，没有修改项！');
          exit;
        }
    }
    
    public function allset(){
        if(check_login()){
            if(powerget()){
                $id = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                
                //获取用户组
                $group = Session::get('usergroup');
                $types = unserialize($res['type']);
                // $form_app = 1;
                // if($types == [0] || $types == [2] || $types == [0,2] || $types == [2,0]){
                //     $form_app = 2;
                // }
                
                // $this->assign('form_app', $form_app);

                $this->assign('types', $types);

//                include_once 'Ordinary.php';
                //$or = new \Ordinary();
                //$plat = $or ->checkPlat();
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
            return $this->fetch('allset');
        }else{
            $this->redirect('Login/index');
        }
    }
    public function allsetsave(){
        $appletid = input("appletid");
        
        $data = array();
        //小程序ID
        $data['uniacid'] = input("appletid");
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
    //多图片上传
    public function imgupload(){
        $data['appletid'] = $_GET['appletid'];
        $files = request()->file('');    
        foreach($files as $file){        
            // 移动到框架应用根目录/public/upimages/ 目录下        
            $info = $file->validate(['ext'=>'jpg,png,gif,jpeg'])->move(ROOT_PATH . 'public' . DS . 'upimages');
           if($info){
                $data['url'] =  ROOT_HOST."/upimages/".date("Ymd",time())."/".$info->getFilename();
                $data['dateline'] = time();
                $res = Db::name('wd_xcx_image_url')->insert($data);
            }else{
                // 上传失败获取错误信息
                return $this->error($file->getError()) ;
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


    //微信小程序设置
    public function wxset(){
        $id = input("appletid");
        $res = Db::name('wd_xcx_applet')->where("id",$id) ->field('thumb, name, xcxId, appID, appSecret, mchid, signkey, tominiprogram')->find();
        if(!$res){
            $this->error("找不到对应的小程序！");
        }

        if($res['tominiprogram']){
            $tominiprogram = unserialize($res['tominiprogram']);
        }else{
            $tominiprogram = '';
        }

        $item = Db::name('wd_xcx_fx_gz')->where("uniacid",$id) ->field('certtext, keytext')->find();

        $this->assign('keytext', $item);

        $this->assign('tominiprogram', $tominiprogram);

        $this->assign('applet',$res);
        return $this->fetch('wxset');
    }

    //微信小程序设置保存
    public function wxsave(){
        $appletid = input("appletid");
        // $res = Db::name('wd_xcx_base')->where("uniacid",$appletid)->find();
        $app = array(
            "xcxId" => trim(input("xcxId")),
            "appID" => trim(input("appID")),
            "appSecret" => trim(input("appSecret")),
            "mchid" => trim(input("mchid")),
            "signkey" => trim(input("signkey")),
        );

        $res = Db::name('wd_xcx_applet') ->where('id', $appletid) ->update($app);

        $cert = input('certtext');
        $key = input('keytext');
        
        if($cert && $key){
            $cert_path = ROOT_PATH."public/Cert/".$appletid."/apiclient_cert.pem";
            $key_path = ROOT_PATH."public/Cert/".$appletid."/apiclient_key.pem";
            $path = ROOT_PATH."public/Cert";

            if(!file_exists($path)){
                if (mkdir($path)) {
                    $upath = ROOT_PATH."public/Cert/".$appletid."/";
                    if(!file_exists($upath)){
                        mkdir($upath);
                    }
                }
            }else{
                $upath = ROOT_PATH."public/Cert/".$appletid."/";
                if(!file_exists($upath)){
                    mkdir($upath);
                }
            }

            file_put_contents($cert_path, $cert);
            file_put_contents($key_path, $key);
        }

        $item = Db::name('wd_xcx_fx_gz')->where("uniacid",$appletid)->find();
        if($item){
            $r = Db::name('wd_xcx_fx_gz')->where("uniacid",$appletid)->update(["certtext" => $cert, "keytext" => $key]);
        }else{
            $r = Db::name('wd_xcx_fx_gz')->insert(["uniacid" =>$appletid,"fxs_xy" =>'', "fxs_name"=> '', "catext"=>'', "thumb"=>'',"sq_thumb"=>'', "certtext" => $cert, "keytext" => $key]);
        }

        if($res !== false || $r !== fasle){
            $this->success('修改成功!');
        }else{
            $this->error('修改失败!');
        }
    }

    //支付宝小程序设置
    public function aliset(){
        $id = input("appletid");
        $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
        $ali_aes = Db::name('wd_xcx_base')->where('uniacid', $id)->value('ali_aes');
        $res['ali_aes'] = $ali_aes;
        if(!$res){
            $this->error("找不到对应的小程序！");
        }
        $this->assign('applet',$res);
        return $this->fetch('aliset');
    }

    //支付宝小程序设置保存
    public function alisave(){
        $appletid = input("appletid");
        $app = array(
            "ali_appID" => trim(input("ali_appID")),
            "ali_public_key" => trim(input('ali_public_key')),
            "ali_private_key" => trim(input('ali_private_key')),
        );

        $ali_aes = input("ali_aes");
        $res2 = 0;
        if(!empty($ali_aes)){
            $is = Db::name('wd_xcx_base')->where('uniacid', $appletid)->find();
            if($is){
                $res2 = Db::name('wd_xcx_base')->where('uniacid', $appletid)->update(['ali_aes' => $ali_aes]);
            }
        }

        $res = Db::name('wd_xcx_applet') ->where('id', $appletid) ->update($app);

        if($res || $res2){
            $this->success('修改成功!');
        }else{
            $this->error('修改失败!');
        }
    }

    //下载支付宝源码包
    public function downloadAli(){
        $uniacid = input("uniacid");
        $domain = 'www.baidu.com';
        $ip = '888.888.888.888';
        $platform = PLATFORM;

        //远程服务器更改配置, 打包, 返回文件名
        $url = "http://qdy.hdewm.com/aliprogram.php?uniacid=$uniacid&domain=$domain&ip=$ip&platform=$platform";
        $response = $this->_requestGetcurl($url);
        //下载到服务器
        $url = 'http://qdy.hdewm.com/alizip/'.$response;
        $dir = ROOT_PATH.'public/aliprogram/'.$response;  //项目服务器路径, 含文件名
        $ch = curl_init($url);
        $fp = fopen($dir, "wb");
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $res=curl_exec($ch);
        curl_close($ch);
        fclose($fp);
        if($res){
            //下载到本地
            $dw = new download($response, ROOT_PATH.'public/aliprogram/'); //下载文件
            $dw->getfiles();

            //删除本地服务器文件
            unlink(ROOT_PATH.'public/aliprogram/'.$response);
        }
        
        
        // $dw = new download('test20190323152641.zip', 'http://wx.hdewm.com/'); //下载文件
        // $dw->getfiles();
        die;


        //import('ORG.Util.FileToZip');
        //$handler = opendir(ROOT_PATH . "public/components"); //$cur_file 文件所在目录
        //$download_file = array();

        //$i = 0;
        //while( ($filename = readdir($handler)) !== false ) {
         //if($filename != '.' && $filename != '..') {
         //$download_file[$i++] = $filename;
         //}
        //}
        //closedir($handler);
        $scandir=new traverseDir(ROOT_PATH . "public/components", ROOT_PATH . "public/"); //$save_path zip包文件目录
        $scandir->tozip();
        // $zip = new \ZipArchive;
        // dump($zip);die;
    }



    //字节跳动小程序设置
    public function bdanceset(){
        $id = input("appletid");
        $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
        if(!$res){
            $this->error("找不到对应的小程序！");
        }
        $this->assign('applet',$res);
        return $this->fetch('bdanceset');
    }

    //字节跳动小程序设置保存
    public function bdancesave(){
        $appletid = input("appletid");
        $app = array(
            "bdance_appID" => trim(input("bdance_appID")),
            "bdance_appSecret" => trim(input('bdance_appSecret')),
            "bdance_mchid" => trim(input('bdance_mchid')),
            "bdance_mchid_appid" => trim(input('bdance_mchid_appid')),
            "bdance_mchid_secret" => trim(input('bdance_mchid_secret')),
            "bdance_app_id" => trim(input('bdance_app_id')),
            "bdance_app_public_key" => trim(input('bdance_app_public_key')),
            "bdance_app_private_key" => trim(input('bdance_app_private_key')),
            "bdance_h5_appid" => trim(input('bdance_h5_appid')),
            "bdance_h5_mchid" => trim(input('bdance_h5_mchid')),
            "bdance_h5_signkey" => trim(input('bdance_h5_signkey')),
        );
        $bdance_wx_certtext = input('bdance_wx_certtext');
        $bdance_wx_keytext = input('bdance_wx_keytext');
        if($bdance_wx_certtext && $bdance_wx_keytext){
            $cert_path = ROOT_PATH."public/Cert/".$appletid."/bdance_apiclient_cert.pem";
            $key_path = ROOT_PATH."public/Cert/".$appletid."/bdance_apiclient_key.pem";
            $path = ROOT_PATH."public/Cert";

            if(!file_exists($path)){
                if (mkdir($path)) {
                    $upath = ROOT_PATH."public/Cert/".$appletid."/";
                    if(!file_exists($upath)){
                        mkdir($upath);
                    }
                }
            }else{
                $upath = ROOT_PATH."public/Cert/".$appletid."/";
                if(!file_exists($upath)){
                    mkdir($upath);
                }
            }

            file_put_contents($cert_path, $bdance_wx_certtext);
            file_put_contents($key_path, $bdance_wx_keytext);
        }

        $app['bdance_wx_certtext'] = $bdance_wx_certtext;
        $app['bdance_wx_keytext'] = $bdance_wx_keytext;
        $res = Db::name('wd_xcx_applet') ->where('id', $appletid) ->update($app);

        if($res !== false){
            $this->success('修改成功!');
        }else{
            $this->error('修改失败!');
        }
    }

    //下载字节跳动小程序源码包
    public function downloadBdance(){
        $uniacid = input("uniacid");
        $appid = input("appid");
        $domain = 'www.baidu.com';
        $ip = '888.888.888.888';
        $platform = PLATFORM;

        //远程服务器更改配置, 打包, 返回文件名
        $url = "http://qdy.hdewm.com/bdanceprogram.php?uniacid=$uniacid&domain=$domain&ip=$ip&appid=$appid&platform=$platform";
        $response = $this->_requestGetcurl($url);
        //下载到服务器
        $url = 'http://qdy.hdewm.com/bdancezip/'.$response;
        $dir = ROOT_PATH.'public/bdanceprogram/'.$response;  //项目服务器路径, 含文件名
        $ch = curl_init($url);
        $fp = fopen($dir, "wb");
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $res=curl_exec($ch);
        curl_close($ch);
        fclose($fp);
        if($res){
            //下载到本地
            $dw = new download($response, ROOT_PATH.'public/bdanceprogram/'); //下载文件
            $dw->getfiles();

            //删除本地服务器文件
            unlink(ROOT_PATH.'public/bdanceprogram/'.$response);
        }
        
        
        // $dw = new download('test20190323152641.zip', 'http://wx.hdewm.com/'); //下载文件
        // $dw->getfiles();
        die;


        //import('ORG.Util.FileToZip');
        //$handler = opendir(ROOT_PATH . "public/components"); //$cur_file 文件所在目录
        //$download_file = array();

        //$i = 0;
        //while( ($filename = readdir($handler)) !== false ) {
         //if($filename != '.' && $filename != '..') {
         //$download_file[$i++] = $filename;
         //}
        //}
        //closedir($handler);
        $scandir=new traverseDir(ROOT_PATH . "public/components", ROOT_PATH . "public/"); //$save_path zip包文件目录
        $scandir->tozip();
        // $zip = new \ZipArchive;
        // dump($zip);die;
    }

    public function downloadbaidu(){
        $uniacid = input("uniacid");
        $domain = 'www.baidu.com';
        $ip = '888.888.888.888';
        $platform = PLATFORM;

        //远程服务器更改配置, 打包, 返回文件名
        $url = "http://qdy.hdewm.com/bdprogram.php?uniacid=$uniacid&domain=$domain&ip=$ip&platform=$platform";
        $response = $this->_requestGetcurl($url);
        //下载到服务器
        $url = 'http://qdy.hdewm.com/bdzip/'.$response;
        $dir = ROOT_PATH.'public/bdprogram/'.$response;  //项目服务器路径, 含文件名
        $ch = curl_init($url);
        $fp = fopen($dir, "wb");
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $res=curl_exec($ch);
        curl_close($ch);
        fclose($fp);
        if($res){
            //下载到本地
            $dw = new download($response, ROOT_PATH.'public/bdprogram/'); //下载文件
            $dw->getfiles();

            //删除本地服务器文件
            unlink(ROOT_PATH.'public/bdprogram/'.$response);
        }
        
        
        // $dw = new download('test20190323152641.zip', 'http://wx.hdewm.com/'); //下载文件
        // $dw->getfiles();
        die;


        //import('ORG.Util.FileToZip');
        //$handler = opendir(ROOT_PATH . "public/components"); //$cur_file 文件所在目录
        //$download_file = array();

        //$i = 0;
        //while( ($filename = readdir($handler)) !== false ) {
         //if($filename != '.' && $filename != '..') {
         //$download_file[$i++] = $filename;
         //}
        //}
        //closedir($handler);
        $scandir=new traverseDir(ROOT_PATH . "public/components", ROOT_PATH . "public/"); //$save_path zip包文件目录
        $scandir->tozip();
        // $zip = new \ZipArchive;
        // dump($zip);die;
    }


    public function _requestGetcurl($url){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $data = curl_exec($curl);
        curl_close($curl);
        return $data;
    }


    //h5设置
    public function h5set(){
        $id = input("appletid");
        $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
        if(!$res){
            $this->error("找不到对应的小程序！");
        }
        $this->assign('applet',$res);
        return $this->fetch('h5set');
    }

    //h5设置保存
    public function h5save(){
        $appletid = input("appletid");

        $cert = trim(input('wx_h5_cert'));
        $key = trim(input('wx_h5_key'));

        $app = array(
            "ali_h5_id" => trim(input('ali_h5_id')),
            "ali_h5_private_key" => trim(input('ali_h5_private_key')),
            "ali_h5_public_key" => trim(input('ali_h5_public_key')),
            "wx_h5_appid" => trim(input('wx_h5_appid')),
            "wx_h5_mchid" => trim(input('wx_h5_mchid')),
            "wx_h5_signkey" => trim(input('wx_h5_signkey')),
            "wx_h5_cert" => $cert,
            "wx_h5_key" => $key
        );


        
        if($cert && $key){
            $cert_path = ROOT_PATH."public/Cert/".$appletid."/h5_apiclient_cert.pem";
            $key_path = ROOT_PATH."public/Cert/".$appletid."/h5_apiclient_key.pem";
            $path = ROOT_PATH."public/Cert";

            if(!file_exists($path)){
                if (mkdir($path)) {
                    $upath = ROOT_PATH."public/Cert/".$appletid."/";
                    if(!file_exists($upath)){
                        mkdir($upath);
                    }
                }
            }else{
                $upath = ROOT_PATH."public/Cert/".$appletid."/";
                if(!file_exists($upath)){
                    mkdir($upath);
                }
            }

            file_put_contents($cert_path, $cert);
            file_put_contents($key_path, $key);
        }

        $res = Db::name('wd_xcx_applet') ->where('id', $appletid) ->update($app);

        if($res){
            $this->success('修改成功!');
        }else{
            $this->error('修改失败, 未修改!');
        }
    }

    //pc设置
    public function pcset(){
        $id = input("appletid");
        $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
        $bases = array();
        if($res['pc_logo']){
            $bases['pc_logo'] = $res['pc_logo'];
        }else{
            $bases['pc_logo'] = '';
        }
        if($res['banner']){
            $bases['pc_banner'] = unserialize($res['banner']);
        }else{
            $bases['pc_banner'] = '';
        }
        if($res['pc_show_qrcode']){
            $bases['pc_show_qrcode'] = unserialize($res['pc_show_qrcode']);
        }else{
            $bases['pc_show_qrcode'] = '';
        }
        if(!$res){
            $this->error("找不到对应的小程序！");
        }
        $this->assign('bases', $bases);
        $this->assign('applet',$res);
        return $this->fetch('pcset');
    }

    //pc设置保存
    public function pcsave(){
        $appletid = input("appletid");
        $pc_logo = input('commonuploadpic2');
        if(!$pc_logo){
            $pc_logo = input('pc_logo');
        }

        //二维码
        $qr_code = input('commonuploadpic3');
        if(!$qr_code){
            $qr_code = input('pc_show_qrcode');
        }

        $qr_code2 = input('commonuploadpic4');
        if(!$qr_code2){
            $qr_code2 = input('pc_show_qrcode2');
        }


        //PC域名
        $domain = input('domain');
        if($domain == $_SERVER['SERVER_NAME']){
            $this->error('不可使用当前域名！');
        }else{
            if($domain){
                $has = Db::name('wd_xcx_applet') ->where('id', 'neq', $appletid) ->where('domain', $domain) ->find();
                if($has){
                    $this->error('域名重复，请重新设置！');
                }else{
                    $app['domain'] = $domain;
                }
            }else{
                $app['domain'] = '';
            }
        }
        $app['site_title'] = input('site_title');
        $app['site_keywords'] = input('site_keywords');
        $app['site_description'] = input('site_description');
        
        //PC样式
        $pc_style = input('pc_style');
        if(!$pc_style){
            $app['pc_style'] = 1;
        }else{
            $app['pc_style'] = $pc_style;
        }

        //PC端banner
        $banner['banner1'] = input("commonuploadpic5");

        if(!$banner['banner1']){

           $banner['banner1'] = input("tbanner1") ;

        }

        $banner['banner2'] = input("commonuploadpic6");

        if(!$banner['banner2']){

           $banner['banner2'] = input("tbanner2") ;

        }

        $banner['banner3'] = input("commonuploadpic7");

        if(!$banner['banner3']){

           $banner['banner3'] = input("tbanner3") ;

        }
        $app['banner'] = serialize($banner);
        $app['pc_logo'] = $pc_logo;
        $app['pc_show_qrcode'] = serialize(['qrcode1' => $qr_code, 'qrcode2' => $qr_code2]);

        $res = Db::name('wd_xcx_applet') ->where('id', $appletid) ->update($app);

        if($res){
            $this->success('修改成功!');
        }else{
            $this->error('修改失败!');
        }
    }

    //获取PC网站预览地址
    public function geturl(){
        $uniacid = input('uniacid');
        $domain = Db::name('wd_xcx_applet') ->where('id', $uniacid) ->field('domain') ->find();
        if($domain['domain']){
            return 'http://'.$domain['domain'];
        }else{
            return 'http://'.$_SERVER['SERVER_NAME'].'/front/index/index?uniacid='.$uniacid;
        }
    }

    //获取H5预览二维码
    public function getqrcode(){
        $uniacid = input('uniacid');
        $preview = input('preview');
        $pageid = input('pageid');

        if($pageid){
            $url = 'https://'.$_SERVER['SERVER_NAME']. STATIC_ROOT . '/h5/index.html?id='.$uniacid.'#/pages/index/index?pageid='.$pageid;
        }else{
            $url = 'https://'.$_SERVER['SERVER_NAME']. STATIC_ROOT . '/h5/index.html?id='.$uniacid;
        }

        Vendor('phpqrcode.phpqrcode');
        $errorCorrectionLevel =intval(3) ;//容错级别 
        $matrixPointSize = intval(4);//生成图片大小 
         //生成二维码图片 
        $object = new \QRcode();
        $time = time();
        if($preview == 1){
            $filename = ROOT_PATH.'public/ewmimg/h5/'.$uniacid.$time.'_pre.png';
        }else{
            $filename = ROOT_PATH.'public/ewmimg/h5/'.$uniacid.$time.'.png';
        }

//         dump($filename);die;
        $object->png($url, $filename, $errorCorrectionLevel, $matrixPointSize, 2); 
        if($preview == 1){
            $qr_code = STATIC_ROOT.'/ewmimg/h5/'.$uniacid.$time.'_pre.png';
        }else{
            $qr_code = STATIC_ROOT.'/ewmimg/h5/'.$uniacid.$time.'.png';
        }
        $data = array('h5_qrcode' => $qr_code);

        $res = Db::name('wd_xcx_applet') ->where('id', $uniacid) ->update($data);

        if($res !== 'false'){
            return json_encode(['qr_code'=>$qr_code, 'url'=>$url]);
        }else{
            return 2;
        }


    }

    //QQ设置
    public function qqset(){
        $id = input("appletid");
        $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
        $bases = array();
        if(!$res){
            $this->error("找不到对应的小程序！");
        }
        $this->assign('bases', $bases);
        $this->assign('applet',$res);
        return $this->fetch('qqset');
    }

    //qq保存
    public function qqsave(){
        $appletid = input("appletid");
        $app = array(
            "qq_appid" => trim(input('qq_appid')),
            "qq_appsecret" => trim(input('qq_appsecret')),
            "qq_apptoken" => trim(input('qq_apptoken')),
            "qq_mchid" => trim(input('qq_mchid')),
            "qq_mchid_key" => trim(input('qq_mchid_key')),
            "qq_mchid_password" => trim(input('qq_mchid_password')),
        );
        $qq_certtext = input('qq_certtext');
        $qq_keytext = input('qq_keytext');
        if($qq_certtext && $qq_keytext){
            $cert_path = ROOT_PATH."public/Cert/".$appletid."/qq_apiclient_cert.pem";
            $key_path = ROOT_PATH."public/Cert/".$appletid."/qq_apiclient_key.pem";
            $path = ROOT_PATH."public/Cert";

            if(!file_exists($path)){
                if (mkdir($path)) {
                    $upath = ROOT_PATH."public/Cert/".$appletid."/";
                    if(!file_exists($upath)){
                        mkdir($upath);
                    }
                }
            }else{
                $upath = ROOT_PATH."public/Cert/".$appletid."/";
                if(!file_exists($upath)){
                    mkdir($upath);
                }
            }

            file_put_contents($cert_path, $qq_certtext);
            file_put_contents($key_path, $qq_keytext);
        }

        $app['qq_certtext'] = $qq_certtext;
        $app['qq_keytext'] = $qq_keytext;

        $res = Db::name('wd_xcx_applet') ->where('id', $appletid) ->update($app);

        if($res){
            $this->success('修改成功!');
        }else{
            $this->error('修改失败!');
        }
    }

    public function downloadqq(){
        $uniacid = input("uniacid");
        $domain = 'www.baidu.com';
        $ip = '888.888.888.888';
        $platform = PLATFORM;

        //远程服务器更改配置, 打包, 返回文件名
        $url = "http://qdy.hdewm.com/qqprogram.php?uniacid=$uniacid&domain=$domain&ip=$ip&platform=$platform";
        $response = $this->_requestGetcurl($url);
        //下载到服务器
        $url = 'http://qdy.hdewm.com/qqzip/'.$response;
        $dir = ROOT_PATH.'public/qqprogram/'.$response;  //项目服务器路径, 含文件名
        $ch = curl_init($url);
        $fp = fopen($dir, "wb");
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $res=curl_exec($ch);
        curl_close($ch);
        fclose($fp);
        if($res){
            //下载到本地
            $dw = new download($response, ROOT_PATH.'public/qqprogram/'); //下载文件
            $dw->getfiles();

            //删除本地服务器文件
            unlink(ROOT_PATH.'public/qqprogram/'.$response);
        }
    }

    //百度设置
    public function baiduset(){
        $id = input("appletid");
        $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
        $bases = array();
        if(!$res){
            $this->error("找不到对应的小程序！");
        }
        $this->assign('bases', $bases);
        $this->assign('applet',$res);
        return $this->fetch('baiduset');
    }

    //百度保存
    public function baidusave(){
        $appletid = input("appletid");
        $app = array(
            "baidu_xcxId" => trim(input('baidu_xcxId')),
            "baidu_appkey" => trim(input('baidu_appkey')),
            "baidu_appSecret" => trim(input('baidu_appSecret')),
            "baidu_dealId" => trim(input('baidu_dealId')),
            "baidu_pay_appkey" => trim(input('baidu_pay_appkey')),
            "baidu_private_key" => trim(input('baidu_private_key')),
            "baidu_public_key" => trim(input('baidu_public_key')),
        );

        $res = Db::name('wd_xcx_applet') ->where('id', $appletid) ->update($app);

        if($res){
            $this->success('修改成功!');
        }else{
            $this->error('修改失败!');
        }
    }

    //检测微信小程序是否有重复
    public function checkWxAppid(){
        $uniacid = input('uniacid');
        $appid = input('appid');
        $is = Db::name('wd_xcx_applet')->where('appID', $appid)->where('id', 'neq', $uniacid)->find();
        if($is){
            return 1;
        }else{
            return 2;
        }
    }

    //检测微信小程序是否有重复
    public function checkqqAppid(){
        $uniacid = input('uniacid');
        $appid = input('appid');
        $is = Db::name('wd_xcx_applet')->where('qq_appid', $appid)->where('id', 'neq', $uniacid)->find();
        if($is){
            return 1;
        }else{
            return 2;
        }
    }

    //检测支付宝小程序是否有重复
    public function checkAliAppid(){
        $uniacid = input('uniacid');
        $appid = input('appid');
        $is = Db::name('wd_xcx_applet')->where('ali_appID', $appid)->where('id', 'neq', $uniacid)->find();
        if($is){
            return 1;
        }else{
            return 2;
        }
    }
    //检测字节跳动小程序是否有重复
    public function checkBdanceAppid(){
        $uniacid = input('uniacid');
        $appid = input('appid');
        $is = Db::name('wd_xcx_applet')->where('bdance_appID', $appid)->where('id', 'neq', $uniacid)->find();
        if($is){
            return 1;
        }else{
            return 2;
        }
    }

    //检测百度小程序是否有重复
    public function checkBdAppid(){
        $uniacid = input('uniacid');
        $appid = input('appid');
        $is = Db::name('wd_xcx_applet')->where('baidu_xcxId', $appid)->where('id', 'neq', $uniacid)->find();
        if($is){
            return 1;
        }else{
            return 2;
        }
    }

}

/**
 * zip下载类文件
 * 遍历目录，打包成zip格式
 */
class traverseDir {
    public $currentdir; //当前目录
    public $filename; //文件名
    public $fileinfo; //用于保存当前目录下的所有文件名和目录名以及文件大小
    public $savepath;
    public function __construct($curpath, $savepath) {
        $this->currentdir = $curpath; //返回当前目录
        $this->savepath = $savepath; //返回当前目录
        
    }
    //遍历目录
    public function scandir($filepath) {
        if (is_dir($filepath)) {
            $arr = scandir($filepath);
            foreach ($arr as $k => $v) {
                $this->fileinfo[$v][] = $this->getfilesize($v);
            }
        } else {
            echo "<script>alert('当前目录不是有效目录');</script>";
        }
    }
    /**
     * 返回文件的大小
     *
     * @param string $filename 文件名
     * @return 文件大小(KB)
     */
    public function getfilesize($fname) {
        return filesize($fname) / 1024;
    }
    /**
     * 压缩文件(zip格式)
     */
    public function tozip() {
        $zip = new \ZipArchive();
        $zipname = date('YmdHis', time());
        if (!file_exists($zipname)) {
            $res = $zip->open($this->savepath . $zipname . '.zip', \ZipArchive::OVERWRITE | \ZIPARCHIVE::CREATE); //创建一个空的zip文件
            if ($res === true) {
                $this->addFileToZip($this->currentdir, $zip);
            }
            $zip->close();
            $dw = new download($zipname . '.zip', $this->savepath); //下载文件
            $dw->getfiles();
            unlink($this->savepath . $zipname . '.zip'); //下载完成后要进行删除
            
        }
    }
     //这段莫名其妙报错，如果不报错应该就行了
    public function addFileToZip($path, $zip) {
        $handler = opendir($path); //打开当前文件夹由$path指定。
        while(($filename = readdir($handler)) !== false) {
            if($filename != "." && $filename != "..") { //文件夹文件名字为'.'和‘..’，不要对他们进行操作
                if(is_dir($path . "/" . $filename)) { // 如果读取的某个对象是文件夹，则递归
                    $this->addFileToZip($path . "/" . $filename, $zip);
                }else{ //将文件加入zip对象
                    $zip->addFile($path . "/" . $filename);
                }
            }
        }
        @closedir($path);
    }

    
}
/**
 * 下载文件
 *
 */
class download {
    protected $_filename;
    protected $_filepath;
    protected $_filesize; //文件大小
    protected $savepath; //文件大小
    public function __construct($filename, $savepath) {
        $this->_filename = $filename;
        $this->_filepath = $savepath . $filename;
    }
    //获取文件名
    public function getfilename() {
        return $this->_filename;
    }
    //获取文件路径（包含文件名）
    public function getfilepath() {
        return $this->_filepath;
    }
    //获取文件大小
    public function getfilesize() {
        return $this->_filesize = number_format(filesize($this->_filepath) / (1024 * 1024), 2); //去小数点后两位
        
    }
    //下载文件的功能
    public function getfiles() {
        //检查文件是否存在
        if (file_exists($this->_filepath)) {
            //打开文件
            $file = fopen($this->_filepath, "r");
            //返回的文件类型
            Header("Content-type: application/octet-stream");
            //按照字节大小返回
            Header("Accept-Ranges: bytes");
            //返回文件的大小
            Header("Accept-Length: " . filesize($this->_filepath));
            //这里对客户端的弹出对话框，对应的文件名
            Header("Content-Disposition: attachment; filename=" . $this->_filename);
            //修改之前，一次性将数据传输给客户端
            echo fread($file, filesize($this->_filepath));
            //修改之后，一次只传输1024个字节的数据给客户端
            //向客户端回送数据
            $buffer = 1024; //
            //判断文件是否读完
            while (!feof($file)) {
                //将文件读入内存
                $file_data = fread($file, $buffer);
                //每次向客户端回送1024个字节的数据
                echo $file_data;
            }
            fclose($file);
        } else {
            echo "<script>alert('对不起,您要下载的文件不存在');</script>";
        }
    }


}
