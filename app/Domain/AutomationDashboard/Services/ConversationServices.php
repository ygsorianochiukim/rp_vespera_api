<?php

namespace App\Domain\AutomationDashboard\Services;

use App\Domain\AutomationDashboard\DTO\CreateConversationRecordDTO;
use App\Domain\AutomationDashboard\Repositories\ConversationRepository;

class ConversationServices
{
    public function __construct(
        protected ConversationRepository $repository
    ) {}

    public function list()
    {
        return $this->repository->getAll();
    }
    public function listLogsHandsoff()
    {
        return $this->repository->displayHumanHandsOff();
    }
    public function create(array $data)
    {
        $dto = new CreateConversationRecordDTO(
            conversation_id: $data['conversation_id'],
            customer_psid: $data['customer_psid'],
            conversation_name: $data['conversation_name'],
            assigned_status: $data['assigned_status'],
            assigned_agent: $data['assigned_agent'],
            status: $data['status'],
            last_message: $data['last_message'],
            transfer_count_bot: $data['transfer_count_bot'],
            transfer_count_human: $data['transfer_count_human'],
            date_created: $data['date_created'],
        );

        return $this->repository->create($dto);
    }

    public function update(int $conversation_id, array $data)
    {
        $conversation = $this->repository->find($conversation_id);
        return $this->repository->update($conversation, $data);
    }
}