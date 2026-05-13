<?php
namespace App\Console\Commands;
use App\Models\OltDevice;
use App\Models\UplinkMonitor;
use App\Models\SupportTicket;
use App\Services\SmsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
class MonitorUplinkStatus extends Command
{
    protected $signature = 'uplink:monitor';
    protected $description = 'Monitor VSOL OLT uplink port status via SNMP';
    // Port config: SNMP index → port info
    private array $ports = [
        1 => ['name' => 'GE1', 'description' => 'UpR-Link',  'role' => 'master'],
        2 => ['name' => 'GE2', 'description' => 'BpR-Link',  'role' => 'backup'],
    ];
    public function handle(): void
    {
        $olts = OltDevice::where('brand', 'vsol')->where('status', 'active')->get();
        foreach ($olts as $olt) {
            $this->checkOlt($olt);
        }
    }
    private function checkOlt(OltDevice $olt): void
    {
        $snmpPort = 161; // VSOL SNMP port
        $community = $olt->snmp_community ?? 'public';
        $ip = $olt->ip;
        foreach ($this->ports as $idx => $portInfo) {
            // SNMP দিয়ে operational status নাও
            $cmd = "snmpget -v2c -t 5 -c {$community} {$ip}:{$snmpPort} 1.3.6.1.2.1.2.2.1.8.{$idx} 2>/dev/null";
            $output = shell_exec($cmd);
            $currentStatus = 'unknown';
            if ($output && str_contains($output, 'up(1)')) {
                $currentStatus = 'up';
            } elseif ($output && str_contains($output, 'down(2)')) {
                $currentStatus = 'down';
            }
            // DB তে আগের status নাও
            $record = UplinkMonitor::where('olt_id', $olt->id)
                ->where('port_name', $portInfo['name'])
                ->first();
            $previousStatus = $record?->status ?? 'unknown';
            $statusChanged = $record && $previousStatus !== 'unknown' && $previousStatus !== $currentStatus;
            // Active port — master up হলে master active, নইলে backup
            $isActive = false;
            if ($portInfo['role'] === 'master' && $currentStatus === 'up') {
                $isActive = true;
            } elseif ($portInfo['role'] === 'backup') {
                // Master status চেক করো
                $masterStatus = UplinkMonitor::where('olt_id', $olt->id)
                    ->where('role', 'master')->value('status');
                $isActive = ($masterStatus !== 'up' && $currentStatus === 'up');
            }
            UplinkMonitor::updateOrCreate(
                ['olt_id' => $olt->id, 'port_name' => $portInfo['name']],
                [
                    'description'     => $portInfo['description'],
                    'role'            => $portInfo['role'],
                    'previous_status' => $previousStatus,
                    'status'          => $currentStatus,
                    'is_active_port'  => $isActive,
                    'last_changed_at' => $statusChanged ? now() : ($record?->last_changed_at ?? now()),
                ]
            );
            // Port switch হলে alert
            if ($statusChanged) {
                $this->sendAlert($olt, $portInfo, $previousStatus, $currentStatus);
            }
            $this->info("{$olt->name} | {$portInfo['name']} ({$portInfo['description']}) | {$previousStatus} → {$currentStatus}" . ($statusChanged ? ' ⚠️ CHANGED!' : ''));
        }
    }
    private function sendAlert(OltDevice $olt, array $portInfo, string $prev, string $current): void
    {
        $msg = "⚠️ Uplink Alert!\nOLT: {$olt->name}\nPort: {$portInfo['name']} ({$portInfo['description']})\nStatus: {$prev} → {$current}\nTime: " . now()->format('d M Y H:i:s');
        // SMS to admin
        try {
            $adminPhone = \App\Models\Setting::get('admin_phone', '');
            if ($adminPhone) {
                $sms = new SmsService();
                $sms->send($adminPhone, $msg);
            }
        } catch (\Exception $e) {
            Log::warning('Uplink alert SMS error: ' . $e->getMessage());
        }
        // Support Ticket
        try {
            SupportTicket::create([
                'subject'     => "Uplink Port Switch: {$portInfo['name']} {$prev}→{$current}",
                'description' => $msg,
                'priority'    => 'high',
                'status'      => 'open',
                'customer_id' => null,
            ]);
        } catch (\Exception $e) {
            Log::warning('Uplink alert ticket error: ' . $e->getMessage());
        }
        Log::warning('Uplink status changed: ' . $msg);
    }
}
