<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;
use think\Cookie;
class Bizlogin extends Controller
{
    public function index(){
        $shopid = Cookie::get("venue_id");  //商户id
        $id = Cookie::get("uniacid");  //uniacid
        $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
        if(!$res){
            $this->error("找不到对应的小程序！");
        }
        $this->assign('applet',$res);
        $this->assign('page',1); //页面id
        return $this->fetch('index');
    }
    public function goods(){
        $id = input("appletid");  //uniacid
        $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
        if(!$res){
            $this->error("找不到对应的小程序！");
        }
        $this->assign("applet",$res);
        $shopid = Cookie::get("venue_id");  //店铺id
        // var_dump($_COOKIE['venue_id']);  另一种获取cookie的方法
        $goods = Db::name("wd_xcx_shops_goods")->where("uniacid",$id)->where("sid",$shopid)->order("createtime desc")->paginate(10,false,['query' => array('appletid' => input("appletid"))]);
        $count = Db::name("wd_xcx_shops_goods")->where("uniacid",$id)->where("sid",$shopid)->order("createtime desc")->count();
        if($goods->toArray()){
            $products = $goods->toArray()['data'];
        }
        foreach ($products as $key => &$value) {
            $value['thumb'] = remote($id,$value['thumb'],1);
        }
        $this->assign('goods',$goods);
        $this->assign('goodslist',$products);
        $this->assign('counts',$count);
        $this->assign('page',2); //页面id
        return $this->fetch('goods');
    }
    //审核商品通过
    public function goodspass(){
        $pid = intval(input("goodsid"));
        $appletid = input("appletid");
        $data = array(
            "uniacid" =>$appletid,
            "id" => $pid,
            "status" => 1
        );
        $res = Db::name('wd_xcx_shops_goods')->where("uniacid",$appletid)->where("id",$pid)->update($data);
        if($res){
            $this->success("审核通过");
        }else{
            $this->success("审核失败");
        }
    }
    //审核商品不通过
    public function goodscancel(){
        $pid = intval(input("goodsid"));
        $appletid = input("appletid");
        $data = array(
            "uniacid" =>$appletid,
            "id" => $pid,
            "status" => 2
        );
        $res = Db::name('wd_xcx_shops_goods')->where("uniacid",$appletid)->where("id",$pid)->update($data);
        if($res){
            $this->success("审核不通过");
        }else{
            $this->success("审核失败");
        }
    }
    //新增商品
    public function goodsadd(){
        $appletid = input("appletid");
        $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
        if(!$res){
            $this->error("找不到对应的小程序！");
        }
        $this->assign('applet',$res);
        $goodsid = input("goodsid");
        $shopid = Cookie::get("venue_id");
        $goodsinfo = '';
        if($goodsid){
            $goods = Db::name('wd_xcx_shops_goods')->where("uniacid",$appletid)->where("id",$goodsid)->find();
            if($goods['images']){
                $goods['images'] = unserialize($goods['images']);
                if($goods['images']){
                    foreach ($goods['images'] as $key => &$value) {
                        $value = remote($appletid,$value,1);
                    }
                }
            }
        }else{
            $goods = "";
        }
        $listX = Db::name('wd_xcx_goods_cate')->where("flag",1)->where("uniacid",$appletid)->where("cid",0)->order('num desc')->select();
        $listY = array();
        foreach($listX as $key=>$val) {
            $id = intval($val['id']);
            $listP = Db::name('wd_xcx_goods_cate')->where("flag",1)->where("uniacid",$appletid)->where("id",$id)->order('num desc')->select();
            $listS = Db::name('wd_xcx_goods_cate')->where("flag",1)->where("uniacid",$appletid)->where("cid",$id)->order('num desc')->select();
//                    //子集数据量
            $zjcount = Db::name('wd_xcx_goods_cate')->where("flag",1)->where("uniacid",$appletid)->where("cid",$id)->order('num desc')->count();
            $listP['data'] = $listS;
            $listP['zcount'] = $zjcount;
            array_push($listY,$listP);
        }

        //获取所有表单
        $forms = Db::name('wd_xcx_formlist') ->where('uniacid', $appletid) ->select();
        $this->assign('forms', $forms);

        $this->assign('cates',$listY);
        $this->assign('goodsid',$goodsid);
        $this->assign('page',2); //页面id
        $this ->assign('goods',$goods);
        $this->assign('shopid',$shopid);
        return $this->fetch('goodsadd');
    }
    //提交商品信息
    public function goodssave(){
       $appletid = input("appletid");
        $pid = input("pid");
        $sid = input("sid");
        $flag = input("flag");
        if(!$sid){
            $this ->error('请重新登录!');
        }
        if(!$flag){
            $flag = 0;
        }
        $hot = input("hot");
        if(!$hot){
            $hot = 0;
        }
        $pcid = Db::name('wd_xcx_shops_shop')->where('uniacid',$appletid)->where('id',$sid)->find();
        if($pcid){
            $data= array(
                "uniacid" => $appletid,
                "sid" => input('sid'),
                "num" => input('num'),
                "flag" => $flag,
                "hot" => $hot,
                "title" => input('title'),
                "buy_type" => input('buy_type'),
                "pageview" => input('pageview'),
                "vsales" => input('vsales'),
                "rsales" => input('rsales'),
                "sellprice" => input('sellprice'),
                "marketprice" => input('marketprice'),
                "storage" => input('storage'),
                "kuaidi"=>input("kuaidi"),
                "cid"=>input("cid"),
                "descp" => input("descp"),
                "descs" => input('descs'),
                'formset' => input('formset'),
                'formset' => input('formset'),
                'video' => input('video')
            );
        }

        $good_set = Db::name('wd_xcx_shops_set') ->where('uniacid', $appletid) ->field('goods') ->find();
        if(!$good_set){
            $data['status'] = 0;
        }else{
            $data['status'] = ($good_set['goods'] == '1') ? 0 : 1;
        }

        $imgsrcs = input("imgsrcs/a");
        if($imgsrcs){
            foreach ($imgsrcs as $key => &$value) {
                $value = moveurl(remote($appletid,$value,1));
            }
            $data['images'] = serialize($imgsrcs);
        }else{
            $data['images'] = [];
        }
        $thumb = input("commonuploadpic1");
        if($thumb){
           $data['thumb'] = moveurl(remote($appletid,$thumb,1));
        }
        $goodsid = input("goodsid");
        if($goodsid){
            $res = Db::name('wd_xcx_shops_goods')->where("uniacid",$appletid)->where("id",$goodsid)->update($data);
        }else{
            //查看是否需要审核
            $conf = Db::name('wd_xcx_shops_set') ->where('uniacid', $appletid) ->field('goods') ->find();
            if($conf){
                if($conf['goods'] == 2){
                    $data['status'] = 1;
                }
            }
            $data['uniacid'] = $appletid;
            $res = Db::name("wd_xcx_shops_goods")->insert($data);
        }
        if($res){
            $this->success('商品信息更新成功！');
        }
    }
    //删除商品
    public function goodsdel(){
        $appletid = input("appletid");
        $pid = input("goodsid");
        $data = array(
            "uniacid"=>$appletid,
            "id"=>$pid
        );
        $res = Db::name('wd_xcx_shops_goods')->where($data)->delete();
        if($res){
            $this->success('商品删除成功');
        }else{
            $this->success('商品删除失败');
        }
    }
    public function order(){
        $appletid = input("appletid");
        $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
        if(!$res){
            $this->error("找不到对应的小程序！");
        }
        $this->assign('applet',$res);
        $op = input('op');
        $shopid = Cookie::get("venue_id");

        if($op == "hx"){  //核销
            $order = input('orderid');
            $shopid = input('shopid');
            $data['hxtime'] = time();
            $data['flag'] = 2;
            $res = Db::name('wd_xcx_duo_products_order')->where("id",$order)->update($data);
            if($shopid != '0'){
                $money = Db::name('wd_xcx_shops_shop')->where("uniacid",$appletid)->where("id",$shopid)->field("tixian")->find()['tixian'];
                $add = Db::name('wd_xcx_duo_products_order')->where("uniacid",$appletid)->where("id",$order)->field("price")->find()['price'];
            
                $money = $money + $add;
                $result = Db::name('wd_xcx_shops_shop')->where("uniacid",$appletid)->where("id",$shopid)->update(array('tixian' => $money));
            }
            if($res){
                $this->success("核销成功");
            }
        }

        if($op == 'refuseth' || $op == 'refuseqx'){  //拒绝退货   拒绝取消
            $orderid = input('orderid');
            $data['flag'] = $op == 'refuseqx' ? 1 : 9;
            model('ImsSudu8PageDuoProductsOrder') ->save($data, ['id'=>$orderid]);
            $this->success("拒绝退货成功");
        }

        if($op == 'quxiao' || $op == 'allowth' || $op == 'confirmtk'){   //取消订单  同意退货  同意取消订单
            $uniacid = input('appletid');
            $order_id=input("orderid");
            $orderObj = model('ImsSudu8PageDuoProductsOrder');
            $proObj = model('ImsSudu8PageShopsGoods');
            $userObj = model('ImsSudu8PageSuperuser');
            if(input('qxbeizhu')){
                $data['qxbeizhu'] = input('qxbeizhu');
            }
            $order = $orderObj->get($order_id);
            if(!$order){
                $this->error('该订单不存在！');
            }
            if ($order->flag == 5 || $order->flag == 8) {
                $this->error('订单状态不正确！');
            }
            $pids = unserialize($order->jsondata);
            $user = $userObj->get($order->suid);

            $now = time();
            $out_refund_no = date("Y",$now).date("m",$now).date("d",$now).date("H",$now).date("i",$now).date("s",$now).rand(1000,9999);

            Db::startTrans();
            try {
                //改变订单状态
                if($op == 'quxiao'){
                    $data['flag'] = 5;
                }else if($op == 'allowth'|| $op == 'confirmtk'){
                    $data['flag'] = 8;
                }

                $data['th_orderid'] = $out_refund_no;
                $orderObj->save($data, ['id' => $order_id]);

                //处理优惠券
                if ($order->coupon) {
                    $cou['flag'] = 0;
                    $cou['utime'] = 0;
                    model('ImsSudu8PageCouponUser')->save($cou, ['id' => $order->coupon]);
                }

                //处理库存与真实销量
                $order_product = $proObj->get($pids[0]['pid']);
                if ($pids[0]['num'] > 0) {   //更新销量
                    $newProData['rsales'] = $order_product->rsales - $pids[0]['num'];
                    $newProData['rsales'] = $newProData['rsales'] > 0 ? $newProData['rsales'] : 0;
                    $proObj->save($newProData, ['id' => $pids[0]['pid']]);
                }
                if ($order_product->storage != -1) { //有限量库存 更新库存
                    if ($pids[0]['num'] > 0) {
                        $proKc['storage'] = $order_product->storage + $pids[0]['num'];
                        $proObj->save($proKc, ['id' => $pids[0]['pid']]);
                    }
                }

                //处理退款
                $yuTk = $order->price - $order->payprice;
                if($yuTk > 0){    //处理余额
                    $userMoney = $user->money + $yuTk;
                    $userObj->save(['money' => $userMoney], ['id' => $order->suid]);

                    $xfmoney = array(
                        "uniacid" => $uniacid,
                        "orderid" => $order->order_id,
                        "suid" => $order->suid,
                        "type" => "add",
                        "score" => $yuTk,
                        "message" => "退款退回余额",
                        "creattime" => time()
                    );
                    model('ImsSudu8PageMoney') ->save($xfmoney);
                }

                if ($order->payprice > 0) {
                    $res = orderRefund($uniacid, $order_id, 'duo');
                    if($res['status'] == false){
                        throw new \Exception($res['msg']);
                    }
                }

                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                $this->error('取消失败，' . $e->getMessage());
            }

            $this->success('取消成功');
        }


        if($op == "fh"){  
            $order = input('orderid');
            $data['flag'] = 2;
            $res = Db::name('wd_xcx_duo_products_order')->where("id",$order)->update($data);
            if($res){
                $this->success("操作成功");
            }
        }
        if($op == "fahuo"){  //发货
            $order = input('orderid');
            $data['hxtime'] = time();
            $data['kuadi'] = input('kuaidi');
            $data['kuaidihao'] = input('kuaidihao');
            $data['flag'] = 4;
            $res = Db::name('wd_xcx_duo_products_order')->where("id",$order)->update($data);
            if($res){
                $this->success("发货成功");
            }
        }
        $total = Db::name('wd_xcx_duo_products_order')->where('uniacid',$appletid)->where("sid",$shopid)->order("creattime desc")->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
        $orders = $total->toArray()['data'];
        foreach ($orders as $key => &$res) {
            $res['jsondata'] = unserialize($res['jsondata']);
            $res['creattime'] = date("Y-m-d H:i:s",$res['creattime']);
            $res['hxtime'] = $res['hxtime'] == 0?"无核销信息":date("Y-m-d H:i:s",$res['hxtime']);
            $res['userinfo'] = Db::name('wd_xcx_user')->where('uniacid',$appletid)->where("openid",$res['openid'])->find();
            $res['counts'] = count($res['jsondata']);
            $coupon = Db::name('wd_xcx_coupon_user')->where('uniacid',$appletid)->where("id",$res['coupon'])->find();
            $couponinfo = Db::name('wd_xcx_coupon')->where('uniacid',$appletid)->where("id",$coupon['cid'])->find();
            $res['couponinfo'] = $couponinfo;
            if($res['sid'] == '0'){
                $res['shopname'] = '总平台';
            }else{
                $res['shopname'] = Db::name('wd_xcx_shops_shop')->where('uniacid',$appletid)->where("id",$res['sid'])->field('name')->find()['name'];
            }
            // 重新算总价
            $allprice = 0;
            foreach ($res['jsondata'] as $key2 => &$reb) {
                $allprice += ($reb['num']*1)*($reb['proinfo']['price']);
            }
            $res['allprice'] = $allprice;
            // 积分转钱
            //积分转换成金钱
            $jf_gz = Db::name('wd_xcx_rechargeconf')->where('uniacid',$appletid)->find();
            
            if(!$jf_gz){
                $gzscore = 10000;
                $gzmoney = 1;
            }else{
                $gzscore = $jf_gz['score'];
                $gzmoney = $jf_gz['money'];
            }
            $res['jfmoney'] = $res['jf']*$gzmoney/$gzscore;
            // 转换地址
            if($res['address']!=0){
                $res['address_get'] = Db::name('wd_xcx_duo_products_address')->where('openid',$res['openid'])->where('id',$res['address'])->find();
            }else{
                // dump($res['m_address']);die;
                $res['address_get'] = unserialize($res['m_address']);
            }
            if($res['formid']){
                $res['formcon'] = Db::name('wd_xcx_formcon')->where('uniacid',$appletid)->where('id',$res['formid'])->find();

                $res['formcon'] = unserialize($res['formcon']['val']);
                foreach ($res['formcon'] as $k => $vi) {
                    if(isset($vi['z_val'])){
                        foreach ($vi['z_val'] as $kv => $vv) {
                            if(strpos($vv,'http')===false){
                                $res['formcon'][$k]['z_val'][$kv] = remote($appletid,$vv,1);
                            }else{
                                $res['formcon'][$k]['z_val'][$kv] = $vv;
                            }
                        }
                    }
                }
            }
        }
        $this->assign('page',3); //页面id
        $this->assign('orders',$orders);
        $this->assign('total',$total);
        return $this->fetch('order');
    }

    public function fahuo(){
        $appletid = input("appletid");
        $order_id = input("orderid");
        $shopid = input('shopid');
        $data['hxtime'] = time();
        $data['flag'] = 2;
        Db::name('wd_xcx_duo_products_order')->where("uniacid",$appletid)->where("id",$order_id)->update($data);
        if($shopid != '0'){
            $money = Db::name('wd_xcx_shops_shop')->where("uniacid",$appletid)->where("id",$shopid)->find()['tixian'];
            $add = Db::name('wd_xcx_duo_products_order')->where("uniacid",$appletid)->where("id",$order_id)->find()['price'];
            $jiesuan = Db::name('wd_xcx_shops_set') ->where('uniacid', $appletid) ->find();
            $jiesuan = $jiesuan['jiesuan'];
            if (floatval($jiesuan) > 0) {
                $d = floatval($jiesuan) / 100;
                $c = $add * $d;
                $price = $add - $c;
                $price = round($price, 2);
                $money = $money + $price;
            }else{
                $money = $money + $add;
            }
            $result = Db::name('wd_xcx_shops_shop')->where("uniacid",$appletid)->where("id",$shopid)->update(array('tixian' => $money));
        }
        if($result){
            $this->success("核销成功");
        }
    }

    //提现申请
    public function tixian(){
        $appletid = input("appletid");
        $shopid = Session::get("shopuserid");
        $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
        if(!$res){
            $this->error("找不到对应的小程序！");
        }
        $this->assign('applet',$res);

        $set = Db::name('wd_xcx_shops_set') ->where('uniacid', $appletid) ->field('tixiantype, minimum') -> find();
        $type = explode(',', $set['tixiantype']);
        $this->assign('type', $type);
        $this->assign('minimum', $set['minimum']);

        $tixian = Db::name('wd_xcx_shops_shop') ->where('id', $shopid) ->field('tixian') ->find();
        $this->assign('tixian', $tixian['tixian']);

        $this->assign('page',4); //页面id

        return $this->fetch('tixian');
    }

    //提现申请提交
    public function txshenq(){
        $appletid = input('appletid');
        $type = input('type');
        $card = input('card');
        $account = input('account');
        $money = input('money');
        $shopid = Session::get("shopuserid");

        $set = Db::name('wd_xcx_shops_set') ->where('uniacid', $appletid) ->field('minimum') -> find();
        $tixian = Db::name('wd_xcx_shops_shop') ->where('id', $shopid) ->field('tixian') ->find();

        if(!$type){
            $this->error('请选择打款方式!');
        }else{
            $data['types'] = $type;
        }

        if($type == 3){
            if(!$card){
                $this->error('请输入银行卡开户行!');
            }else{
                $data['account'] = $card.':'.$account;
            }
        }else{
            if(!$account){
                $this->error('请输入账号!');
            }else{
                $data['account'] = $account;
            }

            if(!$money){
                $this->error('请输入提现金额!');
            }else{
                if(is_numeric($money)){
                    if($money > $tixian['tixian']){
                        $this->error('您的可提现额度不足, 请重新输入!');
                    }else if($money < $set['minimum']){
                        $this->error('您的提现金额低于最低提现额, 请重新输入!');
                    }else{
                        $data['money'] = $money;
                    }

                }else{
                    $this->error('请输入正确的提现金额!');
                }
            }
        }
        $data['uniacid'] = $appletid;
        $data['beizhu'] = input('beizhu');
        $data['sid'] = $shopid;
        $data['createtime'] = time();
        $data['flag'] = 0;
        $res = Db::name('wd_xcx_shops_tixian') ->insert($data);
        if($res){
            //更新可提现余额
            $tixian = $tixian['tixian'] - $money;
            Db::name('wd_xcx_shops_shop') ->where('id', $shopid) ->update(['tixian' => $tixian]);
            $this->success('提现申请提交成功!', Url('Bizlogin/withdraw').'?appletid='.$appletid);
        }else{
            $this->error('发生未知错误, 请稍后重试!');
        }
    }


    public function turnover(){
        $appletid = input("appletid");
        $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
        if(!$res){
            $this->error("找不到对应的小程序！");
        }
        $this->assign('applet',$res);
        $rule = Session::get('rule');
        $this->assign('rule',$rule);
        $sid = Session::get("shopuserid");
        $op = input('op');
        $shopname = Db::name('wd_xcx_shops_shop')->where('uniacid', $appletid)->where('id', $sid)->value('name');

        $Txrecords = Db::name('wd_xcx_shops_tixian') ->where('uniacid', $appletid) ->where('sid', $sid)->field('createtime as creattime, money as price, flag, types') ->order('createtime desc') ->select();
        foreach ($Txrecords as $k => $value) {
            $Txrecords[$k]['from'] = 'tx';
        }

        $Szrecords = Db::name('wd_xcx_duo_products_order') ->where('uniacid', $appletid) ->where('sid', $sid)->where('flag', 'in', [2]) ->field('price,creattime,flag') ->order('creattime desc') ->select();
        foreach ($Szrecords as $k => $value) {
            $Szrecords[$k]['from'] = 'buy';
        }
        $moneyArr = Db::name('wd_xcx_money')->where('uniacid', $appletid) ->where('sid', $sid)->order('creattime desc')->field('score as price,creattime')->select();
        foreach ($moneyArr as $k => $value) {
            $moneyArr[$k]['from'] = 'store';
        }
        $arr = array_merge($Szrecords, $moneyArr);
        $arr = array_merge($Txrecords, $arr);
        foreach ($arr as $s => $vs) {
            $creattime[] = $vs['creattime'];
        }
        if($arr){
            array_multisort($creattime, SORT_DESC, $arr);
        }

        if($op == 'excel'){
            require_once ROOT_PATH.'public/plugin/PHPExcel/PHPExcel.php';
            $objPHPExcel = new \PHPExcel();

            /*以下是一些设置*/
            $objPHPExcel->getProperties()->setCreator("多商户收支流水记录")
                ->setLastModifiedBy("多商户收支流水记录")
                ->setTitle("多商户收支流水记录")
                ->setSubject("多商户收支流水记录")
                ->setDescription("多商户收支流水记录")
                ->setKeywords("多商户收支流水记录")
                ->setCategory("多商户收支流水记录");
            $objPHPExcel->getActiveSheet()->setCellValue('A1', '商户名');
            $objPHPExcel->getActiveSheet()->setCellValue('B1', '金额(元)');
            $objPHPExcel->getActiveSheet()->setCellValue('C1', '时间');
            $objPHPExcel->getActiveSheet()->setCellValue('D1', '类型');
            $objPHPExcel->getActiveSheet()->setCellValue('E1', '说明');
            foreach ($arr as $key => &$res) {
                $num=$key+2;
                $res['creattime'] = date("Y-m-d H:i:s",$res['creattime']);
                if($res['from'] == 'store'){
                    $res['type'] = '收入';
                    $res['message'] = '店内支付';
                }else if($res['from'] == 'buy'){
                    $res['type'] = '收入';
                    $res['message'] = '售出商品';
                }else{
                    $res['type'] = '支出';
                    if($res['types'] == 1){
                        if($res['flag'] == 0){
                            $res['message'] = '提现到：微信(待审核)';
                        }else if($res['flag'] == 1){
                            $res['message'] = '提现到：微信(已通过)';
                        }else if($res['flag'] == 2){
                            $res['message'] = '提现到：微信(未通过)';
                        }
                    }else if($res['types'] == 2){
                        if($res['flag'] == 0){
                            $res['message'] = '提现到：支付宝(待审核)';
                        }else if($res['flag'] == 1){
                            $res['message'] = '提现到：支付宝(已通过)';
                        }else if($res['flag'] == 2){
                            $res['message'] = '提现到：支付宝(未通过)';
                        }
                    }else if($res['types'] == 3){
                        if($res['flag'] == 0){
                            $res['message'] = '提现到：银行卡(待审核)';
                        }else if($res['flag'] == 1){
                            $res['message'] = '提现到：银行卡(已通过)';
                        }else if($res['flag'] == 2){
                            $res['message'] = '提现到：银行卡(未通过)';
                        }
                    }
                }
                $objPHPExcel->setActiveSheetIndex(0)
                            ->setCellValueExplicit('A'.$num, $shopname,'s')
                            ->setCellValueExplicit('B'.$num, $res['price'],'s')
                            ->setCellValueExplicit('C'.$num, $res['creattime'],'s') 
                            ->setCellValueExplicit('D'.$num, $res['type'],'s')
                            ->setCellValueExplicit('E'.$num, $res['message'], 's');
                  
            }
            $objPHPExcel->getActiveSheet()->setTitle($shopname.'收支流水记录');
            $objPHPExcel->setActiveSheetIndex(0);
            $excelname=$shopname."收支流水记录";
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="'.$excelname.'.xls"');
            header('Cache-Control: max-age=0');
            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output');
            exit;
        }
        $this->assign('shopname', $shopname);
        $this->assign('arr', $arr);
        $this->assign('page',6); //页面id

        return $this->fetch('shoppay');  
    }


    //提现
    public function withdraw(){ 
 
        $appletid = input("appletid");
        $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
        if(!$res){
            $this->error("找不到对应的小程序！");
        }
        $this->assign('applet',$res);
        $records = Db::name('wd_xcx_shops_tixian')->where("uniacid",$appletid)->order("createtime desc")->select();
        foreach ($records as $key => &$value){
            $value['shopname'] = Db::name('wd_xcx_shops_shop')->where("uniacid",$appletid)->where("id",$value['sid'])->find()['name'];
        };
        $count = Db::name('wd_xcx_shops_tixian')->where("uniacid",$appletid)->count();
        // dump($records);die;
        $this->assign('records',$records);
        $this->assign('counts',$count);
        $this->assign('page',4); //页面id
        return $this->fetch('withdraw');
    }
    //提现审核
    public function withdrawpass(){
        $appletid = input("appletid");
        $id = input("id");
        $data = array(
            "flag" => 1
        );
        $res = Db::name('wd_xcx_shops_tixian')->where("uniacid",$appletid)->where("id",$id)->update($data);
        if($res){
            $this->success("审核成功");
        }
    }
    public function shopset(){
        // $appletid = input("appletid");
        // $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
        // if(!$res){
        //     $this->error("找不到对应的小程序！");
        // }
        $shopid = Cookie::get("venue_id");  //商户id
        $appletid = Cookie::get("uniacid");  //uniacid
        $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
        if(!$res){
            $this->error("找不到对应的小程序！");
        }
        $this->assign('applet',$res);
        $shopid = Cookie::get("venue_id");//店铺id
        $listV = Db::name('wd_xcx_shops_cate')->where("uniacid",$appletid)->order('num desc')->order('id desc')->select();
        $shopinfo = '';
        if($shopid){
            $shopinfo = Db::name('wd_xcx_shops_shop')->where("uniacid",$appletid)->where("id",$shopid)->find();
            if($shopinfo['images']){
                $shopinfo['images'] = unserialize($shopinfo['images']);
                if($shopinfo['images']){
                    foreach ($shopinfo['images'] as $key => &$value) {
                        $value = remote($appletid,$value,1);
                    }
                }
            }
        }else{
            $shopid = 0;
        }
        $this->assign('page',5); //页面id
        $this->assign('shopid',$shopid);
        $this->assign('shopinfo',$shopinfo);
        $this->assign('listAll',$listV);
        return $this->fetch("shopset");
    }



     //提交新添加的商户信息
    public function shopsave(){
        $appletid = input("appletid");
        $shopid = input("shopid");
        //判断修改时账号是否唯一
        $cid = input("cid");
        $latlong = input("latlong");
        if($latlong){
            $latlong = explode(",",$latlong);
            $latitude = $latlong[0];
            $longitude = $latlong[1];
        }else{
            $latitude = "";
            $longitude = "";
        }
        $password = input("password");
        $pcid = Db::name('wd_xcx_shops_cate')->where('uniacid',$appletid)->where('id',$cid)->find();
        if($pcid){
            $data= array(
                "uniacid" => $appletid,
                "cid" => input('cid'),
                "password" => $password,
                "intro" => input("intro"),
                "worktime" => input("worktime"),
                "name" => input("name"),
                "star" => input("star"),
                "tel" => input("tel"),
                "address" => input("address"),
                "latitude" => $latitude,
                "longitude" => $longitude,
                "title" => input("title"),
                "descp" => input("descp"),
            );
        }
        $imgsrcs = input("imgsrcs/a");
        if($imgsrcs){
            foreach ($imgsrcs as $key => &$value) {
                $value = remote($appletid,$value,1);
            }
            $data['images'] = serialize($imgsrcs);
        }else{
            $data['images'] = [];
        }
        //logo
        $logo = input("commonuploadpic1");
        if($logo){
           $data['logo'] = remote($appletid,$logo,2);
        }
        $bg = input("commonuploadpic2");
        if($bg){
            $data['bg'] = remote($appletid,$bg,2);
        }
        $yyzz = input("commonuploadpic3");
        if($yyzz){
            $data['yyzz'] = remote($appletid,$yyzz,2);
        }

        $shop = Db::name('wd_xcx_shops_shop')->where("uniacid",$appletid)->where("id",$shopid)->find();
        if($shop){
            $res = Db::name('wd_xcx_shops_shop')->where("uniacid",$appletid)->where("id",$shopid)->update($data);
        }
        if($res){
            if($password == $shop['password']){
                $this->success('店铺信息更新成功！');
            }else{
                $this->success('店铺信息更新成功,账号信息有更改，需重新登录！', 'index/login/bizlogin');
            }
        }else{
            $this->success('店铺信息更新失败！');
        }
    }
    public function getwuliu(){
        $uniacid = input('uniacid');
        $kuaidi = input('kuaidi');
        $kuaidihao = input('kuaidihao');
        //获取物流接口设置
        $set = Db::name('wd_xcx_duo_products_yunfei') ->where('uniacid', $uniacid) ->find();
        if($set['api_type'] == 3){
            if($set['appcode']){
                $res  = $this -> getAliwuliu($set['appcode'], $kuaidi, $kuaidihao);
                return $res;
                exit;
            }else{
                return json_encode(array('type'=>'ali', 'list'=>'', 'status'=> -1));
            }

        }

        $kd_code = array(
            '顺丰' => 'SF',
            '韵达' => 'YD',
            '天天' => 'HHTT',
            '申通' => 'HLWL',
            '圆通' => 'YTO',
            '中通' => 'ZTO',
            '国通' => 'GTO',
            '百世' => 'HTKY',
            'EMS'  => 'EMS',
            '邮政' => 'YZPY',
            'FEDEX联邦(国内件)' => 'FEDEX',
            '宅急送' => 'ZJS',
            '安捷快递' => 'AJ',
            '大田物流' => 'DTWL',
            '百福东方' => 'BFDF',
            '德邦快运' => 'DBLKY',
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
            '优速快递' => 'UC',
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

        include 'KdApi.php';


        if($kuaidi){
            $kuaidi = $kd_code[$kuaidi];
        }
        $kd = new KdApi();
        $res = $kd->getOrderTracesByJson($uniacid, $kuaidi, $kuaidihao);
        // $data['data'] = $res;
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
        return json_encode(array('type'=>'kdniao', 'list'=>$info, 'status'=> $status));
    }

    //阿里云市场上的物流查询
    public function getAliwuliu($appcode, $kuaidi, $kuaidihao){
        // $kuaidi = input('kuaidi');
        // $kuaidihao = input('kuaidihao');

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
        // $data = $_POST;
        $host = "https://wuliu.market.alicloudapi.com";//api访问链接
        $path = "/kdi";//API访问后缀
        $method = "GET";
        $appcode = $appcode;  //阿里云云市场购买的 appcode
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
        return json_encode(array('type'=>'ali', 'list'=>$info, 'status'=> $status));
    }
}