<?php

namespace App\Http\Controllers\Csdb;

use App\Http\Controllers\Controller;
use App\Http\Requests\Csdb\CsdbImportFromDDN;
use App\Http\Requests\Csdb\DdnCreate;
use App\Models\Csdb;
use App\Models\Csdb\Ddn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Rules\Csdb\BrexDmRef as BrexDmRefRules;
use Closure;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Ptdi\Mpub\Main\CSDBStatic;

class DdnController extends Controller
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
      return $this->ret2(200, ["{$CSDBModel->filename} has been created."], ['csdb' => $CSDBModel, 'infotype' => 'info']);
    }
    return $this->ret2(400, ["fail to create and save DDN."]);
  }

  public function list(Request $request)
  {
    $DDNModels = Ddn::with(['csdb' => function(Builder $query){
      $query->select(['id', 'filename', 'storage_id']);
      $query->with(['owner' => function(Builder $query){
        $query->select(['id', 'first_name', 'middle_name', 'last_name', 'job_title', 'email', 'work_enterprise_id']);
        $query->with(['work_enterprise' => function(Builder $query){
          $query->without('code');
          $query->select(['id', 'name']);
        }]);
      }]);
    }])->where('dispatchTo_id', $request->user()->id)->orderBy('id', 'desc')->paginate(100);
    return $DDNModels;
  }

  public function import(CsdbImportFromDDN $request)
  {
    // check duplicated file
    if(!($request->overwrite) && !empty($request->duplicatedCSDBModels)){
      return $this->ret2(200, [
        'duplicatedCsdb' => $request->duplicatedCSDBModels,
      ]);
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
    }
    $message = "Success to import " . join(", ", $success) . (!empty($fail) ? " and fail to import " . join(", ", $fail) : '.');
    return $this->ret2(200, [
      'infotype' => (empty($fail) ? 'info' : (!empty($success) ? 'warning' : '')),
      'message' => $message,
    ]);
  }

  public function read_json(Request $request, string $filename)
  {
    Csdb::$storage_user_id = null;
    if (!($OBJECTModel = Csdb::getObject($filename, ['exception' => ['CSDB-DELL', 'CSDB-PDEL']])->first())) return $this->ret2(400, ["{$filename} fails to be showed."]);

    // determine if request user is in dispatchTo
    if($OBJECTModel->dispatchTo_id != $request->user()->id) return $this->ret2(200, ['infotype' => 'warning', 'message' => "You are not allowed access the {$filename}."]);

    // load json
    $OBJECTModel->CSDBObject->load(CSDB_STORAGE_PATH . "/" . $OBJECTModel->csdb->owner->storage . "/" . $filename);
    $json = json_decode(CSDBStatic::xml_to_json($OBJECTModel->CSDBObject->document));
    return $this->ret2(200, ['model' => $OBJECTModel->makeHidden(['id']), 'json' => $json]); // ini yang dipakai vue
  }
}
