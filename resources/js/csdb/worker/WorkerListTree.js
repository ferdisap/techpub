// jika ingin pakai modul, begini
// import setListTreeData from './setListTreeData.js';

const ListTree = {
  data: {},
  // request: async function (data) {
  request: async function () {
    let response;
    let route = this.data.route;
    if (Object.values(route.method).includes('GET') || route.method.includes('HEAD')) {
      response = await fetch(route.url);
    }
    return new Promise(async (resolve, reject) => {
      // tes;
      if (response.ok) {
        let json = await response.json();
        // resolve(setListTreeData(json.csdbs)); // jika ingin pakai modul
        // resolve(this.setListTreeData(json.csdbs));
        resolve(this.setListTreeData(json.csdbs));
      } else {
        reject([]);
      }
    })
  },
  /**
   * @return {Array}
   * @param {Object} response from axios Response 
   */
  setListTreeData: function (responseData) {
    // sortir berdasarkan path
    responseData = responseData.sort((a, b) => {
      return a.path > b.path ? 1 : (a.path < b.path ? -1 : 0);
    });
    // sortir object dan level path nya eg: "/csdb/n219/amm" berarti level 3
    let obj = {};
    let levels = {};
    for (const v of responseData) {
      let path = v.path
      let split = path.split("/");
      let l = split.length;

      let p = [];
      for (let i = 1; i <= l; i++) {
        p.push(split[i - 1]);
        levels[i] = levels[i] ?? [];
        levels[i].push(p.join("/"));
      }
      levels[l].indexOf(path) < 0 ? levels[l].push(path) : '';

      obj[path] = obj[path] || [];
      obj[path].push(v);

    }
    return [obj, levels];
  },

  createHTMLString: function(start_l, data_list_level, data_list){
    const gen_objlist = function (models, style = '') {
      let listobj = '';
      if (models) { // ada kemungkinan models undefined karena path "csdb/n219/amm", csdb/n219 nya tidak ada csdbobject nya
        for (const model of models) {
          const isICN = model.filename.substr(0, 3) === 'ICN';
          const logo = isICN ? `<span class="material-symbols-outlined">mms</span>&#160;` : `<span class="material-symbols-outlined">description</span>&#160;`;
          let href = isICN ? this.data.hrefForOther : this.data.hrefForPdf ;
          const cb = `<span cb-window class="mr-1"><input type="checkbox" value="${model.filename}"/></span>`;
          href = href.replace(':filename', model.filename);
          const viewType = isICN ? 'other' : 'pdf';
          listobj = listobj + `
                <div class="obj" style="${style}" cb-room>
                  ${cb}${logo}<a href="${href}" @click.prevent="$parent.clickFilename({path:'${model.path}',filename: '${model.filename}', viewType:'${viewType}'})">${model.filename}</a>
                </div>`
        }
      }
      return listobj
    };
    const path_yang_sudah = [];
    const fn = (start_l = 1, leveldata = {}, dataobj = {}, callback, parentPath = '') => {
      let details = '';
      let defaultMarginLeft = 5;
      if (leveldata[start_l]) {

        for (const path of leveldata[start_l]) { // untuk setiap path 'csdb' dan 'xxx'

          let pathSplit = path.split("/");
          let currFolder = pathSplit[start_l - 1];
          pathSplit.splice(pathSplit.indexOf(currFolder), 1);
          let parentP = pathSplit.join("/");

          if (path_yang_sudah.indexOf(path) >= 0
            || path_yang_sudah.indexOf(parentP) >= 0
            || parentP !== parentPath // expresi ini membuat path tidak di render
          ) {
            continue;
          }
          let isOpen = this.data.open ? this.data.open[path] : false;
          isOpen = isOpen ? 'open' : '';
          const cb = `<span cb-window class="mr-1"><input type="checkbox" value=""/></span>`;

          // generating folder list
          // <details ${isOpen} style="margin-left:${start_l * 3 + defaultMarginLeft}px;" path="${path}" @click="clickDetails($el)">
          details = details + `
          <details ${isOpen} cb-room style="margin-left:${start_l * 3 + defaultMarginLeft}px;" path="${path}">
            <summary class="list-none flex">
              ${cb}
              <span @click.prevent="expandCollapse('${path}')" class="material-symbols-outlined cursor-pointer text-sm content-center">chevron_right</span> 
              <a href="#" @click.prevent="$parent.clickFolder({path: '${path}'})">${currFolder}</a>
            </summary>`;

          if (leveldata[start_l + 1]) {
            details = details + (callback.bind(this, start_l + 1, leveldata, dataobj, callback, path))();
          }

          // generating obj list
          details = details + (gen_objlist.bind(this,dataobj[path], `margin-left:${start_l * 3 + defaultMarginLeft + 2}px;`))();

          details = details + "</details>"

          path_yang_sudah.push(path);
        }
      }
      return details
    };

    return fn(this.data.start_l, this.data.list_level, this.data.list, fn);
  }
}

onmessage = async function (e) {
  ListTree.data = e.data.data;
  let ret;
  switch (e.data.mode) {
    case 'fetchData':
      ret = await ListTree.request(e.data.data);
      break;  
    case 'createHTMLString':
      ret = ListTree.createHTMLString(e.data.data.start_l, e.data.data.data_list_level, e.data.data.data_list)
      break;  
    default:
      break;
  }
  postMessage(ret);
}