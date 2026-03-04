<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'gif_id',
        'alias',
        'gif_data',
    ];

    protected $casts = [
        'gif_data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
