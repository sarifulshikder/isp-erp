<?php
namespace App\Console\Commands;
use App\Models\OnuMonitor;
use App\Models\SupportTicket;
use App\Services\SmsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
class AlertOltSignal extends Command
{
    protected $signature = 'olt:alert';
    protected $description = 'Send alerts for critical/warning ONU signals';
    public function handle(): void
    {
        $criticalOnus = OnuMonitor::where('signal_status', 'critical')
            ->where('alert_sent', false)
            ->with('olt')
            ->get();
        foreach ($criticalOnus as $onu) {
            $this->info("Alert: {$onu->onu_id} - {$onu->description} - {$onu->rx_power} dBm");
            // SMS to admin
            try {
                $sms = new SmsService();
                $adminPhone = \App\Models\Setting::get('admin_phone', '');
                if ($adminPhone) {
                    $sms->send($adminPhone,
                        "ONU Alert: {$onu->description} ({$onu->onu_id}) signal critical: {$onu->rx_power} dBm"
                    );
                }
            } catch (\Exception $e) {
                Log::warning('OLT alert SMS error: ' . $e->getMessage());
            }
            // Support Ticket — Zammad এর বদলে নিজের system এ
            try {
                SupportTicket::create([
                    'subject'     => "ONU Signal Critical: {$onu->description}",
                    'description' => "ONU: {$onu->onu_id}\nDescription: {$onu->description}\nMAC: {$onu->mac}\nPON: {$onu->pon_port}\nRX Power: {$onu->rx_power} dBm\nStatus: {$onu->signal_status}\nOLT: {$onu->olt->name}",
                    'priority'    => 'high',
                    'status'      => 'open',
                ]);
            } catch (\Exception $e) {
                Log::warning('OLT alert Support Ticket error: ' . $e->getMessage());
            }
            $onu->update(['alert_sent' => true]);
        }
        $this->info('Alert check done. Alerted: ' . $criticalOnus->count());
    }
}
