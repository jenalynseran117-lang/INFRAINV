<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InspectionLog extends Model
{
    use HasFactory;

    // Listahan ng mga columns na pwedeng i-save
    protected $fillable = [
        'purchase_order_id',
        'inspector_id',
        'decision',
        'remarks'
    ];

    // Relationship: Ang log na ito ay pag-aari ng isang Purchase Order
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    // Relationship: Ang log na ito ay ginawa ng isang User (Inspector)
    public function inspector()
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }
}
