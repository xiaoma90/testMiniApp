<?php
namespace app\api\controller;

use Decode\Decode\Decode;
use phpmail\Phpmailer;
use think\Controller;
use think\Db;
use think\Session;
use think\Request;
use think\Exception;
use think\cache\driver\Redis;

use app\index\model\WdXcxMainShopOrderItem as OrderItem;
use app\index\model\WdXcxMainShopOrder as Order;

use Aliyun\Core\Config;
use Aliyun\Core\Profile\DefaultProfile;
use Aliyun\Core\DefaultAcsClient;
use Aliyun\Api\Sms\Request\V20170525\SendSmsRequest;

class Mainwxapp extends BaseController
{
   //多规格栏目列表页获取列表信息
    public function doPagelistAllPic()
    {
        $uniacid = input('uniacid');
        $suid = input('suid');
        $top_cid = intval(input('top_cid'));
        $sub_cid = intval(input('sub_cid'));
        $pindex = max(1, intval(input('page')));
        $pagesize = 10;
        $begin = ($pindex - 1) * 10;
        $type = input('type');
        if($type == 'flashSale'){
            $catestyle = Db::name("wd_xcx_flashsale_set")->where("uniacid",$uniacid)->value("catestyle");
        }elseif($type == 'reserve'){
            $catestyle = Db::name("wd_xcx_reserve_set")->where("uniacid",$uniacid)->value("catestyle");
        }else{
            $catestyle = Db::name("wd_xcx_base")->where("uniacid",$uniacid)->value("catestyle");
        }
        if(!$catestyle){
            $catestyle = 1;
        }
        $top_cate = [];
        $slide_is = 0;
        $slides = '';
        $sub_cate = "";
        $prolist = "";
        $sub_cate_name = "";

        if($type == 'flashSale'){
            $result_flashSale = $this->getAllFlashCateAndPro($uniacid, $catestyle, $top_cid, $sub_cid, $begin, $pagesize);
            $result['data'] = [];
            $result['data']['top_cid'] = $result_flashSale['top_cid'];
            $result['data']['sub_cid'] = $result_flashSale['sub_cid'];
            $result['data']['catestyle'] = $result_flashSale['catestyle'];
            $result['data']['top_cate'] = $result_flashSale['top_cate'];
            $result['data']['slide_is'] = $result_flashSale['slide_is'];
            $result['data']['slides'] = $result_flashSale['slides'];
            $result['data']['sub_cate'] = $result_flashSale['sub_cate'];
            $result['data']['prolists'] = $result_flashSale['prolists'];
            $result['data']['sub_cate_name'] = $result_flashSale['sub_cate_name'];
            return json_encode($result);
        }elseif($type == 'reserve'){
            $result_flashSale = $this->getAllReserveCateAndPro($uniacid, $catestyle, $top_cid, $sub_cid, $begin, $pagesize, $suid);
            $result['data'] = [];
            $result['data']['top_cid'] = $result_flashSale['top_cid'];
            $result['data']['sub_cid'] = $result_flashSale['sub_cid'];
            $result['data']['catestyle'] = $result_flashSale['catestyle'];
            $result['data']['top_cate'] = $result_flashSale['top_cate'];
            $result['data']['slide_is'] = $result_flashSale['slide_is'];
            $result['data']['slides'] = $result_flashSale['slides'];
            $result['data']['sub_cate'] = $result_flashSale['sub_cate'];
            $result['data']['prolists'] = $result_flashSale['prolists'];
            $result['data']['sub_cate_name'] = $result_flashSale['sub_cate_name'];
            return json_encode($result);
        }

        if($catestyle == 1 || $catestyle == 2){
            $top_cate = Db::name('wd_xcx_cate')->where("uniacid", $uniacid) ->where('cid', 0) ->where('statue', 1) ->where('type', 'showPro') ->field("id,name, slide_is, randid")->order('num desc,id desc')->select(); //得到所有一级栏目
            if($top_cate){
                if($top_cid > 0){
                    $top_cate_now = Db::name('wd_xcx_cate')->where("uniacid", $uniacid) ->where('id', $top_cid) ->where('statue', 1) ->where('type', 'showPro') ->field("id,name, slide_is, randid")->find();
                }else{
                    $top_cate_now = $top_cate[0];
                    $top_cid = $top_cate[0]['id'];
                }
                if($top_cate_now['slide_is'] == 1){
                    $top_cate_now['slides'] = Db::name('wd_xcx_products_url')->where("randid", $top_cate_now['randid'])->select();
                    unset($top_cate_now['randid']);
                    if($top_cate_now['slides']){
                        $slides = [];
                        foreach($top_cate_now['slides'] as $ks => $vs){
                            $slides[$ks]['url'] = remote($uniacid, $vs['url'], 1);
                        }
                        if(count($slides) == 0){
                            $slides = "";
                        }else{
                            $slide_is = 1;
                        }
                    }else{
                        $slides = "";
                    }
                }
                $sub_cate = Db::name('wd_xcx_cate')->where("uniacid", $uniacid) ->where('cid', $top_cate_now['id']) ->where('statue', 1) ->where('type', 'showPro') ->field("id,name, catepic")->order('num desc,id desc')->select(); //当前选中id
                if($sub_cate){
                    foreach ($sub_cate as $k => $v) {
                        if($sub_cate[$k]['catepic']){
                            $sub_cate[$k]['catepic'] = remote($uniacid, $sub_cate[$k]['catepic'], 1);
                        }
                    }
                    if($sub_cid == 0){
                        $sub_cid = $sub_cate[0]['id'];
                    }
                    if($catestyle == 2){
                        $sub_cate_name = Db::name('wd_xcx_cate')->where("uniacid", $uniacid) ->where('id', $sub_cid)->value("name");
                        // $sub_cate[0]['name'];
                        $pids = Db::name('wd_xcx_cate_pro') ->where('cate_id', $sub_cid)  ->field('pid')->select();
                        $pids = array_column($pids, 'pid');
                        $prolist = Db::name('wd_xcx_products') ->where('type', 'showProMore') ->where('is_sale', 0) -> where('id', 'in', $pids) ->field('id, type, is_more, thumb, title, desc, price, market_price, discount_status, discount, use_more,sale_num,sale_tnum')->order('num desc, id desc') ->limit($begin, $pagesize) ->select();
                        $grade = Db::name('wd_xcx_superuser')->where("uniacid",$uniacid)->where('id', $suid)->value('grade');
                        $grade = intval($grade) > 0 ? intval($grade) : 1;
                        foreach ($prolist as $k1 => $v1) {
                            if($prolist[$k1]['thumb']){
                                $prolist[$k1]['thumb'] = remote($uniacid, $prolist[$k1]['thumb'], 1);
                            }
                            if($v1['use_more'] == 1){
                                $price = Db::name('wd_xcx_duo_products_type_value') ->where('pid', $v1['id']) ->column('price');
                                $prolist[$k1]['price'] = min($price);
                                $values = Db::name("wd_xcx_duo_products_type_value")->where("pid", $v1['id'])->select();
                                foreach ($values as $ks => $vs) {
                                    $prolist[$k1]['sale_num'] = $prolist[$k1]['sale_num']+$vs['salenum']+$vs['vsalenum'];
                                }
                            }else{
                                $prolist[$k1]['sale_num'] = $prolist[$k1]['sale_tnum'];
                            }

                            $prolist[$k1]['discount_price'] = 0; //折扣价
                            $discount_status = intval($v1['discount_status']);
                            if($discount_status == 2){
                                $v1['discount'] = unserialize($v1['discount']);
                                foreach ($v1['discount'] as $key => $value) {
                                    if($grade == $value['grade']){
                                        if(floatval($value['discount']) > 0){
                                            $prolist[$k1]['discount_price'] = sprintf("%01.2f", $prolist[$k1]['price'] * floatval($value['discount']) * 0.1) < 0.01 ? 0.01 : sprintf("%01.2f", $prolist[$k1]['price'] * floatval($value['discount']) * 0.1);
                                            break;
                                        }
                                    }
                                }
                            }else if($v1['discount_status'] == 1){
                                $v1['discount'] = Db::name('wd_xcx_vipgrade')->where('grade', $grade)->where('uniacid', $uniacid)->where('discount_flag', 1)->field('discount_grade')->value('discount_grade');

                                $v1['discount'] = floatval($v1['discount']);
                                if($v1['discount'] > 0){
                                    $prolist[$k1]['discount_price'] = sprintf("%01.2f", $prolist[$k1]['price'] * floatval($v1['discount'] * 0.1)) < 0.01 ? 0.01 : sprintf("%01.2f", $prolist[$k1]['price'] * floatval($v1['discount'] * 0.1));

                                }
                            }

                        }
                    }
                }
            }
        }else{
            if($top_cid == 0){
                $top_cid = Db::name('wd_xcx_cate')->where("uniacid", $uniacid) ->where('cid', 0) ->where('statue', 1) ->where('type', 'showPro')->order('num desc,id desc')->find()['id']; //得到所有一级栏目
            }
            $top_cate = Db::name('wd_xcx_cate')->where("uniacid", $uniacid) ->where('cid', $top_cid) ->where('statue', 1) ->where('type', 'showPro') ->field("id,name, catepic")->order('num desc,id desc')->select(); //当前选中id
            if($catestyle == 4){
                $top_cate_sub = array_column($top_cate, 'id'); //得到二级栏目id集合
                $top_cate = array_reverse($top_cate);
                $top_cate[] = ['id' => $top_cid, 'name' => '全部', 'pagenum' => $pagesize];
                $top_cate = array_reverse($top_cate);
            }
            if($sub_cid == 0){
                $sub_cid = $top_cate[0]['id'];
            }
            if($top_cid == $sub_cid){
                $pids = Db::name('wd_xcx_cate_pro') ->where('cate_id', 'in' ,$top_cate_sub)  ->field('pid')->select();
            }else{
                $pids = Db::name('wd_xcx_cate_pro') ->where('cate_id', $sub_cid)  ->field('pid')->select();
            }
            $pids = array_column($pids, 'pid');
            $pids = array_unique($pids);

            $prolist = Db::name('wd_xcx_products') ->where('type', 'showProMore') ->where('is_sale', 0) -> where('id', 'in', $pids) ->field('id, type, is_more, thumb, title, desc, price, market_price, discount_status, discount, use_more,sale_num,sale_tnum')->order('num desc, id desc') ->limit($begin, $pagesize) ->select();
            $grade = Db::name('wd_xcx_superuser')->where("uniacid",$uniacid)->where('id', $suid)->value('grade');
            $grade = intval($grade) > 0 ? intval($grade) : 1;
            foreach ($prolist as $k1 => $v1) {
                if($prolist[$k1]['thumb']){
                    $prolist[$k1]['thumb'] = remote($uniacid, $prolist[$k1]['thumb'], 1);
                }
                if($v1['use_more'] == 1){
                    $price = Db::name('wd_xcx_duo_products_type_value') ->where('pid', $v1['id']) ->column('price');
                    $prolist[$k1]['price'] = min($price);
                    $values = Db::name("wd_xcx_duo_products_type_value")->where("pid", $v1['id'])->select();
                    foreach ($values as $ks => $vs) {
                        $prolist[$k1]['sale_num'] = $prolist[$k1]['sale_num']+$vs['salenum']+$vs['vsalenum'];
                    }
                }else{
                    $prolist[$k1]['sale_num'] = $prolist[$k1]['sale_tnum'];
                }

                $prolist[$k1]['discount_price'] = 0; //折扣价
                $discount_status = intval($v1['discount_status']);
                if($discount_status == 2){
                    $v1['discount'] = unserialize($v1['discount']);
                    foreach ($v1['discount'] as $key => $value) {
                        if($grade == $value['grade']){
                            if(floatval($value['discount']) > 0){
                                $prolist[$k1]['discount_price'] = sprintf("%01.2f", $prolist[$k1]['price'] * floatval($value['discount']) * 0.1) < 0.01 ? 0.01 : sprintf("%01.2f", $prolist[$k1]['price'] * floatval($value['discount']) * 0.1);
                                break;
                            }
                        }
                    }
                }else if($v1['discount_status'] == 1){
                    $v1['discount'] = Db::name('wd_xcx_vipgrade')->where('grade', $grade)->where('uniacid', $uniacid)->where('discount_flag', 1)->field('discount_grade')->value('discount_grade');
                    $v1['discount'] = floatval($v1['discount']);
                    if($v1['discount'] > 0){
                        $prolist[$k1]['discount_price'] = sprintf("%01.2f", $prolist[$k1]['price'] * floatval($v1['discount'] * 0.1)) < 0.01 ? 0.01 : sprintf("%01.2f", $prolist[$k1]['price'] * floatval($v1['discount'] * 0.1));
                    }
                }
            }
        }
        $result['data'] = [];
        $result['data']['top_cid'] = $top_cid;
        $result['data']['sub_cid'] = $sub_cid;
        $result['data']['catestyle'] = $catestyle;
        $result['data']['top_cate'] = $top_cate;
        $result['data']['slide_is'] = $slide_is;
        $result['data']['slides'] = $slides;
        $result['data']['sub_cate'] = $sub_cate;
        $result['data']['prolists'] = $prolist;
        $result['data']['sub_cate_name'] = $sub_cate_name;
        return json_encode($result);
    }

    private function getAllReserveCateAndPro($uniacid, $catestyle, $top_cid, $sub_cid, $begin, $pagesize, $suid){
        $top_cate = [];
        $slide_is = 0;
        $slides = '';
        $sub_cate = "";
        $prolist = "";
        $sub_cate_name = "";

        if($catestyle == 1 || $catestyle == 2){
            $top_cate = Db::name('wd_xcx_flashsale_cate')->where("uniacid", $uniacid) ->where('cid', 0) ->where('statue', 1) ->where('catefor', 'reserve') ->field("id,name, slide_is, randid")->order('num desc,id desc')->select(); //得到所有一级栏目
            if($top_cate){
                if($top_cid > 0){
                    $top_cate_now = Db::name('wd_xcx_flashsale_cate')->where("uniacid", $uniacid) ->where('id', $top_cid) ->where('statue', 1) ->where('catefor', 'reserve') ->field("id,name, slide_is, randid")->find();
                }else{
                    $top_cate_now = $top_cate[0];
                    $top_cid = $top_cate[0]['id'];
                }
                if($top_cate_now['slide_is'] == 1){
                    $top_cate_now['slides'] = Db::name('wd_xcx_products_url')->where("randid", $top_cate_now['randid'])->select();
                    unset($top_cate_now['randid']);
                    if($top_cate_now['slides']){
                        $slides = [];
                        foreach($top_cate_now['slides'] as $ks => $vs){
                            $slides[$ks]['url'] = remote($uniacid, $vs['url'], 1);
                        }
                        if(count($slides) == 0){
                            $slides = "";
                        }else{
                            $slide_is = 1;
                        }
                    }else{
                        $slides = "";
                    }
                }
                $sub_cate = Db::name('wd_xcx_flashsale_cate')->where("uniacid", $uniacid) ->where('cid', $top_cate_now['id']) ->where('statue', 1) ->where('catefor', 'reserve') ->field("id,name, catepic")->order('num desc,id desc')->select(); //当前选中id
                if($sub_cate){
                    foreach ($sub_cate as $k => $v) {
                        if($sub_cate[$k]['catepic']){
                            $sub_cate[$k]['catepic'] = remote($uniacid, $sub_cate[$k]['catepic'], 1);
                        }
                    }
                    if($sub_cid == 0){
                        $sub_cid = $sub_cate[0]['id'];
                    }
                    if($catestyle == 2){
                        $sub_cate_name = Db::name('wd_xcx_flashsale_cate')->where("uniacid", $uniacid) ->where('id', $sub_cid)->value("name");
                        $prolist = Db::name('wd_xcx_products') ->where('type', 'reserve') ->where('is_sale', 0) -> where('cid', $sub_cid) ->field('id, type, is_more, thumb, title, desc, price, market_price, discount_status, discount, use_more,sale_num,sale_tnum')->order('num desc, id desc') ->limit($begin, $pagesize) ->select();
                        $grade = Db::name('wd_xcx_superuser')->where("uniacid",$uniacid)->where('id', $suid)->value('grade');
                        $grade = intval($grade) > 0 ? intval($grade) : 1;
                        foreach ($prolist as $k1 => $v1) {
                            if($prolist[$k1]['thumb']){
                                $prolist[$k1]['thumb'] = remote($uniacid, $prolist[$k1]['thumb'], 1);
                            }

                            $prolist[$k1]['discount_price'] = 0; //折扣价
                            $discount_status = intval($v1['discount_status']);
                            if($discount_status == 2){
                                $v1['discount'] = unserialize($v1['discount']);
                                foreach ($v1['discount'] as $key => $value) {
                                    if($grade == $value['grade']){
                                        if(floatval($value['discount']) > 0){
                                            $prolist[$k1]['discount_price'] = sprintf("%01.2f", $prolist[$k1]['price'] * floatval($value['discount']) * 0.1) < 0.01 ? 0.01 : sprintf("%01.2f", $prolist[$k1]['price'] * floatval($value['discount']) * 0.1);
                                            break;
                                        }
                                    }
                                }
                            }else if($v1['discount_status'] == 1){
                                $v1['discount'] = Db::name('wd_xcx_vipgrade')->where('grade', $grade)->where('uniacid', $uniacid)->where('discount_flag', 1)->field('discount_grade')->value('discount_grade');

                                $v1['discount'] = floatval($v1['discount']);
                                if($v1['discount'] > 0){
                                    $prolist[$k1]['discount_price'] = sprintf("%01.2f", $prolist[$k1]['price'] * floatval($v1['discount'] * 0.1)) < 0.01 ? 0.01 : sprintf("%01.2f", $prolist[$k1]['price'] * floatval($v1['discount'] * 0.1));

                                }
                            }

                        }
                    }
                }
            }
        }else{
            if($top_cid == 0){
                $top_cid = Db::name('wd_xcx_flashsale_cate')->where("uniacid", $uniacid) ->where('cid', 0) ->where('statue', 1) ->where('catefor', 'reserve')->order('num desc,id desc')->find()['id']; //得到所有一级栏目
            }
            $top_cate = Db::name('wd_xcx_flashsale_cate')->where("uniacid", $uniacid) ->where('cid', $top_cid) ->where('statue', 1) ->where('catefor', 'reserve') ->field("id,name, catepic")->order('num desc,id desc')->select(); //当前选中id
            if($catestyle == 4){
                $top_cate_sub = array_column($top_cate, 'id'); //得到二级栏目id集合
                $top_cate = array_reverse($top_cate);
                $top_cate[] = ['id' => $top_cid, 'name' => '全部', 'pagenum' => $pagesize];
                $top_cate = array_reverse($top_cate);
            }
            if($sub_cid == 0){
                $sub_cid = $top_cate[0]['id'];
            }
            if($top_cid == $sub_cid){
                $pids = $top_cate_sub;
            }else{
                $pids = $sub_cid;
            }

            $prolist = Db::name('wd_xcx_products') ->where('type', 'reserve') ->where('is_sale', 0) -> where('cid', 'in', $pids) ->field('id, type, is_more, thumb, title, desc, price, market_price, discount_status, discount, use_more,sale_num,sale_tnum')->order('num desc, id desc') ->limit($begin, $pagesize) ->select();
            $grade = Db::name('wd_xcx_superuser')->where("uniacid",$uniacid)->where('id', $suid)->value('grade');
            $grade = intval($grade) > 0 ? intval($grade) : 1;
            foreach ($prolist as $k1 => $v1) {
                if($prolist[$k1]['thumb']){
                    $prolist[$k1]['thumb'] = remote($uniacid, $prolist[$k1]['thumb'], 1);
                }

                $prolist[$k1]['discount_price'] = 0; //折扣价
                $discount_status = intval($v1['discount_status']);
                if($discount_status == 2){
                    $v1['discount'] = unserialize($v1['discount']);
                    foreach ($v1['discount'] as $key => $value) {
                        if($grade == $value['grade']){
                            if(floatval($value['discount']) > 0){
                                $prolist[$k1]['discount_price'] = sprintf("%01.2f", $prolist[$k1]['price'] * floatval($value['discount']) * 0.1) < 0.01 ? 0.01 : sprintf("%01.2f", $prolist[$k1]['price'] * floatval($value['discount']) * 0.1);
                                break;
                            }
                        }
                    }
                }else if($v1['discount_status'] == 1){
                    $v1['discount'] = Db::name('wd_xcx_vipgrade')->where('grade', $grade)->where('uniacid', $uniacid)->where('discount_flag', 1)->field('discount_grade')->value('discount_grade');
                    $v1['discount'] = floatval($v1['discount']);
                    if($v1['discount'] > 0){
                        $prolist[$k1]['discount_price'] = sprintf("%01.2f", $prolist[$k1]['price'] * floatval($v1['discount'] * 0.1)) < 0.01 ? 0.01 : sprintf("%01.2f", $prolist[$k1]['price'] * floatval($v1['discount'] * 0.1));
                    }
                }
            }
        }
        return [
            'top_cid' => $top_cid,
            'sub_cid' => $sub_cid,
            'catestyle' => $catestyle,
            'top_cate' => $top_cate,
            'slide_is' => $slide_is,
            'slides' => $slides,
            'sub_cate' => $sub_cate,
            'prolists' => $prolist,
            'sub_cate_name' => $sub_cate_name
        ];

    }


    private function getAllFlashCateAndPro($uniacid, $catestyle, $top_cid, $sub_cid, $begin, $pagesize){
        $top_cate = [];
        $slide_is = 0;
        $slides = '';
        $sub_cate = "";
        $prolist = "";
        $sub_cate_name = "";

        if($catestyle == 1 || $catestyle == 2){
            $top_cate = Db::name('wd_xcx_flashsale_cate')->where("uniacid", $uniacid) ->where('cid', 0) ->where('statue', 1) ->where('catefor', 'flashsale') ->field("id,name, slide_is, randid")->order('num desc,id desc')->select(); //得到所有一级栏目
            if($top_cate){
                if($top_cid > 0){
                    $top_cate_now = Db::name('wd_xcx_flashsale_cate')->where("uniacid", $uniacid) ->where('id', $top_cid) ->where('statue', 1) ->where('catefor', 'flashsale') ->field("id,name, slide_is, randid")->find();
                }else{
                    $top_cate_now = $top_cate[0];
                    $top_cid = $top_cate[0]['id'];
                }
                if($top_cate_now['slide_is'] == 1){
                    $top_cate_now['slides'] = Db::name('wd_xcx_products_url')->where("randid", $top_cate_now['randid'])->select();
                    unset($top_cate_now['randid']);
                    if($top_cate_now['slides']){
                        $slides = [];
                        foreach($top_cate_now['slides'] as $ks => $vs){
                            $slides[$ks]['url'] = remote($uniacid, $vs['url'], 1);
                        }
                        if(count($slides) == 0){
                            $slides = "";
                        }else{
                            $slide_is = 1;
                        }
                    }else{
                        $slides = "";
                    }
                }
                $sub_cate = Db::name('wd_xcx_flashsale_cate')->where("uniacid", $uniacid) ->where('cid', $top_cate_now['id']) ->where('statue', 1) ->where('catefor', 'flashsale') ->field("id,name, catepic")->order('num desc,id desc')->select(); //当前选中id
                if($sub_cate){
                    foreach ($sub_cate as $k => $v) {
                        if($sub_cate[$k]['catepic']){
                            $sub_cate[$k]['catepic'] = remote($uniacid, $sub_cate[$k]['catepic'], 1);
                        }
                    }
                    if($sub_cid == 0){
                        $sub_cid = $sub_cate[0]['id'];
                    }
                    if($catestyle == 2){
                        $sub_cate_name = Db::name('wd_xcx_flashsale_cate')->where("uniacid", $uniacid) ->where('id', $sub_cid)->value("name");
                        // $sub_cate[0]['name'];

                        $prolist = Db::name('wd_xcx_products') ->where('type', 'showPro') ->where('is_sale', 0) -> where('cid', $sub_cid) ->field('id, type, is_more, thumb, title, desc, price, market_price, discount_status, discount, use_more,sale_num,sale_tnum')->order('num desc, id desc') ->limit($begin, $pagesize) ->select();
                        foreach ($prolist as $k1 => $v1) {
                            if($prolist[$k1]['thumb']){
                                $prolist[$k1]['thumb'] = remote($uniacid, $prolist[$k1]['thumb'], 1);
                            }
                            $prolist[$k1]['discount_price'] = 0; //折扣价
                        }
                    }
                }
            }
        }else{
            if($top_cid == 0){
                $top_cid = Db::name('wd_xcx_flashsale_cate')->where("uniacid", $uniacid) ->where('cid', 0) ->where('statue', 1) ->where('catefor', 'flashsale')->order('num desc,id desc')->find()['id']; //得到所有一级栏目
            }
            $top_cate = Db::name('wd_xcx_flashsale_cate')->where("uniacid", $uniacid) ->where('cid', $top_cid) ->where('statue', 1) ->where('catefor', 'flashsale') ->field("id,name, catepic")->order('num desc,id desc')->select(); //当前选中id
            if($catestyle == 4){
                $top_cate_sub = array_column($top_cate, 'id'); //得到二级栏目id集合
                $top_cate = array_reverse($top_cate);
                $top_cate[] = ['id' => $top_cid, 'name' => '全部', 'pagenum' => $pagesize];
                $top_cate = array_reverse($top_cate);
            }
            if($sub_cid == 0){
                $sub_cid = $top_cate[0]['id'];
            }
            if($top_cid == $sub_cid){
                $pids = $top_cate_sub;
            }else{
                $pids = $sub_cid;
            }

            $prolist = Db::name('wd_xcx_products') ->where('type', 'showPro') ->where('is_sale', 0) -> where('cid', 'in', $pids) ->field('id, type, is_more, thumb, title, desc, price, market_price, discount_status, discount, use_more,sale_num,sale_tnum')->order('num desc, id desc') ->limit($begin, $pagesize) ->select();

            foreach ($prolist as $k1 => $v1) {
                if($prolist[$k1]['thumb']){
                    $prolist[$k1]['thumb'] = remote($uniacid, $prolist[$k1]['thumb'], 1);
                }
                $prolist[$k1]['discount_price'] = 0; //折扣价
            }
        }

        return [
            'top_cid' => $top_cid,
            'sub_cid' => $sub_cid,
            'catestyle' => $catestyle,
            'top_cate' => $top_cate,
            'slide_is' => $slide_is,
            'slides' => $slides,
            'sub_cate' => $sub_cate,
            'prolists' => $prolist,
            'sub_cate_name' => $sub_cate_name
        ];
    }


    //多规格下单获取运费
    public function doPageGetFreight()
    {
        $uniacid = input('uniacid');
        $suid = input('suid');
        $grade = Db::name('wd_xcx_superuser')->where('uniacid', $uniacid)->where('id', $suid)->value('grade');
        if($grade > 0){
            $free_package = Db::name('wd_xcx_vipgrade')->where('uniacid', $uniacid)->where('grade', $grade)->where('status', 1)->value('free_package');
            if($free_package == 1){ //会员包邮
                $result['data']['error'] = -1;
                return json_encode($result);
            }
        }
        $pro_city = trim(input("pro_city")); //省市：
        $province = json_decode(file_get_contents(ROOT_PATH ."public/json/province.json"),true); //得到全国省

        $buydata = html_entity_decode(input('buydata'));
        $buydata = json_decode($buydata, TRUE);

        $freight_price = 0; //运费
        $freight_arr = [];
        $result = [];
        foreach ($buydata as $ks => $vs) {
            $arr = explode('|', $vs); //得到产品id，模板id，数量
            $proinfo = Db::name('wd_xcx_products')->where('uniacid', $uniacid)->where('id', $arr[0])->field('id, freight_type, freight_price, yunfei_ggid')->find();

            if($proinfo['freight_type'] == 1){ //运费模板
                if(!$proinfo['yunfei_ggid']){ //未设置模板走默认模板
                    $freight_model = Db::name('wd_xcx_freight')->where('uniacid', $uniacid)->where('is_enable', 1)->where('is_delete', 0)->find();
                    if(!$freight_model ){ //默认模板不存在，返回报错 1
                        $result['data']['error'] = 1;
                        return json_encode($result);

                    }
                }else{
                    $freight_model = Db::name('wd_xcx_freight')->where('uniacid', $uniacid)->where('id', $proinfo['yunfei_ggid'])->where('is_delete', 0)->find();
                    if(!$freight_model){ //设置模板不存在，返回报错 1
                        $result['data']['error'] = 1;
                        $result['data']['error_msg'] = "运费模板不存在";
                        return json_encode($result);
                    }
                }
                if(!$freight_model['detail']){
                    $result['data']['error'] = 2;
                    return json_encode($result);
                }
                $pro_arr = array_values(json_decode(stripslashes(html_entity_decode($freight_model['detail'])), true));
                $is_city = 0;
                foreach ($province as $k => $v) {
                    foreach ($pro_arr as $ki => $vi) {
                        foreach ($vi['province_list'] as $key => $value) {
                            if($value['ProID'] == $v['ProID']){
                                $city = $v['name'].' '.$value['name'];
                                if($pro_city == $city){
                                    $is_city ++;
                                    if(isset($freight_arr[$proinfo['yunfei_ggid']])){
                                        $freight_arr[$proinfo['yunfei_ggid']][0] += $arr[2];
                                    }else{
                                        $freight_arr[$proinfo['yunfei_ggid']] = [$arr[2], $vi['first'], $vi['first_price'], $vi['second'], $vi['second_price']]; //数量, 首件, 首件价格，续件，续件价格
                                    }
                                }
                            }
                        }
                    }
                }
                if(!$is_city){//收货地址不在运费模板中，返回报错 2
                    $result['data']['error'] = 2;
                    $result['data']['error_msg'] = "收货地址不在运费模板中";
                    return json_encode($result);
                }

            }else{
                if(isset($freight_arr['type0'])){
                    array_push($freight_arr['type0'], $proinfo['freight_price']);
                }else{
                    $freight_arr['type0'][] = $proinfo['freight_price'];
                }
            }
        }
        $freight_fixed_price = 0; //运费固定价格
        $freight_model_price = 0; //运费模板价格

        if(isset($freight_arr['type0'])){
            $freight_fixed_price = max($freight_arr['type0']); //得到固定运费价格
        }

        $first_prices = []; //定义运费模板首件价格组
        unset($freight_arr['type0']);
        foreach ($freight_arr as $k => $v) {
            array_push($first_prices, $v[2]);
        }
        if(count($first_prices) > 0){
            $first_price = max($first_prices); //得到运费模板首件运费最高价
            $first_price_num = 0;
            foreach ($first_prices as $k => $v) {
                if($first_price == $v){
                    $first_price_num++;
                }
            }
            if($first_price_num > 1){
                $first_second_prices = []; //首件价格存在多个，算续件,统计续件价格组；
                foreach ($freight_arr as $k => $v) {
                    if($v[2] == $first_price){
                        array_push($first_second_prices, $v[4]);
                    }
                }
                $first_second_price = max($first_second_prices); //得到续件价格
                foreach ($freight_arr as $k => $v) {
                    if($v[2] == $first_price){//运费模板为首件运费最高模板
                        if($v[4] == $first_second_price){ //判断续件为最高的运费模板算首件费用
                            $freight_model_price += $v[2]; //运费首件价
                            if($v[0] > $v[1]){ //存在续件运费
                                $freight_model_price += ceil(($v[0] - $v[1]) / $v[3]) * $v[4]; //续件费用
                            }
                        }else{
                            $freight_model_price += ceil($v[0] / $v[3]) * $v[4]; //续件费用
                        }
                    }else{//运费模板为非首件运费最高模板，所有购买数量按续件算
                        $freight_model_price += ceil($v[0] / $v[3]) * $v[4]; //续件费用
                    }
                }
            }else{
                foreach ($freight_arr as $k => $v) {  //$v[i] i=0下单数量 i=1首件数量 i=2首件运费 i=3续件数量 i=4续件运费
                    if($v[2] == $first_price){//运费模板为首件运费最高模板
                        $freight_model_price += $v[2]; //运费首件价
                        if($v[0] > $v[1]){ //存在续件运费
                            $freight_model_price += ceil(($v[0] - $v[1]) / $v[3]) * $v[4]; //续件费用
                        }
                    }else{//运费模板为非首件运费最高模板，所有购买数量按续件算
                        $freight_model_price += ceil($v[0] / $v[3]) * $v[4]; //续件费用
                    }
                }
            }
        }

        $all_freight_price = sprintf("%01.2f", ($freight_fixed_price + $freight_model_price));
        $result['data']['error'] = 0;
        $result['data']['all_freight_price'] = $all_freight_price;
        return json_encode($result);
    }

    //多规格下单获取运费
    public function doPageCheckPro()
    {
        $suid = input('suid');
        $uniacid = input('uniacid');
        $page = max(intval(input('page')), 1);
        $pagesize = 10;
        $begin = ($page - 1) * $pagesize;

        $search_keys = input('search_keys'); //搜索关键词
        $cate_id = intval(input('cate_id'));

        $sort_type = input('sort_type'); // 1新品 2销量 3价格
        $sort_type_attr = input('sort_type_attr'); // 1倒序 2正序
        $is_vip_price = input('is_vip_price'); // 会员价格 0不做查询 1有 2无
        $price_min = input('price_min');
        $price_max = input('price_max');
        $where = [];
        $where['uniacid'] = $uniacid;
        if($search_keys){
            $where['title'] = ['like', '%'.$search_keys.'%'];
        }
        if($cate_id > 0){
            $pids = Db::name('wd_xcx_cate_pro')->where('uniacid', $uniacid)->where('cate_id', $cate_id)->column('pid');
            $where['id'] = ['in', $pids];
        }

        $order_where = '';
        if($sort_type == 1 && $sort_type_attr == 1){
            $order_where = "id desc";
        }else if($sort_type == 1 && $sort_type_attr == 2){
            $order_where = "id asc";
        }

        if($is_vip_price == 1){
            $where['discount_status'] = ['>', 0];
        }else if($is_vip_price == 2){
            $where['discount_status'] = 0;
        }
        if(!$price_min && !$price_max && $sort_type != 3 && $sort_type != 2){
            $prolist = Db::name('wd_xcx_products')->where($where)->where('is_sale', 0)->where('type', 'showProMore')->order($order_where)->field('id, type, thumb, title, price, market_price, discount_status, discount, use_more, sale_num, sale_tnum')->limit($begin, $pagesize)->select(); //无价格区间和价格排序
            if(count($prolist) == 0){
                $result['data'] = [];
                return json_encode($result);
            }
            foreach ($prolist as $k => $v) {
                if($v['thumb']){
                    $prolist[$k]['thumb'] = remote($uniacid, $v['thumb'], 1);
                }
                $prolist[$k]['sale_total'] =  $this -> getProSaleTotal($uniacid, $v['id']);//总销量 虚拟加实售
                $prolist[$k]['discount_price'] = 0;
                if($v['discount_status'] > 0){
                    $prolist[$k]['discount_price'] = $this -> getProDiscounts(1,$uniacid, $suid, $v['id'])['discount_price'];
                }
            }
            $result['data'] = $prolist;
            return json_encode($result);
        }
        //价格/销量排序或价格区间
        $prolist = Db::name('wd_xcx_products')->where($where)->where('is_sale', 0)->where('type', 'showProMore')->order($order_where)->field('id, type, thumb, title, price, market_price, discount_status, discount, use_more, sale_num, sale_tnum')->limit($begin, $pagesize)->select();
        if(count($prolist) == 0){
            $result['data'] = [];
            return json_encode($result);
        }
        foreach ($prolist as $k => $v) {
            if($v['thumb']){
                $prolist[$k]['thumb'] = remote($uniacid, $v['thumb'], 1);
            }
            if($v['use_more'] == 2){ //单规格商品
                $prolist[$k]['min_price'] = $v['price'];
            }else{
                $prolist[$k]['min_price'] = Db::name('wd_xcx_duo_products_type_value') ->where('pid', $v['id']) ->min('price');
            }
            $prolist[$k]['discount_price'] = 0;
            if($v['discount_status'] > 0){
                $prolist[$k]['discount_price'] = $this -> getProDiscounts(1,$uniacid, $suid, $v['id'])['discount_price'];
            }
            $prolist[$k]['sale_total'] =  $this -> getProSaleTotal($uniacid, $v['id']);//总销量 虚拟加实售
        }


        if($sort_type == 3){  //按价格排序
            //根据字段min_price对数组$data进行降序排列
            $min_prices = array_column($prolist, 'min_price');
            if($sort_type_attr == 1){
                array_multisort($min_prices, SORT_DESC, $prolist);
            }else{
                array_multisort($min_prices, SORT_ASC, $prolist);
            }
        }
        if($sort_type == 2){  //按销量排序
            //根据字段min_price对数组$data进行降序排列
            $sale_total = array_column($prolist, 'sale_total');
            if($sort_type_attr == 1){
                array_multisort($sale_total, SORT_DESC, $prolist);
            }else{
                array_multisort($sale_total, SORT_ASC, $prolist);
            }
        }
        if($price_min || $price_max){ //有价格区间时
            $new_pro = [];
            foreach ($prolist as $k => $v) {
                if($price_min && $price_max){
                    if(floatval($v['min_price']) >= floatval($price_min) && floatval($v['min_price']) <= floatval($price_max)){
                        $new_pro[] = $v;
                    }
                }else if($price_max){
                    if(floatval($v['min_price']) <= floatval($price_max)){
                        $new_pro[] = $v;
                    }
                }else if($price_max){
                    if(floatval($v['min_price']) <= floatval($price_max)){
                        $new_pro[] = $v;
                    }
                }
            }
            $res_pro = [];
            foreach ($new_pro as $k => $v) {
                if($k >= $begin && $k < ($begin + $pagesize)){
                    $res_pro[] = $v;
                }
            }
        }else{
            $res_pro = $prolist;
        }

        $result['data'] = $res_pro;
        return json_encode($result);
    }
        /**
     * [getProSaleTotal 获取商品总销量]
     * @param  [type] $uniacid [description]
     * @param  [type] $pid     [description]
     * @return [type]          [description]
     */
    private function getProSaleTotal($uniacid, $pid){
        $proinfo = Db::name('wd_xcx_products')->where('id', $pid)->find();
        if($proinfo['use_more'] == 1){ //use_more 1 多规格  0 单规格
            $sale_num = Db::name('wd_xcx_duo_products_type_value')->where("pid", $pid)->sum('salenum'); //商品实际销量
            $vsalenum = Db::name('wd_xcx_duo_products_type_value')->where("pid", $pid)->sum('vsalenum'); //商品虚拟销量
            $sale_total = $sale_num + $vsalenum;
        }else{
            $sale_total = $proinfo['sale_num'] + $proinfo['sale_tnum'];
        }
        return $sale_total;
    }

    /**
     * [doPagegetmygwc 获取购物车数据]
     * @return [type] [description]
     */
    public function doPagegetmygwc(){
        $uniacid = input('uniacid');
        $suid = input('suid');

        $result = [
            'data' => []
        ];

        if(!$suid){
            return $result;
        }

        //我的购物车数据
        $mygwc = Db::name('wd_xcx_duo_products_gwc')->alias("a")->join("wd_xcx_products b", "a.pvid = b.id")->where('a.suid', $suid)->where('a.flag', 1)->where('b.id', 'gt', 0)->field("a.*,b.kuaidi") ->select();
        //获取商品设置数据
        $take_self = Db::name('wd_xcx_duo_products_yunfei') ->where('uniacid', $uniacid) ->value('take_self');
        if(!$take_self){
            $take_self = 1;
        }
        $express_data = [
            'normal' => [],
            'invalid' => []
        ];  //仅商品
        $take_data = [
            'normal' => [],
            'invalid' => []
        ];   //仅自取
        $all_support_data = [
            'normal' => [],
            'invalid' => []
        ]; //全部支持
        $invalid_ids = [];  //全部失效购物车id

        foreach ($mygwc as $key => $value){
            $pro_info = Db::name('wd_xcx_products') ->where('id', $value['pvid']) ->field('title, thumb, use_more, is_sale, pro_kc') ->find();
            if($pro_info){
                if($pro_info['thumb']){
                    $pro_info['thumb'] = remote($uniacid, $pro_info['thumb'], 1);
                }
                if($value['pid'] != -1){
                    $type = Db::name('wd_xcx_duo_products_type_value') ->where('id', $value['pid']) ->find();
                    if($type){
                        $pro_info['ggz'] = $this->getProTypeValue($value['pid']);
                        $pro_info['pro_kc'] = intval($type['kc']);
                    }else{  //如果规格值不存在  商品置为失效
                        $value['pro_info'] = $pro_info;
                        if($take_self != 1){ //开启自取  有三种配送方式
                            if($value['kuaidi'] == 0){
                                array_push($express_data['invalid'], $value);
                                array_push($invalid_ids, $value['id']);
                            }else if($value['kuaidi'] == 1){
                                array_push($take_data['invalid'], $value);
                                array_push($invalid_ids, $value['id']);
                            }else{
                                array_push($all_support_data['invalid'], $value);
                                array_push($invalid_ids, $value['id']);
                            }
                        }else{  //关闭自取  只有快递配送
                            array_push($express_data['invalid'], $value);
                            array_push($invalid_ids, $value['id']);
                        }
                        continue;
                    }

                }else{
                    $pro_info['ggz'] = '';
                }
                $price_data = $this->getProDiscounts(0,$uniacid, $suid, $value['pvid'], 1, $value['pid']);
                $pro_info['discounts'] = $price_data['discounts'];
                $pro_info['discount_price'] = $price_data['discount_price'];
                $pro_info['discount_price'] = number_format($pro_info['discount_price'], 2);
                $value['pro_info'] = $pro_info;
                if($take_self != 1){ //开启自取  有三种配送方式
                    if($value['kuaidi'] == 0){
                        if($pro_info['is_sale'] == 0){
                            if(($value['pid'] == -1 && $pro_info['use_more'] == 1) || ($value['pid'] != -1 && $pro_info['use_more'] == 2)){
                                array_push($express_data['invalid'], $value);
                                array_push($invalid_ids, $value['id']);
                            }else{
                                array_push($express_data['normal'], $value);
                            }
                        }else{
                            array_push($express_data['invalid'], $value);
                            array_push($invalid_ids, $value['id']);
                        }

                    }else if($value['kuaidi'] == 1){
                        if($pro_info['is_sale'] == 0){
                            if(($value['pid'] == -1 && $pro_info['use_more'] == 1) || ($value['pid'] != -1 && $pro_info['use_more'] == 2)){
                                array_push($take_data['invalid'], $value);
                                array_push($invalid_ids, $value['id']);
                            }else{
                                array_push($take_data['normal'], $value);
                            }
                        }else{
                            array_push($take_data['invalid'], $value);
                            array_push($invalid_ids, $value['id']);
                        }
                    }else{
                        if($pro_info['is_sale'] == 0){
                            if(($value['pid'] == -1 && $pro_info['use_more'] == 1) || ($value['pid'] != -1 && $pro_info['use_more'] == 2)){
                                array_push($all_support_data['invalid'], $value);
                                array_push($invalid_ids, $value['id']);
                            }else{
                                array_push($all_support_data['normal'], $value);
                            }
                        }else{
                            array_push($all_support_data['invalid'], $value);
                            array_push($invalid_ids, $value['id']);
                        }
                    }
                }else{  //关闭自取  只有快递配送
                    if($pro_info['is_sale'] == 0){
                        if(($value['pid'] == -1 && $pro_info['use_more'] == 1) || ($value['pid'] != -1 && $pro_info['use_more'] == 2)){
                            array_push($express_data['invalid'], $value);
                            array_push($invalid_ids, $value['id']);
                        }else{
                            array_push($express_data['normal'], $value);
                        }
                    }else{
                        array_push($express_data['invalid'], $value);
                        array_push($invalid_ids, $value['id']);
                    }
                }
            }
        }

        $result['data'] = [
            'express_data' => $express_data,
            'take_data' => $take_data,
            'all_support_data' => $all_support_data,
            'take_self' => $take_self,
            'invalid_ids' => implode(',', $invalid_ids)
        ];
        return json_encode($result);
    }

    /**
     * 删除多个购物车数据
     */
    public function doPageDelGwcs(){
        $uniacid = input('uniacid');
        $ids = input('gwc_ids');
        $suid = input('suid');

        if($ids){
            $ids = explode(',', $ids);
            $res = Db::name('wd_xcx_duo_products_gwc') ->where([
                'uniacid' => $uniacid,
                'suid' => $suid
            ]) ->delete($ids);
            if($res){
                return json_encode(['data' => ['error' => 0]]);
            }else{
                return json_encode(['data' => ['error' => 2, 'msg' => '删除失败，请稍后再试！']]);
            }
        }else{
            return json_encode(['data' => ['error' => 1, 'msg' => '没有需要删除的商品！']]);
        }
    }



    /**
     * [getProTypeValue 处理商品规格值]
     * @param  [type] $type_id [规格值ID]
     * @return [type]          [组合的规格值字符串]
     */
    private function getProTypeValue($type_id){
        $type = Db::name('wd_xcx_duo_products_type_value') ->where('id', $type_id) ->find();
        if($type){
            $gg = $type['comment'];
            $ggarr = explode(",", $gg);
            $str = "";
            foreach ($ggarr as $index => $rec) {
                $i = $index + 1;
                $kk = "type" . $i;
                $str .= $rec . ":" . $type[$kk] . ",";
            }
            $str = substr($str, 0, strlen($str) - 1);
            return $str;
        }else{
            return '';
        }

    }



    // 多规格下单页面积分转换
    public function dopageptsetgwcscore(){
        $uniacid = input("uniacid");
        $suid = input("suid");
        $buydata = html_entity_decode(input('buydata'));
        $buydata = json_decode($buydata, TRUE);
        // $buydata = ['129|1508|1', '133|1509|2', '34|-1|5']; //产品id|规格值id(-1为单规格)|数量
        $jifen = 0;
        foreach ($buydata as $key => $res) {
            $buy_pro = explode('|', $res);
            $num = $buy_pro[2];
            $baseinfo = Db::name("wd_xcx_products") ->where('id',$buy_pro[0]) ->find();
            $score = $baseinfo['score'];
            if($score){
                $jifen += intval($score) * $num;
            }
        }
        //积分转换成金钱
        $jf_gz=Db::name("wd_xcx_rechargeconf")->where("uniacid",$uniacid)->find();
        if(!$jf_gz){
            $gzscore = 100;
            $gzmoney = 1;
        }else{
            $gzscore = intval($jf_gz['score']);
            $gzmoney = intval($jf_gz['money']);
        }
        // 我的积分抵用
        $userinfo=Db::name("wd_xcx_superuser")->where("id",$suid)->where("uniacid",$uniacid)->find();
        $score = $userinfo['score'];
        //比较我的积分和扣除积分
        $data = array();
        if($jifen >= 0 && $score>=$jifen){
            $zhmoney = ($jifen * $gzmoney)/$gzscore;
            $moneycl = floor($zhmoney);
            $jf = $moneycl * $gzscore;
        }else{
            $zhmoney = ($score * $gzmoney)/$gzscore;
            $moneycl = floor($zhmoney);
            //消费掉的积分
            $jf = $moneycl * $gzscore;
        }
        $data["moneycl"] = $moneycl;
        $data["jf"] = $jf;
        $data["gzscore"] = $gzscore;
        $data["gzmoney"] = $gzmoney;
        return json_encode(['data' => $data]);
    }


    //多规格下单获取优惠券
    public function doPageGetMyCoupon(){
        $uniacid = input('uniacid');
        $suid = input('suid');
        $buydata = html_entity_decode(input('buydata'));
        $buydata = json_decode($buydata, TRUE);
        $total_price = input('total_price');

        $buy_pro_ids = [];
        foreach ($buydata as $key => $res) {
            $buy_pro = explode('|', $res);
            array_push($buy_pro_ids, $buy_pro[0]);
        }
        $my_coupon = Db::name('wd_xcx_coupon_user') ->where([
                'uniacid' => $uniacid,
                'suid' => $suid,
                'flag' => 0
            ]) ->order('id desc') ->select();
        $can_use = [];
        $canot_use = [];
        foreach ($my_coupon as $key => $value) {
            $value['etime'] = $value['etime'] == 0 ? 2133999048 : $value['etime'];
            if( $value['btime'] < time() && $value['etime'] > time()){  //在使用时间内

                $my_coupon[$key]['etime'] = date('Y-m-d', $value['etime']);
                $my_coupon[$key]['btime'] = $my_coupon[$key]['btime'] == 0 ? '现在' : date('Y-m-d', $value['btime']);
                $my_coupon[$key]['is_check'] = 0;

                if($value['use_type'] == 0){
                    if($total_price >= $value['pay_money']){
                        $my_coupon[$key]['can_use'] = true;
                        array_push($can_use, $my_coupon[$key]);
                    }else{
                        $my_coupon[$key]['can_use'] = false;
                        $my_coupon[$key]['can_use_msg'] = '不满足使用金额';
                        array_push($canot_use, $my_coupon[$key]);
                    }
                }else{
                    //获取所有可用的栏目
                    $pro_ids = []; //可以使用栏目中所有可以使用的商品ID
                    $use_cate = explode(',', $value['use_class']);
                    foreach ($use_cate as $k => $v){
                        if(substr($v, 0, 1) == 'a'){
                            $cate_id = substr($v, 1);
                            $cate_cid = Db::name('wd_xcx_cate') ->where('id', $cate_id) ->where('statue', 1) ->value('cid');
                            if($cate_cid == 0){  //顶级栏目 查子栏目再查商品
                                $child_cate = Db::name('wd_xcx_cate') ->where('cid', $cate_id) ->where('statue', 1) ->column('id');
                                $pros = Db::name('wd_xcx_cate_pro') ->where('cate_id', 'in', $child_cate) ->column('pid');
                                $pro_ids = array_unique(array_merge($pro_ids, $pros));
                            }else{  //二级栏目  直接查商品
                                $pros = Db::name('wd_xcx_cate_pro') ->where('cate_id', $cate_id) ->column('pid');
                                $pro_ids = array_unique(array_merge($pro_ids, $pros));
                            }
                        }
                    }

                    //优惠券是否所有商品都能用
                    if($buy_pro_ids == array_intersect($buy_pro_ids, $pro_ids)){
                        if($total_price >= $value['pay_money']){
                            $my_coupon[$key]['can_use'] = true;
                            array_push($can_use, $my_coupon[$key]);
                        }else{
                            $my_coupon[$key]['can_use'] = false;
                            $my_coupon[$key]['can_use_msg'] = '不满足使用金额';
                            array_push($canot_use, $my_coupon[$key]);
                        }
                    }else{
                        $my_coupon[$key]['can_use'] = false;
                        $my_coupon[$key]['can_use_msg'] = '不支持使用此优惠券';
                        array_push($canot_use, $my_coupon[$key]);
                    }
                }
            }else{  //不在使用时间内
                $my_coupon[$key]['btime'] = date('Y-m-d', $value['btime']);
                $my_coupon[$key]['etime'] = date('Y-m-d', $value['etime']);
                $my_coupon[$key]['can_use'] = false;
                $my_coupon[$key]['can_use_msg'] = '非使用时间区间';
                array_push($canot_use, $my_coupon[$key]);
            }

        }
        $flag = [];
        foreach ($can_use as $can){
            $flag[] = $can['price'];
        }
        array_multisort($flag, SORT_DESC, $can_use);
        $my_coupon = array_merge($can_use, $canot_use);

        //获取用户余额
        $userinfo = Db::name('wd_xcx_superuser') ->where('id', $suid) ->field('money') ->find();


        return json_encode(['data'=>[
                'coupon_list' => $my_coupon,
                'userinfo' => $userinfo
            ]]);
    }

    //多规格订单获取我的联系方式
    public function getMyContactInfo(){
        $uniacid = input('uniacid');
        $suid = input('suid');

        $shop_id = input('shop_id');
        $add_id = input('add_id');

        if($add_id){
            $default_address = Db::name('wd_xcx_duo_products_address') ->where([
                'uniacid' => $uniacid,
                'id' => $add_id,
            ]) ->find();
        }else{
            $default_address = Db::name('wd_xcx_duo_products_address') ->where([
                'uniacid' => $uniacid,
                'suid' => $suid,
                'is_mo' => 2
            ]) ->find();
            if(!$default_address){
                $default_address = Db::name('wd_xcx_duo_products_address') ->where([
                    'uniacid' => $uniacid,
                    'suid' => $suid,
                    'is_mo' => 1,
                ]) ->order('id desc') ->find();
            }
        }

        if($shop_id){
            $shop_info = Db::name('wd_xcx_store') ->where('uniacid', $uniacid) ->where('id', $shop_id) ->find();
        }else{
            $type = input('type');
            if($type == 'flashSale' || $type == 'pt' || $type == 'bargain'){
                $baseinfo = Db::name('wd_xcx_base')->where('uniacid', $uniacid)->field('name, tel, address')->find();
                if($baseinfo){
                    $shop_info = ['id' => 0, 'title' => $baseinfo['name'], 'tel' => $baseinfo['tel'], 'province' => '', 'city' => '', 'country' => $baseinfo['address'], 'times' => ''];
                }else{
                    $shop_info = null;
                }
            }else{
                $shop_info = null;
            }
        }

        $default_concat = Db::name('wd_xcx_duo_products_address') ->where([
                'uniacid' => $uniacid,
                'suid' => $suid,
                'is_mo' => 3
            ]) ->find();
        $result['data'] = [
            'default_address' => $default_address,
            'default_concat' => $default_concat,
            'shop_info' => $shop_info
        ];
        //获取商品自取设置
        $take_self = Db::name('wd_xcx_duo_products_yunfei') ->where('uniacid', $uniacid) ->value('take_self');
        if($take_self){
            $result['data']['take_self'] = $take_self;
        }else{
            $result['data']['take_self'] = 1;
        }

        $baoyou = Db::name('wd_xcx_duo_products_yunfei') ->where('uniacid', $uniacid) ->value('byou');
        if($baoyou !== null){
            $result['data']['baoyou'] = intval($baoyou);
        }else{
            $result['data']['baoyou'] = -1;
        }
        return json_encode($result);
    }

    //获取表单信息
    public function getFormInfo(){
        $uniacid = input('uniacid');
        $form_id = Db::name('wd_xcx_duo_products_yunfei') ->where('uniacid', $uniacid) ->value('formset');
        if($form_id){
            $form = $this ->getFormContent($uniacid, $form_id);
            if($form){
                return json_encode(['data' => $form]);
            }else{
                return json_encode(['data' => null]);
            }
        }else{
            return json_encode(['data' => null]);
        }
    }


    //添加自取联系方式
    public function updateTakingContact(){
        $uniacid = input('uniacid');
        $suid = input('suid');
        $mobile = input('mobile');

        $has = Db::name('wd_xcx_duo_products_address') ->where([
                'uniacid' => $uniacid,
                'suid' => $suid,
                'is_mo' => 3
            ]) ->find();
        if($has){
            $res = Db::name('wd_xcx_duo_products_address') ->where([
                'uniacid' => $uniacid,
                'suid' => $suid,
                'is_mo' => 3
            ]) ->update(['mobile'=>$mobile]);
        }else{
            $res = Db::name('wd_xcx_duo_products_address') ->insert([
                'uniacid' => $uniacid,
                'suid' => $suid,
                'name' => '',
                'mobile' => $mobile,
                'address' => '',
                'more_address' => '',
                'postalcode' => '',
                'is_mo' => 3,
                'creattime' => time(),
                'froms' => 'weixin'
            ]);
        }

        if(!$res){
            $result['data'] = [
                'error' => 1,
                'msg' => '操作失败！'
            ];
            return json_encode($result);
        }


    }


    //多规格订单获取下单商品的信息
    public function getBuyGoodsInfo(){
        $uniacid = input('uniacid');
        $suid = input('suid');
        $buydata = html_entity_decode(input('buydata'));
        $buydata = json_decode($buydata, TRUE);
//        $buydata = ['129|1554|1', '133|1579|2', '128|-1|5', '127|1550|3']; //产品id|规格值id(-1为单规格)|数量

        $total_num = 0;
        $total_price = 0;
        $total_discount_price = 0;
        $buyPro = [];
        foreach ($buydata as $key => $res) {
            $buy_pro = explode('|', $res);
            $proInfo = Db::name("wd_xcx_products") ->where('id',$buy_pro[0]) ->where('is_sale', 0) ->field('id, title, thumb, price, kuaidi') ->find();
            if($proInfo){
                if($proInfo['thumb']){
                    $proInfo['thumb'] = remote($uniacid, $proInfo['thumb'], 1);
                }
                if($buy_pro[1] != -1){
                    $pro_type_value = Db::name('wd_xcx_duo_products_type_value') ->where('id', $buy_pro[1]) ->find();
                    if($pro_type_value){
                        $proInfo['ggarr'] = $this->getProTypeValue($buy_pro[1]);
                        $proInfo['price'] = $pro_type_value['price'];
                    }else{
                        return json_encode(['data' => ['error' => 1, 'msg' => '商品已失效']]);
                    }

                }else{
                    $proInfo['ggarr'] = '';
                }

                $discount_data = $this->getProDiscounts(0,$uniacid, $suid, $buy_pro[0], 1, $buy_pro[1]);
                $proInfo['discounts'] = $discount_data['discounts'];
                $proInfo['discount_price'] = $discount_data['discount_price'];
                $proInfo['num'] = intval($buy_pro[2]);
                $proInfo['subtotal'] = $proInfo['num'] * $proInfo['discount_price']; //小计
                $proInfo['sub_dis_price'] = $proInfo['price'] * $proInfo['num'] - $proInfo['subtotal'];
                $total_discount_price = $total_discount_price + $proInfo['sub_dis_price'];
                $total_price = $total_price + $proInfo['subtotal'];

                $proInfo['price'] = number_format($proInfo['price'], 2);
                $proInfo['discount_price'] = number_format($proInfo['discount_price'], 2);
                $proInfo['subtotal'] = number_format($proInfo['subtotal'], 2);
                $proInfo['sub_dis_price'] = number_format($proInfo['sub_dis_price'], 2);

                array_push($buyPro, $proInfo);
                $total_num = $total_num + $buy_pro[2];
            }else{
                return json_encode(['data' => ['error' => 1, 'msg' => '商品已下架，请重新选择']]);
            }

        }

        $total_price = number_format($total_price, 2, '.', '');

        $data = [
            'error' => 0,
            'buyPro' => $buyPro,
            'total_num' => $total_num,
            'total_price' => $total_price,
            'total_discount_price' => $total_discount_price
        ];

        return json_encode(['data' => $data]);

    }


    //多规格订单创建订单
    public function createMainGoodsOrder(){
        $uniacid = input('uniacid');
        $suid = input('suid');
        $source = input('source');

        $buydata = html_entity_decode(input('buydata'));
        $buydata = json_decode($buydata, TRUE);
        $buy_data = $buydata;

        $buy_pro_ids = [];
        $buy_pro = [];
        //处理下单商品
        foreach ($buydata as $key => $value) {
            $value = explode('|', $value);
            array_push($buy_pro, $value);
            array_push($buy_pro_ids, $value[0]);
        }
        $delivery_type = input('delivery_type');
        $pay_type = input('pay_type');

        $pay_money = input('pay_money');  // 总实付金额
        $discount_money = input('discount_money');  //总优惠金额（优惠券，积分）
        $freight_money = input('freight_money');  //总配送费 
        $total_num = input('total_num');  //订单总计商品数量

        $score_use = input('score_use');  //使用的积分
        $score_money = input('score_money'); //积分抵扣的金额

        $coupon_use = input('coupon_use');   //优惠券抵扣金额
        $coupon_id = input('coupon_id');   //优惠券领取ID

        $from_gwc = input('from_gwc');

        $userinfo = Db::name('wd_xcx_superuser') ->where('id', $suid) ->find();
        $order_id = date('YmdHi', time()).substr(microtime(), 2, 4).rand(1000,9999);
        //检查积分
        if($userinfo['score'] < $score_use){
            return json_encode(['data' => ['error' => 1, 'msg' => '积分不足！']]);
        }

        //检查优惠券是否可用
        if($coupon_id){
            $cou_info = Db::name('wd_xcx_coupon_user') ->where('id', $coupon_id) ->where('flag', 0) ->find();
            if($cou_info){
                $cou_info['etime'] = $cou_info['etime'] == 0 ? 2133999048 : $cou_info['etime'];
                if(time() > $cou_info['btime'] && time() < $cou_info['etime']){
                    if($cou_info['use_type'] == 1){
                        $pro_ids = []; //可以使用栏目中所有可以使用的商品ID
                        $use_cate = explode(',', $cou_info['use_class']);
                        foreach ($use_cate as $k => $v){
                            if(substr($v, 0, 1) == 'a'){
                                $cate_id = substr($v, 1);
                                $cate_cid = Db::name('wd_xcx_cate') ->where('id', $cate_id)->where('statue', 1) ->value('cid');
                                if($cate_cid == 0){  //顶级栏目 查子栏目再查商品
                                    $child_cate = Db::name('wd_xcx_cate') ->where('cid', $cate_id) ->where('statue', 1) ->column('id');
                                    $pros = Db::name('wd_xcx_cate_pro') ->where('cate_id', 'in', $child_cate) ->column('pid');
                                    $pro_ids = array_unique(array_merge($pro_ids, $pros));
                                }else{  //二级栏目  直接查商品
                                    $pros = Db::name('wd_xcx_cate_pro') ->where('cate_id', $cate_id) ->column('pid');
                                    $pro_ids = array_unique(array_merge($pro_ids, $pros));
                                }
                            }
                        }
                        //优惠券是否所有商品都能用
                        if($buy_pro_ids != array_intersect($buy_pro_ids, $pro_ids)){
                            return json_encode(['data' => ['error' => 2, 'msg' => '商品不在优惠券使用栏目内！']]);
                        }
                    }
                }else{
                    return json_encode(['data' => ['error' => 2, 'msg' => '优惠券不在可使用时间！']]);
                }
            }else{
                return json_encode(['data' => ['error' => 2, 'msg' => '优惠券不存在或已使用！']]);
            }
        }


        //检查余额
        if($pay_type == 1 && $pay_money > $userinfo['money']){
            return json_encode(['data' => ['error' => 3, 'msg' => '用户余额不足！']]);
        }

        //处理商品  核算价格  组装子订单数据
        $goods_total_price = 0;
        $calc_price = 0;   //计算订单商品总价
        $order_item = [];
        $score_sent = 0;
        $gwc_ids = [];
        $total_discount_money = $pay_money + $discount_money;  //所有商品折扣的钱
        $shen_discount_money = $discount_money;  //总优惠的钱
        foreach ($buy_pro as $key => $value) {
            $pid = $value[0];
            $type_id = $value[1];
            $num = $value[2];
            $pro = Db::name('wd_xcx_products') ->where([
                    'id' => $pid,
                    'is_sale' => 0
                ]) ->find();
            if($pro){
                $pro_discount = $this->getProDiscounts(0,$uniacid, $suid, $pid, 1, $type_id);
                $sub_price = $pro_discount['discount_price'] * $num;
                $calc_price = $calc_price + $sub_price;
                $scoreback = intval($this ->getProScoreBack($uniacid, $suid, $pid, $pro_discount['discount_price']) * $num);
                $score_sent = $score_sent + $scoreback ;

                if($pro['thumb']){
                    $pro['thumb'] = remote($uniacid, $pro['thumb'], 1);
                }
                //组装子订单数据
                $pro_item_data = [
                    'uniacid' => $uniacid,
                    'suid' => $suid,
                    'source' => $source,
                    'order_id' => $order_id,
                    'order_item_id' => $order_id. '-'. sprintf('%04d', rand(0, 9999)),
                    'num' => $num,
                    'pro_id' => $pro['id'],
                    'pro_discounts' => $pro_discount['discounts'],
                    'pro_discounts_price' => $pro_discount['discount_price'],
                    'pro_title' => $pro['title'],
                    'pro_thumb' => $pro['thumb'],
                    'score_send_info' => $scoreback,
                    'creat_time' => time(),
                    'delivery_type' => $delivery_type,
                    'status' => 0,
                    'pro_type_id' => $type_id
                ];

                if($type_id != -1){
                    $type_value = Db::name('wd_xcx_duo_products_type_value') ->where('id', $type_id) ->find();
                    if($num*1 > $type_value['kc']*1){
                        return json_encode(['data' => ['error' => 4, 'msg' => '商品'.$pro['title'].$type_id.'库存不足！']]);
                    }
                    $pro_item_data['pro_price'] = $type_value['price'];
                    $pro_item_data['pro_attr'] = $this->getProTypeValue($type_id);
                    $pro_item_data['pro_discounts_jian_price'] = $type_value['price'] - $pro_discount['discount_price'];
                    $goods_total_price = $goods_total_price + $type_value['price'] * $num;


                }else{
                    if($num*1 > $pro['pro_kc']*1){
                        return json_encode(['data' => ['error' => 4, 'msg' => '商品'.$pro['title'].$type_id.'库存不足！']]);
                    }
                    $pro_item_data['pro_price'] = $pro['price'];
                    $pro_item_data['pro_attr'] = '';
                    $pro_item_data['pro_discounts_jian_price'] = $pro['price'] - $pro_discount['discount_price'];
                    $goods_total_price = $goods_total_price + $pro['price'] * $num;
                }

                if($discount_money){
                    //计算价格比例
                    if($key == (count($buy_pro) - 1)){
                        $pro_item_data['pro_can_refound_price'] = $pro_discount['discount_price'] - round($shen_discount_money / $num, 2);
                        $fx_base_price = $sub_price - $shen_discount_money;
                    }else{
                        $dan_youhui = round(((($pro_discount['discount_price'] * $num) / $total_discount_money ) * $shen_discount_money) / $num, 2);
                        $pro_item_data['pro_can_refound_price'] = round($pro_discount['discount_price'] - $dan_youhui, 2);
                        $fx_base_price = $sub_price - $dan_youhui * $num;
                        $shen_discount_money = $shen_discount_money - $dan_youhui * $num;
                    }

                }else{
                    $pro_item_data['pro_can_refound_price'] = $pro_discount['discount_price'];
                    $fx_base_price = $pro_discount['discount_price'] * $num;
                }

                $order_item_log = [
                    ['time'=>time(), 'log'=>'订单创建']
                ];
                $pro_item_data['order_item_log'] = serialize($order_item_log);


                //分销规则
                $pro_fx = $this->getProFxSet($uniacid, $pid);

                $pro_fx['fx_base_price'] = round($fx_base_price, 2);
                $pro_item_data['pro_fx'] = serialize($pro_fx);
                array_push($order_item, $pro_item_data);

                //查询是否有购物车记录
                $gwc_id = Db::name('wd_xcx_duo_products_gwc') ->where([
                    'uniacid' => $uniacid,
                    'suid' => $suid,
                    'pid' => $type_id,
                    'pvid' => $pid,
                    'flag' => 1
                ]) ->value('id');
                if($gwc_id){
                    array_push($gwc_ids, $gwc_id);
                }

            }else{
                return json_encode(['data' => ['error' => 4, 'msg' => '商品不存或已下架！']]);
            }
        }
        //检验订单价格


        $should_pay = $calc_price + $freight_money - $discount_money;
        if($should_pay < 0){
            $should_pay = 0;
        }
        $should_pay = round($should_pay, 2);
        if($should_pay*100 != $pay_money*100){
            return json_encode(['data' => ['error' => 5, 'msg' => '订单价格不正确']]);
        }

        //订单数据
        $order_data = [
            'uniacid' => $uniacid,
            'suid' => $suid,
            'source' => $source,
            'order_id' => $order_id,
            'pay_money' => $pay_money,
            'discount_money' => $discount_money,
            'freight_money' => $freight_money,
            'creat_time' => time(),
            'score_sent' => $score_sent,
            'score_use' => $score_use,
            'score_money' => $score_money,
            'coupon_id' => $coupon_id,
            'coupon_use' => $coupon_use,
            'user_remark' => input('user_remark'), //用户备注
            'form_id' => input('form_id'),
            'status' => 0,
            'buy_data' => serialize($buy_data),
            'pay_type' => $pay_type,
            'total_num' => $total_num,
            'total_can_tui_money' => 0,
            'delivery_type' => $delivery_type,
            'formlist_id' => input('formlist_id'),
            'from_gwc' => $from_gwc,
            'goods_total_price' => $goods_total_price
        ];

        if($delivery_type == 1){
            $address_id = input('address_id'); //收货地址ID
            $address_info = Db::name('wd_xcx_duo_products_address') ->where('id', $address_id) ->field('name, mobile, address, more_address, postalcode') ->find();
            $order_data['address_info'] = serialize($address_info);
            $order_data['buyer_mobile'] = $address_info['mobile'];
        }else{
            $self_taking_shop_id = input('self_taking_shop_id');
            $self_taking_contact = input('self_taking_contact');
            $self_taking_shop_info = Db::name('wd_xcx_store') ->where('id', $self_taking_shop_id) ->find();
            $self_taking_info = [
                'self_taking_shop_id' => $self_taking_shop_id,
                'self_taking_contact' => $self_taking_contact,
                'self_taking_shop_info' => serialize($self_taking_shop_info),
                'self_taking_time' => input('self_taking_time'),
            ];
            $order_data['self_taking_info'] = serialize($self_taking_info);
            $order_data['buyer_mobile'] = $self_taking_contact;
            //将预留联系电话存进表里
            $taking_contact_info = Db::name('wd_xcx_duo_products_address') ->where([
                'uniacid' => $uniacid,
                'suid' => $suid,
                'is_mo' => 3
            ]) ->find();
            if($taking_contact_info){
                if($taking_contact_info['mobile'] != $self_taking_contact){
                    Db::name('wd_xcx_duo_products_address') ->where('id', $taking_contact_info['id']) ->update(['mobile' => $self_taking_contact]);
                }
            }else{
                Db::name('wd_xcx_duo_products_address') ->insert([
                    'uniacid' => $uniacid,
                    'suid' => $suid,
                    'name' => '',
                    'mobile' => $self_taking_contact,
                    'address' => '',
                    'more_address' => '',
                    'postalcode' => '',
                    'is_mo' => 3,
                    'creattime' => time(),
                    'froms' => 'weixin'
                ]);
            }
        }
        $formlist_val = null;
        if(input('formlist_val')){
            $formlist_val = stripslashes(html_entity_decode(input('formlist_val'))); //表单内容
            $formlist_val = json_decode($formlist_val, TRUE);
            foreach ($formlist_val as $k => $f){
                if($f['name'] == '单行' || $f['name'] == '多行'){
                    $formlist_val[$k]['val'] = $this->filterEmoji($f['val']);
                }
            }
            $order_data['formlist_val'] = serialize($formlist_val);
        }

        //积分流水数据
        if($score_use > 0) {
            $xfscore = array(
                "uniacid" => $uniacid,
                "orderid" => $order_id,
                'suid' => $suid,
                'source' => $source,
                "type" => "del",
                "score" => $score_use,
                "message" => "消费",
                "creattime" => time()
            );
        }
        //下单插入数据
        Db::startTrans();
        try{
            //表单
            if(input('formlist_id')){
                if($formlist_val){
                    $formcon_id = $this->doPageFormval($uniacid, $suid, 0, $formlist_val, 'mainShop', $source, input('formlist_id'));
                    if(!$formcon_id){
                        throw new Exception("表单提交失败-1！");
                    }else{
                        $order_data['formcon_id'] = $formcon_id;
                    }
                }else{
                    throw new Exception("表单没有内容-1！");
                }
            }
            //插入主订单表
            $main_order = Db::name('wd_xcx_main_shop_order') ->insert($order_data);
            if(!$main_order){
                throw new Exception("下单失败-1！");
            }

            //插入所有子订单表
            $item_order = Db::name('wd_xcx_main_shop_order_item') ->insertAll($order_item);
            if(!$item_order){
                throw new Exception("下单失败-2！");
            }

            //修改优惠券状态
            if($coupon_id > 0){
                $cou = Db::name('wd_xcx_coupon_user') ->where('id', $coupon_id) ->update(['flag'=>1, 'utime'=>time()]);
                if(!$cou){
                    throw new Exception("下单失败-3！");
                }
            }

            //更新积分
            if($score_use > 0){
                $score_sql = "update {$this->prefix}wd_xcx_superuser SET score = score - ".$score_use." WHERE id = ".$suid." and score >".$score_use;
                $score_res = Db::query($score_sql);
                $score_rec = Db::name('wd_xcx_score')->insert($xfscore);

            }

            //处理商品库存
            foreach ($buy_pro as $key => $value) {
                $this ->toDealWithInventorySales($value[0], $value[1], $value[2]);
            }

            //处理购物车
            if($from_gwc == 1){
                $del_gwc = Db::name('wd_xcx_duo_products_gwc') ->where('id', 'in', $gwc_ids) ->update(['flag'=>2]);
                if(!$del_gwc){
                    throw new Exception("下单失败-5！");
                }
            }

            Db::commit();

        }catch(\Exception $e){
            Db::rollback();
            return json_encode(['data' => ['error' => 6, 'msg' => $e ->getMessage()]]);
        }

        return json_encode(['data' => ['error' => 0, 'order_id' => $order_id]]);
    }



    /**
     * [getProScoreBack 获取多规格商品的赠送积分]
     * @param  [type] $uniacid [description]
     * @param  [type] $suid     [description]
     * @param  [type] $pid     [description]
     * @return [type]          [description]
     */
    private function getProScoreBack($uniacid, $suid, $pid, $price){
        $scoreback = Db::name('wd_xcx_products') ->where('id', $pid) ->value('scoreback');
        $score_bei = 1;
        if($scoreback){
            //计算积分
            if(strpos($scoreback, "%")){
                $scoreback = floatval(chop($scoreback, "%"));
                $scoretomoney = Db::name("wd_xcx_rechargeconf")->where("uniacid",$uniacid)->find();
                $scoreback = $price * $scoreback / 100;
                $scoreback = floor($scoreback * intval($scoretomoney['score']) / intval($scoretomoney['money']));
            }else{
                $scoreback = intval($scoreback);
            }

            //查询会员等级倍数
            $grade = Db::name('wd_xcx_superuser') ->where('id', $suid) ->value('grade');
            if($grade){
                $score_bei = Db::name('wd_xcx_vipgrade')->where('grade', $grade)->where('uniacid', $uniacid)->where('score_flag', 1)->value('score_bei');
                if($score_bei < 1){
                    $score_bei = 1;
                }
            }

            return $scoreback * $score_bei;

        }else{
            return 0;
        }
    }


    /**
     * [getProFxSet 获取商品的分销规则]
     * @param  [type] $uniacid [description]
     * @param  [type] $pid     [description]
     * @return [type]          [description]
     */
    private function getProFxSet($uniacid, $pid){
        $pro = Db::name('wd_xcx_products') ->where('id', $pid) ->find();
        $fx_gz = Db::name('wd_xcx_fx_gz') ->where('uniacid', $uniacid) ->field('fx_cj, one_bili, two_bili, three_bili') ->find();
        if($pro['fx_uni'] == 2){ //使用系统比例
            $fx = [
                'fx_cj' => $fx_gz['fx_cj'],
                'commission_type' => 1,
                'commission_one' => $fx_gz['one_bili'],
                'commission_two' => $fx_gz['two_bili'],
                'commission_three' => $fx_gz['three_bili'],
            ];
        }else{ //使用独立设置的比例
            $fx = [
                'fx_cj' => $fx_gz['fx_cj'],
                'commission_type' => $pro['commission_type'],    //  1 百分比  2 金额
                'commission_one' => $pro['commission_one'],
                'commission_two' => $pro['commission_two'],
                'commission_three' => $pro['commission_three'],
            ];
        }
        return $fx;
    }


    //多规格商品规格/型号操作
    public function doPageGetProModel(){
        $uniacid = input('uniacid');
        $pid = input('pid');
        $suid = input('suid');
        $result = [];
        $userinfo = Db::name('wd_xcx_superuser')->where("uniacid",$uniacid)->where('id', $suid)->field('grade')->find();
        $products = Db::name('wd_xcx_products')->where("uniacid", $uniacid)->where('id', $pid)->field('discount_status, discount as discount_arr, price, pro_kc, thumb, use_more, is_sale')->find();
        if(!$products){
            $adata['error'] = 2; //商品不存在
            return json_encode($result);
        }
        $products['grade'] = $userinfo['grade'] > 0 ? $userinfo['grade'] : 1;
        $products['discount_status'] = intval($products['discount_status']);
        $products['discount'] = 0;
        if($products['discount_status'] == 2){
            $products['discount_arr'] = unserialize($products['discount_arr']);
            foreach ($products['discount_arr'] as $key => $value) {
                if($userinfo['grade'] == $value['grade']){
                    if(floatval($value['discount']) > 0){
                        $products['discount'] = $value['discount'];
                    }
                }
            }
        }else if($products['discount_status'] == 1){
            $products['discount'] = Db::name('wd_xcx_vipgrade')->where('grade', $userinfo['grade'])->where('uniacid', $uniacid)->where('discount_flag', 1)->field('discount_grade')->find()['discount_grade'];
            $products['discount'] = floatval($products['discount']);
        }
        if($products['use_more'] == 1){ //多规格

            $proarr = Db::name('wd_xcx_duo_products_type_value')->where("pid", $pid)->order("id asc")->select();
            if($proarr){
                $types = $proarr[0]['comment'];
                //构建规格组
                $typesarr = explode(",", $types);
                // 构建规格组json
                $typesjson = [];
                foreach ($typesarr as $key => &$rec) {
                    $str = "type" . ($key + 1);
                    $ziji = Db::name('wd_xcx_duo_products_type_value')->where("pid", $pid)->order("id asc")->field($str)->select();
                    $xarr = array();
                    foreach ($ziji as $key => $res) {
                        array_push($xarr, $res[$str]);
                    }
                    $cdata["val"] = array_unique($xarr);
                    $cdata['ck'] = 0;
                    $typesjson[$rec] = $cdata;
                }
                $adata['grouparr'] = $typesarr;
                $adata['grouparr_val'] = $typesjson;
                $adata['arr_selected'] = $proarr[0]['type1'];
                if($proarr[0]['type2']){
                    $adata['arr_selected'] = $adata['arr_selected'].';'.$proarr[0]['type2'];
                }
                if($proarr[0]['type3']){
                    $adata['arr_selected'] = $adata['arr_selected'].';'.$proarr[0]['type3'];
                }
                $adata['id'] = $proarr[0]['id'];
                $adata['kc'] = $proarr[0]['kc'];
                $adata['use_more'] = 1;
                $adata['price'] = sprintf("%01.2f", $proarr[0]['price']);
                $adata['discount_price'] = sprintf("%01.2f", $proarr[0]['price'] * $products['discount'] * 0.1);
                $adata['thumb'] = '';
                $adata['discount'] = $products['discount'];
                $adata['is_sale'] = $products['is_sale'];

                if($proarr[0]['thumb']){
                    $adata['thumb'] = remote($uniacid, $proarr[0]['thumb'], 1);
                }
                $adata['error'] = 0;
            }else{
                $adata['error'] = 1; //商品规格不存在
            }
        }else{//单规格
            $adata['price'] = sprintf("%01.2f", $products['price']);
            $adata['discount_price'] = sprintf("%01.2f", $products['price'] * $products['discount'] * 0.1);
            $adata['id'] = -1;
            $adata['kc'] = $products['pro_kc'];
            $adata['use_more'] = 2;
            $adata['thumb'] = '';
            $adata['discount'] = $products['discount'];
            $adata['is_sale'] = $products['is_sale'];

            if($products['thumb']){
                $adata['thumb'] = remote($uniacid, $products['thumb'], 1);
            }
            $adata['error'] = 0;

        }
        $result['data'] = $adata;
        return json_encode($result);
    }
    //多规格选择规格查询数据
    public function doPageGuigeInfo()
    {
        $uniacid = input("uniacid");
        $str = input('str');
        $arr = explode("######", $str);
        $id = input('id');
        $where = "";
        foreach ($arr as $key => &$res) {
            $vv = $key + 1;
            $where .= " and type" . $vv . " = " . "'" . $res . "'";
        }
        $proinfo = Db::query("SELECT * FROM {$this->prefix}wd_xcx_duo_products_type_value WHERE pid= " . $id . $where);
        foreach ($proinfo as $key => &$value) {
            if($value['thumb']){
                $value['thumb'] = remote($uniacid, $value['thumb'], 1);
            }
            $value['salenum']=$value['salenum']+$value["vsalenum"];
        }
        $baseinfo = Db::name('wd_xcx_products')->where("id", $proinfo[0]['pid'])->find();
        if($baseinfo['thumb']){
            $baseinfo['thumb'] = remote($uniacid, $baseinfo['thumb'], 1);
        }
        if($baseinfo['shareimg']){
            $baseinfo['shareimg'] = remote($uniacid, $baseinfo['shareimg'], 1);
        }
        $adata['proinfo'] = $proinfo[0];
        $adata['baseinfo'] = $baseinfo;
        $result['data'] = $adata;
        return json_encode($result);
    }
    //加入购物车
    public function dopagegwcadd()
    {
        $uniacid = input("uniacid");
        $suid = input("suid");
        $id = input("id");
        $pid = input('pid');
        $prokc = input("prokc");
        $proinfo = Db::name('wd_xcx_duo_products_type_value')->where("id", $id)->find();
        $baseinfo = Db::name('wd_xcx_products')->where("id", $pid)->find();
        //判断该商品是不是已经存在
        if($baseinfo['use_more'] == 1){
            $where['pid'] = $id;
            $where['pvid'] = $pid;
        }else{
            $where['pvid'] = $pid;
            $where['pid'] = -1;
        }
        $gwcinfo = Db::name('wd_xcx_duo_products_gwc')->where("suid", $suid)->where('flag', 1)->where($where)->find();

        if ($gwcinfo) {
            $kc = $gwcinfo['num'];
            $newkc = $kc + $prokc;
            $data = array(
                "num" => $newkc,
                "creattime" => time()
            );
            $res = Db::name('wd_xcx_duo_products_gwc')->where("id", $gwcinfo['id'])->update($data);
        } else {
            $data = array(
                "uniacid" => $uniacid,
                "suid" => $suid,
                "pvid" => $pid,   // 商品ID
                "num" => $prokc,
                "creattime" => time()
            );
            if($baseinfo['use_more'] == 1){
                $data['pid'] = $id;
            }else{
                $data['pid'] = -1;
            }
            $res = Db::name('wd_xcx_duo_products_gwc')->insert($data);
        }
        // 统计购物车里面的情况
        if ($res) {
            return json_encode(1);
        }
    }
    //检查会员卡设置
    public function doPagecheckvip()
    {
        $uniacid = input('uniacid');
        $suid = input('suid');
        $kwd = input('kwd');
        $id = input('id');

        $userinfo = Db::name('wd_xcx_superuser')->where('uniacid', $uniacid)->where('id', $suid)->field("vipid,grade")->find();
        $vipid = $userinfo['vipid'];
        $grade = $userinfo['grade'];

        if($kwd == 'bargain'){
            $proinfo = Db::name('wd_xcx_bargain_pro')->where('uniacid', $uniacid)->where('id', $id)->field('vipconfig')->find();
        }else if($kwd == 'pt'){
            $proinfo = Db::name('wd_xcx_pt_pro')->where('uniacid', $uniacid)->where('id', $id)->field('vipconfig')->find();
        }else{
            $proinfo = Db::name('wd_xcx_products')->where('uniacid', $uniacid)->where('id', $id)->field('vipconfig')->find();
        }

        if(!empty($proinfo)){
            $vipconfig = unserialize($proinfo['vipconfig']);

        }else{
            $vipconfig = [];
        }

        $gz = input('gz'); //新规则会员等级购买20190308


        if(empty($vipid)){
            if($vipconfig && isset($vipconfig['set1']) && $vipconfig['set1'] == 1){
                if($vipconfig['set2'] == 1){
                    if($gz == 1){
                        $result['needgrade'] = isset($vipconfig['set3'])?intval($vipconfig['set3']):0;
                        $result['vipname'] = Db::name('wd_xcx_vipgrade')->where('uniacid', $uniacid)->where('grade', $result['needgrade'])->field('name')->find()['name'];
                        $result['grade'] = $grade;
                    }else{
                        $result = false;
                    }
                    $res['data'] = $result;
                    return json_encode($res);
                }else{
                    $result = true;
                    $res['data'] = $result;
                    return json_encode($res);
                }
            }
            else{
                $needvip = Db::name('wd_xcx_vip_config')->where('uniacid', $uniacid)->field($kwd)->find()[$kwd];
                //不是会员  会员可进
                if($needvip==1){
                    $result = false;
                    $res['data'] = $result;
                    return json_encode($res);
                }else{
                    $result = true;
                    $res['data'] = $result;
                    return json_encode($res);
                }
            }
        }else{
            if($gz == 1){
                if($vipconfig['set1'] == 1){
                    if($vipconfig['set2'] == 1){
                        $result['needgrade'] = isset($vipconfig['set3'])?intval($vipconfig['set3']):0;
                        $result['vipname'] = Db::name('wd_xcx_vipgrade')->where('uniacid', $uniacid)->where('grade', $result['needgrade'])->field('name')->find()['name'];
                        $result['grade'] = $grade;
                    }else{
                        $result = true;
                    }
                    $res['data'] = $result;
                    return json_encode($res);
                }else{
                   $result = true;
                }
            }else{
                $result = true;
            }
            $res['data'] = $result;
            return json_encode($res);
        }
    }
    //多规格数据
    public function dopageduoproducts()
    {
        $uniacid = input("uniacid");
        $id = input("id");
        $suid = input('suid');

        $userinfo = Db::name('wd_xcx_superuser')->where("uniacid",$uniacid)->where('id', $suid)->find();
        $products = Db::name('wd_xcx_products')->where("uniacid", $uniacid)->where('id', $id)->find();

        $hits = $products['hits'] + 1;
        Db::name("wd_xcx_products")->where("uniacid", $uniacid)->where('id', $id)->update(array('hits' => $hits));
        $products = Db::name('wd_xcx_products')->where("uniacid", $uniacid)->where('id', $id)->find();
        $videourl = $products['video'];
        if($videourl != ''){
            if(strpos($videourl,".mp4")!==false || strpos($videourl,".MP4")!==false){
                $products['video'] = $videourl;
            }else{
                include 'videoInfo.php';
                $videoInfo = new videoInfo();
                if(preg_match("/^(http:\/\/|https:\/\/).*$/",$products['video'])){
                    $videodata = $videoInfo->getVideoInfo($products['video']);
                    $products['video'] = $videodata['url'];
                }else{
                    $products['video']='';
                }
            }
        }
        $products['grade'] = $userinfo['grade'];
        $products['vipid'] = $userinfo['vipid'];
        $userinfo['grade'] = $userinfo['grade'] > 0 ? $userinfo['grade'] : 1;
        $products['discount_status'] = intval($products['discount_status']);

        if($products['discount_status'] == 2){
            $products['discount'] = unserialize($products['discount']);
        }else if($products['discount_status'] == 1){
            $products['discount'] = Db::name('wd_xcx_vipgrade')->where('grade', $userinfo['grade'])->where('uniacid', $uniacid)->where('discount_flag', 1)->field('discount_grade')->find()['discount_grade'];
            $products['discount'] = floatval($products['discount']);
        }
        $products['discount_price'] = $this -> getProDiscounts(0, $uniacid, $suid, $id)['discount_price'];
        // 检查该商品有没有收藏过
        $shouc = Db::name('wd_xcx_collect')->where("uniacid", $uniacid)->where('suid', $suid)->where('cid', $id)->where('type', "showProMore")->find();
        if ($shouc) {
            $shouc = 2;
        } else {
            $shouc = 1;
        }
        // if($products['types']==2){
        $products['mark_price'] = $products['market_price'];
        $products['texts'] = $products['product_txt'];
        $products['xsl'] = 0;
        $xn_num = 0;
        if($products['use_more'] == 1){
            $proarr = Db::name('wd_xcx_duo_products_type_value')->where("pid", $id)->order("id asc")->select();
            // 处理库存量
            $kcl = 0;
            foreach ($proarr as $key => &$res) {
                $kcl += $res['kc'];
                $products['xsl'] = $products['xsl'] + $res['vsalenum']+$res['salenum'];
                $xn_num += $res['vsalenum'];
            }
            $products['kc'] = $kcl;

            $types = $proarr[0]['comment'];
            //构建规格组
            $typesarr = explode(",", $types);
            // 构建规格组json
            $typesjson = [];
            foreach ($typesarr as $key => &$rec) {
                $str = "type" . ($key + 1);
                $ziji = Db::name('wd_xcx_duo_products_type_value')->where("pid", $id)->order("id asc")->field($str)->select();
                $xarr = array();
                foreach ($ziji as $key => $res) {
                    array_push($xarr, $res[$str]);
                }
                $cdata["val"] = array_unique($xarr);
                $cdata['ck'] = 0;
                $typesjson[$rec] = $cdata;
            }
            $adata['grouparr'] = $typesarr;
            $adata['grouparr_val'] = $typesjson;
        }else if($products['use_more'] == 2){
            $products['xsl'] = $products['sale_num'] + $products['sale_tnum'];
            $adata['grouparr'] = [];
            $adata['grouparr_val'] = [];
            $xn_num = $products['sale_num'];
        }

        // }
        $products['explains'] = explode(",", $products['labels']);
        if ($products['explains'][0] == "") {
            $products['explains'] = "";
        }

        $imgarr = unserialize($products['text']);
        foreach ($imgarr as $key => &$value) {
            $value = remote($uniacid, $value, 1);
        }
        $products['imgtext'] = $imgarr;
        if($products['shareimg']){
            $products['shareimg'] = remote($uniacid, $products['shareimg'], 1);
        }
        if($products['thumb']){
            $products['thumb'] = remote($uniacid, $products['thumb'], 1);
        }

        //商品评价数量和最新一条
        $source = input('source');
        $detail_evaluate = $this->detail_evaluate($source, $uniacid, $id);
        $products['evaluate_total'] = $detail_evaluate['evaluate_total'];
        $products['evaluate_first'] = $detail_evaluate['evaluate_first'];

        $detail_coupon = $this->detail_coupon($uniacid, $suid, $id);
        if($products['xsl'] == 0){
            $detail_buyuser = [
                [
                    'nickname' => '用户***',
                    'avatar' => ROOT_HOST . STATIC_ROOT . '/image/static/pay_list_person.png'
                ]
            ];
        }else{
            $detail_buyuser = $this->detail_buyuser($uniacid, $id, $xn_num);
        }


        $products['detail_coupon'] = $detail_coupon;
        $products['detail_buyuser'] = $detail_buyuser;


        $adata['products'] = $products;
        // 分销商的关系[1.绑定上下级关系 ]
        //获取该小程序的分销关系绑定规则
        $guiz = Db::name('wd_xcx_fx_gz')->where("uniacid", $uniacid)->field("fx_cj,sxj_gx,uniacid")->find();
        $fxsid = input('fxsid');

        if ($fxsid != 'undefined' && $fxsid != '0'&&$fxsid!=0 && $suid) {
            $fxsinfo = Db::name('wd_xcx_superuser')->where("uniacid", $uniacid)->where("id", $fxsid)->find();
            // 1.先进行上下级关系绑定[判断是不是首次下单]
            if ($guiz['fx_cj'] != 4&&$guiz['sxj_gx'] == 1 && $userinfo['parent_id'] == '0' && $fxsid != '0' && $userinfo['fxs'] != 2 && $fxsinfo['fxs'] == 2) {
                $p_fxs = $fxsinfo['parent_id'];  //分销商的上级
                $p_p_fxs = $fxsinfo['p_parent_id']; //分销商的上上级
                // 判断启用几级分销
                $fx_cj = $guiz['fx_cj'];
                // 分别做判断
                if ($fx_cj == 1) {
                    $uuser = Db::name('wd_xcx_superuser')->where("uniacid", $uniacid)->where("id", $suid)->update(array("parent_id" => $fxsid));
                }
                if ($fx_cj == 2) {
                    $uuser = Db::name('wd_xcx_superuser')->where("uniacid", $uniacid)->where("id", $suid)->update(array("parent_id" => $fxsid, "p_parent_id" => $p_fxs));
                }
                if ($fx_cj == 3) {
                    $uuser = Db::name('wd_xcx_superuser')->where("uniacid", $uniacid)->where("id", $suid)->update(array("parent_id" => $fxsid, "p_parent_id" => $p_fxs, "p_p_parent_id" => $p_p_fxs));
                }
            }
            $adata['guiz'] = Db::name('wd_xcx_fx_gz')->where("uniacid", $uniacid)->field("one_bili,two_bili,three_bili,uniacid")->find();
        } else {
            $fx_cj = $guiz['fx_cj'];
            if ($fx_cj == 1) {
                $adata['guiz']['one_bili'] = Db::name('wd_xcx_fx_gz')->where("uniacid", $uniacid)->field("one_bili")->find()['one_bili'];
                $adata['guiz']['two_bili'] = 0;
                $adata['guiz']['three_bili'] = 0;
            } else if ($fx_cj == 2) {
                $adata['guiz']['one_bili'] = Db::name('wd_xcx_fx_gz')->where("uniacid", $uniacid)->field("one_bili")->find()['one_bili'];
                $adata['guiz']['two_bili'] = Db::name('wd_xcx_fx_gz')->where("uniacid", $uniacid)->field("two_bili")->find()['two_bili'];
                $adata['guiz']['three_bili'] = 0;
            } else if ($fx_cj == 3) {
                $adata['guiz']['one_bili'] = Db::name('wd_xcx_fx_gz')->where("uniacid", $uniacid)->field("one_bili")->find()['one_bili'];
                $adata['guiz']['two_bili'] = Db::name('wd_xcx_fx_gz')->where("uniacid", $uniacid)->field("two_bili")->find()['two_bili'];
                $adata['guiz']['three_bili'] = Db::name('wd_xcx_fx_gz')->where("uniacid", $uniacid)->field("three_bili")->find()['three_bili'];
            } else if ($fx_cj == 4) {
                $adata['guiz']['one_bili'] = 0;
                $adata['guiz']['two_bili'] = 0;
                $adata['guiz']['three_bili'] = 0;
            }
        }
        if (!$guiz) {
            $adata['guiz'] = array(
                "one_bili" => 0,
                "two_bili" => 0,
                "three_bili" => 0
            );
        }
        $adata['vip_config'] = 0;
        if(empty($userinfo['vipid'])){

            if(!empty($products['vipconfig'])){
                $vipconfig = unserialize($products['vipconfig']);

                if($vipconfig['set1'] == 1){
                    $adata['vip_config'] = $vipconfig['set2'];
                }else{
                    $vip_config = Db::name('wd_xcx_vip_config')->where("uniacid", $uniacid)->find();
                    if(!empty($vip_config)){
                        $adata['vip_config'] = $vip_config['duo'];
                    }
                }
            }else{
                $vip_config = Db::name('wd_xcx_vip_config')->where("uniacid", $uniacid)->find();
                if(!empty($vip_config)){
                    $adata['vip_config'] = $vip_config['duo'];
                }
            }
        }
        $adata['shouc'] = $shouc;
        $result['data'] = $adata;
        return json_encode($result);
    }
    public function detail_buyuser($uniacid, $pid, $xn_num){
        $list = Db::name('wd_xcx_main_shop_order_item')->where('uniacid', $uniacid)->where('pro_id', $pid)->field('suid, source')->order('id desc') ->limit(6)->select();
        $buyuser = [];
        if(count($list)>=6){
            foreach ($list as $k => &$v) {
                $info = $this->getnameandavatar($v['source'], $uniacid, $v['suid']);
                $buyuser[$k]['nickname'] = $info['nickname'];
                $buyuser[$k]['avatar'] = $info['avatar'];
            }
        }else{
            $num = count($list) + $xn_num;
            foreach ($list as $k => &$v) {
                $info = $this->getnameandavatar($v['source'], $uniacid, $v['suid']);
                $buyuser[$k]['nickname'] = $info['nickname'];
                $buyuser[$k]['avatar'] = $info['avatar'];
            }
            if($num <= 6){
                $num = $xn_num;
            }else{
                $num = 6 - count($list);
            }

            for($i=0; $i<$num; $i++){
                $temp = [
                    'nickname' => '用户***',
                    'avatar' => ROOT_HOST . STATIC_ROOT . '/image/static/avatar'.$i.'.png'
                ];
                array_push($buyuser, $temp);
            }

        }

        return $buyuser;
    }
    public function detail_coupon($uniacid, $suid, $pid){
        $pro_cate = Db::name('wd_xcx_products')->alias('a')->join('wd_xcx_cate_pro b','a.id = b.pid')->join('wd_xcx_cate c', 'b.cate_id = c.id')->where('a.uniacid', $uniacid)->where('a.id', $pid)->where('c.statue', 1)->field('b.cate_id')->select();//得到商品所属栏目的id
        $pro_cate_ids = array_column($pro_cate, 'cate_id');

        $detail_coupon = []; //优惠券组


        //商品可领优惠券
        $coupon_all = Db::name('wd_xcx_coupon')->where("uniacid", $uniacid)->where('flag', 1)->where('give_type', '<>', 1)->order('num desc, id desc')->select(); //得到所有自助领取并且上架优惠券
        foreach ($coupon_all as $k => $v) { //得到未过期优惠券
            $use_contents = unserialize($v['use_contents']);
            if($use_contents['use_type'] == 0){
                $use_time = explode(',', $use_contents['use_time']);
                if($use_time[1] != 0 && $use_time[1]< time()){
                    unset($coupon_all[$k]);
                    continue;
                }else{
                    $use_time[0] = $use_time[0] > 0 ? date("Y-m-d", $use_time[0]) : 0;
                    $use_time[1] = $use_time[1] > 0 ? date("Y-m-d", $use_time[1]) : 0;
                    $coupon_all[$k]['btime'] = $use_time[0];
                    $coupon_all[$k]['etime'] = $use_time[1];
                    $use_contents['use_time'] = $use_time;
                }
            }
            $coupon_all[$k]['use_contents'] = $use_contents;
            $use_goods_contents = unserialize($v['use_goods_contents']);
            if($use_goods_contents['type'] == 1){
                $contents = $use_goods_contents['contents'];
                if(strstr($contents, 'a') === false){//不存在
                    unset($coupon_all[$k]);
                    continue;
                }else{
                    $contents_arr = explode(',', $contents);
                    $sub_arr = []; //当前优惠券的二级栏目组
                    foreach ($contents_arr as $ks => $vs) {
                        if(strstr($vs, 'a') !== false){//不存在
                            $vs = substr($vs, 1);
                            $is_top = Db::name('wd_xcx_cate')->where('cid', 0)->where('id', $vs)->where('statue', 1)->find();
                            if($is_top){
                                $sub = Db::name('wd_xcx_cate')->where('cid', $vs)->where('statue', 1)->select();
                                if($sub){
                                    $sub = array_column($sub, 'id');
                                    $sub_arr = array_merge($sub_arr, $sub);
                                }
                            }else{
                                $is_sub = Db::name('wd_xcx_cate')->where('id', $vs)->where('statue', 1)->find();
                                if($is_sub){
                                    array_push($sub_arr, $vs);
                                }

                            }
                        }
                    }
                    if(count(array_intersect($sub_arr, $pro_cate_ids)) > 0){ //$sub_arr 优惠券可领栏目id，$pro_cate_ids 商品所属栏目id
                        // $coupon_all[$k]['use_goods_contents'] = $use_goods_contents;
                        $is_get = 1;
                        $my_get = Db::name('wd_xcx_coupon_user')->where("uniacid", $uniacid)->where("cid", $v['id'])->where("suid", $suid)->count();
                        if ($v['xz_count'] == 0) {
                            $coupon_all[$k]['nowCount'] = "无限";
                        } else {
                            $coupon_all[$k]['nowCount'] = intval($v['xz_count']) - intval($my_get);
                        }
                        if ($k['xz_count'] > 0 && $my_get >= $k['xz_count']) {
                            $is_get = 0;
                        }
                        $coupon_all[$k]['is_get'] = $is_get;
                        $detail_coupon[] = $coupon_all[$k];
                    }
                }
            }else{
                // $coupon_all[$k]['use_goods_contents'] = $use_goods_contents;
                $is_get = 1;
                $my_get = Db::name('wd_xcx_coupon_user')->where("uniacid", $uniacid)->where("cid", $v['id'])->where("suid", $suid)->count();
                if ($v['xz_count'] == 0) {
                    $coupon_all[$k]['nowCount'] = "无限";
                } else {
                    $coupon_all[$k]['nowCount'] = intval($v['xz_count']) - intval($my_get);
                }
                if ($k['xz_count'] > 0 && $my_get >= $k['xz_count']) {
                    $is_get = 0;
                }
                $coupon_all[$k]['is_get'] = $is_get;
                $detail_coupon[] = $coupon_all[$k];
            }
        }
        return $detail_coupon;
    }
    public function detail_evaluate($source, $uniacid, $pid){
        $evaluate_total = Db::name('wd_xcx_evaluate')->where("uniacid", $uniacid)->where('pid', $pid)->count();
        $evaluate_first = Db::name('wd_xcx_evaluate')->where("uniacid", $uniacid)->where('pid', $pid)->field("id, suid, content, creattime, imgs")->order('id desc')->find();
        if($evaluate_first){
            $evaluate_first['imgs'] = unserialize($evaluate_first['imgs']);
            $evaluate_first['creattime'] = date('Y-m-d', strtotime($evaluate_first['creattime']));
            $info = $this -> getnameandavatar($source, $uniacid, $evaluate_first['suid']);
            $evaluate_first['nickname'] = $info['nickname'];
            $evaluate_first['avatar'] = $info['avatar'];
        }else{
            $evaluate_first = [];
        }
        $data = [
            'evaluate_total' => $evaluate_total,
            'evaluate_first' => $evaluate_first
        ];
        return $data;
    }



    /**
     * 我的订单列表
     */
    public function doPageGetMyOrders(){
        $uniacid = input('uniacid');
        $suid = input('suid');
        $flag = input('flag') ? input('flag') : 0;
        $page = input('page') ? input('page') : 1;

        $size = 10;
        $begin = ($page - 1) * $size ;

        $this ->checkOrderPayStatus($uniacid);  //检查过期未支付订单
        $this ->checkDeliverQuery($uniacid); //处理自动收货
        $this ->checkSupportEndQuery($uniacid);  //处理订单售后结束后返佣
        $where = [];
        if($flag == 0){  //全部订单
            $where = [];
        }elseif($flag == 1){  //未付款订单
            $where = ['status' => 0];
        }elseif($flag == 2){  //待发货
            $where = ['status' => 1];
        }elseif($flag == 3){  //待收货 待核销
            $where = ['status' => 2];
        }elseif($flag == 4){   //待评价 全部收货 核销
            $where = ['status' => 3];
        }
        $orders = Db::name('wd_xcx_main_shop_order') ->where([
            'uniacid' => $uniacid,
            'suid' => $suid,
            'is_delete' => 0
        ])  ->where($where)
            ->order('id desc')
            ->limit($begin, $size)
            ->select();
        if($orders){
            //售后时间
            $support_time = Db::name('wd_xcx_duo_products_yunfei') ->where('uniacid', $uniacid) ->value('support_time');
            if(!$support_time){
                $support_time = 15;
            }
            $support_time = $support_time * 24 * 3600;
            foreach ($orders as $k => $value){
                $order_items = Db::name('wd_xcx_main_shop_order_item') ->where([
                    'uniacid' => $uniacid,
                    'suid' => $suid,
                    'order_id' => $value['order_id']
                ]) ->select();
                if($value['delivery_type'] == 1 && !$orders[$k]['express']){
                    if(count($order_items) == 1){
                        if($order_items[0]['express'] != ''){
                            $orders[$k]['express'] = $order_items[0]['express'];
                            $orders[$k]['express_no'] = $order_items[0]['express_no'];
                        }
                    }else{
                        $is_ps = 1; //商家配送
                        foreach($order_items as $ks => $vs){
                            if($vs['express'] != -1){
                                $is_ps = 2;
                                break;
                            }
                        }
                        if($is_ps == 1){
                            $orders[$k]['express'] = -1;
                            $orders[$k]['express_no'] = $order_items[0]['express_no'];
                        }
                    }
                }
                $orders[$k]['order_items'] = $order_items;

                $over_time = ($value['check_time'] + $support_time) - time();
                if($over_time > 0){
                    $orders[$k]['is_in_supp'] = true;
                }else{
                    $orders[$k]['is_in_supp'] = false;
                }
            }
        }

        $money = Db::name('wd_xcx_superuser') ->where('id', $suid) ->value('money');
        $money = $money ? $money : 0;
        $money = number_format($money, 2);

        return json_encode(['data' => ['orders' => $orders, 'money' => $money]]);

    }

    /**
     * 处理未支付订单 半小时设为过期
     */
    private function checkOrderPayStatus($uniacid){
        $time = time() - 1800;
        $orders = Db::name('wd_xcx_main_shop_order')
                    ->where('uniacid', $uniacid)
                    ->where('status', 0)
                    ->where('creat_time', 'LT', $time)
                    ->select();
        foreach ($orders as $k => $value){
            //查询积分 返回
            if($value['score_use'] > 0){
                Db::name('wd_xcx_superuser') ->where('id', $value['suid']) ->setInc('score', $value['score_use']);
            }

            //处理优惠券
            if($value['coupon_id']> 0){
                Db::name('wd_xcx_coupon_user') ->where('id', $value['coupon_id']) ->update(['flag' => 0]);
            }

            //处理销量与库存
            $buy_data = unserialize($value['buy_data']);
            foreach ($buy_data as $k => $v){
                $v = explode('|', $v);
                $pid = $v[0];
                $type_id = $v[1];
                $num = $v[2];
                if($type_id != -1){ //多规格
                    $sql = "update {$this->prefix}wd_xcx_food_type_value SET kc = kc + ".$num.", salenum = salenum - ".$num." WHERE id = ".$type_id." and salenum >=".$num;
                    Db::query($sql);
                    $sql_pro = "update {$this->prefix}wd_xcx_products SET sale_tnum = sale_tnum - ".$num." WHERE id = ".$pid ." and sale_tnum >=".$num;
                    Db::query($sql_pro);
                }else{
                    $sql_pro = "update {$this->prefix}wd_xcx_products SET sale_tnum = sale_tnum - ".$num.", pro_kc = pro_kc + " . $num . " WHERE id = ".$pid ." and sale_tnum >= ".$num;
                    Db::query($sql_pro);
                }
            }

            //处理子订单状态 日志
            $order_items = Db::name('wd_xcx_main_shop_order_item') ->where('order_id', $value['order_id']) ->select();
            foreach ($order_items as $k_i => $v_i){
                $order_item_logs = unserialize($v_i['order_item_log']);
                $order_item_log = ['time'=>time(), 'log'=>'订单未支付，已过期'];
                array_push($order_item_logs, $order_item_log);
                Db::name('wd_xcx_main_shop_order_item')
                    ->where('order_id', $value['order_id'])
                    ->update(['status'=>-1, 'order_item_log'=>serialize($order_item_logs)]);
            }

            //改变订单状态
            Db::name('wd_xcx_main_shop_order') ->where('order_id', $value['order_id']) ->update(['status'=>-1]);  //未支付过期
        }
    }

    /**
     * 处理自动收货订单
     */
    private function checkDeliverQuery($uniacid){
        $Deliver_day = Db::name('wd_xcx_duo_products_yunfei') ->where('uniacid', $uniacid) ->value('receiving');
        $Deliver_day = $Deliver_day ? $Deliver_day : 15;
        if($Deliver_day){
            $time_diff = time() - $Deliver_day * 24 * 3600;

            $clorders = Db::name('wd_xcx_main_shop_order')->where('uniacid',$uniacid)->where('status', 2)->where('delivery_type',1)->where('deliver_time', 'LT', $time_diff)->select();
            foreach ($clorders as $key => $res) {
                $order_items = Db::name('wd_xcx_main_shop_order_item') ->where('order_id', $res['order_id']) ->where('status', 'gt', 0) ->select(); //自动收货子订单处理
                $services = Db::name('wd_xcx_main_shop_order_service')->where("uniacid", $uniacid)->where("order_item_id", "like", "%".$res['order_id']."%")->where("status", "in", [0, 1] )->where("refund_time", 0)->select(); //查出进行中和同意中未退货的订单
                foreach ($services as $k => $v) {
                    $service_data = [
                        'status' => -1,
                        'revoke_time' => time(),
                    ];
                    Db::name('wd_xcx_main_shop_order_service')->where("uniacid", $uniacid)->where("order_service_id", $v['order_service_id'])->update($service_data);
                }

                //处理子订单状态
                foreach ($order_items as $item){
                    $order_item_logs = unserialize($item['order_item_log']);
                    $order_item_log = ['time'=>time(), 'log'=>'订单确认收货'];
                    array_push($order_item_logs, $order_item_log);
                    Db::name('wd_xcx_main_shop_order_item') ->where('id', $item['id']) ->update(['status'=>3, 'received_time' => time(), 'order_item_log' => serialize($order_item_logs)]);
                }

                $adata = array(
                    "check_time" => time(),
                    "status" => 3
                );
                Db::name('wd_xcx_main_shop_order')->where('id',$res['id'])->update($adata);
            }
        }
    }

    /**
     * 处理售后到期 分销返佣
     */
    private function checkSupportEndQuery($uniacid){
        $support = Db::name('wd_xcx_duo_products_yunfei')  ->where('uniacid', $uniacid) ->value('support_time');
        $support = $support ? $support : 15;
        $support = $support * 24 * 3600;
        $time = time() - $support;

        $orders = Db::name('wd_xcx_main_shop_order') ->where([
            'uniacid' => $uniacid,
            'check_time' => ['between time', [100,$time]],
            'is_fanxian' => 0
        ]) ->select();
        if(count($orders)>0){
            foreach ($orders as $order){
                $this ->dopagegivemoney($uniacid, $order['suid'], $order['order_id']);
            }
        }
    }


    /**
     * 订单页面获取门店列表
     */
    public function getTakeSelfShopList(){
        $uniacid = input('uniacid');
        $type = input('type');
        if($type == 'mainShop'){
            $store_set = Db::name('wd_xcx_duo_products_yunfei') ->where('uniacid', $uniacid) ->find();
            if($store_set){
                if($store_set['take_self'] == 2){
                    $store_ids = $store_set['stores'];
                    if($store_ids){
                        $store_ids = explode(',', $store_ids);
                        $shop_list = [];
                        foreach ($store_ids as $v){
                            $shop = Db::name('wd_xcx_store') ->where('id', $v) ->find();
                            if($shop){
                                array_push($shop_list, $shop);
                            }
                        }
                        return json_encode(['data' => ['error' => 0], 'shop_list'=>$shop_list]);
                    }else{
                        return json_encode(['data' => ['error' => 3, 'msg' => '未设置自取门店']]);
                    }
                }else{
                    return json_encode(['data' => ['error' => 2, 'msg' => '自取未开启']]);
                }
            }else{
                return json_encode(['data' => ['error' => 1, 'msg' => '未设置']]);
            }
        }else if($type == 'flashSale'){
            $id = input('gid');
            $pro = Db::name('wd_xcx_products')->where('id', $id)->where('uniacid', $uniacid)->find();
            if ($pro['stores']) {
                $stores = explode(',', $pro['stores']);
                $shop_list = Db::name('wd_xcx_store')->where("uniacid", $uniacid)->where('id', 'in', $stores)->select();
                return json_encode(['data' => ['error' => 0], 'shop_list'=>$shop_list]);
            } else {
                $baseinfo = Db::name('wd_xcx_base')->where('uniacid', $uniacid)->field('name, tel, address')->find();
                if($baseinfo){
                    $shop_list[] = ['id' => 0, 'title' => $baseinfo['name'], 'tel' => $baseinfo['tel'], 'province' => '', 'city' => '', 'country' => $baseinfo['address'], 'times' => ''];
                    return json_encode(['data' => ['error' => 0], 'shop_list'=>$shop_list]);
                }else{
                    return json_encode(['data' => ['error' => 3, 'msg' => '未设置自取门店']]);
                }
            }
        }else if($type == 'pt'){
            $id = input('gid');
            $pro = Db::name('wd_xcx_pt_pro')->where('id', $id)->where('uniacid', $uniacid)->find();
            if ($pro['stores']) {
                $stores = explode(',', $pro['stores']);
                $shop_list = Db::name('wd_xcx_store')->where("uniacid", $uniacid)->where('id', 'in', $stores)->select();
                return json_encode(['data' => ['error' => 0], 'shop_list'=>$shop_list]);
            } else {
                $baseinfo = Db::name('wd_xcx_base')->where('uniacid', $uniacid)->field('name, tel, address')->find();
                if($baseinfo){
                    $shop_list[] = ['id' => 0, 'title' => $baseinfo['name'], 'tel' => $baseinfo['tel'], 'province' => '', 'city' => '', 'country' => $baseinfo['address'], 'times' => ''];
                    return json_encode(['data' => ['error' => 0], 'shop_list'=>$shop_list]);
                }else{
                    return json_encode(['data' => ['error' => 3, 'msg' => '未设置自取门店']]);
                }
            }
        }else if($type == 'bargain'){
            $id = input('gid');
            $pro = Db::name('wd_xcx_bargain_pro')->where('id', $id)->where('uniacid', $uniacid)->find();
            if ($pro['stores']) {
                $stores = explode(',', $pro['stores']);
                $shop_list = Db::name('wd_xcx_store')->where("uniacid", $uniacid)->where('id', 'in', $stores)->select();
                return json_encode(['data' => ['error' => 0], 'shop_list'=>$shop_list]);
            } else {
                $baseinfo = Db::name('wd_xcx_base')->where('uniacid', $uniacid)->field('name, tel, address')->find();
                if($baseinfo){
                    $shop_list[] = ['id' => 0, 'title' => $baseinfo['name'], 'tel' => $baseinfo['tel'], 'province' => '', 'city' => '', 'country' => $baseinfo['address'], 'times' => ''];
                    return json_encode(['data' => ['error' => 0], 'shop_list'=>$shop_list]);
                }else{
                    return json_encode(['data' => ['error' => 3, 'msg' => '未设置自取门店']]);
                }
            }
        }
    }

    /**
     * 订单搜索
     */
    public function doPageOrderSearch(){
        $uniacid = input('uniacid');
        $suid = input('suid');
        $key = input('key');
        $flag = input('flag') ? input('flag') : 0;
        $where = [];
        if($flag == 0){  //全部订单
            $where = [];
        }elseif($flag == 1){  //未付款订单
            $where = ['status' => 0];
        }elseif($flag == 2){  //待发货
            $where = ['status' => 1];
        }elseif($flag == 3){  //待收货 待核销
            $where = ['status' => 2];
        }elseif($flag == 4){   //待评价 全部收货 核销
            $where = ['status' => 3];
        }

        if($key){
            $orders = [];
            $order_items = OrderItem::where([
                'uniacid' => $uniacid,
                'suid' => $suid,
                'pro_title' => ['like', '%'.$key.'%'],
            ]) ->where($where) ->column('order_id');
            $order_items = array_unique($order_items);
            if(count($order_items) > 0){
                foreach ($order_items as $item){
                    $order = Order::where('order_id', $item) ->where('is_delete', 0) ->with('orderItems') ->find();
                    if($order){
                        $order = $order->toArray();
                        array_push($orders, $order);
                    }
                }
                $tag = [];
                foreach ($orders as $order){
                    $tag[] = $order['id'];
                }
                array_multisort($tag, SORT_DESC, $orders);

                return json_encode(['data' => ['error' => 0, 'orders'=>$orders]]);
            }else{
                return json_encode(['data' => ['error' => 0, 'orders'=>[]]]);
            }

        }else{
            return json_encode(['data' => ['error' => 0, 'orders'=>[]]]);
        }
    }

    /**
     * 用户取消总订单 未支付
     */
    public function doPageCancelOrderNoPay(){
        $uniacid = input('uniacid');
        $suid = input('suid');
        $order_id = input('order_id');

        if($order_id){
            $order = Order::where([
                'uniacid' => $uniacid,
                'suid' => $suid,
                'order_id' => $order_id,
                'status' => 0
            ]) ->with('orderItems') ->find();

            //积分流水数据
            if($order->score_use > 0) {
                $xfscore = array(
                    "uniacid" => $uniacid,
                    "orderid" => $order_id,
                    'suid' => $suid,
                    'source' => $order->source,
                    "type" => "add",
                    "score" => $order->score_use,
                    "message" => "订单取消退回积分",
                    "creattime" => time()
                );
            }

            if($order){
                Db::startTrans();
                try{
                    //处理积分
                    if($order->score_use > 0){
                        $user_update = Db::name('wd_xcx_superuser') ->where('id', $suid) ->setInc('score', $order->score_use);
                        $back_score = Db::name('wd_xcx_score') ->insert($xfscore);
                        if(!$user_update || !$back_score){
                            throw new \Exception('积分返还失败！');
                        }
                    }

                    //处理优惠券
                    if($order->coupon_id > 0){
                        $cou_update = Db::name('wd_xcx_coupon_user') ->where('id', $order->coupon_id) ->update([
                            'flag' => 0,
                            'utime' => 0
                        ]);
                        if(!$cou_update){
                            throw new \Exception('优惠券返还失败！');
                        }
                    }

                    //改变订单状态
                    $order_update = Order::where('order_id', $order_id) ->update([
                        'status' => -2,
                        'cancel_time' => time()
                    ]);
                    if(!$order_update){
                        throw new \Exception('订单状态改变失败！');
                    }

                    //改变子订单状态
                    foreach ($order->orderItems as $item){
                        $order_item_logs = unserialize($item->order_item_log);
                        $order_item_log = ['time'=>time(), 'log'=>'订单未支付，取消订单'];
                        array_push($order_item_logs, $order_item_log);
                        $update_item = Db::name('wd_xcx_main_shop_order_item') ->where('id', $item->id) ->update([
                            'status' => -2,
                            'cancel_time' => time(),
                            'order_item_log' => serialize($order_item_logs)
                        ]);
                        if(!$update_item){
                            throw new \Exception('子订单状态更新失败！');
                        }
                        //处理库存
                        $this ->toDealWithInventorySales($item->pro_id, $item->pro_type_id, $item->num, 2);
                    }
                    Db::commit();

                }catch(\Exception $e){
                    Db::rollback();
                    return json_encode(['data' => ['error' => 3, 'msg' => $e ->getMessage()]]);
                }

                return json_encode(['data' => ['error' => 0]]);


            }else{
                return json_encode(['data' => ['error' => 2, 'msg' => '订单不存在！']]);
            }
        }else{
            return json_encode(['data' => ['error' => 1, 'msg' => '缺少请求信息！']]);
        }
    }


    /**
     * 用户取消总订单 已付款
     */
    public function cancelPaymentOrder(){
        $uniacid = input('uniacid');
        $suid = input('suid');
        $source = input('source');
        $order_id = input('order_id');
        $remark = input('remark');
        $type = input('type');

        if($order_id){
            $order = Order::where([
                'uniacid' => $uniacid,
                'suid' => $suid,
                'order_id' => $order_id,
                'status' => ['in', [1,2]]
            ])->with('orderItems') ->find();
            if($order){
                $order = $order->toArray();
                $order_service_id = 's'.date('YmdHi', time()).substr(microtime(), 2, 4).rand(1000,9999);

                Db::name('wd_xcx_main_shop_order') ->where('order_id', $order_id) ->update(['allow_all_refund' => 2, 'order_service_id' => $order_service_id, 'has_service' => 1]);

                //创建售后订单

                $order_service_data = [
                    'uniacid' => $uniacid,
                    'suid' => $suid,
                    'source' => $source,
                    'order_service_id' => $order_service_id,
                    'order_item_id' => $order_id,
                    'num' => $order['total_num'],
                    'apply_remark' => $remark,
                    'apply_type' => $type,
                    'status' => 0,
                    'creat_time' => time(),
                    'is_item' => 2
                ];
                if($order['is_change_price'] == 1){
                    $order_service_data['refund_money'] = $order['change_price'];
                }else{
                    $order_service_data['refund_money'] = $order['pay_money'];
                }
                foreach ($order['order_items'] as $item){
                    //改变子订单状态
                    $order_item_logs = unserialize($item['order_item_log']);
                    $order_item_log = ['time'=>time(), 'log'=>'订单已支付，申请退款'];
                    array_push($order_item_logs, $order_item_log);
                    Db::name('wd_xcx_main_shop_order_item') ->where('order_id', $order['order_id']) ->update([
                        'status' => 4,
                        'cancel_time' => time(),
                        'order_service_id' => $order_service_id,
                        'order_item_log' => serialize($order_item_logs)
                    ]);
                    $order_service_data['apply_status'] = $item['status'];
                }
                Db::name('wd_xcx_main_shop_order_service') ->insert($order_service_data);
                return json_encode(['data' => ['error' => 0, 'order_service' => $order_service_data]]);

            }else{
                return json_encode(['data' => ['error' => 1, 'msg' => '订单不存在！']]);
            }
        }else{
            return json_encode(['data' => ['error' => 1, 'msg' => '缺少请求信息！']]);
        }

    }

    /**
     * 用户删除订单
     */
    public function deleteOrder(){
        $uniacid = input('uniacid');
        $suid = input('suid');
        $order_id = input('order_id');

        $res = Db::name('wd_xcx_main_shop_order') ->where([
            'uniacid' => $uniacid,
            'suid' => $suid,
            'order_id' => $order_id,
            'is_delete' => 0,
            'status' => ['in', [-3,-2,-1,5]]
        ]) ->update(['is_delete'=>1]);
        if($res){
            return json_encode(['data' => ['error' => 0]]);
        }else{
            return json_encode(['data' => ['error' => 1, 'msg' => '删除失败！']]);
        }
    }



    /**
     * @param $pro_id
     * @param $type_id
     * @param $num
     * @param int $do
     * @throws Exception
     */
    private function toDealWithInventorySales($pro_id, $type_id, $num, $do=1){
        if($do == 1){  //卖出  减库存 加销量
            if($type_id != -1){
                Db::name('wd_xcx_duo_products_type_value') ->where([
                    'id' => $type_id,
                    'kc' => ['EGT', $num],
                ]) ->setDec('kc', $num);  //减规格值库存
                Db::name('wd_xcx_duo_products_type_value') ->where([
                    'id' => $type_id,
                ]) ->setInc('salenum', $num);  //加规格值销量
                Db::name('wd_xcx_products') ->where([
                    'id' => $pro_id,
                ]) ->setInc('sale_tnum', $num);  //加商品的真实销量
            }else{
                Db::name('wd_xcx_products') ->where([
                    'id' => $pro_id,
                    'pro_kc' => ['EGT', $num],
                ]) ->setDec('pro_kc', $num);  //减商品的真实库存
                Db::name('wd_xcx_products') ->where('id', $pro_id) ->setInc('sale_tnum', $num); //加商品真实销量
            }
        }else{   // 取消订单  加库存 减销量
            if($type_id != -1){
                Db::name('wd_xcx_duo_products_type_value') ->where([
                    'id' => $type_id,
                    'salenum' => ['EGT', $num],
                ]) ->setDec('salenum', $num);  //减规格值销量
                Db::name('wd_xcx_duo_products_type_value') ->where([
                    'id' => $type_id,
                ]) ->setInc('kc', $num);  //加规格值库存
                Db::name('wd_xcx_products') ->where([
                    'id' => $pro_id,
                    'sale_tnum' => ['EGT', $num],
                ]) ->setDec('sale_tnum', $num);  //减商品的真实销量
            }else{
                Db::name('wd_xcx_products') ->where([
                    'id' => $pro_id,
                    'sale_tnum' => ['EGT', $num],
                ]) ->setDec('sale_tnum', $num);  //减商品的真实销量
                Db::name('wd_xcx_products') ->where('id', $pro_id) ->setInc('pro_kc', $num);
            }
        }
    }



    /**
     * 个人中心获取订单状态数量
     */
    public function getMainOrderStatus(){
        $uniacid = input('uniacid');
        $suid = input('suid');

        //待付款
        $not_payment = Db::name('wd_xcx_main_shop_order') ->where([
            'uniacid' => $uniacid,
            'suid' => $suid,
            'status' => 0
        ]) ->count();

        //待发货
        $not_delivery = Db::name('wd_xcx_main_shop_order') ->where([
            'uniacid' => $uniacid,
            'suid' => $suid,
            'status' => 1
        ]) ->count();

        //待收货
        $not_receiver = Db::name('wd_xcx_main_shop_order') ->where([
            'uniacid' => $uniacid,
            'suid' => $suid,
            'status' => 2
        ]) ->count();
        //待评价
        $not_evaluate = Db::name('wd_xcx_main_shop_order') ->where([
            'uniacid' => $uniacid,
            'suid' => $suid,
            'status' => 3
        ]) ->count();

        //售后
        $not_after_sale = Db::name('wd_xcx_main_shop_order_service') ->where([
            'uniacid' => $uniacid,
            'suid' => $suid,
            'refund_time' => 0
        ])  ->where(function ($query){
            $query ->where([
                'status' => 0
            ]) ->whereOr([
                'status' => 1,
            ]);
        }) ->count();
        $order_types = [
            'not_payment' => $not_payment,
            'not_delivery' => $not_delivery,
            'not_receiver' => $not_receiver,
            'not_evaluate' => $not_evaluate,
            'not_after_sale' => $not_after_sale
        ];
        return json_encode(['data' => ['error' => 0, 'order_types' => $order_types]]);

    }

    //多规格确认收货
    public function dopageqrshouh(){
        $uniacid = input("uniacid");
        $suid = input("suid");
        $order_id = input("order_id");
        $adata = array(
            "status" => 3,  //确认收货
            "check_time" => time()
        );


        $order_items = Db::name('wd_xcx_main_shop_order_item') ->where('order_id', $order_id) ->where('status', 'gt', 0) ->select();
        

        // $services = Db::name('wd_xcx_main_shop_order_service')->where("uniacid", $uniacid)->where("order_item_id", "like", "%".$order_id."%")->where("status=0 OR (status = 1 and refund_time = 0)")->select(); //查出进行中和同意中的订单
        $services = Db::name('wd_xcx_main_shop_order_service')->where("uniacid", $uniacid)->where("order_item_id", "like", "%".$order_id."%")->where("status", "in", [0, 1] )->where("refund_time", 0)->select(); //查出进行中和同意中未退货的订单
        foreach ($services as $k => $v) {
            $service_data = [
                'status' => -1,
                'revoke_time' => time(),
            ];
            Db::name('wd_xcx_main_shop_order_service')->where("uniacid", $uniacid)->where("order_service_id", $v['order_service_id'])->update($service_data);
        }
        Db::name('wd_xcx_main_shop_order')->where('order_id', $order_id)->update($adata);


        //处理子订单状态
        foreach ($order_items as $item){
            $order_item_logs = unserialize($item['order_item_log']);
            $order_item_log = ['time'=>time(), 'log'=>'订单确认收货'];
            array_push($order_item_logs, $order_item_log);
            Db::name('wd_xcx_main_shop_order_item') ->where('id', $item['id']) ->update(['status'=>3, 'received_time' => time(), 'order_item_log' => serialize($order_item_logs)]);
        }
        $order = Db::name("wd_xcx_main_shop_order")->where("uniacid",$uniacid)->where("order_id",$order_id)->find();

        add_all_pay($uniacid, $order['total_can_tui_money'], $suid);

        check_vip_grade($uniacid, $suid);

        //购买送积分
        $hasscoreback = $order['score_sent'];
        if($hasscoreback > 0){
            $new_user=Db::name("wd_xcx_superuser")->where("uniacid",$uniacid)->where("id",$order['suid'])->find();
            $new_my_score = $new_user['score'] + $hasscoreback;
            Db::name("wd_xcx_superuser")->where("uniacid",$uniacid)->where("id",$new_user['id'])->update(array("score"=>$new_my_score));
            $scoreback_data = array(
                "uniacid" => $uniacid,
                "orderid" => $order_id,
                "suid" => $new_user['id'],
                "type" => "add",
                "score" => $hasscoreback,
                "message" => "买送积分",
                "creattime" => time()
            );
            Db::name("wd_xcx_score")->insert($scoreback_data);
        }

        if($order['source'] == 1){
            $openid = Db::name("wd_xcx_user")->where("suid", $suid)->value('openid');
            $jsons = [
                'fprice' => $order['pay_money']
            ];
            $jsons = serialize($jsons);
            sendSubscribe($uniacid, 2, $openid, $jsons);
        }
        return json_encode(['data' => 1]);
    }

    /**
     * 订单详情页
     */
    public function mainShopOrderDetails(){
        $uniacid = input('uniacid');
        $suid = input('suid');
        $order_id = input('order_id');

        $order = Order::where([
            'uniacid' => $uniacid,
            'suid' => $suid,
            'order_id' => $order_id
        ]) ->with('orderItems') ->find();

        if($order){
            $order = $order->toArray();

            if($order['delivery_type'] == 1){
                if(!$order['express']){
                    if(count($order['order_items']) == 1){
                        if($order['order_items'][0]['express'] != ''){
                            $order['express'] = $order['order_items'][0]['express'];
                            $order['express_no'] = $order['order_items'][0]['express_no'];
                        }
                    }else{
                        $is_ps = 1; //商家配送
                        foreach($order['order_items'] as $ks => $vs){
                            if($vs['express'] != -1){
                                $is_ps = 2;
                                break;
                            }
                        }
                        if($is_ps == 1){
                            $order['express'] = -1;
                            $order['express_no'] = $order['order_items'][0]['express_no'];
                        }
                    }
                }
                $order['address_info'] = unserialize($order['address_info']);
            }else{
                $order['self_taking_info'] = unserialize($order['self_taking_info']);
                $order['self_taking_info']['self_taking_shop_info'] = unserialize($order['self_taking_info']['self_taking_shop_info']);
            }
            $total_discounts_jian_price = 0;
            foreach ($order['order_items'] as $item){
                $total_discounts_jian_price = $total_discounts_jian_price + $item['pro_discounts_jian_price'] * $item['num'];
            }
            $order['total_discounts_jian_price'] = number_format($total_discounts_jian_price, 2);

            //判断是否有在申请中未完成售后订单
            $is_after_sale = Db::name('wd_xcx_main_shop_order_service') ->where('order_item_id', 'like', '%'.$order_id.'%') ->where('status', 0) ->count();
            $order['is_after_sale'] = $is_after_sale;

            switch ($order['pay_to']){
                case 0: $order['pay_to'] = '余额支付';
                        break;
                case 1: $order['pay_to'] = '微信支付';
                        break;
                case 2: $order['pay_to'] = '支付宝支付';
                        break;
                case 3: $order['pay_to'] = '百度支付';
                        break;
                case 4: $order['pay_to'] = 'QQ支付';
                        break;
            }
            $order['creat_time'] = date('Y-m-d H:i:s', $order['creat_time']);
            if($order['cancel_time']>0){
                $order['cancel_time'] = date('Y-m-d H:i:s', $order['cancel_time']);
            }
            if($order['pay_time']>0){
                $order['pay_time'] = date('Y-m-d H:i:s', $order['pay_time']);
            }
            if($order['complete_time']>0){
                $order['complete_time'] = date('Y-m-d H:i:s', $order['complete_time']);
            }
            if($order['deliver_time']>0){
                $order['deliver_time'] = date('Y-m-d H:i:s', $order['deliver_time']);
            }
            $money = Db::name('wd_xcx_superuser') ->where('id', $suid) ->value('money');
            //售后时间
            $support_time = Db::name('wd_xcx_duo_products_yunfei') ->where('uniacid', $uniacid) ->value('support_time');
            if(!$support_time){
                $support_time = 15;
            }
            $support_time = $support_time * 24 * 3600;
            $over_time = ($order['check_time'] + $support_time) - time();
            if($over_time > 0){
                $order['can_apply'] = true;
            }else{
                $order['can_apply'] = false;
            }

            return json_encode(['data' => ['error' => 0, 'order' => $order, 'money'=> $money]]);

        }else{
            return json_encode(['data' => ['error' => 1, 'msg' => '订单不存在！']]);
        }
    }

    /**
     * 付款订单 总订单取消订单申请页
     */
    public function applyAllAfterSales(){
        $uniacid = input('uniacid');
        $suid = input('suid');
        $order_id = input('order_id');

        $order = Order::where([
            'uniacid' => $uniacid,
            'suid' => $suid,
            'order_id' => $order_id,
            'allow_all_refund' => 1,
            'status' => ['in', [1,2]]
        ]) ->with('orderItems') ->find();

        if($order){
            $order = $order ->toArray();
            return json_encode(['data' => ['error' => 0, 'order' => $order]]);
        }else{
            return json_encode(['data' => ['error' => 1, 'msg' => '订单不存在！']]);
        }
    }

    /**
     * 子订单申请页面
     */
    public function applyItemAfterSales(){
        $uniacid = input('uniacid');
        $suid = input('suid');
        $order_item_id = input('order_item_id');

        $order_item = Db::name('wd_xcx_main_shop_order_item') ->where([
            'uniacid' => $uniacid,
            'suid' => $suid,
            'order_item_id' => $order_item_id,
            'status' => ['in', [1,2,3,7]]
        ]) ->find();

        $order = Db::name('wd_xcx_main_shop_order') ->where('order_id', $order_item['order_id']) ->find();

        //判断是否有在申请中未完成售后订单
        $is_after_sale = Db::name('wd_xcx_main_shop_order_service') ->where('order_item_id', 'like', '%'.$order['order_id'].'%') ->where('status', 0) ->count();
        if($is_after_sale > 0){
            return json_encode(['data' => ['error' => 2, 'msg' => '订单售后进行中，请稍后再来！']]);
        }

        $orderItem_counts = Db::name("wd_xcx_main_shop_order_item")->where("uniacid", $uniacid)->where("order_id", $order_item['order_id'])->count();

        $refund_count = Db::name("wd_xcx_main_shop_order_item")->where("uniacid", $uniacid)->where("order_id", $order_item['order_id'])->where("status", -4)->count();

        if($orderItem_counts - $refund_count == 1){
            $order_item['is_last_refund'] = 1;
            $order_item['shen_all_can_refund_money'] = $order['total_can_tui_money'];
            $order_item['freight_all'] = $order['freight_money'];
        }else{
            $order_item['is_last_refund'] = 0;
        }

        $is_freight = 0;
        if($order['freight_money'] > 0){
            $is_freight = Db::name("wd_xcx_main_shop_order_item")->where('uniacid', $uniacid)->where('order_id', $order_item['order_id'])->where('delivery_type', 1)->where('status', 'gt', 1)->find();
        }

        if(!$is_freight){
            $orderItem_count = Db::name("wd_xcx_main_shop_order_item")->where("uniacid",$uniacid)->where("order_id", $order_item['order_id'])->where("status", 1)->count();
            if($orderItem_count == 1){
                $order_item_id_serach = Db::name("wd_xcx_main_shop_order_item")->where("uniacid",$uniacid)->where("order_id", $order_item['order_id'])->where("status", 1)->value('order_item_id');
                if($order_item_id_serach == $order_item_id){
                    $order_item['is_add_freight'] = 1;
                }
            }
        }


        if($order_item){
            return json_encode(['data' => ['error' => 0, 'order_item' => $order_item]]);
        }else{
            return json_encode(['data' => ['error' => 1, 'msg' => '订单不存在！']]);
        }
    }

    /**
     *子订单提交申请
     */
    public function applyItemSubmit(){
        $uniacid = input('uniacid');
        $suid = input('suid');
        $order_item_id = input('order_item_id');
        $num = input('num');
        $type = input('type');
        $refund_money = input('refund_money');
        $source = input('source');
        $remark = input('remark');

        $order_item = Db::name('wd_xcx_main_shop_order_item') ->where([
            'uniacid' => $uniacid,
            'suid' => $suid,
            'order_item_id' => $order_item_id
        ]) ->find();

        $is = Db::name('wd_xcx_main_shop_order_item') ->where([
            'uniacid' => $uniacid,
            'order_id' => $order_item['order_id'],
            'order_item_id' => ['neq', $order_item_id],
            'delivery_type' => 1,
            'status' => ['gt', 0],
        ]) ->find();
        $freight_money = 0;
        if(!$is && $num == $order_item['num']-$order_item['refund_num']){
            $freight_money = Db::name('wd_xcx_main_shop_order')->where("uniacid",$uniacid)->where("order_id",$order_item['order_id'])->value('freight_money');
        }

        if($order_item){
            if($num > ($order_item['num']-$order_item['refund_num']) || $refund_money > $order_item['pro_can_refound_price'] * $order_item['num'] + $freight_money){
                return json_encode(['data' => ['error' => 2, 'msg' => '申请数量或者价格不正确！']]);
            }else{
                $order_service_id = 's'.date('YmdHi', time()).substr(microtime(), 2, 4).rand(1000,9999);
                $order_service_data = [
                    'uniacid' => $uniacid,
                    'suid' => $suid,
                    'source' => $source,
                    'order_service_id' => $order_service_id,
                    'order_item_id' => $order_item_id,
                    'num' => $num,
                    'refund_money' => $refund_money,
                    'apply_remark' => $remark,
                    'apply_type' => $type,
                    'status' => 0,
                    'apply_status' => $order_item['status'],
                    'creat_time' => time()
                ];

                $order_item_update = [
                    'tui_time' => time(),
//                    'refund_num' => $num + $order_item['refund_num'],
                    'has_service' => 1,
                    'order_service_id' => $order_service_id
                ];
                if($order_item['status'] == 1 || $order_item['status'] == 2){
                    $order_item_update['status'] = 5;
                }else{
                    $order_item_update['status'] = 6;
                }
                $order_item_logs = unserialize($order_item['order_item_log']);
                if($type == 0){
                    $order_item_log = ['time'=>time(), 'log'=>'用户申请退款:数量:'.$num];
                }else{
                    $order_item_log = ['time'=>time(), 'log'=>'用户申请退货退款:数量:'.$num];
                }
                array_push($order_item_logs, $order_item_log);
                $order_item_update['order_item_log'] = serialize($order_item_logs);

                //改变子订单状态
                Db::name('wd_xcx_main_shop_order_item') ->where('order_item_id', $order_item_id) ->update($order_item_update);

                //改变主订单状态  不可统一取消订单
                Db::name('wd_xcx_main_shop_order') ->where('order_id', $order_item['order_id']) ->update(['allow_all_refund' => 2]);

                //保存售后订单
                Db::name('wd_xcx_main_shop_order_service') ->insert($order_service_data);

                return json_encode(['data' => ['error' => 0, 'order_service' => $order_service_data]]);

            }
        }else{
            return json_encode(['data' => ['error' => 1, 'msg' => '订单不存在！']]);
        }
    }

    /**
     * 售后订单主动撤销
     */
    public function applyAfterSalesRevoke(){
        $uniacid = input('uniacid');
        $suid = input('suid');
        $order_service_id = input('order_service_id');

        $order_service = Db::name('wd_xcx_main_shop_order_service') ->where([
            'uniacid' => $uniacid,
            'suid' => $suid,
            'order_service_id' => $order_service_id,
            'status' => 0
        ]) ->find();

        if($order_service){
            if($order_service['is_item'] == 1){ //子订单
                $order_item_logs = Db::name('wd_xcx_main_shop_order_item') ->where('order_item_id', $order_service['order_item_id']) ->value('order_item_log');
                $order_item_logs = unserialize($order_item_logs);
                $order_item_log = ['time'=>time(), 'log'=>'用户撤销申请退款'];
                array_push($order_item_logs, $order_item_log);
                Db::name('wd_xcx_main_shop_order_item') ->where('order_item_id', $order_service['order_item_id']) ->update(['status' => $order_service['apply_status'], 'order_item_log' => serialize($order_item_logs)]);
            }else{  //主订单
                $order = Order::where([
                    'order_id' => $order_service['order_item_id']
                ]) ->with('orderItems') ->find();
                $order = $order ->toArray();
                //修改主订单状态
                Db::name('wd_xcx_main_shop_order') ->where('order_id', $order['order_id']) ->update(['allow_all_refund' => 1]);

                //修改子订单状态
                foreach ($order['order_items'] as $item){
                    $order_item_logs = Db::name('wd_xcx_main_shop_order_item') ->where('order_item_id', $item['order_item_id']) ->value('order_item_log');
                    $order_item_logs = unserialize($order_item_logs);
                    $order_item_log = ['time'=>time(), 'log'=>'用户撤销申请退款'];
                    array_push($order_item_logs, $order_item_log);
                    Db::name('wd_xcx_main_shop_order_item') ->where('order_item_id', $item['order_item_id']) ->update(['status' => $order_service['apply_status'], 'order_item_log' => serialize($order_item_logs)]);
                }
            }
            //修改售后订单状态
            Db::name('wd_xcx_main_shop_order_service') ->where('order_service_id', $order_service_id) ->update([
                'status' => -1,
                'revoke_time' => time()
            ]);
            return json_encode(['data' => ['error' => 0]]);

        }else{
            return json_encode(['data' => ['error' => 1, 'msg' => '售后订单不存在，或者不可撤销！']]);
        }

    }

    /**
     *获取子订单物流列表
     */
    public function getLogisticsInforList(){
        $uniacid = input('uniacid');
        $suid = input('suid');
        $order_id = input('order_id');

        $order_items = Db::name('wd_xcx_main_shop_order_item')->where([
            'uniacid' => $uniacid,
            'suid' => $suid,
            'order_id' => $order_id
        ]) ->select();
        $info_list = [];
        foreach ($order_items as $item){

            if($item['express'] == -1){
                $name = '商家配送_'.$item['express_no'];
                $info_list[$name]['order'][] = $item;
                $info_list[$name]['flag'] = "配送中";
                $info_list[$name]['express'] = "商家配送";
                $info_list[$name]['express_no'] = $item['express_no'];
            }else if($item['express'] && $item['express_no']){
                $name = $item['express'].'_'.$item['express_no'];
                if(!isset($info_list[$name])){
                    $info_list[$name]['express'] = $item['express'];
                    $info_list[$name]['express_no'] = $item['express_no'];
                    $info_list[$name]['order'][] = $item;
                    $wu_info = $this->getWuliu($uniacid, $item['express'], $item['express_no'], $item['order_item_id']);
                    $info_list[$name]['flag'] = $wu_info['flag'];
                }else{
                    $info_list[$name]['order'][] = $item;
                }
            }else{
                $info_list['not'][] = $item;
            }
        }
        return json_encode(['data' => ['error' => 0, 'info_list'=>$info_list]]);

    }

    /**
     * 售后订单详情页
     */
    public function afterOrderDetails(){
        $uniacid = input('uniacid');
        $suid = input('suid');
        $order_service_id = input('order_service_id');

        $service_order = Db::name('wd_xcx_main_shop_order_service') ->where([
            'uniacid' => $uniacid,
            'suid' => $suid,
            'order_service_id' => $order_service_id
        ]) ->find();

        if($service_order){
            $service_order['apply_send_address'] = $service_order['apply_send_address'] ? unserialize($service_order['apply_send_address']) : '';
            if($service_order['is_item'] == 1){ //子订单申请
                $order_item = Db::name('wd_xcx_main_shop_order_item') ->where('order_item_id', $service_order['order_item_id']) ->find();
                $service_order['order_items'][0] = $order_item;
            }else{ //主订单统一退款
                $order = Db::name('wd_xcx_main_shop_order') ->where('order_id', $service_order['order_item_id']) ->find();
                if($order['coupon_id'] > 0){
                    $coupon_title = Db::name('wd_xcx_coupon_user') ->where('id', $order['coupon_id']) ->value('title');
                    $service_order['coupon_title'] = $coupon_title;
                }else{
                    $service_order['coupon_title'] = '';
                }
                $service_order['score_use'] = $order['score_use'];
                $order_items = Db::name('wd_xcx_main_shop_order_item') ->where('order_id', $service_order['order_item_id']) ->select();
                $service_order['order_items'] = $order_items;
            }
            $service_order['creat_time'] = date('Y-m-d H:i:s', $service_order['creat_time']);
            if($service_order['agree_time']){
                $service_order['agree_time'] = date('Y-m-d H:i:s', $service_order['agree_time']);
            }
            if($service_order['refuse_time']){
                $service_order['refuse_time'] = date('Y-m-d H:i:s', $service_order['refuse_time']);
            }
            if($service_order['refund_time']){
                $service_order['refund_time'] = date('Y-m-d H:i:s', $service_order['refund_time']);
            }
            if($service_order['revoke_time']){
                $service_order['revoke_time'] = date('Y-m-d H:i:s', $service_order['revoke_time']);
            }
            return json_encode(['data' => ['error' => 0, 'order_services' => $service_order]]);
        }else{
            return json_encode(['data' => ['error' => 1, 'msg' => '售后订单不存在！']]);
        }
    }

    /**
     * 售后订单列表页
     */
    public function afterOrderLists(){
        $uniacid = input('uniacid');
        $suid = input('suid');
        $page = input('page') ? input('page') : 1;

        $size = 10;
        $begin = ($page - 1) * $size ;

        $service_orders = Db::name('wd_xcx_main_shop_order_service') ->where([
            'uniacid' => $uniacid,
            'suid' => $suid
        ])  ->order('creat_time desc')
            ->limit($begin, $size) ->select();

        if($service_orders){
            foreach ($service_orders as $k => $order){
                if($order['is_item'] == 1){
                    $order_items = Db::name('wd_xcx_main_shop_order_item') ->where('order_item_id', $order['order_item_id']) ->find();
                    $service_orders[$k]['order_items'][0] = $order_items;
                }else{
                    $order_items = Db::name('wd_xcx_main_shop_order_item') ->where('order_id', $order['order_item_id']) ->select();
                    $service_orders[$k]['order_items'] = $order_items;
                }
            }
            return json_encode(['data' => ['error' => 0, 'service_orders' => $service_orders]]);
        }else{
            return json_encode(['data' => ['error' => 0, 'service_orders' => []]]);
        }
    }

    /**
     * 评价列表页
     */
    public function evaluationList(){
        $uniacid = input('uniacid');
        $suid = input('suid');
        $order_id = input('order_id');

        $order = Order::where([
            'uniacid' => $uniacid,
            'suid' => $suid,
            'order_id' => $order_id
        ]) ->with('orderItems') ->find();
        if($order){
            $order = $order->toArray();
            return json_encode(['data' => ['error' => 0, 'order' => $order]]);
        }else{
            return json_encode(['data' => ['error' => 1, 'msg' => '订单不存在！']]);
        }
    }

    /**
     * 评价也提价
     */
    public function orderEvaluationSubmint(){
        $uniacid = input('uniacid');
        $suid = input('suid');
        $order_id = input('order_id');
        $assess = input('assess');   // 1初次评价  2 追评
        $evaluate_data = html_entity_decode(input('evaluate_data'));
        $evaluate_data = json_decode($evaluate_data, TRUE);
        if(!$evaluate_data){
            return json_encode(['data' => ['error' => 2, 'msg' => '评价内容不存在！']]);
        }

        $order = Db::name('wd_xcx_main_shop_order') ->where([
            'uniacid' => $uniacid,
            'suid' => $suid,
            'order_id' => $order_id,
            'assess' => $assess
        ])  ->find();

        if($order){
            $evaluate_insert_data = [];
            foreach ($evaluate_data as $evaluate){
                if($assess == 1){
                    $order_item = Db::name('wd_xcx_main_shop_order_item') ->where('order_item_id', $evaluate['order_item_id']) ->find();
                    $arr = [
                        'uniacid' => $uniacid,
                        'pid' => $order_item['pro_id'],
                        'orderid' => $evaluate['order_item_id'],
                        'assess' => $evaluate['level'],
                        'content' => $evaluate['content'],
                        'anonymous' => $evaluate['anonymous'],
                        'imgs' => $evaluate['imgs'] ? serialize($evaluate['imgs']) : '',
                        'suid' => $suid,
                        'type' => 'mainshop',
                        'creattime' => date('Y-m-d H:i:s', time())
                    ];
                    array_push($evaluate_insert_data, $arr);
                }else{
                    $data = [
                        'append_content' => $evaluate['content'],
                        'append_imgs' => $evaluate['imgs'] ? serialize($evaluate['imgs']) : '',
                        'append_creattime' => date('Y-m-d H:i:s', time())
                    ];
                    Db::name('wd_xcx_evaluate') ->where('orderid', $evaluate['order_item_id']) ->update($data);
                }
            }
            if(count($evaluate_insert_data) > 0){
                Db::name('wd_xcx_evaluate') ->insertAll($evaluate_insert_data);
            }

            if($assess == 1){
                Db::name('wd_xcx_main_shop_order') ->where('order_id', $order_id) ->update(['assess' => 2, 'status' => 5]);
                $order_items = Db::name('wd_xcx_main_shop_order_item') ->where('order_id', $order_id) ->select();
                foreach ($order_items as $item) {
                    $order_item_logs = unserialize($item['order_item_log']);
                    $order_item_log = ['time'=>time(), 'log'=>'订单评价'];
                    array_push($order_item_logs, $order_item_log);
                    Db::name('wd_xcx_main_shop_order_item')->where('id', $item['id'])->update(['order_item_log'=>serialize($order_item_logs), 'complete_time' => time(), 'assess' => '2', 'status' => 7]);
                }
            }else{
                Db::name('wd_xcx_main_shop_order') ->where('order_id', $order_id) ->update(['assess' => 3, 'status' => 5]);
            }

            return json_encode(['data' => ['error' => 0]]);

        }else{
            return json_encode(['data' => ['error' => 1, 'msg' => '订单不存在！']]);
        }
    }

    //退货物流
    public function doPageRefundexpress(){
        $uniacid = input('uniacid');
        $refund_express = input('refund_express');
        $refund_express_no = input('refund_express_no');
        $order_service_id = input('order_service_id');
        $is = Db::name("wd_xcx_main_shop_order_service")->where("uniacid",$uniacid)->where("order_service_id",$order_service_id)->find();
        if($is){
            Db::name("wd_xcx_main_shop_order_service")->where("uniacid",$uniacid)->where("order_service_id",$order_service_id)->update([
                    'express' => $refund_express,
                    'express_no' => $refund_express_no,
                ]);
            return json_encode(['data' => ['error' => 0]]);
        }else{
            return json_encode(['data' => ['error' => 1, 'msg' => '售后订单不存在！']]);
        }
    }
    //支付宝获取手机号验签与解密
    public function doPagealijiemi(){
        $uniacid = input('uniacid');
        $encryptedData  = input('encryptedData');
        $encryptedData = html_entity_decode($encryptedData);
        $encryptedData = json_decode($encryptedData, TRUE)['response'];

        $ali_aes = Db::name('wd_xcx_base')->where('uniacid', $uniacid)->value('ali_aes');
        $res = $this->decryptData($encryptedData, $ali_aes);
        return $res;
    }

    //字节跳动获取手机号验签与解密
    public function doPagebytejiemi(){
        $uniacid = input('uniacid');
        $iv  = input('iv');
        $encryptedData  = input('encryptedData');
        $sessionKey = input('newSessionKey');
        $aesKey = base64_decode($sessionKey);
        $aesIV = base64_decode($iv);
        $aesCipher = base64_decode($encryptedData);
        $res = json_decode(openssl_decrypt( $aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV), TRUE);
        $result = [];
        $result['data'] = $res['phoneNumber'];
        return json_encode($result);
    }
    public function decryptData( $encryptedData, $key)
    {
        $aesKey=base64_decode($key);
        $iv = 0;

        $aesIV=base64_decode($iv);

        $aesCipher=base64_decode($encryptedData);

        $result=openssl_decrypt( $aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);
        return $result;
    }

    //点赞
    public function doPageLikes()
    {
        $uniacid = input("uniacid");
        $suid = input('suid');
        $cid = input("id");
        $type = input("type");

        //先判断有没有点赞过
        $likes = Db::name('wd_xcx_likes')->where("uniacid", $uniacid)->where("suid", $suid)->where("type", $type)->where("cid", $cid)->find();
        if ($likes) {
            $res = Db::name('wd_xcx_likes')->where("uniacid", $uniacid)->where("suid", $suid)->where("type", $type)->where("cid", $cid)->delete();
            if ($res) {
                $adata['data'] = [
                    'error' => 0,
                    'msg' => '取消收藏成功',
                ];
                return json_encode($adata);
            }
        } else {
            $data = array(
                "suid" => $suid,
                "type" => $type,
                "cid" => $cid,
                "uniacid" => $uniacid
            );
            $res = Db::name('wd_xcx_likes')->insert($data);
            if ($res) {
                $adata['data'] = [
                    'error' => 0,
                    'msg' => '收藏成功',
                ];
                return json_encode($adata);
            }
        }
    }
}
