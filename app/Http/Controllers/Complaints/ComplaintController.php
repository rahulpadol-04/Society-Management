<?php

declare(strict_types=1);

namespace App\Http\Controllers\Complaints;

use App\Http\Controllers\Controller;
use App\Http\Requests\Complaints\StoreComplaintRequest;
use App\Http\Requests\Complaints\UpdateComplaintRequest;
use App\Models\Complaint;
use App\Models\ComplaintCategory;
use App\Models\User;
use App\Services\Complaints\ComplaintService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ComplaintController extends Controller
{
    public function __construct(protected ComplaintService $service) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Complaint::class);

        $complaints = $this->service->repository()->query()
            ->with(['category', 'raisedBy', 'assignee'])
            ->latest()
            ->limit(1000)
            ->get();

        return view('complaints.index', [
            'complaints'   => $complaints,
            'statusCounts' => $this->service->statusCounts(),
            'categories'   => ComplaintCategory::where('is_active', true)->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Complaint::class);

        return view('complaints.create', [
            'categories' => ComplaintCategory::where('is_active', true)->get(),
        ]);
    }

    public function store(StoreComplaintRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['attachments'] = $this->storeAttachments($request);

        $complaint = $this->service->create($data);

        return redirect()->route('complaints.show', $complaint)
            ->with('success', "Complaint {$complaint->reference} registered.");
    }

    public function show(Complaint $complaint): View
    {
        $this->authorize('view', $complaint);

        $complaint->load(['category', 'raisedBy', 'assignee', 'activities.user', 'feedback']);

        return view('complaints.show', [
            'complaint' => $complaint,
            'staff'     => $this->assignableStaff(),
        ]);
    }

    public function edit(Complaint $complaint): View
    {
        $this->authorize('update', $complaint);

        return view('complaints.edit', [
            'complaint'  => $complaint,
            'categories' => ComplaintCategory::where('is_active', true)->get(),
            'staff'      => $this->assignableStaff(),
        ]);
    }

    public function update(UpdateComplaintRequest $request, Complaint $complaint): RedirectResponse
    {
        $data = $request->validated();

        if (! empty($data['assigned_to']) && $data['assigned_to'] != $complaint->assigned_to) {
            $this->service->assign($complaint, (int) $data['assigned_to'], $data['note'] ?? null);
        }

        if (! empty($data['status']) && $data['status'] !== $complaint->status) {
            $this->service->changeStatus($complaint, $data['status'], $data['note'] ?? null);
        }

        $complaint->update(collect($data)->only(['title', 'description', 'complaint_category_id', 'priority'])->all());

        return redirect()->route('complaints.show', $complaint)->with('success', 'Complaint updated.');
    }

    public function destroy(Complaint $complaint): RedirectResponse
    {
        $this->authorize('delete', $complaint);

        $complaint->delete();

        return redirect()->route('complaints.index')->with('success', 'Complaint deleted.');
    }

    public function feedback(Request $request, Complaint $complaint): RedirectResponse
    {
        $this->authorize('feedback', $complaint);

        $data = $request->validate([
            'rating'  => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->service->addFeedback($complaint, $data['rating'], $data['comment'] ?? null);

        return back()->with('success', 'Thank you for your feedback.');
    }

    protected function assignableStaff()
    {
        return User::whereHas('roles', fn ($q) => $q->whereIn('slug', ['maintenance-staff', 'sub-admin', 'vendor']))->get();
    }

    protected function storeAttachments(Request $request): array
    {
        $paths = [];

        foreach ((array) $request->file('attachments', []) as $file) {
            $paths[] = $file->store('complaints/'.current_society_id(), 'public');
        }

        return $paths;
    }
}
