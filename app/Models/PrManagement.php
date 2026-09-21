<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrManagement extends Model
{
    use HasFactory;

    // 1. Pangalan ng table sa database (Optional kung pr_managements ang plural, 
    // pero safe na ilagay ito dahil "pr_management" ang ginawa nating migration)
    protected $table = 'pr_management';

    // 2. Mass Assignment: Listahan ng mga columns na pwedeng lagyan ng data
    protected $fillable = [
        'user_id',
        'file',
        'pr_number',
        'item_photo', // Isama ito kung idinagdag mo sa migration
        
    ];


    

    // 3. Relationships (Optional pero recommended)
    // Para makuha mo kung sinong user ang nag-upload: $pr->user->name
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
