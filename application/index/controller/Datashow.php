<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;
class Datashow extends Base
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
                if($res['thumb']){
                    $_SESSION['app_icon'] = $res['thumb'];
                }else{
                    $_SESSION['app_icon'] = STATIC_ROOT . '/image/logo2.png';
                }

                if($res['name']){
                    $_SESSION['app_name'] = $res['name'];
                }else{
                    $_SESSION['app_name'] = '智慧多端系统管理';
                }
                // $visitNum = pdo_fetchcolumn("SELECT sum(`visit_pv`) FROM ".tablename("wxapp_general_analysis")." WHERE uniacid = :uniacid",array(":uniacid"=>$appletid));
                $num=Db::name("wd_xcx_base")->where("uniacid",$appletid)->find();
                

                $visitNum =$num['visitnum'];
                $this->assign('visitNum',$visitNum);
                $userNum = Db::name("wd_xcx_superuser")->where("uniacid",$appletid)->count();
                $this->assign('userNum',$userNum);
                $vipNum = Db::name("wd_xcx_superuser")->where("uniacid",$appletid)->where("vipid","gt",0)->count();
                $this->assign('vipNum',$vipNum);
                $firstDay = time();
                $lastDay = time()+31*24*3600;

                $main_order_price = Db::name('wd_xcx_main_shop_order') ->where('uniacid', $appletid) ->where('status', 'GT', 0) ->sum('if(is_change_price = 1, change_price, pay_money)');
                $main_order_price = $main_order_price ? $main_order_price : 0;

                $shop_order_price = Db::name('wd_xcx_duo_products_order') ->where('uniacid', $appletid) ->where("flag","in",[1,2,4,6,7,9,10]) ->sum('price');
                $shop_order_price = $shop_order_price ? $shop_order_price : 0;

                $order_price = Db::name('wd_xcx_order') ->where('uniacid', $appletid) ->where("flag","in",[1,2,4,6,7,9,10]) ->sum('price');
                $order_price = $order_price ? $order_price : 0;

                $pt_order_price = Db::name('wd_xcx_pt_order') ->where('uniacid', $appletid) ->where("flag","in",[1,2,4,6,7,9,10]) ->sum('price');
                $pt_order_price = $pt_order_price ? $pt_order_price : 0;

                $bargain_order_price = Db::name('wd_xcx_bargain_order') ->where('uniacid', $appletid) ->where("flag","in",[1,2,4,6,7,9,10]) ->sum('true_price');
                $bargain_order_price = $bargain_order_price ? $bargain_order_price : 0;

                $totalConsumption = $main_order_price + $shop_order_price + $order_price + $pt_order_price + $bargain_order_price;

                $this->assign('totalConsumption',$totalConsumption);
                $yue = Db::name("wd_xcx_superuser")->where("uniacid",$appletid)->sum("money");
                $yue = sprintf("%.2f", $yue);
                $now = time();
                $tod = date("Y-m-d",time());
                $thirtyDayBefore = strtotime("$tod -30 days");
                $this->assign('yue',$yue);
                //$duo_platform_num = Db::name("wd_xcx_duo_products_order")->where("uniacid",$appletid)->where("sid",0)->where("creattime",">=",$thirtyDayBefore)->where("creattime","<=",$now)->where("flag","in",[1,2,4,6,7,9,10])->count();
                $duo_platform_num = Db::name('wd_xcx_main_shop_order') ->where([
                    'uniacid' => $appletid,
                    'creat_time' => ['EGT', $thirtyDayBefore],
                    'status' => ['GT', 0]
                ]) ->count();
                $this->assign("duo_platform_num",$duo_platform_num);
                //$duo_platform_money = Db::name("wd_xcx_duo_products_order")->where("uniacid",$appletid)->where("sid",0)->where("creattime",">=",$thirtyDayBefore)->where("creattime","<=",$now)->where("flag","in",[1,2,4,6,7,9,10])->sum("price");
                $duo_platform_money = Db::name('wd_xcx_main_shop_order') ->where([
                    'uniacid' => $appletid,
                    'creat_time' => ['EGT', $thirtyDayBefore],
                    'status' => ['GT', 0]
                ]) ->sum('if(is_change_price = 1, change_price, pay_money)');
                $duo_platform_money = sprintf("%.2f", $duo_platform_money);
                $this->assign("duo_platform_money",$duo_platform_money);
                for($i = 6; $i >= 0; $i--){
                    $stt = "$tod -".$i." days";
                    $btime = strtotime(date("Y-m-d 00:00:00", strtotime($stt)));
                    $etime = strtotime(date("Y-m-d 23:59:59", strtotime($stt)));
                    $duo_chart_num[6-$i] = Db::name('wd_xcx_main_shop_order') ->where([
                        'uniacid' => $appletid,
                        'creat_time' => ['between time', [$btime, $etime]],
                        'status' => ['GT', 0]
                    ]) ->count();
                    $duo_chart_num[6-$i] = $duo_chart_num[6-$i]?$duo_chart_num[6-$i]:0;
                    $duo_chart_money[6-$i] = Db::name('wd_xcx_main_shop_order') ->where([
                        'uniacid' => $appletid,
                        'creat_time' => ['between time', [$btime, $etime]],
                        'status' => ['GT', 0]
                    ]) ->sum('if(is_change_price = 1, change_price, pay_money)');
                    $duo_chart_money[6-$i] = sprintf("%.2f", $duo_chart_money[6-$i]);
                    $last_week_date[6-$i] = date("Ymd",$btime);
                }
                $duo_chart_num_max = max($duo_chart_num)?max($duo_chart_num)+intval(max($duo_chart_num)/3):50;
                $this->assign("duo_chart_num_max",$duo_chart_num_max);
                $duo_chart_money_max = max($duo_chart_money) != 0.00?max($duo_chart_money)+intval(max($duo_chart_money)/10):500;
                $this->assign("duo_chart_money_max",$duo_chart_money_max);
                $duo_chart_num_interval = $duo_chart_num_max/5;
                $this->assign("duo_chart_num_interval",$duo_chart_num_interval);
                $duo_chart_money_interval = $duo_chart_money_max/5;
                $this->assign("duo_chart_money_interval",$duo_chart_money_interval);
                $duo_chart_num = '[' .implode(',', $duo_chart_num). ']';
                $this->assign("duo_chart_num",$duo_chart_num);
                $duo_chart_money = '[' . implode(',', $duo_chart_money) . ']';
                $this->assign("duo_chart_money",$duo_chart_money);
                $last_week_date = '[' . implode(',', $last_week_date) . ']';
                $this->assign("last_week_date",$last_week_date);
                // dump($duo_chart_num);die;
                $today = strtotime(date("Y-m-d 00:00:00", time()));
//                $today_num = Db::name("wd_xcx_duo_products_order")->where("uniacid",$appletid)->where("sid",0)->where("creattime",">=",$today)->where("creattime","<=",$now)->where("flag","in",[1,2,4,6,7,9,10])->count();
                $today_num = Db::name('wd_xcx_main_shop_order') ->where([
                    'uniacid' => $appletid,
                    'creat_time' => ['EGT', $today],
                    'status' => ['GT', 0]
                ]) ->count();
                $today_num=$today_num?$today_num:0;
                $this->assign("today_num",$today_num);
                //$today_money = Db::name("wd_xcx_duo_products_order")->where("uniacid",$appletid)->where("sid",0)->where("creattime",">=",$today)->where("creattime","<=",$now)->where("flag","in",[1,2,4,6,7,9,10])->sum("price");
                $today_money = Db::name('wd_xcx_main_shop_order') ->where([
                    'uniacid' => $appletid,
                    'creat_time' => ['EGT', $today],
                    'status' => ['GT', 0]
                ]) ->sum('if(is_change_price = 1, change_price, pay_money)');
                $today_money = sprintf("%.2f", $today_money);
                if(empty($today_money) || $today_money == '0.00') $today_money = 0;
                $this->assign("today_money",$today_money);
                //$today_avg = Db::name("wd_xcx_duo_products_order")->where("uniacid",$appletid)->where("sid",0)->where("creattime",">=",$today)->where("creattime","<=",$now)->where("flag","in",[1,2,4,6,7,9,10])->avg("price");
                $today_avg = Db::name('wd_xcx_main_shop_order') ->where([
                    'uniacid' => $appletid,
                    'creat_time' => ['EGT', $today],
                    'status' => ['GT', 0]
                ]) ->avg('if(is_change_price = 1, change_price, pay_money)');
                $today_avg = sprintf("%.2f", $today_avg);
                if(empty($today_avg) || $today_avg == '0.00') $today_avg = 0;
                $this->assign("today_avg",$today_avg);

                $today_start_get= date("Y-m-d 00:00:00", mktime(0,0,0,date('m'),date('d'),date('Y')));

                $today_end_get= date("Y-m-d 23:59:59", mktime(0,0,0,date('m'),date('d'),date('Y')));

                //$today_flag0 = Db::name("wd_xcx_duo_products_order")->where("uniacid",$appletid)->where("sid",0)->where("creattime",">=",$today)->where("creattime","<=",$now)->where("flag",0)->count();
                $today_flag0 = Db::name('wd_xcx_main_shop_order') ->where([
                    'uniacid' => $appletid,
                    'creat_time' => ['EGT', $today],
                    'status' => 1
                ]) ->count();
                $today_flag0=$today_flag0?$today_flag0:0;
                $this->assign("today_flag0",$today_flag0);
                $this->assign("today_start_get",$today_start_get);
                $this->assign("today_end_get",$today_end_get);

                $today_flag1 = Db::name("wd_xcx_duo_products_order")->where("uniacid",$appletid)->where("sid",0)->where("creattime",">=",$today)->where("creattime","<=",$now)->where("flag",1)->count();
                $today_flag1 = Db::name('wd_xcx_main_shop_order') ->where([
                    'uniacid' => $appletid,
                    'creat_time' => ['EGT', $today],
                    'status' => 2,
                    'delivery_type' => 2
                ]) ->count();
                $today_flag1=$today_flag1?$today_flag1:0;
                $this->assign("today_flag1",$today_flag1);
                $today_flag4 = Db::name("wd_xcx_duo_products_order")->where("uniacid",$appletid)->where("sid",0)->where("creattime",">=",$today)->where("creattime","<=",$now)->where("flag",5)->count();
                $today_flag4 = Db::name('wd_xcx_main_shop_order_service') ->where([
                    'uniacid' => $appletid,
                    'creat_time' => ['EGT', $today],
                ]) ->count();
                $today_flag4=$today_flag4?$today_flag4:0;
                $this->assign("today_flag4",$today_flag4);

                $yes = strtotime(date("Y-m-d 00:00:00", strtotime("$tod -1 day")));
                $yes_end = strtotime(date("Y-m-d 23:59:59", strtotime("$tod -1 day")));

                $yes_start_get = date("Y-m-d 00:00:00", strtotime("$tod -1 day"));
                $yes_end_get = date("Y-m-d 23:59:59", strtotime("$tod -1 day"));

                $this->assign("yes_start_get",$yes_start_get);
                $this->assign("yes_end_get",$yes_end_get);
                //$yes_num = Db::name("wd_xcx_duo_products_order")->where("uniacid",$appletid)->where("sid",0)->where("creattime",">=",$yes)->where("creattime","<=",$yes_end)->where("flag","in",[1,2,4,6,7,9,10])->count();
                $yes_num = Db::name('wd_xcx_main_shop_order') ->where([
                    'uniacid' => $appletid,
                    'creat_time' => ['between time', [$yes_start_get, $yes_end_get]],
                    'status' => ['GT', 0]
                ]) ->count();
                $yes_num=$yes_num?$yes_num:0;
                $this->assign("yes_num",$yes_num);
                //$yes_money = Db::name("wd_xcx_duo_products_order")->where("uniacid",$appletid)->where("sid",0)->where("creattime",">=",$yes)->where("creattime","<=",$yes_end)->where("flag","in",[1,2,4,6,7,9,10])->sum("price");
                $yes_money = Db::name('wd_xcx_main_shop_order') ->where([
                    'uniacid' => $appletid,
                    'creat_time' => ['between time', [$yes_start_get, $yes_end_get]],
                    'status' => ['GT', 0]
                ]) ->sum('if(is_change_price = 1, change_price, pay_money)');
                $yes_money = sprintf("%.2f", $yes_money);
                if(empty($yes_money) || $yes_money == '0.00') $yes_money = 0;
                $this->assign("yes_money",$yes_money);
                //$yes_avg = Db::name("wd_xcx_duo_products_order")->where("uniacid",$appletid)->where("sid",0)->where("creattime",">=",$yes)->where("creattime","<=",$yes_end)->where("flag","in",[1,2,4,6,7,9,10])->avg("price");
                $yes_avg = Db::name('wd_xcx_main_shop_order') ->where([
                    'uniacid' => $appletid,
                    'creat_time' => ['between time', [$yes_start_get, $yes_end_get]],
                    'status' => ['GT', 0]
                ]) ->avg('if(is_change_price = 1, change_price, pay_money)');
                $yes_avg = sprintf("%.2f", $yes_avg);
                if(empty($yes_avg) || $yes_avg == '0.00') $yes_avg = 0;
                $this->assign("yes_avg",$yes_avg);
                //$yes_flag0 = Db::name("wd_xcx_duo_products_order")->where("uniacid",$appletid)->where("sid",0)->where("creattime",">=",$yes)->where("creattime","<=",$yes_end)->where("flag",0)->count();
                $yes_flag0 = Db::name('wd_xcx_main_shop_order') ->where([
                    'uniacid' => $appletid,
                    'creat_time' => ['between time', [$yes_start_get, $yes_end_get]],
                    'status' => 1
                ]) ->count();
                $yes_flag0=$yes_flag0?$yes_flag0:0;
                $this->assign("yes_flag0",$yes_flag0);
                //$yes_flag1 = Db::name("wd_xcx_duo_products_order")->where("uniacid",$appletid)->where("sid",0)->where("creattime",">=",$yes)->where("creattime","<=",$yes_end)->where("flag",1)->count();
                $yes_flag1 = Db::name('wd_xcx_main_shop_order') ->where([
                    'uniacid' => $appletid,
                    'creat_time' => ['between time', [$yes_start_get, $yes_end_get]],
                    'status' => 2,
                    'delivery_type' => 2
                ]) ->count();

                $yes_flag1=$yes_flag1?$yes_flag1:0;
                $this->assign("yes_flag1",$yes_flag1);
                //$yes_flag4 = Db::name("wd_xcx_duo_products_order")->where("uniacid",$appletid)->where("sid",0)->where("creattime",">=",$yes)->where("creattime","<=",$yes_end)->where("flag",5)->count();
                $yes_flag4 = Db::name('wd_xcx_main_shop_order_service') ->where([
                    'uniacid' => $appletid,
                    'creat_time' => ['between time', [$yes_start_get, $yes_end_get]],
                ]) ->count();
                
                $yes_flag4=$yes_flag4?$yes_flag4:0;
                $this->assign("yes_flag4",$yes_flag4);

                $week = strtotime(date("Y-m-d 00:00:00", strtotime("$tod -7 days")));

                $week_start_get = date("Y-m-d 00:00:00", strtotime("$tod -7 days"));
                $week_end_get= date("Y-m-d 23:59:59", mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1);
                $this->assign("week_start_get",$week_start_get);
                $this->assign("week_end_get",$week_end_get);

                //$week_num = Db::name("wd_xcx_duo_products_order")->where("uniacid",$appletid)->where("sid",0)->where("creattime",">=",$week)->where("creattime","<=",$now)->where("flag","in",[1,2,4,6,7,9,10])->count();
                $week_num = Db::name('wd_xcx_main_shop_order') ->where([
                    'uniacid' => $appletid,
                    'creat_time' => ['between time', [$week_start_get, $week_end_get]],
                    'status' => ['GT', 0]
                ]) ->count();
                $week_num=$week_num?$week_num:0;
                $this->assign('week_num',$week_num);
                //$week_money = Db::name("wd_xcx_duo_products_order")->where("uniacid",$appletid)->where("sid",0)->where("creattime",">=",$week)->where("creattime","<=",$now)->where("flag","in",[1,2,4,6,7,9,10])->sum("price");
                $week_money = Db::name('wd_xcx_main_shop_order') ->where([
                    'uniacid' => $appletid,
                    'creat_time' => ['between time', [$week_start_get, $week_end_get]],
                    'status' => ['GT', 0]
                ]) ->sum('if(is_change_price = 1, change_price, pay_money)');
                $week_money = sprintf("%.2f", $week_money);
                if(empty($week_money) || $week_money == '0.00') $week_money = 0;
                $this->assign('week_money',$week_money);
                //$week_avg = Db::name("wd_xcx_duo_products_order")->where("uniacid",$appletid)->where("sid",0)->where("creattime",">=",$week)->where("creattime","<=",$now)->where("flag","in",[1,2,4,6,7,9,10])->avg("price");
                $week_avg = Db::name('wd_xcx_main_shop_order') ->where([
                    'uniacid' => $appletid,
                    'creat_time' => ['between time', [$week_start_get, $week_end_get]],
                    'status' => ['GT', 0]
                ]) ->avg('if(is_change_price = 1, change_price, pay_money)');
                $week_avg = sprintf("%.2f", $week_avg);
                if(empty($week_avg) || $week_avg == '0.00') $week_avg = 0;
                $this->assign('week_avg',$week_avg);
                //$week_flag0 = Db::name("wd_xcx_duo_products_order")->where("uniacid",$appletid)->where("sid",0)->where("creattime",">=",$week)->where("creattime","<=",$now)->where("flag",0)->count();
                $week_flag0 = Db::name('wd_xcx_main_shop_order') ->where([
                    'uniacid' => $appletid,
                    'creat_time' => ['between time', [$week_start_get, $week_end_get]],
                    'status' => 1
                ]) ->count();
                $week_flag0=$week_flag0?$week_flag0:0;
                $this->assign('week_flag0',$week_flag0);
                //$week_flag1 = Db::name("wd_xcx_duo_products_order")->where("uniacid",$appletid)->where("sid",0)->where("creattime",">=",$week)->where("creattime","<=",$now)->where("flag",1)->count();
                $week_flag1 = Db::name('wd_xcx_main_shop_order') ->where([
                    'uniacid' => $appletid,
                    'creat_time' => ['between time', [$week_start_get, $week_end_get]],
                    'status' => 2,
                    'delivery_type' => 2
                ]) ->count();
                $week_flag1=$week_flag1?$week_flag1:0;
                $this->assign('week_flag1',$week_flag1);
                //$week_flag4 = Db::name("wd_xcx_duo_products_order")->where("uniacid",$appletid)->where("sid",0)->where("creattime",">=",$week)->where("creattime","<=",$now)->where("flag",5)->count();
                $week_flag4 = Db::name('wd_xcx_main_shop_order_service') ->where([
                    'uniacid' => $appletid,
                    'creat_time' => ['between time', [$week_start_get, $week_end_get]],
                ]) ->count();
                $week_flag4=$week_flag4?$week_flag4:0;
                $this->assign('week_flag4',$week_flag4);

                $month = strtotime(date("Y-m-d 00:00:00", strtotime("$tod -30 days")));

                $month_start_get = date("Y-m-d 00:00:00", strtotime("$tod -30 days"));
                $month_end_get= date("Y-m-d 23:59:59", mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1);

                $this->assign("month_start_get",$month_start_get);
                $this->assign("month_end_get",$month_end_get);
                //$month_num = Db::name("wd_xcx_duo_products_order")->where("uniacid",$appletid)->where("sid",0)->where("creattime",">=",$month)->where("creattime","<=",$now)->where("flag","in",[1,2,4,6,7,9,10])->count();
                $month_num = Db::name('wd_xcx_main_shop_order') ->where([
                    'uniacid' => $appletid,
                    'creat_time' => ['between time', [$month_start_get, $month_end_get]],
                    'status' => ['GT', 0]
                ]) ->count();
                $month_num=$month_num?$month_num:0;
                $this->assign("month_num",$month_num);
                //month_money = Db::name("wd_xcx_duo_products_order")->where("uniacid",$appletid)->where("sid",0)->where("creattime",">=",$month)->where("creattime","<=",$now)->where("flag","in",[1,2,4,6,7,9,10])->sum("price");
                $month_money = Db::name('wd_xcx_main_shop_order') ->where([
                    'uniacid' => $appletid,
                    'creat_time' => ['between time', [$month_start_get, $month_end_get]],
                    'status' => ['GT', 0]
                ]) ->sum('if(is_change_price = 1, change_price, pay_money)');
                $month_money = sprintf("%.2f", $month_money);
                if(empty($month_money) || $month_money == '0.00') $month_money = 0;
                $this->assign("month_money",$month_money);
                //$month_avg = Db::name("wd_xcx_duo_products_order")->where("uniacid",$appletid)->where("sid",0)->where("creattime",">=",$month)->where("creattime","<=",$now)->where("flag","in",[1,2,4,6,7,9,10])->avg("price");
                $month_avg = Db::name('wd_xcx_main_shop_order') ->where([
                    'uniacid' => $appletid,
                    'creat_time' => ['between time', [$month_start_get, $month_end_get]],
                    'status' => ['GT', 0]
                ]) ->avg('if(is_change_price = 1, change_price, pay_money)');
                $month_avg = sprintf("%.2f", $month_avg);
                if(empty($month_avg) || $month_avg == '0.00') $month_avg = 0;
                $this->assign("month_avg",$month_avg);
                //$month_flag0 = Db::name("wd_xcx_duo_products_order")->where("uniacid",$appletid)->where("sid",0)->where("creattime",">=",$month)->where("creattime","<=",$now)->where("flag",0)->count();
                $month_flag0 = Db::name('wd_xcx_main_shop_order') ->where([
                    'uniacid' => $appletid,
                    'creat_time' => ['between time', [$month_start_get, $month_end_get]],
                    'status' => 1
                ]) ->count();
                $month_flag0=$month_flag0?$month_flag0:0;
                $this->assign("month_flag0",$month_flag0);
                //$month_flag1 = Db::name("wd_xcx_duo_products_order")->where("uniacid",$appletid)->where("sid",0)->where("creattime",">=",$month)->where("creattime","<=",$now)->where("flag",1)->count();
                $month_flag1 = Db::name('wd_xcx_main_shop_order') ->where([
                    'uniacid' => $appletid,
                    'creat_time' => ['between time', [$month_start_get, $month_end_get]],
                    'status' => 2,
                    'delivery_type' => 2
                ]) ->count();
                $month_flag1=$month_flag1?$month_flag1:0;
                $this->assign("month_flag1",$month_flag1);
                //$month_flag4 = Db::name("wd_xcx_duo_products_order")->where("uniacid",$appletid)->where("sid",0)->where("creattime",">=",$month)->where("creattime","<=",$now)->where("flag",5)->count();
                $month_flag4 = Db::name('wd_xcx_main_shop_order_service') ->where([
                    'uniacid' => $appletid,
                    'creat_time' => ['between time', [$month_start_get, $month_end_get]],
                ]) ->count();
                $month_flag4=$month_flag4?$month_flag4:0;
                $this->assign("month_flag4",$month_flag4);
                //$duo_platform_orders = Db::name("wd_xcx_duo_products_order")->where("uniacid",$appletid)->where("sid",0)->order('creattime desc')->limit(0,5)->field("id,creattime,order_id,jsondata,uid,flag,nav")->select();

                $main_products = Db::name('wd_xcx_products') ->where([
                    'uniacid' => $appletid,
                    'is_sale' => 0,
                    'type' => 'showProMore'
                ]) ->order('sale_tnum desc') ->limit(5) ->field('id, title, sale_tnum') ->select();

                foreach ($main_products as $ke => $item){
                    $main_products[$ke]['sort'] = $ke + 1;
                    $total_price = Db::name('wd_xcx_main_shop_order_item') ->where([
                        'uniacid' => $appletid,
                        'pro_id' => $item['id'],
                        'status' => ['GT', 0]
                    ]) ->sum('pro_discounts_price * num');
                    $main_products[$ke]['total_price'] = $total_price ? $total_price : 0.00;
                }

                /**
                foreach ($duo_platform_orders as $key => &$value) {
                    $value['creattime'] = date('Y-m-d H:i:s', $value['creattime']);
                    $jsondata = unserialize($value['jsondata']);
                    unset($value['jsondata']);
                    $pname = '';
                    // if($jsondata[0]['is_from_shops'] == 1){
                    if(2 == 1){
                        foreach ($jsondata as $k => $v) {
                            $pname .= Db::name("wd_xcx_shops_goods")->where("uniacid",$appletid)->where("id",$v['pid'])->field("title")->find()['title'];
                        }
                    }else{

                            if($jsondata){
                                foreach ($jsondata as $k => $v) {
                                    if(isset($v['baseinfo2']) && $v['baseinfo2']){
                                        $pname .= $v['baseinfo2']['title'] . ':' . chop($v['proinfo']['ggz'],',') . 'x' . $v['num'] . ';';
                                    }else{
                                       if(isset($v['type']) && $v['type'] == "showProMore"){

                                            $pname .= $v['baseinfo']['title'] . ':' . chop($v['proinfo']['ggz'],',') . 'x' . $v['num'] . ';';
                                        }else{
                                            $pname .= $v['baseinfo']['title'] . ':' . chop($v['proinfo']['ggz'],',') . 'x' . $v['num'] . ';';
                                        }
                                    }
                                }
                            }else{
                                $pname = "";
                            }
                    }
                    $pname = chop($pname, ';');

                    $value['pname'] = $pname;
                    $value['nickname'] = Db::name("wd_xcx_user")->where("uniacid",$appletid)->where("id",$value['uid'])->field("nickname")->find()['nickname'];
                    unset($value['uid']);
                }
                */

                $this->assign("duo_platform_orders",$main_products);
                //商户id
                $duo_shop_orders = Db::name("wd_xcx_duo_products_order")->where("uniacid",$appletid)->where("sid","neq",0)->field("id,creattime,order_id,jsondata,uid,flag,nav")->order("creattime desc")->limit(0,5)->select();
                foreach ($duo_shop_orders as $key => &$value) {
                    $value['creattime'] = date('Y-m-d H:i:s', $value['creattime']);
                    $jsondata = unserialize($value['jsondata']);
                    unset($value['jsondata']);
                    $pname = '';
                    // if($jsondata[0]['is_from_shops'] == 1){
                    if(2 == 1){
                        foreach ($jsondata as $k => $v) {
                            $pname .= Db::name("wd_xcx_shops_goods")->where("uniacid",$appletid)->where("id",$v['pid'])->field("title")->find()['title'];
                        }
                    }else{
                        foreach ($jsondata as $k => $v) {
                            $pname .= Db::name("wd_xcx_shops_goods")->where("uniacid",$appletid)->where("id",$v['proinfo']['pid'])->field("title")->find()['title'];
                        }
                    }
                    $pname = chop($pname, ';');
                    $value['pname'] = $pname;
                    $value['nickname'] = Db::name("wd_xcx_user")->where("uniacid",$appletid)->where("id",$value['uid'])->field("nickname")->find()['nickname'];
                    unset($value['uid']);
                }
                $this->assign("duo_shop_orders",$duo_shop_orders);
                $yuyue_orders = Db::name("wd_xcx_order")->where("uniacid",$appletid)->where("is_more",1)->order("creattime desc")->limit(0,5)->select();
                foreach ($yuyue_orders as $key => &$value) {
                    $value['creattime'] = date('Y-m-d H:i:s', $value['creattime']); 
                    $value['pname'] = Db::name("wd_xcx_products")->where("uniacid",$appletid)->where("id",$value['pid'])->field("title")->find()['title'];
                    unset($value['pid']);
                    $value['nickname'] = Db::name("wd_xcx_user")->where("uniacid",$appletid)->where("id",$value['uid'])->field("nickname")->find()['nickname'];
                    unset($value['uid']);
                }
                $this->assign("yuyue_orders",$yuyue_orders);
                $miaosha_orders = Db::name("wd_xcx_order")->where("uniacid",$appletid)->where("is_more",0)->order("creattime desc")->limit(0,5)->select();
                foreach ($miaosha_orders as $key => &$value) {
                    $value['creattime'] = date('Y-m-d H:i:s', $value['creattime']); 
                    $value['pname'] = Db::name("wd_xcx_products")->where("uniacid",$appletid)->where("id",$value['pid'])->field("title")->find()['title'];
                    unset($value['pid']);
                    $value['nickname'] = Db::name("wd_xcx_user")->where("uniacid",$appletid)->where("id",$value['uid'])->field("nickname")->find()['nickname'];
                    unset($value['uid']);
                }
                $this->assign("miaosha_orders",$miaosha_orders);
                $pintuan_orders = Db::name("wd_xcx_pt_order")->where("uniacid",$appletid)->order("creattime desc")->limit(0,5)->select();
                foreach ($pintuan_orders as $key => &$value) {
                    $value['creattime'] = date('Y-m-d H:i:s', $value['creattime']);
                    $jsondata = unserialize($value['jsondata']);
                    unset($value['jsondata']);
                    $pname = '';
                    
                    foreach ($jsondata as $k => $v) {
                        $pname .= Db::name("wd_xcx_pt_pro")->where("uniacid",$appletid)->where("id",$v['baseinfo'])->field("title")->find()['title'];
                    }
                    
                    $pname = chop($pname, ';');
                    
                    $value['pname'] = $pname;
                    $value['nickname'] = Db::name("wd_xcx_user")->where("uniacid",$appletid)->where("id",$value['uid'])->field("nickname")->find()['nickname'];
                    unset($value['uid']);
                }
                $this->assign("pintuan_orders",$pintuan_orders);
                $video_orders = Db::name("wd_xcx_video_pay")->where("uniacid",$appletid)->order("creattime desc")->limit(0,5)->select();
                foreach ($video_orders as $key => &$value) {
                    $value['creattime'] = date('Y-m-d H:i:s', $value['creattime']); 
                    $value['pname'] = Db::name("wd_xcx_products")->where("uniacid",$appletid)->where("id",$value['pid'])->field("title")->find()['title'];
                    unset($value['pid']);
                    // $value['nickname'] = Db::name("wd_xcx_superuser")->where("uniacid",$appletid)->where("id",$value['suid'])->field("nickname")->find()['nickname'];
                    $value['nickname'] = getNameAvatar($value['suid'], $appletid);
                    // unset($value['openid']);
                }
                $this->assign("video_orders",$video_orders);

                $user_buy_sort = Db::name('wd_xcx_superuser') ->where('uniacid', $appletid) ->order('allpay desc') ->limit(5) ->select();
                foreach ($user_buy_sort as $uk => $user_item){
                    $user_buy_sort[$uk]['sort'] = $uk + 1;
                    $user_buy_sort[$uk]['username'] = getNameAvatar($user_item['id'], $appletid)['nickname'];
                    $main_order_count = Db::name('wd_xcx_main_shop_order') ->where('uniacid', $appletid) ->where('suid', $user_item['id']) ->where('status', 'GT', 0) ->count();

                    $shop_order_count = Db::name('wd_xcx_duo_products_order') ->where('uniacid', $appletid) ->where('suid', $user_item['id']) ->where("flag","in",[1,2,4,6,7,9,10]) ->count();

                    $order_count = Db::name('wd_xcx_order') ->where('uniacid', $appletid) ->where('suid', $user_item['id']) ->where("flag","in",[1,2,4,6,7,9,10]) ->count();

                    $pt_order_count = Db::name('wd_xcx_pt_order') ->where('uniacid', $appletid) ->where('suid', $user_item['id']) ->where("flag","in",[1,2,4,6,7,9,10]) ->count();

                    $bargain_order_count = Db::name('wd_xcx_bargain_order') ->where('uniacid', $appletid) ->where('suid', $user_item['id']) ->where("flag","in",[1,2,4,6,7,9,10]) ->count();

                    $user_buy_sort[$uk]['total_buy_num'] = $main_order_count + $shop_order_count + $order_count + $pt_order_count + $bargain_order_count;

                    $user_buy_sort[$uk]['allpay'] = number_format($user_item['allpay'], 2);
                }

                $this->assign("user_buy_sort",$user_buy_sort);
                //SELECT type,cid,COUNT(*) as num FROM `{$this->prefix}wd_xcx_collect` GROUP BY cid,type ORDER BY num desc;
                $collect_max = Db::name("wd_xcx_collect")->field("count(*) as num")->where("uniacid",$appletid)->group("cid,type")->order("num desc")->limit(0,1)->find()['num'];
                    $this->assign("collect_max",$collect_max);
                $collects = Db::name("wd_xcx_collect")->where("uniacid",$appletid)->where("type","in",['showPro','showProMore','showPro_lv','shopsPro'])->group("cid,type")->limit(0,5)->field("id,type, cid,count(*) as num")->order("num desc")->select();
                // $collects['num'] = count($collects);
                foreach ($collects as $key => &$value) {
                    if($value['type'] == 'shopsPro'){
                        // $value['title'] = Db::name("wd_xcx_shops_goods")->where("uniacid",$appletid)->where("id",$value['cid'])->field("title")->find()['title'];
                        $value['title'] = 1;
                    }else{
                        $value['title'] = Db::name("wd_xcx_products")->where("uniacid",$appletid)->where("id",$value['cid'])->field("title")->find()['title'];
                    }
                    unset($value['cid']);
                    unset($value['type']);
                }
                
                $this->assign("collects",$collects);
                $sale_max_1 = Db::name("wd_xcx_products")->where("uniacid",$appletid)->max("sale_tnum");
                // $sale_max_2 = Db::name("wd_xcx_shops_goods")->where("uniacid",$appletid)->max("rsales");
                $sale_max_2 = 0;
                $sale_max = max($sale_max_1, $sale_max_2);
                $this->assign("sale_max",$sale_max);
                $sales_1 = Db::name("wd_xcx_products")->where("uniacid",$appletid)->order("sale_tnum desc")->limit(0,5)->field("id,title,sale_tnum as rsales")->select();
                // $sales_2 = Db::name("wd_xcx_shops_goods")->where("uniacid",$appletid)->order("rsales desc")->limit(0,5)->field("id,title,rsales")->select();
                $sales_2 = [];
                $sales = array_merge($sales_1, $sales_2);
                $key_value = $new_array = array();
                foreach($sales as $k => $v){
                    $key_value[$k] = $v['rsales'];
                }
                arsort($key_value);
                // reset($key_value);
                foreach ($key_value as $k => $v) {
                    $new_array[] = $sales[$k];
                }
                $sales = $new_array;
                for($i = 5; $i < 10; $i++){
                    unset($sales[$i]);
                }
                
                $this->assign("sales",$sales);
                $credit_max = Db::name("wd_xcx_user")->where("uniacid",$appletid)->max("score");
                $credits = Db::name("wd_xcx_user")->where("uniacid",$appletid)->order("score desc")->limit(0,5)->field("id,nickname,score")->select(); 
                foreach ($credits as $key => &$value) {
                    $value['nickname'] = rawurldecode($value['nickname']);
                }
                $this->assign('credit_max',$credit_max);
                $this->assign('credits',$credits);
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
            //根据所属套装组获取权限
            if($res['combo_id'] == 0){
                //$this ->error('请设置小程序的套餐组');
                $id = Db::name('wd_xcx_rule') -> field('id') ->select();
                $node_id = array();
                foreach ($id as $item) {
                    $node_id[] = (string)$item['id'];
                }
            }else{
                $combo = Db::name('wd_xcx_combo') ->where('id', $res['combo_id']) ->find();
                if($combo){
                    if($combo['node_id']){
                        $node_id = unserialize($combo['node_id']);
                    }else{
                        $this->error('请为您的功能套餐设置权限!');
                    }
                }else{
                    $this->error('请设置功能套餐, 或您的功能套餐已被删除!');
                    exit;
                }
            }
            $_SESSION['node_id'] = $node_id;
            //$this ->assign('node_id', $node_id);
            return $this->fetch('index');
        }else{
            $this->redirect('Login/index');
        }
        
    }
    public function statistics(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);

                $proType = input('proType');
                $proTypes = array('duo', 'miaosha', 'yuyue');
                $proType = in_array($proType, $proTypes) ? $proType : 'duo';

                $year_now = input('year_now') ? input('year_now') : date('Y');
                $month = input('month') ? input('month') : 0;
                $montharr = array(1,2,3,4,5,6,7,8,9,10,11,12);
                $day = input('day') ? input('day') : 0;

                $type = input('type') ? input('type') : 0; //0交易额 1交易量
                if($type == 0){ // 交易额
                    if($proType == "duo"){
                        $where = "round(sum(CASE
        WHEN change_price > 0 THEN
            change_price
        ELSE
            pay_money
        END),2) as avePrice";
                    }else{
                        $where = "round(sum(`true_price`),2) as avePrice";
                    }
                }else{//交易量
                    $where = "id";
                }
                $max = array();
                $alldata = array();
                if($day > 0){
                    $start = strtotime($year_now."-".$month."-".$day." 0:0:0");
                    $end = strtotime($year_now."-".$month."-".$day." 23:59:59");
                    if($proType == "duo"){
                        $all = Db::query("SELECT {$where} FROM {$this->prefix}wd_xcx_main_shop_order WHERE uniacid = {$appletid} and status > 0 and creat_time >= {$start} and creat_time <= {$end}");
                        if($type == 1){
                            $all = count($all);
                        }else{
                            $all = $all[0]['avePrice'];
                        }
                    }else if($proType == "yuyue"){
                        $all = Db::query("SELECT {$where} FROM {$this->prefix}wd_xcx_order WHERE is_more = 1 and uniacid = {$appletid} and flag in (1,2,4,7,9) and creattime >= {$start} and creattime <= {$end}");
                        if($type == 1){
                            $all = count($all);
                        }else{
                            $all = $all[0]['avePrice'];
                        }
                    }else if($proType == "miaosha"){
                        $all = Db::query("SELECT {$where} FROM {$this->prefix}wd_xcx_order WHERE is_more = 0 and uniacid = {$appletid} and flag in (1,2,4,7,9) and creattime >= {$start} and creattime <= {$end}");
                        if($type == 1){
                            $all = count($all);
                        }else{
                            $all = $all[0]['avePrice'];
                        }
                    }
                    $alls = $all?$all:0;
                    for($i=1; $i<=24; $i++){
                        $first = strtotime($year_now."-".$month."-".$day." ".$i.":0:0");
                        $last = strtotime($year_now."-".$month."-".$day." ".$i.":59:59");
                        //每日的总销售额
                        if($proType == "duo"){
                            $all_son = Db::query("SELECT {$where} FROM {$this->prefix}wd_xcx_main_shop_order WHERE uniacid = {$appletid} and status > 0 and creat_time >= {$first} and creat_time <= {$last}");
                            if($type == 1){
                                $all_son = count($all_son);
                            }else{
                                $all_son = $all_son[0]['avePrice'];
                            }
                        }else if($proType == "yuyue"){
                            $all_son = Db::query("SELECT {$where} FROM {$this->prefix}wd_xcx_order WHERE is_more = 1 and uniacid = {$appletid} and flag in (1,2,4,7,9) and creattime >= {$first} and creattime <= {$last}");
                            if($type == 1){
                                $all_son = count($all_son);
                            }else{
                                $all_son = $all_son[0]['avePrice'];
                            }
                        }else if($proType == "miaosha"){
                            $all_son = Db::query("SELECT {$where} FROM {$this->prefix}wd_xcx_order WHERE is_more = 0 and uniacid = {$appletid} and flag in (1,2,4,7,9) and creattime >= {$first} and creattime <= {$last}");
                            if($type == 1){
                                $all_son = count($all_son);
                            }else{
                                $all_son = $all_son[0]['avePrice'];
                            }
                        }
                        array_push($max,$all_son);
                        $alldata[$i]['all'] = $all_son?$all_son:0;
                        $alldata[$i]['per'] = $all_son?round(($all_son / $alls)*100, 2):0;
                    }
                }else if($month > 0){
                    $nextMonth = (($month+1)>12) ? 1 : ($month+1);
                    $year_now = ($nextMonth>12) ? ($year_now+1) : $year_now;
                    $days = date('d',mktime(0,0,0,$nextMonth,0,$year_now));
                    $start = strtotime($year_now."-".$month."-1 0:0:0");
                    $end = strtotime(($year_now)."-".$month."-".$days." 23:59:59");
                    if($proType == "duo"){
                        $all = Db::query("SELECT {$where} FROM {$this->prefix}wd_xcx_main_shop_order WHERE uniacid = {$appletid} and status > 0 and creat_time >= {$start} and creat_time <= {$end}");
                        if($type == 1){
                            $all = count($all);
                        }else{
                            $all = $all[0]['avePrice'];
                        }
                    }else if($proType == "yuyue"){
                        $all = Db::query("SELECT {$where} FROM {$this->prefix}wd_xcx_order WHERE is_more = 1 and uniacid = {$appletid} and flag in (1,2,4,7,9) and creattime >= {$start} and creattime <= {$end}");
                        if($type == 1){
                            $all = count($all);
                        }else{
                            $all = $all[0]['avePrice'];
                        }
                    }else if($proType == "miaosha"){
                        $all = Db::query("SELECT {$where} FROM {$this->prefix}wd_xcx_order WHERE is_more = 0 and uniacid = {$appletid} and flag in (1,2,4,7,9) and creattime >= {$start} and creattime <= {$end}");
                        if($type == 1){
                            $all = count($all);
                        }else{
                            $all = $all[0]['avePrice'];
                        }
                    }
                    $alls = $all?$all:0;
                    for($i=1; $i<=$days; $i++){
                        $first = strtotime($year_now."-".$month."-".$i." 0:0:0");
                        $last = strtotime($year_now."-".$month."-".$i." 23:59:59");
                        //每日的总销售额
                        if($proType == "duo"){

                            $all_son = Db::query("SELECT {$where} FROM {$this->prefix}wd_xcx_main_shop_order WHERE uniacid = {$appletid} and status > 0 and creat_time >= {$first} and creat_time <= {$last}");
                            if($type == 1){
                                $all_son = count($all_son);
                            }else{
                                $all_son = $all_son[0]['avePrice'];
                            }
                        }else if($proType == "yuyue"){
                            $all_son = Db::query("SELECT {$where} FROM {$this->prefix}wd_xcx_order WHERE is_more = 1 and uniacid = {$appletid} and flag in (1,2,4,7,9) and creattime >= {$first} and creattime <= {$last}");
                            if($type == 1){
                                $all_son = count($all_son);
                            }else{
                                $all_son = $all_son[0]['avePrice'];
                            }
                        }else if($proType == "miaosha"){
                            $all_son = Db::query("SELECT {$where} FROM {$this->prefix}wd_xcx_order WHERE is_more = 0 and uniacid = {$appletid} and flag in (1,2,4,7,9) and creattime >= {$first} and creattime <= {$last}");
                            if($type == 1){
                                $all_son = count($all_son);
                            }else{
                                $all_son = $all_son[0]['avePrice'];
                            }
                        }
                        array_push($max,$all_son);
                        $alldata[$i]['all'] = $all_son?$all_son:0;

                        $alldata[$i]['per'] = $all_son?round(($all_son / $alls)*100, 2):0;
                    }

                }else{//年 
                    $start = strtotime($year_now."-1-1 0:0:0");
                    $end = strtotime(($year_now)."-12-31 23:59:59");
                    if($proType == "duo"){

                        $all = Db::query("SELECT {$where} FROM {$this->prefix}wd_xcx_main_shop_order WHERE uniacid = {$appletid} and status > 0 and creat_time >= {$start} and creat_time <= {$end}");
                        if($type == 1){
                            $all = count($all);
                        }else{
                            $all = $all[0]['avePrice'];
                        }
                    }else if($proType == "yuyue"){
                        $all = Db::query("SELECT {$where} FROM {$this->prefix}wd_xcx_order WHERE is_more = 1 and uniacid = {$appletid} and flag in (1,2,4,7,9) and creattime >= {$start} and creattime <= {$end}");
                        if($type == 1){
                            $all = count($all);
                        }else{
                            $all = $all[0]['avePrice'];
                        }
                    }else if($proType == "miaosha"){
                        $all = Db::query("SELECT {$where} FROM {$this->prefix}wd_xcx_order WHERE is_more = 0 and uniacid = {$appletid} and flag in (1,2,4,7,9) and creattime >= {$start} and creattime <= {$end}");
                        if($type == 1){
                            $all = count($all);
                        }else{
                            $all = $all[0]['avePrice'];
                        }
                    }
                    $alls = $all?$all:0;
                
                    for($i=1; $i<=12; $i++){
                        $first = strtotime($year_now."-".$i."-1");
                        if($i<12){
                            $j = $i + 1;
                            $last = strtotime($year_now."-".$j."-1") - 1;
                        }else{
                            $last = strtotime($year_now."-12-31 23:59:59");
                        }
                        //每月的总额
                        if($proType == "duo"){

                            $all_son = Db::query("SELECT {$where} FROM {$this->prefix}wd_xcx_main_shop_order WHERE uniacid = {$appletid} and status > 0 and creat_time >= {$first} and creat_time <= {$last}");


                            if($type == 1){
                                $all_son = count($all_son);
                            }else{
                                $all_son = $all_son[0]['avePrice'];
                            }
                        }else if($proType == "yuyue"){
                            $all_son = Db::query("SELECT {$where} FROM {$this->prefix}wd_xcx_order WHERE is_more = 1 and uniacid = {$appletid} and flag in (1,2,4,7,9) and creattime >= {$first} and creattime <= {$last}");
                            if($type == 1){
                                $all_son = count($all_son);
                            }else{
                                $all_son = $all_son[0]['avePrice'];
                            }
                        }else if($proType == "miaosha"){
                            $all_son = Db::query("SELECT {$where} FROM {$this->prefix}wd_xcx_order WHERE is_more = 0 and uniacid = {$appletid} and flag in (1,2,4,7,9) and creattime >= {$first} and creattime <= {$last}");
                            if($type == 1){
                                $all_son = count($all_son);
                            }else{
                                $all_son = $all_son[0]['avePrice'];
                            }
                        }
                        array_push($max,$all_son);
                        $alldata[$i]['all'] = $all_son?$all_son:0;
                        $alldata[$i]['per'] = $all_son?round(($all_son / $alls)*100, 2):0;
                    }
                }
                $maxs = max($max)?max($max):0;

                //搜索条件年start
                $years = array();
                $currentYear = date('Y');
                for ($i=0; $i< 10; $i++)
                {
                    $years[$i] = $currentYear - $i;
                }
                $years = array_reverse($years);
                $years = array();
                $currentYear = date('Y');
                for ($i=0; $i< 10; $i++)
                {
                    $years[$i] = $currentYear - $i;
                }
                $years = array_reverse($years);
                //搜索条件年end
                $this->assign('proType', $proType);
                $this->assign('years', $years);
                $this->assign('year_now', $year_now);
                $this->assign('montharr', $montharr);
                $this->assign('month', $month);
                $this->assign('type', $type);
                $this->assign('alls', $alls);
                $this->assign('maxs', $maxs);
                $this->assign('day', $day);
                $this->assign('alldata', $alldata);
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
            return $this->fetch('statistics');
        }else{

            $this->redirect('Login/index');

        }
    }
    public function getdays()
    {
        $year = input('year');
        $month = input('month');
        $nextMonth = (($month+1)>12) ? 1 : ($month+1);
        $year = ($nextMonth>12) ? ($year+1) : $year;
        $days = date('d',mktime(0,0,0,$nextMonth,0,$year));
        echo $days;
    }

    public function ranking()
    {
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);

                $proType = input('proType');
                $proTypes = array('duo', 'miaosha', 'yuyue');
                $proType = in_array($proType, $proTypes) ? $proType : 'duo';
                $type = input('type') ? input('type') : 0; //0销售额 1销售量

                $pageindex = max(1, intval(input('page')));
                $pagesize = 10;  
                $start = ($pageindex-1) * $pagesize;

                $start_time = input('start_time');
                $end_time = input('end_time');
               
                if(!empty($start_time) || !empty($end_time)){
                    $datetime=array(
                        'start' => $start_time,
                        'end' => $end_time,
                        );
                }else{
                    $datetime = array(
                        'start' => "",
                        'end' => "",
                        );
                }

                $starts = 0;
                if(!empty($datetime)){
                    $starts = strtotime($datetime['start']);
                    $end = strtotime($datetime['end']);
                }

                if($type == 0){
                    $where = "allprices";
                }else{
                    $where = "allnums";
                }
                if($proType == "yuyue"){
                    $where1 = "a.is_more = 1";
                }else{
                    $where1 = "a.is_more = 0";
                }

                if($proType == "duo"){
                    $lists = Db::name("wd_xcx_products")->where('type', 'showProMore')->where('uniacid', $appletid)->field('id, title')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"),'proType' => $proType, 'starts' => $starts, 'end' => $end, 'type' =>$type)]);
                    $list = Db::name("wd_xcx_products")->where('type', 'showProMore')->where('uniacid', $appletid)->field('id, title')->select();

                    if($list){
                        foreach ($list as $k => $v) {
                            if($starts > 0){
                                $order = Db::name("wd_xcx_main_shop_order_item")->where('uniacid', $appletid)->where("pro_id", $v['id'])->where('status', 'gt', 0)->where('creat_time', '>=', $starts)->where('creat_time', '<=', $end)->select();
                            }else{
                                $order = Db::name("wd_xcx_main_shop_order_item")->where('uniacid', $appletid)->where("pro_id", $v['id'])->where('status', 'gt', 0)->select();
                            }

                            if(!empty($order)){
                                $allprices = 0;
                                $allnums = 0;
                                foreach($order as $ki => $vi){
                                    $allprices += $vi['pro_discounts_price'] * $vi['num'];
                                    $allnums += $vi['num'];
                                }
                                $list[$k]['allprices'] = $allprices;
                                $list[$k]['allnums'] = $allnums;
                            }else{
                                $list[$k]['allprices'] = 0;
                                $list[$k]['allnums'] = 0;
                            }
                        }

                        if($type == 0){
                            $column = array_column($list, 'allprices');
                        }else{
                            $column = array_column($list, 'allnums');
                        }
                        array_multisort($column,SORT_DESC,$list);
                    }

                    $list1 = array();
                    $j = 0;
                    foreach($list as $kk => $vv){
                        if($kk >= $start && $kk < ($start + 10)){
                            $list1[$j] = $vv;
                            $j++;
                        }
                    }
                    $list = $list1;
           
                }else if($proType == "yuyue" || $proType == "miaosha"){
                    if($starts > 0){
                        $lists = Db::name("wd_xcx_products")->alias('a')->join('wd_xcx_order b', 'a.id = b.pid')->where($where1)->where('a.type', 'showPro')->where('a.uniacid', $appletid)->where('b.flag', 'in', '1,2,4,7,9')->where('creattime', '>=', $starts)->where('creattime', '<=', $end)->group('a.id')->order("{$where} desc")->field('a.title,round(sum(b.price),2) as allprices,sum(b.num) as allnums')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"),'proType' => $proType, 'starts' => $starts, 'end' => $end, 'type' =>$type)]);
                    }else{
                        $lists = Db::name("wd_xcx_products")->alias('a')->join('wd_xcx_order b', 'a.id = b.pid')->where($where1)->where('a.type', 'showPro')->where('a.uniacid', $appletid)->where('b.flag', 'in', '1,2,4,7,9')->group('a.id')->order("{$where} desc")->field('a.title,round(sum(b.price),2) as allprices,sum(b.num) as allnums')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"),'proType' => $proType, 'starts' => $starts, 'end' => $end, 'type' =>$type)]);
                    }
                    $list = $lists->toArray()['data'];
                }
                $counts = count($list);
                $this->assign('counts', $counts);

                if($lists == ""){
                    $this->assign('render', "");
                }else{
                    $this->assign('render', $lists->render());
                }
                $this->assign('list', $list);
                $this->assign('page', intval(input('page')));
                $this->assign('proType', $proType);
                $this->assign('starts', $start_time);
                $this->assign('end', $end_time);
                $this->assign('type', $type);
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
            return $this->fetch('ranking');
        }else{

            $this->redirect('Login/index');

        }
    }
}