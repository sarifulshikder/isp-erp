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
        'mikrotik_profile', 'balance', 'zone_id', 'latitude', 'longitude',
        'connection_type', 'mac_address',
        'mikrotik_mode',
        'mikrotik_device_id',
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
    public function mikrotikDevice()
    {
        return $this->belongsTo(MikrotikDevice::class);
    }
    // Mode helpers
    public function usesFreeRadiusOnly(): bool
    {
        return $this->mikrotik_mode === 'freeradius_only';
    }
    public function usesAllRouters(): bool
    {
        return $this->mikrotik_mode === 'all';
    }
    public function usesSpecificRouter(): bool
    {
        return $this->mikrotik_mode === 'specific';
    }
    // Customer এর জন্য target MikroTik devices return করে
    public function resolveTargetMikrotikDevices()
    {
        return match ($this->mikrotik_mode) {
            'specific' => MikrotikDevice::where('id', $this->mikrotik_device_id)->get(),
            'all'      => MikrotikDevice::where('status', 'active')->get(),
            default    => collect(), // freeradius_only
        };
    }
}
