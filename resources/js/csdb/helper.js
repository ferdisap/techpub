const array_unique = (arr) => arr.filter((value, index, a) => a.indexOf(value) === index);

/**
 * sudah dicoba, hasilnya sama seperti di laravel request yang ubah fd ke array
 */
const formDataToObject = (v) => {
  const obj = {};
  v.forEach((value, key, fd) => {
    if (key.substr(key.length - 2) === '[]') {
      key = key.substr(0, key.length - 2);
      obj[key] = obj[key] ?? [];
      obj[key].push(value);
    }
    else obj[key] = value
  })
  return obj;
}

const objectToFormData = (v) => {
  const fd = new FormData;
  Object.keys(v).forEach(k => {
      if(Array.isArray(v[k])){
          fd.set(k+'[]', v[k]);
      } else {
          fd.set(k, v[k]);
      }
  });
  return fd;
}

/**
 * output = [A,...A] dimana A adalah [':bar', index: 18, input: 'asassas:fooa sasa :bar', groups: undefined]
 * @param {*} pattern 
 * @param {*} subject 
 * @returns {array} #0 match, #1 index, $2 input, #3 group 
 */
const findText = (pattern, subject) => {
  let match = [];
  let m;
  while ((m = pattern.exec(subject)) !== null) {
    if (m.index === pattern.lastIndex) {
      pattern.lastIndex++;
    }
    match.push(m);
  }
  return match;
}

/**
 * @param {Object} obj 
 * @param {string} key bisa berupa string dengan '.' sebagai hirarki
 * @returns {*}
 */
const getObjectValueFromString = (obj, key) => {
  if(!(isObject(obj) && !isEmpty(key))) return '';
  const i = key.indexOf(".")
  let v;
  if(i > 0){
    v = obj[key.substring(0,i)];
    v = getObjectValueFromString(v, key.substring(i+1));
  } else {
    v = obj[key];
  }
  return v;
}

const isObject = (v) => (v !== undefined) && (v !== null) && (v.constructor.name === 'Object') && (!Array.isArray(v));

const isString = (v) => (v !== undefined) && (v !== null) && (v.constructor.name === 'String');

const isNumber = (v) => (v !== undefined) && (v !== null) && (v.constructor.name === 'Number');

const isEmpty = (v) => (v == undefined) || (v == null) || (v == '') || ((v.length | Object.keys(v).length) < 1);

const isArray = (v) => (v !== undefined) && (v !== null) && (v.constructor.name === 'Array');

const isClassIntance = (v) => (v !== undefined) && (v !== null) && (v.constructor.name !== 'Object') && (v.constructor.name !== 'Array') && (v.constructor.name !== 'Function') && (v.constructor.name !== 'String') && (v.constructor.name !== 'Number');

const isFunction = (v) => (v !== undefined) && (v !== null) && (v.constructor.name === 'Function');

// DOM
/**
 * null jika selector adalah self element
 * @param {NodeElement} el 
 * @param {String} CSS selector 
 * @returns 
 */
const findAncestor = function (el, sel) {
  while ((el = el.parentElement) && !((el.matches || el.matchesSelector).call(el, sel)));
  return el;
}
const matchSel = function (el, sel) {
  return (el.matches || el.matchesSelector).call(el, sel);
}
const indexFromParent = function (el) {
  return Array.prototype.slice.call(el.parentElement.children).indexOf(el);
}

// event
function isArrowDownKeyPress(evt) {
  return (evt.keyCode === 40) ? true : false;
}
function isArrowUpKeyPress(evt) {
  return (evt.keyCode === 38) ? true : false;
}
function isEnterKeyPress(evt) {
  return (evt.keyCode === 13) ? true : false;
}
function isEscapeKeyPress(evt) {
  return (evt.which === 27) ? true : false;
}
function isLeftClick(evt) {
  return (evt.which === 1) ? true : false;
}
function isRightClick(evt) {
  return (evt.which === 3) ? true : false;
}
function isCharacterKeyPress(evt) {
  if (typeof evt.which == "undefined") {
    // This is IE, which only fires keypress events for printable keys
    return true;
  } else if (typeof evt.which == "number" && evt.which > 0) {
    // In other browsers except old versions of WebKit, evt.which is
    // only greater than zero if the keypress is a printable key.
    // We need to filter out backspace and ctrl/alt/meta key combinations
    // return !evt.ctrlKey && !evt.metaKey && !evt.altKey && evt.which != 8;
    // modifan saya
    return !evt.ctrlKey && !evt.metaKey && !evt.altKey && evt.which != 8 && (evt.which !== 9)
      && (evt.which !== 1) && (evt.which !== 2) && (evt.which !== 3) && (evt.which !== 27) && (evt.which !== 13) && (evt.which !== 37) && (evt.which !== 38) && (evt.which !== 39) && (evt.which !== 40);
  }
  return false;
}

/**
 * Urutan:
 * 1. jika ada text, maka text dicopy;
 * 2. jika ada event, maka pakai text didalam event target
 * 3. jika window selection type 'Range' maka pakai text dalam anchorNode nya
 * 4. jika ada ContextMenu dan ada anchorNode nya, pakai text dalam anchorNode nya
 * @param {*} event 
 * @param {*} text 
 * @returns 
 */
function copy(event, text) {
  if (text) {
    navigator.clipboard.writeText(text); // output promise
    return;
  }
  let a;
  const selection = window.getSelection();
  if (event) a = event.target;
  else if (selection.type === 'Range') a = selection.anchorNode;
  else if (this.ContextMenu && this.ContextMenu.anchorNode) a = this.ContextMenu.anchorNode;

  if (a) {
    const range = new Range();

    // Start range at second paragraph
    range.setStartBefore(a);

    // End range at third paragraph
    range.setEndAfter(a);

    // Add range to window selection
    selection.addRange(range);

    // tergantung window.isSecureContext. kalau php artisan serve, maka true, jika IIS false karena belum tau caranya
    navigator.clipboard.writeText(range.toString()); // output promise
  }
  return;
}

export {
  // general
  array_unique, formDataToObject, objectToFormData, findText, getObjectValueFromString, isObject, isNumber, isEmpty, isString, isArray, isClassIntance, isFunction,
  // DOM
  findAncestor, matchSel, indexFromParent,
  // event
  isArrowDownKeyPress, isArrowUpKeyPress, isEnterKeyPress, isEscapeKeyPress, isLeftClick, isRightClick, isCharacterKeyPress,
  // utilization
  copy
};