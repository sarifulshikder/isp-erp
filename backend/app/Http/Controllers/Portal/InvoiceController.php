<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    public function index()
    {
        $customer = Auth::guard('customer')->user();
        $invoices = $customer->invoices()->latest()->paginate(10);
        return view('portal.invoices', compact('invoices'));
    }

    public function show($id)
    {
        $customer = Auth::guard('customer')->user();
        $invoice = $customer->invoices()->findOrFail($id);
        return view('portal.invoice-detail', compact('invoice'));
    }
}
