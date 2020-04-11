<?php
namespace app\front\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;
class Pcdetail extends Controller
{
    public function index(){
        $uniacid = input("uniacid");
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

        $type = input('type');
        $id = input('id');
        $now_cid = 0; 
        $cid = input("cid");

        $pc_style = Db::name('wd_xcx_applet')->where("id", $uniacid)->value("pc_style");
        $pc_style = $pc_style ? $pc_style : 1;
        if($pc_style == 1){
            
        }else if($pc_style == 2){
            //获取年
            $now_year = date('Y',time());
            $this->assign("now_year", $now_year);

            $cates = Db::name("wd_xcx_cate")->where("uniacid", $uniacid)->where("type = 'showArt' OR type = 'showPic'")->where('cid', 0)->where('to_pc_index', 1)->field('id,name,type')->order('num desc, id desc')->limit(9)->select();
            foreach ($cates as $k => &$v) {
                $v['subcates'] = Db::name("wd_xcx_cate")->where("uniacid", $uniacid)->where('cid', $v['id'])->field('id,name,type')->order('num desc, id desc')->select();
            }
            $this->assign("cates", $cates);

            if($type == 'showArt'){
                $info = Db::name('wd_xcx_products')->where('uniacid', $uniacid)->where('id', $id)->find();
                $info['ctime'] = $info['etime'] ? date("Y-m-d H:i:s", $info['etime']) : date("Y-m-d H:i:s", $info['ctime']);
                $info_prev = Db::name('wd_xcx_products')->where('uniacid', $uniacid)->where('type', 'showArt')->where('id', 'lt', $id)->order('id desc')->field('id,title,type')->find();
                $info_next = Db::name('wd_xcx_products')->where('uniacid', $uniacid)->where('type', 'showArt')->where('id', 'gt', $id)->order('id asc')->field('id,title,type')->find();
            }

            $info_new = Db::name('wd_xcx_products')->where('uniacid', $uniacid)->where('type', 'showArt')->order('id desc')->limit(3)->field('id,ctime,etime,title,type')->select();
            foreach ($info_new as $key => &$value) {
                $value['ctime'] = $value['etime'] ? date('Y-m-d H:i:s', $value['etime']) : date('Y-m-d H:i:s', $value['ctime']);
            }

            $cid_now = Db::name("wd_xcx_cate")->where('uniacid', $uniacid)->where('id', $cid)->value('cid');
            if($cid_now == 0) { //一级栏目
                $now_cid = $cid;
            }else{
                $now_cid = $cid_now;
            }

            $this->assign("now_cid", $now_cid);
            $this->assign("info", $info);
            $this->assign("info_prev", $info_prev);
            $this->assign("info_next", $info_next);
            $this->assign("info_new", $info_new);

            return $this->fetch('pcdetail1');
        }
    }
    
}