<?php
namespace App\Http\Controllers;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Package;
class DashboardController extends Controller {
    public function index() {
        $data = [
            'total_customers' => Customer::count(),
            'active_customers' => Customer::where('status', 'active')->count(),
            'expired_customers' => Customer::where('expire_date', '<', now())->count(),
            'total_packages' => Package::count(),
            'total_invoices' => Invoice::count(),
            'unpaid_invoices' => Invoice::where('status', 'unpaid')->count(),
            'total_collection' => Payment::whereMonth('paid_at', now()->month)->sum('amount'),
            'total_due' => Invoice::where('status', 'unpaid')->sum('total'),
            'recent_customers' => Customer::with('package')->latest()->take(5)->get(),
            'recent_payments' => Payment::with('customer')->latest()->take(5)->get(),
        ];
        return response()->json(['status' => 'success', 'data' => $data]);
    }
}
