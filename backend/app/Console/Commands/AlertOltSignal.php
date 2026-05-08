<?php
namespace App\Console\Commands;
use App\Models\OnuMonitor;
use App\Services\SmsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
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

            // Zammad ticket
            try {
                Http::withHeaders([
                    'Authorization' => 'Token token=' . \App\Models\Setting::get('zammad_token', ''),
                    'Content-Type'  => 'application/json',
                ])->post('http://172.20.0.15:8080/api/v1/tickets', [
                    'title'    => "ONU Signal Critical: {$onu->description}",
                    'group'    => 'Users',
                    'customer' => 'sarifulshikder@gmail.com',
                    'article'  => [
                        'subject'  => 'ONU Signal Alert',
                        'body'     => "ONU: {$onu->onu_id}\nDescription: {$onu->description}\nMAC: {$onu->mac}\nPON: {$onu->pon_port}\nRX Power: {$onu->rx_power} dBm\nStatus: {$onu->signal_status}\nOLT: {$onu->olt->name}",
                        'type'     => 'note',
                        'internal' => false,
                    ],
                ]);
            } catch (\Exception $e) {
                Log::warning('OLT alert Zammad error: ' . $e->getMessage());
            }

            $onu->update(['alert_sent' => true]);
        }

        $this->info('Alert check done. Alerted: ' . $criticalOnus->count());
    }
}
