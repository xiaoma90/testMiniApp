<?php
namespace app\index\model;
use think\Model;
use think\Db;

class ImsSudu8PageExternalFanyongTx extends Model
{
    protected $pk = 'id';
    protected $uniacid = '';
    protected $name = 'wd_xcx_external_fanyong_tx';

    //初始化
    protected function initialize()
    {
        $this->uniacid = input('appletid');
    }

    //关联项目表
    public function appInfo(){

    }

    //查询所有 没有关联
    public function getAll(){
        return $this->where('uniacid', $this->uniacid)
                    ->order('id desc')
                    ->paginate(10,false,[ 'query' => array('appletid'=>$this->uniacid)]);
    }
}