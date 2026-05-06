<?php
namespace App\Observers;

use App\Models\Package;
use App\Models\MikrotikDevice;
use App\Services\MikrotikService;

class PackageObserver
{
    public function created(Package $package): void
    {
        $this->syncProfile($package);
    }

    public function updated(Package $package): void
    {
        $this->syncProfile($package);
    }

    private function syncProfile(Package $package): void
    {
        $devices = MikrotikDevice::where('status', 'active')->get();
        $mikrotik = new MikrotikService();

        // Rate limit format: download/upload in Mbps
        $rateLimit = $package->speed_download . 'M/' . $package->speed_upload . 'M';

        foreach ($devices as $device) {
            if ($mikrotik->connect($device)) {
                $mikrotik->addProfile($package->name, $rateLimit);
            }
        }
    }
}
