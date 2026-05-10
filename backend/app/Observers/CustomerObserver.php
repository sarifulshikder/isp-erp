<?php
namespace App\Observers;
use App\Models\Customer;
use App\Models\SupportTicket;
use App\Services\MikrotikService;
use App\Services\SmsService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
class CustomerObserver
{
    public function created(Customer $customer): void
    {
        // MikroTik — mode অনুযায়ী
        try {
            $mikrotik = new MikrotikService();
            if (!$customer->usesFreeRadiusOnly()) {
                $mikrotik->syncAddCustomer($customer);
            }
        } catch (\Exception $e) {
            Log::error('CustomerObserver created MikroTik error: ' . $e->getMessage());
        }
        // FreeRADIUS — শুধু PPPoE এর জন্য
        if ($customer->connection_type === 'pppoe' && $customer->password) {
            $this->syncRadiusUser($customer->username, $customer->password);
        }
    }
    public function updated(Customer $customer): void
    {
        // ── MikroTik mode বা device পরিবর্তন হলে ──────────────────────
        if ($customer->wasChanged('mikrotik_mode') || $customer->wasChanged('mikrotik_device_id')) {
            try {
                $mikrotik = new MikrotikService();
                $mikrotik->syncRemoveCustomerFromAll($customer);
                if (!$customer->usesFreeRadiusOnly()) {
                    $mikrotik->syncAddCustomer($customer);
                }
            } catch (\Exception $e) {
                Log::error('CustomerObserver mode change error: ' . $e->getMessage());
            }
        }
        // ── Password পরিবর্তন হলে radius আপডেট (PPPoE only) ──────────
        if ($customer->wasChanged('password') && $customer->connection_type === 'pppoe') {
            $this->syncRadiusUser($customer->username, $customer->password);
        }
        // ── Status পরিবর্তন না হলে বাকি কিছু করার নেই ────────────────
        if (!$customer->wasChanged('status')) return;
        try {
            $mikrotik = new MikrotikService();
            if ($customer->status === 'active') {
                $mikrotik->syncEnableCustomer($customer);
                $sms = new SmsService();
                $sms->accountActivated($customer->phone, ['name' => $customer->name]);
            } elseif (in_array($customer->status, ['inactive', 'suspended'])) {
                $mikrotik->syncDisableCustomer($customer);
                // Support Ticket — n8n/Zammad এর বদলে সরাসরি
                try {
                    SupportTicket::create([
                        'subject'     => "Customer Suspended: {$customer->name}",
                        'description' => "Customer: {$customer->name}\nUsername: {$customer->username}\nPhone: {$customer->phone}\nStatus: {$customer->status}",
                        'priority'    => 'medium',
                        'status'      => 'open',
                        'customer_id' => $customer->id,
                    ]);
                } catch (\Exception $e) {
                    Log::warning('Support ticket creation error: ' . $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            Log::error('CustomerObserver updated error: ' . $e->getMessage());
        }
    }
    public function deleted(Customer $customer): void
    {
        // সব MikroTik device থেকে remove
        try {
            $mikrotik = new MikrotikService();
            $mikrotik->syncRemoveCustomerFromAll($customer);
        } catch (\Exception $e) {
            Log::error('CustomerObserver deleted MikroTik error: ' . $e->getMessage());
        }
        // Radius থেকে remove (PPPoE only)
        if ($customer->connection_type === 'pppoe') {
            try {
                DB::connection('radius')->table('radcheck')
                    ->where('username', $customer->username)->delete();
                DB::connection('radius')->table('radusergroup')
                    ->where('username', $customer->username)->delete();
            } catch (\Exception $e) {
                Log::warning('Radius delete error: ' . $e->getMessage());
            }
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
