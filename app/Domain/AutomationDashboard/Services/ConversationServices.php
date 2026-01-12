<?php

namespace App\Domain\AutomationDashboard\Services;

use App\Domain\AutomationDashboard\DTO\CreateConversationRecordDTO;
use App\Domain\AutomationDashboard\DTO\UpdateConversationDTO;
use App\Domain\AutomationDashboard\DTO\UpdateStatusDTO;
use App\Domain\AutomationDashboard\DTO\UpdateStatusLogsDTO;
use App\Domain\AutomationDashboard\Models\ConversationModel;
use App\Domain\AutomationDashboard\Repositories\ConversationRepository;
use Carbon\Carbon;

class ConversationServices
{
    public function __construct(
        protected ConversationRepository $repository
    ) {}

    public function list()
    {
        return $this->repository->getAll();
    }
    public function fetchPSID(int $customer_psid)
    {
        return $this->repository->getCustomerPSID($customer_psid);
    }
    public function listLogsHandsoff()
    {
        return $this->repository->displayHumanHandsOff();
    }
    public function create(array $data)
    {
        $dto = new CreateConversationRecordDTO(
            conversation_id: $data['conversation_id'] ?? null,
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
    public function UpdateTransferHandoff(array $data): ConversationModel
    {
        $conversation = $this->repository->find($data['conversation_id']);

        if (!$conversation) {
            throw new \Exception('Conversation not found.');
        }

        $dto = new UpdateStatusDTO(
            customer_psid: null,
            conversation_id: $data['conversation_id'],
            status: $data['status'],
            transfer_count_bot: $data['transfer_count_bot'],
            transfer_count_human: $data['transfer_count_human'],
        );

        return $this->repository->updateTransferLogs($conversation, $dto);
    }
    public function UpdateTransferHandoffBot(array $data): ConversationModel
    {
        $conversation = $this->repository->find_psid($data['customer_psid']);

        if (!$conversation) {
            throw new \Exception('Conversation not found.');
        }

        $dto = new UpdateStatusDTO(
            conversation_id: null,
            customer_psid: $data['customer_psid'],
            status: $data['status'],
            transfer_count_bot: $data['transfer_count_bot'],
            transfer_count_human: $data['transfer_count_human'],
        );

        return $this->repository->updateTransBot($conversation, $dto);
    }

    public function updateConversation(array $data): ConversationModel {
        $conversation = $this->repository->find_psid($data['customer_psid']);
        if (!$conversation) {
            throw new \Exception('Conversation not found.');
        }

        $dto = new UpdateConversationDTO(
            customer_psid: $data['customer_psid'],
            last_message: $data['last_message'],
            date_created: now('Asia/Manila'),
        );

        return $this->repository->updateConversation($conversation, $dto);
    }
    public function updateStatusLogs(array $data): ConversationModel {
        $conversation = $this->repository->find_psid($data['customer_psid']);
        if (!$conversation) {
            throw new \Exception('Conversation not found.');
        }

        $dto = new UpdateStatusLogsDTO(
            customer_psid: $data['customer_psid'],
            status: $data['status'],
            date_created: now('Asia/Manila'),
        );

        return $this->repository->updateStatusLogs($conversation, $dto);
    }
}