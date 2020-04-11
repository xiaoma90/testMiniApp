<?php
namespace app\front\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;
class Hot extends Controller
{
    public function hot(){
    	$uniacid = input("uniacid");
    	$mp = Db::name('wd_xcx_applet')->where("id", $uniacid)->field("name,banner,type,pc_show_qrcode,site_title,site_keywords,site_description")->find();
        $type = unserialize($mp['type']);
        if(!in_array(3, $type)){
            $this->error('对不起, 您没有使用该PC网站的权限!');
        }
    	$mp['banner'] = unserialize($mp['banner']);
    	foreach ($mp['banner'] as $item) {
    		if (!stristr($item, 'http')){
    			$item = remote($uniacid, $item, 1);
    			if(stristr($item, 'https')){
    				$item = str_replace("https", "http", $item);
    			}
    		}
    	}

        if($mp['pc_show_qrcode']){
            $showimg = $mp['pc_show_qrcode'];
        }else{
            $showimg = '';
        }
        $this->assign('showimg', $showimg);
    	$this->assign("mpname", $mp['name']);
    	$this->assign("images", $mp['banner']);

    	$cates = Db::name("wd_xcx_cate")->where("uniacid", $uniacid)->where("type", "showPro")->where("to_pc_index", 1)->field("id,name")->select();

    	$products = Db::name("wd_xcx_products")->alias("p")->join("wd_xcx_cate c", "p.cid = c.id and p.uniacid = c.uniacid")->where("p.uniacid", $uniacid)->where("c.to_pc_index", 1)->where("c.type", "showPro")->order("p.id", "desc")->limit(30)->field("p.id,p.title,p.thumb")->select();
    	
    	foreach ($products as $key => &$value) {
    		if (!stristr($value['thumb'], 'http')){
    			$value['thumb'] = remote($uniacid, $value['thumb'], 1);
    			if(stristr($value['thumb'], 'https')){
    				$value['thumb'] = str_replace("https", "http", $value['thumb']);
    			}
    		}
    	}
    	$this->assign("cates", $cates);
    	$this->assign("products", $products);

        $pc_style = Db::name('wd_xcx_applet')->where("id", $uniacid)->value("pc_style");
        $pc_style = $pc_style ? $pc_style : 1;
        if($pc_style == 1)
            return $this->fetch('hot');
        else
            return $this->fetch('hot2');
    }

    public function changePro(){
    	$cate = input("cate", 0);
    	$uniacid = input("uniacid");
    	if($cate == 0){
    		$products = Db::name("wd_xcx_products")->alias("p")->join("wd_xcx_cate c", "p.cid = c.id and p.uniacid = c.uniacid")->where("p.uniacid", $uniacid)->where("c.to_pc_index", 1)->where("c.type", "showPro")->order("p.id", "desc")->limit(6)->field("p.id,p.title,p.thumb")->select();
    	}else{
    		$products = Db::name("wd_xcx_products")->where("uniacid", $uniacid)->where("cid", $cate)->order("id", "desc")->limit(30)->field("id,title,thumb")->select();
    	}

    	foreach ($products as $key => &$value) {
    		if (!stristr($value['thumb'], 'http')){
    			$value['thumb'] = remote($uniacid, $value['thumb'], 1);
    			if(stristr($value['thumb'], 'https')){
    				$value['thumb'] = str_replace("https", "http", $value['thumb']);
    			}
    		}
    	}

    	return $products;
    }

    public function getPro(){
    	$pid = input("pid");
    	$uniacid = input("uniacid");

    	$product = Db::name("wd_xcx_products")->where("uniacid", $uniacid)->where("id", $pid)->field("id,title,thumb,price,desc,text")->find();
    	$product['text'] = unserialize($product['text']);
    	
    	if (!stristr($product['thumb'], 'http')){
			$product['thumb'] = remote($uniacid, $product['thumb'], 1);
			if(stristr($product['thumb'], 'https')){
				$product['thumb'] = str_replace("https", "http", $product['thumb']);
			}
		}
    	foreach ($product['text'] as $key => &$value) {
    		if (!stristr($value, 'http')){
    			$value = remote($uniacid, $value, 1);
    			if(stristr($value, 'https')){
    				$value = str_replace("https", "http", $value);
    			}
    		}
    	}

    	return $product;
    }
    
}