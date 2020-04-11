<?php
use think\Controller;
use think\Db;
class jd extends Controller {
    protected $appkey;  //网站推广 appkey
    protected $secretkey;   //网站推广 secretkey
    protected $siteid;   // 站点ID 网站推广ID
    protected $positionid;  //推广位ID
    protected $config;
    protected $key; // 授权key 60天有效期
    protected $unionId;   //联盟ID
    public $api = "https://router.jd.com/api?";
    public $api_union = "http://api.xixi05.cn/api/";

    public function __construct ($uniacid) {
        $config = Db::name('wd_xcx_external_config') ->find(1);
        if(!$config){
            $this->error('请联系管理员完善推广基础配置！');
        }else{
            if(!$config['jd_appkey'] || !$config['jd_secretkey']){
                $this->error('请联系管理员完善推广基础配置->京东参数配置！');
            }
        }
        $this->config = [
            'jd_appkey' => $config['jd_appkey'],
            'jd_secretkey' => $config['jd_secretkey'],
            'jd_siteid' => $config['jd_siteid'],
            'jd_key' => $config['jd_key'],
            'jd_unionId' => $config['jd_unionId'],
        ];
        if($uniacid != -1 && $uniacid){
            $app = Db::name('wd_xcx_applet') ->where('id', $uniacid) ->field('jd_id') ->find();

            if(!$app['jd_id']){
                $this->error('请联系管理员完善推广基础配置->京东推广位！');
            }else{
                $this->config['jd_positionid'] = $app['jd_id'];
                $this->positionid = $this->config['jd_positionid'];
            }
        }

        $this->appkey = $this->config['jd_appkey'];
        $this->secretkey = $this->config['jd_secretkey'];
        $this->siteid = $this->config['jd_siteid'];

        $this->key = $this->config['jd_key'];
        $this->unionId = $this->config['jd_unionId'];
    }

    /* 类目查询 */
    public function categoryGet() {
        $params = array(
            'req' => array(
                'parentId' => 0,
                'grade' => 0
            )
        );
        $data = array(
            'method' => 'jd.union.open.category.goods.get',
            'app_key' => $this->appkey,
            'access_token' => '',
            'timestamp' => time(),
            'v' => '1.0',
            'sign_method' => 'md5',
            'param_json' => json_encode($params),
            'format' => 'json'
        );
        $data['sign'] = $this->_getSign($data);
        $query = http_build_query($data);
        $url = $this->api . $query;
        $res = $this->_requestPost($url, $data);
        // $query = http_build_query($data);
        // $url = $this->api . $query;
        // $res = ihttp_get($url);
        $res = json_decode($res, true);
        $result = json_decode($res['jd_union_open_category_goods_get_response']['result'], true);
        return $result;
        if($res['code']!=200) {
            return false;
        }
        $res = $res['content'];
        $arr = json_decode($res,true);
       
        $arr = $arr['jd_union_open_category_goods_get_response'];
        if($arr['code']==0) {
            $arr = json_decode($arr['result'],true);
            if($arr['code']==200) {
                return $arr['data'];
            }
            return false;
        }
        return false;
    }

    /*根据条件查询商品*/
    public function searchGoodsBuyKey($key){
        $params = array(
            'goodsReqDTO' => array(
            )
        );
        if(count($key)>0){
            if(isset($key['keyword'])){
                $params['goodsReqDTO']['keyword'] = $key['keyword'];
            }
            if(isset($key['cid1'])){
                $params['goodsReqDTO']['cid1'] = $key['cid1'];
            }

            if(isset($key['commissionShareStart']) && isset($key['commissionShareEnd'])){
                $params['goodsReqDTO']['commissionShareStart'] = $key['commissionShareStart'];
                $params['goodsReqDTO']['commissionShareEnd'] = $key['commissionShareEnd'];
            }

            if(isset($key['pricefrom']) && isset($key['commissionShareEnd'])){
                $params['goodsReqDTO']['pricefrom'] = $key['pricefrom'];
                $params['goodsReqDTO']['priceto'] = $key['priceto'];
            }

            $params['goodsReqDTO']['pageSize'] = $key['pageSize'];
            $params['goodsReqDTO']['pageIndex'] = $key['pageIndex'];

        }else{
            $params['goodsReqDTO']['pageSize'] = 20;
        }
        
        $data = array(
            'method' => 'jd.union.open.goods.query',
            'app_key' => $this->appkey,
            'access_token' => '',
            'timestamp' => date('Y-m-d H:i:s', time()),
            'v' => '1.0',
            'sign_method' => 'md5',
            'param_json' => json_encode($params),
            'format' => 'json'
        );
        // if(count($key)>0){

        // }
        
        $data['sign'] = $this->_getSign($data);
        $query = http_build_query($data);
        $url = $this->api . $query;
        $res = $this->_requestPost($url, $data);
        $res = json_decode($res, true);
        $res = json_decode($res['jd_union_open_goods_query_response']['result'], true);
        if(isset($res['data'])){
            return $res['data'];
        }else{
            return null;
        }
        if($res['code']!=200) {
            return false;
        }
    }


    public function searchGoods ($page=1,$size=20,$keyword='',$cid1=false,$skuids=false,$coupon=false) {
        $query = array(
            'key' => $this->key,
        );
        $params = array(
            'goodsReqDTO' => array(
                'pageIndex' => $page,
                'pageSize' => $size,
                'sortName' => 'price',
                'sort' => 'asc'
            )
        );
        if($coupon) {
            $params['goodsReqDTO']['isCoupon'] = 1;
        }
        if($cid1) {
            $params['goodsReqDTO']['cid1'] = $cid1;
        }
        if($keyword) {
            $params['goodsReqDTO']['keyword'] = '短裤';
        }
        if($skuids) {
            $skuarr = explode(',',$skuids);
            $params['goodsReqDTO']['skuIds'] = $skuarr;
        }

        $url = $this->api_union . "goodsQuery?".http_build_query($query);
        dump($url);
        dump($params);die;
        $params = http_build_query($params);
        $res = $this->_requestPost($url,$params);
        dump($res);die;
        if($res['code']!=200) {
            return false;
        }
        $res = $res['content'];
        $arr = json_decode($res,true);
        return $arr['data'];
    }

    /* 转链 */
    public function promotionByunionid ($materialId,$couponUrl,$pid) {
        $query = array(
            'key' => $this->key
        );
       
        $params = array(
            'promotionCodeReq' => array(
                'positionId' => $pid,
            )
        );
        if($materialId) {
            $params['promotionCodeReq']['materialId'] = $materialId;
        }
      
        if($couponUrl) {
            $params['promotionCodeReq']['couponUrl'] = $couponUrl;
        }
        $url = $this->api_union . "promotionByunionid?".http_build_query($query);
        $res = ihttp_post($url,$params);
        if($res['code']!=200) {
            return false;
        }
        $res = $res['content'];
        $arr = json_decode($res,true);
        return $arr['data'];
    }

    /* 京粉精选 */
    public function jingfenQuery ($page=1,$size=20) {
        $params = array(
            'goodsReq' => array(
                'eliteId' => 1,
                'pageIndex' => $page,
                'pageSize' => $size,
                'sortName' => 'price',
                'sort' => 'asc'
            )
        );
        $data = array(
            'method' => 'jd.union.open.goods.jingfen.query',
            'app_key' => $this->appkey,
            'access_token' => '',
            'timestamp' => time(),
            'v' => '1.0',
            'sign_method' => 'md5',
            'param_json' => json_encode($params),
            'format' => 'json'
        );
        $data['sign'] = $this->_getSign($data);
        $query = http_build_query($data);
        $url = $this->api . $query;
        $res = $this->_requestPost($url, $data);
        $res = json_decode($res, true);
        return json_decode($res['jd_union_open_goods_jingfen_query_response']['result'], true);
        if($res['code']!=200) {
            return false;
        }
        $res = $res['content'];
        $arr = json_decode($res,true);
        $arr = $arr['jd_union_open_goods_jingfen_query_response'];
        if($arr['code']==0) {
            $arr = json_decode($arr['result'],true);
            if($arr['code']==200) {
                return $arr['data'];
            }
            return false;
        }
        return false;
    }


    /* 创建生成推广位(需要申请) */
    public function getCreatePid ($name) {
        $params = array(
            'positionReq' => array(
                'unionId' => $this->unionId,
                'key' => $this->key,
                'unionType' => 1,
                'type' => 1,
                'spaceNameList' => ["项目162"],
                'siteId' => $this->siteid,
            )
        );
        $data = array(
            'method' => 'jd.union.open.order.query',
            'app_key' => $this->appkey,
            'access_token' => '',
            'timestamp' => time(),
            'v' => '1.0',
            'sign_method' => 'md5',
            'param_json' => json_encode($params),
            'format' => 'json'
        );
        $data['sign'] = $this->_getSign($data);
        $query = http_build_query($data);
        $res = $this->_requestPost($this->api, $data);
        dump(json_decode($res, true));die;
        $url = $this->api . $query;
        $res = ihttp_get($url);
        if($res['code']!=200) {
            return false;
        }
        $res = $res['content'];
        $arr = json_decode($res,true);
        /*  [jd_union_open_promotion_common_get_response] => Array
        (
            [result] => {"code":2001104,"data":"10","message":"商品不在推广中","requestId":"21922_0b115ef6_jtfebj6d_14612575"}
            [code] => 0
        ) */
        $arr = $arr['jd_union_open_promotion_common_get_response'];
        if($arr['code']==0) {
            $arr = json_decode($arr['result'],true);
            if($arr['code']==200) {
                return $arr['data']['clickURL'];
            }
            return false;
        }
        return false;
    }


    /* 通过普通商品链接获取推广链接 */
    public function getUrlByUrl ($url, $ext, $curl=false) {
        $params = array(
            'promotionCodeReq' => array(
                'materialId' => $url,
                'siteId' => $this->siteid,
                'positionId' =>$this->positionid,
                'ext1' => $ext,
            )
        );
        if($curl){
            $params['promotionCodeReq']['couponUrl'] =  $curl;
        }
        $data = array(
            'method' => 'jd.union.open.promotion.common.get',
            'app_key' => $this->appkey,
            'access_token' => '',
            'timestamp' => date('Y-m-d H:i:s', time()),
            'v' => '1.0',
            'sign_method' => 'md5',
            'param_json' => json_encode($params),
            'format' => 'json'
        );
        $data['sign'] = $this->_getSign($data);
        $query = http_build_query($data);
        $url = $this->api . $query;
        $res = $this->_requestPost($url, $data);
        $res = json_decode($res, true);
        return json_decode($res['jd_union_open_promotion_common_get_response']['result'], true);
        $res = ihttp_get($url);
        if($res['code']!=200) {
            return false;
        }
        $res = $res['content'];
        $arr = json_decode($res,true);
        /*  [jd_union_open_promotion_common_get_response] => Array
        (
            [result] => {"code":2001104,"data":"10","message":"商品不在推广中","requestId":"21922_0b115ef6_jtfebj6d_14612575"}
            [code] => 0
        ) */
        $arr = $arr['jd_union_open_promotion_common_get_response'];
        if($arr['code']==0) {
            $arr = json_decode($arr['result'],true);
            if($arr['code']==200) {
                return $arr['data']['clickURL'];
            }
            return false;
        }
        return false;
    }

    /* 获取单个商品详情 */
    public function getGoodsDetail ($id) {
        $params = array(
            'skuIds' => $id
        );
        $data = array(
            'method' => 'jd.union.open.goods.promotiongoodsinfo.query',
            'app_key' => $this->appkey,
            'access_token' => '',
            'timestamp' => date('Y-m-d H:i:s', time()),
            'v' => '1.0',
            'sign_method' => 'md5',
            'param_json' => json_encode($params),
            'format' => 'json'
        );
        $data['sign'] = $this->_getSign($data);
        $query = http_build_query($data);
        $url = $this->api . $query;
        $res = $this->_requestPost($url, $data);
        $res = json_decode($res, true);
        $res = json_decode($res['jd_union_open_goods_promotiongoodsinfo_query_response']['result'], true);
        if(isset($res['data'])){
            return $res['data'][0];
        }else{
            return false;
        }
        $res = ihttp_get($url);
        if($res['code']!=200) {
            return false;
        }
        $res = $res['content'];
        $arr = json_decode($res,true);
        $result = $arr['jd_union_open_goods_promotiongoodsinfo_query_response']['result'];
        $arr = json_decode($result,true);
        if($arr['code']==200) {
            return $arr['data'][0];
        }
        return false;
    }

    public function getOrderList ($pn, $time) {
        $params = array(
            'orderReq' => array(
                'pageNo' => $pn,
                'pageSize' => 500,
                'type' => 1,
                'time' => $time,
            )
        );
        $data = array(
            'method' => 'jd.union.open.order.query',
            'app_key' => $this->appkey,
            'access_token' => '',
            'timestamp' => date('Y-m-d H:i:s', time()),
            'v' => '1.0',
            'sign_method' => 'md5',
            'param_json' => json_encode($params),
            'format' => 'json'
        );
        $data['sign'] = $this->_getSign($data);
        $query = http_build_query($data);
        $url = $this->api . $query;
        $res = $this->_requestPost($url, $data);
        $res = json_decode($res, true);
        $res = json_decode($res['jd_union_open_order_query_response']['result'], true);
        if(isset($res['data'])){
            return $res;
        }else{
            $res['data'] = [];
            return $res;
        }
        if($res['code']!=200) {
            return false;
        }
        $res = $res['content'];
        $arr = json_decode($res,true);
        /* [result] => {"code":200,"data":[{"ext1":"25","finishTime":0,"orderEmt":2,"orderId":90197850853,"orderTime":1552977336000,"parentId":0,"payMonth":0,"plus":0,"popId":0,"skuList":[{"actualCosPrice":0.00,"actualFee":0.00,"cid1":1320,"cid2":1584,"cid3":2679,"commissionRate":3.00,"estimateCosPrice":28.80,"estimateFee":0.86,"ext1":"25","finalRate":100.00,"frozenSkuNum":0,"payMonth":0,"pid":"","popId":0,"positionId":1742158240,"price":28.80,"siteId":1731810747,"skuId":4914930,"skuName":"魔法士 干脆面 巴西烤肉味 48包 方便面干吃面 26g*48","skuNum":1,"skuReturnNum":0,"subSideRate":90.00,"subUnionId":"","subsidyRate":10.00,"traceType":2,"unionAlias":"","unionTag":"00000000","unionTrafficGroup":5,"validCode":16}],"unionId":1001406745,"validCode":16}],"hasMore":false,"message":"success","requestId":"21922_0b115ef6_jtfen6bj_14637435"}
            [code] => 0 */
        $result = $arr['jd_union_open_order_query_response']['result'];
        $result = json_decode($result,true);
        if($result['code']==200) {
            return $result['data'];
        }
        return false;
    }

    protected function _getSign($Obj) {
        foreach ($Obj as $k => $v) {
            if($v) {
                $Parameters[$k] = $v;
            }
        }
        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $buff = '';
        foreach ($Parameters as $k => $v) {
            $buff .= $k . $v ;
        }
      
        $String = $this->secretkey.$buff.$this->secretkey;
        $String = md5($String);
        $result_ = strtoupper($String);
        return $result_;
    }

    public function getOrderStatusText ($status) {
        $arr = array(
            '-1' => '未知',
            '2' => '无效-拆单',
            '3' => '无效-取消',
            '4' => '无效-京东帮帮主订单',
            '5' => '无效-账号异常',
            '6' => '无效-赠品类目不返佣',
            '7' => '无效-校园订单',
            '8' => '无效-企业订单',
            '9' => '无效-团购订单',
            '10' => '无效-开增值税专用发票订单',
            '11' => '无效-乡村推广员下单',
            '12' => '无效-自己推广自己下单(废弃)',
            '13' => '无效-违规订单',
            '14' => '无效-来源与备案网址不符',
            '15' => '待付款',
            '16' => '已付款',
            '17' => '已完成',
            '18' => '已结算'
        );
        return $arr[$status];
    }

    //不带报头的curl
    public function _requestPost($url, $data, $ssl = true)
    {
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
        $response = curl_exec($curl);
        if (false === $response) {
            echo '<br>', curl_error($curl), '<br>';
            return false;
        }
        curl_close($curl);
        return $response;
    }
}