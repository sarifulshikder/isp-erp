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
        'mikrotik_profile', 'balance',
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
}
