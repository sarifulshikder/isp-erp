<?php
namespace App\Services;

use App\Models\OltDevice;
use App\Models\OnuMonitor;
use App\Models\OnuSignalHistory;
use Illuminate\Support\Facades\Log;

class VsolService
{
    private string $baseUrl;
    private string $username;
    private string $password;
    private string $cookie = '';

    public function __construct(OltDevice $olt)
    {
        $this->baseUrl  = "http://{$olt->ip}:{$olt->port}";
        $this->username = $olt->username;
        $this->password = $olt->password;
    }

    public function login(): bool
    {
        try {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL            => "{$this->baseUrl}/action/main.html",
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => "user={$this->username}&pass={$this->password}&who=100",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_COOKIEJAR      => '/tmp/vsol_laravel_cookie.txt',
                CURLOPT_COOKIEFILE     => '/tmp/vsol_laravel_cookie.txt',
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT        => 15,
            ]);
            curl_exec($ch);
            curl_close($ch);
            $this->cookie = '/tmp/vsol_laravel_cookie.txt';
            return true;
        } catch (\Exception $e) {
            Log::error('VSOL login error: ' . $e->getMessage());
            return false;
        }
    }

    private function fetch(string $url): string
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_COOKIEFILE     => $this->cookie,
            CURLOPT_TIMEOUT        => 15,
        ]);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result ?: '';
    }

    // OPM Diag থেকে RX Power + Distance + Temperature + MAC + Description
    public function getOnuOpmDiag(int $ponId = 255): array
    {
        $html = $this->fetch("{$this->baseUrl}/action/onuopmdiag.html?select={$ponId}&who=100");
        return $this->parseOpmDiag($html);
    }

    // ONU list (status, mac, description)
    public function getOnuList(int $ponId = 255): array
    {
        $html = $this->fetch("{$this->baseUrl}/action/onuauthinfo.html?select={$ponId}&who=100");
        return $this->parseOnuList($html);
    }

    private function parseOpmDiag(string $html): array
    {
        $onus = [];
        preg_match_all(
            '/<td class=\'hd\'>(EPON[\d\/\:]+)<\/td>\s*<td>([\w:]+)<\/td>\s*<td>(.*?)<\/td>\s*<td>([\d\.]+)<\/td>\s*<td>([\d\.]+)<\/td>\s*<td>([\d\.]+)<\/td>\s*<td>([\d\.]+)<\/td>\s*<td>([\d\.\-]+)<\/td>\s*<td>([\d\.\-]+)<\/td>/s',
            $html,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $m) {
            $onuId = trim($m[1]);
            // PON port ONU ID থেকে বের করি: EPON0/2:5 → PON2
            preg_match('/EPON0\/(\d+):/', $onuId, $ponMatch);
            $ponPort = isset($ponMatch[1]) ? "PON{$ponMatch[1]}" : 'Unknown';

            $onus[$onuId] = [
                'onu_id'      => $onuId,
                'mac'         => trim($m[2]),
                'description' => trim(strip_tags($m[3])),
                'distance'    => (float) $m[4],
                'temperature' => (float) $m[5],
                'tx_power'    => (float) $m[8],
                'rx_power'    => (float) $m[9],
                'pon_port'    => $ponPort,
            ];
        }

        return $onus;
    }

    private function parseOnuList(string $html): array
    {
        $onus = [];
        preg_match_all(
            '/<td class=\'hd\'>(EPON[\d\/\:]+)\s*<\/td>\s*<td><font color=(#[0-9a-fA-F]+)>/s',
            $html,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $m) {
            $onus[trim($m[1])] = $m[2] === '#008040' ? 'online' : 'offline';
        }

        return $onus;
    }

    public static function getSignalStatus(float $rxPower): string
    {
        if ($rxPower >= -24) return 'normal';
        if ($rxPower >= -27) return 'warning';
        return 'critical';
    }

    public function pollAndSave(OltDevice $olt): int
    {
        if (!$this->login()) return 0;

        $count   = 0;
        $opmData = $this->getOnuOpmDiag(255);
        $statusData = $this->getOnuList(255);

        foreach ($opmData as $onuId => $data) {
            $rxPower = $data['rx_power'];
            $signal  = self::getSignalStatus($rxPower);
            $status  = $statusData[$onuId] ?? 'unknown';

            OnuMonitor::updateOrCreate(
                ['olt_id' => $olt->id, 'onu_id' => $onuId],
                [
                    'mac'           => $data['mac'],
                    'description'   => $data['description'],
                    'pon_port'      => $data['pon_port'],
                    'status'        => $status,
                    'rx_power'      => $rxPower,
                    'tx_power'      => $data['tx_power'],
                    'signal_status' => $signal,
                    'alert_sent'    => false,
                    'last_seen_at'  => now(),
                ]
            );

            OnuSignalHistory::create([
                'olt_id'        => $olt->id,
                'onu_id'        => $onuId,
                'mac'           => $data['mac'],
                'rx_power'      => $rxPower,
                'signal_status' => $signal,
            ]);

            $count++;
        }

        $olt->update(['last_polled_at' => now()]);
        return $count;
    }
}
