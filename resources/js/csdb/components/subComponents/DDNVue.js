import {
  fetchJsonFile, jsonFileGetRemarks,
  resolve_dmIdent, resolve_dmlIdent, resolve_pmIdent, resolve_commentIdent, resolve_infoEntityIdent,
  resolve_pmCode, resolve_dmCode, resolve_dmlCode, resolve_commentCode, resolve_ddnCode,
  resolve_issueInfo, resolve_issueDate, resolve_language
} from '../../S1000DHelper';
import jp from 'jsonpath';
import Randomstring from 'randomstring';
import { array_unique, findAncestor } from '../../helper';
import axios from 'axios';

function DDN(json){
  return {
    code: resolve_ddnCode(jp.query(json, '$..identAndStatusSection..ddnAddress..ddnIdent..ddnCode')[0], '').toUpperCase(),
    issueDate: resolve_issueDate(jp.query(json, '$..ddnAddressItems..issueDate')[0]),
    securityClassification: jp.query(json, '$..ddnStatus..security..at_securityClassification')[0],
    brex: resolve_dmIdent(jp.query(json, '$..ddnStatus..brexDmRef..dmRefIdent')[0]),
    remarks: jsonFileGetRemarks(json, '$..ddnStatus..remarks'),
    // dispatchFileNames: jp.query(json, '$..dispatchFileName'),
    dispatchFileNames: jp.query(json, '$..dispatchFileName').map(v => v = v[0]),
    mediaIdents: jp.query(json, '$..mediaIdent').map(v => v = v[0]),
  }
}

async function showDDNContent(filename){
  const response = await fetchJsonFile({ filename: filename }, 'api.read_ddnjson');
  if(response.statusText === 'OK'){
    // handle and arrange json file
    this.DDNObject = DDN(response.data.json);
    // window.json = response.data.json;

    // createEntriesString
    // do create string here

    // set currentObjectModel
    // this.techpubStore.currentObjectModel = response.data.model;
  }
}

function explorerConfig(filename){
  return {
    name: 'Explorer',
    params: {
      filename: filename,
      viewType: 'pdf'
    },
    query: {
      ddn: this.$route.params.filename,
      bbi: 'Preview'
    }
  }
}

function importObject(){
  // check duplicated file
  const filenames = this.CB.value();
  const post = (overwrite = false) => {
    return axios({
      route: {
        name: 'api.import_ddn_list',
        data: {
          filename: this.$route.params.filename,
          filenames: filenames,
          overwrite: overwrite,
        }
      },
    })
    .then(async (response) => {
      if(response.statusText === 'OK'){
        if(response.data.duplicatedCsdb){
          const duplicatedFilename = [];
          let duplicatedPath = [];
          response.data.duplicatedCsdb.forEach(csdb => {
            duplicatedFilename.push(csdb.filename);
            duplicatedPath.push(csdb.path);
          });
          duplicatedPath = array_unique(duplicatedPath);
          if(await this.$root.alert({
            name: 'beforeImportCsdbObject', 
            filename: duplicatedFilename.join(", "),
            previousObjectPath: duplicatedPath.join(", "),
          })){
            post(true);
          }
        }
      }
    })
  };
  post();

  
  return;
}

function refresh(data){
  this.showDDNContent(data.filename);
}

export {showDDNContent, explorerConfig, importObject, refresh}