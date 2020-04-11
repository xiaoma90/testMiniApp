<?php

/**

 * Created by PhpStorm.

 * User: Administrator

 * Date: 2018/8/20

 * Time: 14:36

 */
namespace app\index\controller;
use think\Controller;

use think\Db;

use think\Request;

use think\Session;

use think\View;



class Log extends Controller

{

    //日志展示首页

    public function index(){

        if(check_login()){

            if(check_group()){

                //获取所有日志记录

                if(input('show') ){

                    if(input('show')==1){

                        $res = Db::name('wd_xcx_log') ->alias('a') ->join('wd_xcx_admin b', 'a.admin = b.uid')->where('a.type', 'eq',0) ->field('a.*, b.realname') ->order('id', 'desc')->paginate(10,false,['query' => request()->param()]);

                        $count = Db::name('wd_xcx_log')->where('type','eq', 0) ->field('a.*, b.realname') ->count();

                    }else{

                        $res = Db::name('wd_xcx_log') ->alias('a') ->join('wd_xcx_admin b', 'a.admin = b.uid')->where('a.type', 'eq',1) ->field('a.*, b.realname') ->order('id', 'desc')->paginate(10,false,['query' => request()->param()]);

                        $count = Db::name('wd_xcx_log')->where('type','eq', 1) ->field('a.*, b.realname') ->count();

                    }

                }else{

                    $res = Db::name('wd_xcx_log') ->alias('a') ->join('wd_xcx_admin b', 'a.admin = b.uid') ->field('a.*, b.realname') ->order('id', 'desc')->paginate(10);

                    $count = Db::name('wd_xcx_log') ->count();

                }

                $page = $res ->render();

                $this ->assign('log', $res);

                $this ->assign('count', $count);

                $this ->assign('page', $page);

                return $this ->fetch('index');

            }

        }else{

            $this->redirect('Login/index');

        }



    }



}