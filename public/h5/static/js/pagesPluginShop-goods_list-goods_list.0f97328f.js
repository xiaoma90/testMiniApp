(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pagesPluginShop-goods_list-goods_list"],{"1d46":function(t,i,e){"use strict";e.r(i);var o=e("27b0"),a=e("72f1");for(var s in a)"default"!==s&&function(t){e.d(i,t,function(){return a[t]})}(s);e("3134");var n=e("2877"),r=Object(n["a"])(a["default"],o["a"],o["b"],!1,null,"9fd15478",null);i["default"]=r.exports},"1d5f":function(t,i,e){i=t.exports=e("2350")(!1),i.push([t.i,".goods[data-v-9fd15478]{display:-webkit-box;display:-webkit-flex;display:-ms-flexbox;display:flex;width:100%;overflow:hidden;height:calc(100vh - 51px);\n\t\t/* padding-bottom: 35px; */background:#fff;border-top:%?2?% solid #f9f9f9}.menu-wrapper[data-v-9fd15478]{-webkit-box-flex:0;-webkit-flex:0 0 100px;-ms-flex:0 0 100px;flex:0 0 100px;width:100px;background:#f9f9f9;height:100%}.sr_y[data-v-9fd15478] ::-webkit-scrollbar{display:none}.menu-item[data-v-9fd15478]{display:table;height:%?92?%;width:100px;padding:0 12px;-webkit-box-sizing:border-box;box-sizing:border-box;line-height:%?90?%;color:#999;font-size:.8rem;text-align:center;white-space:nowrap;overflow:hidden;-o-text-overflow:ellipsis;text-overflow:ellipsis;text-align:left;-webkit-box-align:center;-webkit-align-items:center;-ms-flex-align:center;align-items:center}.menu-item.current[data-v-9fd15478]{color:#666;background:#f9f9f9;text-align:center;font-size:%?30?%;height:%?92?%;line-height:%?92?%;border-bottom:%?2?% solid #efefef}.menu-item.current.active[data-v-9fd15478]{color:#494949;background:#fff}.foods-wrapper[data-v-9fd15478]{-webkit-box-flex:1;-webkit-flex:1;-ms-flex:1;flex:1;padding:%?30?%;-webkit-box-sizing:border-box;box-sizing:border-box}.foods-item[data-v-9fd15478]{display:-webkit-box;display:-webkit-flex;display:-ms-flexbox;display:flex;padding-bottom:%?30?%;border-bottom:1px solid #f8f8f8;padding-top:%?30?%}.food-grouping:last-child .foods-item[data-v-9fd15478]:last-child{border:none;margin-bottom:0}.foods-item .icon[data-v-9fd15478]{width:%?118?%;height:%?118?%;-webkit-box-flex:0;-webkit-flex:0 0 57px;-ms-flex:0 0 57px;flex:0 0 57px;margin-right:10px;vertical-align:middle}.foods-item .title[data-v-9fd15478]{color:#333;font-size:%?28?%;overflow:hidden;-o-text-overflow:ellipsis;text-overflow:ellipsis;white-space:nowrap}.foods-item .intro[data-v-9fd15478]{color:#999;font-size:%?24?%;overflow:hidden;-o-text-overflow:ellipsis;text-overflow:ellipsis;white-space:nowrap}.foods-item .price[data-v-9fd15478]{color:red;font-size:%?28?%;overflow:hidden;-o-text-overflow:ellipsis;text-overflow:ellipsis;white-space:nowrap}.foods-item .content[data-v-9fd15478]{width:%?248?%}.xnerkd[data-v-9fd15478]{padding:%?8?% %?20?%;font-size:%?24?%;background:#f9f9f9;color:#737373}.cartcontrol-wrap[data-v-9fd15478]{text-align:center;-webkit-box-flex:1;-webkit-flex:1;-ms-flex:1;flex:1;position:relative}\n\n\t/* \t.gw{\n\t\twidth: 40rpx;\n\t\theight: 40rpx;\n\t\tposition: absolute;\n\t\tbottom: 0;\n\t\tright: 20rpx;\n\t} */.xguige[data-v-9fd15478]{color:#fff;font-size:%?22?%;padding:%?6?% %?20?%;border-radius:%?22?%;position:absolute;bottom:0;right:%?20?%;width:25px}.input-style[data-v-9fd15478]{\n\t\t/* height: 0.5rem; */\n\t\t/* width: 600rpx; */position:absolute;left:%?80?%;top:0;right:0;bottom:0;background-color:#f0f0f0!important}.searchBox[data-v-9fd15478]{display:block;padding:%?20?%;-webkit-box-sizing:border-box;box-sizing:border-box;background:#fff}.list_search[data-v-9fd15478]{position:relative;width:100%;height:%?60?%;background-color:#f0f0f0;overflow:hidden;border-radius:%?50?%;border:%?2?% solid #f9f9f9;position:relative;padding:0 %?20?%;-webkit-box-sizing:border-box;box-sizing:border-box}.ssk-icon[data-v-9fd15478]{position:absolute;top:50%;-webkit-transform:translateY(-50%);-ms-transform:translateY(-50%);transform:translateY(-50%);left:%?20?%}",""])},"27b0":function(t,i,e){"use strict";var o=function(){var t=this,i=t.$createElement,e=t._self._c||i;return e("v-uni-view",[e("v-uni-form",{staticClass:"searchBox",attrs:{"report-submit":"true"},on:{submit:function(i){i=t.$handleEvent(i),t.searchR(i)}}},[e("v-uni-view",{staticClass:"list_search"},[e("v-uni-view",{staticClass:"iconfont icon-x-sousuo1 ssk-icon"}),e("v-uni-input",{staticClass:"input-style",attrs:{type:"text",name:"keywords","confirm-type":"search",placeholder:"请输入关键词","placeholder-style":"background:#f0f0f0;color:#999"},on:{input:function(i){i=t.$handleEvent(i),t.searchInput(i)},confirm:function(i){i=t.$handleEvent(i),t.searchR(i)}}})],1)],1),e("v-uni-view",{staticClass:"goods"},[e("v-uni-view",{staticClass:"menu-wrapper"},[e("v-uni-scroll-view",{staticClass:"sr_y",staticStyle:{height:"100%"},attrs:{"scroll-y":""}},[t._l(t.cates,function(i,o){return[e("v-uni-view",{key:o+"_0",staticClass:"menu-item current",class:i.cid==t.cid||0==t.cid?"active":"",attrs:{"data-cid":i.cid},on:{click:function(i){i=t.$handleEvent(i),t.changeData(i)}}},[t._v(t._s(i.name))])]})],2)],1),e("v-uni-view",{staticClass:"foods-wrapper"},[e("v-uni-scroll-view",{staticClass:"sr_y",staticStyle:{height:"100%"},attrs:{"scroll-y":""}},t._l(t.goodsL,function(i,o){return e("v-uni-view",{key:o,staticClass:"food-grouping"},[e("v-uni-view",{staticClass:"xnerkd"},[t._v(t._s(i.cname))]),t._l(i.list,function(i,o){return e("v-uni-view",{key:o,staticClass:"foods-item"},[e("v-uni-image",{staticClass:"icon",attrs:{src:i.thumb}}),e("v-uni-view",{staticClass:"content"},[e("v-uni-view",{staticClass:"title"},[t._v(t._s(i.title))]),e("v-uni-view",{staticClass:"intro",staticStyle:{height:"30rpx"}},[t._v(t._s(i.descs))]),e("v-uni-view",{staticClass:"price"},[t._v("¥"+t._s(i.sellprice))])],1),e("v-uni-view",{staticClass:"cartcontrol-wrap"},[e("v-uni-view",{staticClass:"xguige",staticStyle:{background:"#0061CE"},attrs:{"data-id":i.id},on:{click:function(i){i=t.$handleEvent(i),t.goDetail(i)}}},[t._v("购买")])],1)],1)})],2)}),1)],1)],1)],1)},a=[];e.d(i,"a",function(){return o}),e.d(i,"b",function(){return a})},3134:function(t,i,e){"use strict";var o=e("78a4"),a=e.n(o);a.a},"72f1":function(t,i,e){"use strict";e.r(i);var o=e("ff7b"),a=e.n(o);for(var s in o)"default"!==s&&function(t){e.d(i,t,function(){return o[t]})}(s);i["default"]=a.a},"78a4":function(t,i,e){var o=e("1d5f");"string"===typeof o&&(o=[[t.i,o,""]]),o.locals&&(t.exports=o.locals);var a=e("4f06").default;a("d5b03302",o,!0,{sourceMap:!1,shadowMode:!1})},ff7b:function(t,i,e){"use strict";Object.defineProperty(i,"__esModule",{value:!0}),i.default=void 0;var o=e("55f5"),a={data:function(){return{cid:0,$imgurl:this.$imgurl,cates:[],cname:"",goodsL:[],tel:""}},onLoad:function(t){var i=this;uni.setNavigationBarTitle({title:"商品列表"}),this._baseMin(this);var e=0;t.sid&&(e=t.sid),this.sid=e;var a=0;t.fxsid&&(a=t.fxsid),t.tel&&(this.tel=t.tel),o.h5login(a,function(){i.getStoreData()})},onPullDownRefresh:function(){this.cid=0,this.getStoreData(),uni.stopPullDownRefresh()},methods:{goDetail:function(t){uni.navigateTo({url:"/pagesPluginShop/goods_detail/goods_detail?id="+t.currentTarget.dataset.id+"&tel="+this.tel})},searchInput:function(t){this.searchKey=t.detail.value},searchR:function(){uni.navigateTo({url:"/pages/search/search?sid="+this.sid+"&title="+this.searchKey})},changeData:function(t){this.cid=t.currentTarget.dataset.cid,this.getStoreData()},getStoreData:function(){var t=this;uni.request({url:this.$baseurl+"doPageGetStoreData",data:{sid:this.sid,cid:this.cid},success:function(i){t.cates=i.data.data.cates,t.cid=i.data.data.cid,t.goodsL=i.data.data.goodsL}})}}};i.default=a}}]);