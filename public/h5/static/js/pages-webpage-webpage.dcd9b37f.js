(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-webpage-webpage"],{"41fa":function(e,n,t){"use strict";t.r(n);var r=t("f9bd"),u=t("7e23");for(var i in u)"default"!==i&&function(e){t.d(n,e,function(){return u[e]})}(i);var a=t("2877"),o=Object(a["a"])(u["default"],r["a"],r["b"],!1,null,"2d293718",null);n["default"]=o.exports},"7e23":function(e,n,t){"use strict";t.r(n);var r=t("aae4"),u=t.n(r);for(var i in r)"default"!==i&&function(e){t.d(n,e,function(){return r[e]})}(i);n["default"]=u.a},aae4:function(e,n,t){"use strict";Object.defineProperty(n,"__esModule",{value:!0}),n.default=void 0;var r={data:function(){return{webviewStyles:{progress:{color:"#FF3333"}},url:"",baseinfo:""}},onPullDownRefresh:function(){var e=this;e.getinfos(),uni.stopPullDownRefresh()},onLoad:function(e){var n=this;n._baseMin(this),n.url=decodeURIComponent(e.url);e.fxsid&&(n.fxsid=e.fxsid)},onShareAppMessage:function(){return{}}};n.default=r},f9bd:function(e,n,t){"use strict";var r=function(){var e=this,n=e.$createElement,t=e._self._c||n;return t("div",[t("v-uni-web-view",{attrs:{"webview-styles":e.webviewStyles,src:e.url}})],1)},u=[];t.d(n,"a",function(){return r}),t.d(n,"b",function(){return u})}}]);