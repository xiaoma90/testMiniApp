(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-evaluate-evaluate"],{"0518":function(s,a,t){"use strict";var e=t("288e");Object.defineProperty(a,"__esModule",{value:!0}),a.default=void 0;var i=e(t("f499")),n=t("2c90"),o={data:function(){return{$imgurl:this.$imgurl,assess:1,assess_duo:[],assessList:[{id:1,icon:"icon-c-haoping",name:"好评"},{id:2,icon:"icon-c-zhongchaping",name:"中评"},{id:3,icon:"icon-c-zhongchaping",name:"差评"}],anonymous:0,order_id:"",count_sy:5,imgs:[],nowcount:0,evaluatecon:"",type:"",add:0,thumb:"",duoPro:"",count:1,evaluate_duo:[],imgs_duo:[],anonymous_duo:[],pid:0}},onLoad:function(s){var a=this,t=this,e=0;s.fxsid&&(t.fxsid=s.fxsid),s.type&&(t.type=s.type);var i=0;s.add&&(i=s.add,t.add=s.add),s.id&&(t.pid=s.id),s.order_id&&(t.order_id=s.order_id),0==i?uni.setNavigationBarTitle({title:"产品评价"}):uni.setNavigationBarTitle({title:"产品追评"}),this._baseMin(this),t.proinfo(),n.h5login(e,function(){},function(){a.needBind=!0})},onPullDownRefresh:function(){var s=this;s.proinfo(),uni.stopPullDownRefresh()},methods:{proinfo:function(){var s=this;uni.request({url:s.$baseurl+"doPageassesspro",data:{uniacid:s.$uniacid,order_id:s.order_id,type:s.type,pid:s.pid},success:function(a){s.thumb=a.data.data.thumb,s.pid=a.data.data.pid,s.duoPro=a.data.data,s.count=a.data.count;for(var t=0;t<s.duoPro.length;t++){var e={id:1};console.log(e),s.assess_duo.push(e);var i={evaluatecon:"",nowcount:0};s.evaluate_duo.push(i);var n=[];s.imgs_duo.push(n);var o={anonymous:0};s.anonymous_duo.push(o)}}})},chooseAssess:function(s){var a=s.currentTarget.dataset.id;this.assess=a},chooseAssess_duo:function(s){var a=s.currentTarget.dataset.id,t=s.currentTarget.dataset.dkey;this.assess_duo[t]["id"]=a,console.log(this.assess_duo)},chooseAnonymous:function(s){var a=this.anonymous;a=0==a?1:0,this.anonymous=a},chooseAnonymous_duo:function(s){var a=s.currentTarget.dataset.dkey,t=this.anonymous_duo[a].anonymous;t=0==t?1:0,this.anonymous_duo[a].anonymous=t},chooseimg:function(){var s=this,a=s.count_sy,t=s.imgs;a-=t.length;var e=s.zhixin;uni.chooseImage({count:a,sizeType:["original","compressed"],sourceType:["album","camera"],success:function(a){e=!0,s.zhixin=e,uni.showLoading({title:"图片上传中"});var i=a.tempFilePaths,n=0,o=i.length,u=s.$baseurl+"wxupimg",d=function a(){uni.uploadFile({url:u,formData:{uniacid:s.$uniacid},filePath:i[n],name:"file",success:function(i){var u=i.data;t.push(u),s.imgs=t,n++,n<o?a():(e=!1,s.zhixin=e,uni.hideLoading())}})};d()}})},chooseimg_duo:function(s){var a=this,t=a.count_sy,e=s.currentTarget.dataset.dkey,i=a.imgs_duo[e];t-=i.length;var n=a.zhixin;uni.chooseImage({count:t,sizeType:["original","compressed"],sourceType:["album","camera"],success:function(s){n=!0,a.zhixin=n,uni.showLoading({title:"图片上传中"});var t=s.tempFilePaths,o=0,u=t.length,d=a.$baseurl+"wxupimg",c=function s(){uni.uploadFile({url:d,formData:{uniacid:a.$uniacid},filePath:t[o],name:"file",success:function(t){var d=t.data;i.push(d),a.imgs_duo[e]=i,o++,o<u?s():(n=!1,a.zhixin=n,uni.hideLoading())}})};c()}})},delimg_duo:function(s){var a=this,t=s.currentTarget.dataset.dkey,e=s.currentTarget.dataset.index;a.imgs_duo[t].splice(e,1)},delimg:function(s){var a=this,t=s.currentTarget.dataset.index,e=a.imgs;e.splice(t,1),a.imgs=e},evaluate:function(s){var a=this,t=s.detail.value,e=s.detail.cursor;a.evaluatecon=t,a.nowcount=e},evaluate_duo_bt:function(s){var a=this,t=s.detail.value,e=s.detail.cursor,i=s.currentTarget.dataset.dkey;a.evaluate_duo[i]["evaluatecon"]=t,a.evaluate_duo[i]["nowcount"]=e},submit:function(){var s=this,a=s.assess,t=s.evaluatecon,e=s.imgs,n=s.anonymous;if(""==t)return uni.showModal({title:"提示",content:"评价不能为空",showCancel:!1}),!1;uni.request({url:s.$baseurl+"doPageEvaluateSub",cachetime:"30",data:{uniacid:s.$uniacid,assess:a,evaluatecon:t,imgs:(0,i.default)(e),anonymous:n,suid:uni.getStorageSync("suid"),order_id:s.order_id,pid:s.pid,type:s.type,add:s.add},success:function(s){1==s.data?uni.showModal({title:"提示",content:"评价提交成功",showCancel:!1,success:function(s){uni.navigateBack({delta:1})}}):uni.showModal({title:"提示",content:"评价提交失败"})},fail:function(s){}})},submit_duo:function(){for(var s=this,a=s.assess_duo,t=s.evaluate_duo,e=s.imgs_duo,n=s.anonymous_duo,o=0;o<t.length;o++){if(""==t[o].evaluatecon)return uni.showModal({title:"提示",content:"评价不能为空",showCancel:!1}),!1;t[o].evaluatecon}uni.request({url:s.$baseurl+"doPageDuoEvaluateSub",cachetime:"30",data:{uniacid:s.$uniacid,assess:(0,i.default)(a),evaluatecon:(0,i.default)(t),imgs:(0,i.default)(e),anonymous:(0,i.default)(n),suid:uni.getStorageSync("suid"),order_id:s.order_id,duoPro:(0,i.default)(s.duoPro),add:s.add},success:function(s){s.data?uni.showModal({title:"提示",content:"评价提交成功",showCancel:!1,success:function(s){uni.navigateBack({delta:1})}}):uni.showModal({title:"提示",content:"评价提交失败"})},fail:function(s){}})}}};a.default=o},"3c64":function(s,a,t){var e=t("b954");"string"===typeof e&&(e=[[s.i,e,""]]),e.locals&&(s.exports=e.locals);var i=t("4f06").default;i("69ae7fb9",e,!0,{sourceMap:!1,shadowMode:!1})},"5c3a":function(s,a,t){"use strict";var e=t("3c64"),i=t.n(e);i.a},b954:function(s,a,t){a=s.exports=t("2350")(!1),a.push([s.i,"uni-page-body[data-v-dfd9a826]{background-color:#fff;padding-bottom:%?100?%}.assess_header[data-v-dfd9a826]{padding:%?20?% %?30?%;border-bottom:%?2?% solid #eee}.assess_header_img[data-v-dfd9a826]{width:%?78?%;height:%?78?%;margin-right:%?66?%}.assess_header_text[data-v-dfd9a826]{font-size:%?28?%;color:#434343;margin-right:%?114?%}.assess_header_text[data-v-dfd9a826]:last-child{margin-right:0}.assess_header_text uni-text[data-v-dfd9a826]{font-size:%?28?%;color:#969696;margin-right:%?10?%}.assess_header_text_on[data-v-dfd9a826]{color:#ff610b}.assess_header_text_on uni-text[data-v-dfd9a826]{color:#ff610b}.assess_textarea[data-v-dfd9a826]{padding:%?16?% %?30?%;font-size:%?28?%;color:#434343;width:100%;-webkit-box-sizing:border-box;box-sizing:border-box;height:%?420?%}.assess_picturebox[data-v-dfd9a826]{margin-top:%?20?%;font-size:0;padding:0 %?30?%;overflow:visible}.assess_picture[data-v-dfd9a826]{display:inline-block;margin-right:%?16?%;position:relative;overflow:visible;background-color:#f8f8f8;width:%?160?%;height:%?160?%;margin-bottom:%?16?%;border-radius:%?16?%;vertical-align:bottom}.assess_picture_img[data-v-dfd9a826]{width:%?160?%;height:%?160?%;border-radius:%?10?%;display:block}.assess_picture[data-v-dfd9a826]:nth-child(4n){margin-right:0}.assess_picture_icon[data-v-dfd9a826]{font-size:%?36?%;color:#ff610b;position:absolute;top:%?-14?%;right:%?-10?%}.assess_picture_img2[data-v-dfd9a826]{width:%?75?%;height:%?61?%;position:absolute;left:0;top:0;bottom:0;right:0;display:block;margin:auto}.assess_empty[data-v-dfd9a826]{height:%?16?%;width:100%;background-color:#f8f8f8}.assess_buy[data-v-dfd9a826]{margin-top:%?22?%;padding:0 %?30?%}.assess_buy_left[data-v-dfd9a826]{font-size:%?30?%;color:#b1b1b1;margin-top:%?4?%}.assess_buy_left_on[data-v-dfd9a826]{color:#ff610b}.assess_buy_center[data-v-dfd9a826]{font-size:%?28?%;color:#434343;margin-left:%?14?%}.assess_buy_right[data-v-dfd9a826]{font-size:%?28?%;color:#b1b1b1;-webkit-box-flex:1;-webkit-flex:1;-ms-flex:1;flex:1;text-align:right}.assess_submit[data-v-dfd9a826]{margin:%?106?% auto 0;width:%?440?%;height:%?80?%;background-color:#ff610b;text-align:center;line-height:%?80?%;border-radius:%?80?%;font-size:%?28?%;color:#fff}.nowcount[data-v-dfd9a826]{position:absolute;right:%?30?%;bottom:%?10?%;color:#dfdfdf}body.?%PAGE?%[data-v-dfd9a826]{background-color:#fff}",""])},e18b:function(s,a,t){"use strict";var e=function(){var s=this,a=s.$createElement,t=s._self._c||a;return s.$imgurl?t("div",[s.count<2?[t("v-uni-view",{staticClass:"assess_header hbj"},[t("v-uni-image",{staticClass:"assess_header_img",attrs:{src:s.thumb,mode:"aspectFill"}}),0==s.add?t("v-uni-view",{staticClass:"assess_header_right hbj"},s._l(s.assessList,function(a,e){return t("v-uni-view",{key:e,staticClass:"assess_header_text",class:s.assess==a.id?"assess_header_text_on":"",attrs:{"data-id":a.id},on:{click:function(a){a=s.$handleEvent(a),s.chooseAssess(a)}}},[t("v-uni-text",{staticClass:"iconfont",class:a.icon}),s._v(s._s(a.name))],1)}),1):t("v-uni-view",{staticClass:"assess_header_right hbj",staticStyle:{color:"#b1b1b1"}},[s._v("追评无法重新选择评价等级")])],1),t("v-uni-view",{staticStyle:{position:"relative"}},[t("v-uni-textarea",{staticClass:"assess_textarea",attrs:{maxlength:"200",placeholder:"说说你的使用心得，分享给他们吧!"},on:{input:function(a){a=s.$handleEvent(a),s.evaluate(a)}}}),t("v-uni-view",{staticClass:"nowcount"},[s._v(s._s(s.nowcount)+"/200")])],1),t("v-uni-view",{staticClass:"assess_picturebox"},[s.imgs.length>0?s._l(s.imgs,function(a,e){return t("v-uni-view",{key:e,staticClass:"assess_picture"},[t("v-uni-image",{staticClass:"assess_picture_img",attrs:{src:a,mode:"aspectFill"}}),t("v-uni-view",{staticClass:"assess_picture_icon iconfont icon-x-guanbi2",attrs:{"data-index":e},on:{click:function(a){a=s.$handleEvent(a),s.delimg(a)}}})],1)}):s._e(),t("v-uni-view",{staticClass:"assess_picture"},[t("v-uni-image",{staticClass:"assess_picture_img2",attrs:{src:s.$imgurl+"camera.png",mode:"aspectFill"},on:{click:function(a){a=s.$handleEvent(a),s.chooseimg(a)}}})],1)],2),t("v-uni-view",{staticClass:"assess_empty"}),t("v-uni-view",{staticClass:"assess_buy hbj"},[0==s.add?t("v-uni-view",{staticClass:"assess_buy_left iconfont icon-x-dui2",class:1==s.anonymous?"assess_buy_left_on":"",on:{click:function(a){a=s.$handleEvent(a),s.chooseAnonymous(a)}}}):s._e(),0==s.add?t("v-uni-view",{staticClass:"assess_buy_center",on:{click:function(a){a=s.$handleEvent(a),s.chooseAnonymous(a)}}},[s._v("匿名")]):s._e(),t("v-uni-view",{staticClass:"assess_buy_right",style:0==s.add?"":"text-align:left"},[s._v("你的评价可以帮助其他小伙伴哦！")])],1),t("v-uni-view",{staticClass:"assess_submit",on:{click:function(a){a=s.$handleEvent(a),s.submit(a)}}},[s._v("发布")])]:s._e(),s.count>1?[s._l(s.duoPro,function(a,e){return[t("v-uni-view",{key:e+"_0",staticClass:"assess_header hbj"},[t("v-uni-image",{staticClass:"assess_header_img",attrs:{src:a.thumb,mode:"aspectFill"}}),0==s.add?t("v-uni-view",{staticClass:"assess_header_right hbj"},s._l(s.assessList,function(a,i){return t("v-uni-view",{key:i,staticClass:"assess_header_text",class:s.assess_duo[e]["id"]==a.id?"assess_header_text_on":"",attrs:{"data-id":a.id,"data-dkey":e},on:{click:function(a){a=s.$handleEvent(a),s.chooseAssess_duo(a)}}},[t("v-uni-text",{staticClass:"iconfont",class:a.icon}),s._v(s._s(a.name))],1)}),1):t("v-uni-view",{staticClass:"assess_header_right hbj",staticStyle:{color:"#b1b1b1"}},[s._v("追评无法重新选择评价等级")])],1),t("v-uni-view",{key:e+"_1",staticStyle:{position:"relative"}},[t("v-uni-textarea",{staticClass:"assess_textarea",attrs:{maxlength:"200",placeholder:"说说你的使用心得，分享给他们吧!","data-dkey":e},on:{input:function(a){a=s.$handleEvent(a),s.evaluate_duo_bt(a)}}}),t("v-uni-view",{staticClass:"nowcount"},[s._v(s._s(s.evaluate_duo[e]["nowcount"])+"/200")])],1),t("v-uni-view",{key:e+"_2",staticClass:"assess_picturebox",staticStyle:{"border-bottom":"2rpx solid #EEE"}},[s.imgs_duo[e].length>0?s._l(s.imgs_duo[e],function(a,i){return t("v-uni-view",{key:i,staticClass:"assess_picture"},[t("v-uni-image",{staticClass:"assess_picture_img",attrs:{src:a,mode:"aspectFill"}}),t("v-uni-view",{staticClass:"assess_picture_icon iconfont icon-x-guanbi2",attrs:{"data-dkey":e,"data-index":i},on:{click:function(a){a=s.$handleEvent(a),s.delimg_duo(a)}}})],1)}):s._e(),t("v-uni-view",{staticClass:"assess_picture"},[t("v-uni-image",{staticClass:"assess_picture_img2",attrs:{src:s.$imgurl+"camera.png",mode:"aspectFill","data-dkey":e},on:{click:function(a){a=s.$handleEvent(a),s.chooseimg_duo(a)}}})],1)],2),t("v-uni-view",{key:e+"_3",staticClass:"assess_buy hbj"},[0==s.add?t("v-uni-view",{staticClass:"assess_buy_left iconfont icon-x-dui2",class:1==s.anonymous_duo[e]["anonymous"]?"assess_buy_left_on":"",attrs:{"data-dkey":e},on:{click:function(a){a=s.$handleEvent(a),s.chooseAnonymous_duo(a)}}}):s._e(),0==s.add?t("v-uni-view",{staticClass:"assess_buy_center",attrs:{"data-dkey":e},on:{click:function(a){a=s.$handleEvent(a),s.chooseAnonymous_duo(a)}}},[s._v("匿名")]):s._e(),t("v-uni-view",{staticClass:"assess_buy_right",style:0==s.add?"":"text-align:left"},[s._v("你的评价可以帮助其他小伙伴哦！")])],1),t("v-uni-view",{key:e+"_4",staticClass:"assess_empty"})]}),t("v-uni-view",{staticClass:"assess_submit",on:{click:function(a){a=s.$handleEvent(a),s.submit_duo(a)}}},[s._v("发布")])]:s._e()],2):s._e()},i=[];t.d(a,"a",function(){return e}),t.d(a,"b",function(){return i})},f0ba:function(s,a,t){"use strict";t.r(a);var e=t("0518"),i=t.n(e);for(var n in e)"default"!==n&&function(s){t.d(a,s,function(){return e[s]})}(n);a["default"]=i.a},ff6f:function(s,a,t){"use strict";t.r(a);var e=t("e18b"),i=t("f0ba");for(var n in i)"default"!==n&&function(s){t.d(a,s,function(){return i[s]})}(n);t("5c3a");var o=t("2877"),u=Object(o["a"])(i["default"],e["a"],e["b"],!1,null,"dfd9a826",null);a["default"]=u.exports}}]);