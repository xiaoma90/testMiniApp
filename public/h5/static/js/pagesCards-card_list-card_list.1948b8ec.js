(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pagesCards-card_list-card_list"],{"1de5":function(t,a,i){a=t.exports=i("2350")(!1),a.push([t.i,"uni-page-body[data-v-41d5772a]{background-color:#f6f6f6}.hbj[data-v-41d5772a]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-orient:horizontal;-webkit-box-direction:normal;-webkit-flex-direction:row;flex-direction:row;-webkit-box-align:center;-webkit-align-items:center;align-items:center}.card_listbox[data-v-41d5772a]{background-color:#fff;border-radius:%?12?%;width:%?710?%;margin:%?30?% auto 0}.card_list_head[data-v-41d5772a]{height:%?145?%;padding:0 %?24?%;border-bottom:%?2?% solid #eee}.card_list_head_left[data-v-41d5772a]{width:%?100?%;height:%?100?%;border-radius:50%;border:%?2?% solid #eee}.card_list_head_center[data-v-41d5772a]{font-size:%?36?%;color:#232323;margin-left:%?20?%;-webkit-box-flex:1;-webkit-flex:1;flex:1}.card_list_head_center uni-text[data-v-41d5772a]{font-size:%?24?%;color:#969696;margin-left:%?8?%}.card_list_head_right[data-v-41d5772a]{width:%?54?%;height:%?54?%}.card_list_content[data-v-41d5772a]{padding:%?24?%;position:relative}.card_list_content_single[data-v-41d5772a]{font-size:%?24?%;color:#969696;margin-bottom:%?20?%}.card_list_content_single uni-text[data-v-41d5772a]{font-family:Arial,Helvetica,sans-serif;color:#232323;font-size:%?24?%;margin-left:%?24?%}.card_list_content_single[data-v-41d5772a]:last-child{margin-bottom:0}.card_list_link[data-v-41d5772a]{width:%?54?%;height:%?54?%;border-radius:50%;background-color:#2f74fd;position:absolute;bottom:%?24?%;right:%?24?%}.card_list_link uni-image[data-v-41d5772a]{position:absolute;margin:auto;left:0;right:0;top:0;bottom:0;width:%?12?%;height:%?21?%}body.?%PAGE?%[data-v-41d5772a]{background-color:#f6f6f6}",""])},6368:function(t,a,i){"use strict";i.r(a);var e=i("b16a"),n=i.n(e);for(var s in e)"default"!==s&&function(t){i.d(a,t,function(){return e[t]})}(s);a["default"]=n.a},a2c1:function(t,a,i){var e=i("1de5");"string"===typeof e&&(e=[[t.i,e,""]]),e.locals&&(t.exports=e.locals);var n=i("4f06").default;n("1836a436",e,!0,{sourceMap:!1,shadowMode:!1})},aabf:function(t,a,i){"use strict";var e=i("a2c1"),n=i.n(e);n.a},b16a:function(t,a,i){"use strict";Object.defineProperty(a,"__esModule",{value:!0}),a.default=void 0;var e=i("7131"),n={data:function(){return{$imgurl:this.$imgurl,staffs:"",list_style:1,baseinfo:""}},onLoad:function(t){var a=this;uni.setNavigationBarTitle({title:"名片列表"});var i=0;t.fxsid&&(i=t.fxsid,a.fxsid=t.fxsid),this._baseMin(this);uni.getStorageSync("suid");e.h5login(i,function(){a.getstaffs()})},methods:{redirectto:function(t){var a=t.currentTarget.dataset.link,i=t.currentTarget.dataset.linktype;this._redirectto(a,i)},getstaffs:function(){var t=this;uni.request({url:t.$baseurl+"doPagegetStaffs",data:{uniacid:t.$uniacid},success:function(a){t.staffs=a.data.data}})},staffcard:function(t){var a=t.currentTarget.dataset.text;uni.navigateTo({url:"/pagesCards/card_info/card_info?id="+a})},sharestaffcard:function(t){var a=t.currentTarget.dataset.text;uni.navigateTo({url:"/pagesCards/card_info/card_info?id="+a+"&share=1"})}}};a.default=n},b9e2:function(t,a,i){"use strict";i.r(a);var e=i("d9ee"),n=i("6368");for(var s in n)"default"!==s&&function(t){i.d(a,t,function(){return n[t]})}(s);i("aabf");var r=i("2877"),d=Object(r["a"])(n["default"],e["a"],e["b"],!1,null,"41d5772a",null);a["default"]=d.exports},d9ee:function(t,a,i){"use strict";var e=function(){var t=this,a=t.$createElement,i=t._self._c||a;return t.$imgurl?i("div",[t._l(t.staffs,function(a,e){return[i("v-uni-view",{key:e+"_0",staticClass:"card_listbox",attrs:{"data-text":a.id},on:{click:function(a){arguments[0]=a=t.$handleEvent(a),t.staffcard.apply(void 0,arguments)}}},[i("v-uni-view",{staticClass:"card_list_head hbj",on:{click:function(a){a.stopPropagation(),arguments[0]=a=t.$handleEvent(a)}}},[""==a.pic?[i("v-uni-image",{staticClass:"card_list_head_left",attrs:{src:t.$imgurl+"default_pic.png",mode:"aspectFill"}})]:[i("v-uni-image",{staticClass:"card_list_head_left",attrs:{src:a.pic,mode:"aspectFill"}})],i("v-uni-view",{staticClass:"card_list_head_center"},[t._v(t._s(a.realname)),i("v-uni-text",[t._v(t._s(a.title)+"-"+t._s(a.job))])],1),i("v-uni-image",{staticClass:"card_list_head_right",attrs:{src:t.$imgurl+"card_ewm.png",mode:"aspectFill","data-text":a.id},on:{click:function(a){arguments[0]=a=t.$handleEvent(a),t.sharestaffcard.apply(void 0,arguments)}}})],2),i("v-uni-view",{staticClass:"card_list_content"},[i("v-uni-view",{staticClass:"card_list_content_single"},[t._v("手机"),i("v-uni-text",[t._v(t._s(a.mobile))])],1),null==a.wxnumber||""==a.wxnumber?[i("v-uni-view",{staticClass:"card_list_content_single"},[t._v("微信"),i("v-uni-text",[t._v(t._s(a.mobile))])],1)]:[i("v-uni-view",{staticClass:"card_list_content_single"},[t._v("微信"),i("v-uni-text",[t._v(t._s(a.wxnumber))])],1)],null!=a.email&&""!=a.email?[i("v-uni-view",{staticClass:"card_list_content_single"},[t._v("邮箱"),i("v-uni-text",[t._v(t._s(a.email))])],1)]:t._e(),i("v-uni-view",{staticClass:"card_list_link"},[i("v-uni-image",{attrs:{src:t.$imgurl+"card_list_yjt.png",mode:"aspectFill"}})],1)],2)],1)]}),i("myfooter",{attrs:{page_signs:t.page_signs,baseinfo:t.baseinfo}})],2):t._e()},n=[];i.d(a,"a",function(){return e}),i.d(a,"b",function(){return n})}}]);