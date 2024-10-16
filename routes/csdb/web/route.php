<?php

use App\Http\Controllers\Csdb\CsdbController;
use App\Http\Controllers\CsdbApi\MainController;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| CSDB WEB Routes
|--------------------------------------------------------------------------
| 
| This routes are assigned to the "web" middleware 
| group by RouteServiceProvider.
|
|
*/

Route::middleware('auth')->group(function () {
  Route::get("/csdb/{view?}",[CsdbController::class, 'app'])->where('view','(.*)');
});

// Route::get("/s1000d/csdb/all",[CsdbController::class, 'getCsdbs'])->name('api.get_csdbs'); // api.get_allobjects_list