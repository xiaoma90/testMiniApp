<?php
namespace app\front\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;
class NewsDetail extends Controller
{
    public function index(){
    	$uniacid = input("uniacid");
    	$nid = input("nid");
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

    	$news = Db::name("wd_xcx_products")->where("uniacid", $uniacid)->where("id", $nid)->find();
    	$news['edittime'] = date("Y-m-d H:i:s", $news['edittime']);
    	
    	$this->assign("news", $news);
        
    	//$topThree = Db::name("wd_xcx_products")->alias("p")->join("wd_xcx_cate c", "p.cid = c.id and p.uniacid = c.uniacid")->where("p.uniacid", $uniacid)->where("c.to_pc_index", 1)->where("c.type", "showArt")->order("p.id", "desc")->limit(3)->field("p.id,p.title,p.thumb,p.desc")->select();
        
    	$lastone = Db::name("wd_xcx_products")->alias("p")->join("wd_xcx_cate c", "p.cid = c.id and p.uniacid = c.uniacid")->where("p.uniacid", $uniacid)->where("c.to_pc_index", 1)->
    			where("c.type", "showArt")->where("p.id", "<", $nid)->order("edittime", "desc")->field("p.id,p.title")->find();
    	$nextone = Db::name("wd_xcx_products")->alias("p")->join("wd_xcx_cate c", "p.cid = c.id and p.uniacid = c.uniacid")->where("p.uniacid", $uniacid)->where("c.to_pc_index", 1)->
    			where("c.type", "showArt")->where("p.id", ">", $nid)->order("edittime", "asc")->field("p.id,p.title")->find();

    	$this->assign("lastone", $lastone);
    	$this->assign("nextone", $nextone);

    	$three = Db::name("wd_xcx_products")->alias("p")->join("wd_xcx_cate c", "p.cid = c.id and p.uniacid = c.uniacid")->where("p.uniacid", $uniacid)->where("c.to_pc_index", 1)->
    			where("c.type", "showArt")->where("p.id", "neq", $nid)->order("p.edittime desc")->limit(3)->field("p.id,p.title,p.edittime")->select();
    	foreach ($three as $key => &$value) {
    		$value['edittime'] = date("Y-m-d H:i:s", $value['edittime']);
    	}
    	$this->assign("three", $three);

        $pc_style = Db::name('wd_xcx_applet')->where("id", $uniacid)->value("pc_style");
        $pc_style = $pc_style ? $pc_style : 1;
        if($pc_style == 1)
            return $this->fetch('news_detail');
        else
            return $this->fetch('news_detail2');
    }
    
}