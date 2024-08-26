<?php

use App\Events\Csdb\DdnCreated;
use App\Http\Controllers\Csdb\CommentController;
use App\Http\Controllers\Csdb\CsdbController;
use App\Http\Controllers\Csdb\HistoryController;
use App\Http\Controllers\CsdbServiceController;
use App\Http\Controllers\Csdb\DmlController;
use App\Http\Controllers\EnterpriseController;
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
Route::get("/api/get", [EnterpriseController::class, 'get_enterprises'])->middleware('auth')->name('api.get_enterprises');