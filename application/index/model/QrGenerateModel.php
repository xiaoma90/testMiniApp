<?php

namespace app\index\model;

// use app\portal\service\Qr;
use app\index\service\common\GenerateQrFile;
use think\Db;
use think\Model;

/**
 * @property mixed id
 */
class QrGenerateModel extends Model
{

    const STATUS_OK = 1;

    protected $name = 'wd_xcx_qr_generate';

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    protected $dateFormat = "Y-m-d H:i";

    public function qrCode()
    {
        return $this->hasMany('qr_code_model', 'generate_id', 'id')->where('status', 1);
    }

    public function qrBinding()
    {
        return $this->hasMany('qr_code_model', 'generate_id', 'id')->where('is_binding', 1);
    }

    public function getIndex($appletId, $size)
    {
        return $this->withCount(['qrCode', 'qrBinding'])
            ->order(['create_time desc'])
            ->paginate($size, false, [
                'query' => [
                    'appletid' => $appletId
                ]
            ]);
    }


    /**
     * 生码 表格文件存入服务器
     * @param $param
     * @return array|bool
     * @throws \think\exception\DbException
     */
    public function generate($param)
    {
        $num = $param['num'];
        $uniacid = intval($param['appletid']);
        $codes = getRand($num);

        $qr = new QrCodeModel();
        $hasErr = false;
        Db::startTrans();
        try {
            //添加一条生成批次记录
            $this->all_num = $num;
            $this->uniacid = $uniacid;
            $this->save();
            //开始添加二维码数据
            $codeData = [];
            foreach ($codes as $key => $val) {
                $codeData[$key]['generate_id'] = $this->id;
                $codeData[$key]['code'] = $val;
            }
            $res = $qr->saveAll($codeData);

            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            $hasErr = true;
            Db::rollback();
        }
        if ($hasErr) {
            return ['result' => !$hasErr];
        }

        $code_ids = [];
        foreach ($res as $key => $item) {
            $code_ids[$key]['id'] = (int)$item->id;
        }

        $generateQrFile = new GenerateQrFile();
        // 生成表格
        $setting = QrSettingModel::get(['uniacid' => $uniacid]);

        if (!$setting['url']) {
            return ['result' => false, 'message' => '未设置二维码规则链接'];
        }
        $path = $generateQrFile->exportExcel($codes, $this, $setting['url']);
        return $this->updateSql($this->id, $code_ids, $path);
    }


    //生成结束批量更新数据
    protected function updateSql($generate_id, $code_ids, $path)
    {
        $hasErr = false;
        Db::startTrans();
        try {
            $qr = new QrCodeModel;
            $list = [];
            foreach ($code_ids as $key => $id) {
                $list[$key]['id'] = $id['id'];
                $list[$key]['status'] = 1;
            }
            $this->update(['id' => $generate_id, 'all_generate' => 1, 'url' => $path['url'], 'name' => $path['name']]);

            $qr->saveAll($list);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $hasErr = true;
        }
        return ['result' => !$hasErr];
    }

}
