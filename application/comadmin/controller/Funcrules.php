<?php
namespace app\comadmin\controller;

use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;

class Funcrules extends Controller
{
    public function index(){
        if(check_login()){
//        	include ROOT_PATH.'application/index/controller/Ordinary.php';
	       // $or = new \Ordinary();
	        //$license = $or ->checkAuth();
	       // $this->assign("license", $license);
	       
            return $this->fetch('index');
        }else{
            $this->redirect('Index/Login/index');
        }
    }
    public function keys(){
    	//include ROOT_PATH.'application/index/controller/Ordinary.php';
       // $or = new \Ordinary();
       // $license = $or ->checkAuth();

  	$op = input('op');
    	$keys = '';

    	if($license[$op]){
    		$keys_arr = include ROOT_PATH.'application/index/controller/License.php';
            $keys = $keys_arr[$op];
    	}
		$this->assign("op", $op);
    	$this->assign("keys", $keys);
    	return $this->fetch('keys');
    }
    public function checkauth(){
        $op = input('op');
        $keys = input('keys');
        if($keys){
            $keys_arr = include ROOT_PATH.'application/index/controller/License.php';
             
            if(!isset($keys_arr[$op])){
                $license = urlencode($keys_arr['license']);
                $subkey = urlencode($keys);
                $hosturl = urlencode($_SERVER['HTTP_HOST']);

               $authhosturl = self::UPDATE_ENDPOINT . '?a=auth&u=' . $hosturl . '&key=' . $license . '&op=' . $op . '&subkey=' . $subkey;
                $authinfo = file_get_contents($authhosturl);
                $authinfo = json_decode($authinfo, true);
                if($authinfo['code'] == 0){
                    $keys_arr[$op] = htmlspecialchars_decode($keys);
                    $str = '<?php return [';
                    foreach ($keys_arr as $k => $v){
                        $str .= '\''.$k.'\''.'=>'.'\''.$v.'\''.',';
                    };
                    $str .= ']; ';
                    $path = ROOT_PATH.'application/index/controller/License.php';
                    if(file_put_contents($path, $str)){
                        $this->success("授权成功");
                    }else {
                        $this->error("授权失败：授权配置文件更新失败");
                    }
                }else{
                    $this->error("授权失败：".$authinfo['message']);
                }
            }else{
                $this->error("授权失败：授权配置文件中已有插件密钥");
            }
        }else{
            $this->error("授权失败：授权密钥不能为空");
        }
    }
}