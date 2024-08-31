<?php

use App\Http\Controllers\Csdb\BrController;
use Illuminate\Support\Facades\Route;

Route::get("/api/br/{filename}",[BrController::class, 'json_file'])->middleware('auth')->name('api.read_json_br');