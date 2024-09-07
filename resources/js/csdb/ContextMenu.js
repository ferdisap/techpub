import { findAncestor, isArray } from "./helper.js";
/**
 * HOW TO USE
 * 
 * 1. Construct >> new ContextMenu();
 * 2. set id as per 'menuId' to the element
 * 3. >> ContextMenu.register(menuId)
 * 4. >> ContextMenu.toogle(false, menuId);
 */


class ContextMenu {

  /**
   * index menandakan child/level/hiarki context menu
   */
  activeId = [];
  collection;
  anchorNode; // berupa target node saat di #trig
  triggerTarget; // DOMElement

  constructor() {
    this.collection = {};
    // this.id = '';
    this.activeId = [];
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
          if (findAncestor(event.target, "*[context-menu]")) break;
        case 'FORM':
          if (findAncestor(event.target, "*[context-menu]")) break;
        // break;
        default:
          // this.toggle(false, this.id);
          this.toggle(false, this.activeId);
          break;
      }
    // }, true); // kalau false, menu tidak menutup otomatis
    },false); // update 7Sep2024 dibuat false, agar jika ada child context menu, maka tidak buble ke sini
    // window.ContextMenu = new Proxy(this,{}); // tidak perlu dibuat di window, karena di vue sudah di instance di app.js
  }

  /**
   * 
   * @param {String} menuId 
   * @param {Array} config contains object that keys are 'on' means event name and 'id' means trigger id. Eg: [{on:'pointerover',id:'foo'}]
   * @returns 
   */
  register(menuId, level = 0, config = []) {
    const menu = document.getElementById(menuId);
    if (!menu) return;

    this.collection[menuId] = {
      state: false,
      displayOnFalse: config.defaultDisplayOnFalse ?? 'none',
      displayOnTrue: config.defaultDisplayOnTrue ?? 'block',
    }

    menu.style.position = 'fixed';
    menu.style.zIndex = '100';

    if (config.length) {
      for (let i = 0; i < config.length; i++) {
        const triggerElement = document.getElementById(config[i].id);
        triggerElement.addEventListener(config[i].on, (e) => {
          e.stopPropagation();
          if (this.activeId[level]) this.turn(false, this.activeId[level]);
          this.activeId[level] = menuId;
          this.turn(true, menuId)
          this.setPositionMenu(e, menuId);
        },true);
        triggerElement.addEventListener('pointerleave', (e) => {
          e.stopPropagation();
          setTimeout(() => {
            document.addEventListener(config[i].on, (e) => {
              e.stopPropagation();
              if (!findAncestor(e.target, "#" + menuId)) this.turn(false, menuId);
            }, { once: true, capture:true});
          }, 10)
        },true);
      }
    } else {
      const area = menu.parentElement;
      area.setAttribute('cm-target-id', menuId);
      area.addEventListener('contextmenu', this.#trig.bind(this));
      this.activeId[level] = menuId;
    }
    return true;
  }

  turn(state, id) {
    if (this.collection[id].state != state) {
      this.collection[id].state = state;
      this.display(id);
    }
  }

  /**
   * jika state TRUE dan id EXIST maka akan mengOFFkan previous menu dan mengONkan current menu id
   * @param {Bool} state 
   * @param {String} id 
   */
  toggle(state = false, activeId = []) {
    if (state && activeId.length > 0) {
      // turnoff previous menu
      this.activeId.forEach(id => this.turn(!state, id));
      // turnon current menu
      this.activeId = activeId
      this.activeId.forEach(id => this.turn(state, id));
    } else {
      // kalau 'state' TRUE dan 'id' FALSE maka turn ON current this.id. 
      // kalau 'state' FALSE dan 'id' TRUE maka akan turn OFF the id.
      // kalau 'state' FALSE dam 'id' FALSE maka akan turn OFF current ID;
      if (activeId.length > 0) this.activeId = activeId;
      this.activeId.forEach(id => {
        if (this.collection[id]) {
          this.collection[id].state = state;
          this.display(id);
        };
      })
    }
    // sengaja dibuat pakai setTimeout agar setiap event terkait bisa dijalankan sebeleum document mentup context menu, contohnya fitur copy di helper;
    if (!state) setTimeout(() => this.anchorNode = '', 0);
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
    this.toggle(true, [id]);
    this.setPositionMenu(event, id);
    this.anchorNode = event.target;
  }

  // position the Context Menu in right position.
  setPositionMenu(e, id) {
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