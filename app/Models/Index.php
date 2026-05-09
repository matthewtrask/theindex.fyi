<?php

namespace App\Models;

use App\Enums\Category;
use App\Enums\IndexStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Index extends Model
{
    protected $table = 'indexes';

    protected $fillable = [
        'name',
        'slug',
        'url',
        'description',
        'category',
        'accepts_submissions',
        'status',
        'last_checked_at',
        'submitted_by',
    ];

    protected $casts = [
        'category' => Category::class,
        'status' => IndexStatus::class,
        'accepts_submissions' => 'boolean',
        'last_checked_at' => 'datetime',
    ];

    public function clicks(): HasMany
    {
        return $this->hasMany(Click::class);
    }
}
