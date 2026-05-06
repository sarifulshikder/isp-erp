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
            return [
                'success' => true,
                'identity' => $response[0]['name'] ?? 'Unknown',
                'device' => $this->device->name,
            ];
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

    public function getOnlineUsers(): array
    {
        try {
            $query = new Query('/ppp/active/print');
            return $this->client->query($query)->read();
        } catch (\Exception $e) {
            return [];
        }
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
}
