import axios from 'axios';

async function submit(event) {
  this.showLoadingProgress = true;
  const formData = new FormData(event.target);
  let response = await axios({
    route: {
      name: 'api.create_dml',
      data: formData,
    },
    useMainLoadingBar: false,
  });
  if (response.statusText === 'OK') this.emitter.emit('createDMLFromEditorDML', response.data.csdb);
  this.showLoadingProgress = false;
}

async function update(event){
  this.showLoadingProgress = true;
  const formData = new FormData(event.target);
  let response = await axios({
    route: {
      name: 'api.dmlupdate',
      data: formData,
    },
    useMainLoadingBar: false,
  });
  if(response.statusText === 'OK'){
    // this.emitter.emit('createDMLFromEditorDML', { model: response.data.data });
    // do something here
  }
  this.showLoadingProgress = false;
}

/**
 * DEPRECATED, dipindah ke DMLVue.js
 */
async function showDMLContent(){
  this.showLoadingProgress = true;
  let response = await axios({
    route: {
      name: 'api.read_json',
      data: {filename: this.$route.params.filename}
    },
    useMainLoadingBar: false,
  });
  if(response.statusText === 'OK'){
    // do something here
    // this.transformed = response.data.transformed
    this.json = response.data.json
    this.showLoadingProgress = false;
    this.techpubStore.currentObjectModel = response.data.model;
  }
}

async function searchBrex(event){
  if(event.target.id === this.DropdownBrexSearch.idInputText){
    let route = this.techpubStore.getWebRoute('api.dmc_search', {sc: "filename::" + event.target.value, limit:5});
    this.DropdownBrexSearch.keypress(event, route);
  }
}

export {submit, update, showDMLContent, searchBrex};