<?php
namespace app\index\validate;
use think\Validate;

class ImsSudu8PageProducts extends Validate{
    protected $rule = [
        'num' => 'integer',
        'cid' => 'require',
        'title' => 'require',
        'sale_end_time' => 'checkendtime:1',
        'hits' => 'integer',
        'price' => 'float',
        'market_price' => 'float',
        'product_txt' => 'require',
        'sale_num' => 'integer',
        'pro_kc' => 'integer',
        'pro_xz' => 'integer',
        'score' => 'integer',

    ];

    protected $message  =  [
        'num.integer' => '排序必须为数字！',
        'cid.require' => '请选择所属栏目',
        'title.require' => '标题不能为空，请输入标题',
        'sale_end_time.checkendtime' => '秒杀开始时间不能大于结束时间，请重新设置!',
        'hits.integer' => '访问量必须为数字',
        'price.float' => '门店价格必须为数字',
        'market_price.float' => '市场价格必须为数字',
        'product_txt.require' => '请输入商品详情!',
        'sale_num.integer' => '销量必须为数字',
        'pro_kc.integer' => '库存必须为数字',
        'pro_xz.integer' => '限购数量必须为数字',
        'score.integer' => '最高抵用积分必须为数字',

    ];

    protected $scene = [  //检验字段
        'add' => ['num', 'cid', 'title', 'sale_end_time', 'hits', 'price', 'market_price', 'sale_num', 'pro_kc', 'score', 'pro_xz', 'product_txt'],
    ];

    //检测秒杀时间
    protected function checkendtime(){
        $sale_time = input('sale_time');
        $sale_end_time = input('sale_end_time');
        if($sale_time && $sale_end_time){
            if(strtotime($sale_time) > strtotime($sale_end_time)){
                return '秒杀开始时间不能大于结束时间,请重新设置!';
            }
        }
        return true;

    }
}