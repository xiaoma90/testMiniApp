(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pagesExchange-show-show"],{"0072":function(t,e,i){var a=i("bbe1");"string"===typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);var n=i("4f06").default;n("0994b242",a,!0,{sourceMap:!1,shadowMode:!1})},"0630":function(t,e,i){"use strict";i.r(e);var a=i("a1de"),n=i.n(a);for(var o in a)"default"!==o&&function(t){i.d(e,t,function(){return a[t]})}(o);e["default"]=n.a},"3e0a":function(t,e,i){"use strict";i.r(e);var a=i("a2aa"),n=i("0630");for(var o in n)"default"!==o&&function(t){i.d(e,t,function(){return n[t]})}(o);i("9552");var r=i("2877"),s=Object(r["a"])(n["default"],a["a"],a["b"],!1,null,"227f61cc",null);e["default"]=s.exports},"53f2":function(t,e,i){"use strict";Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0,i("28a5"),i("7f7f"),i("3b2b"),i("a481"),i("4917");var a=/^<([-A-Za-z0-9_]+)((?:\s+[a-zA-Z_:][-a-zA-Z0-9_:.]*(?:\s*=\s*(?:(?:"[^"]*")|(?:'[^']*')|[^>\s]+))?)*)\s*(\/?)>/,n=/^<\/([-A-Za-z0-9_]+)[^>]*>/,o=/([a-zA-Z_:][-a-zA-Z0-9_:.]*)(?:\s*=\s*(?:(?:"((?:\\.|[^"])*)")|(?:'((?:\\.|[^'])*)')|([^>\s]+)))?/g,r=h("area,base,basefont,br,col,frame,hr,img,input,link,meta,param,embed,command,keygen,source,track,wbr"),s=h("a,address,article,applet,aside,audio,blockquote,button,canvas,center,dd,del,dir,div,dl,dt,fieldset,figcaption,figure,footer,form,frameset,h1,h2,h3,h4,h5,h6,header,hgroup,hr,iframe,isindex,li,map,menu,noframes,noscript,object,ol,output,p,pre,section,script,table,tbody,td,tfoot,th,thead,tr,ul,video"),c=h("abbr,acronym,applet,b,basefont,bdo,big,br,button,cite,code,del,dfn,em,font,i,iframe,img,input,ins,kbd,label,map,object,q,s,samp,script,select,small,span,strike,strong,sub,sup,textarea,tt,u,var"),d=h("colgroup,dd,dt,li,options,p,td,tfoot,th,thead,tr"),l=h("checked,compact,declare,defer,disabled,ismap,multiple,nohref,noresize,noshade,nowrap,readonly,selected"),u=h("script,style");function f(t,e){var i,f,h,p=[],v=t;p.last=function(){return this[this.length-1]};while(t){if(f=!0,p.last()&&u[p.last()])t=t.replace(new RegExp("([\\s\\S]*?)</"+p.last()+"[^>]*>"),function(t,i){return i=i.replace(/<!--([\s\S]*?)-->|<!\[CDATA\[([\s\S]*?)]]>/g,"$1$2"),e.chars&&e.chars(i),""}),_("",p.last());else if(0==t.indexOf("\x3c!--")?(i=t.indexOf("--\x3e"),i>=0&&(e.comment&&e.comment(t.substring(4,i)),t=t.substring(i+3),f=!1)):0==t.indexOf("</")?(h=t.match(n),h&&(t=t.substring(h[0].length),h[0].replace(n,_),f=!1)):0==t.indexOf("<")&&(h=t.match(a),h&&(t=t.substring(h[0].length),h[0].replace(a,b),f=!1)),f){i=t.indexOf("<");var g=i<0?t:t.substring(0,i);t=i<0?"":t.substring(i),e.chars&&e.chars(g)}if(t==v)throw"Parse Error: "+t;v=t}function b(t,i,a,n){if(i=i.toLowerCase(),s[i])while(p.last()&&c[p.last()])_("",p.last());if(d[i]&&p.last()==i&&_("",i),n=r[i]||!!n,n||p.push(i),e.start){var u=[];a.replace(o,function(t,e){var i=arguments[2]?arguments[2]:arguments[3]?arguments[3]:arguments[4]?arguments[4]:l[e]?e:"";u.push({name:e,value:i,escaped:i.replace(/(^|[^\\])"/g,'$1\\"')})}),e.start&&e.start(i,u,n)}}function _(t,i){if(i){for(a=p.length-1;a>=0;a--)if(p[a]==i)break}else var a=0;if(a>=0){for(var n=p.length-1;n>=a;n--)e.end&&e.end(p[n]);p.length=a}}_()}function h(t){for(var e={},i=t.split(","),a=0;a<i.length;a++)e[i[a]]=!0;return e}function p(t){return t.replace(/<\?xml.*\?>\n/,"").replace(/<!doctype.*>\n/,"").replace(/<!DOCTYPE.*>\n/,"")}function v(t){return t.reduce(function(t,e){var i=e.value,a=e.name;return t[a]?t[a]=t[a]+" "+i:t[a]=i,t},{})}function g(t){t=p(t);var e=[],i={node:"root",children:[]};return f(t,{start:function(t,a,n){var o={name:t};if(0!==a.length&&(o.attrs=v(a)),n){var r=e[0]||i;r.children||(r.children=[]),r.children.push(o)}else e.unshift(o)},end:function(t){var a=e.shift();if(a.name!==t&&console.error("invalid state: mismatch end tag"),0===e.length)i.children.push(a);else{var n=e[0];n.children||(n.children=[]),n.children.push(a)}},chars:function(t){var a={type:"text",text:t};if(0===e.length)i.children.push(a);else{var n=e[0];n.children||(n.children=[]),n.children.push(a)}},comment:function(t){var i={node:"comment",text:t},a=e[0];a.children||(a.children=[]),a.children.push(i)}}),i.children}var b=g;e.default=b},9552:function(t,e,i){"use strict";var a=i("0072"),n=i.n(a);n.a},a1de:function(t,e,i){"use strict";var a=i("288e");Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0,i("a481"),i("b54a"),i("7f7f");var n=a(i("53f2")),o=i("55f5"),r={data:function(){return{$imgurl:this.$imgurl,baseinfo:[],orderlist:[],id:"",sc:0,bg:"",datas:{labels:[],slide:[]},content:"",jhsl:1,dprice:"",yhje:0,hjjg:"",sfje:"",order:"",my_num:"",xg_num:"",shengyu:"",userInfo:"",num:[],xz_num:[],proinfo:"",pic_video:"",isplay:!1,currentSwiper:0,minHeight:220,heighthave:0,autoplay:!0,dlength:0,needAuth:!1,needBind:!1}},onShareAppMessage:function(){var t=this;return{title:t.title}},onPullDownRefresh:function(){uni.stopPullDownRefresh()},onLoad:function(t){var e=this,i=t.id;e.id=i;var a=0;t.fxsid&&(a=t.fxsid,uni.setStorageSync("fxsid",a),e.fxsid=t.fxsid),this._baseMin(this);uni.getStorageSync("suid");o.h5login(a,function(){var t=e.id;e.getShowPic(t)})},methods:{makePhoneCall:function(t){var e=this,i=e.baseinfo.tel;uni.makePhoneCall({phoneNumber:i})},collect:function(t){var e=this;if(!this.getSuid())return!1;t.currentTarget.dataset.name;uni.request({url:e.$baseurl+"doPageCollect",data:{uniacid:e.$uniacid,suid:wx.getStorageSync("suid"),types:"exchange",id:e.id},header:{"content-type":"application/json"},success:function(t){var i=t.data.data;e.sc="收藏成功"==i?1:0,uni.showToast({title:i,icon:"succes",duration:1e3,mask:!0})}})},redirectto:function(t){var e=t.currentTarget.dataset.link,i=t.currentTarget.dataset.linktype;this._redirectto(e,i)},getShowPic:function(t){var e=this,i=uni.getStorageSync("suid");uni.request({url:e.$baseurl+"doPageScoreinfo",data:{uniacid:e.$uniacid,id:t,suid:i},success:function(t){e.datas=t.data.data,e.dlength=e.datas.slide.length,e.datas.product_txt&&(e.datas.product_txt=e.datas.product_txt.replace(/\<img/gi,'<img style="width:100%;height:auto;display:block" '),e.datas.product_txt=(0,n.default)(e.datas.product_txt)),e.pic_video=t.data.data.video,e.sc=t.data.data.sc,uni.setNavigationBarTitle({title:t.data.data.title}),uni.hideNavigationBarLoading(),uni.stopPullDownRefresh()}})},save:o.throttle(function(t){var e=this;if(!this.getSuid())return!1;e.jhsl;var i=uni.getStorageSync("suid"),a=e.id;uni.request({url:e.$baseurl+"doPagecheckvip",data:{uniacid:e.$uniacid,kwd:"exchange",suid:i},success:function(n){n.data.data?uni.showModal({title:"提示",content:"确定兑换此商品吗？",success:function(n){if(n.confirm)uni.request({url:e.$baseurl+"doPageScoreorder",data:{uniacid:e.$uniacid,suid:i,id:a,formId:t.detail.formId,openid:uni.getStorageSync("openid"),source:uni.getStorageSync("source")},header:{"content-type":"application/json"},success:function(t){var e=t.data.data,i=e.flag;0==i?uni.showModal({title:"提醒",content:e.msg,showCancel:!1}):uni.showToast({title:"兑换成功",icon:"success",duration:1e3,success:function(){setTimeout(function(){uni.redirectTo({url:"/pagesExchange/order/order"})},1e3)}})}});else if(n.cancel);}}):uni.showModal({title:"进入失败",content:"使用本功能需先开通vip!",showCancel:!1,success:function(t){t.confirm&&uni.navigateTo({url:"/pages/register/register?type=jifen"})}})},fail:function(t){}})},2e3),tabChange:function(t){var e=t.currentTarget.dataset.id;this.nowcon=e},swiperLoad:function(t){var e=this;uni.getSystemInfo({success:function(i){var a=t.detail.width,n=t.detail.height,o=a/n,r=i.windowWidth/o;e.heighthave||(e.minHeight=r,e.heighthave=1)}})},swiperChange:function(t){this.autoplay=!0,this.currentSwiper=t.detail.current,this.isplay=!1,this.autoplay=this.autoplay},playvideo:function(){var t=this;t.autoplay=!1,t.isplay=!0,t.autoplay=t.autoplay},endvideo:function(){var t=this;t.autoplay=!0,t.isplay=!1,t.autoplay=t.autoplay},getSuid:function(){var t=uni.getStorageSync("suid");if(t)return!0;var e="";return e?this.needBind=!0:this.needAuth=!0,!1},cell:function(){this.needAuth=!1},closeAuth:function(){this.needAuth=!1,this.needBind=!0},closeBind:function(){this.needBind=!1}}};e.default=r},a2aa:function(t,e,i){"use strict";var a=function(){var t=this,e=t.$createElement,i=t._self._c||e;return t.$imgurl?i("div",[t.needAuth?i("auth",{attrs:{needAuth:t.needAuth},on:{closeAuth:function(e){e=t.$handleEvent(e),t.closeAuth(e)},cell:function(e){e=t.$handleEvent(e),t.cell(e)}}}):t._e(),t.needBind?i("bindPhone",{attrs:{needBind:t.needBind},on:{closeBind:function(e){e=t.$handleEvent(e),t.closeBind(e)}}}):t._e(),i("v-uni-view",{staticClass:"pro_head"},[i("v-uni-view",{staticClass:"wrap",style:"height:"+t.minHeight+"px;"},[i("v-uni-swiper",{staticClass:"slide",staticStyle:{height:"100%"},attrs:{"indicator-active-color":t.baseinfo.base_color2,interval:"3000",duration:"1000",autoplay:t.autoplay,"indicator-color":"rgba(0, 0, 0, .3)"},on:{change:function(e){e=t.$handleEvent(e),t.swiperChange(e)}}},[t._l(t.datas.slide,function(e,a){return[i("v-uni-swiper-item",[i("v-uni-image",{staticClass:"slide-image",attrs:{src:e,width:"100%",mode:"widthFix"},on:{load:function(e){e=t.$handleEvent(e),t.swiperLoad(e)}}}),!t.isplay&&t.pic_video&&0==a?i("v-uni-view",[i("v-uni-image",{staticClass:"play-image",attrs:{src:t.$imgurl+"play_audio.png",mode:"aspectFill"},on:{click:function(e){e=t.$handleEvent(e),t.playvideo(e)}}})],1):t._e(),t.isplay?i("v-uni-view",{staticStyle:{height:"100%"}},[i("v-uni-image",{staticClass:"play-image1",attrs:{src:t.$imgurl+"c.png",mode:"widthFix"},on:{click:function(e){e=t.$handleEvent(e),t.endvideo(e)}}}),i("v-uni-video",{staticStyle:{margin:"0",height:"100%",width:"100%",overflow:"hidden"},attrs:{src:t.pic_video,objectFit:"cover",autoplay:"true"}})],1):t._e()],1)]})],2),t.isplay?t._e():i("v-uni-view",{staticClass:"dots hbj",style:{width:14*(t.dlength-1)+14*t.dlength+"rpx"}},[t._l(t.datas.slide,function(e,a){return[i("v-uni-view",{key:a+"_0",staticClass:"dot",class:a==t.currentSwiper?"active":""})]})],2)],1),t.autoplay?i("v-uni-view",{staticClass:"pro_tit"},[t._v(t._s(t.datas.title))]):t._e()],1),i("v-uni-view",{staticClass:"price"},[i("v-uni-view",{staticClass:"price1",style:{color:t.baseinfo.base_color2,border:"2rpx solid "+t.baseinfo.base_color2}},[t._v("所需积分")]),i("v-uni-view",{staticClass:"price2",style:"color:"+t.baseinfo.base_color2},[t._v(t._s(t.datas.price))]),i("v-uni-view",{staticClass:"price3 pline"},[t._v("¥"+t._s(t.datas.market_price))]),t.datas.pro_kc>=0?i("v-uni-view",{staticClass:"price3"},[t._v("库存量："+t._s(t.datas.pro_kc))]):t._e(),i("v-uni-view",{staticClass:"sale_num"},[t._v("已兑："+t._s(t.datas.sale_num))])],1),t.datas.labels[0]?i("v-uni-view",{staticClass:"biaoq"},t._l(t.datas.labels,function(e,a){return i("v-uni-view",{directives:[{name:"key",rawName:"v-key",value:e,expression:"item"}],key:a,staticClass:"biaoq_t"},[i("v-uni-image",{staticClass:"biaoq_p",attrs:{src:t.$imgurl+"pro_icon.png"}}),t._v(t._s(e))],1)}),1):t._e(),t.datas.pro_xz>0?i("v-uni-view",{staticClass:"youhuiq"},[t.datas.pro_xz>0?i("v-uni-view",{staticClass:"youhdiv"},[t._v("每人限购"+t._s(t.datas.pro_xz))]):t._e()],1):t._e(),i("v-uni-view",{staticClass:"spxq"},[i("v-uni-view",{staticClass:"p_title"},[i("v-uni-view",{staticClass:"pcon active",style:"color:"+t.baseinfo.base_color2},[t._v("兑换详情")])],1),i("v-uni-view",{staticClass:"xqnr"},[i("v-uni-rich-text",{attrs:{nodes:t.datas.product_txt}})],1)],1),i("v-uni-view",{staticClass:"pro_footer_bg"}),i("v-uni-view",{staticClass:"pro_footer"},[i("v-uni-view",{staticClass:"pro_f1 pro_f_home"},[i("v-uni-navigator",{attrs:{"open-type":"redirectTo",url:"/pages/index/index"}},[i("v-uni-image",{attrs:{src:t.$imgurl+"i_home.png"}}),i("v-uni-text",[t._v("首页")])],1)],1),i("v-uni-view",{staticClass:"pro_f1 pro_f_star",attrs:{"data-name":t.datas.id},on:{click:function(e){e=t.$handleEvent(e),t.collect(e)}}},[0==t.sc?i("v-uni-image",{attrs:{src:t.$imgurl+"i_like.png"}}):t._e(),1==t.sc?i("v-uni-image",{attrs:{src:t.$imgurl+"u_star.png"}}):t._e(),i("v-uni-text",[t._v("收藏")])],1),i("v-uni-view",{staticClass:"pro_f1 pro_f_tel",on:{click:function(e){e=t.$handleEvent(e),t.makePhoneCall(e)}}},[i("v-uni-image",{attrs:{src:t.$imgurl+"i_tel.png"}}),i("v-uni-text",[t._v("客服")])],1),i("v-uni-form",{attrs:{"report-submit":"true"},on:{submit:function(e){e=t.$handleEvent(e),t.save(e)}}},[t.datas.pro_kc>0||-1==t.datas.pro_kc?i("v-uni-button",{staticClass:"pro_f1 pro_f_buy",style:{background:t.baseinfo.base_color2,color:t.baseinfo.tabbar_bg2},attrs:{formType:"submit"}},[t._v("立即兑换")]):t._e()],1),0==t.datas.pro_kc?i("v-uni-view",{staticClass:"pro_f1 pro_f_buy_t"},[t._v("您来晚了，商品已被兑换完")]):t._e()],1)],1):t._e()},n=[];i.d(e,"a",function(){return a}),i.d(e,"b",function(){return n})},bbe1:function(t,e,i){e=t.exports=i("2350")(!1),e.push([t.i,"uni-page-body[data-v-227f61cc]{height:100%}uni-button[data-v-227f61cc]{height:%?100?%;line-height:%?100?%;padding:0;margin:0;border-radius:0}.pro_head[data-v-227f61cc]{position:relative}.slide[data-v-227f61cc]{margin-bottom:0}.pro_tit[data-v-227f61cc]{background:#fff;height:%?70?%;line-height:%?70?%;padding-left:%?20?%;\n\t\t/* position: absolute; */\n\t\t/* bottom: 0;\n\t\tleft: 0; */width:100%;-webkit-box-sizing:border-box;box-sizing:border-box;color:#666;font-size:%?26?%;overflow:hidden}.price[data-v-227f61cc]{position:relative;height:%?100?%;line-height:%?110?%;border-bottom:1px solid #eee;background-color:#fff;overflow:hidden}.products_qg[data-v-227f61cc]{width:%?200?%;height:%?70?%;line-height:%?70?%;background-color:#ff9e05;color:#fff;text-align:center;position:absolute;bottom:%?22?%;right:%?20?%;border-radius:%?6?%}.price1[data-v-227f61cc]{float:left;font-size:%?22?%;\n\t\t/* color: #e7142f; border: 2rpx solid #e7142f;  */border-radius:%?6?%;line-height:%?26?%;height:%?26?%;margin:%?38?% %?6?% 0 %?20?%;padding:0 %?4?%}.price2[data-v-227f61cc]{float:left;font-size:%?56?%;line-height:%?90?%}.price3[data-v-227f61cc]{float:left;font-size:%?26?%;color:#999;margin-left:10px}.pline[data-v-227f61cc]{text-decoration:line-through}.sale_num[data-v-227f61cc]{float:right;margin-right:%?20?%}.biaoq[data-v-227f61cc]{overflow:hidden;padding:%?20?%;background-color:#fff;color:#71b41a}.biaoq_t[data-v-227f61cc]{float:left;margin-right:%?26?%;background-size:%?30?%}.biaoq_p[data-v-227f61cc]{width:%?34?%;height:%?34?%;margin-right:%?10?%;position:relative;top:%?6?%}.youhuiq[data-v-227f61cc]{border-top:1px solid #eee;border-bottom:1px solid #eee;margin-top:10px;background-color:#fff;padding:20px 0 20px 20px}.youhdiv[data-v-227f61cc]{display:inline-block;margin-right:20px;border:1px solid #dedede;border-radius:4px;padding:5px 20px}.spxq[data-v-227f61cc]{background-color:#fff;margin-top:%?20?%;padding:0 %?20?% %?60?%}.xbts[data-v-227f61cc]{height:%?66?%;line-height:%?66?%;border-bottom:1px solid #eee;color:#999;font-size:14px}.xqnr[data-v-227f61cc]{margin-top:%?30?%}.pro_footer_bg[data-v-227f61cc]{height:%?100?%}.pro_footer[data-v-227f61cc]{position:fixed;height:%?100?%;line-height:%?100?%;left:0;bottom:0;z-index:999;width:100%;background:#fff;font-size:%?26?%}.pro_f1[data-v-227f61cc]{float:left;-webkit-box-sizing:border-box;box-sizing:border-box;text-align:center}.pro_f1 uni-navigator[data-v-227f61cc]{display:block;height:100%;overflow:hidden}.pro_f1 uni-image[data-v-227f61cc]{display:block;width:%?36?%;height:%?36?%;margin:%?15?% auto %?8?%}.pro_f1 uni-text[data-v-227f61cc]{display:block;line-height:%?30?%;font-size:%?22?%}.pro_f_home[data-v-227f61cc],.pro_f_star[data-v-227f61cc],.pro_f_tel[data-v-227f61cc]{width:14%;border-right:1px solid #eee;height:100%;border-top:1px solid #eee}.pro_f_buy[data-v-227f61cc],.pro_f_price[data-v-227f61cc]{width:58%}.pro_f_price[data-v-227f61cc]{color:#fff;font-size:%?36?%;background:#fd8c00}.pro_f_buy[data-v-227f61cc]{background:#e7142f;color:#fff;font-size:%?28?%}.pro_f_buy_t[data-v-227f61cc]{width:58%;background:#ccc;color:#fff;font-size:%?28?%}.p_title[data-v-227f61cc]{display:-webkit-box;display:-webkit-flex;display:-ms-flexbox;display:flex;border-bottom:%?2?% solid #eee}\n\n\t/* .active {\n  color: #e7142f;\n} */.p_title .pcon[data-v-227f61cc]{\n\t\t/* float: left; */width:100%;text-align:center;line-height:%?80?%;position:relative}.pro_head[data-v-227f61cc]{position:relative}.wrap[data-v-227f61cc]{max-height:15rem;position:relative}.slide[data-v-227f61cc]{margin-bottom:0}.slide-image[data-v-227f61cc]{display:block;position:absolute}.slide .play-image[data-v-227f61cc]{display:block;width:%?80?%;height:%?80?%;position:absolute;left:0;top:0;right:0;z-index:6000;margin:auto;bottom:%?34?%}.video-width[data-v-227f61cc]{width:100%;height:283px}.slide .play-image1[data-v-227f61cc]{display:block;width:%?30?%;height:%?30?%;position:absolute;left:0;top:-216px;right:%?-667?%;bottom:0;z-index:6000;margin:auto}.dot[data-v-227f61cc]{width:%?15?%;height:%?15?%;border-radius:50%;margin-right:%?15?%;background-color:#fff}.dots[data-v-227f61cc]{padding:%?10?% %?15?%;position:absolute;margin:auto;left:0;right:0;bottom:%?20?%;text-align:center;background:rgba(0,0,0,.5);border-radius:%?30?%;width:%?90?%}.dots .active[data-v-227f61cc]{width:%?15?%;height:%?15?%;background-color:#c5c4c3}\n\n\t/* .p_title .active i {\n  width: 60rpx;\n  height: 6rpx;\n  background: #e7142f;\n  display: block;\n  margin: 0 auto;\n} */.active[data-v-227f61cc]{border-bottom:0!important}",""])}}]);