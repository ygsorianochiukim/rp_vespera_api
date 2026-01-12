<?php

namespace App\Domain\AutomationDashboard\DTO;

class UpdateStatusLogsDTO
{
    public function __construct(
        public ?int $customer_psid,
        public ?string $status,
        public ?string $date_created,
    ) {}
}