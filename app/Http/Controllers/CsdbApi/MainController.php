<?php

namespace App\Http\Controllers\CsdbApi;

use App\Http\Requests\Csdb\CsdbCreateByXMLEditor;
use App\Models\Csdb;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Response;

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
      Response::make([
        'infotype' => 'note',
        'message' => "New {$CSDBModel->filename} has been created.",
        "csdb" => $CSDBModel,
      ],200,['content-type' => 'application/json']);
    }
    Response::make([
      'infotype' => 'warning',
      'message' => "{$CSDBModel->filename} failed to create.",
      'errors' => $CSDBModel->CSDBObject->errors->get(),
      'csdb' => $CSDBModel
    ],400,['content-type' => 'application/json']);
  }
}
