<?php


namespace App\Domain\UploadIntermentSlideShowPhoto\Models;


use Illuminate\Database\Eloquent\Model;


class UploadIntermentSlideShowPhoto extends Model
{

    protected $table = 'wbs_i_upload_interment_slideshow_photos';

protected $fillable = [
    'document_no',
    'uploader_name',
    'email_add',
    'photo',
    'submitted_at',
    'folder_id', // NEW: Google Drive folder ID
];

protected $casts = [
    'photo' => 'array',
    'folder_id' => 'string', // optional, but good practice
];
}
