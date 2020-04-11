<?php
namespace app\front\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;
class Index extends Controller
{
    public function index(){
    	$uniacid = input("uniacid");
//    	include_once ROOT_PATH.'application/index/controller/Ordinary.php';
//    	$or = new \Ordinary();
//    	$plat = $or ->checkPlat();
//    	if(!$plat['pc']){
//    	    $this ->error('对不起，该插件未开通，请联系管理员');
//        }

        $mp = Db::name('wd_xcx_applet')->where("id", $uniacid)->field("name,banner,type, pc_show_qrcode,pc_logo,site_title,site_keywords,site_description")->find();
        $copyright = Db::name("wd_xcx_base")->where("uniacid", $uniacid)->value('copyright');
        $this->assign("copyright", $copyright);
        $type = unserialize($mp['type']);
        if(!in_array(3, $type)){
            $this->error('对不起, 您没有使用该PC网站的权限!');
        }
        if($mp['pc_logo']){
            $mp['pc_logo'] = remote($uniacid, $mp['pc_logo'], 1);
        }
        $mp['pc_show_qrcode'] = $mp['pc_show_qrcode'] ? unserialize($mp['pc_show_qrcode']) : [];
        $mp['banner'] = $mp['banner'] ? unserialize($mp['banner']) : [];

        foreach ($mp['pc_show_qrcode'] as $ki => &$vi) {
            $vi = $vi ? remote($uniacid, $vi, 1) : '';
        }
        foreach ($mp['banner'] as $ks => &$vs) {
            $vs = $vs ? remote($uniacid, $vs, 1) : '';
        }
        $this->assign("baseinfo", $mp);
        $this->assign("uniacid", $uniacid);

        $now_cid = 0; 
        $cid = input("cid");

        $pc_style = Db::name('wd_xcx_applet')->where("id", $uniacid)->value("pc_style");
        $pc_style = $pc_style ? $pc_style : 1;
        if($pc_style == 1){  //商品模板
            $cates = Db::name("wd_xcx_cate")->where("uniacid", $uniacid)->where('type', 'showpro')->where('cid', 0)->where('to_pc_index', 1)->field('id,name')->order('num desc, id desc')->select();
            foreach ($cates as $k => &$v) {
                $v['subcates'] = Db::name("wd_xcx_cate")->where("uniacid", $uniacid)->where('cid', $v['id'])->field('id,name')->order('num desc, id desc')->select();

                $v['pros'] = Db::name("wd_xcx_products")->where("uniacid", $uniacid)->where('pcid', $v['id'] )->field('id,title,thumb,price,desc')->order('num desc, id desc')->limit(10)->select();
                foreach ($v['pros'] as $key => &$value) {
                    $value['thumb'] = remote($uniacid, $value['thumb'], 1);
                }
            }
        	$this->assign("cates", $cates);
            $hots = Db::name("wd_xcx_products")->where("uniacid", $uniacid)->where('type', 'showProMore')->field('id,title,thumb,price')->order('num desc, id desc')->where('type_i', 1)->select();

            foreach ($hots as $kv => &$vv) {
                $vv['thumb'] = remote($uniacid, $vv['thumb'], 1);
            }
            $this->assign("hots", $hots);
            return $this->fetch('index');
        }else if($pc_style == 2){ //文章组图模板
            //获取年
            $now_year = date('Y',time());
            $this->assign("now_year", $now_year);

            $cates = Db::name("wd_xcx_cate")->where("uniacid", $uniacid)->where("type = 'showArt' OR type = 'showPic'")->where('cid', 0)->where('to_pc_index', 1)->field('id,name,type')->order('num desc, id desc')->limit(9)->select();
            foreach ($cates as $k => &$v) {
                $v['subcates'] = Db::name("wd_xcx_cate")->where("uniacid", $uniacid)->where('cid', $v['id'])->field('id,name,type')->order('num desc, id desc')->select();
            }
            $this->assign("cates", $cates);

            $art_new = Db::name("wd_xcx_products")->where("uniacid", $uniacid)->where('type', 'showArt')->field('id,title,thumb,ctime,etime,type')->order('num desc, id desc')->limit(12)->select();
            foreach ($art_new as $kvi => &$vvi) {
                $vvi['thumb'] = remote($uniacid, $vvi['thumb'], 1);
                $vvi['ctime'] = $vvi['etime'] ? date('Y-m-d', $vvi['etime']) : date('Y-m-d', $vvi['ctime']);

            }

            $art_hots = Db::name("wd_xcx_products")->where("uniacid", $uniacid)->where('type', 'showArt')->field('id,title,thumb,desc,type')->order('num desc, id desc')->where('type_i', 1)->select();
            foreach ($art_hots as $kv => &$vv) {
                $vv['thumb'] = remote($uniacid, $vv['thumb'], 1);
            }

            $pic_hots = Db::name("wd_xcx_products")->where("uniacid", $uniacid)->where('type', 'showPic')->field('id,title,thumb,type')->order('num desc, id desc')->where('type_i', 1)->select();
            foreach ($pic_hots as $kvs => &$vvs) {
                $vvs['thumb'] = remote($uniacid, $vvs['thumb'], 1);
            }

            $cid_now = Db::name("wd_xcx_cate")->where('uniacid', $uniacid)->where('id', $cid)->value('cid');
            if($cid_now == 0) { //一级栏目
                $now_cid = $cid;
            }else{
                $now_cid = $cid_now;
            }

            $this->assign("now_cid", $now_cid);
            $this->assign("art_new", $art_new);
            $this->assign("art_hots", $art_hots);
            $this->assign("pic_hots", $pic_hots);
            return $this->fetch('index1');
        }
    }

    public function proinfo(){
        $uniacid = input('uniacid');
        $id = input('id');
        $proinfo = Db::name('wd_xcx_products')->where('id', $id)->field('title,price,desc,text')->find();
        if($proinfo['text']){
            $proinfo['text'] = unserialize($proinfo['text']);
            if($proinfo['text']){
                foreach ($proinfo['text'] as $key => &$value) {
                    $value = remote($uniacid, $value, 1);
                }
            }
        }
        return $proinfo;
    }
}