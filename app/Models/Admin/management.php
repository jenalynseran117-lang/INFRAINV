<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class management extends Model
{
    use HasFactory;

    protected $table = 'pr_management'; // matches your migration
    protected $fillable = ['user_id', 'file', 'file_type'];

    public function purchaseOrders()
    {
        // Sinasabi nito sa Laravel na ang isang PR (management) ay pwedeng magkaroon ng Purchase Order
        // Gamit ang 'management_id' bilang tagapag-ugnay (Foreign Key)
        return $this->hasMany(\App\Models\PurchaseOrder::class, 'management_id');
    }
}
