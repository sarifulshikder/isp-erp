<?php
namespace App\Filament\Widgets;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Package;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('মোট Customer', Customer::count())
                ->description('Active: ' . Customer::where('status', 'active')->count())
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),

            Stat::make('Expired Customer', Customer::where('expire_date', '<', now())->count())
                ->description('Needs renewal')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color('danger'),

            Stat::make('এই মাসের Collection', 'BDT ' . number_format(Payment::whereMonth('paid_at', now()->month)->sum('amount'), 2))
                ->description('Total payments this month')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('মোট বাকি', 'BDT ' . number_format(Invoice::where('status', 'unpaid')->sum('total'), 2))
                ->description('Unpaid invoices: ' . Invoice::where('status', 'unpaid')->count())
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('মোট Package', Package::count())
                ->description('Active: ' . Package::where('status', 'active')->count())
                ->descriptionIcon('heroicon-m-square-3-stack-3d')
                ->color('info'),

            Stat::make('Paid Invoices', Invoice::where('status', 'paid')->count())
                ->description('Total: ' . Invoice::count() . ' invoices')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}
