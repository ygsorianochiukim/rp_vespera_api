<?php

namespace App\Domain\AutoForfeiture\Services;

use App\Domain\AutoForfeiture\Repositories\ForfeitureSigneeRepository;
use Illuminate\Support\Facades\DB;

class ForfeitureSigneeService
{
    protected ForfeitureSigneeRepository $repository;

    public function __construct(ForfeitureSigneeRepository $repository)
    {
        $this->repository = $repository;
    }
    public function list()
    {
        return $this->repository->getAll();
    }
    public function create(array $data): int
    {
        $numberStart = 1;
        $usercode = str_pad($numberStart, 5, '0', STR_PAD_LEFT);

        $id = DB::connection('mysql_secondary')->table('mp_t_lotforfeiture_signee')->insertGetId([
            'usercode'              => $usercode,
            'bpar_i_person_id'      => null,
            'mp_t_lotforfeiture_id' => $data['mp_t_lotforfeiture_id'],
            'created'               => 'runner_autoforfeiture',
            'date_created'          => now()->format('Y-m-d H:i:s'),
            'date_updated'          => null,
            'is_active'             => true,
            'role'                  => 'MKR',
        ]);
        return $id;
    }
    public function createPR(array $data): int
    {
        $numberStart = 1;
        $usercode = str_pad($numberStart, 5, '0', STR_PAD_LEFT);

        $id = DB::connection('mysql_secondary')->table('mp_t_lotforfeiture_signee')->insertGetId([
            'usercode'              => $usercode,
            'bpar_i_person_id'      => null,
            'mp_t_lotforfeiture_id' => $data['mp_t_lotforfeiture_id'],
            'created'               => 'runner_autoforfeiture',
            'date_created'          => now()->format('Y-m-d H:i:s'),
            'date_updated'          => null,
            'is_active'             => true,
            'role'                  => 'CKR',
        ]);
        return $id;
    }
}
