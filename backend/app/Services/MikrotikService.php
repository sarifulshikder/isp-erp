<?php
namespace App\Services;
use App\Models\MikrotikDevice;
use App\Models\Customer;
use App\Models\MikrotikImportReview;
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
    // ─────────────────────────────────────────
    public function getClient(): Client
    {
        return $this->client;
    }

    // MULTI-ROUTER SYNC (mode-aware)
    // ─────────────────────────────────────────
    public function syncAddCustomer(Customer $customer): void
    {
        $devices = $customer->resolveTargetMikrotikDevices();
        foreach ($devices as $device) {
            if (!$this->connect($device)) continue;
            $profile = $customer->mikrotik_profile ?? 'default';
            if ($customer->connection_type === 'hotspot') {
                $this->addHotspotUser($customer->username, $customer->password ?? '', $profile);
            } else {
                $this->addPPPoEUser($customer->username, $customer->password ?? '', $profile);
            }
        }
    }
    public function syncRemoveCustomerFromAll(Customer $customer): void
    {
        $devices = MikrotikDevice::where('status', 'active')->get();
        foreach ($devices as $device) {
            if (!$this->connect($device)) continue;
            if ($customer->connection_type === 'hotspot') {
                $this->removeHotspotUser($customer->username);
            } else {
                $this->removePPPoEUser($customer->username);
            }
        }
    }
    public function syncEnableCustomer(Customer $customer): void
    {
        $devices = $customer->resolveTargetMikrotikDevices();
        foreach ($devices as $device) {
            if (!$this->connect($device)) continue;
            if ($customer->connection_type === 'hotspot') {
                $this->enableHotspotUser($customer->username);
            } else {
                $this->enablePPPoEUser($customer->username);
            }
        }
    }
    public function syncDisableCustomer(Customer $customer): void
    {
        $devices = $customer->resolveTargetMikrotikDevices();
        foreach ($devices as $device) {
            if (!$this->connect($device)) continue;
            if ($customer->connection_type === 'hotspot') {
                $this->disableHotspotUser($customer->username);
            } else {
                $this->disablePPPoEUser($customer->username);
            }
        }
    }
    // ─────────────────────────────────────────
    // EXPORT (Software → MikroTik)
    // ─────────────────────────────────────────
    public function exportToDevice(MikrotikDevice $device): array
    {
        $customers = Customer::where(function ($q) use ($device) {
            $q->where('mikrotik_mode', 'all')
              ->orWhere(function ($q2) use ($device) {
                  $q2->where('mikrotik_mode', 'specific')
                     ->where('mikrotik_device_id', $device->id);
              });
        })->get();
        if (!$this->connect($device)) {
            return ['exported' => 0, 'skipped' => 0, 'failed' => 0, 'error' => 'Connection failed'];
        }
        // Existing secrets থেকে username list নাও
        $query = new Query('/ppp/secret/print');
        $existing = $this->client->query($query)->read();
        $existingUsernames = array_map(fn($s) => strtolower($s['name'] ?? ''), $existing);
        $results = ['exported' => 0, 'skipped' => 0, 'failed' => 0];
        foreach ($customers as $customer) {
            if (in_array(strtolower($customer->username), $existingUsernames)) {
                $results['skipped']++;
                continue;
            }
            $profile = $customer->mikrotik_profile ?? 'default';
            $success = $customer->connection_type === 'hotspot'
                ? $this->addHotspotUser($customer->username, $customer->password ?? '', $profile)
                : $this->addPPPoEUser($customer->username, $customer->password ?? '', $profile);
            $success ? $results['exported']++ : $results['failed']++;
        }
        return $results;
    }
    // ─────────────────────────────────────────
    // IMPORT (MikroTik → Review Queue)
    // ─────────────────────────────────────────
    public function importFromDevice(MikrotikDevice $device): array
    {
        if (!$this->connect($device)) {
            return ['queued' => 0, 'skipped' => 0, 'error' => 'Connection failed'];
        }
        $query = new Query('/ppp/secret/print');
        $secrets = $this->client->query($query)->read();
        $existingUsernames = Customer::pluck('username')
            ->map(fn($u) => strtolower($u))->toArray();
        $pendingUsernames = MikrotikImportReview::where('mikrotik_device_id', $device->id)
            ->where('status', 'pending')
            ->pluck('username')
            ->map(fn($u) => strtolower($u))->toArray();
        $results = ['queued' => 0, 'skipped' => 0];
        foreach ($secrets as $secret) {
            $username = $secret['name'] ?? null;
            if (!$username) continue;
            $lower = strtolower($username);
            if (in_array($lower, $existingUsernames) || in_array($lower, $pendingUsernames)) {
                $results['skipped']++;
                continue;
            }
            MikrotikImportReview::create([
                'mikrotik_device_id' => $device->id,
                'username'           => $username,
                'password'           => $secret['password'] ?? null,
                'profile'            => $secret['profile'] ?? null,
                'comment'            => $secret['comment'] ?? null,
                'status'             => 'pending',
            ]);
            $results['queued']++;
        }
        return $results;
    }
    // ─────────────────────────────────────────
    // AUTO-PUSH — নতুন device এ সব 'all' mode customers
    // ─────────────────────────────────────────
    public function pushAllModeCustomersToDevice(MikrotikDevice $device): array
    {
        $customers = Customer::where('mikrotik_mode', 'all')
            ->where('status', 'active')->get();
        if (!$this->connect($device)) {
            return ['pushed' => 0, 'failed' => 0, 'error' => 'Connection failed'];
        }
        $results = ['pushed' => 0, 'failed' => 0];
        foreach ($customers as $customer) {
            $profile = $customer->mikrotik_profile ?? 'default';
            $success = $customer->connection_type === 'hotspot'
                ? $this->addHotspotUser($customer->username, $customer->password ?? '', $profile)
                : $this->addPPPoEUser($customer->username, $customer->password ?? '', $profile);
            $success ? $results['pushed']++ : $results['failed']++;
        }
        return $results;
    }
}
