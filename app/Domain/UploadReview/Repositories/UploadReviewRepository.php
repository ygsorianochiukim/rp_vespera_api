<?php

namespace App\Domain\UploadReview\Repositories;

use App\Domain\UploadReview\Models\UploadReview;

class UploadReviewRepository
{
    public function existsDuplicate(string $documentNo, string $reviewerName): bool
    {
        return UploadReview::where('document_no', $documentNo)
            ->whereRaw('LOWER(reviewer_name) = ?', [strtolower($reviewerName)])
            ->where('is_valid', true)
            ->exists();
    }

    public function create(array $data): UploadReview
    {
        return UploadReview::create($data);
    }
}
