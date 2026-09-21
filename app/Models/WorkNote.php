<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkNote extends Model
{
    use HasFactory;

    // Pinapayagan nito ang pag-save ng data sa mga columns na ito
    protected $fillable = [
        'user_id',
        'role',
        'message'
    ];

    // Relationship para makuha ang pangalan ng user kung kailangan
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
