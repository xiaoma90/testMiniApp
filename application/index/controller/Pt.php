<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;
vendor('Qiniu.autoload');
use Qiniu\Auth as Auth;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;
class Pt extends Base
{
    public function set(){
    //退款代码
        $appletid = input("appletid");
        $app = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
        
        //微信
        // $t_arr = ['201906261553139883'];
        // include "WinXinRefund.php";
        // $SSLCERT_PATH = ROOT_PATH . 'public/Cert/' . $appletid . '/apiclient_cert.pem';//证书路径
        // $SSLKEY_PATH = ROOT_PATH . 'public/Cert/' . $appletid . '/apiclient_key.pem';//证书路径

        // $t_arr = explode(',', '201907011612393295,201907011616278912,201907011628077710');
        // foreach ($t_arr as $keys => $values) {
        //     $mchid = $app['mchid'];   //商户号
        //     $apiKey = $app['signkey'];    //商户的秘钥
        //     $appid = $app['appID'];                 //小程序的id
        //     $appkey = $app['appSecret'];            //小程序的秘钥
        //     $openid = 'openid';    //申请者的openid
        //     $outTradeNo = $values;
        //     $totalFee = 1;  //申请了提现多少钱
        //     $outRefundNo = $values; //商户订单号
        //     $refundFee = 1;  //申请了提现多少钱
        //     $opUserId = $mchid;//商户号
        //     $weixinpay = new WinXinRefund($openid, $outTradeNo, $totalFee, $outRefundNo, $refundFee, $SSLCERT_PATH, $SSLKEY_PATH, $opUserId, $appid, $apiKey);
        //     $return = $weixinpay->refund();
        //     var_dump($return);
        // }
        //     exit;
        
        // 支付宝
        // $t_arr = explode(',', '');
        // foreach ($t_arr as $key => $value) {
        //     Vendor('alipaysdk.aop.AopClient');
        //     Vendor('alipaysdk.aop.request.AlipayTradeRefundRequest');

        //     $aop = new \AopClient ();
        //     $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        //     $aop->appId = $app['ali_appID'];
        //     $aop->rsaPrivateKey = $app['ali_private_key'];
        //     $aop->alipayrsaPublicKey = $app['ali_public_key'];
        //     $aop->apiVersion = '1.0';
        //     $aop->signType = 'RSA2';
        //     $aop->postCharset = 'UTF-8';
        //     $aop->format = 'json';
        //     $request = new \AlipayTradeRefundRequest ();
        //     $request->setBizContent("{'refund_amount':0.01, 'out_trade_no': " . $value . "}");
        //     $result = $aop->execute($request);
        //     $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        //     $resultCode = $result->$responseNode->code;
        //     var_dump($resultCode);
        // }
        // exit;
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $pintuan = Db::name('wd_xcx_pt_gz')->where('uniacid',$appletid)->find();
                $this->assign('pintuan',$pintuan);
            }else{
                $usergroup = Session::get('usergroup');
                if($usergroup==1){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/applet');
                }
                if($usergroup==2){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/index');
                }
                if($usergroup==3){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/index');
                }

            }
            return $this->fetch('set');
        }else{
            $this->redirect('Login/index');
        }
    }
    function printf_info($data)
    {
        foreach($data as $key=>$value){
            echo "<font color='#f00;'>$key</font> : $value <br/>";
        }
    }
    //退款管理
    public function tuikuan(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $op = input('op');
                if($op){
                    if($op == "shenhe"){
                        $id = input('id');
                        $val = input('val');
                        $tx_query=Db::name("wd_xcx_pt_tx")->where("id",$id)->where("uniacid",$appletid)->find();
                        if($tx_query['flag'] != 1){
                            $this->error('操作失败，请勿重复操作！');
                        }
                        $order_query=Db::name("wd_xcx_pt_order")->where("order_id",$tx_query['ptorder'])->where("flag",5)->where("uniacid",$appletid)->find();
                        $yue_price = $order_query['yue_price'];
                        $wx_price = $order_query['wx_price'];

                        $user_info=Db::name("wd_xcx_superuser")->where("id",$tx_query['suid'])->where("uniacid",$appletid)->find();
                        if($val==2){
                            $return=array();
                            if($yue_price > 0){
                                $new_yue = $user_info['money'] + $yue_price;
                                $moneydata = array(
                                    "money" => $new_yue
                                );
                                Db::name("wd_xcx_superuser")->where("id",$user_info["id"])->update($moneydata);
                                Db::name("wd_xcx_pt_order")->where("id",$order_query['id'])->update(array('yue_price' => 0));
                            }    
                            if($wx_price > 0){
                                 $app = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                                 $sqtx = Db::name('wd_xcx_pt_tx')->where("uniacid",$appletid)->where("id",$id)->find();
                                if($order_query['paytype'] == 1){  //微信支付
                                    if($order_query['source'] == 1){
                                        $mchid = $app['mchid'];   //商户号
                                        $apiKey = $app['signkey'];    //商户的秘钥
                                        $appid = $app['appID'];                 //小程序的id
                                        $SSLCERT_PATH = ROOT_PATH . 'public/Cert/' . $appletid . '/apiclient_cert.pem';//证书路径
                                        $SSLKEY_PATH = ROOT_PATH . 'public/Cert/' . $appletid . '/apiclient_key.pem';//证书路径
                                    }elseif($order_query['source'] == 3){
                                        $mchid = $app['wx_h5_mchid'];   //商户号
                                        $apiKey = $app['wx_h5_signkey'];    //商户的秘钥
                                        $appid = $app['wx_h5_appid'];                 //小程序的id
                                        $SSLCERT_PATH = ROOT_PATH.'public/Cert/'.$appletid.'/h5_apiclient_cert.pem';//证书路径
                                        $SSLKEY_PATH =  ROOT_PATH.'public/Cert/'.$appletid.'/h5_apiclient_key.pem';//证书路径
                                    }elseif($order_query['source'] == 5){
                                        $mchid = $app['bdance_h5_mchid'];   //商户号
                                        $apiKey = $app['bdance_h5_signkey'];    //商户的秘钥
                                        $appid = $app['bdance_h5_appid'];                 //小程序的id
                                        $SSLCERT_PATH = ROOT_PATH.'public/Cert/'.$appletid.'/bdance_apiclient_cert.pem';//证书路径
                                        $SSLKEY_PATH =  ROOT_PATH.'public/Cert/'.$appletid.'/bdance_apiclient_key.pem';//证书路径
                                    }
                                    
                                    $appkey = $app['appSecret'];            //小程序的秘钥
                                    $openid = 'openid';    //申请者的openid
                                    $outTradeNo = $sqtx['ptorder'];
                                    $totalFee = $sqtx['money']*100;  //申请了提现多少钱
                                    $outRefundNo = $sqtx['ptorder']; //商户订单号
                                    $refundFee = $sqtx['money']*100;  //申请了提现多少钱
                                    
                                    $opUserId = $mchid;//商户号
                                    include "WinXinRefund.php";
                                    $weixinpay = new WinXinRefund($openid, $outTradeNo, $totalFee, $outRefundNo, $refundFee, $SSLCERT_PATH, $SSLKEY_PATH, $opUserId, $appid, $apiKey);
                                    $return = $weixinpay->refund();
                                    if (!$return) {
                                        throw new \Exception('微信退款失败， 请检查系统设置->微信小程序相关配置');
                                    }
                                }elseif ($order_query['paytype'] == 2){     //支付宝支付
                                    Vendor('alipaysdk.aop.AopClient');
                                    Vendor('alipaysdk.aop.request.AlipayTradeRefundRequest');

                                    $aop = new \AopClient ();
                                    $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
                                    $aop->appId = $app['ali_appID'];
                                    $aop->rsaPrivateKey = $app['ali_private_key'];
                                    $aop->alipayrsaPublicKey = $app['ali_public_key'];
                                    $aop->apiVersion = '1.0';
                                    $aop->signType = 'RSA2';
                                    $aop->postCharset = 'UTF-8';
                                    $aop->format = 'json';
                                    $request = new \AlipayTradeRefundRequest ();
                                    $request->setBizContent("{'refund_amount':" . $sqtx['money'] . ", 'out_trade_no': " . $sqtx['ptorder'] . "}");
                                    $result = $aop->execute($request);
                                    $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
                                    $resultCode = $result->$responseNode->code;
                                    if (!empty($resultCode) && $resultCode == 10000) {
                                        $return = true;
                                    } else {
                                        throw new \Exception('支付宝退款失败， 请检查系统设置->支付宝小程序设置');
                                    }
                                }elseif($order_query['paytype'] == 3){
                                     $pay_info = unserialize($order_query['pay_info']);
                                     require_once(ROOT_PATH.'application/api/controller/bdpay/Autoloader.php');
                                     $params = [
                                         'method' => 'nuomi.cashier.applyorderrefund',
                                         'orderId' => intval($pay_info['orderId']),
                                         'userId' => intval($pay_info['userId']),
                                         'refundType' => '1',
                                         'refundReason' => '订单退款',
                                         'tpOrderId' => $order_query['order_id'],
                                         'appKey' => $app['baidu_pay_appkey']
                                     ];
                                     $rsaSign = \NuomiRsaSign::genSignWithRsa($params, $app['baidu_private_key']);
                                     $params['rsaSign'] = $rsaSign;
                                     $url = 'https://nop.nuomi.com/nop/server/rest';
                                     $res = _Postrequest($url, http_build_query($params));
                                     $res = json_decode($res, true);
                                     if($res['errno'] == 0){
                                         $return = true;
                                     }else{
                                         $this->error('退款失败!请检查系统设置->百度小程序设置');exit;
                                     }
                                }elseif($order_query['paytype'] == 4){
                                    $pay_info = unserialize($order_query['pay_info']);
                                    $chars = "abcdefghijklmnopqrstuvwxyz0123456789";  
                                    $nonce_str = "";  
                                    for($i = 0; $i < 32; $i++) {  
                                        $nonce_str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
                                    }
                                    $op_user_passwd = MD5($app['qq_mchid_password']);
                                    $appid = $app['qq_appid'];
                                    $mch_id = $app['qq_mchid'];
                                    $out_trade_no = $order_query['order_id'];
                                    $refund_fee = $sqtx['money']*100;
                                    $now = time();
                                    $out_refund_no = date("Y", $now) . date("m", $now) . date("d", $now) . date("H", $now) . date("i", $now) . date("s", $now) . rand(1000, 9999);
                                    $sign_str = "appid=".$appid."&mch_id=".$mch_id."&nonce_str=".$nonce_str."&op_user_id=".$mch_id."&op_user_passwd=".$op_user_passwd."&out_refund_no=".$out_refund_no."&out_trade_no=".$out_trade_no."&refund_fee=".$refund_fee;
                                    $sign = $sign_str."&key=".$app['qq_mchid_key'];
                                    $sign = strtoupper(MD5($sign));
                                    $params = "<xml>
                                            <appid>".$appid."</appid>
                                            <mch_id>".$mch_id."</mch_id>
                                            <nonce_str>".$nonce_str."</nonce_str>
                                            <op_user_id>".$mch_id."</op_user_id>
                                            <op_user_passwd>".$op_user_passwd."</op_user_passwd>
                                            <out_refund_no>".$out_refund_no."</out_refund_no>
                                            <out_trade_no>".$out_trade_no."</out_trade_no>
                                            <refund_fee>".$refund_fee."</refund_fee>
                                            <sign>".$sign."</sign>
                                            </xml>";
                                    $url = 'https://api.qpay.qq.com/cgi-bin/pay/qpay_refund.cgi';
                                    $res = $this -> postXmlSSLCurl($params, $url, 30, $appletid);
                                    $res = $this->xmlToArray($res);
                                    if($res){
                                        if($res['return_code'] == 'SUCCESS'){
                                            $return = true;
                                        }else{
                                            $this->error('退款失败!请检查系统设置->QQ小程序设置');exit;
                                        }
                                    }else{
                                        $this->error('退款失败!请检查系统设置->QQ小程序设置');exit;
                                    }
                                }
                            }
                            if($return){
                                    if($wx_price>0){
                                         $xfmoney1=array(
                                            "uniacid"=>$appletid,
                                            "orderid"=>$tx_query['ptorder'],
                                            "suid"=>$user_info["id"],
                                            "type"=>"add",
                                            "score"=>$wx_price,
                                            "creattime"=>time()
                                        );
                                        if($order_query['paytype'] == 1){
                                            $xfmoney1["message"] = "退款退回微信"; 
                                        }else if($order_query['paytype'] == 2){
                                            $xfmoney1["message"] = "退款退回支付宝"; 
                                        }else if($order_query['paytype'] == 3){
                                            $xfmoney1["message"] = "退款退回百度"; 
                                        }else if($order_query['paytype'] == 4){
                                            $xfmoney1["message"] = "退款退回QQ"; 
                                        }
                                        Db::name('wd_xcx_money')->insert($xfmoney1);
                                    }

                                    if($yue_price>0){
                                        $xfmoney2=array(
                                            "uniacid"=>$appletid,
                                            "orderid"=>$tx_query['ptorder'],
                                            "suid"=>$user_info["id"],
                                            "type"=>"add",
                                            "score"=>$yue_price,
                                            "message"=>"退款退回余额",
                                            "creattime"=>time()
                                        );
                                        Db::name('wd_xcx_money')->insert($xfmoney2);
                                    }


                                    Db::name('wd_xcx_pt_tx')->where("id",$id)->update(array("flag"=>2,"txtime"=>time()));
                                    Db::name("wd_xcx_pt_order")->where("id",$order_query['id'])->update(array('wx_price' => 0));
                                    
                                    
                                    if($order_query['source'] != 3 && $order_query['pay_info']){
                                        $jsondata = unserialize($order_query['jsondata']);
                                        $ptpro=Db::name('wd_xcx_pt_pro')->where("id",$jsondata[0]['baseinfo'])->find();
                                        $jsons['orderid'] = $order_query['order_id'];
                                        $jsons['ftitle'] = $ptpro['title'];
                                        $jsons['fprice'] = "实付：".$order_query['price'];

                                        
                                        if($order_query['source'] == 1){
                                            $openid = Db::name('wd_xcx_user')->where('suid', $order_query['suid'])->value('openid');
                                            $jsons = [
                                                'order_id' => $order_query['order_id'],
                                                'fprice' => $order_query['price'],
                                                'msg' => "退款成功",
                                            ];
                                            $jsons = serialize($jsons);
                                            sendSubscribe($appletid, 3, $openid, $jsons);
                                        }else if($order_query['source'] == 6){
                                            if($yue_price > 0){
                                                $jsons['refund_type'] = "退回QQ：￥".$wx_price."元，退回余额：￥".$yue_price;
                                            }else{
                                                $jsons['refund_type'] = "退回QQ：￥".$order_query['price']."元";
                                            }
                                            $jsons = serialize($jsons);
                                            $openid = Db::name('wd_xcx_qq_user')->where('suid', $order_query['suid'])->value('openid');
                                            tpl_send($appletid, 8, $openid, $order_query['source'], $order_query['qx_formid'], $jsons);
                                        }else if($order_query['source'] == 5){
                                            if($yue_price > 0){
                                                $jsons['refund_type'] = "退回微信：￥".$wx_price."元，退回余额：￥".$yue_price;
                                            }else{
                                                $jsons['refund_type'] = "退回微信：￥".$order_query['price']."元";
                                            }
                                            $jsons = serialize($jsons);
                                            $openid = Db::name('wd_xcx_toutiao_user')->where('suid', $order_query['suid'])->value('openid');
                                            tpl_send($appletid, 8, $openid, $order_query['source'], $order_query['qx_formid'], $jsons);
                                        }
                                        
                                    }
                                    $jsondata = unserialize($order_query['jsondata']);

                                    //处理库存
                                    foreach ($jsondata as $rsi) {
                                        // 处理销售量
                                        $pvid = $rsi['pvid'];
                                        $num = $rsi['num'];
                                        $pro = Db::name("wd_xcx_pt_pro")->where("id", $pvid)->find();
                                        $pronum = $pro['xsl'];
                                        $newpronum = $pronum - $num;
                                        Db::name("wd_xcx_pt_pro")->where("id", $pvid)->update(array("xsl" => $newpronum));
                                        // 减去对应的库存
                                        $spid = $rsi['proinfo'];
                                        $pro_val = Db::name("wd_xcx_pt_pro_val")->where("id", $spid)->find();
                                        $spnum = $pro_val['kc'];
                                        $kc = $spnum + $num;
                                        Db::name("wd_xcx_pt_pro_val")->where("id", $spid)->update(array("kc" => $kc));
                                    }
                                    $this->success("退款成功 状态修改成功");
                                
                            }else{
                                if($wx_price>0){
                                    $xfmoney1=array(
                                        "uniacid"=>$appletid,
                                        "orderid"=>$tx_query['ptorder'],
                                        "suid"=>$user_info["id"],
                                        "type"=>"add",
                                        "score"=>$wx_price,
                                        "creattime"=>time()
                                    );
                                    if($order_query['paytype'] == 1){
                                        $xfmoney1["message"] = "退款退回微信"; 
                                    }else if($order_query['paytype'] == 2){
                                        $xfmoney1["message"] = "退款退回支付宝"; 
                                    }else if($order_query['paytype'] == 3){
                                        $xfmoney1["message"] = "退款退回百度"; 
                                    }else if($order_query['paytype'] == 4){
                                        $xfmoney1["message"] = "退款退回QQ"; 
                                    }
                                    Db::name('wd_xcx_money')->insert($xfmoney1);
                                }

                                if($yue_price>0){
                                    $xfmoney2=array(
                                        "uniacid"=>$appletid,
                                        "orderid"=>$tx_query['ptorder'],
                                        "suid"=>$user_info["id"],
                                        "type"=>"add",
                                        "score"=>$yue_price,
                                        "message"=>"退款退回余额",
                                        "creattime"=>time()
                                    );
                                    Db::name('wd_xcx_money')->insert($xfmoney2);
                                }
                                Db::name("wd_xcx_pt_tx")->where("id",$id)->update(array("flag"=>2,"txtime"=>time()));
                                if($order_query['source'] != 3){
                                    $jsondata = unserialize($order_query['jsondata']);
                                    $ptpro=Db::name('wd_xcx_pt_pro')->where("id",$jsondata[0]['baseinfo'])->find();
                                    $jsons['orderid'] = $order_query['order_id'];
                                    $jsons['ftitle'] = $ptpro['title'];
                                    $jsons['fprice'] = "实付：".$order_query['price'];
                                    $jsons['refund_type'] = "退回余额：￥".$order_query['price']."元";
                                    $jsons = serialize($jsons);
                                    if($order_query['source'] == 1){
                                        $openid = Db::name('wd_xcx_user')->where('suid', $order_query['suid'])->value('openid');
                                        $jsons = [
                                            'order_id' => $order_query['order_id'],
                                            'fprice' => $order_query['price'],
                                            'msg' => "退款成功",
                                        ];
                                        $jsons = serialize($jsons);
                                        sendSubscribe($appletid, 3, $openid, $jsons);
                                    }else if($order_query['source'] == 6){
                                        $openid = Db::name('wd_xcx_qq_user')->where('suid', $order_query['suid'])->value('openid');
                                        tpl_send($appletid, 8, $openid, $order_query['source'], $order_query['qx_formid'], $jsons);
                                    }else if($order_query['source'] == 5){
                                        $openid = Db::name('wd_xcx_toutiao_user')->where('suid', $order_query['suid'])->value('openid');
                                        tpl_send($appletid, 8, $openid, $order_query['source'], $order_query['qx_formid'], $jsons);
                                    }
                                    
                                }
                                $jsondata = unserialize($order_query['jsondata']);

                                //处理库存
                                foreach ($jsondata as $rsi) {
                                    // 处理销售量
                                    $pvid = $rsi['pvid'];
                                    $num = $rsi['num'];
                                    $pro = Db::name("wd_xcx_pt_pro")->where("id", $pvid)->find();
                                    $pronum = $pro['xsl'];
                                    $newpronum = $pronum - $num;
                                    Db::name("wd_xcx_pt_pro")->where("id", $pvid)->update(array("xsl" => $newpronum));
                                    // 减去对应的库存
                                    $spid = $rsi['proinfo'];
                                    $pro_val = Db::name("wd_xcx_pt_pro_val")->where("id", $spid)->find();
                                    $spnum = $pro_val['kc'];
                                    $kc = $spnum + $num;
                                    Db::name("wd_xcx_pt_pro_val")->where("id", $spid)->update(array("kc" => $kc));
                                }
                                $this->success("退款成功");
                            }





                        }
                        if($val==3){
                            Db::name('wd_xcx_pt_tx')->where("id",$id)->update(array("flag"=>3,"txtime"=>time()));

                            if($order_query['source'] == 1){
                                $openid = Db::name("wd_xcx_user")->where("suid", $order_query['suid'])->value('openid');
                                $jsons = [
                                    'order_id' => $order_query['order_id'],
                                    'fprice' => $order_query['price'],
                                    'msg' => "退款被拒",
                                ];
                                $jsons = serialize($jsons);
                                sendSubscribe($appletid, 3, $openid, $jsons);
                            }
                            $this->success("提现状态 修改成功");
                        }
                    }
                }else{
                    $sqtx_list = Db::name('wd_xcx_pt_tx')->where("uniacid",$appletid)->order("id desc")->paginate(10,false,['query' => ['appletid' => $appletid]]);
                    $sqtx = $sqtx_list->toArray()['data'];
                    foreach ($sqtx as $key => &$res) {
                        $user = Db::name('wd_xcx_superuser')->where("uniacid",$appletid)->where('id',$res['suid'])->find();
                        $info = getNameAvatar($user['id'], $appletid);
                        $user['nickname'] = $info['nickname'];
                        $user['avatar'] = $info['avatar'];
                        $res['userinfo'] = $user;
                        $res['creattime'] = date("Y-m-d H:i:s", $res['creattime']);
                    }
                    $this->assign("sqtx_list",$sqtx_list);
                    $this->assign("sqtx",$sqtx);
                }
            }else{
                $usergroup = Session::get('usergroup');
                if($usergroup==1){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/applet');
                }
                if($usergroup==2){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/index');
                }
                if($usergroup==3){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/index');
                }

            }
            return $this->fetch('tkreply');
        }else{
            $this->redirect('Login/index');
        }
    }


    private function ff(){
        $secret = md5('worldidc_wnmd'); // md5('worldidc_wnmd');

        $key_content = include('License.php');
        $key_content = $key_content['license'];
        $length = strlen($key_content);

        // 密钥长度小于 102 必然无效
        // if($length < 102) {
        //     die();
        // }

        $is = base64_decode(substr($key_content, 0, 6));

        if(substr($is, 0, 1) == '|'){
            $str_arr = unpack("C2", substr($is, 1));
            $key_content = substr($key_content, 6);
            $len1 = $str_arr[1];
            $len2 = $length - 6 - $len1 - $str_arr[2];
        }else{
            $len1 = 26;
            $len2 = $length - 102;
        }

        // 获取加密的 code
        $code = base64_decode(substr($key_content, $len1, $len2));

        $code_length = strlen($code);

        $round = $code_length / 32;
        $left = $code_length % 32;

        // 获取和 code 等长的 self_key
        $self_key = str_repeat($secret, $round) . substr($secret, 0, $left);

        // 这边不妨把两个都 unpack 下

        $decode = array_map(function($a, $b) {
            $c = $a - $b;
            return $c > 0 ? $c : $c + 256;
        }, unpack("C{$code_length}", $code), unpack("C{$code_length}", $self_key));

        $str = array_reduce($decode, function($sum, $code) {
            return $sum .= chr($code);
        }, '');

        //end
        if($str == $_SERVER['HTTP_HOST']){

            //通过
        }else{

                  // echo '密钥错误，请联系开发者获取正确密钥!';
          //  exit();
        }
    }

    public function setsave(){
        $data = array();
        //小程序ID
        $data['uniacid'] = input("appletid");
        $data['types'] = input("types");
        $data['is_pt'] = input("is_pt");
        $data['is_tuikuan'] = input("is_tuikuan");
        $data['pt_time'] = input("pt_time");
        if($data["pt_time"]==0||$data["pt_time"]==null||$data["pt_time"]==""){
            $data["pt_time"]=24;
        }
        $data['fahuo'] = input("fahuo");
        if($data["fahuo"]==0||$data["fahuo"]==null||$data["fahuo"]==""){
            $data["fahuo"]=7;
        }
        $data['guiz'] = input('content');
        $pintuan = Db::name('wd_xcx_pt_gz')->where('uniacid',$data['uniacid'])->find();
        if(!$pintuan){
            $res = Db::name('wd_xcx_pt_gz')->insert($data);
        }else{
            $res = Db::name('wd_xcx_pt_gz')->where('uniacid',$data['uniacid'])->update($data);
        }
        if($res){
            $this->success('拼团规则新增/更新成功!');
        }else{
            $this->error('拼团规则新增/更新更新失败，没有修改项！');
            exit;
        }
    }
    //主动取消订单
    public function qxorder(){
        $appletid = input("appletid");
        $order_id = input("order_id");
        $orderinfo = Db::name('wd_xcx_pt_order')->where('uniacid',$appletid)->where('order_id',$order_id)->find();
        Db::name('wd_xcx_pt_order')->where('order_id',$order_id)->update(array("flag"=>5));
        $pdata=array(
            "uniacid"=>$appletid,
            "suid"=>$orderinfo['suid'],
            "ptorder"=> $orderinfo['order_id'],
            "money"=>$orderinfo['price'],
            "creattime"=>time(),
            "flag"=>1,
            "is_success"=>1,
        );
        $id = Db::name('wd_xcx_pt_tx')->insertGetId($pdata);
        if($id){
            $tx_query=Db::name("wd_xcx_pt_tx")->where("id",$id)->where("uniacid",$appletid)->find();
            $order_query = Db::name("wd_xcx_pt_order")->where("order_id",$tx_query['ptorder'])->where("uniacid",$appletid)->find();
            $yue_price = $order_query['yue_price'];
            $wx_price = $order_query['wx_price'];

            $user_info=Db::name("wd_xcx_superuser")->where("id",$tx_query['suid'])->where("uniacid",$appletid)->find();
            if($tx_query['is_success'] == 1){
                $nowscore = $user_info['score'];
                $newscore = $nowscore*1 + $order_query['jf']*1;
                if($order_query['jf']>0){
                    $xfscore=array(
                        "uniacid"=>$appletid,
                        "orderid"=>$order_query['order_id'],
                        "suid"=>$user_info["id"],
                        "type" => "add",
                        "score" => $order_query['jf']*1,
                        "message" => "拼团退还积分",
                        "creattime" => time()
                    );
                    Db::name('wd_xcx_score')->insert($xfscore);
                }
                Db::name('wd_xcx_superuser')->where('id',$user_info['id'])->update(array("score"=>$newscore));

                // 返回优惠券
                if($order_query['coupon']!=0){
                    // 先判断优惠券有没有过期了
                    $coupon = Db::name('wd_xcx_coupon_user')->where('uniacid',$appletid)->where('id',$order_query['coupon'])->find();
                    // 如果没有过期更改优惠券状态
                    if($coupon['etime']==0){
                        Db::name('wd_xcx_coupon_user')->where('id',$order_query['coupon'])->update(array("utime"=>0,"flag"=>0));
                    }else{
                        if($now <= $coupon['etime']){
                            Db::name('wd_xcx_coupon_user')->where('id',$order_query['coupon'])->update(array("utime"=>0,"flag"=>0));
                        }
                    }
                }
            }

            $return=array();
            if($yue_price > 0){
                $new_yue = $user_info['money'] + $yue_price;
                $moneydata = array(
                    "money" => $new_yue
                );
                $res = Db::name("wd_xcx_superuser")->where("id",$user_info["id"])->update($moneydata);
                Db::name("wd_xcx_pt_order")->where("id",$order_query['id'])->update(array('yue_price' => 0));
            }    
            if($wx_price > 0){
                $app = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                $sqtx = Db::name('wd_xcx_pt_tx')->where("uniacid",$appletid)->where("id",$id)->find();
                if($order_query['paytype'] == 1){  //微信支付
                    if($order_query['source'] == 1){
                        $mchid = $app['mchid'];   //商户号
                        $apiKey = $app['signkey'];    //商户的秘钥
                        $appid = $app['appID'];                 //小程序的id
                        $SSLCERT_PATH = ROOT_PATH . 'public/Cert/' . $appletid . '/apiclient_cert.pem';//证书路径
                        $SSLKEY_PATH = ROOT_PATH . 'public/Cert/' . $appletid . '/apiclient_key.pem';//证书路径
                    }elseif($order_query['paytype'] == 3){
                        $mchid = $app['wx_h5_mchid'];   //商户号
                        $apiKey = $app['wx_h5_signkey'];    //商户的秘钥
                        $appid = $app['wx_h5_appid'];                 //小程序的id
                        $SSLCERT_PATH = ROOT_PATH.'public/Cert/'.$appletid.'/h5_apiclient_cert.pem';//证书路径
                        $SSLKEY_PATH =  ROOT_PATH.'public/Cert/'.$appletid.'/h5_apiclient_key.pem';//证书路径
                    }elseif($order_query['source'] == 5){
                        $mchid = $app['bdance_h5_mchid'];   //商户号
                        $apiKey = $app['bdance_h5_signkey'];    //商户的秘钥
                        $appid = $app['bdance_h5_appid'];                 //小程序的id
                        $SSLCERT_PATH = ROOT_PATH.'public/Cert/'.$appletid.'/bdance_apiclient_cert.pem';//证书路径
                        $SSLKEY_PATH =  ROOT_PATH.'public/Cert/'.$appletid.'/bdance_apiclient_key.pem';//证书路径
                    }

                    $openid = 'openid';    //申请者的openid
                    $outTradeNo = $sqtx['ptorder'];
                    $totalFee = $sqtx['money']*100;  //申请了提现多少钱
                    $outRefundNo = $sqtx['ptorder']; //商户订单号
                    $refundFee = $sqtx['money']*100;  //申请了提现多少钱


                    $opUserId = $mchid;//商户号
                    include "WinXinRefund.php";
                    $weixinpay = new WinXinRefund($openid, $outTradeNo, $totalFee, $outRefundNo, $refundFee, $SSLCERT_PATH, $SSLKEY_PATH, $opUserId, $appid, $apiKey);
                    $return = $weixinpay->refund();
                    if (!$return) {
                        throw new \Exception('微信退款失败， 请检查系统设置->微信小程序相关配置');
                    }
                }elseif ($order_query['paytype'] == 2){     //支付宝支付
                    Vendor('alipaysdk.aop.AopClient');
                    Vendor('alipaysdk.aop.request.AlipayTradeRefundRequest');

                    $aop = new \AopClient ();
                    $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
                    $aop->appId = $app['ali_appID'];
                    $aop->rsaPrivateKey = $app['ali_private_key'];
                    $aop->alipayrsaPublicKey = $app['ali_public_key'];
                    $aop->apiVersion = '1.0';
                    $aop->signType = 'RSA2';
                    $aop->postCharset = 'UTF-8';
                    $aop->format = 'json';
                    $request = new \AlipayTradeRefundRequest ();
                    $request->setBizContent("{'refund_amount':" . $sqtx['money'] . ", 'out_trade_no': " . $sqtx['ptorder'] . "}");
                    $result = $aop->execute($request);
                    $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
                    $resultCode = $result->$responseNode->code;
                    if (!empty($resultCode) && $resultCode == 10000) {
                        $return = true;
                    } else {
                        throw new \Exception('支付宝退款失败， 请检查系统设置->支付宝小程序设置');
                    }
                }elseif($order_query['paytype'] == 3){
                    $pay_info = unserialize($order_query['pay_info']);
                    require_once(ROOT_PATH.'application/api/controller/bdpay/Autoloader.php');
                    $params = [
                        'method' => 'nuomi.cashier.applyorderrefund',
                        'orderId' => intval($pay_info['orderId']),
                        'userId' => intval($pay_info['userId']),
                        'refundType' => '1',
                        'refundReason' => '订单退款',
                        'tpOrderId' => $order_id,
                        'appKey' => $app['baidu_pay_appkey']
                    ];
                    $rsaSign = \NuomiRsaSign::genSignWithRsa($params, $app['baidu_private_key']);
                    $params['rsaSign'] = $rsaSign;
                    $url = 'https://nop.nuomi.com/nop/server/rest';
                    $res = _Postrequest($url, http_build_query($params));
                    $res = json_decode($res, true);
                    if($res){
                        if($res['errno'] == 0){
                            $return = true;
                        }else{
                            $this->error('退款失败!请检查系统设置->百度小程序设置');exit;
                        }
                    }else{
                        $this->error('退款失败!请检查系统设置->百度小程序设置');exit;
                    }

                }elseif($order_query['paytype'] == 4){
                        $pay_info = unserialize($order_query['pay_info']);
                        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";  
                        $nonce_str = "";  
                        for($i = 0; $i < 32; $i++) {  
                            $nonce_str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
                        }
                        $op_user_passwd = MD5($app['qq_mchid_password']);
                        $appid = $app['qq_appid'];
                        $mch_id = $app['qq_mchid'];
                        $out_trade_no = $order_query['order_id'];
                        $refund_fee = $sqtx['money']*100;
                        $now = time();
                        $out_refund_no = date("Y", $now) . date("m", $now) . date("d", $now) . date("H", $now) . date("i", $now) . date("s", $now) . rand(1000, 9999);
                        $sign_str = "appid=".$appid."&mch_id=".$mch_id."&nonce_str=".$nonce_str."&op_user_id=".$mch_id."&op_user_passwd=".$op_user_passwd."&out_refund_no=".$out_refund_no."&out_trade_no=".$out_trade_no."&refund_fee=".$refund_fee;
                        $sign = $sign_str."&key=".$app['qq_mchid_key'];
                        $sign = strtoupper(MD5($sign));
                        $params = "<xml>
                                <appid>".$appid."</appid>
                                <mch_id>".$mch_id."</mch_id>
                                <nonce_str>".$nonce_str."</nonce_str>
                                <op_user_id>".$mch_id."</op_user_id>
                                <op_user_passwd>".$op_user_passwd."</op_user_passwd>
                                <out_refund_no>".$out_refund_no."</out_refund_no>
                                <out_trade_no>".$out_trade_no."</out_trade_no>
                                <refund_fee>".$refund_fee."</refund_fee>
                                <sign>".$sign."</sign>
                                </xml>";
                        $url = 'https://api.qpay.qq.com/cgi-bin/pay/qpay_refund.cgi';
                        $res = $this -> postXmlSSLCurl($params, $url, 30, $appletid);
                        $res = $this->xmlToArray($res);
                        if($res){
                            if($res['return_code'] == 'SUCCESS'){
                                $return = true;
                            }else{
                                $this->error('退款失败!请检查系统设置->QQ小程序设置');exit;
                            }
                        }else{
                            $this->error('退款失败!请检查系统设置->QQ小程序设置');exit;
                        }

                }


                if($return){
                    if($wx_price>0){
                         $xfmoney1=array(
                            "uniacid"=>$appletid,
                            "orderid"=>$tx_query['ptorder'],
                            "suid"=>$user_info["id"],
                            "type"=>"add",
                            "score"=>$wx_price,
                            "creattime"=>time()
                        );
                        if($order_query['paytype'] == 1){
                            $xfmoney1["message"] = "退款退回微信"; 
                        }else if($order_query['paytype'] == 2){
                            $xfmoney1["message"] = "退款退回支付宝"; 
                        }else if($order_query['paytype'] == 3){
                            $xfmoney1["message"] = "退款退回百度"; 
                        }else if($order_query['paytype'] == 4){
                            $xfmoney1["message"] = "退款退回QQ"; 
                        }
                        Db::name('wd_xcx_money')->insert($xfmoney1);
                    }

                    if($yue_price>0){
                        $xfmoney2=array(
                            "uniacid"=>$appletid,
                            "orderid"=>$tx_query['ptorder'],
                            "suid"=>$user_info["id"],
                            "type"=>"add",
                            "score"=>$yue_price,
                            "message"=>"退款退回余额",
                            "creattime"=>time()
                        );
        
                        Db::name('wd_xcx_money')->insert($xfmoney2);
                    }

                    
                    Db::name("wd_xcx_pt_order")->where("id",$order_query['id'])->update(array('wx_price' => 0));

                    if($order_query['source'] != 3 && $order_query['pay_info']){
                        $jsondata = unserialize($order_query['jsondata']);
                        $ptpro=Db::name('wd_xcx_pt_pro')->where("id",$jsondata[0]['baseinfo'])->find();
                        $jsons['orderid'] = $order_query['order_id'];
                        $jsons['ftitle'] = $ptpro['title'];
                        $jsons['fprice'] = "实付：".$order_query['price'];
                        if($order_query['source'] == 1){
                            $openid = Db::name('wd_xcx_user')->where('suid', $order_query['suid'])->value('openid');
                            $jsons = [
                                'order_id' => $order_query['order_id'],
                                'fprice' => $order_query['price'],
                                'msg' => "退款成功",
                            ];
                            $jsons = serialize($jsons);
                            sendSubscribe($appletid, 3, $openid, $jsons);
                        }else if($order_query['source'] == 6){
                            if($yue_price > 0){
                                $jsons['refund_type'] = "退回QQ：￥".$wx_price."元，退回余额：￥".$yue_price;
                            }else{
                                $jsons['refund_type'] = "退回QQ：￥".$order_query['price']."元";
                            }
                            $jsons = serialize($jsons);
                            $openid = Db::name('wd_xcx_qq_user')->where('suid', $order_query['suid'])->value('openid');
                            tpl_send($appletid, 8, $openid, $order_query['source'], $order_query['qx_formid'], $jsons);
                        }else if($order_query['source'] == 5){
                            if($yue_price > 0){
                                $jsons['refund_type'] = "退回微信：￥".$wx_price."元，退回余额：￥".$yue_price;
                            }else{
                                $jsons['refund_type'] = "退回微信：￥".$order_query['price']."元";
                            }
                            $jsons = serialize($jsons);
                            $openid = Db::name('wd_xcx_toutiao_user')->where('suid', $order_query['suid'])->value('openid');
                            tpl_send($appletid, 8, $openid, $order_query['source'], $prepayid, $jsons);
                        }
                        
                    }
                    Db::name("wd_xcx_pt_tx")->where("id",$id)->update(array("flag"=>2,"txtime"=>time()));

                    $jsondata = unserialize($order_query['jsondata']);
                    //处理库存
                    foreach ($jsondata as $rsi) {
                        // 处理销售量
                        $pvid = $rsi['pvid'];
                        $num = $rsi['num'];
                        $pro = Db::name("wd_xcx_pt_pro")->where("id", $pvid)->find();
                        $pronum = $pro['xsl'];
                        $newpronum = $pronum - $num;
                        Db::name("wd_xcx_pt_pro")->where("id", $pvid)->update(array("xsl" => $newpronum));
                        // 减去对应的库存
                        $spid = $rsi['proinfo'];
                        $pro_val = Db::name("wd_xcx_pt_pro_val")->where("id", $spid)->find();
                        $spnum = $pro_val['kc'];
                        $kc = $spnum + $num;
                        Db::name("wd_xcx_pt_pro_val")->where("id", $spid)->update(array("kc" => $kc));
                    }
                    $this->success("退款成功 状态修改成功");
                }else{
                    if($wx_price>0){
                        $xfmoney1=array(
                            "uniacid"=>$appletid,
                            "orderid"=>$tx_query['ptorder'],
                            "suid"=>$user_info["id"],
                            "type"=>"add",
                            "score"=>$wx_price,
                            "creattime"=>time()
                        );
                        if($order_query['paytype'] == 1){
                            $xfmoney1["message"] = "退款退回微信"; 
                        }else if($order_query['paytype'] == 2){
                            $xfmoney1["message"] = "退款退回支付宝"; 
                        }else if($order_query['paytype'] == 3){
                            $xfmoney1["message"] = "退款退回百度"; 
                        }else if($order_query['paytype'] == 4){
                            $xfmoney1["message"] = "退款退回QQ"; 
                        }
                        Db::name('wd_xcx_money')->insert($xfmoney1);
                    }

                    if($yue_price>0){
                        $xfmoney2=array(
                            "uniacid"=>$appletid,
                            "orderid"=>$tx_query['ptorder'],
                            "suid"=>$user_info["id"],
                            "type"=>"add",
                            "score"=>$yue_price,
                            "message"=>"退款退回余额",
                            "creattime"=>time()
                        );
   
                        Db::name('wd_xcx_money')->insert($xfmoney2);
                    }
                    Db::name("wd_xcx_pt_tx")->where("id",$id)->update(array("flag"=>2,"txtime"=>time()));

                    $jsondata = unserialize($order_query['jsondata']);
                    //处理库存
                    foreach ($jsondata as $rsi) {
                        // 处理销售量
                        $pvid = $rsi['pvid'];
                        $num = $rsi['num'];
                        $pro = Db::name("wd_xcx_pt_pro")->where("id", $pvid)->find();
                        $pronum = $pro['xsl'];
                        $newpronum = $pronum - $num;
                        Db::name("wd_xcx_pt_pro")->where("id", $pvid)->update(array("xsl" => $newpronum));
                        // 减去对应的库存
                        $spid = $rsi['proinfo'];
                        $pro_val = Db::name("wd_xcx_pt_pro_val")->where("id", $spid)->find();
                        $spnum = $pro_val['kc'];
                        $kc = $spnum + $num;
                        Db::name("wd_xcx_pt_pro_val")->where("id", $spid)->update(array("kc" => $kc));
                    }

                    $this->success("退款成功");
                }
            }else{
                $openid = Db::name('wd_xcx_user')->where('suid', $order_query['suid'])->value('openid');
                $jsons = [
                    'order_id' => $order_query['order_id'],
                    'fprice' => $order_query['price'],
                    'msg' => "退款成功",
                ];
                $jsons = serialize($jsons);
                sendSubscribe($appletid, 3, $openid, $jsons);
            }

            $jsondata = unserialize($order_query['jsondata']);
            //处理库存
            foreach ($jsondata as $rsi) {
                // 处理销售量
                $pvid = $rsi['pvid'];
                $num = $rsi['num'];
                $pro = Db::name("wd_xcx_pt_pro")->where("id", $pvid)->find();
                $pronum = $pro['xsl'];
                $newpronum = $pronum - $num;
                Db::name("wd_xcx_pt_pro")->where("id", $pvid)->update(array("xsl" => $newpronum));
                // 减去对应的库存
                $spid = $rsi['proinfo'];
                $pro_val = Db::name("wd_xcx_pt_pro_val")->where("id", $spid)->find();
                $spnum = $pro_val['kc'];
                $kc = $spnum + $num;
                Db::name("wd_xcx_pt_pro_val")->where("id", $spid)->update(array("kc" => $kc));
            }

            Db::name('wd_xcx_pt_tx')->where("id",$id)->update(array("flag"=>2,"txtime"=>time()));
            $this->success("退款成功");
        }
    }
    public function yaoqing(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $this->doovershare($appletid);
                $orders = Db::name('wd_xcx_pt_share')->where('uniacid',$appletid)->order('id desc')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                $plist = $orders->all();
                $count = Db::name('wd_xcx_pt_share')->where('uniacid',$appletid)->order('id desc')->count();
                $guiz = Db::name('wd_xcx_pt_gz')->where("uniacid",$appletid)->find();
                foreach ($plist as $key => &$res) {
                    // 商品
                    $pro = Db::name('wd_xcx_pt_pro')->where("uniacid",$appletid)->where("id",$res['pid'])->find();
                    if($pro['thumb']){
                        $pro['thumb'] = remote($appletid,$pro['thumb'],1);
                    }else{
                        $pro['thumb'] = remote($appletid,"/image/noimage_1.png",1);
                    }
                    $res['pro'] = $pro;
                    if($guiz['pt_time']){
                        $overtime = $res['creattime']*1 + ($guiz['pt_time'] * 3600);
                    }else{
                        $overtime = $res['creattime']*1 +24* 3600;
                    }

                    $res['creattime'] = date("Y-m-d H:i:s",$res['creattime']);
                    $res['overtime'] = date("Y-m-d H:i:s",$overtime);
                    // 团员
                    $res['team'] = $this->getmytd($appletid,$res['shareid']);
                }
                $this->assign('plist',$plist);
                $this->assign('orders',$orders);
                $this->assign('counts',$count);
            }else{
                $usergroup = Session::get('usergroup');
                if($usergroup==1){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/applet');
                }
                if($usergroup==2){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/index');
                }
                if($usergroup==3){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/index');
                }

            }
            return $this->fetch('yaoqing');
        }else{
            $this->redirect('Login/index');
        }
    }

    function getmytd($uniacid,$shareid){
        $alllist = Db::name('wd_xcx_pt_order')->where("uniacid",$uniacid)->where("flag","neq",0)->where("flag","neq",3)->where('pt_order',$shareid)->order('creattime desc')->select();
        foreach ($alllist as $key => &$res) {
            $userinfo = Db::name('wd_xcx_superuser')->where("uniacid",$uniacid)->where('id',$res['suid'])->find();
            $info = getNameAvatar($res['suid'], $uniacid);
            $userinfo['avatar'] = $info['avatar'];
            $userinfo['nickname'] = $info['nickname'];
            $res['team'] = $userinfo;
        }
        return $alllist;
    }

    public function cate(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $cates = Db::name('wd_xcx_pt_cate')->where('uniacid',$appletid)->order('num desc')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                $count = Db::name('wd_xcx_pt_cate')->where("uniacid",$appletid)->order('num desc')->count();
                $this->assign('cates',$cates);
                $this->assign('counts',$count);
            }else{
                $usergroup = Session::get('usergroup');
                if($usergroup==1){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/applet');
                }
                if($usergroup==2){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/index');
                }
                if($usergroup==3){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/index');
                }

            }
            return $this->fetch('cate');
        }else{
            $this->redirect('Login/index');
        }
    }
    public function cateadd(){
        $appletid = input("appletid");
        $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
        if(!$res){
            $this->error("找不到对应的小程序！");
        }
        $this->assign('applet',$res);
        $cateid = intval(input('cateid'));
        $cate = Db::name('wd_xcx_pt_cate')->where("uniacid",$appletid)->where('id',$cateid)->find();
        if(!$cateid){
            $cateid = 0;
        }
        $this->assign('cate',$cate);
        $this->assign('cateid',$cateid);
        return $this->fetch('cateadd');
    }
    public function catesave(){
        $data = array();
        //小程序ID
        $uniacid = input("appletid");
        $num = input("num");
        if($num){
            $data['num'] = $num;
        }else{
            $data['num'] = 1;
        }
        $title = input("title");
        if($title){
            $data['title'] = $title;
        }else{
            $this->error('栏目名称不能为空！');
            exit;
        }
        $data['creattime'] = time();
        $cateid = intval(input('cateid'));
        if (!$cateid) {
            $data['uniacid'] = $uniacid;
            $all=Db::name("wd_xcx_pt_cate")->where("uniacid",$uniacid)->select();
            foreach($all as $k){
                if($k['title']==$title){
                    $this->error('栏目名称已经存在');
                    exit;
                }
            }
            $res = Db::name('wd_xcx_pt_cate')->insert($data);
        } else {
            $all=Db::name("wd_xcx_pt_cate")->where("uniacid",$uniacid)->where("id","neq",$cateid)->select();
            foreach($all as $k){
                if($k['title']==$title){
                    $this->error('栏目名称已经存在');
                    exit;
                }
            }
            $res = Db::name('wd_xcx_pt_cate')->where('id',$cateid)->where('uniacid',$uniacid)->update($data);
        }
        if($res){
            $this->success('拼团分类新增/更新成功!',Url('Pt/cate').'?appletid='.$uniacid);
        }else{
            $this->error('拼团分类新增/更新更新失败，没有修改项！');
            exit;
        }
    }
    public function catedel(){
        $appletid = input("appletid");
        $cateid = input("cateid");
        $is = Db::name("wd_xcx_pt_pro")->where("cid", $cateid)->count();
        if($is){
            $this->error('删除失败，拼团栏目下拥有商品，无法删除');
        }
        $data = array(
            "uniacid"=>$appletid,
            "id"=>$cateid
        );
        $res = Db::name('wd_xcx_pt_cate')->where($data)->delete();
        if($res){
            $this->success('拼团栏目删除成功');
        }else{
            $this->error('拼团栏目删除失败');
        }
    }
    public function pro(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);

                $cid = input("cid") ? input("cid") : 0;
                $title = input("key");

                $where = [];
                if($cid > 0){
                    $where['cid'] = $cid;
                }

                if($title){
                    $where['title'] = ['like',"%".$title."%"];
                }
                
                $products = Db::name('wd_xcx_pt_pro')->where('uniacid',$appletid)->where($where)->order("id desc")->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                $prolist = $products->all();
                if($prolist){
                    foreach ($prolist as $key => &$res) {
                        $cate = Db::name('wd_xcx_pt_cate')->where('uniacid',$appletid)->where('id',$res['cid'])->find();
                        $res['cate'] = $cate['title'];
                        if($res['thumb']){
                            $res['thumb'] = remote($appletid,$res['thumb'],1);
                        }else{
                            $res['thumb'] = remote($appletid,"/image/noimage.jpg",1);
                        }
                    }
                }
                $count = Db::name('wd_xcx_pt_pro')->where("uniacid",$appletid)->count();
                $cate = Db::name('wd_xcx_pt_cate')->where('uniacid',$appletid)->select();
                $this ->ff();
                $this->assign('key',$title);
                $this->assign('cid',$cid);
                $this->assign('cate',$cate);
                $this->assign('products',$products);
                $this->assign('prolist',$prolist);
                $this->assign('counts',$count);
            }else{
                $usergroup = Session::get('usergroup');
                if($usergroup==1){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/applet');
                }
                if($usergroup==2){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/index');
                }
                if($usergroup==3){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/index');
                }

            }
            return $this->fetch('pro');
        }else{
            $this->redirect('Login/index');
        }
    }
    // 新增拼团产品
    public function proadd(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);

                //会员等级
                $grade_arr = Db::name("wd_xcx_vipgrade")->where("uniacid", $appletid)->order('grade asc')->select();
                if(empty($grade_arr)){
                    $data_s = [
                        'uniacid' => $appletid,
                        'grade' => 1,
                        'name' => '大众会员',
                        'upgrade' => 0,
                        'price' => 0,
                        'status' => 1,
                        'bgcolor' => '#434550',
                        'card_img' => ROOT_HOST.'/vipgrade/vip_card.png',
                        'descs' => '默认会员等级'
                    ];
                    $gid = Db::name("wd_xcx_vipgrade")->insertGetid($data_s);
                    $grade_arr[0]['name'] = '大众会员';
                    $grade_arr[0]['grade'] = 1;
                    $grade_arr[0]['id'] = $gid;
                }

                $yunfei_gg_list = Db::name("wd_xcx_freight")->where("uniacid", $appletid)->where("is_delete", 0)->field("id,name")->select();
                $this->assign('yunfei_gg_list',$yunfei_gg_list);

                $id = input('pid');
                $listV = Db::name('wd_xcx_pt_cate')->where("uniacid",$appletid)->order('num desc')->order('id desc')->select();
                if($id){
                    $products = Db::name('wd_xcx_pt_pro')->where("uniacid",$appletid)->where('id',$id)->find();
                    $allimg = Db::name('wd_xcx_products_url')->where('randid',$products['onlyid'])->select();
                    foreach ($allimg as $key => &$value) {
                        $value['url'] = remote($appletid,$value['url'],1);
                    }
                    if($products['thumb']){
                        $products['thumb'] = remote($appletid,$products['thumb'],1);
                    }
                    if($products['shareimg']){
                        $products['shareimg'] = remote($appletid,$products['shareimg'],1);
                    }

                    if(!empty($products['vipconfig'])){
                        $products['vipconfig'] = unserialize($products['vipconfig']);
                        if(!isset($products['vipconfig']['set3'])){
                            $products['vipconfig']['set3'] = 0;
                        }
                    }else{
                        $products['vipconfig'] = [
                            'set1' => '0',
                            'set2' => '0',
                            'set3' => '0'
                        ];
                    }

                    
                    $proarr = Db::name('wd_xcx_pt_pro_val')->where('pid',$id)->order('id desc')->select();
                    if($proarr){
                        $types = $proarr[0]['comment'];
                        //构建规格组
                        $typesarr = explode(",", $types);
                        $counttypes = count($typesarr);
                        // 构建规格组json
                        $typesjson = [];
                        foreach ($typesarr as $key => &$rec) {
                            $str = "type".($key+1);
                            $ziji = Db::name('wd_xcx_pt_pro_val')->where('pid',$id)->group($str)->field($str)->select();
                            $xarr = array();
                            foreach ($ziji as $key => $res) {
                                array_push($xarr, $res[$str]);
                            }
                            $typesjson[$rec] = $xarr;
                        }
                        // 构建对应的数值
                        $datajson = [];
                        foreach ($proarr as $key => &$rec) {
                            $strs = $rec['type1'].$rec['type2'].$rec['type3'];
                            $strv = $rec['kc'].",".$rec['price'].",".$rec['dprice'].",".$rec['thumb'];
                            $datajson[$strs]=$strv;
                        }
                        $datajson_keys = $datajson ? json_encode(array_keys($datajson),JSON_UNESCAPED_UNICODE) : [];
                    }else{
                        $counttypes = 0;
                    }
                }else{
                    $products = "";
                    $id = 0;
                    $allimg = "";
                    $counttypes = 0;
                    $typesarr = [];
                    $typesjson = [];
                    $datajson = [];
                    $datajson_keys = [];
                }

                $forms = Db::name('wd_xcx_formlist')->where("uniacid", $appletid) ->order('id desc')->select();

                $this->assign('forms', $forms);

                $this->assign('counttypes',$counttypes);
                $this->assign('typesarr',$typesarr);
                $this->assign('typesjson',$typesjson);
                $this->assign('datajson',$datajson);
                $this->assign('datajson_keys',$datajson_keys);
                $this->assign('allimg',$allimg);
                $this->assign('id',$id);
                $this->assign('products',$products);
                $this->assign('listAll',$listV);
                $this->assign('grade_arr',$grade_arr);


                $stores=Db::name("wd_xcx_store")->where("uniacid",$appletid)->select();
                $this->assign('stores',$stores);



            }else{
                $usergroup = Session::get('usergroup');
                if($usergroup==1){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/applet');
                }
                if($usergroup==2){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/index');
                }
                if($usergroup==3){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/index');
                }
            }
            return $this->fetch('proadd');
        }else{
            $this->redirect('Login/index');
        }
    }
    public function prosave(){
        $appletid = input("appletid");
        $pid = input("pid");
        $cid = intval(input('cid'));
        $onlyid = input('onlyid');
        if($onlyid){
            $imgsrcs = input("imgsrcs/a");
            if($imgsrcs){
                $imgarr = array();
                foreach ($imgsrcs as $k => $v) {
                    $imgarr['randid'] = $onlyid;
                    $imgarr['appletid'] = $appletid;
                    $imgarr['url'] = remote($appletid,$v,2);
                    $imgarr['dateline'] = time();
                    $is = Db::name('wd_xcx_products_url')->insert($imgarr);
                }
            }else{
                $is = 1;
            }
        }
        $imgs = Db::name('wd_xcx_products_url')->where('randid',$onlyid)->select();
        $imgtext = array();
        foreach($imgs as $k => $v){
            array_push($imgtext,$v['url']);
        }
        $pcid = Db::name('wd_xcx_pt_cate')->where('uniacid',$appletid)->where('id',$cid)->find();
        $tz_yh = input('tz_yh');
        if(!$tz_yh){
            $tz_yh = 10;
        }
        $type_x = input('type_x');
        if(!$type_x){
            $type_x = 0;
        }
        $type_y = input('type_y');
        if(!$type_y){
            $type_y = 0;
        }
        $type_i = input('type_i');
        if(!$type_i){
            $type_i = 0;
        }

        $kuaidi=input('kuaidi');

        $stores = $kuaidi > 0 ? (input("stores") ? input("stores") : '') : '';
        if(!$stores){
            $stores='';
        }
        $show_pro=0;
        if(input("show_pro")){
            $show_pro=input("show_pro");
        }



        if($pcid){
            $data = array(
                "uniacid" => $appletid,
                "num" => input('num'),
                "cid" => input('cid'),
                "type_x" => $type_x,
                "type_y" => $type_y,
                "type_i" => $type_i,
                "show_pro"=>$show_pro,
                "title" => input('title'),
                "price" => input('price'),
                "mark_price" => input('mark_price'),
                "imgtext" => serialize($imgtext),
                "descs" => input('descs'),
                'explains' => input('explains'),
                "score" => input('score'),
                "onlyid" => $onlyid,
                "texts" => htmlspecialchars_decode(input('texts')),
                "pt_min" => input('pt_min'),
                "pt_max" => input('pt_max'),
                "tz_yh" => $tz_yh,
                "kuaidi" => $kuaidi,
                "stores"=>$stores,
                "yunfei_ggid" => input('yunfei_ggid'),
                "video" => input('video'),
            );
            //缩略图
            $thumb = input('commonuploadpic1');
            if($thumb){
                $data['thumb'] = remote($appletid,$thumb,2);
            }
            $shareimg = input('commonuploadpic2');
            if($shareimg){
                $data['shareimg'] = remote($appletid,$shareimg,2);
            }
            $guig = input('ischeck');
            $data["types"] = intval($guig);

            //会员设置
            $set1 = input("set1");
            $set2 = input("set2");
            $set3 = input("set3");
            $vipconfig = array(
                "set1" => $set1,
                "set2" => $set2,
                "set3" => $set3
                );
            $data['vipconfig']  = serialize($vipconfig);
            $data['formset'] = input('formset');

            if($pid){
                Db::name('wd_xcx_pt_pro')->where('id',$pid)->update($data);
                // 全部删除已有数据
                Db::name('wd_xcx_pt_pro_val')->where('pid',$pid)->delete();
                $newsid = $pid;
            }else{
                $newsid = Db::name('wd_xcx_pt_pro')->insertGetId($data);
            }
            // 规格组长度
            $typelen = input('typelen');
            // 规格数组
            $types = input('typesarr');
            $typezz = $types;
            $typesarr = explode(",", $types);
            // 子商品
            // $ggarr = input('biaogedata');
            $ggarr = stripslashes(html_entity_decode(input('biaogedata')));
            $proarr = json_decode($ggarr,true);
            $count = 0;
            $valcount = Db::name('wd_xcx_pt_pro_val')->where('pid',$newsid)->count();
            foreach ($proarr as $key => $rec) {
                if($typelen == 1){
                    $type1 = $rec[$typesarr[0]];
                    $type2 = "";
                    $type3 = "";
                }
                if($typelen == 2){
                    $type1 = $rec[$typesarr[0]];
                    $type2 = $rec[$typesarr[1]];
                    $type3 = "";
                }
                if($typelen == 3){
                    $type1 = $rec[$typesarr[0]];
                    $type2 = $rec[$typesarr[1]];
                    $type3 = $rec[$typesarr[2]];
                }
                $datas = array(
                    "pid" => $newsid,
                    "type1" => $type1,
                    "type2" => $type2,
                    "type3" => $type3,
                    "kc" => $rec['库存'],
                    "price" => $rec['拼团价'],
                    "dprice" => $rec['单买价'],
                    "thumb" => $rec['规格图片'],
                    "comment" => $typezz,
                    "updatetime" => time()
                );

                if($valcount>0){
                    $cha =  $valcount - $key;
                    if($key<$valcount){
                        $ids = Db::name('wd_xcx_pt_pro_val')->where("pid",$newsid)->select();
                        $res = Db::name('wd_xcx_pt_pro_val')->where("id",$ids[$key]['id'])->update($datas);
                    }else{
                        $res = Db::name('wd_xcx_pt_pro_val')->insert($datas);
                    }
                }else{
                    $res = Db::name('wd_xcx_pt_pro_val')->insert($datas);
                }
                if($res){
                    $count++;
                    // var_dump($count);
                    if($count == count($proarr)){
                        $this->success('拼团商品更新成功',Url('Pt/pro').'?appletid='.$appletid);
                    }
                }
            }

        }
    }

    //批量删除操作
    public function delall(){
 
        $appletid = input("appletid");

        $array1=input('pros');
        $arr=explode(',',$array1);

        $res = Db::name('wd_xcx_pt_pro')->where("uniacid",$appletid)->where('id',"in",$arr)->delete();
        if($res){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
        return $this->fetch('pro');
       
    }

    public function prodel(){
        $appletid = input("appletid");
        $pid = input("pid");
        $data = array(
            "uniacid"=>$appletid,
            "id"=>$pid
        );
        $res = Db::name('wd_xcx_pt_pro')->where($data)->delete();
        if($res){
            $this->success('拼团商品删除成功');
        }else{
            $this->success('拼团商品删除失败');
        }
    }
    // 拼团订单管理
    public function order(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $op = input('op');

                $ops = array('display', 'hx', 'fahuo','fh');
                $op = in_array($op, $ops) ? $op : 'display';

                $this->doovershare($appletid);
                if($op == "hx"){  //核销
                    $order = input('orderid');
                    $data['hxtime'] = time();
                    $data['flag'] = 2;
                    $data['hxinfo'] = 'a:1:{i:0;i:1;}';
                    $res = Db::name('wd_xcx_pt_order')->where("id",$order)->update($data);
                    $orderinfo = Db::name('wd_xcx_pt_order') ->where("id",$order) ->find();
                    $fxsorder = Db::name('wd_xcx_fx_ls')->where('uniacid',$appletid)->where('order_id',$orderinfo['order_id'])->find();
                    if($fxsorder){
                        $this->dopagegivemoney($appletid,$orderinfo['suid'],$orderinfo['order_id']);
                    }
                    $info = Db::name('wd_xcx_pt_order')->where('id', $order)->field('suid,price')->find();
                    add_all_pay($appletid, $info['price'], $info['suid']);
                    check_vip_grade($appletid, $info['suid']);

                    if($orderinfo['source'] == 1){
                        $openid = Db::name('wd_xcx_user')->where('suid', $orderinfo['suid'])->value('openid');
                        $jsons = [
                            'fprice' => $orderinfo['price']
                        ];
                        $jsons = serialize($jsons);
                        sendSubscribe($appletid, 2, $openid, $jsons);
                    }
                    if($res){
                        $this->success("核销成功");
                    }
                }
                if($op == "fh"){
                    $order = input('orderid');
                    $data['flag'] = 2;
                    $res = Db::name('wd_xcx_pt_order')->where("id",$order)->update($data);
                    if($res){
                        $this->success("操作成功");
                    }
                    
                }
                if($op == "fahuo"){  //发货
                    $order = input('orderid');
                    $data['hxtime'] = time();
                    $data['kuadi'] = input('kuadi');
                    $data['kuaidihao'] = input('kuaidihao');
                    $data['flag'] = 4;
                    $res = Db::name('wd_xcx_pt_order')->where("id",$order)->update($data);
                    if($res){
                        $info = Db::name('wd_xcx_pt_order') ->where("id",$order) ->find();
                        if($info['source'] == 1){
                            $openid = Db::name("wd_xcx_user")->where("suid", $info['suid'])->value('openid');
                            $jsons = [
                                'order_id' => $info['order_id']
                            ];
                            $jsons = serialize($jsons);
                            sendSubscribe($appletid, 1, $openid, $jsons);
                        }
                        $this->success("发货成功");
                    }
                }

                $clorders = Db::name('wd_xcx_pt_order')->where('uniacid',$appletid)->where('flag',4)->select();
                $pt_gz=Db::name("wd_xcx_pt_gz")->where("uniacid",$appletid)->find();
                if(!$pt_gz){
                    $pt_gz['fahuo'] = 7;
                }
                if($pt_gz['fahuo'] > 0){
                    foreach ($clorders as $key => &$res) {
                        $st = $res['hxtime'] + 3600*24*$pt_gz['fahuo'];
                        if($st < time()){
                            $adata = array(
                                "hxtime" => $st,
                                "flag" => 2
                            );
                            Db::name('wd_xcx_pt_order')->where('id',$res['id'])->update($adata);
                            // 核销完成后去检测要不要进行分销商返现
                            $order_id = $res['order_id'];
                            $suid = $res['suid'];
                            $fxsorder = Db::name('wd_xcx_fx_ls')->where('uniacid',$appletid)->where('order_id',$order_id)->find();
                            if($fxsorder){
                                $this->dopagegivemoney($appletid,$suid,$order_id);
                            }
                        }
                    }
                }

                // 处理30分钟未付款的订单
                $wforders = Db::name('wd_xcx_pt_order')->where('uniacid',$appletid)->where('flag',0)->select();
                foreach ($wforders as $key => &$res) {
                    $st = $res['creattime'] + 1800;
                    if($st < time()){
                        $adata = array(
                            "flag" => 3
                        );
                        Db::name('wd_xcx_pt_order')->where('id',$res['id'])->update($adata);
                        Db::name("wd_xcx_fx_ls")->where("uniacid",$appletid)->where("order_id",$res['order_id'])->update($adata);
                        $jsdatass = unserialize($res['jsondata']);
                        //处理库存
                        foreach ($jsdatass as $rsi) {
                            // 处理销售量
                            $pvid = $rsi['pvid'];
                            $num = $rsi['num'];
                            $pro = Db::name("wd_xcx_pt_pro")->where("id", $pvid)->find();
                            $pronum = $pro['xsl'];
                            $newpronum = $pronum - $num;
                            Db::name("wd_xcx_pt_pro")->where("id", $pvid)->update(array("xsl" => $newpronum));
                            // 减去对应的库存
                            $spid = $rsi['proinfo'];
                            $pro_val = Db::name("wd_xcx_pt_pro_val")->where("id", $spid)->find();
                            $spnum = $pro_val['kc'];
                            $kc = $spnum + $num;
                            Db::name("wd_xcx_pt_pro_val")->where("id", $spid)->update(array("kc" => $kc));
                        }
                    }
                }


                $search_flag = input('search_flag');
                $search_keys = input('search_keys');
                $search_type = input('search_type');
                $start_get = input('start_get') ? strtotime(input('start_get')) : '';
                $end_get = input('end_get') ? strtotime(input('end_get')) : '';
                $where = '';
                if ($search_flag != "") {
                    if($search_flag == 1){
                        $where .= " a.flag = 1 and a.nav = 1";
                    }elseif($search_flag == 10){
                        $where .= " a.flag = 1 and a.nav = 2";
                    }else{
                        $where.= " a.flag = ".$search_flag;
                    }
                    
                }

                if ($start_get && $end_get) {
                    $where.= " a.creattime > ".$start_get." and a.createtime < ".$end_get;
                }else if($start_get){
                    $where.= " a.creattime >= ".$start_get;
                }else if ($end_get) {
                    $where.= " a.creattime <= ".$start_get;
                }

                if ($search_type && $search_keys) {
                    if ($search_type == 1) {
                        $where.= " a.order_id like '%".$search_keys."%'";
                    }
                    if ($search_type == 2) {
                        $where.= " b.name like '%".$search_keys."%'";
                    }
                    if ($search_type == 3) {
                        $where.= " b.mobile like '%".$search_keys."%'";
                    }
                    if ($search_type == 4) {
                        $where.= " b.address like '%".$search_keys."%'";
                    }
                }

                $olist = Db::name('wd_xcx_pt_order')->alias("a")->join("wd_xcx_duo_products_address b",'a.address=b.id','left')->where('a.uniacid',$appletid)->where($where)->where('a.jqr',1)->order('a.creattime desc')->field("a.*")->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                $orders = $olist->all();
                $counts = count($orders);
                foreach ($orders as $key => &$res) {
                    if(!empty($res['self_taking_info'])){
                        $self_taking_info= unserialize($res['self_taking_info']);
                        $self_taking_shop_info = unserialize($self_taking_info['self_taking_shop_info']);
                        $self_taking_info['self_taking_shop_info'] = $self_taking_shop_info;           
                        $res['self_taking_info'] = $self_taking_info;
                    }

                    $pt_tx=Db::name("wd_xcx_pt_tx")->where("ptorder",$res['order_id'])->where("uniacid",$appletid)->find();
                    $res['pt_tx'] = $pt_tx;
                    $pt_open=Db::name("wd_xcx_pt_share")->where("uniacid",$appletid)->where("shareid",$res['pt_order'])->find();
                    $res['join_count'] = $pt_open['join_count'];
                    $res['pt_min'] = $pt_open['pt_min'];
                    $res['pt_max'] = $pt_open['pt_max'];
                    $res['hxinfo2']="";
                    if($res['hxinfo']==""||$res['hxinfo']==null){
                        $res['hxinfo2']="无";
                    }else{
                        $res['hxinfo'] = unserialize($res['hxinfo']);
                        if($res['hxinfo'][0]==1){
                            $res['hxinfo2']="系统核销";
                        }else if($res['hxinfo'][0] == '密码核销' || $res['hxinfo'][0] == '管理员核销'){
                           $res['hxinfo2'] = $res['hxinfo'][0];
                        }else if($res['hxinfo'][0]=='核销员核销'){

                            $res['hxinfo2']=$res['hxinfo'][1].'核销';

                        // }else{
                        //     $store=Db::name('wd_xcx_store')->where("id",$res['hxinfo'][1])->where("uniacid",$appletid)->find();
                        //     $staff=Db::name('wd_xcx_staff')->where("id",$res['hxinfo'][2])->where("uniacid",$appletid)->find();
                        //     $res['hxinfo2']="门店：".$store['title']."</br>员工：".$staff['realname'];
                        }
                    }
                    $res['jsondata'] = unserialize($res['jsondata']);
                    $res['creattime'] = date("Y-m-d H:i:s",$res['creattime']);
                    $res['hxtime'] = $res['hxtime'] == 0?"无核销信息":date("Y-m-d H:i:s",$res['hxtime']);
                    $res['userinfo'] = Db::name('wd_xcx_user')->where('uniacid',$appletid)->where('openid',$res['openid'])->find();
                    $res['counts'] = count($res['jsondata']);
                    $coupon =  Db::name('wd_xcx_coupon_user')->where('uniacid',$appletid)->where('id',$res['coupon'])->find();
                    $couponinfo = Db::name('wd_xcx_coupon')->where('uniacid',$appletid)->where('id',$coupon['cid'])->find();
                    $res['couponinfo'] = $couponinfo;
                    $ptinfo = Db::name('wd_xcx_pt_share')->where('uniacid',$appletid)->where("shareid",$res['pt_order'])->field("flag,join_count,pid")->find();
                    $proinfo = Db::name('wd_xcx_pt_pro')->where('uniacid',$appletid)->where("id",$ptinfo['pid'])->field("pt_min")->find();
                    // 重新算总价
                    $allprice = 0;
                    foreach ($res['jsondata'] as $key2 => &$reb) {
                        $allprice += ($reb['num']*1)*($reb['proinfo']['price']);
                        if(!isset($reb['baseinfo2'])){
                            $reb['baseinfo2']=Db::name('wd_xcx_pt_pro')->where("id",$reb['baseinfo'])->find();
                            if($reb['baseinfo2']['thumb']){
                                $reb['baseinfo2']['thumb'] = remote($appletid,$reb['baseinfo2']['thumb'],1);
                            }else{
                                $reb['baseinfo2']['thumb'] = remote($appletid,"/image/noimage_1.png",1);
                            }
                            $reb['proinfo']=Db::name('wd_xcx_pt_pro_val')->where("id",$reb['proinfo'])->find();
                            if($reb['proinfo']){
                                $reb['proinfo']['ggz']=$reb['proinfo']['comment'].":".$reb['proinfo']['type1'];
                            }
                        }
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
                        if(!$res['address_get']){
                            $res['address_get'] = unserialize($res['m_address']);
                            if(!isset($res['address_get']['name'])){
                                $res['address_get']['name'] = "";
                            }
                            if(!isset($res['address_get']['mobile'])){
                                $res['address_get']['mobile'] = "";
                            }
                            if(!isset($res['address_get']['address'])){
                                $res['address_get']['address'] = "";
                            }

                            if(!isset($res['address_get']['postalcode'])){
                                $res['address_get']['postalcode'] = "";
                            }
                            if(!isset($res['address_get']['more_address'])){
                                $res['address_get']['more_address'] = "";
                            }
                        }
                    }else{
                        $res['address_get'] = unserialize($res['m_address']);
                        if(!isset($res['address_get']['name'])){
                            $res['address_get']['name'] = "";
                        }
                        if(!isset($res['address_get']['mobile'])){
                            $res['address_get']['mobile'] = "";
                        }
                        if(!isset($res['address_get']['address'])){
                            $res['address_get']['address'] = "";
                        }

                        if(!isset($res['address_get']['postalcode'])){
                            $res['address_get']['postalcode'] = "";
                        }
                        if(!isset($res['address_get']['more_address'])){
                            $res['address_get']['more_address'] = "";
                        }
                    }
                }

                if($start_get){
                    $start_get = date("Y-m-d H:i:s", $start_get);
                }
                if($end_get){
                    $end_get = date("Y-m-d H:i:s", $end_get);
                }
                $this->assign("search_flag", $search_flag);
                $this->assign("search_type", $search_type);
                $this->assign("search_keys", $search_keys);
                $this->assign("start_get", $start_get);
                $this->assign("end_get", $end_get);

                $this->assign('orders',$orders);
                $this->assign('olist',$olist);
                $this->assign('counts',$counts);
            }else{
                $usergroup = Session::get('usergroup');
                if($usergroup==1){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/applet');
                }
                if($usergroup==2){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/index');
                }
                if($usergroup==3){
                    $this->error("您没有权限操作该小程序或找不到相应小程序！",'Applet/index');
                }
            }
            return $this->fetch('order');
        }else{
            $this->redirect('Login/index');
        }
    }

    public function orderdown(){
        $appletid = input("appletid");
        $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
        if(!$res){
            $this->error("找不到对应的小程序！");
        }
        $this->assign('applet',$res);

        $search_flag = input('search_flag');
        $search_keys = input('search_keys');
        $search_type = input('search_type');
        $start_get = input('start_get') ? strtotime(input('start_get')) : '';
        $end_get = input('end_get') ? strtotime(input('end_get')) : '';
        $where = [];
        if ($search_flag != "") {
            $where['flag'] = $search_flag;
        }

        if ($start_get && $end_get) {
            $where['creattime'] = ['between', [$start_get,$end_get]];
        }else if($start_get){
            $where['creattime'] = ['>=', $start_get];
        }else if ($end_get) {
            $where['creattime'] = ['<=', $end_get];
        }

        if ($search_type) {
            if ($search_type == 1) {
                $where['order_id'] = ["like", "%".$search_keys ."%"];
            }
            if ($search_type == 2) {
                $where['name'] = ["like", "%".$search_keys ."%"];
            }
            if ($search_type == 3) {
                $where['mobile'] = ["like", "%".$search_keys ."%"];
            }
            if ($search_type == 4) {
                $where['address'] = ["like", "%".$search_keys ."%"];
            }
        }

        $orders = Db::name('wd_xcx_pt_order')->where('uniacid',$appletid)->where($where)->where('jqr',1)->order('creattime desc')->select();

        foreach ($orders as $key => &$res) {
            $pt_tx=Db::name("wd_xcx_pt_tx")->where("ptorder",$res['order_id'])->where("uniacid",$appletid)->find();
            $res['pt_tx'] = $pt_tx;
            $pt_open=Db::name("wd_xcx_pt_share")->where("uniacid",$appletid)->where("shareid",$res['pt_order'])->find();
            $res['join_count'] = $pt_open['join_count'];
            $res['pt_min'] = $pt_open['pt_min'];
            $res['pt_max'] = $pt_open['pt_max'];
            $res['hxinfo2']="";
            if($res['hxinfo']==""||$res['hxinfo']==null){
                $res['hxinfo2']="暂无核销信息";
            }else{
                $res['hxinfo'] = unserialize($res['hxinfo']);
                if($res['hxinfo'][0]==1){
                    $res['hxinfo2']="系统核销";
                }else{
                    $store=Db::name('wd_xcx_store')->where("id",$res['hxinfo'][1])->where("uniacid",$appletid)->find();
                    $staff=Db::name('wd_xcx_staff')->where("id",$res['hxinfo'][2])->where("uniacid",$appletid)->find();
                    $res['hxinfo2']="门店：".$store['title']."</br>员工：".$staff['realname'];
                }
            }
            $res['jsondata'] = unserialize($res['jsondata']);
            $res['creattime'] = date("Y-m-d H:i:s",$res['creattime']);
            $res['hxtime'] = $res['hxtime'] == 0?"无核销信息":date("Y-m-d H:i:s",$res['hxtime']);
            $res['userinfo'] = Db::name('wd_xcx_user')->where('uniacid',$appletid)->where('openid',$res['openid'])->find();
            $res['counts'] = count($res['jsondata']);
            $coupon =  Db::name('wd_xcx_coupon_user')->where('uniacid',$appletid)->where('id',$res['coupon'])->find();
            $couponinfo = Db::name('wd_xcx_coupon')->where('uniacid',$appletid)->where('id',$coupon['cid'])->find();
            $res['couponinfo'] = $couponinfo;
            $ptinfo = Db::name('wd_xcx_pt_share')->where('uniacid',$appletid)->where("shareid",$res['pt_order'])->field("flag,join_count,pid")->find();
            $proinfo = Db::name('wd_xcx_pt_pro')->where('uniacid',$appletid)->where("id",$ptinfo['pid'])->field("pt_min")->find();
//                        if($res['flag']==1 && ($ptinfo['join_count'] < $proinfo['pt_min'])){
//                            $res['flag'] = 10;
//                        }
            // 重新算总价
            $allprice = 0;
            foreach ($res['jsondata'] as $key2 => &$reb) {
                $allprice += ($reb['num']*1)*($reb['proinfo']['price']);
                if(!isset($reb['baseinfo2'])){
                    $reb['baseinfo2']=Db::name('wd_xcx_pt_pro')->where("id",$reb['baseinfo'])->find();
                    if($reb['baseinfo2']['thumb']){
                        $reb['baseinfo2']['thumb'] = remote($appletid,$reb['baseinfo2']['thumb'],1);
                    }else{
                        $reb['baseinfo2']['thumb'] = remote($appletid,"/image/noimage_1.png",1);
                    }
                    $reb['proinfo']=Db::name('wd_xcx_pt_pro_val')->where("id",$reb['proinfo'])->find();
                    if($reb['proinfo']){
                        $reb['proinfo']['ggz']=$reb['proinfo']['comment'].":".$reb['proinfo']['type1'];
                    }
                }
            }
            $res['allprice'] = $allprice;
            if($res['flag'] ==0 && $res['ck'] == 2 && $res['join_count'] < $res['pt_max'] && !$res['pt_tx']['flag'] || $res['flag'] == 0 && $res['ck'] == 1 && !$res['join_count'] && !$res['pt_tx']['flag']){
                $res['flag1'] = '未付款';
            }else if($res['flag'] ==0 && $res['ck'] == 2 && $res['join_count'] >= $res['pt_max'] && !$res['pt_tx']['flag'] || $res['flag'] ==3 && ($res['yue_price'] == 0 || $res['wx_price'] == 0) && !$res['pt_tx']['flag'] || $res['flag'] == 3 && $res['ck'] == 1 && !$res['join_count'] && !$res['pt_tx']['flag']){
                $res['flag1'] = '待支付、已结束';
            }else if($res['join_count'] < $res['pt_min'] && $res['flag'] == 1 && $res['types'] == 1){
                $res['flag1'] = '已付款';
            }else if($res['flag'] ==1 && $res['nav'] == 2 && $res['join_count'] >= $res['pt_min'] && !$res['pt_tx']['flag'] || $res['flag'] ==3 && ($res['yue_price'] != 0 || $res['wx_price'] != 0) && $res['nav'] == 2 && $res['join_count'] >= $res['pt_min'] && !$res['pt_tx']['flag'] || $res['flag'] ==1 && $res['nav'] == 2 && $res['types'] == 2 && !$res['pt_tx']['flag']){
                $res['flag1'] = '未核销';
            }else if($res['flag'] == 2 ){
                $res['flag1'] = '已完成';
            }else if($res['flag'] == 4){
                $res['flag1'] = '已发货';
            }else if($res['pt_tx']['flag'] == 1){
                $res['flag1'] = '待退款';
            }else if($res['pt_tx']['flag'] == 2){
                $res['flag1'] = '已退款';
            }else if($res['pt_tx']['flag'] == 3){
                $res['flag1'] = '已拒绝退款';
            }else if($res['flag'] ==1 && $res['nav'] == 1 && ($res['join_count'] >= $res['pt_min'] || $res['types'] == 2)){
                $res['flag1']="待发货";
            }else if($res['flag'] ==1 && $res['nav'] == 1 && $res['join_count'] < $res['pt_min']){
                $res['flag1']="已支付、未成团";
            }else if($res['flag']==5){
                $res['flag1'] = "已取消";
            }else if($res['flag']==6){
                $res['flag1'] = "取消中";
            }else if($res['flag']==7){
                $res['flag1'] = "退货中";
            }else if($res['flag']==8){
                $res['flag1'] = "退货成功";
            }else if($res['flag']==9){
                $res['flag1'] = "退货失败";
            }else if($res['flag']==10){
                $res['flag1'] = "待消费";
            }else if($res['flag']==-1){
                $res['flag1'] = "已失效";
            }

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
                if(!$res['address_get']){
                    $res['address_get'] = unserialize($res['m_address']);
                    if(!isset($res['address_get']['name'])){
                        $res['address_get']['name'] = "";
                    }
                    if(!isset($res['address_get']['mobile'])){
                        $res['address_get']['mobile'] = "";
                    }
                    if(!isset($res['address_get']['address'])){
                        $res['address_get']['address'] = "";
                    }

                    if(!isset($res['address_get']['postalcode'])){
                        $res['address_get']['postalcode'] = "";
                    }
                    if(!isset($res['address_get']['more_address'])){
                        $res['address_get']['more_address'] = "";
                    }
                }
            }else{
                $res['address_get'] = unserialize($res['m_address']);
                if(!isset($res['address_get']['name'])){
                    $res['address_get']['name'] = "";
                }
                if(!isset($res['address_get']['mobile'])){
                    $res['address_get']['mobile'] = "";
                }
                if(!isset($res['address_get']['address'])){
                    $res['address_get']['address'] = "";
                }

                if(!isset($res['address_get']['postalcode'])){
                    $res['address_get']['postalcode'] = "";
                }
                if(!isset($res['address_get']['more_address'])){
                    $res['address_get']['more_address'] = "";
                }
            }
        }

         require_once ROOT_PATH.'public/plugin/PHPExcel/PHPExcel.php';
         $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()->setCreator("导出拼团订单列表")
            ->setLastModifiedBy("导出拼团订单列表")
            ->setTitle("导出拼团订单列表")
            ->setSubject("导出拼团订单列表")
            ->setDescription("导出拼团订单列表")
            ->setKeywords("导出拼团订单列表")
            ->setCategory("导出拼团订单列表");
        $objPHPExcel->getActiveSheet()->setCellValue('A1', '下单时间');
         $objPHPExcel->getActiveSheet()->setCellValue('B1', '拼团编号');
         $objPHPExcel->getActiveSheet()->setCellValue('C1', '订单编号');
        $objPHPExcel->getActiveSheet()->setCellValue('D1', '商品信息');

        $objPHPExcel->getActiveSheet()->setCellValue('E1', '单价*数量');
        $objPHPExcel->getActiveSheet()->setCellValue('F1', '订单总价');
        $objPHPExcel->getActiveSheet()->setCellValue('G1', '核销时间/发货时间');
        $objPHPExcel->getActiveSheet()->setCellValue('H1', '姓名');
        $objPHPExcel->getActiveSheet()->setCellValue('I1', '联系方式');
       $objPHPExcel->getActiveSheet()->setCellValue('J1', '地址');
        $objPHPExcel->getActiveSheet()->setCellValue('K1', '状态');
        $objPHPExcel->getActiveSheet()->setCellValue('L1', '快递');
        $objPHPExcel->getActiveSheet()->setCellValue('M1', '快递号');

        foreach($orders as $k => $v){
            $num=$k+2;
           $objPHPExcel->getActiveSheet()->setCellValueExplicit('A'.$num, $v['creattime'],'s');
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('B'.$num, $v['pt_order'],'s');
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('C'.$num, $v['order_id'],'s');
            foreach($v['jsondata'] as $j => $f){
                if(isset($f['baseinfo2']['title'])){
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('D'.$num,$f['baseinfo2']['title'].";".$f['proinfo']['ggz'],'s');
                }else{
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('D'.$num,$f['proinfo']['ggz'],'s');
                }
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('E'.$num,$f['proinfo']['price'].'*'.$f['num'],'s');
            }
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('F'.$num,$v['price'],'s');
           if($v['hxtime']!=0){
               $objPHPExcel->getActiveSheet()->setCellValueExplicit('G'.$num, $v['hxtime'],'s');
           }else{
               $objPHPExcel->getActiveSheet()->setCellValueExplicit('G'.$num,"未核销/未发货",'s');
           }
          if($v['address_get']){
              $objPHPExcel->getActiveSheet()->setCellValue('H'.$num, $v['address_get']['name'],'s');
              $objPHPExcel->getActiveSheet()->setCellValue('I'.$num, $v['address_get']['mobile'],'s');
              $objPHPExcel->getActiveSheet()->setCellValue('J'.$num, $v['address_get']['address'].$v['address_get']['more_address'],'s');
          }
            $objPHPExcel->getActiveSheet()->setCellValue('K'.$num, $v['flag1'],'s');
            $objPHPExcel->getActiveSheet()->setCellValue('L'.$num, $v['kuadi']);
            $objPHPExcel->getActiveSheet()->setCellValue('M'.$num,$v['kuaidihao']);
        }
        $objPHPExcel->getActiveSheet()->setTitle('导出拼团列表');
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="拼团订单列表.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }


    // 向我的上级返钱操作
    public function dopagegivemoney($uniacid,$suid,$orderid){
        $guiz = Db::name('wd_xcx_fx_gz')->where('uniacid',$uniacid)->find();
        $order = Db::name('wd_xcx_fx_ls')->where('uniacid',$uniacid)->where('order_id',$orderid)->find();
        Db::name('wd_xcx_fx_ls')->where('order_id',$orderid)->update(array("flag"=>2));
        $me = Db::name('wd_xcx_superuser')->where('uniacid',$uniacid)->where('id',$order['suid'])->find();
        $me_p_get_money = $me['p_get_money'];
        $me_p_p_get_money = $me['p_p_get_money'];
        $me_p_p_p_get_money = $me['p_p_p_get_money'];
        // 启动一级分销提成
        if($guiz['fx_cj'] == 1){
            if($order['parent_id']){
                $puser = Db::name('wd_xcx_superuser')->where('uniacid',$uniacid)->where('id',$order['suid'])->find();
                $kdata = array(
                    "fx_allmoney" => $puser['fx_allmoney'] + $order['parent_id_get'],
                    "fx_money" => $puser['fx_money'] + $order['parent_id_get']
                );
                Db::name('wd_xcx_superuser')->where('uniacid',$uniacid)->where('id',$order['parent_id'])->update($kdata);
                // 我给我的父级贡献的钱
                $new_p_get_money = $me_p_get_money*1 + $order['parent_id_get']*1;
                Db::name('wd_xcx_superuser')->where('uniacid',$uniacid)->where('id',$order['suid'])->update(array("p_get_money" => $new_p_get_money));
            }
        }
        // 启动二级分销提成
        if($guiz['fx_cj'] == 2){
            if($order['parent_id']){
                $puser = Db::name('wd_xcx_superuser')->where('uniacid',$uniacid)->where('id',$order['parent_id'])->find();
                $kdata = array(
                    "fx_allmoney" => $puser['fx_allmoney'] + $order['parent_id_get'],
                    "fx_money" => $puser['fx_money'] + $order['parent_id_get']
                );
                Db::name('wd_xcx_superuser')->where('uniacid',$uniacid)->where('id',$order['parent_id'])->update($kdata);
                // 我给我的父级贡献的钱
                $new_p_get_money = $me_p_get_money*1 + $order['parent_id_get']*1;
                Db::name('wd_xcx_superuser')->where('uniacid',$uniacid)->where('id',$order['suid'])->update(array("p_get_money" => $new_p_get_money));
            }
            if($order['p_parent_id']){
                $puser = Db::name('wd_xcx_superuser')->where('uniacid',$uniacid)->where('id',$order['p_parent_id'])->find();
                $kdata = array(
                    "fx_allmoney" => $puser['fx_allmoney'] + $order['p_parent_id_get'],
                    "fx_money" => $puser['fx_money'] + $order['p_parent_id_get']
                );
                Db::name('wd_xcx_superuser')->where('uniacid',$uniacid)->where('id',$order['p_parent_id'])->update($kdata);
                // 我给我的父级的父级贡献的钱
                $new_p_p_get_money = $me_p_p_get_money*1 + $order['p_parent_id_get']*1;
                Db::name('wd_xcx_superuser')->where('uniacid',$uniacid)->where('id',$order['suid'])->update(array("p_p_get_money" => $new_p_p_get_money));
            }
        }
        // 启动三级分销提成
        if($guiz['fx_cj'] == 3){
            if($order['parent_id']){
                $puser = Db::name('wd_xcx_superuser')->where('uniacid',$uniacid)->where('id',$order['parent_id'])->find();
                $kdata = array(
                    "fx_allmoney" => $puser['fx_allmoney'] + $order['parent_id_get'],
                    "fx_money" => $puser['fx_money'] + $order['parent_id_get']
                );
                Db::name('wd_xcx_superuser')->where('uniacid',$uniacid)->where('id',$order['parent_id'])->update($kdata);
                // 我给我的父级贡献的钱
                $new_p_get_money = $me_p_get_money*1 + $order['parent_id_get']*1;
                Db::name('wd_xcx_superuser')->where('uniacid',$uniacid)->where('id',$order['suid'])->update(array("p_get_money" => $new_p_get_money));
            }
            if($order['p_parent_id']){
                $puser = Db::name('wd_xcx_superuser')->where('uniacid',$uniacid)->where('id',$order['p_parent_id'])->find();
                $kdata = array(
                    "fx_allmoney" => $puser['fx_allmoney'] + $order['p_parent_id_get'],
                    "fx_money" => $puser['fx_money'] + $order['p_parent_id_get']
                );
                Db::name('wd_xcx_superuser')->where('uniacid',$uniacid)->where('id',$order['p_parent_id'])->update($kdata);
                // 我给我的父级的父级贡献的钱
                $new_p_p_get_money = $me_p_p_get_money*1 + $order['p_parent_id_get']*1;
                Db::name('wd_xcx_superuser')->where('uniacid',$uniacid)->where('id',$order['suid'])->update(array("p_p_get_money" => $new_p_p_get_money));
            }
            if($order['p_p_parent_id']){
                $puser =  Db::name('wd_xcx_superuser')->where('uniacid',$uniacid)->where('id',$order['p_p_parent_id'])->find();
                $kdata = array(
                    "fx_allmoney" => $puser['fx_allmoney'] + $order['p_p_parent_id_get'],
                    "fx_money" => $puser['fx_money'] + $order['p_p_parent_id_get']
                );
                Db::name('wd_xcx_superuser')->where('uniacid',$uniacid)->where('id',$order['p_p_parent_id'])->update($kdata);
                // 我给我的父级的父级的附近贡献的钱
                $new_p_p_p_get_money = $me_p_p_p_get_money*1 + $order['p_p_parent_id_get']*1;
                Db::name('wd_xcx_superuser')->where('uniacid',$uniacid)->where('id',$order['suid'])->update(array("p_p_p_get_money" => $new_p_p_p_get_money));
            }
        }
    }


    // 处理过期的订单
    function doovershare($uniacid){
        $now = time();
        $guiz = Db::name('wd_xcx_pt_gz')->where('uniacid',$uniacid)->find();
        $allshare = Db::name('wd_xcx_pt_share')->where('uniacid',$uniacid)->where("flag","in",[1,2])->select();

        foreach ($allshare as $key => &$res) {

            $max = $res['pt_max']*1;
            $min = $res['pt_min']*1;

            $ct = $res['creattime'];
            if($guiz['pt_time']){
                $overtime = $ct*1 + ($guiz['pt_time'] * 3600);  //拼团结束的时间
            }else{
                $overtime=$ct*1+24*3600;
            }

            // 订单没过期
            if($overtime >= $now){
                // 拼团成功
                if($res['join_count']>=$min){
                    $share_arr = array("flag"=>2);
                    //订阅消息start
                    if($res['source'] == 1 && $res['joiner']){
                        $joiner = unserialize($res['joiner']);
                        $lists = Db::name("wd_xcx_pt_order")->where("uniacid", $uniacid)->where("source", 1)->where("suid", 'in', $joiner)->where("pt_order", $res["shareid"])->where("jqr", 1)->select();
                        foreach ($lists as $reb1) {
                            $jsondata = unserialize($reb1['jsondata']);
                            $title = Db::name('wd_xcx_pt_pro')->where('id', $jsondata[0]['id'])->value('title');
                            $user_info = Db::name('wd_xcx_user')->where('suid', $reb1['suid'])->find();
                            $jsons = [
                                    'ftitle' => $title,
                                    'num' => $res['join_count'],
                                    'fprice' => $reb1['price'],
                                 ];
                            $jsons = serialize($jsons);
                            sendSubscribe($uniacid, 4, $user_info['openid'], $jsons);  //模板消息发送
                        }
                        $share_arr['joiner'] = '';
                        //订阅消息end
                    }
                    
                    Db::name("wd_xcx_pt_share")->where("id",$res['id'])->update($share_arr);
                }
            }
            // 订单已过期
            if($overtime < $now){
                // 拼团失败
                if($res['join_count']<$min){
                    // 自动成团
                    if($guiz['is_pt']==2){
                        // 生成机器人并完成订单
                        Db::name("wd_xcx_pt_share")->where("id",$res["id"])->update(array("flag"=>2,"join_count"=>$min));
                        $jsondata = Db::name('wd_xcx_pt_order') ->where('pt_order', $res['shareid']) ->field('jsondata') ->find()['jsondata'];

                        //订阅消息start
                        $lists = Db::name("wd_xcx_pt_order")->where("uniacid", $uniacid)->where("source", 1)->where("pt_order", $res["shareid"])->where("jqr", 1)->select();
                        foreach ($lists as $reb1) {
                            $jsondata = unserialize($reb1['jsondata']);
                            $title = Db::name('wd_xcx_pt_pro')->where('id', $jsondata[0]['id'])->value('title');
                            $user_info = Db::name('wd_xcx_user')->where('suid', $reb1['suid'])->find();
                            $jsons = [
                                    'ftitle' => $title,
                                    'num' => $res['pt_min'],
                                    'fprice' => $reb1['price'],
                                 ];
                            $jsons = serialize($jsons);
                            sendSubscribe($uniacid, 4, $user_info['openid'], $jsons);  //模板消息发送
                        }
                        //订阅消息end
                        

                        // 生成机器人订单
                        $xhjc = $min - $res['join_count'];

                        $tmp = range(1,30);
                        $arr = array_rand($tmp,$xhjc);

                        for($i=0; $i<$xhjc; $i++){
                            // 获取机器人信息
                            // $jqr=Db::name("wd_xcx_pt_robot")->where("id",$arr[$i]) ->find();
                            $jqrarr = array(
                                "uniacid" => $uniacid,
                                "suid" => $res['suid'],
                                "pt_order" => $res['shareid'],
                                'jsondata' => $jsondata,
                                "ck" => 2,
                                "jqr" => 2
                            );
                            Db::name("wd_xcx_pt_order")->insert($jqrarr);
                        }

                    }else{
                        // 结束订单并退还所有的钱到余额
                        Db::name("wd_xcx_pt_share")->where("id",$res["id"])->update(array("flag"=>3));
                        $lists=Db::name("wd_xcx_pt_order")->where("uniacid",$uniacid)->where("pt_order",$res["shareid"])->where("jqr",1)->select();
                        foreach ($lists as $key1 => &$reb) {
                           Db::name("wd_xcx_pt_order")->where("id",$reb['id'])->update(array("flag"=>5));
                            $user=Db::name("wd_xcx_superuser")->where("id",$reb['suid'])->where("uniacid",$uniacid)->find();
                            $pdata=array(
                                "uniacid"=>$uniacid,
                                "suid"=>$reb['suid'],
                                "ptorder"=> $reb['order_id'],
                                "money"=>$reb['price'],
                                "creattime"=>time(),
                                "flag"=>1
                            );
                            Db::name("wd_xcx_pt_tx")->insert($pdata);
                            // 返回钱
                            // $nowmoney = $user['money'];
                            // $newmoney = $nowmoney*1 + $reb['price']*1;
                            // 返回积分
                            $nowscore = $user['score'];
                            $newscore = $nowscore*1 + $reb['jf']*1;
                            if($reb['jf']>0){

                             $xfscore=array(
                                    "uniacid"=>$uniacid,
                                    "orderid"=>$reb['id'],
                                    "suid"=>$user["id"],
                                    "type" => "add",
                                    "score" => $reb['jf']*1,
                                    "message" => "拼团退还积分",
                                    "creattime" => time()
                             );
                            Db::name("wd_xcx_score")->insert($xfscore);
                            }
                            Db::name("wd_xcx_superuser")->where("uniacid",$uniacid)->where("id",$user["id"])->update(array("score"=>$newscore));
                            // 返回优惠券
                            if($reb['coupon']!=0){
                                // 先判断优惠券有没有过期了
                                $coupon=Db::name("wd_xcx_coupon_user")->where("id",$reb['coupon'])->where("uniacid",$uniacid)->find();
                                // 如果没有过期更改优惠券状态
                                if($coupon['etime']==0){
                                    Db::name("wd_xcx_coupon_user")->where("id",$reb['coupon'])->update(array("utime"=>0,"flag"=>0));
                                }else{
                                if($now <= $coupon['etime']){
                                    Db::name("wd_xcx_coupon_user")->where("id",$reb['coupon'])->update(array("utime"=>0,"flag"=>0));
                                }
                                }
                            }

                            $jsondata = unserialize($reb['jsondata']);

                            //处理库存
                            foreach ($jsondata as $rsi) {
                                // 处理销售量
                                $pvid = $rsi['pvid'];
                                $num = $rsi['num'];
                                $pro = Db::name("wd_xcx_pt_pro")->where("id", $pvid)->find();
                                $pronum = $pro['xsl'];
                                $newpronum = $pronum - $num;
                                Db::name("wd_xcx_pt_pro")->where("id", $pvid)->update(array("xsl" => $newpronum));
                                // 减去对应的库存
                                $spid = $rsi['proinfo'];
                                $pro_val = Db::name("wd_xcx_pt_pro_val")->where("id", $spid)->find();
                                $spnum = $pro_val['kc'];
                                $kc = $spnum + $num;
                                Db::name("wd_xcx_pt_pro_val")->where("id", $spid)->update(array("kc" => $kc));
                            }

                            if($reb['source'] == 1){
                                $title = Db::name('wd_xcx_pt_pro')->where('id', $jsondata[0]['id'])->value('title');
                                $user_info = Db::name('wd_xcx_user')->where('suid', $reb['suid'])->find();
                                $jsons = [
                                        'ftitle' => $title,
                                        'num' => $res['join_count'],
                                        'msg' => '时间超时',
                                     ];
                                $jsons = serialize($jsons);
                                sendSubscribe($uniacid, 5, $user_info['openid'], $jsons);  //模板消息发送
                            }
                        }
                    }
                }else{
                    Db::name("wd_xcx_pt_share")->where("id",$res["id"])->update(array("flag"=>4));
                }
            }
        }
    }
    //单个图片上传操作
    function onepic_uploade($file){
        $thumb = request()->file($file);
        if(isset($thumb)){
            $dir = upload_img();
            $info = $thumb->validate(['ext'=>'jpg,png,gif,jpeg'])->move($dir);
            if($info){
                $imgurl = ROOT_HOST."/upimages/".date("Ymd",time())."/".$info->getFilename();
                return $imgurl;
            }
        }
    }
    //规格图片上传
    public function imgupload(){
        $uniacid = input("uniacid");
        $url = getRemoteType($uniacid, 0, 2);
        return $url;

        // $remote = Db::name("wd_xcx_base")->where("uniacid",$uniacid)->field("remote")->find()['remote'];
        // if(!$remote){
        //     $remote = 1;
        // }
        // $groupid = 0;
        // if($remote == 1){
        //     $files = request()->file('');
        //     foreach($files as $file){
        //         // 移动到框架应用根目录/public/upimages/ 目录下        
        //         $info = $file->validate(['ext'=>'jpg,png,gif,jpeg'])->move(ROOT_PATH . 'public' . DS . 'upimages');
        //         if($info){
        //             $url =  "/upimages/".date("Ymd",time())."/".$info->getFilename();
        //             $arr = array("url"=>$url);
        //             return json_encode($arr);
        //         }else{
        //             // 上传失败获取错误信息
        //             return $this->error($file->getError()) ;
        //         }
        //     }
        // }else if($remote == 2){
        //     $qiniu_info = Db::name("wd_xcx_remote")->where("type",2)->where("uniacid",$uniacid)->find();
        //     $file = $_FILES['uploadfile']['tmp_name'];
        //     $is_img = getimagesize($file);
        //     if($is_img){
        //     }
        //     $oringal_name = $_FILES['uploadfile']['name'];

        //     $pathinfo = pathinfo($oringal_name);
        //     // var_dump($pathinfo);exit;
        //     // 要上传图片的本地路径
        //     $ext = $pathinfo['extension'];
        //     $key = 'upimages/'.md5(uniqid(microtime(true),true)).'.'.$ext;

        //     // 需要填写你的 Access Key 和 Secret Key
        //     $accessKey = $qiniu_info['ak'];
        //     $secretKey = $qiniu_info['sk'];
        //     // 构建鉴权对象
        //     $auth = new Auth($accessKey, $secretKey);
        //     // 要上传的空间
        //     $bucket = $qiniu_info['bucket'];
        //     $domain = $qiniu_info['domain'];
        //     $token = $auth->uploadToken($bucket);
        //     // 初始化 UploadManager 对象并进行文件的上传
        //     $uploadMgr = new UploadManager();
        //     // 调用 UploadManager 的 putFile 方法进行文件的上传
        //     list($ret, $err) = $uploadMgr->putFile($token, $key, $file);

        //     if ($err !== null) {
        //         echo ["err"=>1,"msg"=>$err,"data"=>""];
        //     } else {
        //         $arr = array("url"=>$qiniu_info['domain'].'/'.$ret['key']);
        //         return json_encode($arr);
        //     }
        // }
    }
    //多图片上传
    public function imgupload_duo(){
        $data['randid'] = input('randid');
        $files = request()->file('');
        foreach($files as $file){
            // 移动到框架应用根目录/public/upimages/ 目录下        
            $info = $file->validate(['ext'=>'jpg,png,gif,jpeg'])->move(ROOT_PATH . 'public' . DS . 'upimages');
            if($info){
                $data['url'] =  ROOT_HOST."/upimages/".date("Ymd",time())."/".$info->getFilename();
                $data['dateline'] = time();
                $res = Db::name('wd_xcx_products_url')->insert($data);
            }else{
                // 上传失败获取错误信息
                return $this->error($file->getError()) ;
            }
        }
    }
    //上传成功后获取图片
    public function getimg(){
        $id = $_POST['id'];
        $allimg = Db::name('wd_xcx_products_url')->where("randid",$id)->select();
        if($allimg){
            return $allimg;
        }
    }
    public function del_img(){
        $id = input("id");
        $res = Db::name('wd_xcx_products_url')->where('id', $id)->delete();
        if($res){
            return 1;
        }else{
            $this->error("删除失败！");
        }
    }
    //需要使用证书的请求
    function postXmlSSLCurl($xml,$url,$second=30,$uniacid)
    {
        $ch = curl_init();
        //超时时间
        curl_setopt($ch,CURLOPT_TIMEOUT,$second);
        //这里设置代理，如果有的话
        //curl_setopt($ch,CURLOPT_PROXY, '8.8.8.8');
        //curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
        //设置header
        curl_setopt($ch,CURLOPT_HEADER,FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
        //设置证书
        //使用证书：cert 与 key 分别属于两个.pem文件
        //默认格式为PEM，可以注释
        $SSLCERT_PATH = ROOT_PATH.'public/Cert/'.$uniacid.'/qq_apiclient_cert.pem';//证书路径
        $SSLKEY_PATH =  ROOT_PATH.'public/Cert/'.$uniacid.'/qq_apiclient_key.pem';//证书路径
        curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
        curl_setopt($ch,CURLOPT_SSLCERT, $SSLCERT_PATH);
        //默认格式为PEM，可以注释
        curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
        curl_setopt($ch,CURLOPT_SSLKEY, $SSLKEY_PATH);
        //post提交方式
        curl_setopt($ch,CURLOPT_POST, true);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$xml);
        $data = curl_exec($ch);
        //返回结果
        if($data){
            curl_close($ch);
            return $data;
        }
        else {
            $error = curl_errno($ch);
            echo "curl出错，错误码:$error"."<br>";
            curl_close($ch);
            return false;
        }
    }
    private function xmlToArray($xml) {  

        //禁止引用外部xml实体   

        libxml_disable_entity_loader(true);  

        $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);  

        $val = json_decode(json_encode($xmlstring), true);  

        return $val;  

    }


}
