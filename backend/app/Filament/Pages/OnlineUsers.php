<?php
namespace App\Filament\Pages;
use App\Models\MikrotikDevice;
use App\Models\Customer;
use App\Services\MikrotikService;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use RouterOS\Query;
class OnlineUsers extends Page
{
    protected string $view = 'filament.pages.online-users';
    protected ?string $heading = '🟢 Online Users';
    protected static ?int $navigationSort = 1;
    public array $users = [];
    public int $total = 0;
    public string $pingResult = '';
    public string $pingUsername = '';
    public string $searchQuery = '';
    public int $selectedDeviceId = 0;
    public array $trafficData = [];
    public string $trafficUsername = '';
    public bool $showTrafficModal = false;
    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-signal';
    }
    public static function getNavigationLabel(): string
    {
        return 'Online Users';
    }
    public static function getNavigationGroup(): string
    {
        return 'Operations';
    }
    public function mount(): void
    {
        $this->loadUsers();
    }
    public function loadUsers(): void
    {
        $users = [];
        try {
            $query = MikrotikDevice::where('status', 'active');
            if ($this->selectedDeviceId > 0) {
                $query->where('id', $this->selectedDeviceId);
            }
            $devices = $query->get();
            $mikrotik = new MikrotikService();
            $customerNames = Customer::pluck('name', 'username')->toArray();
            $customerPhones = Customer::pluck('phone', 'username')->toArray();
            foreach ($devices as $device) {
                if (!$mikrotik->connect($device)) continue;
                $q = new Query('/ppp/active/print');
                $sessions = $mikrotik->getClient()->query($q)->read();
                foreach ($sessions as $session) {
                    $username = $session['name'] ?? '';
                    // Interface name থেকে traffic নাও
                    $rx = 0; $tx = 0;
                    try {
                        $ifQuery = new Query("/interface/monitor-traffic");
                        $ifQuery->where('name', '<pppoe-' . $username . '>');
                        $ifResult = $mikrotik->getClient()->query($ifQuery)->read();
                        if (!empty($ifResult)) {
                            $rx = (int)($ifResult[0]['rx-byte'] ?? 0);
                            $tx = (int)($ifResult[0]['tx-byte'] ?? 0);
                        }
                    } catch (\Exception $e) {}
                    $users[] = [
                        'id'          => $session['.id'] ?? '',
                        'device_id'   => $device->id,
                        'device_name' => $device->name,
                        'username'    => $username,
                        'customer'    => $customerNames[$username] ?? 'Unknown',
                        'phone'       => $customerPhones[$username] ?? '—',
                        'ip'          => $session['address'] ?? '—',
                        'uptime'      => $session['uptime'] ?? '—',
                        'rx'          => $this->formatBytes($rx),
                        'tx'          => $this->formatBytes($tx),
                        'mac'         => $session['caller-id'] ?? '—',
                        'session_id'  => $session['session-id'] ?? '—',
                    ];
                }
            }
        } catch (\Exception $e) {}
        // Search filter
        if ($this->searchQuery) {
            $search = strtolower($this->searchQuery);
            $users = array_filter($users, fn($u) =>
                str_contains(strtolower($u['username']), $search) ||
                str_contains(strtolower($u['customer']), $search) ||
                str_contains($u['ip'], $search)
            );
        }
        $this->users = array_values($users);
        $this->total = count($this->users);
    }
    public function disconnect(string $sessionId, int $deviceId): void
    {
        try {
            $device = MikrotikDevice::find($deviceId);
            $mikrotik = new MikrotikService();
            if ($mikrotik->connect($device)) {
                $query = new Query('/ppp/active/remove');
                $query->equal('.id', $sessionId);
                $mikrotik->getClient()->query($query)->read();
                Notification::make()->title('✅ Disconnected')->success()->send();
                $this->loadUsers();
            }
        } catch (\Exception $e) {
            Notification::make()->title('❌ Failed: ' . $e->getMessage())->danger()->send();
        }
    }
    public function ping(string $ip, string $username): void
    {
        try {
            $device = MikrotikDevice::where('status', 'active')->first();
            $mikrotik = new MikrotikService();
            if ($mikrotik->connect($device)) {
                $query = new Query('/ping');
                $query->equal('address', $ip);
                $query->equal('count', '4');
                $result = $mikrotik->getClient()->query($query)->read();
                $ping = collect($result)->last();
                $this->pingResult = "Ping to {$ip} ({$username})\n" .
                    "Sent: " . ($ping['sent'] ?? '—') . " | " .
                    "Received: " . ($ping['received'] ?? '—') . " | " .
                    "Loss: " . ($ping['packet-loss'] ?? '—') . "% | " .
                    "Avg RTT: " . ($ping['avg-rtt'] ?? '—') . "ms";
                $this->pingUsername = $username;
            }
        } catch (\Exception $e) {
            $this->pingResult = "Ping failed: " . $e->getMessage();
            $this->pingUsername = $username;
        }
    }
    public function openTraffic(string $username, int $deviceId): void
    {
        try {
            $device = MikrotikDevice::find($deviceId);
            $mikrotik = new MikrotikService();
            if (!$mikrotik->connect($device)) return;
            $ifName = '<pppoe-' . $username . '>';
            // monitor-traffic দিয়ে live Kbps নাও
            $q = new Query('/interface/monitor-traffic');
            $q->equal('interface', $ifName);
            $q->equal('once', '');
            $result = $mikrotik->getClient()->query($q)->read();
            $rxKbps = isset($result[0]['rx-bits-per-second'])
                ? round((int)$result[0]['rx-bits-per-second'] / 1000000, 3)
                : 0;
            $txKbps = isset($result[0]['tx-bits-per-second'])
                ? round((int)$result[0]['tx-bits-per-second'] / 1000000, 3)
                : 0;
            // Push to traffic history (max 20 points)
            $existing = $this->trafficUsername === $username ? $this->trafficData : [];
            $existing[] = [
                'time' => now()->format('H:i:s'),
                'rx'   => max(0, $rxKbps),
                'tx'   => max(0, $txKbps),
            ];
            if (count($existing) > 20) {
                $existing = array_slice($existing, -20);
            }
            $this->trafficData = $existing;
            $this->trafficUsername = $username;
            $this->showTrafficModal = true;

        } catch (\Exception $e) {
            $this->trafficData = [];
            $this->trafficUsername = $username;
            $this->showTrafficModal = true;
        }
    }
    public function changeProfile(string $username, int $deviceId, string $profile): void
    {
        try {
            $device = MikrotikDevice::find($deviceId);
            $mikrotik = new MikrotikService();
            if ($mikrotik->connect($device)) {
                // Secret update
                $q = new Query('/ppp/secret/print');
                $q->where('name', $username);
                $result = $mikrotik->getClient()->query($q)->read();
                if (!empty($result)) {
                    $id = $result[0]['.id'];
                    $uq = new Query('/ppp/secret/set');
                    $uq->equal('.id', $id);
                    $uq->equal('profile', $profile);
                    $mikrotik->getClient()->query($uq)->read();
                }
                // Active session এও apply
                $aq = new Query('/ppp/active/print');
                $aq->where('name', $username);
                $active = $mikrotik->getClient()->query($aq)->read();
                if (!empty($active)) {
                    $removeQ = new Query('/ppp/active/remove');
                    $removeQ->equal('.id', $active[0]['.id']);
                    $mikrotik->getClient()->query($removeQ)->read();
                }
                Notification::make()->title("✅ Profile changed to '{$profile}'")->success()->send();
                $this->loadUsers();
            }
        } catch (\Exception $e) {
            Notification::make()->title('❌ Failed: ' . $e->getMessage())->danger()->send();
        }
    }
    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
        if ($bytes >= 1048576)    return number_format($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024)       return number_format($bytes / 1024, 2) . ' KB';
        return $bytes . ' B';
    }
    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Refresh')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(fn() => $this->loadUsers()),
        ];
    }
}
