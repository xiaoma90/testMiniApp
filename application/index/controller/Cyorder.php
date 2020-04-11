<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;


class Cyorder extends Base
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


                $search_flag = input('search_flag');
                $search_keys = input('search_keys');
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

                if ($search_keys) {
                    $where['order_id'] = ["like", "%".$search_keys ."%"];
                }

                $listV = Db::name('wd_xcx_food_order')->where("uniacid",$appletid)->where($where)->order('id desc')->paginate(10, false, [ 'query' => array('appletid'=>input("appletid"))]);

                $counts = count($listV);
                $data = $listV->all();
                foreach ($data as $k => $v) {
                    $data[$k]['val'] = unserialize($data[$k]['val']);
                    $data[$k]['score_info'] = $v['score_info'] ? unserialize($data[$k]['score_info']) : [];
                    foreach ($data[$k]['val'] as $ky => $vy) {
                        $data[$k]['val'][$ky]['thumb'] = Db::name('wd_xcx_food')->where("id",$vy[0])->find()['thumb'];
                        if( $data[$k]['val'][$ky]['thumb']){
                            $data[$k]['val'][$ky]['thumb'] = remote($appletid,$data[$k]['val'][$ky]['thumb'],1);
                        }else{
                            $data[$k]['val'][$ky]['thumb'] = remote($appletid,"/image/noimage_1.png",1);
                        }
                        $data[$k]['val'][$ky]['totalPay'] = $vy[1] * $vy[3];
                    }
                }

                if($start_get){
                    $start_get = date("Y-m-d H:i:s", $start_get);
                }
                if($end_get){
                    $end_get = date("Y-m-d H:i:s", $end_get);
                }
                $this->assign("search_flag", $search_flag);
                $this->assign("search_keys", $search_keys);
                $this->assign("start_get", $start_get);
                $this->assign("end_get", $end_get);

                $this->assign('counts',$counts);
                $this->assign('cates',$data);
                $this->assign('page',$listV->render());
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


    public function orderdown(){
        $appletid = input("appletid");
        $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
        if(!$res){
            $this->error("找不到对应的小程序！");
        }
        $this->assign('applet',$res); 

        $search_flag = input('search_flag');
        $search_keys = input('search_keys');
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

        if ($search_keys) {
            $where['order_id'] = ["like", "%".$search_keys ."%"];
        }

        $orders = Db::name('wd_xcx_food_order')->where("uniacid",$appletid)->where($where)->order('id desc')->select();


        require_once ROOT_PATH.'public/plugin/PHPExcel/PHPExcel.php';
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()->setCreator("导出订单列表")
                ->setLastModifiedBy("订单列表")
                ->setTitle("导出订单列表")
                ->setSubject("导出订单列表")
                ->setDescription("导出订单列表")
                ->setKeywords("导出订单列表")
                ->setCategory("导出订单列表");

        $objPHPExcel->getActiveSheet()->setCellValue('A1', '订单号');
        $objPHPExcel->getActiveSheet()->setCellValue('B1', '桌号');
        $objPHPExcel->getActiveSheet()->setCellValue('C1', '商品名称');
        $objPHPExcel->getActiveSheet()->setCellValue('D1', '商品分类');
        $objPHPExcel->getActiveSheet()->setCellValue('E1', '单价/数量');
        $objPHPExcel->getActiveSheet()->setCellValue('F1', '订单总价');
        $objPHPExcel->getActiveSheet()->setCellValue('G1', '姓名');
        $objPHPExcel->getActiveSheet()->setCellValue('H1', '联系方式');
        $objPHPExcel->getActiveSheet()->setCellValue('I1', '地址');
        $objPHPExcel->getActiveSheet()->setCellValue('J1', '状态');
        $objPHPExcel->getActiveSheet()->setCellValue('K1', '下单时间');
        $objPHPExcel->getActiveSheet()->setCellValue('L1', '小程序uniacid');
        foreach($orders as $k => $v){
            $num=$k+2;
            $v['val'] = unserialize($v['val']);
            $proname = "";
            $proprice = "";
            $catename = "";
            foreach ($v['val'] as $ki => $vi) {
                $proname .= $vi[2].",";
                $proprice .= $vi[1]."*".$vi[3].",";
                $cate = Db::name("wd_xcx_food")->alias("a")->join("wd_xcx_food_cate b","a.cid = b.id")->where("a.id",$vi[0])->field("b.title as name")->find();
                $catename .= $cate['name'].",";
            }
            $v['creattime'] = date("Y-m-d H:i:s",$v['creattime']);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('A'.$num, $v['order_id'],'s');
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$num, $v['zh']);
            $objPHPExcel->getActiveSheet()->setCellValue('C'.$num, $proname);
            $objPHPExcel->getActiveSheet()->setCellValue('D'.$num, $catename);
            $objPHPExcel->getActiveSheet()->setCellValue('E'.$num, $proprice);
            $objPHPExcel->getActiveSheet()->setCellValue('F'.$num, $v['price']);
            $objPHPExcel->getActiveSheet()->setCellValue('G'.$num, $v['username']);
            $objPHPExcel->getActiveSheet()->setCellValue('H'.$num, $v['usertel']);
            $objPHPExcel->getActiveSheet()->setCellValue('I'.$num, $v['address']);
            if($v['flag']==0){
                $flag = "未支付";
            }
            if($v['flag']==1){
                $flag = "已支付";
            }
            if($v['flag']==2){
                $flag = "已完成";
            }
            if($v['flag']==-1){
                $flag = "已关闭";
            }
            if($v['flag']==-2){
                $flag = "订单无效";
            }
            $objPHPExcel->getActiveSheet()->setCellValue('J'.$num, $flag);
            $objPHPExcel->getActiveSheet()->setCellValue('K'.$num, $v['creattime']);
            $objPHPExcel->getActiveSheet()->setCellValue('L'.$num, $v['uniacid']);
        }
        $objPHPExcel->getActiveSheet()->setTitle('导出订单列表');
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="餐饮订单列表.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
}