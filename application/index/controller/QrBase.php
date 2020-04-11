<?php
/**
 * @author azal
 * @date 2019/11/4
 */

namespace app\index\controller;


use app\index\model\Applet;
use app\index\model\QrSettingModel;
use think\Request;
use think\Session;

class QrBase extends Base
{

    const SIZE = 10;

    protected $appletId;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);

        $this->checkPermission();

        $appletId = input("appletid");
        $applet = new Applet();
        if (!$applet->find($appletId)) {
            $this->error("找不到对应的小程序！");
        }

        $this->appletId = $appletId;
    }

    protected function checkPermission()
    {
        $this->isLogin();

        $this->checkRole();
    }

    protected function isLogin()
    {
        if (!check_login()) {
            $this->redirect('Login/index');
            return;
        };
    }

    protected function checkRole()
    {
        if (!powerget()) {
            $this->checkGroup();
        };
        return;
    }

    protected function checkGroup()
    {
        $userGroup = Session::get('usergroup');
        if ($userGroup == 1) {
            $this->error("您没有权限操作该小程序或找不到相应小程序！", 'Applet/applet');
        }
        if ($userGroup == 2) {
            $this->error("您没有权限操作该小程序或找不到相应小程序！", 'Applet/index');
        }
        if ($userGroup == 3) {
            $this->error("您没有权限操作该小程序或找不到相应小程序！", 'Applet/index');
        }
        return;
    }

    protected function spliceUrl($url)
    {
        return Url($url) . '?appletid=' . $this->appletId;
    }

    public function checkUrlSet()
    {
        if (!$qrUrl = QrSettingModel::where('uniacid', $this->appletId)->value('url')) {
            $this->error('未设置二维码规则链接！');
            return false;
        } else {
            return $qrUrl;
        }
    }

}