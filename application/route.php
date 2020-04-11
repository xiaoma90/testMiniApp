<?php

// +----------------------------------------------------------------------

// | ThinkPHP [ WE CAN DO IT JUST THINK ]

// +----------------------------------------------------------------------

// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.

// +----------------------------------------------------------------------

// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )

// +----------------------------------------------------------------------

// | Author: liu21st <liu21st@gmail.com>

// +----------------------------------------------------------------------
use think\Route;
use think\Db;

$domain = $_SERVER['HTTP_HOST'];
$requset_url = $_SERVER['REQUEST_URI'];
$flag = strstr($requset_url, '/front/');
 // var_dump($_SERVER['REQUEST_URI']);die;
 // "/front/hot/hot.html"
$ss = Db::name('wd_xcx_applet')  ->where('domain', $domain) ->field('id') ->find();
if($ss && !$flag){
	Route::domain($domain,'front/index/index?uniacid='.$ss['id']);
}

// Route::rule('createApplet','index/Applet/createApplet');//创建用户以及小程序接口
// Route::rule('changepwd','index/Login/changepwd');//修改用户密码
// Route::rule('updateEndtime','index/Applet/updateEndtime');//修改用户密码
// Route::rule('getProjectInfo','index/Applet/getproinfo');//获取项目ID与项目到期时间
// // Route::rule('delProject','index/Applet/delpro');//删除项目以及用户
// Route::rule('updateUsername','index/Applet/updateUsername');//修改用户名
// Route::rule('openMoreprogram','index/Applet/openMoreprogram');//开通小程序


//Route::get('synchronousOrders','index/External/getOrderFromExt');//同步订单
Route::get('renewalCommodities','index/External/updateGoods');//更新商品信息


Route::get('getOrderFromJd','index/External/getJdOrder');//每分钟定时获取京东订单信息

Route::get('updateOrders','index/External/updateExternalOrders');//定时获更新所有订单信息




return [

    '__pattern__' => [

        'name' => '\w+',

    ],

    '[hello]'     => [

        ':id'   => ['index/hello', ['method' => 'get'], ['id' => '\d+']],

        ':name' => ['index/hello', ['method' => 'post']],

    ],



];

