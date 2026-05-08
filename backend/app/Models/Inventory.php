<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $fillable = [
        'category_id', 'name', 'serial_number', 'model', 'brand',
        'status', 'purchase_price', 'purchase_date',
        'assigned_customer_id', 'assigned_date', 'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'assigned_date' => 'date',
    ];

    public function category()
    {
        return $this->belongsTo(InventoryCategory::class, 'category_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'assigned_customer_id');
    }
}
