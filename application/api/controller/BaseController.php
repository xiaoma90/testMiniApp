<?php

namespace app\api\controller;

use think\Controller;

use think\Request;

use think\Db;

class BaseController extends Controller {

    protected $prefix = '';

    //构造函数
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this ->prefix = config('database.prefix');
    }

    /**
     * 获取指定端的用户头像昵称
     */
    protected function getnameandavatar($source, $uniacid, $suid){      // source  1  微信小程序  2  支付宝小程序  3 H5
        $info = [];
        if($source == 1){
            $info = Db::name('wd_xcx_user') ->where('suid', $suid) ->where('uniacid', $uniacid) ->field('nickname, avatar') ->find();
            if($info){
                $info['nickname'] = rawurldecode($info['nickname']);
            }

        }elseif($source == 2){
            $info = Db::name('wd_xcx_ali_user') ->where('suid', $suid) ->where('uniacid', $uniacid) ->field('nick_name as nickname, avatar') ->find();
        }elseif($source == 3){
            $info = Db::name('wd_xcx_superuser') ->where('id', $suid) ->where('uniacid', $uniacid) ->field('phone as nickname') ->find();
            $info['nickname'] = substr_replace($info['nickname'], '***',3, 6);
            if($info){
                $info['avatar'] = ROOT_HOST.'/image/pay_list_person.png';
            }
        }elseif($source == 4){
            $info = Db::name('wd_xcx_baidu_user') ->where('suid', $suid) ->where('uniacid', $uniacid) ->field('nickname, avatar') ->find();
        }elseif($source == 5){
            $info = Db::name('wd_xcx_toutiao_user') ->where('suid', $suid) ->where('uniacid', $uniacid) ->field('nickname, avatar') ->find();
        }elseif($source == 6){
            $info = Db::name('wd_xcx_qq_user') ->where('suid', $suid) ->where('uniacid', $uniacid) ->field('nickname, avatar') ->find();
        }

        if(!$info){
            $info = [
                'nickname' => '用户**',
                'avatar' => ROOT_HOST.'/image/pay_list_person.png'
            ];
        }
        return $info;
    }


    /**
     * 分销给上级返钱
     */
    protected function dopagegivemoney($uniacid, $suid, $order_id){
        $prefix= config('database.prefix');
        $order_items = Db::name('wd_xcx_main_shop_order_item') ->where([
            'uniacid' => $uniacid,
            'suid' => $suid,
            'order_id' => $order_id,
            'status' => ['in', [3, 7]]
        ]) ->select();
        if(count($order_items)>0){
            foreach ($order_items as $item){
                $fx_ls = Db::name('wd_xcx_fx_ls') ->where([
                    'uniacid' => $uniacid,
                    'suid' => $suid,
                    'order_id' => $item['order_item_id']
                ]) ->find();
                if($fx_ls){
                    //父级
                    if($fx_ls['parent_id_get'] && $fx_ls['parent_id']){
                        $user = Db::name('wd_xcx_superuser') ->where('id', $fx_ls['parent_id']) ->field('fx_allmoney, fx_money') ->find();
                        if($user){
                            $res = Db::name('wd_xcx_superuser') ->where('id', $fx_ls['parent_id']) ->update([
                                'fx_allmoney' => $user['fx_allmoney'] + $fx_ls['parent_id_get'],
                                'fx_money' => $user['fx_money'] + $fx_ls['parent_id_get']
                            ]);
                        }
                    }
                    //父父级
                    if($fx_ls['p_parent_id_get'] && $fx_ls['p_parent_id']){
                        $user = Db::name('wd_xcx_superuser') ->where('id', $fx_ls['p_parent_id']) ->field('fx_allmoney, fx_money') ->find();
                        if($user){
                            $res = Db::name('wd_xcx_superuser') ->where('id', $fx_ls['p_parent_id']) ->update([
                                'fx_allmoney' => $user['fx_allmoney'] + $fx_ls['p_parent_id_get'],
                                'fx_money' => $user['fx_money'] + $fx_ls['p_parent_id_get']
                            ]);
                        }
                    }
                    //父父父级
                    if($fx_ls['p_p_parent_id_get'] && $fx_ls['p_p_parent_id']){
                        $user = Db::name('wd_xcx_superuser') ->where('id', $fx_ls['p_p_parent_id']) ->field('fx_allmoney, fx_money') ->find();
                        if($user){
                            $res = Db::name('wd_xcx_superuser') ->where('id', $fx_ls['p_p_parent_id']) ->update([
                                'fx_allmoney' => $user['fx_allmoney'] + $fx_ls['p_p_parent_id_get'],
                                'fx_money' => $user['fx_money'] + $fx_ls['p_p_parent_id_get']
                            ]);
                        }
                    }
                    //改变订单状态
                    Db::name('wd_xcx_fx_ls') ->where('id', $fx_ls['id']) ->update(['flag' => 2]);
                }
            }
            Db::name('wd_xcx_main_shop_order') ->where('order_id', $order_id) ->update(['is_fanxian' => 1]);
        }
    }

    /**
     * [getProDiscounts 获取商品的会员价]
     * @param  [type] $uniacid [description]
     * @param  [type] $grade   [description]
     * @param  [type] $pid     [description]
     * @param  [type] $not_discounts=1     [1 会员折扣价， 2 非会员最低价]
     * @param  [type] $type_id [商品规格值，-1 单规格  >0 商品指定规格  -2 所有商品最低价]
     * @return [type]          [description]
     */
    protected function getProDiscounts($show=0,$uniacid, $suid, $pid, $not_discounts=1, $type_id=-2){
        $discounts = 0;
        $discount_price = 0;
        $proinfo = Db::name('wd_xcx_products')->where('id', $pid)->find();


        $grade = Db::name('wd_xcx_superuser') ->where('id', $suid) ->value('grade');
        if($show){
            $grade = $grade > 0 ? $grade : 1;
        }
        if($proinfo['discount_status'] == 2){
            $discounts = unserialize($proinfo['discount']);
            foreach ($discounts as $ko => $vo) {
                if($vo['grade'] == $grade){
                    $discounts = floatval($vo['discount']);
                    break;
                }else{
                    $discounts = 0;
                }
            }
        }else if($proinfo['discount_status'] == 1){
            $discount = Db::name('wd_xcx_vipgrade')->where('grade', $grade)->where('uniacid', $uniacid)->where('discount_flag', 1)->value('discount_grade');
            $discounts = floatval($discount);
        }else{
            $discounts = 0;
        }
        if($discounts && $not_discounts == 1){
            if($type_id == -1){ //单规格
                $discount_price = sprintf("%01.2f", $proinfo['price'] * $discounts * 0.1) < 0.01 ? 0.01 : sprintf("%01.2f", $proinfo['price'] * $discounts * 0.1);
            }elseif($type_id == -2){
                if($proinfo['use_more'] != 1){
                    $discount_price = sprintf("%01.2f", $proinfo['price'] * $discounts * 0.1) < 0.01 ? 0.01 : sprintf("%01.2f", $proinfo['price'] * $discounts * 0.1);
                }else{
                    $type_price = Db::name('wd_xcx_duo_products_type_value') ->where('pid', $pid) ->min('price');
                    $discount_price = sprintf("%01.2f", $type_price * $discounts * 0.1) < 0.01 ? 0.01 : sprintf("%01.2f", $type_price * $discounts * 0.1);
                }
            }else{
                $type_price = Db::name('wd_xcx_duo_products_type_value') ->where('id', $type_id) ->value('price');
                $discount_price = sprintf("%01.2f", $type_price * $discounts * 0.1) < 0.01 ? 0.01 : sprintf("%01.2f", $type_price * $discounts * 0.1);
            }
        }else{
            if($proinfo['use_more'] != 1){
                $discount_price = $proinfo['price'];
            }else{
                if($type_id == -2){
                    $discount_price = Db::name('wd_xcx_duo_products_type_value') ->where('pid', $pid) ->min('price');
                }else{
                    $discount_price = Db::name('wd_xcx_duo_products_type_value') ->where('id', $type_id) ->value('price');
                }

            }
        }

        $data = [
            'discounts' => $discounts,
            'discount_price' => $discount_price
        ];

        return $data;
    }

    /**
     * 获取表单
     */
    protected function getFormContent($uniacid, $form_id){
        if($form_id){
            $form = Db::name('wd_xcx_formlist') ->where('id', $form_id) ->find();
            if($form){
                $form['tp_text'] = unserialize($form['tp_text']);
                $tp_text = [];
                if($form['tp_text']){
                    foreach ($form['tp_text'] as $key => &$res) {
                        if($key > 0){
                            $tp_key = $key - 1;
                            if($res['required'] == true){
                                $tp_text[$tp_key]['ismust'] = 1;
                            }else{
                                $tp_text[$tp_key]['ismust'] = 0;
                            }
                            $tp_text[$tp_key]['name'] = $res['label'];
                            if($res['field_type'] == '单行文本'){
                                $tp_text[$tp_key]['type'] = 0;
                                $tp_text[$tp_key]['tp_text'][0]['yval'] = 0;
                            }else if($res['field_type'] == '多行文本'){
                                $tp_text[$tp_key]['type'] = 1;
                                $tp_text[$tp_key]['tp_text'] = '';
                            }else if($res['field_type'] == '多选' || $res['field_type'] == '单选'){
                                if($res['field_type'] == '多选'){
                                    $tp_text[$tp_key]['type'] = 3;
                                }else{
                                    $tp_text[$tp_key]['type'] = 4;
                                }
                                foreach ($res['field_options']['options'] as $key1 => &$rec1) {
                                    $rec1['yval'] = $rec1['label'];
                                    unset($rec1['label']);
                                }
                                $tp_text[$tp_key]['tp_text'] = $res['field_options']['options'];
                            }else if($res['field_type'] == '下拉选'){
                                $tp_text[$tp_key]['type'] = 2;
                                $tp_text[$tp_key]['tp_text'] = [];
                                foreach ($res['field_options']['options'] as $key2 => &$rec2) {
                                    array_push($tp_text[$tp_key]['tp_text'], $rec2['label']);
                                }
                            }else if($res['field_type'] == '日期'){
                                $tp_text[$tp_key]['type'] = 7;
                            }else if($res['field_type'] == '时间'){
                                $tp_text[$tp_key]['type'] = 11;
                            }else if($res['field_type'] == '图片'){
                                $tp_text[$tp_key]['type'] = 5;
                                $tp_text[$tp_key]['tp_text'] = $res['field_options']['maxpic'];
                                $tp_text[$tp_key]['z_val'] =array();
                            }else if($res['field_type'] == '手机号'){
                                $tp_text[$tp_key]['type'] = 0;
                                $tp_text[$tp_key]['tp_text'][0]['yval'] = 1;
                            }else if($res['field_type'] == '身份证'){
                                $tp_text[$tp_key]['type'] = 0;
                                $tp_text[$tp_key]['tp_text'][0]['yval'] = 7;
                            }
                            $tp_text[$tp_key]['val'] = '';
                        }
                    }
                }
                $form['tp_text'] = $tp_text;

                return $form;
            }else{
                return null;
            }
        }else{
            return null;
        }
    }


    /**
     * 表单提交
     */
    protected function doPageFormval($uniacid, $suid, $cid, $pagedata, $types, $source, $fid)
    {
        // 新增自定义表单数据接收
        $forms = $pagedata;
        if($forms != "NULL"){
            foreach ($forms as $key1 => &$res) {
                if ($res['type'] == 14) {
                    $strtime = strtotime($res['days']);
                    $arrs = array(
                        "uniacid" => $uniacid,
                        "cid" => $cid,
                        "types" => $types,
                        "datys" => $strtime,
                        "pagedatekey" => $res['indexkey'],
                        "arrkey" => $res['xuanx'],
                        "creattime" => time()
                    );
                    Db::name('wd_xcx_form_dd')->insert($arrs);
                }
            }
            $data = array(
                "uniacid" => $uniacid,
                "cid" => $cid,
                "creattime" => time(),
                "val" => serialize($forms),
                "flag" => 0,
                "fid"=>$fid,
                "type"=>$types,
                "suid"=>$suid,
                'source' => $source,
            );
            $res = Db::name('wd_xcx_formcon')->insertGetId($data);
            if ($res) {
                $formsinfo = Db::name('wd_xcx_formlist')->where("id", $fid)->find();
                $jsons = [
                    "ftitle" => $formsinfo['formname'],
                    "fmsg" => $formsinfo['descs']
                ];
                $jsons = serialize($jsons);

//                tpl_send($uniacid, 11, input('openid'), $source, input('form_id'), $jsons);

                $form = Db::name('wd_xcx_formcon')->where('uniacid', $uniacid)->where("id", $res)->field("id")->find();
                $form['con'] = "表单提交成功";
                $result['data'] = $form;
                return $res;
            }
        }else{
            $formsinfo = Db::name('wd_xcx_formlist')->where("id", $fid)->find();
            $jsons = [
                "ftitle" => $formsinfo['formname'],
                "fmsg" => $formsinfo['descs']
            ];
            $jsons = serialize($jsons);

            tpl_send($uniacid, 11, input('openid'), $source, input('form_id'), $jsons);

            $form['con'] = "提交成功";
            $result['data'] = $form;
            return true;
        }
    }


    /**
     * 获取物流详情
     */
    protected function getWuliu($uniacid, $kuaidi, $kuaidihao, $order_item_id){
        $set = Db::name('wd_xcx_duo_products_yunfei') ->where('uniacid', $uniacid) ->find();
        if($order_item_id){
            if(strpos($order_item_id, '-')){
                $pro = Db::name('wd_xcx_main_shop_order_item') ->where('order_item_id', $order_item_id) ->find();
            }else{
                $pro = Db::name('wd_xcx_main_shop_order_item') ->where('order_id', $order_item_id) ->find();
            }
        }else{
            $pro = '';
        }
        if($set['api_type'] == 3){
            if($set['appcode']){
                $kd_code = array(
                    '顺丰' => 'SFEXPRESS',
                    '韵达' => 'YUNDA',
                    '天天' => 'TTKDEX',
                    '申通' => 'STO',
                    '圆通' => 'YTO',
                    '中通' => 'ZTO',
                    '国通' => 'GTO',
                    '百世' => 'HTKY',
                    'EMS'  => 'EMS',
                    '邮政' => 'CHINAPOST',
                    'FEDEX联邦(国内件)' => 'FEDEX',
                    '宅急送' => 'ZJS',
                    '安捷快递' => 'ANJELEX',
                    '大田物流' => 'DTW',
                    '百福东方' => 'EES',
                    '德邦快运' => 'DEPPON',
                    'D速物流' => 'DEXP',
                    'COE东方快递' => 'COE',
                    '共速达' => 'GSD',
                    '佳怡物流' => 'JIAYI',
                    '京广速递' => 'KKE',
                    '急先达' => 'JOUST',
                    '加运美' => 'TMS',
                    '晋越快递' => 'PEWKEE',
                    '全晨快递' => 'QCKD',
                    '民航快递' => 'CAE',
                    '龙邦快递' => 'LBEX',
                    '联昊通速递' => 'LTS',
                    '全一快递' => 'APEX',
                    '如风达' => 'RFD',
                    '速尔快递' => 'SURE',
                    '盛丰物流' => 'SFWL',
                    '天地华宇' => 'HOAU',
                    'TNT快递' => 'TNT',
                    'UPS' => 'UPS',
                    '万家物流' => 'WANJIA',
                    '信丰物流' => 'XFEXPRESS',
                    '亚风快递' => 'BROADASIA',
                    '优速快递' => 'UC56',
                    '远成物流' => 'YCGWL',
                    '运通快递' => 'YTEXPRESS',
                    '源安达快递' => 'YADEX',
                    '中铁快运' => 'CRE',
                    '中邮快递' => 'CNPL',
                    '安能物流' => 'ANE',
                    '九曳供应链' => 'JIUYESCM',
                    '东骏快捷'=>'DJ56',
                    '万象'=>'EWINSHINE',
                    '芝麻开门'=>'ZMKMEX'
                );
                $kuaidi = $kd_code[$kuaidi];
                $host = "https://wuliu.market.alicloudapi.com";//api访问链接
                $path = "/kdi";//API访问后缀
                $method = "GET";
                $appcode = $set['appcode'];  //阿里云云市场购买的 appcode
                $headers = array();
                array_push($headers, "Authorization:APPCODE " . $appcode);
                $querys = "no=$kuaidihao&type=$kuaidi";  //参数写在这里
                $bodys = "";
                $url = $host . $path . "?" . $querys;//url拼接

                $curl = curl_init();
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($curl, CURLOPT_FAILONERROR, false);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_HEADER, false);
                if (1 == strpos("$".$host, "https://"))
                {
                    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
                }
                $data = curl_exec($curl);

                curl_close($curl);
                if(strpos($data,'签收') !== false){
                    $flag = '已签收';
                }else{
                    $flag = '运输中';
                }
                $res = json_decode($data, true);
                if($res['status'] == 0){
                    if(count($res['result']['list']) > 0){
                        $status = 0;
                        $info = $res['result']['list'];
                    }else{
                        $status = -1;
                        $info = '';
                    }
                }else{
                    $status = -1;
                    $info = '';
                }
                return array('type'=>'ali', 'list'=>$info, 'status'=> $status, 'flag'=>$flag, 'pro' => $pro);
            }

        }else{

            $kd_code = array(
                '顺丰速运' => 'SF',
                '韵达' => 'YD',
                '天天' => 'HHTT',
                '申通' => 'HLWL',
                '圆通' => 'YTO',
                '中通' => 'ZTO',
                '国通' => 'GTO',
                '百世汇通' => 'HTKY',
                'EMS'  => 'EMS',
                '邮政' => 'YZPY',
                'FEDEX联邦(国内件)' => 'FEDEX',
                '宅急送' => 'ZJS',
                '安捷快递' => 'AJ',
                '大田物流' => 'DTWL',
                '百福东方' => 'BFDF',
                '德邦' => 'DBLKY',
                'D速物流' => 'DSWL',
                'COE东方快递' => 'COE',
                '共速达' => 'GSD',
                '佳怡物流' => 'JYWL',
                '京广速递' => 'JGSD',
                '急先达' => 'JXD',
                '加运美' => 'JYM',
                '晋越快递' => 'JYKD',
                '全晨快递' => 'QCKD',
                '民航快递' => 'MHKD',
                '龙邦快递' => 'LB',
                '联昊通速递' => 'LHT',
                '全一快递' => 'UAPEX',
                '如风达' => 'RFD',
                '速尔快递' => 'SURE',
                '盛丰物流' => 'SFWL',
                '天地华宇' => 'HOAU',
                'TNT快递' => 'TNT',
                'UPS' => 'UPS',
                '万家物流' => 'WJWL',
                '信丰物流' => 'XFEX',
                '亚风快递' => 'YFSD',
                '优速' => 'UC',
                '远成物流' => 'YCWL',
                '运通快递' => 'YTKD',
                '源安达快递' => 'YADEX',
                '中铁快运' => 'ZTKY',
                '中邮快递' => 'ZYKD',
                '安能物流' => 'ANE',
                '九曳供应链' => 'JIUYE',
                '晟邦物流'=>'SBWL',
                '东骏快捷'=>'DJKJWL'
            );
            if($kuaidi){
                $kuaidi = $kd_code[$kuaidi];
            }
            include_once 'KdApi.php';
            $kd = new KdApi();
            $res = $kd->getOrderTracesByJson($uniacid, $kuaidi, $kuaidihao);
            if(strpos($res,'签收') !== false){
                $flag = '已签收';
            }else{
                $flag = '运输中';
            }
            $res = json_decode($res, true);
            if($res['Success']){
                if(count($res['Traces']) > 0){
                    $status = 0;
                    $info = array_reverse($res['Traces']);
                }else{
                    $status = -1;
                    $info = '';
                }
            }else{
                $status = -1;
                $info = '';
            }

            return array('type'=>'kdniao', 'list'=>$info, 'status'=> $status, 'flag' => $flag, 'pro' => $pro);
        }

    }


    // 过滤掉emoji表情
    protected function filterEmoji($str)
    {
        $str = preg_replace_callback(
            '/./u',
            function (array $match) {
                return strlen($match[0]) >= 4 ? '' : $match[0];
            },
            $str);
        return $str;
    }


}