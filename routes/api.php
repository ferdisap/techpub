<?php

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
| lakukan GET /sanctum/csrf-cookie HTTP/1.1 kemudian di javascript lakukan decodeURIComponent(xsrf)
| atau get csrf by hit GET /get/csrf HTTP/1.1

*/

// Route::get("/get/csrf", function () {
//   return response(csrf_token(),200,[
//     "content-type" => 'text/plain'
//   ]);
// });

Route::middleware('auth:sanctum')->post('/user', function (Request $request) {
  return $request->user();
});

// Route::get("/s1000d/csdb/all",function(){
//   return 'foo';
// })->middleware('auth:sanctum')->name('api.get_csdbs'); // api.get_allobjects_list
