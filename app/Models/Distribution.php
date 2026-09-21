<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Distribution extends Model
{
  use HasFactory;

  protected $fillable = ['project_id', 'released_by', 'notes'];

  public function project()
  {
    return $this->belongsTo(Project::class);
  }

  public function items()
  {
    return $this->hasMany(DistributionItem::class);
  }

  public function releasedBy()
  {
    return $this->belongsTo(User::class, 'released_by');
  }
}
