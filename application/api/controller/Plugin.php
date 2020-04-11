<?php

namespace app\api\controller;

use Decode\Decode\Decode;
use phpmail\Phpmailer;
use think\Controller;
use think\Db;
use think\Session;
use think\Request;
use think\Exception;
use think\cache\driver\Redis;

class Plugin extends Controller
{
    //根据来源获取对应的头像，昵称
    private function getnameandavatar($source, $uniacid, $suid)
    {      // source  1  微信小程序  2  支付宝小程序  3 H5
        $info = [];
        if ($source == 1) {
            $info = Db::name('wd_xcx_user')->where('suid', $suid)->where('uniacid', $uniacid)->field('nickname, avatar')->find();
            if ($info) {
                $info['nickname'] = rawurldecode($info['nickname']);
            }

        } elseif ($source == 2) {
            $info = Db::name('wd_xcx_ali_user')->where('suid', $suid)->where('uniacid', $uniacid)->field('nick_name as nickname, avatar')->find();
        } elseif ($source == 3) {
            $info = Db::name('wd_xcx_superuser')->where('id', $suid)->where('uniacid', $uniacid)->field('phone as nickname')->find();
            $info['nickname'] = substr_replace($info['nickname'], '***', 3, 6);
            if ($info) {
                $info['avatar'] = ROOT_HOST . '/image/pay_list_person.png';
            }
        } elseif ($source == 4) {
            $info = Db::name('wd_xcx_baidu_user')->where('suid', $suid)->where('uniacid', $uniacid)->field('nickname, avatar')->find();
        } elseif ($source == 5) {
            $info = Db::name('wd_xcx_toutiao_user')->where('suid', $suid)->where('uniacid', $uniacid)->field('nickname, avatar')->find();
        } elseif ($source == 6) {
            $info = Db::name('wd_xcx_qq_user')->where('suid', $suid)->where('uniacid', $uniacid)->field('nickname, avatar')->find();
        }

        if (!$info) {
            $info = [
                'nickname' => '用户**',
                'avatar' => ROOT_HOST . '/image/pay_list_person.png'
            ];
        }
        return $info;
    }

    /**
     * [ddlb 订单轮播]
     * @return [type] [description]
     */
    public function ddlb(){
        $uniacid = input("uniacid");
        $source = input("source");
        $c = Db::name('wd_xcx_duo_products_order')->where("uniacid", $uniacid)->order('creattime', 'desc')->limit(5)->field('suid, creattime')->select();
        foreach ($c as $key => $value) {
            $wx = $this->getnameandavatar($source, $uniacid, $value['suid']);
            $name1 = $wx['nickname'];
            $c[$key]['wx_name'] = mb_substr($name1, 0, 1, 'utf-8') . "**";
            $c[$key]['wx_avatar'] = $wx['avatar'];
        }

        $array2 = $c;
        $date = array_column($array2, 'creattime');
        array_multisort($date, SORT_ASC, $array2);
        $result['count'] = $c;
        return json_encode(['data' => $result]);
    }

    /**
     * [feedback 万能表单]
     * @return [type] [description]
     */
    public function feedback(){
        $uniacid = input("uniacid");
        $sourceid = input("sourceid");
        if(!$sourceid){
            $data['forminfo']['tp_text'] = [];
            return json_encode(['data' => $data]);
        }
        $sourceid = explode(':', $sourceid)[1];
        $data['forminfo'] = Db::name('wd_xcx_formlist')->where("uniacid", $uniacid)->where("id", $sourceid)->find();
        if ($data['forminfo']) {
            $data['forminfo']['tp_text'] = unserialize($data['forminfo']['tp_text']);
            $tp_text = [];
            if ($data['forminfo']['tp_text']) {
                if ($data['forminfo']['tp_text']) {
                    foreach ($data['forminfo']['tp_text'] as $key => &$res) {
                        if ($key > 0) {
                            $tp_key = $key - 1;
                            if ($res['required'] == true) {
                                $tp_text[$tp_key]['ismust'] = 1;
                            } else {
                                $tp_text[$tp_key]['ismust'] = 0;
                            }
                            $tp_text[$tp_key]['name'] = $res['label'];
                            if ($res['field_type'] == '单行文本') {
                                $tp_text[$tp_key]['type'] = 0;
                                $tp_text[$tp_key]['tp_text'][0]['yval'] = 0;
                            } else if ($res['field_type'] == '多行文本') {
                                $tp_text[$tp_key]['type'] = 1;
                                $tp_text[$tp_key]['tp_text'] = '';
                            } else if ($res['field_type'] == '多选' || $res['field_type'] == '单选') {
                                if ($res['field_type'] == '多选') {
                                    $tp_text[$tp_key]['type'] = 3;
                                } else {
                                    $tp_text[$tp_key]['type'] = 4;
                                }
                                foreach ($res['field_options']['options'] as $key1 => &$rec1) {
                                    $rec1['yval'] = $rec1['label'];
                                    unset($rec1['label']);
                                }
                                $tp_text[$tp_key]['tp_text'] = $res['field_options']['options'];
                            } else if ($res['field_type'] == '下拉选') {
                                $tp_text[$tp_key]['type'] = 2;
                                $tp_text[$tp_key]['tp_text'] = [];
                                foreach ($res['field_options']['options'] as $key2 => &$rec2) {
                                    array_push($tp_text[$tp_key]['tp_text'], $rec2['label']);
                                }
                            } else if ($res['field_type'] == '日期') {
                                $tp_text[$tp_key]['type'] = 7;
                            } else if ($res['field_type'] == '时间') {
                                $tp_text[$tp_key]['type'] = 11;
                            } else if ($res['field_type'] == '图片') {
                                $tp_text[$tp_key]['type'] = 5;
                                $tp_text[$tp_key]['tp_text'] = $res['field_options']['maxpic'];
                                $tp_text[$tp_key]['z_val'] = array();
                            } else if ($res['field_type'] == '手机号') {
                                $tp_text[$tp_key]['type'] = 0;
                                $tp_text[$tp_key]['tp_text'][0]['yval'] = 1;
                            } else if ($res['field_type'] == '身份证') {
                                $tp_text[$tp_key]['type'] = 0;
                                $tp_text[$tp_key]['tp_text'][0]['yval'] = 7;
                            }
                            $tp_text[$tp_key]['val'] = '';
                        }
                    }
                }
            }
            $data['forminfo']['tp_text'] = $tp_text;
        }else{
            $data['forminfo']['tp_text'] = [];
        }
        return json_encode(['data' => $data]);
    }

    /**
     * [msmk 秒杀模块]
     * @return [type] [description]
     */
    public function msmk(){
        $uniacid = input("uniacid");
        $sourceid = input("sourceid");
        if(!$sourceid){
            $list = [];
            return json_encode(['data' => $list]);
        }
        $sourceid = explode(':', $sourceid)[1];
        $count = input("count");
        $con_type = input("con_type");
        $con_key = input("con_key");

        $where = "";
        if ($con_type == 1 && $con_key == 1) {
            $where = 'ORDER BY id DESC';
        }
        if ($con_type == 2 && $con_key == 1) {
            $where = 'AND type_x=1 ORDER BY id DESC';
        }
        if ($con_type == 3 && $con_key == 1) {
            $where = 'AND type_y=1 ORDER BY id DESC';
        }
        if ($con_type == 4 && $con_key == 1) {
            $where = 'AND type_i=1 ORDER BY id DESC';
        }
        if ($con_type == 1 && $con_key == 2) {
            $where = 'ORDER BY hits DESC';
        }
        if ($con_type == 2 && $con_key == 2) {
            $where = 'AND type_x=1 ORDER BY hits DESC';
        }
        if ($con_type == 3 && $con_key == 2) {
            $where = 'AND type_y=1 ORDER BY hits DESC';
        }
        if ($con_type == 4 && $con_key == 2) {
            $where = 'AND type_i=1 ORDER BY hits DESC';
        }
        if ($con_type == 1 && $con_key == 3) {
            $where = 'ORDER BY num DESC';
        }
        if ($con_type == 2 && $con_key == 3) {
            $where = 'AND type_x=1 ORDER BY num DESC';
        }
        if ($con_type == 3 && $con_key == 3) {
            $where = 'AND type_y=1 ORDER BY num DESC';
        }
        if ($con_type == 4 && $con_key == 3) {
            $where = 'AND type_i=1 ORDER BY num DESC';
        }
        $prefix = config('database.prefix');
        $list = Db::query("SELECT title,thumb,id,`desc`,price,market_price,sale_num,sale_tnum,sale_time,sale_end_time,pro_kc FROM {$prefix}wd_xcx_products WHERE `uniacid` = {$uniacid} AND `type` = 'showPro' AND `is_more` = 0 AND `flag` = 1 AND `is_sale`=0 AND  (`cid` = {$sourceid} or `pcid` = {$sourceid} ) " . $where . " LIMIT 0,{$count}");
        if ($list) {
            foreach ($list as $kk => $vv) {
                $list[$kk]['linkurl'] = "/pagesFlashSale/showPro/showPro?id=" . $vv['id'];
                $list[$kk]['linktype'] = "page";
                $list[$kk]['sale_num'] = $vv['sale_num'] + $vv['sale_tnum'];
                if (strpos($vv['thumb'], 'http') === false && $vv['thumb'] != "") {
                    $list[$kk]['thumb'] = remote($uniacid, $vv['thumb'], 1);
                }
            }
        } else {
            $list = [];
        }
        return json_encode(['data' => $list]);
    }

    /**
     * [pt 拼团]
     * @return [type] [description]
     */
    public function pt(){
        $uniacid = input("uniacid");
        $sourceid = input("sourceid");
        if(!$sourceid){
            $list = [];
            return json_encode(['data' => $list]);
        }
        $sourceid = explode(':', $sourceid)[1];
        $count = input("count");
        $con_type = input("con_type");
        $con_key = input("con_key");
        $source = input('source');

        $where = "";
        if ($con_type == 1 && $con_key == 1) {
            $where = 'ORDER BY id DESC';
        }
        if ($con_type == 2 && $con_key == 1) {
            $where = 'AND type_x=1 ORDER BY id DESC';
        }
        if ($con_type == 3 && $con_key == 1) {
            $where = 'AND type_y=1 ORDER BY id DESC';
        }
        if ($con_type == 4 && $con_key == 1) {
            $where = 'AND type_i=1 ORDER BY id DESC';
        }
        if ($con_type == 1 && $con_key == 2) {
            $where = 'ORDER BY hits DESC';
        }
        if ($con_type == 2 && $con_key == 2) {
            $where = 'AND type_x=1 ORDER BY hits DESC';
        }
        if ($con_type == 3 && $con_key == 2) {
            $where = 'AND type_y=1 ORDER BY hits DESC';
        }
        if ($con_type == 4 && $con_key == 2) {
            $where = 'AND type_i=1 ORDER BY hits DESC';
        }
        if ($con_type == 1 && $con_key == 3) {
            $where = 'ORDER BY num DESC';
        }
        if ($con_type == 2 && $con_key == 3) {
            $where = 'AND type_x=1 ORDER BY num DESC';
        }
        if ($con_type == 3 && $con_key == 3) {
            $where = 'AND type_y=1 ORDER BY num DESC';
        }
        if ($con_type == 4 && $con_key == 3) {
            $where = 'AND type_i=1 ORDER BY num DESC';
        }
        $prefix = config('database.prefix');

        $list = Db::query("SELECT * FROM {$prefix}wd_xcx_pt_pro WHERE `uniacid` = {$uniacid} AND `show_pro`=0 AND `cid` = {$sourceid} " . $where . " LIMIT 0,{$count}");
        if ($list) {
            foreach ($list as $kk => $vv) {
                $list[$kk]['linkurl'] = "/pagesPt/products/products?id=" . $vv['id'];
                $list[$kk]['linktype'] = "page";
                $team_user = Db::name('wd_xcx_pt_share')->where('uniacid', $uniacid)->where('pid', $vv['id'])->field('suid')->limit(0, 6)->select();
                $list[$kk]['team_user'] = $team_user ? count($team_user) : 0;
                if ($team_user) {
                    foreach ($team_user as &$vsi) {
                        $vsi['avatar'] = $this->getnameandavatar($source, $uniacid, $vsi['suid'])['avatar'];
                        unset($vsi['suid']);
                    }
                }
                $list[$kk]['team_user_avatars'] = $team_user ? $team_user : [];
                $list[$kk]['tgr'] = Db::name('wd_xcx_pt_share')->where('uniacid', $uniacid)->where('pid', $vv['id'])->sum('join_count');
                $list[$kk]['tgr'] = $list[$kk]['tgr'] ? $list[$kk]['tgr'] : 0;
                if (strpos($vv['thumb'], 'http') === false && $vv['thumb'] != "") {
                    $list[$kk]['thumb'] = remote($uniacid, $vv['thumb'], 1);
                }
            }
        } else {
            $list = [];
        }
        return json_encode(['data' => $list]);
    }

    /**
     * [cases 图文组]
     * @return [type] [description]
     */
    public function cases(){
        $uniacid = input("uniacid");
        $sourceid = input("sourceid");
        if(!$sourceid){
            $list = [];
            return json_encode(['data' => $list]);
        }
        $sourceid = explode(':', $sourceid)[1];
        $count = input("count");
        $con_type = input("con_type");
        $con_key = input("con_key");
        $showtype = input('showtype');

        $where = "";
        if ($con_type == 1 && $con_key == 1) {
            $where = 'ORDER BY id DESC';
        }
        if ($con_type == 2 && $con_key == 1) {
            $where = 'AND type_x=1 ORDER BY id DESC';
        }
        if ($con_type == 3 && $con_key == 1) {
            $where = 'AND type_y=1 ORDER BY id DESC';
        }
        if ($con_type == 4 && $con_key == 1) {
            $where = 'AND type_i=1 ORDER BY id DESC';
        }
        if ($con_type == 1 && $con_key == 2) {
            $where = 'ORDER BY hits DESC';
        }
        if ($con_type == 2 && $con_key == 2) {
            $where = 'AND type_x=1 ORDER BY hits DESC';
        }
        if ($con_type == 3 && $con_key == 2) {
            $where = 'AND type_y=1 ORDER BY hits DESC';
        }
        if ($con_type == 4 && $con_key == 2) {
            $where = 'AND type_i=1 ORDER BY hits DESC';
        }
        if ($con_type == 1 && $con_key == 3) {
            $where = 'ORDER BY num DESC';
        }
        if ($con_type == 2 && $con_key == 3) {
            $where = 'AND type_x=1 ORDER BY num DESC';
        }
        if ($con_type == 3 && $con_key == 3) {
            $where = 'AND type_y=1 ORDER BY num DESC';
        }
        if ($con_type == 4 && $con_key == 3) {
            $where = 'AND type_i=1 ORDER BY num DESC';
        }
        $prefix = config('database.prefix');
        if($showtype == 1){
            $list = Db::query("SELECT id,title,thumb,type FROM {$prefix}wd_xcx_products WHERE (`type` = 'showPic' or `type` = 'showArt') AND `uniacid` = {$uniacid} AND `flag` = 1 AND `is_sale`=0 AND (`cid` = {$sourceid} or `pcid` = {$sourceid} ) " . $where);
        }else{
            $list = Db::query("SELECT id,title,thumb,type FROM {$prefix}wd_xcx_products WHERE (`type` = 'showPic' or `type` = 'showArt') AND `uniacid` = {$uniacid} AND `flag` = 1 AND `is_sale`=0 AND (`cid` = {$sourceid} or `pcid` = {$sourceid} ) " . $where . " LIMIT 0,{$count}");
        }
        if ($list) {
            foreach ($list as $kk => $vv) {
                $list[$kk]['linkurl'] = "/pages/" . $vv['type'] . "/" . $vv['type'] . "?id=" . $vv['id'];
                if (strpos($vv['thumb'], 'http') === false && $vv['thumb'] != "") {
                    $list[$kk]['thumb'] = remote($uniacid, $vv['thumb'], 1);
                }
            }
        } else {
            $list = [];
        }
        return json_encode(['data' => $list]);
    }

    /**
     * [listdesc 文章列表]
     * @return [type] [description]
     */
    public function listdesc(){
        $uniacid = input("uniacid");
        $sourceid = input("sourceid");
        if(!$sourceid){
            $list = [];
            return json_encode(['data' => $list]);
        }
        $sourceid = explode(':', $sourceid)[1];
        $count = input("count");
        $con_type = input("con_type");
        $con_key = input("con_key");

        $where = "";
        if ($con_type == 1 && $con_key == 1) {
            $where = 'ORDER BY id DESC';
        }
        if ($con_type == 2 && $con_key == 1) {
            $where = 'AND type_x=1 ORDER BY id DESC';
        }
        if ($con_type == 3 && $con_key == 1) {
            $where = 'AND type_y=1 ORDER BY id DESC';
        }
        if ($con_type == 4 && $con_key == 1) {
            $where = 'AND type_i=1 ORDER BY id DESC';
        }
        if ($con_type == 1 && $con_key == 2) {
            $where = 'ORDER BY hits DESC';
        }
        if ($con_type == 2 && $con_key == 2) {
            $where = 'AND type_x=1 ORDER BY hits DESC';
        }
        if ($con_type == 3 && $con_key == 2) {
            $where = 'AND type_y=1 ORDER BY hits DESC';
        }
        if ($con_type == 4 && $con_key == 2) {
            $where = 'AND type_i=1 ORDER BY hits DESC';
        }
        if ($con_type == 1 && $con_key == 3) {
            $where = 'ORDER BY num DESC';
        }
        if ($con_type == 2 && $con_key == 3) {
            $where = 'AND type_x=1 ORDER BY num DESC';
        }
        if ($con_type == 3 && $con_key == 3) {
            $where = 'AND type_y=1 ORDER BY num DESC';
        }
        if ($con_type == 4 && $con_key == 3) {
            $where = 'AND type_i=1 ORDER BY num DESC';
        }
        $prefix = config('database.prefix');
        $list = Db::query("SELECT * FROM {$prefix}wd_xcx_products WHERE `type` = 'showArt' AND `is_sale`=0 AND  `uniacid` = {$uniacid} AND `flag` = 1 AND (`cid` = {$sourceid} or `pcid` = {$sourceid} ) " . $where . " LIMIT 0,{$count}");
        if ($list) {
            foreach ($list as $kk => $vv) {
                $list[$kk]['linktype'] = 'page';
                if ($vv['music_art_info'] != "") {
                    $music_art_info = unserialize($vv['music_art_info']);
                    if ($music_art_info['art_price'] == "") {
                        $list[$kk]['art_price'] = 0;
                    } else {
                        $list[$kk]['art_price'] = $music_art_info['art_price'];
                    }
                    if ($music_art_info['music_price'] == "") {
                        $list[$kk]['music_price'] = 0;
                    } else {
                        $list[$kk]['music_price'] = $music_art_info['music_price'];
                    }
                }
                $count = Db::name("wd_xcx_comment")->where("uniacid", $uniacid)->where("aid", $vv['id'])->count();
                $list[$kk]['comments'] = $count;
                $list[$kk]['linkurl'] = "/pages/showArt/showArt?id=" . $vv['id'];
                if (strpos($vv['thumb'], 'http') === false && $vv['thumb'] != "") {
                    $list[$kk]['thumb'] = remote($uniacid, $vv['thumb'], 1);
                }
                $list[$kk]['ctime'] = date('Y年m月d日', $vv['ctime']);
                $list[$kk]['likes'] = Db::name('wd_xcx_likes') ->where('uniacid', $uniacid) ->where('cid', $vv['id']) ->where('type', 'showArt') ->count();
            }
        } else {
            $list = [];
        }
        return json_encode(['data' => $list]);
    }

    /**
     * [notice 公告]
     * @return [type] [description]
     */
    public function notice(){
        $uniacid = input("uniacid");
        $sourceid = input("sourceid");
        if(!$sourceid){
            $list = [];
            return json_encode(['data' => $list]);
        }
        $sourceid = explode(':', $sourceid)[1];
        $count = input("count");
        $noticedata = input('noticedata');
        $prefix = config('database.prefix');
        $list = Db::query("SELECT id,title FROM {$prefix}wd_xcx_products WHERE `uniacid` = {$uniacid} AND `type` = 'showArt' AND `is_sale`=0 AND (`cid` = {$sourceid} or `pcid` = {$sourceid} ) ORDER BY id DESC LIMIT 0,{$count}");
        if ($list) {
            foreach ($list as $kk => $vv) {
                if ($noticedata == 0) {
                    $list[$kk]['linktype'] = 'page';
                }
                $list[$kk]['linkurl'] = "/pages/showArt/showArt?id=" . $vv['id'];
            }
        } else {
            $list = [];
        }
        return json_encode(['data' => $list]);
    }

    /**
     * [goods 产品模块]
     * @return [list] [返回商品数组]
     */
    public function goods(){
        $uniacid = input("uniacid");
        $sourceid = input("sourceid");
        if(!$sourceid){
            $list = [];
            return json_encode(['data' => $list]);
        }
        $sourceid = explode(':', $sourceid)[1];
        $list = [];

        $cate = Db::name('wd_xcx_cate')->where('uniacid', $uniacid)->where('id', $sourceid)->where('statue', 1)->find();
        $pro_ids = [];
        if ($cate) {
            if ($cate['cid'] == 0) {
                $cate_two = Db::name('wd_xcx_cate')->where([
                    'uniacid' => $uniacid,
                    'cid' => $sourceid,
                    'statue' => 1
                ])->column('id');
                array_push($cate_two, $sourceid);
                $pro_ids = Db::name('wd_xcx_cate_pro')->where([
                    'uniacid' => $uniacid,
                    'cate_id' => ['in', $cate_two]
                ])->column('pid');
                $pro_ids = array_unique($pro_ids);
            } else {
                $pro_ids = Db::name('wd_xcx_cate_pro')->where([
                    'uniacid' => $uniacid,
                    'cate_id' => $sourceid
                ])->column('pid');
            }

            $count = input("count");
            $con_type = input("con_type");
            $con_key = input("con_key");

            $where = "";
            if ($con_type == 1 && $con_key == 1) {
                $where = 'ORDER BY id DESC';
            }
            if ($con_type == 2 && $con_key == 1) {
                $where = 'AND type_x=1 ORDER BY id DESC';
            }
            if ($con_type == 3 && $con_key == 1) {
                $where = 'AND type_y=1 ORDER BY id DESC';
            }
            if ($con_type == 4 && $con_key == 1) {
                $where = 'AND type_i=1 ORDER BY id DESC';
            }
            if ($con_type == 1 && $con_key == 2) {
                $where = 'ORDER BY hits DESC';
            }
            if ($con_type == 2 && $con_key == 2) {
                $where = 'AND type_x=1 ORDER BY hits DESC';
            }
            if ($con_type == 3 && $con_key == 2) {
                $where = 'AND type_y=1 ORDER BY hits DESC';
            }
            if ($con_type == 4 && $con_key == 2) {
                $where = 'AND type_i=1 ORDER BY hits DESC';
            }
            if ($con_type == 1 && $con_key == 3) {
                $where = 'ORDER BY num DESC';
            }
            if ($con_type == 2 && $con_key == 3) {
                $where = 'AND type_x=1 ORDER BY num DESC';
            }
            if ($con_type == 3 && $con_key == 3) {
                $where = 'AND type_y=1 ORDER BY num DESC';
            }
            if ($con_type == 4 && $con_key == 3) {
                $where = 'AND type_i=1 ORDER BY num DESC';
            }
            $prefix = config('database.prefix');
            $pro_ids = implode(',', $pro_ids);
            if($pro_ids){
                $sql = "SELECT * FROM {$prefix}wd_xcx_products WHERE uniacid = " . $uniacid . " and type = 'showProMore' and is_sale = 0 and id in (" . $pro_ids . ") " . $where . " limit 0, " . $count;
                $list = Db::query($sql);
                if ($list) {
                    foreach ($list as $kk => $vv) {
                        if ($vv['type'] == "showPro" && $vv['is_more'] == 0) {
                            $list[$kk]['linkurl'] = "/pages/showPro/showPro?id=" . $vv['id'];

                            $items_orders = Db::name('wd_xcx_order')->where('pid', $vv['id'])->where('uniacid', $uniacid)->select();
                            $items_pro_num = 0;
                            if ($items_orders) {
                                foreach ($items_orders as $rec) {
                                    $items_pro_num += $rec['num'];
                                }
                            }
                            $list[$kk]['sale_num'] = $list[$kk]['sale_num'] + $items_pro_num;
                        } else if ($vv['is_more'] == 1) {
                            $list[$kk]['linkurl'] = "/pages/showPro_lv/showPro_lv?id=" . $vv['id'];
                            $list[$kk]['sale_num'] = $list[$kk]['sale_num'] + $list[$kk]['sale_tnum'];
                        } else {
                            if ($vv['use_more'] == 1) {
                                $values = Db::name("wd_xcx_duo_products_type_value")->where("pid", $vv['id'])->select();
                                foreach ($values as $ks => $vs) {
                                    $list[$kk]['sale_num'] = $list[$kk]['sale_num'] + $vs['salenum'] + $vs['vsalenum'];
                                }
                            } else {
                                $list[$kk]['sale_num'] = $list[$kk]['sale_num'] + $list[$kk]['sale_tnum'];
                            }

                            $list[$kk]['linkurl'] = "/pages/showProMore/showProMore?id=" . $vv['id'];
                        }
                        if (strpos($vv['thumb'], 'http') === false && $vv['thumb'] != "") {
                            $list[$kk]['thumb'] = remote($uniacid, $vv['thumb'], 1);
                        }
                    }
                }
            }
        }
        return json_encode(['data' => $list]);
    }

    /**
     * [reserve 预约预定]
     * @return [type] [description]
     */
    public function reserve(){
        $uniacid = input("uniacid");
        $sourceid = input("sourceid");
        if(!$sourceid){
            $list = [];
            return json_encode(['data' => $list]);
        }
        $sourceid = explode(':', $sourceid)[1];
        $count = input("goodsnum");
        $con_type = input("con_type");
        $con_key = input("con_key");

        $where = "";
        if ($con_type == 1 && $con_key == 1) {
            $where = 'ORDER BY id DESC';
        }
        if ($con_type == 4 && $con_key == 1) {
            $where = 'AND type_i=1 ORDER BY id DESC';
        }
        if ($con_type == 1 && $con_key == 2) {
            $where = 'ORDER BY hits DESC';
        }
        if ($con_type == 4 && $con_key == 2) {
            $where = 'AND type_i=1 ORDER BY hits DESC';
        }
        if ($con_type == 1 && $con_key == 3) {
            $where = 'ORDER BY num DESC';
        }
        if ($con_type == 4 && $con_key == 3) {
            $where = 'AND type_i=1 ORDER BY num DESC';
        }

        $prefix = config('database.prefix');
        $list = Db::query("SELECT * FROM {$prefix}wd_xcx_products WHERE `uniacid` = {$uniacid}  AND `is_sale`= 0 and `is_more` = 1 AND (`cid` = {$sourceid} or `pcid` = {$sourceid} ) " . $where . " LIMIT 0,{$count}");
        if ($list) {
            foreach ($list as $kk => $vv) {
                $list[$kk]['linkurl'] = "/pagesReserve/proDetail/proDetail?id=" . $vv['id'];
                $list[$kk]['sale_num'] = ($list[$kk]['sale_num'] + $list[$kk]['sale_tnum']) < 0 ? 0 : ($list[$kk]['sale_num'] + $list[$kk]['sale_tnum']);
                if (strpos($vv['thumb'], 'http') === false && $vv['thumb'] != "") {
                    $list[$kk]['thumb'] = remote($uniacid, $vv['thumb'], 1);
                }
            }
        }else{
            $list = [];
        }
        return json_encode(['data' => $list]);
    }

    /**
     * [yhq 优惠券]
     * @param [type] $[counts] [style下counts]
     * @return [type] [description]
     */
    public function yhq(){
        $uniacid = input('uniacid');
        $counts_yhq = input('counts');

        $coupon = Db::name("wd_xcx_coupon")->where("flag", 1)->where("uniacid", $uniacid)->order('num desc')->limit(0, $counts_yhq)->select();
        foreach ($coupon as $kz => $vz) {
            $coupon[$kz]['linktype'] = 'page';
        }
        return json_encode(['data' => $coupon]);
    }

    /**
     * [xnlf 虚拟来访]
     * @return [fwl] [params下fwl]
     * @return [backgroundimg] [params下backgroundimg]
     * @return [avatars] [模块下avatars]
     */
    public function xnlf(){
        $uniacid = input("uniacid");
        $fwl = input("fwl");
        $backgroundimg = input("backgroundimg");
        $data = [];
        $source = input('source');

        $num = Db::name('wd_xcx_base')->where("uniacid", $uniacid)->find();
        $data['fwl'] = $fwl + $num['visitnum'] * 1;
        if ($backgroundimg != "") {
            $data['backgroundimg'] = remote($uniacid, $backgroundimg, 1);
        }
        $avatars = [];
        $user_arr = Db::name("wd_xcx_superuser")->where("uniacid", $uniacid)->order("id desc")->field('id')->select();
        $iii = 0;
        foreach ($user_arr as $key => $value) {
            if ($iii < 5) {
                $userinfo = $this->getnameandavatar($source, $uniacid, $value['id']);
                if ($userinfo['avatar']) {
                    array_push($avatars, $userinfo['avatar']);
                    $iii++;
                }
            } else {
                break;
            }
        }
        $data['avatars'] = $avatars;
        return json_encode(['data' => $data]);
    }

    /**
     * [multiple 多商户模块]
     * @param [type] $[showtype] [style下的showtype]
     * @param [type] $[counts] [params下的counts]
     * @param [type] $[content_type] [params下的content_type]
     * @return [type] [description]
     */
    public function multiple(){
        $uniacid = input('uniacid');
        $showtype = input('showtype');
        $tjnum = input('counts');
        $content_type = input('content_type');

        $data = [];
        if (!isset($showtype)) {
            $data['showtype'] = 0;
        }

        if ($content_type == 1) {
            $orderby = " createtime desc ";
        }
        if ($content_type == 2) {
            $orderby = " star desc ";
        }
        $prefix = config('database.prefix');
        $store['storeHot'] = Db::query("SELECT id,uniacid,name,logo,hot FROM {$prefix}wd_xcx_shops_shop WHERE `status` = 1 and `flag` = 1 AND `uniacid` = {$uniacid} AND `hot` = 1 ORDER BY " . $orderby . " LIMIT 0," . $tjnum);
        $num2 = count($store['storeHot']);
        for ($i = 0; $i < $num2; $i++) {
            if (stristr($store['storeHot'][$i]['logo'], 'http')) {
                $store['storeHot'][$i]['logo'] = $store['storeHot'][$i]['logo'];
            } else {
                $store['storeHot'][$i]['logo'] = remote($uniacid, $store['storeHot'][$i]['logo'], 1);
            }
        }
        return json_encode(['data' => $store]);
    }

    /**
     * [mlist 多商户列表]
     * @return [type] [description]
     */
    public function mlist(){
        $uniacid = input('uniacid');
        $viewcount = input('viewcount');
        $content_type = input('content_type');
        $store['catelist'] = Db::name("wd_xcx_shops_cate")->where("uniacid", $uniacid)->where('flag', 1)->field("id,num,name")->order("num desc")->select();
        if ($viewcount) {
            $tjnum = $viewcount;
        } else {
            $tjnum = 4;
        }

        if ($content_type) {
            $content_type = $content_type;
        } else {
            $content_type = 1;
        }

        if ($content_type == 1) {
            $orderby = " createtime desc ";
        }
        if ($content_type == 2) {
            $orderby = " star desc ";
        }
        $prefix = config('database.prefix');
        $store['storeHot'] = Db::query("SELECT id,uniacid,name,logo,hot,tel,address FROM {$prefix}wd_xcx_shops_shop WHERE `status` = 1 and  `flag` = 1 AND `uniacid` = {$uniacid} AND `hot` = 1 ORDER BY " . $orderby . " LIMIT 0," . $tjnum);
        $num2 = count($store['storeHot']);
        for ($i = 0; $i < $num2; $i++) {
            if (stristr($store['storeHot'][$i]['logo'], 'http')) {
                $store['storeHot'][$i]['logo'] = $store['storeHot'][$i]['logo'];
            } else {
                $store['storeHot'][$i]['logo'] = remote($uniacid, $store['storeHot'][$i]['logo'], 1);
            }
        }

        return json_encode(['data' => $store]);
    }

    /**
     * [bargain 砍价]
     * @return [type] [description]
     */
    public function bargain(){
        $uniacid = input('uniacid');
        $sourceid = input('sourceid');
        $count = input('goodsnum');
        $con_type = input("con_type");
        $con_key = input("con_key");
        $where = "";

        if(!$sourceid){
            $result = [];
            $result['data']['data2'] = [];
            $result['data']['data'] = [];
            return json_encode($result);
        }

        if (isset($sourceid) && $sourceid != "") {
            $sourceid = explode(':', $sourceid)[1];

            if ($con_type == 1 && $con_key == 1) {
                $where = 'ORDER BY id DESC';
            }
            if ($con_type == 2 && $con_key == 1) {
                $where = 'AND hot=1 ORDER BY id DESC';
            }
            if ($con_type == 1 && $con_key == 3) {
                $where = 'ORDER BY num DESC';
            }
            if ($con_type == 2 && $con_key == 3) {
                $where = 'AND hot=1 ORDER BY num DESC';
            }
            $prefix = config('database.prefix');
            $sql = "SELECT title,thumb,id,descs,price,miniPrice,virtualSaleVolume,realSaleVolume,activeBinTime,activeEndTime,kc FROM {$prefix}wd_xcx_bargain_pro WHERE `uniacid` = {$uniacid} AND activeBinTime <= " . time() . " AND activeEndTime >= " . time() . " AND status = 1 AND cateId = {$sourceid} " . $where . " LIMIT 0,{$count}";

            $data2 = [];
            $list = Db::query($sql);
            if ($list) {
                foreach ($list as $kk => $vv) {
                    $list[$kk]['saleVolume'] = intval($vv['virtualSaleVolume']) + intval($vv['realSaleVolume']);
                    $list[$kk]['sale_end_time'] = $vv['activeEndTime'];
                    $list[$kk]['thumb'] = remote($uniacid, $vv['thumb'], 1);
                    $list[$kk]['linkurl'] = "/pagesBargain/bargain_pro/bargain_pro?id=" . $vv['id'];
                    $list[$kk]['linktype'] = "page";
                    if ($list[$kk]['saleVolume'] > 0 && $vv['kc'] > 0) {
                        $list[$kk]['sale_percent'] = round($list[$kk]['saleVolume'] / intval($vv['kc']), 2) * 100;
                    } else {
                        $list[$kk]['sale_percent'] = 0;
                    }

                }
                $data2 = $list;
                $data = $list;
            }
        } else {
            $data2 = [];
            $data = [];
        }
        $result = [];
        $result['data']['data2'] = $list; 
        $result['data']['data'] = $list; 
        return json_encode($result);
    }

    /**
     * [personlist 名片]
     * @return [type] [description]
     */
    public function personlist(){
        $uniacid = input('uniacid');
        $count = input('goodsnum');

        $data = Db::name('wd_xcx_staff')->where('uniacid', $uniacid)->order('sort desc')->limit($count)->select();
        foreach ($data as $kkk => $vvv) {
            $data[$kkk]['linktype'] = 'page';
            if (strpos($vvv['pic'], 'http') === false && $vvv['pic'] != "") {
                $data[$kkk]['pic'] = remote($uniacid, $data[$kkk]['pic'], 1);
            }
            $data[$kkk]['score'] = intval($vvv['score']);
        }
        return json_encode(['data' => $data]);
    } 

    /**
     * [supply 供求关系]
     * @return [type] [description]
     */
    public function supply(){
        $uniacid = input('uniacid');
        $count = input('newsnum');
        $data_type = input('data_types');
        $supply = input('supply');

        if ($data_type == 1) {
            $where_type = '';
        } else if ($data_type == 2) {
            $where_type = 'fid = 1';
        } else if ($data_type == 3) {
            $where_type = 'fid = 2';
        }

        if ($supply == 1) {
            $where_from = '';
        } else if ($supply == 2) {
            $where_from = 'hot = 1';
        } else if ($supply == 3) {
            $where_from = 'stick = 1';
        }

        $supplydata = Db::name('wd_xcx_supply_release')->where('uniacid', $uniacid)->where($where_type)->where($where_from)->order('hot asc, stick asc, createtime desc')->where('shenhe', 1)->limit($count)->select();
        foreach ($supplydata as $key => $svalue) {
            $supplydata[$key]['linkurl'] = "/pagesPluginSupply/page/page?rid=" . $svalue['id'];
            if ($svalue['img']) {
                $supplydata[$key]['thumb'] = remote($uniacid, unserialize($svalue['img'])[0], 1);
            } else {
                $supplydata[$key]['thumb'] = remote($uniacid, '/image/nopic.jpg', 1);
            }
        }
        return json_encode(['data' => $supplydata]);
    }

    /**
     * [video 视频]
     * @return [type] [description]
     */
    public function video(){
        $uniacid = input('uniacid');
        $videourl = input('videourl');
//        $poster = input('poster');
        if ($videourl) {
            if (strpos($videourl, ".mp4") !== false || strpos($videourl, ".MP4") !== false) {

            } else {
                if (preg_match("/^(http:\/\/|https:\/\/).*$/", $videourl)) {
                    include 'videoInfo.php';
                    $videoInfo = new videoInfo();
                    $videodata = $videoInfo->getVideoInfo($videourl);
                    $videourl = $videodata['url'];
                } else {
                    $videourl = '';
                }
            }
        }else{
            $videourl = '';
        }

//        if($poster){
//            $poster = remote($uniacid, $poster, 1);
//        }

        return json_encode(['data' => ['videourl' => $videourl]]);
    }
}
