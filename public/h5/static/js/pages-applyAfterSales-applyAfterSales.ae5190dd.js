(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-applyAfterSales-applyAfterSales"],{"19c0":function(t,e,a){"use strict";a.r(e);var i=a("4cb6"),n=a("8bee");for(var o in n)"default"!==o&&function(t){a.d(e,t,function(){return n[t]})}(o);a("c1c2");var s=a("2877"),l=Object(s["a"])(n["default"],i["a"],i["b"],!1,null,"40512caa",null);e["default"]=l.exports},"4cb6":function(t,e,a){"use strict";var i=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",[a("v-uni-view",{staticStyle:{padding:"20rpx","margin-bottom":"70rpx"}},[a("v-uni-view",{staticClass:"apply_types flex_bflow"},[a("v-uni-view",{staticClass:"apply_choose1"},[t._v("售后类型")]),1==t.from_to?a("v-uni-view",{staticClass:"apply_choose2"},[a("v-uni-text",[t._v("仅退款")])],1):t._e(),2==t.from_to&&(1==t.status||2==t.status&&2==t.delivery_type)?a("v-uni-view",{staticClass:"apply_choose2"},[a("v-uni-text",[t._v("仅退款")])],1):t._e(),2==t.from_to&&(3==t.status||7==t.status||2==t.status&&1==t.delivery_type)?a("v-uni-view",{staticClass:"apply_choose2",on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.chooseApply.apply(void 0,arguments)}}},[a("v-uni-text",[t._v(t._s(t.tk_msg))]),a("v-uni-text",{staticClass:"iconfont icon-x-you apply_icon"})],1):t._e()],1),a("v-uni-view",{staticClass:"apply_types1"},[t._l(t.dataAll,function(e,i){return[a("v-uni-view",{key:i+"_0",staticStyle:{"padding-top":"20rpx"}},[a("v-uni-view",{staticClass:"flex-row"},[a("v-uni-image",{staticClass:"product_img",attrs:{src:""!=e.pro_thumb?e.pro_thumb:t.$host+"/diypage/resource/images/diypage/default/default.jpg",mode:"aspectFill"}}),a("v-uni-view",{staticClass:"order_list_product_center"},[a("v-uni-view",{staticClass:"order_product_title text_hide"},[t._v(t._s(e.pro_title))]),e.pro_attr?a("v-uni-view",{staticClass:"order_product_des text_hide"},[t._v(t._s(e.pro_attr))]):t._e()],1),a("v-uni-view",{staticClass:"flex1"}),a("v-uni-view",[a("v-uni-view",{staticClass:"order_product_price"},[a("v-uni-text",[t._v("￥")]),t._v(t._s(e.pro_price))],1),a("v-uni-view",{staticClass:"order_product_count"},[t._v("X1")])],1)],1)],1),a("v-uni-view",{key:i+"_1",staticClass:"apply_items1 flex_bflow apply_line"},[a("v-uni-view",{staticClass:"apply_choose3"},[t._v("商品数量")]),a("v-uni-view",{staticStyle:{display:"flex"}},[a("v-uni-view",{staticStyle:{width:"40rpx",height:"40rpx","line-height":"40rpx",background:"#f6f6f6","text-align":"center",display:"inline-block",color:"#a0a0a0"},attrs:{"data-type":1},on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.num_change.apply(void 0,arguments)}}},[a("v-uni-view",{staticClass:"iconfont icon-x-jian",staticStyle:{"font-size":"22rpx"}})],1),a("v-uni-view",{staticStyle:{width:"80rpx",height:"40rpx","line-height":"40rpx","text-align":"center",display:"inline-block",background:"#f6f6f6",color:"#434343","font-size":"22rpx"}},[t._v(t._s(e.cancel_num))]),a("v-uni-view",{staticStyle:{width:"40rpx",height:"40rpx","line-height":"40rpx",background:"#f6f6f6","text-align":"center",display:"inline-block",color:"#a0a0a0"},attrs:{"data-type":2},on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.num_change.apply(void 0,arguments)}}},[a("v-uni-view",{staticClass:"iconfont icon-x-jia",staticStyle:{"font-size":"22rpx"}})],1)],1)],1)]}),a("v-uni-view",{staticClass:"apply_items2 flex_bflow"},[a("v-uni-view",{staticClass:"apply_choose3"},[t._v("退款金额")]),a("v-uni-view",{staticStyle:{color:"#E95D3C"}},[t._v("￥"+t._s(t.cancel_money))])],1),a("v-uni-view",{staticClass:"apply_items3"},[a("v-uni-view",{staticClass:"apply_choose3"},[t._v("申请原因")]),a("v-uni-textarea",{staticClass:"apply_textarea",attrs:{maxlength:"170",placeholder:"问题描述越详细，可以提高你的申请成功率!","placeholder-style":"color:#ccc;font-size:24rpx;"},on:{input:function(e){arguments[0]=e=t.$handleEvent(e),t.evaluate.apply(void 0,arguments)}}}),a("v-uni-view",{staticClass:"now_count"},[t._v(t._s(t.nowcount)+"/170")])],1)],2)],1),a("v-uni-view",{staticClass:"apply_tijiao"},[1==t.from_to?a("v-uni-view",{staticClass:"apply_btn",on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.cancelPaymentOrder.apply(void 0,arguments)}}},[t._v("提交")]):t._e(),2==t.from_to?a("v-uni-view",{staticClass:"apply_btn",on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.cancelPaymentOrderItem.apply(void 0,arguments)}}},[t._v("提交")]):t._e()],1),1==t.apply_box?a("v-uni-view",[a("v-uni-view",{staticClass:"mask",on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.hideApply.apply(void 0,arguments)}}}),a("v-uni-view",{staticClass:"apply_cbox"},[a("v-uni-view",{staticClass:"apply_titbox",on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.hideApply.apply(void 0,arguments)}}},[a("v-uni-view",{staticClass:"apply_tit"},[t._v("请选择售后类型")]),a("v-uni-view",{staticClass:"iconfont icon-x-guanbi apply_guanbi"})],1),a("v-uni-view",[a("v-uni-view",{staticClass:"applybox_types flex_bflow"},[a("v-uni-view",[t._v("仅退款")]),a("v-uni-view",{staticClass:"iconfont icon-x-gou",class:[0==t.chooseapplytype?"choose_jf":"no_jf"],attrs:{"data-type":0},on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.chooseapplytypes.apply(void 0,arguments)}}})],1),a("v-uni-view",{staticClass:"applybox_types flex_bflow",staticStyle:{"border-bottom":"0"}},[a("v-uni-view",[t._v("退货退款")]),a("v-uni-view",{staticClass:"iconfont icon-x-gou",class:[1==t.chooseapplytype?"choose_jf":"no_jf"],attrs:{"data-type":1},on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.chooseapplytypes.apply(void 0,arguments)}}})],1)],1),a("v-uni-view",{staticClass:"btn_applytype",on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.getApplyType.apply(void 0,arguments)}}},[t._v("确定")])],1)],1):t._e()],1)},n=[];a.d(e,"a",function(){return i}),a.d(e,"b",function(){return n})},"7d5d":function(t,e,a){var i=a("97bc");"string"===typeof i&&(i=[[t.i,i,""]]),i.locals&&(t.exports=i.locals);var n=a("4f06").default;n("3fdaa5c8",i,!0,{sourceMap:!1,shadowMode:!1})},"85ca":function(t,e,a){"use strict";Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var i=a("7131"),n={data:function(){return{$imgurl:this.$imgurl,$host:this.$host,apply_box:0,chooseapplytype:-1,num:1,evaluatecon:"",nowcount:0,order_id:0,order_item_id:0,dataAll:"",from_to:0,tk_msg:"请选择售后类型",cancel_money:0,status:0,delivery_type:0,total_num:0,is_last_refund:0,is_add_freight:0,shen_all_can_refund_money:0,freight_all:0}},onLoad:function(t){var e=this;this._baseMin(this);var a=0;t.fxsid&&(a=t.fxsid),this.fxsid=a;var n=0;if(t.from_to&&(n=t.from_to),this.from_to=n,1==n){var o=0;t.order_id&&(o=t.order_id),this.order_id=o}else if(2==n){var s=0;t.order_item_id&&(s=t.order_item_id),this.order_item_id=s}var l=uni.getStorageSync("suid");l&&(this.suid=l);var c=uni.getStorageSync("source");c&&(this.source=c),i.h5login(a,function(){1==n?e.getapplyall():2==n&&e.getapplydetail()})},methods:{getapplyall:function(){var t=this;uni.request({url:this.$host+"/api/mainwxapp/applyAllAfterSales",data:{uniacid:this.$uniacid,suid:this.suid,order_id:this.order_id},success:function(e){console.log(e.data.data);for(var a=0;a<e.data.data.order.order_items.length;a++)e.data.data.order.order_items[a]["cancel_num"]=e.data.data.order.order_items[a]["num"];t.dataAll=e.data.data.order.order_items,t.cancel_money=e.data.data.order.pay_money},fail:function(t){console.log(t)}})},getapplydetail:function(){var t=this;uni.request({url:this.$host+"/api/mainwxapp/applyItemAfterSales",data:{uniacid:this.$uniacid,suid:this.suid,order_item_id:this.order_item_id},success:function(e){if(0==e.data.data.error){var a=[];a[0]=e.data.data.order_item,a[0]["cancel_num"]=a[0]["num"],t.dataAll=a,t.status=a[0]["status"],t.delivery_type=a[0]["delivery_type"],t.is_last_refund=a[0]["is_last_refund"],t.is_add_freight=a[0]["is_add_freight"],t.shen_all_can_refund_money=a[0]["shen_all_can_refund_money"],1==t.is_last_refund?1==t.is_add_freight?t.cancel_money=t.shen_all_can_refund_money:(t.freight_all=a[0]["freight_all"],t.cancel_money=(t.shen_all_can_refund_money-t.freight_all).toFixed(2)):t.cancel_money=(a[0]["pro_can_refound_price"]*a[0]["num"]).toFixed(2)}else uni.showModal({title:"提示",content:"申请失败，"+e.data.data.msg,showCancel:!1,success:function(t){uni.navigateBack({delta:2})}})},fail:function(t){console.log(t)}})},cancelPaymentOrder:function(){var t=this.evaluatecon;if(!t)return uni.showModal({title:"提示",content:"申请原因不能为空",showCancel:!1}),!1;var e=0,a=this.source;uni.request({url:this.$host+"/api/mainwxapp/cancelPaymentOrder",data:{uniacid:this.$uniacid,suid:this.suid,order_id:this.order_id,remark:t,type:e,source:a},success:function(t){if(0==t.data.data.error){var e=t.data.data.order_service.order_service_id;uni.showModal({title:"提示",content:"退款申请成功",showCancel:!1,success:function(t){console.log(t),uni.redirectTo({url:"/pages/orderAftersale/orderAftersale?order_service_id="+e})}})}else uni.showModal({title:"提示",content:"申请失败，"+t.data.data.msg,showCancel:!1})},fail:function(t){console.log(t)}})},cancelPaymentOrderItem:function(){var t=this.evaluatecon;if(!t)return uni.showModal({title:"提示",content:"申请原因不能为空",showCancel:!1}),!1;var e=this.status,a=this.delivery_type,i=-1;if(1==e||2==e&&2==a)i=0;else if(i=this.chooseapplytype,-1==i)return void uni.showModal({title:"提示",content:"请选择售后类型",showCancel:!1});var n=this.source;uni.request({url:this.$host+"/api/mainwxapp/applyItemSubmit",data:{uniacid:this.$uniacid,suid:this.suid,order_item_id:this.order_item_id,num:this.dataAll[0]["cancel_num"],refund_money:this.cancel_money,type:i,source:n,remark:t},success:function(t){if(0==t.data.data.error){var e=t.data.data.order_service.order_service_id;uni.showModal({title:"提示",content:"退款申请成功",showCancel:!1,success:function(t){console.log(t),uni.redirectTo({url:"/pages/orderAftersale/orderAftersale?order_service_id="+e})}})}else uni.showModal({title:"提示",content:"申请失败，"+t.data.data.msg,showCancel:!1})},fail:function(t){console.log(t)}})},chooseApply:function(){0==this.apply_box&&(this.apply_box=1),this.chooseapplytype=-1},hideApply:function(){1==this.apply_box&&(this.apply_box=0),this.chooseapplytype=-1},getApplyType:function(){var t=this.chooseapplytype;-1!=t?(1==this.apply_box&&(this.apply_box=0),0==t?this.tk_msg="仅退款":1==t&&(this.tk_msg="退货退款")):uni.showModal({title:"提示",content:"请选择售后类型",showCancel:!1})},chooseapplytypes:function(t){this.chooseapplytype=t.currentTarget.dataset.type},num_change:function(t){var e=t.currentTarget.dataset.type,a=this.from_to;if(2==a){var i=this.dataAll,n=0;1==e?n=i[0]["cancel_num"]-1:2==e&&(n=i[0]["cancel_num"]+1),n>0&&(n<i[0]["num"]?(i[0]["cancel_num"]=n,this.cancel_money=(n*i[0]["pro_can_refound_price"]).toFixed(2)):n==i[0]["num"]&&(i[0]["cancel_num"]=n,1==this.is_last_refund?1==this.is_add_freight?this.cancel_money=this.shen_all_can_refund_money:(this.freight_all=i[0]["freight_all"],this.cancel_money=this.shen_all_can_refund_money-this.freight_all):this.cancel_money=(n*i[0]["pro_can_refound_price"]).toFixed(2))),this.dataAll=i}},evaluate:function(t){var e=this,a=t.detail.value,i=t.detail.cursor;e.evaluatecon=a,e.nowcount=i}}};e.default=n},"8bee":function(t,e,a){"use strict";a.r(e);var i=a("85ca"),n=a.n(i);for(var o in i)"default"!==o&&function(t){a.d(e,t,function(){return i[t]})}(o);e["default"]=n.a},"97bc":function(t,e,a){e=t.exports=a("2350")(!1),e.push([t.i,".flex-row[data-v-40512caa]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center}.flex_bflow[data-v-40512caa]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-pack:justify;-webkit-justify-content:space-between;justify-content:space-between;-webkit-box-align:center;-webkit-align-items:center;align-items:center}.apply_types[data-v-40512caa],.apply_types1[data-v-40512caa]{background:#fff;padding:%?20?%;border-radius:%?10?%;margin-bottom:%?20?%}.apply_types1[data-v-40512caa]{padding:0 %?20?% %?20?%}.apply_choose1[data-v-40512caa]{font-size:%?28?%;color:#333}.apply_choose2[data-v-40512caa]{font-size:%?24?%;color:#999}.apply_icon[data-v-40512caa]{margin-left:%?10?%;font-size:%?24?%}.apply_choose3[data-v-40512caa]{font-size:%?26?%;color:#333}.apply_line[data-v-40512caa]{border-bottom:%?2?% solid #eee}.apply_items1[data-v-40512caa],.apply_items2[data-v-40512caa]{padding:%?20?% 0}.apply_items2[data-v-40512caa]{border-bottom:%?2?% solid #eee}.apply_items3[data-v-40512caa]{padding-top:%?20?%;position:relative}.apply_textarea[data-v-40512caa]{width:100%;height:%?284?%;margin-top:%?20?%;background:#f6f6f6;padding:%?20?%;box-sizing:border-box;font-size:%?24?%}.now_count[data-v-40512caa]{position:absolute;bottom:%?20?%;right:%?20?%;font-size:%?24?%;color:#b6b6b6}.apply_cbox[data-v-40512caa]{position:absolute;bottom:0;left:0;height:%?400?%;width:100%;background:#fff;z-index:891;padding:%?20?%;box-sizing:border-box;border-radius:%?20?% %?20?% 0 0}.apply_titbox[data-v-40512caa]{position:relative;margin-bottom:%?20?%}.apply_tit[data-v-40512caa]{text-align:center;color:#232323}.apply_guanbi[data-v-40512caa]{position:absolute;top:0;right:0}.applybox_types[data-v-40512caa]{padding:%?20?% 0;border-bottom:%?2?% solid #eee}.no_jf[data-v-40512caa]{font-size:%?20?%;color:#f6f6f6;background:#f6f6f6;border-radius:50%;width:%?34?%;height:%?34?%;line-height:%?34?%;text-align:center;box-sizing:border-box;border:%?2?% solid #a0a0a0}.choose_jf[data-v-40512caa]{font-size:%?20?%;color:#fff;background:#e95d3c;border-radius:50%;width:%?34?%;height:%?34?%;line-height:%?34?%;text-align:center;box-sizing:border-box}.btn_applytype[data-v-40512caa]{margin-top:%?60?%;box-sizing:border-box;width:100%;text-align:center;background:#e54a48;color:#fff;border-radius:%?70?%;height:%?70?%;line-height:%?70?%}\n/* 提交按钮 */.apply_tijiao[data-v-40512caa]{position:fixed;bottom:0;left:0;box-sizing:border-box;padding:%?20?%;width:100%;text-align:center}.apply_btn[data-v-40512caa]{background:#e54a48;color:#fff;font-size:%?24?%;border-radius:%?70?%;height:%?70?%;line-height:%?70?%}",""])},c1c2:function(t,e,a){"use strict";var i=a("7d5d"),n=a.n(i);n.a}}]);