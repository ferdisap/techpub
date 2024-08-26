import jp from 'jsonpath';
import axios from 'axios';

async function fetchJsonFile(data = {}, routename){
  return await axios({
    route: {
      name: routename ?? 'api.read_json',
      data: data
    },
  });
}

function jsonFileGetRemarks(json, path) {
  path += '..simplePara';
  let remarks = [];
  try {    
    jp.query(json, path).forEach(v => remarks.push(v[0]));
  } catch (error) {
    console.error('cannot resolve remarks element.');
  }
  return remarks;
}

// code
function resolve_pmCode(pmCode, prefix = 'PMC-') {
  if(!pmCode) return '';
  let modelIdentCode; modelIdentCode = (modelIdentCode = pmCode.find(v => v['at_modelIdentCode'])) ? modelIdentCode['at_modelIdentCode'] : '';
  let pmIssuer; pmIssuer = (pmIssuer = pmCode.find(v => v['at_pmIssuer'])) ? pmIssuer['at_pmIssuer'] : '';
  let pmNumber; pmNumber = (pmNumber = pmCode.find(v => v['at_pmNumber'])) ? pmNumber['at_pmNumber'] : '';
  let pmVolume; pmVolume = (pmVolume = pmCode.find(v => v['at_pmVolume'])) ? pmVolume['at_pmVolume'] : '';
  return prefix+modelIdentCode+'-'+pmIssuer+'-'+pmNumber+'-'+pmVolume;
}
function resolve_dmCode(dmCode, prefix = 'DMC-'){
  if(!dmCode) return '';
  let modelIdentCode; modelIdentCode = (modelIdentCode = dmCode.find(v => v['at_modelIdentCode'])) ? modelIdentCode['at_modelIdentCode'] : '';
  let systemDiffCode; systemDiffCode = (systemDiffCode = dmCode.find(v => v['at_systemDiffCode'])) ? systemDiffCode['at_systemDiffCode'] : '';
  let systemCode; systemCode = (systemCode = dmCode.find(v => v['at_systemCode'])) ? systemCode['at_systemCode'] : '';
  let subSystemCode; subSystemCode = (subSystemCode = dmCode.find(v => v['at_subSystemCode'])) ? subSystemCode['at_subSystemCode'] : '';
  let subSubSystemCode; subSubSystemCode = (subSubSystemCode = dmCode.find(v => v['at_subSubSystemCode'])) ? subSubSystemCode['at_subSubSystemCode'] : '';
  let assyCode; assyCode = (assyCode = dmCode.find(v => v['at_assyCode'])) ? assyCode['at_assyCode'] : '';
  let disassyCode; disassyCode = (disassyCode = dmCode.find(v => v['at_disassyCode'])) ? disassyCode['at_disassyCode'] : '';
  let disassyCodeVariant; disassyCodeVariant = (disassyCodeVariant = dmCode.find(v => v['at_disassyCodeVariant'])) ? disassyCodeVariant['at_disassyCodeVariant'] : '';
  let infoCode; infoCode = (infoCode = dmCode.find(v => v['at_infoCode'])) ? infoCode['at_infoCode'] : '';
  let infoCodeVariant; infoCodeVariant = (infoCodeVariant = dmCode.find(v => v['at_infoCodeVariant'])) ? infoCodeVariant['at_infoCodeVariant'] : '';
  let itemLocationCode; itemLocationCode = (itemLocationCode = dmCode.find(v => v['at_itemLocationCode'])) ? itemLocationCode['at_itemLocationCode'] : '';
  return prefix +
  modelIdentCode + "-" + systemDiffCode + "-" +
  systemCode + "-" + subSystemCode + subSubSystemCode + "-" +
  assyCode + "-" + disassyCode + disassyCodeVariant + "-" +
  infoCode + infoCodeVariant + "-" + itemLocationCode;
}
function resolve_dmlCode(dmlCode, prefix = 'DML-'){
  if(!dmlCode) return '';
  let dmlType; dmlType = (dmlType = dmlCode.find(v => v['at_dmlType'])) ? dmlType['at_dmlType'] : '';
  let modelIdentCode; modelIdentCode = (modelIdentCode = dmlCode.find(v => v['at_modelIdentCode'])) ? modelIdentCode['at_modelIdentCode'] : '';
  let senderIdent; senderIdent = (senderIdent = dmlCode.find(v => v['at_senderIdent'])) ? senderIdent['at_senderIdent'] : '';
  let seqNumber; seqNumber = (seqNumber = dmlCode.find(v => v['at_seqNumber'])) ? seqNumber['at_seqNumber'] : '';
  let yearOfDataIssue; yearOfDataIssue = (yearOfDataIssue = dmlCode.find(v => v['at_yearOfDataIssue'])) ? yearOfDataIssue['at_yearOfDataIssue'] : '';
  return prefix + modelIdentCode + "-" + senderIdent + "-" + dmlType + "-" + yearOfDataIssue + "-" + seqNumber;
}
function resolve_commentCode(commentCode, prefix = 'COM-'){
  if(!commentCode) return '';
  let modelIdentCode; modelIdentCode = (modelIdentCode = commentCode.find(v => v['at_modelIdentCode'])) ? modelIdentCode['at_modelIdentCode'] : '';
  let senderIdent; senderIdent = (senderIdent = commentCode.find(v => v['at_senderIdent'])) ? senderIdent['at_senderIdent'] : '';
  let yearOfDataIssue; yearOfDataIssue = (yearOfDataIssue = commentCode.find(v => v['at_yearOfDataIssue'])) ? yearOfDataIssue['at_yearOfDataIssue'] : '';
  let seqNumber; seqNumber = (seqNumber = commentCode.find(v => v['at_seqNumber'])) ? seqNumber['at_seqNumber'] : '';
  let commentType; commentType = (commentType = commentCode.find(v => v['at_commentType'])) ? commentType['at_commentType'] : '';
  return prefix + modelIdentCode + "-" + senderIdent + "-" + commentType + "-" + yearOfDataIssue + "-" + seqNumber;
}

function resolve_ddnCode(ddnCode, prefix = 'DDN-'){
  if(!ddnCode) return '';
  let modelIdentCode; modelIdentCode = (modelIdentCode = ddnCode.find(v => v['at_modelIdentCode'])) ? modelIdentCode['at_modelIdentCode'] : '';
  let senderIdent; senderIdent = (senderIdent = ddnCode.find(v => v['at_senderIdent'])) ? senderIdent['at_senderIdent'] : '';
  let receiverIdent; receiverIdent = (receiverIdent = ddnCode.find(v => v['at_receiverIdent'])) ? receiverIdent['at_receiverIdent'] : '';
  let yearOfDataIssue; yearOfDataIssue = (yearOfDataIssue = ddnCode.find(v => v['at_yearOfDataIssue'])) ? yearOfDataIssue['at_yearOfDataIssue'] : '';
  let seqNumber; seqNumber = (seqNumber = ddnCode.find(v => v['at_seqNumber'])) ? seqNumber['at_seqNumber'] : '';
  return prefix + modelIdentCode + "-" + senderIdent + "-" + receiverIdent + "-" + yearOfDataIssue + "-" + seqNumber;
}

// ident
function resolve_dmlIdent(dmlIdent, prefix = "DML-", format = ".xml"){
  if(!dmlIdent) return '';
  const dmlCode = resolve_dmlCode(jp.query(dmlIdent,'$..dmlCode')[0],prefix);
  const issueInfo = resolve_issueInfo(jp.query(dmlIdent,'$..issueInfo')[0]);
  return (dmlCode + (issueInfo ? "_"+issueInfo : '')).toUpperCase()+format;
}
function resolve_dmIdent(dmIdent, prefix = "DMC-", format = ".xml"){
  if(!dmIdent) return '';
  const dmCode = resolve_dmCode(jp.query(dmIdent,'$..dmCode')[0],prefix);
  const language = resolve_language(jp.query(dmIdent,'$..language')[0]);
  const issueInfo = resolve_issueInfo(jp.query(dmIdent,'$..issueInfo')[0]);
  return (dmCode + (issueInfo ? "_"+issueInfo + (language ? "_"+language : ''): '')).toUpperCase()+format;
}
function resolve_pmIdent(pmIdent, prefix = "PMC-", format = ".xml"){
  if(!pmIdent) return '';
  const pmCode = resolve_pmCode(jp.query(pmIdent,'$..pmCode')[0],prefix);
  const language = resolve_language(jp.query(pmIdent,'$..language')[0]);
  const issueInfo = resolve_issueInfo(jp.query(pmIdent,'$..issueInfo')[0]);
  return (pmCode + (issueInfo ? "_"+issueInfo + (language ? "_"+language : ''): '')).toUpperCase()+format;
}
function resolve_commentIdent(commentIdent, prefix = "COM-", format = ".xml"){
  if(!commentIdent) return '';
  const commentCode = resolve_commentCode(jp.query(commentIdent,'$..commentCode')[0],prefix);
  const language = resolve_language(jp.query(commentIdent,'$..language')[0]);
  return (commentCode + (language ? "_"+language : '')).toUpperCase()+format;
}
function resolve_infoEntityIdent(infoEntityIdent, prefix = "", format = ""){
  if(!infoEntityIdent) return '';
  let infoEntityRefIdent; infoEntityRefIdent = (infoEntityRefIdent = issueInfo.find(v => v['at_infoEntityRefIdent'])) ? infoEntityRefIdent['at_infoEntityRefIdent'] : '';
  if(prefix) infoEntityRefIdent = infoEntityRefIdent.replace('ICN-',prefix);
  if(format) infoEntityRefIdent = infoEntityRefIdent.replace(/\.\w+$/,format);
  return infoEntityIdent;
}

function resolve_language(language){
  if(!language) return '';
  let languageIsoCode; languageIsoCode = (languageIsoCode = language.find(v => v['at_languageIsoCode'])) ? languageIsoCode['at_languageIsoCode'] : '';
  let countryIsoCode; countryIsoCode = (countryIsoCode = language.find(v => v['at_countryIsoCode'])) ? countryIsoCode['at_countryIsoCode'] : '';
  return languageIsoCode +'-'+countryIsoCode;
}

function resolve_issueInfo(issueInfo){
  if(!issueInfo) return '';
  let issueNumber; issueNumber = (issueNumber = issueInfo.find(v => v['at_issueNumber'])) ? issueNumber['at_issueNumber'] : '';
  let inWork; inWork = (inWork = issueInfo.find(v => v['at_inWork'])) ? inWork['at_inWork'] : '';
  return issueNumber + '-' + inWork;
}

function resolve_issueDate(issueDate){
  if(!issueDate) return '';
  let year; year = (year = issueDate.find(v => v['at_year'])) ? year['at_year'] : '';
  let month; month = (month = issueDate.find(v => v['at_month'])) ? month['at_month'] : '';
  let day; day = (day = issueDate.find(v => v['at_day'])) ? day['at_day'] : '';
  return (new Date(year,month,day)).toDateString()
}



export {
  fetchJsonFile, jsonFileGetRemarks,
  resolve_dmIdent, resolve_dmlIdent, resolve_pmIdent, resolve_commentIdent, resolve_infoEntityIdent,
  resolve_pmCode, resolve_dmCode, resolve_dmlCode, resolve_commentCode, resolve_ddnCode,
  resolve_issueInfo, resolve_issueDate, resolve_language};