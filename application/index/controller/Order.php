<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;
class Order extends Base
{
    public function index(){
        if(check_login()){
            if(powerget()){
                $id = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);

                $forms = Db::name('wd_xcx_forms')->where("uniacid",$id)->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);

                $count = Db::name('wd_xcx_forms')->where("uniacid",$id)->count();
                $this->assign('forms',$forms);
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
            return $this->fetch('index');
        }else{
            $this->redirect('Login/index');
        }

    }
    public function save(){
        $appletid = input("appletid");

        $data = array();
        //小程序ID
        $data['uniacid'] = input("appletid");
        //全局表单样式
        $forms_style = input("forms_style");
        if($forms_style){
            $data['forms_style'] = (int)$forms_style;
        }else{
            $data['forms_style'] = 1;
        }
        //输入框提示语
        $forms_inps = input("forms_inps");
        if($forms_inps){
            $data['forms_inps'] = (int)$forms_inps;
        }else{
            $data['forms_inps'] = 0;
        }
        //表单页面顶部样式
        $forms_head = input("forms_head");
        if($forms_head){
            $data['forms_head'] = $forms_head;
        }else{
            $data['forms_head'] = "header";
        }
        //表单中文名称
        $forms_name = input("forms_name");
        if($forms_name){
            $data['forms_name'] = $forms_name;
        }
        //表单英文名称
        $forms_ename = input("forms_ename");
        if($forms_ename){
            $data['forms_ename'] = $forms_ename;
        }
        //表单名称样式
        $forms_title_s = input("forms_title_s");
        if($forms_title_s){
            $data['forms_title_s'] = $forms_title_s;
        }else{
            $data['forms_title_s'] = "title1";
        }
        //提交间隔
        $subtime = input("subtime");
        if($subtime){
            $data['subtime'] = $subtime;
        }else{
            $data['subtime'] = 0;
        }
        //按钮文字
        $forms_btn = input("forms_btn");
        if($forms_btn){
            $data['forms_btn'] = $forms_btn;
        }
        //提交成功提示
        $success = input("success");
        if($success){
            $data['success'] = $success;
        }
        //文本框1
        $name = input("name");
        if($name){
            $data['name'] = $name;
        }
        $name_must = input("name_must");
        if($name_must){
            $data['name_must'] = $name_must;
        }else{
            $data['name_must'] = 0;
        }
        //文本框2
        $tel = input("tel");
        if($tel){
            $data['tel'] = $tel;
        }
        $tel_use = input("tel_use");
        if($tel_use){
            $data['tel_use'] = $tel_use;
        }else{
            $data['tel_use'] = 0;
        }
        $tel_must = input("tel_must");
        if($tel_must){
            $data['tel_must'] = $tel_must;
        }else{
            $data['tel_must'] = 0;
        }
        $tel_i = input("tel_i");
        if($tel_i){
            $data['tel_i'] = $tel_i;
        }else{
            $data['tel_i'] = 0;
        }
        //文本框3
        $wechat = input("wechat");
        if($wechat){
            $data['wechat'] = $wechat;
        }
        $wechat_use = input("wechat_use");
        if($wechat_use){
            $data['wechat_use'] = $wechat_use;
        }else{
            $data['wechat_use'] = 0;
        }
        $wechat_must = input("wechat_must");
        if($wechat_must){
            $data['wechat_must'] = $wechat_must;
        }else{
            $data['wechat_must'] = 0;
        }
        $wechat_i = input("wechat_i");
        if($wechat_i){
            $data['wechat_i'] = $wechat_i;
        }else{
            $data['wechat_i'] = 0;
        }
        //文本框4
        $address = input("address");
        if($address){
            $data['address'] = $address;
        }
        $address_use = input("address_use");
        if($address_use){
            $data['address_use'] = $address_use;
        }else{
            $data['address_use'] = 0;
        }
        $address_must = input("address_must");
        if($address_must){
            $data['address_must'] = $address_must;
        }else{
            $data['address_must'] = 0;
        }
        $address_i = input("address_i");
        if($address_i){
            $data['address_i'] = $address_i;
        }else{
            $data['address_i'] = 0;
        }
        //文本框5
        // $t5n = input("t5n");
        // if($t5n){
        //     $data['t5n'] = $t5n;
        // }
        // $t5u = input("t5u");
        // if($t5u){
        //     $data['t5u'] = $t5u;
        // }else{
        //     $data['t5u'] = 0;
        // }
        // $t5m = input("t5m");
        // if($t5m){
        //     $data['t5m'] = $t5m;
        // }else{
        //     $data['t5m'] = 0;
        // }
        // $t5i = input("t5i");
        // if($t5i){
        //     $data['t5i'] = $t5i;
        // }else{
        //     $data['t5i'] = 0;
        // }
        //文本框6
        // $t6n = input("t6n");
        // if($t6n){
        //     $data['t6n'] = $t6n;
        // }
        // $t6u = input("t6u");
        // if($t6u){
        //     $data['t6u'] = $t6u;
        // }else{
        //     $data['t6u'] = 0;
        // }
        // $t6m = input("t6m");
        // if($t6m){
        //     $data['t6m'] = $t6m;
        // }else{
        //     $data['t6m'] = 0;
        // }
        // $t6i = input("t6i");
        // if($t6i){
        //     $data['t6i'] = $t6i;
        // }else{
        //     $data['t6i'] = 0;
        // }
        //上传提示语
        // $img1not = input("img1not");
        // if($img1not){
        //     $data['img1not'] = $img1not;
        // }
        //日期选择
        $date = input("date");
        if($date){
            $data['date'] = $date;
        }
        $date_use = input("date_use");
        if($date_use){
            $data['date_use'] = $date_use;
        }else{
            $data['date_use'] = 0;
        }
        $date_must = input("date_must");
        if($date_must){
            $data['date_must'] = $date_must;
        }else{
            $data['date_must'] = 0;
        }
        $date_i = input("date_i");
        if($date_i){
            $data['date_i'] = $date_i;
        }else{
            $data['date_i'] = 0;
        }
        //时间选择
        $time = input("time");
        if($time){
            $data['time'] = $time;
        }
        $time_use = input("time_use");
        if($time_use){
            $data['time_use'] = $time_use;
        }else{
            $data['time_use'] = 0;
        }
        $time_must = input("time_must");
        if($time_must){
            $data['time_must'] = $time_must;
        }else{
            $data['time_must'] = 0;
        }
        $time_i = input("time_i");
        if($time_i){
            $data['time_i'] = $time_i;
        }else{
            $data['time_i'] = 0;
        }
        //单选1
        $single_n = input("single_n");
        if($single_n){
            $data['single_n'] = $single_n;
        }
        $single_num = input("single_num");
        if($single_num){
            $data['single_num'] = $single_num;
        }
        $single_use = input("single_use");
        if($single_use){
            $data['single_use'] = $single_use;
        }else{
            $data['single_use'] = 0;
        }
        $single_must = input("single_must");
        if($single_must){
            $data['single_must'] = $single_must;
        }else{
            $data['single_must'] = 0;
        }
        $single_i = input("single_i");
        if($single_i){
            $data['single_i'] = $single_i;
        }else{
            $data['single_i'] = 0;
        }
        $single_v = input("single_v");
        if($single_v){
            $data['single_v'] = $single_v;
        }
        //单选2
        // $s2n = input("s2n");
        // if($s2n){
        //     $data['s2n'] = $s2n;
        // }
        // $s2num = input("s2num");
        // if($s2num){
        //     $data['s2num'] = $s2num;
        // }
        // $s2u = input("s2u");
        // if($s2u){
        //     $data['s2u'] = $s2u;
        // }else{
        //     $data['s2u'] = 0;
        // }
        // $s2m = input("s2m");
        // if($s2m){
        //     $data['s2m'] = $s2m;
        // }else{
        //     $data['s2m'] = 0;
        // }
        // $s2i = input("s2i");
        // if($s2i){
        //     $data['s2i'] = $s2i;
        // }else{
        //     $data['s2i'] = 0;
        // }
        // $s2v = input("s2v");
        // if($s2v){
        //     $data['s2v'] = $s2v;
        // }
        //复选1
        $checkbox_n = input("checkbox_n");
        if($checkbox_n){
            $data['checkbox_n'] = $checkbox_n;
        }
        $checkbox_num = input("checkbox_num");
        if($checkbox_num){
            $data['checkbox_num'] = $checkbox_num;
        }
        $checkbox_use = input("checkbox_use");
        if($checkbox_use){
            $data['checkbox_use'] = $checkbox_use;
        }else{
            $data['checkbox_use'] = 0;
        }
        $checkbox_must = input("checkbox_must");
        if($checkbox_must){
            $data['checkbox_must'] = $checkbox_must;
        }else{
            $data['checkbox_must'] = 0;
        }
        $checkbox_i = input("checkbox_i");
        if($checkbox_i){
            $data['checkbox_i'] = $checkbox_i;
        }else{
            $data['checkbox_i'] = 0;
        }
        $checkbox_v = input("checkbox_v");
        if($checkbox_v){
            $data['checkbox_v'] = $checkbox_v;
        }
        //复选2
        // $c2n = input("c2n");
        // if($c2n){
        //     $data['c2n'] = $c2n;
        // }
        // $c2num = input("c2num");
        // if($c2num){
        //     $data['c2num'] = $c2num;
        // }
        // $c2u = input("c2u");
        // if($c2u){
        //     $data['c2u'] = $c2u;
        // }else{
        //     $data['c2u'] = 0;
        // }
        // $c2m = input("c2m");
        // if($c2m){
        //     $data['c2m'] = $c2m;
        // }else{
        //     $data['c2m'] = 0;
        // }
        // $c2i = input("c2i");
        // if($c2i){
        //     $data['c2i'] = $c2i;
        // }else{
        //     $data['c2i'] = 0;
        // }
        // $c2v = input("c2v");
        // if($c2v){
        //     $data['c2v'] = $c2v;
        // }
        //多行文本1
        $content_n = input("content_n");
        if($content_n){
            $data['content_n'] = $content_n;
        }
        $content_use = input("content_use");
        if($content_use){
            $data['content_use'] = $content_use;
        }else{
            $data['content_use'] = 0;
        }
        $content_must = input("content_must");
        if($content_must){
            $data['content_must'] = $content_must;
        }else{
            $data['content_must'] = 0;
        }
        $content_i = input("content_i");
        if($content_i){
            $data['content_i'] = $content_i;
        }else{
            $data['content_i'] = 0;
        }
        //多行文本2
        // $con2n = input("con2n");
        // if($con2n){
        //     $data['con2n'] = $con2n;
        // }
        // $con2u = input("con2u");
        // if($con2u){
        //     $data['con2u'] = $con2u;
        // }else{
        //     $data['con2u'] = 0;
        // }
        // $con2m = input("con2m");
        // if($con2m){
        //     $data['con2m'] = $con2m;
        // }else{
        //     $data['con2m'] = 0;
        // }
        // $con2i = input("con2i");
        // if($con2i){
        //     $data['con2i'] = $con2i;
        // }else{
        //     $data['con2i'] = 0;
        // }
        $t5arr = array(
            't5n' => input('t5n'),
            't5u' => input('t5u'),
            't5m' => input('t5m'),
            't5i' => input('t5i'),
        );
        $t5text = serialize($t5arr);
        $t6arr = array(
            't6n' => input('t6n'),
            't6u' => input('t6u'),
            't6m' => input('t6m'),
            't6i' => input('t6i'),
        );
        $t6text = serialize($t6arr);
        $c2arr = array(
            'c2n' => input('c2n'),
            'c2num' => input('c2num'),
            'c2v' => input('c2v'),
            'c2u' => input('c2u'),
            'c2m' => input('c2m'),
            'c2i' => input('c2i'),
        );
        $c2text = serialize($c2arr);
        $s2arr = array(
            's2n' => input('s2n'),
            's2num' => input('s2num'),
            's2v' => input('s2v'),
            's2u' => input('s2u'),
            's2m' => input('s2m'),
            's2i' => input('s2i'),
        );
        $s2text = serialize($s2arr);
        $con2arr = array(
            'con2n' => input('con2n'),
            'con2u' => input('con2u'),
            'con2m' => input('con2m'),
            'con2i' => input('con2i'),
        );
        $con2text = serialize($con2arr);
        $img1arr = array(
            'img1n' => input('img1n'),
            'img1u' => input('img1u'),
            'img1m' => input('img1m'),
            'img1i' => input('img1i'),
            'img1not' => input('img1not'),
        );
        $img1text = serialize($img1arr);
        $data['t5'] = $t5text;
        $data['t6'] = $t6text;
        $data['c2'] = $c2text;
        $data['s2'] = $s2text;
        $data['con2'] = $con2text;
        $data['img1'] = $img1text;
        //发件人邮箱
        $mail_user = input("mail_user");
        if($mail_user){
            $data['mail_user'] = $mail_user;
        }
        //授权码
        $mail_password = input("mail_password");
        if($mail_password){
            $data['mail_password'] = $mail_password;
        }
        //发件平台名称
        $mail_user_name = input("mail_user_name");
        if($mail_user_name){
            $data['mail_user_name'] = $mail_user_name;
        }
        //收件人邮箱
        $mail_sendto = input("mail_sendto");
        if($mail_sendto){
            $data['mail_sendto'] = $mail_sendto;
        }
        $bases = Db::name('wd_xcx_forms_config')->where("uniacid",$appletid)->count();
        if($bases>0){
            $res = Db::name('wd_xcx_forms_config')->where("uniacid",$appletid)->update($data);
        }else{
            $data['uniacid'] = $appletid;
            $res = Db::name('wd_xcx_forms_config')->insert($data);
        }
        if($res){
            $this->success('基础信息更新成功！');
        }else{
            $this->error('基础信息更新失败，没有修改项！');
            exit;
        }
    }
    public function add(){
        if(check_login()){
            if(powerget()){
                $id = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $cate=array();
                $cate = Db::name('wd_xcx_cate')->where("uniacid",$id)->select();
                $this->assign('cate',$cate);
                $cateurlid = 0;
                $cateinfo=array();
                $cateid = input("cateid");
                if($cateid){
                    //有栏目号时，先判断该栏目是不是属于该小程序！
                    $lanmu = Db::name('wd_xcx_cate')->where("id",$cateid)->find();
                    if($lanmu['uniacid']==$id){
                        $cateinfo = $lanmu;
                        if($lanmu['cid']==0){
                            $cateurlid = 1;
                        }
                    }else{
                        $usergroup = Session::get('usergroup');
                        if($usergroup==1){
                            $this->error("找不到该栏目，或者该栏目不属于本小程序",'Applet/applet');
                        }
                        if($usergroup==2){
                            $this->error("找不到该栏目，或者该栏目不属于本小程序",'Applet/index');
                        }
                    }


                }else{
                    $cateid=0;
                }
                $configform = Db::name('wd_xcx_forms_config')->where("uniacid",$id)->find();
                if($configform){

                    $configform['t5'] = unserialize($configform['t5']);
                    $configform['t6'] = unserialize($configform['t6']);
                    $configform['c2'] = unserialize($configform['c2']);
                    $configform['s2'] = unserialize($configform['s2']);
                    $configform['con2'] = unserialize($configform['con2']);
                    $configform['img1'] = unserialize($configform['img1']);
                }else{
                    $configform = array();
                }
                // echo "<pre>";
                // var_dump($configform);
                // echo "</pre>";
                // die();
                $this->assign('configform',$configform);
                $this->assign('cateid',$cateid);
                $this->assign('cateinfo',$cateinfo);
                $this->assign('cateurlid',$cateurlid);

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
            return $this->fetch('add');
        }else{
            $this->redirect('Login/index');
        }

    }
    // 查看操作
    public function seeit(){
        if(check_login()){
            if(powerget()){
                $id = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $configform = Db::name('wd_xcx_forms_config')->where("uniacid",$id)->find();
                if($configform['t5']){
                    $configform['t5'] = unserialize($configform['t5']);
                }
                if($configform['t6']){
                    $configform['t6'] = unserialize($configform['t6']);
                }
                if($configform['s2']){
                    $configform['s2'] = unserialize($configform['s2']);
                }
                if($configform['con2']){
                    $configform['con2'] = unserialize($configform['con2']);
                }
                if($configform['img1']){
                    $configform['img1'] = unserialize($configform['img1']);
                }
                if($configform['c2']){
                    $configform['c2'] = unserialize($configform['c2']);
                }
                $this->assign('configform',$configform);
                $orderinfo="";
                $orderid = input("orderid");
                if($orderid){
                    //有栏目号时，先判断该栏目是不是属于该小程序！
                    $lanmu = Db::name('wd_xcx_forms')->where("id",$orderid)->find();
                    if($lanmu['uniacid']==$id){
                        $orderinfo = $lanmu;
                    }else{
                        $usergroup = Session::get('usergroup');
                        if($usergroup==1){
                            $this->error("找不到该预约，或者该预约不属于本小程序",'Applet/applet');
                        }
                        if($usergroup==2){
                            $this->error("找不到该预约，或者该预约不属于本小程序",'Applet/index');
                        }
                    }

                }else{
                    $orderid=0;
                }
                $this->assign('orderinfo',$orderinfo);
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
            return $this->fetch('seeit');
        }else{
            $this->redirect('Login/index');
        }
    }
    public function seeit_do(){
        $orderid = input("orderid");
        $appletid = input("appletid");
        $vvdate = Db::name('wd_xcx_forms')->where("id",$orderid)->find();
        if($vvdate['uniacid']==$appletid){
            $shuju['status'] = 1;
            $shuju['vtime'] = time();
            $res = Db::name('wd_xcx_forms')->where("id",$orderid)->update($shuju);
            if($res){
                $this->success("设置成功！");
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
    }
    public function orderdel(){
        $data['id'] = input("orderid");
        $res = Db::name('wd_xcx_forms')->where($data)->delete();
        if($res){
            $this->success('删除成功');
        }else{
            $this->success('删除失败');
        }
    }
    public function orderdel_f(){
        $data['id'] = input("formid");
        $res = Db::name('wd_xcx_formlist')->where($data)->delete();
        if($res){
            $this->success('删除成功');
        }else{
            $this->success('删除失败');
        }
    }
    public function wnlist(){
        $id = input("appletid");
        $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
        if(!$res){
            $this->error("找不到对应的小程序！");
        }
        $this->assign('applet',$res);
        $jieguo_list = Db::name('wd_xcx_formlist')->where("uniacid",$id) ->order('id desc')->paginate(10, false, ['query' => ['appletid' => $id]]);
        $this->assign('forms_list',$jieguo_list);
        $this->assign('forms',$jieguo_list->toArray()['data']);
        return $this->fetch('wnlist');
    }
    public function wnset(){
        $id = input("appletid");
        $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
        if(!$res){
            $this->error("找不到对应的小程序！");
        }
        $this->assign('applet',$res);
        if(input("formid")){
            $formid = input("formid");
        }else{
            $formid = 0;
        }
        $this->assign('formid',$formid);

        // 输出已有状态
        $jieguo = [];
        if($formid>0){
            $jieguo = Db::name('wd_xcx_formlist')->where("id",$formid)->find();
            if($jieguo['tp_text'] != ''){
                $jieguo['tp_text'] = json_encode(unserialize($jieguo['tp_text']), JSON_UNESCAPED_UNICODE);
                $jieguo['tp_text'] = preg_replace("/\'/", "\'", $jieguo['tp_text']);
                $jieguo['tp_text'] = preg_replace('/(\\\n)/', "<br>", $jieguo['tp_text']);
                $formtitle = $jieguo['formtitle'];
                $formname = $jieguo['formname'];
                $descs = $jieguo['descs'];
            }else{
                $jieguo['tp_text'] = '这是空啦';
                $formtitle = $jieguo['formtitle'];
                $formname = $jieguo['formname'];
                $descs = $jieguo['descs'];
            }
        }else{
            $jieguo['tp_text'] = '这是空啦';
            $formtitle = '';
            $formname = '';
            $descs = '';
        }
        $this->assign('formtitle',$formtitle);
        $this->assign('formname',$formname);
        $this->assign('descs',$descs);
        $this->assign('forms',$jieguo);
        return $this->fetch('wnset');
    }
    public function wnsave(){
        $data['uniacid'] = input("uniacid");
        $data['formname'] = input("formname");
        $data['formtitle'] = input("formtitle");
        $data['descs'] = input("descs");
        $type = input('type');
        if($type == 1){
            if(input('datas')){
                $forms = stripslashes(html_entity_decode(input('datas')));
                $forms = json_decode($forms, TRUE);
                $data['tp_text'] = serialize($forms['fields']);
            }
        }
        $formid = input("formid");
        if($formid > 0){
            Db::name('wd_xcx_formlist')->where('id', $formid)->update($data);
        }else{
            $formid = Db::name('wd_xcx_formlist')->insertGetId($data);
        }
        return $formid;
    }
    public function emailset(){
        if(check_login()){
            if(powerget()){
                $id = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $formset = Db::name('wd_xcx_forms_config')->where("uniacid",$id)->find();
                $this->assign('formset',$formset);
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
            return $this->fetch('emailset');
        }else{
            $this->redirect('Login/index');
        }
    }
    public function emailsave(){
        $uniacid = input("appletid");
        $mail_sendto = input("mail_sendto");
        $data['mail_sendto'] = "";
        if($mail_sendto){
            $data['mail_sendto'] = $mail_sendto;
        }

        $formset = Db::name('wd_xcx_forms_config')->where("uniacid",$uniacid)->find();

        if($formset){
            $res = Db::name('wd_xcx_forms_config')->where("uniacid",$uniacid)->update($data);
        }else{
            $this->error('请先设置基础表单信息');
        }

        if($res){
            $this->success('更新成功');
        }else{
            $this->success('更新失败');
        }
    }
    public function xinx(){
        if(check_login()){
            if(powerget()){
                $id = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);

                $formset = Db::name('wd_xcx_formcon')->where("uniacid",$id)->field('id, flag, creattime, type, cid')->order("id DESC")->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);

                $count = Db::name('wd_xcx_formcon')->where("uniacid",$id)->count();
                $newformset = $formset->toArray();
                foreach ($newformset['data'] as &$res) {
                    $res['creattime'] = date("Y-m-d H:i:s",$res['creattime']);
                    if($res['type'] == 'duo' || $res['type'] == 'mainShop'){
                        $res['title'] = '商品';
                    }else if($res['type'] == 'diy'){
                        $res['title'] = 'DIY';
                    }else if($res['type'] == 'VIP申请'){
                        $res['title'] = '会员申请';
                    }else if($res['type'] == 'showArt' || $res['type'] == 'miaosha'){
                        $pro = Db::name('wd_xcx_products')->where("uniacid",$id)->where("id",$res['cid'])->find();
                        $res['title'] = $pro['title'];
                    }else if ($res['type'] == 'bargain'){
                        $pro = Db::name('wd_xcx_bargain_pro')->where("uniacid",$id)->where("id",$res['cid'])->find();
                        $res['title'] = $pro['title'];
                    }else if ($res['type'] == 'pt'){
                        $pro = Db::name('wd_xcx_pt_pro')->where("uniacid",$id)->where("id",$res['cid'])->find();
                        $res['title'] = $pro['title'];
                    }else if ($res['type'] == 'duoShop'){
                        $pro = Db::name('wd_xcx_shops_goods')->where("uniacid",$id)->where("id",$res['cid'])->find();
                        $res['title'] = $pro['title'];
                    }
                }
                $this->assign('formset',$formset);
                $this->assign('newformset',$newformset);
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
            return $this->fetch('xinx');
        }else{
            $this->redirect('Login/index');
        }
    }
    public function xinxdel(){
        if(check_login()){
            if(powerget()) {
                $id = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id", $id)->find();
                if (!$res) {
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet', $res);
                $conid = input('id');
                $res=Db::name('wd_xcx_formcon')->where('id',$conid)->delete();
                if($res){
                    $this->success('删除成功');
                }else{
                    $this->success('删除失败');
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
            return $this->fetch('xinx');
        }else{
            $this->redirect('Login/index');
        }
    }
    public function bxiang(){
        if(check_login()){
            if(powerget()) {
                $id = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id", $id)->find();
                if (!$res) {
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet', $res);
                $conid = input('id');
                $item=Db::name("wd_xcx_formcon")->alias('a')->join('wd_xcx_formlist b','a.fid = b.id','left')->where('a.uniacid',$id)->where('a.id',$conid)->field('a.*,b.formname as title')->find();

                if($item){
                    if($item['source'] == 1){
                        $item['source'] = "微信端";
                    }else if($item['source'] == 2){
                        $item['source'] = "支付端";
                    }else if($item['source'] == 3){
                        $item['source'] = "H5端";
                    }else if($item['source'] == 4){
                        $item['source'] = "百度端";
                    }else if($item['source'] == 5){
                        $item['source'] = "头条端";
                    }
                    $title['title'] = '';
                    $title['formset'] = '';
                    if($item['type'] == 'showArt' || $item['type'] == 'miaosha'){
                        $title=Db::name('wd_xcx_products')->where('id',$item['cid'])->where('uniacid',$id)->find();
                        $title=Db::name('wd_xcx_products')->where('id',$item['cid'])->where('uniacid',$id)->find();
                    }else if($item['type'] == 'bargain'){
                        $title=Db::name('wd_xcx_bargain_pro')->where('id',$item['cid'])->where('uniacid',$id)->find();
                        $title['formset'] = $title['form_id'];
                    }else if($item['type'] == 'pt'){
                        $title=Db::name('wd_xcx_pt_pro')->where('id',$item['cid'])->where('uniacid',$id)->find();
                    }else if($item['type'] == 'duoShop'){
                        $title=Db::name('wd_xcx_shops_goods')->where('id',$item['cid'])->where('uniacid',$id)->find();
                    }
                    $item['title'] = "";
                    if($title['formset']){
                        $item['title'] = $title['title'];
                        $a=Db::name('wd_xcx_formlist')->where("uniacid",$id)->where('id',$title['formset'])->find();
                        $item['formtitle']=$a['formname'];

                    }else{
                        $a=Db::name('wd_xcx_formlist')->where("uniacid",$id)->where('id',$item['fid'])->find();
                        $item['formtitle']=$a['formname'];
                    }

                    $beizhu= unserialize($item['val']);

                    $beizhustr = "";

                    if($beizhu){
                        foreach ($beizhu as $key => &$rek) {
                            if($rek['type']==3){
                                $vv = "";
                                foreach ($rek['val'] as $reb) {
                                    $vv.=$reb.",";
                                }
                                $beizhustr.="<div class='control-group_c'><label class='control-label' style='color:#666666'>".$rek['name']."</label><div class='controls' style='line-height:35px;'>".substr($vv, 0,strlen($vv)-1)."</div></div>";
                            }else if($rek['type']==5){
                                if(isset($rek['z_val'])){
                                    foreach($rek['z_val'] as $con){
                                        $beizhustr .= "<div class='control-group_c'><label class='control-label' style='color:#666666'>".$rek['name']."</label><img src='".$con."' style='width:60px;margin:10px'/></div>";
                                    }
                                }else{
                                    $beizhustr .= "<div class='control-group_c'><label class='control-label' style='color:#666666'>".$rek['name']."</label><div class='controls' style='line-height:35px;'>图片不存在"."</div></div>";
                                }
                            }else{
                                if(isset($rek['val'])){
                                    $beizhustr.="<div class='control-group_c'><label class='control-label' style='color:#666666'>". $rek['name']."</label><div class='controls' style='line-height:35px;'>".$rek['val']."</div></div>";
                                }else{
                                    $beizhustr.= "<div class='control-group_c'><label class='control-label' style='color:#666666'>".$rek['name']."</label><div class='controls' style='line-height:35px;'>"."</div></div>";
                                }
                            }
                        }
                        $item['val'] = $beizhustr != 'a:0:{}' ? $beizhustr : '';

                    }else{
                        $item['val'] = '';
                    }

                    $item['creattime'] = date("Y-m-d H:i:s",$item['creattime']);
                    if($item['vtime']){
                        $item['vtime'] = date("Y-m-d H:i:s",$item['vtime']);
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
            return $this->fetch('bxiang');
        }else{
            $this->redirect('Login/index');
        }
    }

    public function setbxiang(){
        if(check_login()){
            if(powerget()) {
                $id = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id", $id)->find();
                if (!$res) {
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet', $res);
                $beizhu=input('beizhu');
                $conid=input('id');
                $data = array(
                    'flag' => 1,
                    'beizhu' => $beizhu,
                    'vtime'=>time()
                );
                $res = Db::name('wd_xcx_formcon')->where("uniacid",$id)->where('id',$conid)->update($data);

                $item = Db::name('wd_xcx_formcon')->where("uniacid",$id)->where('id',$conid)->find();

                if($item['source'] != 3){
                    $jsons = [
                            'creattime' => $item['creattime'],
                            'vtime' => time(),
                        ];
                    $jsons = serialize($jsons);
                    tpl_send($id, 6, $item['openid'], $item['source'], $item['formid'], $jsons);
                }

                if($res){
                    $this->success('设置已查看成功！');
                }else{
                    $this->error('设置已查看失败！');
                    exit;
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
            return $this->fetch('bxiang');
        }else{
            $this->redirect('Login/index');
        }
    }

    public function excel(){
        $id = input("appletid");
        $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
        if(!$res){
            $this->error("找不到对应的小程序！");
        }
        $this->assign('applet',$res);
        $formid = input("formid");
        $jieguo=Db::name("wd_xcx_formlist")->where('id',$formid)->order('id')->find();
        $jieguos = unserialize($jieguo['tp_text']);

        require_once ROOT_PATH.'public/plugin/PHPExcel/PHPExcel.php';
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()->setCreator($jieguo["formname"])
            ->setLastModifiedBy($jieguo["formname"])
            ->setTitle($jieguo["formname"])
            ->setSubject($jieguo["formname"])
            ->setDescription($jieguo["formname"])
            ->setKeywords($jieguo["formname"])
            ->setCategory($jieguo["formname"]);
        $array2=array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ','BA','BB','BC','BD','BE','BF','BG','BH','BI','BJ');
        $objPHPExcel->getActiveSheet()->setCellValue($array2[0]."1","提交人昵称");
        $objPHPExcel->getActiveSheet()->setCellValue($array2[1]."1","提交人头像路径");
        $objPHPExcel->getActiveSheet()->setCellValue($array2[2]."1","查看时间（0未查看）");
        $objPHPExcel->getActiveSheet()->setCellValue($array2[3]."1","表单备注");
        for($i=1;$i<count($jieguos);$i++){
            $objPHPExcel->getActiveSheet()->setCellValue($array2[$i+3]."1",$jieguos[$i]["label"]);
        }
        $excel=Db::name('wd_xcx_formcon')->where('fid',$formid)->where('uniacid',$id)->select();

        foreach($excel as $k=>$reb){
            $k=$k+2;
            $userinfo=Db::name('wd_xcx_user')->where('openid',$reb['openid'])->where("uniacid",$id)->find();
//            var_dump($reb['val']);
            $reb['val']= unserialize($reb['val']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($array2[0].$k,$userinfo['nickname'],'s');
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($array2[1].$k,$userinfo['avatar'],'s');
            if($reb['vtime']){
                $objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($array2[2].$k,date("Y-m-d H:i:s",$reb['vtime']),'s');
            }else{
                $objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($array2[2].$k,0,'s');
            }
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($array2[3].$k,$reb['beizhu'],'s');
            for($j=0;$j<count($jieguos);$j++) {
                if (isset($reb['val'][$j]['val'])) {
                    if ($reb['val'][$j]['val']) {
                        if (is_array($reb['val'][$j]['val'])) {
                            $a = "";
                            for ($m = 0; $m < count($reb['val'][$j]['val']); $m++) {
                                $a .= $reb['val'][$j]['val'][$m] . '\n';
                            }
                            $objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($array2[$j + 4] . $k, $a, 's');
                        } else {
                            $objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($array2[$j + 4] . $k, $reb['val'][$j]['val'], 's');
                        }
                    } else {
                        $objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($array2[$j + 4] . $k, '', 's');
                    }
                }
            }
        }

        $objPHPExcel->getActiveSheet()->setTitle($jieguo["formname"].'信息');
        $objPHPExcel->setActiveSheetIndex(0);
        $excelname=$jieguo["formname"]."提交记录表";
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$excelname.'.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }


}