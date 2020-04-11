<?php

namespace app\common\validate;


use think\Validate;

class Setting extends Validate
{
    protected $rule = [
        'url'  => 'require|url',
    ];
    protected $message = [
        'url.require' => '请填写二维码规则链接',
        'url.url' => '请填写正确的二维码规则链接',
    ];

}