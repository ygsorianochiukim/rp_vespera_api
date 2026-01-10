<?php

namespace App\Domain\AutoForfeiture\Repositories;

use App\Domain\AutoForfeiture\DTO\CreateForfeitureSigneeDTO;
use App\Domain\AutoForfeiture\Models\ForfeitureSignee;
use Illuminate\Support\Facades\DB;

class ForfeitureSigneeRepository
{
    public function getAll()
    {
        return ForfeitureSignee::where('is_active', true)->get();
    }

    public function find(int $id): ?ForfeitureSignee
    {
        return ForfeitureSignee::where('mp_t_lotforfeiture_line_id', $id)->first();
    }
     public function create(CreateForfeitureSigneeDTO $dto): int
    {
        return DB::connection('mysql_secondary')
            ->table('mp_t_lotforfeiture_signee')
            ->insertGetId([
                'usercode'               => $dto->usercode,
                'bpar_i_person_id'       => $dto->bpar_i_person_id,
                'mp_t_lotforfeiture_id'  => $dto->mp_t_lotforfeiture_id,
                'created'                => $dto->created,
                'date_created'           => $dto->date_created,
                'updated'                => $dto->updated,
                'date_updated'           => $dto->date_updated,
                'is_active'              => $dto->is_active,
                'role'                   => $dto->role,
            ]);
    }
}
