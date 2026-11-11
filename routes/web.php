<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DeploymentController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TelegramMappingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Route Sementara (Tahap Desain UI)
|--------------------------------------------------------------------------
| Data di bawah masih dummy. Nantinya dipindah ke Controller dan diambil
| dari database / Redmine API.
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Semua halaman menu di bawah ini hanya bisa diakses setelah login.
Route::middleware('auth')->group(function () {

Route::get('/', function () {
    $stats = [
        'total_users' => 24,
        'new_users'   => 3,
        'tasks_today' => 12,
        'tasks_done'  => 5,
        'redmine'     => true,
        'telegram'    => true,
    ];

    $deploys = [
        ['issue_id' => 1042, 'task' => 'Perbaikan modul login SSO',          'time' => '09:00', 'pic' => 'Budi Santoso',  'status' => 'Done'],
        ['issue_id' => 1057, 'task' => 'Update API laporan keuangan',        'time' => '11:30', 'pic' => 'Siti Aminah',   'status' => 'In Progress'],
        ['issue_id' => 1063, 'task' => 'Migrasi database server staging',   'time' => '13:00', 'pic' => 'Rudi Hartono',  'status' => 'Scheduled'],
        ['issue_id' => 1071, 'task' => 'Hotfix notifikasi email pengguna',   'time' => '15:00', 'pic' => 'Dewi Lestari',  'status' => 'Scheduled'],
        ['issue_id' => 1078, 'task' => 'Deploy fitur export PDF invoice',    'time' => '16:30', 'pic' => 'Andi Wijaya',   'status' => 'Failed'],
    ];

    $logs = [
        ['icon' => 'fa-paper-plane',        'color' => 'primary', 'text' => 'Reminder deploy #1063 dikirim ke @rudi_h',       'time' => '2 menit lalu'],
        ['icon' => 'fa-circle-check',       'color' => 'success', 'text' => 'Issue #1042 diupdate menjadi Done',              'time' => '15 menit lalu'],
        ['icon' => 'fa-link',               'color' => 'info',    'text' => 'User Dewi Lestari berhasil terhubung',           'time' => '1 jam lalu'],
        ['icon' => 'fa-triangle-exclamation','color' => 'danger', 'text' => 'Gagal kirim pesan ke Chat ID 58812345',          'time' => '2 jam lalu'],
        ['icon' => 'fa-rotate',             'color' => 'warning', 'text' => 'Sinkronisasi issue Redmine selesai (48 issue)',  'time' => '3 jam lalu'],
    ];

    return view('dashboard', compact('stats', 'deploys', 'logs'));
})->name('dashboard');

Route::resource('user-mappings', TelegramMappingController::class)
    ->only(['index', 'store', 'update', 'destroy']);

Route::get('/deployments', [DeploymentController::class, 'index'])->name('deployments.index');
Route::post('/deployments/sync', [DeploymentController::class, 'sync'])->name('deployments.sync');
Route::post('/deployments/{issueId}/notify', [DeploymentController::class, 'notify'])->whereNumber('issueId')->name('deployments.notify');

Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
Route::put('/settings/integration', [SettingController::class, 'updateIntegration'])->name('settings.integration');
Route::put('/settings/scheduler', [SettingController::class, 'updateScheduler'])->name('settings.scheduler');

});
