<?php

namespace App\Domain\UploadIntermentPhoto\DTO;

class CreateUploadIntermentPhotoDTO
{
public function __construct(
    public string $documentNo,
    public ?string $uploaderName = null,
    public ?string $photo = null,
    
  
) {}
}

