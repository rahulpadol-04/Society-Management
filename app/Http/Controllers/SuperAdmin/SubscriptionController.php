<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\SubscriptionInvoice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->can('subscriptions.view'), 403);

        $query = Subscription::with(['society', 'plan'])
            ->latest();

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $subscriptions = $query->paginate(20)->withQueryString();

        $statusCounts = Subscription::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('superadmin.subscriptions.index', compact('subscriptions', 'statusCounts'));
    }

    public function show(Subscription $subscription): View
    {
        abort_unless(request()->user()->can('subscriptions.view'), 403);

        $subscription->load(['society', 'plan']);

        $invoices = SubscriptionInvoice::where('subscription_id', $subscription->id)
            ->latest()
            ->get();

        return view('superadmin.subscriptions.show', compact('subscription', 'invoices'));
    }

    public function cancel(Subscription $subscription): RedirectResponse
    {
        $this->authorize('cancel', $subscription);

        $subscription->update([
            'status'       => 'cancelled',
            'cancelled_at' => now(),
        ]);

        // Sync society subscription_status
        $subscription->society?->update(['subscription_status' => 'cancelled']);

        return redirect()->route('subscriptions.show', $subscription)
            ->with('success', 'Subscription cancelled.');
    }
}
