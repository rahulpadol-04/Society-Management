<?php

declare(strict_types=1);

namespace App\Http\Controllers\Communication;

use App\Http\Controllers\Controller;
use App\Http\Requests\Communication\StoreMessageRequest;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\User;
use App\Services\Communication\CommunicationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function __construct(protected CommunicationService $service) {}

    public function inbox(): View
    {
        $userId = auth()->id();

        $conversations = Conversation::whereHas(
            'participants',
            fn ($q) => $q->where('user_id', $userId)
        )
            ->with(['creator', 'participants.participant'])
            ->latest('last_message_at')
            ->get();

        return view('communication.messages.inbox', compact('conversations'));
    }

    public function show(Conversation $conversation): View
    {
        $this->authorize('view', $conversation);

        $conversation->load(['messages.author', 'participants.participant']);

        // Mark as read for current user.
        ConversationParticipant::where('conversation_id', $conversation->id)
            ->where('user_id', auth()->id())
            ->update(['last_read_at' => now()]);

        $users = User::whereHas('roles')->get(['id', 'name']);

        return view('communication.messages.show', compact('conversation', 'users'));
    }

    public function store(StoreMessageRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if (! empty($data['conversation_id'])) {
            // Reply to existing conversation.
            $conversation = Conversation::findOrFail($data['conversation_id']);
            $this->authorize('view', $conversation);
            $this->service->postMessage($conversation, auth()->id(), $data['body']);

            return redirect()->route('communication.messages.show', $conversation)
                ->with('success', 'Reply sent.');
        }

        // Start a new conversation.
        $participantIds = $data['participant_ids'] ?? [];

        $conversation = $this->service->startConversation(
            $participantIds,
            $data['subject'] ?? null,
            $data['body'],
            auth()->id(),
        );

        return redirect()->route('communication.messages.show', $conversation)
            ->with('success', 'Conversation started.');
    }
}
