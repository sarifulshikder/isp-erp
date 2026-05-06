<?php
namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Invoice;
use App\Services\SmsService;
use Illuminate\Console\Command;

class GenerateMonthlyInvoices extends Command
{
    protected $signature = 'invoices:generate-monthly';
    protected $description = 'Auto generate invoices for customers expiring soon';

    public function handle(SmsService $sms)
    {
        $customers = Customer::where('status', 'active')
            ->whereDate('expire_date', '<=', now()->addDays(7)->toDateString())
            ->whereDate('expire_date', '>=', now()->toDateString())
            ->whereDoesntHave('invoices', function ($query) {
                $query->where('status', 'unpaid')
                      ->whereMonth('created_at', now()->month);
            })
            ->with('package')
            ->get();

        if ($customers->isEmpty()) {
            $this->info('No invoices to generate.');
            return;
        }

        foreach ($customers as $customer) {
            if (!$customer->package) continue;

            $invoice = Invoice::create([
                'invoice_no' => 'INV-' . date('Ymd') . '-' . rand(1000, 9999),
                'customer_id' => $customer->id,
                'package_id' => $customer->package_id,
                'amount' => $customer->package->price,
                'discount' => 0,
                'total' => $customer->package->price,
                'issue_date' => now()->toDateString(),
                'due_date' => $customer->expire_date,
                'status' => 'unpaid',
            ]);

            $sms->sendFromTemplate('sms_expiry_reminder', $customer->phone, [
                'name' => $customer->name,
                'amount' => $customer->package->price,
                'invoice_no' => $invoice->invoice_no,
                'expire_date' => $customer->expire_date,
            ]);

            $this->info("Invoice generated: {$invoice->invoice_no} for {$customer->name}");
        }

        $this->info("Total invoices generated: {$customers->count()}");
    }
}
