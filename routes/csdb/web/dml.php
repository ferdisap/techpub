<?php

use App\Http\Controllers\CsdbController;
use App\Http\Controllers\CsdbServiceController;
use App\Http\Controllers\Csdb\DmlController;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;

Route::post("/api/createdml",[DmlController::class, 'create'])->middleware('auth')->name('api.create_dml');
Route::get("/api/dmrl/all",[DmlController::class, 'get_dmrl_list'])->middleware('auth')->name('api.get_dmrl_list');
// Route::post("/api/dmlupdate/{filename}",[DmlController::class, 'dmlupdate'])->middleware('auth')->name('api.dmlupdate');
Route::post("/api/dmlupdate/{filename}",[DmlController::class, 'update'])->middleware('auth')->name('api.dmlupdate');
Route::get("/api/csl/{filename}", [DmlController::class, 'getCsl'])->middleware('auth')->name('api.get_csl');

Route::get("/api/dml/{filename}/merge", [DmlController::class, 'merge'])->middleware('auth')->name('api.dml_merge');