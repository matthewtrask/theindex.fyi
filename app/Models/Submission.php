<?php

namespace App\Models;

use App\Enums\Category;
use App\Enums\SubmissionStatus;
use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    protected $fillable = [
        'name',
        'url',
        'description',
        'category',
        'submitted_by_email',
        'status',
    ];

    protected $casts = [
        'category' => Category::class,
        'status' => SubmissionStatus::class,
    ];
}
