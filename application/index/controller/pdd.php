<?php
// namespace app\index\controller;
use think\Controller;
use think\Db;
class pdd extends Controller {
    protected $client_id;
    protected $client_secret;
    public $api = "https://gw-api.pinduoduo.com/api/router";
    public $config;
    public function __construct ($uniacid) {
        $config = Db::name('wd_xcx_external_config') ->find(1);
        if(!$config){
          $this->error('请联系管理员完善推广基础配置！');
        }else{
          if(!$config['pdd_client_id'] || !$config['pdd_client_secret']){
            $this->error('请联系管理员完善推广基础配置->拼多多参数配置！');
          }
        }
        if($uniacid != -1 && $uniacid){
            $app = Db::name('wd_xcx_applet') ->where('id', $uniacid) ->field('p_id') ->find();

            if(!$app['p_id']){
                $this->error('请联系管理员完善推广基础配置->拼多多推广位！');
            }else{
                $this->config['pdd_pid'] = $app['p_id'];
            }
        }
        $this->config['pdd_client_id'] = $config['pdd_client_id'];
        $this->config['pdd_client_secret'] = $config['pdd_client_secret'];


        $this->client_id = $this->config['pdd_client_id'];
        $this->client_secret = $this->config['pdd_client_secret'];
    }

     /* 签名 */
     protected function _getSign($Obj) {
      foreach ($Obj as $k => $v) {
          if($v!=='') {
              $Parameters[$k] = $v;
          }
      }
      ksort($Parameters);
      $buff = '';
      foreach ($Parameters as $k => $v) {
          $buff .= $k . $v ;
      }
      $String = $this->client_secret.$buff.$this->client_secret;
      $String = md5($String);
      $result_ = strtoupper($String);
      return $result_;
  }

    public function test () {
        $data = array(
            'type' => 'pdd.ddk.goods.pid.generate',
            'data_type' => 'JSON',
            'timestamp' => '1551620368',
            'client_id' => $this->client_id,
            'number' => '1',
        );
       echo  $this->_getSign($data);
    }

    /* 创建推广位 */
    public function getCreatePid ($name, $num=1) {
      $data = array(
          'type' => 'pdd.ddk.goods.pid.generate',
          'data_type' => 'JSON',
          'timestamp' => '1551620368',
          'client_id' => $this->client_id,
          'number' => $num,
          'p_id_name_list' => $name
      );
      $data['sign'] = $this->_getSign($data);
      $res = $this->_requestPost($this->api,$data);
      $res = json_decode($res, true);
      return $res['p_id_generate_response']['p_id_list'][0]['p_id'];
      if($res['code']!=200) {
        return false;
      }
      $json = $res['content'];
      $arr = json_decode($json,true);
      return $arr['goods_detail_response']['goods_details'][0];
    }


    /* 获取商品详情 */
    public function getGoodsDetail ($id) {
      $ids = array($id);
      $data = array(
        'type' => 'pdd.ddk.goods.detail',
        'data_type' => 'JSON',
        'timestamp' => time(),
        'client_id' => $this->client_id,
        'goods_id_list' => json_encode($ids)
      );
      $data['sign'] = $this->_getSign($data);
      $res = $this->_requestPost($this->api,$data);
      $res = json_decode($res, true);
      if(isset($res['error_response'])){
          return null;
      }else{
          return $res['goods_detail_response']['goods_details'][0];
      }
      if($res['code']!=200) {
        return false;
      }
      $json = $res['content'];
      $arr = json_decode($json,true);
      return $arr['goods_detail_response']['goods_details'][0];
    }

    /* 获取所有推广位 */
    public function getPidList () {
      $data = array(
        'type' => 'pdd.ddk.goods.pid.query',
        'data_type' => 'JSON',
        'timestamp' => time(),
        'client_id' => $this->client_id,
       /*  'page' => '1',
        'size' => '10' */
      );
      $data['sign'] = $this->_getSign($data);
      $res = ihttp_post($this->api,$data);
      _pr($res);
    }
    /* 获取推广链接 */
    public function getUrlById ($id,$p_id,$custom_parameters=false) {
      $arr = array(
        $id
      );
      $data = array(
        'type' => 'pdd.ddk.goods.promotion.url.generate',
        'data_type' => 'JSON',
        'timestamp' => time(),
        'client_id' => $this->client_id,
        'p_id' => $this->config['pdd_pid'],
        'goods_id_list' => json_encode($arr),
        'generate_we_app' => 'true',
        'p_id' => $p_id
      );
      if($custom_parameters) {
        $data['custom_parameters'] = $custom_parameters;
      }
      $data['sign'] = $this->_getSign($data);
      $res = $this->_requestPost($this->api,$data);
      $res = json_decode($res, true);

      return $res['goods_promotion_url_generate_response']['goods_promotion_url_list'];
      
      if($res['code']!=200) {
        return false;
      }
      $json = $res['content'];
      $arr = json_decode($json,true);
      return $arr['goods_promotion_url_generate_response']['goods_promotion_url_list'][0];
    }

    /* 获取相关订单 */
    public function getOrderList ($start,$end) {
      $data = array(
        'type' => 'pdd.ddk.order.list.increment.get',
        'data_type' => 'JSON',
        'timestamp' => time(),
        'client_id' => $this->client_id,
        'start_update_time' => $start,
        'end_update_time' => $end
      );
      $data['sign'] = $this->_getSign($data);
      $res = $this->_requestPost($this->api,$data);
      $res = json_decode($res, true);
      if(isset($res['error_response'])){
          return null;
      }else{
          return $res['order_list_get_response']['order_list'];
      }
      $json = $res['content'];
      $arr = json_decode($json,true);
      return $arr['order_list_get_response'];
    }

    /* 获取订单详情 */
    public function getOrderDetail ($order_sn) {
      $data = array(
        'type' => 'pdd.ddk.order.detail.get',
        'data_type' => 'JSON',
        'timestamp' => time(),
        'client_id' => $this->client_id,
        'order_sn' => $order_sn
      );
      $data['sign'] = $this->_getSign($data);
      $res = ihttp_post($this->api,$data);
      $json = $res['content'];
      $arr = json_decode($json,true);
      return $arr['order_detail_response'];
    }

    /* 获取热门商品 */
    public function getTopGoodsList ($page,$size=20,$sort_type=1) {
      $offset = ($page-1)*$size;
      $data = array(
        'type' => 'pdd.ddk.top.goods.list.query',
        'data_type' => 'JSON',
        'timestamp' => time(),
        'client_id' => $this->client_id,
        'offset' => $offset,
        'limit' => $size,
        'sort_type' => $sort_type
      );
      $data['sign'] = $this->_getSign($data);
      $res = $this->_requestPost($this->api,$data);
      $res = json_decode($res, true);
      return $res['top_goods_list_get_response']['list'];
      if($res['code']!=200) {
        return false;
      }
      $json = $res['content'];
      $arr = json_decode($json,true);
      return $arr['top_goods_list_get_response']['list'];
    }

    /* 获取商品分类 */
    public function getGoodsCates() {
      $data = array(
        'type' => 'pdd.goods.cats.get',
        'data_type' => 'JSON',
        'timestamp' => time(),
        'client_id' => $this->client_id,
        'parent_cat_id' => 0
      );
      $data['sign'] = $this->_getSign($data);
      $res = $this->_requestPost($this->api,$data);
      return json_decode($res, true);
      if($res['code']!=200) {
        return false;
      }
      $json = $res['content'];
      $arr = json_decode($json,true);
      return $arr['top_goods_list_get_response']['list'];
    }


    /* 搜索商品 */
    public function search ($key,$page=1) {
      $data = array(
        'type' => 'pdd.ddk.goods.search',
        'data_type' => 'JSON',
        'timestamp' => time(),
        'client_id' => $this->client_id,
        'page' => $page,
        'pid' => $this->config['pdd_pid'],
        'sort_type' => 5
      );
      $data = array_merge($data, $key);
      $data['sign'] = $this->_getSign($data);
      $res = $this->_requestPost($this->api,$data);
      $res = json_decode($res, true);
      if(isset($res['error_response'])){
          return null;
      }else{
          return $res['goods_search_response']['goods_list'];
      }
      if($res['code']!=200) {
        return false;
      }
      $json = $res['content'];
      $arr = json_decode($json,true);
      return $arr['goods_search_response']['goods_list'];
    }

    /* 获取主题列表 */
    public function getThemeList ($page=1) {
      $data = array(
        'type' => 'pdd.ddk.theme.list.get',
        'data_type' => 'JSON',
        'timestamp' => time(),
        'client_id' => $this->client_id,
        'page' => $page,
        'page_size' => 10,
      );
      $data['sign'] = $this->_getSign($data);
      $res = ihttp_post($this->api,$data);
      if($res['code']!=200) {
        return false;
      }
      $json = $res['content'];
      $arr = json_decode($json,true);
      return $arr['theme_list_get_response']['theme_list'];
    }

    /* 获取主题商品列表 */
    public function getThemeGoods ($themeid) {
      $data = array(
        'type' => 'pdd.ddk.theme.goods.search',
        'data_type' => 'JSON',
        'timestamp' => time(),
        'client_id' => $this->client_id,
        'theme_id' => $themeid,
      );
      $data['sign'] = $this->_getSign($data);
      $res = ihttp_post($this->api,$data);
      if($res['code']!=200) {
        return false;
      }
      $json = $res['content'];
      $arr = json_decode($json,true);
      return $arr['theme_list_get_response']['goods_list'];
    }

    /* 类目列表 */
    public function getOpt ($id=0) {
      $data = array(
        'type' => 'pdd.goods.opt.get',
        'data_type' => 'JSON',
        'timestamp' => time(),
        'client_id' => $this->client_id,
        'parent_opt_id' => $id,
      );
      $data['sign'] = $this->_getSign($data);
      $res = ihttp_post($this->api,$data);
      if($res['code']!=200) {
        return false;
      }
      $json = $res['content'];
      $arr = json_decode($json,true);
      return $arr['goods_opt_get_response']['goods_opt_list'];
    }

    /* 运营频道
    0, "1.9包邮", 1, "今日爆款", 2, "品牌清仓", 3, "默认商城", 非必填 ,默认是1
    */
   public function getRecommend ($channel=1,$page=1,$size=10) {
      $offset = ($page-1)*$size;
      $data = array(
        'type' => 'pdd.ddk.goods.recommend.get',
        'data_type' => 'JSON',
        'timestamp' => time(),
        'client_id' => $this->client_id,
        'offset' => $offset,
        'limit' =>$size,
        'channel_type' => $channel,
        'pid' => $this->config['pdd_pid']
      );
      $data['sign'] = $this->_getSign($data);
      $res = ihttp_post($this->api,$data);
      if($res['code']!=200) {
        return false;
      }
      $json = $res['content'];
      $arr = json_decode($json,true);
      return $arr['goods_basic_detail_response']['list'];
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