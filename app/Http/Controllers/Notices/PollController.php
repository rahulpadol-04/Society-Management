<?php

declare(strict_types=1);

namespace App\Http\Controllers\Notices;

use App\Http\Controllers\Controller;
use App\Http\Requests\Notices\StorePollRequest;
use App\Models\Notice;
use App\Models\Poll;
use App\Services\Notices\NoticeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

class PollController extends Controller
{
    public function __construct(protected NoticeService $service) {}

    public function store(StorePollRequest $request, Notice $notice): RedirectResponse
    {
        $this->authorize('manage', Poll::class);

        $data = $request->validated();

        $this->service->createPoll(
            $notice,
            [
                'question'        => $data['question'],
                'description'     => $data['description'] ?? null,
                'multiple_choice' => (bool) ($data['multiple_choice'] ?? false),
                'closes_at'       => $data['closes_at'] ?? null,
            ],
            $data['options']
        );

        return back()->with('success', 'Poll created successfully.');
    }

    public function vote(Request $request, Poll $poll): RedirectResponse
    {
        $this->authorize('vote', $poll);

        $request->validate([
            'option_ids'   => ['required', 'array', 'min:1'],
            'option_ids.*' => ['required', 'integer', 'exists:poll_options,id'],
        ]);

        try {
            $this->service->vote($poll, $request->input('option_ids', []), (int) auth()->id());
            return back()->with('success', 'Your vote has been recorded.');
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function close(Request $request, Poll $poll): RedirectResponse
    {
        $this->authorize('close', $poll);

        $this->service->closePoll($poll);

        return back()->with('success', 'Poll closed.');
    }

    public function results(Poll $poll): RedirectResponse
    {
        $this->authorize('manage', Poll::class);

        return redirect()->route('notices.show', $poll->notice_id);
    }
}
