<?php

namespace app\index\controller;

use app\index\model\Applet;
use app\index\model\ImsSudu8PageFlashsaleCate as Cate;
use app\index\validate\ImsSudu8PageProducts as Validate;

use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;

class Flashsale extends Base
{
    // 栏目列表
    public function catelist()
    {

        if (check_login()) {
            if (powerget()) {
                $uniacid = input('appletid');
                $app = new Applet;
                $appinfo = $app->getAppInfo();
                $this->assign('applet', $appinfo);

                //获取所有分类
                $cate = new Cate();
                $cates = $cate->getAllCate();
                //分类总数
                $count = $cate->getChildCateCount();

                $listV = $cates->toArray();
                $listAll = array();
                foreach ($listV['data'] as $key => $val) {
                    $id = intval($val['id']);

                    $listP = $cate->get($id);
                    if ($listP['catepic']) {
                        $listP['catepic'] = remote($uniacid, $listP['catepic'], 1);
                    } else {
                        $pic = "/image/noimage_1.png";
                        $listP['catepic'] = remote($uniacid, $pic, 1);
                    }
                    $listS = $cate->getChildCate($id);
                    foreach ($listS as $ki => $vi) {
                        if ($vi['catepic']) {
                            $listS[$ki]['catepic'] = remote($uniacid, $vi['catepic'], 1);
                        } else {
                            $pic2 = "/image/noimage_1.png";
                            $listS[$ki]['catepic'] = remote($uniacid, $pic2, 1);
                        }
                    }
                    $listF[0] = $listP;

                    //子集数据量
                    $zjcount = $cate->getChildCateCount($id);
                    $listF['data'] = $listS;
                    $listF['zcount'] = $zjcount;

                    array_push($listAll, $listF);
                }

                $this->assign('cates', $listAll);
                $this->assign('news', $cates);
                $this->assign('counts', $count);

                return $this->fetch('catelist');
            } else {
                $usergroup = Session::get('usergroup');
                if ($usergroup == 1) {
                    $this->error("您没有权限操作该小程序或找不到相应小程序！", 'Applet/applet');
                }
                if ($usergroup == 2) {
                    $this->error("您没有权限操作该小程序或找不到相应小程序！", 'Applet/index');
                }
                if ($usergroup == 3) {
                    $this->error("您没有权限操作该小程序或找不到相应小程序！", 'Applet/index');
                }
            }
        } else {
            $this->redirect('Login/index');
        }
    }


    //添加栏目
    public function add()
    {
        if (check_login()) {
            if (powerget()) {
                $uniacid = input('appletid');
                $cateid = input('cateid');
                $is_top = 0;
                $app = new Applet;
                $appinfo = $app->getAppInfo();
                $this->assign('applet', $appinfo);

                //获取栏目信息
                $cate = new Cate;
                $cates = $cate->getCates();
                $this->assign('cate', $cates);

                $allimg = '';
                $cateinfo = '';
                $cateurlid = 0;

                $huan = [];
                //判断是编辑还是新增
                if ($cateid) {   //编辑
                    $cateinfo = $cate->get($cateid)->toArray();
                    if ($cateinfo['uniacid'] != $uniacid) {
                        $usergroup = Session::get('usergroup');
                        if ($usergroup == 1) {
                            $this->error("找不到该栏目，或者该栏目不属于本项目");
                        }
                        if ($usergroup == 2) {
                            $this->error("找不到该栏目，或者该栏目不属于本项目");
                        }

                    } else {
                        $cateinfo['cateconf'] = unserialize($cateinfo['cateconf']);
                        if ($cateinfo['cid'] == 0) {
                            $cateurlid = 1;
                        }

                        if ($cateinfo['catepic']) {
                            $cateinfo['catepic'] = remote($uniacid, $cateinfo['catepic'], 1);
                        }
                        if ($cateinfo['randid']) {
                            $allimg = $cate->slide()->where('randid', $cateinfo['randid'])->select();
                            foreach ($allimg as $key => $value) {
                                $v = $value->toArray();
                                $v['url'] = remote($uniacid, $v['url'], 1);
                                array_push($huan, $v);
                            }

                        }
                    }
                    if($cateinfo['cid'] == 0){
                        $is_top = 1;
                    }
                } else {
                    $cateid = 0;
                }

                $this->assign('allimg', $huan);
                $this->assign('cateid', $cateid);
                $this->assign('is_top', $is_top);
                $this->assign('cateinfo', $cateinfo);
                $this->assign('cateurlid', $cateurlid);

            } else {
                $usergroup = Session::get('usergroup');
                if ($usergroup == 1) {
                    $this->error("您没有权限操作该小程序或找不到相应小程序！", 'Applet/applet');
                }
                if ($usergroup == 2) {
                    $this->error("您没有权限操作该小程序或找不到相应小程序！", 'Applet/index');
                }
                if ($usergroup == 3) {
                    $this->error("您没有权限操作该小程序或找不到相应小程序！", 'Applet/index');
                }

            }
            return $this->fetch('add');
        } else {
            $this->redirect('Login/index');
        }
    }


    //保存栏目
    public function save()
    {
        $uniacid = input('appletid');
        $cate = new Cate;
        $data = array();
        $data['uniacid'] = $uniacid;
        //排序
        $num = input("num");
        if ($num) {
            $data['num'] = $num;
        } else {
            $data['num'] = 0;
        }

        $data['randid'] = input("randid");
        $imgsrcs = input("imgsrcs/a");

        //启用
        $statue = input("statue");
        if ($statue === false) {
            $data['statue'] = 1;
        } else {
            $data['statue'] = (int)$statue;
        }
        //启用
        $slide_is = input("slide_is");
        if ($slide_is) {
            $data['slide_is'] = (int)$slide_is;
        } else {
            $data['slide_is'] = 2;
        }
        //所属栏目
        $cid = input("cid");
        if ($cid) {
            $data['cid'] = $cid;
        } else {
            $data['cid'] = 0;
        }
        //栏目名称
        $name = input("name");
        if ($name) {
            $data['name'] = $name;
        } else {
            $this->error("请填写栏目名称！");
        }
        //英文栏目名
        $ename = input("ename");
        if ($ename) {
            $data['ename'] = $ename;
        }

        $catepic = input("commonuploadpic");
        if ($catepic) {
            $data['catepic'] = remote($data['uniacid'], $catepic, 2);
        } else {
            $data['catepic'] = "";
        }
        //简介
        $cdesc = input("cdesc");
        if ($cdesc) {
            $data['cdesc'] = $cdesc;
        }
        //每页数量
        $pagenum = input("pagenum");
        if ($pagenum) {
            $data['pagenum'] = $pagenum;
        }
        //首页显示
        $show_i = input("show_i");
        if ($show_i) {
            $data['show_i'] = $show_i;
        } else {
            $data['show_i'] = 0;
        }
        //首页标题样式
        $list_tstyle = input("list_tstyle");
        if ($list_tstyle) {
            $data['list_tstyle'] = $list_tstyle;
        } else {
            $data['list_tstyle'] = 0;
        }
        //列表标题样式
        $list_tstylel = input("list_tstylel");
        if ($list_tstylel) {
            $data['list_tstylel'] = $list_tstylel;
        } else {
            $data['list_tstylel'] = 0;
        }
        $list_style_more = input("list_style_more");
        if ($list_style_more) {
            $data['list_style_more'] = input('list_style_more');
        } else {
            $data['list_style_more'] = 1;
        }
        //列表类型
        $list_type = input("list_type");
        if ($list_type) {
            if ($cid == 0) {
                $data['list_type'] = $list_type;
            } else {
                $data['list_type'] = 1;
            }

        } else {
            if ($cid == 0) {
                $data['list_type'] = 0;
            } else {
                $data['list_type'] = 1;
            }
        }
        //列表标题样式
        $list_stylet = input("list_stylet");
        if ($list_stylet) {
            $data['list_stylet'] = $list_stylet;
        }
        //文章页面样式
        $pic_page_btn = input("pic_page_btn");
        if ($pic_page_btn) {
            $data['pic_page_btn'] = $pic_page_btn;
        } else {
            $data['pic_page_btn'] = 0;
        }

        $data['type'] = 'showPro';

        //内容列表样式
        $list_style = input("list_style");
        if ($list_style) {
            $data['list_style'] = $list_style;
        } else {
            $data['list_style'] = 11;
        }
        $pic_page_bg = input("pic_page_bg");
        if ($pic_page_bg !== false && $pic_page_bg !== null) {
            $data['pic_page_bg'] = $pic_page_bg;
        } else {
            $data['pic_page_bg'] = 0;
        }
        //栏目内容
        $content = input("content");
        if ($content) {
            $data['content'] = $content;
        }
        $cateConf = array(
            'pmarb' => input("pmarb"),
            'ptit' => input("ptit"),
        );

        $data['to_pc_index'] = input('to_pc_index');


        $data['cateconf'] = serialize($cateConf);
        $id = input("cateid");

        Db::startTrans();
        try {
            $products_url = model('ProductsUrl');
            //存储幻灯片图片
            if ($imgsrcs) {
                $imgarr = array();
                foreach ($imgsrcs as $k => $v) {
                    $products_url_data = [];
                    $products_url_data['randid'] = $data['randid'];
                    $products_url_data['appletid'] = $data['uniacid'];
                    $products_url_data['url'] = remote($data['uniacid'], $v, 2);
                    $products_url_data['dateline'] = time();
                    array_push($imgarr, $products_url_data);
                }
                $products_url->saveAll($imgarr);
            }
            if ($id != 0) {
                $cate->save($data, ['id' => $id]);
            } else {
                $data['catefor'] = 'flashsale';
                $cate->save($data);
            }
            Db::commit();

        } catch (\Exception $e) {
            Db::rollback();
            $this->error('新增失败' . $e->getMessage());
        }

        $this->success('栏目信息新增/更新成功！', Url('Flashsale/catelist') . '?appletid=' . $data['uniacid']);
    }

    //删除栏目
    public function del(){
        $uniacid = input('appletid');
        $cateid = input('cateid');
        $goods = model('ImsSudu8PageProducts');
        $count = $goods::where('uniacid', $uniacid) ->where('cid', $cateid) ->count();
        if($count>0){
            $this->error('该栏目下存在商品，请先删除商品！');
        }
        $res = Cate::destroy(function ($query)use($uniacid, $cateid){
           $query ->where([
               'id'=> $cateid,
               'uniacid' =>$uniacid,
           ]);
        });
        if($res){
            $this->success('删除成功！');
        }else{
            $this->error('删除失败！');
        }
    }


    //删除商品
    public function delpro(){
        $uniacid = input('appletid');
        $newsid = input('newsid');

        $goods = model('ImsSudu8PageProducts');

        $res = $goods->destroy($newsid);
        
        if($res){
            $this->success('删除成功！');
        }else{
            $this->error('删除失败！');
        }
    }

    private function ddEE(){
        $secret = md5('worldidc_wnmd'); // md5('worldidc_wnmd');

        $key_content = include('License.php');
        $key_content = $key_content['license'];
        $length = strlen($key_content);

        // 密钥长度小于 102 必然无效
        // if($length < 102) {
        //     die();
        // }

        $is = base64_decode(substr($key_content, 0, 6));

        if(substr($is, 0, 1) == '|'){
            $str_arr = unpack("C2", substr($is, 1));
            $key_content = substr($key_content, 6);
            $len1 = $str_arr[1];
            $len2 = $length - 6 - $len1 - $str_arr[2];
        }else{
            $len1 = 26;
            $len2 = $length - 102;
        }

        // 获取加密的 code
        $code = base64_decode(substr($key_content, $len1, $len2));

        $code_length = strlen($code);

        $round = $code_length / 32;
        $left = $code_length % 32;

        // 获取和 code 等长的 self_key
        $self_key = str_repeat($secret, $round) . substr($secret, 0, $left);

        // 这边不妨把两个都 unpack 下

        $decode = array_map(function($a, $b) {
            $c = $a - $b;
            return $c > 0 ? $c : $c + 256;
        }, unpack("C{$code_length}", $code), unpack("C{$code_length}", $self_key));

        $str = array_reduce($decode, function($sum, $code) {
            return $sum .= chr($code);
        }, '');

        //end
        if($str == $_SERVER['HTTP_HOST']){

            //通过
        }else{

         //   echo '密钥错误，请联系开发者获取正确密钥!';
          //  exit();
        }
    }


    public function delallcate(){
        $uniacid = input('appletid');
        $cateids = input('cateids');
        $cateids = explode(',', $cateids);
        $res = Cate::destroy(function ($query)use($uniacid, $cateids){
           $query ->where([
               'id'=> ['in', $cateids],
               'uniacid' =>$uniacid,
           ]);
        });
        if($res){
            $this->success('删除成功！');
        }else{
            $this->error('删除失败！');
        }
    }


    //商品列表
    public function pro()
    {
        if (check_login()) {
            if (powerget()) {
                $uniacid = input('appletid');
                $cid = input("cid") ? input("cid") : 0;
                $title = input("key");
                $app = new Applet;
                $appinfo = $app->getAppInfo();
                $this->assign('applet', $appinfo);

                $cate = model('ImsSudu8PageFlashsaleCate');

                $listV = $cate->getAllCate()->toArray()['data'];
                $listAll = array();
                foreach ($listV as $key => $val) {
                    $id = intval($val['id']);
                    $listP = $cate->getCateById($id);
                    $listS = $cate->getChildCate($id);
                    //子集数据量
                    $zjcount = $cate->getChildCateCount($id);
                    $listF[0] = $listP;
                    $listF['data'] = $listS;
                    $listF['zcount'] = $zjcount;
                    array_push($listAll, $listF);
                }
                $this->assign('cate', $listAll);


                //获取子集
                // $listallcate=Db::name('wd_xcx_cate')->where("cid",$cid)->select();
                $listallcate = $cate->getChildCate($cid);
                $array1 = array();
                for ($a = 0; $a < count($listallcate); $a++) {
                    array_push($array1, $listallcate[$a]['id']);
                }
                array_push($array1, $cid);

                $goods = model('ImsSudu8PageProducts');
                $array2 = implode(",", $array1);
                $array2 = '[' . $array2 . ']';
                $where = '';
                if ($cid > 0) {
                    $where = 'and cid in ' . $array2;
                }

                if ($title) {
                    $where .= 'and title like %' . $title . '%';
                }
                $news = $goods->GetByTitle($title)->GetByCid($array1, $cid)->GetGoods($cid, $title)->where('uniacid', $uniacid)->paginate(10, false, ['query' => array('appletid' => $uniacid, 'cid' => $cid, 'title' => $title)]);
                $news = $news->each(function ($item) use ($uniacid) {
                    if ($item->thumb) {
                        $item->thumb = remote($uniacid, $item->thumb, 1);
                    } else {
                        $pic = "/image/noimage.jpg";
                        $item->thumb = remote($uniacid, $pic, 1);
                    }

                });

                $count = count($news);

                $this->assign('list', $news);
                // $this->assign('news',$news);
                $this->assign('counts', $count);


            } else {
                $usergroup = Session::get('usergroup');
                if ($usergroup == 1) {
                    $this->error("您没有权限操作该小程序或找不到相应小程序！", 'Applet/applet');
                }
                if ($usergroup == 2) {
                    $this->error("您没有权限操作该小程序或找不到相应小程序！", 'Applet/index');
                }
                if ($usergroup == 3) {
                    $this->error("您没有权限操作该小程序或找不到相应小程序！", 'Applet/index');
                }

            }
            return $this->fetch('pro');
        } else {
            $this->redirect('Login/index');
        }
    }


    //添加商品
    public function addPro()
    {
        if (check_login()) {
            if (powerget()) {
                $appletid = input("appletid");
                $app = new Applet;
                $appinfo = $app->getAppInfo();
                $this->assign('applet', $appinfo);

                //会员等级
                $grade_arr = Db::name("wd_xcx_vipgrade")->where("uniacid", $appletid)->order('grade asc')->select();

                $yunfei_gg_list = model('ImsSudu8PageFreight')->all(['uniacid' => $appletid, 'is_delete' => 0]);
                $this->assign('yunfei_gg_list', $yunfei_gg_list);
                $cate = model('ImsSudu8PageFlashsaleCate');

                $listV = $cate->getAllCate()->toArray()['data'];
                $listAll = array();
                foreach ($listV as $key => $val) {
                    $id = intval($val['id']);
                    $listP = $cate->getCateById($id);
                    $listS = $cate->getChildCate($id);
                    //子集数据量
                    $zjcount = $cate->getChildCateCount($id);
                    $listF[0] = $listP;
                    $listF['data'] = $listS;
                    $listF['zcount'] = $zjcount;
                    array_push($listAll, $listF);
                }
                $this->assign('cate', $listAll);
                $stores=Db::name("wd_xcx_store")->where("uniacid",$appletid)->select();
                $this->assign('stores',$stores);
//                $cates = Db::name('wd_xcx_multicate')->where("uniacid",$appletid)->where('statue',1)->where("type","showPro")->select();
//                $multipros = array();
                $goods = model('ImsSudu8PageProducts');
                $allimg = [];
                $newsid = input("newsid");
                $newsinfo = array();
                if ($newsid) {
                    //有新闻号时，先判断该新闻是不是属于该小程序！
                    $newsget = Db::name('wd_xcx_products') ->where('id', $newsid) ->find();
                    if ($newsget['uniacid'] == $appletid) {
                        if ($newsget['thumb']) {
                            $newsget['thumb'] = remote($appletid, $newsget['thumb'], 1);
                        }
                        if ($newsget['shareimg']) {
                            $newsget['shareimg'] = remote($appletid, $newsget['shareimg'], 1);
                        }
                        $newsget['text'] = unserialize($newsget['text']);
                        $allimg = $goods->slide()->where('randid', $newsget['randid'])->select();
                        foreach ($allimg as $key => &$value) {
                            $value['url'] = remote($appletid, $value['url'], 1);
                        }
                        $newsinfo = $newsget;
//                        $sons_keys =   Db::name('wd_xcx_multicates')->where("id",'in',$newsinfo['top_catas'])->select();
//                        foreach ($sons_keys as $k => $v){
//                            $sons_keys[$k]['sons'] = Db::name('wd_xcx_multicates')->where("pid",$v['id'])->select();
                       // }
                       if(!empty($newsinfo['vipconfig'])){
                           $newsinfo['vipconfig'] = unserialize($newsinfo['vipconfig']);
                           if(!isset($newsinfo['vipconfig']['set3'])){
                               $newsinfo['vipconfig']['set3'] = 0;
                           }
                       }
                        //图片集
                    } else {
                        $usergroup = Session::get('usergroup');
                        if ($usergroup == 1) {
                            $this->error("找不到该内容，或者该内容不属于本小程序");
                        }
                        if ($usergroup == 2) {
                            $this->error("找不到该内容，或者该内容不属于本小程序");
                        }
                    }
                } else {
                    $newsid = 0;
                    $cate_arr = "";
                    $multipro_arr = "";
                    $sons_keys = "";
//                    foreach ($cates as $k => $v) {
//                        $cates[$k]['flag'] = 0;
//                    }
                }
                $jieguo = Db::name('wd_xcx_formlist')->where("uniacid", $appletid) ->order('id desc')->select();

//                $this->assign('cates',$cates);
                $this->assign('forms', $jieguo);
                $this->assign('allimg', $allimg);
//                $this->assign('sons_keys',$sons_keys);
                $this->assign('imgcount', count($allimg));
                $this->assign('newsid', $newsid);
                $this->assign('newsinfo', $newsinfo);
                $this->assign('grade_arr',$grade_arr);
            } else {
                $usergroup = Session::get('usergroup');
                if ($usergroup == 1) {
                    $this->error("您没有权限操作该小程序或找不到相应小程序！", 'Applet/applet');
                }
                if ($usergroup == 2) {
                    $this->error("您没有权限操作该小程序或找不到相应小程序！", 'Applet/index');
                }
                if ($usergroup == 3) {
                    $this->error("您没有权限操作该小程序或找不到相应小程序！", 'Applet/index');
                }

            }
            return $this->fetch('addpro');
        } else {
            $this->redirect('Login/index');
        }
    }

    //保存秒杀商品
    public function savePro()
    {
        if (check_login()) {
            if (powerget()) {
                $uniacid = input('appletid');
                $data = array();
                //小程序ID
                $data['uniacid'] = $uniacid;
                //排序
                $data['num'] = input('num') ? input('num') : 0;
                //所属栏目
                $cid = input('cid');
                if ($cid) {
                    $data['cid'] = $cid;
                    // 获取栏目具体信息
                    $lanmu = model('ImsSudu8PageFlashsaleCate')->get($cid);
                    $data['type'] = 'showPro';
                    $data['lanmu'] = $lanmu['name'];

                    if ($lanmu['cid'] == 0) {
                        $data['pcid'] = $cid;
                    } else {
                        $data['pcid'] = $lanmu['cid'];
                    }
                }

               //开启会员购买设置
                $set1 = input("set1");
                $set2 = input("set2");
                $set3 = input("set3");
                $vipconfig = array(
                   "set1" => $set1,
                   "set2" => $set2,
                   "set3" => $set3
                );
                $data['vipconfig']  = serialize($vipconfig);

                //商品标签
                $data['labels'] = input("labels") ? input("labels") : '';

                //访问量
                $data['hits'] = input('hits') ? input('hits') : 0;
                //标题
                $data['title'] = input('title') ? input('title') : '';
                //已售数量
                $data['sale_num'] = input("sale_num") ? input("sale_num") : 0;
                //门店价
                $data['price'] = input("price") ? input("price") : '0.00';
                //市场价
                $data['market_price'] = input("market_price") ? input("market_price") : '0.00';
                //库存
                $data['pro_kc'] = input("pro_kc") ? input("pro_kc") : 0;

                //每人限购数
                $data['pro_xz'] = input("pro_xz") ? input("pro_xz") : 0;

                //秒杀开始时间
                $data['sale_time'] = input("sale_time") ? strtotime(input("sale_time")) : 0;
                //秒杀结束时间
                $data['sale_end_time'] = input("sale_end_time") ? strtotime(input("sale_end_time")) : 0;

                //是否确认订单
                $data['pro_flag_ding'] = input("pro_flag_ding") ? input("pro_flag_ding") : 0;
                //取商品方式
                $data['kuaidi'] = input('kuaidi');
                //分销设置
                $data['fx_uni'] = input('fx_uni') ? input('fx_uni') : 2;

                $data['commission_type'] = input('commission_type') ? input('commission_type') : 1;
                $data['commission_one'] = input('commission_one');
                $data['commission_two'] = input('commission_two');
                $data['commission_three'] = input('commission_three');

                $data['yunfei_ggid'] = input('yunfei_ggid');
                $data['stores'] = intval(input('kuaidi')) > 0 ? (input("stores") ? input("stores") : '') : '';

                $randid = input('randid');
                if ($randid) {
                    $imgsrcs = input("imgsrcs/a");
                    $data['randid'] = $randid;
                }
                // 处理幻灯片
                // if (!$randid) {
                // } else {
                //     $silde = Db::name('wd_xcx_products_url')->where("randid", $randid)->select();
                //     $arrsilde = array();
                //     if ($silde) {
                //         foreach ($silde as $rec) {
                //             $arrsilde[] = $rec['url'];
                //         }
                //         $data['text'] = serialize($arrsilde);
                //     } else {
                //         $data['text'] = "";
                //     }
                // }
                // dump($data['text']);die;
                //缩略图
                $thumb = input("commonuploadpic1");
                if ($thumb) {
                    $data['thumb'] = remote($data['uniacid'], $thumb, 2);
                }
                //分享图
                $shareimg = input("commonuploadpic2");
                if ($shareimg) {
                    $data['shareimg'] = remote($data['uniacid'], $shareimg, 2);
                }
                //简介
                $data['desc'] = input('desc') ? input('desc') : '';
                //自定义表单
                $data['formset'] = input("formset") ? input("formset") : 0;

                //商品详情
                $data['product_txt'] = htmlspecialchars_decode(input('product_txt')) ? htmlspecialchars_decode(input('product_txt')) : '';

                $data['con2'] = htmlspecialchars_decode(input('con2')) ? htmlspecialchars_decode(input('con2')) : '';
                $data['con3'] = htmlspecialchars_decode(input('con3')) ? htmlspecialchars_decode(input('con3')) : '';
                $data['buy_type'] = input('buy_type') ? input('buy_type') : '购买';

                $newsid = input("newsid");
//                $top_catas = Db::name('wd_xcx_multicate')->where("id",input('mulitcataid'))->find();
                $data['sons_catas'] = input('sons/a') ? implode(',', input('sons/a')) : '';
//                $data['top_catas'] = $top_catas['top_catas']?implode(',',unserialize($top_catas['top_catas'])):'';
//                $data['mulitcataid'] = input('mulitcataid');
                $data["get_share_gz"] = input('get_share_gz');
                $data['score'] = input('score');
                $data["get_share_score"] = input('get_share_score');
                $data["get_share_num"] = input('get_share_num');
                $data['ctime'] = time();
                $data['is_sale'] = input("is_sale") ? input("is_sale") : 0;
                $data['video'] = input('video');

                Db::startTrans();
                try {
                    $validate = new Validate;
                    if (!$validate->scene('add')->check($data)) {
                        throw new \Exception($validate->getError());
                    }
                    $products_url = model('ProductsUrl');
                    //存储幻灯片图片
                    if ($imgsrcs) {
                        $imgarr = array();
                        foreach ($imgsrcs as $k => $v) {
                            $products_url_data = [];
                            $products_url_data['randid'] = $data['randid'];
                            $products_url_data['appletid'] = $data['uniacid'];
                            $products_url_data['url'] = remote($data['uniacid'], $v, 2);
                            $products_url_data['dateline'] = time();
                            array_push($imgarr, $products_url_data);
                        }
                        $products_url->saveAll($imgarr);
                        $silde = Db::name('wd_xcx_products_url')->where("randid", $randid)->select();
                        $arrsilde = array();
                        if ($silde) {
                            foreach ($silde as $rec) {
                                $arrsilde[] = $rec['url'];
                            }
                            $data['text'] = serialize($arrsilde);
                        } else {
                            $data['text'] = "";
                        }
                    }else{
                        $silde = Db::name('wd_xcx_products_url')->where("randid", $randid)->select();
                        $arrsilde = array();
                        if ($silde) {
                            foreach ($silde as $rec) {
                                $arrsilde[] = $rec['url'];
                            }
                            $data['text'] = serialize($arrsilde);
                        } else {
                            $data['text'] = "";
                        }
                    }
                    if ($newsid != 0) {
                        model('ImsSudu8PageProducts')->save($data, ['id' => $newsid]);
                    } else {
                        if(!$thumb && !$shareimg){
                            throw new \Exception('商品缩略图与分享图请至少设置一张！');
                        }
                        model('ImsSudu8PageProducts')->save($data);
                    }

                    Db::commit();

                } catch (\Exception $e) {
                    Db::rollback();
                    $this->error('新增/编辑秒杀商品失败，' . $e->getMessage());
                }

                $this->success('秒杀商品新增/更新成功！', Url('Flashsale/pro') . '?appletid=' . $data['uniacid']);

            } else {
                $usergroup = Session::get('usergroup');
                if ($usergroup == 1) {
                    $this->error("您没有权限操作该小程序或找不到相应小程序！", 'Applet/applet');
                }
                if ($usergroup == 2) {
                    $this->error("您没有权限操作该小程序或找不到相应小程序！", 'Applet/index');
                }
                if ($usergroup == 3) {
                    $this->error("您没有权限操作该小程序或找不到相应小程序！", 'Applet/index');
                }
            }
        } else {
            $this->redirect('Login/index');
        }

    }


    //商品订单
    public function orders()
    {
        if (check_login()) {
            if (powerget()) {
                $uniacid = input('appletid');
                $app = new Applet;
                $appinfo = $app->getAppInfo();
                $this->assign('applet', $appinfo);

                $search_flag = input("search_flag");
                $search_type = input("search_type");
                $search_keys = input("search_keys");
                $start_get = input("start_get");
                $end_get = input("end_get");


                $where = [];
                if ($search_flag != '') {
                    if($search_flag == 1){  //待发货
                        $where['flag'] = array('eq', 1);
                        $where['nav'] = array('eq', 1);
                    }elseif($search_flag == 10){   //待消费
                        $where['flag'] = array('eq', 1);
                        $where['nav'] = array('eq', 2);
                    }else{
                        $where['flag'] = array('eq', $search_flag);
                    }
                    
                }

                $this ->ddEE();

                if($start_get || $end_get){
                    if(!$start_get){
                        $start_get_t = 0;
                    }else{
                        $start_get_t = $start_get;
                    }
                    if(!$end_get){
                        $end_get_t = date('Y-m-d H:i', time());
                    }else{
                        $end_get_t = $end_get;
                    }
                    $where['creattime'] = ['between time', [$start_get_t, $end_get_t]];
                }

                if ($search_type) {
                    if ($search_type == 1) {
                        $where['order_id'] = array('like', '%' . $search_keys . '%');
                    }
                    // if ($search_type == 2) {
                    //     $where['name'] = ['like', '%' . $search_keys . '%'];
                    // }
                    // if ($search_type == 3) {
                    //     $where['mobile'] = ['like', '%' . $search_keys . '%'];
                    // }
                    // if ($search_type == 4) {
                    //     $where['address'] = ['like', '%' . $search_keys . '%'];
                    // }
                }

                $order = model('ImsSudu8PageOrder');
                $orders = $order->getOrders($where, $search_flag, $search_type, $search_keys, $start_get, $end_get);
                // $orders = Db::name('wd_xcx_order')->where("uniacid", $uniacid)->where($where)->select();

                // dump($order->getLastSql());die;
                $counts = count($orders);

                $orders = $orders->each(function ($item) use ($uniacid) {
                    $item->addressData = $item->addressData;
                    $item->order_yue = floatval($item->true_price) - floatval($item->pay_price);
                    if(!empty($item->self_taking_info)){
                        $self_taking_info= unserialize($item->self_taking_info);
                        $self_taking_shop_info = unserialize($self_taking_info['self_taking_shop_info']);
                        $self_taking_info['self_taking_shop_info'] = $self_taking_shop_info;           
                        $item->self_taking_info = $self_taking_info;
                    }

                    if (empty($item->name)) {
                        if ($item->m_address) {
                            $item->m_address = unserialize($item->m_address);
                            $item->name = $item->m_address['name'];
                            $item->mobile = $item->m_address['mobile'];
                            $item->address = $item->m_address['address'];
                        }
                    }

                    if ($item->formid) {
                        $formMo = model('ImsSudu8PageFormcon');
                        $arr2 = $formMo->get($item->formid);
                        $arr2['val'] = unserialize($arr2['val']);

                        $item->forminfo = $arr2['val'];

                    } else {
                        $item->forminfo = '';
                    }

                    if ($item->custime) {
                        $item->custime = date("Y-m-d H:i:s", $item->custime);
                    } else {
                        $item->custime = "";
                    }
                    $item->thumb = remote($uniacid, $item->thumb, 1);

                    if ($item->hxinfo == "") {
                        $item->hxinfo2 = "无";
                    } else {
                        $item->hxinfo = unserialize($item->hxinfo);
                        if ($item->hxinfo[0] == 1) {
                            $item->hxinfo2 = "系统核销";
                        }else if($item->hxinfo[0] == '密码核销' || $item->hxinfo[0] == '管理员核销'){
                            $item->hxinfo2 = $item->hxinfo[0];
                        }else if($item->hxinfo[0]=='核销员核销'){
                            $item->hxinfo2=$item->hxinfo[1].'核销';
                        }
//                        else{
//                            $store=Db::name('wd_xcx_store')->where("id",$row['hxinfo'][1])->where("uniacid",$id)->find();
//                            $staff=Db::name('wd_xcx_staff')->where("id",$row['hxinfo'][2])->where("uniacid",$id)->find();
//                            $row['hxinfo2']="门店：".$store['title']."</br>员工：".$staff['realname'];
//                        }
                    }
                    //获取联系方式
                    $item->creattime = date("Y-m-d H:i:s", $item->creattime);
                    $user = model('ImsSudu8PageUser')->get($item->suid);
                    if ($user['nickname']) {
                        $row['nickname'] = $user['nickname'];
                    } else {
                        $item->nickname = "";
                    }

                    if ($item->beizhu != '' || $item->beizhu_val != '') {
                        $item->beizhu = empty($item->beizhu) ? $item->beizhu : $item->beizhu_val;
                    } else {
                        $item->beizhu = '';
                    }

                    //查询优惠劵
                    $item->order_duo = unserialize($item->order_duo);
                    $yhInfo_msg = array();
                    if (!empty($item->yhinfo)) {
                        $yhInfo = unserialize($item->yhinfo);
                        $yhInfo_msg['yhInfo_yunfei'] = $yhInfo['yunfei'];
                        $yhInfo_msg['yhInfo_score'] = $yhInfo['score'];
                        $yhInfo_msg['yhInfo_yhq'] = $yhInfo['yhq'];
                        $yhInfo_msg['yhInfo_mj'] = $yhInfo['mj'];

                        $item->yhInfo_msg = $yhInfo_msg;
                    } else {
                        $item->yhInfo_msg['yhInfo_yunfei'] = 0;
                        if ($item->dkscore > 0) {
                            $jfgz = model('ImsSudu8PageRechargeconf')->get(['uniacid' => $uniacid]);
                            $item->yhInfo_msg['yhInfo_score']['msg'] = $item->dkscore . "抵扣" . floatval($item->dkscore) * floatval($jfgz['money']) / floatval($jfgz['score']);
                            $item->yhInfo_msg['yhInfo_score']['money'] = floatval($item->dkscore) * floatval($jfgz['money']) / floatval($jfgz['score']);
                        } else {
                            $item->yhInfo_msg['yhInfo_score']['msg'] = "未使用积分";
                            $item->yhInfo_msg['yhInfo_score']['money'] = 0;
                        }
                        if ($item->coupon) {
                            //查询优惠劵
                            $coupon = model('ImsSudu8PageCouponUser')->getCou($item->coupon);
                            $item->yhInfo_msg['yhInfo_yhq']['msg'] = $coupon->title;
                            $item->yhInfo_msg['yhInfo_yhq']['money'] = $coupon->price;
                        } else {
                            $item->yhInfo_msg['yhInfo_yhq']['msg'] = "未使用优惠券";
                            $item->yhInfo_msg['yhInfo_yhq']['money'] = 0;
                        }
                        $item->yhInfo_msg['yhInfo_mj']['msg'] = "";
                        $item->yhInfo_msg['yhInfo_mj']['money'] = 0;
                    }
                    if (!$item->custime) {
                        $item->custime = "未消费";
                    }
                });
//                dump($orders);die;
                $this->assign('order', $orders);
                $this->assign('counts', $counts);
                $this->assign("search_flag", $search_flag);
                $this->assign("search_type", $search_type);
                $this->assign("search_keys", $search_keys);
                $this->assign("start_get", $start_get);
                $this->assign("end_get", $end_get);

                return $this->fetch('orders');
            } else {
                $usergroup = Session::get('usergroup');
                if ($usergroup == 1) {
                    $this->error("您没有权限操作该小程序或找不到相应小程序！", 'Applet/applet');
                }
                if ($usergroup == 2) {
                    $this->error("您没有权限操作该小程序或找不到相应小程序！", 'Applet/index');
                }
                if ($usergroup == 3) {
                    $this->error("您没有权限操作该小程序或找不到相应小程序！", 'Applet/index');
                }
            }
        } else {
            $this->redirect('Login/index');
        }
    }

    //订单处理
    public function order()
    {
        $op = input('op');
        $uniacid = input('appletid');

        if ($op == 'fahuo') {   //发货
            $orderid = input('orderid');
            $data['custime'] = time();
            $data['kuaidi'] = input('kuaidi');
            $data['kuaidihao'] = input('kuaidihao');
            $data['flag'] = 4;

            $res = model('ImsSudu8PageOrder')->save($data, ['uniacid' => $uniacid, 'id' => $orderid]);
            if ($res) {
                $info = model('ImsSudu8PageOrder')->get($orderid);
                if($info['source'] == 1){
                    $openid = Db::name("wd_xcx_user")->where("suid", $info['suid'])->value('openid');
                    $jsons = [
                        'order_id' => $info['order_id']
                    ];
                    $jsons = serialize($jsons);
                    sendSubscribe($uniacid, 1, $openid, $jsons);
                }
                $this->success("发货成功");
            }
        }

        if ($op == 'hx') {   //核销订单
            $orderid = input('orderid');
            $data['custime'] = time();
            $data['flag'] = 2;
            $data['hxinfo'] = 'a:1:{i:0;i:1;}';
            $order_id = model('ImsSudu8PageOrder') ->get($orderid);
            $res = model('ImsSudu8PageOrder')->save($data, ['id' => $orderid]);

            //改变分销订单状态
            if(model('ImsSudu8PageFxLs') ->isFxOrder($order_id['order_id'])){
                $fx['flag'] = 2;
                $rr = model('ImsSudu8PageFxLs') ->save($fx, ['order_id' => $order_id['order_id']]);
                $this->dopagegivemoney($uniacid, $order_id['suid'], $order_id['order_id']);
            }else{
                $rr = true;
            }
            

            add_all_pay($uniacid, $order_id['price'], $order_id['suid']);
            check_vip_grade($uniacid, $order_id['suid']);

            if($order_id['source'] == 1){
                $openid = Db::name("wd_xcx_user")->where("suid", $order_id['suid'])->value('openid');
                $jsons = [
                    'fprice' => $order_id['price']
                ];
                $jsons = serialize($jsons);
                sendSubscribe($uniacid, 2, $openid, $jsons);
            }
            if ($res && $rr) {
                $this->success("核销成功！");
            }
        }

        if ($op == 'confirmtk') {   //取消订单
            $orderObj = model('ImsSudu8PageOrder');
            $proObj = model('ImsSudu8PageProducts');
            $userObj = model('ImsSudu8PageSuperuser');
            $order_id = input('orderid');
            if (input('qxbeizhu')) {
                $data['qxbeizhu'] = input('qxbeizhu');
            }

            $order = $orderObj->get($order_id);
            if ($order->flag == 5) {
                return;
            }
            $order_product = $proObj->get($order->pid);
            $user = $userObj->get($order->suid);

            $now = time();
            $out_refund_no = date("Y", $now) . date("m", $now) . date("d", $now) . date("H", $now) . date("i", $now) . date("s", $now) . rand(1000, 9999);
            //开启事务
            Db::startTrans();
            try {
                //改变订单状态
                $data['flag'] = 5;
                $data['th_orderid'] = $out_refund_no;
                $orderObj->save($data, ['id' => $order_id]);

                //处理优惠券
                if ($order->coupon) {
                    $cou['flag'] = 0;
                    $cou['utime'] = 0;
                    model('ImsSudu8PageCouponUser')->save($cou, ['id' => $order->coupon]);
                }
                //处理积分
                if ($order->dkscore) {
                    $upUserData['score'] = $user->score  + $order->dkscore;
                    $userObj->save($upUserData, ['id' => $order->suid]);
                    $score_data = array(
                        "uniacid" => $uniacid,
                        "orderid" => $order->order_id,
                        "suid" => $order->suid,
                        "type" => "add",
                        "score" => $order->dkscore,
                        "message" => "退款退回抵扣积分",
                        "creattime" => time()
                    );
                    model('ImsSudu8PageScore')->save($score_data);
                }

                //处理库存与真实销量
                if ($order->num > 0) {   //更新销量
                    $newProData['sale_tnum'] = $order_product->sale_tnum - $order->num;
                    $newProData['sale_tnum'] = $newProData['sale_tnum'] > 0 ? $newProData['sale_tnum'] : 0;
                    $proObj->save($newProData, ['id' => $order->pid]);
                }
                if ($order_product->pro_kc != -1) { //有限量库存 更新库存
                    if ($order['num'] > 0) {
                        $proKc['pro_kc'] = $order_product->pro_kc + $order->num;
                        $proObj->save($proKc, ['id' => $order->pid]);
                    }
                }

                //处理分销订单
                if(model('ImsSudu8PageFxLs') ->isFxOrder($order['order_id'])){
                    $fx['flag'] = 3;
                    model('ImsSudu8PageFxLs') ->save($fx, ['order_id' => $order['order_id']]);
                }

                //处理退款
                $yuTk = $order->true_price - $order->pay_price;
                if($yuTk > 0){    //处理余额
                    $userMoney = $user->money + $yuTk;
                    $userObj->save(['money' => $userMoney], ['id' => $order->suid]);

                    $xfmoney = array(
                        "uniacid" => $uniacid,
                        "orderid" => $order->order_id,
                        "suid" => $order->suid,
                        "type" => "add",
                        "score" => $yuTk,
                        "message" => "退款退回余额",
                        "creattime" => time()
                    );
                    Db::name('wd_xcx_money')->insert($xfmoney);
                }

                if ($order->pay_price > 0) {
                    $app = model('Applet')->get($uniacid);

                    if($order->paytype == 1){  //微信支付
                        if($order->source == 1){
                            $mchid = $app->mchid;   //商户号
                            $apiKey = $app->signkey;    //商户的秘钥
                            $appid = $app->appID;                 //小程序的id
                            $SSLCERT_PATH = ROOT_PATH.'public/Cert/'.$uniacid.'/apiclient_cert.pem';//证书路径
                            $SSLKEY_PATH =  ROOT_PATH.'public/Cert/'.$uniacid.'/apiclient_key.pem';//证书路径
                        }elseif($order->source == 3){
                            $mchid = $app ->wx_h5_mchid;   //商户号
                            $apiKey = $app ->wx_h5_signkey;    //商户的秘钥
                            $appid = $app ->wx_h5_appid;                 //小程序的id
                            $SSLCERT_PATH = ROOT_PATH.'public/Cert/'.$uniacid.'/h5_apiclient_cert.pem';//证书路径
                            $SSLKEY_PATH =  ROOT_PATH.'public/Cert/'.$uniacid.'/h5_apiclient_key.pem';//证书路径
                        }elseif($order['source'] == 5){
                            $mchid = $app['bdance_h5_mchid'];   //商户号
                            $apiKey = $app['bdance_h5_signkey'];    //商户的秘钥
                            $appid = $app['bdance_h5_appid'];                 //小程序的id
                            $SSLCERT_PATH = ROOT_PATH.'public/Cert/'.$uniacid.'/bdance_apiclient_cert.pem';//证书路径
                            $SSLKEY_PATH =  ROOT_PATH.'public/Cert/'.$uniacid.'/bdance_apiclient_key.pem';//证书路径
                        }
                        
                        $appkey = $app->appSecret;            //小程序的秘钥
                        $openid = 'openid';    //申请者的openid
                        $outTradeNo = $order->order_id;
                        $totalFee = intval($order->pay_price * 100);  //申请了提现多少钱
                        $outRefundNo = $order_id; //商户订单号
                        $refundFee = intval($order->pay_price * 100);  //申请了提现多少钱

                        $opUserId = $mchid;//商户号
                        include "WinXinRefund.php";
                        $weixinpay = new WinXinRefund($openid, $outTradeNo, $totalFee, $outRefundNo, $refundFee, $SSLCERT_PATH, $SSLKEY_PATH, $opUserId, $appid, $apiKey);
                        $return = $weixinpay->refund();
                        if (!$return) {
                            throw new \Exception('微信退款失败， 请检查系统设置->微信小程序相关配置');
                        }

                    }elseif ($order->paytype == 2){     //支付宝支付
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
                        $request->setBizContent("{'refund_amount':" . $order->pay_price . ", 'out_trade_no': " . $order->order_id . "}");
                        $result = $aop->execute($request);
                        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
                        $resultCode = $result->$responseNode->code;
                        if (!empty($resultCode) && $resultCode == 10000) {
                            $return = true;
                        } else {
                            throw new \Exception('支付宝退款失败， 请检查系统设置->支付宝小程序设置');
                        }
                    }elseif($order->paytype == 3){
                        $pay_info = unserialize($order->pay_info);
                        require_once(ROOT_PATH.'application/api/controller/bdpay/Autoloader.php');
                        $params = [
                            'method' => 'nuomi.cashier.applyorderrefund',
                            'orderId' => intval($pay_info['orderId']),
                            'userId' => intval($pay_info['userId']),
                            'refundType' => '1',
                            'refundReason' => '订单退款',
                            'tpOrderId' => $order['order_id'],
                            'appKey' => $app->baidu_pay_appkey
                        ];
                        $rsaSign = \NuomiRsaSign::genSignWithRsa($params, $app->baidu_private_key);
                        $params['rsaSign'] = $rsaSign;
                        $url = 'https://nop.nuomi.com/nop/server/rest';
                        $res = _Postrequest($url, $params);
                        $res = json_decode($res, true);
                        if($res['errno'] == 0){
                            $return = true;
                        }else{
                            throw new \Exception('百度退款失败， 请检查系统设置->百度小程序设置');
                        }
                    }elseif($order->paytype == 4){
                        $pay_info = unserialize($order['pay_info']);
                        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";  
                        $nonce_str = "";  
                        for($i = 0; $i < 32; $i++) {  
                            $nonce_str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
                        }
                        $op_user_passwd = MD5($app['qq_mchid_password']);
                        $appid = $app['qq_appid'];
                        $mch_id = $app['qq_mchid'];
                        $out_trade_no = $order['order_id'];
                        $refund_fee = $order['pay_price']*100;
                        $sign_str = "appid=".$appid."&mch_id=".$mch_id."&nonce_str=".$nonce_str."&op_user_id=".$mch_id."&op_user_passwd=".$op_user_passwd."&out_refund_no=".$out_refund_no."&out_trade_no=".$out_trade_no."&refund_fee=".$refund_fee;
                        $sign = $sign_str."&key=".$app['qq_mchid_key'];
                        $sign = strtoupper(MD5($sign));
                        $params = "<xml>
                                <appid>".$appid."</appid>
                                <mch_id>".$mch_id."</mch_id>
                                <nonce_str>".$nonce_str."</nonce_str>
                                <op_user_id>".$mch_id."</op_user_id>
                                <op_user_passwd>".$op_user_passwd."</op_user_passwd>
                                <out_refund_no>".$out_refund_no."</out_refund_no>
                                <out_trade_no>".$out_trade_no."</out_trade_no>
                                <refund_fee>".$refund_fee."</refund_fee>
                                <sign>".$sign."</sign>
                                </xml>";
                        $url = 'https://api.qpay.qq.com/cgi-bin/pay/qpay_refund.cgi';
                        $res = $this -> postXmlSSLCurl($params, $url, 30, $uniacid);
                        $res = $this->xmlToArray($res);
                        if($res){
                            if($res['return_code'] == 'SUCCESS'){
                                $return = true;
                            }else{
                                $this->error('退款失败!请检查系统设置->QQ小程序设置');exit;
                            }
                        }else{
                            $this->error('退款失败!请检查系统设置->QQ小程序设置');exit;
                        }
                    }




                    if($order->pay_price > 0){    //处理余额
                        $xfmoney = array(
                            "uniacid" => $uniacid,
                            "orderid" => $order->order_id,
                            "suid" => $order->suid,
                            "type" => "add",
                            "score" => $order->pay_price,
                            "creattime" => time()
                        );
                        if($order->paytype == 1){
                            $xfmoney["message"] = "退款退回微信"; 
                        }else if($order->paytype == 2){
                            $xfmoney["message"] = "退款退回支付宝"; 
                        }else if($order->paytype == 3){
                            $xfmoney["message"] = "退款退回百度"; 
                        }else if($order->paytype == 4){
                            $xfmoney["message"] = "退款退回QQ"; 
                        }
                        Db::name('wd_xcx_money')->insert($xfmoney);
                    }
                    if($return){
                        if($order['source'] != 3){
                            $jsons['orderid'] = $order['order_id'];
                            $jsons['ftitle'] = $order['product'];
                            $jsons['fprice'] = "实付：".$order['true_price'];

                            
                            if($order['source'] == 1){
                                if($yuTk > 0){
                                    $jsons['refund_type'] = "退回微信：￥".$order->pay_price."元，退回余额：￥".$yuTk;
                                }else{
                                    $jsons['refund_type'] = "退回微信：￥".$order['true_price']."元";
                                }

                                $openid = Db::name('wd_xcx_user')->where('suid', $order['suid'])->value('openid');

                                $jsons = [
                                    'order_id' => $order['order_id'],
                                    'fprice' => $order['price'],
                                    'msg' => "退款成功",
                                ];
                                $jsons = serialize($jsons);
                                sendSubscribe($uniacid, 3, $openid, $jsons);
             
                            }else if($order['source'] == 6){
                                if($yuTk > 0){
                                    $jsons['refund_type'] = "退回QQ：￥".$order->pay_price."元，退回余额：￥".$yuTk;
                                }else{
                                    $jsons['refund_type'] = "退回QQ：￥".$order['true_price']."元";
                                }
                                $jsons = serialize($jsons);

                                $openid = Db::name('wd_xcx_qq_user')->where('suid', $order['suid'])->value('openid');
                                tpl_send($uniacid, 8, $openid, $order['source'], $order['qx_formid'], $jsons);
                            }else if($order['source'] == 5){
                                if($yuTk > 0){
                                    $jsons['refund_type'] = "退回微信：￥".$order->pay_price."元，退回余额：￥".$yuTk;
                                }else{
                                    $jsons['refund_type'] = "退回微信：￥".$order['true_price']."元";
                                }
                                $jsons = serialize($jsons);

                                $openid = Db::name('wd_xcx_toutiao_user')->where('suid', $order['suid'])->value('openid');
                                tpl_send($uniacid, 8, $openid, $order['source'], $order['qx_formid'], $jsons);
                            }

                        }
                    }
                }else{
                    if($order['source'] != 3){
                        $jsons['orderid'] = $order['order_id'];
                        $jsons['ftitle'] = $order['product'];
                        $jsons['fprice'] = "实付：".$order['true_price'];
                        $jsons['refund_type'] = "退回余额：￥".$order['true_price']."元";
                        $jsons = serialize($jsons);
                        if($order['source'] == 1){
                            $openid = Db::name('wd_xcx_user')->where('suid', $order['suid'])->value('openid');
                            $jsons = [
                                'order_id' => $order['order_id'],
                                'fprice' => $order['price'],
                                'msg' => "退款成功",
                            ];
                            $jsons = serialize($jsons);
                            sendSubscribe($uniacid, 3, $openid, $jsons);
                        }else if($order['source'] == 6 && $order['qx_formid']){
                            $openid = Db::name('wd_xcx_qq_user')->where('suid', $order['suid'])->value('openid');
                            tpl_send($uniacid, 8, $openid, $order['source'], $order['qx_formid'], $jsons);
                        }else if($order['source'] == 5 && $order['qx_formid'] ){
                            $openid = Db::name('wd_xcx_toutiao_user')->where('suid', $order['suid'])->value('openid');
                            tpl_send($uniacid, 8, $openid, $order['source'], $order['qx_formid'], $jsons);
                        }
                    }
                }

                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                $this->error('取消失败，' . $e->getMessage());
            }

            $this->success('取消成功');

        }
    }
    //需要使用证书的请求
    function postXmlSSLCurl($xml,$url,$second=30,$uniacid)
    {
        $ch = curl_init();
        //超时时间
        curl_setopt($ch,CURLOPT_TIMEOUT,$second);
        //这里设置代理，如果有的话
        //curl_setopt($ch,CURLOPT_PROXY, '8.8.8.8');
        //curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
        //设置header
        curl_setopt($ch,CURLOPT_HEADER,FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
        //设置证书
        //使用证书：cert 与 key 分别属于两个.pem文件
        //默认格式为PEM，可以注释
        $SSLCERT_PATH = ROOT_PATH.'public/Cert/'.$uniacid.'/qq_apiclient_cert.pem';//证书路径
        $SSLKEY_PATH =  ROOT_PATH.'public/Cert/'.$uniacid.'/qq_apiclient_key.pem';//证书路径
        curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
        curl_setopt($ch,CURLOPT_SSLCERT, $SSLCERT_PATH);
        //默认格式为PEM，可以注释
        curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
        curl_setopt($ch,CURLOPT_SSLKEY, $SSLKEY_PATH);
        //post提交方式
        curl_setopt($ch,CURLOPT_POST, true);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$xml);
        $data = curl_exec($ch);
        //返回结果
        if($data){
            curl_close($ch);
            return $data;
        }
        else {
            $error = curl_errno($ch);
            echo "curl出错，错误码:$error"."<br>";
            curl_close($ch);
            return false;
        }
    }
    private function xmlToArray($xml) {  

        //禁止引用外部xml实体   

        libxml_disable_entity_loader(true);  

        $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);  

        $val = json_decode(json_encode($xmlstring), true);  

        return $val;  

    } 
    //拒绝修改
    public function refusemodify(){
        $id = input('id');
        $uniacid = input('appletid');
        Db::name('wd_xcx_order')->where("uniacid",$uniacid)->where("id",$id)->update(array("flag"=>1));

        $info = Db::name("wd_xcx_order")->where("id", $id)->find();
        if($info['source'] == 1){
            $openid = Db::name("wd_xcx_user")->where("suid", $info['suid'])->value('openid');
            $jsons = [
                'order_id' => $info['order_id'],
                'fprice' => $info['price'],
                'msg' => "退款被拒",
            ];
            $jsons = serialize($jsons);
            sendSubscribe($uniacid, 3, $openid, $jsons);
        }
        $this->success('拒绝取消成功!');
    }

    //秒杀商品基础设置
    public function baseSet(){
        $uniacid = input('appletid');
        $app = new Applet;
        $appInfo = $app->getAppInfo();
        $this->assign('applet', $appInfo);

        $base = model('ImsSudu8PageFlashsaleSet') ->getBaseSet();

        $this->assign('baseInfo', $base);
        $this->assign('catestyle', $base['catestyle']);

        return $this->fetch('baseSet');
    }

    //保存基础设置
    public function setSave(){
        $uniacid = input('appletid');

        $send_mail = input('send_mail');
        $order_close_time = input('order_close_time');
        $queren_time = input('queren_time');
        $catestyle = input('catestyle');

        $baseObj = model('ImsSudu8PageFlashsaleSet');
        $base = $baseObj ->getBaseSet();
        if(!$send_mail){
            $data['send_mail'] = 1;
        }else{
            $data['send_mail'] = $send_mail;
        }
        if(!$order_close_time && $order_close_time !== 0){
            $data['order_close_time'] = 30;
        }else{
            $data['order_close_time'] = $order_close_time;
        }
        if(!$queren_time && $queren_time !== 0){
            $data['queren_time'] = 7;
        }else{
            $data['queren_time'] = $queren_time;
        }
        if(!$catestyle){
            $data['catestyle'] = 2;
        }else{
            $data['catestyle'] = $catestyle;
        }

        if($base){
            $res = $baseObj ->save($data, ['uniacid'=>$uniacid]);
        }else{
            $data['uniacid'] = $uniacid;
            $res = $baseObj ->save($data);
        }

        if($res){
            $this->success('基础设置成功！');
        }else{
            $this->success('基础设置失败！');
        }
    }

    // 向我的上级返钱操作
    public function dopagegivemoney($uniacid,$suid,$orderid){
        $guiz = Db::name('wd_xcx_fx_gz')->where('uniacid',$uniacid)->find();
        $order = Db::name('wd_xcx_fx_ls')->where('uniacid',$uniacid)->where('order_id',$orderid)->find();
        Db::name('wd_xcx_fx_ls')->where('order_id',$orderid)->update(array("flag"=>2));
        $me = Db::name('wd_xcx_superuser')->where('uniacid',$uniacid)->where('id',$order['suid'])->find();
        $me_p_get_money = $me['p_get_money'];
        $me_p_p_get_money = $me['p_p_get_money'];
        $me_p_p_p_get_money = $me['p_p_p_get_money'];
        // 启动一级分销提成
        if($guiz['fx_cj'] == 1){
            if($order['parent_id']){
                $puser = Db::name('wd_xcx_superuser')->where('uniacid',$uniacid)->where('id',$order['parent_id'])->find();
                $kdata = array(
                    "fx_allmoney" => $puser['fx_allmoney'] + $order['parent_id_get'],
                    "fx_money" => $puser['fx_money'] + $order['parent_id_get']
                );
                Db::name('wd_xcx_superuser')->where('uniacid',$uniacid)->where('id',$order['parent_id'])->update($kdata);
                // 我给我的父级贡献的钱
                $new_p_get_money = $me_p_get_money*1 + $order['parent_id_get']*1;  
                Db::name('wd_xcx_superuser')->where('uniacid',$uniacid)->where('id',$order['suid'])->update(array("p_get_money" => $new_p_get_money));
            }
        }
        // 启动二级分销提成
        if($guiz['fx_cj'] == 2){
            if($order['parent_id']){
                $puser = Db::name('wd_xcx_superuser')->where('uniacid',$uniacid)->where('id',$order['parent_id'])->find();
                $kdata = array(
                    "fx_allmoney" => $puser['fx_allmoney'] + $order['parent_id_get'],
                    "fx_money" => $puser['fx_money'] + $order['parent_id_get']
                );
                Db::name('wd_xcx_superuser')->where('uniacid',$uniacid)->where('id',$order['parent_id'])->update($kdata);
                // 我给我的父级贡献的钱
                $new_p_get_money = $me_p_get_money*1 + $order['parent_id_get']*1;
                Db::name('wd_xcx_superuser')->where('uniacid',$uniacid)->where('id',$order['suid'])->update(array("p_get_money" => $new_p_get_money));
            }
            if($order['p_parent_id']){
                $puser = Db::name('wd_xcx_superuser')->where('uniacid',$uniacid)->where('id',$order['p_parent_id'])->find();
                $kdata = array(
                    "fx_allmoney" => $puser['fx_allmoney'] + $order['p_parent_id_get'],
                    "fx_money" => $puser['fx_money'] + $order['p_parent_id_get']
                );
                Db::name('wd_xcx_superuser')->where('uniacid',$uniacid)->where('id',$order['p_parent_id'])->update($kdata);
                // 我给我的父级的父级贡献的钱
                $new_p_p_get_money = $me_p_p_get_money*1 + $order['p_parent_id_get']*1;
                Db::name('wd_xcx_superuser')->where('uniacid',$uniacid)->where('id',$order['suid'])->update(array("p_p_get_money" => $new_p_p_get_money));
            }
        }
        // 启动三级分销提成
        if($guiz['fx_cj'] == 3){
            if($order['parent_id']){
                $puser = Db::name('wd_xcx_superuser')->where('uniacid',$uniacid)->where('id',$order['parent_id'])->find();
                $kdata = array(
                    "fx_allmoney" => $puser['fx_allmoney'] + $order['parent_id_get'],
                    "fx_money" => $puser['fx_money'] + $order['parent_id_get']
                );
                Db::name('wd_xcx_superuser')->where('uniacid',$uniacid)->where('id',$order['parent_id'])->update($kdata);
                // 我给我的父级贡献的钱
                $new_p_get_money = $me_p_get_money*1 + $order['parent_id_get']*1;
                Db::name('wd_xcx_superuser')->where('uniacid',$uniacid)->where('id',$order['suid'])->update(array("p_get_money" => $new_p_get_money));
            }
            if($order['p_parent_id']){
                $puser = Db::name('wd_xcx_superuser')->where('uniacid',$uniacid)->where('id',$order['p_parent_id'])->find();
                $kdata = array(
                    "fx_allmoney" => $puser['fx_allmoney'] + $order['p_parent_id_get'],
                    "fx_money" => $puser['fx_money'] + $order['p_parent_id_get']
                );
                Db::name('wd_xcx_superuser')->where('uniacid',$uniacid)->where('id',$order['p_parent_id'])->update($kdata);
                // 我给我的父级的父级贡献的钱
                $new_p_p_get_money = $me_p_p_get_money*1 + $order['p_parent_id_get']*1;
                Db::name('wd_xcx_superuser')->where('uniacid',$uniacid)->where('id',$order['suid'])->update(array("p_p_get_money" => $new_p_p_get_money));
            }
            if($order['p_p_parent_id']){
                $puser =  Db::name('wd_xcx_superuser')->where('uniacid',$uniacid)->where('id',$order['p_p_parent_id'])->find();
                $kdata = array(
                    "fx_allmoney" => $puser['fx_allmoney'] + $order['p_p_parent_id_get'],
                    "fx_money" => $puser['fx_money'] + $order['p_p_parent_id_get']
                );
                Db::name('wd_xcx_superuser')->where('uniacid',$uniacid)->where('id',$order['p_p_parent_id'])->update($kdata);
                // 我给我的父级的父级的附近贡献的钱
                $new_p_p_p_get_money = $me_p_p_p_get_money*1 + $order['p_p_parent_id_get']*1;
                Db::name('wd_xcx_superuser')->where('uniacid',$uniacid)->where('id',$order['suid'])->update(array("p_p_p_get_money" => $new_p_p_p_get_money));
            }
        }
    }



    public function delallm(){
        $appletid = input("appletid");
        $array1=input('mpros');
        $arr=explode(',',$array1);
        $res = Db::name('wd_xcx_products')->where("uniacid",$appletid)->where('id',"in",$arr)->delete();
        if($res){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }

}