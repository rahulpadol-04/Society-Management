<?php

declare(strict_types=1);

namespace App\Services\Notices;

use App\Events\Notices\NoticePublished;
use App\Models\Notice;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\PollVote;
use App\Repositories\Contracts\NoticeRepositoryInterface;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Encapsulates the notice lifecycle: creation, publishing (fires event),
 * and poll management including vote recording with duplicate-vote prevention.
 */
class NoticeService extends BaseService
{
    public function __construct(NoticeRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function create(array $data): Notice
    {
        return DB::transaction(function () use ($data): Notice {
            /** @var Notice */
            return $this->repository->create([
                ...$data,
                'author_id' => $data['author_id'] ?? auth()->id(),
            ]);
        });
    }

    public function publish(Notice $notice): Notice
    {
        return DB::transaction(function () use ($notice): Notice {
            $notice->update([
                'is_published' => true,
                'published_at' => now(),
            ]);

            NoticePublished::dispatch($notice->refresh());

            return $notice->refresh();
        });
    }

    public function createPoll(?Notice $notice, array $data, array $optionLabels): Poll
    {
        return DB::transaction(function () use ($notice, $data, $optionLabels): Poll {
            $societyId = $notice?->society_id ?? current_society_id();

            /** @var Poll */
            $poll = Poll::create([
                'society_id'      => $societyId,
                'notice_id'       => $notice?->id,
                'question'        => $data['question'],
                'description'     => $data['description'] ?? null,
                'multiple_choice' => (bool) ($data['multiple_choice'] ?? false),
                'closes_at'       => $data['closes_at'] ?? null,
                'is_active'       => true,
                'created_by'      => auth()->id(),
            ]);

            foreach ($optionLabels as $label) {
                if (filled($label)) {
                    PollOption::create([
                        'society_id'  => $societyId,
                        'poll_id'     => $poll->id,
                        'label'       => $label,
                        'votes_count' => 0,
                    ]);
                }
            }

            return $poll->load('options');
        });
    }

    /**
     * Record a vote (or votes for multiple_choice). Prevents double-voting.
     * For single-choice polls, if the user already voted throws RuntimeException.
     * For multiple-choice polls, silently skips already-voted options.
     *
     * @param  int[]  $optionIds
     */
    public function vote(Poll $poll, array $optionIds, int $userId): void
    {
        DB::transaction(function () use ($poll, $optionIds, $userId): void {
            if (! $poll->multiple_choice && $poll->hasVoted($userId)) {
                throw new RuntimeException('You have already voted on this poll.');
            }

            $poll->loadMissing('options');

            foreach ($optionIds as $optionId) {
                // Skip if already voted on this specific option (multiple choice).
                $alreadyVoted = PollVote::where('poll_id', $poll->id)
                    ->where('poll_option_id', $optionId)
                    ->where('user_id', $userId)
                    ->exists();

                if ($alreadyVoted) {
                    continue;
                }

                PollVote::create([
                    'society_id'     => $poll->society_id,
                    'poll_id'        => $poll->id,
                    'poll_option_id' => $optionId,
                    'user_id'        => $userId,
                ]);

                PollOption::where('id', $optionId)->increment('votes_count');
            }
        });
    }

    public function closePoll(Poll $poll): Poll
    {
        $poll->update(['is_active' => false]);

        return $poll->refresh();
    }
}
