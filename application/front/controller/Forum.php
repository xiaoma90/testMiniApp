<?php
namespace app\front\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;
class Forum extends Controller
{
    public function index(){
    	$uniacid = input("uniacid");
    	$mp = Db::name('wd_xcx_applet')->where("id", $uniacid)->field("name,banner,pc_show_qrcode")->find();
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

    	$types = Db::name("wd_xcx_forum_func")->where("uniacid", $uniacid)->where("status", 1)->field("id,title")->select();
        $this->assign("types", $types);

        $typeselected = input("ts");
        if(empty($typeselected) && count($types) > 0){
            $typeselected = $types[0]['id'];
        }
        $this->assign("typeselected", $typeselected);

        $rel = Db::name("wd_xcx_forum_release")->where("uniacid", $uniacid)->where("fid", $typeselected)->order("id desc")->paginate(2,false,[ 'query' => array('uniacid'=>input("uniacid"), 'ts'=>$typeselected)]);
        $release = $rel->toArray();
        $release = $release['data'];
        foreach ($release as $key => &$value) {
            $info = $this->getNickname($value['suid'], $uniacid);
            $value['nickname'] = $info['nickname'];
            $value['avatar'] = $info['avatar'];
            $value['img'] = unserialize($value['img']);
            if($value['img']){
                foreach ($value['img'] as $item) {
                    if (!stristr($item, 'http')){
                        $item = remote($uniacid, $item, 1);
                        if(stristr($item, 'https')){
                            $item = str_replace("https", "http", $item);
                        }
                    }
                }
            }
            $value['comments_num'] = Db::name("wd_xcx_forum_comment")->where("uniacid", $uniacid)->where("rid", $value['id'])->where("flag", 1)->count();
            $value['comments'] = Db::name("wd_xcx_forum_comment")->where("uniacid", $uniacid)->where("rid", $value['id'])->where("flag", 1)->order("likesNum desc")->limit(2)->select();
            foreach ($value['comments'] as $k => &$v) {
                $uinfo = $this->getNickname($v['suid'], $uniacid);
                $v['nickname'] = $uinfo['nickname'];
                $v['avatar'] = $uinfo['avatar'];
            }
        }
        $count = Db::name("wd_xcx_forum_release")->where("uniacid", $uniacid)->where("fid", $typeselected)->count();
        $this->assign("rel", $rel);
        $this->assign("release", $release);
        //var_dump($release);
        $this->assign("count", $count);

        $stick_num = Db::name("wd_xcx_forum_release")->where("uniacid", $uniacid)->where("stick", 1)->count();
        if($stick_num == 0){
            $hot = Db::name("wd_xcx_forum_release")->where("uniacid", $uniacid)->where("hot", 1)->order("likes desc")->limit(2)->select();
        }else{
            $hot = Db::name("wd_xcx_forum_release")->where("uniacid", $uniacid)->where("stick", 1)->order("likes desc")->limit(2)->select();
        }
        foreach ($hot as $key => &$value) {
            $info = $this->getNickname($value['suid'], $uniacid);
            $value['nickname'] = $info['nickname'];
            $value['avatar'] = $info['avatar'];
            $value['img'] = unserialize($value['img']);
            if($value['img']){
                foreach ($value['img'] as $item) {
                    if (!stristr($item, 'http')){
                        $item = remote($uniacid, $item, 1);
                        if(stristr($item, 'https')){
                            $item = str_replace("https", "http", $item);
                        }
                    }
                }
            }
        }
        $this->assign("hot", $hot);

        $pc_style = Db::name('wd_xcx_applet')->where("id", $uniacid)->value("pc_style");
        $pc_style = $pc_style ? $pc_style : 1;
        if($pc_style == 1)
            return $this->fetch('forum');
        else
            return $this->fetch('forum2');
    }
    
    private function getNickname($suid, $uniacid){
        $user = Db::name("wd_xcx_user")->where('suid', $suid)->where('uniacid', $uniacid)->field('nickname, avatar')->find();
        $nickname = $user['nickname'];
        $avatar = $user['avatar'];
        if(empty($nickname) && empty($avatar)){
            $user = Db::name('wd_xcx_ali_user')->where('suid', $suid)->where('uniacid', $uniacid)->field('nick_name, avatar')->find();
            $nickname = $user['nick_name'];
            $avatar = $user['avatar'];
        }
        if(empty($nickname) && empty($avatar)){
            $nickname = Db::name('wd_xcx_superuser')->where('id', $suid)->where('uniacid', $uniacid)->value('phone');
            $avatar = "";
        }
        $info = array(
            'nickname' => rawurldecode($nickname),
            'avatar' => $avatar
        );
        return $info;
    }
}