<?php
namespace App\Providers;

use App\Models\Package;
use App\Models\Customer;
use App\Observers\PackageObserver;
use App\Observers\CustomerObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Package::observe(PackageObserver::class);
        Customer::observe(CustomerObserver::class);
    }
}
