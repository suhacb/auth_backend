<?php

use App\Http\Controllers\ApplicationsController;
use App\Http\Controllers\HealthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('health', [HealthController::class, 'check'])->name('health');

Route::prefix('auth')->name('auth.')->middleware('verify.application')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::post('login-token', [AuthController::class, 'loginToken'])->name('login_token'); // Used to obtain one time login token from application
    Route::get('validate-access-token', [AuthController::class, 'validateAccessToken'])->name('validate-access-token');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
});

Route::prefix('applications')->name('applications.')->middleware('verify.auth.application')->group(function () {
    Route::get('', [ApplicationsController::class, 'index'])->name('index');
    Route::get('{application}', [ApplicationsController::class, 'show'])->name('show');
    Route::post('', [ApplicationsController::class, 'store'])->name('store');
    Route::put('{application}', [ApplicationsController::class, 'update'])->name('update');
    Route::delete('{application}', [ApplicationsController::class, 'delete'])->name('delete');
});
