<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Click extends Model
{
    public $timestamps = false;

    protected $fillable = ['index_id', 'clicked_at'];

    protected $casts = [
        'clicked_at' => 'datetime',
    ];

    public function index(): BelongsTo
    {
        return $this->belongsTo(Index::class);
    }
}
