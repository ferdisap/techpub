import Randomstring from "randomstring";
import { useTechpubStore } from "../techpub/techpubStore";
import axios from "axios";
import mitt from 'mitt';
import { array_unique } from "./helper";
import $ from 'jquery';

class CheckboxSelector{
  /**
   * Untuk ditaruh di parent checkbox input
   */
  id = ''; 

  /**
   * untuk menandai checkbox mana yang lagi di hover
   */
  cbHovered = ''; // checkbox input id;

  /**
   * Jika semua checkbox kondisi select all true semua
   */
  isSelectAll = false;

  /**
   * Untuk menandai jika view sedang selection mode
   */
  selectionMode = false;

  /**
   * untuk menampilkan RC menu atau menu trigger lainnya
   */
  isShowTriggerPanel = false;

  constructor(){
    this.id = Randomstring.generate({charset:'alphabetic'});
  }

  setCbHovered(id)
  {
    clearTimeout(this._toSetCbHovered);
    if(!this.isShowTriggerPanel) this.cbHovered = id;
    this._toSetCbHovered = setTimeout(()=> (!this.isShowTriggerPanel) ? (this.cbHovered = '') : null ,1000);
  }

  setCbHoveredByEventTarget(event, attributeName, prefixCheckboxId = '' , parentId = null){
    clearTimeout(this.to_setCbHovered);
    this.to_setCbHovered = setTimeout(()=>{
      let cbid = prefixCheckboxId + $(event.target).parents(parentId ?? 'tr')[0].getAttribute(attributeName);
      return this.setCbHovered(cbid);
    },100)
  }

  select(cbid = ''){
    if(!cbid) cbid = this.cbHovered;
    this.isSelectAll = false;
    this.selectionMode = true;
    this.isShowTriggerPanel = false
    setTimeout(()=>{
      let input = document.getElementById(cbid);
      input.checked = !input.checked
    },10);
  }

  selectByEventTarget(event, attributeName, prefixCheckboxId = '' , parentId = null){
    let cbid = prefixCheckboxId + $(event.target).parents(parentId ?? 'tr')[0].getAttribute(attributeName);
    console.log(cbid);
    return this.select(cbid);
  }

  copy(text){
    return navigator.clipboard.writeText(text);
  }

  selectAll(isSelect = true, cssInputSelector = ''){
    this.selectionMode = true;
    this.isShowTriggerPanel = false;
    if(!cssInputSelector) cssInputSelector = "#"+this.id+" input[type='checkbox']";
    setTimeout(()=>document.querySelectorAll(cssInputSelector).forEach((input) => input.checked = isSelect),0)
    this.isSelectAll = isSelect;
  }

  cancel(){
    this.selectAll(false);
    this.selectionMode = false;
  }

  /**
   * 
   * @param {boolean} isSelect 
   * @param {string} cssInputSelector 
   * @returns {array} contain string input value
   */
  getAllSelectionValue(isSelect = true, cssInputSelector = ''){
    let values = new Array;
    if(!cssInputSelector) {
      cssInputSelector = isSelect ? "#"+this.id+" input[type='checkbox']:checked" : "#"+this.id+" input[type='checkbox']:not(:checked)"
    };
    document.querySelectorAll(cssInputSelector).forEach((inputEl)=> {
      if(inputEl.checked = isSelect){
        values.push(inputEl.value);
      }
    });
    return values;
  }

  /**
   * 
   * @param {boolean} isSelect 
   * @param {string} cssInputSelector 
   * @returns {NodeList} contain string input value
   */
  getAllSelectionElement(isSelect = true, cssInputSelector = ''){
    if(!cssInputSelector) {
      cssInputSelector = isSelect ? "#"+this.id+" input[type='checkbox']:checked" : "#"+this.id+" input[type='checkbox']:not(:checked)"
    };
    return document.querySelectorAll(cssInputSelector);
  }
  
}

class CsdbObjectCheckboxSelector extends CheckboxSelector {

  context;

  constructor(component){
    super();
    this.context = component; // berupa Proxy ComponentVue
  }

  /**
   * @returns {object} resolve config.data
   */
  async fetch(routeName, config, callback){
    let resolve,reject;
    const prom = new Promise((r,j) => {
      resolve = r; reject = j;
    })

    const route = useTechpubStore().getWebRoute(routeName,config.data);
    const response = await axios({
      url: route.url,
      method: route.method[0],
      data: route.params,
    });

    if(response.statusText === 'OK'){
      (callback.bind(this))();
      resolve(config.data);
    } else {
      reject(false);
    }
    return prom;
  }

  /**
   * masih terbatas pada checkbox value, yakni filename atau path
   */
  copy(){
    try {
      super.copy(document.getElementById(this.cbHovered).value);
      console.log("copy success");
    } catch (error) {
      console.error("copy fails");
    }
    this.isShowTriggerPanel = false;
  }
  
  /**
   * makesure value checkbox is filename to put to server
   * @return {Promise} berisi string filename yang di change path 
   */
  async changePath(event, config, callback){
    if(!(config)){
      config = {};
      config.data = new FormData(event.target);
    }

    let value = this.selectionMode ? this.getAllSelectionValue() : [document.getElementById(this.cbHovered).value];
    if(config.data instanceof FormData) config.data.set('filename', value); // payload = 'xxx,yyy,zzz'
    else config.data.filename = value.join(","); // payload = 'xxx,yyy,zzz';

    return await this.fetch(config.routeName ? config.routeName : 'api.change_object_path', config, callback);
  }

  async delete(callback){
    let value = this.selectionMode ? this.getAllSelectionValue() : [document.getElementById(this.cbHovered).value];
    return await this.fetch('api.delete_objects', {data:{filename: value}}, callback);
  }

  async permanentDelete(callback){
    let value = this.selectionMode ? this.getAllSelectionValue() : [document.getElementById(this.cbHovered).value];
    return await this.fetch('api.permanentdelete_object', {data:{filename: value}}, callback);
  }

  async restore(callback){
    let value = this.selectionMode ? this.getAllSelectionValue() : [document.getElementById(this.cbHovered).value];
    return await this.fetch('api.restore_object', {data:{filename: value}}, callback);
  }

  /**
   * DEPRECIATED, nanti dipindah ke Folder.js
   * @returns {Promise} contain csdbs
   */
  async getCsdbFilenameFromFolderVue(context){
    let resolve,reject;
    const prom = new Promise((r,j) => {
      resolve = r; reject = j;
    })

    let csdbs = [];
    let paths = [];
    let o = undefined;
    let values = []; // bisa berupa filename atau path
    if(this.selectionMode) values = this.getAllSelectionValue(true);
    else values = [document.getElementById(this.cbHovered).value];
    values.forEach((v) => {
      if(o = context.data.folders.find((path) => path === v)) paths.push(o);
      else if(o = context.data.csdb.find((obj) => obj.filename === v)) csdbs.push(o.filename);
    })

    if(paths.length > 0){
      // here for request to server, pakai this.getCsdbModelsFromStringPath
      // await response for csdb and it must be appended to csdbs
      const route = useTechpubStore().getWebRoute('api.get_object_csdbs',{sc: "path::"+paths.join(",")});
      const response = await axios({
        url:route.url,
        method: route.method[0],
        data: route.params,
      });
      if(response.statusText === 'OK'){
        csdbs = array_unique(csdbs.concat(response.data.csdbs));
      }
    }
    
    if(csdbs.length > 0) resolve(csdbs) 
    else reject(false);
    return prom;
  }

  /**
   * * DEPRECIATED, nanti dipindah ke Folder.js
   * @returns {Promise} contains csdbs
   */
  async getCsdbModelsFromStringPath(paths = []){

  }
}

export {CheckboxSelector, CsdbObjectCheckboxSelector};