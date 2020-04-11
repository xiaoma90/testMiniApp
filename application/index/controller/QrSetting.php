<?php

namespace app\index\controller;

use app\index\model\QrSettingModel;
use think\Loader;

class QrSetting extends QrBase
{

    protected $setting;

    public function __construct(QrSettingModel $setting)
    {
        parent::__construct();
        $this->setting = $setting;
    }

    public function index()
    {
        if ($setting = $this->setting->where('uniacid', $this->appletId)->find()) {
            $this->assign('data', $setting);
        }

        return $this->fetch('qrcode/setting');
    }


    public function update()
    {
        $param = $this->request->param();

        $validate = Loader::validate('Setting');

        if(!$validate->check($param)){
            $this->error($validate->getError());
        }

        if ($setting = $this->setting->where('uniacid', $this->appletId)->find()) {
            $setting->url = $param['url'];
            $setting->save();
        } else {
            $this->setting->url = $param['url'];
            $this->setting->uniacid = $this->appletId;
            $this->setting->save();
        }

        $this->success('保存成功!', $this->spliceUrl('QrSetting/index'));
    }

}
