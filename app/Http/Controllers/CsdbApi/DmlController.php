<?php

namespace App\Http\Controllers\CsdbApi;

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
      return Response::make([
        'infotype' => 'note',
        'message' => "New {$CSDBModel->filename} has been created.",
        "csdb" => $CSDBModel,
      ], 200, ['content-type' => 'application/json']);
    }
    return Response::make([
      'infotype' => 'warning',
      'message' => "{$CSDBModel->filename} failed to create.",
      'errors' => $CSDBModel->CSDBObject->errors->get(),
      'csdb' => $CSDBModel
    ], 422, ['content-type' => 'application/json']);
  }

  /**
   * ingat, $CSDBModel->object->CSDBObject !== $CSDBModel->CSDBObject
   */
  public function update(DmlUpdateFromEditorDML $request, Csdb $CSDBModel)
  { 
    $CSDBModel->object->CSDBObject->load(CSDB_STORAGE_PATH . DIRECTORY_SEPARATOR . $request->user()->storage . DIRECTORY_SEPARATOR . $CSDBModel->filename);
    $CSDBModel->object->fill_xml($request->validated());

    if ($CSDBModel->object->saveDOMandModel($request->user()->storage, [
      ['MAKE_CSDB_UPDT_History', [$CSDBModel]],
      ['MAKE_USER_UPDT_History', [$request->user(), '', $CSDBModel->filename]]
    ])) {
      return Response::make([
        'infotype' => 'note',
        'message' => "{$CSDBModel->filename} has been update.",
        "csdb" => $CSDBModel,
      ], 200, ['content-type' => 'application/json']);
    }
    return Response::make([
      'infotype' => 'warning',
      'message' => "{$CSDBModel->filename} failed to update.",
      'errors' => $CSDBModel->CSDBObject->errors->get(),
      'csdb' => $CSDBModel
    ], 422, ['content-type' => 'application/json']);
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
      return Response::make([
        'infotype' => 'note',
        'message' => "New {$request->MergedCSDBModel->filename} has been created.",
        "csdb" => $request->MergedCSDBModel,
      ], 200, ['content-type' => 'application/json']);
    }
    return Response::make([
      'infotype' => 'warning',
      'message' => "{$request->MergedCSDBModel->filename} failed to create.",
      'errors' => $request->MergedCSDBModel->CSDBObject->errors->get(),
      'csdb' => $request->MergedCSDBModel
    ], 422, ['content-type' => 'application/json']);
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
    return Response::make([
      'csdb' => $CSDBModel
    ], 200, ['content-type' => 'application/json']);    
  }
}
