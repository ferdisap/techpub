<?php

use App\Http\Controllers\BrdpController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\DmoduleController;
use App\Http\Controllers\BrexController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Spipu\Html2Pdf\Exception\ExceptionFormatter;
use Spipu\Html2Pdf\Exception\Html2PdfException;
use Spipu\Html2Pdf\Html2Pdf;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [Controller::class, 'index'])->name('welcome');

require __DIR__.'/auth.php';
require __Dir__."/enterprise/general.php";
require __Dir__."/csdb/general.php";
require __Dir__."/csdb/comment.php";
require __Dir__."/csdb/ddn.php";
require __Dir__."/csdb/dml.php";
require __Dir__."/csdb/br.php";

Route::get('/auth/check', [Controller::class, 'authcheck'])->middleware('auth'); // berguna untuk vue
Route::get('/route/{name}', [Controller::class, 'route']); // masih digunakan di xsl
Route::get('/getAllRoutes', [Controller::class, 'getAllRoutesNamed']); // berguna untuk vue

Route::get("/api/info/{name}", function(Request $request, string $name){
  $content = file_get_contents(resource_path("notes/info/{$name}.md"));
  if($content){
    $status = 200;
    $contentType = "text/markdown";
  }
  return Response::make($content ?? '',$status ?? 400,[
    "Content-Type" => $contentType ?? "text/plain"
  ]);
})->middleware('auth')->name('api.info');

Route::get("/api/alert/{name}", function(Request $request, string $name){
  $content = file_get_contents(resource_path("notes/alert/{$name}.md"));
  if($content){
    $status = 200;
    $contentType = "text/markdown";
  }
  return Response::make($content ?? '',$status ?? 400,[
    "Content-Type" => $contentType ?? "text/plain"
  ]);
})->middleware('auth')->name('api.alert');

Route::get('/worker/{filename}', [Controller::class, 'getWorker'])->middleware('auth');