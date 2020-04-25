import SmsView from "./components/SmsView.vue";
import ScheduleView from "./components/ScheduleView";

panel.plugin("schnitzerund/sms", {
  views: {
    schedule: {
      component: ScheduleView,
      icon: "mailjet-schedule",
      label: "Mailjet Schedule",
    },
    sms: {
      component: SmsView,
      icon: "mailjet-sms",
      label: "Mailjet SMS",
    },
  },
  icons: {
    "mailjet-schedule":
      '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"><g class="nc-icon-wrapper"><path data-color="color-2" d="M25,18a7,7,0,1,0,7,7A7,7,0,0,0,25,18Zm4,8H24V21h2v3h3Z"/> <path d="M16,29H4a1,1,0,0,1-1-1V10A1,1,0,0,1,4,9H28a1,1,0,0,1,1,1v6h2V6a2,2,0,0,0-2-2H24V1a1,1,0,0,0-2,0V4H10V1A1,1,0,0,0,8,1V4H3A2,2,0,0,0,1,6V29a2,2,0,0,0,2,2H16Z"/></g></svg>',
    "mailjet-sms":
      '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48"><g class="nc-icon-wrapper"><path d="M43,1H23a4,4,0,0,0-4,4V25a1,1,0,0,0,1,1,.988.988,0,0,0,.581-.187L27.32,21H43a4,4,0,0,0,4-4V5A4,4,0,0,0,43,1ZM32,15H26a1,1,0,0,1,0-2h6a1,1,0,0,1,0,2Zm8-6H26a1,1,0,0,1,0-2H40a1,1,0,0,1,0,2Z" data-color="color-2"/><path d="M31,23V39a1,1,0,0,1-1,1H7a1,1,0,0,1-1-1V11a1,1,0,0,1,1-1H17V5H8A4,4,0,0,0,4,9V43a4,4,0,0,0,4,4H29a4,4,0,0,0,4-4V23Z"/></g></svg>',
  },
});
