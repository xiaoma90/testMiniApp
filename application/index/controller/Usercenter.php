<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;
class Usercenter extends Base
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
                $items =Db::name('wd_xcx_usercenter_set')->where("uniacid",$id)->find();
                if($items){
                    $usercenterset = unserialize($items['usercenterset']);
                    for($i=1;$i<=13;$i++){
                        if(!isset($usercenterset['flag'.$i])){
                            $usercenterset['flag'.$i] = 1;
                        }
                    }
                }else{
                    $usercenterset = array();
                }
                $this->assign('usercenterset',$usercenterset);

//                include_once 'Ordinary.php';
               // $or = new \Ordinary();
                //$plugin = $or ->checkPlugin();
               // $this ->assign('plugin', $plugin);

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
        $uniacid = input("appletid");
        $data = array(
                    'title1' => input('title1'),
                    'num1' => input('num1'),
                    'thumb1' =>input('img1'),
                    'flag1' =>input('flag1'),
                    'url1' => input('url1'),
                    'icon1' => input('icon1'),
                    'title2' => input('title2'),
                    'num2' => input('num2'),
                    'thumb2' => input('img2'),
                    'flag2' => input('flag2'),
                    'url2' => input('url2'),
                    'icon2' => input('icon2'),
                    'title3' => input('title3'),
                    'num3' => input('num3'),
                    'thumb3' => input('img3'),
                    'flag3' => input('flag3'),
                    'url3' => input('url3'),
                    'icon3' => input('icon3'),
                    'title4' => input('title4'),
                    'num4' => input('num4'),
                    'thumb4' => input('img4'),
                    'flag4' => input('flag4'),
                    'url4' => input('url4'),
                    'icon4' => input('icon4'),
                    'title5' => input('title5'),
                    'num5' => input('num5'),
                    'thumb5' => input('img5'),
                    'flag5' => input('flag5'),
                    'url5' => input('url5'),
                    'icon5' => input('icon5'),
                    'title6' => input('title6'),
                    'num6' => input('num6'),
                    'thumb6' => input('img6'),
                    'flag6' => input('flag6'),
                    'url6' => input('url6'),
                    'icon6' => input('icon6'),
                    'title7' => input('title7'),
                    'num7' => input('num7'),
                    'thumb7' => input('img7'),
                    'flag7' => input('flag7'),
                    'url7' => input('url7'),
                    'icon7' => input('icon7'),
                    'title8' => input('title8'),
                    'num8' => input('num8'),
                    'thumb8' => input('img8'),
                    'flag8' => input('flag8'),
                    'url8' => input('url8'),
                    'icon8' => input('icon8'),
                    'title9' => input('title9'),
                    'num9' => input('num9'),
                    'thumb9' => input('img9'),
                    'flag9' => input('flag9'),
                    'url9' => input('url9'),
                    'icon9' => input('icon9'),
                    'title10' => input('title10'),
                    'num10' => input('num10'),
                    'thumb10' => input('img10'),
                    'flag10' => input('flag10'),
                    'url10' => input('url10'),
                    'icon10' => input('icon10'),
                    'title11' => input('title11'),
                    'num11' => input('num11'),
                    'thumb11' => input('img11'),
                    'flag11' => input('flag11'),
                    'url11' => input('url11'),
                    'icon11' => input('icon11'),
                    'title12' => input('title12'),
                    'num12' => input('num12'),
                    'thumb12' => input('img12'),
                    'flag12' => input('flag12'),
                    'url12' => input('url12'),
                    'icon12' => input('icon12'),
                    'title13' => input('title13'),
                    'num13' => input('num13'),
                    'thumb13' => input('img13'),
                    'flag13' => input('flag13'),
                    'url13' => input('url13'),
                    'icon13' => input('icon13'),
                    'title14' => input('title14'),
                    'num14' => input('num14'),
                    'thumb14' => input('img14'),
                    'flag14' => input('flag14'),
                    'url14' => input('url14'),
                    'icon14' => input('icon14'),
                    
                    'title15' => input('title15'),

                    'num15' => input('num15'),

                    'thumb15' => input('img15'),

                    'flag15' => input('flag15'),

                    'url15' => input('url15'),

                    'icon15' => input('icon15'),


                    'title16' => input('title16'),

                    'num16' => input('num16'),

                    'thumb16' => input('img16'),

                    'flag16' => input('flag16'),

                    'url16' => input('url16'),

                    'icon16' => input('icon16'),

                    'title17' => input('title17'),

                    'num17' => input('num17'),

                    'thumb17' => input('img17'),

                    'flag17' => input('flag17'),

                    'url17' => input('url17'),

                    'icon17' => input('icon17'),
                    

                    'title18' => input('title18'),

                    'num18' => input('num18'),

                    'thumb18' => input('img18'),

                    'flag18' => input('flag18'),

                    'url18' => input('url18'),

                    'icon18' => input('icon18'),


                    'title19' => input('title19'),

                    'num19' => input('num19'),

                    'thumb19' => input('img19'),

                    'flag19' => input('flag19'),

                    'url19' => input('url19'),

                    'icon19' => input('icon19'),


                    'title20' => input('title20'),

                    'num20' => input('num20'),

                    'thumb20' => input('img20'),

                    'flag20' => input('flag20'),

                    'url20' => input('url20'),

                    'icon20' => input('icon20'),
                    

                    'title21' => input('title21'),

                    'num21' => input('num21'),

                    'thumb21' => input('img21'),

                    'flag21' => input('flag21'),

                    'url21' => input('url21'),

                    'icon21' => input('icon21'),


                    'title22' => input('title22'),

                    'num22' => input('num22'),

                    'thumb22' => input('img22'),

                    'flag22' => input('flag22'),

                    'url22' => input('url22'),

                    'icon22' => input('icon22'),
                );
        $strs = serialize($data);
        $data = array(
            'uniacid' => $uniacid,
            'usercenterset' => $strs
        );
        $items = Db::name('wd_xcx_usercenter_set')->where("uniacid",$uniacid)->find();
        if($items){
            $res = Db::name('wd_xcx_usercenter_set')->where("uniacid",$uniacid)->update(array("usercenterset"=>$strs));
        }else{
            $res = Db::name('wd_xcx_usercenter_set')->insert($data);
        }
        if($res){
          $this->success('更新成功！');
        }else{
          $this->error('更新失败，没有更新项目！');
          exit;
        }
    }
}