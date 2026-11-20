<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/dashboard');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/admin', [DashboardController::class, 'superAdmin'])->name('admin');
Route::get('/portal/{token}', [DashboardController::class, 'portal'])->name('portal');
Route::get('/email-preview/{token?}', [DashboardController::class, 'emailPreview'])->name('email.preview');
