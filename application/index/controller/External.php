<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;

use app\index\model\Applet;
use app\index\model\ImsSudu8PagePddCate as PddCates;
use app\index\model\ImsSudu8PageJdCate as JdCates;
use app\index\model\ImsSudu8PageExternalCate as ExternalCates;
use app\index\model\ImsSudu8PageExternalGoods as ExternalGoods;
use app\index\model\ImsSudu8PageExternalOrder as ExternalOrder;
use app\index\model\ImsSudu8PageFxGz as FxGz;
use app\index\model\ImsSudu8PageExternalFanyongLs as FanyongLs;
use app\index\model\ImsSudu8PageSuperuser as User;
use app\index\model\ImsSudu8PageFxLs as FxLs;
use app\index\model\ImsSudu8PageExternalFanyongTx as FxTx;


class External extends Base
{
	/*分类列表*/
	public function cate(){
		 if (check_login()) {
            if (powerget()) {
                $uniacid = input('appletid');
                $app = new Applet;
                $appinfo = $app->getAppInfo();
                $this->assign('applet', $appinfo);

                //查询所有分类
                $external = new ExternalCates();
                $externals = $external ->getExternal();
                // $eee = $externals ->each(function ($item) use ($uniacid){
                //     dump($item->jdCate);
                // });
                // die;
                // dump($externals);die;
                $count = $external ->where('uniacid', $uniacid) ->count();
                $this->assign('externals', $externals);
                $this->assign('count', $count);

                return $this->fetch('cate');

            } else {
                $usergroup = Session::get('usergroup');
                if ($usergroup == 1) {
                    $this->error("您没有权限操作该小程序或找不到相应小程序！", 'Applet/applet');
                }
                if ($usergroup == 2) {
                    $this->error("您没有权限操作该小程序或找不到相应小程序！", 'Applet/index');
                }
                if ($usergroup == 3) {
                    $this->error("您没有权限操作该小程序或找不到相应小程序！", 'Applet/index');
                }
            }
        } else {
            $this->redirect('Login/index');
        }
	}

	/*添加分类*/
	public function addcate(){
		 if (check_login()) {
            if (powerget()) {
                $uniacid = input('appletid');
                $app = new Applet;
                $appinfo = $app->getAppInfo();
                $this->assign('applet', $appinfo);

                //获取拼多多所有分类
                $pdd = new PddCates();
                $pddCates = $pdd ->all();
                $this->assign('pddCates', $pddCates);

                //获取京东所有分类
                $jd = new JdCates();
                $jdCates = $jd ->all();
                $this->assign('jdCates', $jdCates);

                $externalCate = new ExternalCates();
                $cid = input('cateid');
                if($cid){
                    $external = $externalCate ->get($cid);
                }else{
                    $external = '';
                }
                $this ->assign('external', $external);

                return $this->fetch('addcate');

            } else {
                $usergroup = Session::get('usergroup');
                if ($usergroup == 1) {
                    $this->error("您没有权限操作该小程序或找不到相应小程序！", 'Applet/applet');
                }
                if ($usergroup == 2) {
                    $this->error("您没有权限操作该小程序或找不到相应小程序！", 'Applet/index');
                }
                if ($usergroup == 3) {
                    $this->error("您没有权限操作该小程序或找不到相应小程序！", 'Applet/index');
                }
            }
        } else {
            $this->redirect('Login/index');
        }
	}

	/*保存分类*/
    public function savecate(){
        $uniacid = input('appletid');
        $cid = input('cid');
        $cat_name = input('cat_name');
        if(!$cat_name){
            $this->error('分类名称不能为空！');
        }
        $pdd_cate_id = input('pdd_cate_id');
        $jd_cate_id = input('jd_cate_id');
        if(!$pdd_cate_id && !$jd_cate_id){
            $this->error('请至少选择一种分类！');
        }

        $external = new ExternalCates();
        $data['cat_name'] = $cat_name;
        $data['pdd_cat_id'] = $pdd_cate_id;
        $data['jd_cat_id'] = $jd_cate_id;
        if($cid){
            $res = $external ->save($data, ['uniacid'=>$uniacid, 'id'=>$cid]);
        }else{
            $data['uniacid'] = $uniacid;
            $res = $external ->save($data);
        }

        if($res){
            $this ->success('分类添加/更新成功！', Url('External/cate') . '?appletid=' . $uniacid);
        }else{
            $this->error('添加/更新失败，请稍后再试！');
        }
    }

    /* 删除分类*/
    public function delcate(){
        if (check_login()) {
            if (powerget()) {
                $uniacid = input('appletid');
                $cid = input('cateid');

                $external = new ExternalCates();
                $res = $external ->destroy(['uniacid'=>$uniacid, 'id'=>$cid]);
                if($res) {
                    $this->success('分类删除成功！');
                }else{
                    $this->error('请稍后再试！');
                }

            } else {
                $usergroup = Session::get('usergroup');
                if ($usergroup == 1) {
                    $this->error("您没有权限操作该小程序或找不到相应小程序！", 'Applet/applet');
                }
                if ($usergroup == 2) {
                    $this->error("您没有权限操作该小程序或找不到相应小程序！", 'Applet/index');
                }
                if ($usergroup == 3) {
                    $this->error("您没有权限操作该小程序或找不到相应小程序！", 'Applet/index');
                }
            }
        } else {
            $this->redirect('Login/index');
        }
    }

    /*商品列表*/
    public function goods(){
        if (check_login()) {
            if (powerget()) {
                $uniacid = input('appletid');
                $app = new Applet;
                $appinfo = $app->getAppInfo();
                $this->assign('applet', $appinfo);

                //查询所有分类
                $external = new ExternalCates();
                $externals = $external ->getExternal();
                $this->assign('externals', $externals);

                //数据库查询获取商品
                $external_goods = new ExternalGoods();
                $goods = $external_goods ->getAllGoods();
                $hasGood = false;
                if(count($goods->toArray()['data'])>0){
                    $hasGood = true;
                }
                $count = $external_goods ->where('uniacid', $uniacid) ->count();

                $this->assign('goods', $goods);
                $this->assign('hasGood', $hasGood);
                $this->assign('count', $count);


                return $this->fetch('goods');

            } else {
                $usergroup = Session::get('usergroup');
                if ($usergroup == 1) {
                    $this->error("您没有权限操作该小程序或找不到相应小程序！", 'Applet/applet');
                }
                if ($usergroup == 2) {
                    $this->error("您没有权限操作该小程序或找不到相应小程序！", 'Applet/index');
                }
                if ($usergroup == 3) {
                    $this->error("您没有权限操作该小程序或找不到相应小程序！", 'Applet/index');
                }
            }
        } else {
            $this->redirect('Login/index');
        }
    }

    /*从拼多多京东获取商品*/
    public function getGoodsFromExternal(){
        include_once 'pdd.php';
        include_once 'jd.php';

        $uniacid = input('appletid');
        $pdd = new \pdd($uniacid);
        $jd = new \jd($uniacid);
        $keyword = input('keyword');
        $goodsNum = input('goodsNum');
        $goods_source = input('goods_source');
        $goods_cats = input('goods_cats');
        $promotion_rate_low = input('promotion_rate_low');
        $promotion_rate_high = input('promotion_rate_high');
        $price_low = input('price_low');
        $price_high = input('price_high');

        if(!$goods_source){
            $this->error('请选择商品源！');
        }

        if($goods_source == 2){
            if(!$keyword && !$goodsNum && !$promotion_rate_low && !$promotion_rate_high && !$price_low && !$price_high){
                $this->error('请至少输入一种搜索条件！');
            }
        }

        if($promotion_rate_low || $promotion_rate_high){
            if($promotion_rate_low){
                if(!is_numeric($promotion_rate_low)){
                    $this->error('比例下限必须为数字！');
                }
            }
            if($promotion_rate_high){
                if(!is_numeric($promotion_rate_high)){
                    $this->error('比例上限必须为数字！');
                }
            }
            if($promotion_rate_low && !$promotion_rate_high){
                $this->error('请输入比例上限！');
            }
            if(!$promotion_rate_low && $promotion_rate_high){
                $this->error('请输入比例下限！');
            }
            if($promotion_rate_low && $promotion_rate_high && $promotion_rate_low*1 > $promotion_rate_high*1){
                $this->error('佣金比例下限不能大于上限！');
            }
            if($goods_source == 2){
                if($promotion_rate_low > 100 || $promotion_rate_high >100){
                    $this->error('佣金比例值超出范围！');
                }
            }else{
                if($promotion_rate_low > 1000 || $promotion_rate_high >1000){
                    $this->error('佣金比例值超出范围！');
                }
            }
            
        }

        if($price_low){
            if(!is_numeric($price_low)){
                $this->error('券后价下限必须为数字！');
            }
        }
        if($price_high){
            if(!is_numeric($price_high)){
                $this->error('券后价上限必须为数字！');
            }
        }
        $pdd_goods = [];   //拼多多商品
        $jd_goods = [];
        $insertData = [];
        if(!$price_low && !$price_high && !$keyword  && !$goods_cats && !$promotion_rate_low && !$promotion_rate_high && !$goodsNum){
            //如果没有搜索条件，直接插在热销商品
            if($goods_source == 1){ //pdd
                $pdd_goods = $pdd->search(['page_size' => 20]);
            }elseif ($goods_source == 2){   //jd
                // $result = $jd ->jingfenQuery(1, 50);   //获取精粉商品不需要搜索条件   京东搜索商品必须指定至少一种搜索条件
                if($result['code'] == 200){
                    $jd_goods = $result['data'];
                }else{
                    $this->error('暂未获取到商品，请稍后再试！');
                }
                dump($jd_goods);die;
            }
        }else{
            //如果有搜索条件 按搜索条件查找

            $key = [];  // pdd搜索条件
            
            if($goods_source == 1){ //pdd
                if($keyword){
                    $key['keyword'] = $keyword;
                }
                if($goods_cats){
                    $external = new ExternalCates();
                    $externalInfo = $external ->get($goods_cats);
                    if($externalInfo->pdd_cat_id){
                        $key['cat_id'] = $externalInfo->pdd_cat_id;
                    }
                }
                $range_list = '[';
                if($price_low || $price_high){   //券后价范围
                    if($price_low && !$price_high){
                        $price_low = $price_low * 100;
                        $price = '{"range_id":1,"range_from":'.$price_low.',"range_to":1500000}';
                    }else if(!$price_low && $price_high){
                        $price_high = $price_high * 100;
                        $price = '{"range_id":1,"range_from":0,"range_to":'.$price_high.'}';
                    }else{
                        $price_low = $price_low * 100;
                        $price_high = $price_high * 100;
                        $price = '{"range_id":1,"range_from":'.$price_low.',"range_to":'.$price_high.'}';
                    }
                    $range_list .= $price;
                }
                if($promotion_rate_low && $promotion_rate_high){    //佣金比例范围
                    $range_list .= '{"range_id":2,"range_from":'.$promotion_rate_low.',"range_to":'.$promotion_rate_high.'}';
                }
                $range_list .= ']';
                $key['range_list'] = $range_list;
                if($goodsNum){
                    if($goodsNum > 100){
                        $page = floor($goodsNum / 100);
                        for($i=1; $i<=$page; $i++){
                            $key['page'] = $i;
                            $temp_good = $pdd->search($key);
                            $pdd_goods = array_merge($pdd_goods, $temp_good);
                        }
                        $key['page'] = $page + 1;
                        $key['page_size'] = $goodsNum % 100;
                        $last_goods = $pdd->search($key);
                        $pdd_goods = array_merge($pdd_goods, $last_goods);
                    }else{
                        $key['page_size'] = $goodsNum;
                        $pdd_goods = $pdd->search($key);
                    }
                }else{
                    $key['page_size'] = 20;
                    $pdd_goods = $pdd->search($key);
                }

            }elseif ($goods_source == 2){   //jd  按搜索条件搜索
                if($keyword){
                    $key['keyword'] = $keyword;
                }

                if($goods_cats){
                    $external = new ExternalCates();
                    $externalInfo = $external ->get($goods_cats);
                    if($externalInfo->jd_cat_id){
                        $key['cid1'] = $externalInfo->jd_cat_id;
                    }
                }

                if($promotion_rate_low && $promotion_rate_high){
                    $key['commissionShareStart'] = $promotion_rate_low;
                    $key['commissionShareEnd'] = $promotion_rate_high;
                }

                if($price_low && $price_high){
                    $key['pricefrom'] = $price_low;
                    $key['priceto'] = $price_high;
                }

                if($goodsNum > 30){
                    $page = floor($goodsNum / 30);
                    for($i=1; $i<=$page; $i++){
                        $key['pageIndex'] = $i;
                        $key['pageSize'] = 30;
                        $temp_good = $jd->searchGoodsBuyKey($key);
                        $jd_goods = array_merge($jd_goods, $temp_good);
                    }
                    $key['pageIndex'] = $page + 1;
                    $key['pageSize'] = $goodsNum % 100;
                    $last_goods = $jd->searchGoodsBuyKey($key);
                    $jd_goods = array_merge($jd_goods, $last_goods);
                }else{
                    $key['pageIndex'] = 1;
                    $key['pageSize'] = $goodsNum;
                    $jd_goods = $jd->searchGoodsBuyKey($key);
                }

            }
        }


        if(count($pdd_goods)>0){
            foreach ($pdd_goods as $v){
                $new = array(
                    'uniacid' => $uniacid,
                    'name' => $v['goods_name'],
                    'goods_thumbnail_url' => $v['goods_thumbnail_url'],
                    'goods_id' => $v['goods_id'],
                    'type' => 'pdd',
//                    'money' => round($v['promotion_rate']*$config['fanyong']*($v['min_group_price']-$v['coupon_discount'])/100000,2),
                    'coupon_remain_quantity' => $v['coupon_remain_quantity'],
                    'min_group_price' => $v['min_group_price']/100,   //最小拼团价
                    'sold_quantity' => $v['sales_tip'],
                    'coupon_discount' => $v['coupon_discount']/100,   //优惠券金额
//                    'real_price' =>  ($v['min_group_price']-$v['coupon_discount'])/100,    //券后价
                    'mall_name' => $v['mall_name'],
                    'coupon_start_time' => $v['coupon_start_time'],
                    'coupon_end_time' => $v['coupon_end_time'],
                    'promotion_rate' => $v['promotion_rate'],
                    'goods_eval_score' => isset($v['goods_eval_score']) ? $v['goods_eval_score'] : ''
                );
                array_push($insertData, $new);
            }
        }elseif(count($jd_goods)>0){
            foreach ($jd_goods as $v){
                //下载京东商品第一张图片到本地，用于展示
                $name = $uniacid.$v['skuId'].date('Ymd', time()).'.jpg';
                $img_con = file_get_contents($v['imageInfo']['imageList'][0]['url']);
                file_put_contents(ROOT_PATH . 'public' . DS . 'eximages/'.$name, $img_con);
                $goods_url = 'https://'.$_SERVER['SERVER_NAME'].'/eximages/'.$name;

                $new = array(
                    'uniacid' => $uniacid,
                    'name' => $v['skuName'],
                    'goods_thumbnail_url' => $goods_url,
                    'goods_id' => $v['skuId'],
                    'type' => 'jd',
                    'min_group_price' => $v['priceInfo']['price'],   //最小拼团价
                    'sold_quantity' => $v['inOrderCount30Days'],
                    'mall_name' => $v['shopInfo']['shopName'],
                    'promotion_rate' => $v['commissionInfo']['commissionShare'],
                    'goods_eval_score' => $v['goodCommentsShare'],
                    'commission' => $v['commissionInfo']['commission'],
                    'good_link' => $v['materialUrl']
                );
                if(count($v['couponInfo']['couponList']) > 0){
                    $new['cou_link'] = $v['couponInfo']['couponList'][0]['link'];
                    $new['coupon_discount'] = $v['couponInfo']['couponList'][0]['discount'];
                    $new['coupon_start_time'] = $v['couponInfo']['couponList'][0]['getStartTime']/1000;
                    $new['coupon_end_time'] = $v['couponInfo']['couponList'][0]['getEndTime']/1000;
                }
                $img_list = [];
                foreach ($v['imageInfo']['imageList'] as $k => $im){
                    array_push($img_list, $im['url']);
                }
                $new['image_list'] = serialize($img_list);

                array_push($insertData, $new);
            }
        }else{
            $this->error('获取商品失败，请稍后再试！');
        }

        //数据插入  商品已存在  则更新商品信息
        if(count($insertData)>0){
            $external_goods = new ExternalGoods();
            $exist_goods = $external_goods ->getAllGoodIds($uniacid);
            foreach ($insertData as $k => $v){
                if(in_array($v['goods_id'], $exist_goods)){
                    $external_goods ->where('goods_id', $v['goods_id']) ->update($v);
                }else{
                    $external_goods ->insert($v);
                }
            }
            $this->success('获取成功！',Url('External/goods') . '?appletid=' . $uniacid);
        }else{
            $this->error('获取商品失败，请稍后再试！');
        }

    }

    //操作商品
    public function doGoods(){
        $uniacid = input('appletid');
        $op = input('op');
        $gid = input('gid');
        $external_goods = new ExternalGoods();

        if($op == 'sale'){
            $good = $external_goods ->get($gid);
            if($good){
                if($good->is_sale == 1){
                    $res = $external_goods ->save(['is_sale'=>2], ['id'=>$gid]);
                }else{
                    $res = $external_goods ->save(['is_sale'=>1], ['id'=>$gid]);
                }
                if($res){
                    $this->success('操作成功！');
                }else{
                    $this->error('操作失败，请稍后再试！');
                }
            }else{
                $this->error('商品不存在！');
            }
        }

        if($op == 'toIndex'){
            $good = $external_goods ->get($gid);
            if($good){
                if($good->is_index == 1){
                    $res = $external_goods ->save(['is_index'=>2], ['id'=>$gid]);
                }else{
                    $res = $external_goods ->save(['is_index'=>1], ['id'=>$gid]);
                }
                if($res){
                    $this->success('操作成功！');
                }else{
                    $this->error('操作失败，请稍后再试！');
                }
            }else{
                $this->error('商品不存在！');
            }
        }

        if($op == 'delete'){
            $good = $external_goods ->get($gid);
            if($good){
                $res = $external_goods ->destroy($gid);
                if($res){
                    $this->success('操作成功！');
                }else{
                    $this->error('操作失败，请稍后再试！');
                }
            }else{
                $this->error('商品不存在！');
            }
        }

    }

    /*批量删除商品*/
    public function delallm(){
        if (check_login()) {
            if (powerget()) {
                $uniacid = input('appletid');
                $ids = input('mpros');
                $external_goods = new ExternalGoods();
                $res = $external_goods ->destroy($ids);
                if($res){
                    $this->success('批量删除成功！');
                }else{
                    $this->error('操作失败，请稍后再试！');
                }

            } else {
                $usergroup = Session::get('usergroup');
                if ($usergroup == 1) {
                    $this->error("您没有权限操作该小程序或找不到相应小程序！", 'Applet/applet');
                }
                if ($usergroup == 2) {
                    $this->error("您没有权限操作该小程序或找不到相应小程序！", 'Applet/index');
                }
                if ($usergroup == 3) {
                    $this->error("您没有权限操作该小程序或找不到相应小程序！", 'Applet/index');
                }
            }
        } else {
            $this->redirect('Login/index');
        }
    }


    /*订单列表*/
    public function orderList(){
        if (check_login()) {
            if (powerget()) {
                $uniacid = input('appletid');
                $app = new Applet;
                $appinfo = $app->getAppInfo();
                $this->assign('applet', $appinfo);

                $keyword = input('keyword');
                $orderid = input('orderid');
                $goods_source = input('goods_source');
                $start_get = input('start_get');
                $end_get = input('end_get');

                //查询订单
                $order = new ExternalOrder();
                if($keyword || $orderid || $goods_source != 0 || $start_get || $end_get){
                    $where = [];
                    if($keyword){
                        $where['goods_name'] = ['like', '%'.$keyword.'%'];
                    }
                    if($orderid){
                        $where['order_sn'] = ['like', '%'.$orderid.'%'];
                    }
                    if($goods_source == 1){
                        $where['type'] = 'pdd';
                    }
                    if($goods_source == 2){
                        $where['type'] = 'jd';
                    }
                    if($start_get && $end_get){
                        $start_get = strtotime($start_get);
                        $end_get = strtotime($end_get);
                        if($start_get > $end_get){
                            $this->error('开始时间不能大于结束时间！');
                        }
                        if($end_get > time()){
                                $this->error('结束时间不能大于当前时间！');
                         }
                        $where['order_create_time'] = ['BETWEEN', [$start_get, $end_get]];
                    }else if($start_get){
                        $where['order_create_time'] = ['BETWEEN', [strtotime($start_get), time()]];
                    }elseif($end_get){
                        $where['order_create_time'] = ['BETWEEN', [1559318400, $end_get]];
                    }
                    $orders = $order ->search($where);
                    $count = $order ->where('uniacid', $uniacid) ->where($where) ->count();

                }else{
                    $orders = $order ->getAllGoods();
                    $count = $order ->where('uniacid', $uniacid) ->count();
                }


                $has = false;
                if(count($orders->toArray()['data']) > 0){
                    $has = true;
                }
                $this->assign('has', $has);
                $this->assign('orders', $orders);
                $this->assign('count', $count);

                return $this->fetch('orderList');

            } else {
                $usergroup = Session::get('usergroup');
                if ($usergroup == 1) {
                    $this->error("您没有权限操作该小程序或找不到相应小程序！", 'Applet/applet');
                }
                if ($usergroup == 2) {
                    $this->error("您没有权限操作该小程序或找不到相应小程序！", 'Applet/index');
                }
                if ($usergroup == 3) {
                    $this->error("您没有权限操作该小程序或找不到相应小程序！", 'Applet/index');
                }
            }
        } else {
            $this->redirect('Login/index');
        }
    }

    /*同步订单*/
    public function getOrderFromExt(){
        include_once 'pdd.php';
        $user = new User();
        
        $uniacid = input('appletid');
        $pdd = new \pdd($uniacid);
        if(!$uniacid){
            $this->error('参数不正确！');
        }
//        $this -> doFxAndFanYong($uniacid, 12, 12, 12);
        $start_get = input('start_get');
        $end_get = input('end_get');
        $now = time();
        if($start_get || $end_get){
            if(!$start_get){
                $this->error('开始时间不能为空！');
            }
            if(!$end_get){
                $this->error('结束时间不能为空！');
            }
            if($start_get && $end_get){
                $start_get = strtotime($start_get);
                $end_get = strtotime($end_get);
                if($start_get > $end_get){
                    $this->error('开始时间不能大于结束时间！');
                }
                if($end_get > $now){
                    $this->error('结束时间不能大于当前时间！');
                }
                if(($end_get-$start_get) > 24*3600){
                    $this->error('同步订单，时间差不能大于24小时！');
                }
            }
        }else{
            $end_get = $now;
            $start_get = $end_get - 24*3600 + 1;
        }
        $res = $pdd ->getOrderList($start_get, $end_get);
        if(!$res){
            $this->error('暂无可同步的订单！');
        }
        if(count($res)>0){
            //只处理该推广位的订单
            $app = new Applet();
            $appInfo = $app ->get($uniacid);
            $order = new ExternalOrder();
            $fanyong = round($appInfo ->fanyong / 100, 2);

            $app_ls = new FanyongLs();
            $fx_ls = new FxLs();

            $fxgz = new FxGz();
            $fx_info = $fxgz ->get(['uniacid'=>$uniacid]);
            //处理订单表数据
            $orders = [];
            foreach ($res as $key => $value){
                if($value['p_id'] == $appInfo->p_id){
                    $fxsid = $value['custom_parameters'];
                    $fxsid = substr($fxsid, 7);
                    $fxsid = substr($fxsid, 0, -1);
                    if($fxsid != 0){
                    	$fxuser = $user ->get($fxsid);
                    	if($fxuser['fxs'] == 2){
                    		$isfxs = 1;
                    	}else{
                    		$isfxs = 0;
                    	}
                    }else{
                    	$isfxs = 0;
                    }
                    $new = [
                        'uniacid' => $uniacid,
                        'p_id' => $value['p_id'],
                        'order_sn' => $value['order_sn'],
                        'goods_id' => $value['goods_id'],
                        'goods_name' => $value['goods_name'],
                        'goods_thumbnail_url' => $value['goods_thumbnail_url'],
                        'goods_quantity' => $value['goods_quantity'],
                        'goods_price' => $value['goods_price']/100,
                        'order_amount' => $value['order_amount']/100,
                        'promotion_rate' => $value['promotion_rate'],
                        'promotion_amount' => $value['promotion_amount']/100,
                        'order_status' => $value['order_status'],
                        'order_create_time' => $value['order_create_time'],
                        'order_modify_at' => $value['order_modify_at'],
                        'fxsid' => $fxsid,
                        'type' => 'pdd',
                    ];
                    //总平台佣金
                    $new['pintai_fanyong'] = round($new['promotion_amount'] * (1 -$fanyong), 2);
                    if($fx_info && $fxsid != 0 && $isfxs == 1){
                        if($fx_info ->fx_cj != 4 && $fx_info ->one_bili != 0){  //总平台，小程序所有者，分销推广者参数与分佣
                            $one_bili = $fx_info ->one_bili / 100;
                            $new['applet_fanyong'] = round(($new['promotion_amount'] - $new['pintai_fanyong']) * (1-$one_bili), 2);
                            $new['fxs_fanyong'] = round($new['promotion_amount'] - $new['pintai_fanyong'] - $new['applet_fanyong'], 2);
                        }else{
                            $new['applet_fanyong'] = round($new['promotion_amount'] - $new['pintai_fanyong'], 2);
                            $new['fxs_fanyong'] = 0;
                        }
                    }else{  //只有总平台与小程序所有者两者参数与分佣
                        $new['applet_fanyong'] = round($new['promotion_amount'] - $new['pintai_fanyong'], 2);
                        $new['fxs_fanyong'] = 0;
                    }
                    $orders[$value['order_sn']] = $new;

                    //处理分销，佣金流水等
                    if($value['order_status'] == 1){  //订单付款生成，创建流水
                        $this ->createFanYongLs($uniacid, $new['order_sn'], $new['fxsid'], $new['applet_fanyong'], $new['fxs_fanyong'], $new['promotion_amount'], 'pdd', 1);
                    }

                    if($value['order_status'] == 2){  //确认收货  改变订单状态
                        $app_ls ->where(['uniacid'=>$uniacid, 'order_sn'=>$new['order_sn']]) ->update(['order_status'=>2 ]);
                    }

                    if($value['order_status'] == 4){  //审核失败，分佣取消
                        $app_ls ->where(['uniacid'=>$uniacid, 'order_sn'=>$new['order_sn']]) ->update(['order_status'=>4 ]);
                        $fx_ls ->where(['uniacid'=>$uniacid, 'order_id'=>$new['order_sn']]) ->update(['flag'=>3 ]);
                    }

                    if($value['order_status'] == 5){  //已结算 更新订单状态，结算佣金
                        $this -> doFxAndFanYong($uniacid, $new['order_sn'], $new['fxsid'], $new['applet_fanyong'], $new['fxs_fanyong']);
                    }
                }

            }
            //插入或更新数据库
            foreach ($orders as $k => $v){
                $has = $order ->get(['order_sn' => $k]);
                if($has){
                    if($has->order_modify_at != $v['order_modify_at']){
                        $data = [
                            'order_status' => $v['order_status'],
                            'order_modify_at' => $v['order_modify_at'],
                        ];
                        $order ->where('order_sn', $has->order_sn) ->update($data);
                    }
                }else{
                    $res = $order ->insert($v);
                }
            }
            $this->success('订单同步成功！', Url('External/orderlist') . '?appletid=' . $uniacid);

        }else{
            $this->error('暂无可同步的订单！');
        }

    }

    /**  订单创建 生成流水记录
     * @param $uniacid
     * @param $order_sn  订单号
     * @param $fxsid  分销商ID
     * @param $applet_fanyong   小程序平台返佣金额
     * @param $fxs_fanyong   分销商返佣金额
     */
    private function createFanYongLs($uniacid, $order_sn, $fxsid, $applet_fanyong, $fxs_fanyong, $promotion_amount, $type, $status){
        if($type == 'pdd'){
            $app_falg = 1;
            $fx_falg = 1;
        }else{
            if($status == 4){
                $app_falg = 4;
                $fx_falg = 3;
            }else{
                $app_falg = $status;
                $fx_falg = 1;
            }
        }

        if($applet_fanyong){   //小程序平台流水记录
            $app_ls = new FanyongLs();
            $has = $app_ls ->get(['uniacid'=>$uniacid, 'order_sn' => $order_sn]);
            if(!$has){
                $app_ls_date = [
                    'uniacid' => $uniacid,
                    'order_sn' => $order_sn,
                    'type' => $type,
                    'fanyong' => $applet_fanyong,
                    'promotion_amount' => $promotion_amount,
                    'fxsid' => $fxsid,
                    'createtime' => time(),
                    'order_status' => $app_falg
                ];
                $app_ls -> save($app_ls_date);
            }
        }

        if($fxs_fanyong){
            $fx_ls = new FxLs();
            $ls = $fx_ls ->get(['uniacid'=>$uniacid, 'order_id'=>$order_sn]);
            if(!$ls){
                $lsdata = array(
                    "uniacid" => $uniacid,
                    "suid" => $fxsid,
                    "parent_id" => $fxsid,
                    "parent_id_get" => $fxs_fanyong,
                    "p_parent_id" => 0,
                    "p_parent_id_get" => 0,
                    "p_p_parent_id" => 0,
                    "p_p_parent_id_get" => 0,
                    "order_id" => $order_sn,
                    "source" => 1,
                    "creattime" => time(),
                    'type' => 'ext|'.$type,
                    'flag' => $fx_falg
                );
                $fx_ls ->save($lsdata);
            }
        }
    }


    /**   订单结算 佣金到账
     * @param $uniacid
     * @param $order_sn  订单号
     * @param $fxsid   分销商
     * @param $applet_fanyong  小程序平台返佣
     * @param $fxs_fanyong   分销商返佣
     */
    private function doFxAndFanYong($uniacid, $order_sn, $fxsid, $applet_fanyong, $fxs_fanyong){
        $app = new Applet;
        $appinfo = $app->getAppInfo();

        //将佣金更新到小程序平台
        if($applet_fanyong){
            $app_ls = new FanyongLs();
            $has = $app_ls -> get(['uniacid'=>$uniacid, 'order_sn' => $order_sn, 'order_status'=> ['NEQ', 5] ]);
            if($has){
                $pro_data = [
                    'fy_all_money' => $appinfo->fy_all_money + $applet_fanyong,
                    'fy_tx_money' => $appinfo->fy_tx_money + $applet_fanyong,
                ];
                $app->save($pro_data, ['id'=>$uniacid]);

                //更新流水状态
                if($has ->type == 'pdd'){
                    $app_ls ->where(['uniacid'=>$uniacid, 'order_sn'=>$order_sn]) ->update(['order_status'=>5 ]);
                }else{
                    $app_ls ->where(['uniacid'=>$uniacid, 'order_sn'=>$order_sn]) ->update(['order_status'=>18 ]);
                }

            }

        }

        //佣金更新到分销商
        if($fxs_fanyong){
            $user = new User();
            $fx_ls = new FxLs();
            $ls = $fx_ls ->get(['uniacid'=>$uniacid, 'order_id'=>$order_sn, 'flag' => ['NEQ', 2]]);
            if(!$ls){
                $userInfo = $user ->get($fxsid);
                $fx_data = [
                    'fx_allmoney' => $userInfo ->fx_allmoney + $fxs_fanyong,
                    'fx_money' => $userInfo ->fx_money + $fxs_fanyong,
                ];
                $user ->save($fx_data, ['id'=>$userInfo->id]);
                //更新流水状态
                $fx_ls ->where(['uniacid'=>$uniacid, 'order_id'=>$order_sn]) ->update(['flag'=>2 ]);
            }
        }
    }


    /*平台分佣收入流水*/
    public function moneyIn(){
        if (check_login()) {
            if (powerget()) {
                $uniacid = input('appletid');
                $app = new Applet;
                $appinfo = $app->getAppInfo();
                $this->assign('applet', $appinfo);

                $ls = new FanyongLs();
                $lsData = $ls ->getAllLs();
                $lsData = $lsData ->each(function ($item) use ($uniacid){
                    if($item ->fxsid){
                        $info = getNameAvatar($item ->fxsid, $uniacid);
                        $item ->fxs_nickname = $info['nickname'];
                        $item ->fxs_avatar = $info['avatar'];
                    }
                });
                $count = $ls ->where('uniacid', $uniacid) ->count();
                $has = false;
                if(count($lsData->toArray()['data'])>0){
                    $has = true;
                }
                $this->assign('lsData', $lsData);
                $this->assign('has', $has);
                $this->assign('count', $count);

                return $this->fetch('money_in');

            } else {
                $usergroup = Session::get('usergroup');
                if ($usergroup == 1) {
                    $this->error("您没有权限操作该小程序或找不到相应小程序！", 'Applet/applet');
                }
                if ($usergroup == 2) {
                    $this->error("您没有权限操作该小程序或找不到相应小程序！", 'Applet/index');
                }
                if ($usergroup == 3) {
                    $this->error("您没有权限操作该小程序或找不到相应小程序！", 'Applet/index');
                }
            }
        } else {
            $this->redirect('Login/index');
        }
    }


    /*提现记录*/
    public function moneyOut(){
        if (check_login()) {
            if (powerget()) {
                $uniacid = input('appletid');
                $app = new Applet;
                $appinfo = $app->getAppInfo();
                $this->assign('applet', $appinfo);

                $tx = new FxTx();
                $tx_data = $tx ->getAll();

                $count = $tx ->where('uniacid', $uniacid) ->count();
                $has = false;
                if(count($tx_data->toArray()['data'])>0){
                    $has = true;
                }
                $this->assign('has', $has);
                $this->assign('txData', $tx_data);
                $this->assign('count', $count);

                return $this->fetch('money_out');

            } else {
                $usergroup = Session::get('usergroup');
                if ($usergroup == 1) {
                    $this->error("您没有权限操作该小程序或找不到相应小程序！", 'Applet/applet');
                }
                if ($usergroup == 2) {
                    $this->error("您没有权限操作该小程序或找不到相应小程序！", 'Applet/index');
                }
                if ($usergroup == 3) {
                    $this->error("您没有权限操作该小程序或找不到相应小程序！", 'Applet/index');
                }
            }
        } else {
            $this->redirect('Login/index');
        }
    }

    /*提现申请*/
    public function fenYongTx(){
        if (check_login()) {
            if (powerget()) {
                $uniacid = input('appletid');
                $tx_money = input('tx_money');
                $zfbzh = input('zfbzh');
                $zfbxm = input('zfbxm');

                $app = new Applet;
                $appinfo = $app->getAppInfo();

                if(!$tx_money){
                    $this->error('请输入提现金额！');
                }else{
                    if(is_numeric($tx_money)){
                        if($tx_money*1 > $appinfo->fy_tx_money){
                            $this->error('可提现金额不足，请重新输入！');
                        }
                    }else{
                        $this->error('请输入正确的提现金额！');
                    }
                }

                if(!$zfbzh){
                    $this->error('请输入支付宝账号！');
                }

                if(!$zfbxm){
                    $this->error('请输入账号姓名！');
                }

                $data = [
                    'uniacid' => $uniacid,
                    'types' => 3,
                    'money' => $tx_money,
                    'zfbzh' => $zfbzh,
                    'zfbxm' => $zfbxm,
                    'createtime' => time()
                ];
                $tx = new FxTx();
                $res = $tx ->save($data);
                if($res){
                    //更新可提现余额
                    $yu_money = $appinfo->fy_tx_money - $tx_money;
                    $app ->save(['fy_tx_money'=>$yu_money], ['id'=>$uniacid]);
                    $this->success('提现申请提交成功！');
                }else{
                    $this->success('提交失败，请稍后再试！');
                }

            } else {
                $usergroup = Session::get('usergroup');
                if ($usergroup == 1) {
                    $this->error("您没有权限操作该小程序或找不到相应小程序！", 'Applet/applet');
                }
                if ($usergroup == 2) {
                    $this->error("您没有权限操作该小程序或找不到相应小程序！", 'Applet/index');
                }
                if ($usergroup == 3) {
                    $this->error("您没有权限操作该小程序或找不到相应小程序！", 'Applet/index');
                }
            }
        } else {
            $this->redirect('Login/index');
        }


    }

    /*更新商品信息*/
    public function updateGoods(){
        include_once 'pdd.php';
        include_once 'jd.php';
        $uniacid = input('appletid');
        $pdd = new \pdd($uniacid);
        $jd = new \jd($uniacid);
        $uniacid = input('appletid');
        $external_goods = new ExternalGoods();
        $pdd_ids = $external_goods  ->getGoodIds($uniacid, 'pdd');
        $jd_ids = $external_goods  ->getGoodIds($uniacid, 'jd');
        if(count($pdd_ids) > 0){
            foreach ($pdd_ids as $k=>$v){
                $ginfo = $pdd ->getGoodsDetail($v);
                if($ginfo){
                    $new = array(
                        'name' => $ginfo['goods_name'],
                        'goods_thumbnail_url' => $ginfo['goods_thumbnail_url'],
                        'goods_id' => $ginfo['goods_id'],
                        'coupon_remain_quantity' => $ginfo['coupon_remain_quantity'],
                        'min_group_price' => $ginfo['min_group_price']/100,   //最小拼团价
                        'sold_quantity' => $ginfo['sales_tip'],
                        'coupon_discount' => $ginfo['coupon_discount']/100,   //优惠券金额
                        'mall_name' => $ginfo['mall_name'],
                        'coupon_start_time' => $ginfo['coupon_start_time'],
                        'coupon_end_time' => $ginfo['coupon_end_time'],
                        'promotion_rate' => $ginfo['promotion_rate'],
                        'goods_eval_score' => isset($ginfo['goods_eval_score']) ? $ginfo['goods_eval_score'] : ''
                    );
                    $external_goods ->where('goods_id', $v) ->update($new);
                }else{
                    $external_goods ->where('goods_id', $v) ->delete();
                }
            }
        }

        if(count($jd_ids) > 0){
            foreach ($jd_ids as $k=>$v){
                $ginfo = $jd ->getGoodsDetail($v);
                if($ginfo){
                    $new = array(
                        'name' => $ginfo['goodsName'],
                        // 'goods_thumbnail_url' => $ginfo['imgUrl'],
                        'min_group_price' => $ginfo['wlUnitPrice'],   //最小拼团价
                        'sold_quantity' => $ginfo['inOrderCount'],
                        'promotion_rate' => $ginfo['commisionRatioWl'],
                    );
                    $external_goods ->where('goods_id', $v) ->update($new);
                }else{
                    $external_goods ->where('goods_id', $v) ->delete();
                }
            }
        }

        $this->success('更新成功！');

    }


    /*每分钟定时查询京东新的订单*/
    public function getJdOrder(){
        //获取所有项目的推广位于uniacid
        $jd_order = [];
        include_once 'jd.php';
        $jd = new \jd(-1);
        $pn = 1;
        $time = date('YmdHi', time() - 600);
        while(count($res = $jd->getOrderList($pn, $time)['data'])>499){
            $pn = $pn + 1;
            $jd_order = array_merge($jd_order, $res);
        }
        $jd_order = array_merge($jd_order, $res);
        if(count($jd_order) == 0){
            return;
        }
        $app = new Applet;
        $user = new User;
        $info = $app ->getJdPid();
        $order = new ExternalOrder;
        $fxgz = new FxGz();
        foreach($jd_order as $k => $jd){
            $goods = $jd['skuList'][0];
            if(isset($info[$goods['positionId']])){
                $uniacid = $info[$goods['positionId']];
            }
            $fx_info = $fxgz ->get(['uniacid'=>$uniacid]);
            $appInfo = $app ->get($uniacid);
            $fanyong = round($appInfo ->fanyong / 100, 2);
            $fxsid = explode('|', $jd['ext1'])[1];
            if($fxsid != 0){
            	$fxuser = $user ->get($fxsid);
            	if($fxuser['fxs'] == 2){
            		$isfxs = 1;
            	}else{
            		$isfxs = 0;
            	}
            }else{
            	$isfxs = 0;
            }
            $new = [
                'uniacid' => $uniacid,
                'p_id' => $goods['positionId'],
                'order_sn' => $jd['orderId'],
                'goods_id' => $goods['skuId'],
                'goods_name' => $goods['skuName'],
                'goods_thumbnail_url' => '',
                'goods_quantity' => 0,
                'goods_price' => $goods['price'],
                'goods_quantity' => $goods['skuNum'],
                'order_amount' => $goods['estimateCosPrice'],
                'promotion_rate' => round($goods['commissionRate'] * $goods['finalRate'] / 100, 2),
                'promotion_amount' => $goods['estimateFee'],
                'order_status' => $goods['validCode'],
                'order_modify_at' => 0,
                'order_create_time' => $jd['orderTime']/1000,
                'fxsid' => $fxsid,
                'type' => 'jd'
            ];
            //总平台佣金
            $new['pintai_fanyong'] = round($new['promotion_amount'] * (1-$fanyong), 2);
            //小程序平台与分销商
            if($fxsid && $fx_info && $isfxs){
                if($fx_info ->fx_cj != 4 && $fx_info ->one_bili != 0){  //总平台，小程序所有者，分销推广者参数与分佣
                    $one_bili = $fx_info ->one_bili / 100;
                    $new['applet_fanyong'] = round(($new['promotion_amount'] - $new['pintai_fanyong']) * (1-$one_bili), 2);
                    $new['fxs_fanyong'] = round($new['promotion_amount'] - $new['pintai_fanyong'] - $new['applet_fanyong'], 2);
                }else{
                    $new['applet_fanyong'] = round($new['promotion_amount'] - $new['pintai_fanyong'], 2);
                    $new['fxs_fanyong'] = 0;
                }
            }else{
                $new['applet_fanyong'] = round($new['promotion_amount'] - $new['pintai_fanyong'], 2);
                $new['fxs_fanyong'] = 0;
            }
            $has = $order ->get(['order_sn' => $new['order_sn']]);
            if(!$has){
                $order ->insert($new);
            }
            if($new['order_status'] < 15){
                $status = 4;
            }else{
                $status = $new['order_status'];
            }
            //处理分销，佣金流水等
            $this ->createFanYongLs($uniacid, $new['order_sn'], $new['fxsid'], $new['applet_fanyong'], $new['fxs_fanyong'], $new['promotion_amount'], 'jd', $status);

        }
    }


    //定时更新订单状态   定时一个小时
    public function updateExternalOrders(){
        $order = new ExternalOrder;

        include_once 'jd.php';
        $jd = new \jd(-1);
        include_once 'pdd.php';
        $pdd = new \pdd(-1);

        $app_ls = new FanyongLs();
        $fx_ls = new FxLs();
        $end_get = time();
        $start_get = $end_get - 3600;  //一个小时
        $p_order_info = $pdd ->getOrderList($start_get, $end_get);  //拼多多订单

        if(count($p_order_info) > 0){
            $this -> doPddOrders($p_order_info);
        }
        $orders = $order ->getJdOrders();   //京东订单
        if($orders){
            foreach($orders as $k => $v){
                $search_time = date('YmdHi', $v->order_create_time);
                $jd_order = $jd -> getOrderList(1, $search_time)['data'];
                if(count($jd_order)>0){
                    foreach($jd_order as $k1 => $jd_v){
                        if($jd_v['validCode'] < 15){
                            $status = 4;
                        }else{
                            $status = $jd_v['validCode'];
                        }
                        $order ->where('order_sn', $jd_v['orderId']) ->where('order_status', 'not in', '4, 18') ->update(['order_status'=> $status]);
                        $order_info = $order ->get(['order_sn' => $jd_v['orderId']]);
                        if($order_info['order_status'] == 16){
                            //处理分销，佣金流水等
                            $this ->createFanYongLs($order_info['uniacid'], $order_info['order_sn'], $order_info['fxsid'], $order_info['applet_fanyong'], $order_info['fxs_fanyong'], $order_info['promotion_amount'], 'jd', $status);
                        }

                        if($order_info['order_status'] == 17){  //确认收货  改变订单状态
                            $app_ls ->where(['order_sn'=>$order_info['order_sn']]) ->update(['order_status'=>17 ]);
                        }

                        if($order_info['order_status'] < 15){  //无效订单，分佣取消
                            $app_ls ->where(['order_sn'=>$order_info['order_sn']]) ->update(['order_status'=>4 ]);
                            $fx_ls ->where(['order_id'=>$order_info['order_sn']]) ->update(['flag'=>3 ]);
                        }

                        if($order_info['order_status'] == 18){  //已结算 更新订单状态，结算佣金
                            $this -> doFxAndFanYong($order_info['uniacid'], $order_info['order_sn'], $order_info['fxsid'], $order_info['applet_fanyong'], $order_info['fxs_fanyong']);
                        }
                    }
                }
            }
        }

    }


    //定时更新中处理拼多多订单
    public function doPddOrders($orders){
        $order = new ExternalOrder;
        $app = new Applet;
        $user = new User;
        $fxgz = new FxGz();
        $app_ls = new FanyongLs();
        $fx_ls = new FxLs();
        foreach ($orders as $k => $value){
            $fxsid = $value['custom_parameters'];
            $fxsid = substr($fxsid, 7);
            $fxsid = substr($fxsid, 0, -1);
            $uniacid = $app ->get(['p_id' => $value['p_id']])['id'];
            $fx_info = $fxgz ->get(['uniacid'=>$uniacid]);
            $appInfo = $app ->get($uniacid);
            $fanyong = round($appInfo ->fanyong / 100, 2);
            if($fxsid != 0){
            	$fxuser = $user ->get($fxsid);
            	if($fxuser['fxs'] == 2){
            		$isfxs = 1;
            	}else{
            		$isfxs = 0;
            	}
            }else{
            	$isfxs = 0;
            }
            $new = [
                'uniacid' => $uniacid,
                'p_id' => $value['p_id'],
                'order_sn' => $value['order_sn'],
                'goods_id' => $value['goods_id'],
                'goods_name' => $value['goods_name'],
                'goods_thumbnail_url' => $value['goods_thumbnail_url'],
                'goods_quantity' => $value['goods_quantity'],
                'goods_price' => $value['goods_price']/100,
                'order_amount' => $value['order_amount']/100,
                'promotion_rate' => $value['promotion_rate'],
                'promotion_amount' => $value['promotion_amount']/100,
                'order_status' => $value['order_status'],
                'order_create_time' => $value['order_create_time'],
                'order_modify_at' => $value['order_modify_at'],
                'fxsid' => $fxsid,
                'type' => 'pdd',
            ];
            //总平台佣金
            $new['pintai_fanyong'] = round($new['promotion_amount'] * (1 -$fanyong), 2);
            if($fx_info && $fxsid != 0 && $isfxs){
                if($fx_info ->fx_cj != 4 && $fx_info ->one_bili != 0){  //总平台，小程序所有者，分销推广者参数与分佣
                    $one_bili = $fx_info ->one_bili / 100;
                    $new['applet_fanyong'] = round(($new['promotion_amount'] - $new['pintai_fanyong']) * (1-$one_bili), 2);
                    $new['fxs_fanyong'] = round($new['promotion_amount'] - $new['pintai_fanyong'] - $new['applet_fanyong'], 2);
                }else{
                    $new['applet_fanyong'] = round($new['promotion_amount'] - $new['pintai_fanyong'], 2);
                    $new['fxs_fanyong'] = 0;
                }
            }else{  //只有总平台与小程序所有者两者参数与分佣
                $new['applet_fanyong'] = round($new['promotion_amount'] - $new['pintai_fanyong'], 2);
                $new['fxs_fanyong'] = 0;
            }
            $orders[$value['order_sn']] = $new;

            //处理分销，佣金流水等
            if($value['order_status'] == 1){  //订单付款生成，创建流水
                $this ->createFanYongLs($uniacid, $new['order_sn'], $new['fxsid'], $new['applet_fanyong'], $new['fxs_fanyong'], $new['promotion_amount'], 'pdd', 1);
            }

            if($value['order_status'] == 2){  //确认收货  改变订单状态
                $app_ls ->where(['uniacid'=>$uniacid, 'order_sn'=>$new['order_sn']]) ->update(['order_status'=>2 ]);
            }

            if($value['order_status'] == 4){  //审核失败，分佣取消
                $app_ls ->where(['uniacid'=>$uniacid, 'order_sn'=>$new['order_sn']]) ->update(['order_status'=>4 ]);
                $fx_ls ->where(['uniacid'=>$uniacid, 'order_id'=>$new['order_sn']]) ->update(['flag'=>3 ]);
            }

            if($value['order_status'] == 5){  //已结算 更新订单状态，结算佣金
                $this -> doFxAndFanYong($uniacid, $new['order_sn'], $new['fxsid'], $new['applet_fanyong'], $new['fxs_fanyong']);
            }

            $info = $order->get(['order_sn'=>$value['order_sn']]);
            if($info){
                $data = [
                    'order_status' => $value['order_status'],
                    'order_modify_at' => $value['order_modify_at'],
                ];
                $order ->where('order_sn', $info->order_sn) ->update($data);
            }else{
                $order ->insert($new);
            }
        }
    }
}