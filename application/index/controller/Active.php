<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;
class Active extends Base
{
    public function applyinfo(){
        $id = input("applyid");
        $str = "";
        $info = Db::name('wd_xcx_active_apply')->where('id', $id)->find();
        $str .= "<div class='formlist'>姓名：".$info['username']."</div>";
        $str .= "<div class='formlist'>手机号：".$info['tel']."</div>";
        $forminfo = $info['forminfo'];
        if($forminfo){
            $str .= "<div style='color:blue;margin-top:20px'>表单信息：</div>";
            $forminfo = unserialize($forminfo);
            foreach ($forminfo as $key => &$a) {
                $str .="<div class='formlist'>";
                if ($a['type']== 3){
                    $str .= $a['name'].'：';
                    if(is_array($a['val'])){
                        foreach ($a['val'] as  $a2){
                            $str.=$a2.',';
                        }
                    }
                }
                if ($a['type']== 5){
                    $str .= $a['name'].'：';
                    if(is_array($a['val'])){
                        foreach ($a['z_val'] as  $a3){
                            $str.='<a href="'.$a3.'" target="_blank"><img src="'.$a3.'" alt="" style="width:60px;margin-right:5px;"></a>';
                        }
                    }
                }
                if ($a['type']!= 3 && $a['type']!= 5){
                    $str .= $a['name'].'：'.$a['val'];
                }
                $str .="</div>";
            }
        }
        return $str;
    }
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
                $catelist = Db::name('wd_xcx_active_cate')->where("uniacid",$id)->order("id desc")->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                if($catelist->toArray()){
                    $list = $catelist->toArray()['data'];
                    foreach ($list as $key => $value) {
                        if($value['thumb']){
                            $list[$key]['thumb'] = remote($id, $value['thumb'], 1);
                        }
                    }
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
                    $cateinfo = Db::name('wd_xcx_active_cate')->where("uniacid",$id)->where("id",$cateid)->find();
                    $cateinfo['thumb'] = remote($id, $cateinfo['thumb'], 1);
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
        $data = [];
        $data['num'] = input("num");
        $data['uniacid'] = input("appletid");
        $data['flag'] = input("flag");
        $data['name'] = input("name");
        //缩略图
        $thumb = input("commonuploadpic1");
        if($thumb){
            $data['thumb'] = remote($data['uniacid'],$thumb,2);
        }
        $cateid = input('cateid');
        if(!empty($cateid)){
            $res = Db::name("wd_xcx_active_cate")->where('id',$cateid)->update($data);
        }else{
            $res = Db::name("wd_xcx_active_cate")->insert($data);
        }
        if($res){
          $this->success('分类信息更新成功！',Url('Active/catelist').'?appletid='.$data['uniacid']);
        }else{
          $this->error('分类信息更新失败，没有修改项！！');
          exit;
        }
    }
    public function catedel(){
        $data['id'] = input("cateid");

        $is = Db::name('wd_xcx_active')->where('cateid', input('cateid'))->count();
        if($is){
            $this->success('该分类下存在活动，删除失败');
        }

        $res = Db::name('wd_xcx_active_cate')->where($data)->delete();
        if($res){
            $this->success('删除成功');
        }else{
            $this->success('删除失败');
        }
    }

    public function lists(){
        if(check_login()){
            if(powerget()){
                $id = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);

                $lists = array();
                $lists = Db::name('wd_xcx_active')->alias('a')->join("wd_xcx_active_cate b", 'a.cateid = b.id')->where("a.uniacid",$id)->field('a.*, b.name as catename')->order("a.num desc, a.id desc")->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
                if($lists->toArray()){
                    $list = $lists->toArray()['data'];
                    foreach ($list as $key => &$value) {
                        $value['thumb'] = remote($id, $value['thumb'], 2);
                    }
                }
                $this->assign('list',$list);
                $this->assign('lists',$lists);
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
            return $this->fetch('lists');
        }else{
            $this->redirect('Login/index');
        }
    }

    public function listsadd(){
        if(check_login()){
            if(powerget()){
                $id = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $activeinfo = [];
                $allimg = '';
                $aid = input('aid') ? input('aid') : 0;
                if(!empty($aid)){
                    $activeinfo = Db::name('wd_xcx_active')->where("uniacid",$id)->where("id",$aid)->find();
                    $activeinfo['thumb'] = remote($id, $activeinfo['thumb'], 1);
                    $allimg = Db::name('wd_xcx_products_url')->where("randid",$activeinfo['onlyid'])->select();
                    foreach ($allimg as $key => &$value) {
                        $value['url'] = remote($id,$value['url'],1);
                    }
                }

                $catelist = Db::name('wd_xcx_active_cate')->where("uniacid",$id)->field("id,name")->order('id desc')->select();
                $this->assign('catelist',$catelist);

                $forms = Db::name('wd_xcx_formlist')->where("uniacid",$id)->field("id,formtitle")->order('id desc')->select();
                $this->assign('forms',$forms);
                
                $this->assign('allimg',$allimg);
                $this->assign('aid',$aid);
                $this->assign('activeinfo',$activeinfo);
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
            return $this->fetch('listsadd');
        }else{
            $this->redirect('Login/index');
        }
    }
    public function listsdel(){
        $data['id'] = input("aid");
        $res = Db::name('wd_xcx_active')->where($data)->delete();
        if($res){
            $this->success('删除成功');
        }else{
            $this->success('删除失败');
        }
    }
    public function listssave(){
        $data = [];
        $uniacid = input("appletid");
        $data['num'] = input("num");
        $data['flag'] = input("flag");
        $data['allperson'] = input("allperson");
        $data['allapply'] = input("allapply");
        $data['cateid'] = input("cateid");
        $data['name'] = input("name");
        //缩略图
        $thumb = input("commonuploadpic1");
        if($thumb){
            $data['thumb'] = remote($uniacid,$thumb,2);
        }
        $onlyid = input('onlyid');
        if($onlyid){
            $imgsrcs = input("imgsrcs/a");
            if($imgsrcs){
                $imgarr = array();
                foreach ($imgsrcs as $k => $v) {
                    $imgarr['randid'] = $onlyid;
                    $imgarr['appletid'] = $uniacid;
                    $imgarr['url'] = remote($uniacid,$v,2);
                    $imgarr['dateline'] = time();
                    $is = Db::name('wd_xcx_products_url')->insert($imgarr);
                }
            }else{
                $is = 1;
            }
            $data['onlyid'] = $onlyid;
        }
        // 处理幻灯片
        if($onlyid){
            $silde = Db::name('wd_xcx_products_url')->where("randid",$onlyid)->select();
            $arrsilde = array();
            if($silde){
                foreach ($silde as $rec) {
                    $arrsilde[]=$rec['url'];
                }
                $data['pics'] = serialize($arrsilde);
            }else{
                $data['pics'] = "";
            }
        }
        $data['starttime'] = strtotime(input("starttime"));
        $data['endtime'] = strtotime(input("endtime"));
        $data['activetime'] = input("activetime");
        $data['address'] = input("address");
        $data['tel'] = input("tel");
        $data['formset'] = input("formset");
        $data['shenhe'] = input("shenhe");
        $data['contents'] = input("contents");

        $aid = input('aid');
        if($aid > 0){
            $res = Db::name('wd_xcx_active')->where('id', $aid)->update($data);
        }else{
            $data['uniacid'] = $uniacid;
            $res = Db::name('wd_xcx_active')->insert($data);
        }
        if($res){
          $this->success('活动信息添加/更新成功！',Url('Active/lists').'?appletid='.$uniacid);
        }else{
          $this->error('活动信息添加/更新失败，没有修改项！！');
          exit;
        }
    }
    public function applylist(){
        if(check_login()){
            if(powerget()){
                $id = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$id)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);

                $where = [];
                $search_flag = intval(input('search_flag'));
                if($search_flag > 0){
                    $where['flag'] = $search_flag;
                }

                $starttime = input('starttime') ? intval(strtotime(input('starttime'))) : 0;
                if($starttime > 0){
                    $where['createtime'] = ['gt', $starttime];
                }

                $endtime = input('endtime') ? intval(strtotime(input('endtime'))) : 0;
                if($endtime > 0){
                    $where['createtime'] = ['lt', $starttime];
                }

                $applylist = [];
                $aid = input('aid');
                if(!empty($aid)){
                    $activename = Db::name('wd_xcx_active')->where('id', $aid)->value('name');
                    $applylist = Db::name('wd_xcx_active_apply')->where("uniacid",$id)->where("aid",$aid)->where($where)->order("id desc")->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"),'aid'=>$aid, '$search_flag' => $search_flag, 'starttime' => $starttime,'endtime' => $endtime)]);
                    
                    $list = $applylist->toArray()['data'];
                    foreach ($list as $key => &$value) {
                        $userinfo = getNameAvatar($value['suid'], $id);
                        $value['nickname'] = rawurldecode($userinfo['nickname']);
                        $value['avatar'] = $userinfo['avatar'];
                        $value['createtime'] = date("Y-m-d H:i:s", $value['createtime']);
                        $value['hxtime'] = $value['hxtime'] > 0 ? date("Y-m-d H:i:s", $value['hxtime']) : '';
                    }
                }
                $this->assign('aid',$aid);
                $this->assign('activename',$activename);
                $this->assign('search_flag',$search_flag);
                $this->assign('starttime',$starttime ? date("Y-m-d H:i:s", $starttime) : 0);
                $this->assign('endtime',$endtime ? date("Y-m-d H:i:s", $endtime) : 0);
                $this->assign('list',$list);
                $this->assign('applylist',$applylist);
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
            return $this->fetch('applylist');
        }else{
            $this->redirect('Login/index');
        }
    }
    //审核
    public function shenhe(){
        $uniacid = input('appletid');
        $applyid = input('applyid');
        $flag = input('flag');
        if($flag == 3){
            $data['hxtime'] = time();
        }
        $data['flag'] = $flag;
        $res = Db::name('wd_xcx_active_apply')->where('id', $applyid)->update($data);
        if($res){
            //1待审核 2待参加 3已完成 4审核未通过
            
            $this->success('审核操作成功');
        }else{
            $this->error('审核操作失败，请重新操作');
        }

    }

    //批量删除操作
    public function delall(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $array1=input('cateids');
                $arr=explode(',',$array1);
                $res = Db::name('wd_xcx_active_cate')->where("uniacid",$appletid)->where('id',"in",$arr)->delete();
                if($res){
                    $this->success('删除成功');
                }else{
                    $this->error('删除失败');
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
            return $this->fetch('catelist');
        }else{
            $this->redirect('Login/index');
        }
    }

    //批量删除操作
    public function delallactive(){
        if(check_login()){
            if(powerget()){
                $appletid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$appletid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);
                $array1=input('aids');
                $arr=explode(',',$array1);
                $res = Db::name('wd_xcx_active')->where("uniacid",$appletid)->where('id',"in",$arr)->delete();
                if($res){
                    $this->success('删除成功');
                }else{
                    $this->error('删除失败');
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
            return $this->fetch('lists');
        }else{
            $this->redirect('Login/index');
        }
    }

    public function excel(){
        $uniacid = input("appletid");
        $aid = input("aid");
        $activename = Db::name('wd_xcx_active')->where('id', $aid)->value('name');

        $where = [];
        $search_flag = intval(input('search_flag'));
        if($search_flag > 0){
            $where['flag'] = $search_flag;
        }

        $starttime = input('starttime') ? intval(strtotime(input('starttime'))) : 0;
        if($starttime > 0){
            $where['createtime'] = ['gt', $starttime];
        }

        $endtime = input('endtime') ? intval(strtotime(input('endtime'))) : 0;
        if($endtime > 0){
            $where['createtime'] = ['lt', $starttime];
        }

        $applylist = Db::name('wd_xcx_active_apply')->where("uniacid",$uniacid)->where('aid', $aid)->where($where)->order('id desc')->select();

            require_once ROOT_PATH.'public/plugin/PHPExcel/PHPExcel.php';
            $objPHPExcel = new \PHPExcel();

            /*以下是一些设置*/
            $objPHPExcel->getProperties()->setCreator("活动申请记录")
                ->setLastModifiedBy("活动申请记录")
                ->setTitle("活动申请记录")
                ->setSubject("活动申请记录")
                ->setDescription("活动申请记录")
                ->setKeywords("活动申请记录")
                ->setCategory("活动申请记录");
            $objPHPExcel->getActiveSheet()->setCellValue('A1', '活动名称');
            $objPHPExcel->getActiveSheet()->setCellValue('B1', '报名人昵称');
            $objPHPExcel->getActiveSheet()->setCellValue('C1', '报名人头像');
            $objPHPExcel->getActiveSheet()->setCellValue('D1', '联系人姓名');
            $objPHPExcel->getActiveSheet()->setCellValue('E1', '联系人电话');
            $objPHPExcel->getActiveSheet()->setCellValue('F1', '报名时间');
            $objPHPExcel->getActiveSheet()->setCellValue('G1', '核销时间');
            $objPHPExcel->getActiveSheet()->setCellValue('H1', '状态');
            $objPHPExcel->getActiveSheet()->setCellValue('I1', '万能表单信息');
            foreach ($applylist as $k => $v) {
                $num=$k+2;
                $userinfo = getNameAvatar($v['suid'], $uniacid, 1);
                $v['nickname'] = $userinfo['nickname'];
                $v['avatar'] = $userinfo['avatar'];
                $v['activename'] = $activename;
                $v['createtime'] = date("Y-m-d H:i:s", $v['createtime']);
                $v['hxtime'] = $v['hxtime'] > 0 ? date("Y-m-d H:i:s", $v['hxtime']) : '';
                if($v['flag'] == 1){
                    $v['flag1'] = '待审核';
                }else if($v['flag'] == 2){
                    $v['flag1'] = '待参加';
                }else if($v['flag'] == 3){
                    $v['flag1'] = '已完成';
                }else if($v['flag'] == 4){
                    $v['flag1'] = '审核不通过';
                }


                $forminfo = '';
                $form_arr = $v['forminfo'] ? unserialize($v['forminfo']) : '';
                if($form_arr){
                    foreach ($form_arr as $kk => $vv) {
                        if($vv['type']== 3){
                            $type3_info = "";
                            foreach ($vv['val'] as $key => $value) {
                                $type3_info = $type3_info.$value.",";
                            }

                            $forminfo = $forminfo.$vv['name'].":".$type3_info.";\r\n";
                        }
                        if($vv['type']== 5){
                            $type5_info = "";
                            foreach ($vv['z_val'] as $key => $value) {
                                $type5_info = $type5_info.remote($uniacid, $value, 1).",";
                            }

                            $forminfo = $forminfo.$vv['name'].":".$type5_info.";\r\n";
                        }
                        if($vv['type'] != 5 && $vv['type'] != 3){
                            $forminfo = $forminfo.$vv['name']."：".$vv['val'].";\r\n";
                        }
                    }
                }
                
                $objPHPExcel->getActiveSheet()->getStyle("I".$num)->getAlignment()->setWrapText(TRUE); 
                $objPHPExcel->setActiveSheetIndex(0)
                            ->setCellValueExplicit('A'.$num, $v['activename'],'s')
                            ->setCellValueExplicit('B'.$num, $v['nickname'],'s')
                            ->setCellValueExplicit('C'.$num, $v['avatar'],'s') 
                            ->setCellValueExplicit('D'.$num, $v['username'],'s')
                            ->setCellValueExplicit('E'.$num, $v['tel'],'s')
                            ->setCellValueExplicit('F'.$num, $v['createtime'], 's')
                            ->setCellValueExplicit('G'.$num, $v['hxtime'], 's')
                            ->setCellValueExplicit('H'.$num, $v['flag1'], 's')
                            ->setCellValueExplicit('I'.$num, $forminfo);
                  
            }

            // var_dump($d1);exit;

            $objPHPExcel->getActiveSheet()->setTitle('导出活动申请记录表');
            $objPHPExcel->setActiveSheetIndex(0);
            $excelname="活动申请记录表";
            ob_end_clean();//清除缓冲区,避免乱码
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="'.$excelname.'.xls"');
            header('Cache-Control: max-age=0');
            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output');
            exit;
    }
}
