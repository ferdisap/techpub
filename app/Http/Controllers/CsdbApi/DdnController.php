<?php

namespace App\Http\Controllers\CsdbApi;

use App\Http\Controllers\Controller;
use App\Http\Requests\Csdb\CommentCreate;
use App\Http\Requests\Csdb\CsdbImportFromDDN;
use App\Http\Requests\Csdb\DdnCreate;
use App\Models\Csdb;
use App\Rules\Csdb\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Routing\Controller as BaseController;

class DdnController extends BaseController
{
  public function create(DdnCreate $request)
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
        'csdb' => $CSDBModel
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
   * import csdb akan meng copy CSDBObject/xml nya juga
   */
  public function import(CsdbImportFromDDN $request)
  {
    // check duplicated file
    if (!($request->overwrite) && !empty($request->duplicatedCSDBModels)) {
      $filenames = $request->duplicatedCSDBModels->toArray();
      array_walk($filenames, fn (&$v) => $v['filename']);
      return Response::make([
        "infotype" => "warning",
        "message" => "There is none of csdb imported. Some csdb's is prevented to overwrite.",
        "errors" => [
          'failure' => $filenames,
        ]
      ], 400, ['content-type' => "application/json"]);
    } else {
      $success = [];
      $fail = [];
      foreach ($request->validated('filenames') as $filename) {
        $CSDBModel = Csdb::getCsdb($filename)->first() ?? new Csdb();
        $CSDBModel->CSDBObject->load(CSDB_STORAGE_PATH . DIRECTORY_SEPARATOR . $request->validated('DDNCSDBModel')->owner->storage . DIRECTORY_SEPARATOR . $filename);
        $CSDBModel->filename = $filename;
        $CSDBModel->path = $request->validated('path');
        $CSDBModel->storage_id = $request->user()->id;
        $CSDBModel->initiator_id = $request->user()->id;

        if ($CSDBModel->saveDOMandModel($request->user()->storage, [
          ['MAKE_CSDB_IMPT_History', [Csdb::class]],
          ['MAKE_USER_IMPT_History', [$request->user(), '', $CSDBModel->filename]]
        ])) {
          $success[] = $CSDBModel->filename;
        } else $fail[] = $CSDBModel->filename;
      }
      $totalFail = count($fail);
      $totalSuccess = count($success);
      $infotype = $totalSuccess < 1 ? "warning" : ($totalFail > 0 ? 'caution' : 'note');
      $code = $totalSuccess && !$totalFail ? 200 : (!$totalSuccess ? 400 : 299);
      $message = "Success to import " . join(", ", $success) . (!empty($fail) ? " and fail to import " . join(", ", $fail) : '.');

      $responseContent = [
        'infotype' => $infotype,
        'message' => $message,
        'data' => [
          'success' => $success
        ]
      ];

      if ($code != 200) $responseContent['errors'] = [
        'failure' => $fail,
      ];
      return Response::make($responseContent, $code, ['content-type' => 'application/json']);
    }
  }
}
