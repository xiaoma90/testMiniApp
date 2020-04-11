<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;

use think\cache\driver\Redis;
use Aliyun\Core\Config;
use Aliyun\Core\Profile\DefaultProfile;
use Aliyun\Core\DefaultAcsClient;
use Aliyun\Api\Sms\Request\V20170525\SendSmsRequest;

class Register extends Controller
{
    public function index(){
        $shortmsg = Db::name('wd_xcx_register')->where('id', 1)->value('shortmsg');
        if(!$shortmsg){
            $shortmsg = 3;
        }
        $this->assign('shortmsg', $shortmsg);
        return $this->fetch('index');
    }
    public function sub(){
        $username = trim(input('username'));
        $password = md5(trim(input('password')));
        $tel = trim(input('tel'));
        // $name = input('name');
        // $email = input('email');

        $captcha = trim(input('vcode')); //图形验证码
        if(!captcha_check($captcha)){
            $this->error('图形验证码不正确');
        }
        $code = trim(input('code'));
        if($code){
            $send_code = 0;
            $rediscon = $this->GetRediscon();
            $redis = new Redis($rediscon);
            $send_code = $redis->get('code_' . $tel);
            if($code != $send_code){
                $this->error('手机验证码不正确');
            }
        }
        $config = Db::name('wd_xcx_register')->where('id', 1)->find();
        if($config){
            $projects = unserialize($config['projects']);
            $overtime = ($config['day'] + 1) * 24 * 3600 + strtotime(date('Y-m-d', time()));
            $data = [
                'username' => $username,
                'password' => $password,
                'group' => 1,
                'mobile' => $tel,
                // 'realname' => $name,
                // 'email' => $email,
                'overtime' => $overtime,
                'type' => $config['projects'],
                'jxs' => -1,
                'updatetime' => time()
            ];
            $userid = Db::name('wd_xcx_admin')->insertGetId($data); //创建用户
            if($userid){//创建小程序
                $data_s['name'] = '试用项目';
                $data_s['end_time'] = $overtime;
                $log_time = '时间到'.date('Y-m-d', $overtime);
                $log_price = ".";
                $data_s['type'] = $config['projects'];
                $log_type = "";
                foreach (unserialize($config['projects']) as $k=> $v){
                    if($v == '0'){
                        $log_type .= '微信小程序,';
                    }else if($v == '1'){
                        $log_type .= '百度小程序,';
                    }else if($v == '3'){
                        $log_type .= 'PC网站,';
                    }else if($v == '4'){
                        $log_type .= 'H5应用,';
                    }else if($v == '5'){
                        $log_type .= '字节跳动小程序,';
                    }else if($v == '6'){
                        $log_type .= 'QQ小程序,';
                    }else{
                        $log_type .= '支付宝小程序,';
                    }
                }
                $log_type = substr($log_type, 0, -1);

                $data_s['combo_id'] = $config['combo_id'];
                $data_s['dateline'] = time();
                $data_s['adminid'] = $userid;
                $data_s['jxs'] = -1;
                $res = Db::name('wd_xcx_applet') ->insertGetId($data_s);
                if($res){   //设置推广位ID
                    $tabbar1 = [
                            'tabbar_name' => '首页',
                            'tabbar_url' => '/pages/index/index',
                            'tabbar_linktype' => 'page',
                            'tabbar' => 2,
                            'tabimginput_1' => 'icon-x-shouye5'
                            ];

                    $tabbar2 = [
                            'tabbar_name' => '商品分类',
                            'tabbar_url' => '/pages/catelist/catelist?type=showProMore',
                            'tabbar_linktype' => 'page',
                            'tabbar' => 2,
                            'tabimginput_1' => 'icon-x-caidan4'
                            ];

                    $tabbar3 = [
                            'tabbar_name' => '购物车',
                            'tabbar_url' => '/pages/gwc/gwc',
                            'tabbar_linktype' => 'page',
                            'tabbar' => 2,
                            'tabimginput_1' => 'icon-x-gwc1'
                            ];

                    $tabbar4 = [
                            'tabbar_name' => '个人中心',
                            'tabbar_url' => '/pages/usercenter/usercenter',
                            'tabbar_linktype' => 'page',
                            'tabbar' => 2,
                            'tabimginput_1' => 'icon-x-geren1'
                            ];
                    
                    $tabbar_new = serialize([serialize($tabbar1), serialize($tabbar2), serialize($tabbar3), serialize($tabbar4)]);
                    
                    //创建基础表数据
                    $base_data = [
                        'uniacid' => $res,
                        'copyimg' => '',
                        'base_color_t' => '',
                        'base_color' => '#4491F1',
                        'base_tcolor' => '#ffffff',
                        'base_color2' => '#70b0ff',
                        'tabbar_bg1' => '#FFFFFF',
                        'tabbar_bg2' => '#FFFFFF',
                        'tabbar_bg3' => '#60A1F2',
                        'c_title' => '',
                        'video' => '',
                        'v_img' => '',
                        'i_b_x_ts' => '',
                        'i_b_y_ts' => '',
                        'catename_x' => '',
                        'catenameen_x' => '',
                        'tel_box' => '',
                        'tabbar_t' => 1,
                        'tabbar_bg' => 'FFFFFF',
                        'color_bar' => 'CCCCCC',
                        'tabbar_tc' => '222222',
                        'tabbar_tca' => 'FF4444',
                        'tabbar' => '',
                        'tabnum' => '',
                        'copy_do' => '',
                        'copy_id' => '',
                        'gonggao' => '',
                        'gonggaoUrl' => '',
                        'tabbar_new' => $tabbar_new,
                        'tabnum_new' => 4,
                        'config' => 'a:4:{s:5:"commA";s:1:"0";s:6:"commAs";s:1:"0";s:5:"commP";s:1:"0";s:6:"commPs";s:1:"0";}',
                    ];   
                    Db::name('wd_xcx_base')->insert($base_data);

                    //个人中心功能配置
                    $usercenter_data = array(
                        'title1' => '分销中心',
                        'num1' => 1,
                        'thumb1' => 'http://four.nttrip.cn/image/nifx.png',
                        'flag1' => 2,
                        'url1' => '/pagesFenxiao/fenxiao_center/fenxiao_center',
                        'icon1' => 'icon-x-fenxiao2',
                        'title2' => '签到中心',
                        'num2' => 2,
                        'thumb2' => 'http://four.nttrip.cn/image/niqd.png',
                        'flag2' => 1,
                        'url2' => '/pagesSign/index/index',
                        'icon2' => 'icon-x-qiandao2',
                        'title3' => '积分兑换中心',
                        'num3' => 3,
                        'thumb3' => 'http://four.nttrip.cn/image/nijf.png',
                        'flag3' => 1,
                        'url3' => '/pagesExchange/list/list',
                        'icon3' => 'icon-x-jifen',
                        'title4' => '我的餐饮订单',
                        'num4' => 4,
                        'thumb4' => 'http://four.nttrip.cn/image/nicy.png',
                        'flag4' => 1,
                        'url4' => '/pagesFood/food_my/food_my',
                        'icon4' => 'icon-x-diancan',
                        'title5' => '我的订单',
                        'num5' => 5,
                        'thumb5' => 'http://four.nttrip.cn/image/nidd.png',
                        'flag5' => 1,
                        'url5' => '/pages/order_more_list/order_more_list',
                        'icon5' => 'icon-x-gouwu',
                        'title6' => '我的付费视频',
                        'num6' => 6,
                        'thumb6' => 'http://four.nttrip.cn/image/nisp.png',
                        'flag6' => 1,
                        'url6' => '/pages/order_art/order_art',
                        'icon6' => 'icon-x-shipin',
                        'title7' => '我的优惠券',
                        'num7' => 7,
                        'thumb7' => 'http://four.nttrip.cn/image/niyh.png',
                        'flag7' => 2,
                        'url7' => '/pages/mycoupon/mycoupon',
                        'icon7' => 'icon-x-youhuiquan2',
                        'title8' => '我的收藏',
                        'num8' => 8,
                        'thumb8' => 'http://four.nttrip.cn/image/nisc.png',
                        'flag8' => 2,
                        'url8' => '/pages/collect/collect',
                        'icon8' => 'icon-c-xing1',
                        'title9' => '我的地址',
                        'num9' => 9,
                        'thumb9' => 'http://four.nttrip.cn/image/nidz.png',
                        'flag9' => 2,
                        'url9' => '/pages/address/address',
                        'icon9' => 'icon-x-dizhi',
                        'title10' => '',
                        'num10' => '',
                        'thumb10' => '',
                        'flag10' => '',
                        'url10' => '',
                        'icon10' => '',
                        'title11' => '拼团订单',
                        'num11' => 11,
                        'thumb11' => 'http://four.nttrip.cn/image/ptdd.png',
                        'flag11' => 1,
                        'url11' => '/pagesPt/orderlist/orderlist',
                        'icon11' => 'icon-x-pintuan',
                        'title12' => '预约订单',
                        'num12' => 12,
                        'thumb12' => 'http://four.nttrip.cn/image/yydd.png',
                        'flag12' => 1,
                        'url12' => '/pagesReserve/orderList/orderList',
                        'icon12' => 'icon-x-qiandao3',
                        'title13' => '秒杀订单',
                        'num13' => 13,
                        'thumb13' => 'http://four.nttrip.cn/image/ptdd.png',
                        'flag13' => 1,
                        'url13' => '/pagesFlashSale/orderlist_dan/orderlist_dan',
                        'icon13' => 'icon-x-miaosha',
                        'title14' => '店铺管理',
                        'num14' => 14,
                        'thumb14' => 'http://four.nttrip.cn/image/ptdd.png',
                        'flag14' => 1,
                        'url14' => '/pagesPluginShop/manage_index/manage_index',
                        'icon14' => 'icon-c-dianpu2',
                        'title15' => '我的表单',
                        'num15' => 15,
                        'thumb15' => 'http://four.nttrip.cn/image/ptdd.png',
                        'flag15' => 1,
                        'url15' => '/pages/form_list/form_list',
                        'icon15' => 'icon-x-dingdan2',
                        'title16' => '砍价订单',
                        'num16' => 16,
                        'thumb16' => 'http://four.nttrip.cn/image/ptdd.png',
                        'flag16' => 1,
                        'url16' => '/pagesBargain/orderlist/orderlist',
                        'icon16' => 'icon-x-kanjia',
                        'title17' => '客服',
                        'num17' => 17,
                        'thumb17' => 'http://four.nttrip.cn/image/ptdd.png',
                        'flag17' => 1,
                        'url17' => '',
                        'icon17' => 'icon-x-kefu',
                        'title18' => '活动报名',
                        'num18' => 18,
                        'thumb18' => 'http://four.nttrip.cn/image/ptdd.png',
                        'flag18' => 1,
                        'url18' => '/pagesActive/apply_collect/apply_collect',
                        'icon18' => 'icon-x-pingjia',
                        'title19' => '我的评价',
                        'num19' => 19,
                        'thumb19' => 'http://four.nttrip.cn/image/ptdd.png',
                        'flag19' => 1,
                        'url19' => '/pagesOther/evaluate/evaluate?user=1',
                        'icon19' => 'icon-x-pingjia2',
                        'title20' => '联系我们',
                        'num20' => 20,
                        'thumb20' => 'http://four.nttrip.cn/image/ptdd.png',
                        'flag20' => 1,
                        'url20' => '',
                        'icon20' => 'icon-c-shouji',
                        'title21' => '扫码核销',
                        'num21' => 21,
                        'thumb21' => 'http://four.nttrip.cn/image/ptdd.png',
                        'flag21' => 1,
                        'url21' => '',
                        'icon21' => 'icon-x-saoma',
                        'title22' => '多商户订单',
                        'num22' => 22,
                        'thumb22' => 'http://four.nttrip.cn/image/ptdd.png',
                        'flag22' => 1,
                        'url22' => '/pages/order_more_list/order_more_list',
                        'icon22' => 'icon-x-ruzhu'
                    );
                    $strs = serialize($usercenter_data);
                    $usercenter_data = array(
                        'uniacid' => $res,
                        'usercenterset' => $strs
                    );
                    Db::name('wd_xcx_usercenter_set')->insert($usercenter_data);
                }
                $log['admin'] = $userid; //操作人id=注册用户id
                $log['time'] = time();
                $log['type'] = '0'; //0操作记录 1充值记录
                
                $log['text'] = "在".date('Y-m-d H:i:s', time()).', 平台注册创建了名称为试用项目的小程序,'.$log_time.', 类型为'.$log_type;
                Db::name('wd_xcx_log') ->insert($log);
                $this->success('注册成功', 'Login/index');
            }
        }
    }
    function GetRediscon()
    {
        return require(__DIR__.'/../../rediscon.php');
    }
    public function getcode(){
        $phone = input('tel');
        $is2 = Db::name('wd_xcx_admin')->where('mobile', $phone)->find();
        if($is2){
            return 2;  //参数错误
        }
        $rediscon = $this->GetRediscon();
        $redis = new Redis($rediscon);
        $time_phone = $redis->get('time_' . $phone);
        if ($time_phone && $time_phone + 60 > time()) {
            return 3;  //还没超过60s，不能再发
        }
        
        $phoneNumbers = [$phone];
        $code = rand(1000, 9999);

        $shortmsg = Db::name("wd_xcx_com_about")->where("id", 1)->value("shortmsg");
        if (!$shortmsg) {
            $shortmsg = 1; //启用全局腾讯云短信服务
        }
        if ($shortmsg == 1) { //启用全局腾讯云短信服务
            $sms = Db::name("wd_xcx_sms")->where("uniacid", -1)->where("type", 1)->find(); //全局腾讯云短信信息
        } else if ($shortmsg == 2) {
            $sms = Db::name("wd_xcx_sms")->where("uniacid", -1)->where("type", 2)->find(); //全局阿里云短信信息
        }
        if (empty($sms)) {
            return 1;  //参数错误
        }

        if ($shortmsg == 1) { //腾讯云短信服务
            include_once 'dxsrc/index.php';
            try {
                $msender = new SmsMultiSender($sms['tx_access_id'], $sms['tx_access_secret']);
                $params = [$code, 5];
                $result = $msender->sendWithParam("86", $phoneNumbers, $sms['tx_code_tpl'],
                    $params, $sms['tx_sign'], "", "");  // 签名参数未提供或者为空时，会使用默认签名发送短信

                $rsp = json_decode($result, true);

                if ($rsp['errmsg'] == 'OK') {
                    $redis->set('code_' . $phone, $code);
                    $redis->set('time_' . $phone, time());
                    return 0;  //发送成功
                } else {
                    return 1;  //参数错误
                }
            } catch (\Exception $e) {
                var_dump($e);
            }
        } else if ($shortmsg == 2) {
            $rsp = $this->send_sms($phoneNumbers[0], $code, $sms['tx_access_id'], $sms['tx_access_secret'], $sms['tx_sign'], $sms['tx_code_tpl']);
            if ($rsp != 505) {
                if ($rsp['Code'] == 'OK') {
                    $redis->set('code_' . $phone, $code);
                    $redis->set('time_' . $phone, time());
                    return 0;  //发送成功
                } else {
                    return 1;  //参数错误
                }
            } else {
                return 1;  //参数错误
            }
        }
    }
    function send_sms($to, $code = "", $accessKeyId, $accessKeySecret, $signName, $templateCode)
    {
        require_once dirname(__DIR__) . '/../../extend/api_sdk/vendor/autoload.php';
        Config::load(); //加载区域结点配置
        // $accessKeyId = '阿里云生成的accessKeyId';
        // $accessKeySecret = '阿里云生成的accessKeySecret';
        $templateParam = $code;
        //短信签名
        // $signName = "短信签名";
        //短信模板ID
        // $templateCode = "短信模板"; // 注册登录短信验证码模板
        //短信API产品名（短信产品名固定，无需修改）
        $product = "Dysmsapi";
        //短信API产品域名（接口地址固定，无需修改）
        $domain = "dysmsapi.aliyuncs.com";
        //暂时不支持多Region（目前仅支持cn-hangzhou请勿修改）
        $region = "cn-hangzhou";
        // 初始化用户Profile实例
        $profile = DefaultProfile::getProfile($region, $accessKeyId, $accessKeySecret);
        // 增加服务结点
        DefaultProfile::addEndpoint("cn-hangzhou", "cn-hangzhou", $product, $domain);
        // 初始化AcsClient用于发起请求
        $acsClient = new DefaultAcsClient($profile);
        // 初始化SendSmsRequest实例用于设置发送短信的参数
        $request = new SendSmsRequest();
        // 必填，设置雉短信接收号码
        $request->setPhoneNumbers($to);
        // 必填，设置签名名称
        $request->setSignName($signName);
        // 必填，设置模板CODE
        $request->setTemplateCode($templateCode);
        // 可选，设置模板参数
        if ($templateParam) {
            $request->setTemplateParam(json_encode(['code' => $templateParam]));
        }

        // 启动事务
        // Db::startTrans();
        //发起访问请求
        try {
            $acsResponse = $acsClient->getAcsResponse($request);
            // 更新成功 提交事务
            // Db::commit();
            // return true;
        } catch (\Exception $e) {
            // 更新失败 回滚事务
            // Db::rollback();
            return 505;
        }

        //返回请求结果
        $result = json_decode(json_encode($acsResponse), true);
        // 具体返回值参考文档：https://help.aliyun.com/document_detail/55451.html?spm=a2c4g.11186623.6.563.YSe8FK
        return $result;
    }

    public function check(){
        $username = input('username');
        $tel = input('tel');
        $shortmsg = input('shortmsg');
        $code = input('code');

        $err_code = 0;
        $is1 = Db::name('wd_xcx_admin')->where('username', $username)->find();
        $is2 = Db::name('wd_xcx_admin')->where('mobile', $tel)->find();
        $send_code = 0;
        if($shortmsg == 1){
            $rediscon = $this->GetRediscon();
            $redis = new Redis($rediscon);
            $send_code = $redis->get('code_' . $tel);
        }

        if($is1){
            $err_code = 1;
        }else if($is2){
            $err_code = 3;
        }else if (intval($code) != intval($send_code)) {
            $err_code = 4;
        }
        return $err_code;
    }
}