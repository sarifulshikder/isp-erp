<?php
namespace App\Observers;
use App\Models\Customer;
use App\Models\MikrotikDevice;
use App\Services\MikrotikService;
use App\Services\SmsService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class CustomerObserver
{
    public function created(Customer $customer): void
    {
        // MikroTik PPPoE user add
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
            Log::error('CustomerObserver created MikroTik error: ' . $e->getMessage());
        }

        // FreeRADIUS user add
        $this->syncRadiusUser($customer->username, $customer->password);
    }

    public function updated(Customer $customer): void
    {
        // Password পরিবর্তন হলে radius আপডেট
        if ($customer->isDirty('password')) {
            $this->syncRadiusUser($customer->username, $customer->password);
        }

        // Status পরিবর্তন না হলে বাকি কিছু করার নেই
        if (!$customer->isDirty('status')) return;

        try {
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

                    // n8n webhook — Zammad ticket create
                    try {
                        Http::timeout(5)->post('http://isp_n8n:5678/webhook/88e6acce-63f2-4cf2-8fd4-624ce711952a', [
                            'customer_name' => $customer->name,
                            'username'      => $customer->username,
                            'phone'         => $customer->phone,
                            'status'        => $customer->status,
                        ]);
                    } catch (\Exception $webhookEx) {
                        Log::warning('n8n webhook error: ' . $webhookEx->getMessage());
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('CustomerObserver updated error: ' . $e->getMessage());
        }
    }

    public function deleted(Customer $customer): void
    {
        // MikroTik থেকে remove
        try {
            $mikrotik = new MikrotikService();
            $devices = MikrotikDevice::where('status', 'active')->get();
            foreach ($devices as $device) {
                if ($mikrotik->connect($device)) {
                    $mikrotik->removePPPoEUser($customer->username);
                }
            }
        } catch (\Exception $e) {
            Log::error('CustomerObserver deleted MikroTik error: ' . $e->getMessage());
        }

        // Radius থেকে remove
        try {
            DB::connection('radius')->table('radcheck')
                ->where('username', $customer->username)->delete();
            DB::connection('radius')->table('radusergroup')
                ->where('username', $customer->username)->delete();
        } catch (\Exception $e) {
            Log::warning('Radius delete error: ' . $e->getMessage());
        }
    }

    private function syncRadiusUser(string $username, string $password): void
    {
        try {
            DB::connection('radius')->table('radcheck')->updateOrInsert(
                ['username' => $username, 'attribute' => 'Cleartext-Password'],
                ['op' => ':=', 'value' => $password]
            );
        } catch (\Exception $e) {
            Log::warning('Radius sync error: ' . $e->getMessage());
        }
    }
}
