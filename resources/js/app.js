import "./bootstrap";
import "laravel-datatables-vite";
import "/node_modules/select2/dist/css/select2.css";
import "/node_modules/bootstrap-datepicker/dist/css/bootstrap-datepicker.css";
import "/node_modules/intro.js/minified/introjs.min.css";
import "/node_modules/tom-select/dist/css/tom-select.bootstrap5.min.css";
import TomSelect from "tom-select";
import { Fancybox } from "@fancyapps/ui";
import "@fancyapps/ui/dist/fancybox/fancybox.css";
import SignaturePad from "signature_pad";
import { Chart, registerables } from "chart.js";
import "/node_modules/flatpickr/dist/flatpickr.css";
import flatpickr from "flatpickr";
import Echo from "laravel-echo";
import Pusher from "pusher-js";

Chart.register(...registerables);

window.Chart = Chart;
window.TomSelect = TomSelect;
window.Fancybox = Fancybox;
window.SignaturePad = SignaturePad;
window.flatpickr = flatpickr;
window.Pusher = Pusher;

window.Echo = new Echo({
  broadcaster: "pusher",
  key: import.meta.env.VITE_PUSHER_APP_KEY,
  cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? "mt1",
  wsHost:
    import.meta.env.VITE_PUSHER_HOST ?? `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
  wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
  wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
  forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? "https") === "https",
  enabledTransports: ["ws", "wss"],
  disabledStats: true,
});
// Pusher.logToConsole = true; // dev only
// if (window.Laravel?.userId) {
//   window.Echo.private(`App.Models.User.${window.Laravel.userId}`).notification((notification) => {
//     console.log("[PUSH] BroadcastNotificationCreated @", new Date().toISOString(), notification);
//     // Update UI:
//     // Livewire.dispatch("refreshNotifications").to("notifications.menu");
//   });
// }

// Custom Private Channel
// if (window.Laravel?.userId) {
//   const ch = window.Echo.private(`users.${window.Laravel.userId}`);

//   // helpful debug
//   ch.subscribed(() => console.log("✅ Subscribed to users." + window.Laravel.userId));
//   ch.error((e) => console.error("❌ Channel error", e));

//   // listen to our custom event name
//   ch.listen(".notification.pushed", (payload) => {
//     console.log("[PUSH] notification.pushed received @", new Date().toISOString(), payload);
//     // Livewire.dispatch('refreshNotifications').to('notifications.menu');
//   });
// }
