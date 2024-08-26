function setUpdate(filename){
  this.isUpdate = true; 
  if(filename.slice(0,3) === 'ICN'){
    this.isFile = true;
  } else {
    this.isFile = false;
    this.XMLEditor.setRoute(this.techpubStore.getWebRoute('api.get_object_raw',{filename: filename}));
    this.XMLEditor.fetchRaw();
  }
}

function setCreate(){
  this.isUpdate = false;
  this.isFile = false;
  this.XMLEditor.stopFetch();
  this.XMLEditor.changeText('');
}

function switchEditor(name){
  switch (name) {
    case 'XMLEditor':
      this.isFile = false;
      if(this.$route.params.filename.substring(0,3) === 'ICN') this.isUpdate = false;
      setTimeout(()=>document.getElementById(this.XMLEditor.id).innerHTML = this.XMLEditor.editor.dom.outerHTML,0);
      break;
    case 'FILEUpload':
      this.isFile = true;
      if(this.$route.params.filename && this.$route.params.filename.slice(0,3) != 'ICN') this.isUpdate = false;
      break;
  }
}

function readEntity(event){
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

async function submit(event){
  this.showLoadingProgress = true;
  this.isFile ? (await this.submitUploadFile(event)) : (
    this.isUpdate ? (await this.submitUpdateXml(event)) : (await this.submitCreateXml(event))
  );
  this.showLoadingProgress = false;
}

async function submitUploadFile(event){
  const fd = new FormData(event.target);
  const response = await axios({
    route: {
      name: 'api.upload_ICN',
      data: fd
    }, useMainLoadingBar: false,
  });
  if(response.statusText === 'OK') this.emitter.emit('uploadICNFromEditor', response.data.csdb);
}

async function submitCreateXml(event){
  const fd = new FormData(event.target);
  fd.set('xmleditor', this.XMLEditor.editor.state.doc.toString());
  const response = await axios({
    route: {
      name: 'api.create_object',
      data: fd
    }, useMainLoadingBar: false,
  });
  if(response.statusText === 'OK') this.emitter.emit('createObjectFromEditor', response.data.csdb);
}

async function submitUpdateXml(event){
  const fd = new FormData(event.target);
  fd.set('xmleditor', this.XMLEditor.editor.state.doc.toString());
  const response = await axios({
    route: {
      name: 'api.update_object',
      data: fd
    }, useMainLoadingBar: false,
  });
  console.log(response);
  if(response.statusText === 'OK') this.emitter.emit('updateObjectFromEditor', response.data.csdb);
}
export {setUpdate, setCreate, switchEditor, readEntity, submit, submitUploadFile, submitCreateXml, submitUpdateXml}