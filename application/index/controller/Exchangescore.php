<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;

use app\index\model\ImsSudu8PageScoreCate as Cate;
use app\index\model\Applet;
use app\index\model\ImsSudu8PageScoreShop as Goods;
use app\index\model\ProductsUrl as Imgs;
use app\index\model\ImsSudu8PageMessage as Msg;

class Exchangescore extends Base
{
    public function catelist(){
        if(check_login()){
            if(powerget()){
                $uniacid = input('appletid');
                $appinfo = Db::name('wd_xcx_applet')->where("id", $uniacid)->find();
                $this->assign('applet',$appinfo);

                $listV_s = Db::name('wd_xcx_score_cate') ->where('uniacid', $uniacid) ->order('num desc') ->paginate(10,false,['query' => ['appletid' => $uniacid]]);
                $listV = $listV_s->toArray()['data'];
                foreach ($listV as $key => &$value) {
                    $value['catepic'] = remote($uniacid,$value['catepic'],1);
                }
                $this->assign('cates',$listV);
                $this->assign('cates_list',$listV_s);

                return $this->fetch('catelist');
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
            
        }else{
            $this->redirect('Login/index');
        }
        
    }
    public function cateadd(){
        if(check_login()){
            if(powerget()){
                $uniacid = input('appletid');
                $app = new Applet;
                $appinfo = $app ->getAppInfo();
                $this->assign('applet',$appinfo);

                $cateid = input("cateid");
                if($cateid){
                    //有栏目号时，先判断该栏目是不是属于该小程序！
                    // $cate = new Cate;
                    $lanmu = Db::name('wd_xcx_score_cate')->where("id", $cateid)->find();
                    // Cate::get(['id' => $cateid]);
                    if($lanmu['uniacid'] == $uniacid){
                        $lanmu['catepic'] = remote($uniacid,$lanmu['catepic'],1);
                        $cateinfo = $lanmu;
                    }else{
                        $usergroup = Session::get('usergroup');
                        if($usergroup==1){
                            $this->error("找不到该栏目，或者该栏目不属于本小程序");
                        }
                        if($usergroup==2){
                            $this->error("找不到该栏目，或者该栏目不属于本小程序");
                        }
                    }
                    
                }else{
                    $cateid=0;
                    $cateinfo = "";
                }
                $this->assign('cateid',$cateid);
                $this->assign('cateinfo',$cateinfo);
          
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
            return $this->fetch('cateadd');
        }else{
            $this->redirect('Login/index');
        }
        
    }
    public function catesave(){
        $data = array();
        //小程序ID
        $data['uniacid'] = input("appletid");
        //排序
        $num = input("num");
        if($num){
            $data['num'] = $num;
        }
        $name = input("name");
        if($name){
            $data['name'] = $name;
        }
        //栏目图片
        $catepic = input("commonuploadpic");
        if($catepic){
            $data['catepic'] = remote($data['uniacid'],$catepic,2);
        }
        
        $id = input("cateid");

        $cate = new Cate;
        if($id!=0){
            $res = $cate->save($data, ['id' => $id]);
        }else{
            $res = $res = $cate->save($data);
        }
        if($res){
          $this->success('栏目信息添加/更新成功！',Url('Exchangescore/catelist').'?appletid='. $data['uniacid']);
        }else{
          $this->error('栏目信息添加/更新失败，没有修改项！');
          exit;
        }
    }
    // 删除操作
    public function catedel(){
        $data['id'] = input("cateid");
        //查询该栏目下是否有商品
        $goods = new Goods;
        $count = $goods ->where('cid', $data['id']) ->count();
        if($count>0){
            $this->error('改栏目下还有商品，请删除后再删除！');
        }
        $cate = new Cate;
        $res = $cate->where($data)->delete();
        if($res){
            $this->success('删除成功');
        }else{
            $this->success('删除失败');
        }
    }

    public function goodslist(){

        if(check_login()){


            if(powerget()){

                $uniacid = input('appletid');
                $app = new Applet;
                $appinfo = $app ->getAppInfo();
                $this->assign('applet',$appinfo);

                $cid = input("cid") ? input("cid") : 0;
                $title = input("key");

                $where = [];
                if($cid > 0){
                    $where['cid'] = $cid;
                }

                if($title){
                    $where['title'] = ['like',"%".$title."%"];
                }

                $goods = new Goods;
                $pros = $goods->getPros($where);
                $products = $pros->toArray()['data'];
                $counts = count($products);
                foreach ($products as $key => &$value) {
                    $value['name'] = Db::name('wd_xcx_score_cate')->where('uniacid',$uniacid) ->where('id', $value['cid'])->value('name');
                   if($value['thumb']){
                       $products[$key]['thumb'] = remote($uniacid,$value['thumb'],1);
                   }else{
                       $products[$key]['thumb'] = remote($uniacid,"/image/noimage.jpg",1);
                   }
                }

                $cate = Db::name('wd_xcx_score_cate')->where('uniacid',$uniacid)->select();
                $this->assign('key',$title);
                $this->assign('cid',$cid);
                $this->assign('cate',$cate);

                $this->assign('counts',$counts);
                $this->assign('page',$pros->render());
                $this->assign('products',$products);
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

            return $this->fetch('goods');
        }else{
            $this->redirect('Login/index');
        }
        
    }



    public function goodsadd(){

        if(check_login()){

            if(powerget()){

                $uniacid = input('appletid');
                $app = new Applet;
                $appinfo = $app ->getAppInfo();
                $this->assign('applet',$appinfo);


                $cate = new Cate;
                $cates = $cate->getCates();
                $this->assign('cate',$cates);
                $pid = input("pid");
                if($pid){
                    $goods = new Goods;
                    $products = $goods->get(['id' => $pid]);
                    if($products['uniacid']==$uniacid){
                        $imgs = new Imgs;
                        $allimg = $imgs->all(['randid' => $products['onlyid']]);
                        foreach ($allimg as $key => &$value) {
                            $value['url'] = remote($uniacid,$value['url'],1);
                        }
                        $products['thumb'] = remote($uniacid,$products['thumb'],1);
                    }
                }else{
                    $products = "";
                    $pid = 0;
                    $allimg ="";
                }
                $this->assign('allimg',$allimg);
                $this->assign('pid',$pid);
                $this->assign('products',$products);
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
            return $this->fetch('goodsadd');
        }else{
            $this->redirect('Login/index');
        }
        
    }


    public function goodssave(){

        $data = array();
        //小程序ID
        $data['uniacid'] = input("appletid");
        $onlyid = $_POST['onlyid'];
        if($onlyid){
            $data['onlyid'] = $onlyid;
        }
        // 处理幻灯片
        if(!$onlyid){

        }else{
            $imgsrcs = input("imgsrcs/a");
            $products_url = model('ProductsUrl');
            if($imgsrcs){
                $imgarr = array();
                foreach ($imgsrcs as $k => $v) {
                    $imgarr['randid'] = $onlyid;
                    $imgarr['appletid'] = $data['uniacid'];
                    $imgarr['url'] = remote($data['uniacid'],$v,2);
                    $imgarr['dateline'] = time();
                    $is = $products_url->save($imgarr);
                }
            }else{
                $is = 1;
            }
            $slide = $products_url->all(["randid" => $onlyid]);
            $arrsilde = array();
            if($slide){

                foreach ($slide as $rec) {

                    $arrsilde[]=$rec['url'];

                }

                $data['text'] = serialize($arrsilde);

            }else{

                $data['text'] = "";

            }
        }

        //排序
        $num = input("num");
        if($num){
            $data['num'] = intval($num);
        }

        $cid = input("cid");
        if($cid){
            $data['cid'] = intval($cid);
        }

        $hits = input("hits");
        if($hits){
            $data['hits'] = $hits;
        }

        $sale_num = input("sale_num");
        if($sale_num){
            $data['sale_num'] = $sale_num;
        }

        $price = input("price");
        if($price){
            $data['price'] = $price;
        }

        $market_price = input("market_price");
        if($market_price){
            $data['market_price'] = $market_price;
        }

        $pro_kc = input("pro_kc");
        if($pro_kc){
            $data['pro_kc'] = $pro_kc;
        }

        $sale_tnum = input("sale_tnum");
        if($sale_tnum){
            $data['sale_tnum'] = $sale_tnum;
        }
        $labels = input("labels");
        if($labels){
            $data['labels'] = $labels;
        }
        $title = input("title");
        if($title){
            $data['title'] = $title;
        }
        $desc = input("desc");
        if($desc){
            $data['desk'] = $desc;
        }

        $text = input("text");
        if($text){
            $data['product_txt'] = htmlspecialchars_decode($text);
        }

        //产品图片
        $thumb = input("commonuploadpic");
        if($thumb){
            $data['thumb'] = remote($data['uniacid'],$thumb,2);
        }
        
        $data['video'] = input("video");
        $id = input("pid");
        $goods = new Goods;
        if($id != 0){
            $res = $goods->save($data, ['id' => $id]);
        }else{
            $res = $goods->save($data);
        }
        if($res){
          $this->success('积分商品信息添加/更新成功！',Url('Exchangescore/goodslist').'?appletid='. $data['uniacid']);
        }else{
          $this->error('积分商品信息添加/更新失败，没有修改项！');
          exit;
        }
    }

    // 删除操作
    public function goodsdel(){
        $data['id'] = input("pid");
        $res = Db::name('wd_xcx_score_shop')->where($data)->delete();
        if($res){
            $this->success('删除成功');
        }else{
            $this->success('删除失败');
        }
    }

    public function msg(){
        if(check_login()){
            if(powerget()){
                $uniacid = input('appletid');
                $app = new Applet;
                $appinfo = $app ->getAppInfo();
                $this->assign('applet',$appinfo);

                $msg = new Msg;
                $base = $msg->get(['uniacid' => $uniacid,'flag' => 5]);
                $this->assign('base',$base);

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

        $data = array();
        $uniacid = input("appletid");
        //消息模板id
        $pay_id = input("pay_id");
        $data['mid'] = trim($pay_id);

        $url = input("url");
        $data['url'] = trim($url);

        $msg = new Msg;
        $count = $msg->where('uniacid', $uniacid)->where('flag', 5)->count();
        if($count>0){
            $res = $msg->save($data, ['uniacid' => $uniacid, 'flag' => 5]);
        }else{
            $data['flag'] = 5;
            $data['uniacid'] = $uniacid;
            $res = $msg->save($data);
        }

        if($res){
          $this->success('积分兑换通知更新成功！');
        }else{
          $this->error('积分兑换通知更新失败，没有修改项！');
          exit;
        }
    }

    public function orderlist(){

        if(check_login()){


            if(powerget()){

                $uniacid = input('appletid');
                $app = new Applet;
                $appinfo = $app ->getAppInfo();
                $this->assign('applet',$appinfo);
                
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
                
                $listV = Db::name('wd_xcx_score_order')->where("uniacid",$uniacid)->where($where)->order('id desc')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                $counts = count($listV);
                $data = $listV->all();
                foreach ($data as $key => &$value) {
                    $userinfo = Db::name('wd_xcx_superuser')->where("id", $value['suid'])->field("truename as realname,phone as mobile")->find();
                    $value['realname'] = $userinfo['realname'];
                    $value['mobile'] = $userinfo['mobile'];
                }
                $this->assign('counts',$counts);
                $this->assign('listV',$data);
                $this->assign('page',$listV->render());

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

    public function hx(){
        $data['custime'] = time();
        $data['flag'] = 1;
        $order_id = input('order_id');
        $res = Db::name('wd_xcx_score_order')->where('order_id',$order_id)->update($data);
        if($res){
            $this->success('兑换成功');
        }else{
            $this->success('兑换失败');
        }
    }

    public function orderdown(){
        $uniacid = input("appletid");
        $app = new Applet;
        $appinfo = $app ->getAppInfo();
        $this->assign('applet',$appinfo);
        $orders = Db::name('wd_xcx_score_order')->alias('a')->join('wd_xcx_user b','a.openid = b.openid')->join('wd_xcx_score_shop c','a.pid = c.id')->join('wd_xcx_score_cate d','c.cid = d.id')->where("b.uniacid",$uniacid)->order('a.id desc')->field('a.*,b.realname,b.mobile,d.name')->select();

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
        $objPHPExcel->getActiveSheet()->setCellValue('B1', '产品图片');
        $objPHPExcel->getActiveSheet()->setCellValue('C1', '产品名称');
        $objPHPExcel->getActiveSheet()->setCellValue('D1', '产品分类');
        $objPHPExcel->getActiveSheet()->setCellValue('E1', '单价/数量');
        $objPHPExcel->getActiveSheet()->setCellValue('F1', '订单总价');
        $objPHPExcel->getActiveSheet()->setCellValue('G1', '姓名');
        $objPHPExcel->getActiveSheet()->setCellValue('H1', '联系方式');
        $objPHPExcel->getActiveSheet()->setCellValue('I1', '核销时间');
        $objPHPExcel->getActiveSheet()->setCellValue('J1', '状态');
        $objPHPExcel->getActiveSheet()->setCellValue('K1', '下单时间');
        $objPHPExcel->getActiveSheet()->setCellValue('L1', '小程序uniacid');
        foreach($orders as $k => $v){
            $num=$k+2;
            $v['creattime'] = date("Y-m-d H:i:s",$v['creattime']);
            $v['custime'] = $v['custime'] == 0?"未核销":date("Y-m-d H:i:s",$v['custime']);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('A'.$num, $v['order_id'],'s');
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$num, $v['thumb']);
            $objPHPExcel->getActiveSheet()->setCellValue('C'.$num, $v['product']);
            $objPHPExcel->getActiveSheet()->setCellValue('D'.$num, $v['name']);
            $objPHPExcel->getActiveSheet()->setCellValue('E'.$num, $v['price']."*".$v['num']);
            $objPHPExcel->getActiveSheet()->setCellValue('F'.$num, $v['price']*$v['num']);
            $objPHPExcel->getActiveSheet()->setCellValue('G'.$num, $v['realname']);
            $objPHPExcel->getActiveSheet()->setCellValue('H'.$num, $v['mobile']);
            $objPHPExcel->getActiveSheet()->setCellValue('I'.$num, $v['custime']);
            if($v['flag']==0){
                $flag = "立即兑换";
            }
            if($v['flag']==1 || $v['flag'] == 2){
                $flag = "已兑换";
            }
            $objPHPExcel->getActiveSheet()->setCellValue('J'.$num, $flag);
            $objPHPExcel->getActiveSheet()->setCellValue('K'.$num, $v['creattime']);
            $objPHPExcel->getActiveSheet()->setCellValue('L'.$num, $v['uniacid']);
        }
        $objPHPExcel->getActiveSheet()->setTitle('导出订单列表');
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="积分兑换订单列表.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
}