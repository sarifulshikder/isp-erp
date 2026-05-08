<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FiberRoute extends Model
{
    protected $fillable = [
        'name', 'type', 'olt_device_id', 'splitter_id',
        'customer_id', 'coordinates', 'color', 'status', 'note',
    ];

    protected $casts = [
        'coordinates' => 'array',
    ];

    public function oltDevice()
    {
        return $this->belongsTo(OltDevice::class);
    }

    public function splitter()
    {
        return $this->belongsTo(Splitter::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
