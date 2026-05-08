<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Splitter extends Model
{
    protected $fillable = [
        'name', 'type', 'olt_device_id', 'zone_id',
        'latitude', 'longitude', 'address', 'status', 'note',
    ];

    public function oltDevice()
    {
        return $this->belongsTo(OltDevice::class);
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function fiberRoutes()
    {
        return $this->hasMany(FiberRoute::class);
    }
}
