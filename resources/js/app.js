require('./bootstrap');
import { createApp } from 'vue'
import App from './app.vue'
import router from './plugins/route.js'
import TransModel from './shared/filters/trans-model';
import i18n from './plugins/i18n/i18n';
import store from './shared/store';
import interceptor from "./shared/interceptor";
import Toaster from "@meforma/vue-toaster";
import VueSweetalert2 from 'vue-sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

const app = createApp(App);
app.use(VueSweetalert2);
app.use(i18n);
app.use(router);
app.use(Toaster);
app.config.globalProperties.$filters = TransModel;
app.provide("store", store);
app.provide('toast', app.config.globalProperties.$toast);
app.provide('swal', app.config.globalProperties.$swal);
interceptor();
app.mount('#app');