<?php

declare(strict_types=1);

namespace App\Http\Controllers\Notices;

use App\Http\Controllers\Controller;
use App\Http\Requests\Notices\StoreNoticeRequest;
use App\Http\Requests\Notices\UpdateNoticeRequest;
use App\Models\Notice;
use App\Services\Notices\NoticeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NoticeController extends Controller
{
    public function __construct(protected NoticeService $service) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Notice::class);

        $query = $this->service->repository()->query()
            ->with(['author', 'poll.options']);

        // Admins/sub-admins see drafts too; residents only see published.
        if (! $request->user()->can('notices.create')) {
            $query->published();
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        $pinned  = (clone $query)->pinned()->latest('published_at')->get();
        $notices = (clone $query)->where('pinned', false)->latest('published_at')->get();

        return view('notices.index', compact('pinned', 'notices'));
    }

    public function create(): View
    {
        $this->authorize('create', Notice::class);

        return view('notices.create');
    }

    public function store(StoreNoticeRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $attachment = null;
        if ($request->hasFile('attachment')) {
            $attachment = $request->file('attachment')->store('notices/'.current_society_id(), 'public');
        }

        $notice = $this->service->create([
            'title'      => $data['title'],
            'body'       => $data['body'],
            'category'   => $data['category'],
            'audience'   => $data['audience'],
            'pinned'     => (bool) ($data['pinned'] ?? false),
            'event_at'   => $data['event_at'] ?? null,
            'attachment' => $attachment,
        ]);

        // Optional inline poll creation.
        if (! empty($data['poll_question']) && ! empty($data['poll_options'])) {
            $this->service->createPoll(
                $notice,
                [
                    'question'        => $data['poll_question'],
                    'description'     => $data['poll_description'] ?? null,
                    'multiple_choice' => (bool) ($data['poll_multiple_choice'] ?? false),
                    'closes_at'       => $data['poll_closes_at'] ?? null,
                ],
                $data['poll_options']
            );
        }

        return redirect()->route('notices.show', $notice)
            ->with('success', 'Notice created successfully.');
    }

    public function show(Notice $notice): View
    {
        $this->authorize('view', $notice);

        $notice->load(['author', 'poll.options']);

        $userVotedOptionIds = [];
        if ($notice->poll) {
            $userVotedOptionIds = $notice->poll->votes()
                ->where('user_id', auth()->id())
                ->pluck('poll_option_id')
                ->toArray();
        }

        return view('notices.show', compact('notice', 'userVotedOptionIds'));
    }

    public function edit(Notice $notice): View
    {
        $this->authorize('update', $notice);

        return view('notices.edit', compact('notice'));
    }

    public function update(UpdateNoticeRequest $request, Notice $notice): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('notices/'.current_society_id(), 'public');
        } else {
            unset($data['attachment']);
        }

        $data['pinned'] = (bool) ($data['pinned'] ?? false);

        $notice->update($data);

        return redirect()->route('notices.show', $notice)
            ->with('success', 'Notice updated.');
    }

    public function destroy(Notice $notice): RedirectResponse
    {
        $this->authorize('delete', $notice);

        $notice->delete();

        return redirect()->route('notices.index')
            ->with('success', 'Notice deleted.');
    }

    public function publish(Notice $notice): RedirectResponse
    {
        $this->authorize('publish', $notice);

        $this->service->publish($notice);

        return back()->with('success', 'Notice published successfully.');
    }
}
