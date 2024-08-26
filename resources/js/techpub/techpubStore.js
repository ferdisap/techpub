// per 18 AUG 2024, sepertinya techpubStore yang dipakai adalah untuk Auth dan Error message, currentObjectModel, showLoadingBar, date() saja

/**, csdb3
 * WebRoutes: {
    "passw,

/**, csdb3
 * WebRoutes: {
    "passw
    ord.reset": {
        "name": "password.reset",
        "method": [
            "GET",
            "HEAD"
        ],
        "path": "/reset-password/:token",
        "params": {
            "token": ":token"
        }
    },
  }

  Auth: {
    name: '',
    email: ''
  },

  Errors: [ {name: ['text1']}, ... ]
 */

import { defineStore } from 'pinia';

export const useTechpubStore = defineStore('useTechpubStore', {
  state: () => {
    return {
      Auth: {},

      /**
       * DEPRECATED
       * diganti dengan RoutesWeb.js
       */
      WebRoutes: {},

      /**
       * DEPRECIATED
       */
      Project: [],

      /**
       * DEPRECIATED
       */
      Errors: [],

      showLoadingBar: false,

      /**
       * DEPRECIATED
       */
      showIdentSection: true,

      /**
       * DEPRECIATED
       */
      isOpenICNDetailContainer: false,

      /**
       * DEPRECIATED
       * awalnya digunakan untuk mengirim data dari ObjectDetail to ObjectUpdate.
       * Selanjutnya diharapkan bisa dipakai untuk menggantikan transformedObject
       */
      currentDetailObject: {
        model: undefined,
        blob: undefined, // transformed csdb object is blob
        // filename: undefined, // filename harusnya tidak perlu karena ada didalam
        projectName: undefined,
        // transformed: undefined, // harusnya tidak diperlukan karena sudah ada blob. Agar menghemat memory
      }, // blob object

      /**
       * DEPRECIATED
       * untuk DML app, csdb3
       * kayaknya ini tidak dipakai karena nanti pakai DMLList di IndexDML.vue
       */
      DMLList: [], // tidak  digunakan lagi

      /**
       * DEPRECIATED
       * untuk csdb3
       */
      BREXList: [], // tidak  digunakan lagi
      BRDPList: [], // tidak  digunakan lagi
      // BRList:[],

      /**
       * DEPRECIATED
       * untuk csdb3
       */
      OBJECTList: {},

      /**
       * DEPRECIATED
       * digunakan saat Upload.vue ke Editor.vue
       */
      readText: '',

      /** 
       * DEPRECIATED
       * pengganti fitur di App.vue 
       */
      isSuccess: true,
      
      /**
       * DEPRECIATED
       */
      errors: undefined,

      /**
       * DEPRECIATED
       */
      message: undefined,

      /**
       * digunakan untuk setiap component yang ada route.param filename nya
       */
      currentObjectModel: {},


    }
  },
  actions: {
    // /**
    //  * 
    //  * @param {string} type 'dml','csl', 'brex', 'brdp'
    //  * @param {*} params 
    //  * dipindah ke ListTree.vue
    //  */
    // async get_list(type, params = {}) {
    //   console.log(type);
    //   params.filenameSearch = this[`${type}_filenameSearch`] ?? '';
    //   let response = await axios({
    //     route: {
    //       name: `api.get_${type}_list`,
    //       data: params
    //     }
    //   })
    //   if (response.statusText === 'OK') {
    //     // this[`${type}_list`] = response.data;
    //     // this[`${type}_page`] = response.data.current_page;

    //     // ini jika ingin pakai nested path. Tapi servernya jangan di paginate. Jika di paginate, ganti 'response.data' menjadi 'response.data.data'
    //     // window.allobj = response.data.data;
    //     response.data.data = Object.assign({}, response.data.data); // entah Array atau object, akan menjadi Object;
    //     const sorter = function (container, asc = 1) {
    //       let arr = Object.keys(container).sort(); // ascending;
    //       if (!asc) {
    //         arr = arr.sort(() => -1); // descending
    //       }
    //       arr = arr.sort((a, b) => {
    //         if (b.substring(0, 2) === '__') {
    //           return asc ? (b > a ? -1 : 1) : (b > a ? 1 : -1);
    //         } else {
    //           return asc ? (b > a ? 1 : -1) : (b > a ? -1 : 1)
    //         }
    //       });
    //       arr.forEach((e, i) => {
    //         if (e.substring(0, 2) === '__') {
    //           arr = array_move(arr, i, 0);
    //         }
    //       });
    //       return arr.reduce((obj, key) => {
    //         obj[key] = container[key];
    //         return obj;
    //       }, {});
    //     };

    //     /**
    //      * Untuk memindahkan index elemen array. Ini bisa dipakai oleh fungsi lain.
    //      */
    //     const array_move = function (arr, old_index, new_index) {
    //       if (new_index >= arr.length) {
    //         var k = new_index - arr.length + 1;
    //         while (k--) {
    //           arr.push(undefined);
    //         }
    //       }
    //       arr.splice(new_index, 0, arr.splice(old_index, 1)[0]);
    //       return arr;
    //     };

    //     const append = function (container, path, csdbObject, callback) {
    //       let exploded_path = path.split("/");
    //       let loop = 0;
    //       let maxloop = exploded_path.length;
    //       let folder = "__" + exploded_path[loop];
    //       while (loop < maxloop) {
    //         container[folder] = container[folder] || {};
    //         if (exploded_path[loop + 1]) {
    //           exploded_path = exploded_path.slice(1);
    //           let newpath = exploded_path.join("/");
    //           container[folder] = callback(container[folder], newpath, csdbObject, callback);
    //           return container;
    //         }
    //         loop++;
    //       }
    //       let containerLength = Object.keys(container[folder]).filter(e => parseInt(e.slice(0, -1)) || parseInt(e.slice(0, -1)) === 0 ? true : false).length;
    //       container[folder][containerLength + "_"] = csdbObject;
    //       container = sorter(container,0);
    //       return container;
    //     }
    //     let newobj = {};
    //     for (const i in response.data.data) {
    //       let obj = response.data.data[i];
    //       newobj = append(newobj, obj['path'], obj, append);
    //       delete response.data.data[i];
    //     }
    //     this[`${type}_list`] = newobj;
    //     // window.sorter = sorter;
    //     // window.append = append;
    //     // console.log(window.newobj = newobj);

    //     // console.log(window.rsp = response.data.data);
    //     // const append = function (arr, path, csdbObject, callback) {
    //     //   arr = Object.assign({},arr);
    //     //   let exploded_path = path.split("/");
    //     //   let loop = 0;
    //     //   let maxloop = exploded_path.length;
    //     //   let folder = "__" + exploded_path[loop];
    //     //   while (loop < maxloop) {
    //     //     arr[folder] = arr[folder] || [];
    //     //     if (exploded_path[loop + 1]) {
    //     //       exploded_path = exploded_path.slice(1);
    //     //       let newpath = exploded_path.join("/");
    //     //       arr[folder] = callback(arr[folder], newpath, csdbObject, callback);
    //     //       return arr;
    //     //       break;
    //     //     }
    //     //     loop++;
    //     //   }
    //     //   arr[folder] = Object.assign([], arr[folder]);
    //     //   arr[folder].push(csdbObject);
    //     //   arr[folder] = Object.assign({},arr[folder]);
    //     //   return arr;
    //     // }
    //     // response.data.data.forEach((obj, k) => {
    //     //   response.data.data = append(response.data.data, obj['path'], obj, append);
    //     //   delete response.data.data[k];
    //     // })
    //     // response.data.data = [Object.assign({}, response.data.data)];
    //     // this[`${type}_list`] = response.data.data;
    //     // console.log(window.data = response.data.data);


    //     // untuk dump di console.log
    //     // obj1 = {filename: 'foo1', path: 'csdb'};
    //     // obj11 = {filename: 'foo11', path: 'csdb/n219'};
    //     // obj12 = {filename: 'foo12', path: 'csdb/n219'};
    //     // obj111 = {filename: 'foo11', path: 'csdb/n219/amm'};
    //     // allobj = [obj1, obj11, obj12, obj111];

    //     // append = function(arr, path, csdbObject, callback){
    //     //     let exploded_path = path.split("/");
    //     //     let loop = 0;
    //     //     let maxloop = exploded_path.length;
    //     //     let folder = "__" + exploded_path[loop];
    //     //     while (loop < maxloop) {
    //     //         arr[folder] = arr[folder] || [];
    //     //         if(exploded_path[loop+1]){
    //     //             exploded_path = exploded_path.slice(1);
    //     //             let newpath = exploded_path.join("/");
    //     //             arr[folder] = callback(arr[folder], newpath, csdbObject, callback);
    //     //             return arr;
    //     //             break;
    //     //         }
    //     //         loop++;
    //     //     }
    //     //     arr[folder].push(csdbObject);
    //     //     return arr;
    //     // }
    //     // allobj.forEach((obj, k) => {
    //     //     allobj = append(allobj, obj['path'], obj, append);
    //     //     delete allobj[k];
    //     // });
    //     // allobj = Object.assign({},allobj)



    //     // window.objs = response.data.data;
    //     // let objs = response.data.data;
    //     // let newobjs = {};
    //     // for(const i in objs.data){
    //     //   let obj = objs.data[i];
    //     //   if(!newobjs[obj.path]){
    //     //    newobjs[obj.path] = [];
    //     //   }
    //     //   newobjs[obj.path].push(obj);
    //     // }
    //     // this[`${type}_list`] = newobjs;
    //     // return this[`${type}_list`];
    //   }
    // },

    /**
     * DEPRECIATED
     */
    async goto(type, page = undefined) {
      this.get_list(type, { page: page });
    },

    /**
     * DEPRECIATED
     */
    isEmpty(value) {
      return (value == null || value === '' || (typeof value === "string" && value.trim().length === 0));
    },

    date(str) {
      return (new Date(str)).toLocaleDateString('en-EN', {
        year: 'numeric', month: 'short', day: 'numeric'
      });
    },

    /**
     * DEPRECATED
     * diganti dengan RouteWeb.js @get()
     * @param {*} name 
     * @param {*} params 
     * @param {*} Plain Object which get same from this.WebRoute[`${name}`]
     * @returns {Object} if route method is get then all params has attached to URL, otherwise is attached in route.params 
     * returned Object will have params (plain Object) if the method is POST;
     */
    getWebRoute(name, data, route = undefined) {
      route ? (route.params = data) : route = Object.assign({}, this.WebRoutes[name]); // paramsnya sudah ada, tapi tidak ada valuenya. eg: 'filename': ':filename'
      for (const p in route.params) {
        let par = data[p];
        if (par === undefined|null) {
          if(data instanceof FormData && data.get(p)){
            par = data.get(p);
          } else {
            throw new Error(`Parameter '${p}' shall be exist.`);
          }
        };
        route.path = route.path ? route.path.replace(new RegExp(`:${p}\\??`), par) : (() => {throw new Error('Route must have its path.')})();
        delete data[p]; // ini diperlukan agar params tidak dijadikan query data
      }
      route.path = route.path.replace(/(:\/\/)|(\/)+/g, "$1$2"); // untuk menghilangkan multiple slash
      if (!route.method || route.method.includes('GET')) {
        let url = new URL(window.location.origin + route.path);
        url.search = new URLSearchParams(data);
        route.url = url.toString();
        route.params = Object.assign({}, route.params); // supaya tidak ada Proxy
      }
      else if (route.method.includes('POST')) {
        route.params = data;
        route.url = new URL(window.location.origin + route.path).toString();
      }
      route.method = Object.assign({}, route.method); // supaya tidak ada Proxy, sehingga worker bisa pakainya
      return route;
    },
    // getWebRoute(name, params, route = undefined) {
    //   // mapping FormData. Jika value berupa array, maka dijoin pakai comma
    //   let fd;
    //   if (params instanceof FormData) {
    //     fd = params;
    //     let a = {};
    //     for (const [k, v] of params) {
    //       a[k] = a[k] || '';
    //       a[k] += ',' + v;
    //       if (a[k][0] === ',') {
    //         a[k] = a[k].slice(1);
    //       }
    //     }
    //     params = a;
    //   }

    //   // jika route ada (diambil dari WebRoute biasanya, yang paramnya masih ':value') maka paramnya terisi dengan arguments param;
    //   route ? (route.params = params) : route = Object.assign({}, this.WebRoutes[name]); // paramsnya sudah ada, tapi tidak ada valuenya. eg: 'filename': ':filename'
    //   for (const p in route.params) {
    //     if (!params[p]) throw new Error(`Parameter '${p}' shall be exist.`);
    //     route.path = route.path ? route.path.replace(`:${p}`, params[p]) : (() => {throw new Error('Route must have its path.')})();
    //     delete params[p]; // ini diperlukan agar params tidak dijadikan query data
    //   }
    //   if (!route.method || route.method.includes('GET')) {
    //     let url = new URL(window.location.origin + route.path);
    //     url.search = new URLSearchParams(params);
    //     route.url = url.toString();
    //     route.params = Object.assign({}, route.params); // supaya tidak ada Proxy
    //   }
    //   else if (route.method.includes('POST')) {
    //     // route.params = params; // kalau pakai ini, entity (file upload) terbaca sebagai text "[Object File]", bukan binary
    //     route.params = fd; // kalau pakai ini, payload text tidak akan di terbaca di inspector.
    //     route.url = new URL(window.location.origin + route.path).toString();
    //   }
    //   route.method = Object.assign({}, route.method); // supaya tidak ada Proxy, sehingga worker bisa pakainya
    //   return route;
    // },

    /**
     * @param {string} projectName 
     * @param {Object} data 
     */
    // setObjects(projectName, data) {
    //   this.Project.find(v => v.name = projectName).objects = data;
    // },

    /**
     * get Project
     * @param {string} projectName 
     * @returns object
     */
    // project(projectName) {
    //   return this.Project.find(v => v.name == projectName);
    // },

    /**
     * get objects from project
     */
    // object(projectName, filename) {
    //   let pr = this.project(projectName);
    //   if (!pr) {
    //     return false;
    //   }
    //   else if (!pr.objects) {
    //     return false;
    //   }
    //   else {
    //     return pr.objects.find(v => v.filename == filename);
    //   }
    // },

    /**
     * sort object
     */
    // sortObjects(projectName, key, ascending = true) {
    //   let objects = this.project(projectName).objects;

    //   // karena objects[i]['initiator'] = {name: '...', email: '...'}
    //   if ((key == 'name')) {
    //     let sorted_array = Object.entries(objects).sort((a, b) => {
    //       const sortA = a[1][key]['name'].toUpperCase();
    //       const sortB = b[1][key]['name'].toUpperCase();
    //       if (ascending) {
    //         return sortA < sortB ? 1 : (sortA > sortB ? -1 : 0);
    //       } else {
    //         return sortA < sortB ? -1 : (sortA > sortB ? 1 : 0);
    //       }
    //     });
    //     for (let i = 0; i < sorted_array.length; i++) {
    //       this.project(projectName).objects[i] = sorted_array[i][1];
    //     }
    //   }
    //   else if ((key == 'title')) {
    //     let sorted_array = Object.entries(objects).sort((a, b) => {
    //       const sortA = a[1]['remarks'][key].toUpperCase();
    //       const sortB = b[1]['remarks'][key].toUpperCase();
    //       if (ascending) {
    //         return sortA < sortB ? 1 : (sortA > sortB ? -1 : 0);
    //       } else {
    //         return sortA < sortB ? -1 : (sortA > sortB ? 1 : 0);
    //       }
    //     });
    //     for (let i = 0; i < sorted_array.length; i++) {
    //       this.project(projectName).objects[i] = sorted_array[i][1];
    //     }
    //   }
    //   else {
    //     let sorted_array = Object.entries(objects).sort((a, b) => {
    //       if (!(a[1][key] && b[1][key])) {
    //         return 0;
    //       }
    //       const sortA = a[1][key].toUpperCase();
    //       const sortB = b[1][key].toUpperCase();
    //       if (ascending) {
    //         return sortA < sortB ? 1 : (sortA > sortB ? -1 : 0);
    //       } else {
    //         return sortA < sortB ? -1 : (sortA > sortB ? 1 : 0);
    //       }
    //     });
    //     for (let i = 0; i < sorted_array.length; i++) {
    //       this.project(projectName).objects[i] = sorted_array[i][1];
    //     }
    //   }
    // },

    /**
     * untuk form input name
     * @param {string} name 
     * @returns array contains error text
     */
    error(name) {
      let err = [];
      for (const [k, name] of Object.entries(arguments)) {
        let e = this.Errors.find(o => o[name]);
        if (e) {
          err = err.concat(e[name]);
        }
      }
      return err;
      // let err = this.Errors.find(o => o[name]); // return array karena disetiap hasil validasi error [{name: ['text1']},...]
      // if (err) {
      //   return err[name];  // return array ['text1', 'text2', ...] 
      // }
    },

    /** 
     * DEPRECIATED
     * pengganti fitur pada App.vue 
     * */
    async set_error(axiosError) {
      axiosError.response.data.errors ? (this.errors = axiosError.response.data.errors) : (this.errors = undefined);
      axiosError.response.data.message ? (this.message = axiosError.message + ': ' + axiosError.response.data.message) : (this.message = axiosError.message);
      this.isSuccess = false;
      this.showLoadingBar = false;
    },

    /**
     * DEPRECATED
     * tidak diperukan lagi. Cek di Flash.vue
     */
    set_success(response, isSuccess = true) {
      this.errors = undefined;
      this.isSuccess = true;
      this.message = response.data.message ? response.data.message : '';
      this.showLoadingBar = false;
    },

    /**
     * DEPRECAED
     * tidak diperlukan karena bisa get langsung
     */
    getCurrentObjectModel(){
      return this.currentDetailObject;
    },

    /**
     * DEPRECATED, diganti getCurrentObjectModel
     * @param {*} output 
     * @param {*} object 
     * @returns 
     */
    // return src:blob untuk ICN atau blob.text() jika xml
    // return Promise
    async getCurrentDetailObject(output = '', object = {}) {
      const blob = object.blob ?? this.currentDetailObject.blob;
      if (!blob) {
        return ['', ''];
      }
      window.blob = blob;
      if (output == 'srcblob') {
        const url = URL.createObjectURL(blob);
        return [output, url.toString()];
      }
      else if (output == 'text') {
        return [output, await blob.text()];
      }
      else if (output == '') {
        if (blob.type.includes('text') || blob.type.includes('xml')) {
          return [blob.type, await blob.text()];
        } else {
          const url = URL.createObjectURL(blob);
          return [blob.type, url.toString()];
        }
      }
      // return (async () => {
      // })();
    },

    /**
     * DEPRECIATED
     */
    async setCurrentDetailObject_blob() {
      this.showLoadingBar = true;
      const route = this.getWebRoute('api.get_transform_csdb', { project_name: this.currentDetailObject['projectName'], filename: this.currentDetailObject.model['filename'] });
      await axios({
        url: route.url,
        method: route[0],
        data: route.params,
        responseType: 'blob',
      })
        .then(async (response) => {
          this.currentDetailObject.blob = response.data;
        });
    },

    /**
     * DEPRECIATED
     * object = {model: ..., blob: ...}
     * */
    async setCurrentObject_model(object, projectName, filename) {
      if (!this.currentDetailObject.model) {
        this.showLoadingBar = true;
        const route = this.getWebRoute('api.get_csdb_object_data', { project_name: projectName, filename: filename });
        await axios({
          url: route.url,
          method: route[0],
          data: route.params,
        })
          .then(response => {
            this.currentDetailObject.model = response.data[0];
          })
          .catch(error => this.$root.error(error));
        this.currentDetailObject['projectName'] = projectName;
      } else {
        this.currentDetailObject.model = object ? object : this.currentDetailObject.model;
      }
      this.showLoadingBar = false;
    },

    /**
     * DEPRECIATED
     * get one dml from DMLList
     * @param {string} filename 
     */
    dml(filename) {
      if (filename) {
        return this.DMLList.find(v => v.filename == filename);
      }
    }
  }


})
