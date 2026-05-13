<?php
namespace App\Filament\Widgets;
use App\Models\UplinkMonitor;
use App\Models\OltDevice;
use Filament\Widgets\Widget;
class UplinkStatusWidget extends Widget
{
    protected static ?int $sort = 0;
    protected int|string|array $columnSpan = 'full';
    protected string $view = 'filament.widgets.uplink-status';
    protected static ?string $pollingInterval = '30s';
    public function getViewData(): array
    {
        $uplinks = UplinkMonitor::with('olt')->orderBy('port_name', 'asc')->get();
        return ['uplinks' => $uplinks];
    }
}
