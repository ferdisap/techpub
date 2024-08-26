import Checkbox from "../../Checkbox";

class DMLVueCb extends Checkbox {

  /**
   * sama seperti di FolderVueCb.js
   * @param {String} homeId 
   */
  constructor(homeId){
    super(homeId, false);

    this.domObserver = new MutationObserver(()=>{
      this.register();
    });
    // ### jika tidak pakai table, maka config tambahkan subtree:true, supaya ke detect jika ada perubahan di descendant element
    this.domObserver.observe(document.querySelector('#'+homeId+' tbody'),{childList:true})
  }
}

export default DMLVueCb;