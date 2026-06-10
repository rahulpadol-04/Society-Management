<?php

declare(strict_types=1);

namespace App\Http\Controllers\Communication;

use App\Http\Controllers\Controller;
use App\Http\Requests\Communication\StoreMessageTemplateRequest;
use App\Http\Requests\Communication\UpdateMessageTemplateRequest;
use App\Models\MessageTemplate;
use App\Services\Communication\CommunicationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TemplateController extends Controller
{
    public function __construct(protected CommunicationService $service) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', MessageTemplate::class);

        $templates = MessageTemplate::latest()->get();

        return view('communication.templates.index', compact('templates'));
    }

    public function store(StoreMessageTemplateRequest $request): RedirectResponse
    {
        $this->authorize('create', MessageTemplate::class);

        MessageTemplate::create($request->validated());

        return redirect()->route('communication.templates.index')
            ->with('success', 'Template created.');
    }

    public function update(UpdateMessageTemplateRequest $request, MessageTemplate $template): RedirectResponse
    {
        $this->authorize('update', $template);

        $template->update($request->validated());

        return redirect()->route('communication.templates.index')
            ->with('success', 'Template updated.');
    }

    public function destroy(MessageTemplate $template): RedirectResponse
    {
        $this->authorize('delete', $template);

        $template->delete();

        return redirect()->route('communication.templates.index')
            ->with('success', 'Template deleted.');
    }
}
