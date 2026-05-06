<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Package extends Model {
    protected $fillable = ['name', 'description', 'speed_download', 'speed_upload', 'price', 'validity_days', 'status'];
    public function customers() {
        return $this->hasMany(Customer::class);
    }
    public function invoices() {
        return $this->hasMany(Invoice::class);
    }
}
