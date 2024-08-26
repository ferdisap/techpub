/**
 * Untuk memindahkan index elemen array. Ini bisa dipakai oleh fungsi lain.
 * didapat dari stackoverflow.com
 * @return {Array}
 */
const array_move = function (arr, old_index, new_index) {
  if (new_index >= arr.length) {
    let k = new_index - arr.length + 1;
    while (k--) {
      arr.push(undefined);
    }
  }
  arr.splice(new_index, 0, arr.splice(old_index, 1)[0]);
  return arr;
};

/**
 * Untuk sort Object by Its Key. If The keys contain '__' in first string, it will be in first index;
 * @param {Object} container 
 * @param {Number} asc 
 * @returns 
 */
const sorter = function (container, asc = 1) {
  let arr = Object.keys(container).sort(); // ascending;
  if (!asc) {
    arr = arr.sort(() => -1); // descending
  }
  arr = arr.sort((a, b) => {
    if (b.substring(0, 2) === '__') {
      return asc ? (b > a ? -1 : 1) : (b > a ? 1 : -1);
    } else {
      return asc ? (b > a ? 1 : -1) : (b > a ? -1 : 1)
    }
  });
  arr.forEach((e, i) => {
    if (e.substring(0, 2) === '__') {
      arr = array_move(arr, i, 0);
    }
  });
  return arr.reduce((obj, key) => {
    obj[key] = container[key];
    return obj;
  }, {});
};

/**
 * khusus untuk model SQL data, untuk menemukan jenis type apa sebuah object
 * @return {String} dmrl, csl, brex, brdp, dm, pm, icn
 */
const objectType = function (model = {}) {
  if (model.remarks && model.remarks.ident) {
    let prefix = model.remarks.ident.prefix.toUpperCase();
    // nanti prefix CSL tidak dipakai lagi
    if (prefix === 'DML-' || prefix === 'CSL') {
      let dmlType = model.remarks.ident.dmlCode.dmlType.toUpperCase();
      return (dmlType === 'P' || dmlType === 'C') ? 'dmrl' : (dmlType === 'S' ? 'csl' : '');
    }
    else if (prefix === 'DMC-') {
      let infoCode = model.remarks.ident.dmCode.infoCode.toUpperCase();
      return (infoCode === '022') ? 'brex' : (infoCode === '024' ? 'brdp' : 'dm')
    }
    else if (prefix === 'ICN-') {
      return 'icn';
    }
    else if (prefix === 'PMC') {
      return 'pm';
    }
  }
  return ''
}

/**
 * ini diperlukan untuk memindahkan setiap object ke dalam sebuah object yang keynya (path) adalah path SQL column
 * tapi sepertinya tidak dipakai karena berat jika SQL ribuan (lebih dari 10000 sudah di coba dan sangat lama)
 * @param {Object} container
 * @param {Array} path contais sqeuential array that object will be put in nested by the element array
 * @param {Object} source file
 * @param {Callback} generally same function (recursive)
 */
const append = function (container, path, csdbObject, callback = append) {
  let loop = 0;
  let maxloop = path.length;
  let folder = "__" + path[loop];
  while (loop < maxloop) {
    container[folder] = container[folder] || {};
    if (path[loop + 1]) {
      path = path.slice(1);
      container[folder] = callback(container[folder], path, csdbObject, callback);
      return container;
    }
    loop++;
  }
  let containerLength = Object.keys(container[folder]).filter(e => parseInt(e.slice(0, -1)) || parseInt(e.slice(0, -1)) === 0 ? true : false).length;
  container[folder][containerLength + "_"] = csdbObject; // ditamba '_' agar bisa di sort
  container = sorter(container, 0);
  return container;
}

/**
 * ini diperlukan untuk memindahkan setiap object ke dalam sebuah object yang keynya adalah path SQL column
 */
// const append = function (container, path, csdbObject, callback) {
//   let loop = 0;
//   let maxloop = path.length;
//   let folder = "__" + path[loop];
//   while (loop < maxloop) {
//     container[folder] = container[folder] || {};
//     if (path[loop + 1]) {
//       path = path.slice(1);
//       container[folder] = callback(container[folder], path, csdbObject, callback);
//       return container;
//     }
//     loop++;
//   }
//   let containerLength = Object.keys(container[folder]).filter(e => parseInt(e.slice(0, -1)) || parseInt(e.slice(0, -1)) === 0 ? true : false).length;
//   container[folder][containerLength + "_"] = csdbObject; // ditamba '_' agar bisa di sort
//   container = sorter(container, 0);
//   return container;
// }
// let newobj = {};
// for (const i in response.data.data) {
//   let obj = response.data.data[i];
//   let path = obj.path.split("/");
//   newobj = append(newobj, path, obj, append);
//   delete response.data.data[i];
// }
// this.data[`${type}_list`] = newobj;
// delete (response.data.data);
// delete (response.data.message);
// this.paginationInfo = response.data;

export { array_move, sorter, objectType };