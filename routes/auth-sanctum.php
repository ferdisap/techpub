<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Sanctum\Authenticate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [Authenticate::class, 'store'])->name('app.login');
Route::post('/auth-check', function(Request $request){
  return response([
    'user' => $request->user()->only(["email", 'first_name', 'middle_name', 'last_name', 'job_title', 'accessKey']),
  ],200,['content-type' => 'application/json']);
})->middleware('auth:sanctum')->name('app.auth_check');
Route::post('/logout', [Authenticate::class, 'destroy'])->middleware('auth:sanctum')->name('app.logout');