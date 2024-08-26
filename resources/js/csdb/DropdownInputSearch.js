import Randomstring from "randomstring";
import { useTechpubStore } from "../techpub/techpubStore";
import {isArrowDownKeyPress, isArrowUpKeyPress,isEnterKeyPress, isEscapeKeyPress, isLeftClick} from './helper.js';

// source: https://stackoverflow.com/questions/4179708/how-to-detect-if-the-pressed-key-will-produce-a-character-inside-an-input-text
function isCharacterKeyPress(evt) {
  if (typeof evt.which == "undefined") {
    // This is IE, which only fires keypress events for printable keys
    return true;
  } else if (typeof evt.which == "number" && evt.which > 0) {
    // In other browsers except old versions of WebKit, evt.which is
    // only greater than zero if the keypress is a printable key.
    // We need to filter out backspace and ctrl/alt/meta key combinations
    // return !evt.ctrlKey && !evt.metaKey && !evt.altKey && evt.which != 8;
    // modifan saya
    return !evt.ctrlKey && !evt.metaKey && !evt.altKey && evt.which != 8 
    && (evt.which !== 1) && (evt.which !== 2) && (evt.which !== 3) && (evt.which !== 27) && (evt.which !== 13) && (evt.which !== 37) && (evt.which !== 38) && (evt.which !== 39) && (evt.which !== 40);
  }
  return false;
}

class DropdownInputSearch {
  idInputText = '';
  idDropdownListContainer = ''

  timeout = 0;
  process = new Promise(r => { r() });
  result = [];
  isDone = true;

  selected = {};
  hovered = {};
  listItemKey = '';
  showList = true;

  routeName = '';

  constructor(listItemKey, routeName) {
    this.idInputText = Randomstring.generate({ charset: 'alphabetic' });
    this.idDropdownListContainer = Randomstring.generate({ charset: 'alphabetic' });
    this.listItemKey = listItemKey;
    this.routeName = routeName;
  }

  /**
   * Nanti diubah jangan pakai if-else tapi pakai switch biar lebih cepat
   * @param {Event} event 
   * @param {Object} techpubRoute 
   * @returns {array} index#1 event name, index#2 result
   */
  keypress(event, techpubRoute) {
    event.preventDefault();
    event.stopPropagation();
    this.showList = true;
    if(isCharacterKeyPress(event)) {
      return ['isCharacterKeyPress', this.searching(event, techpubRoute)];
    }
    else if(isArrowDownKeyPress(event)) {
      return ['isArrowDownKeyPress', this.moveDown(event)];
    }
    else if(isArrowUpKeyPress(event)) {
      return ['isArrowUpKeyPress', this.moveUp(event)];
    }
    else if(isEnterKeyPress(event)) {
      return ['isEnterKeyPress', this.select(event)];
    }
    else if(isLeftClick(event)) {
      return ['isLeftClick', this.selectByClick(event)];
    }
    else if(isEscapeKeyPress(event)) {
      return ['isEscapeKeyPress', this.cancel(event)];
    }
  }

  searching(event, techpubRoute) {
    this.process = new Promise((r, j) => {
      clearTimeout(this.timeout);
      this.isDone = false;
      this.timeout = setTimeout(() => {
        if(!techpubRoute){
          techpubRoute = useTechpubStore().getWebRoute(this.routeName, {sc: event.target.value, limit:5})
        }
        let worker;
        if (window.Worker) {
          worker = new Worker(`/worker/WorkerInputSearch.js`, { type: "module" });
        }
        if (!worker) return false;
        worker.onmessage = (e) => {
          if (e.data.error) { // error merupakan key dari worker js
            j(false);
            this.result = [];
            this.isDone = false;
          } else {
            r(true);
            this.isDone = true;
            this.result = (
              e.data.csdbs ? e.data.csdbs : (
                e.data.users ? e.data.users : e.data.result
              )
            ); // result merupakan key dari server
          }
          worker.terminate();
        }
        worker.postMessage({ route: techpubRoute });
      }, 500);
    });

    return this.process;
  }

  moveDown(event){
    let el = event.target;
    if(event.target.id === this.idInputText){
      el = document.getElementById(this.idDropdownListContainer).firstElementChild;
    } else {
      el = el.nextElementSibling ?? document.getElementById(this.idInputText);// null jika tidak ada nextElementSibling;
    }
    this.focus(el);
    this.unfocus(event.target);
    this.setHovered(el);
  }

  moveUp(event){
    let el = event.target.previousElementSibling ?? document.getElementById(this.idInputText); // null jika tidak ada previousElementSibling
    this.focus(el);
    this.unfocus(event.target);
    this.setHovered(el);
  }

  select(event){
    if(event.target.id === this.idInputText) {
      return event.target.value
    }
    else {
      this.selected = this.hovered; // object hasil fetch bisa csdb,user,dll
      document.getElementById(this.idInputText).value = this.selected[this.listItemKey];
      this.showList = false;
      return this.selected[this.listItemKey];
    };
  }
  
  selectByClick(event){
    this.setHovered(event.target);
    return this.select(event);
  }

  cancel(event){
    this.hovered, this.selected = {};
    this.showList = false;
  }

  // ##### function helper below ######
  setHovered(element){
    let listItemKeyValue = element.getAttribute(this.listItemKey);
    // console.log(listItemKeyValue);
    this.hovered = this.result.find(v => v[this.listItemKey] === listItemKeyValue); // object hasil fetch
  }
  focus(element){
    // console.log(element);
    element.tabIndex = 0;
    element.focus();
  }
  unfocus(element){
    element.tabIndex = -1;
  }
}

export default DropdownInputSearch;