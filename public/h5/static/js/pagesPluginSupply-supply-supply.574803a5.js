(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pagesPluginSupply-supply-supply"],{"19e9":function(t,i,e){"use strict";e.r(i);var a=e("2b1c"),n=e("e420");for(var o in n)"default"!==o&&function(t){e.d(i,t,function(){return n[t]})}(o);e("f232");var s=e("2877"),d=Object(s["a"])(n["default"],a["a"],a["b"],!1,null,"5354de1a",null);i["default"]=d.exports},"2b1c":function(t,i,e){"use strict";var a=function(){var t=this,i=t.$createElement,e=t._self._c||i;return t.$imgurl?e("v-uni-view",[t.needAuth?e("auth",{attrs:{needAuth:t.needAuth},on:{closeAuth:function(i){i=t.$handleEvent(i),t.closeAuth(i)},cell:function(i){i=t.$handleEvent(i),t.cell(i)}}}):t._e(),t.needBind?e("bindPhone",{attrs:{needBind:t.needBind},on:{closeBind:function(i){i=t.$handleEvent(i),t.closeBind(i)}}}):t._e(),e("v-uni-view",{staticClass:"nav"},[e("v-uni-view",{class:["nav_con",1==t.type?"active":""],attrs:{"data-type":"1"},on:{click:function(i){i=t.$handleEvent(i),t.changeType(i)}}},[t._v("供应")]),e("v-uni-view",{class:["nav_con",2==t.type?"active":""],attrs:{"data-type":"2"},on:{click:function(i){i=t.$handleEvent(i),t.changeType(i)}}},[t._v("求购")])],1),t.supplyAll.length>0?[t._l(t.supplyAll,function(i,a){return[e("v-uni-view",{key:a+"_0",staticClass:"dynamic_single",attrs:{"data-rid":i.id},on:{click:function(i){i=t.$handleEvent(i),t.goContent(i)}}},[e("v-uni-view",{staticClass:"hbj"},[e("v-uni-image",{staticClass:"dynamic_tx",attrs:{src:i.avatar,mode:"aspectFill"}}),e("v-uni-view",{staticClass:"dynamic_view1_center"},[e("v-uni-view",{staticClass:"dynamic_name"},[e("v-uni-text",{staticClass:"dynamic_name_text1"},[t._v(t._s(i.nickname))]),e("v-uni-text",{staticClass:"dynamic_name_text2"},[t._v(t._s(i.createtime))])],1),e("v-uni-view",{staticClass:"forum_cardbox hbj"},[1==i.stick?e("v-uni-view",{staticClass:"forum_card_single",staticStyle:{background:"#2F74FD"}},[t._v("置顶")]):t._e(),1==i.hot?e("v-uni-view",{staticClass:"forum_card_single",staticStyle:{background:"#E12735"}},[t._v("推荐")]):t._e()],1)],1),e("v-uni-view",{staticClass:"flex1"}),e("v-uni-view",{on:{click:function(i){i.stopPropagation(),i=t.$handleEvent(i)}}},[""!=i.telphone&&"undefined"!=i.telphone?e("v-uni-image",{staticClass:"forum_phone",attrs:{src:t.$imgurl+"phone.png","data-tel":i.telphone,mode:"aspectFill"},on:{click:function(i){i=t.$handleEvent(i),t.makephone(i)}}}):t._e()],1)],1),e("v-uni-view",{staticClass:"dynamic_title"},[t._v(t._s(i.title))]),e("v-uni-view",{staticClass:"dynamic_content",staticStyle:{overflow:"hidden","text-overflow":"ellipsis",display:"-webkit-box","-webkit-line-clamp":"4","-webkit-box-orient":"vertical"}},[t._v(t._s(i.content))]),""!=i.address?e("v-uni-view",{staticClass:"forum_address hbj"},[e("v-uni-image",{staticClass:"forum_address_left",attrs:{src:t.$imgurl+"position.png",mode:"aspectFill"}}),e("v-uni-view",{staticClass:"forum_address_right"},[t._v(t._s(i.address))])],1):t._e(),e("v-uni-view",{staticClass:"dynamic_imgbox"},[t._l(i.img,function(t,i){return[e("v-uni-image",{key:i+"_0",staticClass:"dynamic_img",attrs:{src:t,mode:"aspectFill"}})]})],2),e("v-uni-view",{staticClass:"dynamic_single_bot hbj",on:{click:function(i){i.stopPropagation(),i=t.$handleEvent(i)}}},[e("v-uni-view",{staticClass:"dynamic_single_bot_view"},[e("v-uni-text",{staticClass:"dynamic_single_bot_text1 iconfont icon-c-kan"}),e("v-uni-text",{staticClass:"dynamic_single_bot_text2"},[t._v(t._s(i.hits))])],1),e("v-uni-view",{staticClass:"dynamic_single_bot_view",attrs:{"data-rid":i.id,"data-index":a},on:{click:function(i){i=t.$handleEvent(i),t.changeCollection(i)}}},[e("v-uni-text",{staticClass:"dynamic_single_bot_text1 iconfont icon-c-xin1",style:1==i.is_collect?"color:#FF0000":""}),e("v-uni-text",{staticClass:"dynamic_single_bot_text2"},[t._v(t._s(i.collection))])],1),e("v-uni-view",{staticClass:"dynamic_single_bot_view"},[e("v-uni-text",{staticClass:"dynamic_single_bot_text1 iconfont icon-x-pinglun"}),e("v-uni-text",{staticClass:"dynamic_single_bot_text2"},[t._v(t._s(i.comment))])],1)],1),i.commentList.length>0?e("v-uni-view",{staticClass:"dzplbox"},[e("v-uni-view",{staticClass:"sjx"}),i.commentList.length>0?e("v-uni-view",{staticClass:"plhbox"},[t._l(i.commentList,function(i,a){return[e("v-uni-view",{key:a+"_0",staticClass:"plh"},[e("v-uni-text",[t._v(t._s(i.nickname)+":")]),t._v(t._s(i.content))],1)]})],2):t._e()],1):t._e()],1)]})]:[e("v-uni-image",{staticClass:"pageNotice",attrs:{src:t.$imgurl+"notice.png"}}),e("v-uni-view",{staticClass:"pageNoticeT"},[t._v("暂无内容")])],e("v-uni-view",{staticClass:"release_btn",staticStyle:{"line-height":"88rpx"},on:{click:function(i){i=t.$handleEvent(i),t.goRelease(i)}}},[t._v("发布")]),e("v-uni-view",{staticClass:"release_btn",staticStyle:{bottom:"300rpx","line-height":"88rpx"},on:{click:function(i){i=t.$handleEvent(i),t.gomydata(i)}}},[t._v("我的")]),1==t.baseinfo.tabbar_t?e("copyright",{attrs:{baseinfo:t.baseinfo}}):t._e()],2):t._e()},n=[];e.d(i,"a",function(){return a}),e.d(i,"b",function(){return n})},a3a4:function(t,i,e){var a=e("d10a");"string"===typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);var n=e("4f06").default;n("af47fd68",a,!0,{sourceMap:!1,shadowMode:!1})},d10a:function(t,i,e){i=t.exports=e("2350")(!1),i.push([t.i,".release_btn[data-v-5354de1a]{width:%?88?%;height:%?88?%;bottom:%?400?%;right:%?30?%;border-radius:50%;background-color:#e32c3a;text-align:center;position:fixed;color:#fff;font-size:%?28?%}.nav[data-v-5354de1a]{background:#fff;height:%?88?%;margin-bottom:%?10?%;overflow:visible}.nav uni-view[data-v-5354de1a]{width:50%;height:%?88?%;display:inline-block;text-align:center}.nav .nav_con[data-v-5354de1a]{line-height:%?88?%}.active[data-v-5354de1a]{color:#333;font-weight:700;border-bottom:%?4?% solid red}.dynamic_single[data-v-5354de1a]{padding:%?50?% %?30?% %?40?%;background-color:#fff;margin-top:%?15?%}.dynamic_tx[data-v-5354de1a]{width:%?90?%;height:%?90?%;border-radius:50%;margin-right:%?24?%}.dynamic_view1_center[data-v-5354de1a]{width:%?470?%}.dynamic_name[data-v-5354de1a]{font-size:%?28?%;color:#434343\n\t/* height: 38rpx; */}.dynamic_view1_right[data-v-5354de1a]{font-size:%?28?%;color:#2f74fd}.dynamic_view1_right uni-image[data-v-5354de1a]{width:%?20?%;height:%?24?%;vertical-align:middle;margin-right:%?10?%}.dynamic_content[data-v-5354de1a]{font-size:%?30?%;color:#434343;line-height:%?50?%;margin-top:%?20?%}.dynamic_imgbox[data-v-5354de1a]{margin-top:%?20?%;font-size:0}.dynamic_img[data-v-5354de1a]{width:%?160?%;height:%?160?%;margin-right:%?16?%;margin-bottom:%?20?%;border-radius:%?12?%}.dynamic_img[data-v-5354de1a]:nth-child(4n){margin-right:0}.dynamic_img1[data-v-5354de1a]{width:%?160?%;height:%?160?%;margin-right:%?16?%;margin-bottom:%?20?%;border-radius:%?12?%}.dynamic_img1[data-v-5354de1a]:nth-child(3n){margin-right:0}.dynamic_single_bot[data-v-5354de1a]{margin-top:%?30?%}.dynamic_single_bot_view[data-v-5354de1a]{width:%?150?%;margin-right:%?10?%}.dynamic_single_bot_text1[data-v-5354de1a]{font-size:%?36?%;color:#cacaca;margin-right:%?10?%;vertical-align:middle}.dynamic_single_bot_text2[data-v-5354de1a]{font-size:%?24?%;color:#969696}.dynamic_name_text1[data-v-5354de1a]{\n\t/* height:38rpx; */max-width:%?250?%;overflow:hidden;white-space:nowrap;-o-text-overflow:ellipsis;text-overflow:ellipsis;display:inline-block;font-size:%?28?%;color:#232323}.dynamic_name_text2[data-v-5354de1a]{font-size:%?24?%;color:#969696;font-family:Arial,Helvetica,sans-serif;margin-left:%?10?%;vertical-align:%?8?%}.forum_card_single[data-v-5354de1a]{padding:%?2?% %?20?%;border-radius:%?40?%;font-size:%?24?%;color:#fff;margin-right:%?10?%}.forum_phone[data-v-5354de1a]{width:%?40?%;height:%?40?%}.forum_address[data-v-5354de1a]{margin:%?10?% auto}.forum_address_left[data-v-5354de1a]{width:%?24?%;height:%?28?%;margin-right:%?10?%}.forum_address_right[data-v-5354de1a]{width:%?650?%;height:%?38?%;line-height:%?38?%;overflow:hidden;white-space:nowrap;-o-text-overflow:ellipsis;text-overflow:ellipsis;font-size:%?24?%;color:#969696}.forum_scroll[data-v-5354de1a]{padding:0 %?30?%;background-color:#fff;width:100%;-webkit-box-sizing:border-box;box-sizing:border-box;font-size:0;white-space:nowrap}.forum_scroll_view[data-v-5354de1a]{height:%?80?%;line-height:%?80?%;border-bottom:%?2?% solid #fff;color:#434343;font-size:%?28?%;display:inline-block;margin-right:%?60?%;padding:0 %?8?%}.forum_scroll_view[data-v-5354de1a]:last-child{margin-right:0}.forum_scroll_view_on[data-v-5354de1a]{color:#2f74fd;border-bottom:%?2?% solid #2f74fd}.release_btn[data-v-5354de1a]{width:%?94?%;height:%?94?%;bottom:%?400?%;right:%?30?%;border-radius:50%;background-color:#e32c3a;line-height:%?94?%;text-align:center;position:fixed;color:#fff;font-size:%?28?%}.hbj2[data-v-5354de1a]{display:-webkit-box;display:-webkit-flex;display:-ms-flexbox;display:flex;-webkit-box-orient:horizontal;-webkit-box-direction:normal;-webkit-flex-direction:row;-ms-flex-direction:row;flex-direction:row}.pyqstyle[data-v-5354de1a]{width:%?576?%}.dzplbox[data-v-5354de1a]{width:100%;padding:%?20?% %?10?%;-webkit-box-sizing:border-box;box-sizing:border-box;background-color:#f8f8f8;margin-top:%?30?%;position:relative;overflow:visible}.dzh[data-v-5354de1a]{\n\t/* height: 64rpx; */padding:0 %?16?%}.dzhleft[data-v-5354de1a]{font-size:%?36?%;color:#cacaca;margin-right:%?15?%;overflow:hidden}.dzhright[data-v-5354de1a]{font-size:%?24?%;color:#838383;\n\t/* height: 32rpx; */width:%?550?%\n\t/* overflow: hidden;\nwhite-space: nowrap;\ntext-overflow: ellipsis; */}.plhbox[data-v-5354de1a]{padding:0 %?16?%;margin-top:%?12?%}.plh[data-v-5354de1a]{font-size:%?24?%;color:#434343;line-height:%?40?%;margin-top:%?10?%}.plh[data-v-5354de1a]:first-child{margin:0}.plh uni-text[data-v-5354de1a]{color:#2f74fd}.sjx[data-v-5354de1a]{height:0;width:0;border-left:%?10?% solid rgba(0,0,0,0);border-right:%?10?% solid rgba(0,0,0,0);border-bottom:%?20?% solid #f8f8f8;position:absolute;top:%?-20?%;left:%?30?%}.dynamic_view1_center2[data-v-5354de1a]{width:%?370?%}.dynamic_img2[data-v-5354de1a]{width:100%;height:%?358?%;border-radius:%?12?%}.dynamic_title[data-v-5354de1a]{color:#434343;font-size:%?32?%;font-weight:700;margin-top:%?20?%;overflow:hidden}",""])},e420:function(t,i,e){"use strict";e.r(i);var a=e("ef6e"),n=e.n(a);for(var o in a)"default"!==o&&function(t){e.d(i,t,function(){return a[t]})}(o);i["default"]=n.a},ef6e:function(t,i,e){"use strict";var a=e("288e");Object.defineProperty(i,"__esModule",{value:!0}),i.default=void 0;var n,o=a(e("bd86")),s=e("55f5"),d=(n={data:function(){return{$imgurl:this.$imgurl,needAuth:!1,needBind:!1,type:1,supplyAll:{},baseinfo:[],page:1}},onLoad:function(t){var i=this;this._baseMin(this),t.fid&&(this.type=t.fid);var e=0;t.fxsid&&(e=t.fxsid),this.fxsid=e;uni.getStorageSync("suid");s.h5login(e,function(){i.getsupply(1)})},onPullDownRefresh:function(){},onReachBottom:function(){var t=this,i=1*t.page+1;uni.request({url:t.$baseurl+"dopageGetsupply",data:{uniacid:t.$uniacid,suid:uni.getStorageSync("suid"),type:t.type,page:i},success:function(e){t.supplyAll=t.supplyAll.concat(e.data.data),t.page=i}})},onShow:function(){this.getsupply(this.type)}},(0,o.default)(n,"onPullDownRefresh",function(){this.getsupply(this.type),uni.stopPullDownRefresh()}),(0,o.default)(n,"methods",{changeType:function(t){var i=t.currentTarget.dataset.type;this.type=i,this.getsupply(this.type)},getsupply:function(t){var i=this;uni.request({url:i.$baseurl+"dopageGetsupply",data:{uniacid:i.$uniacid,suid:uni.getStorageSync("suid"),type:t},success:function(t){i.supplyAll=t.data.data}})},goRelease:function(){uni.navigateTo({url:"/pagesPluginSupply/release/release?type="+this.type})},gomydata:function(){uni.navigateTo({url:"/pagesPluginSupply/collect/collect"})},changeCollection:function(t){var i=this;if(!this.getSuid())return!1;var e=t.currentTarget.dataset.index,a=t.currentTarget.dataset.rid;uni.request({url:i.$baseurl+"doPageSupplyCollection",data:{uniacid:i.$uniacid,suid:uni.getStorageSync("suid"),rid:a,vs:1},success:function(t){var a=i.supplyAll;1==t.data.data.is_collect?(uni.showToast({title:"收藏成功"}),a[e]["is_collect"]=1):2==t.data.data.is_collect&&(uni.showToast({title:"取收成功"}),a[e]["is_collect"]=2),a[e]["collection"]=t.data.data.num,i.supplyAll=a}})},goContent:function(t){var i=t.currentTarget.dataset.rid;uni.navigateTo({url:"/pagesPluginSupply/page/page?rid="+i})},makephone:function(t){var i=t.currentTarget.dataset.tel;uni.makePhoneCall({phoneNumber:i})},cell:function(){this.needAuth=!1},closeAuth:function(){this.needAuth=!1,this.needBind=!0},closeBind:function(){this.needBind=!1},getSuid:function(){var t=uni.getStorageSync("suid");if(t)return!0;var i="";return i?this.needBind=!0:this.needAuth=!0,!1}}),n);i.default=d},f232:function(t,i,e){"use strict";var a=e("a3a4"),n=e.n(a);n.a}}]);