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
    protected static ?string $pollingInterval = '30s';
    public array $users = [];
    public int $total = 0;
    public string $pingResult = '';
    public string $pingUsername = '';
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
            $devices = MikrotikDevice::where('status', 'active')->get();
            $mikrotik = new MikrotikService();
            // Software এ থাকা customers username map
            $customers = Customer::pluck('phone', 'username')->toArray();
            $customerNames = Customer::pluck('name', 'username')->toArray();
            foreach ($devices as $device) {
                if (!$mikrotik->connect($device)) continue;
                // Active sessions
                $query = new Query('/ppp/active/print');
                $sessions = $mikrotik->getClient()->query($query)->read();
                foreach ($sessions as $session) {
                    $username = $session['name'] ?? '';
                    $users[] = [
                        'id'          => $session['.id'] ?? '',
                        'device_id'   => $device->id,
                        'device_name' => $device->name,
                        'username'    => $username,
                        'customer'    => $customerNames[$username] ?? 'Unknown',
                        'phone'       => $customers[$username] ?? '—',
                        'ip'          => $session['address'] ?? '—',
                        'uptime'      => $session['uptime'] ?? '—',
                        'rx'          => $this->formatBytes((int)($session['bytes-in'] ?? 0)),
                        'tx'          => $this->formatBytes((int)($session['bytes-out'] ?? 0)),
                        'mac'         => $session['caller-id'] ?? '—',
                    ];
                }
            }
        } catch (\Exception $e) {}
        $this->users = $users;
        $this->total = count($users);
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
            $devices = MikrotikDevice::where('status', 'active')->first();
            $mikrotik = new MikrotikService();
            if ($mikrotik->connect($devices)) {
                $query = new Query('/ping');
                $query->equal('address', $ip);
                $query->equal('count', '4');
                $result = $mikrotik->getClient()->query($query)->read();
                $ping = collect($result)->last();
                $this->pingResult = "Ping to {$ip} ({$username}):\n" .
                    "Sent: " . ($ping['sent'] ?? '—') . " | " .
                    "Received: " . ($ping['received'] ?? '—') . " | " .
                    "Loss: " . ($ping['packet-loss'] ?? '—') . "% | " .
                    "Avg: " . ($ping['avg-rtt'] ?? '—') . "ms";
                $this->pingUsername = $username;
            }
        } catch (\Exception $e) {
            $this->pingResult = "Ping failed: " . $e->getMessage();
            $this->pingUsername = $username;
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
