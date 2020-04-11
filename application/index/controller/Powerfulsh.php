<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Date: 2018/7/25
 * Time: 10:35
 */
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;

class Powerfulsh extends Base
{

    public function test(){
        $upimages = '(
            "https://four.nttrip.cn/upimages/20190724/7df92f6080f7ab70db27867d9e34d64b257.jpg",
            "https://four.nttrip.cn/upimages/20190724/a612515d24800d628d5301d2d364cea2620.jpg"
        )';

        preg_match_all("/http\S*?.jpg/", $upimages, $matches);
        if($matches){
            $images = serialize($matches[0]);
        }else{
            $images = '';
        }
        dump($images);
    }

    //  展示列表
    public function cate(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $cates = Db::name('wd_xcx_shops_cate')->where('uniacid',$appletid)->order('num desc')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                $count = Db::name('wd_xcx_shops_cate')->where("uniacid",$appletid)->order('num desc')->count();
                $this->assign('cates',$cates);
                $this->assign('counts',$count);
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
            return $this->fetch('cate');
        }else{
            $this->redirect('Login/index');
        }
    }
    // 商品展示 编辑
    public function cateadd(){
        $appletid = input("appletid");
        $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
        if(!$res){
            $this->error("找不到对应的小程序！");
        }
        $this->assign('applet',$res);
        $cateid = intval(input('cateid'));
        $cate = Db::name('wd_xcx_shops_cate')->where("uniacid",$appletid)->where('id',$cateid)->find();
        if(!$cateid){
            $cateid = 0;
        }
        $this->assign('cate',$cate);
        $this->assign('cateid',$cateid);
        return $this->fetch('cateadd');
    }
    //  商品展示  删除
    public function catedel(){
        $appletid = input("appletid");
        $cateid = input("cateid");
        $is = Db::name('wd_xcx_shops_shop')->where("cid", $cateid)->count();
        if($is){
            $this->success('删除失败,店铺分类下还有店铺无法删除');
        }

        $data = array(
            "uniacid"=>$appletid,
            "id"=>$cateid
        );
        $res = Db::name('wd_xcx_shops_cate')->where($data)->delete();
        if($res){
            $this->success('多商户店铺分类删除成功');
        }else{
            $this->success('多商户店铺分类删除失败');
        }
    }
    //商品展示 保存信息
    public function catesave(){
        $data = array();
        //小程序ID
        $uniacid = input("appletid");
        $num = input("num");
        if($num){
            $data['num'] = $num;
        }else{
            $data['num'] = 1;
        }
        $name = input("name");
        if($name){
            $data['name'] = $name;
        }else{
            $this->error('栏目名称不能为空！');
            exit;
        }
        $data['flag'] = input("flag");
        $cateid = intval(input('cateid'));
        if (!$cateid) {
            $data['uniacid'] = $uniacid;
            $res = Db::name('wd_xcx_shops_cate')->insert($data);
        } else {
            $res = Db::name('wd_xcx_shops_cate')->where('id',$cateid)->where('uniacid',$uniacid)->update($data);
        }
        if($res){
            $this->success('分类新增/更新成功!',Url('Powerfulsh/cate').'?appletid='.$uniacid);
        }else{
            $this->error('分类新增/更新更新失败，没有修改项！');
            exit;
        }
    }
    //展示商铺
    public function tenant(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $op = input("op");
                if($op){
                    if($op =='tenantshenhe'){
                        $pid = input("shopid");
                        $res = Db::name('wd_xcx_shops_shop')->where('uniacid',$appletid)->where('id',$pid)->update(array('status'=>1));
                        //发送模板消息
                        
                        if($res){
                            if($res){
                                $this->shenhetongzhi($appletid, $pid, 1);
                                $this->success('商家审核通过');
                            }else{
                                $this->success('商家审核失败');
                            }
                        }
                    }
                    if($op =='tenantcancel'){
                        $pid = input("shopid");
                        $res = Db::name('wd_xcx_shops_shop')->where('uniacid',$appletid)->where('id',$pid)->update(array('status'=>2));
                        if($res){
                            if($res){
                                $this->shenhetongzhi($appletid, $pid, 2);
                                $this->success('商家审核不通过');
                            }else{
                                $this->success('商家审核失败');
                            }
                        }
                    }
                }else{
                    $cid = input('cid');
                    $key = input('key');
                    if(!$cid && !$key){
                        $shops = Db::name('wd_xcx_shops_shop')->where('uniacid',$appletid)->order('num desc,id desc')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                        $count = Db::name('wd_xcx_shops_shop')->where("uniacid",$appletid)->count();
                    }else{
                        if($cid && !$key){
                            $shops = Db::name('wd_xcx_shops_shop')->where('uniacid',$appletid) ->where('cid', $cid)->order('num desc,id desc')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                            $count = Db::name('wd_xcx_shops_shop')->where("uniacid",$appletid) ->where('cid', $cid)->count();
                        }else if($key && !$cid){
                            $shops = Db::name('wd_xcx_shops_shop')->where('uniacid',$appletid) ->where('name', 'LIKE', "%$key%")->order('num desc,id desc')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                            $count = Db::name('wd_xcx_shops_shop')->where("uniacid",$appletid) ->where('name', 'LIKE', "%$key%")->count();
                        }else{
                            $shops = Db::name('wd_xcx_shops_shop')->where('uniacid',$appletid)->where('cid', $cid) ->where('name', 'LIKE', "%$key%")->order('num desc,id desc')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                            $count = Db::name('wd_xcx_shops_shop')->where("uniacid",$appletid)->where('cid', $cid) ->where('name', 'LIKE', "%$key%")->count();
                        }
                    }
                    
                    $shoplist = $shops->all();
                    //获取商品分类
                    $cates = Db::name('wd_xcx_shops_cate') ->where('uniacid', $appletid) ->field('id, name') ->select();
                    $this->assign('cate', $cates);
                    if($shoplist){
                        foreach ($shoplist as $key => &$res) {
                            $cate = Db::name('wd_xcx_shops_cate')->where('uniacid',$appletid)->where('id',$res['cid'])->find();
                            $res['cate'] = $cate['name'];
                        }
                    }
                    
                    $this->assign('shoplist',$shoplist);
                    $this->assign('shops',$shops);
                    $this->assign('counts',$count);
                }
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
            return $this->fetch('tenant');
        }else{
            $this->redirect('Login/index');
        }
    }
    //新增商户
    public function tenantadd(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $shopid = input("shopid");
                $listV = Db::name('wd_xcx_shops_cate')->where("uniacid",$appletid)->order('num desc')->order('id desc')->select();
                $shopinfo = '';
                $now_nickname = '';
                if($shopid){
                    $shopinfo = Db::name('wd_xcx_shops_shop')->where("uniacid",$appletid)->where("id",$shopid)->find();
                    if($shopinfo['images']){
                        $shopinfo['images'] = unserialize($shopinfo['images']);
                        if($shopinfo['images']){
                            foreach ($shopinfo['images'] as $key => $value) {
                                $shopinfo['images'][$key] = remote($appletid,$value,1);
                            }
                        }
                    }
                }else{
                    $shopid = 0;
                }

                //获取所有用户
                $users = Db::name('wd_xcx_superuser') ->where('uniacid', $appletid) ->field('id') ->order('id desc') ->select();
                $shop_users = [];
                foreach ($users as $key => $value) {
                    $info = getNameAvatar($value['id'], $appletid);
                    if($shopinfo){
                        if($shopinfo['suid'] == $value['id']){
                            $now_nickname = $info['nickname'];
                        }
                    }
                    $users[$key]['nickname'] = $info['nickname'];
                    if($info['nickname']){
                        array_push($shop_users, $users[$key]);
                    }
                }
                $this->assign('users', $shop_users);
                $this->assign('now_nickname',$now_nickname);
                $this->assign('shopid',$shopid);
                $this->assign('shopinfo',$shopinfo);
                $this->assign('listAll',$listV);
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
            return $this->fetch('tenantadd');
        }else{
            $this->redirect('Login/index');
        }
    }
    //提交新添加的商户信息
    public function tenantsave(){
        $appletid = input("appletid");
        $shopid = input("shopid");
        $cid = input("cid");
        if(!$cid){
            $this->error('请选择店铺类型!');
        }

        $suid = input('suid');
        if(!$suid){
            $this->error('请选择店铺管理员!');
        }else{
            if($shopid){
                $count = Db::name('wd_xcx_shops_shop') ->where('uniacid', $appletid) ->where('suid', $suid) ->where('id', 'NEQ', $shopid) ->find();
            }else{
                $count = Db::name('wd_xcx_shops_shop') ->where('uniacid', $appletid) ->where('suid', $suid) ->find();
            }
            
            if($count){
                $this ->error('该管理员店铺已存在， 请重新选择！');
            }

        }
     
        $flag = input("flag");
        if(!$flag){
            $flag = 0;
        }
        $hot = input("hot");
        if(!$hot){
            $hot = 0;
        }

        $username = input("username");
        if($username == ""){
            $this->error("账号不能为空");
            exit;
        }
        $password = input("password");
        if($password == ""){
            $this->error("密码不能为空");
            exit;
        }

        // $openid = input("openid");
        // $is = Db::name('wd_xcx_shops_shop')->where('uniacid',$appletid)->where("id",'neq',$shopid)->where('openid',$openid)->find();
        // if($is){
        //     $this->error("该openid已有绑定店铺，请修改后提交！");
        //     exit;
        // }


        $pcid = Db::name('wd_xcx_shops_cate')->where('uniacid',$appletid)->where('id',$cid)->find();
        if($pcid){


            $data= array(
                "uniacid" => $appletid,
                "suid" => $suid,
                "cid" => input('cid'),
                "num" => input('num'),
                "flag" => $flag,
                "hot" => $hot,
                "username" => input("username"),
                "name" => input("name"),
                "password" => input("password"),
                "intro" => input("intro"),
                "worktime" => input("worktime"),
                "star" => input("star"),
                "tel" => input("tel"),
                "address" => input("address"),
                "latitude" => input("latitude"),
                "longitude" => input("longitude"),
                "title" => input("title"),
                "descp" => input("descp"),
                "hits" => input("hits"),
                "shoppay_is" => input("shoppay_is") ? input("shoppay_is") : 2
            );
        }
        $imgsrcs = input("imgsrcs/a");
        if($imgsrcs){
            foreach ($imgsrcs as $key => &$value) {
                $value = remote($appletid,$value,1);
            }
            $data['images'] = serialize($imgsrcs);
        }else{
            $data['images'] = [];
        }
        //logo
        $logo = input("commonuploadpic1");
        if($logo){
           $data['logo'] = remote($appletid,$logo,2);
        }
        $bg = input("commonuploadpic2");
        if($bg){
            $data['bg'] = remote($appletid,$bg,2);
        }
        $yyzz = input("commonuploadpic3");
        if($yyzz){
            $data['yyzz'] = remote($appletid,$yyzz,2);
        }
        if($shopid>0){
            $res = Db::name('wd_xcx_shops_shop')->where("uniacid",$appletid)->where("id",$shopid)->update($data);
        }else{
            $is =Db::name('wd_xcx_shops_shop')->where('username',$username)->find();
            if($is){
                $this->error("账号名重复");
                exit;
            }
            $data['uniacid'] = $appletid;
            $res = Db::name("wd_xcx_shops_shop")->insert($data);
        }
        if($res){
            $this->success('店铺信息更新成功！',Url('Powerfulsh/tenant').'?appletid='.$appletid);
        }else{
            $this->error('店铺信息更新失败！');
        }
    }
    //删除用户
    public function  tenantdel(){
        $appletid = input("appletid");
        $pid = input("shopid");
        $data = array(
            "uniacid"=>$appletid,
            "id"=>$pid
        );
        $res = Db::name('wd_xcx_shops_shop')->where($data)->delete();
        if($res){
            $this->success('商家删除成功');
        }else{
            $this->success('商家删除失败');
        }
    }
    //商品管理 展示
    public function goods(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                //获取店铺
                $shops = Db::name('wd_xcx_shops_shop') ->where('uniacid', $appletid) ->field('id, name') ->select();
                $this->assign('shop', $shops);
                $sid = input('sid');
                $key = input('key');
                if(!$sid && !$key){
                    $goods = Db::name('wd_xcx_shops_goods')->where('uniacid',$appletid) ->order('id desc')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                    $count = Db::name('wd_xcx_shops_goods')->where("uniacid",$appletid)->count();
                }else{
                    if($sid && !$key){
                        $goods = Db::name('wd_xcx_shops_goods')->where('uniacid',$appletid) ->where('sid', $sid) ->order('id desc')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                        $count = Db::name('wd_xcx_shops_goods')->where("uniacid",$appletid) ->where('sid', $sid)->count();
                    }elseif ($key && !$sid) {
                        $goods = Db::name('wd_xcx_shops_goods')->where('uniacid',$appletid) ->where('title', 'LIKE', "%$key%") ->order('id desc')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                        $count = Db::name('wd_xcx_shops_goods')->where("uniacid",$appletid) ->where('title', 'LIKE', "%$key%")->count();
                    }else{
                        $goods = Db::name('wd_xcx_shops_goods')->where('uniacid',$appletid)->where('sid', $sid) ->where('title', 'LIKE', "%$key%") ->order('id desc')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                        $count = Db::name('wd_xcx_shops_goods')->where("uniacid",$appletid)->where('sid', $sid) ->where('title', 'LIKE', "%$key%") ->count();
                    }
                }
                $this ->qq();
                $goodslist = $goods->all();
                if($goodslist){
                    foreach ($goodslist as $key => &$res) {
                        if($res['thumb']){
                            $res['thumb'] = remote($appletid,$res['thumb'],1);
                        }else{
                            $res['thumb'] = remote($appletid,"/image/noimage_1.png",1);
                        }
                        $cate = Db::name('wd_xcx_shops_shop')->where('uniacid',$appletid)->where('id',$res['sid'])->field("name")->find();
                        $res['shopname'] = $cate['name'];
                    }
                }
                $this->assign('goods',$goods);
                $this->assign('goodslist',$goodslist);
                $this->assign('counts',$count);
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
            return $this->fetch('goods');
        }else{
            $this->redirect('Login/index');
        }
    }
    //新增商品
    public function goodsadd(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $goodsid = input("goodsid");
                $listV = Db::name('wd_xcx_shops_shop')->where("uniacid",$appletid)->order('num desc')->order('id desc')->select();
                $goodsinfo = '';
                if($goodsid){
                    $goods = Db::name('wd_xcx_shops_goods')->where("uniacid",$appletid)->where("id",$goodsid)->find();
                    if($goods['images']){
                        $goods['images'] = unserialize($goods['images']);
                        if($goods['images']){
                            foreach ($goods['images'] as $key => &$value) {
                                $value = remote($appletid,$value,1);
                            }
                        }
                    }
                }else{
                    $shopid = 0;
                    $goods = "";
                }
                $listX = Db::name('wd_xcx_goods_cate')->where("flag",1)->where("uniacid",$appletid)->where("cid",0)->order('num desc')->select();
                $listY = array();
               foreach($listX as $key=>$val) {
                    $id = intval($val['id']);
                    $listP = Db::name('wd_xcx_goods_cate')->where("flag",1)->where("uniacid",$appletid)->where("id",$id)->order('num desc')->select();
                    $listS = Db::name('wd_xcx_goods_cate')->where("flag",1)->where("uniacid",$appletid)->where("cid",$id)->order('num desc')->select();
//                    //子集数据量
                    $zjcount = Db::name('wd_xcx_goods_cate')->where("flag",1)->where("uniacid",$appletid)->where("cid",$id)->order('num desc')->count();
                    $listP['data'] = $listS;
                   $listP['zcount'] = $zjcount;
                    array_push($listY,$listP);
                }
                $this->assign('cates',$listY);

                //获取所有表单
                $forms = Db::name('wd_xcx_formlist') ->where('uniacid', $appletid) ->order('id desc') ->select();
                $this->assign('forms', $forms);

                $this->assign('goodsid',$goodsid);
                $this ->assign('goods',$goods);
                $this->assign('listAll',$listV);
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
            return $this->fetch('goodsadd');
        }else{
            $this->redirect('Login/index');
        }
    }
    //提交商品信息
    public function goodssave(){
       $appletid = input("appletid");
        $pid = input("pid");
        $sid = input("sid");
        $flag = input("flag");
        if(!$flag){
            $flag = 0;
        }
        $hot = input("hot");
        if(!$hot){
            $hot = 0;
        }
        $pcid = Db::name('wd_xcx_shops_shop')->where('uniacid',$appletid)->where('id',$sid)->find();
        if($pcid){

            $kuaidi=input('kuaidi');
            $data= array(
                "uniacid" => $appletid,
                "sid" => input('sid'),
                "num" => input('num'),
                "flag" => $flag,
                "hot" => $hot,
                "title" => input('title'),
                "buy_type" => input('buy_type'),
                "pageview" => input('pageview'),
                "vsales" => input('vsales'),
                "rsales" => input('rsales'),
                "sellprice" => input('sellprice'),
                "marketprice" => input('marketprice'),
                "storage" => input('storage'),
                "descp" => htmlspecialchars_decode(input('descp')),
                "descs" => input('descs'),
                "kuaidi"=>$kuaidi,
                "cid"=>input("cid"),
                'formset' => input('formset'),
                'video' => input('video'),
            );
        }
        $imgsrcs = input("imgsrcs/a");
        if($imgsrcs){
            foreach ($imgsrcs as $key => &$value) {
                $value = remote($appletid,$value,1);
            }
            $data['images'] = serialize($imgsrcs);
        }else{
            $data['images'] = [];
        }
        $thumb = input("commonuploadpic1");
        if($thumb){
           $data['thumb'] = remote($appletid,$thumb,1);
        }
        $goodsid = input("goodsid");
        if($goodsid){
            $res = Db::name('wd_xcx_shops_goods')->where("uniacid",$appletid)->where('id',$goodsid)->update($data);
        }else{
            //查看是否需要审核
            $conf = Db::name('wd_xcx_shops_set') ->where('uniacid', $appletid) ->field('goods') ->find();
            if($conf){
                if($conf['goods'] == 2){
                    $data['status'] = 1;
                }
            }
            $data['uniacid'] = $appletid;
            $data['createtime'] = time();
            $res = Db::name("wd_xcx_shops_goods")->insert($data);
        }
        if($res){
            $this->success('商品信息更新成功！',Url('Powerfulsh/goods').'?appletid='.$appletid);
        }else{
            $this->error('商品信息更新失败没有修改项！');
        }
    }
    //删除商品
    public function goodsdel(){
        $appletid = input("appletid");
        $pid = input("goodsid");
        $data = array(
            "uniacid"=>$appletid,
            "id"=>$pid
        );
        $res = Db::name('wd_xcx_shops_goods')->where($data)->delete();
        if($res){
            $this->success('商品删除成功');
        }else{
            $this->success('商品删除失败');
        }
    }
    //审核商品通过
    public function goodspass(){
        $pid = intval(input("goodsid"));
        $appletid = input("appletid");
        $data = array(
            "uniacid" =>$appletid,
            "id" => $pid,
            "status" => 1
        );
        $res = Db::name('wd_xcx_shops_goods')->where("uniacid",$appletid)->where("id",$pid)->update($data);
        if($res){
            $this->success("审核通过");
        }else{
            $this->success("审核失败");
        }
    }
    //审核商品不通过
    public function goodscancel(){
        $pid = intval(input("goodsid"));
        $appletid = input("appletid");
        $data = array(
            "uniacid" =>$appletid,
            "id" => $pid,
            "status" => 2
        );
        $res = Db::name('wd_xcx_shops_goods')->where("uniacid",$appletid)->where("id",$pid)->update($data);
        if($res){
            $this->success("审核不通过");
        }else{
            $this->success("审核失败");
        }
    }
    //审核商品是否通过
    // public function goodspass(){
    //     $appletid = input("appletid");
    //     $pid = input("goodsid");
    //     $data = array(
    //         "status" => 1,
    //     );
    //     $res = Db::name('wd_xcx_shops_goods')->where("appletid",$appletid)->where("id",$pid)->update($data);
    //     if($res){
    //         $this->success('审核通过');
    //     }else{
    //         $this->success('审核失败');
    //     }
    // }
    //订单管理
    public function order(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $op = input('op');

                if($op == "hx"){  //核销
                    $order = input('orderid');
                    $shopid = input('shopid');
                    $data['hxtime'] = time();
                    $data['hxinfo'] = 'a:1:{i:0;i:1;}';
                    $data['flag'] = 2;
                    $res = Db::name('wd_xcx_duo_products_order')->where("id",$order)->update($data);
                    if($shopid != '0'){
                        $money = Db::name('wd_xcx_shops_shop')->where("uniacid",$appletid)->where("id",$shopid)->field("tixian")->find()['tixian'];
                        $add = Db::name('wd_xcx_duo_products_order')->where("uniacid",$appletid)->where("id",$order)->field("price")->find()['price'];
                        $money = $money + $add;
                        $result = Db::name('wd_xcx_shops_shop')->where("uniacid",$appletid)->where("id",$shopid)->update(array('tixian' => $money));
                    }
                    if($res){
                        $info = Db::name('wd_xcx_duo_products_order')->where('id', $order)->field('suid,price,source')->find();
                        add_all_pay($appletid, $info['price'], $info['suid']);
                        check_vip_grade($appletid, $info['suid']);

                        if($info['source'] == 1){
                            $openid = Db::name("wd_xcx_user")->where("suid", $info['suid'])->value('openid');
                            $jsons = [
                                'fprice' => $info['price']
                            ];
                            $jsons = serialize($jsons);
                            sendSubscribe($appletid, 2, $openid, $jsons);
                        }

                        $this->success("核销成功");
                    }
                }
                if($op == "fh"){  
                    $order = input('orderid');
                    $data['flag'] = 2;
                    $res = Db::name('wd_xcx_duo_products_order')->where("id",$order)->update($data);
                    if($res){
                        $this->success("操作成功");
                    }
                }
                if($op == "fahuo"){  //发货
                    $order = input('orderid');
                    $data['hxtime'] = time();
                    $data['kuadi'] = input('kuaidi');
                    $data['kuaidihao'] = input('kuaidihao');
                    $data['flag'] = 4;
                    $res = Db::name('wd_xcx_duo_products_order')->where("id",$order)->update($data);
                    if($res){
                        $info = Db::name('wd_xcx_duo_products_order')->where("id", $order)->find();
                        if($info['source'] == 1){
                            $openid = Db::name("wd_xcx_user")->where("suid", $info['suid'])->value('openid');
                            $jsons = [
                                'order_id' => $info['order_id']
                            ];
                            $jsons = serialize($jsons);
                            sendSubscribe($appletid, 1, $openid, $jsons);
                        }
                        $this->success("发货成功");
                    }
                }

                if($op == 'refuseth' || $op == 'refuseqx'){  //拒绝退货
                    $orderid = input('orderid');
                    $data['flag'] = $op == 'refuseqx' ? 1 : 9;
                    model('ImsSudu8PageDuoProductsOrder') ->save($data, ['id'=>$orderid]);

                    $info = Db::name('wd_xcx_duo_products_order')->where("id", $orderid)->find();
                    if($info['source'] == 1){
                        $openid = Db::name("wd_xcx_user")->where("suid", $info['suid'])->value('openid');
                        $jsons = [
                            'order_id' => $info['order_id'],
                            'fprice' => $info['price'],
                            'msg' => "退款被拒",
                        ];
                        $jsons = serialize($jsons);
                        sendSubscribe($appletid, 3, $openid, $jsons);
                    }

                    $this->success("拒绝退款/退货成功");
                }

                if($op == 'quxiao' || $op == 'allowth' || $op == 'confirmtk'){   //取消订单  同意退货  同意取消订单
                    $uniacid = input('appletid');
                    $order_id=input("orderid");
                    $orderObj = model('ImsSudu8PageDuoProductsOrder');
                    $proObj = model('ImsSudu8PageShopsGoods');
                    $userObj = model('ImsSudu8PageSuperuser');
                    if(input('qxbeizhu')){
                        $data['qxbeizhu'] = input('qxbeizhu');
                    }
                    $order = $orderObj->get($order_id);
                    if(!$order){
                        $this->error('该订单不存在！');
                    }
                    if ($order->flag == 5 || $order->flag == 8) {
                        $this->error('订单状态不正确！');
                    }
                    $pids = unserialize($order->jsondata);
                    $user = $userObj->get($order->suid);

                    $now = time();
                    $out_refund_no = date("Y",$now).date("m",$now).date("d",$now).date("H",$now).date("i",$now).date("s",$now).rand(1000,9999);

                    Db::startTrans();
                    try {
                        //改变订单状态
                        if($op == 'quxiao'){
                            $data['flag'] = 5;
                        }else if($op == 'allowth'|| $op == 'confirmtk'){
                            $data['flag'] = 8;
                        }

                        $data['th_orderid'] = $out_refund_no;
                        $orderObj->save($data, ['id' => $order_id]);

                        //处理优惠券
                        if ($order->coupon) {
                            $cou['flag'] = 0;
                            $cou['utime'] = 0;
                            model('ImsSudu8PageCouponUser')->save($cou, ['id' => $order->coupon]);
                        }

                        //处理库存与真实销量
                        $order_product = $proObj->get($pids[0]['pid']);
                        if ($pids[0]['num'] > 0) {   //更新销量
                            $newProData['rsales'] = $order_product->rsales - $pids[0]['num'];
                            $newProData['rsales'] = $newProData['rsales'] > 0 ? $newProData['rsales'] : 0;
                            $proObj->save($newProData, ['id' => $pids[0]['pid']]);
                        }
                        if ($order_product->storage != -1) { //有限量库存 更新库存
                            if ($pids[0]['num'] > 0) {
                                $proKc['storage'] = $order_product->storage + $pids[0]['num'];
                                $proObj->save($proKc, ['id' => $pids[0]['pid']]);
                            }
                        }

                        //处理退款
                        $yuTk = $order->price - $order->payprice;
                        if($yuTk > 0){    //处理余额
                            $userMoney = $user->money + $yuTk;
                            $userObj->save(['money' => $userMoney], ['id' => $order->suid]);

                            $xfmoney = array(
                                "uniacid" => $uniacid,
                                "orderid" => $order->order_id,
                                "suid" => $order->suid,
                                "type" => "add",
                                "score" => $yuTk,
                                "message" => "退款退回余额",
                                "creattime" => time()
                            );
                            model('ImsSudu8PageMoney') ->save($xfmoney);
                        }

                        if ($order->payprice > 0) {
                            $res = orderRefund($uniacid, $order_id, 'duo');
                            if($res['status'] == false){
                                throw new \Exception($res['msg']);
                            }
                        }

                        Db::commit();
                    } catch (\Exception $e) {
                        Db::rollback();
                        $this->error('取消失败，' . $e->getMessage());
                    }
                    if($order['source'] == 1){
                        $openid = Db::name("wd_xcx_user")->where("suid", $order['suid'])->value('openid');
                        $jsons = [
                            'order_id' => $order['order_id'],
                            'fprice' => $order['price'],
                            'msg' => "退款成功",
                        ];
                        $jsons = serialize($jsons);
                        sendSubscribe($uniacid, 3, $openid, $jsons);
                    }
                    $this->success('取消成功');
                }

                $search_flag = input('search_flag');
                $search_keys = input('search_keys');
                $search_type = input('search_type');
                $start_get = input('start_get') ? strtotime(input('start_get')) : '';
                $end_get = input('end_get') ? strtotime(input('end_get')) : '';
                $where = '';
                if ($search_flag != "") {
                    if($search_flag == 1){
                        $where .= " a.flag = 1 and a.nav = 1";
                    }elseif($search_flag == 10){
                        $where .= " a.flag = 1 and a.nav = 2";
                    }else{
                        $where.= " a.flag = ".$search_flag;
                    }
                    
                }

                if ($start_get && $end_get) {
                    $where.= " a.creattime > ".$start_get." and a.createtime < ".$end_get;
                }else if($start_get){
                    $where.= " a.creattime >= ".$start_get;
                }else if ($end_get) {
                    $where.= " a.creattime <= ".$start_get;
                }

                if ($search_type && $search_keys) {
                    if ($search_type == 1) {
                        $where.= " a.order_id like '%".$search_keys."%'";
                    }
                    if ($search_type == 2) {
                        $where.= " b.name like '%".$search_keys."%'";
                    }
                    if ($search_type == 3) {
                        $where.= " b.mobile like '%".$search_keys."%'";
                    }
                    if ($search_type == 4) {
                        $where.= " b.address like '%".$search_keys."%'";
                    }
                }
                
                $total = Db::name('wd_xcx_duo_products_order')->alias("a")->join("wd_xcx_duo_products_address b",'a.address=b.id','left')->where('a.uniacid',$appletid)->where("a.sid",'neq',0)->where($where)->order("a.creattime desc")->field('a.*')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"), 'search_flag'=>$search_flag,"search_type"=>$search_type,"search_keys"=>$search_keys,"start_get"=>$start_get,"end_get"=>$end_get)]);
                $orders = $total->toArray()['data'];

                foreach ($orders as $key => &$res) {
                    $res['jsondata'] = unserialize($res['jsondata']);
                    $res['creattime'] = date("Y-m-d H:i:s",$res['creattime']);
                    $res['hxtime'] = $res['hxtime'] == 0 ? "无" : date("Y-m-d H:i:s",$res['hxtime']);

                    $res['hxinfo'] = $res['hxinfo'] ? unserialize($res['hxinfo']) : '';
                    if($res['hxinfo']){
                        if ($res['hxinfo'][0] == 1) {
                            $res['hxinfo2'] = "系统核销";
                        }else if($res['hxinfo'][0] == '密码核销' || $res['hxinfo'][0] == '管理员核销'){
                           $res['hxinfo2'] = $res['hxinfo'][0];
                        }else if($res['hxinfo'][0]=='核销员核销'){
                            $res['hxinfo2']=$res['hxinfo'][1].'核销';
                        }
                    } else{
                        $res['hxinfo2'] = '无';

                    }


                    $res['userinfo'] = Db::name('wd_xcx_user')->where('uniacid',$appletid)->where("openid",$res['openid'])->find();
                    $res['counts'] = count($res['jsondata']);
                    $coupon = Db::name('wd_xcx_coupon_user')->where('uniacid',$appletid)->where("id",$res['coupon'])->find();
                    $couponinfo = Db::name('wd_xcx_coupon')->where('uniacid',$appletid)->where("id",$coupon['cid'])->find();
                    $res['couponinfo'] = $couponinfo;
                    if($res['sid'] == '0'){
                        $res['shopname'] = '总平台';
                    }else{
                        $res['shopname'] = Db::name('wd_xcx_shops_shop')->where('uniacid',$appletid)->where("id",$res['sid'])->field('name')->find()['name'];
                    }
                    // 重新算总价
                    $allprice = 0;
                    foreach ($res['jsondata'] as $key2 => &$reb) {
                        $allprice += ($reb['num']*1)*($reb['proinfo']['price']);
                    }
                    $res['allprice'] = $allprice;
                    // 积分转钱
                    //积分转换成金钱
                    $jf_gz = Db::name('wd_xcx_rechargeconf')->where('uniacid',$appletid)->find();
                    
                    if(!$jf_gz){
                        $gzscore = 10000;
                        $gzmoney = 1;
                    }else{
                        $gzscore = $jf_gz['score'];
                        $gzmoney = $jf_gz['money'];
                    }
                    $res['jfmoney'] = $res['jf']*$gzmoney/$gzscore;
                    // 转换地址
                    if($res['address']!=0){
                        $res['address_get'] = Db::name('wd_xcx_duo_products_address')->where('openid',$res['openid'])->where('id',$res['address'])->find();
                        if(!$res['address_get']){
                            $res['address_get']=unserialize($res['m_address']);
                            if(!isset($res['address_get']['name'])){
                                $res['address_get']['name'] = "";
                            }
                            if(!isset($res['address_get']['mobile'])){
                                $res['address_get']['mobile'] = "";
                            }
                            if(!isset($res['address_get']['address'])){
                                $res['address_get']['address'] = "";
                            }

                            if(!isset($res['address_get']['postalcode'])){
                                $res['address_get']['postalcode'] = "";
                            }
                            if(!isset($res['address_get']['more_address'])){
                                $res['address_get']['more_address'] = "";
                            }
                        }
                    }else{
                        $res['address_get'] = unserialize($res['m_address']);
                        if(!isset($res['address_get']['name'])){
                            $res['address_get']['name'] = "";
                        }
                        if(!isset($res['address_get']['mobile'])){
                            $res['address_get']['mobile'] = "";
                        }
                        if(!isset($res['address_get']['address'])){
                            $res['address_get']['address'] = "";
                        }

                        if(!isset($res['address_get']['postalcode'])){
                            $res['address_get']['postalcode'] = "";
                        }
                        if(!isset($res['address_get']['more_address'])){
                            $res['address_get']['more_address'] = "";
                        }
                    }
                    if($res['formid']){
                        $arr2=Db::name('wd_xcx_formcon')->where('uniacid',$appletid)->where('id',$res['formid'])->find();
                        $arr2['val']=unserialize($arr2['val']);
                        $res['forminfo']=$arr2['val'];
                    }else{
                        $res['forminfo']='';
                    }
                }
                if($start_get){
                    $start_get = date("Y-m-d H:i:s", $start_get);
                }
                if($end_get){
                    $end_get = date("Y-m-d H:i:s", $end_get);
                }
                $this->assign("search_flag", $search_flag);
                $this->assign("search_type", $search_type);
                $this->assign("search_keys", $search_keys);
                $this->assign("start_get", $start_get);
                $this->assign("end_get", $end_get);

                $this->assign('orders',$orders);
                $this->assign('total',$total);

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
            return $this->fetch('order');
        }else{
            $this->redirect('Login/index');
        }
    }
    public function fahuo(){
        $appletid = input("appletid");
        $order_id = input("orderid");
        $shopid = input('shopid');
        $data['hxtime'] = time();
        $data['flag'] = 2;
        Db::name('wd_xcx_duo_products_order')->where("uniacid",$appletid)->where("id",$order_id)->update($data);
        if($shopid != '0'){
            $money = Db::name('wd_xcx_shops_shop')->where("uniacid",$appletid)->where("id",$shopid)->find()['tixian'];
            $add = Db::name('wd_xcx_duo_products_order')->where("uniacid",$appletid)->where("id",$order_id)->find()['price'];
            $jiesuan = Db::name('wd_xcx_shops_set') ->where('uniacid', $appletid) ->find();
            $jiesuan = $jiesuan['jiesuan'];
            if (floatval($jiesuan) > 0) {
                $d = floatval($jiesuan) / 100;
                $c = $add * $d;
                $price = $add - $c;
                $price = round($price, 2);
                $money = $money + $price;
            }else{
                $money = $money + $add;
            }
            $result = Db::name('wd_xcx_shops_shop')->where("uniacid",$appletid)->where("id",$shopid)->update(array('tixian' => $money));
        }
        if($result){
            $this->success("核销成功");
        }
    }
    //提现申请
    public function withdraw(){ 
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $records_list = Db::name('wd_xcx_shops_tixian')->where("uniacid",$appletid)->order("createtime desc")->paginate(10,false,['query' => ['appletid' => $appletid]]);
                $records = $records_list -> toArray()['data'];
                foreach ($records as $key => &$value){
                    $value['shopname'] = Db::name('wd_xcx_shops_shop')->where("uniacid",$appletid)->where("id",$value['sid'])->find()['name'];
                };
                $count = Db::name('wd_xcx_shops_tixian')->where("uniacid",$appletid)->count();
                $this->assign('records_list',$records_list);
                $this->assign('records',$records);
                $this->assign('counts',$count);
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
            return $this->fetch('withdraw');
        }else{
            $this->redirect('Login/index');
        }
    }
    //提现审核
    public function withdrawpass(){
        $appletid = input("appletid");
        $id = input("id");
        $val = input('val');
        $tixianInfo = Db::name('wd_xcx_shops_tixian') ->where('uniacid', $appletid) ->where('id', $id) ->find();
        $shop = Db::name('wd_xcx_shops_shop') ->where('id', $tixianInfo['sid']) ->find();

        if($val == 1){
            $formid = Db::name('wd_xcx_shops_tixian') ->where('uniacid', $appletid) ->where('id', $id) ->field('formID') ->find()['formID'];
            if($formid){
                $formId=$tixianInfo['formID'];
                switch ($tixianInfo['types']) {
                    case '1':
                        $ftype = '微信';
                        break;
                    case '2':
                        $ftype = '支付宝';
                        break;
                    default:
                        $ftype = '银行卡';
                        break;
                }

                if($tixianInfo['source'] != 3){
                    $fmoney = $tixianInfo['money'].'元';
                    $jsons = [
                                'fmoney' => $fmoney,
                                'ftype' => $ftype,
                            ];
                    $jsons = serialize($jsons);
                    if($tixianInfo['source'] == 6){
                        $openid = Db::name('wd_xcx_qq_user')->where('suid', $tixianInfo['suid'])->value('openid');
                        tpl_send($appletid, 12, $openid, $tixianInfo['source'], $formId, $jsons);
                    }
                }
      
                $data=array(
                    'flag' => 1,
                    'txtime' => time()
                );
                $res = Db::name('wd_xcx_shops_tixian') ->where('uniacid', $appletid) ->where('id', $id) ->update($data);
                if($res){
                    $this->success('确认打款成功!');
                }else{
                    $this->error('发送未知错误,请稍后重试!!');
                }
            }else{
                if($tixianInfo['source'] == 1){
                    $openid = Db::name('wd_xcx_user')->where('suid', $tixianInfo['suid'])->value('openid');
                    $jsons = [
                        'fprice' => $tixianInfo['money'],
                        'msg' => "审核通过",
                    ];
                    $jsons = serialize($jsons);
                    sendSubscribe($appletid, 9, $openid, $jsons);
                }
                $data=array(
                    'flag' => 1,
                    'txtime' => time()
                );
                $res = Db::name('wd_xcx_shops_tixian') ->where('uniacid', $appletid) ->where('id', $id) ->update($data);
                if($res){
                    $this->success('确认打款成功!');
                }else{
                    $this->error('发送未知错误,请稍后重试!!');
                }
            }
            
        }else if($val==2){
            $data = array(
                "flag" => 2
            );
            $res = Db::name('wd_xcx_shops_tixian')->where("uniacid",$appletid)->where("id",$id)->update($data);
            //退换提现的金额
            $fan = $shop['tixian'] + $tixianInfo['money'];
            Db::name('wd_xcx_shops_shop')->where('id', $shop['id']) ->update(['tixian' => $fan]);
            if($res){
                if($tixianInfo['source'] == 1){
                    $openid = Db::name('wd_xcx_user')->where('suid', $tixianInfo['suid'])->value('openid');
                    $jsons = [
                        'fprice' => $tixianInfo['money'],
                        'msg' => "审核被拒",
                    ];
                    $jsons = serialize($jsons);
                    sendSubscribe($appletid, 9, $openid, $jsons);
                }
                $this->success("已拒绝!");
            }
        }
        
    }
    //系统设置
    public function system(){
        if(check_login()){
            if(powerget()){
                $id = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $systems = Db::name('wd_xcx_shops_set')->where("uniacid",$id)->find();
                if($systems['bg']){
                    $systems['bg'] = remote($id,$systems['bg'],1);
                }
                $this->assign('systems',$systems);
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
            return $this->fetch('system');
        }else{
            $this->redirect('Login/index');
        }
    }
    public function systemsave(){
        $appletid = input("appletid");
        $res = Db::name('wd_xcx_shops_set')->where("uniacid",$appletid)->find();
        $data = array();
        $tjnum = input("tjnum");
        if($tjnum){
            $data['tjnum']=$tjnum;
        }else{
            $data['tjnum'] = 6;
        }
        $num = input("num");
        if($num){
            $data['num']=$num;
        }else{
            $data['num'] = 6;
        }
        $apply = $_POST['apply'];
        if($apply){
            $data['apply'] = $apply;
        }
        $goods = $_POST['goods'];
        if($goods){
            $data['goods'] = $goods;
        }
        $withdraw = $_POST['withdraw'];
        if($withdraw){
            $data['withdraw'] = $withdraw;
        }
        $minimum =input("minimum");
        if($minimum){
            $data['minimum'] = $minimum;
        }
        $bg = input("commonuploadpic1");
        if($bg){
            $data['bg'] = remote($appletid,$bg,2);
        }
        $tixiantype= input('tixiantype/a');
        if(isset($tixiantype)){
            $data['tixiantype'] = implode(",", $tixiantype);
        }else{
            $data['tixiantype'] = implode(",", array('1'));
        }

        $data['jiesuan'] = input('jiesuan');
        $data['tixianok'] = input('tixianok');
        $data['shenheok'] = input('shenheok');

        $data['protocol'] = input('protocol');
        // var_dump($data['protocol']);exit;
        $systems = Db::name('wd_xcx_shops_set')->where("uniacid",$appletid)->count();
        if($systems>0){
            $res = Db::name('wd_xcx_shops_set')->where("uniacid",$appletid)->update($data);
        }else{
            $data['uniacid']=$appletid;
            $res = Db::name('wd_xcx_shops_set')->insert($data);
        }
        if($res){
            $this->success('基础信息更新成功！');
        }else{
            $this->error('基础信息更新失败，没有修改项！');
            $this->redirect('Login/index');
        }
    }
    public function shoppay(){
        if(check_login()){
            if(powerget()){
                $id = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $op = input('op');
                $ops = array('display', 'excel');
                $op = in_array($op, $ops) ? $op : 'display';
                $search_keys = input('search_keys');
                $where = '';
                if(!empty($search_keys)){
                    $where = "b.name like '%".$search_keys."%'";
                }
                // $lists = Db::name('wd_xcx_money')->alias('a')->join('wd_xcx_shops_shop b', 'a.sid = b.id')->join('wd_xcx_superuser c', 'a.uid = c.id')->where('a.sid', 'gt', 0)->where('a.uniacid', $id)->where($where)->order('creattime desc')->field("a.*,b.name,c.nickname,c.avatar,c.realname,c.mobile")->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                $lists = Db::name('wd_xcx_money')->alias('a')->join('wd_xcx_shops_shop b', 'a.sid = b.id')->join('wd_xcx_superuser c', 'a.suid = c.id')->where('a.sid', 'gt', 0)->where('a.uniacid', $id)->where($where)->order('creattime desc')->field("a.*,b.name,c.id,c.truename as realname,c.phone as mobile")->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                
                if($op == 'excel'){
                   require_once ROOT_PATH.'public/plugin/PHPExcel/PHPExcel.php';

                    $objPHPExcel = new \PHPExcel();

                    /*以下是一些设置*/
                    $objPHPExcel->getProperties()->setCreator("多商户店内支付记录")
                        ->setLastModifiedBy("多商户店内支付记录")
                        ->setTitle("多商户店内支付记录")
                        ->setSubject("多商户店内支付记录")
                        ->setDescription("多商户店内支付记录")
                        ->setKeywords("多商户店内支付记录")
                        ->setCategory("多商户店内支付记录");
                    $objPHPExcel->getActiveSheet()->setCellValue('A1', '店铺名称');
                    $objPHPExcel->getActiveSheet()->setCellValue('B1', '金额(元)');
                    $objPHPExcel->getActiveSheet()->setCellValue('C1', '用户微信信息');
                    $objPHPExcel->getActiveSheet()->setCellValue('D1', '用户真实信息');
                    $objPHPExcel->getActiveSheet()->setCellValue('E1', '支付描述');
                    $objPHPExcel->getActiveSheet()->setCellValue('F1', '支付时间');

                    foreach ($lists->toArray()['data'] as $key => &$res) {
                        $user = getNameAvatar($res['id'], $id, 1);
                        $res['nickname'] = $user['nickname'];
                        $res['avatar'] = $user['avatar'];
                        $num=$key+2; 
                        $res['creattime'] = date("Y-m-d H:i:s",$res['creattime']);

                        $objPHPExcel->getActiveSheet()->getStyle("C".$num)->getAlignment()->setWrapText(TRUE);    //内容换行

                        $objPHPExcel->setActiveSheetIndex(0)
                                    ->setCellValueExplicit('A'.$num, $res['name'],'s')
                                    ->setCellValueExplicit('B'.$num, $res['score'],'s')
                                    ->setCellValueExplicit('C'.$num, $res['avatar'].' '.$res['nickname'],'s') 
                                    ->setCellValueExplicit('D'.$num, $res['realname'].' '.$res['mobile'],'s')
                                    ->setCellValueExplicit('E'.$num, $res['message'], 's')
                                    ->setCellValueExplicit('F'.$num, $res['creattime'], 's');
                          
                    }
                    $objPHPExcel->getActiveSheet()->setTitle('多商户店内支付记录');
                    $objPHPExcel->setActiveSheetIndex(0);
                    $excelname="多商户店内支付记录";
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$excelname.'.xls"');
                    header('Cache-Control: max-age=0');
                    $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
                    $objWriter->save('php://output');
                    exit;
                }
                $list = $lists->toArray()['data'];
                foreach ( $list as $k => &$value) {
                    $user = getNameAvatar($value['id'], $id);
                    $value['nickname'] = $user['nickname'];
                    $value['avatar'] = $user['avatar'];
                }
                $this->assign('search_keys',$search_keys);
                $this->assign('list',$list);
                $this->assign('pager',$lists->render());
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
            return $this->fetch('shoppay');
        }else{
            $this->redirect('Login/index');
        }
    }
    //单个图片上传操作
    function onepic_uploade($file){
        $thumb = request()->file($file);
        if(isset($thumb)){
            $dir = upload_img();
            $info = $thumb->validate(['ext'=>'jpg,png,gif,jpeg'])->move($dir);
            if($info){
                $imgurl = ROOT_HOST."/upimages/".date("Ymd",time())."/".$info->getFilename();
                return $imgurl;
            }
        }
    }
    //多图片上传
    public function imgupload(){
        $data['appletid'] = $_GET['appletid'];
        $files = request()->file('');
        foreach($files as $file){
            // 移动到框架应用根目录/public/upimages/ 目录下
            $info = $file->validate(['ext'=>'jpg,png,gif,jpeg'])->move(ROOT_PATH . 'public' . DS . 'upimages');
            if($info){
                $data['url'] =  ROOT_HOST."/upimages/".date("Ymd",time())."/".$info->getFilename();
                $data['dateline'] = time();
                $res = Db::name('wd_xcx_shops_shop')->insert($data);
            }else{
                // 上传失败获取错误信息
                return $this->error($file->getError()) ;
            }
        }
    }
    //上传成功后获取图片
    public function getimg(){
        $id = $_POST['id'];
        $images = Db::name('wd_xcx_shops_shop')->where("appletid",$id)->select();
        if($images){
            return $images;
        }
    }
    public function del(){
        $id = input("id");
        $res = Db::name('wd_xcx_shops_shop')->where('id', $id)->delete();
        if($res){
            return 1;
        }else{
            $this->error("删除失败！");
        }
    }


    private function qq(){
        $secret = md5('worldidc_wnmd'); // md5('worldidc_wnmd');

        $key_content = include('License.php');
        $key_content = $key_content['license'];
        $length = strlen($key_content);

        // 密钥长度小于 102 必然无效
        // if($length < 102) {
        //     die();
        // }

        $is = base64_decode(substr($key_content, 0, 6));

        if(substr($is, 0, 1) == '|'){
            $str_arr = unpack("C2", substr($is, 1));
            $key_content = substr($key_content, 6);
            $len1 = $str_arr[1];
            $len2 = $length - 6 - $len1 - $str_arr[2];
        }else{
            $len1 = 26;
            $len2 = $length - 102;
        }

        // 获取加密的 code
        $code = base64_decode(substr($key_content, $len1, $len2));

        $code_length = strlen($code);

        $round = $code_length / 32;
        $left = $code_length % 32;

        // 获取和 code 等长的 self_key
        $self_key = str_repeat($secret, $round) . substr($secret, 0, $left);

        // 这边不妨把两个都 unpack 下

        $decode = array_map(function($a, $b) {
            $c = $a - $b;
            return $c > 0 ? $c : $c + 256;
        }, unpack("C{$code_length}", $code), unpack("C{$code_length}", $self_key));

        $str = array_reduce($decode, function($sum, $code) {
            return $sum .= chr($code);
        }, '');

        //end
        if($str == $_SERVER['HTTP_HOST']){

            //通过
        }else{

                  // echo '密钥错误，请联系开发者获取正确密钥!';
          //  exit();
        }
    }

    public function shenhetongzhi($appletid, $id, $status){
        $shop = Db::name('wd_xcx_shops_shop') ->where('id', $id) ->find();
        $openID = $shop['openid'];
        $formId = $shop['formid'];
        if($shop['source'] != 3){
            if($shop['source'] == 1){
                if ($status == 1) {
                     $tInfo = '通过';
                } else {
                     $tInfo = '拒绝';
                }
                $jsons = [
                    "tInfo" => $tInfo,
                    "name" => $shop['name'],
                ];
                $jsons = serialize($jsons);
                $openid = Db::name('wd_xcx_user')->where('suid', $shop['suid'])->value('openid');
                sendSubscribe($appletid, 6, $openid, $jsons);
            }else if($shop['source'] == 6 && $formId){
                if ($status == 1) {
                     $tInfo = '店铺审核通过了！';
                } else {
                     $tInfo = '店铺审核被拒绝！';
                }
                $jsons = [
                    "tInfo" => $tInfo
                ];
                $jsons = serialize($jsons);
                $openid = Db::name('wd_xcx_qq_user')->where('suid', $shop['suid'])->value('openid');
                tpl_send($appletid, 5, $openid, $shop['source'], $shop['formid'], $jsons);
            }
        }
  
    }
    public function goodscate(){
        if(check_login()){
            if(powerget()){
                $id = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $cates_list = Db::name('wd_xcx_goods_cate')->where("uniacid",$id)->order('num desc')->paginate(10,false, ['query' => ['appletid' => $id]]);
                $cates = $cates_list->toArray()['data'];
                $count = Db::name('wd_xcx_goods_cate')->where("uniacid",$id)->order('num desc')->count();
                foreach($cates as $key => &$res){
                    $a= Db::name('wd_xcx_goods_cate')->where("uniacid",$id)->where("id",$res["cid"])->find();
                    $res["cname"]=$a["name"];
                }
                $this->assign('cates_list',$cates_list);
                $this->assign('cates',$cates);
                $this->assign('counts',$count);
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
            return $this->fetch('goodscate');
        }else{
            $this->redirect('Login/index');
        }
    }

    public function goodscateadd(){
        $appletid = input("appletid");
        $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
        if(!$res){
            $this->error("找不到对应的小程序！");
        }
        $this->assign('applet',$res);
        $cateid = intval(input('cateid'));
        if(!$cateid){
            $cateid = 0;
        }
        $cate = Db::name('wd_xcx_goods_cate')->where("uniacid",$appletid)->where('id',$cateid)->find();
        $cates = Db::name('wd_xcx_goods_cate')->where("uniacid",$appletid)->where("id","neq",$cateid)->where("cid",0)->select();

        $this->assign('cate',$cate);
        $this->assign('cates',$cates);
        $this->assign('cateid',$cateid);
        return $this->fetch('goodscateadd');
    }

    public function goodscatesave(){
        $appletid = input("appletid");
        $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
        if(!$res){
            $this->error("找不到对应的小程序！");
        }
        $cateid=input("cateid");
        $data['name']=input("name");
        $data['num']=input("num");
        $data['cid']=input("cid");
        $data['flag']=input("flag");
        if (!$cateid) {
            $data['uniacid'] = $appletid;
            $res = Db::name('wd_xcx_goods_cate')->insert($data);
        } else {
            $res = Db::name('wd_xcx_goods_cate')->where('id',$cateid)->where('uniacid',$appletid)->update($data);
        }
        if($res){
            $this->success('基础信息更新成功！！',Url('Powerfulsh/goodscate').'?appletid='.$data['uniacid']);
        }else{
            $this->error('基础信息更新失败，没有修改项！');
        }
    }
    public function goodscatedel(){
        $appletid = input("appletid");
        $cateid = input("cateid");

        $is = Db::name('wd_xcx_shops_goods')->where("uniacid",$appletid)->where("cid",$cateid)->count();
        if($is){
            $this->error('多商户商品栏目删除失败,该分类下存在商品');
        }

        $data = array(
            "uniacid"=>$appletid,
            "id"=>$cateid
        );
        $cates = Db::name('wd_xcx_goods_cate')->where("uniacid",$appletid)->where("cid",$cateid)->select();
        if(count($cates)>0){
            $this->error('多商户商品栏目删除失败,该分类存在子分类');
        }else{
            $res = Db::name('wd_xcx_goods_cate')->where($data)->delete();
            if($res){
                $this->success('多商户商品栏目删除成功');
            }else{
                $this->error('多商户商品栏目删除失败');
            }
        }
    }

    public function orderdown(){
        $appletid = input("appletid");
        $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
        if(!$res){
            $this->error("找不到对应的小程序！");
        }
        $this->assign('applet',$res);
        $order_id = input('order_id');
        $search_flag = input('search_flag');
        $search_keys = input('search_keys');
        $search_type = input('search_type');
        $start_get = input('start_get') ? strtotime(input('start_get')) : '';
        $end_get = input('end_get') ? strtotime(input('end_get')) : '';
        $where = [];
        if ($search_flag != "") {
            $where['flag'] = $search_flag;
        }

        if ($start_get && $end_get) {
            $where['creattime'] = ['between', [$start_get,$end_get]];
        }else if($start_get){
            $where['creattime'] = ['>=', $start_get];
        }else if ($end_get) {
            $where['creattime'] = ['<=', $end_get];
        }

        if ($search_type) {
            if ($search_type == 1) {
                $where['order_id'] = ["like", "%".$search_keys ."%"];
            }
            if ($search_type == 2) {
                $where['name'] = ["like", "%".$search_keys ."%"];
            }
            if ($search_type == 3) {
                $where['mobile'] = ["like", "%".$search_keys ."%"];
            }
            if ($search_type == 4) {
                $where['address'] = ["like", "%".$search_keys ."%"];
            }
        }

        $orders = Db::name('wd_xcx_duo_products_order')->where('uniacid',$appletid)->where("sid",'neq',0)->where($where)->order("creattime desc")->select();

       require_once ROOT_PATH.'public/plugin/PHPExcel/PHPExcel.php';
       $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()->setCreator("导出多商户订单列表")
            ->setLastModifiedBy("导出多商户订单列表")
            ->setTitle("导出多商户订单列表")
            ->setSubject("导出多商户订单列表")
            ->setDescription("导出多商户订单列表")
            ->setKeywords("导出多商户订单列表")
            ->setCategory("导出多商户订单列表");
        $objPHPExcel->getActiveSheet()->setCellValue('A1', '下单时间');
        $objPHPExcel->getActiveSheet()->setCellValue('B1', '订单编号');
        $objPHPExcel->getActiveSheet()->setCellValue('C1', '数量*单价');
        $objPHPExcel->getActiveSheet()->setCellValue('D1', '实付');
        $objPHPExcel->getActiveSheet()->setCellValue('E1', '店铺');
        $objPHPExcel->getActiveSheet()->setCellValue('F1', '商品信息');
        $objPHPExcel->getActiveSheet()->setCellValue('G1', '姓名');
        $objPHPExcel->getActiveSheet()->setCellValue('H1', '联系方式');
        $objPHPExcel->getActiveSheet()->setCellValue('I1', '地址');
        $objPHPExcel->getActiveSheet()->setCellValue('J1', '类型');
        $objPHPExcel->getActiveSheet()->setCellValue('K1', '状态');
        $objPHPExcel->getActiveSheet()->setCellValue('L1', '万能表单提交信息');


        foreach($orders as $k => $v){
            $num=$k+2;
            $v['creattime'] = date("Y-m-d H:i:s",$v['creattime']);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('A'.$num, $v['creattime'],'s');
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('B'.$num, $v['order_id'],'s');
            $v['jsondata'] = unserialize($v['jsondata']);
            foreach($v['jsondata'] as $j => $f){
                if(isset($f['baseinfo']['title'])){
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('F'.$num,$f['baseinfo']['title'],'s');
                }else{
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('F'.$num,'','s');
                }
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('C'.$num, $f['proinfo']['price']."*".$f['num'],'s');
            }


            $objPHPExcel->getActiveSheet()->setCellValueExplicit('D'.$num, $v['price'],'s');

            if($v['sid'] == '0'){
                $v['shopname'] = '总平台';
            }else{
                $v['shopname'] = Db::name('wd_xcx_shops_shop')->where('uniacid',$appletid)->where("id",$v['sid'])->field('name')->find()['name'];
            }
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('E'.$num, $v['shopname'],'s');
            // 转换地址
            if($v['address']!=0){
                $v['address_get'] = Db::name('wd_xcx_duo_products_address')->where('openid',$v['openid'])->where('id',$res['address'])->find();
                if(!$v['address_get']){
                    $v['address_get']=unserialize($v['m_address']);
                    if(!isset($v['address_get']['name'])){
                        $v['address_get']['name'] = "";
                    }
                    if(!isset($v['address_get']['mobile'])){
                        $v['address_get']['mobile'] = "";
                    }
                    if(!isset($v['address_get']['address'])){
                        $v['address_get']['address'] = "";
                    }

                    if(!isset($v['address_get']['postalcode'])){
                        $v['address_get']['postalcode'] = "";
                    }
                    if(!isset($v['address_get']['more_address'])){
                        $v['address_get']['more_address'] = "";
                    }
                }
            }else{
                $v['address_get'] = unserialize($v['m_address']);
                if(!isset($v['address_get']['name'])){
                    $v['address_get']['name'] = "";
                }
                if(!isset($v['address_get']['mobile'])){
                    $v['address_get']['mobile'] = "";
                }
                if(!isset($v['address_get']['address'])){
                    $v['address_get']['address'] = "";
                }

                if(!isset($v['address_get']['postalcode'])){
                    $v['address_get']['postalcode'] = "";
                }
                if(!isset($v['address_get']['more_address'])){
                    $v['address_get']['more_address'] = "";
                }
            }
            if($v['address_get']){
                $objPHPExcel->getActiveSheet()->setCellValue('G'.$num, $v['address_get']['name'],'s');
                $objPHPExcel->getActiveSheet()->setCellValue('H'.$num, $v['address_get']['mobile'],'s');
                $objPHPExcel->getActiveSheet()->setCellValue('I'.$num, $v['address_get']['address'].$v['address_get']['more_address'],'s');
            }

            if($v['nav'] == 1){
                $msgs = "发货订单";
            }else{
                $msgs = "自提订单";
            }
            $objPHPExcel->getActiveSheet()->setCellValue('J'.$num, $msgs);

            $a="";
            if($v['flag']==0){
                $a="未支付";
            }else if($v['flag'] ==2){
                $a="已结算";
            }else if($v['flag'] ==3){
                $a="已过期";
            }else if($v['flag']==4){
                $a="已发货";
            }else if($v['flag']==1){
                if($v['nav']==1){
                    $a="待发货";
                }else{
                    $a="待核销";
                }
            }
            $objPHPExcel->getActiveSheet()->setCellValue('K'.$num, $a,'s');



            $forminfo = "";
            $v['formcon'] = Db::name('wd_xcx_formcon')->where('uniacid',$appletid)->where('id',$v['formid'])->find();
            $v['formcon'] = unserialize($v['formcon']['val']);
            if($v['formcon']){
                foreach ($v['formcon'] as $kk => $vv) {
                    if($vv['type']== 3){
                        $type3_info = "";
                        foreach ($vv['val'] as $key => $value) {
                            $type3_info = $type3_info.$value.",";
                        }

                        $forminfo = $forminfo.$vv['name'].":".$type3_info.";\r\n";
                    }
                    if($vv['type']== 5){
                        $type5_info = "";
                        foreach ($vv['z_val'] as $key => $value) {
                            $type5_info = $type5_info.remote($appletid, $value, 1).",";
                        }

                        $forminfo = $forminfo.$vv['name'].":".$type5_info.";\r\n";
                    }
                    if($vv['type'] != 5 && $vv['type'] != 3){
                        $forminfo = $forminfo.$vv['name']."：".$vv['val'].";\r\n";
                    }
                }
            }
            $objPHPExcel->getActiveSheet()->getStyle("L".$num)->getAlignment()->setWrapText(TRUE); 
            $objPHPExcel->getActiveSheet()->setCellValue('L'.$num, $forminfo);
        }
        $objPHPExcel->getActiveSheet()->setTitle('导出多商户订单列表');
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="多商户订单列表.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

    //生成二维码
    public function qrcode(){
        $uniacid = input("appletid");
        $cid = input("shopid");
        $staffid = input("staffid");
        $tableid = input("tableid");
        $str = input('str');

        $app = Db::name('wd_xcx_applet')->where("id",$uniacid)->find();
        if($str == 'wx'){
            $appid = $app['appID'];
            $appsecret = $app['appSecret'];
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $appid . "&secret=" . $appsecret;
            $weixin = file_get_contents($url);
            $jsondecode = json_decode($weixin); //对JSON格式的字符串进行编码
            $array = get_object_vars($jsondecode);//转换成数组
            $access_token = $array['access_token'];//输出token
                
            $ewmurl = "https://api.weixin.qq.com/wxa/getwxacode?access_token=" . $access_token;
            if($cid > 0){
                $sharepath = 'pagesPluginShop/shop/shop?cid='.$cid;
            }else if($staffid > 0){
                $sharepath = 'pagesCards/card_info/card_info?id='.$staffid;
            }else if($tableid > 0){
                $sharepath = 'pagesFood/food/food?id='.$tableid;
            }

            $data = array(
                "path" => $sharepath,
                "width" => '80'
            );
            $datas = json_encode($data);
            $result = $this->_Postrequest($ewmurl, $datas);
            $root = ROOT_PATH;
            $path = "public/ewmimg/wx/{$uniacid}" . date('Ym');
            $newpath = $root . $path;
            $sjc = time() . rand(1000, 9999);
            if (!file_exists($newpath)) {
                mkdir($newpath);
            }
            file_put_contents($newpath . "/" . $uniacid . date('Ym') . $sjc . ".jpg", $result);
            $imgpath = ROOT_HOST . "/ewmimg/wx/{$uniacid}" . date('Ym') . "/" . $uniacid . date('Ym') . $sjc . ".jpg";
            if (strpos($imgpath, 'https') === false) {
                $imgpath = "https" . substr($imgpath, 4);
            }
            return $imgpath;
        }else if($str == 'baidu'){

        }else if($str == 'alipay'){
            $appid = $app['ali_appID'];     
            include_once ROOT_PATH.'application/api/controller/AopClient.php';
            include_once ROOT_PATH.'application/api/controller/alipaysdk/aop/request/AlipayOpenAppQrcodeCreateRequest.php';

            $aop = new \app\api\controller\aopClient;
            $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do'; 

            $aop->appId = $appid;
            $aop->rsaPrivateKey = $app['ali_private_key'];
            $aop->alipayrsaPublicKey = $app['ali_public_key'];
            $aop->apiVersion = '1.0';
            $aop->signType = 'RSA2';
            $aop->postCharset='UTF-8';
            $aop->format='json';
            $request = new \AlipayOpenAppQrcodeCreateRequest();

            if($cid > 0){
                $request->setBizContent("{" .
                "\"url_param\":\"/pagesPluginShop/shop/shop?cid=".$cid."\"," .
                "\"query_param\":\"cid=".$cid."\"," .
                "\"describe\":\"二维码描述\"" .
                "  }");
            }else if($staffid > 0){
                $request->setBizContent("{" .
                "\"url_param\":\"/pagesCards/card_info/card_info?id=".$staffid."\"," .
                "\"query_param\":\"id=".$staffid."\"," .
                "\"describe\":\"二维码描述\"" .
                "  }");
            }else if($tableid > 0){
                $request->setBizContent("{" .
                "\"url_param\":\"/pagesFood/food/food?id=".$tableid."\"," .
                "\"query_param\":\"id=".$tableid."\"," .
                "\"describe\":\"二维码描述\"" .
                "  }");
            }
            $result = $aop->execute ($request); 
            $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
            $resultCode = $result->$responseNode->code;
            if($resultCode == '10000'){
                $sjc = time() . rand(1000, 9999);
                // $res = file_get_contents($result->$responseNode->qr_code_url);
                $ch = curl_init();
                $timeout = 20;
                curl_setopt($ch,CURLOPT_URL,$result->$responseNode->qr_code_url);
                curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
                curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
                $res = curl_exec($ch);

                curl_close($ch);
                $path = "public/ewmimg/ali/{$uniacid}" . date('Ym');
                $root = ROOT_PATH;
                $newpath = $root . $path;
                $sjc = time() . rand(1000, 9999);
                if (!file_exists($newpath)) {
                    mkdir($newpath);
                }

                file_put_contents($newpath . "/" . $uniacid . date('Ym') . $sjc . ".jpg", $res);
                $imgpath = ROOT_HOST . "/ewmimg/ali/{$uniacid}" . date('Ym') . "/" . $uniacid . date('Ym') . $sjc . ".jpg";
                if (strpos($imgpath, 'https') === false) {
                    $imgpath = "https" . substr($imgpath, 4);
                }
                return $imgpath;
            }
        }else if($str == 'bdance'){
            $appid = $app['bdance_appID'];
            $appsecret = $app['bdance_appSecret'];
            $url = "https://developer.toutiao.com/api/apps/token?grant_type=client_credential&appid=" . $appid . "&secret=" . $appsecret;
            $weixin = file_get_contents($url);
            $jsondecode = json_decode($weixin, true); //对JSON格式的字符串进行编码
            $access_token = $jsondecode['access_token'];//输出token
            $ewmurl = "https://developer.toutiao.com/api/apps/qrcode";
            if($cid > 0){
                $sharepath = '/pagesPluginShop/shop/shop?cid='.$cid;
            }else if($staffid > 0){
                $sharepath = '/pagesCards/card_info/card_info?id='.$staffid;
            }else if($tableid > 0){
                $sharepath = '/pagesFood/food/food?id='.$tableid;
            }
            $data = array(
                "access_token" => $access_token,
                "appname" => "toutiao",
                "path" => $sharepath,
                "width" => 280,
            );

            $datas = json_encode($data);

            $result = $this->_Postrequest($ewmurl, $datas);
            $root = ROOT_PATH;
            $path = "public/ewmimg/bdance/{$uniacid}" . date('Ym');
            $newpath = $root . $path;
            $sjc = time() . rand(1000, 9999);
            if (!file_exists($newpath)) {
                mkdir($newpath);
            }
            file_put_contents($newpath . "/" . $uniacid . date('Ym') . $sjc . ".jpg", $result);
            $imgpath = ROOT_HOST . "/ewmimg/bdance/{$uniacid}" . date('Ym') . "/" . $uniacid . date('Ym') . $sjc . ".jpg";
            if (strpos($imgpath, 'https') === false) {
                $imgpath = "https" . substr($imgpath, 4);
            }
            return $imgpath;
        }else if($str == 'h5'){
            Vendor('phpqrcode.phpqrcode');
            $errorCorrectionLevel =intval(3) ;//容错级别 
            $matrixPointSize = intval(4);//生成图片大小 
             //生成二维码图片 
            $object = new \QRcode();
            $sjc = time() . rand(1000, 9999);
            $newpath = ROOT_PATH."public/ewmimg/h5/{$uniacid}" . date('Ym');
            if(!file_exists($newpath)){
                mkdir($newpath);
            }
            $filename = ROOT_HOST."/ewmimg/".$sjc.".png";

            $filename = $newpath . "/" . $uniacid . date('Ym') . $sjc . ".png";
            if($cid > 0){
                $url = 'https://'.$_SERVER['SERVER_NAME'].'/h5/index.html?id='.$uniacid.'#/pagesPluginShop/shop/shop?cid='.$cid;
            }else if($staffid > 0){
                $url = 'https://'.$_SERVER['SERVER_NAME'].'/h5/index.html?id='.$uniacid.'#/pagesCards/card_info/card_info?id='.$staffid;
            }else if($tableid > 0){
                $url = 'https://'.$_SERVER['SERVER_NAME'].'/h5/index.html?id='.$uniacid.'#/pagesFood/food/food?scene='.$tableid;
            }
            $object->png($url, $filename, $errorCorrectionLevel, $matrixPointSize, 2); 
            $imgpath = ROOT_HOST . "/ewmimg/h5/{$uniacid}" . date('Ym') . "/" . $uniacid . date('Ym') . $sjc . ".png";
            if (strpos($imgpath, 'https') === false) {
                $imgpath = "https" . substr($imgpath, 4);
            }
            return $imgpath;

        }


       // $tdata = array(
       //      "ewm" => $path
       //  );
        // $res = Db::name('wd_xcx_shops_shop') ->where('id', $cid) ->update($tdata);
        // if($res){
        //     echo $path;
        // }else{
        //     echo 2;
        // }
    }
    public function downloadimg()
    {
        $files = input("src");
        $type = input("type");

        $files = str_replace($_SERVER['SERVER_NAME'], '', $files);

        $files = ROOT_PATH.'public/'.$files;

        $filename = $type.'.jpg';
        header("Content-type: application/octet-stream");
        header("Content-Disposition: attachment; filename={$filename};");
        header("Content-Length: ". filesize($files));
        readfile($files);
    }
    function _Postrequest($url, $data, $ssl = true)
    {   
        $headers = [
            "Content-type: application/json;charset='utf-8'"
        ];
        //curl完成
        $curl = curl_init();
        //设置curl选项
        curl_setopt($curl, CURLOPT_URL, $url);//URL
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:38.0) Gecko/20100101 Firefox/38.0 FirePHP/0.7.4';
        curl_setopt($curl, CURLOPT_USERAGENT, $user_agent);//user_agent，请求代理信息
        curl_setopt($curl, CURLOPT_AUTOREFERER, true);//referer头，请求来源
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);//设置超时时间
        //SSL相关
        if ($ssl) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//禁用后cURL将终止从服务端进行验证
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);//检查服务器SSL证书中是否存在一个公用名(common name)。
        }
        // 处理post相关选项
        curl_setopt($curl, CURLOPT_POST, true);// 是否为POST请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);// 处理请求数据
        // 处理响应结果
        curl_setopt($curl, CURLOPT_HEADER, false);//是否处理响应头
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);//curl_exec()是否返回响应结果
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers); 
        // 发出请求
        $response = curl_exec($curl);
        if (false === $response) {
            echo '<br>', curl_error($curl), '<br>';
            return false;
        }
        curl_close($curl);
        return $response;
    }


}
