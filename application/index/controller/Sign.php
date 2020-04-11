<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;
use app\index\model\ImsSudu8PageSignCon as SignSet;
use app\index\model\ImsSudu8PageSign;
use app\index\model\Applet;


class Sign extends Base
{
    public function set(){
        if(check_login()){
            if(powerget()){
                $uniacid = input('appletid');
                $app = new Applet;
                $appinfo = $app ->getAppInfo();
                $this->assign('applet',$appinfo);

                $set = model('ImsSudu8PageSignCon');
                $bases = $set->getSet();
                $this->assign('bases',$bases);
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
    public function save(){
        $appletid = input("appletid");
        $set = new SignSet;
        $info = $set ->where("uniacid",$appletid)->find();
        $score = input("score");
        $max_score = input("max_score");
        $data = array(
            "score" => $score,
            "max_score" => $max_score,
            "uniacid" => $appletid
        );
        if(!$info){
           $res = $set->save($data);
        }else{
            $res = $set->where("uniacid",$appletid)->update($data);
        }
        if($res){
          $this->success('积分签到基本配置更新成功！');
        }else{
          $this->error('积分签到基本配置更新失败，没有修改项！');
          exit;
        }
    }
    public function lists(){
        if(check_login()){
            if(powerget()){
                $uniacid = input('appletid');
                $app = new Applet;
                $appinfo = $app ->getAppInfo();
                $this->assign('applet',$appinfo);

                // $info = new ImsSudu8PageSign;
                // $list = $info->getList();
                $list = Db::name('wd_xcx_sign')->where("uniacid", $uniacid)->paginate(10, false, ['query' => array('appletid' => $uniacid)]);

                $counts = count($list);
                $sign = $list->all();
                foreach ($sign as $key => &$value) {
                    $info = getNameAvatar($value['suid'], $uniacid);
                    $value['nickname'] = $info['nickname'];
                    $value['avatar'] = $info['avatar'];
                    $sign[$key]['creattime'] = date('Y-m-d H:i:s',$value['creattime']);
                }
                $this->assign('counts',$counts);
                $this->assign('page',$list->render());
                $this->assign('sign',$sign);

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
            return $this->fetch('lists');
        }else{
            $this->redirect('Login/index');
        }
    }
}