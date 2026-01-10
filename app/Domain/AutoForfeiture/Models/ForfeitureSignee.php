<?php

namespace App\Domain\AutoForfeiture\Models;

use Illuminate\Database\Eloquent\Model;

class ForfeitureSignee extends Model
{
    protected $connection = 'mysql_secondary';
    protected $table = 'mp_t_lotforfeiture_signee';
    protected $primaryKey = 'mp_t_lotforfeiture_signee_id';
    public $timestamps = false;

    protected $fillable = [
        'usercode',
        'bpar_i_person_id',
        'mp_t_lotforfeiture_id',
        'created',
        'date_created',
        'updated',
        'date_updated',
        'is_active',
        'role',
    ];
}
