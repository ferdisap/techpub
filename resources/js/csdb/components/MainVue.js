import { useTechpubStore } from '../../techpub/techpubStore';
import axios from 'axios';
// available action towards CSDB
/**
 * @param {*} data; if data.filenames is array then it'll be joined, otherwise data.filename is choosen 
 */
function joinFilename(data){
  let filenames = data.filenames || data.filename;
  if (Array.isArray(filenames)) filenames = filenames.join(', ');
  return filenames;
}

async function deleteCSDBs(data){
  if(!data) return;
  let filenames = this.joinFilename(data);
  if (!(await this.$root.alert({ name: 'beforeDeleteCsdbObject', filename: filenames }))) {
    return;
  }
  axios({
    route: {
      name: 'api.delete_objects',
      data: {filenames: filenames},
    },
  })
  then(response => {
    if(response.statusText === 'OK' || ((response.status >= 200) && (response.status < 300))) this.emitter.emit('DeleteMultipleCSDBObject', response.data.models)
  });
}

/**
 * jika ingin pakai fungsi ini di component lain, pakai getCSDBObjectModel.call(this, data)
 * data must contain filename
 * @param {Object} data 
 */
function getCSDBObjectModel(data){
  if(data.filename !== (useTechpubStore().currentObjectModel.csdb ? useTechpubStore().currentObjectModel.csdb.filename : '')){
    clearTimeout(this.to);
    this.to = setTimeout(()=>{
      axios({
        route: {
          name: 'api.get_object_model',
          data: {filename: data.filename}        
        },
      }).then((response)=>{
        if(response.statusText === 'OK' || ((response.status >= 200) && (response.status < 300))) useTechpubStore().currentObjectModel = response.data.model;
      })
    },0);
  }
}

export {joinFilename, deleteCSDBs, getCSDBObjectModel};