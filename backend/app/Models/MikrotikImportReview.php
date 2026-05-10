<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class MikrotikImportReview extends Model
{
    protected $fillable = [
        'mikrotik_device_id',
        'username',
        'password',
        'profile',
        'comment',
        'status',
        'customer_id',
    ];
    public function mikrotikDevice()
    {
        return $this->belongsTo(MikrotikDevice::class);
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}
