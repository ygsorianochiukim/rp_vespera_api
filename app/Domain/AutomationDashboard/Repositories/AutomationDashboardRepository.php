<?php

namespace App\Domain\AutomationDashboard\Repositories;

use App\Domain\AutomationDashboard\DTO\CreateAutomationDashboardDTO;
use App\Domain\AutomationDashboard\DTO\UpdateConversationDTO;
use App\Domain\AutomationDashboard\DTO\UpdateConversationLogsDTO;
use App\Domain\AutomationDashboard\Models\AutomationDashboard;
use Illuminate\Support\Str;


class AutomationDashboardRepository
{
    public function getAll()
    {
        return AutomationDashboard::where('is_active', true)->get();
    }
    public function find_psid(int $customer_psid): ?AutomationDashboard
    {
        return AutomationDashboard::where('customer_psid', $customer_psid)->first();
    }

    public function find(int $conversation_log_id): ?AutomationDashboard
    {
        return AutomationDashboard::where('conversation_log_id', $conversation_log_id)->first();
    }

    public function create(CreateAutomationDashboardDTO $dto): AutomationDashboard
    {
        return AutomationDashboard::create([
            'customer_psid'              => $dto->customer_psid,
            'conversation_status'        => $dto->conversation_status,
            'conversation_updated_from'  => $dto->conversation_updated_from,
            'conversation_updated_to'    => $dto->conversation_updated_to,
            'created_by'                 => $dto->created_by,
            'date_created'               => now(),
        ]);
    }

    public function update(AutomationDashboard $conversation, array $data): AutomationDashboard
    {
        $conversation->update($data);
        return $conversation;
    }

    public function updateConversationLogs(AutomationDashboard $conversation,UpdateConversationLogsDTO $updateDTO): AutomationDashboard {
        $conversation->update([
            'customer_psid'  => $updateDTO->customer_psid,
            'conversation_status'  => $updateDTO->conversation_status,
            'conversation_updated_from'   => $updateDTO->conversation_updated_from,
            'conversation_updated_to' => $updateDTO->conversation_updated_to,
        ]);

        return $conversation;
    }
}
