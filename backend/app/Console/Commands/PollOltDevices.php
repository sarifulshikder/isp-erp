<?php
namespace App\Console\Commands;
use App\Models\OltDevice;
use App\Services\VsolService;
use App\Services\BdcomService;
use Illuminate\Console\Command;

class PollOltDevices extends Command
{
    protected $signature = 'olt:poll';
    protected $description = 'Poll all OLT devices and update ONU status';

    public function handle(): void
    {
        $olts = OltDevice::where('status', 'active')->get();
        foreach ($olts as $olt) {
            $this->info("Polling {$olt->name} ({$olt->brand})...");
            try {
                if ($olt->brand === 'bdcom') {
                    $service = new BdcomService($olt);
                    $count = $service->pollAndSave($olt);
                } else {
                    $service = new VsolService($olt);
                    $count = $service->pollAndSave($olt);
                }
                $this->info("Saved {$count} ONUs from {$olt->name}");
            } catch (\Exception $e) {
                $this->error("Error polling {$olt->name}: " . $e->getMessage());
            }
        }
        $this->info('Done!');
    }
}
