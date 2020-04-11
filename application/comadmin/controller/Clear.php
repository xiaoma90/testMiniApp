<?php
namespace app\comadmin\controller;

use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;
use think\Cache;

class Clear extends Controller
{
    public function index(){

        $this->clear_sys_cache();
        $this->clear_temp_ahce();
        $this->clear_log_chache();
        $this->success("清除缓存成功");
    }
    /**  
     * 清除模版缓存 不删除cache目录  
     */  
    public function clear_sys_cache() {  
        Cache::clear();  
    }  
    /**  
     * 清除模版缓存 不删除 temp目录  
     */  
    public function clear_temp_ahce() {  
        array_map( 'unlink', glob( TEMP_PATH.'*.php' ) );  
    }  
    /**  
     * 清除日志缓存 不删除log目录  
     */  
    public function clear_log_chache() {  
        $d = opendir(LOG_PATH);
        $path_arr = [];
        while(false !== ($f = readdir($d))) {
            $dName = LOG_PATH. $f;
            if(is_dir($dName) && $dName != LOG_PATH.'.' && $dName != LOG_PATH.'..') {
                array_push($path_arr, $dName);
            }
        }
        foreach ($path_arr as $item) {  
            array_map( 'unlink', glob( $item.'/*.log' ) );  
            rmdir( $item );  
        }
    }  
}