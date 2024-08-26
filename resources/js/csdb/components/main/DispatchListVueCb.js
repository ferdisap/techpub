import Checkbox from "../../Checkbox";

class DispatchListVueCb extends Checkbox {
  constructor(homeId) {
    super(homeId, false);

    this.domObserver = new MutationObserver((mutationList) => {
      for (let i = 0; i < mutationList.length; i++) {
        if (mutationList[i].addedNodes.length > 0) {
          for (let ii = 0; ii < mutationList[i].addedNodes.length; ii++) {
            if (mutationList[i].addedNodes[ii].attributes.getNamedItem('cb-room')) {
              this.register(mutationList[i].addedNodes[ii]);
            }
          }
        };
      }
    });
    // ### jika tidak pakai table, maka config tambahkan subtree:true, supaya ke detect jika ada perubahan di descendant element
    this.domObserver.observe(document.querySelector('#' + homeId + ' tbody'), { childList: true })
  }
}

export default DispatchListVueCb;