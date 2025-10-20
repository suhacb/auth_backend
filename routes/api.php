<?php

use App\Http\Controllers\ApplicationsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');


Route::prefix('auth')->name('auth.')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::post('login-token', [AuthController::class, 'loginToken'])->name('login_token'); // Used to obtain one time login token from application
});

Route::prefix('applications')->name('applications.')->group(function () {
    Route::get('', [ApplicationsController::class, 'index'])->name('index');
    Route::get('{application}', [ApplicationsController::class, 'show'])->name('show');
    Route::post('', [ApplicationsController::class, 'store'])->name('store');
    Route::put('{application}', [ApplicationsController::class, 'update'])->name('update');
    Route::delete('{application}', [ApplicationsController::class, 'delete'])->name('delete');
});
