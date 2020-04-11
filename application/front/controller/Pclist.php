<?php
namespace app\front\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;

class Pclist extends Controller
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

        $cid = input("cid");
        $now_cid = 0; 

        $pc_style = Db::name('wd_xcx_applet')->where("id", $uniacid)->value("pc_style");
        $pc_style = $pc_style ? $pc_style : 1;
        if($pc_style == 1){
            $cates = Db::name("wd_xcx_cate")->where("uniacid", $uniacid)->where('type', 'showpro')->where('cid', 0)->where('to_pc_index', 1)->field('id,name')->order('num desc, id desc')->select();
            foreach ($cates as $k => &$v) {
                $v['subcates'] = Db::name("wd_xcx_cate")->where("uniacid", $uniacid)->where('type', 'showpro')->where('cid', $v['id'])->field('id,name')->order('num desc, id desc')->select();
            }
            $this->assign("cates", $cates);
            $this->assign("uniacid", $uniacid);
            $cid_now = Db::name("wd_xcx_cate")->where('uniacid', $uniacid)->where('id', $cid)->value('cid');
            if($cid_now == 0) { //一级栏目
                $topcate = Db::name("wd_xcx_cate")->where('uniacid', $uniacid)->where('id', $cid)->field('id, name')->find();
                $subcates = Db::name("wd_xcx_cate")->where("uniacid", $uniacid)->where('type', 'showpro')->where('cid', $topcate['id'])->field('id,name')->order('num desc, id desc')->select();
                $where['pcid'] = $cid;

            }else{
                $topcate = Db::name("wd_xcx_cate")->where('uniacid', $uniacid)->where('id', $cid_now)->field('id, name')->find();
                $subcates = Db::name("wd_xcx_cate")->where("uniacid", $uniacid)->where('type', 'showpro')->where('cid', $cid_now)->field('id,name')->order('num desc, id desc')->select();
                $where['cid'] = $cid;
            }

            $pros = Db::name("wd_xcx_products")->where("uniacid", $uniacid)->where('type', 'showProMore')->where($where)->field('id,title,thumb,price,sale_num,sale_tnum')->order('num desc, id desc')->paginate(16, false, ['query' => ['uniacid' => $uniacid, 'cid' => $cid]]);
            $prolist = $pros->toArray()['data'];
            foreach ($prolist as $key => &$value) {
                $value['sale_num'] = $value['sale_num'] + $value['sale_tnum'];
                $value['thumb'] = remote($uniacid, $value['thumb'], 1);
            }
            $this->assign("pros", $pros);
            $this->assign("prolist", $prolist);
            $this->assign("cid", $cid);
            $this->assign("topcate", $topcate);
            $this->assign("subcates", $subcates);

        	return $this->fetch('pclist');
        }else if($pc_style == 2){
            //获取年
            $now_year = date('Y',time());
            $this->assign("now_year", $now_year);

            $type = input("type"); //栏目类型 showArt 或 showPic

            $cates = Db::name("wd_xcx_cate")->where("uniacid", $uniacid)->where("type = 'showArt' OR type = 'showPic'")->where('cid', 0)->where('to_pc_index', 1)->field('id,name,type')->order('num desc, id desc')->limit(9)->select();
            foreach ($cates as $k => &$v) {
                $v['subcates'] = Db::name("wd_xcx_cate")->where("uniacid", $uniacid)->where('cid', $v['id'])->field('id,name,type')->order('num desc, id desc')->select();
            }
            $this->assign("cates", $cates);

            $cid_now = Db::name("wd_xcx_cate")->where('uniacid', $uniacid)->where('id', $cid)->value('cid');
            if($cid_now == 0) { //一级栏目
                $where['pcid'] = $cid;
                $now_cid = $cid;
            }else{
                $where['cid'] = $cid;
                $now_cid = $cid_now;
            }

            $pros = Db::name("wd_xcx_products")->where("uniacid", $uniacid)->where("type", $type)->where($where)->field('id,title,ctime,desc,type')->order('id desc')->paginate(10, false, ['query' => ['uniacid' => $uniacid, 'cid' => $cid, 'type' => $type]]);
            $prolist = $pros->toArray()['data'];
            foreach ($prolist as &$vvs) {
                $vvs['day'] = date('d', $vvs['ctime']);
                $vvs['ctime'] = date('Y', $vvs['ctime'])."/".date('m', $vvs['ctime']);
            }
            $this->assign("now_cid", $now_cid);
            $this->assign("pros", $pros);
            $this->assign("prolist", $prolist);
            $this->assign("cid", $cid);
            $this->assign("uniacid", $uniacid);
            return $this->fetch('pclist1');
        }
    }
}