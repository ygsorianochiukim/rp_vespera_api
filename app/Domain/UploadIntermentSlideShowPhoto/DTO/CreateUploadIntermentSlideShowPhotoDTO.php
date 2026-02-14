<?php

namespace App\Domain\UploadIntermentSlideShowPhoto\DTO;

class CreateSlideshowPhotoDTO
{
    public function __construct(
        public string $documentNo,
        public ?string $photo = null
    ) {}
}
