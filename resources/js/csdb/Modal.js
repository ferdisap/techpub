import { formDataToObject, isArray, isEmpty } from "./helper";
/**
 * HOW TO USE:
 * 1. construct new Modal() in your app;
 * 2. execute register(id) where id is Modal container id;
 * 3. execute start(referer) where the referer is element/its id container that contains attribute 'modal-input-refs';
 * 4. execute (finish() or cancel()) OR add attribute (`modal-button="ok"` and `modal-button="not_ok"`) to the two buttons inside modal;
 * 5. dont forget to add attribute:
 *    `modal-input-name="..."` inside modal to the input/textarea/select-option/else element;
 *    `modal-input-ref=${modal-input-name} outside modal as a reference;
 * 6. attribute 'modal-input-name' and 'modal-input-ref' should be suffixed with '[]' at its name to let display parsed by <br/>
 * 
 * replace() will use innerHTML of element@modal-input-ref for the value replacement, not textContent. So it does with fill()
 */
class Modal {


  id = ''; // current active id
  collection = {}; // collection data
  button = () => { };

  setId(id) {
    this.id = id;
  }

  register(id) {
    document.querySelectorAll(`#${id} *[modal-button]`).forEach(btn => {
      switch (btn.getAttribute("modal-button")) {
        case 'ok':
          btn.addEventListener('click', this.finish.bind(this), true) // kalau mau removeEvent, callback harus disimpan dalam sebuah variable fungsi / fungsi yang tidak bind. Jika capture ture, maka di removeEventnya juga true
          this.button(true);
          break;
        case 'not_ok':
          btn.addEventListener('click', this.cancel.bind(this), true) // kalau mau removeEvent, callback harus disimpan dalam sebuah variable fungsi / fungsi yang tidak bind. Jika capture ture, maka di removeEventnya juga true
          this.button(false);
          break;
      }
    })
    this.collection[id] = new Proxy({
      isShow: false,
    }, {
      // harus return bool, src = https://stackoverflow.com/questions/59187284/is-there-a-reason-why-this-proxy-always-throws-set-on-proxy-trap-returned-fal
      set: (target, key, value) => {
        if (key === 'isShow') {
          if (value) (document.getElementById(this.id).style.display = 'block');
          else {
            (document.getElementById(this.id).style.display = 'none');
            this.unsetCollection(1);
          }
        };
        target[key] = value;
        return true;
      }
    })
  }

  /**
   * processed config are: 1. manualUnset:boolean
   * @param {DOMElement} referer // jika tidak ada maka nanti tidak akan di replace
   * @param {string} id of modal container
   * @param {Object} config 
   * @returns 
   */
  start(referer, id, config = {}) {
    if (id) this.id = id;
    this.collection[this.id].config = config;

    // set referer
    if (referer && referer.nodeType === Node.ELEMENT_NODE) {
      this.referer = referer
      this.collection[this.id].referer = referer;
    };
    // else this.referer = document.getElementById(this.referer);

    const container = document.getElementById(this.id);
    container.display = 'block';

    // set collection data
    let resolve, reject;
    this.collection[this.id].data = new Promise((r, j) => {
      resolve = r; reject = j;
    });

    // set button
    this.button = (state) => {
      return state ? resolve(this.getValue(container)) : resolve(false);
    }

    // set isShow to display automatically by proxy
    this.collection[this.id].isShow = true;

    // fill
    this.fill();

    return this.collection[this.id];
  }

  finish() {
    this.button(true);
    this.replace()
      .finally(() => {
        this.collection[this.id].isShow = false;
      })
  }

  cancel() {
    this.button(false);
    this.collection[this.id].isShow = false;
  }

  getValue = (container) => {
    const data = {};
    container.querySelectorAll("*[modal-input-name]").forEach(el => {
      let key = el.getAttribute('modal-input-name');
      let value = el.value;
      if (key.substring(key.length - 2) === '[]') {
        key = key.substring(0, key.length - 2)
        value = value.split(/<br\/>|<br>|\n/g); // atau di replace dengan "\n" sesuai backend nya sekarang pakai array (split)
      };
      data[key] = value;
    });
    return data;
  }

  getShow() {
    return this.collection[this.id] ? this.collection[this.id].isShow : false;
  }

  getData() {
    return this.collection[this.id] ? this.collection[this.id].data : undefined;
  }

  /**
   * untuk mengisi data dari modal ke referer
   * @param {DOMElement} referer 
   * @param {String} id of referer 
   * @returns {Promise.resolve}
   */
  async replace(data, id) {
    if (!id) id = this.id;

    if(!this.collection[id].referer) return Promise.resolve(true);

    // jika sudah di replaced, tidak perlu di replaced lagi
    if (this.collection[id].replaced) return Promise.resolve(true);
    // get data
    if (!data) {
      if (!(this.collection[id].data && this.collection[id].referer)) return Promise.resolve(false);
      data = await this.getData();
    }
    // jika sudah di unset maka return false;
    if (!data || !this.collection[id].referer) return Promise.resolve(false);
    // jika semua isi data empty maka 
    if (Object.values(data).filter(v => !isEmpty(v)).length < 1) return Promise.resolve(false);
    // replacing data    
    this.collection[id].referer.querySelectorAll(`*[modal-input-ref]`).forEach(ref => {
      let refName = ref.getAttribute('modal-input-ref');
      let inner = data[refName] ? data[refName] : data[refName.substring(0, refName.length - 2)]; // jika >>> refName.substring(refName.length-2) === '[]' <<< maka ambil hilangkan '[]' dengan code >>> refName.substring(0,refName.length-2) <<<
      if (isArray(inner)) {
        inner = inner.filter(t => t);
        inner = inner.join(" <br/> ");
      }
      ref.innerHTML = inner ?? ''; // supaya tidak innerHTML-nya tidak 'undefined'
    });
    this.collection[id].replaced = true;
    return Promise.resolve(true);
  }

  /**
   * untuk mengisi data dari referer to modal
   */
  fill() {
    if(!this.collection[this.id].referer) return false;

    // getting text from referer
    const data = {};
    this.collection[this.id].referer.querySelectorAll(`*[modal-input-ref]`).forEach(ref => {
      let refName = ref.getAttribute('modal-input-ref');
      if (refName === 'remarks[]') {
        refName = refName.substring(0, refName.length - 2);
        data[refName] = ref.innerHTML.split(/<br\/>|<br>/);
      }
      else if (refName.substring(refName.length - 2) === '[]') {
        refName = refName.substring(0, refName.length - 2);
        data[refName] = new Array();
        data[refName].push(ref.innerHTML);
      } else {
        data[refName] = ref.innerHTML;
      }
    })
    // apply text to modal
    document.querySelectorAll(`#${this.id} *[modal-input-name]`).forEach(input => {
      // get data by inputName
      const inputName = input.getAttribute('modal-input-name');
      let v = data[inputName];
      if (!v) v = data[inputName.substring(0, inputName.length - 2)] // kalau ada '[]' diujung name maka dihilangkan

      // fill input
      if (!v) v = '';
      if (input.tagName === 'SELECT') {
        let option = Array.prototype.slice.call(document.querySelectorAll('option')).find(o => o.value === v);
        if (!option) {
          option = document.createElement('option');
          option.value = v;
          option.textContent = v;
        }
        option.selected = true;
      }
      else input.value = v;
      if (v) {
      }
    });

    return this.collection[this.id].filled = true;
  }

  /**
   * kosongin lagi data collectionnya agar tidak memory leaks
   * @params {mixed} jika 2 maka paksa unset, jika 1 maka tergantung config, jika 0 maka tergantung replace()
   */
  async unsetCollection(force = 1) {
    const unset = () => {
      delete this.collection[this.id].data;
      delete this.collection[this.id].referer;
      delete this.collection[this.id].config;
      delete this.collection[this.id].replaced;
      delete this.collection[this.id].filled;
    }
    switch (force) {
      case 2:
        unset();
        return Promise.resolve(true);
      case 1:
        if (this.collection[this.id].config && !this.collection[this.id].config.manualUnset) {
          unset();
          return Promise.resolve(true)
        };
        break;
      case 0:
        if (await this.replace()) {
          unset();
          return Promise.resolve(true);
        }
        break;
    }
    return Promise.resolve(false);
  }
}

export default Modal;