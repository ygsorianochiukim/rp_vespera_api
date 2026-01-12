<?php

namespace App\Domain\AutomationDashboard\Repositories;

use App\Domain\AutomationDashboard\DTO\CreateConversationRecordDTO;
use App\Domain\AutomationDashboard\DTO\UpdateConversationDTO;
use App\Domain\AutomationDashboard\DTO\UpdateStatusDTO;
use App\Domain\AutomationDashboard\DTO\UpdateStatusLogsDTO;
use App\Domain\AutomationDashboard\Models\ConversationModel;

class ConversationRepository
{
    public function getAll()
    {
        return ConversationModel::all();
    }
    public function getCustomerPSID(int $customer_psid)
    {
        return ConversationModel::where('customer_psid', $customer_psid)->first();
    }

    public function displayHumanHandsOff()
    {
        return ConversationModel::where('status','!=','open')->get();
    }

    public function find(int $conversation_id): ?ConversationModel
    {
        return ConversationModel::where('conversation_id', $conversation_id)->first();
    }

     public function countTransferBot()
    {
        return ConversationModel::count('status','!=','open')->get();
    }

    public function countTransferHuman()
    {
        return ConversationModel::count('status','!=','open')->get();
    }

    public function find_psid(int $customer_psid): ?ConversationModel
    {
        return ConversationModel::where('customer_psid', $customer_psid)->first();
    }

    public function create(CreateConversationRecordDTO $dto): ConversationModel
    {
        return ConversationModel::updateOrCreate(
            [
                'conversation_id' => $dto->conversation_id,
            ],
            [
                'customer_psid'        => $dto->customer_psid,
                'conversation_name'    => $dto->conversation_name,
                'assigned_status'      => $dto->assigned_status,
                'assigned_agent'       => $dto->assigned_agent,
                'status'               => $dto->status,
                'last_message'         => $dto->last_message,
                'transfer_count_bot'   => $dto->transfer_count_bot,
                'transfer_count_human' => $dto->transfer_count_human,
                'date_created'         => $dto->date_created ?? now(),
            ]
        );
    }

    public function update(ConversationModel $conversation, array $data): ConversationModel
    {
        $conversation->update($data);
        return $conversation;
    }
    public function updateTransferLogs(ConversationModel $conversation,UpdateStatusDTO $updateDTO): ConversationModel {
        $conversation->update([
            'status'               => $updateDTO->status,
            'transfer_count_bot'   => $updateDTO->transfer_count_bot,
            'transfer_count_human' => $updateDTO->transfer_count_human,
        ]);

        return $conversation;
    }
    public function updateStatusLogs(ConversationModel $conversation,UpdateStatusLogsDTO $updateDTO): ConversationModel {
        $conversation->update([
            'status'   => $updateDTO->status,
            'date_created' => $updateDTO->date_created,
        ]);

        return $conversation;
    }
    public function updateTransBot(ConversationModel $conversation,UpdateStatusDTO $updateDTO): ConversationModel {
        $conversation->update([
            'status'               => $updateDTO->status,
            'transfer_count_bot'   => $updateDTO->transfer_count_bot,
            'transfer_count_human' => $updateDTO->transfer_count_human,
        ]);

        return $conversation;
    }
    public function updateConversation(ConversationModel $conversation,UpdateConversationDTO $updateConversationDTO): ConversationModel {
        $conversation->update([
            'last_message'  => $updateConversationDTO->last_message,
            'date_created'  => $updateConversationDTO->date_created,
        ]);

        return $conversation;
    }
}