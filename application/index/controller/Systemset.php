<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;
class Systemset extends Controller
{
 
    public function index(){
       	if(check_login()){
    		$sbase = "";
            $sbase =  Db::name('wd_xcx_system_base')->find();  
            if($sbase['banner']){
                $sbase['banner'] = unserialize($sbase['banner']);
            }else{
                $sbase['banner'] = "";
            }
            $this->assign('sbase',$sbase);
            return $this->fetch('index');
        }else{
            $this->redirect('Login/index');
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
    public function add(){
        $newsid = input("newsid");
        $name = input("name");
        $logo = $this->onepic_uploade("logo");
        $banner['banner1'] = $this->onepic_uploade("banner1");
        if($banner['banner1'] == null){
            $banner['banner1'] = input("tbanner1");
        }
        $banner['banner2'] = $this->onepic_uploade("banner2");
        if($banner['banner2'] == null){
            $banner['banner2'] = input("tbanner2");
        }
        $banner['banner3'] = $this->onepic_uploade("banner3");
        if($banner['banner3'] == null){
            $banner['banner3'] = input("tbanner3");
        }
        $banner['banner1_t1'] = input("banner1_t1");
        $banner['banner2_t1'] = input("banner2_t1");
        $banner['banner3_t1'] = input("banner3_t1");
        $banner['banner1_t2'] = input("banner1_t2");
        $banner['banner2_t2'] = input("banner2_t2");
        $banner['banner3_t2'] = input("banner3_t2");
        $top_banner = $this->onepic_uploade("top_banner");
        $foot_logo = $this->onepic_uploade("foot_logo");
        $ptel = input("ptel");
        $tel = input("tel");
        $ftime = input("ftime");
        $address = input("address");
        $qq = input("qq");
        $email = input("email");
        $beianxx = input("beianxx");
        $erweima = $this->onepic_uploade("erweima");
        $data['name'] = $name;
        if($logo){
            $data['logo'] = $logo;
        }
        
        if($banner){
            $data['banner'] = serialize($banner);
        }
        if($top_banner){
            $data['top_banner'] = $top_banner;
        }
        if($foot_logo){
            $data['foot_logo'] = $foot_logo;
        }
        
        $data['beianxx'] = $beianxx;
        
        $data['ptel'] = $ptel;
        $data['tel'] = $tel;
        $data['ftime'] = $ftime;
        $data['address'] = $address;
        $data['qq'] = $qq;
        $data['email'] = $email;
        
        if($erweima){
            $data['erweima'] = $erweima;
        }
        $counts =  Db::name('wd_xcx_system_base')->count();  
        if($counts==0){
            $res = Db::name('wd_xcx_system_base')->insert($data);
            
        }else{
            $res = Db::name('wd_xcx_system_base')->where("id",1)->update($data);
        }
        if($res){
          $this->success('系统信息更新成功！','Systemset/index');
        }else{
          $this->error('系统信息更新失败，没有更新项目！');
          exit;
        }
    }
    public function getimg(){
        $id = input('id');     
        $allimg = Db::name('wd_xcx_products_url')->where("typeid",$id)->select();
        if($allimg){
            return $allimg;
        }
        
    }
    public function news(){
        if(check_login()){
            $news = "";
            $news = Db::name('wd_xcx_system_news')->order('num desc')->paginate(10,false,[ 'query' => array('appletid'=>input("appletid"))]);
            $count = Db::name('wd_xcx_system_news')->order('num desc')->count();
            $newnews = $news->toArray();
            foreach ($newnews['data'] as $key => &$res) {
                $lanmu = Db::name('wd_xcx_system_cate')->where("id",$res['cate'])->find();
                $res['lanmu'] = $lanmu['name'];
            }
            // var_dump($newnews['data']);
            // die();
            $this->assign('newnews',$newnews['data']);
            $this->assign('news',$news);
            $this->assign('counts',$count);
            return $this->fetch('news');
        }else{
            $this->redirect('Login/index');
        }
    }
    public function addnews(){
        if(check_login()){
            $id = input("id");
            $cate = Db::name('wd_xcx_system_cate')->select();
            $this->assign('cate',$cate);
            $news = "";
            if($id){
                $news = Db::name('wd_xcx_system_news')->where("id",$id)->find();
            }
            $this->assign('news',$news);
            return $this->fetch('addnews');
        }else{
            $this->redirect('Login/index');
        }
    }
    public function savenew(){
        if(input("num")){
            $data['num'] = input("num");
        }else{
            $data['num'] = 50;
        }
        $data['cate'] = input("cate");
        $data['title'] = input("title");
        $thumb = $this->onepic_uploade("thumb");
        if($thumb){
            $data['thumb'] = $thumb;
        }
        
        $data['desc'] = input("desc");
        $data['creattime'] = time();
        $data['text'] = input("content");
        $id = input("id");
        if($id){
            $res = Db::name('wd_xcx_system_news')->where("id",$id)->update($data);
        }else{
             $res = Db::name('wd_xcx_system_news')->insert($data);
        }
        if($res){
          $this->success('消息更新成功！','Systemset/news');
        }else{
          $this->error('消息更新失败，没有更新项目！');
          exit;
        }
    }
    // 删除操作
    public function del(){
        $data['id'] = input("id");
        $res = Db::name('wd_xcx_system_news')->where($data)->delete();
        if($res){
            $this->success('删除成功');
        }else{
            $this->success('删除失败');
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
    //富文本图片上传
    public function imgupload(){
        $typeid = input("typeid");
        $files = request()->file('');    
        foreach($files as $file){        
            // 移动到框架应用根目录/public/upimages/ 目录下        
            $info = $file->validate(['ext'=>'jpg,png,gif,jpeg'])->move(ROOT_PATH . 'public' . DS . 'upimages');
           if($info){
                $data['url'] =  ROOT_HOST."/upimages/".date("Ymd",time())."/".$info->getFilename();
                $data['typeid'] = $typeid;
                $data['dateline'] = time();
                $res = Db::name('wd_xcx_products_url')->insert($data);
            }else{
                // 上传失败获取错误信息
                return $this->error($file->getError()) ;
            }    
        }
    }
}