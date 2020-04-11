<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;

class Message extends Base
{
    public function index(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $types = unserialize($res['type']);
                $this->assign('types', $types);

                $list = Db::name('wd_xcx_message')->where("uniacid",$appletid)->order('flag asc')->select();
                $wx = [];
                $bdance = [];
                $qq = [];
                $wx['mid_flag1'] = $wx['url_flag1'] = $wx['mid_flag2'] = $wx['url_flag2'] = $wx['mid_flag3'] = $wx['url_flag3'] = $wx['mid_flag4'] = $wx['url_flag4'] = $wx['mid_flag5'] = $wx['url_flag5'] = $wx['mid_flag6'] = $wx['url_flag6'] = $wx['mid_flag7'] = $wx['url_flag7'] = $wx['mid_flag8'] = $wx['url_flag8'] = $wx['mid_flag9'] = $wx['url_flag9'] = $wx['mid_flag10'] = $wx['url_flag10'] = $wx['mid_flag11'] = $wx['url_flag11'] = $wx['mid_flag12'] = $wx['url_flag12'] = '';

                $qq['mid_flag1'] = $qq['url_flag1'] = $qq['mid_flag2'] = $qq['url_flag2'] = $qq['mid_flag3'] = $qq['url_flag3'] = $qq['mid_flag4'] = $qq['url_flag4'] = $qq['mid_flag5'] = $qq['url_flag5'] = $qq['mid_flag6'] = $qq['url_flag6'] = $qq['mid_flag7'] = $qq['url_flag7'] = $qq['mid_flag8'] = $qq['url_flag8'] = $qq['mid_flag9'] = $qq['url_flag9'] = $qq['mid_flag10'] = $qq['url_flag10'] = $qq['mid_flag11'] = $qq['url_flag11'] = $qq['mid_flag12'] = $qq['url_flag12'] = '';

                $bdance['mid_flag1'] = $bdance['url_flag1'] = $bdance['mid_flag2'] = $bdance['url_flag2'] = $bdance['mid_flag3'] = $bdance['url_flag3'] = $bdance['mid_flag4'] = $bdance['url_flag4'] = $bdance['mid_flag5'] = $bdance['url_flag5'] = $bdance['mid_flag6'] = $bdance['url_flag6'] = $bdance['mid_flag7'] = $bdance['url_flag7'] = $bdance['mid_flag8'] = $bdance['url_flag8'] = $bdance['mid_flag9'] = $bdance['url_flag9'] = $bdance['mid_flag10'] = $bdance['url_flag10'] = $bdance['mid_flag11'] = $bdance['url_flag11'] = $bdance['mid_flag12'] = $bdance['url_flag12'] = '';
                foreach ($list as $k => $v) {
                    if($v['flag'] == 1){ //购买成功
                        $wx['mid_flag1'] = $v['mid'];
                        $wx['url_flag1'] = $v['url'];
                        $bdance['mid_flag1'] = $v['bdance_mid'];
                        $bdance['url_flag1'] = $v['bdance_url'];
                        $qq['mid_flag1'] = $v['qq_mid'];
                        $qq['url_flag1'] = $v['qq_url'];
                    }else if($v['flag'] == 2){
                        $wx['mid_flag2'] = $v['mid'];
                        $wx['url_flag2'] = $v['url'];
                        $bdance['mid_flag2'] = $v['bdance_mid'];
                        $bdance['url_flag2'] = $v['bdance_url'];
                        $qq['mid_flag2'] = $v['qq_mid'];
                        $qq['url_flag2'] = $v['qq_url'];
                    }else if($v['flag'] == 3){
                        $wx['mid_flag3'] = $v['mid'];
                        $wx['url_flag3'] = $v['url'];
                        $bdance['mid_flag3'] = $v['bdance_mid'];
                        $bdance['url_flag3'] = $v['bdance_url'];
                        $qq['mid_flag3'] = $v['qq_mid'];
                        $qq['url_flag3'] = $v['qq_url'];
                    }else if($v['flag'] == 4){
                        $wx['mid_flag4'] = $v['mid'];
                        $wx['url_flag4'] = $v['url'];
                        $bdance['mid_flag4'] = $v['bdance_mid'];
                        $bdance['url_flag4'] = $v['bdance_url'];
                        $qq['mid_flag4'] = $v['qq_mid'];
                        $qq['url_flag4'] = $v['qq_url'];
                    }else if($v['flag'] == 5){
                        $wx['mid_flag5'] = $v['mid'];
                        $wx['url_flag5'] = $v['url'];
                        $bdance['mid_flag5'] = $v['bdance_mid'];
                        $bdance['url_flag5'] = $v['bdance_url'];
                        $qq['mid_flag5'] = $v['qq_mid'];
                        $qq['url_flag5'] = $v['qq_url'];
                    }else if($v['flag'] == 6){
                        $wx['mid_flag6'] = $v['mid'];
                        $wx['url_flag6'] = $v['url'];
                        $bdance['mid_flag6'] = $v['bdance_mid'];
                        $bdance['url_flag6'] = $v['bdance_url'];
                        $qq['mid_flag6'] = $v['qq_mid'];
                        $qq['url_flag6'] = $v['qq_url'];
                    }else if($v['flag'] == 7){
                        $wx['mid_flag7'] = $v['mid'];
                        $wx['url_flag7'] = $v['url'];
                        $bdance['mid_flag7'] = $v['bdance_mid'];
                        $bdance['url_flag7'] = $v['bdance_url'];
                        $qq['mid_flag7'] = $v['qq_mid'];
                        $qq['url_flag7'] = $v['qq_url'];
                    }else if($v['flag'] == 8){
                        $wx['mid_flag8'] = $v['mid'];
                        $wx['url_flag8'] = $v['url'];
                        $bdance['mid_flag8'] = $v['bdance_mid'];
                        $bdance['url_flag8'] = $v['bdance_url'];
                        $qq['mid_flag8'] = $v['qq_mid'];
                        $qq['url_flag8'] = $v['qq_url'];
                    }else if($v['flag'] == 9){
                        $wx['mid_flag9'] = $v['mid'];
                        $wx['url_flag9'] = $v['url'];
                        $bdance['mid_flag9'] = $v['bdance_mid'];
                        $bdance['url_flag9'] = $v['bdance_url'];
                        $qq['mid_flag9'] = $v['qq_mid'];
                        $qq['url_flag9'] = $v['qq_url'];
                    }else if($v['flag'] == 10){
                        $wx['mid_flag10'] = $v['mid'];
                        $wx['url_flag10'] = $v['url'];
                        $bdance['mid_flag10'] = $v['bdance_mid'];
                        $bdance['url_flag10'] = $v['bdance_url'];
                        $qq['mid_flag10'] = $v['qq_mid'];
                        $qq['url_flag10'] = $v['qq_url'];
                    }else if($v['flag'] == 11){
                        $wx['mid_flag11'] = $v['mid'];
                        $wx['url_flag11'] = $v['url'];
                        $bdance['mid_flag11'] = $v['bdance_mid'];
                        $bdance['url_flag11'] = $v['bdance_url'];
                        $qq['mid_flag11'] = $v['qq_mid'];
                        $qq['url_flag11'] = $v['qq_url'];
                    }else if($v['flag'] == 12){
                        $wx['mid_flag12'] = $v['mid'];
                        $wx['url_flag12'] = $v['url'];
                        $bdance['mid_flag12'] = $v['bdance_mid'];
                        $bdance['url_flag12'] = $v['bdance_url'];
                        $qq['mid_flag12'] = $v['qq_mid'];
                        $qq['url_flag12'] = $v['qq_url'];
                    }
                }
                $this->assign('wx', $wx);
                $this->assign('bdance', $bdance);
                $this->assign('qq', $qq);

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

            return $this->fetch('index');
        }else{
            $this->redirect('Login/index');
        }
    }
    public function save(){
        $appletid = input('appletid');
        $source = input('source');

        for($i = 1; $i <= 12; $i++){
            if($source == 1){ //微信
                $data = [
                    'mid' => trim(input('wx_mid'.$i)),
                    'url' => trim(input('wx_url'.$i)),
                ];
            }else if($source == 5){ //字节跳动
                $data = [
                    'bdance_mid' => trim(input('bdance_mid'.$i)),
                    'bdance_url' => trim(input('bdance_url'.$i)),
                ];
            }else if($source == 6){ //QQ
                $data = [
                    'qq_mid' => trim(input('qq_mid'.$i)),
                    'qq_url' => trim(input('qq_url'.$i)),
                ];
            }
            $is = Db::name('wd_xcx_message')->where("uniacid", $appletid)->where('flag', $i)->find();
            if($is){
                Db::name('wd_xcx_message')->where("uniacid", $appletid)->where('flag', $i)->update($data);
            }else{
                $data['uniacid'] = $appletid;
                $data['flag'] = $i;
                Db::name('wd_xcx_message')->insert($data);
            }
        }
        $this->success('模板消息设置成功');
    }

    public function subscribe(){
        if(check_login()){
            if(powerget()){
                $appletid = input('appletid');
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $wx['mid_flag1'] = $wx['url_flag1'] = $wx['mid_flag2'] = $wx['url_flag2'] = $wx['mid_flag3'] = $wx['url_flag3'] = $wx['mid_flag4'] = $wx['url_flag4'] = $wx['mid_flag5'] = $wx['url_flag5'] = $wx['mid_flag6'] = $wx['url_flag6'] = $wx['mid_flag7'] = $wx['url_flag7'] = $wx['mid_flag8'] = $wx['url_flag8'] = $wx['mid_flag9'] = $wx['url_flag9'] = $wx['mid_flag10'] = $wx['url_flag10'] = '';
                $set = 0;
                $list = Db::name('wd_xcx_message_subscribe')->where("uniacid",$appletid)->order('flag asc')->select();
                if($list){
                    $set = 1; 
                }
                foreach ($list as $k => $v) {
                    if($v['flag'] == 1){ //购买成功
                        $wx['mid_flag1'] = $v['mid'];
                        $wx['url_flag1'] = $v['url'];
                        // $bdance['mid_flag1'] = $v['bdance_mid'];
                        // $bdance['url_flag1'] = $v['bdance_url'];
                        // $qq['mid_flag1'] = $v['qq_mid'];
                        // $qq['url_flag1'] = $v['qq_url'];
                    }else if($v['flag'] == 2){
                        $wx['mid_flag2'] = $v['mid'];
                        $wx['url_flag2'] = $v['url'];
                        // $bdance['mid_flag2'] = $v['bdance_mid'];
                        // $bdance['url_flag2'] = $v['bdance_url'];
                        // $qq['mid_flag2'] = $v['qq_mid'];
                        // $qq['url_flag2'] = $v['qq_url'];
                    }else if($v['flag'] == 3){
                        $wx['mid_flag3'] = $v['mid'];
                        $wx['url_flag3'] = $v['url'];
                        // $bdance['mid_flag3'] = $v['bdance_mid'];
                        // $bdance['url_flag3'] = $v['bdance_url'];
                        // $qq['mid_flag3'] = $v['qq_mid'];
                        // $qq['url_flag3'] = $v['qq_url'];
                    }else if($v['flag'] == 4){
                        $wx['mid_flag4'] = $v['mid'];
                        $wx['url_flag4'] = $v['url'];
                        // $bdance['mid_flag4'] = $v['bdance_mid'];
                        // $bdance['url_flag4'] = $v['bdance_url'];
                        // $qq['mid_flag4'] = $v['qq_mid'];
                        // $qq['url_flag4'] = $v['qq_url'];
                    }else if($v['flag'] == 5){
                        $wx['mid_flag5'] = $v['mid'];
                        $wx['url_flag5'] = $v['url'];
                        // $bdance['mid_flag5'] = $v['bdance_mid'];
                        // $bdance['url_flag5'] = $v['bdance_url'];
                        // $qq['mid_flag5'] = $v['qq_mid'];
                        // $qq['url_flag5'] = $v['qq_url'];
                    }else if($v['flag'] == 6){
                        $wx['mid_flag6'] = $v['mid'];
                        $wx['url_flag6'] = $v['url'];
                        // $bdance['mid_flag6'] = $v['bdance_mid'];
                        // $bdance['url_flag6'] = $v['bdance_url'];
                        // $qq['mid_flag6'] = $v['qq_mid'];
                        // $qq['url_flag6'] = $v['qq_url'];
                    }else if($v['flag'] == 7){
                        $wx['mid_flag7'] = $v['mid'];
                        $wx['url_flag7'] = $v['url'];
                        // $bdance['mid_flag7'] = $v['bdance_mid'];
                        // $bdance['url_flag7'] = $v['bdance_url'];
                        // $qq['mid_flag7'] = $v['qq_mid'];
                        // $qq['url_flag7'] = $v['qq_url'];
                    }else if($v['flag'] == 8){
                        $wx['mid_flag8'] = $v['mid'];
                        $wx['url_flag8'] = $v['url'];
                        // $bdance['mid_flag8'] = $v['bdance_mid'];
                        // $bdance['url_flag8'] = $v['bdance_url'];
                        // $qq['mid_flag8'] = $v['qq_mid'];
                        // $qq['url_flag8'] = $v['qq_url'];
                    }else if($v['flag'] == 9){
                        $wx['mid_flag9'] = $v['mid'];
                        $wx['url_flag9'] = $v['url'];
                        // $bdance['mid_flag9'] = $v['bdance_mid'];
                        // $bdance['url_flag9'] = $v['bdance_url'];
                        // $qq['mid_flag9'] = $v['qq_mid'];
                        // $qq['url_flag9'] = $v['qq_url'];
                    }else if($v['flag'] == 10){
                        $wx['mid_flag10'] = $v['mid'];
                        $wx['url_flag10'] = $v['url'];
                        // $bdance['mid_flag10'] = $v['bdance_mid'];
                        // $bdance['url_flag10'] = $v['bdance_url'];
                        // $qq['mid_flag10'] = $v['qq_mid'];
                        // $qq['url_flag10'] = $v['qq_url'];
                    // }else if($v['flag'] == 11){
                    //     $wx['mid_flag11'] = $v['mid'];
                    //     $wx['url_flag11'] = $v['url'];
                    //     // $bdance['mid_flag11'] = $v['bdance_mid'];
                    //     // $bdance['url_flag11'] = $v['bdance_url'];
                    //     // $qq['mid_flag11'] = $v['qq_mid'];
                    //     // $qq['url_flag11'] = $v['qq_url'];
                    // }else if($v['flag'] == 12){
                    //     $wx['mid_flag12'] = $v['mid'];
                    //     $wx['url_flag12'] = $v['url'];
                        // $bdance['mid_flag12'] = $v['bdance_mid'];
                        // $bdance['url_flag12'] = $v['bdance_url'];
                        // $qq['mid_flag12'] = $v['qq_mid'];
                        // $qq['url_flag12'] = $v['qq_url'];
                    }
                }
                $this->assign('set', $set);
                $this->assign('wx', $wx);
        
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

            return $this->fetch('subscribe');
        }else{
            $this->redirect('Login/index');
        }
    }
    public function set(){
        $appletid = input('appletid');

        $fields = "appID,appSecret";
        $applet = Db::name('wd_xcx_applet')->where("id", $appletid)->field($fields)->find();
        $result = [];

        if ($applet) {
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $applet['appID'] . "&secret=" . $applet['appSecret'];
            $a_token = _Getrequest($url);
            if ($a_token) {
                //获取模板列表
                $url = 'https://api.weixin.qq.com/wxaapi/newtmpl/gettemplate?access_token=' . $a_token['access_token'];
                $res = $this->_Postrequest($url, '');
                $res = json_decode($res, true);
                if($res['errmsg'] == 'ok'){
                    if(count($res['data']) > 15){
                        $del_num = count($res['data']) - 15;
                        $result['errcode'] = 1;
                        $result['errmsg'] = '订阅模板数量已达到' . count($res['data']) . '个，剩余可用数量不足，请至少删除' . $del_num . '个后重试';
                        return json_encode($result, JSON_UNESCAPED_UNICODE);
                    }
                }

                try{
                    $url_m = "https://api.weixin.qq.com/wxaapi/newtmpl/addtemplate?access_token=" . $a_token['access_token'];
                    $wx = [];
                    $post_infos = [
                                    '{
                                      "tid":"1417",
                                      "kidList":[1, 2, 4],
                                      "sceneDesc": "发货提醒"
                                    }',
                                    '{
                                      "tid":"972",
                                      "kidList":[2, 5],
                                      "sceneDesc": "确认收货"
                                    }',
                                    '{
                                      "tid":"642",
                                      "kidList":[4, 7, 3],
                                      "sceneDesc": "退款"
                                    }',
                                    '{
                                      "tid":"2117",
                                      "kidList":[1, 4, 5, 7],
                                      "sceneDesc": "拼团成功"
                                    }',
                                    '{
                                      "tid":"3577",
                                      "kidList":[2, 3, 4, 5],
                                      "sceneDesc": "拼团失败"
                                    }',
                                    '{
                                      "tid":"1482",
                                      "kidList":[1, 2, 3],
                                      "sceneDesc": "多商户入驻审核"
                                    }',
                                    '{
                                      "tid":"3576",
                                      "kidList":[1, 2, 3],
                                      "sceneDesc": "分销商申请"
                                    }',
                                    '{
                                      "tid":"2744",
                                      "kidList":[3, 4, 1],
                                      "sceneDesc": "会员开通成功"
                                    }',
                                    '{
                                      "tid":"3029",
                                      "kidList":[2, 1],
                                      "sceneDesc": "提现申请结果通知"
                                    }',
                                    '{
                                      "tid":"2252",
                                      "kidList":[1, 2, 3],
                                      "sceneDesc": "积分兑换"
                                    }'
                                ];
                    for($i = 1; $i <= 10; $i++){
                        $is = Db::name('wd_xcx_message_subscribe')->where("uniacid",$appletid)->where("flag", $i)->find();
                        if(!$is){
                            $j = $i - 1;
                            if($post_infos[$j]){
                                $res = _Postrequest($url_m, $post_infos[$j]);
                                $res = json_decode($res, TRUE);
                                
                                if($res['errcode'] == 0){
                                    $data = [];
                                    $wx['wx_mid'.$i] = $res['priTmplId'];
                                    $data['mid'] = $res['priTmplId'];
                                    $data['flag'] = $i;
                                    $data['uniacid'] = $appletid;
                                    Db::name('wd_xcx_message_subscribe')->insert($data);
                                }else{
                                    $result['errcode'] = $res['errcode'];
                                    $result['errmsg'] = $res['errmsg'];
                                    return json_encode($result, JSON_UNESCAPED_UNICODE);
                                }
                            }
                        }
                    }
                }catch(\Exception $e){
                    $result['errcode'] = 1;
                    $result['errmsg'] = '小程序配置有误，一键配置失败！';
                    return json_encode($result, JSON_UNESCAPED_UNICODE);
                } 
                
                $result['errcode'] = 0;
                $result['errmsg'] = '操作成功';
                $result['data'] = $wx;
                return json_encode($result, JSON_UNESCAPED_UNICODE);
            }else{
                $result['errcode'] = 2;
                $result['errmsg'] = '小程序信息不正常';
                return json_encode($result, JSON_UNESCAPED_UNICODE);
            }
        }else{
            $result['errcode'] = 1;
            $result['errmsg'] = '小程序不存在';
            return json_encode($result, JSON_UNESCAPED_UNICODE);
        }
    }
    function _Postrequest($url, $ssl = true)
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
        // curl_setopt($curl, CURLOPT_POST, true);// 是否为POST请求
        // curl_setopt($curl, CURLOPT_POSTFIELDS, $data);// 处理请求数据
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
    public function savesubscribe(){
        $appletid = input('appletid');
        for($i = 1; $i <= 10; $i++){ //1发货提醒 2确认收货通知 3退款提醒 4拼团成功通知 5拼团失败通知 6申请审核通知 7分销审核通知 8会员开通成功提醒 9提现审核通知 10积分变动提醒
            $data = [
                'mid' => trim(input('wx_mid'.$i)),
                'url' => trim(input('wx_url'.$i)),
            ];
            $is = Db::name('wd_xcx_message_subscribe')->where("uniacid", $appletid)->where('flag', $i)->find();
            if($is){
                Db::name('wd_xcx_message_subscribe')->where("uniacid", $appletid)->where('flag', $i)->update($data);
            }else{
                $data['uniacid'] = $appletid;
                $data['flag'] = $i;
                Db::name('wd_xcx_message_subscribe')->insert($data);
            }
        }
        $this->success('订阅消息设置成功');
    }
}