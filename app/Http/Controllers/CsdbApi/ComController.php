<?php

namespace App\Http\Controllers\CsdbApi;

use App\Http\Controllers\Controller;
use App\Http\Requests\Csdb\CommentCreate;
use App\Models\Csdb;
use App\Rules\Csdb\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Routing\Controller as BaseController;

class ComController extends BaseController
{
  /**
   * selanjutnya buat attachment.
   * COM attachment diujung filename ditambah -attachmentNmber.extension see pdf page 1906/3503
   */
  public function create(CommentCreate $request)
  // public function create(Request $request)
  {
    $CSDBModel = new Csdb();
    $CSDBModel->CSDBObject = $request->CSDBObject[0];
    $CSDBModel->filename = $CSDBModel->CSDBObject->filename;
    if ($duplicatedCSDBModel = Csdb::where('filename', $CSDBModel->filename)->first()) {
      return Response::make([
        'infotype' => 'caution',
        'message' => "Cannot create COM due to duplicate filename.",
        'csdb' => $duplicatedCSDBModel,
        'errors' => $CSDBModel->CSDBObject->errors->get(),
      ], 499, ['content-type' => 'application/json']);
    };
    $CSDBModel->path = $request->validated()['path'];
    $CSDBModel->storage_id = $request->user()->id;
    $CSDBModel->initiator_id = $request->user()->id;

    if ($CSDBModel->saveDOMandModel($request->user()->storage, [
      ['MAKE_CSDB_CRBT_History', [Csdb::class]],
      ['MAKE_USER_CRBT_History', [$request->user(), '', $CSDBModel->filename]]
    ])) {
      return Response::make([
        'infotype' => 'note',
        'message' => "New {$CSDBModel->filename} has been created.",
        'csdb' => $CSDBModel
      ],200, ['content-type' => 'application/json']);
    }
    return Response::make([
      'infotype' => 'warning',
      'message' => "{$CSDBModel->filename} failed to create.",
      'errors' => $CSDBModel->CSDBObject->errors->get(),
      'csdb' => $CSDBModel
    ], 422, ['content-type' => 'application/json']);
  }
}


