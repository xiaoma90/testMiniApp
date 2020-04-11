<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;


class Cz extends Base
{
    public function index(){

        if(check_login()){


            if(powerget()){

                $uniacid = input("appletid");

                $res = Db::name('wd_xcx_applet')->where("id",$uniacid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);

                $rechargeconf = Db::name('wd_xcx_rechargeconf')->where("uniacid",$uniacid)->find();
                $this->assign('rechargeconf',$rechargeconf);

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

    public function del(){
        $uniacid = input("appletid");
        $czid = input("cz");
        $res = Db::name('wd_xcx_recharge')->where('id',$czid)->where('uniacid',$uniacid)->delete();
        if($res){
          $this->success('删除成功！');
        }else{
          $this->error('删除失败，没有删除项！');
          exit;
        }
    }


    public function save(){

    	$uniacid = input("appletid");

        $title = input("title");
        if($title){
            $data['title'] = $title;
        }else{
            $data['title'] = "";
        }

        $score_shoppay = input("score_shoppay");
        if(!$score_shoppay || $score_shoppay<0){
            $data['score_shoppay'] = 0;
        }else{
            $data['score_shoppay'] = $score_shoppay;
        }

        $data['uniacid'] = $uniacid;

        $jifen = Db::name('wd_xcx_rechargeconf')->where("uniacid",$uniacid)->find(); 

        if($jifen){
            $res = Db::name('wd_xcx_rechargeconf')->where("uniacid",$uniacid)->update($data); 
        }else{
            $res = Db::name('wd_xcx_rechargeconf')->insert($data);
        }


        if($res){
          $this->success('更新成功！');
        }else{
          $this->error('更新失败，没有修改项！');
          exit;
        }





    }





    public function guiz(){


        if(check_login()){


            if(powerget()){

                $uniacid = input("appletid");

                $res = Db::name('wd_xcx_applet')->where("id",$uniacid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);

                $guiz_list = Db::name('wd_xcx_recharge')->where("uniacid",$uniacid)->order('money asc')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);

                $guiz = $guiz_list->toArray()['data'];
                foreach ($guiz as $key => &$value) {
                    $value['title'] = '';
                    $value['coupon_num'] = 0;
                    $value['coupon_con'] = $value['coupon_con'] ? unserialize($value['coupon_con']) : '';
                    if($value['coupon_con']){
                        foreach ($value['coupon_con'] as $ki => &$vi) {
                            $vi['title'] = Db::name('wd_xcx_coupon')->where("uniacid",$uniacid)->where('id', $vi['coupon_id'])->value('title');
                            $vi['coupon_num'] = $vi['coupon_num'];
                        }
                    }
                }

                $count = Db::name('wd_xcx_recharge')->where("uniacid",$uniacid)->count();

                $this->assign('counts',$count); 
                $this->assign('guiz',$guiz);
                $this->assign('guiz_list',$guiz_list);

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

            return $this->fetch('guiz');
        }else{
            $this->redirect('Login/index');
        }

        
    }



    public function add(){

        if(check_login()){


            if(powerget()){

                $uniacid = input("appletid");

                $res = Db::name('wd_xcx_applet')->where("id",$uniacid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);


                $cz = input("cz");
                $recharge = array();
                if($cz){
                    $recharge = Db::name('wd_xcx_recharge')->where("id",$cz)->find();
                    if($recharge['coupon_con']){
                        $recharge['coupon_con'] = unserialize($recharge['coupon_con']);
                    }
                }else{
                    $cz=0;
                }

                $coupon = Db::name('wd_xcx_coupon')->where('uniacid', $uniacid)->where("flag",1)->field('id,title,etime')->order('num desc, id desc')->select();
                foreach ($coupon as $ki => $vi) {
                    if($vi['etime'] > time() || $vi['etime'] == 0){
                        $coupon[$ki]['overdue'] = 1;
                    }else{
                        $coupon[$ki]['overdue'] = 0;
                    }
                }

                $this->assign('yhqs',$coupon);
                $this->assign('recharge',$recharge);
                $this->assign('cz',$cz);

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


    public function guizsave(){

        $cz = input("cz");

        $uniacid = input("appletid");

        $money = input("money");
        if($money){
            if($money<0){
                $data['money'] = 0;
            }else{
                $data['money'] = $money;
            }  
        }else{
            $data['money'] = 0;
        }


        $getmoney = input("getmoney");
        if($getmoney){
            if($getmoney<0){
                $data['getmoney'] = 0;
            }else{
                $data['getmoney'] = $getmoney;
            }  

        }else{
            $data['getmoney'] = 0;
        }
       

        $getscore = input("getscore");
        if($getscore){

            if($getscore<0){
                $data['getscore'] = 0;
            }else{
                $data['getscore'] = $getscore;
            }   
            
        }else{
            $data['getscore'] = 0;
        }



        $data['uniacid'] = $uniacid;

        $coupon_con = [];
        if(!empty(input('coupon_id/a'))){
            $coupon_id_arr = input('coupon_id/a');
            $coupon_num_arr = input('coupon_num/a');
            $j = 0;
            foreach ($coupon_id_arr as $k => $v) {
                if($v != 0 && $coupon_num_arr[$k] >0){
                    $coupon_con[$j]['coupon_id'] = $v;
                    $coupon_con[$j]['coupon_num'] = $coupon_num_arr[$k];
                    $j++;
                }
            }
        }
        $data['coupon_con'] = serialize($coupon_con);
       

        if($cz){
            $res = Db::name('wd_xcx_recharge')->where("uniacid",$uniacid)->where("id",$cz)->update($data); 
        }else{
            $res = Db::name('wd_xcx_recharge')->insert($data);
        }


        if($res){
          $this->success('规则更新成功！',Url('Cz/guiz').'?appletid='. $uniacid);
        }else{
          $this->error('更新失败，没有修改项！');
          exit;
        }





    }



}