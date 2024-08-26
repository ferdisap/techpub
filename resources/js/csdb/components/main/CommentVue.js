import axios from 'axios';

function arrangeComments(coms) {
  const comTypeQ = [];
  const comTypeI = [];
  const comTypeR = [];
  coms.map(com => {
    switch (com.commentType) {
      case 'q': comTypeQ.push(com); break;
      case 'i': comTypeI.push(com); break;
      case 'r': comTypeR.push(com); break;
    }
  })
  comTypeI.map(comI => {
    const parent = comTypeQ.find(comQ => {
      const threeDigitComI = comI.seqNumber.substring(0, 3);
      return comQ.modelIdentCode === comI.modelIdentCode &&
        comQ.senderIdent === comI.senderIdent &&
        comQ.commentType === 'q' &&
        (comQ.seqNumber.substring(0, 3) === threeDigitComI);
    });
    parent.children = (parent.children ? parent.children : []);
    parent.children.push(comI);
  });
  comTypeR.map(comI => {
    const parent = comTypeQ.find(comQ => {
      const threeDigitComI = comI.seqNumber.substring(0, 3);
      return comQ.modelIdentCode === comI.modelIdentCode &&
        comQ.senderIdent === comI.senderIdent &&
        comQ.commentType === 'q' &&
        (comQ.seqNumber.substring(0, 3) === threeDigitComI);
    });
    parent.children = (parent.children ? parent.children : []);
    parent.children.push(comI);
  });
  return comTypeQ;
}

function htmlString(coms) {
  // ${comment.commentType === 'q' ? 'ml-1' : 'ml-3'}"
  const makeTemplate = (comment) => {
    let str = `<div cb-room class="list type-${comment.commentType}">`

    str += `<div cb-window><input type="checkbox" value="${comment.csdb.filename}"></input></div>`

    str += `<div>`;
    str += `<div comment-type="${comment.commentType}">`;
    // add comment sender last name
    str += `<h6>${comment.csdb.initiator.email}<span class="date"> ${comment.csdb.last_history.created_at}</span></h6>`

    str += `<div class="com-filename">${comment.csdb.filename}</div>`

    // add comment para
    str += `<div class="para-container">`;
    comment.commentContent.forEach(content => {
      str += `<p class="simple-para">` + content + `</p>`;
    })
    str += `</div>`;
    str += `</div>`;

    // add button reply
    // str += `<div @click="createEditor($event)" class="reply">reply</div>`;

    // add children
    if (comment.children) {
      comment.children.forEach(commentChild => {
        str += makeTemplate(commentChild);
      });
    }

    // add text-editor
    // will be added by Comment.vue

    str += `</div>`;
    str += `</div>`;
    return str;
  }

  const l = coms.length;
  let template = '';
  for (let i = 0; i < l; i++) {
    template += makeTemplate(coms[i]);
  }
  return template;
}


function fetch(csdbFilename, force = true) {
  if ((this.comments.csdbFilename === csdbFilename) && !(force)) return;
  axios({
    route: {
      name: 'api.get_csdb_comments',
      data: { filename: csdbFilename },
    }
  }).then(response => {
    const com = arrangeComments(response.data.comments);
    this.comments.template = htmlString(com);
    this.comments.csdbFilename = csdbFilename;
  });
}

async function submit(event) {
  // get data from modal id
  const data = this.Modal.getValue(document.getElementById(this.comments.modalId));

  // append text-editor value to data
  const textEditor = document.querySelector(`#${this.comments.cbHomeId} text-editor`);
  if(!textEditor) return;
  data[textEditor.name] = textEditor.value;

  axios({
    route: {
      name: 'api.create_comment',
      data: data,
    }
  })
  .then(rsp => {
    if(rsp.statusText === 'OK'){
      // do something
      this.fetch(this.$props.csdbFilename);
      this.emitter.emit('CreateCOMFromPreviewComment', rsp.data.csdb);
      this.comments.CB.cancel();
    } else {
      this.emitter.emit('Modal-show', {modalId: this.comments.modalId});
    }
  })
}

export { fetch, submit };