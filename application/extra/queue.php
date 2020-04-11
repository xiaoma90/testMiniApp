<?php

// +----------------------------------------------------------------------

// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]

// +----------------------------------------------------------------------

// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.

// +----------------------------------------------------------------------

// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )

// +----------------------------------------------------------------------

// | Author: yunwuxin <448901948@qq.com>

// +----------------------------------------------------------------------



return [

    'connector' => 'Redis',

    'expire'    =>  60,       //任务的过期时间， 默认为60秒， null 为禁用

    'default'   => 'default',    // 默认队列名称    

    'host'      => '127.0.0.1',    //redis 主机Ip

    'port'      =>  6379,         //redis端口   

    'password'  =>  '',          //redis 密码

    'select'    =>  0,           //redis  使用哪一个db  默认为db0

    'timeout'   =>  0,           // redis 连接超时时间

    'persistent' => false         //是否为长连接
 
];