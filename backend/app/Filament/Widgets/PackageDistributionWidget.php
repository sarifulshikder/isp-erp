<?php
namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Package;
use Filament\Widgets\ChartWidget;

class PackageDistributionWidget extends ChartWidget
{
    protected ?string $heading = 'Package Distribution';
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $packages = Package::withCount('customers')->get();

        $colors = [
            'rgba(34, 197, 94, 0.8)',
            'rgba(59, 130, 246, 0.8)',
            'rgba(251, 191, 36, 0.8)',
            'rgba(239, 68, 68, 0.8)',
            'rgba(168, 85, 247, 0.8)',
            'rgba(20, 184, 166, 0.8)',
        ];

        return [
            'datasets' => [
                [
                    'data' => $packages->pluck('customers_count')->toArray(),
                    'backgroundColor' => array_slice($colors, 0, $packages->count()),
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $packages->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
