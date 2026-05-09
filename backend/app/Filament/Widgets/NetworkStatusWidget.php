<?php
namespace App\Filament\Widgets;

use App\Models\MikrotikDevice;
use App\Models\OltDevice;
use App\Models\OnuMonitor;
use App\Services\MikrotikService;
use Filament\Widgets\Widget;

class NetworkStatusWidget extends Widget
{
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 'full';
    protected string $view = 'filament.widgets.network-status';

    public function getViewData(): array
    {
        $mikrotikDevices = MikrotikDevice::all()->map(function ($device) {
            $mikrotik = new MikrotikService();
            $connected = $mikrotik->connect($device);
            $onlineUsers = $connected ? count($mikrotik->getOnlineUsers()) : 0;
            return [
                'name' => $device->name,
                'host' => $device->host,
                'status' => $connected ? 'online' : 'offline',
                'online_users' => $onlineUsers,
            ];
        });

        $oltDevices = OltDevice::all();
        $totalOnu = OnuMonitor::count();
        $onlineOnu = OnuMonitor::where('status', 'online')->count();
        $criticalOnu = OnuMonitor::where('rx_power', '<', -26)->count();
        $warningOnu = OnuMonitor::whereBetween('rx_power', [-26, -24])->count();

        return [
            'mikrotikDevices' => $mikrotikDevices,
            'oltDevices' => $oltDevices,
            'totalOnu' => $totalOnu,
            'onlineOnu' => $onlineOnu,
            'criticalOnu' => $criticalOnu,
            'warningOnu' => $warningOnu,
        ];
    }
}
