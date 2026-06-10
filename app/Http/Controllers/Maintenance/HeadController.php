<?php

declare(strict_types=1);

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\StoreMaintenanceHeadRequest;
use App\Http\Requests\Maintenance\UpdateMaintenanceHeadRequest;
use App\Models\MaintenanceHead;
use App\Repositories\Contracts\MaintenanceHeadRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class HeadController extends Controller
{
    public function __construct(protected MaintenanceHeadRepositoryInterface $repo) {}

    public function index(): View
    {
        $this->authorize('viewAny', MaintenanceHead::class);

        $heads = $this->repo->query()->latest()->get();

        return view('maintenance.heads.index', compact('heads'));
    }

    public function create(): View
    {
        $this->authorize('create', MaintenanceHead::class);

        return view('maintenance.heads.create');
    }

    public function store(StoreMaintenanceHeadRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_taxable'] = (bool) ($data['is_taxable'] ?? false);
        $data['is_active']  = (bool) ($data['is_active']  ?? true);

        $this->repo->create($data);

        return redirect()->route('maintenance.heads.index')
            ->with('success', 'Maintenance head created.');
    }

    public function edit(MaintenanceHead $head): View
    {
        $this->authorize('update', $head);

        return view('maintenance.heads.edit', compact('head'));
    }

    public function update(UpdateMaintenanceHeadRequest $request, MaintenanceHead $head): RedirectResponse
    {
        $data = $request->validated();
        $data['is_taxable'] = (bool) ($data['is_taxable'] ?? $head->is_taxable);
        $data['is_active']  = (bool) ($data['is_active']  ?? $head->is_active);

        $this->repo->update($head->id, $data);

        return redirect()->route('maintenance.heads.index')
            ->with('success', 'Maintenance head updated.');
    }

    public function destroy(MaintenanceHead $head): RedirectResponse
    {
        $this->authorize('delete', $head);

        $head->delete();

        return redirect()->route('maintenance.heads.index')
            ->with('success', 'Maintenance head deleted.');
    }
}
