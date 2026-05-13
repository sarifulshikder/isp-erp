<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class UplinkMonitor extends Model
{
    protected $fillable = [
        'olt_id', 'port_name', 'description', 'role',
        'status', 'previous_status', 'is_active_port', 'last_changed_at',
    ];
    protected $casts = [
        'is_active_port'  => 'boolean',
        'last_changed_at' => 'datetime',
    ];
    public function olt()
    {
        return $this->belongsTo(OltDevice::class);
    }
}
