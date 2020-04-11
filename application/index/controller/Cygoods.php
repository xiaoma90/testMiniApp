<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;


class Cygoods extends Base
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

                $cid = input("cid") ? input("cid") : 0;
                $title = input("key");

                $where = [];
                if($cid > 0){
                    $where['cid'] = $cid;
                }

                if($title){
                    $where['title'] = ['like',"%".$title."%"];
                }




                $list = Db::name('wd_xcx_food')->where($where)->where("uniacid",$appletid)->order('num desc,id desc')->paginate(10, false, ['query' => ['appletid' => input('appletid')]]);
                $listV = $list->all();
                foreach ($listV as $key => &$value) {
                    $value['catename'] = Db::name('wd_xcx_food_cate')->where('id',$value['cid'])->value("title");

                    if($value['thumb']){
                        $value['thumb'] = remote($appletid,$value['thumb'],1);
                    }else{
                        $value['thumb'] = remote($appletid,"/image/noimage_1.png",1);
                    }

                }
                $this->assign('listV',$listV);
                $this->assign('list',$list);

                $cate = Db::name('wd_xcx_food_cate')->where('uniacid',$appletid)->select();
                $this->assign('key',$title);
                $this->assign('cid',$cid);
                $this->assign('cate',$cate);
                
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



    public function add(){

        if(check_login()){


            if(powerget()){

                $uniacid = input("appletid");
                $res = Db::name('wd_xcx_applet')->where("id",$uniacid)->find();
                if(!$res){
                    $this->error("找不到对应的小程序！");
                }
                $this->assign('applet',$res);

                $goodsid = input("goodsid");

                if($goodsid){
                    //有栏目号时，先判断该栏目是不是属于该小程序！
                    $lanmu = Db::name('wd_xcx_food')->where("id",$goodsid)->find();
                    if($lanmu['uniacid'] == $uniacid){
                        $id = $lanmu['id'];
                        $goodsinfo = $lanmu;
                        if($goodsinfo['thumb']){
                            $goodsinfo['thumb'] = remote($uniacid,$goodsinfo['thumb'],1);
                        }
                        if($goodsinfo['descimg']){
                            $goodsinfo['descimg'] = remote($uniacid,$goodsinfo['descimg'],1);
                        }

                        if($goodsinfo['types']==2){
                            $proarr = Db::name('wd_xcx_food_type_value')->where('pid',$goodsid)->order('id asc')->select();
                            //构建规格组
                            $counttypes=0;
                            $typesarr=array();
                            $typesjson = [];
                            if($proarr){
                                $types = $proarr[0]['comment'];
                                // 构建规格组json
                                $typesarr = explode(",", $types);
                                $counttypes = count($typesarr);

                                foreach ($typesarr as $key => &$rec) {
                                    $str = "type".($key+1);
                                    $ziji = Db::name('wd_xcx_food_type_value')->where('pid',$goodsid)->order("id asc")->field($str)->select();
                                    $xarr = array();
                                    foreach ($ziji as $key => $res) {
                                        array_push($xarr, $res[$str]);
                                    }
                                    $typesjson[$rec] = $xarr;
                                }
                            }
                            // 构建对应的数值
                            $datajson = [];
                            foreach ($proarr as $key => &$rec) {
                                $strs = $rec['type1'].$rec['type2'].$rec['type3'];
                                $strv = $rec['kc'].",".$rec['price'].",".$rec['salenum'].",".$rec['vsalenum'];
                                $datajson[$strs]=$strv;
                            }
                            foreach ($typesjson as $key => &$value) {
                                $value = array_unique($value);
                            }
                        }
                        if($goodsinfo['types']==1){
                            $proarr = Db::name('wd_xcx_food_type_value')->where('pid',$goodsid)->order("id asc")->find();
                            $goodsinfo['kc'] = 1; 
                            $counttypes = 0;
                            $typesarr = [];
                            $typesjson = [];
                            $datajson = [];
                        }

                    }else{
                        $usergroup = Session::get('usergroup');
                        if($usergroup==1){
                            $this->error("找不到该产品，或者该产品不属于本小程序");
                        }
                        if($usergroup==2){
                            $this->error("找不到该产品，或者该产品不属于本小程序");
                        }
                    }
                }else{
                    $goodsid=0;
                    $goodsinfo = "";
                    $id = 0;
                    $counttypes=0;
                    $datajson = [];
                    $typesjson = [];
                    $typesarr = [];
                }
                $cate = Db::name('wd_xcx_food_cate')->where('uniacid',$uniacid)->select();
                $this->assign('cate',$cate);
                $this->assign('goodsid',$goodsid);
                $this->assign('goodsinfo',$goodsinfo);
                $this->assign('id',$id);
                $this->assign('counttypes',$counttypes);
                $this->assign('datajson',$datajson);
                $this->assign('typesjson',$typesjson);
                $this->assign('typesarr',$typesarr);
                
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
            return $this->fetch('add');
        }else{
            $this->redirect('Login/index');
        }
        
    }


    public function save(){
        $id = intval(input("goodsid"));
        //小程序ID
        $data['uniacid'] = input("appletid");
        $data['cid'] = $_POST['cid'];
        $data['num'] = $_POST['num'];
        $data['title'] = $_POST['title'];
        // $data['counts'] = $_POST['counts'];
        $data['price'] = $_POST['price'];
        $data['desccon'] = $_POST['desccon'];
        $data['product_txt'] = input('product_txt');
        $data['unit'] = $_POST['unit'];
        //缩略图
        $thumb = input("commonuploadpic1");
        if($thumb){
            $data['thumb'] = remote($data['uniacid'],$thumb,2);
        }
        // dump($thumb);die;
        //缩略图
        $descimg = input("commonuploadpic2");
        if($descimg){
            $data['descimg'] = remote($data['uniacid'],$descimg,2);
        }

        $guig = input("ischeck");
        $data["types"] = intval($guig);
        if($id!=0){
            Db::name('wd_xcx_food_type_value')->where('pid',$id)->delete();
            Db::name('wd_xcx_food')->where("id",$id)->update($data);
        }else{
            $id = Db::name('wd_xcx_food')->insertGetId($data);
            

        }
        if($guig == 2){
                $ggarr = stripslashes(html_entity_decode(input('biaogedata')));
                $proarr = json_decode($ggarr,true);

                // 规格组长度
                $typelen = input('typelen');
                // 规格数组
                $types = input('typesarr');

                $typezz = $types;
                $typesarr = explode(",", $types);
                // 子商品
                // $ggarr = input('biaogedata');
    
                $count = 0;
                // dump(input());die;
                        
                if($proarr){
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
                            "pid" => $id,
                            "type1" => $type1,
                            "type2" => $type2,
                            "type3" => $type3,
                            "kc" => $rec['库存'],
                            "price" => $rec['价格'],
                            "salenum" => $rec['已售数量'],
                            "comment" => $typezz,
                            "vsalenum"=>$rec['虚拟销量']
                        );
                        $res = Db::name('wd_xcx_food_type_value')->insert($datas);
        
                        if($res){
                            $count++;
                            if($count == count($proarr)){
                                $minprice=Db::name('wd_xcx_food_type_value')->where('pid',$id)->field('price') ->select();
                                $min = $minprice[0]['price']*1;
                                foreach ($minprice as $key => $value) {
                                    if($value['price']*1 < $min){
                                        $min = $value['price'];
                                    }
                                }
                                Db::name("wd_xcx_food")->where("id",$id)->update(array("price"=>$min));
                                $this->success('餐饮商品更新成功',Url('cygoods/index').'?appletid='.$data['uniacid']);
                            }
                        }
                    }
                }else{
                    $this->success('餐饮商品更新成功',Url('cygoods/index').'?appletid='.$data['uniacid']);
                }
        }else{
            $datas = array(
                "pid" => $id,
                "type1" => "默认",
                "type2" => "",
                "type3" => "",
                "kc" => 1,
                "price" =>1,
                "salenum" => 0,
                "comment" => "规格",
                "vsalenum"=>0
            );
            Db::name("wd_xcx_food")->where("id",$id)->update(array("price"=>1));
            $res = Db::name('wd_xcx_food_type_value')->insert($datas);
            if($res){
                $this->success('餐饮商品更新成功',Url('cygoods/index').'?appletid='.$data['uniacid']);
            }
        }

        
        // if($res){
        //   $this->success('产品信息更新成功！',Url('Cygoods/index').'?appletid='.$data['uniacid']);
        // }else{
        //   $this->error('产品信息更新失败，没有修改项！');
        //   exit;
        // }
    }

    // 删除操作
    public function del(){
        $data['id'] = input("cateid");
        $res = Db::name('wd_xcx_food')->where($data)->delete();
        if($res){
            $this->success('删除成功');
        }else{
            $this->success('删除失败');
        }
    }

    //规格图片上传
    public function imgupload(){
        $uniacid = input("uniacid");
        $remote = Db::name("wd_xcx_base")->where("uniacid",$uniacid)->field("remote")->find()['remote'];
        if(!$remote){
            $remote = 1;
        }
        $groupid = 0;
        if($remote == 1){
            $files = request()->file('');  
            foreach($files as $file){        
                // 移动到框架应用根目录/public/upimages/ 目录下        
                $info = $file->validate(['ext'=>'jpg,png,gif,jpeg'])->move(ROOT_PATH . 'public' . DS . 'upimages');
                if($info){
                    $url =  "/upimages/".date("Ymd",time())."/".$info->getFilename();
                    $arr = array("url"=>$url);
                    return json_encode($arr);
                }else{
                    // 上传失败获取错误信息
                    return $this->error($file->getError()) ;
                }    
            }
        }else if($remote == 2){
            $qiniu_info = Db::name("wd_xcx_remote")->where("type",2)->where("uniacid",$uniacid)->find();
            $file = $_FILES['uploadfile']['tmp_name'];
            $is_img = getimagesize($file);
            if($is_img){
            }
            $oringal_name = $_FILES['uploadfile']['name'];
           
            $pathinfo = pathinfo($oringal_name);
            // var_dump($pathinfo);exit;
            // 要上传图片的本地路径
            $ext = $pathinfo['extension'];
            $key = 'upimages/'.md5(uniqid(microtime(true),true)).'.'.$ext;
            
            // 需要填写你的 Access Key 和 Secret Key
            $accessKey = $qiniu_info['ak'];
            $secretKey = $qiniu_info['sk'];
            // 构建鉴权对象
            $auth = new Auth($accessKey, $secretKey);
            // 要上传的空间
            $bucket = $qiniu_info['bucket'];
            $domain = $qiniu_info['domain'];
            $token = $auth->uploadToken($bucket);
            // 初始化 UploadManager 对象并进行文件的上传
            $uploadMgr = new UploadManager();
            // 调用 UploadManager 的 putFile 方法进行文件的上传
            list($ret, $err) = $uploadMgr->putFile($token, $key, $file);
            if ($err !== null) {
                echo ["err"=>1,"msg"=>$err,"data"=>""];
            } else {
                $arr = array("url"=>$qiniu_info['domain'].'/'.$ret['key']);
                return json_encode($arr);
            }
        }
    }



    //单个图片上传操作
    function onepic_uploade($file){
        $thumb = request()->file($file);
        if(isset($thumb)){
            $dir = upload_img();
            $info = $thumb->move($dir); 
            if($info){  
                $imgurl = ROOT_HOST."/upimages/".date("Ymd",time())."/".$info->getFilename();
                return $imgurl;
            }  
        }
    }

    //多图片上传
    public function imgupload_duo(){

        $data['appletid'] = input("appletid");
        $files = request()->file('');  
        foreach($files as $file){        
            // 移动到框架应用根目录/public/upimages/ 目录下        
            $info = $file->move(ROOT_PATH . 'public' . DS . 'upimages');
            if($info){
                $url =  ROOT_HOST."/upimages/".date("Ymd",time())."/".$info->getFilename();
                $arr = array("url"=>$url);
                return json_encode($arr);
            }else{
                // 上传失败获取错误信息
                return $this->error($file->getError()) ;
            }    
        }
    }
}