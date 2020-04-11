<?php
namespace app\index\model;
use think\Model;
use think\Db;

class Applet extends Model{
	protected $pk = 'id';
	protected $name = 'wd_xcx_applet';

	//获取项目信息
	public function getAppInfo(){
		$id = input('appletid');
		$appInfo = $this->get($id);
		if(!$appInfo){
			return false;
		}else{
			return $appInfo;
		}
	}

	//获取所有项目的推广位与uniacid
    public function getJdPid(){
	    return $this ->column('id', 'jd_id');
    }
}	