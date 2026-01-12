<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\AutomationDashboard\Services\AutomationDashboardService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AutomationDashboardController extends Controller
{
    public function __construct(
        protected AutomationDashboardService $service
    ) {}

    public function index()
    {
        return response()->json(
            $this->service->list()
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_psid'               => 'required|integer', 
            'conversation_status'         => 'required|string',
            'conversation_updated_from'   => 'required|date', 
            'conversation_updated_to'     => 'required|date',
        ]);

        return response()->json(
            $this->service->create($data),
            201
        );
    }

    public function update(Request $request, int $conversation_id)
    {
        return response()->json(
            $this->service->update($conversation_id, $request->all())
        );
    }

    public function updateConversationLogs(Request $request)
    {
        $validated = $request->validate([
            'customer_psid'         => 'required|integer',
            'conversation_status'         => 'required|string',
            'conversation_updated_from'   => 'required|date',
            'conversation_updated_to'     => 'nullable|date',
        ]);

        $conversation = $this->service->updateConversationLogger($validated);

        return response()->json([
            'success' => true,
            'message' => 'Message Updated',
            'data'    => $conversation,
        ]);
    }
}