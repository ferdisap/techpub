<?php

use App\Http\Controllers\Csdb\BrController;
use App\Http\Controllers\Csdb\CsdbController;
use App\Http\Controllers\Csdb\CommentController;
use App\Http\Controllers\Csdb\DdnController;
use App\Http\Controllers\Csdb\DmlController;
use App\Http\Controllers\Csdb\HistoryController;
use App\Http\Controllers\CsdbApi\MainController;
use App\Http\Controllers\EnterpriseController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| CSDB WEB Routes
|--------------------------------------------------------------------------
| 
| This routes are assigned to the "api" middleware 
| group by RouteServiceProvider.
|
| Istilah response data "model" diganti menjadi "object"
*/


// Route::middleware('auth:sanctum')->group(function () {
//   Route::get("/s1000d/csdb/all",function(){
//     return 'foobar';
//   })->name('api.get_csdbs'); // api.get_allobjects_list
// });
Route::middleware('auth:sanctum')->group(function () {
  // create
  Route::put("/s1000d/csdb/create",[MainController::class, 'create'])->name('api.create_object');

  // read
  Route::get('/s1000d/csdb/read/{CSDBModel:filename}', [MainController::class, 'read'])
  ->missing(fn() => throw new HttpResponseException(response(["message" => "There is no such csdb."],404)))
  ->name('api.get_object_raw');

  // update
  Route::post("/s1000d/csdb/update/{CSDBModel:filename}", [MainController::class, 'update'])
  ->missing(fn() => throw new HttpResponseException(response(["message" => "There is no such csdb."],404)))
  ->name('api.update_object');

  // delete
  Route::delete("/s1000d/csdb/delete", [MainController::class, 'delete'])->name('api.delete_csdbs');
  Route::delete("/s1000d/csdb/permanentdelete", [MainController::class, 'permanentDelete'])->name('api.permanentdelete_csdbs'); // api.permanentdelete_object

  // restore
  Route::post("/s1000d/csdb/restore", [MainController::class, 'restore'])->name('api.restore_csdb'); // api.restore_object

  // #### below belum di test


  Route::post("/s1000d/icn/upload", [CsdbController::class, 'uploadICN'])->name('api.upload_ICN');
  Route::post('/s1000d/csdb/update/path', [CsdbController::class, 'change_object_path'])->name('api.change_object_path');
  Route::post("/s1000d/dml/create",[DmlController::class, 'create'])->name('api.create_dml');
  Route::post("/s1000d/dml/update/{filename}",[DmlController::class, 'update'])->name('api.dmlupdate');
  Route::post("/s1000d/comment/create",[CommentController::class, 'create'])->name('api.create_comment');
  Route::post("/s1000d/ddn/create",[DdnCOntroller::class, 'create'])->name('api.create_ddn');
  Route::put('/s1000d/ddn/import/{filename}', [DdnController::class, 'import'])->name('api.import_ddn_list');
  Route::put("/s1000d/dml/{filename}/merge", [DmlController::class, 'merge'])->name('api.dml_merge');

  // read
  Route::get("/s1000d/csdbs/all",[CsdbController::class, 'getCsdbs'])->name('api.get_csdbs'); // api.get_allobjects_list
  
  // ini nanti bisa juga di cacce pakai meddleware ETagGeneralContent, tapi jika diinstal di LAN tidak perlu karena available bandwith yang besar
  Route::get("/s1000d/csdb/{CSDBModel:filename}",[CsdbController::class, 'get_csdb_model'])->name('api.get_csdb'); // api.get_csdb_model
  

  Route::get("/s1000d/object/{filename}",[CsdbController::class, 'get_object_model'])->name('api.get_object'); // api.get_object_model
  Route::get('/s1000d/object/{CSDBModel:filename}/raw', [CsdbController::class, 'get_object_raw'])->name('api.get_object_raw');
  Route::get("/s1000d/icn/{CSDBModel:filename}/raw", [CsdbController::class, 'get_icn_raw'])->name('api.get_icn_raw');
  Route::post("/s1000d/download", [CsdbController::class, 'download_objects'])->name('api.download_objects');
  // user
  Route::get('/s1000d/user/search', [UserController::class, 'searchModel'])->name('api.user_search_model');
  // history
  Route::get("/s1000d/csdb/{filename}/histories", [HistoryController::class, 'all'])->name('api.get_csdb_history');
  // comment
  Route::get("/s1000d/csdb/{filename}/comments",[CommentController::class, 'all'])->name('api.get_csdb_comments');  
  // ddn
  Route::get("/s1000d/ddn/dispatched",[DdnController::class, 'list'])->name('api.get_ddn_list');
  // dml
  Route::get("/s1000d/dmrl/all",[DmlController::class, 'get_dmrl_list'])->name('api.get_dmrl_list');
  Route::get("/s1000d/csl/{filename}", [DmlController::class, 'getCsl'])->name('api.get_csl');
  // search
  Route::get("/s1000d/csdb/search",[CsdbController::class, 'get_object_csdbs'])->name('api.search_csdb'); // api.get_object_csdbs
  Route::get("/s1000d/folder/index",[CsdbController::class, 'forfolder_get_allobjects_list'])->name('api.index_folder'); // api.requestbyfolder.get_allobject_list
  Route::get("/s1000d/enterprise/search", [EnterpriseController::class, 'get_enterprises'])->middleware('auth:sanctum')->name('api.get_enterprises');

  // delete
  Route::get("/s1000d/deleted/all",[CsdbController::class, 'get_deletion_list'])->name('api.get_deleted'); // api.get_deletion_list
  
  // restore
  

  // validate
  Route::post("/s1000d/br/validate",[BrController::class, 'validate'])->name('api.validate_by_brex');
});

// read
Route::get('/s1000d/csdb/{filename}/pdf', [CsdbController::class, 'read_pdf_object'])->middleware(['auth:sanctum','ViaDDN','ETagCsdbPDF'])->name('api.read_pdf_object');
Route::get("/s1000d/{CSDBModel:filename}/html",[CsdbController::class, 'read_html_object'])->middleware(['auth:sanctum', 'ViaDDN','ETagCsdbPDF'])->name('api.read_html_object');
Route::get("/s1000d/csdb/{filename}/json",[CsdbController::class, 'read_json'])->middleware(['auth:sanctum', 'ViaDDN','ETagCsdbPDF'])->name('api.read_json');
Route::get("/s1000d/ddn/{filename}/json",[DdnController::class, 'read_json'])->middleware('auth:sanctum')->name('api.read_ddnjson');
