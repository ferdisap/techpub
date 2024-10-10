<?php

namespace App\Http\Controllers\CsdbApi;

use App\Http\Requests\Csdb\CsdbCreateByXMLEditor;
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
    ],400,['content-type' => 'application/json']);
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
    ],400,['content-type' => 'application/json']);
  }
}
