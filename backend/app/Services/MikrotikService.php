<?php
namespace App\Services;
use App\Models\MikrotikDevice;
use RouterOS\Client;
use RouterOS\Query;
class MikrotikService
{
    private Client $client;
    private MikrotikDevice $device;
    public function connect(MikrotikDevice $device): bool
    {
        try {
            $this->device = $device;
            $this->client = new Client([
                'host' => $device->host,
                'user' => $device->username,
                'pass' => $device->password,
                'port' => (int) $device->port,
            ]);
            $device->update(['status' => 'active', 'last_seen' => now()]);
            return true;
        } catch (\Exception $e) {
            $device->update(['status' => 'inactive']);
            return false;
        }
    }
    public function testConnection(): array
    {
        try {
            $query = new Query('/system/identity/print');
            $response = $this->client->query($query)->read();
            return ['success' => true, 'identity' => $response[0]['name'] ?? 'Unknown', 'device' => $this->device->name];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    public function getSystemInfo(): array
    {
        try {
            $query = new Query('/system/resource/print');
            $response = $this->client->query($query)->read();
            return $response[0] ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }
    public function getPPPoEUserId(string $username): ?string
    {
        try {
            $query = (new Query('/ppp/secret/print'))->where('name', $username);
            $response = $this->client->query($query)->read();
            return $response[0]['.id'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }
    public function addPPPoEUser(string $username, string $password, string $profile = 'default'): bool
    {
        try {
            $query = new Query('/ppp/secret/add');
            $query->equal('name', $username);
            $query->equal('password', $password);
            $query->equal('service', 'pppoe');
            $query->equal('profile', $profile);
            $this->client->query($query)->read();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    public function removePPPoEUser(string $username): bool
    {
        try {
            $id = $this->getPPPoEUserId($username);
            if (!$id) return false;
            $query = new Query('/ppp/secret/remove');
            $query->equal('.id', $id);
            $this->client->query($query)->read();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    public function enablePPPoEUser(string $username): bool
    {
        try {
            $id = $this->getPPPoEUserId($username);
            if (!$id) return false;
            $query = new Query('/ppp/secret/enable');
            $query->equal('.id', $id);
            $this->client->query($query)->read();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    public function disablePPPoEUser(string $username): bool
    {
        try {
            $id = $this->getPPPoEUserId($username);
            if (!$id) return false;
            $query = new Query('/ppp/secret/disable');
            $query->equal('.id', $id);
            $this->client->query($query)->read();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    public function enableUser(string $username): bool { return $this->enablePPPoEUser($username); }
    public function disableUser(string $username): bool { return $this->disablePPPoEUser($username); }
    public function getOnlineUsers(): array
    {
        try {
            $query = new Query('/ppp/active/print');
            $active = $this->client->query($query)->read();
            $usernames = array_column($active, 'name');
            $customers = \App\Models\Customer::whereIn('username', $usernames)
                ->get(['username', 'name', 'phone'])
                ->keyBy('username');
            return array_map(function($user) use ($customers) {
                $customer = $customers[$user['name']] ?? null;
                return [
                    'username'      => $user['name'] ?? '',
                    'customer_name' => $customer?->name ?? 'Unknown',
                    'phone'         => $customer?->phone ?? '',
                    'address'       => $user['address'] ?? '',
                    'uptime'        => $user['uptime'] ?? '',
                    'service'       => $user['service'] ?? '',
                    'caller_id'     => $user['caller-id'] ?? '',
                ];
            }, $active);
        } catch (\Exception $e) {
            return [];
        }
    }
    public function getInterfaces(): array
    {
        try {
            $query = new Query('/interface/print');
            $snap1 = $this->client->query($query)->read();
            usleep(500000);
            $snap2 = $this->client->query($query)->read();
            $result = [];
            foreach ($snap1 as $i => $iface) {
                if (($iface['running'] ?? '') !== 'true') continue;
                $rx1 = (int)($iface['rx-byte'] ?? 0);
                $tx1 = (int)($iface['tx-byte'] ?? 0);
                $rx2 = (int)($snap2[$i]['rx-byte'] ?? 0);
                $tx2 = (int)($snap2[$i]['tx-byte'] ?? 0);
                $rxMbps = round(($rx2 - $rx1) * 8 / 500000, 2);
                $txMbps = round(($tx2 - $tx1) * 8 / 500000, 2);
                $result[] = [
                    'name'     => $iface['name'] ?? '',
                    'type'     => $iface['type'] ?? '',
                    'rx_mbps'  => max(0, $rxMbps) . ' Mbps',
                    'tx_mbps'  => max(0, $txMbps) . ' Mbps',
                    'rx_total' => $this->formatBytes((int)($snap2[$i]['rx-byte'] ?? 0)),
                    'tx_total' => $this->formatBytes((int)($snap2[$i]['tx-byte'] ?? 0)),
                    'running'  => 'true',
                ];
            }
            return array_slice($result, 0, 10);
        } catch (\Exception $e) {
            return [];
        }
    }
    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
        if ($bytes >= 1048576)    return number_format($bytes / 1048576, 2)    . ' MB';
        if ($bytes >= 1024)       return number_format($bytes / 1024, 2)       . ' KB';
        return $bytes . ' B';
    }
    public function addProfile(string $name, string $rateLimit): bool
    {
        try {
            $query = new Query('/ppp/profile/add');
            $query->equal('name', $name);
            $query->equal('rate-limit', $rateLimit);
            $this->client->query($query)->read();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    // ── Hotspot User Methods ──────────────────────────────────────────────────
    public function addHotspotUser(string $username, string $password = '', string $profile = 'default'): bool
    {
        try {
            $query = new Query('/ip/hotspot/user/add');
            $query->equal('name', $username);
            if ($password) $query->equal('password', $password);
            $query->equal('profile', $profile);
            $this->client->query($query)->read();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    public function removeHotspotUser(string $username): bool
    {
        try {
            $query = (new Query('/ip/hotspot/user/print'))->where('name', $username);
            $response = $this->client->query($query)->read();
            $id = $response[0]['.id'] ?? null;
            if (!$id) return false;
            $q = new Query('/ip/hotspot/user/remove');
            $q->equal('.id', $id);
            $this->client->query($q)->read();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    public function enableHotspotUser(string $username): bool
    {
        try {
            $query = (new Query('/ip/hotspot/user/print'))->where('name', $username);
            $response = $this->client->query($query)->read();
            $id = $response[0]['.id'] ?? null;
            if (!$id) return false;
            $q = new Query('/ip/hotspot/user/enable');
            $q->equal('.id', $id);
            $this->client->query($q)->read();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    public function disableHotspotUser(string $username): bool
    {
        try {
            $query = (new Query('/ip/hotspot/user/print'))->where('name', $username);
            $response = $this->client->query($query)->read();
            $id = $response[0]['.id'] ?? null;
            if (!$id) return false;
            $q = new Query('/ip/hotspot/user/disable');
            $q->equal('.id', $id);
            $this->client->query($q)->read();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
