(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pagesFlashSale-orderlist_dan-orderlist_dan"],{"38a1":function(t,i,a){"use strict";a.r(i);var e=a("93ad"),s=a.n(e);for(var n in e)"default"!==n&&function(t){a.d(i,t,function(){return e[t]})}(n);i["default"]=s.a},"40c5":function(t,i,a){i=t.exports=a("2350")(!1),i.push([t.i,".flex_bflow[data-v-754f9469]{display:-webkit-box;display:-webkit-flex;display:-ms-flexbox;display:flex;-webkit-box-pack:justify;-webkit-justify-content:space-between;-ms-flex-pack:justify;justify-content:space-between}.flex-row[data-v-754f9469]{display:-webkit-box;display:-webkit-flex;display:-ms-flexbox;display:flex;-webkit-box-orient:horizontal;-webkit-box-direction:normal;-webkit-flex-direction:row;-ms-flex-direction:row;flex-direction:row}\n\n.order_nav[data-v-754f9469]{position:fixed;top:%?84?%;left:0;z-index:2}\n.dd_box[data-v-754f9469]{padding:0 %?20?%}.allbtn_right[data-v-754f9469]{text-align:right}\n/* 支付弹框 */.pay_box[data-v-754f9469]{padding:%?30?% %?20?% %?20?%;width:100%;overflow:hidden;position:fixed;bottom:0;left:0;z-index:999;background:#fff;border-radius:%?20?% %?20?% 0 0;-webkit-box-sizing:border-box;box-sizing:border-box}.pay_function[data-v-754f9469]{text-align:center;font-size:%?32?%;color:#434343;height:%?60?%}.bsyyyd[data-v-754f9469]{color:#a0a0a0;font-size:%?46?%;position:absolute;top:%?14?%;right:%?20?%}.pay_funs[data-v-754f9469]{-webkit-box-align:center;-webkit-align-items:center;-ms-flex-align:center;align-items:center;position:relative}.pay_funsimg[data-v-754f9469]{width:10%;text-align:center}.pay_funsimg uni-image[data-v-754f9469]{width:%?30?%;height:%?28?%}.pay_moneys[data-v-754f9469]{-webkit-box-align:center;-webkit-align-items:center;-ms-flex-align:center;align-items:center;width:89%;padding:%?30?% 0;border-bottom:%?2?% solid #eee}.ye_less[data-v-754f9469]{position:absolute;top:0;left:0;background:hsla(0,0%,100%,.33);width:100%;height:100%}.pay_submit[data-v-754f9469]{background:#e54a48;color:#fff;font-size:%?28?%;margin-top:%?50?%;border-radius:%?40?%}.no_jf[data-v-754f9469]{font-size:%?20?%;color:#f6f6f6;background:#f6f6f6;border-radius:50%;width:%?34?%;height:%?34?%;line-height:%?34?%;text-align:center;-webkit-box-sizing:border-box;box-sizing:border-box;border:%?2?% solid #a0a0a0}.choose_jf[data-v-754f9469]{font-size:%?20?%;color:#fff;background:#e95d3c;border-radius:50%;width:%?34?%;height:%?34?%;line-height:%?34?%;text-align:center;-webkit-box-sizing:border-box;box-sizing:border-box}\n/* 列表为空 */.pageno_address[data-v-754f9469]{display:block;margin:%?200?% auto %?60?%;width:%?280?%;height:%?140?%}",""])},"65d2":function(t,i,a){var e=a("40c5");"string"===typeof e&&(e=[[t.i,e,""]]),e.locals&&(t.exports=e.locals);var s=a("4f06").default;s("f820aecc",e,!0,{sourceMap:!1,shadowMode:!1})},7138:function(t,i,a){"use strict";var e=function(){var t=this,i=t.$createElement,a=t._self._c||i;return a("v-uni-view",[a("v-uni-scroll-view",{staticClass:"order_nav",attrs:{"scroll-x":""}},[t._l(t.nav,function(i,e){return[a("v-uni-view",{key:e+"_0",class:["order_nav_single",t.type==i.id?"order_nav_single_on":""],attrs:{"data-id":i.id,"data-nav":i.nav},on:{click:function(i){i=t.$handleEvent(i),t.chonxhq(i)}}},[t._v(t._s(i.text))])]})],2),a("v-uni-view",{staticClass:"order_list_contentbox",staticStyle:{"padding-top":"80rpx"}},t._l(t.orderinfo,function(i,e){return a("v-uni-view",{key:e,staticClass:"dd_box"},[a("v-uni-view",{staticClass:"order_list_single",attrs:{"data-order":i.order_id},on:{click:function(i){i=t.$handleEvent(i),t.orderinfoGo(i)}}},[a("v-uni-view",{staticClass:"order_list_head hbj"},[a("v-uni-view",{staticClass:"order_list_head_left flex1"},[t._v("订单号："+t._s(i.order_id))]),0==i.flag?a("v-uni-view",{staticClass:"order_list_head_right"},[t._v("待付款")]):t._e(),1==i.flag&&1==i.nav?a("v-uni-view",{staticClass:"order_list_head_right"},[t._v("待发货")]):t._e(),1==i.flag&&2==i.nav?a("v-uni-view",{staticClass:"order_list_head_right"},[t._v("待消费")]):t._e(),2==i.flag?a("v-uni-view",{staticClass:"order_list_head_right"},[t._v("已完成")]):t._e(),-1==i.flag?a("v-uni-view",{staticClass:"order_list_head_right"},[t._v("已过期")]):t._e(),4==i.flag?a("v-uni-view",{staticClass:"order_list_head_right"},[t._v("已发货")]):t._e(),5==i.flag?a("v-uni-view",{staticClass:"order_list_head_right"},[t._v("订单被取消")]):t._e(),6==i.flag?a("v-uni-view",{staticClass:"order_list_head_right"},[t._v("订单取消中")]):t._e(),7==i.flag?a("v-uni-view",{staticClass:"order_list_head_right"},[t._v("退货审核中")]):t._e(),8==i.flag?a("v-uni-view",{staticClass:"order_list_head_right"},[t._v("退货成功")]):t._e(),9==i.flag?a("v-uni-view",{staticClass:"order_list_head_right"},[t._v("退货失败")]):t._e()],1),a("v-uni-view",{staticClass:"order_list_productbox"},[a("v-uni-view",{staticClass:"order_list_product hbj"},[a("v-uni-image",{staticClass:"product_img",attrs:{src:i.thumb,mode:"aspectFill"}}),a("v-uni-view",{staticClass:"order_list_product_center"},[a("v-uni-view",{staticClass:"order_product_title text_hide"},[t._v(t._s(i.product))]),a("v-uni-view",{staticClass:"order_product_des text_hide"})],1),a("v-uni-view",{staticClass:"flex1"}),a("v-uni-view",[a("v-uni-view",{staticClass:"order_product_price"},[a("v-uni-text",[t._v("￥")]),t._v(t._s(i.price))],1),a("v-uni-view",{staticClass:"order_product_count"},[t._v("X"+t._s(i.num))])],1)],1)],1),a("v-uni-view",{staticClass:"order_list_pricebox",on:{click:function(i){i.stopPropagation(),i=t.$handleEvent(i)}}},[a("v-uni-view",{staticClass:"order_list_price_left"},[t._v("共"+t._s(i.num)+"件商品，实付："),a("v-uni-text",{staticStyle:{color:"#f4361d"}},[t._v("￥"),a("v-uni-text",{staticStyle:{"font-size":"28rpx"}},[t._v(t._s(i.num*i.price))])],1),i.kuaidi?a("v-uni-text",{staticStyle:{color:"#969696"}},[t._v("（"+t._s("-1"==i.kuaidi?"商家配送":i.kuaidi)+" "+t._s(i.kuaidihao?"- "+i.kuaidihao:"")+"）")]):t._e()],1),0==i.flag?a("v-uni-view",{staticClass:"allbtn_right"},[a("v-uni-view",{staticClass:"order_list_price_btn",attrs:{"data-order":i.order_id,"data-pid":i.pid},on:{click:function(i){i=t.$handleEvent(i),t.paybox(i)}}},[t._v("立即付款")])],1):t._e(),1==i.flag||2==i.flag||6==i.flag?a("v-uni-view",{staticClass:"allbtn_right"},[a("v-uni-view",{staticClass:"order_list_price_btn",attrs:{"data-order":i.order_id},on:{click:function(i){i=t.$handleEvent(i),t.orderinfoGo(i)}}},[t._v("查看详情")])],1):t._e(),4==i.flag?a("v-uni-view",{staticClass:"allbtn_right"},[a("v-uni-view",{staticClass:"order_list_price_btn",attrs:{"data-order":i.order_id},on:{click:function(i){i=t.$handleEvent(i),t.qrshouh(i)}}},[t._v("确认收货")]),a("v-uni-view",{staticClass:"order_list_price_btn",attrs:{"data-kuaidi":i.kuaidi,"data-kuaidihao":i.kuaidihao},on:{click:function(i){i=t.$handleEvent(i),t.wlinfo(i)}}},[t._v("查看物流")])],1):t._e(),6==i.flag||7==i.flag||9==i.flag?a("v-uni-view",{staticClass:"allbtn_right"},[a("v-uni-view",{staticClass:"order_list_price_btn",on:{click:function(i){i=t.$handleEvent(i),t.makePhoneCallB(i)}}},[t._v("联系商家")])],1):t._e(),2==i.flag&&1==i.assess?a("v-uni-view",{staticClass:"allbtn_right"},[a("v-uni-view",{staticClass:"order_list_price_btn",attrs:{"data-order":i.order_id,"data-type":"miaosha"},on:{click:function(i){i=t.$handleEvent(i),t.goevaluate(i)}}},[t._v("我要评价")])],1):t._e(),2==i.flag&&2==i.assess?a("v-uni-view",{staticClass:"allbtn_right"},[a("v-uni-view",{staticClass:"order_list_price_btn",staticStyle:{color:"green","border-color":"green"}},[t._v("已评价")])],1):t._e(),5==i.flag&&i.qxbeizhu!=t.NULL?a("v-uni-view",{staticClass:"allbtn_right"},[a("v-uni-view",{staticClass:"order_list_msg"},[t._v("商家留言："),a("v-uni-text",[t._v(t._s(i.qxbeizhu))])],1)],1):t._e(),8==i.flag?a("v-uni-view",{staticClass:"allbtn_right"},[a("v-uni-view",{staticClass:"order_list_price_btn",attrs:{"data-pid":i.pid},on:{click:function(i){i=t.$handleEvent(i),t.orderagain(i)}}},[t._v("再次下单")])],1):t._e()],1)],1)],1)}),1),0==t.orderinfo_length?[a("v-uni-image",{staticClass:"pageno_address",attrs:{src:t.$imgurl+"no_search.png"}}),a("v-uni-view",{staticStyle:{"text-align":"center",color:"#666"}},[t._v("还没有相关订单哟~")])]:t._e(),1==t.baseinfo.tabbar_t?a("copyright",{attrs:{baseinfo:t.baseinfo}}):t._e(),a("myfooter",{attrs:{page_signs:t.page_signs,baseinfo:t.baseinfo}}),1==t.showPay?[a("v-uni-view",{staticClass:"mask",on:{click:function(i){i=t.$handleEvent(i),t.payboxclose(i)}}}),a("v-uni-view",{staticClass:"pay_box"},[a("v-uni-view",{staticClass:"pay_function"},[a("v-uni-view",[t._v("选择支付方式")]),a("v-uni-view",{staticClass:"iconfont icon-x-guanbi bsyyyd",on:{click:function(i){i=t.$handleEvent(i),t.payboxclose(i)}}})],1),a("v-uni-view",{staticClass:"pay_funs flex-row"},[a("v-uni-view",{staticClass:"pay_funsimg"},[a("v-uni-image",{attrs:{src:t.$imgurl+"yue.png",mode:"aspectFit"}})],1),a("v-uni-view",{staticClass:"pay_moneys flex_bflow"},[a("v-uni-view",[t._v("余额支付（剩余：￥"+t._s(t.mymoney)+"元）")]),1==t.mymoney_pay?a("v-uni-view",{staticClass:"iconfont icon-x-gou",class:[0==t.choosepayf?"choose_jf":"no_jf"],attrs:{"data-pay_type":1,"data-type":0},on:{click:function(i){i=t.$handleEvent(i),t.choosepay(i)}}}):t._e()],1),2==t.mymoney_pay?a("v-uni-view",{staticClass:"ye_less"}):t._e()],1),t.pay_money>0?[1==t.h5_wxpay?a("v-uni-view",{staticClass:"pay_funs flex-row"},[a("v-uni-view",{staticClass:"pay_funsimg"},[a("v-uni-image",{attrs:{src:t.$imgurl+"weix.png",mode:""}})],1),a("v-uni-view",{staticClass:"pay_moneys flex_bflow"},[a("v-uni-view",[t._v("微信支付")]),a("v-uni-view",{staticClass:"iconfont icon-x-gou",class:[1==t.choosepayf?"choose_jf":"no_jf"],attrs:{"data-pay_type":2,"data-type":1},on:{click:function(i){i=t.$handleEvent(i),t.choosepay(i)}}})],1)],1):t._e(),1==t.h5_alipay?a("v-uni-view",{staticClass:"pay_funs flex-row"},[a("v-uni-view",{staticClass:"pay_funsimg"},[a("v-uni-image",{attrs:{src:t.$imgurl+"zhifb.png",mode:"aspectFit"}})],1),a("v-uni-view",{staticClass:"pay_moneys flex_bflow"},[a("v-uni-view",[t._v("支付宝支付")]),a("v-uni-view",{staticClass:"iconfont icon-x-gou",class:[2==t.choosepayf?"choose_jf":"no_jf"],attrs:{"data-pay_type":2,"data-type":2},on:{click:function(i){i=t.$handleEvent(i),t.choosepay(i)}}})],1)],1):t._e()]:t._e(),a("v-uni-form",{attrs:{"report-submit":"true"},on:{submit:function(i){i=t.$handleEvent(i),t.pay(i)}}},[a("v-uni-button",{staticClass:"pay_submit",attrs:{formType:"submit"}},[t._v("确定")])],1)],2)]:t._e()],2)},s=[];a.d(i,"a",function(){return e}),a.d(i,"b",function(){return s})},"7f36":function(t,i,a){"use strict";var e=a("65d2"),s=a.n(e);s.a},"93ad":function(t,i,a){"use strict";Object.defineProperty(i,"__esModule",{value:!0}),i.default=void 0;var e=a("55f5"),s={data:function(){return{$imgurl:this.$imgurl,page_signs:"/pagesFlashSale/orderlist_dan/orderlist_dan",page:1,morePro:!1,baseinfo:{},orderinfo:[],orderinfo_length:0,type:9,nav:[{id:9,text:"全部"},{id:0,text:"待付款"},{id:1,text:"待消费",nav:2},{id:11,text:"待发货",nav:1},{id:4,text:"已发货"},{id:2,text:"已完成"},{id:-1,text:"已过期"},{id:6,text:"售后"},{id:5,text:"商家已取消"}],showPay:0,choosepayf:0,mymoney:0,mymoney_pay:1,is_submit:1,order_id:0,h5_wxpay:0,h5_alipay:0,pay_type:1,pay_money:0}},onLoad:function(t){this._baseMin(this);var i=0;t.fxsid&&(i=t.fxsid),this.fxsid=i,t.flag&&(this.flag=t.flag),t.type1&&(this.type=t.type),e.h5login(i,function(){})},onShow:function(){this.page=1,this.getList()},onPullDownRefresh:function(){this.page=1,this.getList(),uni.stopPullDownRefresh()},onReachBottom:function(){var t=this,i=1*t.page+1;uni.request({url:t.$baseurl+"doPageMyorder",data:{page:i,uniacid:this.$uniacid,type:this.type,suid:uni.getStorageSync("suid"),is_more:0},success:function(a){t.orderinfo=t.orderinfo.concat(a.data.data.list),t.page=i}})},methods:{getList:function(){var t=this,i=uni.getStorageSync("suid");uni.request({url:t.$baseurl+"doPageMyorder",data:{uniacid:t.$uniacid,suid:i,type:t.type,is_more:0},success:function(i){t.allnum=i.data.data.allnum,t.orderinfo=i.data.data.list,t.orderinfo_length=i.data.data.list.length,t.mymoney=i.data.data.mymoney},fail:function(t){}})},goevaluate:function(t){var i=t.currentTarget.dataset.order,a=t.currentTarget.dataset.type;uni.navigateTo({url:"/pages/evaluate/evaluate?order_id="+i+"&type="+a})},chonxhq:function(t){var i=this,a=t.currentTarget.dataset.id,e=t.currentTarget.dataset.nav||"";this.type=a,this.morePro=!1,this.page=1,11==a&&(a=1),uni.request({url:i.$baseurl+"doPageMyorder",data:{uniacid:i.$uniacid,suid:uni.getStorageSync("suid"),type:a,nav:e,is_more:0},success:function(t){i.orderinfo=t.data.data.list,i.orderinfo_length=t.data.data.list.length},fail:function(t){}})},orderinfoGo:function(t){var i=t.currentTarget.dataset.order;uni.navigateTo({url:"/pagesFlashSale/orderDetail_dan/orderDetail_dan?orderid="+i})},makePhoneCallB:function(t){var i=this,a=i.baseinfo.tel_b;uni.makePhoneCall({phoneNumber:a})},wlinfo:function(t){var i=t.currentTarget.dataset.kuaidi,a=t.currentTarget.dataset.kuaidihao;uni.navigateTo({url:"/pages/logistics_state/logistics_state?kuaidi="+i+"&kuaidihao="+a})},qrshouh:function(t){var i=this,a=t.currentTarget.dataset.order,e=uni.getStorageSync("suid");uni.showModal({title:"提示",content:"确认收货吗？",success:function(t){t.confirm&&uni.request({url:i.$baseurl+"doPagedanshouhuo",data:{uniacid:i.$uniacid,suid:e,orderid:a},success:function(t){uni.showToast({title:"收货成功！",success:function(t){setTimeout(function(){i.page=1,i.getList()},1500)}})}})}})},paybox:function(t){var i=this,a=0,e=this.orderinfo,s=t.currentTarget.dataset.order;this.order_id=s;for(var n=0;n<e.length;n++)e[n]["order_id"]==s&&(a=e[n]["price"]);this.pay_money=a;var o=this.mymoney;a>0&&uni.request({url:this.$baseurl+"doPageGetH5payshow",data:{uniacid:this.$uniacid,suid:this.suid},success:function(t){0==t.data.data.ali&&0==t.data.data.wx?(i.h5_wxpay=0,i.h5_alipay=0):(0==t.data.data.wx?i.h5_wxpay=0:i.h5_wxpay=1,0==t.data.data.ali?i.h5_alipay=0:i.h5_alipay=1,a>o&&(i.mymoney_pay=2,i.pay_type=2,1==i.h5_wxpay&&1==i.h5_alipay?i.choosepayf=1:1==i.h5_wxpay?i.choosepayf=1:i.choosepayf=2))}}),0==this.showPay&&(this.showPay=1)},payboxclose:function(){1==this.showPay&&(this.showPay=0)},choosepay:function(t){this.pay_type=t.currentTarget.dataset.pay_type,this.choosepayf=t.currentTarget.dataset.type},pay:function(t){var i=this.is_submit;if(2==i)return!1;this.is_submit=2;this.$uniacid,this.suid,this.source;var a=this.pay_type,e=this.pay_money,s=this.order_id;t.detail.formId;1==a?this.pay1(s):1==this.choosepayf?this._wxh5pay(this,e,"miaosha",s,"/pagesFlashSale/orderlist_dan/orderlist_dan?type=9"):this._alih5pay(this,e,7,s,"/pagesFlashSale/orderlist_dan/orderlist_dan?type=9")},pay1:function(t){var i=this;uni.showModal({title:"请注意",content:"您将使用余额支付"+i.pay_money+"元",success:function(a){a.confirm?(i.payover_do(t),uni.showLoading({title:"下单中...",mask:!0})):uni.redirectTo({url:"/pagesFlashSale/orderlist_dan/orderlist_dan"})}})},payover_do:function(t){var i=this,a=i.pay_money,e=(i.mymoney,uni.getStorageSync("suid")),s="";uni.request({url:i.$baseurl+"doPagepaynotify",data:{out_trade_no:t,suid:e,payprice:a,types:"miaosha",flag:0,formId:i.formId,uniacid:i.$uniacid,openid:s,source:uni.getStorageSync("source")},success:function(t){"失败"==t.data.data.message?uni.showToast({title:"付款失败, 请刷新后重新付款！",icon:"none",mask:!0,success:function(){uni.navigateTo({url:"/pagesFlashSale/orderlist_dan/orderlist_dan?type=9"}),uni.hideLoading()}}):uni.showToast({title:"购买成功！",icon:"success",mask:!0,success:function(){uni.navigateTo({url:"/pagesFlashSale/orderlist_dan/orderlist_dan?type=9"}),uni.hideLoading()}})}})}}};i.default=s},cb4b:function(t,i,a){"use strict";a.r(i);var e=a("7138"),s=a("38a1");for(var n in s)"default"!==n&&function(t){a.d(i,t,function(){return s[t]})}(n);a("7f36");var o=a("2877"),r=Object(o["a"])(s["default"],e["a"],e["b"],!1,null,"754f9469",null);i["default"]=r.exports}}]);