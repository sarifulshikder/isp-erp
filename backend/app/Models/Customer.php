<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Customer extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'phone', 'email', 'address',
        'username', 'password', 'package_id',
        'connection_date', 'expire_date', 'status',
        'mikrotik_profile', 'balance', 'zone_id', 'latitude', 'longitude', 'connection_type', 'mac_address',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'expire_date' => 'date',
        'connection_date' => 'date',
    ];

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class, 'assigned_customer_id');
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }
}
