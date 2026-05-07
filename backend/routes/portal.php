<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Portal\AuthController;
use App\Http\Controllers\Portal\DashboardController;
use App\Http\Controllers\Portal\InvoiceController;
use App\Http\Controllers\Portal\BkashController;

// Guest routes
Route::middleware('customer.guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('portal.login');
    Route::post('/login', [AuthController::class, 'login'])->name('portal.login.post');
});

// Authenticated routes
Route::middleware('customer.auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('portal.dashboard');
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('portal.invoices');
    Route::get('/invoices/{id}', [InvoiceController::class, 'show'])->name('portal.invoice.show');
    Route::post('/logout', [AuthController::class, 'logout'])->name('portal.logout');

    // bKash Payment
    Route::get('/invoices/{invoiceId}/bkash/pay', [BkashController::class, 'initiate'])->name('portal.bkash.initiate');
    Route::get('/invoices/{invoiceId}/bkash/callback', [BkashController::class, 'callback'])->name('portal.bkash.callback');
});
