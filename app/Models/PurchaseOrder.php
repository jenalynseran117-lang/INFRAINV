<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'management_id',
        'requested_by',
        'po_number',
        'po_date',
        'supplier',
        'stock_no',      // Added to match migration
        'description',   // Added to match migration
        'quantity',      // Added to match migration
        'unit_cost',     // Added to match migration
        'total_cost',
        'items',
        'po_attachment',
        'status',
    ];

    /**
     * The attributes that should be cast.
     * This tells Laravel to automatically handle JSON and Dates.
     */
    protected $casts = [
        'items' => 'array',
        'po_date' => 'date',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    
    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class, 'purchase_order_id');
    }

    public function management()
    {
        return $this->belongsTo(PrManagement::class, 'management_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
}