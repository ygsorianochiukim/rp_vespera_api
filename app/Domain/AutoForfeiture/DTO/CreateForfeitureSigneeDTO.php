<?php

namespace App\Domain\AutoForfeiture\DTO;

class CreateForfeitureSigneeDTO
{
    public function __construct(
        public ?int $usercode,
        public ?int $bpar_i_person_id,
        public int $mp_t_lotforfeiture_id,
        public ?string $role,
        public ?string $created = 'System Auto Forfeited',
        public ?string $updated = null,
        public ?bool $is_active = true,
        public ?string $date_created = null,
        public ?string $date_updated = null,
    ) {}
}
