import { findAncestor } from "./helper.js";
/**
 * HOW TO USE
 * 
 * 1. Construct >> new ContextMenu();
 * 2. set id as per 'menuId' to the element
 * 3. >> ContextMenu.register(menuId)
 * 4. >> ContextMenu.toogle(false, menuId);
 */


class ContextMenu {

  id; // current or last context menu
  collection;
  anchorNode; // berupa target node saat di #trig
  triggerTarget; // DOMElement

  constructor() {
    this.collection = {};
    this.id = '';
    // check di chrome engine berapa banyak listener di register => getEventListeners(document).click[0].listener
    // useCapture = true berarti tidak buble, source: https://stackoverflow.com/questions/7398290/unable-to-understand-usecapture-parameter-in-addeventlistener
    // sebenernya ini lebih baik pakai mousedown, tapi resikonya harus ubah di seluruh vue component yang pakai context menu. Eventnya bukan @click tapi @mousedown
    document.addEventListener('click', (event) => {
    // document.addEventListener('mousedown', (event) => {
      // console.log(window.e = event)
      // if (window.getSelection().type === 'Range') return;
      // event.preventDefault(); // kalau di prevent, misal submit event tidak berjalan jika klik button
      // event.stopPropagation(); // kalau pakai stopPropagation, maka tidak bisa click2 apapun di vue
      switch (event.target.tagName) {
        case 'INPUT':
          // console.log("case INPUT", findAncestor(event.target,"*[context-menu]"));
          if(findAncestor(event.target,"*[context-menu]")) break;
        case 'FORM':
          if(findAncestor(event.target,"*[context-menu]")) break;
          // break;
        default:
          this.toggle(false, this.id);
          break;
      }
    }, true); // kalau false, menu tidak menutup otomatis
    // },false);
    // window.ContextMenu = new Proxy(this,{}); // tidak perlu dibuat di window, karena di vue sudah di instance di app.js
  }

  register(menuId, defaultDisplayOnTrue = 'block', defaultDisplayOnFalse = 'none') {    
    const menu = document.getElementById(menuId);
    if(!menu) return;

    this.collection[menuId] = {
      state: false,
      displayOnFalse: defaultDisplayOnFalse,
      displayOnTrue: defaultDisplayOnTrue,
      // disabled: false,
    }

    menu.style.position = 'fixed';
    menu.style.zIndex = '100';

    const area = menu.parentElement;
    area.setAttribute('cm-target-id', menuId);
    area.addEventListener('contextmenu', this.#trig.bind(this));

    return true;
    // console.log(`${menuId} has been registered.`);
  }

  /**
   * jika state TRUE dan id EXIST maka akan mengOFFkan previous menu dan mengONkan current menu id
   * @param {Bool} state 
   * @param {String} id 
   */
  toggle(state = false, id = '') {
    if (state && id) {
      // turnoff previous menu
      this.collection[this.id].state = !state;
      this.display(this.id);
      // turnon current menu
      this.id = id;
      this.collection[this.id].state = state;
      this.display(this.id);
    } else {
      // kalau 'state' TRUE dan 'id' FALSE maka turn ON current this.id. 
      // kalau 'state' FALSE dan 'id' TRUE maka akan turn OFF the id.
      // kalau 'state' FALSE dam 'id' FALSE maka akan turn OFF current ID;
      if (id) this.id = id;
      if(this.collection[this.id]){
        this.collection[this.id].state = state;
        this.display(this.id);
      };
    }
    // sengaja dibuat pakai setTimeout agar setiap event terkait bisa dijalankan sebeleum document mentup context menu, contohnya fitur copy di helper;
    if(!state) setTimeout(()=>this.anchorNode = '',0);
  }

  display(id) {
    const el = document.getElementById(id);
    const display = this.#getDisplay(id);
    if (el) el.style.display = display;

    // mungkin nanti dihapus supaya tidak memory leaks, tapi pakai clearTimeout agar..
    // if(display === 'none'){
    //   setTimeout(()=>{
    //     this.triggerTarget = undefined;
    //   },1000)
    // }
  }

  #getDisplay(id) {
    return (this.collection[id].state ? this.collection[id].displayOnTrue : this.collection[id].displayOnFalse);
  }

  #trig(event) {
    event.preventDefault();
    event.stopPropagation();
    this.triggerTarget = event.target;

    const id = event.target.closest("*[cm-target-id]").getAttribute('cm-target-id');
    this.#positionMenu(event, id);
    this.toggle(true, id);
    this.anchorNode = event.target;
  }

  // position the Context Menu in right position.
  #positionMenu(e, id) {
    const clickCoords = this.#getPosition(e);
    const clickCoordsX = clickCoords.x;
    const clickCoordsY = clickCoords.y;

    const menu = document.getElementById(id);

    const menuWidth = menu.offsetWidth + 4;
    const menuHeight = menu.offsetHeight + 4;

    const windowWidth = window.innerWidth;
    const windowHeight = window.innerHeight;

    if ((windowWidth - clickCoordsX) < menuWidth) {
      menu.style.left = windowWidth - menuWidth + "px";
    } else {
      menu.style.left = clickCoordsX + "px";
    }

    if ((windowHeight - clickCoordsY) < menuHeight) {
      menu.style.top = windowHeight - menuHeight + "px";
    } else {
      menu.style.top = clickCoordsY + "px";
    }
  }

  // get the position of the right-click in window and returns the x and y coordinates
  #getPosition(e) {
    let posX = 0;
    let posY = 0;

    if (!e) var e = window.event;

    if (e.pageX || e.pageY) {
      posX = e.pageX;
      posY = e.pageY;
    }
    else if (e.clientX || e.clientY) {
      posX = e.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
      posY = e.clientY + document.body.scrollTop + document.documentElement.scrollTop;
    }

    return {
      x: posX,
      y: posY,
    };
  }
}

export default ContextMenu;