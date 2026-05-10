<?php
namespace App\Observers;
use App\Models\MikrotikDevice;
use App\Services\MikrotikService;
use Illuminate\Support\Facades\Log;
class MikrotikDeviceObserver
{
    public function created(MikrotikDevice $device): void
    {
        if ($device->status === 'active') {
            try {
                $mikrotik = new MikrotikService();
                $results = $mikrotik->pushAllModeCustomersToDevice($device);
                Log::info("Auto-push to new device [{$device->name}]: pushed={$results['pushed']}, failed={$results['failed']}");
            } catch (\Exception $e) {
                Log::error('MikrotikDeviceObserver created error: ' . $e->getMessage());
            }
        }
    }
    public function updated(MikrotikDevice $device): void
    {
        if ($device->wasChanged('status') && $device->status === 'active') {
            try {
                $mikrotik = new MikrotikService();
                $results = $mikrotik->pushAllModeCustomersToDevice($device);
                Log::info("Auto-push on device activated [{$device->name}]: pushed={$results['pushed']}, failed={$results['failed']}");
            } catch (\Exception $e) {
                Log::error('MikrotikDeviceObserver updated error: ' . $e->getMessage());
            }
        }
    }
}
