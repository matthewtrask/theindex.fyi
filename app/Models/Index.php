<?php

namespace App\Models;

use App\Enums\Category;
use App\Enums\IndexStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Index extends Model
{
    protected $table = 'indexes';

    public const LANGUAGES = [
        'ar' => 'Arabic',
        'zh' => 'Chinese',
        'cs' => 'Czech',
        'nl' => 'Dutch',
        'en' => 'English',
        'fi' => 'Finnish',
        'fr' => 'French',
        'de' => 'German',
        'he' => 'Hebrew',
        'hi' => 'Hindi',
        'hu' => 'Hungarian',
        'id' => 'Indonesian',
        'it' => 'Italian',
        'ja' => 'Japanese',
        'ko' => 'Korean',
        'no' => 'Norwegian',
        'fa' => 'Persian',
        'pl' => 'Polish',
        'pt' => 'Portuguese',
        'ro' => 'Romanian',
        'ru' => 'Russian',
        'es' => 'Spanish',
        'sv' => 'Swedish',
        'tr' => 'Turkish',
        'uk' => 'Ukrainian',
    ];

    protected $fillable = [
        'name',
        'slug',
        'url',
        'description',
        'category',
        'accepts_submissions',
        'language',
        'status',
        'last_checked_at',
        'submitted_by',
    ];

    public function languageName(): ?string
    {
        return $this->language ? (self::LANGUAGES[$this->language] ?? $this->language) : null;
    }

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
