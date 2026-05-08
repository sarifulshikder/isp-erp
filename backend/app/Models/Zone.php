<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Zone extends Model
{
    protected $fillable = [
        'name', 'area', 'contact_person',
        'contact_phone', 'notes', 'is_active',
        'latitude', 'longitude',
    ];
    protected $casts = [
        'is_active' => 'boolean',
    ];
    public function customers()
    {
        return $this->hasMany(Customer::class);
    }
}
