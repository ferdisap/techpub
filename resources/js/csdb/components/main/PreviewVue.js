import { contentType } from 'es-mime-types';
import RoutesWeb from '../../RoutesWeb';
import axios from 'axios';

function refresh(data) {
  if (data.sourceType === 'blobURL') {
    this.renderFromBlob(data.src, data.mime)
  } else {
    this.render(data.filename, data.viewType ? data.viewType : this.$route.params.viewType);
  }
}

function renderFromBlob(src, mime){
  const icnContainer = document.getElementById('icn-container');
  const embed = `<embed src="${src}" type="${mime}"/>`
  icnContainer.innerHTML = embed;
  // setTimeout(()=>{
  //   this.mime = mime;
  //   this.src = src;
  // },10);
}

async function blobRequestTransformed(routename, data, mime) {
  data = Object.assign(data, this.$route.query);
  delete (data.update_at);
  let responseType = !mime.includes('text') ? 'arraybuffer' : 'json';
  // masukkan cache If-None-Match jika perlu, di server sudah siap
  let response = await axios({
    route: {
      name: routename,
      data: data,
    },
    responseType: responseType,
  });
  if (response.statusText === 'OK') {
    let blob = new Blob([response.data], { type: mime });
    let url = URL.createObjectURL(blob);
    return url;
  } 
}

async function render(filename, viewType){
  URL.revokeObjectURL(this.src);
  this.mime, this.src = undefined;
  let routename;
  let extension = filename.substring(filename.length,filename.length-4);
  let doctype = filename.substring(0,3);

  // eg. DMC viewtype == 'html' (ietm);
  if(extension === '.xml' && viewType === 'html') {
    routename = 'api.read_html_object';
    this.mime = 'text/html';
    this.inIframe = true;
  }
  // eg. DMC viewType == 'pdf'
  else if(extension === '.xml' && viewType === 'pdf') {
    routename = 'api.read_pdf_object';
    this.mime = 'application/pdf';
    this.inIframe = true;
  }
  // eg. ICN
  else if(doctype === 'ICN' && (viewType === 'html' || viewType === 'other')) {
    routename = 'api.get_icn_raw';
    let path = this.pathHelper.extname(filename);
    this.mime = contentType(path);
    this.inIframe = false;
  }
  // eg. externalpubRef pdf
  else if(doctype != 'ICN' && extension != '.xml' && viewType === 'pdf') {
    routename = 'api.read_pdf_object';
    this.mime = 'application/pdf';
    this.inIframe = true;
  }
  // eg. external pubRef non pdf
  else if(doctype != 'ICN' && extension != '.xml' && viewType != 'pdf') {
    routename = 'api.read_other_object';
    let path = this.pathHelper.extname(filename);
    this.mime = contentType(path);
    this.inIframe = false;
  }
  // eg. ICN tapi viewType === 'pdf'
  else return Promise.reject(false);

  // let route = this.techpubStore.getWebRoute(routename, {filename: filename});
  const route = RoutesWeb.get(routename, {filename: filename});
  // ini untuk embed
  if(!this.inIframe) this.src = route.url.toString();
  // ini untuk iframe HTML dan PDF
  else {
    this.showLoadingProgress = true;
    this.src = await this.blobRequestTransformed(routename, { filename: filename }, this.mime)
    this.showLoadingProgress = false;
  };
  
  return Promise.resolve(true);
}

function switchView(name){
  this.view = name;
  this.render(this.$route.params.filename, name);
  this.$router.replace({
      params: {
          filename: this.$route.params.filename,
          viewType: name
      },
      query: this.$route.query
  });
}

export { refresh, renderFromBlob, render, blobRequestTransformed, switchView };