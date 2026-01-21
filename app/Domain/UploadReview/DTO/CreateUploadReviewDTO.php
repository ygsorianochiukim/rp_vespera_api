<?php

namespace App\Domain\UploadReview\DTO;

class CreateUploadReviewDTO
{
public function __construct(
    public string $documentNo,

    // optional
    public ?string $reviewerName = null,
    public ?string $contactNumber = null,

    // selected questions
    public ?string $selectedPublicQuestion = null,
    public ?string $selectedPrivateQuestion = null,

    public ?string $privateFeedback = null,
    public ?string $others = null,

    // file paths (set AFTER upload)
    public ?string $fbScreenshot = null,
    public ?string $googleScreenshot = null,
) {}

}
