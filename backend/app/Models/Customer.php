<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Customer extends Model {
    protected $fillable = ['name', 'phone', 'email', 'address', 'username', 'password', 'package_id', 'connection_date', 'expire_date', 'status', 'mikrotik_profile', 'balance'];
    protected $hidden = ['password'];
    public function package() {
        return $this->belongsTo(Package::class);
    }
    public function invoices() {
        return $this->hasMany(Invoice::class);
    }
    public function payments() {
        return $this->hasMany(Payment::class);
    }
}
