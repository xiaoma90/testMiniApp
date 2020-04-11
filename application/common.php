<?php
error_reporting(E_ERROR | E_PARSE );
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;
use app\index\controller\WinXinRefund;

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------
// 应用公共文件
//定义上传图片的默认路径
function upload_img()
{
    //1.设置上传路径
    $dir = ROOT_PATH . "public/upimages/";
    return $dir;
}

//检查是否登录
function check_login()
{
    $uid = Session::get('uid');
    if (isset($_SESSION['uid'])) {
        $uid = $_SESSION['uid'];
    } else {
        return false;
    }

    // dump($_SESSION);die;
    // var_dump($uid);exit;

    // 检测更新
    $version = 'index/controller/version.php';
    $ver = include($version);
    $ver = $ver['ver'];
    if(PLATFORM == 'wn'){
        $ver = substr($ver, 1);
    }else{
        $ver = str_replace('V', '', $ver);
    }
    if (!defined('VERSION_APP')) {
        define("VERSION_APP", $ver);
    }
    if (!$uid) {
        return false;
    } else {
        return true;
    }
}

//检测用户组
function check_group()
{
    $uid = Session::get('uid');
    if (isset($_SESSION['uid'])) {
        $uid = $_SESSION['uid'];
    } else {
        return false;
    }
    if (!$uid) {
        return false;
    } else {
        $res = Db::name('wd_xcx_admin')->where('uid', $uid)->find();
        if ($res['group'] == 1) {
            return false;
        } else {
            return true;
        }
    }
}

//后台图片上传链接处理（去除网址）
function moveurl($pic)
{
    if (strpos($pic, 'http') !== false) {
        //判断图片非一键模板的图片
        if (strpos($pic, 'http://p2bwp6sww.bkt.clouddn.com') === false) {
            $pic = "/upimages" . explode("/upimages", $pic)[1];
        }
    }
    return $pic;
}

//远程图片链接处理
function remote($uniacid, $url, $type)
{
    $remote_info = Db::name("wd_xcx_base")->where("uniacid", $uniacid)->field("remote, use_remote")->find();  //当前项目设置
    if (!$remote_info) {
        $use_remote = 2;
        $remote = 1;
    } else {
        $use_remote = $remote_info['use_remote'];
        $remote = $remote_info['remote'];
    }
    if ($use_remote == 1) {   //系统设置
        $global_remote = Db::name('wd_xcx_com_about')->where('id', 1)->field('globalremote')->find();
        if (!$global_remote) {
            $remote = 1;
        } else {
            $remote = $global_remote['globalremote'];
        }
        $qiniu = Db::name("wd_xcx_remote")->where("uniacid", -1)->where('type', 2)->find();
        $aliOss = Db::name("wd_xcx_remote")->where("uniacid", -1)->where('type', 3)->find();
    } else {
        $qiniu = Db::name("wd_xcx_remote")->where("uniacid", $uniacid)->where('type', 2)->find();
        $aliOss = Db::name("wd_xcx_remote")->where("uniacid", $uniacid)->where('type', 3)->find();
    }


    if ($remote == 1) {
        if ($type == 1) {   //1是取   2是写
            if (strpos($url, 'http') === false) {
                $host_rul = ROOT_HOST;
                $temp_a = explode(":", $host_rul);

                if ($temp_a[0] == 'http') {
                    $temp_a[0] = 'https';
                    $host_rul = implode(':', $temp_a);
                }


                if (substr($url, 0, 1) == '/') {
                    if (strpos($url, 'addons') === false) {
                        $url = STATIC_ROOT.$url;
                    }
                    $url = $host_rul  .$url;
                } else {
                    if (strpos($url, 'addons') === false) {
                        $url = STATIC_ROOT.'/'.$url;
                    }
                    $url = $host_rul  . '/' . $url;
                }

            } else {
                if (strpos($url, 'addons') === false) {
                    $host_url = $_SERVER['HTTP_HOST'];
                    if(strpos($url, $host_url) !== false){
                        $temp_a = explode($host_url, $url);
                        if (strpos($url, 'upimages') !== false) {
                            $img = '/upimages'.explode('/upimages', $temp_a[1])[1];
                        }else{
                            $img = $temp_a[1];
                        }
                        $url = $temp_a[0].$host_url.STATIC_ROOT.$img;
                    }
                }


                $temp_a = explode(":", $url);

                if ($temp_a[0] == 'http') {
                    $temp_a[0] = 'https';
                    $url = implode(':', $temp_a);
                }
            }
        } else {
            if (strpos($url, 'http') !== false) {
                if (strpos($url, '/addons') !== false || strpos($url, '/upimages') !== false ) {
                    if(strpos($url, '/addons') !== false){
                        $url = "/addons" . explode("/addons", $url)[1];
                    }else{
                        $url = "/upimages" . explode("/upimages", $url)[1];
                    }
                } else if (strpos($url, 'diypage/resource') !== false) {
                    $url = "/diypage/resource" . explode("diypage/resource", $url)[1];
                }
            }
        }
    } else if ($remote == 2) {

        if ($type == 1) {
            if (strpos($url, 'http') === false) {
                if (strpos($url, '/diypage/img/blank.jpg') !== false) {
                    $url = $url;
                } else if (strpos($url, '/diypage/resource/images/diypage/default/default_start.jpg') !== false) {
                    $url = $url;
                } else if (strpos($url, '/diypage/resource/images/diypage/default/tcgg.jpg') !== false) {
                    $url = $url;
                } else {
                    $url = $qiniu['domain'] . '/' . $url;
                }
            }
        } else {
            if (strpos($url, $qiniu['domain']) !== false) {
                $url = explode($qiniu['domain'], $url)[1];
                while (substr($url, 0, 1) == '/') {
                    $url = substr($url, 1);
                }
            }
        }
    } else if ($remote == 3) {
        if ($type == 1) {
            if (strpos($url, 'http') === false) {
                if (strpos($url, '/diypage/img/blank.jpg') !== false) {
                    $url = $url;
                } else if (strpos($url, '/diypage/resource/images/diypage/default/default_start.jpg') !== false) {
                    $url = $url;
                } else if (strpos($url, '/diypage/resource/images/diypage/default/tcgg.jpg') !== false) {
                    $url = $url;
                } else {
                    if ($aliOss && strpos($aliOss['domain'], "http") !== false) {
                        $qianhttp = explode('//', $aliOss['domain'])[0] . '//';
                        $houhttp = explode('//', $aliOss['domain'])[1];
                        while (substr($url, 0, 1) == '/') {
                            $url = substr($url, 1);
                        }
                        if($aliOss['domainIs'] == 1 && $aliOss['domain']){
                            if(strpos($aliOss['domain_bind'], "http") !== false) {
                                $url = $aliOss['domain_bind']. '/' . $url;
                            }else{
                                $url = 'https://' . $aliOss['domain_bind']. '/' . $url;
                            }
                        }else{
                            $url = $qianhttp . $aliOss['bucket'] . '.' . $houhttp . '/' . $url;
                        }
                    } else {                        
                        if ($aliOss) {
                            if($aliOss['domainIs'] == 1 && $aliOss['domain']){
                                $url = 'https://' . $aliOss['domain'] . '/' . $url;
                            }else{
                                $url = 'https://' . $aliOss['bucket'] . '.' . $aliOss['domain'] . '/' . $url;
                            }
                            
                        }
                    }
                }
            }
        } else {
            if ($aliOss && strpos($aliOss['domain'], "http") !== false) {
                $qianhttp = explode('//', $aliOss['domain'])[0] . '//';
                $houhttp = explode('//', $aliOss['domain'])[1] . '//';
                if (strpos($url, $qianhttp . $aliOss['bucket'] . '.' . $houhttp . '/') !== false) {
                    $url = explode($qianhttp . $aliOss['bucket'] . '.' . $houhttp . '/', $url)[1];
                }
            }
        }
    }
    return $url;
}

//根据uid取详情
function all_userinfo($uid)
{
    if (!$uid) {
        return "暂无信息";
    } else {
        $res = Db::name('wd_xcx_admin')->where('uid', $uid)->find();
        return $res;
    }
}

//检测有没有权限对该小程序进行操作
function powerget()
{
    $uid = Session::get('uid');
    $usergroup = $_SESSION['usergroup'];
    $appletid = input("appletid");
    //允许条件:1.登录状态  2.管理员身份  3.小程序管理员身份
    if (!$appletid) {
        return false;   //没有appletid 表示直接输入的网址，精确不到具体的小程序
    }
    if ($usergroup == 1) {   //用户组为1的时候，为普通管理员，需判断该用户是不是该小程序的管理员
        $res = Db::name('wd_xcx_applet')->where('id', $appletid)->find();
        if ($res['adminid'] == $uid) {
            return true;
        } else {
            return false;
        }
    }
    if ($usergroup == 3) {   //用户组为3的时候，为经销商，需判断该用户是不是该小程序的经销商管理员
        $res = Db::name('wd_xcx_applet')->where('id', $appletid)->find();
        if ($res['jxs'] == $uid) {
            return true;
        } else {
            return false;
        }
    }
    if ($usergroup == 2) {
        return true;
    }
}

function getNameAvatar($suid, $uniacid, $excel = 2)
{
    $user = Db::name("wd_xcx_user")->where('suid', $suid)->where('uniacid', $uniacid)->field('nickname, avatar')->find();
    $nickname = $user['nickname'];
    $avatar = $user['avatar'];
    if (empty($nickname) && empty($avatar)) {
        $user = Db::name('wd_xcx_ali_user')->where('suid', $suid)->where('uniacid', $uniacid)->field('nick_name, avatar')->find();
        $nickname = $user['nick_name'];
        $avatar = $user['avatar'];
    }
    if (empty($nickname) && empty($avatar)) {
        $user = Db::name('wd_xcx_baidu_user')->where('suid', $suid)->where('uniacid', $uniacid)->field('nickname, avatar')->find();
        $nickname = $user['nickname'];
        $avatar = $user['avatar'];
    }
    if (empty($nickname) && empty($avatar)) {
        $user = Db::name('wd_xcx_toutiao_user')->where('suid', $suid)->where('uniacid', $uniacid)->field('nickname, avatar')->find();
        $nickname = $user['nickname'];
        $avatar = $user['avatar'];
    }
    if (empty($nickname) && empty($avatar)) {
        $user = Db::name('wd_xcx_qq_user')->where('suid', $suid)->where('uniacid', $uniacid)->field('nickname, avatar')->find();
        $nickname = $user['nickname'];
        $avatar = $user['avatar'];
    }
    if (empty($nickname) && empty($avatar)) {
        $nickname = Db::name('wd_xcx_superuser')->where('id', $suid)->where('uniacid', $uniacid)->value('phone');
        $avatar = "";
    }
    if (!$avatar) {
        $avatar = '/image/static/pay_list_person.png';
        $nickname = '用户**';
    }
    if ($excel == 1) {

        $info = array(
            'nickname' => $nickname,
            'avatar' => $avatar
        );
    } else {
        $info = array(
            'nickname' => rawurldecode($nickname),
            'avatar' => $avatar
        );
    }
    return $info;
}


function orderRefund($uniacid, $orderid, $type)
{   //微信 支付宝 退款
    $result = [];
    if ($type == 'duo') {
        $orderInfo = model('ImsSudu8PageDuoProductsOrder')->get($orderid);
    }
    $app = model('Applet')->get($uniacid);


    if (!$orderInfo) {
        $result['status'] = false;
        $result['msg'] = '订单不存在';
        return $result;
    } else {
        $orderInfo->pay_price = $orderInfo->payprice;
        if ($orderInfo->paytype == 1) {  //微信退款
            $mchid = $app->mchid;   //商户号
            $apiKey = $app->signkey;    //商户的秘钥
            $appid = $app->appID;                 //小程序的id
//            $appkey = $app->appSecret;            //小程序的秘钥
            $openid = 'openid';    //申请者的openid
            $outTradeNo = $orderInfo->order_id;
            $totalFee = intval($orderInfo->pay_price * 100);  //申请了提现多少钱
            $outRefundNo = $orderInfo->order_id; //商户订单号
            $refundFee = intval($orderInfo->pay_price * 100);  //申请了提现多少钱
            $SSLCERT_PATH = ROOT_PATH . 'public/Cert/' . $uniacid . '/apiclient_cert.pem';//证书路径
            $SSLKEY_PATH = ROOT_PATH . 'public/Cert/' . $uniacid . '/apiclient_key.pem';//证书路径
            $opUserId = $mchid;//商户号
//            include "index/controller/WinXinRefund.php";
            $weixinpay = new WinXinRefund($openid, $outTradeNo, $totalFee, $outRefundNo, $refundFee, $SSLCERT_PATH, $SSLKEY_PATH, $opUserId, $appid, $apiKey);
            $return = $weixinpay->refund();
            if (!$return) {
                $result['status'] = false;
                $result['msg'] = '微信退款失败， 请检查系统设置->微信小程序相关配置';
                return $result;
            } else {
                $result['status'] = true;
                $result['msg'] = '微信退款成功';
                return $result;
            }


        } elseif ($orderInfo->paytype == 2) {  //支付宝退款
            Vendor('alipaysdk.aop.AopClient');
            Vendor('alipaysdk.aop.request.AlipayTradeRefundRequest');

            $aop = new \AopClient ();

            $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
            $aop->appId = $app->ali_appID;
            $aop->rsaPrivateKey = $app->ali_private_key;
            $aop->alipayrsaPublicKey = $app->ali_public_key;
            $aop->apiVersion = '1.0';
            $aop->signType = 'RSA2';
            $aop->postCharset = 'UTF-8';
            $aop->format = 'json';

            $request = new \AlipayTradeRefundRequest ();
            $request->setBizContent("{'refund_amount':" . $orderInfo->pay_price . ", 'out_trade_no': " . $orderInfo->order_id . "}");
            $result = $aop->execute($request);
            $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
            $resultCode = $result->alipay_trade_refund_response->code;
            if (!empty($resultCode) && $resultCode == 10000) {
                $res['status'] = true;
                $res['msg'] = '支付宝退款成功';
                return $res;
            } else {
                $res['status'] = false;
                $res['msg'] = '支付宝退款失败， 请检查系统设置->支付宝小程序设置';
                return $res;
            }
        } elseif ($orderInfo->paytype == 3) {   //百度退款
            $pay_info = unserialize($orderInfo->pay_info);
            require_once(ROOT_PATH . 'application/api/controller/bdpay/Autoloader.php');
            $params = [
                'method' => 'nuomi.cashier.applyorderrefund',
                'orderId' => intval($pay_info['orderId']),
                'userId' => intval($pay_info['userId']),
                'refundType' => '1',
                'refundReason' => '订单退款',
                'tpOrderId' => $orderInfo->order_id,
                'appKey' => $app->baidu_pay_appkey
            ];
            $rsaSign = \NuomiRsaSign::genSignWithRsa($params, $app->baidu_private_key);
            $params['rsaSign'] = $rsaSign;
            $url = 'https://nop.nuomi.com/nop/server/rest';
            $res = $this->_Postrequest($url, $params);
            $res = json_decode($res, true);
            if ($res) {
                if ($res['errno'] == 0) {
                    $return = true;
                } else {
                    $this->error('退款失败!请检查系统设置->百度小程序设置');
                    exit;
                }
            } else {
                $this->error('退款失败!请检查系统设置->百度小程序设置');
                exit;
            }
        } elseif ($orderInfo->paytype == 4) {   //QQ退款
            $pay_info = unserialize($orderInfo->pay_info);
            require_once(ROOT_PATH . 'application/api/controller/bdpay/Autoloader.php');
            $params = [
                'method' => 'nuomi.cashier.applyorderrefund',
                'orderId' => intval($pay_info['orderId']),
                'userId' => intval($pay_info['userId']),
                'refundType' => '1',
                'refundReason' => '订单退款',
                'tpOrderId' => $orderInfo->order_id,
                'appKey' => $app->baidu_pay_appkey
            ];
            $rsaSign = \NuomiRsaSign::genSignWithRsa($params, $app->baidu_private_key);
            $params['rsaSign'] = $rsaSign;
            $url = 'https://nop.nuomi.com/nop/server/rest';
            $res = $this->_Postrequest($url, $params);
            $res = json_decode($res, true);
            if ($res) {
                if ($res['errno'] == 0) {
                    $return = true;
                } else {
                    $this->error('退款失败!请检查系统设置->百度小程序设置');
                    exit;
                }
            } else {
                $this->error('退款失败!请检查系统设置->百度小程序设置');
                exit;
            }
        }
    }
}

function add_all_pay($uniacid, $price, $suid)
{
    $userinfo = Db::name('wd_xcx_superuser')->where('uniacid', $uniacid)->where('id', $suid)->field('allpay,grade')->find();
    $allpay = round($price + floatval($userinfo['allpay']), 2);
    Db::name('wd_xcx_superuser')->where('id', $suid)->update(array('allpay' => $allpay));
}

//判断会员等级
function check_vip_grade($uniacid, $suid)
{
    $userinfo = Db::name('wd_xcx_superuser')->where('uniacid', $uniacid)->where('id', $suid)->find();
    $userinfo['allpays'] = round($userinfo['allpay'] + $userinfo['virtualpay'], 2);
    if ($userinfo['grade'] >= 1) {
        $result = Db::name('wd_xcx_vipgrade')->where('uniacid', $uniacid)->where('status', 1)->order('grade desc')->select();  //所有会员等级，包括0
    } else {
        $result = Db::name('wd_xcx_vipgrade')->where('uniacid', $uniacid)->where('status', 1)->where('grade', '>', 1)->order('grade desc')->select();  //所有会员等级，包括0
    }
    if ($result) {
        foreach ($result as $k => $v) {
            if ($v['grade'] > $userinfo['grade']) {
                if (floatval($userinfo['allpays']) >= floatval($v['upgrade'])) {
                    $receive = [];
                    $receive['vid'] = $v['id'];
                    $receive['uniacid'] = $uniacid;
                    if ($userinfo['grade'] == 0) {
                        $vipid = time() . '' . rand(100000, 999999);
                        $data['vipid'] = $vipid;
                        $data['vipcreatetime'] = time();
                    }
                    $data['grade'] = $v['grade'];

                    if ($v['score_feedback_flag'] == 1) {
                        if ($v['score_feedback'] > 0) {
                            $receive['score'] = $v['score_feedback'];
                            $data['score'] = $userinfo['score'] + $v['score_feedback'];
                            $score_data = array(
                                "uniacid" => $uniacid,
                                "orderid" => '',
                                "suid" => $userinfo['id'],
                                "type" => "add",
                                "score" => $v['score_feedback'],
                                "message" => "会员等级回馈积分",
                                "creattime" => time()
                            );
                            Db::name('wd_xcx_score')->insert($score_data);
                        }
                    }
                    if ($v['coupon_flag'] == 1) {
                        $coupon_give = unserialize($v['coupon_give']);
                        if (count($coupon_give) > 0) {
                            $receive['coupon'] = [];
                            foreach ($coupon_give as $k => $v) {
                                for ($i = 0; $i < $v['coupon_num']; $i++) {
                                    $coup = [];
                                    $cid = $v['coupon_id'];
                                    //判断优惠券是否为系统发放
                                    $couponinfo = Db::name('wd_xcx_coupon')->where('uniacid', $uniacid)->where('id', $cid)->where('give_type', 'neq', 2)->find();
                                    if($couponinfo){
                                        $use_contents = unserialize($couponinfo['use_contents']);

                                        $couponinfo['use_goods_contents'] = unserialize($couponinfo['use_goods_contents']);
                                        $couponinfo['use_type'] = $use_contents['use_type'];
                                        $coup['uniacid'] = $uniacid;
                                        $coup['suid'] = $userinfo['id'];
                                        $coup['cid'] = $cid;
                                        if($use_contents['use_type'] == 1) {
                                            $coup['btime'] = strtotime(date("Y-m-d")); //当天开始时间戳
                                            $coup['etime'] = strtotime(date("Y-m-d")) + 3600 * 24 * $use_contents['use_time'];
                                        }else if($use_contents['use_type'] == 2){
                                            $coup['btime'] = strtotime(date("Y-m-d")) + 3600 * 24; //次日开始时间戳为今天开始时间戳加一天时间戳
                                            $coup['etime'] = strtotime(date("Y-m-d")) + 3600 * 24 * ($use_contents['use_time'] + 1); //今天0点时间戳加上n+1天的时间戳
                                        }else{
                                            $use_time = explode(',', $use_contents['use_time']);
                                            $coup['btime'] = $use_time[0];
                                            $coup['etime'] = $use_time[1];
                                        }
                                        $coup['title'] = $couponinfo['title'];
                                        $coup['pay_money'] = $couponinfo['pay_money'];
                                        $coup['price'] = $couponinfo['price'];
                                        $coup['color'] = $couponinfo['color'];
                                        $coup['ltime'] = time();
                                        $coupon_id = Db::name('wd_xcx_coupon_user')->insertGetId($coup);
                                    }
                                }
                                $receive['coupon'][$k]['coupon_id'] = $coupon_id;
                                $receive['coupon'][$k]['coupon_num'] = $v['coupon_num'];
                            }
                            $receive['coupon'] = serialize($receive['coupon']);
                        }
                    }
                    $receive['suid'] = $suid;
                    Db::name('wd_xcx_vip_receive')->insert($receive);
                    Db::name('wd_xcx_superuser')->where('uniacid', $uniacid)->where('id', $suid)->update($data);
                    break;
                }
            }
        }
    }
}

//模板消息
function tpl_send($uniacid, $flag, $openid, $source, $formId, $jsons) //$flag  1购买成功 2拼团成功 3会员卡开通成功 4会员卡审核通知 5多商户审核通知 6万能表单审核结果通知 7分销商审核通过 8退款成功通知 9积分兑换成功通知 10点餐成功通知 12多商户打款通知
{ //$source 1微信 2支付宝 3H5 4百度 5头条 6QQ
    $fields = "";
    if ($source == 1) {
        $fields = "appID,appSecret";
    } else if ($source == 2) {
        $fields = "baidu_appkey,baidu_appSecret";
    } else if ($source == 4) {
        $fields = "baidu_appkey,baidu_appSecret";
    } else if ($source == 5) {
        $fields = "bdance_appID,bdance_appSecret";
    } else if ($source == 6) { //QQ
        $fields = "qq_appid,qq_appsecret";
    }
    if ($fields) {
        $applet = Db::name('wd_xcx_applet')->where("id", $uniacid)->field($fields)->find();

        if ($applet) {
            $mid = Db::name('wd_xcx_message')->where("uniacid", $uniacid)->where('flag', $flag)->find();
            if ($mid && isset($mid['mid']) && $mid['mid'] != "") {
                $jsons = unserialize($jsons);

                if ($source == 1) {
                    $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $applet['appID'] . "&secret=" . $applet['appSecret'];
                    $a_token = _Getrequest($url);
                    if ($a_token) {
                        $url_m = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=" . $a_token['access_token'];

                        $mids = $mid['mid'];
                        $furl = $mid['url'];
                        $ftime = date('Y-m-d H:i:s', time());

                        if ($flag == 1) {//1购买成功
                            $post_info = '{ 
                              "touser": "' . $openid . '",  
                              "template_id": "' . $mids . '", 
                              "page": "' . $furl . '",         
                              "form_id": "' . $formId . '",         
                              "data": {
                                  "keyword1": {
                                      "value": "' . $jsons['orderid'] . '", 
                                      "color": "#173177"
                                  }, 
                                  "keyword2": {
                                      "value": "' . $jsons['fprice'] . '元",
                                      "color": "#173177"
                                  }, 
                                  "keyword3": {
                                      "value":  "' . $jsons['fmsg'] . '", 
                                      "color": "#173177"
                                  } , 
                                  "keyword4": {
                                      "value": "' . $ftime . '", 
                                      "color": "#173177"
                                  } 
                              },
                              "emphasis_keyword": "" 
                            }';
                        } else if ($flag == 2) { //2拼团成功
                            $order_id = $jsons['order_id'];
                            $fps = $jsons['fps'];
                            $fpro = $jsons['fpro'];
                            $fprice = $jsons['fprice'];
                            $fpy = $jsons['fpy'];
                            $fprice = $jsons['fprice'];
                            $fpy = $jsons['fpy'];
                            $fnum = $jsons['fnum'];
                            $fpriceall = $jsons['fpriceall'];
                            $fmsg = $jsons['fmsg'];

                            $post_info = '{
                                  "touser": "' . $openid . '",
                                  "template_id": "' . $mids . '",
                                  "page": "' . $furl . '",
                                  "form_id": "' . $formId . '",
                                  "data": {
                                      "keyword1": {
                                          "value": "' . $order_id . '",
                                          "color": "#173177"
                                      },
                                      "keyword2": {
                                          "value": "' . $fps . '",
                                          "color": "#173177"
                                      },
                                      "keyword3": {
                                          "value": "' . $fpro . '",
                                          "color": "#173177"
                                      },
                                      "keyword4": {
                                          "value": "' . $fprice . '",
                                          "color": "#173177"
                                      },
                                      "keyword5": {
                                          "value": "' . $fpy . '",
                                          "color": "#173177"
                                      },
                                      "keyword6": {
                                          "value": "' . $fnum . '",
                                          "color": "#173177"
                                      },
                                      "keyword7": {
                                          "value": "' . $fpriceall . '",
                                          "color": "#173177"
                                      },
                                      "keyword8": {
                                          "value": "' . $ftime . '",
                                          "color": "#173177"
                                      },
                                      "keyword9": {
                                          "value": "' . $fmsg . '",
                                          "color": "#173177"
                                      }
                                  },
                                  "emphasis_keyword": " "
                                }';
                        } else if ($flag == 3) {  //会员卡开通成功
                            $ftitle = $jsons['cardname'];
                            $fvipid = $jsons['vipid'];
                            $frealname = $jsons['name'];
                            $post_info = '{
                                         "touser": "' . $openid . '",  
                                         "template_id": "' . $mids . '", 
                                         "page": "' . $furl . '",          
                                         "form_id": "' . $formId . '",         
                                         "data": {
                                             "keyword1": {
                                                 "value": "' . $ftitle . '", 
                                                 "color": "#173177"
                                             }, 
                                             "keyword2": {
                                                 "value": "' . $fvipid . '", 
                                                 "color": "#173177"
                                             }, 
                                             "keyword3": {
                                                 "value": "' . $ftime . '", 
                                                 "color": "#173177"
                                             }, 
                                             "keyword4": {
                                                 "value": "' . $frealname . '", 
                                                 "color": "#173177"
                                             } 
                                         },
                                         "emphasis_keyword": "" 
                                       }';
                        } else if ($flag == 4) {  //4会员卡审核通知
                            $applytime = $jsons['applytime'];
                            $jieguo = $jsons['jieguo'];

                            $post_info = '{
                                      "touser": "' . $openid . '",  
                                      "template_id": "' . $mids . '", 
                                      "page": "' . $furl . '",          
                                      "form_id": "' . $formId . '",         
                                      "data": {
                                          "keyword1": {
                                              "value": "' . $jieguo . '", 
                                              "color": "#173177"
                                          },
                                          "keyword2": {
                                              "value": "' . $applytime . '", 
                                              "color": "#173177"
                                          },
                                          "keyword3": {
                                              "value": "' . $ftime . '", 
                                              "color": "#173177"
                                          }
                                      },
                                      "emphasis_keyword": "" 
                                    }';

                        } else if ($flag == 5) { //5多商户审核通知
                            $tInfo = $jsons['tInfo'];

                            $post_info = '{
                                "touser": "' . $openid . '",  
                                "template_id": "' . $mids . '",    
                                "page": "' . $furl . '",          
                                "form_id": "' . $formId . '",  
                                "data": {
                                    "keyword1": {
                                        "value": "' . $tInfo . '", 
                                        "color": "#173177"
                                    }, 
                                    "keyword2": {
                                        "value": "' . $ftime . '", 
                                        "color": "#173177"
                                        }                
                                },
                                "emphasis_keyword": "" 
                            }';
                        } else if ($flag == 6) { //6万能表单审核结果通知
                            $post_info = '{
                              "touser": "' . $openid . '",  
                              "template_id": "' . $mids . '", 
                              "page": "' . $furl . '",          
                              "form_id": "' . $formId . '",         
                              "data": {
                                  "keyword1": {
                                      "value": "审核通过", 
                                      "color": "#173177"
                                  }, 
                                  "keyword2": {
                                      "value": "' . date("Y-m-d H:i:s", $jsons['creattime']) . '", 
                                      "color": "#173177"
                                  },
                                  "keyword3":{
                                      "value": "' . date("Y-m-d H:i:s", $jsons['vtime']) . '",
                                      "color": "#173177"
                                  }
                              },
                              "emphasis_keyword": "keyword1.DATA" 
                            }';
                        } else if ($flag == 7) { //7分销商审核通过
                            $post_info = '{ 
                              "touser": "' . $openid . '",  
                              "template_id": "' . $mids . '", 
                              "page": "' . $furl . '",         
                              "form_id": "' . $formId . '",         
                              "data": {
                                  "keyword1": {
                                      "value": "' . $jsons['truename'] . '", 
                                      "color": "#173177"
                                  }, 
                                  "keyword2": {
                                      "value": "' . $jsons['content'] . '", 
                                      "color": "#173177"
                                  }, 
                                  "keyword3": {
                                      "value": "' . $jsons['creattime'] . '", 
                                      "color": "#173177"
                                  },
                                  "keyword4": {
                                      "value": "' . $jsons['notice'] . '", 
                                      "color": "#173177"
                                  }
                              },
                              "emphasis_keyword": "" 
                            }';
                        } else if ($flag == 8) { //8退款成功通知
                            $post_info = '{ 
                              "touser": "' . $openid . '",  
                              "template_id": "' . $mids . '", 
                              "page": "' . $furl . '",         
                              "form_id": "' . $formId . '",         
                              "data": {
                                  "keyword1": {
                                      "value": "' . $jsons['orderid'] . '", 
                                      "color": "#173177"
                                  }, 
                                  "keyword2": {
                                      "value": "' . $jsons['ftitle'] . '", 
                                      "color": "#173177"
                                  }, 
                                  "keyword3": {
                                      "value": "' . $jsons['fprice'] . '元", 
                                      "color": "#173177"
                                  },
                                  "keyword4": {
                                      "value": "' . $ftime . '", 
                                      "color": "#173177"
                                  },
                                  "keyword5": {
                                      "value": "' . $jsons['refund_type'] . '", 
                                      "color": "#173177"
                                  } 
                              },
                              "emphasis_keyword": "" 
                            }';
                        } else if ($flag == 9) { //9积分兑换成功通知

                            $ftitle = $jsons['ftitle'];
                            $fprice = $jsons['fprice'];
                            $fscore = $jsons['fscore'];
                            $post_info = '{
                                      "touser": "' . $openid . '",  
                                      "template_id": "' . $mids . '", 
                                      "page": "' . $furl . '",          
                                      "form_id": "' . $formId . '",         
                                      "data": {
                                          "keyword1": {
                                              "value": "' . $ftitle . '", 
                                              "color": "#173177"
                                          }, 
                                          "keyword2": {
                                              "value": "' . $fprice . '", 
                                              "color": "#173177"
                                          }, 
                                          "keyword3": {
                                              "value": "' . $ftime . '", 
                                              "color": "#173177"
                                          }, 
                                          "keyword4": {
                                              "value": "' . $fscore . '", 
                                              "color": "#173177"
                                          } 
                                      },
                                      "emphasis_keyword": "keyword1.DATA" 
                                    }';
                        } else if ($flag == 10) { //10点餐成功通知
                            $ftime = date('Y-m-d H:i:s', time());

                            $fscore_name = $jsons['fscore_name'];
                            $fzh = $jsons['fzh'];
                            $forder_id = $jsons['forder_id'];
                            $fcontent = $jsons['fcontent'];
                            $fpaymoney = $jsons['fpaymoney'];

                            $post_info = '{
                              "touser": "' . $openid . '",  
                              "template_id": "' . $mids . '", 
                              "page": "' . $furl . '",          
                              "form_id": "' . $formId . '",         
                              "data": {
                                  "keyword1": {
                                      "value": "' . $fscore_name . '", 
                                      "color": "#173177"
                                  }, 
                                  "keyword2": {
                                      "value": "' . $fzh . '", 
                                      "color": "#173177"
                                  }, 
                                  "keyword3": {
                                      "value": "' . $forder_id . '", 
                                      "color": "#173177"
                                  } , 
                                  "keyword4": {
                                      "value": "' . $fcontent . '", 
                                      "color": "#173177"
                                  }, 
                                  "keyword5": {
                                      "value": "' . $fpaymoney . '元", 
                                      "color": "#173177"
                                  }, 
                                  "keyword6": {
                                      "value": "' . $ftime . '", 
                                      "color": "#173177"
                                  }  
                              },
                              "emphasis_keyword": "" 
                            }';
                        } else if ($flag == 11) { //11万能表单提交成功

                            $ftitle = $jsons['ftitle'];
                            $fmsg = $jsons['fmsg'];
                            $post_info = '{
                                      "touser": "' . $openid . '",
                                      "template_id": "' . $mids . '",
                                      "page": "' . $furl . '",
                                      "form_id": "' . $formId . '",
                                      "data": {
                                          "keyword1": {
                                              "value": "' . $ftitle . '",
                                              "color": "#173177"
                                          },
                                          "keyword2": {
                                              "value": "' . $ftime . '",
                                              "color": "#173177"
                                          },
                                          "keyword3": {
                                              "value": "' . $fmsg . '",
                                              "color": "#173177"
                                          }
                                      },
                                      "emphasis_keyword": "keyword1.DATA"
                                    }';

                        } else if ($flag == 12) { //12多商户打款通知
                            $fmoney = $jsons['fmoney'];
                            $ftype = $jsons['ftype'];
                            $post_info = '{
                                "touser": "' . $openid . '",  
                                "template_id": "' . $mids . '",        
                                "page": "' . $furl . '",         
                                "form_id": "' . $formId . '", 
                                "data": {
                                    "keyword1": {
                                        "value": "' . $ftime . '", 
                                        "color": "#173177"
                                    }, 
                                    "keyword2": {
                                        "value": "' . $fmoney . '", 
                                        "color": "#173177"
                                    }, 
                                    "keyword3": {
                                        "value": "' . $ftype . '", 
                                        "color": "#173177"
                                    }                   
                                },
                                "emphasis_keyword": "" 
                            }';
                        }
                       $res = _Postrequest($url_m, $post_info);
                    }
                } else if ($source == 2) {
                } else if ($source == 4) {
                    $url = "https://openapi.baidu.com/oauth/2.0/token?grant_type=client_credentials&client_id=" . $applet['baidu_appkey'] . "&client_secret=" . $applet['baidu_appSecret'] . "&scope=smartapp_snsapi_base";
                    $a_token = _Getrequest($url);
                    if ($a_token) {
                        $url_m = "https://openapi.baidu.com/rest/2.0/smartapp/template/sendmessage?access_token=" . $a_token['access_token'];

                        $mids = $mid['bd_mid'];
                        $furl = $mid['bd_url'];
                        if ($flag == 1) {//1购买成功

                        } else if ($flag == 2) { //2拼团成功

                        } else if ($flag == 3) {  //会员卡开通成功
                            $ftitle = $jsons['cardname'];
                            $ftime = date('Y-m-d H:i:s', time());
                            $fvipid = $jsons['vipid'];
                            $frealname = $jsons['name'];
                            $post_info = '{
                                         "touser_openId": "' . $openid . '",  
                                         "template_id": "' . $mids . '", 
                                         "page": "' . $furl . '",          
                                         "scene_id": "' . $formId . '",         
                                         "scene_type": "1",         
                                         "data": {
                                             "keyword1": {
                                                 "value": "' . $ftitle . '"
                                             }, 
                                             "keyword2": {
                                                 "value": "' . $fvipid . '"
                                             }, 
                                             "keyword3": {
                                                 "value": "' . $ftime . '"
                                             }, 
                                             "keyword4": {
                                                 "value": "' . $frealname . '"
                                             } 
                                         }
                                       }';
                        } else if ($flag == 4) {  //4会员卡审核通知

                        } else if ($flag == 5) { //5多商户审核通知

                        } else if ($flag == 6) { //6万能表单审核结果通知

                        } else if ($flag == 7) { //7分销商审核通过

                        } else if ($flag == 8) { //8退款成功通知

                        } else if ($flag == 9) { //9积分兑换成功通知


                        } else if ($flag == 10) { //10点餐成功通知

                        } else if ($flag == 12) { //12多商户打款通知

                        }
                        // _Postrequest($url_m, $post_info);


                    }
                } else if ($source == 5) {
                    $url = "https://developer.toutiao.com/api/apps/token?appid=" . $applet['bdance_appID'] . "&secret=" . $applet['bdance_appSecret'] . "&grant_type=client_credential";
                    $a_token = _Getrequest($url);
                    if ($a_token) {
                        $url_m = "https://developer.toutiao.com/api/apps/game/template/send";

                        $mids = $mid['bdance_mid'];
                        $furl = $mid['bdance_url'];
                        $ftime = date('Y-m-d H:i:s', time());
                        $access_token = $a_token['access_token'];
                        $appid = $applet['bdance_appID'];

                        if ($flag == 1) {//1购买成功
                            $jsons['fmsg'] = mb_strlen($jsons['fmsg'], 'UTF8') > 8 ? mb_substr($jsons['fmsg'], 0, 5, 'utf-8') . '...' : $jsons['fmsg'];
                            $post_info = '{ 
                             "access_token": "' . $access_token . '",
                             "app_id": "' . $appid . '",
                              "data": {
                                  "keyword1": {
                                      "value": "' . $ftime . '"
                                  }, 
                                  "keyword2": {
                                      "value": "' . $jsons['fprice'] . '元"
                                  }, 
                                  "keyword3": {
                                      "value":  "' . $jsons['fmsg'] . '"
                                  }, 
                                  "keyword4": {
                                      "value": "' . $jsons['orderid'] . '"
                                  } 
                              },
                              "page": "' . $furl . '",         
                              "form_id": "' . $formId . '",   
                              "touser": "' . $openid . '",  
                              "template_id": "' . $mids . '"
                            }';
                        } else if ($flag == 2) { //2拼团成功
                            $order_id = $jsons['order_id'];
                            $fpro = $jsons['fpro'];
                            $fpro = mb_strlen($fpro, 'UTF8') > 8 ? mb_substr($fpro, 0, 5, 'utf-8') . '...' : $fpro;
                            $fpriceall = $jsons['fpriceall'];
                            $post_info = '{
                                "access_token": "' . $access_token . '",
                                "app_id": "' . $appid . '",
                                  "touser": "' . $openid . '",
                                  "template_id": "' . $mids . '",
                                  "page": "' . $furl . '",
                                  "form_id": "' . $formId . '",
                                  "data": {
                                      "keyword1": {
                                          "value": "' . $order_id . '"
                                      },
                                      "keyword2": {
                                          "value": "' . $fpro . '"
                                      },
                                      "keyword3": {
                                          "value": "' . $fpriceall . '"
                                      },
                                      "keyword4": {
                                          "value": "' . $ftime . '"
                                      }
                                  }
                                }';
                        } else if ($flag == 3) {  //会员卡开通成功
                            $ftitle = $jsons['cardname'];
                            $ftitle = mb_strlen($ftitle, 'UTF8') > 8 ? mb_substr($ftitle, 0, 5, 'utf-8') . '...' : $ftitle;
                            $fvipid = $jsons['vipid'];
                            $frealname = $jsons['name'];
                            $frealname = mb_strlen($frealname, 'UTF8') > 8 ? mb_substr($frealname, 0, 5, 'utf-8') . '...' : $frealname;
                            $post_info = '{
                                        "access_token": "' . $access_token . '",
                                        "app_id": "' . $appid . '",
                                         "touser": "' . $openid . '",  
                                         "template_id": "' . $mids . '", 
                                         "page": "' . $furl . '",          
                                         "form_id": "' . $formId . '",         
                                         "data": {
                                             "keyword1": {
                                                 "value": "' . $ftitle . '"
                                             }, 
                                             "keyword2": {
                                                 "value": "' . $fvipid . '"
                                             }, 
                                             "keyword3": {
                                                 "value": "' . $ftime . '"
                                             }, 
                                             "keyword4": {
                                                 "value": "' . $frealname . '"
                                             } 
                                         }
                                       }';
                        } else if ($flag == 4) {  //4会员卡审核通知
                            $applytime = $jsons['applytime'];
                            $jieguo = $jsons['jieguo'];
                            $post_info = '{
                                    "access_token": "' . $access_token . '",
                                    "app_id": "' . $appid . '",
                                      "touser": "' . $openid . '",  
                                      "template_id": "' . $mids . '", 
                                      "page": "' . $furl . '",          
                                      "form_id": "' . $formId . '",         
                                      "data": {
                                          "keyword1": {
                                              "value": "' . $applytime . '"
                                          },
                                          "keyword2": {
                                              "value": "' . $ftime . '"
                                          },
                                          "keyword3": {
                                              "value": "' . $jieguo . '"
                                          }
                                      }
                                    }';

                        } else if ($flag == 5) { //5多商户审核通知
                            $tInfo = $jsons['tInfo'];

                            $post_info = '{
                                "access_token": "' . $access_token . '",
                                "app_id": "' . $appid . '",
                                "touser": "' . $openid . '",  
                                "template_id": "' . $mids . '",    
                                "page": "' . $furl . '",          
                                "form_id": "' . $formId . '",  
                                "data": {
                                    "keyword1": {
                                        "value": "' . $tInfo . '"
                                    }, 
                                    "keyword2": {
                                        "value": "' . $ftime . '"
                                    }                
                                }
                            }';
                        } else if ($flag == 6) { //6万能表单审核结果通知
                            $post_info = '{
                            "access_token": "' . $access_token . '",
                            "app_id": "' . $appid . '",
                              "touser": "' . $openid . '",  
                              "template_id": "' . $mids . '", 
                              "page": "' . $furl . '",          
                              "form_id": "' . $formId . '",         
                              "data": {
                                  "keyword1": {
                                      "value": "审核通过"
                                  }, 
                                  "keyword2": {
                                      "value": "' . date("Y-m-d H:i:s", $jsons['creattime']) . '"
                                  },
                                  "keyword3":{
                                      "value": "' . date("Y-m-d H:i:s", $jsons['vtime']) . '"
                                  }
                              }
                            }';
                        } else if ($flag == 7) {
                            $jsons['truename'] = mb_strlen($jsons['truename'], 'UTF8') > 8 ? mb_substr($jsons['truename'], 0, 5, 'utf-8') . '...' : $jsons['truename'];
                            $jsons['content'] = mb_strlen($jsons['content'], 'UTF8') > 8 ? mb_substr($jsons['content'], 0, 5, 'utf-8') . '...' : $jsons['content'];
                            $jsons['notice'] = mb_strlen($jsons['notice'], 'UTF8') > 8 ? mb_substr($jsons['notice'], 0, 5, 'utf-8') . '...' : $jsons['notice'];
                            $post_info = '{ 
                            "access_token": "' . $access_token . '",
                            "app_id": "' . $appid . '",
                              "touser": "' . $openid . '",  
                              "template_id": "' . $mids . '", 
                              "page": "' . $furl . '",         
                              "form_id": "' . $formId . '",         
                              "data": {
                                  "keyword1": {
                                      "value": "' . $jsons['truename'] . '"
                                  }, 
                                  "keyword2": {
                                      "value": "' . $jsons['content'] . '"
                                  }, 
                                  "keyword3": {
                                      "value": "' . $jsons['creattime'] . '"
                                  },
                                  "keyword4": {
                                      "value": "' . $jsons['notice'] . '"
                                  }
                              }
                            }';
                        } else if ($flag == 8) { //8退款成功通知
                            $jsons['ftitle'] = mb_strlen($jsons['ftitle'], 'UTF8') > 8 ? mb_substr($jsons['ftitle'], 0, 5, 'utf-8') . '...' : $jsons['ftitle'];
                            $jsons['fprice'] = mb_substr($jsons['fprice'], 3);
                            $post_info = '{ 
                            "access_token": "' . $access_token . '",
                            "app_id": "' . $appid . '",
                              "touser": "' . $openid . '",  
                              "template_id": "' . $mids . '", 
                              "page": "' . $furl . '",         
                              "form_id": "' . $formId . '",         
                              "data": {
                                  "keyword1": {
                                      "value": "' . $ftime . '"
                                  }, 
                                  "keyword2": {
                                      "value": "' . $jsons['ftitle'] . '"
                                  }, 
                                  "keyword3": {
                                      "value": "' . $jsons['orderid'] . '"
                                  },
                                  "keyword4": {
                                      "value": "' . $jsons['fprice'] . '"
                                  }
                              }
                            }';
                        } else if ($flag == 9) { //9积分兑换成功通知

                            $ftitle = $jsons['ftitle'];
                            $ftitle = mb_strlen($ftitle, 'UTF8') > 8 ? mb_substr($ftitle, 0, 5, 'utf-8') . '...' : $ftitle;

                            $fprice = $jsons['fprice'];
                            $fscore = $jsons['fscore'];
                            $post_info = '{
                            "access_token": "' . $access_token . '",
                            "app_id": "' . $appid . '",
                                      "touser": "' . $openid . '",  
                                      "template_id": "' . $mids . '", 
                                      "page": "' . $furl . '",          
                                      "form_id": "' . $formId . '",         
                                      "data": {
                                          "keyword1": {
                                              "value": "' . $ftitle . '"
                                          }, 
                                          "keyword2": {
                                              "value": "' . $fprice . '"
                                          },
                                          "keyword3": {
                                              "value": "' . $ftime . '"
                                          }, 
                                          "keyword4": {
                                              "value": "' . $fscore . '"
                                          } 
                                      }
                                    }';
                        } else if ($flag == 10) { //10点餐成功通知
                            $ftime = date('Y-m-d H:i:s', time());
                            $forder_id = $jsons['forder_id'];
                            $fcontent = $jsons['fcontent'];
                            $fcontent = mb_strlen($fcontent, 'UTF8') > 8 ? mb_substr($fcontent, 0, 5, 'utf-8') . '...' : $fcontent;
                            $fpaymoney = $jsons['fpaymoney'];

                            $post_info = '{
                            "access_token": "' . $access_token . '",
                            "app_id": "' . $appid . '",
                              "touser": "' . $openid . '",  
                              "template_id": "' . $mids . '", 
                              "page": "' . $furl . '",          
                              "form_id": "' . $formId . '",         
                              "data": {
                                  "keyword1": {
                                      "value": "' . $forder_id . '"
                                  }, 
                                  "keyword2": {
                                      "value": "' . $fcontent . '"
                                  }, 
                                  "keyword3": {
                                      "value": "' . $fpaymoney . '"
                                  }, 
                                  "keyword4": {
                                      "value": "' . $ftime . '"
                                  } 
                              }
                            }';
                        } else if ($flag == 11) { //11万能表单提交成功

                            $ftitle = $jsons['ftitle'];
                            $ftitle = mb_strlen($ftitle, 'UTF8') > 8 ? mb_substr($ftitle, 0, 5, 'utf-8') . '...' : $ftitle;

                            $fmsg = $jsons['fmsg'];
                            $fmsg = mb_strlen($fmsg, 'UTF8') > 8 ? mb_substr($fmsg, 0, 5, 'utf-8') . '...' : $fmsg;

                            $post_info = '{
                            "access_token": "' . $access_token . '",
                            "app_id": "' . $appid . '",
                                      "touser": "' . $openid . '",
                                      "template_id": "' . $mids . '",
                                      "page": "' . $furl . '",
                                      "form_id": "' . $formId . '",
                                      "data": {
                                          "keyword1": {
                                              "value": "' . $ftitle . '"
                                          },
                                          "keyword2": {
                                              "value": "' . $ftime . '"
                                          },
                                          "keyword3": {
                                              "value": "' . $fmsg . '"
                                          }
                                      }
                                    }';

                        } else if ($flag == 12) { //12多商户打款通知
                            $fmoney = $jsons['fmoney'];
                            $ftype = $jsons['ftype'];
                            $post_info = '{
                            "access_token": "' . $access_token . '",
                            "app_id": "' . $appid . '",
                                "touser": "' . $openid . '",  
                                "template_id": "' . $mids . '",        
                                "page": "' . $furl . '",         
                                "form_id": "' . $formId . '", 
                                "data": {
                                    "keyword1": {
                                        "value": "' . $ftime . '"
                                    }, 
                                    "keyword2": {
                                        "value": "' . $fmoney . '"
                                    }, 
                                    "keyword3": {
                                        "value": "' . $ftype . '"
                                        
                                    }                   
                                }
                            }';
                        }
                        _Postrequest($url_m, $post_info);
                    }
                } else if ($source == 6) {
                    $url = "https://api.q.qq.com/api/getToken?grant_type=client_credential&appid=" . $applet['qq_appid'] . "&secret=" . $applet['qq_appsecret'];
                    $a_token = _Getrequest($url);
                    if ($a_token) {
                        $url_m = "https://api.q.qq.com/api/json/template/send?access_token=" . $a_token['access_token'];

                        $mids = $mid['qq_mid'];
                        $furl = $mid['qq_url'];
                        $ftime = date('Y-m-d H:i:s', time());

                        if ($flag == 1) {//1购买成功
                            $post_info = '{ 
                              "touser": "' . $openid . '",  
                              "template_id": "' . $mids . '", 
                              "page": "' . $furl . '",         
                              "form_id": "' . $formId . '",         
                              "data": {
                                  "keyword1": {
                                      "value": "' . $jsons['orderid'] . '", 
                                      "color": "#173177"
                                  }, 
                                  "keyword2": {
                                      "value": "' . $jsons['fprice'] . '元",
                                      "color": "#173177"
                                  }, 
                                  "keyword3": {
                                      "value":  "' . $jsons['fmsg'] . '", 
                                      "color": "#173177"
                                  } , 
                                  "keyword4": {
                                      "value": "' . $ftime . '", 
                                      "color": "#173177"
                                  } 
                              },
                              "emphasis_keyword": "" 
                            }';
                        } else if ($flag == 2) { //2拼团成功
                            $order_id = $jsons['order_id'];
                            $fps = $jsons['fps'];
                            $fpro = $jsons['fpro'];
                            $fprice = $jsons['fprice'];
                            $fpy = $jsons['fpy'];
                            $fprice = $jsons['fprice'];
                            $fpy = $jsons['fpy'];
                            $fnum = $jsons['fnum'];
                            $fpriceall = $jsons['fpriceall'];
                            $fmsg = $jsons['fmsg'];

                            $post_info = '{
                                  "touser": "' . $openid . '",
                                  "template_id": "' . $mids . '",
                                  "page": "' . $furl . '",
                                  "form_id": "' . $formId . '",
                                  "data": {
                                      "keyword1": {
                                          "value": "' . $order_id . '",
                                          "color": "#173177"
                                      },
                                      "keyword2": {
                                          "value": "' . $fps . '",
                                          "color": "#173177"
                                      },
                                      "keyword3": {
                                          "value": "' . $fpro . '",
                                          "color": "#173177"
                                      },
                                      "keyword4": {
                                          "value": "' . $fprice . '",
                                          "color": "#173177"
                                      },
                                      "keyword5": {
                                          "value": "' . $fpy . '",
                                          "color": "#173177"
                                      },
                                      "keyword6": {
                                          "value": "' . $fnum . '",
                                          "color": "#173177"
                                      },
                                      "keyword7": {
                                          "value": "' . $fpriceall . '",
                                          "color": "#173177"
                                      },
                                      "keyword8": {
                                          "value": "' . $ftime . '",
                                          "color": "#173177"
                                      },
                                      "keyword9": {
                                          "value": "' . $fmsg . '",
                                          "color": "#173177"
                                      }
                                  },
                                  "emphasis_keyword": " "
                                }';
                        } else if ($flag == 3) {  //会员卡开通成功
                            $ftitle = $jsons['cardname'];
                            $fvipid = $jsons['vipid'];
                            $frealname = $jsons['name'];
                            $post_info = '{
                                         "touser": "' . $openid . '",  
                                         "template_id": "' . $mids . '", 
                                         "page": "' . $furl . '",          
                                         "form_id": "' . $formId . '",         
                                         "data": {
                                             "keyword1": {
                                                 "value": "' . $ftitle . '", 
                                                 "color": "#173177"
                                             }, 
                                             "keyword2": {
                                                 "value": "' . $fvipid . '", 
                                                 "color": "#173177"
                                             }, 
                                             "keyword3": {
                                                 "value": "' . $ftime . '", 
                                                 "color": "#173177"
                                             }, 
                                             "keyword4": {
                                                 "value": "' . $frealname . '", 
                                                 "color": "#173177"
                                             } 
                                         },
                                         "emphasis_keyword": "" 
                                       }';
                        } else if ($flag == 4) {  //4会员卡审核通知
                            $applytime = $jsons['applytime'];
                            $jieguo = $jsons['jieguo'];

                            $post_info = '{
                                      "touser": "' . $openid . '",  
                                      "template_id": "' . $mids . '", 
                                      "page": "' . $furl . '",          
                                      "form_id": "' . $formId . '",         
                                      "data": {
                                          "keyword1": {
                                              "value": "' . $jieguo . '", 
                                              "color": "#173177"
                                          },
                                          "keyword2": {
                                              "value": "' . $applytime . '", 
                                              "color": "#173177"
                                          },
                                          "keyword3": {
                                              "value": "' . $ftime . '", 
                                              "color": "#173177"
                                          }
                                      },
                                      "emphasis_keyword": "" 
                                    }';

                        } else if ($flag == 5) { //5多商户审核通知
                            $tInfo = $jsons['tInfo'];

                            $post_info = '{
                                "touser": "' . $openid . '",  
                                "template_id": "' . $mids . '",    
                                "page": "' . $furl . '",          
                                "form_id": "' . $formId . '",  
                                "data": {
                                    "keyword1": {
                                        "value": "' . $tInfo . '", 
                                        "color": "#173177"
                                    }, 
                                    "keyword2": {
                                        "value": "' . $ftime . '", 
                                        "color": "#173177"
                                        }                
                                },
                                "emphasis_keyword": "" 
                            }';
                        } else if ($flag == 6) { //6万能表单审核结果通知
                            $post_info = '{
                              "touser": "' . $openid . '",  
                              "template_id": "' . $mids . '", 
                              "page": "' . $furl . '",          
                              "form_id": "' . $formId . '",         
                              "data": {
                                  "keyword1": {
                                      "value": "审核通过", 
                                      "color": "#173177"
                                  }, 
                                  "keyword2": {
                                      "value": "' . date("Y-m-d H:i:s", $jsons['creattime']) . '", 
                                      "color": "#173177"
                                  },
                                  "keyword3":{
                                      "value": "' . date("Y-m-d H:i:s", $jsons['vtime']) . '",
                                      "color": "#173177"
                                  }
                              },
                              "emphasis_keyword": "keyword1.DATA" 
                            }';
                        } else if ($flag == 7) { //7分销商审核通过
                            $post_info = '{ 
                              "touser": "' . $openid . '",  
                              "template_id": "' . $mids . '", 
                              "page": "' . $furl . '",         
                              "form_id": "' . $formId . '",         
                              "data": {
                                  "keyword1": {
                                      "value": "' . $jsons['truename'] . '", 
                                      "color": "#173177"
                                  }, 
                                  "keyword2": {
                                      "value": "' . $jsons['content'] . '", 
                                      "color": "#173177"
                                  }, 
                                  "keyword3": {
                                      "value": "' . $jsons['creattime'] . '", 
                                      "color": "#173177"
                                  },
                                  "keyword4": {
                                      "value": "' . $jsons['notice'] . '", 
                                      "color": "#173177"
                                  }
                              },
                              "emphasis_keyword": "" 
                            }';
                        } else if ($flag == 8) { //8退款成功通知
                            $post_info = '{ 
                              "touser": "' . $openid . '",  
                              "template_id": "' . $mids . '", 
                              "page": "' . $furl . '",         
                              "form_id": "' . $formId . '",         
                              "data": {
                                  "keyword1": {
                                      "value": "' . $jsons['orderid'] . '", 
                                      "color": "#173177"
                                  }, 
                                  "keyword2": {
                                      "value": "' . $jsons['ftitle'] . '", 
                                      "color": "#173177"
                                  }, 
                                  "keyword3": {
                                      "value": "' . $jsons['fprice'] . '元", 
                                      "color": "#173177"
                                  },
                                  "keyword4": {
                                      "value": "' . $ftime . '", 
                                      "color": "#173177"
                                  },
                                  "keyword5": {
                                      "value": "' . $jsons['refund_type'] . '", 
                                      "color": "#173177"
                                  } 
                              },
                              "emphasis_keyword": "" 
                            }';
                        } else if ($flag == 9) { //9积分兑换成功通知

                            $ftitle = $jsons['ftitle'];
                            $fprice = $jsons['fprice'];
                            $fscore = $jsons['fscore'];
                            $post_info = '{
                                      "touser": "' . $openid . '",  
                                      "template_id": "' . $mids . '", 
                                      "page": "' . $furl . '",          
                                      "form_id": "' . $formId . '",         
                                      "data": {
                                          "keyword1": {
                                              "value": "' . $ftitle . '", 
                                              "color": "#173177"
                                          }, 
                                          "keyword2": {
                                              "value": "' . $fprice . '", 
                                              "color": "#173177"
                                          }, 
                                          "keyword3": {
                                              "value": "' . $ftime . '", 
                                              "color": "#173177"
                                          }, 
                                          "keyword4": {
                                              "value": "' . $fscore . '", 
                                              "color": "#173177"
                                          } 
                                      },
                                      "emphasis_keyword": "keyword1.DATA" 
                                    }';
                        } else if ($flag == 10) { //10点餐成功通知
                            $ftime = date('Y-m-d H:i:s', time());

                            $fscore_name = $jsons['fscore_name'];
                            $fzh = $jsons['fzh'];
                            $forder_id = $jsons['forder_id'];
                            $fcontent = $jsons['fcontent'];
                            $fpaymoney = $jsons['fpaymoney'];

                            $post_info = '{
                              "touser": "' . $openid . '",  
                              "template_id": "' . $mids . '", 
                              "page": "' . $furl . '",          
                              "form_id": "' . $formId . '",         
                              "data": {
                                  "keyword1": {
                                      "value": "' . $fscore_name . '", 
                                      "color": "#173177"
                                  }, 
                                  "keyword2": {
                                      "value": "' . $fzh . '", 
                                      "color": "#173177"
                                  }, 
                                  "keyword3": {
                                      "value": "' . $forder_id . '", 
                                      "color": "#173177"
                                  } , 
                                  "keyword4": {
                                      "value": "' . $fcontent . '", 
                                      "color": "#173177"
                                  }, 
                                  "keyword5": {
                                      "value": "' . $fpaymoney . '元", 
                                      "color": "#173177"
                                  }, 
                                  "keyword6": {
                                      "value": "' . $ftime . '", 
                                      "color": "#173177"
                                  }  
                              },
                              "emphasis_keyword": "" 
                            }';
                        } else if ($flag == 11) { //11万能表单提交成功

                            $ftitle = $jsons['ftitle'];
                            $fmsg = $jsons['fmsg'];
                            $post_info = '{
                                      "touser": "' . $openid . '",
                                      "template_id": "' . $mids . '",
                                      "page": "' . $furl . '",
                                      "form_id": "' . $formId . '",
                                      "data": {
                                          "keyword1": {
                                              "value": "' . $ftitle . '",
                                              "color": "#173177"
                                          },
                                          "keyword2": {
                                              "value": "' . $ftime . '",
                                              "color": "#173177"
                                          },
                                          "keyword3": {
                                              "value": "' . $fmsg . '",
                                              "color": "#173177"
                                          }
                                      },
                                      "emphasis_keyword": "keyword1.DATA"
                                    }';

                        } else if ($flag == 12) { //12多商户打款通知
                            $fmoney = $jsons['fmoney'];
                            $ftype = $jsons['ftype'];
                            $post_info = '{
                                "touser": "' . $openid . '",  
                                "template_id": "' . $mids . '",        
                                "page": "' . $furl . '",         
                                "form_id": "' . $formId . '", 
                                "data": {
                                    "keyword1": {
                                        "value": "' . $ftime . '", 
                                        "color": "#173177"
                                    }, 
                                    "keyword2": {
                                        "value": "' . $fmoney . '", 
                                        "color": "#173177"
                                    }, 
                                    "keyword3": {
                                        "value": "' . $ftype . '", 
                                        "color": "#173177"
                                    }                   
                                },
                                "emphasis_keyword": "" 
                            }';
                        }
                        _Postrequest($url_m, $post_info);
                    }
                }
            }
        }
    }
}

function _Getrequest($url)
{
    //curl完成
    $curl = curl_init();
    //设置curl选项
    $header = array(
        "authorization: Basic YS1sNjI5dmwtZ3Nocmt1eGI2Njp1TlQhQVFnISlWNlkySkBxWlQ=",
        "content-type: application/json",
        "cache-control: no-cache",
        "postman-token: cd81259b-e5f8-d64b-a408-1270184387ca"
    );
    curl_setopt($curl, CURLOPT_HEADER, 1);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    curl_setopt($curl, CURLOPT_URL, $url);//URL
    curl_setopt($curl, CURLOPT_HEADER, 0);             // 0：不返回头信息
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);//设置超时时间
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    // 发出请求
    $response = curl_exec($curl);
    if (false === $response) {
        echo '<br>', curl_error($curl), '<br>';
        return false;
    }
    curl_close($curl);
    $forms = stripslashes(html_entity_decode($response));
    $forms = json_decode($forms, TRUE);
    return $forms;
}

function _Postrequest($url, $data, $ssl = true)
{
    $headers = [
        "Content-type: application/json;charset='utf-8'"
    ];
    //curl完成
    $curl = curl_init();
    //设置curl选项
    curl_setopt($curl, CURLOPT_URL, $url);//URL
    $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:38.0) Gecko/20100101 Firefox/38.0 FirePHP/0.7.4';
    curl_setopt($curl, CURLOPT_USERAGENT, $user_agent);//user_agent，请求代理信息
    curl_setopt($curl, CURLOPT_AUTOREFERER, true);//referer头，请求来源
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);//设置超时时间
    //SSL相关
    if ($ssl) {
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//禁用后cURL将终止从服务端进行验证
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);//检查服务器SSL证书中是否存在一个公用名(common name)。
    }
    // 处理post相关选项
    curl_setopt($curl, CURLOPT_POST, true);// 是否为POST请求
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);// 处理请求数据
    // 处理响应结果
    curl_setopt($curl, CURLOPT_HEADER, false);//是否处理响应头
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);//curl_exec()是否返回响应结果
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    // 发出请求
    $response = curl_exec($curl);
    if (false === $response) {
        echo '<br>', curl_error($curl), '<br>';
        return false;
    }
    curl_close($curl);
    return $response;
}


function removeXSS($val)
{
    static $obj = null;
    if ($obj === null) {
        require('../extend/HTMLPurifier/HTMLPurifier.auto.php');
        $config = HTMLPurifier_Config::createDefault();
        //保留a标签上的target属性
        $config->set('HTML.TargetBlank', true);
        $obj = new HTMLPurifier($config);
    }

    return $obj->purify($val);
}


/**
 * [getRemoteType 图片上传共用方法]
 * @param  [type]  $uniacid [uniacid]
 * @param  [type]  $groupid [上传图片的相册]
 * @param  integer $flag [是否需要保存]
 * @return [type]           [路径]
 */
function getRemoteType($uniacid, $groupid, $flag = 1)
{   //flag = 1  需要存进pic表   2  不需要存  3diypage上传单图
    $remote_info = Db::name("wd_xcx_base")->where("uniacid", $uniacid)->field("remote, use_remote")->find();  //当前项目设置
    if (!$remote_info) {
        $use_remote = 1;
        $remote = 1;
    } else {
        $use_remote = $remote_info['use_remote'];
        $remote = $remote_info['remote'];
    }
    if ($use_remote == 1) {   //系统设置
        $global_remote = Db::name('wd_xcx_com_about')->where('id', 1)->field('globalremote')->find();
        if (!$global_remote) {
            $remote = 1;
        } else {
            $remote = $global_remote['globalremote'];
        }
        if ($remote == 2) {
            $qiniu_info = Db::name("wd_xcx_remote")->where("type", 2)->where("uniacid", -1)->find();
        } elseif ($remote == 3) {
            $ali_info = Db::name("wd_xcx_remote")->where("type", 3)->where("uniacid", -1)->find();
        }
    } elseif ($use_remote == 2) {  //自己的设置
        if ($remote == 2) {
            $qiniu_info = Db::name("wd_xcx_remote")->where("type", 2)->where("uniacid", $uniacid)->find();
        } elseif ($remote == 3) {
            $ali_info = Db::name("wd_xcx_remote")->where("type", 3)->where("uniacid", $uniacid)->find();
        }
    }

    if ($remote == 1) {
        $files = request()->file('');
        $arrs = [];
        foreach ($files as $file) {
            if($flag != 3){
                foreach($file as $item){
                    // 移动到框架应用根目录/public/upimages/ 目录下        
                    $info = $item->validate(['ext' => 'jpg,png,gif,jpeg'])->move(ROOT_PATH . 'public' . DS . 'upimages');
                    if ($info) {
                        $url = STATIC_ROOT."/upimages/" . date("Ymd", time()) . "/" . $info->getFilename();
                        if ($flag == 1) {
                            $data = array();
                            $data['uniacid'] = $uniacid;
                            $data['gid'] = $groupid;
                            $data['imgurl'] = $url;
                            $data['type'] = 1;
                            $pid = Db::name("wd_xcx_pic")->insertGetId($data);
                            $arr = array("url" => $url, "pid" => $pid);
                            $arrs[] = $arr;
                            // return json_encode($arr);
                        }else{
                            $arr = array("url" => $url);
                            $arrs[] = $arr;
                        }
                        // return json_encode($arr);
                    } else {
                        // 上传失败获取错误信息
                        return $this->error($item->getError());
                    }
                }
            }else{
                // 移动到框架应用根目录/public/upimages/ 目录下        
                $info = $file->validate(['ext' => 'jpg,png,gif,jpeg'])->move(ROOT_PATH . 'public' . DS . 'upimages');
                if ($info) {
                    $url = STATIC_ROOT."/upimages/" . date("Ymd", time()) . "/" . $info->getFilename();
                    if ($flag == 1) {
                        $data = array();
                        $data['uniacid'] = $uniacid;
                        $data['gid'] = $groupid;
                        $data['imgurl'] = $url;
                        $data['type'] = 1;
                        $pid = Db::name("wd_xcx_pic")->insertGetId($data);
                        $arr = array("url" => $url, "pid" => $pid);
                        return json_encode($arr);
                    }

                    $arr = array("url" => $url);
                    return json_encode($arr);
                } else {
                    // 上传失败获取错误信息
                    return $this->error($file->getError());
                }
            }
        }
        return json_encode($arrs);
    } else if ($remote == 2) {
        vendor('Qiniu.autoload');

        $file = $_FILES['uploadfile']['tmp_name'];
        if($flag != 3){
            $arrs = [];
            foreach ($file as $kz => $vz) {
                $is_img = getimagesize($vz);
                if ($is_img) {
                    $oringal_name = $_FILES['uploadfile']['name'][$kz];
                    $pathinfo = pathinfo($oringal_name);

                    // var_dump($pathinfo);exit;
                    // 要上传图片的本地路径
                    $ext = $pathinfo['extension'];
                    $key = 'upimages/' . date("Ymd", time()) . '/' . md5(uniqid(microtime(true), true)) . '.' . $ext;

                    // 需要填写你的 Access Key 和 Secret Key
                    $accessKey = $qiniu_info['ak'];
                    $secretKey = $qiniu_info['sk'];
                    // 构建鉴权对象
                    $auth = new \Qiniu\Auth($accessKey, $secretKey);
                    // 要上传的空间
                    $bucket = $qiniu_info['bucket'];
                    $domain = $qiniu_info['domain'];
                    $token = $auth->uploadToken($bucket);
                    // 初始化 UploadManager 对象并进行文件的上传
                    $uploadMgr = new \Qiniu\Storage\UploadManager();
                    // 调用 UploadManager 的 putFile 方法进行文件的上传
                    list($ret, $err) = $uploadMgr->putFile($token, $key, $vz);
                    if ($err !== null) {
                        echo ["err" => 1, "msg" => $err, "data" => ""];
                    } else {
                        //返回图片的完整URL
                        if ($flag == 1) {
                            $data = array();
                            $data['uniacid'] = $uniacid;
                            $data['gid'] = $groupid;
                            $data['imgurl'] = $ret['key'];
                            $data['type'] = 2;
                            $pid = Db::name("wd_xcx_pic")->insertGetId($data);
                            $arr = array("url" => $qiniu_info['domain'] . '/' . $ret['key'], "pid" => $pid);
                            $arrs[] = $arr;
                        }else{
                            $arr = array("url" => $qiniu_info['domain'] . '/' . $ret['key']);
                            $arrs[] = $arr;
                        }

                    }
                }
            }
            return json_encode($arrs);
        }else{
            $is_img = getimagesize($file);
            if ($is_img) {

            }
            $oringal_name = $_FILES['uploadfile']['name'];

            $pathinfo = pathinfo($oringal_name);

            // var_dump($pathinfo);exit;
            // 要上传图片的本地路径
            $ext = $pathinfo['extension'];
            $key = 'upimages/' . date("Ymd", time()) . '/' . md5(uniqid(microtime(true), true)) . '.' . $ext;

            // 需要填写你的 Access Key 和 Secret Key
            $accessKey = $qiniu_info['ak'];
            $secretKey = $qiniu_info['sk'];
            // 构建鉴权对象
            $auth = new \Qiniu\Auth($accessKey, $secretKey);
            // 要上传的空间
            $bucket = $qiniu_info['bucket'];
            $domain = $qiniu_info['domain'];
            $token = $auth->uploadToken($bucket);
            // 初始化 UploadManager 对象并进行文件的上传
            $uploadMgr = new \Qiniu\Storage\UploadManager();
            // 调用 UploadManager 的 putFile 方法进行文件的上传
            list($ret, $err) = $uploadMgr->putFile($token, $key, $file);
            if ($err !== null) {
                echo ["err" => 1, "msg" => $err, "data" => ""];
            } else {
                //返回图片的完整URL
                if ($flag == 1) {
                    $data = array();
                    $data['uniacid'] = $uniacid;
                    $data['gid'] = $groupid;
                    $data['imgurl'] = $ret['key'];
                    $data['type'] = 2;
                    $pid = Db::name("wd_xcx_pic")->insertGetId($data);
                    $arr = array("url" => $qiniu_info['domain'] . '/' . $ret['key'], "pid" => $pid);
                    return json_encode($arr);
                }

                $arr = array("url" => $qiniu_info['domain'] . '/' . $ret['key']);
                return json_encode($arr);
            }
        }

    } else if ($remote == 3) {
        vendor('aliyun.autoload');

        $file = $_FILES['uploadfile']['tmp_name'];

        if($flag != 3){
            $arrs = [];
            foreach ($file as $kz => $vz) {
                $oringal_name = $_FILES['uploadfile']['name'][$kz];
                $accessKeyId = $ali_info['ak'];
                $accessKeySecret = $ali_info['sk'];
                $endpoint = $ali_info['domain'];
                $bucket = $ali_info['bucket'];
                $object = $oringal_name;
                $pathinfo = pathinfo($oringal_name);
                $ext = $pathinfo['extension'];
                $key = 'upimages/' . date("Ymd", time()) . '/' . md5(uniqid(microtime(true), true)) . '.' . $ext;

                try {
                    $ossClient = new \OSS\OssClient($accessKeyId, $accessKeySecret, $endpoint);
                    if (!$ossClient->doesBucketExist($bucket)) {  //bucket不存在则创建
                        $ossClient->createBucket($bucket);
                    }
                    $res = $ossClient->uploadFile($bucket, $key, $vz);
                    if ($res) {
                        //返回图片的完整URL
                        if ($flag == 1) {
                            $data = array();
                            $data['uniacid'] = $uniacid;
                            $data['gid'] = $groupid;
                            $data['imgurl'] = $key;
                            $data['type'] = 3;
                            $pid = Db::name("wd_xcx_pic")->insertGetId($data);
                            $arr = array("url" => $res['info']['url'], "pid" => $pid);
                            $arrs[] = $arr;
                        }else{
                            $arr = array("url" => $res['info']['url']);
                            $arrs[] = $arr;
                        }
                    } else {
                        echo ["err" => 1, "msg" => '上传错误', "data" => ""];
                    }
                } catch (OssException $e) {
                    printf(__FUNCTION__ . ": FAILED\n");
                    printf($e->getMessage() . "\n");
                    return;
                }
            }
            return json_encode($arrs);
        }else{
            $oringal_name = $_FILES['uploadfile']['name'];
            $accessKeyId = $ali_info['ak'];
            $accessKeySecret = $ali_info['sk'];
            $endpoint = $ali_info['domain'];
            $bucket = $ali_info['bucket'];
            $object = $oringal_name;
            $pathinfo = pathinfo($oringal_name);
            $ext = $pathinfo['extension'];
            $key = 'upimages/' . date("Ymd", time()) . '/' . md5(uniqid(microtime(true), true)) . '.' . $ext;

            try {
                $ossClient = new \OSS\OssClient($accessKeyId, $accessKeySecret, $endpoint);
                if (!$ossClient->doesBucketExist($bucket)) {  //bucket不存在则创建
                    $ossClient->createBucket($bucket);
                }
                $res = $ossClient->uploadFile($bucket, $key, $file);
                if ($res) {
                    //返回图片的完整URL
                    if ($flag == 1) {
                        $data = array();
                        $data['uniacid'] = $uniacid;
                        $data['gid'] = $groupid;
                        $data['imgurl'] = $key;
                        $data['type'] = 3;
                        $pid = Db::name("wd_xcx_pic")->insertGetId($data);
                        $arr = array("url" => $res['info']['url'], "pid" => $pid);
                        return json_encode($arr);
                    }
                    $arr = array("url" => $res['info']['url']);
                    return json_encode($arr);
                } else {
                    echo ["err" => 1, "msg" => '上传错误', "data" => ""];
                }
            } catch (OssException $e) {
                printf(__FUNCTION__ . ": FAILED\n");
                printf($e->getMessage() . "\n");
                return;
            }
        }
    }
}

/**
 * 生成$num个10位随机数
 * @param $num
 * @return array
 */
function getRand($num)
{
    $data = [];
    $pattern = 'abcdefghi1j2k3l4m5no6p7q8r9s0tuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';
    while (count($data) !== (int)$num) {
        //mt_rand() 函数生成随机整数。
        $data[] = substr(str_shuffle($pattern), 8, 10);
    }
    return $data;
}

/**
 * [sendSubscribe description]
 * @return [type] [description]
 */
function sendSubscribe($uniacid, $flag, $openid, $jsons){ //$flag  1发货提醒 2确认收货通知 3退款提醒 4拼团成功通知 5拼团失败通知 6申请审核通知 7分销审核通知 8会员开通成功提醒 9提现审核通知 10积分变动提醒
    $fields = "appID,appSecret";
    $applet = Db::name('wd_xcx_applet')->where("id", $uniacid)->field($fields)->find();
    if ($applet) {
        $info = Db::name('wd_xcx_message_subscribe')->where("uniacid", $uniacid)->where('flag', $flag)->find();
        if($info && $info['mid'] != ''){
            $jsons = unserialize($jsons);
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $applet['appID'] . "&secret=" . $applet['appSecret'];
            $a_token = _Getrequest($url);
            if ($a_token) {
                $url_m = "https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token=" . $a_token['access_token'];
                $mids = $info['mid'];
                $furl = $info['url'];
                if ($flag == 1) { 
                    $ftime = date('Y-m-d H:i', time());
                    $order_id = $jsons['order_id'];
                    $post_info = '{ 
                      "touser": "' . $openid . '",  
                      "template_id": "' . $mids . '", 
                      "page": "' . $furl . '",         
                      "data": {
                            "number1": {
                                "value": "'.$order_id.'"
                            },
                            "date2": {
                                "value": "'.$ftime.'"
                            },
                            "thing4": {
                                "value": "打开小程序查看物流详情>>"
                            }
                      }
                    }';
                } else if ($flag == 2) {
                    $fprice = $jsons['fprice'];
                    $post_info = '{ 
                      "touser": "' . $openid . '",  
                      "template_id": "' . $mids . '", 
                      "page": "' . $furl . '",         
                      "data": {
                            "amount2": {
                                "value": "'.$fprice.'元"
                            },
                            "thing5": {
                                "value": "期待您的下次光临"
                            }
                      }
                    }';
                } else if ($flag == 3) {
                    $order_id = $jsons['order_id'];
                    $fprice = $jsons['fprice'];
                    $fmsg = $jsons['msg'];
                    $post_info = '{ 
                      "touser": "' . $openid . '",  
                      "template_id": "' . $mids . '", 
                      "page": "' . $furl . '",         
                      "data": {
                            "number4": {
                                "value": "'.$order_id.'"
                            },
                            "amount7": {
                                "value": "'.$fprice.'元"
                            },
                            "phrase3": {
                                "value": "'.$fmsg.'"
                            }
                      }
                    }';
                } else if ($flag == 4) {
                    $ftime = date('Y-m-d H:i:s', time());
                    $ftitle = $jsons['ftitle'] ? mb_substr($jsons['ftitle'], 0, 19) : '无';
                    $fnum = $jsons['num'];
                    $fprice = $jsons['fprice'];
                    $post_info = '{ 
                      "touser": "' . $openid . '",  
                      "template_id": "' . $mids . '", 
                      "page": "' . $furl . '",         
                      "data": {
                            "thing1": {
                                "value": "'.$ftitle.'"
                            },
                            "amount4": {
                                "value": "'.$fprice.'"
                            },
                            "number5": {
                                "value": "'.$fnum.'"
                            },
                            "time7": {
                                "value": "'.$ftime.'"
                            }
                      }
                    }';
                } else if ($flag == 5) {
                    $ftime = date('Y-m-d H:i:s', time());
                    $ftitle = $jsons['ftitle'] ? mb_substr($jsons['ftitle'], 0, 19) : '无';
                    $fnum = $jsons['num'];
                    $fmsg = $jsons['msg'];
                    $post_info = '{ 
                      "touser": "' . $openid . '",  
                      "template_id": "' . $mids . '", 
                      "page": "' . $furl . '",         
                      "data": {
                            "thing2": {
                                "value": "'.$ftitle.'"
                            },
                            "number3": {
                                "value": "'.$fnum.'"
                            },
                            "thing4": {
                                "value": "'.$fmsg.'"
                            },
                            "date5": {
                                "value": "'.$ftime.'"
                            }
                      }
                    }';
                } else if ($flag == 6) {
                    $fname = $jsons['name'] ? mb_substr($jsons['name'], 0, 19) : '无';
                    $fmsg = $jsons['tInfo'];
                    $post_info = '{ 
                      "touser": "' . $openid . '",  
                      "template_id": "' . $mids . '", 
                      "page": "' . $furl . '",         
                      "data": {
                            "thing1": {
                                "value": "'.$fname.'"
                            },
                            "thing2": {
                                "value": "申请多商户店铺"
                            },
                            "phrase3": {
                                  "value": "'.$fmsg.'"
                            }
                      }
                    }';
                } else if ($flag == 7) {
                    $ftruename = $jsons['truename'] ? mb_substr($jsons['truename'], 0, 19) : '无';
                    $ftel = $jsons['tel'];
                    $fmsg = $jsons['msg'];
                    $post_info = '{ 
                      "touser": "' . $openid . '",  
                      "template_id": "' . $mids . '", 
                      "page": "' . $furl . '",         
                      "data": {
                            "name1": {
                                "value": "'.$ftruename.'"
                            },
                            "phone_number2": {
                                "value": "'.$ftel.'"
                            },
                            "phrase3": {
                                  "value": "'.$fmsg.'"
                            }
                      }
                    }';
                } else if ($flag == 8) {
                    $nickname = rawurldecode($jsons['nickname']);
                    $tel = $jsons['tel'];
                    $ftime = date('Y年m月d日', time());
                    
                    $post_info = '{ 
                      "touser": "' . $openid . '",  
                      "template_id": "' . $mids . '", 
                      "page": "' . $furl . '",         
                      "data": {
                            "name3": {
                                "value": "'.$nickname.'"
                            },
                            "phone_number4": {
                                "value": "'.$tel.'"
                            },
                            "time1": {
                                  "value": "'.$ftime.'"
                            }
                      }
                    }';
                } else if ($flag == 9) {
                    $fprice = $jsons['fprice'];
                    $fmsg = $jsons['msg'];
                    $post_info = '{ 
                      "touser": "' . $openid . '",  
                      "template_id": "' . $mids . '", 
                      "page": "' . $furl . '",         
                      "data": {
                            "amount2": {
                                "value": "'.$fprice.'元"
                            },
                            "phrase1": {
                                  "value": "'.$fmsg.'"
                            }
                      }
                    }';
                } else if ($flag == 10) {
                    $fscore = $jsons['fscore'];
                    $fmsg = $jsons['ftitle'] ? mb_substr($jsons['ftitle'], 0, 17) . '*1' : '';
                    $post_info = '{ 
                      "touser": "' . $openid . '",  
                      "template_id": "' . $mids . '", 
                      "page": "' . $furl . '",         
                      "data": {
                            "thing1": {
                                "value": "积分兑换商品"
                            },
                            "number2": {
                                "value": "'.$fscore.'"
                            },
                            "thing3": {
                                  "value": "'.$fmsg.'"
                            }
                      }
                    }';
                // } else if ($flag == 11) { //11提现审核通知

                //     $ftitle = $jsons['ftitle'];
                //     $fmsg = $jsons['fmsg'];
                //     $post_info = '{
                //               "touser": "' . $openid . '",
                //               "template_id": "' . $mids . '",
                //               "page": "' . $furl . '",
                //               "form_id": "' . $formId . '",
                //               "data": {
                //                   "keyword1": {
                //                       "value": "' . $ftitle . '",
                //                       "color": "#173177"
                //                   },
                //                   "keyword2": {
                //                       "value": "' . $ftime . '",
                //                       "color": "#173177"
                //                   },
                //                   "keyword3": {
                //                       "value": "' . $fmsg . '",
                //                       "color": "#173177"
                //                   }
                //               },
                //               "emphasis_keyword": "keyword1.DATA"
                //             }';
                // } else if ($flag == 12) { //12积分变动提醒
                //     $fmoney = $jsons['fmoney'];
                //     $ftype = $jsons['ftype'];
                //     $post_info = '{
                //         "touser": "' . $openid . '",  
                //         "template_id": "' . $mids . '",        
                //         "page": "' . $furl . '",         
                //         "form_id": "' . $formId . '", 
                //         "data": {
                //             "keyword1": {
                //                 "value": "' . $ftime . '", 
                //                 "color": "#173177"
                //             }, 
                //             "keyword2": {
                //                 "value": "' . $fmoney . '", 
                //                 "color": "#173177"
                //             }, 
                //             "keyword3": {
                //                 "value": "' . $ftype . '", 
                //                 "color": "#173177"
                //             }                   
                //         },
                //         "emphasis_keyword": "" 
                //     }';
                }
                // var_dump($post_info);
                // $res = _Postrequest($url_m, $post_info);
                // var_dump($res);exit;
                _Postrequest($url_m, $post_info);
            }
        }
    }

}


function getShareBackGroubd($uniacid){
    $setColor = Db::name('wd_xcx_base') ->where('uniacid', $uniacid)->field('base_color, base_color2') ->find();
    $down = hex2rgb($setColor['base_color']);
    $up = hex2rgb($setColor['base_color2']);

    $height = 534;
    $width = 300;

    $im = ImageCreateTrueColor($width, $height);
    //上边  162 172 254
    //下边  119 131 234

    //上边： 217 237 30
    //下边： 124 212 22 根据这几个值，调整$i的系数
    //计算变化值
    $diff_r = $down['r'] - $up['r'];
    $diff_g = $down['g'] - $up['g'];
    $diff_b = $down['b'] - $up['b'];

    $diff_r_num = round(abs($diff_r)/530, 2);
    $diff_g_num = round(abs($diff_g)/530, 2);
    $diff_b_num = round(abs($diff_b)/530, 2);

    for ($i=0; $i < 534; $i++)
    {
        if($diff_r > 0){
            $diff_r_num_d = $up['r'] + floor($i * $diff_r_num);
        }else{
            $diff_r_num_d = $up['r'] - floor($i * $diff_r_num);
        }

        if($diff_g > 0){
            $diff_g_num_d = $up['g'] + floor($i * $diff_g_num);
        }else{
            $diff_g_num_d = $up['g'] - floor($i * $diff_g_num);
        }

        if($diff_b > 0){
            $diff_b_num_d = $up['b'] + floor($i * $diff_b_num);
        }else{
            $diff_b_num_d = $up['b'] - floor($i * $diff_b_num);
        }


        $Color=ImageColorAllocate($im, $diff_r_num_d, $diff_g_num_d, $diff_b_num_d);
        ImageLine($im, 0, 0+$i, $width, 0+$i, $Color);

    }

    $path = ROOT_PATH . 'public/shareImg/' .$uniacid.'_share_back.png';

    //output image
    Header('Content-type: image/png');
    ImagePng($im, $path);

    return $path;
}


function hex2rgb($hexColor){
    $color=str_replace('#','',$hexColor);
    if (strlen($color)> 3){
        $rgb=array(
            'r'=>hexdec(substr($color,0,2)),
            'g'=>hexdec(substr($color,2,2)),
            'b'=>hexdec(substr($color,4,2))
        );
    }else{
        $r=substr($color,0,1). substr($color,0,1);
        $g=substr($color,1,1). substr($color,1,1);
        $b=substr($color,2,1). substr($color,2,1);
        $rgb=array(
            'r'=>hexdec($r),
            'g'=>hexdec($g),
            'b'=>hexdec($b)
        );
    }
    return $rgb;
}