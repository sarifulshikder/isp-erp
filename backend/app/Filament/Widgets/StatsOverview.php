<?php
namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Package;
use App\Models\OnuMonitor;
use App\Models\MikrotikDevice;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $todayCollection = Payment::whereDate('paid_at', today())->sum('amount');
        $monthCollection = Payment::whereMonth('paid_at', now()->month)->whereYear('paid_at', now()->year)->sum('amount');
        $expiringCount = Customer::where('status', 'active')->whereDate('expire_date', '<=', now()->addDays(7))->whereDate('expire_date', '>=', today())->count();
        $criticalOnu = OnuMonitor::where('rx_power', '<', -26)->count();
        $suspendedCount = Customer::where('status', 'suspended')->count();
        $unpaidCount = Invoice::where('status', 'unpaid')->count();
        $unpaidTotal = Invoice::where('status', 'unpaid')->sum('total');

        return [
            Stat::make('মোট Customer', Customer::count())
                ->description('Active: ' . Customer::where('status', 'active')->count())
                ->descriptionIcon('heroicon-m-users')
                ->color('success')
                ->url('/admin/customers')
                ->chart([3,5,4,6,5,7,8]),

            Stat::make('Suspended', $suspendedCount)
                ->description('Inactive: ' . Customer::where('status', 'inactive')->count())
                ->descriptionIcon('heroicon-m-pause-circle')
                ->color('warning')
                ->url('/admin/customers?tableFilters[status][value]=suspended'),

            Stat::make('আজকের Collection', 'BDT ' . number_format($todayCollection, 2))
                ->description('এই মাসে: BDT ' . number_format($monthCollection, 2))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->url('/admin/payments'),

            Stat::make('মোট বাকি', 'BDT ' . number_format($unpaidTotal, 2))
                ->description('Unpaid invoices: ' . $unpaidCount)
                ->descriptionIcon('heroicon-m-clock')
                ->color('danger')
                ->url('/admin/invoices?tableFilters[status][value]=unpaid'),

            Stat::make('এই সপ্তাহে Expire', $expiringCount . ' জন')
                ->description('৭ দিনের মধ্যে expire')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color($expiringCount > 0 ? 'warning' : 'success')
                ->url('/admin/customers'),

            Stat::make('Critical ONU', $criticalOnu)
                ->description('Signal < -26 dBm')
                ->descriptionIcon('heroicon-m-signal-slash')
                ->color($criticalOnu > 0 ? 'danger' : 'success')
                ->url('/admin/onu-monitors'),

            Stat::make('মোট Package', Package::count())
                ->description('Active: ' . Package::where('status', 'active')->count())
                ->descriptionIcon('heroicon-m-square-3-stack-3d')
                ->color('info')
                ->url('/admin/packages'),

            Stat::make('Paid Invoices', Invoice::where('status', 'paid')->count())
                ->description('Total: ' . Invoice::count() . ' invoices')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->url('/admin/invoices'),
        ];
    }
}
