<?php
namespace App\Services;
use App\Models\OltDevice;
use App\Models\OnuMonitor;
use App\Models\OnuSignalHistory;
use Illuminate\Support\Facades\Log;

class BdcomService
{
    private string $ip;
    private int $snmpPort;
    private string $community;

    public function __construct(OltDevice $olt)
    {
        $this->ip        = $olt->ip;
        $this->snmpPort  = $olt->snmp_port ?? 162;
        $this->community = $olt->snmp_community ?? 'public';
    }

    private function snmpwalk(string $oid): array
    {
        $cmd = "snmpwalk -v1 -t 10 -c {$this->community} {$this->ip}:{$this->snmpPort} {$oid} 2>/dev/null";
        $output = shell_exec($cmd);
        if (!$output) return [];

        $results = [];
        foreach (explode("\n", trim($output)) as $line) {
            // Match: enterprises.3320.101.10.1.1.26.66 = INTEGER: 3
            if (preg_match('/\.(\d+)\s*=\s*(?:INTEGER|STRING|Hex-STRING|Counter32|Gauge32):\s*(.+)/', $line, $m)) {
                $results[trim($m[1])] = trim($m[2]);
            }
        }
        return $results;
    }

    // ONU status: OID .26 (3=online, others=offline)
    public function getOnuStatuses(): array
    {
        return $this->snmpwalk('1.3.6.1.4.1.3320.101.10.1.1.26');
    }

    // ONU RX Power: OID .27 (unit: 0.1 dBm)
    public function getOnuRxPowers(): array
    {
        $raw = $this->snmpwalk('1.3.6.1.4.1.3320.101.10.1.1.27');
        $powers = [];
        foreach ($raw as $idx => $value) {
            $val = (int) $value;
            if ($val > 0) {
                // BDCOM: unit is 0.01 dBm, stored as positive integer
                // 3100 → -31.00 dBm, 1247 → -12.47 dBm
                $powers[$idx] = round(-($val / 100), 2);
            }
        }
        return $powers;
    }

    // ONU MAC: OID .3
    public function getOnuMacs(): array
    {
        $raw = $this->snmpwalk('1.3.6.1.4.1.3320.101.10.1.1.3');
        $macs = [];
        foreach ($raw as $idx => $hex) {
            $parts = explode(' ', trim($hex));
            $mac = implode(':', array_map(fn($b) => strtoupper(str_pad($b, 2, '0', STR_PAD_LEFT)), $parts));
            $macs[$idx] = $mac;
        }
        return $macs;
    }

    // ONU description: OID .4 (hex firmware version)
    public function getOnuDescriptions(): array
    {
        $raw = $this->snmpwalk('1.3.6.1.4.1.3320.101.10.1.1.4');
        $descs = [];
        foreach ($raw as $idx => $hex) {
            // Hex-STRING: 56 31 2E 30 00 00 → "V1.0"
            $bytes = explode(' ', trim($hex));
            $str = '';
            foreach ($bytes as $b) {
                $c = hexdec($b);
                if ($c > 31 && $c < 127) $str .= chr($c);
            }
            $descs[$idx] = trim($str) ?: null;
        }
        return $descs;
    }

    // ONU vendor: OID .1
    public function getOnuVendors(): array
    {
        return $this->snmpwalk('1.3.6.1.4.1.3320.101.10.1.1.1');
    }

    // Build ONU ID from index: 66→EPON0/1:1, 67→EPON0/1:2 etc.
    private function buildOnuId(string $idx): string
    {
        $i = (int)$idx - 66;
        $pon = (int)($i / 16) + 1;
        $onu = ($i % 16) + 1;
        return "EPON0/{$pon}:{$onu}";
    }

    // PON port from index
    private function extractPonPort(string $onuId): string
    {
        if (preg_match('/EPON0\/(\d+):/', $onuId, $m)) {
            return 'PON' . $m[1];
        }
        return 'Unknown';
    }

    // Web থেকে ONU info আনো (customer name, MAC, ONU ID)
    public function getOnuInfoFromWeb(OltDevice $olt): array
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => "http://{$olt->ip}:{$olt->port}/onuintfstate.asp",
            CURLOPT_USERPWD        => "{$olt->username}:{$olt->password}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
        ]);
        $html = curl_exec($ch);
        curl_close($ch);

        $info = [];
        // Parse: intfName[0]="EPON0/1:1"; ... description[0]="Anis";
        preg_match_all('/intfName\[(\d+)\]="([^"]+)"/', $html, $names, PREG_SET_ORDER);
        preg_match_all('/description\[(\d+)\]="([^"]*)"/', $html, $descs, PREG_SET_ORDER);
        preg_match_all('/MACAddress\[(\d+)\]="([^"]+)"/', $html, $macs, PREG_SET_ORDER);

        foreach ($names as $m) {
            $i = $m[1];
            $info[$m[2]] = [
                'description' => $descs[$i][2] ?? null,
                'mac_web'     => isset($macs[$i][2]) ? strtoupper(str_replace('.', ':', $macs[$i][2])) : null,
            ];
        }
        return $info;
    }

    public function pollAndSave(OltDevice $olt): int
    {
        $statuses     = $this->getOnuStatuses();
        $rxPowers     = $this->getOnuRxPowers();
        $macs         = $this->getOnuMacs();
        $descriptions = $this->getOnuDescriptions();
        $vendors      = $this->getOnuVendors();
        $webInfo      = $this->getOnuInfoFromWeb($olt);

        $count = 0;
        foreach ($statuses as $idx => $status) {
            $rxPower  = $rxPowers[$idx] ?? null;
            $signal   = $rxPower !== null ? VsolService::getSignalStatus($rxPower) : 'unknown';
            $mac      = $macs[$idx] ?? null;
            $onuId    = $this->buildOnuId($idx);
            $ponPort  = $this->extractPonPort($onuId);
            $vendor   = $vendors[$idx] ?? '';
            $fwVer    = $descriptions[$idx] ?? '';
            // Web থেকে customer name নাও
            $webData  = $webInfo[$onuId] ?? null;
            $desc     = $webData['description'] ?? trim("{$vendor} {$fwVer}") ?: null;

            OnuMonitor::updateOrCreate(
                ['olt_id' => $olt->id, 'onu_id' => $onuId],
                [
                    'mac'           => $mac,
                    'description'   => $desc,
                    'pon_port'      => $ponPort,
                    'status'        => (int)$status === 3 ? 'online' : 'offline',
                    'rx_power'      => $rxPower,
                    'signal_status' => $signal,
                    'alert_sent'    => false,
                    'last_seen_at'  => now(),
                ]
            );

            if ($rxPower !== null) {
                OnuSignalHistory::create([
                    'olt_id'        => $olt->id,
                    'onu_id'        => $onuId,
                    'mac'           => $mac,
                    'rx_power'      => $rxPower,
                    'signal_status' => $signal,
                ]);
            }

            $count++;
        }

        $olt->update(['last_polled_at' => now()]);
        return $count;
    }
}
