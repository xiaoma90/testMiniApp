<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;
class Shops extends Base
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
                $store_all = Db::name('wd_xcx_store')->where("uniacid",$appletid)->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                if($store_all){
                    $store = $store_all->toArray()['data'];
                    foreach ($store as $key => $item){
                        if($item['logo']){
                            $store[$key]['logo'] = remote($appletid, $item['logo'], 1);
                        }
                    }
                }
                $counts = Db::name('wd_xcx_store')->where("uniacid",$appletid)->count();
                $this->assign('counts',$counts);
                $this->assign('store',$store);
                $this->assign('store_all', $store_all);
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
    public function baseset(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $base = Db::name('wd_xcx_storeconf')->where("uniacid",$appletid)->find();
                $this->assign('bases',$base);
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
            return $this->fetch('baseset');
        }else{
            $this->redirect('Login/index');
        }
    }
    public function basesave(){
        $uniacid = input("appletid");
        $data['uniacid'] = $uniacid;
        $search = input("search");
        if($search!==null){
            $data['search'] = $search;
        }
        $title = input("title");
        if($title){
            $data['title'] = $title;
        }
        $flag = input("flag");
        if($flag >=0){
            $data['flag'] = intval($flag);
        }
        $mapkey = input("mapkey");
        if($mapkey){
            $data['mapkey'] = $mapkey;
        }
        $data['ctime'] = time();
        $counts = Db::name('wd_xcx_storeconf')->where("uniacid",$uniacid)->count();
        if($counts>0){
            $res = Db::name('wd_xcx_storeconf')->where("uniacid",$uniacid)->update($data);
        }else{
            $res = Db::name('wd_xcx_storeconf')->insert($data);
        }
        if($res){
            $this->success('多门店信息更新成功！');
        }else{
            $this->error('多门店信息更新失败，没有修改项！');
            exit;
        }
    }
    public function add(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $allimg = "";
                $cid = input("cid");
                $store = array();

//                $staff = Db::name('wd_xcx_staff')->where("uniacid",$appletid)->select();

                if($cid){
                    $store = Db::name('wd_xcx_store')->where("uniacid",$appletid)->where("id",$cid)->find();
                    if($store['logo']){
                        $store['logo'] = remote($appletid, $store['logo'], 1);
                    }
                    $allimg = Db::name('wd_xcx_products_url')->where("randid",$store['onlyid'])->select();
                    foreach($allimg as $k => &$v){
                        $allimg[$k]['url'] = remote($appletid,$v['url'],1);
                    }
                }else{
                    $cid=0;
                }
//                $this->assign('staff',$staff);
                $this->assign('cid',$cid);
                $this->assign('allimg',$allimg);
                $this->assign('store',$store);
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
    public function del(){
        $appletid = input("appletid");
        $cid = input("cid");
        $res = Db::name('wd_xcx_store')->where("uniacid",$appletid)->where("id",$cid)->delete();
        if($res){
            $this->success('删除成功');
        }else{
            $this->success('删除失败');
        }
    }
    public function save(){
        $uniacid = input("appletid");
        $data['uniacid'] = $uniacid;
        //logo
        $logo = input("commonuploadpic1");
        if($logo){
            $data['logo'] = remote($data['uniacid'],$logo,2);
        }
        //背景图
        $thumb = input("commonuploadpic2");
        if($thumb){
            $data['thumb'] = remote($data['uniacid'],$thumb,2);
        }
        //店铺名称
        $title = input("title");
        if($title){
            $data['title'] = $title;
        }else{
            $this->error('店铺名称不能为空');
        }
        //纬度
        $lat = input("lat");
        if($lat){
            $data['lat'] = $lat;
        }
        //经度
        $lon = input("lon");
        if($lon){
            $data['lon'] = $lon;
        }
        //电话
        $tel = input("tel");
        if($tel){
            $data['tel'] = $tel;
        }
        //营业时间
        $times = input("times");
        if($times){
            $data['times'] = $times;
        }
        //省
        $proid = input("province");
        if($proid){
            $data['proid'] = $proid;
        }
        $province = input("pro");
        if($province){
            $data['province'] = $province;
        }
        //市
        $cityid = input("city");
        if($cityid){
            $data['cityid'] = $cityid;
        }
        $city = input("cit");
        if($city){
            $data['city'] = $city;
        }
        //地址
        $country = input("country");
        if($country){
            $data['country'] = $country;
        }

        $data['dateline'] = time();
        //onlyid
        // $onlyid = input("onlyid");
        // if($onlyid){
        //     $data['onlyid'] = $onlyid;
        // }
        // $onlyid = input('onlyid');
        // if($onlyid){
        //     $imgsrcs = input("imgsrcs/a");
        //     if($imgsrcs){
        //         $imgarr = array();
        //         foreach ($imgsrcs as $k => $v) {
        //             $imgarr['randid'] = $onlyid;
        //             $imgarr['appletid'] = $data['uniacid'];
        //             $imgarr['url'] = remote($data['uniacid'],$v,2);
        //             $imgarr['dateline'] = time();
        //             $is = Db::name('wd_xcx_products_url')->insert($imgarr);
        //         }
        //     }else{
        //         $is = 1;
        //     }
        //     $data['onlyid'] = $onlyid;
        // }


        $cid = input("cid");
        if($cid){
            $res = Db::name('wd_xcx_store')->where("uniacid",$uniacid)->where("id",$cid)->update($data);
        }else{
            $res = Db::name('wd_xcx_store')->insert($data);
        }
        if($res){
            $this->success('门店信息更新成功！',Url('Shops/index').'?appletid='.$uniacid);
        }else{
            $this->error('门店信息更新失败，没有修改项！');
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
    //多图片上传
    public function imgupload_duo(){
        $data['randid'] = input('randid');
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
    public function staff(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $where = "";
                $skey = input('skey');
                if(!empty($skey)){
                    $where = " and realname like '%".$skey."%'";
                    $staffslist = Db::query("SELECT * FROM {$this->prefix}wd_xcx_staff WHERE `uniacid` = {$appletid} {$where}");
                    $counts = count($staffslist);
                    $staffs = "";
                }else{
                    $counts = Db::name('wd_xcx_staff')->where("uniacid",$appletid)->where($where)->count();
                    $staffs = Db::name('wd_xcx_staff')->where("uniacid",$appletid)->where($where)->order('sort desc')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                    $staffslist = $staffs->toArray()['data'];
                }

                if($staffslist){
                    foreach ($staffslist as $key => &$value) {
                        if($value['pic']){
                            $value['pic'] = remote($appletid,$value['pic'],1);
                        }
                    }
                }

                $this->assign('counts',$counts);
                $this->assign('staffslist',$staffslist);
                $this->assign('staffs',$staffs);
                $this->assign('skey',$skey);
    
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
            return $this->fetch('staff');
        }else{
            $this->redirect('Login/index');
        }
    }
    public function staffadd(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $allimg = "";
                $id = input("id");
//                $hxmms=array();
//                $a= Db::name('wd_xcx_base')->where("uniacid", $appletid)->find();
//                $staffs= Db::name('wd_xcx_staff')->where("uniacid",$appletid)->select();
//                foreach($staffs as $k=>$v){
//                    array_push($hxmms,$v["hxmm"]);
//                }
//                array_push($hxmms,(int)($a['hxmm']));
//                $this->assign("hxmms",json_encode($hxmms));

                $stores=Db::name('wd_xcx_store')->where("uniacid",$appletid)->select();
                $this->assign("stores",$stores);

                $staff = array();
                if($id){
                    $staff = Db::name('wd_xcx_staff')->where("uniacid",$appletid)->where("id",$id)->find();
                    if(!empty($staff['expand'])){
                        $staff['expand'] = unserialize($staff['expand']);
                    }
                    if(!empty($staff['pic'])){
                        $staff['pic'] = remote($appletid, $staff['pic'], 1);
                    }
                }else{
                    $id=0;
                }
                $this->assign('id',$id);
                $this->assign('staff',$staff);
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
            return $this->fetch('staffadd');
        }else{
            $this->redirect('Login/index');
        }
    }

    public function staffsave(){
        $uniacid = input("appletid");
        $id = intval(input('id'));
        $duogg = input('duogg');
        $duoggarr = explode(',',substr($duogg, 0,strlen($duogg)-1));
        $kkk = serialize($duoggarr);
        $mobile = input('mobile');
        if (!preg_match("/^1[3456789]{1}\d{9}$/",$mobile)) {
            $this->error("手机号格式错误！");
        }
        $score = input('score');
        if(!$score){
            $score = 0;
        }
        if(!is_numeric($score)){
            $this->error('评分为数值,请输入数字');
        }else{
            if($score < 0 || $score > 5){
                $this->error('评分数值为0-5分,请输入正确的数字');
            }
        }
        $len = strlen($score);

        if($len>3){

            $score = substr($score,0,3);

        }
        $proid = input('province');

        $cityid = input('city');

        $areaid = input('area');

        $province =  input('pro') ? input('pro') : "";

        $city = input('cit') ? input('cit') : "";
        $store = input('store') ? input('store') : "";
        $area = input('are') ? input('are') : "";
        //富文本内容处理
        $descp = input('descp');

        
        $hxmm=input("hxmm");
        $hxmms=array();
        $a= Db::name('wd_xcx_base')->where("uniacid", $uniacid)->find();
        if($hxmm==$a['hxmm']){
            $this->error('员工核销密码与系统相同，请重新设置');
        }else{
            if(empty($id)){
                $staffs= Db::name('wd_xcx_staff')->where("uniacid",$uniacid)->select();
                $hxmms=array();
                foreach($staffs as $k=>$v){
                    array_push($hxmms,$v["hxmm"]);
                }
                if(in_array($hxmm,$hxmms)){
                    $this->error('员工核销密码与其他员工的相同，请重新设置');
                }
            }else{
                $staffs= Db::name('wd_xcx_staff')->where("uniacid",$uniacid)->where("id","neq",$id)->select();
                $hxmms=array();
                foreach($staffs as $k=>$v){
                    array_push($hxmms,$v["hxmm"]);
                }
                if(in_array($hxmm,$hxmms)){
                    $this->error('员工核销密码与其他员工的相同，请重新设置');
                }
            }

        }
        $data = array(
                    'sort' => input('sort'),

                    'uniacid' => $uniacid,

                    'realname' => input('realname'),

                    'mobile' => $mobile,

                    'wxnumber' => input('wxnumber'),

                    'email' => input('email'),

                    'company' => input('company'),

                    'province' => $province,

                    'city' => $city,

                    'area' => $area,

                    'address' => input('address'),

                    'title' => input('title'),

                    'job' => input('job'),

                    'contract' => input('contract'),

                    'auth' => input('auth'),

                    'score' => $score,

                    'visit' => input('visit'),

                    'zan' => input('zan'),

                    'forward' => input('forward'),

                    'price' => input('price'),

                    'descp' => $descp,

                    'expand' => $kkk,

                    'proid' => $proid,

                    'cityid' => $cityid,

                    'areaid' => $areaid,

                    'voice' => input('voice'),

                    'hxmm' => input('hxmm'),

                    'autovoice' => input('autovoice'),

                    'store'=>$store,

                    'age' => input('age')

                );
        $pic = input('commonuploadpic1');
        if($pic){
            $data['pic'] = remote($uniacid,$pic,2);
        }

        if (empty($id)) {
            $res = Db::name("wd_xcx_staff")->insert($data);
        } else {
            $res = Db::name("wd_xcx_staff")->where("id", $id)->where("uniacid", $uniacid)->update($data);
        }
        if($res){
            $this->success("员工修改成功", Url('Shops/staff').'?appletid='.$uniacid);
        }else{
            $this->error("员工修改失败");
        }
    }


    public function staffdel(){
        $appletid = input("appletid");
        $cid = input("id");
        $res = Db::name('wd_xcx_staff')->where("uniacid",$appletid)->where("id",$cid)->delete();
        if($res){
            $this->success('删除成功');
        }else{
            $this->success('删除失败');
        }
    }


    //后台生成二维码
    public function qrcode(){
        $appletid = input("appletid");
        $cid = input("id");

        $app = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
        $appid = $app['appID'];
        $appsecret = $app['appSecret'];
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$appsecret;
        $weixin = file_get_contents($url);
        $jsondecode = json_decode($weixin); //对JSON格式的字符串进行编码
        $array = get_object_vars($jsondecode);//转换成数组
        $access_token = $array['access_token'];//输出openid

        $ewmurl = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=" . $access_token;
        $sjc = time().rand(1000,9999);
        $pagepath = 'sudu8_page/staff_card/staff_card';
        $data = [
                    'page' => $pagepath,
                    'width' => '500',
                    'scene' => $cid
                ];
        $data=json_encode($data);
        //$result = $this->_requestPost($ewmurl,$data); 
        //_requestPost($url, $data, $ssl=true) {  
        $curl = curl_init();  
        //设置curl选项  
        curl_setopt($curl, CURLOPT_URL, $ewmurl);//URL  
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:38.0) Gecko/20100101 Firefox/38.0 FirePHP/0.7.4';  
        curl_setopt($curl, CURLOPT_USERAGENT, $user_agent);//user_agent，请求代理信息  
        curl_setopt($curl, CURLOPT_AUTOREFERER, true);//referer头，请求来源  
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);//设置超时时间  
        //SSL相关  
        if (true) {  
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//禁用后cURL将终止从服务端进行验证  
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);//检查服务器SSL证书中是否存在一个公用名(common name)。  
        }  
        // 处理post相关选项  
        curl_setopt($curl, CURLOPT_POST, true);// 是否为POST请求  
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);// 处理请求数据  
        // 处理响应结果  
        curl_setopt($curl, CURLOPT_HEADER, false);//是否处理响应头  
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);//curl_exec()是否返回响应结果  

        // 发出请求  
        $response = curl_exec($curl);
        
        if (false === $response) {  
            echo '<br>', curl_error($curl), '<br>';  
            return false;  
        }  
        curl_close($curl);  

        // var_dump($response);die;
        // var_dump($result);
        // die();
        $newpath = ROOT_PATH."public/ewmimg";
        if(!file_exists($newpath)){
            mkdir($newpath);
        }
        
        file_put_contents(ROOT_PATH."public/ewmimg/".$sjc.".jpg", $response); 
        $path = ROOT_HOST."/ewmimg/".$sjc.".jpg";
        
        $tdata = array(
            "bqrcode" => $path
        );
        
        $res = Db::name('wd_xcx_staff') ->where('id', $cid) ->update($tdata);
        if($res){
            $this->success('二维码生成成功!');
        }else{
            $this->error('发生未知错误, 二维码生成失败, 请稍后重试!');
        }
    }

    //员工设置
    public function staffset(){
        $uniacid = input('appletid');
        $res = Db::name('wd_xcx_applet')->where("id",$uniacid)->find();
        if(!$res){
            $this->error("找不到对应的小程序！");
        }
        $this->assign('applet',$res);

        $staffsetdata = array(
            'uniacid' => $uniacid,
        );

        $staffset = Db::name('wd_xcx_staffset') ->where('uniacid', $uniacid) ->find();
        if(!$staffset){
            Db::name('wd_xcx_staffset') ->insert($staffsetdata);
            $staffset = Db::name('wd_xcx_staffset') ->where('uniacid', $uniacid) ->find();
        }
        $staffset['tabbar'] = unserialize($staffset['tabbar']);
        if(!$staffset['tabbar']){
            $staffset1 = "";
            $staffset2 = "";
            $staffset3 = "";
            $staffset4 = "";
            $staffset5 = "";
        }
        if(isset($staffset['tabbar'][0]) && $staffset['tabbar'][0]){
            $staffset['tabbar'][0] = unserialize($staffset['tabbar'][0]);
            $staffset1 = $staffset['tabbar'][0];
        }else{
            $staffset1 = "";
        }
        if(isset($staffset['tabbar'][1]) && $staffset['tabbar'][1]){
            $staffset['tabbar'][1] = unserialize($staffset['tabbar'][1]);
            $staffset2 = $staffset['tabbar'][1];
        }else{
            $staffset2 = "";
        }
        if(isset($staffset['tabbar'][2]) && $staffset['tabbar'][2]){
            $staffset['tabbar'][2] = unserialize($staffset['tabbar'][2]);
            $staffset3 = $staffset['tabbar'][2];
        }else{
            $staffset3 = "";
        }
        if(isset($staffset['tabbar'][3]) && $staffset['tabbar'][3]){
            $staffset['tabbar'][3] = unserialize($staffset['tabbar'][3]);
            $staffset4 = $staffset['tabbar'][3];
        }else{
            $staffset4 = "";
        }
        if(isset($staffset['tabbar'][4]) && $staffset['tabbar'][4]){
            $staffset['tabbar'][4] = unserialize($staffset['tabbar'][4]);
            $staffset5 = $staffset['tabbar'][4];
        }else{
            $staffset5 = "";
        }

        $this->assign('staffset1', $staffset1);
        $this->assign('staffset2', $staffset2);
        $this->assign('staffset3', $staffset3);
        $this->assign('staffset4', $staffset4);
        $this->assign('staffset5', $staffset5);


        $this->assign('staffset', $staffset);

        return $this->fetch('staffset');
    }

    //员工设置管理
    public function staffset_save(){
        $uniacid = input('appletid');
        $data = array();
        //列表样式  名片样式
        // $data['list_style'] = input('list_style');
        $data['card_style'] = input('card_style');

        //分享赠送积分与抽奖机会
        $data['is_share'] = input('is_share');
        $share_score = input('share_score');
        if(!isset($share_score)){
            $share_score = 10;
        }
        $data['share_score'] = $share_score;   //input('means'] != NULL ? input('means'] : 1

        $share_scount = input('share_scount');
        if(!isset($share_scount)){
            $share_scount = 3;
        }
        $data['share_scount'] = $share_scount;

        $share_prize = input('share_prize');
        if(!isset($share_prize)){
            $share_prize = 1;
        }
        $data['share_prize'] = $share_prize;

        $share_pcount = input('share_pcount');
        if(!isset($share_pcount)){
            $share_pcount = 3;
        }
        $data['share_pcount'] = $share_pcount;

        //保存赠送积分与抽奖机会
        $data['is_save'] = input('is_save');
        $save_score = input('save_score');
        if(!isset($save_score)){
            $save_score = 10;
        }
        $data['save_score'] = $save_score;

        $save_scount = input('save_scount');
        if(!isset($save_scount)){
            $save_scount = 3;
        }
        $data['save_scount'] = $save_scount;

        $save_prize = input('save_prize');
        if(!isset($save_prize)){
            $save_prize = 1;
        }
        $data['save_prize'] = $save_prize;

        $save_pcount = input('save_pcount');
        if(!isset($save_pcount)){
            $save_pcount = 3;
        }
        $data['save_pcount'] = $save_pcount;

        //底部菜单基础配置
        $data['tabbar_t'] = input('tabbar_t');
        $data['tabbar_bg'] = input('tabbar_bg');
        $data['color_bar'] = input('color_bar');
        $data['tabbar_tc'] = input('tabbar_tc');
        $data['tabbar_tca'] = input('tabbar_tca');


        //底部菜单
        $tabbar = array();
        $tabbar1=array(
        'tabbar_name' => input('tabbar1_name'),
        'tabbar_url' => input('tabbar1_url'),
        'tabbar_linktype' => input('tabbar1_linktype'),
        'tabbar' => input('tabbar1')?input('tabbar1'):1
        );
        if(input('tabbar1')==2){
            $tabbar1['tabimginput_1'] = input('tabimginput1_3');
        }else{
            $tabbar1['tabimginput_1'] = input('tabimginput1_1');
            $tabbar1['tabimginput_2'] = input('tabimginput1_2');
        }
        $tabbar2=array(
        'tabbar_name' => input('tabbar2_name'),
        'tabbar_url' => input('tabbar2_url'),
        'tabbar_linktype' => input('tabbar2_linktype'),
        'tabbar' => input('tabbar2')?input('tabbar2'):1
        );
        if(input('tabbar2')==2){
            $tabbar2['tabimginput_1'] = input('tabimginput2_3');
        }else{
            $tabbar2['tabimginput_1'] = input('tabimginput2_1');
            $tabbar2['tabimginput_2'] = input('tabimginput2_2');
        }
        $tabbar3=array(
        'tabbar_name' => input('tabbar3_name'),
        'tabbar_url' => input('tabbar3_url'),
        'tabbar_linktype' => input('tabbar3_linktype'),
        'tabbar' => input('tabbar3')?input('tabbar3'):1
        );
        if(input('tabbar3')==2){
            $tabbar3['tabimginput_1'] = input('tabimginput3_3');
        }else{
            $tabbar3['tabimginput_1'] = input('tabimginput3_1');
            $tabbar3['tabimginput_2'] = input('tabimginput3_2');
        }
        $tabbar4=array(
        'tabbar_name' => input('tabbar4_name'),
        'tabbar_url' => input('tabbar4_url'),
        'tabbar_linktype' => input('tabbar4_linktype'),
        'tabbar' => input('tabbar4')?input('tabbar4'):1
        );
        if(input('tabbar4')==2){
            $tabbar4['tabimginput_1'] = input('tabimginput4_3');
        }else{
            $tabbar4['tabimginput_1'] = input('tabimginput4_1');
            $tabbar4['tabimginput_2'] = input('tabimginput4_2');
        }
        $tabbar5=array(
        'tabbar_name' => input('tabbar5_name'),
        'tabbar_url' => input('tabbar5_url'),
        'tabbar_linktype' => input('tabbar5_linktype'),
        'tabbar' => input('tabbar5')?input('tabbar5'):1
        );
        if(input('tabbar5')==2){
            $tabbar5['tabimginput_1'] = input('tabimginput5_3');
        }else{
            $tabbar5['tabimginput_1'] = input('tabimginput5_1');
            $tabbar5['tabimginput_2'] = input('tabimginput5_2');
        }




        $tabbar1 = serialize($tabbar1);

        $tabbar2 = serialize($tabbar2);

        $tabbar3 = serialize($tabbar3);

        $tabbar4 = serialize($tabbar4);

        $tabbar5 = serialize($tabbar5);

        if(input('tabbar1_url') != ''){

            $tabbar[0]=$tabbar1;

        }

        if(input('tabbar2_url') != ''){

            $tabbar[1]=$tabbar2;

        }

        if(input('tabbar3_url') != ''){

            $tabbar[2]=$tabbar3;

        }

        if(input('tabbar4_url') != ''){

            $tabbar[3]=$tabbar4;

        }

        if(input('tabbar5_url') != ''){

            $tabbar[4]=$tabbar5;

        }

        $tabnum = count($tabbar);

        $tabbar = serialize($tabbar);

        $data['tabbar'] = $tabbar;
        $data['tabnum'] = $tabnum;
        $data['uniacid'] = $uniacid;

        // dump($data);die;
        $res = Db::name('wd_xcx_staffset') ->where('uniacid', $uniacid) ->update($data);
        if($res){
            $this->success('员工设置保存成功!');
        }else{
            $this->error('发生未知错误, 操作失败, 请稍后再试!');
        }
    }

    //批量删除操作
    public function delall(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $array1=input('staffs');
                $arr=explode(',',$array1);

                $res = Db::name('wd_xcx_staff')->where("uniacid",$appletid)->where('id',"in",$arr)->delete();
                if($res){
                    $this->success('删除成功');
                }else{
                    $this->error('删除失败');
                }
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


}