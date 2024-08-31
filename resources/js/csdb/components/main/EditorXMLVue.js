import RoutesWeb from "../../RoutesWeb";
import axios from 'axios';

async function setUpdate(filename) {
  this.isUpdate = true;
  if (filename) {
    this.XMLEditor.setRoute(RoutesWeb.get('api.get_object_raw',{filename: filename}));
    if(await this.XMLEditor.fetchRaw()){
      if(this.techpubStore.currentObjectModel.csdb) this.inputPath.value = this.techpubStore.currentObjectModel.csdb.path;
      else {
        axios({
          route: {
            name: 'api.get_csdb_model',
            data: {filename: filename}
          },
        })
        .then(rsp => {
          if(rsp.statusText === 'OK') this.inputPath.value = rsp.data.csdb.path;
          this.techpubStore.currentObjectModel.csdb = rsp.data.csdb;
        })
      }
    }
  }
}

function setCreate() {
  this.isUpdate = false;
  this.XMLEditor.stopFetch();
  this.XMLEditor.changeText('');
}

/**
 * DEPRECATED
 */
function switchEditor(name) {
  switch (name) {
    case 'XMLEditor':
      this.isFile = false;
      if (this.$route.params.filename.substring(0, 3) === 'ICN') this.isUpdate = false;
      setTimeout(() => document.getElementById(this.XMLEditor.id).innerHTML = this.XMLEditor.editor.dom.outerHTML, 0);
      break;
    case 'FILEUpload':
      this.isFile = true;
      if (this.$route.params.filename && this.$route.params.filename.slice(0, 3) != 'ICN') this.isUpdate = false;
      break;
  }
}

/**
 * DEPRECATED
 * @param {*} event 
 */
function readEntity(event) {
  let file = event.target.files[0];
  if (file) {
    if (file.type === 'text/xml') {
      alert('you will be moved to xml editor.');
      // push route to editor page here
      const reader = new FileReader();
      reader.onload = () => {
        this.switchEditor('XMLEditor');
        this.changeText(reader.result);
        this.emitter.emit('readTextFileFromEditor');
      }
      reader.readAsText(file);
    }
    else {
      this.emitter.emit('readFileURLFromEditor', {
        mime: file.type,
        sourceType: 'blobURL',
        src: URL.createObjectURL(file),
      });
    }
  }
}

function submit(event) {
  this.isUpdate ? (this.submitUpdateXml(event)) : (this.submitCreateXml(event));
}

/** DEPRECATED */
async function submitUploadFile(event) {
  const fd = new FormData(event.target);
  axios({
    route: {
      name: 'api.upload_ICN',
      data: fd
    }, useComponentLoadingProgress: this.componentId,
  })
  .then(response => {
    if (response.statusText === 'OK') this.emitter.emit('uploadICNFromEditor', response.data.csdb);
  });
}

async function submitCreateXml(event) {
  const fd = new FormData(event.target);
  fd.set('xmleditor', this.XMLEditor.editor.state.doc.toString());
  axios({
    route: {
      name: 'api.create_object',
      data: fd
    }, useComponentLoadingProgress: this.componentId,
  })
  .then(response => {
    if (response.statusText === 'OK') this.emitter.emit('createObjectFromEditor', response.data.csdb);
  });
}

async function submitUpdateXml(event) {
  window.e = event;
  const fd = new FormData(event.target);
  fd.set('xmleditor', this.XMLEditor.editor.state.doc.toString());
  axios({
    route: {
      name: 'api.update_object',
      data: fd
    }, useComponentLoadingProgress: this.componentId,
  })
  .then(response => {
    if (response.statusText === 'OK') {
      this.emitter.emit('updateObjectFromEditor', response.data.csdb);
      this.techpubStore.currentObjectModel.csdb = response.data.csdb;
    }
  });
}
export { setUpdate, setCreate, switchEditor, readEntity, submit, submitUploadFile, submitCreateXml, submitUpdateXml }