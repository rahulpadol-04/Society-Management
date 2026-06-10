<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ContactInquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InquiryController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->can('inquiries.view'), 403);

        $query = ContactInquiry::latest();

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $inquiries = $query->paginate(25)->withQueryString();

        $counts = ContactInquiry::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('superadmin.inquiries.index', compact('inquiries', 'counts'));
    }

    public function show(ContactInquiry $inquiry): View
    {
        $this->authorize('view', $inquiry);

        return view('superadmin.inquiries.show', compact('inquiry'));
    }

    public function updateStatus(Request $request, ContactInquiry $inquiry): RedirectResponse
    {
        $this->authorize('update', $inquiry);

        $request->validate([
            'status' => ['required', Rule::in(['new', 'in_progress', 'responded', 'closed'])],
            'notes'  => ['nullable', 'string', 'max:2000'],
        ]);

        $inquiry->update($request->only('status', 'notes'));

        return redirect()->back()->with('success', 'Inquiry status updated.');
    }

    public function destroy(ContactInquiry $inquiry): RedirectResponse
    {
        $this->authorize('delete', $inquiry);

        $inquiry->delete();

        return redirect()->route('inquiries.index')
            ->with('success', 'Inquiry deleted.');
    }
}
