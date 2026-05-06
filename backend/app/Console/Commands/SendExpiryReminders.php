<?php
namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Setting;
use App\Services\SmsService;
use Illuminate\Console\Command;

class SendExpiryReminders extends Command
{
    protected $signature = 'customers:expiry-reminders';
    protected $description = 'Send SMS reminders to customers about to expire';

    public function handle(SmsService $sms)
    {
        $days = (int) Setting::get('expiry_reminder_days', 3);

        $customers = Customer::where('status', 'active')
            ->whereDate('expire_date', now()->addDays($days)->toDateString())
            ->get();

        if ($customers->isEmpty()) {
            $this->info('No customers expiring soon.');
            return;
        }

        foreach ($customers as $customer) {
            $sms->expiryReminder($customer->phone, [
                'name' => $customer->name,
                'expire_date' => $customer->expire_date,
            ]);
            $this->info("Reminder sent to: {$customer->name} ({$customer->phone})");
        }

        $this->info("Total reminders sent: {$customers->count()}");
    }
}
