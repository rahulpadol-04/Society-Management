<?php

declare(strict_types=1);

namespace App\Http\Controllers\Structure;

use App\Http\Controllers\Controller;
use App\Http\Requests\Structure\StoreTowerRequest;
use App\Models\Tower;
use App\Services\Structure\StructureService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TowerController extends Controller
{
    public function __construct(protected StructureService $service) {}

    public function create(): View
    {
        $this->authorize('create', Tower::class);

        return view('structure.towers.create');
    }

    public function store(StoreTowerRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $scaffold = (bool) ($data['scaffold'] ?? false);
        unset($data['scaffold']);

        $tower = $this->service->createTower($data, $scaffold);

        return redirect()->route('towers.show', $tower)
            ->with('success', "Tower {$tower->name} created.".($scaffold ? ' Floors & units generated.' : ''));
    }

    public function show(Tower $tower): View
    {
        $this->authorize('view', $tower);

        $tower->load(['floors', 'flats.owner', 'flats.floor']);

        return view('structure.towers.show', ['tower' => $tower]);
    }

    public function edit(Tower $tower): View
    {
        $this->authorize('update', $tower);

        return view('structure.towers.edit', ['tower' => $tower]);
    }

    public function update(StoreTowerRequest $request, Tower $tower): RedirectResponse
    {
        $data = $request->validated();
        unset($data['scaffold']);
        $tower->update($data);

        return redirect()->route('towers.show', $tower)->with('success', 'Tower updated.');
    }

    public function destroy(Tower $tower): RedirectResponse
    {
        $this->authorize('delete', $tower);

        $tower->delete();

        return redirect()->route('structure.index')->with('success', 'Tower deleted.');
    }

    /** Auto-generate floors + units for an existing tower. */
    public function scaffold(Tower $tower): RedirectResponse
    {
        $this->authorize('update', $tower);

        $this->service->scaffoldFloors($tower);

        return redirect()->route('towers.show', $tower)->with('success', 'Floors & units generated.');
    }
}
