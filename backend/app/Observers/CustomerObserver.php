<?php
namespace App\Observers;

use App\Models\Customer;
use App\Models\MikrotikDevice;
use App\Services\MikrotikService;
use App\Services\SmsService;
use Illuminate\Support\Facades\Log;

class CustomerObserver
{
    public function created(Customer $customer): void
    {
        try {
            $mikrotik = new MikrotikService();
            $devices = MikrotikDevice::where('status', 'active')->get();

            foreach ($devices as $device) {
                if ($mikrotik->connect($device)) {
                    $mikrotik->addPPPoEUser(
                        $customer->username,
                        $customer->password,
                        'default'
                    );
                }
            }
        } catch (\Exception $e) {
            Log::error('CustomerObserver created error: ' . $e->getMessage());
        }
    }

    public function updated(Customer $customer): void
    {
        try {
            if (!$customer->isDirty('status')) return;

            $mikrotik = new MikrotikService();
            $devices = MikrotikDevice::where('status', 'active')->get();

            foreach ($devices as $device) {
                if (!$mikrotik->connect($device)) continue;

                if ($customer->status === 'active') {
                    $mikrotik->enablePPPoEUser($customer->username);
                    $sms = new SmsService();
                    $sms->accountActivated($customer->phone, ['name' => $customer->name]);
                } elseif (in_array($customer->status, ['inactive', 'suspended'])) {
                    $mikrotik->disablePPPoEUser($customer->username);
                }
            }
        } catch (\Exception $e) {
            Log::error('CustomerObserver updated error: ' . $e->getMessage());
        }
    }

    public function deleted(Customer $customer): void
    {
        try {
            $mikrotik = new MikrotikService();
            $devices = MikrotikDevice::where('status', 'active')->get();

            foreach ($devices as $device) {
                if ($mikrotik->connect($device)) {
                    $mikrotik->removePPPoEUser($customer->username);
                }
            }
        } catch (\Exception $e) {
            Log::error('CustomerObserver deleted error: ' . $e->getMessage());
        }
    }
}
