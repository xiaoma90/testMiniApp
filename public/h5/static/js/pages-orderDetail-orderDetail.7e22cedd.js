(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-orderDetail-orderDetail"],{"07b5":function(n,i,e){"use strict";var t=function(){var n=this,i=n.$createElement,e=n._self._c||i;return n.$imgurl?e("div",{staticClass:"bgcolor"},[e("v-uni-view",{staticClass:"order_detail_head",style:{"background-image":"url("+n.$imgurl+"order_detail_head.jpg)","background-size":"100%"}},[0==n.datas.flag?e("v-uni-view",[n._v("订单待付款")]):n._e(),1==n.datas.flag&&1==n.datas.nav?e("v-uni-view",[n._v("订单待发货")]):n._e(),1==n.datas.flag&&2==n.datas.nav?e("v-uni-view",[n._v("订单待消费")]):n._e(),2==n.datas.flag?e("v-uni-view",[n._v("订单已完成")]):n._e(),3==n.datas.flag?e("v-uni-view",[n._v("订单已过期")]):n._e(),4==n.datas.flag?e("v-uni-view",[n._v("订单待收货")]):n._e(),5==n.datas.flag?e("v-uni-view",[n._v("订单已取消")]):n._e(),6==n.datas.flag?e("v-uni-view",[n._v("订单取消中")]):n._e(),7==n.datas.flag?e("v-uni-view",[n._v("退货审核中")]):n._e(),8==n.datas.flag?e("v-uni-view",[n._v("退货成功")]):n._e(),9==n.datas.flag?e("v-uni-view",[n._v("退货失败")]):n._e()],1),n.datas.address>0?[e("v-uni-view",{staticClass:"order_detial_person_info hbj mb10 "},[e("v-uni-view",{staticClass:"order_detail_address_img iconfont icon-x-dizhi2"}),e("v-uni-view",{staticClass:"person_info_left flex1"},[e("v-uni-view",{staticClass:"hbj"},[e("v-uni-view",{staticClass:"recive_person flex1"},[n._v("收货人:"+n._s(n.datas.addressinfo.name))]),e("v-uni-view",{staticClass:"recive_phonenum"},[n._v(n._s(n.datas.addressinfo.mobile))])],1),e("v-uni-view",{staticClass:"recive_address"},[n._v("收货地址："+n._s(n.datas.addressinfo.address)+" "+n._s(n.datas.addressinfo.more_address))])],1)],1)]:n._e(),e("v-uni-view",{staticClass:"order_list_productbox",staticStyle:{background:"#fff",padding:"20rpx"}},[n._l(n.datas.jsondata,function(i,t){return[e("v-uni-view",{key:t+"_0",staticClass:"order_list_product hbj"},[e("v-uni-image",{staticClass:"product_img",attrs:{src:i.proinfo.thumb,mode:"aspectFill"}}),e("v-uni-view",{staticClass:"order_list_product_center"},[e("v-uni-view",{staticClass:"order_product_title2"},[n._v(n._s(i.baseinfo.title))])],1),e("v-uni-view",{staticClass:"flex1"}),e("v-uni-view",[e("v-uni-view",{staticClass:"order_product_price"},[e("v-uni-text",[n._v("￥")]),n._v(n._s(i.proinfo.price))],1),e("v-uni-view",{staticClass:"order_product_count"},[n._v("X"+n._s(i.num))])],1)],1)]})],2),e("v-uni-view",{staticClass:"pricebox mb10"},[e("v-uni-view",{staticClass:"price_single hbj"},[e("v-uni-view",{staticClass:"price_single_left flex1"},[n._v("商品总价")]),e("v-uni-view",{staticClass:"price_single_right"},[n._v("￥"+n._s(n.datas.hjjg))])],1),n.datas["discounts"]>0?[e("v-uni-view",{staticClass:"price_single hbj"},[e("v-uni-view",{staticClass:"price_single_left flex1"},[n._v("折扣")]),e("v-uni-view",{staticClass:"price_single_right"},[n._v(n._s(n.datas["discounts"])+"折")])],1)]:n._e(),n.datas["discounts_price"]>0?[e("v-uni-view",{staticClass:"price_single hbj"},[e("v-uni-view",{staticClass:"price_single_left flex1"},[n._v("折扣优惠")]),e("v-uni-view",{staticClass:"price_single_right"},[n._v("- ￥"+n._s(n.datas["discounts_price"]))])],1)]:n._e(),n.datas["yhInfo_mj"]["money"]>0?[e("v-uni-view",{staticClass:"price_single hbj"},[e("v-uni-view",{staticClass:"price_single_left flex1"},[n._v("满减")]),e("v-uni-view",{staticClass:"price_single_right"},[n._v(n._s(n.datas["yhInfo_mj"]["msg"])+" - ￥"+n._s(n.datas["yhInfo_mj"]["money"]))])],1)]:n._e(),n.datas["yhInfo_yhq"]["money"]>0?[e("v-uni-view",{staticClass:"price_single hbj"},[e("v-uni-view",{staticClass:"price_single_left flex1"},[n._v("优惠券")]),e("v-uni-view",{staticClass:"price_single_right"},[n._v(n._s(n.datas["yhInfo_yhq"]["msg"])+" - ￥"+n._s(n.datas["yhInfo_yhq"]["money"]))])],1)]:n._e(),n.datas["yhInfo_score"]["money"]>0?[e("v-uni-view",{staticClass:"price_single hbj"},[e("v-uni-view",{staticClass:"price_single_left flex1"},[n._v("积分抵扣")]),e("v-uni-view",{staticClass:"price_single_right"},[n._v(n._s(n.datas["yhInfo_score"]["msg"])+" - ￥"+n._s(n.datas["yhInfo_score"]["money"]))])],1)]:n._e(),0==n.datas.sid?e("v-uni-view",{staticClass:"price_single hbj"},[e("v-uni-view",{staticClass:"price_single_left flex1"},[n._v("运费价格")]),e("v-uni-view",{staticClass:"price_single_right"},[n._v("￥"+n._s(n.datas["yhInfo_yunfei"]))])],1):n._e(),e("v-uni-view",{staticClass:"price_single hbj"},[e("v-uni-view",{staticClass:"price_single_left2 flex1"},[n._v("实付款"),e("v-uni-text",[n._v("(另外支付"+n._s(n.datas.payprice)+"元,余额支付"+n._s(n.datas.pay_yue)+"元)")])],1),e("v-uni-view",{staticClass:"price_single_right2"},[e("v-uni-text",[n._v("￥")]),n._v(n._s(n.datas.true_price))],1)],1)],2),""!=n.datas.store_info?e("v-uni-view",{staticClass:"order_info mb10"},[e("v-uni-view",{staticClass:"price_single_left2"},[n._v("自提门店")]),e("v-uni-view",{staticClass:"order_info_ddh_left mt-10"},[n._v("门店名称："),e("v-uni-text",[n._v(n._s(n.datas.store_info["store_name"]))])],1),e("v-uni-view",{staticClass:"order_info_ddh_left mt-10",attrs:{"data-tel":n.datas.store_info["store_tel"]},on:{click:function(i){i=n.$handleEvent(i),n.makePhoneCallC(i)}}},[n._v("门店电话："),e("v-uni-text",[n._v(n._s(n.datas.store_info["store_tel"])),e("v-uni-text",{staticStyle:{"font-size":"20rpx","margin-left":"10rpx"}},[n._v("[点击拨号]")])],1)],1),e("v-uni-view",{staticClass:"order_info_ddh_left mt-10"},[n._v("门店地址："),e("v-uni-text",[n._v(n._s(n.datas.store_info["store_address"]))])],1),e("v-uni-view",{staticClass:"order_info_ddh_left mt-10"},[n._v("营业时间："),e("v-uni-text",[n._v(n._s(""==n.datas.store_info["store_hours"]?"请联系店家":n.datas.store_info["store_hours"]))])],1)],1):n._e(),e("v-uni-view",{staticClass:"order_info mb10"},[e("v-uni-view",{staticClass:"order_info_ddh hbj"},[e("v-uni-view",{staticClass:"order_info_ddh_left flex1"},[n._v("订单号："),e("v-uni-text",[n._v(n._s(n.datas.order_id))])],1)],1),e("v-uni-view",{staticClass:"order_info_ddh_left mt-10"},[n._v("下单时间："),e("v-uni-text",[n._v(n._s(n.datas.creattime))])],1)],1),n.datas.beizhu_val&&"undefined"!=n.datas.beizhu_val?e("v-uni-view",{staticClass:"detail_bz hbj mb10"},[e("v-uni-text",{staticClass:"iconfont icon-x-tishi1"}),n._v("备注："+n._s(n.datas.beizhu_val))],1):n._e(),e("v-uni-view",{staticStyle:{height:"140px"}}),e("v-uni-view",{staticClass:"detail_btnbox hbj"},[e("v-uni-view",{staticClass:"flex1"}),0==n.datas.flag?e("v-uni-view",{staticClass:"detail_btn"},[0==n.datas.sid?[e("v-uni-navigator",{attrs:{url:"/pages/order_more/order_more?orderid="+n.datas.order_id+"&again=1"}},[n._v("立即付款")])]:n._e(),0!=n.datas.sid?[e("v-uni-navigator",{attrs:{url:"/pagesPluginShop/goods_buy/goods_buy?orderid="+n.datas.order_id+"&again=1"}},[n._v("立即付款")])]:n._e()],2):n._e(),1==n.datas.flag?e("v-uni-form",{attrs:{"data-order":n.datas.order_id,"report-submit":"true"},on:{submit:function(i){i=n.$handleEvent(i),n.tuikuan(i)}}},[e("v-uni-button",{staticClass:"detail_btn",staticStyle:{background:"#fff","line-height":"50rpx"},attrs:{"form-type":"submit"}},[n._v("取消订单")])],1):n._e(),1==n.showhx?e("v-uni-view",{staticClass:"hx_con"},[e("v-uni-view",{staticClass:"mask",on:{click:function(i){i=n.$handleEvent(i),n.hxhide(i)}}}),e("v-uni-view",{staticClass:"hexiao"},[e("v-uni-view",{staticClass:"hx_tit"},[n._v("请输入核销密码")]),e("v-uni-view",[e("v-uni-input",{staticClass:"hx_ipt",attrs:{password:"",type:"number",value:n.hxmm},on:{input:function(i){i=n.$handleEvent(i),n.hxmmInput(i)}}})],1),e("v-uni-view",[e("v-uni-button",{staticClass:"hx_btn",on:{click:function(i){i=n.$handleEvent(i),n.hxmmpass(i)}}},[n._v("确认消费")])],1)],1),e("v-uni-view",{staticClass:"hx_c",on:{click:function(i){i=n.$handleEvent(i),n.hxhide(i)}}},[e("v-uni-image",{attrs:{src:n.$imgurl+"c.png",mode:"aspectFit"}})],1)],1):n._e(),1==n.showmask?e("v-uni-view",{staticClass:"mask"}):n._e(),1==n.showmask?e("v-uni-view",{staticClass:"fill_info"},[e("v-uni-view",{staticStyle:{"text-align":"center",color:"#000","margin-bottom":"20rpx"}},[n._v("填写退货信息")]),e("v-uni-picker",{staticStyle:{border:"1px solid #eee",heigth:"60rpx"},attrs:{value:n.index,range:n.kuaidi},on:{change:function(i){i=n.$handleEvent(i),n.bindPickerChange(i)}}},[e("v-uni-view",{staticClass:"picker register_form_view1_input"},[n._v(n._s(n.kuaidi[n.index]))])],1),e("v-uni-view",{staticClass:"register_form_view1"},[e("v-uni-input",{staticClass:"register_form_view1_input",attrs:{placeholder:"快递号/信息"},on:{input:function(i){i=n.$handleEvent(i),n.changekdh(i)}}})],1),e("v-uni-view",{staticStyle:{display:"flex","flex-flow":"row"}},[e("v-uni-view",{staticClass:"fillinfo_cancel",on:{click:function(i){i=n.$handleEvent(i),n.changekdinfo(i)}}},[n._v("提交")]),e("v-uni-view",{staticClass:"fillinfo_submit",on:{click:function(i){i=n.$handleEvent(i),n.cancelkdinfo(i)}}},[n._v("取消")])],1)],1):n._e(),4==n.datas.flag?e("v-uni-view",{staticClass:"detail_btn",attrs:{id:n.datas.order_id},on:{click:function(i){i=n.$handleEvent(i),n.qrshouh(i)}}},[n._v("确认收货")]):n._e(),4==n.datas.flag?e("v-uni-view",{staticClass:"detail_btn",attrs:{id:n.datas.order_id},on:{click:function(i){i=n.$handleEvent(i),n.tuihuo(i)}}},[n._v("申请退款")]):n._e(),e("v-uni-view",{staticClass:"detail_btn",on:{click:function(i){i=n.$handleEvent(i),n.makephonecall(i)}}},[n._v("联系商家")])],1)],2):n._e()},a=[];e.d(i,"a",function(){return t}),e.d(i,"b",function(){return a})},"0cee":function(n,i,e){i=n.exports=e("2350")(!1),i.push([n.i,"\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n/* pages/order_detail/order_detail.wxss */.bgcolor[data-v-dceaccfc]{background:#f6f6f6}.order_detail_head[data-v-dceaccfc]{width:100%;height:%?140?%;display:block;position:relative}.order_detail_head uni-view[data-v-dceaccfc]{width:100%;height:%?140?%;text-align:center;line-height:%?140?%;font-size:%?32?%;color:#fff;position:absolute;left:0;top:0}.order_detial_person_info[data-v-dceaccfc]{padding:%?30?%;background-color:#fff}.order_detail_address_img[data-v-dceaccfc]{width:%?40?%;height:%?60?%;overflow:hidden;font-size:%?40?%}.person_info_left[data-v-dceaccfc]{margin-left:%?24?%}.recive_person[data-v-dceaccfc]{font-size:%?24?%;color:#636363}.recive_phonenum[data-v-dceaccfc]{font-size:%?24?%;color:#636363;font-family:Arial,Helvetica,sans-serif}.recive_address[data-v-dceaccfc]{font-size:%?24?%;color:#636363;line-height:%?40?%;margin-top:%?10?%}.pricebox[data-v-dceaccfc]{border-top:%?2?% solid #eee;padding:%?20?% %?30?%;background-color:#fff}.price_single[data-v-dceaccfc]{height:%?60?%;line-height:%?60?%;overflow:hidden}.price_single_left[data-v-dceaccfc]{font-size:%?24?%;color:#969696}.price_single_right[data-v-dceaccfc]{font-size:%?24?%;color:#969696;line-height:%?60?%}.price_single_left2[data-v-dceaccfc]{font-size:%?28?%;color:#232323}.price_single_left2 uni-text[data-v-dceaccfc]{font-size:%?24?%;color:#969696}.price_single_right2[data-v-dceaccfc]{font-size:%?36?%;color:#f45351}.price_single_right2 uni-text[data-v-dceaccfc]{font-size:%?24?%}.order_info[data-v-dceaccfc]{padding:%?20?% %?30?%;background-color:#fff}.order_info_ddh_left[data-v-dceaccfc]{font-size:%?24?%;color:#969696}.order_info_ddh_left uni-text[data-v-dceaccfc]{font-family:Arial,Helvetica,sans-serif}.copy_btn[data-v-dceaccfc]{width:%?100?%;height:%?40?%;border:%?2?% solid #e5e5e5;border-radius:%?6?%;font-size:%?24?%;color:#969696;text-align:center;line-height:%?40?%}.smfw[data-v-dceaccfc]{height:%?80?%;background-color:#fff;padding:0 %?30?%}.smfw_left[data-v-dceaccfc]{font-size:%?24?%;color:#232323}.smfw_left uni-text[data-v-dceaccfc]{font-family:Arial,Helvetica,sans-serif}.appoint_form[data-v-dceaccfc]{background-color:#fff}.appoint_form_head[data-v-dceaccfc]{padding:0 %?30?%;height:%?85?%;border-bottom:%?2?% solid #eee}.appoint_form_left[data-v-dceaccfc]{font-size:%?28?%;color:#232323}.appoint_form_right[data-v-dceaccfc]{font-size:%?24?%;color:#f45351}.appoint_form_singlebox[data-v-dceaccfc]{padding:%?20?% %?30?%}.appoint_form_singlebox uni-input[data-v-dceaccfc]{border:none;overflow:hidden;padding:0;margin:0}.detail_imgbox[data-v-dceaccfc]{font-size:0}.detail_imgbox uni-image[data-v-dceaccfc]{width:%?120?%;height:%?120?%;display:inline-block;margin-right:%?22?%;margin-bottom:%?22?%;border-radius:%?8?%}.detail_imgbox uni-image[data-v-dceaccfc]:nth-child(5n){margin-right:0}.detail_bz[data-v-dceaccfc]{font-size:%?24?%;color:#232323;background-color:#fff;padding:0 %?30?%;height:%?80?%;line-height:%?80?%;overflow:hidden}.detail_bz uni-text[data-v-dceaccfc]{margin:%?4?% %?15?% 0 0;color:#f45351}.detail_btnbox[data-v-dceaccfc]{height:%?112?%;background-color:#fff;padding:0 %?30?%;position:fixed;left:0;bottom:0;width:100%;-webkit-box-sizing:border-box;box-sizing:border-box}.detail_btn[data-v-dceaccfc]{width:%?160?%;height:%?50?%;line-height:%?46?%;border-radius:%?8?%;text-align:center;border:%?2?% solid #e5e5e5;font-size:%?24?%;color:#636363;margin-left:%?20?%;-webkit-box-sizing:border-box;box-sizing:border-box;padding:0}.hx_con[data-v-dceaccfc]{position:fixed;left:0;top:0;width:100%;height:100%;z-index:99999}.hexiao[data-v-dceaccfc]{border-radius:%?9?%;width:%?600?%;height:%?300?%;background:#fff;position:absolute;left:%?75?%;top:30%;z-index:99999}.hexiao .hx_tit[data-v-dceaccfc]{margin:%?20?% %?20?% %?10?%;text-align:center}.hexiao .hx_ipt[data-v-dceaccfc]{margin:%?20?%;height:%?30?%;line-height:%?30?%;border:1px solid #eee}.hexiao .hx_btn[data-v-dceaccfc]{background-color:#f90;color:#fff;margin:%?20?%;margin-top:%?0?%;margin-bottom:%?20?%;font-size:%?28?%}.hx_c[data-v-dceaccfc]{width:%?50?%;height:%?50?%;position:absolute;top:%?760?%;left:%?350?%;z-index:99999}.hx_c uni-image[data-v-dceaccfc]{width:100%;height:100%;display:block}.fill_info[data-v-dceaccfc]{position:fixed;left:20%;width:60%;background-color:#fff;border-radius:%?10?%;padding:%?20?%;top:28%;z-index:10000}.fillinfo_cancel[data-v-dceaccfc]{text-align:center;color:#000;margin-top:%?20?%;width:45%;height:%?60?%;line-height:%?60?%;border-radius:%?5?%;border:1px solid #ddd}.fillinfo_submit[data-v-dceaccfc]{text-align:center;color:#000;width:45%;margin-left:10%;margin-top:%?20?%;height:%?60?%;line-height:%?60?%;border-radius:%?5?%;border:1px solid #ddd}.register_form_view1_input[data-v-dceaccfc]{height:%?60?%;-webkit-box-sizing:border-box;box-sizing:border-box}uni-input[data-v-dceaccfc]{border:solid 1px #eee;background:#fff;padding:%?6?%;margin-top:%?10?%;line-height:%?28?%}",""])},1776:function(n,i,e){"use strict";e.r(i);var t=e("07b5"),a=e("c903");for(var s in a)"default"!==s&&function(n){e.d(i,n,function(){return a[n]})}(s);e("b404");var o=e("2877"),c=Object(o["a"])(a["default"],t["a"],t["b"],!1,null,"dceaccfc",null);i["default"]=c.exports},5462:function(n,i,e){"use strict";var t=e("288e");Object.defineProperty(i,"__esModule",{value:!0}),i.default=void 0;var a=t(e("bd86")),s=e("55f5"),o={data:function(){var n;return{$imgurl:this.$imgurl,baseinfo:"",tabbar:"",orderid:"",state:1,showmask:0,datas:(n={jsondata:[{baseinfo:[],proinfo:[]}],yhInfo_yhq:[],yhInfo_score:[]},(0,a.default)(n,"yhInfo_yhq",[]),(0,a.default)(n,"yhInfo_mj",[]),(0,a.default)(n,"store_info",[]),n),orderFormDisable:!0,isChange:"",formchangeBtn:2,kuaidi:["选择快递","圆通","中通","申通","顺丰","韵达","天天","EMS","百世","本人到店","其他"],index:""}},onPullDownRefresh:function(){this.getOrder(),uni.stopPullDownRefresh()},onLoad:function(n){var i=this;this._baseMin(this),this.orderid=n.orderid;var e=0;n.fxsid&&(e=n.fxsid),this.fxsid=e,s.h5login(e,function(){i.getOrder()})},methods:{makePhoneCallC:function(n){uni.makePhoneCall({phoneNumber:n.currentTarget.dataset.tel})},getOrder:function(){var n=this;uni.request({url:this.$baseurl+"doPagegetduoOrderDetail",data:{uniacid:this.$uniacid,order_id:this.orderid},success:function(i){n.datas=i.data.data}})},copy:function(n){var i=n.target.id;uni.setClipboardData({data:i,success:function(n){uni.showToast({title:"复制成功"})}})},qrshouh:function(n){var i=n.target.id,e="",t=this.$baseurl,a=this.$uniacid;uni.showModal({title:"提示",content:"确认收货吗？",success:function(n){n.confirm&&uni.request({url:t+"dopagenewquerenxc",data:{uniacid:a,openid:e,orderid:i},success:function(n){uni.showToast({title:"收货成功！",success:function(n){setTimeout(function(){uni.redirectTo({url:"/pages/orderDetail/orderDetail?orderid="+i})},1500)}})}})}})},tuihuo:function(n){this.showmask=1,this.order_tuihuo=n.target.id},tuikuan:function(n){var i=this,e=n.detail.formId,t=n.currentTarget.dataset.order;uni.showModal({title:"提醒",content:"确定要退款吗？",success:function(n){n.confirm&&uni.request({url:i.$baseurl+"doPageduotk",data:{uniacid:i.$uniacid,formId:e,order_id:t},success:function(n){console.log(n),0==n.data.data.flag?uni.showModal({title:"提示",content:n.data.data.message,showCancel:!1,success:function(n){uni.redirectTo({url:"/pages/orderDetail/orderDetail?orderid="+t})}}):uni.showModal({title:"很抱歉",content:n.data.data.message,confirmText:"联系客服",success:function(i){i.confirm&&uni.makePhoneCall({phoneNumber:n.data.mobile})}})}})}})},bindPickerChange:function(n){this.index=n.detail.value},changekdh:function(n){this.kdh=n.target.value},cancelkdinfo:function(){this.showmask=0},changekdinfo:function(){var n=this;0==n.index?uni.showModal({title:"提交失败",content:"必须选择快递",showCancel:!1}):n.kdh?uni.request({url:n.$baseurl+"doPagenewtuihuo",data:{uniacid:n.$uniacid,order_id:n.order_tuihuo,kuaidi:n.kuaidi[n.index],kuaidihao:n.kdh},success:function(n){1==n.data.result&&uni.showToast({title:"已申请退货",icon:"success",success:function(){setTimeout(function(){wx.redirectTo({url:"/pages/order_more_list/order_more_list?flag=10&type1=10"})},1500)}})}}):uni.showModal({title:"提交失败",content:"快递号/信息必填",showCancel:!1})},makephonecall:function(){var n=this;n.datas.seller_tel&&uni.makePhoneCall({phoneNumber:n.datas.seller_tel})}}};i.default=o},b404:function(n,i,e){"use strict";var t=e("c4da"),a=e.n(t);a.a},c4da:function(n,i,e){var t=e("0cee");"string"===typeof t&&(t=[[n.i,t,""]]),t.locals&&(n.exports=t.locals);var a=e("4f06").default;a("3457cda2",t,!0,{sourceMap:!1,shadowMode:!1})},c903:function(n,i,e){"use strict";e.r(i);var t=e("5462"),a=e.n(t);for(var s in t)"default"!==s&&function(n){e.d(i,n,function(){return t[n]})}(s);i["default"]=a.a}}]);