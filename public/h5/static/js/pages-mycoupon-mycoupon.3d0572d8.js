(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-mycoupon-mycoupon"],{"0685":function(t,i,e){"use strict";e.r(i);var a=e("96f0"),n=e("2f627");for(var o in n)"default"!==o&&function(t){e.d(i,t,function(){return n[t]})}(o);e("cd4f");var s=e("2877"),d=Object(s["a"])(n["default"],a["a"],a["b"],!1,null,"d4a857d0",null);i["default"]=d.exports},"2f627":function(t,i,e){"use strict";e.r(i);var a=e("7da4"),n=e.n(a);for(var o in a)"default"!==o&&function(t){e.d(i,t,function(){return a[t]})}(o);i["default"]=n.a},"7da4":function(t,i,e){"use strict";Object.defineProperty(i,"__esModule",{value:!0}),i.default=void 0;var a=e("2c90"),n={data:function(){return{$imgurl:this.$imgurl,baseinfo:{},page_signs:"pages/mycoupon/mycoupon",couponlist:[],youhqid:0,hxmm:"",showhx:0,needAuth:!1,needBind:!1,hide_type:0,couid:"",hxmm_list:[{val:"",fs:!0},{val:"",fs:!1},{val:"",fs:!1},{val:"",fs:!1},{val:"",fs:!1},{val:"",fs:!1}]}},onLoad:function(t){var i=this,e=uni.getStorageSync("suid");this._baseMin(this);var n=0;t.fxsid&&(n=t.fxsid),this.fxsid=n,a.h5login(n,function(){i.getList()}),e||(this.needAuth=!0)},onPullDownRefresh:function(){this.getList(),uni.stopPullDownRefresh()},methods:{showType:function(t){this.couid=t.currentTarget.dataset.couid,0==this.hide_type?this.hide_type=1:this.hide_type=0},cell:function(){this.needAuth=!1},closeAuth:function(){this.needAuth=!1,this.needBind=!0},closeBind:function(){this.needBind=!1,this.getList()},getList:function(){var t=this,i=uni.getStorageSync("suid");uni.request({url:t.$baseurl+"doPagemycoupon",data:{suid:i,flag:1,uniacid:t.$uniacid},success:function(i){t.couponlist=i.data.data,uni.hideNavigationBarLoading(),uni.stopPullDownRefresh()}})},ycoupp:function(){uni.redirectTo({url:"/pages/coupon/coupon"})},hxshow:function(t){this.showhx=1,this.youhqid=t.currentTarget.id},hxhide:function(){this.showhx=0,this.hxmm="";for(var t=0;t<this.hxmm_list.length;t++)this.hxmm_list[t].fs=!1,this.hxmm_list[t].val=""},hxmmInput:function(t){for(var i=t.target.value.length,e=0;e<this.hxmm_list.length;e++)this.hxmm_list[e].fs=!1,this.hxmm_list[e].val=t.target.value[e];i&&(this.hxmm_list[i-1].fs=!0),this.hxmm=t.target.value},hxmmpass:function(){var t=this,i=this,e=i.hxmm,a=i.youhqid;e?uni.request({url:i.$baseurl+"Hxyhq",data:{hxmm:e,youhqid:a,uniacid:i.$uniacid},header:{"content-type":"application/json"},success:function(i){var e=i.data.data;if(0==e){uni.showModal({title:"提示",content:"核销密码不正确！",showCancel:!1}),t.hxmm="";for(var a=0;a<t.hxmm_list.length;a++)t.hxmm_list[a].fs=!1,t.hxmm_list[a].val=""}else uni.showToast({title:"成功",icon:"success",duration:2e3,success:function(t){setTimeout(function(){uni.redirectTo({url:"/pages/mycoupon/mycoupon"})},2e3)}})}}):uni.showModal({title:"提示",content:"核销密码必填！",showCancel:!1})}}};i.default=n},"96f0":function(t,i,e){"use strict";var a=function(){var t=this,i=t.$createElement,e=t._self._c||i;return t.$imgurl?e("v-uni-view",{staticStyle:{position:"fixed","overflow-y":"scroll",top:"0",left:"0",right:"0",bottom:"0"}},[t.needAuth?e("auth",{attrs:{needAuth:t.needAuth},on:{closeAuth:function(i){i=t.$handleEvent(i),t.closeAuth(i)},cell:function(i){i=t.$handleEvent(i),t.cell(i)}}}):t._e(),t.needBind?e("bindPhone",{attrs:{needBind:t.needBind},on:{closeBind:function(i){i=t.$handleEvent(i),t.closeBind(i)}}}):t._e(),e("v-uni-view",{staticClass:"toubu1"},[e("v-uni-view",{staticClass:"yhq",on:{click:function(i){i=t.$handleEvent(i),t.ycoupp(i)}}},[t._v("可领优惠券")]),e("v-uni-view",{staticClass:"wyhq check"},[e("v-uni-view",{staticClass:"check_on"},[t._v("我的优惠券")])],1)],1),e("v-uni-view",{staticStyle:{height:"172rpx"}}),e("v-uni-view",{staticClass:"couponlist_box"},[t._l(t.couponlist,function(i,a){return e("v-uni-view",{key:a,staticClass:"youhqs"},[e("v-uni-view",{staticClass:"yuan_left"}),e("v-uni-view",{staticClass:"yuan_right"}),e("v-uni-view",{staticClass:"wkk"},[e("v-uni-view",{staticClass:"wkk_top"},[e("v-uni-view",{staticClass:"shujl",style:{color:t.baseinfo.base_color}},[e("span",{staticClass:"jiagq"},[e("span",{staticStyle:{"font-size":"42rpx","font-weight":"normal"}},[t._v("￥")]),t._v(t._s(i.price))])]),e("v-uni-view",{staticClass:"qitxx"},[e("v-uni-view",{staticClass:"yhq_t"},[t._v(t._s(i.title))]),e("v-uni-view",{staticClass:"xiaozi"},[0==i.pay_money?e("v-uni-text",[t._v("任意金额可用")]):e("v-uni-text",[t._v("满"+t._s(i.pay_money)+"可用")])],1),e("v-uni-view",{staticClass:"xiaozi",staticStyle:{"margin-top":"18rpx"}},[t._v("有效期："),0==i.btime&&0==i.etime?e("span",[t._v("永久有效")]):t._e(),0==i.btime&&0!=i.etime?e("span",[t._v(t._s(i.etime)+"前有效")]):t._e(),0!=i.btime&&0==i.etime?e("span",[t._v(t._s(i.btime)+"后有效")]):t._e(),0!=i.btime&&0!=i.etime&&i.btime==i.etime?e("span",[t._v(t._s(i.btime)+"当天有效")]):t._e(),0!=i.btime&&0!=i.etime&&i.btime!=i.etime?e("span",[t._v(t._s(i.btime)+"至"+t._s(i.etime))]):t._e()])],1)],1),e("v-uni-view",{staticClass:"include_types"},[1==i.use_type?e("v-uni-view",{staticClass:"include_items"},[e("v-uni-view",{class:["include_words",1==t.hide_type&&t.couid==a?"":"include_words_no"]},[t._v("适用品类："+t._s(i.use_class))]),e("v-uni-view",{class:["iconfont",1==t.hide_type&&t.couid==a?"icon-x-shang":"icon-x-xia"],attrs:{"data-couid":a},on:{click:function(i){i=t.$handleEvent(i),t.showType(i)}}})],1):e("v-uni-view",{staticClass:"include_items"},[e("v-uni-view",{staticClass:"include_words"},[t._v("适用品类：全类目")])],1),e("v-uni-view",[0==i.flag&&1==i.show_status?e("v-uni-button",{staticClass:"ljlq",style:{background:t.baseinfo.base_color},attrs:{id:i.id},on:{click:function(i){i=t.$handleEvent(i),t.hxshow(i)}}},[t._v("立即使用")]):t._e(),0==i.flag&&0==i.show_status?e("v-uni-button",{staticClass:"ljlq2"},[t._v("立即使用")]):t._e(),2==i.flag?e("v-uni-button",{staticClass:"ljlq2"},[t._v("立即使用")]):t._e(),1==i.flag?e("v-uni-button",{staticClass:"ljlq2"},[t._v("已使用")]):t._e()],1)],1),2==i.flag?e("v-uni-view",{staticClass:"time_over"},[e("v-uni-image",{staticClass:"time_over_img",attrs:{src:t.$imgurl+"no_mycoupon.png",mode:""}})],1):t._e()],1)],1)}),e("copyright",{attrs:{baseinfo:t.baseinfo}}),e("myfooter",{attrs:{page_signs:t.page_signs,baseinfo:t.baseinfo}}),1==t.showhx?e("v-uni-view",{staticClass:"hx_con"},[e("v-uni-view",{staticClass:"mask",on:{click:function(i){i=t.$handleEvent(i),t.hxhide(i)}}}),e("v-uni-view",{staticClass:"hexiao"},[e("v-uni-view",{staticClass:"hx_titc",on:{click:function(i){i=t.$handleEvent(i),t.hxhide(i)}}},[e("v-uni-view",{staticClass:"iconfont icon-x-guanbi"})],1),e("v-uni-view",{staticClass:"hexiao_mima"},[e("v-uni-view",{staticClass:"hx_tit"},[t._v("请输入核销密码")]),e("v-uni-view",{staticClass:"code"},t._l(t.hxmm_list,function(i,a){return e("v-uni-text",{key:a,class:[i.fs?"focus":""],attrs:{type:"number"},domProps:{textContent:t._s(i.val)}})}),1),e("v-uni-view",{staticClass:"code_input"},[e("v-uni-input",{attrs:{type:"number",focus:"true","hover-class":"none",maxlength:"6",value:t.hxmm},on:{input:function(i){i=t.$handleEvent(i),t.hxmmInput(i)}}})],1),e("v-uni-view",{staticClass:"hx_yes"},[e("v-uni-view",{staticClass:"hx_btn",style:{background:t.baseinfo.base_color,color:"#fff"},on:{click:function(i){i=t.$handleEvent(i),t.hxmmpass(i)}}},[t._v("确认消费")])],1)],1)],1)],1):t._e()],2)],1):t._e()},n=[];e.d(i,"a",function(){return a}),e.d(i,"b",function(){return n})},cd4f:function(t,i,e){"use strict";var a=e("ea7f"),n=e.n(a);n.a},d5ee:function(t,i,e){i=t.exports=e("2350")(!1),i.push([t.i,".toubu[data-v-d4a857d0]{position:fixed;background:#fff;width:100%;height:%?86?%;line-height:%?86?%;text-align:center;top:0;z-index:10;overflow:visible}.toubu1[data-v-d4a857d0]{position:fixed;width:100%;height:%?86?%;line-height:%?86?%;text-align:center;top:%?86?%;z-index:10;overflow:visible;background:#fff}.check[data-v-d4a857d0]{color:#f4361d}.check_on[data-v-d4a857d0]{border-bottom:%?2?% solid #f4361d}.yhq[data-v-d4a857d0]{display:inline-block;width:50%;color:#969696}.wyhq[data-v-d4a857d0]{display:inline-block;width:50%;-webkit-box-sizing:border-box;box-sizing:border-box;padding:0 %?116?%}.couponlist_box[data-v-d4a857d0]{padding:%?20?%}.youhqs[data-v-d4a857d0]{margin-bottom:%?20?%;border-radius:%?10?%;overflow:hidden;position:relative;background:#fff}.yuan_left[data-v-d4a857d0]{position:absolute;top:%?184?%;left:%?-10?%;background:#f6f6f6;display:block;height:%?20?%;width:%?20?%;border-radius:%?20?%;z-index:1}.yuan_right[data-v-d4a857d0]{position:absolute;top:%?184?%;right:%?-10?%;background:#f6f6f6;display:block;height:%?20?%;width:%?20?%;border-radius:%?20?%;z-index:1}.wkk[data-v-d4a857d0]{position:relative}.time_over[data-v-d4a857d0]{position:absolute;top:0;left:0;width:100%;height:100%;background:hsla(0,0%,100%,.5)}.time_over_img[data-v-d4a857d0]{position:absolute;top:%?40?%;right:%?24?%;width:%?124?%;height:%?92?%}.wkk_top[data-v-d4a857d0]{display:-webkit-box;display:-webkit-flex;display:-ms-flexbox;display:flex;border-bottom:%?2?% dashed #e4e4e4;height:%?194?%;-webkit-box-sizing:border-box;box-sizing:border-box}.shujl[data-v-d4a857d0]{color:#fff;text-align:center;width:30%;overflow-y:hidden;margin:auto}.jiagq[data-v-d4a857d0]{font-size:%?62?%;font-weight:700;display:block;line-height:%?50?%;text-indent:%?-14?%}.qitxx[data-v-d4a857d0]{padding-left:%?10?%;padding-top:%?28?%;width:70%;-webkit-box-sizing:border-box;box-sizing:border-box}.yhq_t[data-v-d4a857d0]{font-size:%?28?%;color:#434343;margin-bottom:%?12?%}.xiaozi[data-v-d4a857d0]{font-size:%?22?%;color:#666}\n/* 适用品类 */.include_types[data-v-d4a857d0]{display:-webkit-box;display:-webkit-flex;display:-ms-flexbox;display:flex;padding:%?20?%;font-size:%?22?%}.include_items[data-v-d4a857d0]{display:-webkit-box;display:-webkit-flex;display:-ms-flexbox;display:flex;width:82%;-webkit-box-sizing:border-box;box-sizing:border-box;padding-right:%?60?%;padding-top:%?10?%;-webkit-box-align:start;-webkit-align-items:flex-start;-ms-flex-align:start;align-items:flex-start}.include_words[data-v-d4a857d0]{width:96%}.include_words_no[data-v-d4a857d0]{white-space:nowrap;overflow:hidden;-o-text-overflow:ellipsis;text-overflow:ellipsis}.include_items .iconfont[data-v-d4a857d0]{font-size:%?24?%;margin-left:%?20?%}\n/* 使用状态 */.ljlq[data-v-d4a857d0]{font-size:%?22?%;color:#fff;border-radius:%?50?%;width:%?124?%;height:%?50?%;line-height:%?50?%;-webkit-box-sizing:border-box;box-sizing:border-box;padding:0}.ljlq2[data-v-d4a857d0]:after,.ljlq[data-v-d4a857d0]:after{border:none}.ljlq2[data-v-d4a857d0]{font-size:%?22?%;background-color:#ccc;color:#fff;width:%?124?%;height:%?50?%;line-height:%?50?%;-webkit-box-sizing:border-box;box-sizing:border-box;border-radius:%?50?%;padding:0}\n/* 核销 */.hx_con[data-v-d4a857d0]{position:fixed;left:0;top:0;width:100%;height:100%;z-index:950}.hexiao[data-v-d4a857d0]{border-radius:%?10?%;width:%?580?%;height:%?400?%;background:#fff;position:absolute;left:%?84?%;top:18%;z-index:1000}.hx_titc[data-v-d4a857d0]{position:absolute;top:%?16?%;right:%?20?%}.hexiao_mima[data-v-d4a857d0]{margin-top:%?42?%;position:relative}.hexiao .hx_tit[data-v-d4a857d0]{font-size:%?32?%;color:#232323;text-align:center}\n/* 输入核销密码 */.code[data-v-d4a857d0]{margin:%?80?% auto %?40?%;display:-webkit-box;display:-webkit-flex;display:-ms-flexbox;display:flex;-webkit-box-orient:horizontal;-webkit-box-direction:normal;-webkit-flex-direction:row;-ms-flex-direction:row;flex-direction:row;-webkit-box-pack:justify;-webkit-justify-content:space-between;-ms-flex-pack:justify;justify-content:space-between;width:%?460?%}.code uni-text[data-v-d4a857d0]{width:%?70?%;height:%?70?%;line-height:%?28?%;border:none;border-bottom:%?2?% solid #b2bfbd;text-align:center;color:#4c4e60;font-size:%?48?%}.code uni-text.focus[data-v-d4a857d0]{border-color:#4c79fa}.code_input[data-v-d4a857d0]{position:absolute;top:%?90?%;width:100%;height:%?80?%;opacity:0;overflow:hidden}.code_input uni-input[data-v-d4a857d0]{position:absolute;left:-50%;width:200%;height:%?80?%;line-height:%?80?%;font-size:%?40?%;text-align:left;outline:none;border:none;background:none;z-index:666}\n/* 按钮 */.hexiao .hx_yes[data-v-d4a857d0]{text-align:center;padding:0 %?40?%}.hexiao .hx_btn[data-v-d4a857d0]{color:#000;font-size:%?26?%;border-radius:%?70?%;height:%?70?%;line-height:%?70?%;width:100%}",""])},ea7f:function(t,i,e){var a=e("d5ee");"string"===typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);var n=e("4f06").default;n("384cbce3",a,!0,{sourceMap:!1,shadowMode:!1})}}]);