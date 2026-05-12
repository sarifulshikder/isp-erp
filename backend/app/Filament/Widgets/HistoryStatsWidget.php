<?php
namespace App\Filament\Widgets;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use Filament\Widgets\Widget;
class HistoryStatsWidget extends Widget
{
    protected static ?int $sort = 6;
    protected int|string|array $columnSpan = 'full';
    protected string $view = 'filament.widgets.history-stats';
    public function getViewData(): array
    {
        return [
            'registration' => [
                'today'       => Customer::whereDate('created_at', today())->count(),
                'yesterday'   => Customer::whereDate('created_at', today()->subDay())->count(),
                'this_month'  => Customer::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
                'last_month'  => Customer::whereMonth('created_at', now()->subMonth()->month)->whereYear('created_at', now()->subMonth()->year)->count(),
                'total'       => Customer::count(),
            ],
            'expiration' => [
                'today'         => Customer::whereDate('expire_date', today())->count(),
                'yesterday'     => Customer::whereDate('expire_date', today()->subDay())->count(),
                'tomorrow'      => Customer::whereDate('expire_date', today()->addDay())->count(),
                'next_7_days'   => Customer::whereDate('expire_date', '>', today())->whereDate('expire_date', '<=', today()->addDays(7))->count(),
                'expired_7days' => Customer::whereDate('expire_date', '>=', today()->subDays(7))->whereDate('expire_date', '<', today())->count(),
            ],
            'invoice' => [
                'pending'       => Invoice::where('status', 'unpaid')->count(),
                'today'         => Invoice::where('status', 'paid')->whereDate('updated_at', today())->count(),
                'yesterday'     => Invoice::where('status', 'paid')->whereDate('updated_at', today()->subDay())->count(),
                'this_month'    => Invoice::where('status', 'paid')->whereMonth('updated_at', now()->month)->whereYear('updated_at', now()->year)->count(),
                'total_unpaid'  => 'BDT ' . number_format(Invoice::where('status', 'unpaid')->sum('total'), 0),
            ],
            'collection' => [
                'today'      => 'BDT ' . number_format(Payment::whereDate('paid_at', today())->sum('amount'), 0),
                'yesterday'  => 'BDT ' . number_format(Payment::whereDate('paid_at', today()->subDay())->sum('amount'), 0),
                'this_month' => 'BDT ' . number_format(Payment::whereMonth('paid_at', now()->month)->whereYear('paid_at', now()->year)->sum('amount'), 0),
                'last_month' => 'BDT ' . number_format(Payment::whereMonth('paid_at', now()->subMonth()->month)->whereYear('paid_at', now()->subMonth()->year)->sum('amount'), 0),
                'total'      => 'BDT ' . number_format(Payment::sum('amount'), 0),
            ],
        ];
    }
}
