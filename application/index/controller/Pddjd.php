<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\View;
class Pddjd extends Controller
{	


	/* 获取热门商品 */
	public function test(){
		include_once 'pdd.php';
		$pdd = new \pdd();

		$res = $pdd->getTopGoodsList(1);

		dump($res);

	}

	/* 获取商品分类 */
	public function cates(){
		include_once 'pdd.php';
		$pdd = new \pdd();

		$res = $pdd->getGoodsCates();
		dump($res);die;
		if($res){
			foreach ($res['goods_cats_get_response']['goods_cats_list'] as $key => $value) {
				//Db::name('wd_xcx_pdd_cate') ->insert($value);
			}
		}

		dump($res);

	}



	// 商品详情   3505534750
	public function goods_detail(){
		include_once 'pdd.php';
		$pdd = new \pdd();

		$arr = $pdd->getGoodsDetail('3505534750');
		dump($arr);
	}


	// 商品详情   3505534750
	public function gobuyurl(){
		include_once 'pdd.php';
		$pdd = new \pdd();

		$arr = $pdd->getUrlById('3505534750');
		dump($arr);
	}


	/* 京粉精选 */
	public function jdgoods(){
		include_once 'jd.php';
		$jd = new \jd();

		$arrs = $jd->jingfenQuery(1);
		dump($arrs);
	}

	/*推广位*/
	public function jdpid(){
		include_once 'jd.php';
		$jd = new \jd();

		$arrs = $jd ->getCreatePid('项目162');
		dump($arrs);
	}

	/*类目*/
	public function jdcate(){
		include_once 'jd.php';
		$jd = new \jd();

		$arrs = $jd ->categoryGet();
		foreach ($arrs['data'] as $key => $value) {
			$data = [
				'level' => $value['grade'],
				'cat_name' => $value['name'],
				'cat_id' => $value['id'],
				'parent_cat_id' => $value['parentId']
			];
			// Db::name('wd_xcx_jd_cate') ->insert($data);
		}
	}

	/*推广链接*/
	public function getUrl(){
		include_once 'jd.php';
		$jd = new \jd();

		$arrs = $jd ->getUrlByUrl('item.jd.com/46009739071.html', 'http://coupon.m.jd.com/coupons/show.action?key=fac01ce644d742749326dc65e6a2f7ca&roleId=20067196&to=haoting.jd.com');
		dump($arrs);
	}

	/*获取商品详情*/
    public function getGoods(){
        include_once 'jd.php';
        $jd = new \jd(202);

        $res = $jd ->getGoodsDetail('46584381737');
        dump($res);
    }

    /*订单*/
    public function getOrder(){
        include_once 'jd.php';
        $jd = new \jd(202);

        $time = date('YmdHi', '1561515625');


        $res = $jd ->getOrderList('222222');
        dump($res);die;
    }

    /*京东查询商品*/
    public function searchGoods(){
    	include_once 'jd.php';
        $jd = new \jd(202);

        $key = [
        	'keyword' => '短裤',
        ];

        $res = $jd ->searchGoodsBuyKey($key);
        dump($res);

    }
}