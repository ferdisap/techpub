import { isArray } from "./helper";
// import { TRUE } from "sass";

// const rcmcloseevent = new Event("rcm-close");
// const dispatch = () => document.dispatchEvent(rcmcloseevent);
// document.addEventListener("click", (e) => {
//   // let button = e.which || e.button;
//   // if (button === 1) {}
//   document.dispatchEvent(rcmcloseevent);
// });
// window.onkeyup = function(e){
//   if (e.keyCode === 27){
//     document.dispatchEvent(rcmcloseevent);
//   }
// }
// // useCapture = true berarti tidak buble, source: https://stackoverflow.com/questions/7398290/unable-to-understand-usecapture-parameter-in-addeventlistener
// document.addEventListener("click", dispatch, true); // dibuat fungsi agar tidak listener tidak multiple ditambah ke document

// const rcmcloseevent = new Event("rcm-close");
const turnOffAllRCM = (event) => {
  event.preventDefault;
  if(top.RCMCollection && top.RCMCollection.length){
    for (let i = 0; i < top.RCMCollection.length; i++) {
      top.RCMCollection[i].toggleOff();
    }
  }
}
// document.addEventListener('rcm-close', turnOffAllRCM);
document.onclick = turnOffAllRCM;

class RightClickMenu{
  id = '';
  name= undefined;
  state = 0;
  context = undefined;
  stopPropagation = false;

  static instantiate(id,name, contextMenu, area, stopPropagation = true){
    this.id = id;
    if(!isArray(top.RCMCollection)) top.RCMCollection = new Array;

    // jika RCM sudah pernah di instantiate maka tidak bisa di instantiate lagi
    let rcm;
    if(rcm = top.RCMCollection.find((rcm) => rcm.id === id)) return rcm;

    rcm = new this();

    rcm.name = name;
    rcm.context = contextMenu;
    contextMenu.style.display = 'none';
    area.oncontextmenu = (event) => {
      event.preventDefault();
      turnOffAllRCM(event);
      rcm.positionMenu();
      rcm.toggleOn();
    }
    // useCapture = true berarti tidak buble, source: https://stackoverflow.com/questions/7398290/unable-to-understand-usecapture-parameter-in-addeventlistener
    area.addEventListener('click', turnOffAllRCM, true);
    if(stopPropagation) rcm.context.onclick = (e) => e.stopPropagation();

    top.RCMCollection.push(new Proxy(rcm,{})); // harapannya pakai proxy agar tidak membebani memory
    return rcm;
  }

  toggleOn(){
    this.context.style.display = "block";
    return this.state = 1;
  }

  toggleOff(){
    this.context.style.display = "none";
    return this.state = 0;
  }

  // get the position of the right-click in window and returns the x and y coordinates
  getPosition(e){
    let posX = 0;
    let posY = 0;

    if(!e) var e = window.event;

    if(e.pageX || e.pageY){
      posX = e.pageX;
      posY = e.pageY;
    }
    else if(e.clientX || e.clientY){
      posX = e.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
      posY = e.clientY + document.body.scrollTop + document.documentElement.scrollTop;
    }

    return {
      x: posX,
      y: posY,
    };
  }

  // position the Context Menu in right position.
  positionMenu(e){
    let clickCoords = this.getPosition(e);
    let clickCoordsX = clickCoords.x;
    let clickCoordsY = clickCoords.y;

    let menuWidth = this.context.offsetWidth + 4;
    let menuHeight = this.context.offsetHeight + 4;

    let windowWidth = window.innerWidth;
    let windowHeight = window.innerHeight;

    if (windowWidth - clickCoordsX < menuWidth){
      this.context.style.left = windowWidth - menuWidth + "px";
    } else {
      this.context.style.left = clickCoordsX + "px";
    }

    if (windowHeight - clickCoordsY < menuHeight){
      this.context.style.top = windowHeight - menuHeight + "px";
    } else {
      this.context.style.top = clickCoordsY + "px";
    }
  }
}

export default RightClickMenu;

// /**
//  * 
//  * @returns Object menu
//  */
// function instanceMenu(name, contextMenu){
//   return {
//     name: name,
//     context: contextMenu,
//     state: 0,
//     activeClass: "block",
//     toggle: toggle,
//   }
// }

// export default instanceMenu;