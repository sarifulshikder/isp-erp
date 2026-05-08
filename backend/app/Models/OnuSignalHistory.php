<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OnuSignalHistory extends Model
{
    protected $fillable = [
        'olt_id', 'onu_id', 'mac', 'rx_power', 'signal_status',
    ];
}
