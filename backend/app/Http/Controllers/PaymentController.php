<?php
namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Invoice;
use App\Services\SmsService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with('customer', 'invoice');
        if ($request->customer_id) $query->where('customer_id', $request->customer_id);
        if ($request->method) $query->where('method', $request->method);
        return response()->json($query->latest()->paginate(20));
    }

    public function store(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'customer_id' => 'required|exists:customers,id',
            'amount' => 'required|numeric',
            'method' => 'required|in:cash,bkash,nagad,rocket,bank',
        ]);

        $payment = Payment::create(array_merge(
            $request->all(),
            ['paid_at' => now()]
        ));

        // Update invoice status
        $invoice = Invoice::find($request->invoice_id);
        $paid = $invoice->payments()->sum('amount');
        if ($paid >= $invoice->total) {
            $invoice->update(['status' => 'paid', 'paid_date' => now()->toDateString()]);
        } else {
            $invoice->update(['status' => 'partial']);
        }

        // Send SMS
        $sms = new SmsService();
        $customer = $payment->customer;
        $sms->paymentReceived($customer->phone, [
            'name' => $customer->name,
            'amount' => $request->amount,
            'invoice_no' => $invoice->invoice_no,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $payment->load('customer', 'invoice')
        ], 201);
    }

    public function show(Payment $payment)
    {
        return response()->json($payment->load('customer', 'invoice'));
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();
        return response()->json(['status' => 'success', 'message' => 'Payment deleted']);
    }
}
