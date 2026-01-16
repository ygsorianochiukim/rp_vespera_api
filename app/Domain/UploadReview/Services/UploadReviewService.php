<?php

namespace App\Domain\UploadReview\Services;

use App\Domain\UploadReview\Repositories\UploadReviewRepository;
use App\Domain\UploadReview\DTO\CreateUploadReviewDTO;
use Illuminate\Validation\ValidationException;

class UploadReviewService
{
    // ✅ Add repository injection here
    public function __construct(private UploadReviewRepository $repository) {}

    public function submit(CreateUploadReviewDTO $dto): void
    {
        // Duplicate name check
        if ($this->repository->existsDuplicate($dto->documentNo, $dto->reviewerName)) {
            throw ValidationException::withMessages([
                'reviewer_name' => 'Duplicate reviewer name detected.'
            ]);
        }

        // Save review
        $this->repository->create([
            'document_no' => $dto->documentNo,
            'reviewer_name' => $dto->reviewerName,
            'q1' => $dto->q1,
            'q2' => $dto->q2,
            'q3' => $dto->q3,
            'q4' => $dto->q4,
            'q5' => $dto->q5,
            'q6' => $dto->q6,
            'others' => $dto->others,
            'fb_username' => $dto->fbUsername,
            'google_username' => $dto->googleUsername,
            'submitted_at' => now(),
            'is_valid' => true,
        ]);
    }
}
