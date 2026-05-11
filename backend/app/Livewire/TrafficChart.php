<?php
namespace App\Livewire;
use App\Models\MikrotikDevice;
use App\Services\MikrotikService;
use Livewire\Component;
use RouterOS\Query;
class TrafficChart extends Component
{
    public string $username = '';
    public int $deviceId = 0;
    public bool $visible = false;
    public array $latest = ['rx' => 0, 'tx' => 0, 'time' => ''];
    public array $chartPoints = [];
    protected $listeners = ['openTrafficModal' => 'handleOpen'];
    public function handleOpen(string $username, int $deviceId): void
    {
        $this->username = $username;
        $this->deviceId = $deviceId;
        $this->visible = true;
        $this->chartPoints = [];
        $this->fetchData();
    }
    public function hide(): void
    {
        $this->visible = false;
        $this->username = '';
        $this->chartPoints = [];
    }
    public function refresh(): void
    {
        if ($this->visible && $this->username) {
            $this->fetchData();
        }
    }
    private function fetchData(): void
    {
        try {
            $device = MikrotikDevice::find($this->deviceId);
            $mikrotik = new MikrotikService();
            if (!$mikrotik->connect($device)) return;
            $ifName = '<pppoe-' . $this->username . '>';
            $q = new Query('/interface/monitor-traffic');
            $q->equal('interface', $ifName);
            $q->equal('once', '');
            $result = $mikrotik->getClient()->query($q)->read();
            $rx = isset($result[0]['rx-bits-per-second'])
                ? round((int)$result[0]['rx-bits-per-second'] / 1000000, 3) : 0;
            $tx = isset($result[0]['tx-bits-per-second'])
                ? round((int)$result[0]['tx-bits-per-second'] / 1000000, 3) : 0;
            $point = [
                'time' => now()->format('H:i:s'),
                'rx'   => max(0, $rx),
                'tx'   => max(0, $tx),
            ];
            $this->latest = $point;
            $this->chartPoints[] = $point;
            if (count($this->chartPoints) > 20) {
                $this->chartPoints = array_slice($this->chartPoints, -20);
            }
        } catch (\Exception $e) {}
    }
    public function render()
    {
        return view('livewire.traffic-chart');
    }
}
