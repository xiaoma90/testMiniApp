<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;


class Cytablenum extends Base
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

                $op = input("op");
                $this->aa();
                if($op=="ewm"){
                    $tnum = input('tnum');
                    $id = input('id');

                    // $table = Db::name('wd_xcx_food_tables')->where("uniacid",$appletid)->where('id',$id)->find();

                    $app = Db::name('wd_xcx_applet')->where("id",$appletid)->find();

                        $appid = $app['appID'];
                        $appsecret = $app['appSecret'];
                        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$appsecret;
                        $weixin = file_get_contents($url);
                        $jsondecode = json_decode($weixin); //对JSON格式的字符串进行编码
                        $array = get_object_vars($jsondecode);//转换成数组
                        $access_token = $array['access_token'];//access_token
                        $ewmurl = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=" . $access_token;
                        $sjc = time().rand(1000,9999);
                        $data = [
                                    'page' => "sudu8_page_plugin_food/food/food",
                                    'width' => '500',
                                    'scene' => $id
                                ];
                        $data=json_encode($data);
                        $result = $this->_requestPost($ewmurl,$data); 
                        $save_path = ROOT_PATH."/public/ewmimg/";
                        if(!file_exists($save_path)){
                            mkdir($save_path);
                        }
                        file_put_contents(ROOT_PATH."/public/ewmimg/".$sjc.".jpg", $result); 
                        $path = ROOT_HOST."/ewmimg/".$sjc.".jpg";
                        //  var_dump($path);
                        // die();
                        
                        $tdata = array(
                            "thumb" => $path
                        );
                        Db::name('wd_xcx_food_tables')->where('id',$id)->update($tdata);
                        $this->success("二维码生成成功");
                        exit;
                }else{
                    $listV_s = Db::name('wd_xcx_food_tables')->where("uniacid",$appletid)->order('tnum desc')->paginate(10,false,['query' => ['appletid' => $appletid]]);
                    $listV = $listV_s->toArray()['data'];
                    $this->assign('cates',$listV);
                    $this->assign('cates_list',$listV_s);
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

        //不带报头的curl
    public function _requestPost($url, $data, $ssl=true) {  
            //curl完成  
            $curl = curl_init();  
            //设置curl选项  
            curl_setopt($curl, CURLOPT_URL, $url);//URL  
            $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:38.0) Gecko/20100101 Firefox/38.0 FirePHP/0.7.4';  
            curl_setopt($curl, CURLOPT_USERAGENT, $user_agent);//user_agent，请求代理信息  
            curl_setopt($curl, CURLOPT_AUTOREFERER, true);//referer头，请求来源  
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);//设置超时时间  
            //SSL相关  
            if ($ssl) {  
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
            return $response;  
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
                    $lanmu = Db::name('wd_xcx_food_tables')->where("id",$cateid)->find();

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


    private function aa(){
        $secret = md5('worldidc_wnmd'); // md5('worldidc_wnmd');

        $key_content = include('License.php');
        $key_content = $key_content['license'];
        $length = strlen($key_content);

        // 密钥长度小于 102 必然无效
        // if($length < 102) {
        //     die();
        // }

        $is = base64_decode(substr($key_content, 0, 6));

        if(substr($is, 0, 1) == '|'){
            $str_arr = unpack("C2", substr($is, 1));
            $key_content = substr($key_content, 6);
            $len1 = $str_arr[1];
            $len2 = $length - 6 - $len1 - $str_arr[2];
        }else{
            $len1 = 26;
            $len2 = $length - 102;
        }

        // 获取加密的 code
        $code = base64_decode(substr($key_content, $len1, $len2));

        $code_length = strlen($code);

        $round = $code_length / 32;
        $left = $code_length % 32;

        // 获取和 code 等长的 self_key
        $self_key = str_repeat($secret, $round) . substr($secret, 0, $left);

        // 这边不妨把两个都 unpack 下

        $decode = array_map(function($a, $b) {
            $c = $a - $b;
            return $c > 0 ? $c : $c + 256;
        }, unpack("C{$code_length}", $code), unpack("C{$code_length}", $self_key));

        $str = array_reduce($decode, function($sum, $code) {
            return $sum .= chr($code);
        }, '');

        //end
        if($str == $_SERVER['HTTP_HOST']){

            //通过
        }else{

           // echo '密钥错误，请联系开发者获取正确密钥!';
           // exit();
        }
    }


    public function save(){


        $data = array();
        //小程序ID
        $data['uniacid'] = input("appletid");

        //排序
        $num = input("num");
        if($num){
            $data['tnum'] = $num;
        }
        $title = input("title");
        if($title){
            $data['title'] = $title;
        }
        
        $id = input("cateid");

        if($id!=0){
            $res = Db::name('wd_xcx_food_tables')->where("id",$id)->update($data);
        }else{
            $res = Db::name('wd_xcx_food_tables')->insert($data);
        }



        if($res){
          $this->success('点菜分类管理信息更新成功！',Url('Cytablenum/index').'?appletid='.$data['uniacid']);
        }else{
          $this->error('点菜分类管理信息更新失败，没有修改项！');
          exit;
        }



    }

    // 删除操作
    public function del(){
        $data['id'] = input("cateid");
        $res = Db::name('wd_xcx_food_tables')->where($data)->delete();
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