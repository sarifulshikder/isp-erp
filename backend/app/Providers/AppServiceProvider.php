<?php
namespace App\Providers;
use App\Models\Package;
use App\Models\Customer;
use App\Models\MikrotikDevice;
use App\Observers\PackageObserver;
use App\Observers\CustomerObserver;
use App\Observers\MikrotikDeviceObserver;
use Illuminate\Support\ServiceProvider;
class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}
    public function boot(): void
    {
        Package::observe(PackageObserver::class);
        Customer::observe(CustomerObserver::class);
        MikrotikDevice::observe(MikrotikDeviceObserver::class);
    }
}
