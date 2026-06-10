<?php

declare(strict_types=1);

namespace App\Http\Controllers\Helpdesk;

use App\Http\Controllers\Controller;
use App\Http\Requests\Helpdesk\StoreEscalationRuleRequest;
use App\Models\EscalationRule;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EscalationController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', \App\Models\SupportTicket::class);

        $rules = EscalationRule::orderBy('level')->get();

        return view('helpdesk.escalation.index', compact('rules'));
    }

    public function store(StoreEscalationRuleRequest $request): RedirectResponse
    {
        EscalationRule::create($request->validated());

        return redirect()->route('helpdesk.escalation.index')->with('success', 'Escalation rule created.');
    }

    public function update(StoreEscalationRuleRequest $request, EscalationRule $escalationRule): RedirectResponse
    {
        $this->authorize('update', \App\Models\SupportTicket::class);

        $escalationRule->update($request->validated());

        return redirect()->route('helpdesk.escalation.index')->with('success', 'Escalation rule updated.');
    }

    public function destroy(EscalationRule $escalationRule): RedirectResponse
    {
        $this->authorize('update', \App\Models\SupportTicket::class);

        $escalationRule->delete();

        return redirect()->route('helpdesk.escalation.index')->with('success', 'Escalation rule deleted.');
    }
}
