<?php
/**
 * Developer: Zhaozewu.
 * Date: 2019/5/10
 * Time: 9:06
 */

namespace app\index\controller;


use app\index\model\QrBatchModel;
use app\index\model\QrCodeModel;

class QrBatch extends QrBase
{

    protected $batch;

    public function __construct(QrBatchModel $batch)
    {
        parent::__construct();
        $this->batch = $batch;
    }

    /**
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $data = $this->request->param();
        $data['appletid'] = $this->appletId;
        $data['size'] = self::SIZE;



        $batch = $this->batch->index($data);

        $this->assign('page', $batch->render());//单独提取分页出来
        $this->assign('data', $batch->items());

        return $this->fetch('qrcode/batch');
    }

    /**
     * 添加/编辑批次
     */
    public function add()
    {
        $shops = $this->batch->shopsCanBind($this->appletId);
        $products = $this->batch->productsCanBind($this->appletId);

        $this->assign('shops', $shops);
        $this->assign('products', $products);
        return $this->fetch('qrcode/batchAdd');
    }


    //获取可绑定的产品
    public function products()
    {
        $products = $this->batch->productsCanBind($this->appletId);

        return json($products);
    }


    //获取产品规格数据
    public function moreValue()
    {
        $product_id = $this->request->param('id');
        $moreValue = $this->batch->productMoreValue($product_id);

        return json($moreValue);
    }

    /**
     * 添加/编辑批次提交
     */
    public function addPost()
    {
        $data = $this->request->param();

        $result = $this->validate($data, 'Batch');

        if ($result !== true) {
            $this->error($result);
        }
        if (!$qrUrl = $this->checkUrlSet()) {
            return false;
        }

        //多规格信息
        $moreValue = [];
        if (isset($data['types']) && isset($data['types'])) {
            $types = json_decode($data['types']);
            foreach ($types as $type) {
                if (isset($data[$type]) && $data[$type]) {
                    $moreValue[] = [
                        'label' => $type,
                        'value' => $data[$type],
                    ];
                }
            }
        }

        $data['more_value'] = sizeof($moreValue) ? json_encode($moreValue) : '';

        $codes = explode($qrUrl, $data['codes']);
        $qr = new QrCodeModel();

        foreach ($codes as $key => $code) {
            $code = trim($code);
            if ($code) {
                $count = $qr->where('code', $code)
                    ->where('is_binding', 0)
                    ->where('status', 1)
                    ->count();
                if ($count !== 1) {
                    $this->error('第' . ($key + 1) . '位code码不存在或者已经被绑定！');
                    return false;
                    break;
                }

            } else {
                unset($codes[$key]);
            }
        }

        $codes = array_values($codes);
        $data['codes'] = $codes;


        $this->batch->binding($data);

        $this->success('生成批次成功!', $this->spliceUrl('QrBatch/index'));
    }

}

