<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkNoteRead extends Model
{
  // Pinapayagan nito ang pag-save ng data sa mga columns na ito
  protected $fillable = [
    'user_id',
    'last_read_at',
  ];

  // Automatic na gagawing Carbon instance ang last_read_at
  protected $casts = [
    'last_read_at' => 'datetime',
  ];

  // Relationship para makuha ang user na may-ari ng record na ito
  public function user()
  {
    return $this->belongsTo(User::class);
  }
}
