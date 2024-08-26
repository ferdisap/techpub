import { EditorView, gutter, lineNumbers, GutterMarker, keymap } from "@codemirror/view";
import { defaultKeymap } from "@codemirror/commands";
import Randomstring from "randomstring";
import { isArray } from "../helper";
/**
 * src = https://github.com/mdn/web-components-examples/blob/main/edit-word/main.js
 */

class TextEditorElement extends HTMLElement {
  static formAssociated = true;
  editor = undefined;
  defaultLineType = 'html-entity';

  #to = 0;

  constructor() {
    super();
    this._internals = this.attachInternals();
    this.addEventListener('keyup', this._onKeyup.bind(this),true);
    setTimeout(()=>{
      if(this.hasAttribute('line-type')) this.defaultLineType = this.getAttribute('line-type');
      if(this.hasAttribute('name')) this.name = this.getAttribute('name'); // kalau pakai getter, attributenya akan hilang
      this.attachEditor(this.textContent); // harus pakai timeout karena pembuatan custom element tidak bisa ada children
    },0);
  }

  attachEditor(text = '') {
    this.editor = new EditorView({
      doc: text,
      extensions: [keymap.of(defaultKeymap), EditorView.lineWrapping, gutter({ class: "cm-mygutter" })],
      // extensions: [keymap.of(defaultKeymap), EditorView.lineWrapping, lineNumbers(), gutter({ class: "cm-mygutter" })],
      parent: this
    });
  }

  get value() {
    const text = this.getText(this.defaultLineType)
    // this._internals.setFormValue(text);
    return text;
  }

  set value(text) {
    this.changeText(text);
    this._internals.setFormValue(text);
    return text;
  }

  set name(v) {
    this.setAttribute('name', v);
  }

  get name(){
    return this.getAttribute('name');
  }

  _onKeyup(event){
    event.stopPropagation();
    event.preventDefault();
    clearTimeout(this.#to);
    this.#to = setTimeout(()=>{
      this._internals.setFormValue(this.getText(this.defaultLineType))
    },300)    
  }
  
  // src: https://stackoverflow.com/questions/8627902/how-to-add-a-new-line-in-textarea-element
  // src: https://stackoverflow.com/questions/15433188/what-is-the-difference-between-r-n-r-and-n
  // &#10; Line Feed and &#13; Carriage Return
  // \r = CR (Carriage Return) → Used as a new line character in Mac OS before X
  // \n = LF (Line Feed) → Used as a new line character in Unix/Mac OS X
  // \r\n = CR + LF → Used as a new line character in Windows
  // line yang dihasilkan dari editor adalah '\n' (unix)
  getText(lineType = '') {
    let line;
    switch (lineType) {
      case 'html-entity':
        line = '<br/>'
        break;
      case 'entity-code':
        line = '&#10;'
        break;
    }
    return line ? this.editor.state.doc.toString().replace(/\n/gm, line) : this.editor.state.doc.toString();
  }

  async changeText(text = '', from = 0, to = -1) {
    if(!this.editor) await (new Promise(r=>setTimeout(r.bind(this,true),0))); // menunggu editor di attach. Ketika di js docuemnt.createElement('text-editor'), editor tidak langsung di attach namun pakai timeout
    if (isArray(text)) text = text.join("\n");
    else if (!text && text != '') return;
    if (to < 0) to = this.getText().length; // harapannya jika to 0 maka akan nambah text di awal

    this.editor.dispatch({ changes: { from: from, to: to, insert: text } });
  }
};

export default TextEditorElement;