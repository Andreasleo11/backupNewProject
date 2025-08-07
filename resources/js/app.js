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
// Register all necessary components
Chart.register(...registerables);

window.Chart = Chart;
window.TomSelect = TomSelect;
window.Fancybox = Fancybox;
window.SignaturePad = SignaturePad;
window.flatpickr = flatpickr;
