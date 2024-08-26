<?php

namespace App\Http\Controllers\Csdb;

use App\Http\Controllers\Controller;
use App\Http\Requests\Csdb\DmlCreateFromEditorDML;
use App\Http\Requests\Csdb\DmlMerge;
use App\Http\Requests\Csdb\DmlUpdateFromEditorDML;
use App\Http\Requests\Csdb\GenerateCsl;
use App\Models\Csdb;
use App\Models\Csdb\Dml;
use App\Rules\Dml\EntryIdent;
use App\Rules\Dml\EntryIssueType;
use App\Rules\Dml\EntryType;
use App\Rules\EnterpriseCode;
use App\Rules\SecurityClassification;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Ptdi\Mpub\CSDB as MpubCSDB;
use Ptdi\Mpub\Helper;
use Ptdi\Mpub\Main\CSDBError;
use Ptdi\Mpub\Main\CSDBObject;
use App\Rules\Csdb\BrexDmRef as BrexDmRefRules;
use Ptdi\Mpub\Main\CSDBStatic;

class DmlController extends Controller
{
  #### csdb4 ####

  /**
   * pada request, tinggal tambah dmlRef di frontend. Nanti tambahkan $otherOptions['dmlRef'] = ['DML-...', 'DML-...'];
   * @return App\Models\Csdb
   */
  public function create(DmlCreateFromEditorDML $request)
  {
    $CSDBModel = new Csdb();
    $CSDBModel->CSDBObject = $request->CSDBObject[0];
    $CSDBModel->filename = $CSDBModel->CSDBObject->filename;
    $CSDBModel->path = $request->validated('path');
    $CSDBModel->storage_id = $request->user()->id;
    $CSDBModel->initiator_id = $request->user()->id;

    if ($CSDBModel->saveDOMandModel($request->user()->storage, [
      ['MAKE_CSDB_CRBT_History', [Csdb::class]],
      ['MAKE_USER_CRBT_History', [$request->user(), '', $CSDBModel->filename]]
    ])) {
      return $this->ret2(200, ["{$CSDBModel->filename} has been created."], ['csdb' => $CSDBModel, 'infotype' => 'info']);
    }
    return $this->ret2(400, ["fail to create and save DML."]);
  }

  public function update(DmlUpdateFromEditorDML $request, string $filename)
  { 
    $request->DMLModel->CSDBObject->load(CSDB_STORAGE_PATH . DIRECTORY_SEPARATOR . $request->user()->storage . DIRECTORY_SEPARATOR . $filename);
    $request->DMLModel->fill_xml($request->validated());

    if ($request->DMLModel->saveDOMandModel($request->user()->storage, [
      ['MAKE_CSDB_UPDT_History', [$request->DMLModel->csdb]],
      ['MAKE_USER_UPDT_History', [$request->user(), '', $request->DMLModel->csdb->filename]]
    ])) {
      return $this->ret2(200, ["{$request->DMLModel->csdb->filename} has been updated."], ['csdb' => $request->DMLModel->csdb, 'infotype' => 'info']);
    }
    return $this->ret2(400, ["fail to update and save DML."]);
  }

  public function merge(DmlMerge $request)
  {
    $request->MergedCSDBModel->filename = $request->MergedCSDBModel->CSDBObject->getFilename();
    $request->MergedCSDBModel->path = $request->validated('path');
    $request->MergedCSDBModel->storage_id = $request->user()->id;
    $request->MergedCSDBModel->initiator_id = $request->user()->id;

    if ($request->MergedCSDBModel->saveDOMandModel($request->user()->storage, [
      ['MAKE_CSDB_CRBT_History', [Csdb::class]],
      ['MAKE_USER_CRBT_History', [$request->user(), '', $request->MergedCSDBModel->filename]]
    ])) {
      return $this->ret2(200, ["{$request->MergedCSDBModel->filename} has been created."], ['csdb' => $request->MergedCSDBModel, 'infotype' => 'info']);
    }
    return $this->ret2(400, ["fail to merge and save DML."]);
  }

  public function getCsl(Request $request, string $filename)
  {
    $origFilename = $filename;
    $filename = preg_replace("/-P-|-C-/",'-S-', $filename);
    $request->route()->setParameter('filename', $filename); // biar bisa pakai method 'with'
    $CSDBModel = Csdb::getCsdb($filename)->with('object')->first();
    if(!$CSDBModel){
      $CSDBModel = new Csdb();
      $CSDBModel->CSDBObject = Csdb::getObject($origFilename)->first()->toCsl($request->user());      
      $CSDBModel->filename = $CSDBModel->CSDBObject->filename;
      $CSDBModel->path = "CSDB/CSL";
      $CSDBModel->storage_id = $request->user()->id;
      $CSDBModel->initiator_id = $request->user()->id;
      if(!($CSDBModel->saveDOMandModel($request->user()->storage, [
        ['MAKE_CSDB_CRBT_History', [Csdb::class]],
        ['MAKE_USER_CRBT_History', [$request->user(), '', $CSDBModel->filename]]
      ],['connection' => false ])))
      {
        $CSDBModel = null;
      } else {
        $CSDBModel->object = Dml::fillTable($CSDBModel->id, $CSDBModel->CSDBObject); 
      }
    }
    $CSDBModel->object->makeVisible(['json']);
    return $this->ret2(200, ['csdb' => $CSDBModel]);
  }

  /**
   * @deprecated
   * DEPRECIATED, karena semua object ditampilkan di listtree explorer
   * aturannya sama seperti CsdbController@get_allobjects_list
   */
  public function get_dmrl_list(Request $request)
  {
    if ($request->get('listtree')) {
      return $this->ret2(200, [
        "data" =>
        Dml::where('filename', 'like', 'DML-%')
          ->get(['filename', 'path'])
          ->toArray()
      ]);
    }
    $this->model = Csdb::with('initiator')->where('filename', 'like', 'DML-%');
    return $this->ret2(200, ['data' => $this->model->get()->toArray()]);
  }

  /**
   * @deprecated
   * DEPRECIATED, karena diganti read_json
   */
  public function read_html_content(Request $request, string $filename)
  {
    if (!($DMLModel = Csdb::getObject($filename, ['exception' => ['CSDB-DELL', 'CSDB-PDEL']])->first())) return $this->ret2(400, ["{$filename} fails to be showed."]);
    $DMLModel->CSDBObject->load(CSDB_STORAGE_PATH . "/" . $request->user()->storage . "/" . $filename);
    $DMLModel->CSDBObject->setConfigXML(CSDB_VIEW_PATH . DIRECTORY_SEPARATOR . "xsl" . DIRECTORY_SEPARATOR . "Config.xml"); // nanti diubah mungkin berbeda antara pdf dan html meskupun harusnya SAMA. Nanti ConfigXML mungkin tidak diperlukan jika fitur BREX sudah siap sepenuhnya.
    $transformed = $DMLModel->CSDBObject->transform_to_xml(CSDB_VIEW_PATH . "/xsl/html_dml/dml.xsl", [
      'filename' => $filename
    ]);
    if ($error = CSDBError::getErrors(false)) {
      return $this->ret2(200, [$error], ['model' => $DMLModel, 'transformed' => $transformed, 'mime' => 'text/html']); // ini yang dipakai vue
    }
    return $this->ret2(200, ['model' => $DMLModel->makeHidden(['id']), 'transformed' => $transformed, 'mime' => 'text/html']); // ini yang dipakai vue
    if (!($DMLModel = Csdb::getObject($filename, ['exception' => ['CSDB-DELL', 'CSDB-PDEL']])->first())) return $this->ret2(400, ["{$filename} fails to be showed."]);
    $DMLModel->CSDBObject->load(CSDB_STORAGE_PATH . "/" . $request->user()->storage . "/" . $filename);
    $json = CSDBStatic::xml_to_json($DMLModel->CSDBObject->document);
    return $this->ret2(200, ['model' => $DMLModel->makeHidden(['id']), 'json' => $json, 'transformed' => '', 'mime' => 'text/html']); // ini yang dipakai vue
  }
}
