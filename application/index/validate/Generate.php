<?php

namespace app\common\validate;

use think\Validate;

class Generate extends Validate
{
    protected $rule = [
        'num'  => 'require|number|max:5000',
    ];
    protected $message = [
        'num.require' => '请填写生成数量',
        'num.number' => '生成数量为正整数',
        'num.max' => '单次生成数量最多5000个',
    ];

}