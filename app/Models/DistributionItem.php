<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DistributionItem extends Model
{
  use HasFactory;

  protected $fillable = [
    'distribution_id',
    'purchase_order_id',
    'item_index',
    'item_name',
    'unit_cost',
    'quantity',
    'storage_location',
  ];

  public function distribution()
  {
    return $this->belongsTo(Distribution::class);
  }
}
