<?php
namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Package;
use App\Models\Zone;
use Carbon\Carbon;

class Reports extends Page
{
    protected static ?string $slug = 'reports';

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-chart-bar';
    }

    public static function getNavigationLabel(): string
    {
        return 'Reports';
    }

    public static function getNavigationGroup(): string
    {
        return 'Reports';
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    public function getTitle(): string
    {
        return 'Advanced Reports';
    }

    public function getView(): string
    {
        return 'filament.pages.reports';
    }

    public function getViewData(): array
    {
        $currentMonth = now()->month;
        $currentYear  = now()->year;

        // Zone wise report
        $zoneReport = Zone::withCount('customers')->get()->map(function ($zone) use ($currentMonth, $currentYear) {
            $customerIds = Customer::where('zone_id', $zone->id)->pluck('id');
            $monthly = Payment::whereIn('customer_id', $customerIds)
                ->whereMonth('paid_at', $currentMonth)
                ->whereYear('paid_at', $currentYear)
                ->sum('amount');
            $total = Payment::whereIn('customer_id', $customerIds)->sum('amount');
            return [
                'name'      => $zone->name,
                'area'      => $zone->area,
                'customers' => $zone->customers_count,
                'monthly'   => $monthly,
                'total'     => $total,
            ];
        });

        // Package wise report
        $packageReport = Package::withCount('customers')->get()->map(function ($pkg) use ($currentMonth, $currentYear) {
            $customerIds = Customer::where('package_id', $pkg->id)->pluck('id');
            $monthly = Payment::whereIn('customer_id', $customerIds)
                ->whereMonth('paid_at', $currentMonth)
                ->whereYear('paid_at', $currentYear)
                ->sum('amount');
            return [
                'name'      => $pkg->name,
                'price'     => $pkg->price,
                'customers' => $pkg->customers_count,
                'monthly'   => $monthly,
            ];
        });

        // Monthly collection (last 6 months)
        $monthlyCollection = collect();
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthlyCollection->push([
                'month'  => $date->format('M Y'),
                'amount' => Payment::whereYear('paid_at', $date->year)->whereMonth('paid_at', $date->month)->sum('amount'),
                'count'  => Payment::whereYear('paid_at', $date->year)->whereMonth('paid_at', $date->month)->count(),
            ]);
        }

        // Due/Unpaid report
        $dueReport = Invoice::where('status', 'unpaid')
            ->with('customer:id,name,phone,zone_id', 'customer.zone:id,name')
            ->orderBy('created_at')
            ->get()
            ->map(function ($inv) {
                $daysOverdue = $inv->due_date ? now()->diffInDays(Carbon::parse($inv->due_date), false) * -1 : 0;
                return [
                    'customer'     => $inv->customer->name ?? '-',
                    'phone'        => $inv->customer->phone ?? '-',
                    'zone'         => $inv->customer->zone->name ?? 'N/A',
                    'amount'       => $inv->total,
                    'due_date'     => $inv->due_date,
                    'days_overdue' => $daysOverdue,
                ];
            });

        return [
            'zoneReport'          => $zoneReport,
            'packageReport'       => $packageReport,
            'monthlyCollection'   => $monthlyCollection,
            'dueReport'           => $dueReport,
            'totalDue'            => Invoice::where('status', 'unpaid')->sum('total'),
            'totalUnpaid'         => Invoice::where('status', 'unpaid')->count(),
            'thisMonthCollection' => Payment::whereMonth('paid_at', $currentMonth)->whereYear('paid_at', $currentYear)->sum('amount'),
        ];
    }
}
