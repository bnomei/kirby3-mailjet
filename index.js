(()=>{(function(){"use strict";var _=function(){var e=this,a=e.$createElement,s=e._self._c||a;return s("k-inside",[s("k-view",{staticClass:"k-mailjet-sms-view"},[s("k-header",[e._v("Mailjet SMS")]),s("k-fieldset",{attrs:{fields:{to:{label:this.$t("mailjet.sms.to"),type:"text",placeholder:this.$t("mailjet.sms.to.placeholder"),width:"1/2",required:!0},from:{label:this.$t("mailjet.sms.from"),type:"text",placeholder:this.$t("mailjet.sms.from.placeholder"),width:"1/2"},message:{label:this.$t("mailjet.sms.message"),maxlength:e.message.maxlength,required:!0,type:"textarea",buttons:!1}}},model:{value:e.sms,callback:function(i){e.sms=i},expression:"sms"}}),s("div",{staticClass:"buttonwrapper"},[s("k-button",{staticClass:"smsbutton",class:{sending:e.sending,"has-error":e.hasError,"is-success":e.isSuccess},attrs:{icon:e.currenticon,disabled:!e.cansend},on:{click:function(i){return e.onClick()}}},[e._v(e._s(this.$t("mailjet.sms.action")))])],1)],1)],1)},v=[],E="";function l(e,a,s,i,r,u,d,b){var t=typeof e=="function"?e.options:e;a&&(t.render=a,t.staticRenderFns=s,t._compiled=!0),i&&(t.functional=!0),u&&(t._scopeId="data-v-"+u);var o;if(d?(o=function(n){n=n||this.$vnode&&this.$vnode.ssrContext||this.parent&&this.parent.$vnode&&this.parent.$vnode.ssrContext,!n&&typeof __VUE_SSR_CONTEXT__!="undefined"&&(n=__VUE_SSR_CONTEXT__),r&&r.call(this,n),n&&n._registeredComponents&&n._registeredComponents.add(d)},t._ssrRegister=o):r&&(o=b?function(){r.call(this,(t.functional?this.parent:this).$root.$options.shadowRoot)}:r),o)if(t.functional){t._injectStyles=o;var x=t.render;t.render=function(y,f){return o.call(f),x(y,f)}}else{var m=t.beforeCreate;t.beforeCreate=m?[].concat(m,o):[o]}return{exports:e,options:t}}const g={data:function(){return{sms:{from:"",to:"",message:""},message:{maxlength:0},cooldown:Number,sending:!1,hasError:!1,isSuccess:!1}},created(){this.$api.get("mailjet/sms/config").then(e=>{this.sms.from=e.from,this.message.maxlength=e.maxlength,this.cooldown=e.cooldown}).catch(e=>{console.log(e)})},computed:{currenticon:function(){return this.hasError?"alert":this.isSuccess?"check":"mailjet-sms"},cansend:function(){return this.sms.to.length>0&&this.sms.message.length>0&&this.sms.message.length<=this.message.maxlength}},methods:{onClick:function(){this.sending=!0,this.$api.post("mailjet/sms/send",this.sms).then(e=>{e.statusCode===200?(this.handleSuccess(),this.sms.to=""):this.handleError(),this.sending=!1}).catch(e=>{this.sending=!1,console.log(e)})},handleSuccess:function(){let e=this;e.isSuccess=!0,setTimeout(function(){e.isSuccess=!1},e.cooldown)},handleError:function(){let e=this;e.hasError=!0,setTimeout(function(){e.hasError=!1},e.cooldown)}}},c={};var p=l(g,_,v,!1,w,"6fef7f10",null,null);function w(e){for(let a in c)this[a]=c[a]}var $=function(){return p.exports}(),k=function(){var e=this,a=e.$createElement,s=e._self._c||a;return s("k-inside",[s("k-view",{staticClass:"k-mailjet-schedule-view"},[s("k-header",[e._v("Mailjet Schedule")]),s("k-items",{attrs:{layout:"cards","data-size":"small"}},e._l(e.schedules,function(i){return s("k-item",{key:i.value,attrs:{layout:"cards",info:i.date,text:i.name}})}),1)],1)],1)},V=[],M="";const j={data(){return{schedules:[]}},created(){this.load()},methods:{load(){this.$api.get("mailjet/schedule/list").then(e=>{this.schedules=e}).catch(e=>{console.log(e)})}}},h={};var S=l(j,k,V,!1,C,"a10e09cc",null,null);function C(e){for(let a in h)this[a]=h[a]}var H=function(){return S.exports}();panel.plugin("schnitzerund/sms",{components:{"k-mailjet-schedule-view":H,"k-mailjet-sms-view":$},icons:{"mailjet-schedule":'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"><g class="nc-icon-wrapper"><path data-color="color-2" d="M25,18a7,7,0,1,0,7,7A7,7,0,0,0,25,18Zm4,8H24V21h2v3h3Z"/> <path d="M16,29H4a1,1,0,0,1-1-1V10A1,1,0,0,1,4,9H28a1,1,0,0,1,1,1v6h2V6a2,2,0,0,0-2-2H24V1a1,1,0,0,0-2,0V4H10V1A1,1,0,0,0,8,1V4H3A2,2,0,0,0,1,6V29a2,2,0,0,0,2,2H16Z"/></g></svg>',"mailjet-sms":'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48"><g class="nc-icon-wrapper"><path d="M43,1H23a4,4,0,0,0-4,4V25a1,1,0,0,0,1,1,.988.988,0,0,0,.581-.187L27.32,21H43a4,4,0,0,0,4-4V5A4,4,0,0,0,43,1ZM32,15H26a1,1,0,0,1,0-2h6a1,1,0,0,1,0,2Zm8-6H26a1,1,0,0,1,0-2H40a1,1,0,0,1,0,2Z" data-color="color-2"/><path d="M31,23V39a1,1,0,0,1-1,1H7a1,1,0,0,1-1-1V11a1,1,0,0,1,1-1H17V5H8A4,4,0,0,0,4,9V43a4,4,0,0,0,4,4H29a4,4,0,0,0,4-4V23Z"/></g></svg>'}})})();})();
