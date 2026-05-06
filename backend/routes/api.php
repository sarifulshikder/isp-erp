<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\MikrotikController;

Route::get('/dashboard', [DashboardController::class, 'index']);
Route::apiResource('packages', PackageController::class);
Route::apiResource('customers', CustomerController::class);
Route::apiResource('invoices', InvoiceController::class);
Route::apiResource('payments', PaymentController::class);
Route::apiResource('mikrotik', MikrotikController::class);

Route::prefix('mikrotik/{mikrotikDevice}')->group(function () {
    Route::get('test', [MikrotikController::class, 'testConnection']);
    Route::get('online-users', [MikrotikController::class, 'onlineUsers']);
    Route::get('system-info', [MikrotikController::class, 'systemInfo']);
    Route::post('enable-user', [MikrotikController::class, 'enableUser']);
    Route::post('disable-user', [MikrotikController::class, 'disableUser']);
    Route::post('add-customer', [MikrotikController::class, 'addCustomerToMikrotik']);
});

Route::get('invoices/{invoice}/pdf', [App\Http\Controllers\InvoicePdfController::class, 'download']);
