<?php
/**
 * Developer: Zhaozewu.
 * Date: 2019/5/14
 * Time: 17:01
 */

namespace app\index\controller;


use app\index\model\QrBatchModel;
use app\index\model\QrCodeModel;
use app\index\model\QrGenerateModel;


class QrCode extends QrBase
{

    public function index()
    {
        $qrModel = new QrCodeModel();

        $data = $this->request->param();
        if (isset($data['flag'])) {
            //别的地方转进来的
            if ($data['flag'] == 'batch') {
                //批次点击查看，根据批次id查
                if (!QrBatchModel::find($data['id'])) {
                    $this->error('未找到该批次记录');
                    return false;
                }
            } else if ($data['flag'] == 'generate') {
                if (!QrGenerateModel::find($data['id'])) {
                    $this->error('未找到该生码记录！');
                    return false;
                }
            }
        }
        $data['uniacid'] = $this->appletId;
        $data['size'] = self::SIZE;
        $qr = $qrModel->index($data);

        $this->assign('data', $qr->items());
        $this->assign('page', $qr->render());

        return $this->fetch('qrcode/codeList');
    }


}