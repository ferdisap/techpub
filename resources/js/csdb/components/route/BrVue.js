import axios from "axios"
import jp from 'jsonpath'
import {
  fetchJsonFile, jsonFileGetRemarks,
  resolve_dmIdent, resolve_dmlIdent, resolve_pmIdent, resolve_commentIdent, resolve_infoEntityIdent,
  resolve_pmCode, resolve_dmCode, resolve_dmlCode, resolve_commentCode, resolve_ddnCode,
  resolve_issueInfo, resolve_issueDate, resolve_language} from '../../S1000DHelper';

const BR = function(json){
  return {
    code: resolve_dmCode(jp.query(json, '$..identAndStatusSection..dmAddress..dmIdent..dmCode')[0], '').toUpperCase(),
    // inWork: json[1]['dmodule'][1]['identAndStatusSection'][0]['dmlAddress'][0]['dmlIdent'][1]['issueInfo'][0]['at_inWork']
    inWork: jp.query(json, '$..identAndStatusSection..dmAddress..dmIdent..issueInfo..at_inWork')[0],
    
  }
}

function setBR(filename){
  axios({
    route: {
      name: 'api.read_json_br',
      data: {filename: filename}
    }
  })
  .then(response => {
    if(response.statusText === 'OK'){
      this.BRObject = BR(response.data.model.json);
    }
  })
}

export {setBR}