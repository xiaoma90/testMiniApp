<?php
namespace app\index\controller;
use think\Controller;
use think\Db;

class Upgrade extends Controller{
	public function run(){
		//2019-08-21   1.02
		$applet_01 = Db::query("select count(*) from information_schema.columns where table_name = 'applet' and column_name = 'site_title'");
        if(!$applet_01[0]['count(*)']){
            Db::query("ALTER table `applet` ADD site_title varchar(255) DEFAULT ''");
        }

        $applet_02 = Db::query("select count(*) from information_schema.columns where table_name = 'applet' and column_name = 'site_keywords'");
        if(!$applet_02[0]['count(*)']){
            Db::query("ALTER table `applet` ADD site_keywords varchar(255) DEFAULT ''");
        }

        $applet_03 = Db::query("select count(*) from information_schema.columns where table_name = 'applet' and column_name = 'site_description'");
        if(!$applet_03[0]['count(*)']){
            Db::query("ALTER table `applet` ADD site_description varchar(255) DEFAULT ''");
        }

        $com_01 = Db::query("select count(*) from information_schema.columns where table_name = 'ims_sudu8_page_com_about' and column_name = 'globalremote'");
        if(!$com_01[0]['count(*)']){
            Db::query("ALTER table `ims_sudu8_page_com_about` ADD globalremote tinyint(2) DEFAULT 1 COMMENT '全局附件 1 未使用  2 七牛  3 阿里云'");
        }

        $base_01 = Db::query("select count(*) from information_schema.columns where table_name = 'ims_sudu8_page_base' and column_name = 'use_remote'");
        if(!$base_01[0]['count(*)']){
            Db::query("ALTER table `ims_sudu8_page_base` ADD use_remote tinyint(2) DEFAULT 1 COMMENT '使用远程附件  1 系统设置  2 自己设置'");
        }

	}

}

(new Upgrade())->run();