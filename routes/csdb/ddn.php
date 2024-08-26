<?php

use App\Http\Controllers\Csdb\DdnController;
use Illuminate\Support\Facades\Route;

Route::post("/api/ddn/create",[DdnCOntroller::class, 'create'])->middleware('auth')->name('api.create_ddn');

// get dispatced ddn
Route::get("/api/ddn/dispatched",[DdnController::class, 'list'])->middleware('auth')->name('api.get_ddn_list');

// import csdb object
Route::post('/api/ddn-import/{filename}', [DdnController::class, 'import'])->middleware('auth')->name('api.import_ddn_list');

// access json
Route::get("/api/ddn-json/{filename}",[DdnController::class, 'read_json'])->middleware('auth')->name('api.read_ddnjson');