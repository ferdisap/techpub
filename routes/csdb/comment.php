<?php

use App\Http\Controllers\Csdb\CommentController;
use Illuminate\Support\Facades\Route;

Route::post("/api/createcomment",[CommentController::class, 'create'])->middleware('auth')->name('api.create_comment');