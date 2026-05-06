<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $customer = Auth::guard('customer')->user()->load('package');
        $recentInvoices = $customer->invoices()->latest()->take(3)->get();
        $recentPayments = $customer->payments()->latest()->take(3)->get();

        $daysLeft = null;
        if ($customer->expire_date) {
            $daysLeft = now()->diffInDays($customer->expire_date, false);
        }

        return view('portal.dashboard', compact('customer', 'recentInvoices', 'recentPayments', 'daysLeft'));
    }
}
