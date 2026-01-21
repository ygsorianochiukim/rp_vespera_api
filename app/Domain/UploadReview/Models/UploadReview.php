<?php

namespace App\Domain\UploadReview\Models;

use Illuminate\Database\Eloquent\Model;

class UploadReview extends Model
{
    // 🔥 THIS FIXES THE 500 ERROR
    protected $table = 'wbs_i_reviews';

protected $fillable = [
    'document_no',
    'reviewer_name',
    'contact_number',
    'selected_public_question',   // ✅ new
    'selected_private_question',  // ✅ new
    'private_feedback',
    'others',
    'fb_screenshot',
    'google_screenshot',
    'submitted_at',
    'is_valid'
];


}
