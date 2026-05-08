<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class OltDevice extends Model
{
    protected $fillable = [
        'name', 'brand', 'ip', 'port',
        'username', 'password', 'status', 'last_polled_at',
        'latitude', 'longitude',
    ];
    protected $casts = [
        'last_polled_at' => 'datetime',
    ];
    public function onuMonitors()
    {
        return $this->hasMany(OnuMonitor::class, 'olt_id');
    }
    public function splitters()
    {
        return $this->hasMany(Splitter::class);
    }
    public function fiberRoutes()
    {
        return $this->hasMany(FiberRoute::class);
    }
}
