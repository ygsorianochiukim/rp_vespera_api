<?php

namespace App\Domain\AutomationDashboard\DTO;

class UpdateStatusDTO
{
    public function __construct(
        public ?int $conversation_id,
        public ?string $status,
        public ?int $transfer_count_bot,
        public ?int $transfer_count_human,
    ) {}
}