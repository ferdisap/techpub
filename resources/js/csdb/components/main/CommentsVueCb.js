import Checkbox from "../../Checkbox";
import Randomstring from "randomstring";

class CommentVueCb extends Checkbox{

  // latestCbRoomQueried = [];
  containerEditorId = '';

  setCbRoomId(event, cbRoom, prev_cbRoom){
    // handle previous cbRoom
    if(prev_cbRoom || (prev_cbRoom = document.getElementById(this.cbRoomId))) {
      if(prev_cbRoom.parentElement.id === this.homeId) prev_cbRoom.querySelector("*[comment-type]").style.border = 'none';
      else prev_cbRoom.style.border = 'none';
      // if(prev_cbRoom.tagName === 'DETAILS') prev_cbRoom.firstElementChild.style.border = 'none'; // set supaya summary yang di unborder
      // else prev_cbRoom.style.border = 'none';
    }

    // handle current cbRoom
    cbRoom = cbRoom ? cbRoom : event.target.closest(`*[cb-room]`);
    if(cbRoom.parentElement.id === this.homeId) cbRoom.querySelector("*[comment-type]").style.border = this.cbRoomBorder;
    else cbRoom.style.border = this.cbRoomBorder;
    this.cbRoomId = cbRoom.id;
  }

  /**
   * Sengaja dibuat lagi karena yang di parent ada preventDefault. fungsi ini kan di jalankan ketika ada event click pada cbRoom atau childnya. oleh karena itu ini untuk menghilangkan preventDefault nya saja. Lagian pushSingle tidak dipakai di Checkbox comment ini
   * @param {*} event 
   */
  pushSingle(event){}

  reply(modalId){
    let containerEditor;
    const cbRoom = this.getCbRoom();
    if (cbRoom && this.containerEditorId && (containerEditor = document.getElementById(this.containerEditorId))) {
      cbRoom.appendChild(containerEditor);
    } else {
      this.createEditor(cbRoom);
    }

    // ngosongin input yang ada di modal
    document.querySelectorAll(`#${modalId} *[name]`).forEach(input => {
      switch (input.name) {
        case 'parentCommentFilename':
          const ancestorOrSelf = cbRoom.closest("*[cb-room].type-q");
          input.value = (ancestorOrSelf ? ancestorOrSelf.querySelector('input[type="checkbox"]').value : '') ;
          break;
        case 'position': 
          const type = (cbRoom.closest("*[cb-room].type-q") ? 'i' : 'q');
          let index;
          switch (type) {
            case 'q': index = [...cbRoom.querySelectorAll("*[cb-room].type-i, *[cb-room].type-r")].length + 1;break;          
            default: index = [...cbRoom.parentElement.querySelectorAll("*[cb-room].type-i, *[cb-room].type-r")].length; break;
          }
          input.value = index;break;
        case 'commentType': input.value = 'i';break;
        case 'commentPriorityCode':input.value = input.firstElementChild.value; break;
        case 'responseType': input.value = input.firstElementChild.value; break;
        case 'remarks': input.value = ''; break;
      }
    })
  }
  
  createNewComment(modalId){
    const cbRoom = document.getElementById(this.homeId);
    this.createEditor(cbRoom);
    
    // ngosongin input yang ada di modal
    document.querySelectorAll(`#${modalId} *[name]`).forEach(input => {
      switch (input.name) {
        case 'parentCommentFilename': input.value = '';break;
        case 'position': input.value = [...cbRoom.querySelectorAll("*[cb-room].type-q")].length + 1; break;
        case 'commentType': input.value = 'q';break;
        case 'commentPriorityCode':input.value = input.firstElementChild.value; break;
        case 'responseType':input.value = input.firstElementChild.value; break;
        case 'remarks':input.value = ''; break;
      }
    });
  }

  cancel(){
    document.getElementById(this.containerEditorId).remove();
    this.containerEditorId = '';
  }

  createEditor(cbRoom = null){
    // if(!cbRoom && !(this.containerEditorId)) cbRoom = document.getElementById(this.homeId);
    if(!cbRoom){
      cbRoom = document.getElementById(this.homeId);
    } 
    let div;
    if(!(this.containerEditorId)){
      div = document.createElement('div');
      this.containerEditorId = Randomstring.generate({ 'charset': 'alphabetic' });
      div.id = this.containerEditorId;
      div.setAttribute('class', 'editor-container');      
  
      div.innerHTML = `<text-editor name="commentContentSimplePara"></text-editor>
      <button id="sendBtn" type="submit" class="material-icons send">send</button>`;
    } else {
      div = document.getElementById(this.containerEditorId);
    }
    cbRoom.appendChild(div);
  }
}
export default CommentVueCb;