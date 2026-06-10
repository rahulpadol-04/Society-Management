<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Communication;

use App\Http\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Communication\StoreMessageRequest;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Services\Communication\CommunicationService;
use Illuminate\Http\JsonResponse;

class MessageController extends Controller
{
    use ApiResponse;

    public function __construct(protected CommunicationService $service) {}

    public function inbox(): JsonResponse
    {
        $userId = auth()->id();

        $conversations = Conversation::whereHas(
            'participants',
            fn ($q) => $q->where('user_id', $userId)
        )
            ->with(['creator', 'participants.participant'])
            ->latest('last_message_at')
            ->get();

        return $this->ok($conversations);
    }

    public function show(Conversation $conversation): JsonResponse
    {
        $this->authorize('view', $conversation);

        $conversation->load(['messages.author', 'participants.participant']);

        ConversationParticipant::where('conversation_id', $conversation->id)
            ->where('user_id', auth()->id())
            ->update(['last_read_at' => now()]);

        return $this->ok($conversation);
    }

    public function store(StoreMessageRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (! empty($data['conversation_id'])) {
            $conversation = Conversation::findOrFail($data['conversation_id']);
            $this->authorize('view', $conversation);
            $message = $this->service->postMessage($conversation, auth()->id(), $data['body']);

            return $this->created($message, 'Reply sent.');
        }

        $conversation = $this->service->startConversation(
            $data['participant_ids'] ?? [],
            $data['subject'] ?? null,
            $data['body'],
            auth()->id(),
        );

        return $this->created($conversation, 'Conversation started.');
    }
}
