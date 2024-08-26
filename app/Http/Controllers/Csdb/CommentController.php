<?php

namespace App\Http\Controllers\Csdb;

use App\Http\Controllers\Controller;
use App\Http\Requests\Csdb\CommentCreate;
use App\Models\Csdb;
use App\Models\Csdb\Comment;
use App\Models\Csdb\History;
use App\Models\Project;
use App\Models\User;
use App\Rules\Csdb\BrexDmRef as BrexDmRefRules;
use Carbon\Carbon;
use Closure;
use DOMElement;
use DOMNode;
use DOMXPath;
use Gumlet\ImageResize;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\Rules\File;
use PhpParser\Node\Expr\Cast\Object_;
// use Ptdi\Mpub\CSDB;
use Ptdi\Mpub\ICNDocument;
use Ptdi\Mpub\Validation;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipStream\ZipStream;
use Illuminate\Support\Facades\Process;
use PrettyXml\Formatter;
use Ptdi\Mpub\Helper;
use Ptdi\Mpub\Main\CSDBStatic;

class CommentController extends Controller
{
  /**
   * selanjutnya buat attachment.
   * COM attachment diujung filename ditambah -attachmentNmber.extension see pdf page 1906/3503
   */
  public function create(CommentCreate $request)
  {
    $CSDBModel = new Csdb();
    $CSDBModel->CSDBObject = $request->CSDBObject[0];
    $CSDBModel->filename = $CSDBModel->CSDBObject->filename;
    if(Csdb::where('filename', $CSDBModel->filename)->first()) return $this->ret2(400, ["Cannot create COM due to duplicate filename."]);
    $CSDBModel->path = $request->validated()['path'];
    $CSDBModel->storage_id = $request->user()->id;
    $CSDBModel->initiator_id = $request->user()->id;

    if ($CSDBModel->saveDOMandModel($request->user()->storage, [
      ['MAKE_CSDB_CRBT_History', [Csdb::class]],
      ['MAKE_USER_CRBT_History', [$request->user(), '', $CSDBModel->filename]]
    ])) {
      return $this->ret2(200, ["{$CSDBModel->filename} has been created."], ['csdb' => $CSDBModel, 'infotype' => 'info']);
    }
    return $this->ret2(400, ["fail to create and save COM."]);
  }

  /**
   * since the Csdb table is not unique filename, all the records will be associated with comments wheter they are different initiator, not creator
   */
  public function all(Request $request, string $filename)
  {
    Csdb::$storage_user_id = null;
    $COMModels = Csdb::getObjects(Comment::class, ['exception' => ['CSDB-DELL', 'CSDB-PDEL']])
      ->where('commentRefs','like', "%{$filename}%")->with('csdb.lastHistory')->get();
    $COMModels->map(function($com){
      $com->csdb->initiator->makeHidden(['first_name', 'middle_name', 'last_name', 'job_title','storage','address', 'work_enterprise']);
      $com->csdb->lastHistory->makeHidden(['code','description']);
    });
    return $this->ret2(200, ['comments' => $COMModels->toArray()]);    
  }
}