<?php

declare(strict_types=1);

namespace App\Services\Communication;

use App\Jobs\Communication\DeliverBroadcast;
use App\Models\Broadcast;
use App\Models\BroadcastRecipient;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\User;
use App\Repositories\Contracts\BroadcastRepositoryInterface;
use App\Services\BaseService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Core Communication service. Handles audience resolution, broadcast dispatch,
 * and the internal messaging (conversations + messages) lifecycle.
 */
class CommunicationService extends BaseService
{
    public function __construct(BroadcastRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    // -------------------------------------------------------------------------
    // Audience resolution (mirrors NotifyComplaintParties role querying)
    // -------------------------------------------------------------------------

    /**
     * Resolve a named audience segment to a Collection of User models belonging
     * to the current tenant, using the same role-based querying pattern as
     * NotifyComplaintParties::societyManagers().
     */
    public function resolveAudience(string $audience): Collection
    {
        $base = User::withoutGlobalScopes()
            ->where('society_id', current_society_id());

        $roles = match ($audience) {
            'owners'    => ['resident'],
            'tenants'   => ['tenant'],
            'staff'     => ['security-guard', 'maintenance-staff', 'accountant', 'sub-admin'],
            'residents' => ['resident', 'tenant'],
            default     => null, // 'all' — no role filter
        };

        if ($roles !== null) {
            $base->whereHas('roles', fn ($q) => $q->whereIn('slug', $roles));
        }

        return $base->get();
    }

    // -------------------------------------------------------------------------
    // Broadcasts
    // -------------------------------------------------------------------------

    /**
     * Resolve the audience, create BroadcastRecipient rows (one per user per
     * channel), update recipients_count + status, then dispatch the delivery job.
     */
    public function sendBroadcast(Broadcast $broadcast): Broadcast
    {
        return DB::transaction(function () use ($broadcast) {
            $users    = $this->resolveAudience($broadcast->audience);
            $channels = $broadcast->channels ?? ['email'];

            foreach ($users as $user) {
                foreach ($channels as $channel) {
                    BroadcastRecipient::create([
                        'society_id'   => $broadcast->society_id,
                        'broadcast_id' => $broadcast->id,
                        'user_id'      => $user->id,
                        'channel'      => $channel,
                        'status'       => 'pending',
                    ]);
                }
            }

            $broadcast->update([
                'recipients_count' => $users->count(),
                'status'           => 'queued',
            ]);

            DeliverBroadcast::dispatch($broadcast->id);

            return $broadcast->refresh();
        });
    }

    // -------------------------------------------------------------------------
    // Internal messaging
    // -------------------------------------------------------------------------

    /**
     * Start a new conversation between the given participants and post the
     * opening message from $authorId.
     */
    public function startConversation(
        array $participantIds,
        ?string $subject,
        string $body,
        int $authorId,
    ): Conversation {
        return DB::transaction(function () use ($participantIds, $subject, $body, $authorId) {
            $conversation = Conversation::create([
                'subject'    => $subject,
                'type'       => count($participantIds) > 2 ? 'group' : 'direct',
                'created_by' => $authorId,
            ]);

            // Ensure the author is always a participant.
            $allParticipants = collect($participantIds)->push($authorId)->unique();

            foreach ($allParticipants as $userId) {
                ConversationParticipant::create([
                    'society_id'      => $conversation->society_id,
                    'conversation_id' => $conversation->id,
                    'user_id'         => $userId,
                ]);
            }

            $this->postMessage($conversation, $authorId, $body);

            return $conversation;
        });
    }

    /**
     * Append a message to an existing conversation and refresh last_message_at.
     */
    public function postMessage(Conversation $conversation, int $userId, string $body): Message
    {
        return DB::transaction(function () use ($conversation, $userId, $body) {
            $message = Message::create([
                'society_id'      => $conversation->society_id,
                'conversation_id' => $conversation->id,
                'user_id'         => $userId,
                'body'            => $body,
            ]);

            $conversation->update(['last_message_at' => now()]);

            return $message;
        });
    }

    // -------------------------------------------------------------------------
    // KPI helpers
    // -------------------------------------------------------------------------

    public function kpi(): array
    {
        return [
            'broadcasts_sent'   => Broadcast::where('status', 'sent')->count(),
            'templates'         => \App\Models\MessageTemplate::count(),
            'recipients_reached' => BroadcastRecipient::where('status', 'sent')->count(),
        ];
    }
}
