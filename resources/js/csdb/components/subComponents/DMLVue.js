import jp from 'jsonpath';
import Randomstring from 'randomstring';
import { findAncestor } from '../../helper';
import { useTechpubStore } from '../../../techpub/techpubStore';
import {
  fetchJsonFile,
  resolve_dmIdent, resolve_dmlIdent, resolve_pmIdent, resolve_commentIdent, resolve_infoEntityIdent,
  resolve_pmCode, resolve_dmCode, resolve_dmlCode, resolve_commentCode,
  resolve_issueInfo, resolve_issueDate, resolve_language
} from '../../S1000DHelper';
import axios from 'axios';

function inWork(json) {
  return json[1]['dml'][1]['identAndStatusSection'][0]['dmlAddress'][0]['dmlIdent'][1]['issueInfo'][0]['at_inWork'];
}
function issueDate(json) {
  let iss = jp.query(json, '$..dmlAddressItems..issueDate')[0];
  return iss.find(v => v['at_day'])['at_day'] + "/" +
    iss.find(v => v['at_month'])['at_month'] + "/" +
    iss.find(v => v['at_year'])['at_year'];
}
function securityClassification(json, path) {
  let sc;
  return (sc =
    ((sc = jp.query(json, path)[0]) ? sc.find(v => v['at_securityClassification']) : '')
  ) ? sc['at_securityClassification'] : '';
}
function remarks(json, path) {
  path += '..simplePara';
  let remarks = [];
  try {
    jp.query(json, path).forEach(v => remarks.push(v[0]));
  } catch (error) {
    console.error('cannot resolve remarks element.');
  }
  return remarks;
}
function dmlContent(json) {
  return jp.query(json, '$..dmlContent')[0]
}

function getAttributeValue(json, pathToElemen, attributeName) {
  let attrValue; (attrValue =
    ((attrValue = jp.query(json, pathToElemen)[0]) ? (
      ((attrValue = attrValue.find(v => v['at_' + attributeName])) ? attrValue['at_' + attributeName] : '')
    ) : '')
  )
  return attrValue;
};

// dmlEntry
function defaultTemplateEntry(entry = {}, trId = '', cbValue = '', no = '') {
  let cbId = Randomstring.generate({ charset: 'alphabetic' });
  if (!cbValue) cbValue = entry.ident ? entry.ident : '';
  const answer = entry.answer ? entry.answer.join("<br/>") : '';
  const rm = entry.remarks ? entry.remarks.join("<br/>") : '';
  let str = `
      <tr cb-room class="dmlEntry hover:bg-blue-300 cursor-pointer" ${trId ? 'id="' + trId + '"' : ''}>
        <td modal-input-ref="no">${no}</td>
        <td cb-window>
          <input file="true" id="${cbId}" value="${cbValue}" type="checkbox">
        </td>
        <td modal-input-ref="entryIdent" class="dmlEntry-ident">${entry.ident ?? ''}</td>
        <td>
          <span modal-input-ref="dmlEntryType" class="text-sm">${entry.dmlEntryType ?? ''}</span>
          <span> | </span>
          <span modal-input-ref="issueType" class="text-sm" >${entry.issueType ?? ''}</span>
        </td>
        <td modal-input-ref="securityClassification">${entry.securityClassification ?? ''}</td>
        <td>
          <span modal-input-ref="enterpriseName" class="text-sm">${entry.enterpriseName ?? ''}</span>   
          <span> | </span>
          <span modal-input-ref="enterpriseCode" class="text-sm italic">${entry.enterpriseCode ?? ''}</span></td>
        <td>
          <span modal-input-ref="answerToEntry" style="display:none">${entry.answerToEntry}</span>
          <div modal-input-ref="answer[]">${answer}</div>
        </td>
        <td modal-input-ref="remarks[]">${rm}</td>
      </tr>`;
  return str.replace(/\s{2,}/g, '');
  // <td modal-input-ref="remarks[]">aaa<br/>bbbb</td>
}
function createEntryData(DMLObject = {}) {
  let entryData = {};
  DMLObject.content.forEach(v => {
    let entry = DMLObject.getEntryData(v['dmlEntry']);
    let trId = Randomstring.generate({ charset: 'alphabetic' })
    entryData[trId] = {
      ident: entry.ident,
      issueType: entry.issueType,
      dmlEntryType: entry.dmlEntryType,
      securityClassification: entry.securityClassification,
      enterpriseName: entry.enterpriseName,
      enterpriseCode: entry.enterpriseCode,
      answer: entry.answer,
      answerToEntry: entry.answerToEntry,
      remarks: entry.remarks,
    };
  })
  return entryData;
}

function createEntryVueTemplate(entryData = {}) {
  let entryTemplate = '';
  Object.keys(entryData).forEach((trId, no) => {
    entryTemplate += defaultTemplateEntry(entryData[trId], trId, '', no + 1);
  })
  return entryTemplate;
}

function fetchDataFromRenderedEntries() {
  const data = [];
  $('.dmlEntry').each((i, v) => {
    data[i] = {};
    v = $(v);
    data[i].ident = v.find("*[name='entryIdent[]']").val();
    data[i].issueType = v.find("*[name='issueType[]']").val();
    data[i].dmlEntryType = v.find("*[name='dmlEntryType[]']").val();
    data[i].securityClassification = v.find("*[name='securityClassification[]']").val();
    data[i].enterpriseName = v.find("*[name='enterpriseName[]']").val();
    data[i].enterpriseCode = v.find("*[name='enterpriseCode[]']").val();
    data[i].remarks = [];
    $(v).find('.remarks textarea').each((n, v) => {
      if (v.value) data[i].remarks.push(v.value)
    });
  });
  return data;
}

async function showDMLContent(filename) {
  useTechpubStore().componentLoadingProgress[this.componentId] = true;
  fetchJsonFile({ filename: filename })
  .then(response => {
    if ((response.statusText === 'OK' || ((response.status >= 200) && (response.status < 300))) && response.data.json) {
      // handle or arrange json file
      this.DMLObject = DML(response.data.json);
      this.DMLType = response.data.model.dmlType;
  
      // create entries string
      const dmlEntryData = createEntryData(this.DMLObject);
      this.dmlEntryVueTemplate = createEntryVueTemplate(dmlEntryData);
  
      useTechpubStore().currentObjectModel = response.data.model;
      useTechpubStore().componentLoadingProgress[this.componentId] = false;
    }
  }).catch(e => useTechpubStore().componentLoadingProgress[this.componentId] = false);
}

async function showCSLContent(filename) {
  useTechpubStore().componentLoadingProgress[this.componentId] = true;
  fetchJsonFile({ filename: filename }, 'api.get_csl')
  .then(response => {
    if ((response.statusText === 'OK' || ((response.status >= 200) && (response.status < 300))) && response.data.csdb.object) {
      // handle or arrange json file
      this.DMLObject = DML(response.data.csdb.object.json);
      this.DMLType = response.data.csdb.object.dmlType;
  
      // create entries string
      const dmlEntryData = createEntryData(this.DMLObject);
      this.dmlEntryVueTemplate = createEntryVueTemplate(dmlEntryData);
    }
  }).catch(e => useTechpubStore().componentLoadingProgress[this.componentId] = false);
}


// hasilya sama
// pmRef1 = entry['dmlEntry'].find(v => v['pmRef'])['pmRef'];
// pmRef2 = jp.query(entry['dmlEntry'],'$..pmRef')[0]

const DML = function (json) {
  return {
    code: resolve_dmlCode(jp.query(json, '$..identAndStatusSection..dmlAddress..dmlIdent..dmlCode')[0], '').toUpperCase(),
    inWork: inWork(json),
    issueNumber: jp.query(json, '$..dmlIdent..issueInfo')[0].find(v => v['at_issueNumber'])['at_issueNumber'].toUpperCase(),
    issueDate: resolve_issueDate(jp.query(json, '$..dmlAddressItems..issueDate')[0]),
    securityClassification: securityClassification(json, '$..dmlStatus..security'),
    BREX: resolve_dmIdent(jp.query(json, '$..dmlStatus..brexDmRef..dmRefIdent')[0]),
    remarks: remarks(json, '$..dmlStatus..remarks'),
    content: dmlContent(json),

    getEntryData(dmlEntry) {
      let ident;
      if (ident = jp.query(dmlEntry, "$..pmRefIdent")[0]) {
        ident = resolve_pmIdent(ident);
      } else
        if (ident = jp.query(dmlEntry, "$..dmRefIdent")[0]) {
          ident = resolve_dmIdent(ident);
        } else
          if (ident = jp.query(dmlEntry, "$..dmlRefIdent")[0]) {
            ident = resolve_dmlIdent(ident);
          } else
            if (ident = jp.query(dmlEntry, "$..commentRefIdent")[0]) {
              ident = resolve_commentIdent(ident);
            } else
              if (ident = dmlEntry.find(v => v['at_infoEntityRefIdent'])) {
                ident = resolve_infoEntityIdent(ident['at_infoEntityRefIdent']);
              }

      let issueType; issueType = (issueType = dmlEntry.find(v => v['at_issueType'])) ? issueType['at_issueType'] : '';
      let entryType; entryType = (entryType = dmlEntry.find(v => v['at_entryType'])) ? entryType['at_entryType'] : '';

      const enterpriseName = jp.query(dmlEntry, '$..responsiblePartnerCompany..enterpriseName')[0][0];
      const enterpriseCode = getAttributeValue(dmlEntry, '$..responsiblePartnerCompany', 'enterpriseCode');

      const answerToEntry = getAttributeValue(dmlEntry, '$..answer', 'answerToEntry');
      return {
        ident: ident,
        issueType: issueType,
        dmlEntryType: entryType,
        securityClassification: securityClassification(dmlEntry, '$..security'),
        enterpriseName: enterpriseName,
        enterpriseCode: enterpriseCode,
        answer: remarks(dmlEntry, '$..answer..remarks'),
        answerToEntry: answerToEntry,
        // karena element <remarks> ada di dalam answer dan dmlEntry maka tidak bisa kasi path langsung seperti '$..remarks.simplePara' karena akan mengambil remarks parennya answer juga, padahal pengennya remarks yang parentnya dmlEntry
        remarks: remarks(dmlEntry.find(v => v['remarks']), '$..remarks'),
      }
    }
  }
}

async function addEntry(next = true, duplicate = false) {
  // create cloning element
  let etarget = this.ContextMenu.triggerTarget;
  let clonned;
  // jika saat contextmenu di klik maka akan cari yang ada cb-roomnya, lalu di clone
  if (etarget = findAncestor(etarget, "*[cb-room]")) {
    clonned = etarget.cloneNode(true);
    clonned.id = Randomstring.generate({ charset: 'alphabetic' });
    if (!duplicate) {
      clonned.querySelectorAll("*[modal-input-ref]").forEach(input => {
        input.innerHTML = '';
      });
    }

    const id = clonned.getAttribute("use-in-modal");
    const modal = this.Modal.start(clonned, id, { manualUnset: true });

    // wait the replace
    if (!(await this.Modal.replace(await modal.data, id))) clonned = false;

    // append clonned into DOM
    if (clonned) {
      next ? etarget.after(clonned) : etarget.before(clonned);
      this.CB.setCbRoomId(null, clonned, null);
    }
  } else {
    const template = document.createElement('tbody');
    template.innerHTML = defaultTemplateEntry();
    clonned = template.firstElementChild.cloneNode(true);
    clonned.setAttribute('use-in-modal', this.dmlEntryModalId);

    const modal = this.Modal.start(clonned, this.dmlEntryModalId, { manualUnset: true });

    // wait the replace
    if (!(await this.Modal.replace(await modal.data, this.dmlEntryModalId))) clonned = false;

    // append clonned into DOM
    if (clonned) {
      document.querySelector(`#${this.cbId} tbody`).appendChild(clonned);
      this.CB.setCbRoomId(null, clonned, null);
    }
  }

  // unset the Modal collection;
  this.Modal.unsetCollection(2);
}

function removeEntry() {
  let etarget = this.ContextMenu.triggerTarget;
  if (etarget = findAncestor(etarget, "*[cb-room]")) {
    etarget.remove();
  }
}

async function submitUpdate() {
  const values = this.getAllValues();
  values.filename = this.$route.params.filename;
  await axios({
    route: {
      name: 'api.dmlupdate',
      data: values,
    },
    useComponentLoadingProgress: this.componentId,
  });
}

async function submitMerge() {

  const data = {};
  data.filename = this.$route.params.filename;
  data.source = document.querySelector(`#${this.mergeId} *[merge-source-container]`).value;
  data.source = data.source.split(/",|\s"/gm).filter(v => v);
  
  let response = await axios({
    route: {
      name: 'api.dml_merge',
      data: data,
    },
    useComponentLoadingProgress: this.componentId,
  });
  if (response.statusText === 'OK') this.emitter.emit('createDMLFromEditorDML', response.data.csdb);
}

function getAllValues() {
  const values = {};
  const allNamed = this.$el.querySelectorAll('.dmlIdentAndStatusSection *[name]');
  const allEntries = this.$el.querySelectorAll('table tbody tr');
  values.ident = {};
  allNamed.forEach(el => {
    let key = el.name;
    let value = el.value;
    if (key.substring(key.length - 2) === '[]') {
      key = key.substring(0, key.length - 2)
      value = value.split(/<br\/>|<br>/g); // atau di replace dengan "\n" sesuai backend nya sekarang pakai array (split)
    };
    values.ident[key] = value;
  })
  values.entries = []
  allEntries.forEach((tr, i) => {
    const obj = {};
    tr.querySelectorAll("*[modal-input-ref]").forEach(input => {
      let key = input.getAttribute('modal-input-ref');
      let value = input.innerHTML;
      if (key.substring(key.length - 2) === '[]') {
        key = key.substring(0, key.length - 2)
        value = value.split(/<br\/>|<br>/g); // atau di replace dengan "\n" sesuai backend nya sekarang pakai array (split)
      };
      obj[key] = value;
    })
    values.entries.push(obj);
  })
  return values;
}

function editEntry(){
  let etarget = this.ContextMenu.triggerTarget;
  if (etarget = findAncestor(etarget, "*[use-in-modal]")) {
    this.Modal.start(etarget, etarget.getAttribute("use-in-modal"));
  }
}

export { showDMLContent, showCSLContent, addEntry, removeEntry, editEntry, submitUpdate, submitMerge, getAllValues };