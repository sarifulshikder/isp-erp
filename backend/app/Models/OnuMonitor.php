<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OnuMonitor extends Model
{
    protected $fillable = [
        'olt_id', 'onu_id', 'mac', 'description', 'pon_port',
        'status', 'rx_power', 'tx_power', 'signal_status',
        'alert_sent', 'last_seen_at',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
        'alert_sent'   => 'boolean',
    ];

    public function olt()
    {
        return $this->belongsTo(OltDevice::class, 'olt_id');
    }

    public function getSignalColorAttribute(): string
    {
        return match($this->signal_status) {
            'normal'   => 'success',
            'warning'  => 'warning',
            'critical' => 'danger',
            default    => 'gray',
        };
    }
}
