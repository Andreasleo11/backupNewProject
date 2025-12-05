// import './bootstrap';
import 'laravel-datatables-vite';
import '/node_modules/select2/dist/css/select2.css';
import '/node_modules/bootstrap-datepicker/dist/css/bootstrap-datepicker.css';
import '/node_modules/intro.js/minified/introjs.min.css';
import '/node_modules/tom-select/dist/css/tom-select.bootstrap5.min.css';
import TomSelect from 'tom-select';
import { Fancybox } from '@fancyapps/ui';
import '@fancyapps/ui/dist/fancybox/fancybox.css';
import SignaturePad from 'signature_pad';
import { Chart, registerables } from 'chart.js';
import '/node_modules/flatpickr/dist/flatpickr.css';
import flatpickr from 'flatpickr';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import $ from 'jquery';
import 'bootstrap-datepicker';
import select2 from 'select2';
import introJs from 'intro.js';

Chart.register(...registerables);

window.Chart = Chart;
window.TomSelect = TomSelect;
window.Fancybox = Fancybox;
window.SignaturePad = SignaturePad;
window.flatpickr = flatpickr;
window.Pusher = Pusher;
window.$ = $;
window.introJs = introJs;
select2();

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */
import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

window.Echo = new Echo({
  broadcaster: 'pusher',
  key: import.meta.env.VITE_PUSHER_APP_KEY,
  cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
  // wsHost:
  //   import.meta.env.VITE_PUSHER_HOST ?? `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
  // wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
  // wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
  // enabledTransports: ["ws", "wss"],
  forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
  disabledStats: true,
});

// Pusher.logToConsole = true;
