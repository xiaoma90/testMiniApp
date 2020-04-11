<?php
/**
 * Developer: Zhaozewu.
 * Date: 2019/5/14
 * Time: 17:01
 */

namespace app\index\controller;


use app\index\model\QrCodeModel;
use app\index\model\QrRecordingModel;
use think\Db;

class QrRecording extends QrBase
{

//查看单个码的被扫记录
    public function index()
    {
        $id = $this->request->param('id');
        if (!$qr = QrCodeModel::find($id)) {
            $this->error('未查询到该条二维码');
            return false;
        } elseif ($qr->status == 0) {
            $this->error('该二维码还未生成！');
            return false;
        } elseif ($qr->batch_id == 0) {
            $this->error('该二维码还未绑定批次！');
            return false;
        } else {

            $recordingModel = new QrRecordingModel();
            $recording = $recordingModel->index([
                'id' => $id,
                'size' => self::SIZE,
                'appletid' => $this->appletId,
            ]);

            $this->assign('page', $recording->render());//单独提取分页出来
            $this->assign('data', $recording->items());

            return $this->fetch('qrcode/record');
        }

    }

}