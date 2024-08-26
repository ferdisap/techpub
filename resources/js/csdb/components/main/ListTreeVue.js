import { isProxy, toRaw } from 'vue';
import { isArray } from '../../helper';
import RoutesWeb from '../../RoutesWeb';
import axios from 'axios';
/**
  * require array_move and sorter from helper.js
  * @param {string} type 'dml','csl', 'brex', 'brdp'
  * @param {Object} params 
 */
async function get_list(type, params = {}) {
  params.listtree = 1;

  const worker = this.createWorker("WorkerListTree.js");
  if (worker) {
    // let route = this.techpubStore.getWebRoute(`api.get_${type}_list`, params); // $type = allobjects, jadi routeName = 'api.get_allobjects_list'
    let route = RoutesWeb.get(`api.get_${type}_list`, params); // $type = allobjects, jadi routeName = 'api.get_allobjects_list'
    let prom = new Promise((resolve, reject) => {
      worker.onmessage = (e) => {
        this.data[`${type}_list`] = e.data[0];
        this.data[`${type}_list_level`] = e.data[1];
        worker.terminate();
        this.showLoadingProgress = false;
        return resolve(true);
      };
    });
    worker.postMessage({
      mode: 'fetchData',
      data: {
        route: route,
      },
    });
    this.showLoadingProgress = true;
    return prom;
  } 
  // else {
  //   let response = await axios({
  //     route: {
  //       name: `api.get_${type}_list`,
  //       data: params
  //     }
  //   })
  //   if (response.statusText === 'OK') {
  //     let data = setListTreeData(response.data.csdbs);
  //     this.data[`${type}_list`] = data[0];
  //     this.data[`${type}_list_level`] = data[1];
  //     return Promise.resolve(true);
  //   }
  //   return Promise.resolve(false);
  // }
}

async function goto(type, page = undefined) {
  this.get_list(type, { page: page });
}

/**
 * data itu isinya path doang, lihat di WorkerListTree.js
 */
function clickFolder(data) {
  if(!this.CB.selectionMode){
    this.emitter.emit('clickFolderFromListTree', data);
  }
}

/**
 * data itu isinya path, filename, viewType
 */
function clickFilename(data) {
  if(!this.CB.selectionMode){
    const config = {
      name: this.$props.routeName,
      params: {
        filename: data.filename,
        viewType: data.viewType
      },
      query: this.$route.query
    };
    window.cg = config;
    this.$router.push(config);
    this.emitter.emit('clickFilenameFromListTree', data); // key path dan filename
  }
}

/**
 * di workernya belum mendukung untuk html karena memang saya belum membuat XSL/ IETM untuk object nya
 */
function createListTreeHTML() {
  let hrefForPdf = this.techpubStore.getWebRoute('', { filename: ':filename', viewType: 'pdf' }, Object.assign({}, this.$router.getRoutes().find(r => r.name === this.$props.routeName)))['path'];
  let hrefForHtml = hrefForPdf.replace('pdf', 'html');
  let hrefForOther = hrefForPdf.replace('pdf', 'other');
  const worker = this.createWorker("WorkerListTree.js");
  if (worker) {
    worker.onmessage = (e) => {
      this.html = e.data
      worker.terminate();
    };
    worker.postMessage({
      mode: 'createHTMLString',
      data: {
        start_l: 1,
        list_level: isProxy(this.data[`${this.$props.type}_list_level`]) ? toRaw(this.data[`${this.$props.type}_list_level`]) : this.data[`${this.$props.type}_list_level`],
        list: isProxy(this.data[`${this.$props.type}_list`]) ? toRaw(this.data[`${this.$props.type}_list`]) : this.data[`${this.$props.type}_list`],
        open: isProxy(this.data.open) ? toRaw(this.data.open) : this.data.open,
        hrefForPdf: hrefForPdf,
        hrefForHtml: hrefForHtml,
        hrefForOther: hrefForOther,
      }
    });
  }
}

/*
 * akan mendelete jika newModel tidak ada
 * @returns {Object} csdbs 
 */
function deleteList(filename) {
  let csdb;
  Object.entries(this.data[`${this.$props.type}_list`]).find(arr => {
    let find = arr[1].find(v => v.filename === filename);
    if (find) {
      let index = arr[1].indexOf(find);
      this.data[`${this.$props.type}_list`][arr[0]].splice(index, 1);
    }
    return csdb = find;    
  }); // ini akan mereturn Array: index#0 path, index#1 Array berisi csdb object
  return csdb;
}

function pushList(model) {
  let path = model.path;
  this.data[`${this.$props.type}_list`][path] = this.data[`${this.$props.type}_list`][path] ?? [];
  this.data[`${this.$props.type}_list`][path].push(model);

  let split = model.path.split("/");
  let level = split.length;
  let p = [];
  for (let i = 1; i <= level; i++) {
    p.push(split[i - 1]);
    this.data[`${this.$props.type}_list_level`][i] = this.data[`${this.$props.type}_list_level`][i] ?? [];
    this.data[`${this.$props.type}_list_level`][i].push(p.join("/"));
  }
  let foundLevel = Object.entries(this.data[`${this.$props.type}_list_level`]).find(arr => arr[0] == level); // output ['level', Array containing path];;
  if (foundLevel && !(this.data[`${this.$props.type}_list_level`][foundLevel[0]].find(v => v === model.path))) { // agar tidak terduplikasi path nya
    this.data[`${this.$props.type}_list_level`][foundLevel[0]].push(model.path)
  }
}


/**
 * digunakan untuk emit.on('ListTree-refresh')
 */
function refresh(data) {
  //data adalah model SQL Csdb Object atau array contain csdb object (bukan meta objek nya)
  if(isArray(data)){
    data.forEach((obj) => {
      this.deleteList(obj.filename)
      this.pushList(obj);
    });
  } else{
    this.deleteList(data.filename)
    this.pushList(data);
  }
  this.createListTreeHTML();
}

/**
 * digunakan untuk emit.on('ListTree-refresh')
 */
function remove(data){
  //data adalah model SQL Csdb Object
  if(isArray(data)){
    data.forEach((d) => this.deleteList(d.filename));
  } else this.deleteList(data.filename);
  this.createListTreeHTML();
}


async function deleteObject() {
  const filenames = this.CB.value();
  const response = await axios({
    route: {
      name: 'api.delete_objects',
      data: {filename: filenames}
    }
  });
  if (!(response.statusText === 'OK')) throw new Error(`Failed to delete ${join(", ", filenames)}`);
  this.CB.cancel();

  // hapus list di folder, tidak seperti listtree yang ada level dan list model, dan emit csdbDelete
  const csdbDeleted = [];
  filenames.forEach((filename) => {
    let csdb = this.deleteList(filename);
    if(csdb) csdbDeleted.push(csdb); // aman walau pakai csdb ada dalam proxy
    else csdbDeleted.push({filename: filename, path: ''}); // path TBD. karena CB.value() hanya mengembalikan value saja
  });
  // emit
  this.emitter.emit('DeleteCSDBObjectFromListTree', csdbDeleted);

  // remove in list tree
  this.CB.latestCbRoomQueried.forEach(cbRoom => {
    cbRoom.remove();
  });
  this.CB.remove_latestCbRoomQueried();
}

export { get_list, goto, clickFolder, clickFilename, createListTreeHTML, deleteList, deleteObject, pushList,
   refresh, remove}