<?php
namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\MikrotikDevice;
use App\Services\MikrotikService;
use App\Services\SmsService;
use Illuminate\Console\Command;

class SuspendExpiredCustomers extends Command
{
    protected $signature = 'customers:suspend-expired';
    protected $description = 'Suspend expired customers on MikroTik and send SMS';

    public function handle(MikrotikService $mikrotik, SmsService $sms)
    {
        $expired = Customer::where('expire_date', '<', now())
            ->where('status', 'active')->get();

        if ($expired->isEmpty()) {
            $this->info('No expired customers found.');
            return;
        }

        $devices = MikrotikDevice::where('status', 'active')->get();

        foreach ($expired as $customer) {
            $customer->update(['status' => 'suspended']);

            foreach ($devices as $device) {
                if ($mikrotik->connect($device)) {
                    $mikrotik->disablePPPoEUser($customer->username);
                    $this->info("Suspended: {$customer->name} on {$device->name}");
                }
            }

            $sms->accountSuspended($customer->phone, [
                'name' => $customer->name,
                'expire_date' => $customer->expire_date,
            ]);
        }

        $this->info("Total suspended: {$expired->count()} customers.");
    }
}
