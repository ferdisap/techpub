import { findAncestor, isNumber, isEmpty, array_unique, isString, formDataToObject } from '../../helper';
// import { isProxy, toRaw } from "vue";
import $ from 'jquery';
import fileDownload from 'js-file-download';
import { isArray } from '../../helper';
import axios from 'axios';

function getObjs(data = {}) {
  if (!data.sc) data.sc = '';
  if (data.path) {
    if (data.sc.search(/path::\S+\s/) < 0) data.sc += " path::" + data.path; // nambah ' path::...' ke dalam sc
    else data.sc = data.sc.replace(/path::\S+\s/, "path::" + data.path + " "); // mengganti 'path::... ' dengan path data.path
    delete data.path;
  }
  const routeName = data.routeName ?? 'api.requestbyfolder.get_allobject_list';
  delete data.routeName;
  axios({
    route: {
      name: routeName,
      data: data// akan receive data: [model1, model2, ...]
    }, useComponentLoadingProgress: this.componentId,
  })
  .then(response => {
    this.storingResponse(response);
  })
}

function storingResponse(response) { 
  if (response.statusText === 'OK' || (response.status >= 200 && response.status < 300)) {
    this.data.csdb = response.data.csdbs; // array contain object csdb
    this.data.folders = response.data.folders; // array contain string path
    // this.data.current_path = response.data.current_path ?? this.$props.dataProps.path;
    this.data.current_path = response.data.current_path ? response.data.current_path : this.$props.dataProps.path;
    delete response.data.data;
    delete response.data.folder;
    delete response.data.message;
    delete response.data.current_path;
    this.data.paginationInfo = response.data;
  }
}

async function goto(url, page) {
  if (page) {
    url = new URL(this.pagination['path']);
    url.searchParams.set('page', page)
  }
  if (url) {
    let response = await axios.get(url);
    if (response.statusText === 'OK') {
      this.storingResponse(response);
    }
  }
}

async function back(path = undefined) {
  if (!this.selectionMode) {
    if (!path) {
      path = this.currentPath.replace(/\/\w+\/?$/, "");
    }
    this.getObjs({ path: path });
    this.data.current_path = path;
  }
}

function clickFolder(event, path) {
  // window.path = path; window.getObjs = this.getObjs;
  if (!this.CB.selectionMode) this.back(path);
}

async function clickFilename(event, filename) {
  if (!this.CB.selectionMode) {
    this.techpubStore.currentObjectModel = Object.values(this.data.csdb).find((obj) => obj.filename === filename);
    await this.$router.push({
      name: 'Explorer',
      params: {
        filename: filename,
        viewType: 'pdf'
      },
      query: this.$route.query
    })
    this.emitter.emit('clickFilenameFromFolder', { filename: filename }) // key filename saja karena bisa diambil dari techpubstore atau server jika perlu
  }
}

function sortTable(event) {
  const getCellValue = function (row, index) {
    return $(row).children('td').eq(index).text();
  };
  const comparer = function (index) {
    return function (a, b) {
      let valA = getCellValue(a, index), valB = getCellValue(b, index);
      // return $.isNumeric(valA) && $.isNumeric(valB) ? valA - valB : valA.toString().localeCompare(valB);
      return isNumber(valA) && isNumber(valB) ? valA - valB : valA.toString().localeCompare(valB);
    }
  };
  let table = $(event.target).parents('table').eq(0);
  let th = $(event.target).parents('th').eq(0);
  if (th.index() === 0) {
    let filerows = table.find('.file-row').toArray().sort(comparer(th.index()));
    let folderrows = table.find('.folder-row').toArray().sort(comparer(th.index()));
    this.asc = !this.asc;
    if (!this.asc) {
      filerows = filerows.reverse();
      folderrows = folderrows.reverse();
    }
    for (let i = 0; i < folderrows.length; i++) {
      table.append(folderrows[i]);
    }
    for (let i = 0; i < filerows.length; i++) {
      table.append(filerows[i]);
    }
  } else {
    let filerows = table.find('.file-row').toArray().sort(comparer(th.index()));
    this.asc = !this.asc;
    if (!this.asc) {
      filerows = filerows.reverse();
    }
    for (let i = 0; i < filerows.length; i++) {
      table.append(filerows[i]);
    }
  }
}

function search() {
  this.getObjs({ sc: this.sc });
}

function removeList(filename) {
  let csdb = this.data.csdb.find((obj) => obj.filename === filename);
  let index = this.data.csdb.indexOf(csdb);
  this.data.csdb.splice(index, 1);
  return csdb;
}

function pushFolder(path) {
  if (path.split("/").length > this.data.current_path.split("/").length) {
    this.data.folders.push(path);
    this.data.folders = array_unique(this.data.folders);
  }
}

async function dispatch(cond = 0) {
  const emitName = !cond ? 'dispatchTo' : 'AddDispatchTo';
  const csdbs = await this.CB.value();
  this.CB.cancel();
  if (!csdbs) return;
  this.emitter.emit(emitName, csdbs);
}

/**
 * hanya untuk membirukan background table row
*/
// function select(event){
//   let el;
//   if(this.CbSelector.selectionMode) {
//     this.CbSelector.select();
//     el = document.getElementById(this.CbSelector.cbHovered);
//   } else el = event.target;
//   this.selectedRow ? this.selectedRow.classList.remove('bg-blue-300') : null;
//   this.selectedRow = findAncestor(el, 'tr');
//   this.selectedRow.classList.add('bg-blue-300');
// }

async function changePath(event) {
  // insert filenames to formdata
  const fd = new FormData(event.target)
  const filenames = await this.CB.value();
  fd.set('filename', filenames.join(","))

  let path = fd.get('path').toUpperCase();
  path = path.replace(/(\/{1,}$)|(\/{2,})/g, (m, p1, p2) => p2 ? '/' : ''); // p2 adalah multiple slash di tengah2 sentence, p1 adalah single or multiple slash di ujung. Penjelasan callback: jika di tengah sentence ketemu multiple slash (p1 exist) maka akan diganti '/'. Jika tidak makan diganti ''. Meskipun p2 captured, tetap diganti ''. eg: 'CSDB/AMM//CN235///' akan diganti 'CSDB/AMM/CN235'.
  if (path === this.data.current_path) return;
  else fd.set('path', path);

  // fetch
  const response = await axios({
    route: {
      name: 'api.change_object_path',
      data: fd
    }, useComponentLoadingProgress: this.componentId,
  });
  if (!(response.statusText === 'OK')) throw new Error(`Failed to change path ${join(", ", filenames)}`);
  this.CB.cancel();

  // handle changed csdb object
  const csdbChangedPath = [];
  filenames.forEach((filename) => {
    let csdb = this.removeList(filename);
    this.pushFolder(path);
    if(csdb){
      csdb.path = path;
      csdbChangedPath.push(csdb); // aman walau pakai csdb ada dalam proxy
    }
    else {
      csdbChangedPath.push({filename: filename, path: path});
    }

  });

  // emit
  this.emitter.emit('ChangePathCSDBObjectFromFolder', csdbChangedPath);
}

/**
 * saat ini, ketika menghapus sebuah folder, isinya kehapus, namun di frontend nya tidak. Karena saat ini hanya menghapus list object, bukan folder.
 * Kedepannya nanti ambil checkbox/current cbRoomId untuk folder saja. Dicheck apakah checkbox checked atau tidak. jika checked maka hapus folder nya
 */
async function deleteObject() {
  const filenames = await this.CB.value();
  // fetch
  const response = await axios({
    route: {
      name: 'api.delete_objects',
      data: {filename: filenames}
    }, useComponentLoadingProgress: this.componentId,
  });
  if (!(response.statusText === 'OK')) throw new Error(`Failed to delete ${join(", ", filenames)}`);
  this.CB.cancel();

  // hapus list di folder, tidak seperti listtree yang ada level dan list model, dan emit csdbDelete
  const csdbDeleted = [];
  filenames.forEach((filename) => {
    let csdb = this.removeList(filename);
    if(csdb) csdbDeleted.push(csdb); // aman walau pakai csdb ada dalam proxy
    else csdbDeleted.push({filename: filename, path: ''}); // path TBD. karena CB.value() hanya mengembalikan value saja
  });
  // emit
  this.emitter.emit('DeleteCSDBObjectFromFolder', csdbDeleted);
}

/**
 * untuk emitter.on('Folder-refresh)
 * @param {Object} data 
 */
function refresh(data) {
  // let to = 0;
  if(isArray(data)){
    data.forEach((obj) => {
      this.getObjs({ path: obj.path })
      // clearTimeout(to);
      // if (obj.path === this.data.current_path){
        // to = setTimeout(()=>{
        //   this.getObjs({ path: this.data.current_path })
        // },10);
      // }
    });
  } else{
    // if (data.path === this.data.current_path) this.getObjs({ path: this.data.current_path })
    if(data.path) this.getObjs({ path: data.path })
  }
}
function remove(data) {
  if(isArray(data)){
    data.forEach((obj) => {
      if (obj.path === this.data.current_path){
        this.removeList(obj.filename);
      }
    });
  } else{
    if (data.path === this.data.current_path) this.getObjs({ path: this.data.current_path })
  }
}

async function download() {
  let filenames = await this.CB.value();
  let response = await axios({
    route: {
      name: 'api.download_objects',
      data: { filename: filenames },
    }, useComponentLoadingProgress: this.componentId,
    responseType: 'blob',
  });
  if (response.statusText === 'OK') {
    const contentDisposition = response.headers.get('content-disposition');
    if(contentDisposition.substr(0,10) === 'attachment'){
      let i;
      if((i = contentDisposition.indexOf('filename')) > -1){
        fileDownload(
          response.data,
          contentDisposition.replace(/.+filename="?(\S+[^"])/g,(m,p1) => p1)
        ); // jika ada banyak maka nanti otomatis ke downloa, misal dari server contentype nya zip ya otomatis downoad zip file
      } else {
        const url = URL.createObjectURL(await response.data); // asumsikan content-type = text/xml
        const a = document.createElement('a');
        a.target = '_blank';
        a.href = url
        a.click();
        URL.revokeObjectURL(url); // memory management
      }
    }
    this.CB.cancel();
  }
}

export {
  getObjs, storingResponse, goto, back, clickFolder, clickFilename, download,
  sortTable, search, removeList, remove, pushFolder, dispatch, changePath, deleteObject, refresh
};