<?php
namespace app\front\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;

class Searchs extends Controller
{
    public function index(){
    	$uniacid = input("uniacid");
        $copyright = Db::name("wd_xcx_base")->where("uniacid", $uniacid)->value('copyright');
        $this->assign("copyright", $copyright);
    	$mp = Db::name('wd_xcx_applet')->where("id", $uniacid)->field("name,banner,type, pc_show_qrcode,pc_logo,site_title,site_keywords,site_description")->find();
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
        $title = input('title');
        $where['title'] = ['like',"%".$title."%"];

        $pc_style = Db::name('wd_xcx_applet')->where("id", $uniacid)->value("pc_style");
        $pc_style = $pc_style ? $pc_style : 1;
        if($pc_style == 1){
            $cates = Db::name("wd_xcx_cate")->where("uniacid", $uniacid)->where('type', 'showpro')->where('cid', 0)->where('to_pc_index', 1)->field('id,name')->order('num desc, id desc')->select();
            foreach ($cates as $k => &$v) {
                $v['subcates'] = Db::name("wd_xcx_cate")->where("uniacid", $uniacid)->where('type', 'showpro')->where('cid', $v['id'])->field('id,name')->order('num desc, id desc')->select();
            }
            $this->assign("cates", $cates);

            
            $pros = Db::name("wd_xcx_products")->where("uniacid", $uniacid)->where('type', 'showProMore')->where($where)->field('id,title,thumb,price,sale_num,sale_tnum')->order('num desc, id desc')->paginate(16, false, ['query' => ['uniacid' => $uniacid, 'title' => $title]]);
            $prolist = $pros->toArray()['data'];
            foreach ($prolist as $key => &$value) {
                $value['sale_num'] = $value['sale_num'] + $value['sale_tnum'];
                $value['thumb'] = remote($uniacid, $value['thumb'], 1);
            }
            $this->assign("pros", $pros);
            $this->assign("prolist", $prolist);
        	return $this->fetch('index');
        }else if($pc_style == 2){
            //获取年
            $now_year = date('Y',time());
            $this->assign("now_year", $now_year);

            $cid = input("cid");
            $cates = Db::name("wd_xcx_cate")->where("uniacid", $uniacid)->where("type = 'showArt' OR type = 'showPic'")->where('cid', 0)->where('to_pc_index', 1)->field('id,name,type')->order('num desc, id desc')->limit(9)->select();
            foreach ($cates as $k => &$v) {
                $v['subcates'] = Db::name("wd_xcx_cate")->where("uniacid", $uniacid)->where('cid', $v['id'])->field('id,name,type')->order('num desc, id desc')->select();
            }
            $this->assign("cates", $cates);

            $now_cid = -1; 
            $this->assign("now_cid", $now_cid);


            $news_type = input('type') ? input('type') : 'showArt';
            $art_num = Db::name("wd_xcx_products")->where("uniacid", $uniacid)->where('type', 'showArt')->where($where)->count();
            $pic_num = Db::name("wd_xcx_products")->where("uniacid", $uniacid)->where('type', 'showPic')->where($where)->count();
            $this->assign("art_num", $art_num);
            $this->assign("pic_num", $pic_num);
            $this->assign("title", $title);
            if($news_type == 'showArt'){
                $art_list = Db::name("wd_xcx_products")->where("uniacid", $uniacid)->where('type', 'showArt')->where($where)->order('id desc')->field('id, title, thumb, ctime, hits, desc')->paginate(10, false, ['query' => ['uniacid' => $uniacid, 'title' => $title, 'type' => $news_type]]);
                $art_lists = $art_list -> toArray()['data'];
                foreach ($art_lists as $ksi => &$vsi) {
                    $vsi['thumb'] = remote($uniacid, $vsi['thumb'], 1);
                    $vsi['ctime'] = date('Y-m-d H:i:s', $vsi['ctime']);
                    $vsi['hits'] = intval($vsi['hits']);
                    $vsi['desc'] = $vsi['desc'] ? $vsi['desc'] : '';
                }
                $this->assign("art_list", $art_list);
                $this->assign("art_lists", $art_lists);
                return $this->fetch('searchart');
            }else{
                $pic_list = Db::name("wd_xcx_products")->where("uniacid", $uniacid)->where('type', 'showPic')->where($where)->order('id desc')->field('id, thumb, text, title')->paginate(12, false, ['query' => ['uniacid' => $uniacid, 'title' => $title, 'type' => $news_type]]);
                $pic_lists = $pic_list -> toArray()['data'];
                foreach ($pic_lists as $ksi1 => &$vsi1) {
                    $vsi1['thumb'] = remote($uniacid, $vsi1['thumb'], 1);
                    $vsi1['num'] = 0;
                    if($vsi1['text']){
                        $vsi1['text'] = unserialize($vsi1['text']);
                        $vsi1['num'] = count($vsi1['text']);
                    }
                }
                $this->assign("pic_list", $pic_list);
                $this->assign("pic_lists", $pic_lists);
                return $this->fetch('searchpic');
            }
        }
    }
}