import axios from 'axios';

async function submit(event) {
  const formData = new FormData(event.target);
  let response = await axios({
    route: {
      name: 'api.create_dml',
      data: formData,
    },
    useComponentLoadingProgress: this.componentId,
  });
  if (response.statusText === 'OK') this.emitter.emit('createDMLFromEditorDML', response.data.csdb);
}

async function update(event){
  const formData = new FormData(event.target);
  let response = await axios({
    route: {
      name: 'api.dmlupdate',
      data: formData,
    },
    useComponentLoadingProgress: this.componentId,
  });
  if(response.statusText === 'OK'){
    // this.emitter.emit('createDMLFromEditorDML', { model: response.data.data });
    // do something here
  }
}

/**
 * DEPRECATED, dipindah ke DMLVue.js
 */
async function showDMLContent(){
  let response = await axios({
    route: {
      name: 'api.read_json',
      data: {filename: this.$route.params.filename}
    },
    useComponentLoadingProgress: this.componentId,
  });
  if(response.statusText === 'OK'){
    // do something here
    // this.transformed = response.data.transformed
    this.json = response.data.json
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