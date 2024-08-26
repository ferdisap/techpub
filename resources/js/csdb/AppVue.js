import axios from 'axios';
import { markdown } from 'markdown';
import createAlert from '../alert';
import { findText } from './helper';
import $ from 'jquery';


/**
 * required data.filename 
 */
async function info(data = {}) {
  let config = {
    route: {
      name: 'api.info',
      data: data,
    },
    responseType: 'text'
  };
  let md = await axios(config);
  // membuat <div> dulu agar didalam MD bisa ada tag HTML. Ini dicontohkan dalam README.md punya laravel (basic);
  let text = md.data;
  let div = $('<div/>').html(text);
  div.contents().each((i, e) => {
    if (e.nodeType === 3) {
      $(e).replaceWith(markdown.toHTML(e.textContent));
    }
  })
  this.infoData.message = div[0].innerHTML;
  this.infoData.show = true;
  this.infoData.name = name;
}

/**
 * required data.filename, data.objectame
 * diutamakan request filename.md. Jika request error, maka akan pakai data.message
 */
async function alert(data = {}) {
  // get MD file
  let response = await axios({
    route: {
      name: 'api.alert',
      data: data,
    }
  });
  if (response.statusText !== 'OK') {
    return;
  }
  let text = response.data;
  if (!text) {
    text = data.message;
  }
  delete data.name; // dihapus data.name agar tidak tertukar antara 'filename' dan 'name'. 'name' adalah nama alert
  delete data.message;
  // replace all variable in MD file with params 'data'
  findText(/`\${([\S]+)}`/gm, text).forEach(v => {
    let replaced = v[0].replace(/(?<=`\${)([\S]+)(?=}`)/gm, 'data.$1')
    text = text.replace(v[0], eval(replaced));
  })

  // escaping text jikalau ada html didalam MD file
  let div = $('<div/>').html(text);
  div.contents().each((i, e) => {
    if (e.nodeType === 3) {
      $(e).replaceWith(markdown.toHTML(e.textContent));
    }
  })
  data.message = div[0].innerHTML;
  // finish by creating Alert;
  this.alertData = createAlert(data);
  return this.alertData.result;
}

export { info, alert };