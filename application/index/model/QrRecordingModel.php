<?php

namespace app\index\model;

use think\Model;

/**
 * @property mixed id
 */
class QrRecordingModel extends Model
{

    protected $name = 'wd_xcx_qr_recording';

    protected $dateFormat = "Y-m-d H:i";

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    //首页列表
    public function index($data)
    {
        return  $this->where('qr_id', $data['id'])
            ->alias('a')
            ->field('a.qr_id as qr_id, a.create_time, a.ip, a.area, b.id as user_id, b.nickname, b.avatar, c.id as batch_id,d.title as product_name, e.name as shop_name, count(b.id) as times')
            ->group('b.id')
            ->join('wd_xcx_qr_code f', 'a.qr_id = f.id')
            ->join('wd_xcx_user b', 'a.user_id = b.id')
            ->join('wd_xcx_qr_batch c', 'f.batch_id = c.id')
            ->join('wd_xcx_products d', 'c.product_id = d.id')
            ->join('wd_xcx_shops_shop e', 'c.shop_id = e.id', 'left')
            ->order('a.create_time desc')
            ->paginate($data['size'], false, [
                'query' => [
                    'appletid' =>$data['appletid']
                ]
            ]);
    }


}
