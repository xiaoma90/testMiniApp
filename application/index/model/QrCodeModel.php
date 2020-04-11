<?php

namespace app\index\model;

use think\Model;

/**
 * @property mixed id
 */
class QrCodeModel extends Model
{

    protected $name = 'wd_xcx_qr_code';

    protected $dateFormat = "Y-m-d H:i";

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    public function getBindingTimeAttr($time)
    {
        return date('Y-m-d H:i', $time);
    }

    public function qrBinding()
    {
        return $this->hasMany('qr_code_model', 'generate_id', 'id')->where('is_binding', 1);
    }

    //首页列表
    public function index($data)
    {
        $filter = [];
        if (isset($data['flag']) && ($data['flag'] == 'batch' || $data['flag'] == 'generate')) {
            //外部进入筛选 按生成id 或者批次id
            $filter['a.' . $data['flag'] . '_id'] = $data['id'];
        }

        return (sizeof($filter) ? self::where($filter) : $this)
            ->alias('a')
            ->field('a.*, b.id as batch_id')
            ->join('wd_xcx_qr_batch b', 'a.batch_id = b.id', 'left')
            ->order('a.is_binding desc')
            ->order('a.id desc')
            ->paginate($data['size'], false, [
                'query' => $data
            ]);
    }

}
