<?php

use App\Http\Controllers\Csdb\BrController;
use Illuminate\Support\Facades\Route;

// sejauh ini belum dipakai. Pengennya nanti buat js/app khusus untuk brex
Route::get("/api/br/{filename}",[BrController::class, 'json_file'])->middleware('auth')->name('api.read_json_br');

Route::post("/api/br/validate",[BrController::class, 'validate'])->middleware('auth')->name('api.validate_by_brex');
