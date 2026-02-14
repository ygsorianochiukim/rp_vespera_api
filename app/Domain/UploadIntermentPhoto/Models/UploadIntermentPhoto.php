<?php

namespace App\Domain\UploadIntermentPhoto\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UploadIntermentPhoto extends Model
{
    protected $table = 'wbs_i_upload_interment_photos';

    protected $fillable = [   
        'document_no',
        'uploader_name',
        'occupant',
        'photo',
        'created_at',
        'is_valid'
    ];

  /**   protected static function booted() */
   // {
    
       // static::created(function ($model) {
           // if ($model->is_valid == 1) {
                //self::sendWebhook($model);
           // }
     //   });

// static::updated(function ($model) {
          //  if ($model->is_valid == 1) {
              //  self::sendWebhook($model);
           // }
      //  });
   //
   // protected static function sendWebhook($model)
   // {
      //  try {
           // $response = Http::post(
             //   'https://n8n.srv1205015.hstgr.cloud/webhook/',
              //  [
                  //  'id' => $model->id,
                  //  'document_no' => $model->document_no,
                 //   'uploader_name' => $model->uploader_name,
                  //  'occupant' => $model->occupant,
                 //   'photo' => $model->photo,
                  //  'is_valid' => $model->is_valid,
                  //  'created_at' => $model->created_at,
                  //  'updated_at' => $model->updated_at,
                //]
           // );

           // Log::info('Webhook sent, status: ' . $response->status());

      // } catch (\Exception $e) {
          //  Log::error('Webhook failed: ' . $e->getMessage());
       // }
   //}
}
