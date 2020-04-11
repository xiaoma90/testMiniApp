<?php

namespace app\index\controller;

use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;

class Cyindex extends Base
{
    public function index()
    {
        if (check_login()) {
            if (powerget()) {
                $id = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id", $id)->find();
                if (!$res) {
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet', $res);
                $bases = Db::name('wd_xcx_food_sj')->where("uniacid", $id)->find();
                if ($bases['thumb']) {
                    $bases['thumb'] = remote($id, $bases['thumb'], 1);
                }
                $this->assign('bases', $bases);
            } else {
                $usergroup = Session::get('usergroup');
                if ($usergroup == 1) {
                    $this->error("您没有权限操作该小程序或找不到相应小程序！", 'Applet/applet');
                }
                if ($usergroup == 2) {
                    $this->error("您没有权限操作该小程序或找不到相应小程序！", 'Applet/index');
                }
                if ($usergroup == 3) {
                    $this->error("您没有权限操作该小程序或找不到相应小程序！", 'Applet/index');
                }
            }
            return $this->fetch('index');
        } else {
            $this->redirect('Login/index');
        }
    }

    public function save()
    {
        $uniacid = input("appletid");
        $res = Db::name('wd_xcx_food_sj')->where("uniacid", $uniacid)->find();

        $data = array();
        //门店LOGO
        $logo = input("commonuploadpic");
        if ($logo) {
            $data['thumb'] = remote($uniacid, $logo, 2);
        }
        //商家名称
        $notice = $_POST['notice'];
        if ($notice) {
            $data['notice'] = $notice;
        }
        //商家名称
        $phone = $_POST['phone'];
        if ($phone) {
            $data['phone'] = $phone;
        }
        //商家名称
        $address = $_POST['address'];
        if ($address) {
            $data['address'] = $address;
        }
        //商家名称
        $tags = $_POST['tags'];
        if ($tags) {
            $data['tags'] = $tags;
        }
        //商家名称
        $name = $_POST['name'];
        if ($name) {
            $data['names'] = $name;
        }
        //单个订单最多抵扣
        $score = $_POST['score'];
        if ($name) {
            $data['score'] = $score;
        }
        //营业时间
        $times = $_POST['times'];
        if ($times) {
            $data['times'] = $times;
        }
        //配送说明
        $fuwu = $_POST['fuwu'];
        if ($fuwu) {
            $data['fuwu'] = $fuwu;
        }
        //配送说明(其他)

        $qita = $_POST['qita'];
        if ($qita) {
            $data['qita'] = $qita;
        }
        //是否填写姓名
        $usname = input('usname');
        if ($usname) {
            $data['usname'] = $usname;
        } else {
            $data['usname'] = 0;
        }
        //是否填写联系方式
        $ustel = input('ustel');
        if ($ustel) {
            $data['ustel'] = $ustel;
        } else {
            $data['ustel'] = 0;
        }
        //是否填写地址
        $usadd = input('usadd');
        if ($usadd) {
            $data['usadd'] = $usadd;
        } else {
            $data['usadd'] = 0;
        }
        //是否填写日期
        $usdate = input('usdate');
        if ($usdate) {
            $data['usdate'] = $usdate;
        } else {
            $data['usdate'] = 0;
        }
        //是否填写时间
        $ustime = input('ustime');
        if ($ustime) {
            $data['ustime'] = $ustime;
        } else {
            $data['ustime'] = 0;
        }

        $bases = Db::name('wd_xcx_food_sj')->where("uniacid", $uniacid)->count();
        if ($bases > 0) {
            $res = Db::name('wd_xcx_food_sj')->where("uniacid", $uniacid)->update($data);
        } else {
            $data['uniacid'] = $uniacid;
            $res = Db::name('wd_xcx_food_sj')->insert($data);
        }
        if ($res) {
            $this->success('商家基本配置更新成功！');
        } else {
            $this->error('商家基本配置更新失败，没有修改项！');
            exit;
        }
    }

    //单个图片上传操作
    function onepic_uploade($file)
    {
        $thumb = request()->file($file);
        if (isset($thumb)) {
            $dir = upload_img();
            $info = $thumb->validate(['ext' => 'jpg,png,gif,jpeg'])->move($dir);
            if ($info) {
                $imgurl = ROOT_HOST . "/upimages/" . date("Ymd", time()) . "/" . $info->getFilename();
                return $imgurl;
            }
        }
    }

    //上传成功后获取图片
    public function getimg()
    {
        $id = $_POST['id'];
        $allimg = Db::name('wd_xcx_image_url')->where("appletid", $id)->select();
        if ($allimg) {
            return $allimg;
        }

    }

    public function del()
    {
        $id = input("id");
        $res = Db::name('wd_xcx_image_url')->where('id', $id)->delete();
        if ($res) {
            return 1;
        } else {
            $this->error("删除失败！");
        }
    }
}