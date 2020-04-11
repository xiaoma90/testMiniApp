<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;
class Auction extends Base
{
    public function catelist(){
        if(check_login()){
        	if(powerget()){
        		$id = input("appletid");
        		$res = Db::name('wd_xcx_applet')->where("id",$id)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
        		$this->assign('applet',$res);

                $catelist = array();
                $catelist = Db::name('wd_xcx_auction_column')->where("uniacid",$id)->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                if($catelist->toArray()){
                    $list = $catelist->toArray()['data'];
                }
                $this->assign('list',$list);
                $this->assign('catelist',$catelist);
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
            return $this->fetch('catelist');
        }else{
            $this->redirect('Login/index');
        }
    }
    public function cateadd(){
        if(check_login()){
            if(powerget()){
                $id = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $cateinfo = [];
                $cateid = input('cateid');
                if(!empty($cateid)){
                	$cateinfo = Db::name('wd_xcx_auction_column')->where("uniacid",$id)->where("id",$cateid)->find();
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

    public function excel(){
        $id = input("appletid");
        $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
        if(!$res){
            $this->error("找不到对应的小程序！");
        }
        $this->assign('applet',$res);
            $d1=Db::name('wd_xcx_auction_order')->alias('a')->join('wd_xcx_auction_goodslist b','a.auction_id = b.id')->where('a.uniacid',$id)->where('a.id','like','$order')->order('a.id desc')->field('a.*,b.img,b.name')->select();
            for ($i=0; $i <sizeof($d1) ; $i++) {
              $dd=Db::name('wd_xcx_user')->where('uniacid', $id)->where('openid',$d[$i]['user_id'])->select();
              $d1[$i]['nickname']=rawurldecode($dd[0]['nickname']);
              $d1[$i]['uniacid']=$_W['uniacid'];
              $d1[$i]['img']=remote($id,$d[$i]['img'],1);
            }
            // var_dump($d1[1]['nickname']);die;
            require_once ROOT_PATH.'public/plugin/PHPExcel/PHPExcel.php';
            $objPHPExcel = new \PHPExcel();

            /*以下是一些设置*/
            $objPHPExcel->getProperties()->setCreator("拍卖订单记录")
                ->setLastModifiedBy("拍卖订单记录")
                ->setTitle("拍卖订单记录")
                ->setSubject("拍卖订单记录")
                ->setDescription("拍卖订单记录")
                ->setKeywords("拍卖订单记录")
                ->setCategory("拍卖订单记录");
            $objPHPExcel->getActiveSheet()->setCellValue('A1', '时间');
            $objPHPExcel->getActiveSheet()->setCellValue('B1', '订单编号');
            $objPHPExcel->getActiveSheet()->setCellValue('C1', '拍卖商品名');
            $objPHPExcel->getActiveSheet()->setCellValue('D1', '总价');
            $objPHPExcel->getActiveSheet()->setCellValue('E1', '姓名');
            $objPHPExcel->getActiveSheet()->setCellValue('F1', '联系方式');
            $objPHPExcel->getActiveSheet()->setCellValue('G1', '联系地址');
            $objPHPExcel->getActiveSheet()->setCellValue('H1', '状态');
            foreach($d1 as $k => $v){
                $num=$k+2;
                if($v['stat'] == 0){
                    $v['flag1'] = '待付款';
                }else if($v['stat'] == 1){
                    $v['flag1'] = '待发货';
                }else if($v['stat'] == 2){
                    $v['flag1'] = '已发货';
                }else if($v['stat'] == 3){
                    $v['flag1'] = '已签收';
                }else if($v['stat'] == 4){
                    $v['flag1'] = '订单超时';
                }
                $objPHPExcel->setActiveSheetIndex(0)
                            ->setCellValueExplicit('A'.$num, $v['created_at'],'s')
                            ->setCellValueExplicit('B'.$num, $v['id'],'s')
                            ->setCellValueExplicit('C'.$num, $v['name'],'s') 
                            ->setCellValueExplicit('D'.$num, $v['cost'],'s')
                            ->setCellValueExplicit('E'.$num, $v['nickname'], 's')
                            ->setCellValueExplicit('F'.$num, $v['phone'], 's')
                            ->setCellValueExplicit('G'.$num, $v['address'].$v['address_more'], 's')
                            ->setCellValueExplicit('H'.$num, $v['flag1'], 's');
                  
            }

            // var_dump($d1);exit;

            $objPHPExcel->getActiveSheet()->setTitle('导出拍卖订单');
            $objPHPExcel->setActiveSheetIndex(0);
            $excelname="拍卖订单记录表";
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="'.$excelname.'.xls"');
            header('Cache-Control: max-age=0');
            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output');
            exit;
    }

    public function catesave(){
    	$data = [];
    	$data['uniacid'] = input("appletid");
    	$data['name'] = input("name");
        $cateid = input('cateid');
        if(!empty($cateid)){
        	$res = Db::name("wd_xcx_auction_column")->where('id',$cateid)->update($data);
        }else{
        	$res = Db::name("wd_xcx_auction_column")->insert($data);
        }
        if($res){
          $this->success('栏目信息更新成功！',Url('Auction/catelist').'?appletid='.$data['uniacid']);
        }else{
          $this->error('栏目信息更新失败，没有修改项！！');
          exit;
        }
    }
    public function catedel(){
    	$data['id'] = input("cateid");
        $res = Db::name('wd_xcx_auction_column')->where($data)->delete();
        if($res){
            $this->success('删除成功');
        }else{
            $this->success('删除失败');
        }
    }

    public function goods(){
    	if(check_login()){
        	if(powerget()){
        		$id = input("appletid");
        		$res = Db::name('wd_xcx_applet')->where("id",$id)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
        		$this->assign('applet',$res);

                $goodslist = array();
                $goodslist = Db::name('wd_xcx_auction_goodslist')->where("uniacid",$id)->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                if($goodslist->toArray()){
                    $list = $goodslist->toArray()['data'];
                    foreach ($list as $key => &$value) {
	                    if($value['img']) {
	                        $value['img'] = remote($id,$value['img'],1);
	                    }else{
	                        $value['img']=remote($id,"/image/noimage.jpg",1);
	                    }
	                }
                }
                $this->assign('list',$list);
                $this->assign('goodslist',$goodslist);
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
        		$id = input("appletid");
        		$res = Db::name('wd_xcx_applet')->where("id",$id)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
        		$this->assign('applet',$res);

        		$catelist=Db::name('wd_xcx_auction_column')->where("uniacid",$id)->select();

                $goodsid = input('goodsid');
                $online = input('online');
                $goodsinfo = array();
                if(!empty($goodsid)){
                	$goodsinfo = Db::name('wd_xcx_auction_goodslist')->where("uniacid",$id)->where("id",$goodsid)->find();
	                $goodsinfo['img'] = remote($id,$goodsinfo['img'],1);
	                $goodsinfo['imglist'] = unserialize($goodsinfo['imglist']);
                }
                $this->assign('online',$online);
                $this->assign('goodsid',$goodsid);
                $this->assign('catelist',$catelist);
                $this->assign('goodsinfo',$goodsinfo);
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

    	$data['uniacid'] = input("appletid");
        //排序
        $data['isindex'] = input('isindex');
        $name = input('name');
        $data['name'] = $name;

        //所属栏目
        $data['cid'] = input('cid');

        $img = input("commonuploadpic");
        if($img){
            $data['img'] = remote($data['uniacid'],$img,2);
        }

        $imgsrcs = input("imgsrcs/a");
        if($imgsrcs){
            $data['imglist'] = serialize($imgsrcs);
        }

        $data['price'] = input('price');
        $data['bond'] = input('bond');
        $data['basc_cost'] = input('basc_cost');
        $data['rules'] = input('rules');
        $data['flow'] = input('flow');
        $data['starttime'] = input('starttime');
        $data['endtime'] = input('endtime');
        $data['introduce'] = input('introduce');

        $goodsid = input("goodsid");
        if(!empty($goodsid)){
            $online = input('online');
            if($online == 1){
                $data['stat']=1;
            }
        	$res = Db::name("wd_xcx_auction_goodslist")->where('id',$goodsid)->update($data);
        }else{
        	$res = Db::name("wd_xcx_auction_goodslist")->insert($data);
        }
        if($res){
          $this->success('拍卖品信息更新成功！',Url('Auction/goods').'?appletid='.$data['uniacid']);
        }else{
          $this->error('拍卖品信息更新失败，没有修改项！');
          exit;
        }
    }
    public function deslog(){
        $id = input("appletid");
        $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
        if(!$res){
            $this->error("找不到对应的小程序！");
        }
        $this->assign('applet',$res);

        $goodsid=input('goodsid');
        $d = Db::name('wd_xcx_auction_deposit')->where('auction_id', $goodsid)->order("id desc")->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
        if($d->toArray()){
            $list = $d->toArray()['data'];
        }
        for ($i=0; $i<count($list); $i++) {
            $dd = $list[$i];
            $username = Db::name('wd_xcx_user')->where('uniacid', $id)->where('openid', $dd['user_id'])->find();
            $list[$i]['nickname']=rawurldecode($username['nickname']);
        }
        $this->assign("list",$list);
        $this->assign("d",$d);
        return $this->fetch('deslog');
    }

    public function offerloglist(){
        $id = input("appletid");
        $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
        if(!$res){
            $this->error("找不到对应的小程序！");
        }
        $this->assign('applet',$res);

        $goodsid=input('goodsid');
        $d = Db::name('wd_xcx_auction_log')->where('auction_id', $goodsid)->order("id desc")->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);

        if($d->toArray()){
            $list = $d->toArray()['data'];
        }
        for ($i=0; $i<count($list); $i++) {
            $dd = $list[$i];
            $username = Db::name('wd_xcx_user')->where('uniacid', $id)->where('openid', $dd['user_id'])->find();
            $list[$i]['nickname']=rawurldecode($username['nickname']);
        }
        $this->assign("list",$list);
        $this->assign("d",$d);
        return $this->fetch('offerloglist');
    }
    public function offline(){
        $id = input("appletid");
        $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
        if(!$res){
            $this->error("找不到对应的小程序！");
        }
        $this->assign('applet',$res);

        $goodsid=input('goodsid');
        $d = Db::name('wd_xcx_auction_goodslist')->where('id', $goodsid)->find();

        if($d['stat'] == 1){
           $res = Db::name("wd_xcx_auction_goodslist")->where("id", $goodsid)->update(array('stat' => 0));
        }
        if($res){
          $this->success('拍卖品信息下架成功！',Url('Auction/goods').'?appletid='.$id);
        }else{
          $this->error('拍卖品信息下架失败！');
          exit;
        }
    }

    public function online(){
        $id = input("appletid");
        $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
        if(!$res){
            $this->error("找不到对应的小程序！");
        }
        $this->assign('applet',$res);

        $goodsid=input('goodsid');
        $d = Db::name('wd_xcx_auction_goodslist')->where('id', $goodsid)->find();

        if($d['stat'] == 0){
           $res = Db::name("wd_xcx_auction_goodslist")->where("id", $goodsid)->update(array('stat' => 1));
        }
        if($res){
          $this->success('拍卖品信息上架成功！',Url('Auction/goods').'?appletid='.$id);
        }else{
          $this->error('拍卖品信息上架失败！');
          exit;
        }
    }

    public function orders(){
        $id = input("appletid");
        $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
        if(!$res){
            $this->error("找不到对应的小程序！");
        }
        $this->assign('applet',$res);

        $order = input('order');
        $where = "";
        if(!empty($order)){
            $where = "a.id like {$order}";
        }

        $list = Db::name('wd_xcx_auction_order')->alias('a')->join('wd_xcx_auction_goodslist b','a.auction_id = b.id')->where('a.uniacid', $id)->where($where)->order("a.id desc")->field("a.*, b.img, b.name")->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
        $d = $list->toArray()['data'];
        for ($i=0; $i <count($d) ; $i++) {
            $dd= Db::name('wd_xcx_user')->where('uniacid', $id)->where('openid',$d[$i]['user_id'])->find();
            $d[$i]['nickname']= rawurldecode($dd['nickname']);
            $d[$i]['img']= remote($id,$d[$i]['img'],1);
        }
        $this->assign('counts',count($d));
        $this->assign('d',$d);
        $this->assign('order',$order);
        $this->assign('list',$list);
        return $this->fetch('orders');
    }

    public function deletes(){
        $id = input("appletid");
        $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
        if(!$res){
            $this->error("找不到对应的小程序！");
        }
        $this->assign('applet',$res);
        $orderid = input('orderid');
        $is = Db::name('wd_xcx_auction_order')->where('id', $orderid)->find();
        if(!empty($is)){
            $res = Db::name('wd_xcx_auction_order')->where('id', $orderid)->delete();
            if($res){
              $this->success('拍卖品删除成功！');
            }else{
              $this->error('拍卖品删除失败！');
              exit;
            }
        }
    }

    public function message(){
        if(check_login()){
            if(powerget()){
                $id = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $one=Db::name('wd_xcx_auction_message')->where('class','appointment')->where('uniacid',$id)->find();
                $two=Db::name('wd_xcx_auction_message')->where('class','deposit')->where('uniacid',$id)->find();
                $three=Db::name('wd_xcx_auction_message')->where('class','depositout')->where('uniacid',$id)->find();
                $four=Db::name('wd_xcx_auction_message')->where('class','payok')->where('uniacid',$id)->find();
                $this->assign('one',$one);
                $this->assign('two',$two);
                $this->assign('three',$three);
                $this->assign('four',$four);
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
            return $this->fetch('message');
        }else{
            $this->redirect('Login/index');
        }
    }
    public function msgsave(){
        $id = input("appletid");
        $mid1 = input('mid1');
        $mid2 = input('mid2');
        $mid3 = input('mid3');
        $mid4 = input('mid4');
        $url1 = input('url1');
        $url2 = input('url2');
        $url3 = input('url3');
        $url4 = input('url4');
        $msg1 = Db::name('wd_xcx_auction_message')->where('uniacid',$id)->where('class','appointment')->find();
        $data1 = [
            'mid' => $mid1,
            'url' => $url1,
        ];
        if(empty($msg1)){
            $data1['uniacid'] = $id;
            $data1['class'] = 'appointment';
            $res1 = Db::name('wd_xcx_auction_message')->insert($data1);
        }else{
            $res1 = Db::name('wd_xcx_auction_message')->where('uniacid', $id)->where('class', 'appointment')->update($data1);
        }

        $msg2 = Db::name('wd_xcx_auction_message')->where('uniacid',$id)->where('class','deposit')->find();
        $data2 = [
            'mid' => $mid2,
            'url' => $url2,
        ];
        if(empty($msg2)){
            $data2['uniacid'] = $id;
            $data2['class'] = 'deposit';
            $res2 = Db::name('wd_xcx_auction_message')->insert($data2);
        }else{
            $res2 = Db::name('wd_xcx_auction_message')->where('uniacid', $id)->where('class', 'appointment')->update($data2);
        }

        $msg3 = Db::name('wd_xcx_auction_message')->where('uniacid',$id)->where('class','depositout')->find();
        $data3 = [
            'mid' => $mid3,
            'url' => $url3,
        ];
        if(empty($msg3)){
            $data3['uniacid'] = $id;
            $data3['class'] = 'depositout';
            $res3 = Db::name('wd_xcx_auction_message')->insert($data3);
        }else{
            $res3 = Db::name('wd_xcx_auction_message')->where('uniacid', $id)->where('class', 'appointment')->update($data3);
        }

        $msg4 = Db::name('wd_xcx_auction_message')->where('uniacid',$id)->where('class','payok')->find();
        $data4 = [
            'mid' => $mid4,
            'url' => $url4,
        ];
        if(empty($msg4)){
            $data4['uniacid'] = $id;
            $data4['class'] = 'payok';
            $res4 = Db::name('wd_xcx_auction_message')->insert($data4);
        }else{
            $res4 = Db::name('wd_xcx_auction_message')->where('uniacid', $id)->where('class', 'appointment')->update($data4);
        }
        if($res1 || $res2 || $res3 || $res4){
          $this->success('模板信息更新成功！',Url('Auction/message').'?appletid='.$id);
        }else{
          $this->error('模板信息更新失败！');
        }
    }

    public function goodstest(){
        if(check_login()){
            if(powerget()){
                $id = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);


                $baby = Db::name('wd_xcx_auction_360baby')->where("id",1)->find();
                $num=$baby['num']+1;
                Db::name('wd_xcx_auction_360baby')->where('id',1)->update(array('uptime'=>date("Y-m-d H:i:s"),'num'=>$num));
                $message= "接触监测服务成功!<br>开始主动进行监测...<br>";
                //操作内容------------------------------------------------------------------------------------------------------
                //监测物品是否结束------------------------------------------------------------------------------------------------------
                $date=date("Y-m-d H:i:s");
                $d=Db::name('wd_xcx_auction_goodslist')->where('stat',1)->where('endtime','<',$date)->select();
                for ($i=0; $i <count($d) ; $i++) {
                $dd=$d[$i];
                  if ($dd['max_user']=='') {
                    Db::name('wd_xcx_auction_goodslist')->where('id',$dd['id'])->update(array('stat'=>3));
                  }else {
                    Db::name('wd_xcx_auction_goodslist')->where('id',$dd['id'])->update(array('stat'=>2));
                    $data=array('user_id'=>$dd['max_user'],
                                'cost'=>$dd['max_cost'],
                                'auction_id'=>$dd['id'],
                                'created_at'=>date("Y-m-d H:i:s"),
                                'update_at'=>date("Y-m-d H:i:s"),
                                'stat'=>0,
                                'uniacid'=>$dd['uniacid'],
                                );
                    Db::name('wd_xcx_auction_order')->insert($data);
                  }
                }
                $message=$message. "拍卖物品进度监测完成...<br>";
                //监测过期的支付
                $d=Db::name('wd_xcx_auction_order')->where('stat',0)->whereTime('created_at','<','-7 day')->select();
                for ($i=0; $i <count($d) ; $i++) {
                    Db::name('wd_xcx_auction_order')->where('id', $d[$i]['id'])->update(array('stat'=>4));
                    Db::name('wd_xcx_auction_goodslist')->where('id', $d[$i]['auction_id'])->update(array('stat'=>3));
                }
                $message=$message. "用户订单监测完成...<br>";
                //结束
                //开始执行退款
                $d=Db::name('wd_xcx_auction_deposit')->alias('a')->join('wd_xcx_auction_goodslist b','a.auction_id = b.id','LEFT')->where('b.stat','>=',2)->where('a.stat',2)->field("a.*,b.max_user,b.stat as state,b.name as gname")->select();
                for ($i=0; $i <count($d) ; $i++) {
                sleep(0.01);
                $dd=$d[$i];
                if ($dd['state']==3) {
                  $info="{"."auction"."}|{".$dd['form_id']."}|{".$dd['uniacid']."}";
                  $sth= $this->getweixinpayinfo($dd['user_id'],$dd['out_trade_no'],$dd['out_refund_no'],$dd['deposit_wx'],$info,$dd['deposit_wx'],$dd['uniacid']);

                  //退款到余额
                  $umoney=Db::name('wd_xcx_user')->where('openid', $dd['user_id'])->where('uniacid', $dd['uniacid'])->find();
                  $umoney=$umoney['money'];
                  Db::name('wd_xcx_user')->where('openid', $dd['user_id'])->where('uniacid', $dd['uniacid'])->update(array('money'=>$dd['deposit']+$umoney));
                  Db::name('wd_xcx_auction_deposit')->where('id', $dd['id'])->update(array('stat'=>1));

                  if ($sth['return_code']=='SUCCESS') {
                    $message=$message.$dd['id']."号退款成功!".'<br>';
                    Db::name('wd_xcx_auction_deposit')->where('id', $dd['id'])->update(array('stat'=>1));
                    $backgoods=Db::name('wd_xcx_auction_deposit')->where('id',$dd['auction_id'])->find();
                    $backdata=array('orderid'=>$dd['out_refund_no'],
                                    'price'=>$dd['deposit_wx'],
                                    'other'=>"您在竞拍".$dd['gname']."时，出局了，现退您保证金，祝您下次竞拍成功!");
                    $message="<br>".$message. $this->sendTplMessage($dd['uniacid'],'depositout', $dd['user_id'], $dd['prepayid'], 'depositout', $backdata);
                  }else {
                    $message=$message.$dd['id']."号退款遇到问题!".'<br>';
                  }
                }else {
                  if ($dd['max_user']!=$dd['user_id']) {
                    $info="{"."auction"."}|{".$dd['form_id']."}|{".$dd['uniacid']."}";
                  $sth= $this->getweixinpayinfo($dd['user_id'],$dd['out_trade_no'],$dd['out_refund_no'],$dd['deposit_wx'],$info,$dd['deposit_wx'],$dd['uniacid']);

                  //退款到余额
                  $umoney=Db::name('wd_xcx_user')->where('uniacid',$id) -> where('openid', $dd['user_id'] )->find();
                  $umoney=$umoney['money'];

                  Db::name('wd_xcx_user')->where('openid', $dd['user_id'])->where('uniacid', $dd['uniacid'])->update(array('money'=>$dd['deposit']+$umoney));
                  Db::name('wd_xcx_auction_deposit')->where('id', $dd['id'])->update(array('stat'=>1));

                  //返回提醒
                  if ($sth['return_code']=='SUCCESS') {
                    Db::name('wd_xcx_auction_deposit')->where('id', $dd['id'])->update(array('stat'=>1));
                    $message=$message.$dd['id']."号退款成功!".'<br>';

                    $backgoods=Db::name('wd_xcx_auction_deposit')->where('id', $dd['auction_id'])->find();
                    $backdata=array('orderid'=>$dd['out_refund_no'],
                                    'price'=>$dd['deposit_wx'],
                                    'other'=>"您在竞拍".$dd['gname']."时，出局了，现退您保证金，祝您下次竞拍成功!");
                    $message="<br>".$message. $this->sendTplMessage($dd['uniacid'],'depositout', $dd['user_id'], $dd['prepayid'], 'depositout', $backdata);

                  }else {
                    $message=$message.$dd['id']."号退款遇到问题!".'<br>';
                  }
                  }
                }

                }


                $message=$message. "退款检测完成...<br>";
                //提醒监测
                $message=$message."进行提醒消息推送<br>";
                $d=Db::name('wd_xcx_auction_remind')->where('stat',0)->select();
                for ($i=0; $i <count($d) ; $i++) {
                $dd=$d[$i];
                $t=Db::name('wd_xcx_auction_goodslist')->where('stat',1)->where('id', $dd['auction_id'])->find();
                if ($t!=false) {
                  $stime=$t['starttime'];
                  $stime=strtotime($stime);
                  $n=strtotime(date("Y-m-d H:i:s"));
                  $n=$stime-$n;
                  //$message=$message.$n."<br>";
                  if ($n<7200) {
                    //开始发送预约提醒
                    $message=$message."执行一条提醒推送...<br>";
                    $data=array('gname'=>$t['name'],'msg'=>"您预约的拍卖物品".$t['name']."即将开始拍卖！开拍时间：".$t['starttime']."不要错过机会哦！");
                    $tt= $this->sendTplMessage($dd['uniacid'],'appointment', $dd['user_id'], $dd['formid'], 'appointment', $data);
                    Db::name('wd_xcx_auction_remind')->where('id', $dd['id'])->update(array('stat'=>1));
                    $message=$message.$tt."<br>";
                  }
                }
                }
                $message=$message."提醒消息执行完毕...<br>";
                $message=$message. "完成所有监测!";
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
            return $this->fetch('goodstest');
        }else{
            $this->redirect('Login/index');
        }        
    }
    //模板消息处理
    public function sendTplMessage($uniacid,$flag, $openid, $formId, $types, $data){ //$fmsg, $orderid, $fprice){
        $id = input("appletid");
        $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
        if(!$res){
            $this->error("找不到对应的小程序！");
        }
        $this->assign('applet',$res);

        $appid = $res['appID'];                 //小程序的id
        $appsecret = $res['appSecret']; 
        if($applet){
          $mid = Db::name('wd_xcx_auction_message')->where('class', $flag) -> where('uniacid', $uniacid)->find();
          if($mid && $mid['mid'] != ""){
            $mids = $mid['mid'];
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$appsecret;
            $a_token = $this->_requestGetcurl($url);
            if($a_token){
              $url_m = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=".$a_token['access_token'];
              $ftime = date('Y-m-d H:i:s',time());
              $furl = $mid['url'];
              if($types == 'auction_buy'){
                $post_info = '{
                          "touser": "'.$openid.'",
                          "template_id": "'.$mids.'",
                          "page": "'.$furl.'",
                          "form_id": "'.$formId.'",
                          "data": {
                              "keyword1": {
                                  "value": "'.$data['auctionname'].'",
                                  "color": "#173177"
                              },
                              "keyword2": {
                                  "value": "'.$data['price'].'元",
                                  "color": "#173177"
                              },
                              "keyword3": {
                                  "value": "'.$data['time'].'",
                                  "color": "#173177"
                              } ,
                              "keyword4": {
                                  "value": "'.$data['other'].'",
                                  "color": "#173177"
                              }
                          },
                          "emphasis_keyword": ""
                        }';
                    }
                    elseif ($types=='appointment') {
                      $post_info = '{
                                "touser": "'.$openid.'",
                                "template_id": "'.$mids.'",
                                "page": "'.$furl.'",
                                "form_id": "'.$formId.'",
                                "data": {
                                    "keyword1": {
                                        "value": "'.$data['gname'].'",
                                        "color": "#173177"
                                    },
                                    "keyword2": {
                                        "value": "'.$data['msg'].'",
                                        "color": "#173177"
                                    }
                                },
                                "emphasis_keyword": ""
                              }';

                    }
                    elseif ($types=='depositout') {
                      $post_info = '{
                                "touser": "'.$openid.'",
                                "template_id": "'.$mids.'",
                                "page": "'.$furl.'",
                                "form_id": "'.$formId.'",
                                "data": {
                                    "keyword1": {
                                        "value": "'.$data['orderid'].'",
                                        "color": "#173177"
                                    },
                                    "keyword2": {
                                        "value": "'.$data['price'].'元",
                                        "color": "#173177"
                                    },
                                    "keyword3": {
                                        "value": "'.$data['other'].'",
                                        "color": "#173177"
                                    }
                                },
                                "emphasis_keyword": ""
                              }';

                    }
                      $gg = $this->ggpost($url_m,$post_info);
                      //return "步骤";
                      file_put_contents(__DIR__."/debug2.txt",$gg);
                      return $gg;
            }
          }
        }
    }

    //模板消息后续
    public function ggpost($url, $data, $ssl=true) {
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
                    // 发出请求
                    $out = curl_exec($curl);

                    if (false === $out) {
                            echo '<br>', curl_error($curl), '<br>';
                            return "错误汇报:".curl_error($curl);
                    }
                    curl_close($curl);
                    return $out;
    }

    public function _requestGetcurl($url){
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
        curl_setopt($curl, CURLOPT_HTTPHEADER  , $header);
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
        $forms = json_decode($forms,TRUE);
        return $forms;
    }
    public function getweixinpayinfo($openid, $out_trade_no,$out_refund_no, $payprice, $info,$refund_fee,$uniacid){


        $app = Db::name('wd_xcx_applet')->where("id",$uniacid)->find();
        $mchid = $app['mchid'];   //商户号
        $apiKey = $app['signkey'];    //商户的秘钥
        $appid = $app['appID'];                 //小程序的id
        $appkey = $app['appSecret'];            //小程序的秘钥
        // 更新信息


        $SSLCERT_PATH = ROOT_PATH.'public/Cert/'.$uniacid.'/apiclient_cert.pem';//证书路径
        $SSLKEY_PATH =  ROOT_PATH.'public/Cert/'.$uniacid.'/apiclient_key.pem';//证书路径
        $opUserId = $mchid;//商户号
        include "WinXinRefund.php";

        $total_fee = $payprice * 100;
        $refund_fee=$refund_fee *100;//退款金额
        $weixinpay = new WinXinRefund($openid,$out_trade_no,$total_fee,$out_refund_no,$refund_fee,$SSLCERT_PATH,$SSLKEY_PATH,$opUserId,$appid,$apiKey);

        $return = $weixinpay->refund();
        if(!$return){
            $this->error("退款失败 请检查证书是否正常");
        }
    }
}