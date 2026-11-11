<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminAuditController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminRecordController;
use App\Http\Controllers\AdminReportController;
use App\Http\Controllers\AdminRoleController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'auth.login')->middleware('guest.redirect');

Route::middleware('guest.redirect')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');

    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');

    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/modules/products/import-template', [AdminRecordController::class, 'productImportTemplate'])->name('modules.products.import-template');
        Route::get('/modules/{module}/import', [AdminRecordController::class, 'importForm'])->name('modules.import.form');
        Route::post('/modules/{module}/import', [AdminRecordController::class, 'import'])->name('modules.import');
        Route::get('/modules/{module}', [AdminRecordController::class, 'index'])->name('modules.index');
        Route::get('/modules/{module}/create', [AdminRecordController::class, 'create'])->name('modules.create');
        Route::post('/modules/{module}', [AdminRecordController::class, 'store'])->name('modules.store');
        Route::get('/modules/{module}/{record}/edit', [AdminRecordController::class, 'edit'])->whereNumber('record')->name('modules.edit');
        Route::put('/modules/{module}/{record}', [AdminRecordController::class, 'update'])->whereNumber('record')->name('modules.update');
        Route::delete('/modules/{module}/{record}', [AdminRecordController::class, 'destroy'])->whereNumber('record')->name('modules.destroy');

        Route::get('/users/export/{format}', [AdminUserController::class, 'export'])
            ->whereIn('format', ['csv', 'excel'])
            ->name('users.export');
        Route::resource('users', AdminUserController::class);
        Route::resource('roles', AdminRoleController::class)->except('show');
        Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
        Route::get('/audit-logs', [AdminAuditController::class, 'index'])->name('audit.index');
        Route::post('/backups', [AdminAuditController::class, 'backup'])->name('audit.backup');
    });

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
