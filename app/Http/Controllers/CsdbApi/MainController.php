<?php

namespace App\Http\Controllers\CsdbApi;

use App\Http\Requests\Csdb\CsdbCreateByXMLEditor;
use App\Http\Requests\Csdb\CsdbDelete;
use App\Http\Requests\Csdb\CsdbPermanentDelete;
use App\Http\Requests\Csdb\CsdbRestore;
use App\Http\Requests\Csdb\CsdbUpdateByXMLEditor;
use App\Models\Csdb;
use App\Models\Csdb\History;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Response;
use PrettyXml\Formatter;

class MainController extends BaseController
{
  /**
   * response code 422 if fail
   * src: https://stackoverflow.com/questions/47269601/what-http-response-code-to-use-for-failed-post-request
   */
  public function create(CsdbCreateByXMLEditor $request)
  {
    $CSDBModel = new Csdb();
    $CSDBModel->CSDBObject = $request->validated('xmleditor')[0];
    $CSDBModel->filename = $CSDBModel->CSDBObject->getFilename();
    $CSDBModel->path = $request->validated()['path'];
    $CSDBModel->storage_id = $request->user()->id;
    $CSDBModel->initiator_id = $request->user()->id;
    if ($CSDBModel->saveDOMandModel(
      $request->user()->storage,
      [
        ['MAKE_CSDB_CRBT_History', [Csdb::class]],
        ['MAKE_USER_CRBT_History', [$request->user(), '', $CSDBModel->filename]]
      ]
    )) {
      return Response::make([
        'infotype' => 'note',
        'message' => "New {$CSDBModel->filename} has been created.",
        "csdb" => $CSDBModel,
      ],200,['content-type' => 'application/json']);
    }
    return Response::make([
      'infotype' => 'warning',
      'message' => "{$CSDBModel->filename} failed to create.",
      'errors' => $CSDBModel->CSDBObject->errors->get(),
      'csdb' => $CSDBModel
    ],422,['content-type' => 'application/json']);
  }

  public function read(Request $request, Csdb $CSDBModel)
  {
    $CSDBModel->CSDBObject->load(CSDB_STORAGE_PATH . "/" . $request->user()->storage . "/" . $CSDBModel->filename);
    if ($CSDBModel->CSDBObject->document) {
      $formatter = new Formatter();
      return Response::make(
        $formatter->format($CSDBModel->CSDBObject->document->saveXML()),
        200, 
        ['Content-Type' => 'text/xml']
      );
    }
    return abort(204);
  }

  /**
   * response code 422 if fail
   * src: https://stackoverflow.com/questions/47269601/what-http-response-code-to-use-for-failed-post-request
   */
  public function update(CsdbUpdateByXMLEditor $request, Csdb $CSDBModel)
  {
    $CSDBModel = $request->validated('oldCSDBModel')[0];
    $CSDBModel->CSDBObject = $request->validated('xmleditor')[0];
    $CSDBModel->path = $request->validated('path');
    if ($CSDBModel->saveDOMandModel($request->user()->storage,[
      History::MAKE_CSDB_UPDT_History($CSDBModel),
      History::MAKE_USER_UPDT_History($request->user(),'',$CSDBModel->filename)
    ])) {
      return Response::make([
        'infotype' => 'note',
        'message' => "{$CSDBModel->filename} has been update.",
        "csdb" => $CSDBModel,
      ],200,['content-type' => 'application/json']);
    }
    return Response::make([
      'infotype' => 'warning',
      'message' => "{$CSDBModel->filename} failed to update.",
      'errors' => $CSDBModel->CSDBObject->errors->get(),
      'csdb' => $CSDBModel
    ],422,['content-type' => 'application/json']);
  }

  public function delete(CsdbDelete $request)
  {
    $result = [
      'success' => [],
      'fail' => [],
    ];
    $validatedData = $request->validated();
    $CSDBModels = $validatedData['CSDBModelArray'];
    unset($validatedData['CSDBModelArray']);
    $qtyCSDBs = count($CSDBModels);
    for ($i=0; $i < $qtyCSDBs; $i++) { 
      $CSDB_HISTORYModel = History::MAKE_CSDB_DELL_History($CSDBModels[$i]);
      $USER_HISTORYModel = History::MAKE_USER_DELL_History($request->user(),'', $CSDBModels[$i]->filename);
      if(History::saveModel([$CSDB_HISTORYModel, $USER_HISTORYModel])){
        $result['success'][] = $CSDBModels[$i]->filename;
      } else {
        $result['fail'][] = $CSDBModels[$i]->filename;
      }
    }
    $totalFail = count($result['fail']);
    $totalSuccess = count($result['success']);
    $infotype = $totalSuccess < 1 ? "warning" : ($totalFail > 0 ? 'caution' : 'info');
    $m = ($totalSuccess > 0 ? ("success: " . $totalSuccess . "/" .($totalSuccess + $totalFail) . ", failure: " . $totalFail . "/" .($totalSuccess + $totalFail)) : "fail: " . ($totalSuccess + $totalFail) . "/". ($totalSuccess + $totalFail));
    $code = $totalSuccess < 1 ? 400 : 200;
    return Response::make([
      'infotype' => $infotype,
      'message' => $m,
      'errors' => [
        'failure' => $result['fail'],
        'success' => $result['success'],
      ]
    ],$code);
  }

  /**
   * sama dengan @delete() tapi beda history saja
   */
  public function permanentDelete(CsdbPermanentDelete $request)
  {
    $result = [
      'success' => [],
      'fail' => [],
    ];
    $validatedData = $request->validated();
    $CSDBModels = $validatedData['CSDBModelArray'];
    unset($validatedData['CSDBModelArray']);
    $qtyCSDBs = count($CSDBModels);
    for ($i=0; $i < $qtyCSDBs; $i++) { 
      $CSDB_HISTORYModel = History::MAKE_CSDB_PDEL_History($CSDBModels[$i]);
      $USER_HISTORYModel = History::MAKE_USER_PDEL_History($request->user(),'', $CSDBModels[$i]->filename);
      if(History::saveModel([$CSDB_HISTORYModel,$USER_HISTORYModel])){
        $result['success'][] = $CSDBModels[$i]->filename;
      } else {
        $result['fail'][] = $CSDBModels[$i]->filename;
      }
    }    
    $totalFail = count($result['fail']);
    $totalSuccess = count($result['success']);
    $infotype = $totalSuccess < 1 ? "warning" : ($totalFail > 0 ? 'caution' : 'info');
    $m = ($totalSuccess > 0 ? ("success: " . $totalSuccess . "/" .($totalSuccess + $totalFail) . ", failure: " . $totalFail . "/" .($totalSuccess + $totalFail)) : "fail: " . ($totalSuccess + $totalFail) . "/". ($totalSuccess + $totalFail));
    $code = $totalSuccess < 1 ? 400 : 200;
    return Response::make([
      'infotype' => $infotype,
      'message' => $m,
      'errors' => [
        'failure' => $result['fail'],
        'success' => $result['success'],
      ]
    ],$code);
  }

  /**
   * sama dengan @delete() tapi beda history saja
   */
  public function restore(CsdbRestore $request)
  {
    $result = [
      'success' => [],
      'fail' => [],
    ];
    $validatedData = $request->validated();
    $CSDBModels = $validatedData['CSDBModelArray'];
    unset($validatedData['CSDBModelArray']);
    $qtyCSDBs = count($CSDBModels);
    for ($i=0; $i < $qtyCSDBs; $i++) { 
      $CSDB_HISTORYModel = History::MAKE_CSDB_RSTR_History($CSDBModels[$i]);
      $USER_HISTORYModel = History::MAKE_USER_RSTR_History($request->user(),'', $CSDBModels[$i]->filename);
      if(History::saveModel([$CSDB_HISTORYModel,$USER_HISTORYModel])){
        $result['success'][] = $CSDBModels[$i]->filename;
      } else {
        $result['fail'][] = $CSDBModels[$i]->filename;
      }
    }
    $totalFail = count($result['fail']);
    $totalSuccess = count($result['success']);
    $infotype = $totalSuccess < 1 ? "warning" : ($totalFail > 0 ? 'caution' : 'info');
    $m = ($totalSuccess > 0 ? ("success: " . $totalSuccess . "/" .($totalSuccess + $totalFail) . ", failure: " . $totalFail . "/" .($totalSuccess + $totalFail)) : "fail: " . ($totalSuccess + $totalFail) . "/". ($totalSuccess + $totalFail));
    $code = $totalSuccess < 1 ? 400 : 200;
    return Response::make([
      'infotype' => $infotype,
      'message' => $m,
      'errors' => [
        'failure' => $result['fail'],
        'success' => $result['success'],
      ]
    ],$code);
  }
}
