<?php

namespace App\Domain\UploadReview\Models;

use Illuminate\Database\Eloquent\Model;

class UploadReview extends Model
{
    protected $fillable = [
        'document_no',
        'reviewer_name',
        'q1',
        'q2',
        'q3',
        'q4',
        'q5',
        'q6',
        'others',
        'fb_username',
        'google_username',
        'submitted_at',
        'is_valid'
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'is_valid' => 'boolean'
    ];
}
