(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-orderDetails-orderDetails"],{"7e38":function(t,i,e){"use strict";Object.defineProperty(i,"__esModule",{value:!0}),i.default=void 0;var a=e("7131"),s={data:function(){return{$imgurl:this.$imgurl,baseinfo:"",orderdetails:"",orderadmin:"",orderstore:"",hxmm_list:[{val:"",fs:!0},{val:"",fs:!1},{val:"",fs:!1},{val:"",fs:!1},{val:"",fs:!1},{val:"",fs:!1}],is_focus:!1,hx_choose:0,hx_ewm:"",showhx:0,hxmm:"",showPay:0,choosepayf:0,mymoney:0,mymoney_pay:1,pay_type:1,pay_money:0,is_submit:1,h5_wxpay:0,h5_alipay:0,suid:0,source:"",order_id:"",can_apply:!1}},onLoad:function(t){var i=this;this._baseMin(this);var e=0;t.fxsid&&(e=t.fxsid),this.fxsid=e;var s=0;t.order_id&&(s=t.order_id),this.order_id=s;var o=uni.getStorageSync("suid");o&&(this.suid=o);var n=uni.getStorageSync("source");n&&(this.source=n),a.h5login(e,function(){i.getorderdetails()})},onPullDownRefresh:function(){this.showhx=0,this.hxmm="",this.getorderdetails(),uni.stopPullDownRefresh()},methods:{orderSale:function(t){var i=t.currentTarget.dataset.order_service_id;console.log(i),uni.navigateTo({url:"/pages/orderAftersale/orderAftersale?order_service_id="+i})},makephone:function(t){var i=t.currentTarget.dataset.tel;uni.makePhoneCall({phoneNumber:i})},getorderdetails:function(){var t=this;uni.request({url:this.$host+"/api/mainwxapp/mainShopOrderDetails",data:{uniacid:this.$uniacid,suid:this.suid,order_id:this.order_id},success:function(i){console.log(i.data.data),t.orderdetails=i.data.data.order,t.orderadmin=i.data.data.order.address_info,t.orderstore=i.data.data.order.self_taking_info,t.mymoney=i.data.data.money,t.can_apply=t.orderdetails.can_apply},fail:function(t){console.log(t)}})},copy:function(t){var i=t.currentTarget.dataset.text;console.log(i),uni.setClipboardData({data:i,success:function(t){uni.getClipboardData({success:function(t){}}),uni.showToast({title:"复制成功",duration:2e3})}})},paybox:function(){var t=this,i=this.mymoney,e=this.orderdetails,a=this.orderdetails.is_change_price,s=0;s=1==a?e["change_price"]:e["pay_money"],this.pay_money=s,s>0&&uni.request({url:this.$baseurl+"doPageGetH5payshow",data:{uniacid:this.$uniacid,suid:this.suid},success:function(e){0==e.data.data.ali&&0==e.data.data.wx?(t.h5_wxpay=0,t.h5_alipay=0):(0==e.data.data.wx?t.h5_wxpay=0:t.h5_wxpay=1,0==e.data.data.ali?t.h5_alipay=0:t.h5_alipay=1,s>i&&(t.mymoney_pay=2,t.pay_type=2,1==t.h5_wxpay&&1==t.h5_alipay?t.choosepayf=1:1==t.h5_wxpay?t.choosepayf=1:t.choosepayf=2))}}),0==this.showPay&&(this.showPay=1)},payboxclose:function(){1==this.showPay&&(this.showPay=0)},choosepay:function(t){this.pay_type=t.currentTarget.dataset.pay_type,this.choosepayf=t.currentTarget.dataset.type},pay:function(){var t=this.is_submit;if(2==t)return!1;this.is_submit=2;this.$uniacid;var i=this.suid,e=this.source,a=this.pay_type,s=this.pay_money,o=this.order_id;1==a?uni.request({url:this.$baseurl+"payCallBackNotify",data:{uniacid:this.$uniacid,order_id:o,suid:i,payprice:s,types:"mainShop",flag:0,fxsid:this.fxsid,source:e,pay_to:0},success:function(t){0!=t.data.data.error?uni.showToast({title:t.data.data.msg,icon:"none",mask:!0,success:function(){setTimeout(function(){uni.redirectTo({url:"/pages/main_shop_order/main_shop_order"})},1e3)}}):uni.showToast({title:"购买成功！",icon:"success",mask:!0,success:function(){setTimeout(function(){uni.redirectTo({url:"/pages/main_shop_order/main_shop_order"})},1e3)}})}}):1==this.choosepayf?this._wxh5pay(this,s,"mainShop",o,this.form_id,"/pages/main_shop_order/main_shop_order"):this._alih5pay(this,s,17,o,this.form_id,"/pages/main_shop_order/main_shop_order")},cancelOrderNoPay:function(){var t=this;uni.showModal({title:"提示",content:"该操作不可逆，是否确认取消",success:function(i){if(i.confirm){var e=t.order_id;uni.request({url:t.$host+"/api/mainwxapp/doPageCancelOrderNoPay",data:{uniacid:t.$uniacid,suid:t.suid,order_id:e},success:function(i){0==i.data.data.error&&uni.showModal({title:"提示",content:"取消成功",showCancel:!1,success:function(i){t.getorderdetails()}})}})}}})},deleteOrder:function(){var t=this,i=this.order_id;uni.showModal({title:"提示",content:"该操作不可逆，是否删除",success:function(e){e.confirm&&uni.request({url:t.$host+"/api/mainwxapp/deleteOrder",data:{uniacid:t.$uniacid,suid:t.suid,order_id:i},success:function(t){0==t.data.data.error?uni.showModal({title:"提示",content:"删除成功",showCancel:!1,success:function(t){uni.redirectTo({url:"/pages/main_shop_order/main_shop_order"})}}):uni.showModal({title:"提示",content:t.data.data.msg,showCancel:!1})}})}})},toapplyall:function(t){var i=t.currentTarget.dataset.type,e="/pages/applyAfterSales/applyAfterSales?";if(1==i){var a=this.order_id;e=e+"order_id="+a+"&from_to=1"}else if(2==i){var s=t.currentTarget.dataset.order_item_id;e=e+"order_item_id="+s+"&from_to=2"}uni.navigateTo({url:e})},qrshouh:function(t){var i=this,e=this.order_id,a=this.suid,s=this.$host,o=this.$uniacid;uni.showModal({title:"提示",content:"确认收货吗？",success:function(t){t.confirm&&uni.request({url:s+"/api/mainwxapp/dopageqrshouh",data:{uniacid:o,suid:a,order_id:e},success:function(t){uni.showToast({title:"收货成功！",success:function(t){uni.startPullDownRefresh(),i.showhx=0,i.hxmm="",i.getorderdetails(),uni.stopPullDownRefresh()}})}})}})},wlinfo:function(){var t=this.orderdetails,i=t.express,e=t.express_no,a=this.order_id;null!=i&&null!=e?uni.navigateTo({url:"/pages/logistics_state/logistics_state?kuaidi="+i+"&kuaidihao="+e+"&order_id="+a}):uni.navigateTo({url:"/pages/logistics_information/logistics_information?order_id="+a})},hxshow:function(t){this.showhx=1},hxhide:function(){this.showhx=0,this.hxmm=""},onFocus:function(t){var i=this;i.isFocus=!0},hxmmInput:function(t){for(var i=t.target.value.length,e=0;e<this.hxmm_list.length;e++)this.hxmm_list[e].fs=!1,this.hxmm_list[e].val=t.target.value[e];i&&(this.hxmm_list[i-1].fs=!0),this.hxmm=t.target.value},gethxmima:function(){this.hx_choose=1},gethxImg:function(){var t=this;uni.request({url:this.$baseurl+"doPageHxEwm",data:{uniacid:this.$uniacid,suid:this.suid,pageUrl:"mainShop",orderid:this.order_id},success:function(i){console.log(i.data),t.hx_ewm=i.data.data,t.hx_choose=2}})},hxhide1:function(){this.hx_choose=0,this.hxmm="";for(var t=0;t<this.hxmm_list.length;t++)this.hxmm_list[t].fs=!1,this.hxmm_list[t].val=""},hxmmpass:function(){var t=this;this.hxmm?uni.request({url:this.$baseurl+"hxmm",data:{uniacid:this.$uniacid,suid:this.suid,order_id:this.order_id,is_more:5,hxmm:this.hxmm},success:function(i){var e=i.data.data;if(0==e){uni.showModal({title:"提示",content:"核销密码不正确！",showCancel:!1}),t.hxmm="";for(var a=0;a<t.hxmm_list.length;a++)t.hxmm_list[a].fs=!1,t.hxmm_list[a].val="";console.log(t.hxmm)}else 1==e?uni.showToast({title:"消费成功",icon:"success",duration:2e3,success:function(i){uni.startPullDownRefresh(),t.showhx=0,t.getorderdetails(),uni.stopPullDownRefresh()}}):2==e&&uni.showModal({title:"提示",content:"已核销!",showCancel:!1,success:function(i){uni.startPullDownRefresh(),t.showhx=0,t.getorderdetails(),uni.stopPullDownRefresh()}})}}):uni.showModal({title:"提示",content:"请输入核销密码！",showCancel:!1})}}};i.default=s},a9c6:function(t,i,e){"use strict";e.r(i);var a=e("c13b"),s=e("f848");for(var o in s)"default"!==o&&function(t){e.d(i,t,function(){return s[t]})}(o);e("d8bf4");var n=e("2877"),r=Object(n["a"])(s["default"],a["a"],a["b"],!1,null,"573a785a",null);i["default"]=r.exports},b28b:function(t,i,e){i=t.exports=e("2350")(!1),i.push([t.i,".flex-row[data-v-573a785a]{display:-webkit-box;display:-webkit-flex;display:flex}.flex_bflow[data-v-573a785a]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-pack:justify;-webkit-justify-content:space-between;justify-content:space-between;-webkit-box-align:center;-webkit-align-items:center;align-items:center}\n\n/* 顶部 */.order_title_box[data-v-573a785a]{height:%?260?%}.order_types[data-v-573a785a]{font-size:%?30?%;color:#fcfcfc}.order_img[data-v-573a785a]{position:relative}.order_img uni-view[data-v-573a785a]{position:absolute;bottom:%?8?%;left:%?58?%;font-size:%?106?%;color:hsla(0,0%,100%,.3)}.order_info[data-v-573a785a]{background:#fff;border-radius:%?10?% %?10?% 0 0;padding:%?20?% %?20?% %?40?%;background-repeat:repeat-x;background-position-y:bottom}.order_infos[data-v-573a785a]{background:#fff;border-radius:%?10?% %?10?% 0 0;padding:%?20?% 0;background-repeat:repeat-x;background-position-y:bottom}.order_info_store[data-v-573a785a]{border-top:%?2?% solid #eee;padding:0 %?20?% %?10?% %?68?%}.order_info_store uni-view[data-v-573a785a]{padding-top:%?20?%;font-size:%?26?%;color:#232323}.order_info_store uni-text[data-v-573a785a]{color:#969696;margin-left:%?22?%}.gmadmin_info[data-v-573a785a]{color:#969696;font-size:%?24?%}.gmadmin_haveinfo[data-v-573a785a]{font-size:%?28?%;color:#232323;margin-bottom:%?10?%}.gmadmin_haveinfo uni-text[data-v-573a785a]{margin-right:%?26?%}.icon_tabs[data-v-573a785a]{color:#232323;margin-right:%?20?%;font-size:%?30?%}.order_proinfo[data-v-573a785a]{background:#fff;border-radius:0 0 %?10?% %?10?%;padding:0 %?20?%}.order_prolist[data-v-573a785a]{padding:%?20?% 0;border-top:%?2?% solid #eee}.order_prolist[data-v-573a785a]:first-child{border-top:0}.product_img[data-v-573a785a]{width:%?150?%;height:%?150?%}.order_price[data-v-573a785a]{background:#fff;border-radius:%?10?%;padding:%?20?%}.price_items[data-v-573a785a]{padding-bottom:%?20?%;font-size:%?24?%;color:#969696}.price_all[data-v-573a785a]{border-top:%?2?% solid #eee;padding-top:%?20?%;font-size:%?28?%}.price_all_text[data-v-573a785a]{color:#434343}.price_all_price[data-v-573a785a]{color:#f4361d}.price_all_price uni-text[data-v-573a785a]{font-size:%?22?%}.order_nums1[data-v-573a785a],.order_nums2[data-v-573a785a]{font-size:%?22?%;color:#969696}.order_nums2[data-v-573a785a]{padding-top:%?20?%}.order_nums1 uni-text[data-v-573a785a],.order_nums2 uni-text[data-v-573a785a]{margin-left:%?50?%}.order_copy[data-v-573a785a]{color:#f4361d;font-size:%?22?%}\n\n/* 底部 */.order_foot[data-v-573a785a]{background:#fff;height:%?100?%;width:100%;position:fixed;bottom:0;left:0;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-pack:end;-webkit-justify-content:flex-end;justify-content:flex-end;padding-right:%?20?%;box-sizing:border-box}\n\n/* 支付弹框 */.pay_box[data-v-573a785a]{padding:%?30?% %?20?% %?20?%;width:100%;overflow:hidden;position:fixed;bottom:0;left:0;z-index:999;background:#fff;border-radius:%?20?% %?20?% 0 0;box-sizing:border-box}.pay_function[data-v-573a785a]{text-align:center;font-size:%?32?%;color:#434343;height:%?60?%}.bsyyyd[data-v-573a785a]{color:#a0a0a0;font-size:%?46?%;position:absolute;top:%?20?%;right:%?20?%}.pay_funs[data-v-573a785a]{-webkit-box-align:center;-webkit-align-items:center;align-items:center;position:relative}.pay_funsimg[data-v-573a785a]{width:10%;text-align:center}.pay_funsimg uni-image[data-v-573a785a]{width:%?30?%;height:%?28?%}.pay_moneys[data-v-573a785a]{-webkit-box-align:center;-webkit-align-items:center;align-items:center;width:89%;padding:%?30?% 0;border-bottom:%?2?% solid #eee}.ye_less[data-v-573a785a]{position:absolute;top:0;left:0;background:hsla(0,0%,100%,.33);width:100%;height:100%}.pay_submit[data-v-573a785a]{background:#e54a48;color:#fff;font-size:%?28?%;margin-top:%?50?%;border-radius:%?40?%}.no_jf[data-v-573a785a]{font-size:%?20?%;color:#f6f6f6;background:#f6f6f6;border-radius:50%;width:%?34?%;height:%?34?%;line-height:%?34?%;text-align:center;box-sizing:border-box;border:%?2?% solid #a0a0a0}.choose_jf[data-v-573a785a]{font-size:%?20?%;color:#fff;background:#f4361d;border-radius:50%;width:%?34?%;height:%?34?%;line-height:%?34?%;text-align:center;box-sizing:border-box}\n\n/* 核销 */.share_con[data-v-573a785a]{z-index:900}\n\n/* 输入核销密码 */.code[data-v-573a785a]{margin:%?80?% auto %?40?%;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-orient:horizontal;-webkit-box-direction:normal;-webkit-flex-direction:row;flex-direction:row;-webkit-box-pack:justify;-webkit-justify-content:space-between;justify-content:space-between;width:%?460?%}.code uni-text[data-v-573a785a]{width:%?70?%;height:%?70?%;line-height:%?28?%;border:none;border-bottom:%?2?% solid #b2bfbd;text-align:center;color:#4c4e60;font-size:%?48?%}.code uni-text.focus[data-v-573a785a]{border-color:#4c79fa}.code_input[data-v-573a785a]{position:absolute;top:%?90?%;width:100%;height:%?80?%;opacity:0;overflow:hidden}.code_input uni-input[data-v-573a785a]{position:absolute;left:-50%;width:200%;height:%?80?%;line-height:%?80?%;font-size:%?40?%;text-align:left;outline:none;border:none;background:none;z-index:666}.hx_con[data-v-573a785a]{position:fixed;left:0;top:0;width:100%;height:100%;z-index:950}.hexiao[data-v-573a785a]{border-radius:%?10?%;width:%?580?%;height:%?650?%;background:#fff;position:absolute;left:%?84?%;top:18%;z-index:1000}.hexiao_inp .hx_tit[data-v-573a785a]{font-size:%?32?%;color:#232323;text-align:center}.hexiao_inp .hx_ipt[data-v-573a785a]{margin:%?20?% auto;height:%?50?%;line-height:%?50?%;border:1px solid #eee;width:60%}.hexiao_inp .hx_yes[data-v-573a785a]{text-align:center;padding:0 %?40?%}.hexiao_inp .hx_btn[data-v-573a785a]{color:#000;font-size:%?26?%;border-radius:%?70?%;height:%?70?%;line-height:%?70?%;width:100%}.hexiao_inp[data-v-573a785a]{border-radius:%?10?%;width:%?580?%;height:%?400?%;background:#fff;position:absolute;left:%?84?%;top:18%;z-index:1000}.hx_title[data-v-573a785a]{text-align:center;position:relative;height:%?80?%;line-height:%?80?%;border-bottom:%?2?% solid #ddd}.hx_titc[data-v-573a785a]{position:absolute;top:%?16?%;right:%?20?%}.hexiao_mima[data-v-573a785a]{margin-top:%?42?%;position:relative}.hx_ewm[data-v-573a785a]{width:%?390?%;height:%?390?%;margin:%?90?% 0 %?40?%}.he_img_tips[data-v-573a785a]{color:#333;font-size:%?26?%;text-align:center}",""])},be50:function(t,i,e){var a=e("b28b");"string"===typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);var s=e("4f06").default;s("7b36140e",a,!0,{sourceMap:!1,shadowMode:!1})},c13b:function(t,i,e){"use strict";var a=function(){var t=this,i=t.$createElement,e=t._self._c||i;return t.$imgurl?e("v-uni-view",[e("v-uni-view",{staticClass:"order_title_box",style:{background:t.baseinfo.base_color}},[e("v-uni-view",{staticClass:"flex_bflow",staticStyle:{padding:"20rpx 120rpx 0"}},[0==t.orderdetails.status?e("v-uni-view",{staticClass:"order_types"},[t._v("待付款")]):t._e(),1==t.orderdetails.status?e("v-uni-view",{staticClass:"order_types"},[t._v("待发货")]):t._e(),2==t.orderdetails.status&&1==t.orderdetails.delivery_type?e("v-uni-view",{staticClass:"order_types"},[t._v("卖家已发货")]):t._e(),2==t.orderdetails.status&&2==t.orderdetails.delivery_type?e("v-uni-view",{staticClass:"order_types"},[t._v("待核销")]):t._e(),3==t.orderdetails.status&&1==t.orderdetails.delivery_type?e("v-uni-view",{staticClass:"order_types"},[t._v("已收货")]):t._e(),3==t.orderdetails.status&&2==t.orderdetails.delivery_type?e("v-uni-view",{staticClass:"order_types"},[t._v("已核销")]):t._e(),5==t.orderdetails.status?e("v-uni-view",{staticClass:"order_types"},[t._v("交易完成")]):t._e(),-1==t.orderdetails.status||-2==t.orderdetails.status||-3==t.orderdetails.status?e("v-uni-view",{staticClass:"order_types"},[t._v("交易关闭")]):t._e(),e("v-uni-view",{staticClass:"order_img"},[0==t.orderdetails.status?e("v-uni-view",{staticClass:"iconfont icon-x-qianbao"}):t._e(),1==t.orderdetails.status?e("v-uni-view",{staticClass:"iconfont icon-x-daifahuo1",staticStyle:{"font-size":"76rpx",left:"68rpx",bottom:"28rpx"}}):t._e(),2==t.orderdetails.status&&1==t.orderdetails.delivery_type?e("v-uni-view",{staticClass:"iconfont icon-x-yifahuo",staticStyle:{"font-size":"90rpx",left:"68rpx",bottom:"18rpx"}}):t._e(),2==t.orderdetails.status&&2==t.orderdetails.delivery_type?e("v-uni-view",{staticClass:"iconfont icon-x-hexiao",staticStyle:{"font-size":"96rpx",left:"64rpx",bottom:"20rpx"}}):t._e(),5==t.orderdetails.status||3==t.orderdetails.status?e("v-uni-view",{staticClass:"iconfont icon-x-yiwancheng",staticStyle:{"font-size":"76rpx",left:"72rpx",bottom:"20rpx"}}):t._e(),-1==t.orderdetails.status||-2==t.orderdetails.status||-3==t.orderdetails.status?e("v-uni-view",{staticClass:"iconfont icon-x-jiaoyiguanbi",staticStyle:{"font-size":"82rpx",left:"50rpx",bottom:"20rpx"}}):t._e(),e("v-uni-image",{staticStyle:{width:"208rpx",height:"108rpx"},attrs:{src:t.$imgurl+"detailTop.png",mode:"aspectFit"}})],1)],1),e("v-uni-view",{staticStyle:{margin:"0 20rpx"}},[t.orderadmin?[e("v-uni-view",{staticClass:"order_info flex-row",style:{"background-image":"url("+t.$imgurl+"tabbottom.png)"}},[e("v-uni-view",{staticClass:"iconfont icon-x-geren2 icon_tabs"}),e("v-uni-view",{staticStyle:{width:"93%"}},[e("v-uni-view",{staticClass:"gmadmin_info flex_bflow"},[e("v-uni-view",[e("v-uni-view",{staticClass:"gmadmin_haveinfo"},[e("v-uni-text",{staticStyle:{"font-weight":"bold","margin-right":"20rpx"}},[t._v(t._s(t.orderadmin.name))]),e("v-uni-text",[t._v(t._s(t.orderadmin.mobile))])],1),e("v-uni-view",[t._v(t._s(t.orderadmin.address)+" "+t._s(t.orderadmin.more_address))])],1)],1)],1)],1)]:t._e(),t.orderstore?[e("v-uni-view",{staticClass:"order_infos",style:{"background-image":"url("+t.$imgurl+"tabbottom.png)"}},[e("v-uni-view",{staticClass:"flex-row",staticStyle:{margin:"0 20rpx 20rpx"}},[e("v-uni-view",{staticClass:"iconfont icon-c-dizhishixin icon_tabs"}),e("v-uni-view",{staticStyle:{width:"93%"}},[e("v-uni-view",{staticClass:"gmadmin_info flex_bflow"},[e("v-uni-view",{staticStyle:{width:"90%"}},[e("v-uni-view",{staticStyle:{color:"#232323","font-size":"30rpx","font-weight":"bold","padding-bottom":"10rpx"}},[t._v(t._s(t.orderstore.self_taking_shop_info.title))]),e("v-uni-view",{staticClass:"gmadmin_haveinfo"},[e("v-uni-text",[t._v("营业时间："+t._s(t.orderstore.self_taking_shop_info.times))]),e("v-uni-text",{attrs:{"data-tel":t.orderstore.self_taking_shop_info.tel},on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.makephone.apply(void 0,arguments)}}},[t._v(t._s(t.orderstore.self_taking_shop_info.tel))])],1),e("v-uni-view",[t._v("地址："+t._s(t.orderstore.self_taking_shop_info.province)+t._s(t.orderstore.self_taking_shop_info.city)+t._s(t.orderstore.self_taking_shop_info.country))])],1)],1)],1)],1),e("v-uni-view",{staticClass:"order_info_store"},[e("v-uni-view",[t._v("自取时间"),e("v-uni-text",[t._v(t._s(t.orderstore.self_taking_time))])],1),e("v-uni-view",[t._v("预留电话"),e("v-uni-text",[t._v(t._s(t.orderstore.self_taking_contact))])],1)],1)],1)]:t._e(),e("v-uni-view",{staticClass:"order_proinfo"},[t._l(t.orderdetails.order_items,function(i,a){return[e("v-uni-view",{key:a+"_0",staticClass:"order_prolist flex-row"},[e("v-uni-image",{staticClass:"product_img",attrs:{src:""!=i.pro_thumb?i.pro_thumb:t.$host+"/diypage/resource/images/diypage/default/default.jpg",mode:"scaleToFill"}}),e("v-uni-view",{staticClass:"order_list_product_center"},[e("v-uni-view",{staticClass:"order_product_title text_hide"},[t._v(t._s(i.pro_title))]),i.pro_attr?e("v-uni-view",{staticClass:"order_product_des text_hide"},[t._v(t._s(i.pro_attr))]):t._e()],1),e("v-uni-view",{staticClass:"flex1"}),e("v-uni-view",[e("v-uni-view",{staticClass:"order_product_price"},[e("v-uni-text",[t._v("￥")]),t._v(t._s(i.pro_price))],1),e("v-uni-view",{staticClass:"order_product_count"},[t._v("X"+t._s(i.num))]),0==t.orderdetails.is_change_price&&i.status>0&&i.status<3&&0==t.orderdetails.is_after_sale&&0==i.refund_num?e("v-uni-view",{staticClass:"order_list_btn",attrs:{"data-type":2,"data-order_item_id":i.order_item_id},on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.toapplyall.apply(void 0,arguments)}}},[t._v("申请退款")]):t._e(),!t.can_apply||3!=i.status&&7!=i.status?t._e():e("v-uni-view",{staticClass:"order_list_btn",attrs:{"data-type":2,"data-order_item_id":i.order_item_id},on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.toapplyall.apply(void 0,arguments)}}},[t._v("申请售后")]),4==i.status||5==i.status||6==i.status?e("v-uni-view",{staticClass:"order_pro_tuikuan",attrs:{"data-order_service_id":i.order_service_id},on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.orderSale.apply(void 0,arguments)}}},[t._v("退款中")]):t._e(),-4==i.status||-3==i.status?e("v-uni-view",{staticClass:"order_pro_tuikuan",attrs:{"data-order_service_id":i.order_service_id},on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.orderSale.apply(void 0,arguments)}}},[t._v("退款成功")]):t._e(),-5==i.status?e("v-uni-view",{staticClass:"order_pro_tuikuan",attrs:{"data-order_service_id":i.order_service_id},on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.orderSale.apply(void 0,arguments)}}},[t._v("退货成功")]):t._e()],1)],1)]})],2)],2),e("v-uni-view",{staticStyle:{margin:"20rpx"}},[e("v-uni-view",{staticClass:"order_price"},[e("v-uni-view",{staticClass:"price_items flex_bflow"},[e("v-uni-view",[t._v("商品总价")]),e("v-uni-view",[t._v("￥"+t._s(t.orderdetails.goods_total_price))])],1),e("v-uni-view",{staticClass:"price_items flex_bflow"},[e("v-uni-view",[t._v("会员折扣")]),e("v-uni-view",[t._v("-￥"+t._s(t.orderdetails.total_discounts_jian_price))])],1),e("v-uni-view",{staticClass:"price_items flex_bflow"},[e("v-uni-view",[t._v("优惠券")]),e("v-uni-view",[t._v("-￥"+t._s(t.orderdetails.discount_money))])],1),e("v-uni-view",{staticClass:"price_items flex_bflow"},[e("v-uni-view",[t._v("积分抵扣")]),e("v-uni-view",[t._v("-￥"+t._s(t.orderdetails.score_money))])],1),e("v-uni-view",{staticClass:"price_items flex_bflow"},[e("v-uni-view",[t._v("运费")]),e("v-uni-view",[t._v("￥"+t._s(t.orderdetails.freight_money))])],1),e("v-uni-view",{staticClass:"price_all flex_bflow"},[e("v-uni-view",{staticClass:"price_all_text"},[t._v("实付款")]),e("v-uni-view",{staticClass:"price_all_price"},[e("v-uni-text",[t._v("￥")]),t._v(t._s(t.orderdetails.pay_money))],1)],1)],1)],1),e("v-uni-view",{staticStyle:{margin:"20rpx 20rpx 120rpx"}},[e("v-uni-view",{staticClass:"order_price"},[e("v-uni-view",{staticClass:"flex_bflow"},[e("v-uni-view",{staticClass:"order_nums1"},[t._v("订单编号"),e("v-uni-text",[t._v(t._s(t.orderdetails.order_id))])],1)],1),e("v-uni-view",{staticClass:"flex-row"},[e("v-uni-view",{staticClass:"order_nums2"},[t._v("创建时间"),e("v-uni-text",[t._v(t._s(t.orderdetails.creat_time))])],1)],1),t.orderdetails.cancel_time?e("v-uni-view",{staticClass:"flex-row"},[e("v-uni-view",{staticClass:"order_nums2"},[t._v("关闭时间"),e("v-uni-text",[t._v(t._s(t.orderdetails.cancel_time))])],1)],1):t._e(),t.orderdetails.pay_time?e("v-uni-view",{staticClass:"flex-row"},[e("v-uni-view",{staticClass:"order_nums2"},[t._v("付款时间"),e("v-uni-text",[t._v(t._s(t.orderdetails.pay_time))])],1)],1):t._e(),t.orderdetails.deliver_time?e("v-uni-view",{staticClass:"flex-row"},[e("v-uni-view",{staticClass:"order_nums2"},[t._v("发货时间"),e("v-uni-text",[t._v(t._s(t.orderdetails.deliver_time))])],1)],1):t._e(),t.orderdetails.complete_time?e("v-uni-view",{staticClass:"flex-row"},[e("v-uni-view",{staticClass:"order_nums2"},[t._v("成交时间"),e("v-uni-text",[t._v(t._s(t.orderdetails.complete_time))])],1)],1):t._e(),t.orderdetails.pay_time?e("v-uni-view",{staticClass:"flex-row"},[e("v-uni-view",{staticClass:"order_nums2"},[t._v("支付方式"),e("v-uni-text",[t._v(t._s(t.orderdetails.pay_to))])],1)],1):t._e()],1)],1)],1),e("v-uni-view",{staticClass:"order_foot flex-row"},[e("v-uni-view",[-1!=t.orderdetails.status&&-2!=t.orderdetails.status&&-3!=t.orderdetails.status?e("v-uni-view",{staticClass:"order_list_btn",attrs:{"data-tel":t.baseinfo.tel},on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.makephone.apply(void 0,arguments)}}},[t._v("联系客服")]):t._e(),0==t.orderdetails.status?e("v-uni-view",{staticClass:"order_list_btn",on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.cancelOrderNoPay.apply(void 0,arguments)}}},[t._v("取消订单")]):t._e(),1==t.orderdetails.allow_all_refund&&(1==t.orderdetails.status||2==t.orderdetails.status&&2==t.orderdetails.delivery_type)?e("v-uni-view",{staticClass:"order_list_btn",attrs:{"data-type":1},on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.toapplyall.apply(void 0,arguments)}}},[t._v("取消订单")]):t._e(),1!=t.orderdetails.delivery_type||1!=t.orderdetails.status&&2!=t.orderdetails.status||-1==t.orderdetails.express?t._e():e("v-uni-view",{staticClass:"order_list_btn",on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.wlinfo.apply(void 0,arguments)}}},[t._v("查看物流")]),1==t.orderdetails.delivery_type&&-1==t.orderdetails.express?e("v-uni-view",{staticClass:"order_list_btn"},[t._v("商家配送")]):t._e(),0==t.orderdetails.status?e("v-uni-view",{staticClass:"order_list_price_btn",on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.paybox.apply(void 0,arguments)}}},[t._v("立即付款")]):t._e(),2==t.orderdetails.status&&1==t.orderdetails.delivery_type?e("v-uni-view",{staticClass:"order_list_price_btn",on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.qrshouh.apply(void 0,arguments)}}},[t._v("确认收货")]):t._e(),2==t.orderdetails.status&&2==t.orderdetails.delivery_type?e("v-uni-view",{staticClass:"order_list_price_btn",on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.hxshow.apply(void 0,arguments)}}},[t._v("立即核销")]):t._e(),-1==t.orderdetails.status||-2==t.orderdetails.status||-3==t.orderdetails.status?e("v-uni-view",{staticClass:"order_list_btn",on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.deleteOrder.apply(void 0,arguments)}}},[t._v("删除订单")]):t._e()],1)],1),1==t.showPay?[e("v-uni-view",{staticClass:"mask",on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.payboxclose.apply(void 0,arguments)}}}),e("v-uni-view",{staticClass:"pay_box"},[e("v-uni-view",{staticClass:"pay_function"},[e("v-uni-view",[t._v("选择支付方式")]),e("v-uni-view",{staticClass:"iconfont icon-x-guanbi bsyyyd",on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.payboxclose.apply(void 0,arguments)}}})],1),e("v-uni-view",{staticClass:"pay_funs flex-row"},[e("v-uni-view",{staticClass:"pay_funsimg"},[e("v-uni-image",{attrs:{src:t.$imgurl+"yue.png",mode:"aspectFit"}})],1),e("v-uni-view",{staticClass:"pay_moneys flex_bflow"},[e("v-uni-view",[t._v("余额支付（剩余：￥"+t._s(t.mymoney)+"元）")]),1==t.mymoney_pay?e("v-uni-view",{staticClass:"iconfont icon-x-gou",class:[0==t.choosepayf?"choose_jf":"no_jf"],attrs:{"data-pay_type":1,"data-type":0},on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.choosepay.apply(void 0,arguments)}}}):t._e()],1),2==t.mymoney_pay?e("v-uni-view",{staticClass:"ye_less"}):t._e()],1),t.pay_money>0?[1==t.h5_wxpay?e("v-uni-view",{staticClass:"pay_funs flex-row"},[e("v-uni-view",{staticClass:"pay_funsimg"},[e("v-uni-image",{attrs:{src:t.$imgurl+"weix.png",mode:""}})],1),e("v-uni-view",{staticClass:"pay_moneys flex_bflow"},[e("v-uni-view",[t._v("微信支付")]),e("v-uni-view",{staticClass:"iconfont icon-x-gou",class:[1==t.choosepayf?"choose_jf":"no_jf"],attrs:{"data-pay_type":2,"data-type":1},on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.choosepay.apply(void 0,arguments)}}})],1)],1):t._e(),1==t.h5_alipay?e("v-uni-view",{staticClass:"pay_funs flex-row"},[e("v-uni-view",{staticClass:"pay_funsimg"},[e("v-uni-image",{attrs:{src:t.$imgurl+"zhifb.png",mode:"aspectFit"}})],1),e("v-uni-view",{staticClass:"pay_moneys flex_bflow"},[e("v-uni-view",[t._v("支付宝支付")]),e("v-uni-view",{staticClass:"iconfont icon-x-gou",class:[2==t.choosepayf?"choose_jf":"no_jf"],attrs:{"data-pay_type":2,"data-type":2},on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.choosepay.apply(void 0,arguments)}}})],1)],1):t._e()]:t._e(),e("v-uni-form",{attrs:{"report-submit":"true"},on:{submit:function(i){arguments[0]=i=t.$handleEvent(i),t.pay.apply(void 0,arguments)}}},[e("v-uni-button",{staticClass:"pay_submit",attrs:{formType:"submit"}},[t._v("确定")])],1)],2)]:t._e(),1==t.showhx?e("v-uni-view",{staticClass:"hx_con"},[e("v-uni-view",{staticClass:"mask",on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.hxhide.apply(void 0,arguments)}}}),e("v-uni-view",{staticClass:"share_con flex-row"},[e("v-uni-view",{staticClass:"share_con_box"},[e("v-uni-view",{staticClass:"flex-row",staticStyle:{"border-right":"2rpx solid #c6cbd9","align-items":"center","justify-content":"center"},on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.gethxmima.apply(void 0,arguments)}}},[e("v-uni-view",{staticClass:"iconfont icon-x-mima1",staticStyle:{"font-size":"40rpx",color:"#FFBA41"}}),e("v-uni-view",{staticStyle:{"margin-left":"10rpx"}},[t._v("密码核销")])],1)],1),e("v-uni-view",{staticClass:"share_con_box"},[e("v-uni-view",{staticClass:"flex-row",staticStyle:{"align-items":"center","justify-content":"center"},on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.gethxImg.apply(void 0,arguments)}}},[e("v-uni-view",{staticClass:"iconfont icon-x-erweima1",staticStyle:{"font-size":"40rpx",color:"#4BC733"}}),e("v-uni-view",{staticStyle:{"margin-left":"10rpx"}},[t._v("二维码核销")])],1)],1)],1),1==t.hx_choose?e("v-uni-view",[e("v-uni-view",{staticClass:"mask",staticStyle:{"z-index":"920"},on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.hxhide1.apply(void 0,arguments)}}}),e("v-uni-view",{staticClass:"hexiao_inp"},[e("v-uni-view",{staticClass:"hx_titc",on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.hxhide1.apply(void 0,arguments)}}},[e("v-uni-view",{staticClass:"iconfont icon-x-guanbi"})],1),e("v-uni-view",{staticClass:"hexiao_mima"},[e("v-uni-view",{staticClass:"hx_tit"},[t._v("请输入核销密码")]),e("v-uni-view",{staticClass:"code"},t._l(t.hxmm_list,function(i,a){return e("v-uni-text",{key:a,class:[i.fs?"focus":""],attrs:{type:"number"},domProps:{textContent:t._s(i.val)}})}),1),e("v-uni-view",{staticClass:"code_input"},[e("v-uni-input",{attrs:{type:"number",focus:"true","hover-class":"none",maxlength:"6",value:t.hxmm},on:{input:function(i){arguments[0]=i=t.$handleEvent(i),t.hxmmInput.apply(void 0,arguments)}}})],1),e("v-uni-view",{staticClass:"hx_yes"},[e("v-uni-view",{staticClass:"hx_btn",style:{background:t.baseinfo.base_color,color:"#fff"},on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.hxmmpass.apply(void 0,arguments)}}},[t._v("确定")])],1)],1)],1)],1):t._e(),2==t.hx_choose?e("v-uni-view",[e("v-uni-view",{staticClass:"mask",staticStyle:{"z-index":"920"},on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.hxhide1.apply(void 0,arguments)}}}),e("v-uni-view",{staticClass:"hexiao"},[e("v-uni-view",{staticClass:"hx_titc",on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.hxhide1.apply(void 0,arguments)}}},[e("v-uni-view",{staticClass:"iconfont icon-x-guanbi"})],1),e("v-uni-view",{staticStyle:{"text-align":"center"}},[e("v-uni-image",{staticClass:"hx_ewm",attrs:{src:t.hx_ewm,mode:"aspectFit"}})],1),e("v-uni-view",{staticClass:"he_img_tips"},[t._v("请将二维码出示给工作人员")])],1)],1):t._e()],1):t._e()],2):t._e()},s=[];e.d(i,"a",function(){return a}),e.d(i,"b",function(){return s})},d8bf4:function(t,i,e){"use strict";var a=e("be50"),s=e.n(a);s.a},f848:function(t,i,e){"use strict";e.r(i);var a=e("7e38"),s=e.n(a);for(var o in a)"default"!==o&&function(t){e.d(i,t,function(){return a[t]})}(o);i["default"]=s.a}}]);