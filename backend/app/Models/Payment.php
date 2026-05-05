<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Payment extends Model {
    protected $fillable = ['invoice_id', 'customer_id', 'amount', 'method', 'transaction_id', 'paid_at', 'note'];
    public function invoice() {
        return $this->belongsTo(Invoice::class);
    }
    public function customer() {
        return $this->belongsTo(Customer::class);
    }
}
