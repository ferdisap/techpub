import Randomstring from "randomstring";
import { isArray } from "./helper";
/**
 * HOW TO USE
 * 1. attach attribute homeId to element, eg: table
 * 2. attach attribute 'cb-window' on td or parent of cb, umumnya digunakan untuk display on/off
 * 3. attach attribute 'cb-room' on tr or grandparent of cb
 * 4. dont forget to put value in the input checkbox
 * 5. Instance new Checkbox() with id that has been attached to the element / table
 * 6. run method register()
 * 7. run method pushSingle(event) atau push()
 */

const reg = function(element) {
  element.id = element.id ? element.id : Randomstring.generate({charset:'alphabetic'});
  element.oncontextmenu = this.setCbRoomId.bind(this); // dibuat ga pakai '@addEventListner' agar tidak multiple jika dilihat dari fungsi getEventListeners(node) di console. Kalaupun pakai '@addEventListener' dan fungsi callback nya ditaruh di luar, maka ketika calling 'this' akan merever ke elemen dom, bukan ke class Checkbox ini
  element.onclick = this.pushSingle.bind(this); // it will be checked if in selectedMode
  this.queryCbWindow(element).style.display = 'none';
}

class Checkbox{
  
  homeId;
  selectionMode;

  cbRoomId; // current cbRoomId
  cbRoomDisplay = 'flex'; // default display on true
  cbRoomBorder = '2px solid black'

  domObserver = undefined;
  cbRoomAdditionalAttribute = {}; // jika bermasalah, coba pakai Proxy karena bisa jadi checkboxnya sudah diregister terlebih dahulu sebelum props ini di set
  
  constructor(homeId, useObserver = true){
    this.homeId = homeId;
    this.selectionMode = false;

    if(useObserver){
      this.domObserver = new MutationObserver(()=>{
        this.register();
      });
      // ### jika tidak pakai table, maka config tambahkan subtree:true, supaya ke detect jika ada perubahan di descendant element
      // this.domObserver.observe(document.querySelector(this.CSSSelector_cbRoom(this.homeId)).parentElement,{childList:true})
      // this.domObserver.observe(document.getElementById(this.homeId),{childList:true})
      this.domObserver.observe(document.getElementById(this.homeId),{childList:true, subtree:true});
    }
  }

  /**
   * @param {Node} cbRooms 
   * @returns {undefined}
   */
  register(cbRoom = null){
    if(cbRoom && !isArray(cbRoom)){
      this.addAttributeOnCbRoom(cbRoom);
      (reg.bind(this))(cbRoom);
    } else {
      cbRoom = cbRoom ? cbRoom : document.querySelectorAll(`#${this.homeId} *[cb-room]`);
      cbRoom.forEach(r => {
        this.addAttributeOnCbRoom(r);
        (reg.bind(this))(r)
      });
    }
  }

  setCbRoomId(event, cbRoom, prev_cbRoom){
    // handle previous cbRoom
    if(prev_cbRoom || (prev_cbRoom = document.getElementById(this.cbRoomId))) prev_cbRoom.style.border = 'none';

    // handle current cbRoom
    cbRoom = cbRoom ? cbRoom : event.target.closest(`*[cb-room]`);
    cbRoom.style.border = this.cbRoomBorder;
    this.cbRoomId = cbRoom.id;
  }

  unsetCbRoomId(event, cbRoom){
    if(cbRoom || (cbRoom = document.getElementById(this.cbRoomId))) cbRoom.style.border = 'none';
    this.cbRoomId = '';
  }

  /**
   * digunakan untuk event click pada cbRoom element atau <tr>
   * @param {Event} event 
   */
  pushSingle(event){
    event.preventDefault();
    if(this.selectionMode){
      event.stopPropagation(); // jika tidak dalam selection mode maka single click filename/folder listTree tidak ngefek pindah route
      const cbRoom = this.queryCbRoom(event.target);
      const cbWindow = this.queryCbWindow(cbRoom);
      cbWindow.style.display = this.cbRoomDisplay;      
      const cbTarget = this.queryCbTarget(cbWindow);
      cbTarget.checked = !cbTarget.checked;
      this.setCbRoomId(event, cbRoom);
    } else {
      this.setCbRoomId(event);
    }
  }

  /**
   * digunakan untuk event click pada context menu
   */
  push(){
    if(this.cbRoomId){
      const cbRoom = document.getElementById(this.cbRoomId);
      const cbWindow = this.queryCbWindow(cbRoom);
      cbWindow.style.display = this.cbRoomDisplay;
      const cbTarget = this.queryCbTarget(cbWindow);
      cbTarget.checked = !cbTarget.checked;
    }
    this.openSelectionMode();
  }

  pushAll(state){
    const cbWindows = this.getCbWindows();
      cbWindows.forEach(w => {
        this.queryCbTarget(w).checked = state;
      });
    if(state) this.openSelectionMode(cbWindows);
    else this.closeSelectionMode(cbWindows);
    return cbWindows;
  }

  cancel(){
    this.closeSelectionMode(this.pushAll(false));
    this.unsetCbRoomId();
  }

  openSelectionMode(cbWindows = null){
    // cbWindows = cbWindows ? cbWindows : document.querySelectorAll(`#${this.homeId} *[cb-window]`);
    cbWindows = cbWindows ? cbWindows : this.getCbWindows();
    cbWindows.forEach(v => {
      v.style.display = this.cbRoomDisplay;
      // this.selectionMode = true;
    });
    this.selectionMode = true;
    return cbWindows;
  }
  
  closeSelectionMode(cbWindows = null){
    // cbWindows = cbWindows ? cbWindows : document.querySelectorAll(this.CSSSelector_cbWindows());
    cbWindows = cbWindows ? cbWindows : this.getCbWindows();
    cbWindows.forEach(v => {
      v.style.display = 'none';
      // this.selectionMode = false;
    });
    this.selectionMode = false;
    return cbWindows
  }

  /**
   * 1. Jika ada param cbRoomId, maka akan mengambil sesuai cbRooomId, atau 2.
   * 2. jika semua cbTarest tidak ada yang checked,  maka 3.
   * 3. smbil value yang cbRoomId sesuai dengan current
   * @param {String} cbRoomId 
   * @returns 
   */
  value(cbRoomId = ''){
    let cssSelector;
    if(cbRoomId) (cssSelector = this.CSSSelector_cbTargets(cbRoomId,'')); // to get value whether its checked or not
    else (cssSelector = this.CSSSelector_cbTargets()); // to get checked value

    let val = [];
    let inputs = document.querySelectorAll(cssSelector);
    // jika tidak ada input checked, maka akan mengambil input sesuai current cbRoomId, whether checked or not
    if(inputs.length > 0){
      inputs.forEach(c => {
        val.push(c.value);
      });
      return val;
    } else {
      inputs = this.getCbTarget();
      return [inputs.value];
    }
  }

  // additional function
  addAttributeOnCbRoom(cbRoom){
    Object.keys(this.cbRoomAdditionalAttribute).forEach(key => {
      cbRoom.setAttribute(key, this.cbRoomAdditionalAttribute[key]);
    })
  }

  // helper function
  getCbRoom(refId = ''){
    return document.querySelector(this.CSSSelector_cbRoom(refId));
  }
  getCbWindow(refId = ''){
    return document.querySelector(this.CSSSelector_cbWindow(refId));
  }
  getCbTarget(refId = '', checked = false){
    return document.querySelector(this.CSSSelector_cbTarget(refId, checked));
  }

  getCbRooms(){
    return document.querySelectorAll(this.CSSSelector_cbRooms());
  }
  getCbWindows(){
    return document.querySelectorAll(this.CSSSelector_cbWindows())
  }
  getCbTargets(){
    return document.querySelectorAll(this.CSSSelector_cbTargets());
  }

  CSSSelector_cbRoom(refId = ''){
    return `#${refId ? refId : (this.homeId + (this.cbRoomId ? ' #'+this.cbRoomId : ' *[cb-room]') )}`;
  }
  CSSSelector_cbWindow(refId = ''){
    return `#${refId ? refId : (this.homeId + (this.cbRoomId ? ' #'+this.cbRoomId : '') )} *[cb-window]`;
  }
  CSSSelector_cbTarget(refId = '', checked = true){
    return `#${refId ? refId : (this.homeId + (this.cbRoomId ? ' #'+this.cbRoomId : '') )} *[cb-window] input[type="checkbox"]`+(checked ? ':checked' : '');
  }

  CSSSelector_cbRooms(refId = ''){
    return '#'+(refId ? refId : this.homeId)+' *[cb-room]';
  }
  CSSSelector_cbWindows(refId = ''){
    return '#'+ (refId ? refId : this.homeId) +' *[cb-window]';
  }
  CSSSelector_cbTargets(refId = '', checked = true){
    return '#'+ (refId ? refId : this.homeId) +' *[cb-window] input[type="checkbox"]'+(checked ? ':checked' : '');
  }

  queryCbRoom(descendant){
    return descendant.closest('*[cb-room]');
  }
  queryCbWindow(ancestor){
    return ancestor.querySelector('*[cb-window]');
  }
  queryCbTarget(ancestor){
    return ancestor.querySelector('input[type="checkbox"]');
  }
}

export default Checkbox;