(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pagesPluginShop-manage_prolist-manage_prolist"],{"198d":function(n,t,i){"use strict";i.r(t);var e=i("5c5fd"),a=i("6299");for(var o in a)"default"!==o&&function(n){i.d(t,n,function(){return a[n]})}(o);i("455b");var r=i("2877"),s=Object(r["a"])(a["default"],e["a"],e["b"],!1,null,"17c22b48",null);t["default"]=s.exports},"455b":function(n,t,i){"use strict";var e=i("4986"),a=i.n(e);a.a},4986:function(n,t,i){var e=i("8a9b");"string"===typeof e&&(e=[[n.i,e,""]]),e.locals&&(n.exports=e.locals);var a=i("4f06").default;a("9ce2ad70",e,!0,{sourceMap:!1,shadowMode:!1})},"5c5fd":function(n,t,i){"use strict";var e=function(){var n=this,t=n.$createElement,i=n._self._c||t;return n.$imgurl?i("v-uni-view",{staticClass:"container"},[i("v-uni-scroll-view",{staticClass:"order_nav",attrs:{"scroll-x":""}},[i("v-uni-view",{class:["order_nav_single",1==n.flag?"order_nav_single_on":""],attrs:{"data-flag":"1"},on:{click:function(t){arguments[0]=t=n.$handleEvent(t),n.changflag.apply(void 0,arguments)}}},[n._v("已审核")]),i("v-uni-view",{class:["order_nav_single",0==n.flag?"order_nav_single_on":""],attrs:{"data-flag":"0"},on:{click:function(t){arguments[0]=t=n.$handleEvent(t),n.changflag.apply(void 0,arguments)}}},[n._v("待审核")])],1),i("v-uni-view",{staticClass:"weui-tab__content"},n._l(n.prolist,function(t,e){return i("v-uni-view",{key:e,staticClass:"main-products"},[i("v-uni-view",{staticClass:"title"},[i("v-uni-view",{staticClass:"title-time"},[n._v("发布时间："+n._s(t.createtime))]),i("v-uni-view",{staticClass:"title-edit"},[i("v-uni-view",{staticClass:"button",attrs:{"data-pid":t.id},on:{click:function(t){arguments[0]=t=n.$handleEvent(t),n.prodel.apply(void 0,arguments)}}},[i("v-uni-image",{attrs:{src:n.$imgurl+"del.png"}}),n._v("删")],1),i("v-uni-view",{staticClass:"button",attrs:{"data-pid":t.id},on:{click:function(t){arguments[0]=t=n.$handleEvent(t),n.proedit.apply(void 0,arguments)}}},[i("v-uni-image",{attrs:{src:n.$imgurl+"edit.png"}}),n._v("编")],1)],1)],1),i("v-uni-view",{attrs:{"data-id":t.id,"data-flag":t.flag},on:{click:function(t){arguments[0]=t=n.$handleEvent(t),n.goodsDetail.apply(void 0,arguments)}}},[i("v-uni-view",{staticClass:"main"},[i("v-uni-view",[i("v-uni-image",{attrs:{src:t.thumb}})],1),i("v-uni-view",{staticClass:"main-mid"},[i("v-uni-view",{staticClass:"main-mid-top"},[n._v(n._s(t.title)),2==t.flag?i("span",{staticStyle:{color:"#ee3333","margin-left":"20rpx","font-size":"20rpx"}},[n._v("(未上架)")]):n._e()]),i("v-uni-view",{staticClass:"main-mid-foot"},[i("v-uni-text",[n._v("库存："+n._s(t.storage))]),i("v-uni-text",[n._v("销量："+n._s(t.sales))])],1)],1),i("v-uni-view",{staticClass:"main-money"},[i("v-uni-text",{staticClass:"money"},[i("v-uni-text",{staticClass:"money-sign"},[n._v("￥")]),n._v(n._s(t.sellprice))],1)],1)],1)],1)],1)}),1),i("v-uni-navigator",{staticClass:"flex-button",attrs:{url:"../manage_pro/manage_pro"}},[i("v-uni-image",{attrs:{src:n.$imgurl+"add.png"}})],1)],1):n._e()},a=[];i.d(t,"a",function(){return e}),i.d(t,"b",function(){return a})},"5f8c":function(n,t,i){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;i("2f62");var e=i("7131"),a={data:function(){return{$imgurl:this.$imgurl,needAuth:!1,needBind:!1,page_signs:"/pagesPluginShop/manage_prolist/manage_prolist",dataindex:[],prolist:[],flag:1}},onLoad:function(n){var t=this;this._baseMin(this),uni.setNavigationBarTitle({title:"商品列表"});var i=0;e.h5login(i,function(){t.getprolist(1)})},onPullDownRefresh:function(){this.getprolist(this.flag),uni.stopPullDownRefresh()},methods:{getprolist:function(n){var t=this;uni.request({url:t.$baseurl+"dopageprolist",data:{status:n,suid:uni.getStorageSync("suid"),uniacid:t.$uniacid},success:function(n){t.prolist=n.data.data}})},changflag:function(n){var t=this,i=n.currentTarget.dataset.flag;void 0!=i&&(t.flag=i),t.getprolist(i)},proedit:function(n){var t=n.currentTarget.dataset.pid;uni.navigateTo({url:"/pagesPluginShop/manage_pro/manage_pro?id="+t})},prodel:function(n){var t=this,i=n.currentTarget.dataset.pid;uni.showModal({title:"提示",content:"确定要删除这个商品吗？",showCancel:!0,cancelText:"取消",cancelColor:"#ccc",confirmText:"删除",confirmColor:"#ff0000",success:function(n){n.confirm&&uni.request({url:t.$baseurl+"dopageprodel",data:{pid:i,id:uni.getStorageSync("mlogin"),uniacid:t.$uniacid},cachetime:"30",success:function(n){1==n.data.data&&uni.showModal({title:"提示",content:"删除成功！",showCancel:!1,success:function(n){uni.redirectTo({url:"/pagesPluginShop/manage_prolist/manage_prolist"})}})}})}})},goodsDetail:function(n){var t=n.currentTarget.dataset.id,i=n.currentTarget.dataset.flag;2==i?uni.showToast({title:"商品未上架",icon:"none"}):uni.navigateTo({url:"/pagesPluginShop/goods_detail/goods_detail?id="+t})}}};t.default=a},6299:function(n,t,i){"use strict";i.r(t);var e=i("5f8c"),a=i.n(e);for(var o in e)"default"!==o&&function(n){i.d(t,n,function(){return e[n]})}(o);t["default"]=a.a},"8a9b":function(n,t,i){t=n.exports=i("2350")(!1),t.push([n.i,"\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n/* pages/spgl.wxss */.container[data-v-17c22b48]{height:100%;-webkit-box-orient:vertical;-webkit-box-direction:normal;-webkit-flex-direction:column;flex-direction:column;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-pack:justify;-webkit-justify-content:space-between;justify-content:space-between;box-sizing:border-box;background-color:#fff;font-family:Microsoft Yahei;padding:0;font-size:13px}.order_nav_single_on[data-v-17c22b48]{color:#6671e4;border-bottom:%?4?% solid #6671e4}.order_nav[data-v-17c22b48]{border-bottom:%?20?% solid #f6f6f6}\n\n/*分割线*/.line[data-v-17c22b48]{height:8px;background-color:#f6f6f6;width:100%}\n\n/*内容*/.main-products[data-v-17c22b48]{padding-top:10px;height:120px;border-bottom:8px solid #f6f6f6}.title[data-v-17c22b48]{font-size:12px;display:-webkit-box;display:-webkit-flex;display:flex;\n\t/*row 横向  column 列表  */-webkit-box-orient:horizontal;-webkit-box-direction:normal;-webkit-flex-direction:row;flex-direction:row;padding-bottom:12px;border-bottom:1px solid #eee}.title-time[data-v-17c22b48]{color:#9a9a9a;margin-left:10px;width:70%}.title-edit[data-v-17c22b48]{margin-right:20px;text-align:right;width:30%;\n\t/*row 横向  column 列表  */-webkit-box-orient:horizontal;-webkit-box-direction:normal;-webkit-flex-direction:row;flex-direction:row;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-pack:end;-webkit-justify-content:flex-end;justify-content:flex-end}.title-edit .button[data-v-17c22b48]{margin-left:20px}.title-edit uni-image[data-v-17c22b48]{width:12px;height:12px}.main[data-v-17c22b48]{display:-webkit-box;display:-webkit-flex;display:flex;\n\t/*row 横向  column 列表  */-webkit-box-orient:horizontal;-webkit-box-direction:normal;-webkit-flex-direction:row;flex-direction:row;margin-top:15px;margin-left:15px}.main uni-image[data-v-17c22b48]{width:62px;height:62px}.main-mid[data-v-17c22b48]{width:200px;margin-left:13px}.main-mid-top[data-v-17c22b48]{font-size:14px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.main-mid-foot[data-v-17c22b48]{\n\t/*row 横向  column 列表  */-webkit-box-orient:horizontal;-webkit-box-direction:normal;-webkit-flex-direction:row;flex-direction:row;font-size:12px;color:#9a9a9a;margin-top:7px}.main-mid-foot uni-text[data-v-17c22b48]{margin-right:30px}.main-money[data-v-17c22b48]{margin-left:20px;color:#ea2525}.money-sign[data-v-17c22b48]{font-size:9px}\n\n/*悬浮按钮*/.flex-button[data-v-17c22b48]{float:right;position:fixed;bottom:20px;right:10px;border:0 solid #fff;border-radius:500px;-moz-box-shadow:2px 2px 5px #6671e4;-webkit-box-shadow:2px 2px 5px #6671e4;box-shadow:2px 2px 5px #6671e4;background-color:#6671e4;width:40px;height:40px;overflow:hidden}.flex-button uni-image[data-v-17c22b48]{width:20px;height:20px;margin:10px}",""])}}]);