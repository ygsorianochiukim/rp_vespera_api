<?php

namespace App\Domain\UploadReview\DTO;

class CreateUploadReviewDTO
{
    public function __construct(
        public string $documentNo,
        public string $reviewerName,
        public ?string $q1 = null,
        public ?string $q2 = null,
        public ?string $q3 = null,
        public ?string $q4 = null,
        public ?string $q5 = null,
        public ?string $q6 = null,
        public ?string $others = null,
        public ?string $fbUsername = null,
        public ?string $googleUsername = null,
    ) {}
}