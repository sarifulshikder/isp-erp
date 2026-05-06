<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Invoice extends Model {
    protected $fillable = ['invoice_no', 'customer_id', 'package_id', 'amount', 'discount', 'total', 'issue_date', 'due_date', 'paid_date', 'status', 'note'];
    public function customer() {
        return $this->belongsTo(Customer::class);
    }
    public function package() {
        return $this->belongsTo(Package::class);
    }
    public function payments() {
        return $this->hasMany(Payment::class);
    }
}
