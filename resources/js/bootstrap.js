// import 'bootstrap';

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */
import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import $ from 'jquery';
import select2 from 'select2';
window.$ = $;
select2();

import datepicker from 'bootstrap-datepicker';
import introJs from 'intro.js';
window.introJs = introJs;
