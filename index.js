(function () {var a={data:function(){return{sms:{from:"",to:"",message:""},message:{maxlength:0},cooldown:Number,sending:!1,hasError:!1,isSuccess:!1}},created:function(){var s=this;this.$api.get("mailjet/sms/config").then(function(t){s.sms.from=t.from,s.message.maxlength=t.maxlength,s.cooldown=t.cooldown}).catch(function(s){console.log(s)})},computed:{currenticon:function(){return this.hasError?"alert":this.isSuccess?"check":"mailjet-sms"},cansend:function(){return this.sms.to.length>0&&this.sms.message.length>0&&this.sms.message.length<=this.message.maxlength}},methods:{onClick:function(){var s=this;this.sending=!0,this.$api.post("mailjet/sms/send",this.sms).then(function(t){200===t.statusCode?(s.handleSuccess(),s.sms.to=""):s.handleError(),s.sending=!1}).catch(function(t){s.sending=!1,console.log(t)})},handleSuccess:function(){var s=this;s.isSuccess=!0,setTimeout(function(){s.isSuccess=!1},s.cooldown)},handleError:function(){var s=this;s.hasError=!0,setTimeout(function(){s.hasError=!1},s.cooldown)}}};if(typeof a==="function"){a=a.options}Object.assign(a,function(){var render=function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c("k-view",{staticClass:"k-mailjet-sms-view"},[_c("k-header",[_vm._v("Mailjet SMS")]),_vm._v(" "),_c("k-fieldset",{attrs:{"fields":{to:{label:this.$t("mailjet.sms.to"),type:"text",placeholder:this.$t("mailjet.sms.to.placeholder"),width:"1/2",required:true},from:{label:this.$t("mailjet.sms.from"),type:"text",placeholder:this.$t("mailjet.sms.from.placeholder"),width:"1/2"},message:{label:this.$t("mailjet.sms.message"),maxlength:_vm.message.maxlength,required:true,type:"textarea",buttons:false}}},model:{value:_vm.sms,callback:function($$v){_vm.sms=$$v},expression:"sms"}}),_vm._v(" "),_c("div",{staticClass:"buttonwrapper"},[_c("k-button",{staticClass:"smsbutton",class:{sending:_vm.sending,"has-error":_vm.hasError,"is-success":_vm.isSuccess},attrs:{"icon":_vm.currenticon,"disabled":!_vm.cansend},on:{"click":function($event){return _vm.onClick()}}},[_vm._v(_vm._s(this.$t("mailjet.sms.action")))])],1)],1)};var staticRenderFns=[];return{render:render,staticRenderFns:staticRenderFns,_compiled:true,_scopeId:"data-v-ab35ae",functional:undefined}}());var b={name:"ScheduleView",data:function(){return{schedules:[]}},created:function(){this.load()},methods:{load:function(){var e=this;this.$api.get("mailjet/schedule/list").then(function(t){e.schedules=t}).catch(function(e){console.log(e)})}}};if(typeof b==="function"){b=b.options}Object.assign(b,function(){var render=function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c("k-view",{staticClass:"k-mailjet-schedule-view"},[_c("k-header",[_vm._v("Mailjet Schedule")]),_vm._v(" "),_c("k-cards",{attrs:{"data-size":"small"}},_vm._l(_vm.schedules,function(schedule){return _c("k-card",{key:schedule.value,attrs:{"info":schedule.date,"text":schedule.name}})}),1)],1)};var staticRenderFns=[];return{render:render,staticRenderFns:staticRenderFns,_compiled:true,_scopeId:"data-v-329329",functional:undefined}}());panel.plugin("schnitzerund/sms",{views:{schedule:{component:b,icon:"mailjet-schedule",label:"Mailjet Schedule"},sms:{component:a,icon:"mailjet-sms",label:"Mailjet SMS"}},icons:{"mailjet-schedule":"<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 32 32\"><g class=\"nc-icon-wrapper\"><path data-color=\"color-2\" d=\"M25,18a7,7,0,1,0,7,7A7,7,0,0,0,25,18Zm4,8H24V21h2v3h3Z\"/> <path d=\"M16,29H4a1,1,0,0,1-1-1V10A1,1,0,0,1,4,9H28a1,1,0,0,1,1,1v6h2V6a2,2,0,0,0-2-2H24V1a1,1,0,0,0-2,0V4H10V1A1,1,0,0,0,8,1V4H3A2,2,0,0,0,1,6V29a2,2,0,0,0,2,2H16Z\"/></g></svg>","mailjet-sms":"<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 48 48\"><g class=\"nc-icon-wrapper\"><path d=\"M43,1H23a4,4,0,0,0-4,4V25a1,1,0,0,0,1,1,.988.988,0,0,0,.581-.187L27.32,21H43a4,4,0,0,0,4-4V5A4,4,0,0,0,43,1ZM32,15H26a1,1,0,0,1,0-2h6a1,1,0,0,1,0,2Zm8-6H26a1,1,0,0,1,0-2H40a1,1,0,0,1,0,2Z\" data-color=\"color-2\"/><path d=\"M31,23V39a1,1,0,0,1-1,1H7a1,1,0,0,1-1-1V11a1,1,0,0,1,1-1H17V5H8A4,4,0,0,0,4,9V43a4,4,0,0,0,4,4H29a4,4,0,0,0,4-4V23Z\"/></g></svg>"}});})();