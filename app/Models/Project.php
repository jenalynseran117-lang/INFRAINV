<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
  use HasFactory;

  protected $fillable = ['name', 'location', 'budget', 'status', 'created_by'];

  protected $casts = [
    'budget' => 'decimal:2',
  ];

  public function distributions()
  {
    return $this->hasMany(Distribution::class);
  }

  public function getTotalDistributedValueAttribute()
  {
    return $this->distributions->sum(fn($d) => $d->items->sum(fn($i) => $i->quantity * $i->unit_cost));
  }

  public function getItemsDistributedCountAttribute()
  {
    return $this->distributions->sum(fn($d) => $d->items->sum('quantity'));
  }

  public function getProgressPercentAttribute()
  {
    if (!$this->budget || $this->budget <= 0) return null;
    return min(100, round(($this->total_distributed_value / $this->budget) * 100));
  }
}
