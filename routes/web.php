<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeploymentController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TelegramBotController;
use App\Http\Controllers\TelegramMappingController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

// Dipanggil Telegram (bukan browser): di luar auth, bebas CSRF, dijaga secret token.
Route::post('/telegram/webhook', [TelegramBotController::class, 'handleWebhook'])->name('telegram.webhook');

Route::post('/logout',[AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Semua halaman menu di bawah ini hanya bisa diakses setelah login.
Route::middleware('auth')->group(function () {

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::resource('user-mappings', TelegramMappingController::class)
    ->only(['index', 'store', 'update', 'destroy']);

Route::get('/deployments', [DeploymentController::class, 'index'])->name('deployments.index');
Route::post('/deployments/sync', [DeploymentController::class, 'sync'])->name('deployments.sync');
Route::post('/deployments/{deployment}/reminder', [DeploymentController::class, 'sendReminder'])->name('deployments.reminder');

Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');

});
