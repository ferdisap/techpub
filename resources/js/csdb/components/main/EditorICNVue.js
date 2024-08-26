import axios from 'axios';

async function submit(event) {
  this.showLoadingProgress = true;

  const fd = new FormData(event.target);
  const response = await axios({
    route: {
      name: 'api.upload_ICN',
      data: fd
    }, useMainLoadingBar: false,
  });
  if (response.statusText === 'OK') this.emitter.emit('uploadICNFromEditor', response.data.csdb);

  this.showLoadingProgress = false;
}

function readEntity(event) {
  let file = event.target.files[0];
  if (file) {
    if (file.type === 'text/xml') {
      alert('you will be moved to xml editor.');
      // push route to editor page here
      const reader = new FileReader();
      reader.onload = () => {
        this.$parent.readTextFileFromUploadICN(reader.result);
        this.emitter.emit('readTextFileFromUploadICN'); // untuk mengoffkan preview dan lain2nya
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

export {submit, readEntity}