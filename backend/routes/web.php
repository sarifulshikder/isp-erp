<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Portal\AuthController;
use App\Http\Controllers\Portal\DashboardController;
use App\Http\Controllers\Portal\InvoiceController;
use App\Http\Controllers\Portal\BkashController;

// ─── Welcome ─────────────────────────────────────────────────────────────────
Route::get('/', function () {
    return redirect()->route('portal.login');
});

// ─── Portal Guest Routes (login না করলে) ─────────────────────────────────────
Route::middleware('guest:customer')->group(function () {
    Route::get('/portal/login', [AuthController::class, 'showLogin'])->name('portal.login');
    Route::post('/portal/login', [AuthController::class, 'login'])->name('portal.login.post');
});

// ─── Portal Auth Routes (login করলে) ─────────────────────────────────────────
Route::middleware('auth:customer')->prefix('portal')->name('portal.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Invoices
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{id}', [InvoiceController::class, 'show'])->name('invoices.show');

    // bKash Payment
    Route::get('/invoices/{invoiceId}/bkash/pay', [BkashController::class, 'initiate'])->name('bkash.initiate');
    Route::get('/invoices/{invoiceId}/bkash/callback', [BkashController::class, 'callback'])->name('bkash.callback');

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
