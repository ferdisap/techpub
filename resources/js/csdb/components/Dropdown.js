import Randomstring from "randomstring";
import RoutesWeb from "../RoutesWeb";
import {
  isObject, isString, isNumber, findText, getObjectValueFromString,
  findAncestor, indexFromParent,
  isArrowDownKeyPress, isArrowUpKeyPress, isEnterKeyPress, isEscapeKeyPress, isLeftClick, isCharacterKeyPress
} from "../helper";


// Example 1: add a div to the page
// const div = fromHTML('<div><span>nested</span> <span>stuff</span></div>');
// document.body.append(div);

// Example 2: add a bunch of rows to an on-page table
// const rows = fromHTML('<tr><td>foo</td></tr><tr><td>bar</td></tr>');
// table.append(...rows);

// Example 3: add a single extra row to the same on-page table
// const td = fromHTML('<td>baz</td>');
// const row = document.createElement(`tr`);
// row.append(td);
// table.append(row);
/**
* @param { String } HTML representing a single element.
* @param { Boolean } flag representing whether or not to trim input whitespace, defaults to true.
* @return { Element | HTMLCollection | null}
*/
const fromHTML = function (html, trim = true) {
  // Process the HTML string.
  html = trim ? html.trim() : html;
  if (!html) return null;

  // Then set up a new template element.
  const template = document.createElement('template');
  template.innerHTML = html;
  const result = template.content.children;

  // Then return either an HTMLElement or HTMLCollection,
  // based on whether the input HTML had one or more roots.
  if (result.length === 1) return result[0];
  return result;
}

// data = [d,...d]. d adalah {}, keysnya adalah 'keys[string...]','targets[string...]','result[object...]'.
// dataResults = [o,...o]. o adalah {}, keysnya sesuai dengan hasil fetch backedn
// config = [c,...c]. c adalah {}, keysnya adalah '__element','__class','__style', '__innerHTML[configsOrKey,...configsOrKey]',
const getContainerResultTemplate = function (dataResults = [{}], configs = [{ __element: '', __class: '', __style: '', __innerHTML: [] }], inputId, targets = '') {
  let html = `<div dd-container-result${targets ? '="' + targets + '"' : ''} class="w-full absolute z-50 bg-slate-100 shadow-md rounded-md">`;
  for (let o = 0; o < dataResults.length; o++) {
    html += `<div dd-list-from="${inputId}"  class="text-sm border-b px-2 cursor-pointer hover:bg-blue-300">`;
    for (let c = 0; c < configs.length; c++) {
      let inner = makeInner(configs[c]);
      // let match = findText(/:\w+/g, inner);
      // let match = findText(/:\w+|\.\w+/g, inner);
      let match = findText(/:[\w\.]+/g, inner);
      for (let m = 0; m < match.length; m++) {
        const key = match[m][0].replace(":",'');
        const value = getObjectValueFromString(dataResults[o], key);
        inner = inner.replace(match[m][0], value);
      }
      html += inner;
    }
    html += `</div>`;
  }
  html += `</div>`;
  return html;
};
const makeInner = function (config = { __element: '', __class: '', __style: '', __innerHTML: [] }) {
  let html = `<${config.__element}` + (config.__class ? ` class="${config.__class}"` : '') + (config.__style ? ` style="${config.__style}"` : '') + `>`;
  for (let i = 0; i < config.__innerHTML.length; i++) {
    let str;
    if (isObject(config.__innerHTML[i])) str = makeInner(config.__innerHTML[i]);
    else if (isString(config.__innerHTML[i]) || isNumber(config.__innerHTML[i])) str = config.__innerHTML[i];
    html += str;
  }
  html += `</${config.__element}>`;
  return html;
}


/**
 * HOW TO USE
 * 1. Add attribute 'dd-input="$key"' dimana $key adalah data yang akan didapan dari server, bisa multiple pakai separator ",". Key pertama adalah value yang nanti ditulis di input dan yang menjadi acuan query ke server. Misal '/?sc=filename::DMC' artinya key pertama harus filename
 * 2. Add attribute 'dd-target="$inputId"' dimana $inputId adalah target input saat result selected. Bisa multiple pakai comma, dimana urutannya sesuai dengan attribute dd-input. Kalau ''||'self' berarti targetnya self
 * 3. Add attribute 'dd-route="techpubStoreRouteName"';
 * 4. Add attribute 'dd-type="csdbs"' atau 'dd-type="users"' dimana key dari sebuah data/result dari server. defaultnya 'result' atau 'results'
 * 5. run register method with scope/parent id;
 * 6. register each input
 */

class Dropdown {

  focusId = '';
  collection = {};
  timeout = 0;

  register(scopeId = '') {
    document.querySelectorAll((scopeId ? `#${scopeId} ` : '') + "*[dd-input]").forEach(input => {
      input.id = input.id ? input.id : Randomstring.generate({ charset: 'alphabetic' });
      input.onclick = this.setFocus.bind(this);
      input.onkeydown = this.onKeyPress.bind(this);
      this.collection[input.id] = {
        keys: input.getAttribute('dd-input').split(","),
        targets: input.getAttribute('dd-target').split(","),
        results: [],
        route: input.getAttribute('dd-route'), // routeName
        type: input.getAttribute('dd-type')
      };
      input.removeAttribute('dd-route');
    })
  }

  setFocus(event) {
    if(this.focusId){
      const input = document.querySelector(`#${this.focusId}`);
      const container = input.parentElement.querySelector("*[dd-container-result]");
      if(container) container.style.display = 'none';
    }
    this.focusId = event.target.id;
    this.render(event.target.id);
  }


  // contoh jika pakai innerHTML yang collection[id].keys nya punya lebih dari 1, misal keysnya adalah @dd-input="filename,path"
  // const template = getContainerResultTemplate(
  //   this.collection[id].results,
  //   [
  //     {
  //       __element: 'span',
  //       __class: 'text-sm',
  //       __innerHTML: [ ":"+this.collection[id].keys[0], " " ,{ // modif di '0' of (keys[0]) nya sesuai keinginan
  //         __element: 'span',
  //         __class: 'text-sm italic',
  //         __innerHTML: [":"+this.collection[id].keys[1]],
  //       }],
  //     }
  //   ],
  //   id,
  //   this.collection[id].targets.join(","),
  // );
  template(collectionId) {
    let config;
    switch (this.collection[collectionId].type) {
      case 'csdbs':
        config = [
          {
            __element: 'span',
            __class: 'text-sm',
            __innerHTML: [":" + this.collection[collectionId].keys[0], " ", { // modif di '0' of (keys[0]) nya sesuai keinginan
              __element: 'span',
              __class: 'text-sm italic text-pink-300',
              __innerHTML: [":path"],
            }],
          }
        ]
        break;
      case 'users':
        config = [
          {
            __element: 'span',
            __class: 'text-sm',
            // __innerHTML: [":first_name :middle_name :last_name" + (this.collection[collectionId].keys[0] ? ':'+this.collection[collectionId].keys[0] : ''), " ", { // modif di '0' of (keys[0]) nya sesuai keinginan
            __innerHTML: [":first_name :middle_name :last_name", " ", { // modif di '0' of (keys[0]) nya sesuai keinginan
              __element: 'span',
              __class: 'text-sm italic text-pink-300',
              __innerHTML: [":email"],
            }],
          }
        ]
        break;
      default:
        config = [
          {
            __element: 'span',
            __class: 'text-sm',
            __innerHTML: [this.collection[collectionId].keys.map(v => (v = ':' + v)).join(" ")],
          }
        ];
        break;
    }

    return getContainerResultTemplate(
      this.collection[collectionId].results,
      config,
      collectionId,
      this.collection[collectionId].targets.join(","),
    );
  }

  render(id) {    
    // remove previous container result
    const input = document.getElementById(id);

    let container = input.parentElement.querySelector("*[dd-container-result]");
    if(container) container.remove();

    // create new container
    container = fromHTML(this.template(id));

    // setEvent
    container.addEventListener('click', this.onClick.bind(this), true);
    container.addEventListener('keyup', this.onKeyPress.bind(this), true);

    input.parentElement.style.position = 'relative';
    input.parentElement.append(container);
  }

  onKeyPress(event) {
    event.stopPropagation();
    let isSearch;

    switch (event.keyCode) {
      case 40: return this.move(event, true); // move down
      case 38: return this.move(event, false); // move up
      case 13: return this.select(event);
      case 27: return this.cancel(event); // escape key
      case 8: isSearch = true; break; // backspace
      case 46: isSearch = true; break; // delete
    }
    if (isCharacterKeyPress(event)) isSearch = true;
    if (isSearch) {
      clearTimeout(this.to_search);
      this.to_search = setTimeout(this.searching.bind(this, event), 300)
    } else {
      if(this.focusId){
        const input = document.querySelector(`#${this.focusId}`);
        const container = input.parentElement.querySelector("*[dd-container-result]");
        if(container) container.style.display = 'none';
      }
    }
  }

  onClick(event) {
    event.preventDefault();
    event.stopPropagation();
    if (isLeftClick(event)) this.select(event);
  }

  searching(event) {
    if(!event.target.value) return;

    const searchValue = event.target.value.replace(/.+,/gm, '').trim();
    this.focusId = event.target.id;
    this.collection[event.target.id].targetSearchValue = searchValue;
    const techpubRoute = RoutesWeb.get(this.collection[event.target.id].route, { 
      sc: this.collection[event.target.id].keys[0] + '::' + searchValue,
      limit: 5 
    });
    // kalau pakai axios, value input berubah lagi seperti di techpubstore walau sudah di set
    fetch(techpubRoute.url)
    .then(rsp => rsp.json())
    .then(data => {
      this.collection[event.target.id].results = (
        this.collection[event.target.id].type ? (
          data[this.collection[event.target.id].type] ? data[this.collection[event.target.id].type] : (
            data.results ? data.results : (
              data.result ? data.result : []
            )
          )
        ) : (
          data.results ? data.results : (
            data.result ? data.result : []
          )
        )
      );
      this.render(event.target.id);
    })
    return;
  }

  move(event, down = true) {
    let el = event.target;
    if (event.target.id === this.focusId) {
      el = document.querySelector(`*[dd-list-from="${event.target.id}"]`);
    } else {
      el = (down ? el.nextElementSibling : el.previousElementSibling) ?? document.getElementById(this.focusId);
    }
    // focusing list
    el.tabIndex = 0;
    el.focus();
  }

  cancel(event) {
    event.target.tabIndex = -1;
    if (event.target.hasAttribute('dd-input')) {
      while(event.target.nextElementSibling && event.target.nextElementSibling.hasAttribute('dd-container-result')){
        event.target.nextElementSibling.style.display = 'none';        
        break;
      }
    } else {
      findAncestor(event.target, "*[dd-container-result]").style.display = 'none';
    }
  }

  /**
   * Setiap targetsId (input value) akan diisi sesuai dengan result dengan index yang sama
   * Misal: attribute dd-input="enterpriseName, enterpriseCode" dan dd-target="someId1, someId2". Input dengan id 'someId1' akan disi dengan enterprise dan seterusnya
   */
  select(event) {
    const evtTarget = this.getEventTarget(event);
    if (!evtTarget) return;

    const indexInResults = indexFromParent(evtTarget); // number
    // untuk setiap target, akan di render value of keys nya
    for (let i = 0; i < this.collection[this.focusId].targets.length; i++) {
      let el;
      let selfTarget, append;
      if (this.collection[this.focusId].targets[i].includes('-append')) append = true;

      if (this.collection[this.focusId].targets[i] === '' || this.collection[this.focusId].targets[i].substr(0, 4) === 'self') {
        el = document.getElementById(this.focusId);
        selfTarget = true;
      } else {
        el = (append ?
          document.getElementById(this.collection[this.focusId].targets[i].replace('-append', '')) :
          document.getElementById(this.collection[this.focusId].targets[i]));
      }

      if (append) {
        el.value = el.value.replace(/(.+,).+/gm, (m, p1) => p1); // akan menghilangkan value terakhir. Eg: 'DMC-saaksmas.xml, DMC-x, DMC-y' akan menghilangkan ' DMC-y'.
        el.value += ' ' + getObjectValueFromString(this.collection[this.focusId].results[indexInResults], this.collection[this.focusId].keys[i]);
      } else {
        el.value = getObjectValueFromString(this.collection[this.focusId].results[indexInResults], this.collection[this.focusId].keys[i]);
      }

    }

    // close/unshowed container here
    findAncestor(event.target, "*[dd-container-result]").style.display = 'none';
  }

  /**
   * 
   * @param {Event} event 
   * @returns DOMElement or null
   */
  getEventTarget(event) {
    return event.target.hasAttribute('dd-list-from') ? event.target : findAncestor(event.target, '*[dd-list-from]');
  }
}

export default Dropdown;