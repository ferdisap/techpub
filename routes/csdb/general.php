<?php

use App\Events\Csdb\DdnCreated;
use App\Http\Controllers\Csdb\CommentController;
use App\Http\Controllers\Csdb\CsdbController;
use App\Http\Controllers\Csdb\HistoryController;
use App\Http\Controllers\CsdbServiceController;
use App\Http\Controllers\Csdb\DmlController;
use App\Http\Controllers\UserController;
use App\Jobs\Csdb\FillObjectTable;
use App\Mail\Csdb\DataDispatchNote;
use App\Models\Csdb;
use App\Models\Csdb\Ddn;
use App\Models\Enterprise;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Ptdi\Mpub\Main\CSDBStatic;
use Ptdi\Mpub\Main\Helper;

// ### Route utama ###
Route::get("/csdb/{view?}",[CsdbController::class, 'app'])->where('view','(.*)')->middleware('auth');

Route::post("/api/csdbcreate",[CsdbController::class, 'create'])->middleware('auth')->name('api.create_object');
Route::post("/api/updateobject/{filename}", [CsdbController::class, 'update'])->middleware('auth')->name('api.update_object');
Route::post("/api/uploadICN", [CsdbController::class, 'uploadICN'])->middleware('auth')->name('api.upload_ICN');

Route::get("/api/allobjects",[CsdbController::class, 'get_allobjects_list'])->middleware('auth')->name('api.get_allobjects_list'); // ini nanti bisa juga di cacce pakai meddleware ETagGeneralContent, tapi jika diinstal di LAN tidak perlu karena available bandwith yang besar
Route::get("/api/model/{filename}",[CsdbController::class, 'get_object_model'])->middleware('auth')->name('api.get_object_model');
Route::get("/api/csdbs",[CsdbController::class, 'get_object_csdbs'])->middleware('auth')->name('api.get_object_csdbs');
Route::get("/api/byfolder-allobjects",[CsdbController::class, 'forfolder_get_allobjects_list'])->middleware('auth')->name('api.requestbyfolder.get_allobject_list');

Route::get('/api/object/raw/{CSDBModel:filename}', [CsdbController::class, 'get_object_raw'])->middleware('auth')->name('api.get_object_raw');
Route::get('/api/content/{filename}/pdf', [CsdbController::class, 'read_pdf_object'])->middleware(['auth', 'ViaDDN','ETagCsdbPDF'])->name('api.read_pdf_object');
Route::get("/api/content/{CSDBModel:filename}/html",[CsdbController::class, 'read_html_object'])->middleware('auth')->name('api.read_html_object');
Route::get("/api/content/{CSDBModel:filename}/other",[CsdbController::class, 'read_html_object'])->middleware('auth')->name('api.read_other_object');
Route::get("/api/icn/raw/{CSDBModel:filename}", [CsdbController::class, 'get_icn_raw'])->middleware('auth')->name('api.get_icn_raw');

// delete
Route::post("/api/csdbdelete", [CsdbController::class, 'delete'])->middleware('auth')->name('api.delete_objects');

// get user model
Route::get('/api/usersearch', [UserController::class, 'searchModel'])->middleware('auth')->name('api.user_search_model');

// get deleted csdb
Route::get("/api/deletion/all",[CsdbController::class, 'get_deletion_list'])->middleware('auth')->name('api.get_deletion_list');

// delete permanent csdb object
Route::post("/api/permanentdelete", [CsdbController::class, 'permanentDelete'])->middleware('auth')->name('api.permanentdelete_object');

// restore csdb object after deleted
Route::post("/api/restore", [CsdbController::class, 'restore'])->middleware('auth')->name('api.restore_object');

// change path
Route::post('/api/object/path/change', [CsdbController::class, 'change_object_path'])->middleware('auth')->name('api.change_object_path');

Route::post("/api/download", [CsdbController::class, 'download_objects'])->middleware('auth')->name('api.download_objects');

// get all history
Route::get("/api/histories/{filename}", [HistoryController::class, 'all'])->middleware('auth')->name('api.get_csdb_history');

// get list comment 
Route::get("/api/comments/{filename}",[CommentController::class, 'all'])->middleware('auth')->name('api.get_csdb_comments');

Route::get("/api/json/{filename}",[CsdbController::class, 'read_json'])->middleware('auth')->name('api.read_json');

// ### percobaan ###
Route::get("/api/content/html/{filename}",[DmlController::class, 'read_html_content'])->middleware('auth')->name('api.get_html_content');

Route::get('/tesapaja',function(){
  dd('aa');
  $DMLCSDBModel = Csdb::find(34);
  $CSLCSDBModel = $DMLCSDBModel->object->toCsl(request()->user());
  dd($CSLCSDBModel);
  // dd($DMLCSDBModel->object);
  // dd($DMLCSDBModel);

  // $CSDBModel = Csdb::find(26);
  // FillObjectTable::dispatch(request()->user(), $CSDBModel, true);
  // FillObjectTable::dispatchSync(request()->user(), $CSDBModel, true);
  // return;

  // $dom = new \DOMDocument();
  // $dom->load(CSDB_STORAGE_PATH."/E6IkA/DMC-MALE-A-15-00-01-00A-018A-A_000-01_EN-EN.xml");
  // $json = CSDBStatic::simple_xml_to_json($dom);
  // CSDBStatic::simple_decode_element($dom->firstElementChild, $element);
  // $element = json_encode($element);
  // dd($element, $json);
  // return;

  // $dom = new \DOMDocument();
  // $dom->load(CSDB_STORAGE_PATH."/E6IkA/DMC-MALE-A-15-00-01-00A-018A-A_000-01_EN-EN.xml");
  // $json = CSDBStatic::xml_to_json($dom);
  // CSDBStatic::decode_element($dom->firstElementChild, $element);
  // $element = json_encode($element);
  // $xml = CSDBStatic::json_to_xml($json);
  // dd($element, $json, $xml);
  // return;


  // Mail::to('ferdisaptoo@gmail.com')->send(new DataDispatchNote());
  // Mail::to('ferdiarrahman@indonesian-aerospace.com')->send(new DataDispatchNote());
  // Mail::to('ferdiarrahman@indonesian-aerospace.com')->send(new DataDispatchNote());
  // Mail::to('luffy@example.com')->send(new DataDispatchNote());
  // Mail::send(new DataDispatchNote());
  $DDNModel = Ddn::find(1);
  // Mail::send(new DataDispatchNote($DDNModel));
  Mail::queue(new DataDispatchNote($DDNModel));
  // dd($DDNModel);
  // DdnCreated::dispatch($DDNModel);
  return;
  dd(DdnCreated::dispatch(Csdb::find(1)));
  $csdb = Csdb::find(1);
  event(new DdnCreated($csdb));

  // $json_str = '{"foo":"bar"}';
  // $arr = ["foo" => "bar"];
  // $json_str2 = '"{\"foo\": \"bar\"}"';
  // json_decode($json_str2,true) return {"foo":"bar"}
  // json_decode($json_str,true) return array assoc
  // json_decode($json_str,true) return array assoc
  // json_decode($json_str,false) return StdClass
  // json_encode($json_str) return {\"foo\":\"bar\"}
  // json_encode($arr) return {"foo":"bar"}
  // dd($json_str, json_decode($json_str,true), json_encode($json_str), json_decode(json_encode($json_str)));

  // $json_str = '{}';
  // // $json_str = '[]';
  // // dd(json_encode($json_str));
  // dd(json_decode($json_str,true));

  // $json_str = '{"foo":"bar"}'; isJson TRUE
  // $json_str = '{\"foo\":\"bar\"}'; isJson FALSE
  // $isJson = function ($string) {
  //   json_decode($string);
  //   return json_last_error() === JSON_ERROR_NONE;
  // };
  // dd($isJson($json_str));

  // $arr = ["lorem ipsum","dolor sit amet"];
  // $json_str = json_encode($arr);
  // dd($json_str, Helper::isJsonString($json_str));

  // $e = (Enterprise::find(6));
  // dd($e->address, $e->remarks);

  // $name = fake()->company();
  // $code = rand(11111,99999);
  // $address = [
  //   'city' => 'Bandung',
  //   'country' => 'Indonesia',
  //   'phoneNumber' => ['08124839295746','08538493057594'],
  // ];
  // $remarks = null;
  // $enterprise = Enterprise::create([
  //   'name' => $name,
  //   'code' => $code,
  //   'address' => $address,
  //   'remarks' => 'tes null'
  //   // 'remarks_tes' => 'tes null'
  //   // 'remarksTes' => 'null tes'
  // ]);
  // dd($enterprise);

  // $enterprise = (Enterprise::find(1));
  // dd($enterprise->code->name);
});