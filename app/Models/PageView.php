<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageView extends Model
{
    public $timestamps = false;

    protected $fillable = ['path', 'referrer', 'visited_at'];

    protected $casts = [
        'visited_at' => 'datetime',
    ];
}
