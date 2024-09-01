import axios from 'axios';

async function submit(event) {
  const formData = new FormData(event.target);
  axios({
    route: {
      name: 'api.create_dml',
      data: formData,
    },
    useComponentLoadingProgress: this.componentId,
  })
  .then(response => {
    if (response.statusText === 'OK' || ((response.status >= 200) && (response.status < 300))) this.emitter.emit('createDMLFromEditorDML', response.data.csdb);
  })
}

async function searchBrex(event){
  if(event.target.id === this.DropdownBrexSearch.idInputText){
    let route = this.techpubStore.getWebRoute('api.dmc_search', {sc: "filename::" + event.target.value, limit:5});
    this.DropdownBrexSearch.keypress(event, route);
  }
}

export {submit, searchBrex};