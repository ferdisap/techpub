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
use Illuminate\Support\Facades\Route;

Route::post('/applogin', [Authenticate::class, 'store'])->middleware('guest')->name('app.login');
Route::post('/applogout', [Authenticate::class, 'destroy'])->middleware('auth:sanctum')->name('app.logout');