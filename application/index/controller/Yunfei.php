<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;
class Yunfei extends Base
{
    public function index(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $counts=Db::name("wd_xcx_freight")->where('uniacid',$appletid)->count();
                $mobans=Db::name("wd_xcx_freight")->where('uniacid',$appletid)->where("is_delete",0)->order('createtime',"desc")->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                $list = $mobans->toArray();
                $this->assign('list',$list);
                $this->assign('mobans',$mobans);
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
            return $this->fetch('index');
        }else{
            $this->redirect('Login/index');
        }
    }

    public function del(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $moban = input("mobanid");
                //查询是否有正在使用此模板的商品
                $duo_count = Db::name('wd_xcx_products') ->where([
                    'uniacid' => $appletid,
                    'yunfei_ggid' => $moban
                ]) ->count();
                $pt_count = Db::name('wd_xcx_pt_pro') ->where([
                    'uniacid' => $appletid,
                    'yunfei_ggid' => $moban
                ]) ->count();
                $bargain_count = Db::name('wd_xcx_bargain_pro') ->where([
                    'uniacid' => $appletid,
                    'freightId' => $moban
                ]) ->count();
                $count = $duo_count + $pt_count + $bargain_count;
                if($count > 0){
                    $this ->error('该模板在使用中，不可以删除！');
                }
                $res=Db::name("wd_xcx_freight")->where("uniacid",$appletid)->where("id",$moban)->update(array("is_delete"=>1));
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
            return $this->fetch('index');
        }else{
            $this->redirect('Login/index');
        }
    }

    public function add(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $mobanid = intval(input("mobanid"));

                if($mobanid){
                    $pro_city_arrs=array();
                    $page_city=array();
                    $item=Db::name("wd_xcx_freight")->where("id",$mobanid)->find();
                    $item['detail'] = array_values(json_decode(stripslashes(html_entity_decode($item['detail'])), true));
                    foreach ($item['detail'] as $key => $value) {
                        foreach($value['province_list'] as $vv){
                            array_push($pro_city_arrs, $vv['name']);
                            array_push($page_city, $vv['id']);
                        }
                    }
                    $pro_city_arrs = json_encode($pro_city_arrs,JSON_UNESCAPED_UNICODE);
                    $page_city = json_encode($page_city,JSON_UNESCAPED_UNICODE);
                    $yunfei_gg_arr_count = count($item['detail']);
                    $yunfei_gg = json_encode(array_values($item['detail']), JSON_UNESCAPED_UNICODE);
                    $this->assign('item',$item);
                    $this->assign('pro_city_arrs',$pro_city_arrs);
                    $this->assign('page_city',$page_city);
                    $this->assign('yunfei_gg_arr_count',$yunfei_gg_arr_count);
                    $this->assign('yunfei_gg',$yunfei_gg);
                    $this->assign('mobanid',$mobanid);
                }else{

                    $this->assign('item','');
                    $this->assign('pro_city_arrs','[]');
                    $this->assign('page_city','[]');
                    $this->assign('yunfei_gg_arr_count',0);
                    $this->assign('yunfei_gg','[]');
                    $this->assign('mobanid',$mobanid);
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
            return $this->fetch('add');
        }else{
            $this->redirect('Login/index');
        }
    }


   public function change(){
       if(check_login()){
           if(powerget()){
               $appletid = input("appletid");
               $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
               if(!$res){
                   $this->error("找不到对应的小程序！");
               }
               $this->assign('applet',$res);
               $moban=input("mobanid");
               $res2=Db::name("wd_xcx_freight")->where("uniacid",$appletid)->update(array("is_enable"=>0));
                $res=Db::name("wd_xcx_freight")->where("id",$moban)->where("uniacid",$appletid)->update(array("is_enable"=>1));
               if($res){
                   $this->success('设置成功!');
               }else{
                   $this->success('设置失败！');
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
           return $this->fetch('index');
       }else{
           $this->redirect('Login/index');
       }
   }

    public function save(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $mobanid=input("mobanid");
                $name=input("name");
                $is_again = Db::name("wd_xcx_freight")->where('name', $name)->where('uniacid', $appletid)->where('id','neq', $mobanid)->where("is_delete", 0)->find();
                if($is_again){
                    $this->error("修改失败,模板名称不能重复");
                }
                $yunfei_gg_arr=input("yunfei_gg_arr");
                $yunfei_gg_arr = array_values(json_decode($yunfei_gg_arr, true));
                $yunfei_gg_arr = json_encode($yunfei_gg_arr,JSON_UNESCAPED_UNICODE);
                $data = [
                    'uniacid' => $appletid,
                    'name' => $name,
                    'detail' =>$yunfei_gg_arr
                    ];
                if($mobanid!=0){
                   $res=Db::name("wd_xcx_freight")->where("id",$mobanid)->update($data);
                   if($res){
                       $this->success('运费规则更新成功！',Url('Yunfei/index').'?appletid='.$data['uniacid']);
                   }else{
                        $this->error("修改失败");
                   }
                }else{
                    $data['createtime'] = date('Y-m-d H:i:s', time());
                    $res=Db::name("wd_xcx_freight")->insert($data);
                    if($res){
                        $this->success('添加运费规则成功！',Url('Yunfei/index').'?appletid='.$data['uniacid']);
                    }else{
                        $this->error("添加失败");
                    }
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
            return $this->fetch('index');
        }else{
            $this->redirect('Login/index');
        }
    }
    public function delgg(){
        $uniacid = input('appletid');
        $id = input('mobanid');
        $index = input('index');
        $detail = Db::name('wd_xcx_freight')->where('uniacid', $uniacid)->where('id', $id)->value('detail');
        $detail = json_decode(stripslashes(html_entity_decode($detail)), true);
        $city = $detail[$index]['province_list'];
        unset($detail[$index]);
        $detail = json_encode($detail, JSON_UNESCAPED_UNICODE);
        $res = Db::name('wd_xcx_freight')->where('uniacid', $uniacid)->where('id', $id)->update(['detail' => $detail]);
        if($res){
            return json_encode($city, JSON_UNESCAPED_UNICODE);
        }
        // var_dump(json_decode(stripslashes(html_entity_decode($detail)), true));exit;
    }
}

?>