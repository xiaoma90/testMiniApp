<?php
/**
 * Developer: Zhaozewu.
 * Date: 2019/5/10
 * Time: 9:06
 */

namespace app\index\controller;


use app\index\model\QrGenerateModel;
use app\index\service\common\zipDownload;

class QrGenerate extends QrBase
{

    protected $generate;

    public function __construct(QrGenerateModel $generate)
    {
        parent::__construct();
        $this->generate = $generate;
    }

    /**
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $generate = $this->generate->getIndex($this->appletId, $this::SIZE);

        $this->assign('page', $generate->render());//单独提取分页出来
        $this->assign('data', $generate->items());

        return $this->fetch('qrcode/generate');
    }


    /**
     * 生码
     * @throws \think\exception\DbException
     */
    public function generate()
    {
        $param = $this->request->param();
        $result = $this->validate($param, 'Generate');

        if (!$result) {
            $this->error($result);
        }

        if (!$this->checkUrlSet()){
            return false;
        }

        $result = $this->generate->generate($param);

        if (!$result['result']) {
            $this->error(isset($result['message']) ? $result['message'] : '二维码生成失败，请重试!');
            return false;
        }
        $this->success('生成成功');
    }


    /*下载二维码表格
    *id: generate_id
    */
    public function downloadQr()
    {
        $generate_id = $this->request->param('generate_id');
        $generateModel = new QrGenerateModel();

        if (!$generate = $generateModel->find($generate_id)) {
            $this->error('找不到该条生成记录！');
        }
        if ($generate->all_generate != QrGenerateModel::STATUS_OK) {
            $this->error('该条记录图片未全部生成，无法下载， 请重新生成！');
        }

        $zip = new zipDownload();

        $zip->downloadExcel($generate->url, $generate->name);
    }
}

