<?php

namespace App\Domain\AutomationDashboard\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class ConversationModel extends Model
{
    use HasFactory;

    protected $table = 'wbs_i_conversation';

    protected $primaryKey = 'conversation_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'customer_psid',
        'conversation_name',
        'assigned_status',
        'assigned_agent',
        'status',
        'last_message',
        'transfer_count_bot',
        'transfer_count_human',
        'date_created',
    ];
}