<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;
class Forum extends Controller
{
    public function func(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $listV = Db::name('wd_xcx_forum_func')->where("uniacid",$appletid)->order('num desc')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                $counts = Db::name('wd_xcx_forum_func')->where("uniacid",$appletid)->order('num desc')->count();
                $list = $listV->toArray()['data'];
                foreach ($list as $key => &$value) {
                    $value['func_img'] = remote($appletid,$value['func_img'],1);
                }
 
                $this->assign('func',$list);
                $this->assign('listV',$listV);
                $this->assign('counts',$counts);
            }else{
                $usergroup = Session::get('usergroup');
                if($usergroup==1){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/applet');
                }
                if($usergroup==2){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/index');
                }
                if($usergroup==3){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/index');
                }
                
            }
            return $this->fetch('func');
        }else{
            $this->redirect('Login/index');
        }
        
    }
    public function funcAdd(){
        if(check_login()){
            if(powerget()){
                $id = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $func_id = input("func_id");
                $func = Db::name('wd_xcx_forum_func')->where('id',$func_id)->where('uniacid',$id)->find();
                if($func){
                    $func['func_img'] = remote($id,$func['func_img'],1);
                }
                $this->assign("func",$func);
                $this->assign("func_id",$func_id);
            }else{
                $usergroup = Session::get('usergroup');
                if($usergroup==1){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/applet');
                }
                if($usergroup==2){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/index');
                }
                if($usergroup==3){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/index');
                }
                
            }
            return $this->fetch('funcAdd');
        }else{
            $this->redirect('Login/index');
        }
        
    }
    public function funcSave(){
        $data = array();
        //小程序ID
        $data['uniacid'] = input("appletid");
        //排序
        $num = input("num");
        if($num){
            $data['num'] = $num;
        }
        //启用
        $status = input("status");
        if($status === null){
            $data['status'] = 1;
        }else{
            $data['status'] = $status;
        }
        //功能名称
        $data['title'] = input("title");
        //功能缩略图
        $func_img = input("commonuploadpic");
        if($func_img){
            $data['func_img'] = remote($data['uniacid'],$func_img,2);
        }
        //功能名称
        $data['title'] = input("title");
        
        //功能id
        $func_id = input("func_id");
        $data['page_type'] = input("page_type");
        $data['createtime'] = date("Y-m-d H:i:s",time());
        if($func_id > 0){
            $res = Db::name('wd_xcx_forum_func')->where("id",$func_id)->update($data);
        }else{
            
            $res = Db::name('wd_xcx_forum_func')->insert($data);
        }
        if($res){
          $this->success('功能信息更新成功！',Url('Forum/func').'?appletid='.$data['uniacid']);
        }else{
          $this->error('功能信息更新失败，没有修改项！');
          exit;
        }
    }
    //检测标题
    public function checktitle(){
        $title = input('title');
        $uniacid = input('uniacid');
        $func_id = input('func_id');
        if($func_id){
            $is = Db::name('wd_xcx_forum_func')->where("title",$title)->where("uniacid",$uniacid) ->where('id', 'neq', $func_id)->find();
        }else{
            $is = Db::name('wd_xcx_forum_func')->where("title",$title)->where("uniacid",$uniacid)->find();
        }
        
        if($is){
            echo 1;
        }else{
            echo 2;
        }
    }
    // 删除功能操作
    public function funcDel(){
        $func_id = input("func_id");
        $res = Db::name('wd_xcx_forum_func')->where('id',$func_id)->delete();
        if($res){
            $this->success('删除成功');
        }else{
            $this->success('删除失败');
        }
    }
    
    public function releaseShenhe(){
        $appletid = input("appletid");
        $id = input("id");
        $flag = input("flag");

        $res = Db::name('wd_xcx_forum_release')->where("uniacid",$appletid)->where("id",$id)->update(array("shenhe" => $flag));
        if($res){
            $this->success('审核操作成功');
        }else{
            $this->success('审核操作失败');
        }
    }
    
    //论坛相关设置  如：发布价格，置顶每日价格
    public function set(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $set = Db::name('wd_xcx_forum_set')->where("uniacid",$appletid)->find();
                $this->assign('set',$set);
            }else{
                $usergroup = Session::get('usergroup');
                if($usergroup==1){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/applet');
                }
                if($usergroup==2){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/index');
                }
                if($usergroup==3){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/index');
                }
                
            }
            return $this->fetch('set');
        }else{
            $this->redirect('Login/index');
        }
    }
    //论坛相关设置  如：发布价格，置顶每日价格
    public function setsave(){
        $appletid = input("appletid");
        $release_money = input("release_money");
        $stick_money = input("stick_money");
        $release_audit = input("release_audit");
        $settop = input("settop");
        $data = array(
            "release_money" => $release_money,
            "stick_money" => $stick_money,
            "release_audit" => $release_audit,
            "settop" => $settop
            );
        $you = Db::name("wd_xcx_forum_set")->where("uniacid",$appletid)->find();
        if($you){
            $res = Db::name("wd_xcx_forum_set")->where("uniacid",$appletid)->update($data);
        }else{
            $data['uniacid'] = $appletid;
            $res = Db::name("wd_xcx_forum_set")->insert($data);
        }
        if ($res) {
            $this->success("设置修改成功");
        }else{
            $this->error("设置修改失败");
        }
    }
    //发布管理
    public function release(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $release = Db::name('wd_xcx_forum_release')->alias("a")->join("wd_xcx_forum_func c","a.fid = c.id")->where("a.uniacid",$appletid)->field("a.*,c.title as func_title")->order("a.hot asc, a.stick asc, a.id desc")->paginate(10,false,['query' => array('appletid' => input("appletid"))]);
                $counts = Db::name('wd_xcx_forum_release')->alias("a")->join("wd_xcx_forum_func c","a.fid = c.id")->where("a.uniacid",$appletid)->count();
                $releaseList = $release->toArray()['data'];
                foreach ($releaseList as $key => &$value) {
                    if($value['stick'] == 1){
                        $is = Db::name("wd_xcx_forum_stick")->where('uniacid', $appletid)->where('rid', $value['id'])->where('stick', 1)->where('stick_status', 1)->find();
                        $overtime = strtotime($is['stick_time']) + $is['stick_days'] * 24 * 3600;
                        if($overtime <= time()){
                            Db::name("wd_xcx_forum_stick")->where('uniacid', $appletid)->where('rid', $value['id'])->where('stick_status', 1)->update(array('stick_status' => 2));
                            Db::name("wd_xcx_forum_release")->where('uniacid', $appletid)->where('id', $value['id'])->where('stick', 1)->update(array('stick' => 2));
                            $value['stick'] = 2;
                        }
                        
                    }
                    
                    $info = getNameAvatar($value['suid'], $appletid);
                    $releaseList[$key]['nickname'] = $info['nickname'];
                    $releaseList[$key]['avatar'] = $info['avatar'];
                }
                $this->assign('release',$release);
                $this->assign('counts',$counts);
                $this->assign('releaseList',$releaseList);
            }else{
                $usergroup = Session::get('usergroup');
                if($usergroup==1){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/applet');
                }
                if($usergroup==2){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/index');
                }
                if($usergroup==3){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/index');
                }
                
            }
            return $this->fetch('release');
        }else{
            $this->redirect('Login/index');
        }
    }
    // 发布详情查看
    public function releaseCon(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $release_id = input("release_id");
                $release = Db::name('wd_xcx_forum_release')->alias("a")->join("wd_xcx_forum_func c","a.fid = c.id")->where("a.uniacid",$appletid)->where("a.id",$release_id)->field("a.*,c.title as func_title")->find();
                if($release){
                    if($release['stick'] == 1){
                        $is = Db::name("wd_xcx_forum_stick")->where('uniacid', $appletid)->where('rid', $release['id'])->where('stick', 1)->where('stick_status', 1)->find();
                        $overtime = strtotime($is['stick_time']) + $is['stick_days'] * 24 * 3600;
                        if($overtime <= time()){
                            Db::name("wd_xcx_forum_stick")->where('uniacid', $appletid)->where('rid', $release['id'])->where('stick_status', 1)->update(array('stick_status' => 2));
                            Db::name("wd_xcx_forum_release")->where('uniacid', $appletid)->where('id', $release['id'])->where('stick', 1)->update(array('stick' => 2));
                            $release['stick'] = 2;
                        }
                    }
                    
                    $release['stickall'] = Db::name('wd_xcx_forum_stick')->where('rid', $release['id'])->where('stick',1)->order('id desc')->select();
                    $release['set'] = Db::name('wd_xcx_forum_set')->where('uniacid', $appletid)->find();
                    if($release['stickall']){
                        foreach ($release['stickall'] as $k => &$vi) {
                            $vi['moneyAll'] = $vi['stick_money'] * $vi['stick_days'];
                        }
                    }else{
                        $release['moneyAll'] = 0;
                    }
                    if($release['img']){
                        $release['img'] = unserialize($release['img']);
                        if($release['img']){
                            foreach ($release['img'] as $key => $value) {
                                $release['img'][$key] = remote($appletid,$value,1);
                            }
                        }
                    }
                    $updatetime = strtotime($release['updatetime']);
                    if($updatetime < 0){
                        $release['updatetime'] = "";
                    }
                    $info = getNameAvatar($release['suid'], $appletid);
                    $release['nickname'] = $info['nickname'];
                    $release['avatar'] = $info['avatar'];
                }
                $this->assign('release',$release);
            }else{
                $usergroup = Session::get('usergroup');
                if($usergroup==1){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/applet');
                }
                if($usergroup==2){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/index');
                }
                if($usergroup==3){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/index');
                }
                
            }
            return $this->fetch('releaseCon');
        }else{
            $this->redirect('Login/index');
        }
    }
    //发布推荐与取消
    public function releaseHot(){
        $release_id = input("release_id");
        $is = Db::name('wd_xcx_forum_release')->where('id',$release_id)->find();
        if ($is['hot'] == 1) {
            $res = Db::name('wd_xcx_forum_release')->where('id',$release_id)->update(array("hot" => 2));
        } elseif ($is['hot'] == 2) {
            $res = Db::name('wd_xcx_forum_release')->where('id',$release_id)->update(array("hot" => 1));
        }
        if($res){
            $this->success("操作成功");
        }else{
            $this->error("操作失败");
        }
    }
    // 删除发布操作
    public function releaseDel(){
        $release_id = input("release_id");
        $res = Db::name('wd_xcx_forum_release')->where('id',$release_id)->delete();
        if($res){
            $this->success('删除成功');
        }else{
            $this->success('删除失败');
        }
    }
    //评论列表管理
    public function comment(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $comment = Db::name('wd_xcx_forum_comment')->where("uniacid",$appletid)->order("id desc")->paginate(10,false,['query' => array('appletid' => input("appletid"))]);
                $counts = Db::name('wd_xcx_forum_comment')->where("uniacid",$appletid)->order("id desc")->count();
                $commentList = $comment->toArray()['data'];
                foreach ($commentList as $key => &$value) {
                    $value['reply'] = Db::name("wd_xcx_forum_reply")->where("commentId",$value['id'])->select();
                    $info = getNameAvatar($value['suid'], $appletid);
                    $value['nickname'] = $info['nickname'];
                    $value['avatar'] = $info['avatar'];
                }
                $this->assign('comment',$comment);
                $this->assign('counts',$counts);
                $this->assign('commentList',$commentList);
            }else{
                $usergroup = Session::get('usergroup');
                if($usergroup==1){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/applet');
                }
                if($usergroup==2){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/index');
                }
                if($usergroup==3){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/index');
                }
                
            }
            return $this->fetch('comment');
        }else{
            $this->redirect('Login/index');
        }
    }
    //删除评论
    public function commentDel(){
        $appletid = input("appletid");
        $comment_id = input("comment_id");
        $rid = Db::name("wd_xcx_forum_comment")->where("uniacid",$appletid)->where("id",$comment_id)->find();
        $res = Db::name("wd_xcx_forum_comment")->where("uniacid",$appletid)->where("id",$comment_id)->delete();
        if($res){
            $release_comment = Db::name('wd_xcx_forum_release')->where('uniacid', $appletid)->where('id', $rid['rid'])->find()['comment'];
            $comment = $release_comment - 1;
            Db::name('wd_xcx_forum_release')->where('uniacid', $appletid)->where('id', $rid['rid'])->update(array("comment" => $comment));
            Db::name('wd_xcx_forum_comment')->where('uniacid', $appletid)->where('id', $comment_id)->delete();
            Db::name('wd_xcx_forum_comment_likes')->where('uniacid', $appletid)->where('commentId', $rid['id'])->delete();
            $this->success("删除成功");            
        }else{
            $this->error("删除失败");            
        }
    }
}