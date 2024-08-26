import { createApp } from 'vue';
import { createRouter, createWebHistory } from 'vue-router';
import App from "./App.vue";
import Routes from "./RoutesVue.js";
import RoutesWeb from './RoutesWeb';

import axios from 'axios';
import { createPinia } from 'pinia';
// import References from '../techpub/References';
import { useTechpubStore } from '../techpub/techpubStore';

import mitt from 'mitt';
// import routes from '../../others/routes.json';

import ContextMenu from './ContextMenu';
import Dropdown from './components/Dropdown';
import Modal from './Modal.js';
import TextEditorElement from './element/TextEditorElement';
// ####### start here

// dipakai untuk comment, remarks. Nanti dibuat lagi untuk xmlEditor
customElements.get('text-editor') || customElements.define('text-editor', TextEditorElement);

const createWorker = function (filename) {
  if (window.Worker && filename) {
    return new Worker(`/worker/${filename}`, { type: "module" });
  } else {
    return false;
  }
}


axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.headers.common['X-CSRF-Token'] = document.querySelector("meta[name='csrf-token']").content;
axios.defaults.withCredentials = true;
// window.axios = axios;

const csdb = createApp(App);
const router = createRouter({
  routes: Routes,
  history: createWebHistory(),
});
const pinia = createPinia();

csdb.use(pinia);
csdb.use(router);
// csdb.config.globalProperties.References = References;
csdb.config.globalProperties.emitter = mitt();
csdb.config.globalProperties.createWorker = createWorker; // ini sudah menjalankan fungsinya createWorker nya, aneh
csdb.config.globalProperties.ContextMenu = new ContextMenu();
csdb.config.globalProperties.Dropdown = new Dropdown();
csdb.config.globalProperties.Modal = new Modal();

// ga bisa npm build jika pakai await 
axios.get('/auth/check')
  .then(response => useTechpubStore().Auth = response.data)
  .catch(response => window.location.href = "/login");
// sebelum mounting app, akan request all Routes dulu
// useTechpubStore().WebRoutes = routes;

/**
 * cara menggunakan axios
 * masukan 'data' (plain object) pada fungsi axios();
 * the data contains 'route' (plain object). The route contains 'name' (string) and 'data' (plain object);
 * the data also contains 'event' (plain Object or event object). 
 * 
 * If the response code is success:
 * if the'event' contains 'name' (string) then it will be emitted an event named the 'event.name', else named the 'route.name'
 * The emitted event will pass the parameter which is event combined with 'route.data'
 */
axios.interceptors.request.use(
  async (config) => {
    let headers = {};
    if (config.route) {
      let route;
      try {
        route = RoutesWeb.get(config.route.name, config.route.data);
      } catch (error) {
        console.error(error);
        console.trace();
        return;
      }
      config.url = route.url;
      config.method = route.method[0];
      config.data = route.data;
    }
    for (const i in headers) {
      config.headers.set(i, headers[i]);
    }
    return config;
  },
);
axios.interceptors.response.use(
  (response) => {
    useTechpubStore().Errors = [];
    csdb.config.globalProperties.emitter.emit('flash', {
      type: response.data.infotype,
      message: response.data.message
    });
    return response;
  },
  (axiosError) => {
    // window.axiosError = axiosError; // jangan dihapus. Untuk dumping jika error pada user
    useTechpubStore().showLoadingBar = false;
    if (axiosError.code) {
      csdb.config.globalProperties.emitter.emit('flash', {
        type: axiosError.response.data.infotype,
        errors: axiosError.response.data.errors,
        message: `<i>${axiosError.message}</i>` + '<br/>' + axiosError.response.data.message
      });
    } else {
      console.error(axiosError.stack);
    }
    return axiosError.response;
  }
);

// window.csdb = csdb;
csdb.mount('#body');

// ####### end here



// delayer
// async function a(){
//   return new Promise(resolve => {
//     setTimeout(() => {
//       console.log('aaa');
//       resolve('respolved');
//     },2000);
//   });
// }
// await a();

// function createRandomString() {
//   return (Math.random() + 1).toString(36).substring(7);
// }
// axios.id = {};