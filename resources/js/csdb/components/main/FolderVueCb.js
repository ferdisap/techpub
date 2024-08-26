import Checkbox from "../../Checkbox";
import { array_unique } from "../../helper";
import { useTechpubStore } from "../../../techpub/techpubStore";

// let to_reg = 0; // dibuat diluar. karena kalau didalam klas, akan muncul error di vue '...maximum exceed...'

class FolderVueCb extends Checkbox {

  // to_register = 0;

  constructor(homeId){
    super(homeId, false);

    this.domObserver = new MutationObserver((mutationList)=>{
      for (let i = 0; i < mutationList.length; i++) {
        if(mutationList[i].addedNodes.length > 0){
          for (let ii = 0; ii < mutationList[i].addedNodes.length; ii++) {
            if(mutationList[i].addedNodes[ii].attributes.getNamedItem('cb-room')){ 
              this.register(mutationList[i].addedNodes[ii]);
            }
          }
        };
      }
    });
    // ### jika tidak pakai table, maka config tambahkan subtree:true, supaya ke detect jika ada perubahan di descendant element
    this.domObserver.observe(document.querySelector('#'+homeId+' tbody'),{childList:true})
  }

  /**
   * jika folder di checked, maka akan mengambil seluruh csdb filename didalamnya
   * @returns {Array}
   */
  async value() {
    let paths = [];
    let val = [];
    // handle when in selectionMode, ambil semua checked value
    if (this.selectionMode) {
      // document.querySelectorAll(this.CSSSelector_cbTargets()).forEach(c => {
      this.getCbTargets().forEach(c => {
        if (c.closest('*[cb-room="folder"]')) paths.push(c.value);
        else val.push(c.value);
      });
      if (paths.length) {
        (await this.fetchCsdbsFromPath(paths)).forEach(csdb => {
          val.push(csdb.filename);
        });
      }
      val = array_unique(val);
    }
    // handle when not in selectionMode, ambil value meski tidak checked
    else {
      if((paths = this.getCbRoom()).getAttribute('cb-room') === 'folder'){
        (await this.fetchCsdbsFromPath([this.queryCbTarget(paths).value])).forEach(csdb => {
          val.push(csdb.filename);
        });
      }
      else {
        val.push(this.getCbTarget().value);
      }
    }
    return Promise.resolve(val);
  }

  async fetchCsdbsFromPath(paths) {
    const route = useTechpubStore().getWebRoute('api.get_object_csdbs', { sc: "path::" + paths.join(",") });
    return new Promise(async (resolve) => {
      const response = await axios({
        url: route.url,
        method: route.method[0],
        data: route.params,
      });
      if (response.statusText === 'OK') return resolve(response.data.csdbs);
      else resolve([]);
    })
  }
}

export default FolderVueCb;