<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;


class Fx extends Base
{
    public function base(){

        if(check_login()){


            if(powerget()){

                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);


                $item = Db::name('wd_xcx_fx_gz')->where("uniacid",$appletid)->find();
                if($item){
                	if($item['sq_thumb']){
                		$item['sq_thumb'] = remote($appletid,$item['sq_thumb'],1);
                	}
                }


                $this->assign('item',$item);
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

            return $this->fetch('base');
        }else{
            $this->redirect('Login/index');
        }
        
    }
    public function extension(){

        if(check_login()){


            if(powerget()){

                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);


                $item = Db::name('wd_xcx_fx_gz')->where("uniacid",$appletid)->find();
                if($item['thumb']){
                    $item['thumb'] = remote($appletid,$item['thumb'],1);
                }

                $this->assign('item',$item);
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

            return $this->fetch('extension');
        }else{
            $this->redirect('Login/index');
        }
        
    }
    public function extensionsave(){
        $uniacid = input("appletid");
        $thumb = input("commonuploadpic");
        if($thumb==null){
            $this->error("分销推广图不能为空");
            exit;
        }
        $data = array(
            "thumb" => remote($uniacid,$thumb,2),
        );
        $item = Db::name('wd_xcx_fx_gz')->where("uniacid",$uniacid)->find();
        if($item){
            $res = Db::name('wd_xcx_fx_gz')->where("uniacid",$uniacid)->update($data);
        }else{
            $res = Db::name('wd_xcx_fx_gz')->insert($data);
        }
        if($res){
            $this->success('分销推广图更新成功！');
        }else{
            $this->error('分销推广图更新失败，没有修改项！');
            exit;
        }
    }
    public function basesave(){
        $uniacid = input("appletid");
        $fxs_name = input("fxs_name");
        if(!$fxs_name){
            $this->error("分销商名称不能为空");
            exit;
        }
        $sq_thumb2 = input("sq_thumb2");
        $sq_thumb = input("commonuploadpic");
        if(!$sq_thumb){
            if($sq_thumb2){
                $sq_thumb = $sq_thumb2;
            }else{
                $this->error("申请图片不能为空");
                exit;
            }
        }else{
            $sq_thumb = remote($uniacid,$sq_thumb,2);
        }

        $fx_msg = trim(input('fx_msg')) ? trim(input('fx_msg')) : "分销商的商品销售统一由厂家直接收款、直接发货，并提供产品的售后服务，返校佣金由厂家统一设置。";

        $data = array(
            "fxs_name" => input("fxs_name"),
            "sq_thumb" => $sq_thumb,
            "uniacid" => input("appletid"),
            "fx_cj" => intval(input('fx_cj')),
            "one_bili" => intval(input('one_bili')),
            "two_bili" => intval(input('two_bili')),
            "three_bili" => intval(input('three_bili')),
            "sxj_gx" => intval(input('sxj_gx')),
            "fxs_sz" => intval(input('fxs_sz')),
            "fxs_sz_val" => intval(input('fxs_sz_val')),
            "fx_msg" => $fx_msg
        );

        $txmoney = input('txmoney');
        if($txmoney){
            $data['txmoney'] = $txmoney;
        }
        if(input('types/a')){
            $b=input('types/a');
            $a='';
            for($i=0;$i<count($b);$i++){
                if($i<count($b)){
                  $a.=$b[$i].',';
                }else{
                    $a.=$b[$i];
                }

            }
            $data['tx_type'] = $a;
        }else{
            $data['tx_type'] ="1,2,3";
        }


       
        $item = Db::name('wd_xcx_fx_gz')->where("uniacid",$uniacid)->find();
        if($item){
            $res = Db::name('wd_xcx_fx_gz')->where("uniacid",$uniacid)->update($data);
        }else{
            $res = Db::name('wd_xcx_fx_gz')->insert($data);
        }
        if($res){
            $this->success('分销基本信息更新成功！');
        }else{
            $this->error('分销基本信息更新失败，没有修改项！');
            exit;
        }
    }
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

    public function relation(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $item = Db::name('wd_xcx_fx_gz')->where("uniacid",$appletid)->find();
                $this->assign('item',$item);
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
            return $this->fetch('relation');
        }else{
            $this->redirect('Login/index');
        }
        
    }

    public function relationsave(){
        $data = array(
            "uniacid" => input("appletid"),
            "sxj_gx" => intval(input('sxj_gx')),
            "fxs_sz" => intval(input('fxs_sz')),
            "fxs_sz_val" => intval(input('fxs_sz_val'))
        );
        $uniacid = input("appletid");
        $item = Db::name('wd_xcx_fx_gz')->where("uniacid",$uniacid)->find();
        if($item){
            $res = Db::name('wd_xcx_fx_gz')->where("uniacid",$uniacid)->update($data);
        }else{
            $res = Db::name('wd_xcx_fx_gz')->insert($data);
        }
        if($res){
            $this->success('上下级关系及分销资格更新成功！');
        }else{
            $this->error('上下级关系及分销资格更新失败，没有修改项！');
            exit;
        }
    }
    public function agree(){

        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $item = Db::name('wd_xcx_fx_gz')->where("uniacid",$appletid)->find();
                $this->assign('item',$item);
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
            return $this->fetch('agree');
        }else{
            $this->redirect('Login/index');
        }
        
    }

    public function agreesave(){
        $data['fxs_xy'] = input("content");
        $uniacid = input("appletid");
        $item = Db::name('wd_xcx_fx_gz')->where("uniacid",$uniacid)->find();
        if($item){
            $res = Db::name('wd_xcx_fx_gz')->where("uniacid",$uniacid)->update($data);
        }else{
            $res = Db::name('wd_xcx_fx_gz')->insert($data);
        }
        if($res){
            $this->success('分销商申请协议更新成功！');
        }else{
            $this->error('分销商申请协议更新失败，没有修改项！');
            exit;
        }
    }

    public function dealer(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $opt=input('opt');
                $id = input('id');
                $val = input('val');
                if($opt=="shenhe"){
                    $users = Db::name('wd_xcx_fx_sq')->where("id",$id)->find();
                    Db::name('wd_xcx_fx_sq')->where("id",$id)->update(array("flag"=>$val));
                    if($val==2){
                        Db::name('wd_xcx_superuser')->where("id",$users['suid'])->where('uniacid',$appletid)->update(array("fxs"=>2,"fxsstop"=>1));

                        $fxs_sz = Db::name('wd_xcx_fx_gz')->where('uniacid', $appletid)->value("fxs_sz");
                        $fxsstop = Db::name('wd_xcx_superuser')->where('uniacid', $appletid)->where("id",$users['suid'])->value("fxsstop");
                        if(($fxs_sz == "2" || $fxsstop == "2") && $users['source'] != 3){
                            $jsons = array(
                                "truename" => $users['truename'],
                                "content" => "申请成为分销商",
                                "creattime" => date("Y-m-d H:i:s", time()),
                                "notice" => "恭喜您已成为分销商！"
                            );
                            if($users['source'] == 1){
                                $tel = Db::name('wd_xcx_superuser')->where('uniacid', $appletid)->where("id",$users['suid'])->value("phone");
                                $jsons['tel'] = $tel;
                                $jsons['msg'] = "通过";
                                $jsons = serialize($jsons);
                                sendSubscribe($appletid, 7, $users['openid'], $jsons);
                            }else{
                                $jsons = serialize($jsons);
                                tpl_send($appletid, 7, $users['openid'], $users['source'], $users['formid'], $jsons);
                            }
                        }
                    }else{
                        $tel = Db::name('wd_xcx_superuser')->where('uniacid', $appletid)->where("id",$users['suid'])->value("phone");
                        $jsons = [];
                        $jsons = array(
                            "truename" => $users['truename'],
                            "msg" => "不通过",
                            "tel" => $tel,
                        );
                        $jsons = serialize($jsons);
                        sendSubscribe($appletid, 7, $users['openid'], $jsons);
                    }
                    $this->success('审核成功');
                }else if($opt=="jinyong"){
                    if($val==2){
                        Db::name('wd_xcx_superuser')->where("id",$id)->where('uniacid',$appletid)->update(array("fxsstop"=>2));
                    }
                    $this->success('禁用成功');

                }else{
                    $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                    if(!$res){
                        $this->error("找不到对应的小程序！");
                    }
                    $this->assign('applet',$res);

					//$users = Db::name('wd_xcx_fx_sq')->where("uniacid",$appletid)->where('flag',1)->select();
					$users_list = Db::name('wd_xcx_superuser')->where("uniacid",$appletid)->where('fxs',2)->paginate(10, false, ['query' => ['appletid' => $appletid]]);

                    $users = $users_list->toArray()['data'];

					foreach ($users as $key => &$res) {
					    //$res['flag'] = 2;

                        $ava = getNameAvatar($res['id'], $appletid);
                        $res['nickname'] = $ava['nickname'];
                        $res['avatar'] = $ava['avatar'];
					    // $res['nickname'] = $this->getNickname(1, $res['id'], $appletid);
					    // $res['avatar'] = $this->getAvatar(1, $res['id'], $appletid);
					    // if(empty($res['nickname']) && empty($res['avatar'])){
         //                	$res['nickname'] = $this->getNickname(2, $res['id'], $appletid);
         //                	$res['avatar'] = $this->getAvatar(2, $res['id'], $appletid);
         //                }
         //                if(empty($res['nickname']) && empty($res['avatar'])){
         //                	$res['nickname'] = $res['phone'];
         //                	$res['avatar'] = "";
         //                }
         //                $res['nickname'] = rawurldecode($res['nickname']);
					    //获取我的下级分销商
					    $fxs_son = Db::name('wd_xcx_superuser') ->where('parent_id', $res['id']) ->where('fxs', 2)->field('id')->select();
					    if($fxs_son){
					        foreach ($fxs_son as $key => &$value) {
                                $info = getNameAvatar($value['id'], $appletid);
                                $value['nickname'] = $info['nickname'];
                                $value['avatar'] = $info['avatar'];

					            // $value['nickname'] = $this->getNickname(1, $value['id'], $appletid);
                 //                $value['avatar'] = $this->getAvatar(1, $value['id'], $appletid);
                 //                if(empty($value['nickname']) && empty($value['avatar'])){
                 //                	$value['nickname'] = $this->getNickname(2, $value['id'], $appletid);
                 //                	$value['avatar'] = $this->getAvatar(2, $value['id'], $appletid);
                 //                }
                 //                if(empty($value['nickname']) && empty($value['avatar'])){
                 //                	$value['nickname'] = $value['phone'];
                 //                	$value['avatar'] = "";
                 //                }
                 //                $value['nickname'] = rawurldecode($value['nickname']);
					        }
					    }
					    $res['fxs_son'] = $fxs_son;
					}

                    $this->assign('users_list',$users_list);
                    $this->assign('users',$users);
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
            return $this->fetch('dealer');
        }else{
            $this->redirect('Login/index');
        }
    }

    public function txreply(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $op = input('op');
                if($op){
                    if($op == "shenhe"){
                        $id = input('id');
                        $val = input('val');
                        if($val==2){
                            // 更新信息
                            $sqtx = Db::name('wd_xcx_fx_tx')->where("uniacid",$appletid)->where("id",$id)->find();
                            $suid = $sqtx['suid'];
                            $money = $sqtx['money'];
             
                            $user = Db::name('wd_xcx_superuser')->where("uniacid",$appletid)->where("id",$suid)->find();

                            $user_fxgetmoney = $user['fx_getmoney'];
                            $user_fxmoney = $user['fx_money'];
                            $user_money = $user['money'];
                            if($sqtx['types']==1){  //支付到余额

                                $user_money = $user_money + $money;  //我的钱
                                $user_fxgetmoney = $user_fxgetmoney + $money;  //分销获得过的钱
                                $user_fxmoney = $user_fxmoney;   //分销的钱

                                $adata = array(
                                    "money" => $user_money,
                                    "fx_getmoney" => $user_fxgetmoney,
                                    "fx_money" => $user_fxmoney
                                );
                                Db::name('wd_xcx_superuser')->where("uniacid",$appletid)->where("id",$suid)->update($adata);
                                Db::name('wd_xcx_fx_tx')->where("id",$id)->update(array("flag"=>2,"txtime"=>time()));
                                $jdata['uniacid'] = $appletid;
                                $jdata['orderid'] = "";
                                $jdata['uid'] = $user['id'];
                                $jdata['type'] = "add";
                                $jdata['score'] = $money;
                                $jdata['message'] = "分销提现到余额";
                                $jdata['creattime'] = time();
                                Db::name('wd_xcx_money')->insert($jdata);
                            }

                            if($sqtx['types']==2){  //支付到微信
                                $app = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                                $suid= $sqtx['suid'];    //申请者的openid
                                $money= $sqtx['money'];  //申请了提现多少钱
                                $userinfo = Db::name('wd_xcx_user')->where("uniacid",$appletid)->where("suid",$suid)->field("openid,nickname")->find();
                                if(empty($userinfo)){
                                	$this->error("该用户尚未绑定微信小程序！");
                                }
                                $nickname = $userinfo['nickname'];
                                $openid = $userinfo['openid'];
                                $mchid = $app['mchid'];   //商户号
                                $apiKey = $app['signkey'];    //商户的秘钥
                                $appid = $app['appID'];                 //小程序的id
                                $appkey = $app['appSecret'];            //小程序的秘钥

                                include 'weixin_zf.php';
                                //②、付款
                                $now = time();
                                $order_id = $order = date("Y",$now).date("m",$now).date("d",$now).date("H",$now).date("i",$now).date("s",$now).rand(1000,9999);

                                $wxPay = new WxpayService($mchid,$appid,$appkey,$apiKey);
                                $result = $wxPay->createJsBizPackage($openid,$money,$order_id,$nickname,$appletid);

                                if($result){
                                    $user_fxgetmoney = $user_fxgetmoney + $money;
                                    $user_fxmoney = $user_fxmoney - $money;

                                    $adata = array(
                                        "fx_getmoney" => $user_fxgetmoney,
                                        "fx_money" => $user_fxmoney
                                    );
                                    Db::name('wd_xcx_superuser')->where("uniacid",$appletid)->where("id",$suid)->update($adata);
                                    Db::name('wd_xcx_fx_tx')->where("id",$id)->update(array("flag"=>2,"txtime"=>time()));
                                }
                            }
                            if($sqtx['types']==3 || $sqtx['types']==4){  //支付到3支付宝 4银行卡
                                $user_money = $user_money + $money;
                                $user_fxgetmoney = $user_fxgetmoney + $money;
                                $user_fxmoney = $user_fxmoney;

                                $adata = array(
                                    "money" => $user_money,
                                    "fx_getmoney" => $user_fxgetmoney,
                                    "fx_money" => $user_fxmoney
                                );

                                Db::name('wd_xcx_superuser')->where("uniacid",$appletid)->where("id",$suid)->update($adata);
                                Db::name('wd_xcx_fx_tx')->where("id",$id)->update(array("flag"=>2,"txtime"=>time()));
                            }
                            if($sqtx['source'] == 1){
                                $openid = Db::name('wd_xcx_user')->where('suid', $sqtx['suid'])->value('openid');
                                $jsons = [
                                    'fprice' => $money,
                                    'msg' => "审核通过",
                                ];
                                $jsons = serialize($jsons);
                                sendSubscribe($appletid, 9, $openid, $jsons);
                            }
                            $this->success("提现成功 新增/修改成功");

                        }
                        if($val==3){
                            Db::name('wd_xcx_fx_tx')->where("id",$id)->update(array("flag"=>3,"txtime"=>time()));
                            // 并吧钱还原过去
                            $sqtx = Db::name('wd_xcx_fx_tx')->where("id",$id)->where("uniacid",$appletid)->find();

                            $suid = $sqtx['suid'];
                            $money = $sqtx['money'];

                            $user = Db::name('wd_xcx_superuser')->where("id",$suid)->where("uniacid",$appletid)->find();

                            $fx_money = $user['fx_money'];

                            $new_fx_money = $fx_money*1 + $money*1;
                            Db::name('wd_xcx_superuser')->where("uniacid",$appletid)->where("id",$suid)->update(array("fx_money"=>$new_fx_money));
                            
                            if($sqtx['source'] == 1){
                                $openid = Db::name('wd_xcx_user')->where('suid', $sqtx['suid'])->value('openid');
                                $jsons = [
                                    'fprice' => $money,
                                    'msg' => "审核被拒",
                                ];
                                $jsons = serialize($jsons);
                                sendSubscribe($appletid, 9, $openid, $jsons);
                            }
                            $this->success("提现申请拒绝成功!");
                        }
                    }
                }else{
                    $sqtx_list = Db::name('wd_xcx_fx_tx')->where("uniacid",$appletid)->order("id desc")->paginate(10, false, ['query' => ['appletid' => $appletid]]);
                    $sqtx = $sqtx_list->toArray()['data'];
                    foreach ($sqtx as $key => &$res) {
                        $user = Db::name('wd_xcx_superuser')->where("uniacid",$appletid)->where('id',$res['suid'])->find();
                        $nameAvatar = $this->getNameAvatar($res['suid'], $appletid);
                        $user['nickname'] = $nameAvatar['nickname'];
                        $user['avatar'] = $nameAvatar['avatar'];
                        $res['userinfo'] = $user;
                        $res['creattime'] = date("Y-m-d H:i:s", $res['creattime']);
                    }

                    $this->assign("sqtx_list",$sqtx_list);
                    $this->assign("sqtx",$sqtx);
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

            return $this->fetch('txreply');
        }else{
            $this->redirect('Login/index');
        }
    }

    public function getNameAvatar($suid, $uniacid){
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

    public function txset(){
        if(check_login()){
            if(powerget()){

                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);

                $item = Db::name('wd_xcx_fx_gz')->where("uniacid",$appletid)->find();
                $this->assign("item",$item);
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

            return $this->fetch('txset');
        }else{
            $this->redirect('Login/index');
        }
    }

    public function txsetsave(){
        $uniacid = input("appletid");
        $txmoney = input('txmoney');
        $cert = input('certtext');
        if(!$cert){
            $this->error("apiclient_cert.pem不能为空");
            exit;
        }
        $key = input('keytext');
        if(!$key){
            $this->error("apiclient_key.pem不能为空");
            exit;
        }
        $ca = input('catext');
        if(!$ca){
            $this->error("rootca.pem不能为空");
            exit;
        }
        $data = array(
            "txmoney" => $txmoney,
            "uniacid" => $uniacid,
            "certtext" => $cert,
            "keytext" => $key,
            "catext" => $ca
        );
        if($cert && $key && $ca){
            $cert_path = ROOT_PATH."public/Cert/".$uniacid."/apiclient_cert.pem";
            $key_path = ROOT_PATH."public/Cert/".$uniacid."/apiclient_key.pem";
            $ca_path = ROOT_PATH."public/Cert/".$uniacid."/rootca.pem";
            $path = ROOT_PATH."public/Cert";

            if(!file_exists($path)){
                if (mkdir($path)) {
                    $upath = ROOT_PATH."public/Cert/".$uniacid."/";
                    if(!file_exists($upath)){
                        mkdir($upath);
                    }
                }
            }else{
                $upath = ROOT_PATH."public/Cert/".$uniacid."/";
                if(!file_exists($upath)){
                    mkdir($upath);
                }
            }

            file_put_contents($cert_path, $cert);
            file_put_contents($key_path, $key);
            file_put_contents($ca_path, $ca);
        }

        if(input('types/a')){
            $b=input('types/a');
            $a='';
            for($i=0;$i<count($b);$i++){
                if($i<count($b)){
                  $a.=$b[$i].',';
                }else{
                    $a.=$b[$i];
                }

            }
            $data['tx_type'] = $a;
        }else{
            $data['tx_type'] ="1,2,3";
        }

        $item = Db::name('wd_xcx_fx_gz')->where("uniacid",$uniacid)->find();
        if($item){
            $res = Db::name('wd_xcx_fx_gz')->where("uniacid",$uniacid)->update($data);
        }else{
            $res = Db::name('wd_xcx_fx_gz')->insert($data);
        }
        if($res){
            $this->success('提现设置更新成功！');
        }else{
            $this->error('提现设置更新失败，没有修改项！');
            exit;
        }
    }

    public function getNickname($source, $suid, $appletid){
    	if($suid){
    		if($source == 1){
	    		$nickname = Db::name('wd_xcx_user')->where('uniacid', $appletid)->where('suid', $suid)->value('nickname');
	    		return rawurldecode($nickname);
	    	}else if($source == 2){
	    		$nickname = Db::name('wd_xcx_ali_user')->where('uniacid', $appletid)->where('suid', $suid)->value('nick_name');
	    		return rawurldecode($nickname);
	    	}else if($source == 3){
	    		$nickname = Db::name('wd_xcx_superuser')->where('uniacid', $appletid)->where('id', $suid)->value('phone');
	    		return $nickname;
	    	}
    	}else{
    		return "";
    	}
    	
    }

    public function getAvatar($source, $suid, $appletid){
    	if($suid){
	    	if($source == 1)
	    		return Db::name('wd_xcx_user')->where('uniacid', $appletid)->where('suid', $suid)->value('avatar');
	    	else if($source == 2)
	    		return Db::name('wd_xcx_ali_user')->where('uniacid', $appletid)->where('suid', $suid)->value('avatar');
	    	else if($source == 3)
	    		return "";
    	}else{
    		return "";
    	}
    }

    public function order(){
        if(check_login()){
            if(powerget()){

                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);

                $item = Db::name('wd_xcx_fx_gz')->where("uniacid",$appletid)->find();
                $this->assign("item",$item);

                $search_flag = intval(input('search_flag'));
                $search_keys = input('search_keys');
                $start_get = input('start_get') ? strtotime(input('start_get')) : '';
                $end_get = input('end_get') ? strtotime(input('end_get')) : '';
                $where = [];
                if ($search_flag > 0) {
                    $where['flag'] = $search_flag;
                }


                if ($start_get) {
                    $where['creattime'] = ['>=', $start_get];
                }

                if ($end_get) {
                    $where['creattime'] = ['<=', $end_get];
                }

                if ($search_keys) {
                    $where['order_id'] = ['like',"%".$search_keys."%"];
                }
                if($start_get){
                    $start_get = date("Y-m-d H:i:s", $start_get);
                }
                if($end_get){
                    $end_get = date("Y-m-d H:i:s", $end_get);
                }
                $orderlist = Db::name('wd_xcx_fx_ls')->where("uniacid",$appletid)->where($where)->order("id desc")->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"),'search_flag'=>$search_flag, 'start_get' => $start_get, 'end_get' => $end_get,'search_keys' => $search_keys)]);
                $orders = $orderlist->toArray()['data'];
                foreach ($orders as $key => &$res) {
                    $v = 0;
                    $bili = 0;
                    // 根据订单号去订单里面去jsondata
                    if($res['types'] == 'duo'){
                        $orderinfo = Db::name('wd_xcx_duo_products_order')->where("uniacid",$appletid)->where('order_id',$res['order_id'])->find();
                        $res['datas'] = $orderinfo['jsondata'] ? unserialize($orderinfo['jsondata']) : '';
                        $res['order'] = $orderinfo;
                        $res['counts'] = $orderinfo['jsondata'] ? count(unserialize($orderinfo['jsondata'])) : 0;
                        $res['hxtime'] = date("Y-m-d H:i",$orderinfo['hxtime']);
                        $jsdata = unserialize($orderinfo['jsondata']);
                        $res['type']=1;
                    }elseif($res['types'] == 'miaosha' || $res['types'] == 'reserve'){
                        $orderinfo = Db::name('wd_xcx_order')->where("uniacid",$appletid)->where('order_id',$res['order_id'])->find();
                        $res['datas'] = [
                            0 => [
                                'baseinfo' => [
                                    'title' => $orderinfo['product'],
                                    'thumb' => $orderinfo['thumb']
                                ],
                                'proinfo' => [
                                    'price' => $orderinfo['price'],
                                    'ggz' => '',
                                ],
                            ]
                        ];

                        if($res['types'] == 'miaosha' || ($res['types'] == 'reserve' && $orderinfo['is_more'] == 0)){
                            $res['datas'][0]['num'] = $orderinfo['num'];
                        }else{
                            $order_num = 0;
                            if($orderinfo['order_duo']){
                                $orderinfo['order_duo'] = unserialize($orderinfo['order_duo']);
                                foreach($orderinfo['order_duo'] as $ikm){
                                    $order_num += $ikm[4];
                                }
                            }
                            $res['datas'][0]['num'] = $order_num;
                        }


                        $res['order'] = $orderinfo;
                        $res['counts'] = $orderinfo['num'];
                        $res['hxtime'] = date("Y-m-d H:i",$orderinfo['custime']);
                        $res['type']=1;
                    }elseif($res['types'] == 'bargain'){
                        $orderinfo = Db::name('wd_xcx_bargain_order')->where("uniacid",$appletid)->where('order_id',$res['order_id'])->find();
                        $res['datas'] = [
                            0 => [
                                'baseinfo' => [
                                    'title' => $orderinfo['title'],
                                    'thumb' => $orderinfo['thumb']
                                ],
                                'proinfo' => [
                                    'price' => $orderinfo['price'],
                                    'ggz' => '',
                                ],
                                'num' => $orderinfo['num']
                            ]
                        ];
                        $res['order'] = $orderinfo;
                        $res['counts'] = $orderinfo['num'];
                        $res['hxtime'] = date("Y-m-d H:i",$orderinfo['hxtime']);
                        $res['type']=1;
                    }elseif($res['types'] == 'pt'){
                        $orderinfo = Db::name('wd_xcx_pt_order')->where("uniacid",$appletid)->where('order_id',$res['order_id'])->find();
                        $jsdata = unserialize($orderinfo['jsondata']);
                        $res['datas'] = [];
                        foreach ($jsdata as $key => $value) {
                            $goodInfo = Db::name('wd_xcx_pt_pro') ->where('id',$value['baseinfo'])->find();
                            $temp = [
                                'baseinfo' => [
                                    'title' => $goodInfo['title'],
                                    'thumb' => $goodInfo['thumb']
                                ],
                                'proinfo' => [
                                    'price' => $goodInfo['price'],
                                    'ggz' => $value['proval_ggz'],
                                ],
                                'num' => $value['num']
                            ];
                            array_push($res['datas'], $temp);
                        }
                         
                        $res['order'] = $orderinfo;
                        $res['counts'] = count(unserialize($orderinfo['jsondata']));
                        $res['hxtime'] = date("Y-m-d H:i",$orderinfo['hxtime']);
                        $res['type']=1;
                    }elseif($res['types'] == 'mainShop'){
                        $orderinfo = Db::name('wd_xcx_main_shop_order_item')->where("uniacid",$appletid)->where('order_item_id',$res['order_id'])->find();
                        $temp = [
                            'baseinfo' => [
                                'title' => $orderinfo['pro_title'],
                                'thumb' => $orderinfo['pro_thumb']
                            ],
                            'proinfo' => [
                                'price' => $orderinfo['pro_price'],
                                'ggz' => $orderinfo['pro_attr'],
                            ],
                            'num' => $orderinfo['num']
                        ];

                        $pro_fx = unserialize($orderinfo['pro_fx']);

                        $orderinfo['price'] = $pro_fx['fx_base_price'];

                        $res['datas'][] = $temp;
                        $res['order'] = $orderinfo;
                        $res['counts'] = 1;
                        $res['hxtime'] = date("Y-m-d H:i",$orderinfo['check_time']);
                        $res['type']=1;
                    }elseif($res['types'] == 'ext|pdd' || $res['types'] == 'ext|jd'){
                        $orderinfo = Db::name('wd_xcx_external_order')->where("uniacid",$appletid)->where('order_sn',$res['order_id'])->find();
                        $res['datas'] = [
                            0 => [
                                'baseinfo' => [
                                    'title' => $orderinfo['goods_name'],
                                    'thumb' => $orderinfo['goods_thumbnail_url']
                                ],
                                'proinfo' => [
                                    'price' => $orderinfo['goods_price'],
                                    'ggz' => '',
                                ],
                                'num' => $orderinfo['goods_quantity']
                            ]
                        ];
                        $orderinfo['price'] = $orderinfo['order_amount'];
                        $orderinfo['flag'] = $orderinfo['order_status'];
                        $res['order'] = $orderinfo;
                        $res['counts'] = $orderinfo['goods_quantity'];
                        $res['hxtime'] = '';
                        $res['type']=1;
                    }

                    $res['gmz'] = Db::name('wd_xcx_superuser')->where("uniacid",$appletid)->where('id',$res['suid'])->find();
                    $gmzinfo = getNameAvatar($res['suid'], $appletid);
                    $res['gmz']['nickname'] = $gmzinfo['nickname'];
                    $res['gmz']['avatar'] = $gmzinfo['avatar'];

                    if($res['parent_id']){
                        $v = 1;
                        $res["v1"] = Db::name('wd_xcx_superuser')->where("uniacid",$appletid)->where('id',$res['parent_id'])->find();
                        $res["v1"]['hmoney'] = $res['parent_id_get'];
                        $temp1 = getNameAvatar($res['parent_id'], $appletid);
                        $res['v1']['nickname'] = $temp1['nickname'];
                        $res['v1']['avatar'] = $temp1['avatar'];
                    }else{
                    	$res['v1'] = "";
                    }
                    if($res['p_parent_id']){
                        $v = 2;
                        $res["v2"] = Db::name('wd_xcx_superuser')->where("uniacid",$appletid)->where('id',$res['p_parent_id'])->find();
                        $res["v2"]['hmoney'] = $res['p_parent_id_get'];
                        $temp2 = getNameAvatar($res['p_parent_id'], $appletid);
                        $res['v2']['nickname'] = $temp2['nickname'];
                        $res['v2']['avatar'] = $temp2['avatar'];
                    }else{
                    	$res['v2'] = "";
                    }
                    if($res['p_p_parent_id']){
                        $v = 3;
                        $res["v3"] = Db::name('wd_xcx_superuser')->where("uniacid",$appletid)->where('id',$res['p_p_parent_id'])->find();
                        $res["v3"]['hmoney'] = $res['p_p_parent_id_get'];
                        $temp3 = getNameAvatar($res['p_p_parent_id'], $appletid);
                        $res['v3']['nickname'] = $temp3['nickname'];
                        $res['v3']['avatar'] = $temp3['avatar'];
                    }else{
                    	$res['v3'] = "";
                    }
                    $res['creattime'] = date("Y-m-d H:i",$res['creattime']);
                    $res['v'] = $v;
                }
        
                $this->assign('search_flag',$search_flag);
                $this->assign('search_keys',$search_keys);
                $this->assign('start_get',$start_get);
                $this->assign('end_get',$end_get);
                $this->assign('orders',$orders);
                $this->assign('orderlist',$orderlist);

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

    public function getxia(){
        $uniacid = input('uniacid');
        $useid=input('useid');
        //$res=Db::name("wd_xcx_superuser")->where("uniacid",$uniacid)->where("id",$useid)->find();


        $users=Db::name("wd_xcx_superuser")->where("uniacid",$uniacid)->where("parent_id", $useid)->field('id,fxs')->select();
        foreach($users as &$res){
            $u_info = getNameAvatar($res['id'], $uniacid);
            $res['nickname'] = $u_info['nickname'];
            $res['avatar'] = $u_info['avatar'];
      //   	$res['nickname'] = $this->getNickname(1, $res['id'], $uniacid);
		    // $res['avatar'] = $this->getAvatar(1, $res['id'], $uniacid);
		    // if(empty($res['nickname']) && empty($res['avatar'])){
      //       	$res['nickname'] = $this->getNickname(2, $res['id'], $uniacid);
      //       	$res['avatar'] = $this->getAvatar(2, $res['id'], $uniacid);
      //       }
      //       if(empty($res['nickname']) && empty($res['avatar'])){
      //       	$res['nickname'] = $res['phone'];
      //       	$res['avatar'] = "";
      //       }
      //       $res['nickname'] = rawurldecode($res['nickname']);
        }

        return json_encode(array('list'=>$users));
    }

    //分销商申请记录
    public function apply(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);

                //查询所有申请记录
                $record = Db::name('wd_xcx_fx_sq') ->where('uniacid', $appletid) ->order('id desc') ->paginate(10, false,[ 'query' => array('appletid'=>input("appletid"))]);
                $counts = Db::name('wd_xcx_fx_sq') ->where('uniacid', $appletid) ->count();

                $this->assign('counts', $counts);
                $this->assign('users', $record);
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
            return $this->fetch('apply');
        }else{
            $this->redirect('Login/index');
        }
    }


}