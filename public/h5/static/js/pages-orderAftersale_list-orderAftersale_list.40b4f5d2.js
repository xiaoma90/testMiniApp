(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-orderAftersale_list-orderAftersale_list"],{3640:function(t,e,s){"use strict";s.r(e);var i=s("db0c6"),a=s.n(i);for(var r in i)"default"!==r&&function(t){s.d(e,t,function(){return i[t]})}(r);e["default"]=a.a},"64f8":function(t,e,s){e=t.exports=s("2350")(!1),e.push([t.i,".flex-row[data-v-da7c4676]{display:-webkit-box;display:-webkit-flex;display:flex}.flex_bflow[data-v-da7c4676]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-pack:justify;-webkit-justify-content:space-between;justify-content:space-between}.page[data-v-da7c4676]{padding:%?20?%}.order_page[data-v-da7c4676]{padding:%?20?%;background:#fff;border-radius:%?10?%;margin-bottom:%?20?%}.order_serverid[data-v-da7c4676]{padding-bottom:%?20?%;font-size:%?24?%;color:#969696}.order_prolist[data-v-da7c4676]{padding:%?20?% 0;border-top:%?2?% solid #eee}.order_serve_type[data-v-da7c4676]{border-top:%?2?% solid #eee;padding-top:%?20?%}.serve_type_tips[data-v-da7c4676]{font-size:%?24?%;color:#434343;margin-left:%?14?%}.pageno_address[data-v-da7c4676]{display:block;margin:%?200?% auto %?60?%;width:%?280?%;height:%?140?%}",""])},a28e:function(t,e,s){var i=s("64f8");"string"===typeof i&&(i=[[t.i,i,""]]),i.locals&&(t.exports=i.locals);var a=s("4f06").default;a("2caa61ce",i,!0,{sourceMap:!1,shadowMode:!1})},b76c:function(t,e,s){"use strict";var i=function(){var t=this,e=t.$createElement,s=t._self._c||e;return t.$imgurl?s("v-uni-view",[s("v-uni-view",{staticClass:"page"},[t.orderlists_length>0?[t._l(t.orderlists,function(e,i){return[s("v-uni-view",{key:i+"_0",staticClass:"order_page"},[s("v-uni-view",{staticClass:"order_serverid"},[t._v("售后单号："+t._s(e.order_service_id))]),t._l(e.order_items,function(e,i){return[s("v-uni-view",{key:i+"_0",staticClass:"order_prolist flex-row"},[s("v-uni-image",{staticClass:"product_img",attrs:{src:""!=e.pro_thumb?e.pro_thumb:t.$host+"/diypage/resource/images/diypage/default/default.jpg",mode:"scaleToFill"}}),s("v-uni-view",{staticClass:"order_list_product_center"},[s("v-uni-view",{staticClass:"order_product_title text_hide"},[t._v(t._s(e.pro_title))]),e.pro_attr?s("v-uni-view",{staticClass:"order_product_des text_hide"},[t._v(t._s(e.pro_attr))]):t._e()],1),s("v-uni-view",{staticClass:"flex1"}),s("v-uni-view",[s("v-uni-view",{staticClass:"order_product_count"},[t._v("X"+t._s(e.num))])],1)],1)]}),s("v-uni-view",{staticClass:"flex_bflow order_serve_type"},[s("v-uni-view",[1==e.apply_type?s("v-uni-text",{staticClass:"iconfont icon-x-tuihuo",style:{color:t.baseinfo.base_color}}):t._e(),0==e.apply_type?s("v-uni-text",{staticClass:"iconfont icon-x-shouhou",style:{color:t.baseinfo.base_color}}):t._e(),1==e.apply_type&&0==e.status?s("v-uni-text",{staticClass:"serve_type_tips"},[t._v("退货退款，处理中")]):t._e(),1!=e.apply_type||1!=e.status||e.refund_time?t._e():s("v-uni-text",{staticClass:"serve_type_tips"},[t._v("退货退款，退货中")]),1==e.apply_type&&1==e.status&&e.refund_time?s("v-uni-text",{staticClass:"serve_type_tips"},[t._v("退货退款，完成")]):t._e(),1==e.apply_type&&2==e.status?s("v-uni-text",{staticClass:"serve_type_tips"},[t._v("退货退款，退款失败")]):t._e(),1==e.apply_type&&-1==e.status?s("v-uni-text",{staticClass:"serve_type_tips"},[t._v("退货退款，退款取消")]):t._e(),0==e.apply_type&&0==e.status?s("v-uni-text",{staticClass:"serve_type_tips"},[t._v("仅退款，处理中")]):t._e(),0==e.apply_type&&1==e.status?s("v-uni-text",{staticClass:"serve_type_tips"},[t._v("仅退款，退款成功")]):t._e(),0==e.apply_type&&2==e.status?s("v-uni-text",{staticClass:"serve_type_tips"},[t._v("仅退款，退款失败")]):t._e(),0==e.apply_type&&-1==e.status?s("v-uni-text",{staticClass:"serve_type_tips"},[t._v("仅退款，退款取消")]):t._e(),3==e.status?s("v-uni-text",{staticClass:"serve_type_tips"},[t._v("仅退款，商家退款，退款成功")]):t._e()],1),s("v-uni-view",{staticClass:"order_list_price_btn",attrs:{"data-order_service_id":e.order_service_id},on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.orderSale.apply(void 0,arguments)}}},[t._v("查看详情")])],1)],2)]})]:[s("v-uni-image",{staticClass:"pageno_address",attrs:{src:t.$imgurl+"no_search.png"}}),s("v-uni-view",{staticStyle:{"text-align":"center",color:"#666"}},[t._v("还没有相关订单哟~")])]],2),s("myfooter",{attrs:{page_signs:t.page_signs,baseinfo:t.baseinfo}})],1):t._e()},a=[];s.d(e,"a",function(){return i}),s.d(e,"b",function(){return a})},bb55:function(t,e,s){"use strict";var i=s("a28e"),a=s.n(i);a.a},db0c6:function(t,e,s){"use strict";Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var i=s("7131"),a={data:function(){return{$imgurl:this.$imgurl,baseinfo:"",orderlists:"",orderlists_length:0,page:1,next:1}},onLoad:function(t){var e=this;this._baseMin(this);var s=uni.getStorageSync("suid");s&&(this.suid=s);var a=0;i.h5login(a,function(){e.getOrderLists()})},onPullDownRefresh:function(){this.page=1,this.getOrderLists(),uni.stopPullDownRefresh()},onReachBottom:function(){var t=this,e=t.next,s=t.page+1;1==e&&uni.request({url:this.$host+"/api/MainWxapp/afterOrderLists",data:{uniacid:this.$uniacid,suid:this.suid,page:s},success:function(e){e.data.data.service_orders?(t.orderlists=t.orderlists.concat(e.data.data.service_orders),t.page=s):t.next=2}})},methods:{orderSale:function(t){var e=t.currentTarget.dataset.order_service_id;uni.navigateTo({url:"/pages/orderAftersale/orderAftersale?order_service_id="+e})},getOrderLists:function(){var t=this;uni.request({url:this.$host+"/api/MainWxapp/afterOrderLists",data:{uniacid:this.$uniacid,suid:this.suid,page:1},success:function(e){t.orderlists=e.data.data.service_orders,t.orderlists_length=e.data.data.service_orders.length}})}}};e.default=a},ee15:function(t,e,s){"use strict";s.r(e);var i=s("b76c"),a=s("3640");for(var r in a)"default"!==r&&function(t){s.d(e,t,function(){return a[t]})}(r);s("bb55");var n=s("2877"),o=Object(n["a"])(a["default"],i["a"],i["b"],!1,null,"da7c4676",null);e["default"]=o.exports}}]);