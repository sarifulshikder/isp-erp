<?php
namespace App\Filament\Widgets;

use App\Models\MikrotikDevice;
use App\Services\MikrotikService;
use Filament\Widgets\Widget;

class LiveBandwidthWidget extends Widget
{
    protected static ?int $sort = 7;
    protected int|string|array $columnSpan = 'full';
    protected string $view = 'filament.widgets.live-bandwidth';
    protected static ?string $pollingInterval = '10s';

    public function getViewData(): array
    {
        $data = [];
        try {
            $devices = MikrotikDevice::where('status', 'active')->get();
            $mikrotik = new MikrotikService();

            foreach ($devices as $device) {
                if ($mikrotik->connect($device)) {
                    $interfaces = $mikrotik->getInterfaces();
                    $data[] = [
                        'device' => $device->name,
                        'host' => $device->host,
                        'interfaces' => $interfaces,
                    ];
                }
            }
        } catch (\Exception $e) {
            $data = [];
        }
        return ['devices' => $data];
    }
}
