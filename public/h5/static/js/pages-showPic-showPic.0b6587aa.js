(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-showPic-showPic"],{"1e9d":function(n,t,i){"use strict";var e=i("288e");Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var a=e(i("e814")),s=i("7131"),o=(getApp(),{data:function(){return{$imgurl:this.$imgurl,picList:[],piclist_num:0,thumb:"",shareShow:0,shareScore:0,shareNotice:0,fxsid:0,shareHome:0,datas:"",baseinfo:{},sharesuid:"",get_share_gz:2,share:0,id:0,shareimg:0,shareimg_url:"",system_w:0,system_h:0,img_w:0,img_h:0,title:""}},onLoad:function(n){var t=this;this._baseMin(this);var i=0;n.fxsid&&(i=n.fxsid),this.fxsid=i,this.id=n.id,n.userid&&(this.sharesuid=n.userid);var e=uni.getStorageSync("systemInfo");this.img_w=(0,a.default)((.65*e.windowWidth).toFixed(0)),this.img_h=(0,a.default)((1.875*this.img_w).toFixed(0)),this.system_w=(0,a.default)(e.windowWidth),this.system_h=(0,a.default)(e.windowHeight),s.h5login(this.fxsid,function(){t.getPic()})},onShareAppMessage:function(){var n=this,t=uni.getStorageSync("suid"),i=n.id,e="";return e="/pages/showPic/showPic?id="+i+"&fxsid="+t+"&userid="+t,{title:n.title,path:e,success:function(n){}}},onPullDownRefresh:function(){var n=this,t=n.id;n.getShowPic(t),uni.stopPullDownRefresh()},methods:{navback:function(){uni.navigateBack()},getPic:function(){this.getShowPic(this.id)},redirectto:function(n){var t=n.currentTarget.dataset.link,i=n.currentTarget.dataset.linktype;this._redirectto(t,i)},getShowPic:function(n){var t=this;uni.request({url:t.$baseurl+"dopageshowPic",data:{id:n,uniacid:t.$uniacid},cachetime:"30",success:function(n){t.picList=n.data.data.text,t.piclist_num=n.data.data.text.length,t.thumb=n.data.data.thumb,t.title=n.data.data.title,t.desc=n.data.data.desc,t.get_share_gz=n.data.data.get_share_gz,uni.setNavigationBarTitle({title:t.title}),uni.setStorageSync("isShowLoading",!1),uni.hideNavigationBarLoading(),uni.stopPullDownRefresh(),t.givepscore()}})},shareClo:function(){this.shareShow=0},share111:function(){var n=this;n.share=1},share_close:function(){var n=this;n.share=0},h5ShareAppMessage:function(){var n=this,t=uni.getStorageSync("suid");uni.showModal({title:"长按复制链接后分享",content:this.$host+"/h5/index.html?id="+this.$uniacid+"#/pages/showPic/showPic?id="+this.id+"&fxsid="+t+"&userid="+t,showCancel:!1,success:function(t){n.share=0}})},getShareImg:function(){uni.showLoading({title:"海报生成中"});var n=this;uni.request({url:n.$baseurl+"dopageshareewm",data:{uniacid:n.$uniacid,suid:uni.getStorageSync("suid"),gid:n.id,types:"pic",source:uni.getStorageSync("source"),pageUrl:"showPic"},success:function(t){uni.hideLoading(),0==t.data.data.error?(n.shareimg=1,n.shareimg_url=t.data.data.url):uni.showToast({title:t.data.data.msg,icon:"none"})}})},closeShare:function(){this.shareimg=0},saveImg:function(){var n=this;uni.getImageInfo({src:n.shareimg_url,success:function(t){uni.saveImageToPhotosAlbum({filePath:t.path,success:function(){uni.showToast({title:"保存成功！",icon:"none"}),n.shareimg=0,n.share=0}})}})},aliSaveImg:function(){var n=this;uni.getImageInfo({src:n.shareimg_url,success:function(t){my.saveImage({url:t.path,showActionSheet:!0,success:function(){my.alert({title:"保存成功"}),n.shareimg=0,n.share=0}})}})},givepscore:function(){var n=this,t=n.id,i="showPic",e=n.sharesuid,a=uni.getStorageSync("suid");e!=a&&0!=e&&""!=e&&void 0!=e&&uni.request({url:n.$baseurl+"doPagegiveposcore",data:{id:t,types:i,suid:a,fxsid:e,uniacid:n.$uniacid,source:uni.getStorageSync("source")},success:function(n){}})},closeAuth:function(){this.needAuth=!1,this._checkBindPhone(this)},closeBind:function(){this.needBind=!1,this.getPic()}}});t.default=o},"369b":function(n,t,i){t=n.exports=i("2350")(!1),t.push([n.i,"\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n/* 头部 */.top_nav[data-v-b3664b9a]{position:relative;z-index:10}.top_nav_back[data-v-b3664b9a]{position:fixed;top:4%;left:%?20?%;z-index:10;height:%?60?%;width:%?60?%;text-align:center;line-height:%?60?%}.flex-row[data-v-b3664b9a]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-orient:horizontal;-webkit-box-direction:normal;-webkit-flex-direction:row;flex-direction:row;-webkit-box-align:center;-webkit-align-items:center;align-items:center}.trump[data-v-b3664b9a]{width:100%;height:100%}.trump_top[data-v-b3664b9a]{position:relative;width:100%;height:%?484?%}.trump_top uni-image[data-v-b3664b9a]{width:100%;height:100%}.trump_cont[data-v-b3664b9a]{position:absolute;bottom:%?10?%;left:%?20?%;color:#fff}.trump_con_title[data-v-b3664b9a]{font-size:%?34?%;width:%?400?%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.trump_con_num[data-v-b3664b9a]{font-size:%?28?%}.pic_imglist[data-v-b3664b9a]{padding:%?20?%;box-sizing:border-box}.pic_imgitem[data-v-b3664b9a]{width:%?230?%;height:%?230?%;border-radius:%?10?%;overflow:hidden;display:inline-block;margin-right:%?10?%}.pic_imgitem[data-v-b3664b9a]:nth-child(3n+3){margin-right:0}.pic_imgitem uni-image[data-v-b3664b9a]{width:100%;height:100%}.shareBtnPic[data-v-b3664b9a]{position:absolute;bottom:%?20?%;right:%?20?%;color:#fff}.shareBtnPic .iconfont[data-v-b3664b9a]{margin-right:%?8?%;font-size:%?28?%}.shareBtnPic .text[data-v-b3664b9a]{font-size:%?24?%}\n/**分享层**/.share_ceng[data-v-b3664b9a]{position:fixed;left:0;bottom:%?0?%;width:100%;background:rgba(0,0,0,.7);height:auto}.share_con[data-v-b3664b9a]{position:absolute;left:0;bottom:0;width:100%;background:#fff;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-orient:horizontal;-webkit-box-direction:normal;-webkit-flex-direction:row;flex-direction:row;-webkit-box-align:center;-webkit-align-items:center;align-items:center}.share_con>uni-view>uni-button[data-v-b3664b9a]{line-height:%?68?%;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center;border-right:%?2?% solid #c6cbd9;border-radius:0;height:%?68?%}.share_con>uni-view:last-child>uni-button[data-v-b3664b9a]:after{border:none}\n\n.fx_box[data-v-b3664b9a]{z-index:900;position:fixed;top:9%;margin:auto}\n.fx_box .fx_close[data-v-b3664b9a]{\n\t/* margin: 45px; */background-color:#fff;width:100%;height:auto;padding-bottom:5px}\n/*关闭按钮*/.fx_box .fx_close uni-view[data-v-b3664b9a]{text-decoration:none;color:#2d2c3b}.fx_content[data-v-b3664b9a]{text-align:center;margin:0 5px}.fx_content .haibao_img[data-v-b3664b9a]{border:1px solid #bbb;margin:0 5px 0 5px!important;float:none}.fx_X[data-v-b3664b9a]{text-align:right;margin-right:5px;height:auto;margin-top:-5px}.haibao_btn[data-v-b3664b9a]{font-size:12px;font-size:normal;margin:0 8px}.haibao_tishi[data-v-b3664b9a]{text-align:center;color:#9d9d9d!important}",""])},"39e6":function(n,t,i){"use strict";var e=function(){var n=this,t=n.$createElement,i=n._self._c||t;return n.$imgurl?i("v-uni-view",[n.needAuth?i("auth",{attrs:{needAuth:n.needAuth},on:{closeAuth:function(t){arguments[0]=t=n.$handleEvent(t),n.closeAuth.apply(void 0,arguments)}}}):n._e(),n.needBind?i("bindPhone",{attrs:{needBind:n.needBind},on:{closeBind:function(t){arguments[0]=t=n.$handleEvent(t),n.closeBind.apply(void 0,arguments)}}}):n._e(),i("v-uni-view",{staticClass:"top_nav"},[i("v-uni-view",{staticClass:"top_nav_back",on:{click:function(t){arguments[0]=t=n.$handleEvent(t),n.navback.apply(void 0,arguments)}}},[i("v-uni-view",{staticClass:"iconfont icon-x-back",style:{color:n.baseinfo.base_tcolor}})],1)],1),i("v-uni-view",{staticClass:"trump"},[i("v-uni-view",{staticClass:"trump_top"},[i("v-uni-image",{attrs:{src:n.thumb,mode:"aspectFill"}}),i("v-uni-view",{staticClass:"trump_cont"},[i("v-uni-view",{staticClass:"trump_con_title"},[n._v(n._s(n.title))]),i("v-uni-view",{staticClass:"trump_con_num"},[n._v(n._s(n.piclist_num)+"张")])],1),1==n.baseinfo.share_open?[1==n.get_share_gz?i("v-uni-view",{staticClass:"shareBtnPic",on:{click:function(t){arguments[0]=t=n.$handleEvent(t),n.share111.apply(void 0,arguments)}}},[i("v-uni-text",{staticClass:"iconfont icon-x-fenxiang2"}),i("v-uni-text",{staticClass:"text"},[n._v("转发")])],1):n._e()]:n._e()],2),i("v-uni-view",{staticClass:"pic_imglist"},[n._l(n.picList,function(t,e){return[i("v-uni-navigator",{key:e+"_0",staticClass:"pic_imgitem",attrs:{url:"/pages/showPiclist/showPiclist?id="+n.id+"&key="+e}},[i("v-uni-image",{attrs:{src:t,mode:"aspectFill"}})],1)]})],2)],1),1==n.shareShow?[i("v-uni-view",{staticClass:"mask"}),i("v-uni-view",{staticClass:"shareBox"},[i("v-uni-image",{attrs:{src:n.$imgurl+"share_ok.png",mode:"widthFix"}}),0==n.shareNotice?i("v-uni-view",{staticClass:"shareText"},[n._v("分享成功，"),i("span",[n._v("+"+n._s(n.shareScore)+"积分")])]):n._e(),1==n.shareNotice?i("v-uni-view",{staticClass:"shareText shareText2"},[n._v("您今日分享次数较多，本次不增加积分，感谢分享！")]):n._e(),i("v-uni-navigator",{staticClass:"shareBtn",attrs:{"open-type":"redirectTo",url:"/pages/usercenter/usercenter"}},[n._v("查看我的积分")]),i("v-uni-view",{staticClass:"shareClo",on:{click:function(t){arguments[0]=t=n.$handleEvent(t),n.shareClo.apply(void 0,arguments)}}})],1)]:n._e(),1==n.share?i("v-uni-view",{staticClass:"mask",on:{click:function(t){arguments[0]=t=n.$handleEvent(t),n.share_close.apply(void 0,arguments)}}}):n._e(),1==n.share?i("v-uni-view",{staticClass:"share_ceng",style:{"z-index":1==n.shareimg?"800":1e3}},[i("v-uni-view",{staticClass:"share_con flex-row"},[i("v-uni-view",{staticClass:"share_con_box"},[i("v-uni-button",{staticClass:"flex-row",on:{click:function(t){arguments[0]=t=n.$handleEvent(t),n.h5ShareAppMessage.apply(void 0,arguments)}}},[i("v-uni-view",{staticClass:"iconfont icon-x-fenxiang5",staticStyle:{"font-size":"40rpx",color:"#56bb3a"}}),i("v-uni-view",{staticStyle:{"margin-left":"10rpx",color:"#333"}},[n._v("分享给好友")])],1)],1),i("v-uni-view",{staticClass:"share_con_box"},[i("v-uni-view",{staticClass:"flex-row",staticStyle:{"justify-content":"center"},on:{click:function(t){arguments[0]=t=n.$handleEvent(t),n.getShareImg.apply(void 0,arguments)}}},[i("v-uni-view",{staticClass:"iconfont icon-x-tupian",staticStyle:{"font-size":"40rpx",color:"#e47b2f"}}),i("v-uni-view",{staticStyle:{"margin-left":"10rpx"}},[n._v("生成分享海报")])],1)],1)],1)],1):n._e(),1==n.shareimg?i("v-uni-view",{staticClass:"fx_box",style:{width:n.img_w+20+"px",height:n.img_h+25+"px",left:(n.system_w-n.img_w-10)/2+"px"}},[i("v-uni-view",{staticClass:"fx_close"},[i("v-uni-view",{staticClass:"fx_X",on:{click:function(t){arguments[0]=t=n.$handleEvent(t),n.closeShare.apply(void 0,arguments)}}},[n._v("x")]),i("v-uni-view",{staticClass:"fx_content"},[i("img",{staticClass:"haibao_img",style:{width:n.img_w+"px",height:n.img_h+"px"},attrs:{src:n.shareimg_url}})]),i("v-uni-view",{staticClass:"haibao_tishi",attrs:{type:"primary"}},[n._v("*长按图片保存*")])],1)],1):n._e()],2):n._e()},a=[];i.d(t,"a",function(){return e}),i.d(t,"b",function(){return a})},"3fb9":function(n,t,i){var e=i("369b");"string"===typeof e&&(e=[[n.i,e,""]]),e.locals&&(n.exports=e.locals);var a=i("4f06").default;a("51c9af6a",e,!0,{sourceMap:!1,shadowMode:!1})},"400d":function(n,t,i){"use strict";i.r(t);var e=i("1e9d"),a=i.n(e);for(var s in e)"default"!==s&&function(n){i.d(t,n,function(){return e[n]})}(s);t["default"]=a.a},"4eba":function(n,t,i){"use strict";i.r(t);var e=i("39e6"),a=i("400d");for(var s in a)"default"!==s&&function(n){i.d(t,n,function(){return a[n]})}(s);i("59d7");var o=i("2877"),r=Object(o["a"])(a["default"],e["a"],e["b"],!1,null,"b3664b9a",null);t["default"]=r.exports},"59d7":function(n,t,i){"use strict";var e=i("3fb9"),a=i.n(e);a.a}}]);