import {formDataToObject, isEmpty, isString, isArray, findAncestor} from '../../helper';
import axios from 'axios';

async function getObjs(data = {}) {
  data = Object.keys(data).forEach(key => data[key] === undefined && delete data[key]);
  let response = await axios({
    route: {
      name: 'api.get_deletion_list',
      data: data,
    }
  });
  if (response.statusText === 'OK') {
    this.storingResponse(response);
  }
}

function storingResponse(response) {
  this.data.csdb = response.data.csdbs;
  delete response.data.csdbs;
  this.data.paginationInfo = response.data;
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

/** sama denga FolderVue.js */
function removeList(filename) {
  let csdb = this.data.csdb.find((obj) => obj.filename === filename);
  let index = this.data.csdb.indexOf(csdb);
  this.data.csdb.splice(index,1);
  return csdb
}

async function restore() {
  let filenames = this.CB.value();
  let response = await axios({
    route: {
      name: 'api.restore_object',
      data: { filename: filenames },
    },
  });
  if (response.statusText != 'OK') return;
  
  // hapus list di folder, tidak seperti listtree yang ada level dan list model, dan emit csdbDelete
  const csdbRestored = [];
  filenames.forEach((filename) => {
    let csdb = this.removeList(filename);
    csdbRestored.push(csdb);
  });

  // emit
  this.emitter.emit('RestoreCSDBobejctFromDeletion',csdbRestored);
}

async function permanentDelete(filename) {
  if (!(await this.$root.alert({ name: 'beforePermanentDeleteCsdbObject', filename: filename }))) {
    return;
  }

  let values = await this.CbSelector.permanentDelete(this.CbSelector.cancel); // output array contains filename
  if(isEmpty(values)) return; // jika fetch hasilnya reject (not resolve)
  else if(values instanceof FormData) values = formDataToObject(values);
  if(isString(values.filename)) values.filename = values.filename.split(',');

  // hapus list di folder, tidak seperti listtree yang ada level dan list model, dan emit csdbDelete
  const csdbDeleted = [];
  values.filename.forEach((filename) => {
    let csdb = this.removeList(filename);
    csdbDeleted.push(csdb);
  });
}



function refresh(data) {
  // data adalah Array contain csdb deleted Object
  data.forEach((obj) => {
    this.data.csdb.push(obj)
  });
}

function select(event){
  let el;
  if(this.CbSelector.selectionMode) {
    this.CbSelector.select();
    el = document.getElementById(this.CbSelector.cbHovered);
  } else el = event.target;
  this.selectedRow ? this.selectedRow.classList.remove('bg-blue-300') : null;
  this.selectedRow = findAncestor(el, 'tr');
  this.selectedRow.classList.add('bg-blue-300');
}

function clickFilename(event, filename){
  // stil nothing to do except to open CB selector for temporary until it has made decision
  this.CB.push();
}

function preview(){

}


export {getObjs, storingResponse, goto, removeList, restore, permanentDelete, refresh, select, preview, clickFilename}