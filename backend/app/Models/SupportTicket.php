<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model {
    protected $guarded = [];
    public function customer() { return $this->belongsTo(Customer::class); }
    public function technician() { return $this->belongsTo(User::class, 'assigned_to'); }
}
