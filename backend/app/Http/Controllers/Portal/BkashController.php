<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\BkashService;
use App\Services\SmsService;
use App\Services\MikrotikService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BkashController extends Controller
{
    private BkashService $bkash;

    public function __construct(BkashService $bkash)
    {
        $this->bkash = $bkash;
    }

    // ─── Step 1: "Pay with bKash" button ────────────────────────────────────
    public function initiate(Request $request, int $invoiceId)
    {
        $customer = Auth::guard('customer')->user();
        $invoice  = $customer->invoices()
                             ->where('id', $invoiceId)
                             ->where('status', 'unpaid')
                             ->firstOrFail();

        $callbackUrl = route('portal.bkash.callback', ['invoiceId' => $invoiceId]);

        $result = $this->bkash->createPayment(
            (string) $invoice->id,
            (float)  $invoice->amount,
            $callbackUrl
        );

        if (!$result['success']) {
            return back()->with('error', 'bKash payment শুরু করা যায়নি: ' . $result['message']);
        }

        session(['bkash_payment_id' => $result['paymentID']]);

        return redirect($result['bkashURL']);
    }

    // ─── Step 2: bKash callback ──────────────────────────────────────────────
    public function callback(Request $request, int $invoiceId)
    {
        $status    = $request->query('status');
        $paymentId = $request->query('paymentID') ?? session('bkash_payment_id');

        Log::info('bKash callback', ['status' => $status, 'paymentID' => $paymentId]);

        if ($status !== 'success' || !$paymentId) {
            return redirect()->route('portal.invoices.show', $invoiceId)
                             ->with('error', 'Payment বাতিল অথবা ব্যর্থ হয়েছে।');
        }

        $result = $this->bkash->executePayment($paymentId);

        if (!$result['success']) {
            return redirect()->route('portal.invoices.show', $invoiceId)
                             ->with('error', 'Payment execute ব্যর্থ: ' . $result['message']);
        }

        // Invoice paid করো
        $invoice = Invoice::findOrFail($invoiceId);
        $invoice->update([
            'status'  => 'paid',
            'paid_at' => now(),
        ]);

        // Payment record
        Payment::create([
            'customer_id' => $invoice->customer_id,
            'invoice_id'  => $invoice->id,
            'amount'      => $result['amount'],
            'method'      => 'bkash',
            'trx_id'      => $result['trxID'],
            'note'        => 'bKash: ' . ($result['customerMsisdn'] ?? ''),
        ]);

        $customer = $invoice->customer;

        // SMS
        try {
            (new SmsService())->send(
                $customer->phone,
                "প্রিয় {$customer->name}, আপনার {$invoice->amount} টাকা bKash পেমেন্ট সফল। TrxID: {$result['trxID']}। ধন্যবাদ।"
            );
        } catch (\Exception $e) {
            Log::warning('SMS failed', ['error' => $e->getMessage()]);
        }

        // MikroTik enable
        try {
            if ($customer->status === 'suspended') {
                $device = $customer->mikrotikDevice;
                if ($device) {
                    (new MikrotikService($device))->enableUser($customer->pppoe_username);
                    $customer->update(['status' => 'active']);
                }
            }
        } catch (\Exception $e) {
            Log::warning('MikroTik enable failed', ['error' => $e->getMessage()]);
        }

        session()->forget('bkash_payment_id');

        return redirect()->route('portal.invoices.show', $invoiceId)
                         ->with('success', "✅ Payment সফল! TrxID: {$result['trxID']}");
    }
}
