<?php
namespace app\front\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;
class News extends Controller
{
    public function index(){
    	$uniacid = input("uniacid");
    	$mp = Db::name('wd_xcx_applet')->where("id", $uniacid)->field("name,banner")->find();
    	$mp['banner'] = unserialize($mp['banner']);
    	foreach ($mp['banner'] as $item) {
    		if (!stristr($item, 'http')){
    			$item = remote($uniacid, $item, 1);
    			if(stristr($item, 'https')){
    				$item = str_replace("https", "http", $item);
    			}
    		}
    	}
    	$this->assign("mpname", $mp['name']);
    	$this->assign("images", $mp['banner']);

    	$topThree = Db::name("wd_xcx_products")->alias("p")->join("wd_xcx_cate c", "p.cid = c.id and p.uniacid = c.uniacid")->where("p.uniacid", $uniacid)->where("c.to_pc_index", 1)->where("c.type", "showArt")->order("p.id", "desc")->limit(3)->field("p.id,p.title,p.thumb,p.desc")->select();
    	$others = Db::name("wd_xcx_products")->alias("p")->join("wd_xcx_cate c", "p.cid = c.id and p.uniacid = c.uniacid")->where("p.uniacid", $uniacid)->where("c.to_pc_index", 1)->where("c.type", "showArt")->order("p.id", "desc")->limit(3,10)->field("p.id,p.title,p.desc,p.edittime")->select();

    	foreach ($topThree as $key => &$value) {
    		if (!stristr($value['thumb'], 'http')){
    			$value['thumb'] = remote($uniacid, $value['thumb'], 1);
    			if(stristr($value['thumb'], 'https')){
    				$value['thumb'] = str_replace("https", "http", $value['thumb']);
    			}
    		}
    	}
    	foreach ($others as $key => &$value) {
    		$edittime = $value['edittime'];
    		$value['year'] = date("Y", $edittime);
    		$value['month'] = date("m", $edittime);
    		$value['day'] = date("d", $edittime);
    	}
    	
    	$this->assign("lastone", count($others));
    	$this->assign("topThree", $topThree);
    	$this->assign("others", $others);
        
        $pc_style = Db::name('wd_xcx_applet')->where("id", $uniacid)->value("pc_style");
        $pc_style = $pc_style ? $pc_style : 1;
        if($pc_style == 1)
            return $this->fetch('index');
        else
            return $this->fetch('index2');
    }
    
}