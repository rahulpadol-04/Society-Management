<?php

declare(strict_types=1);

namespace App\Http\Controllers\Structure;

use App\Http\Controllers\Controller;
use App\Http\Requests\Structure\StoreFlatRequest;
use App\Models\Flat;
use App\Models\Tower;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FlatController extends Controller
{
    public function create(): View
    {
        $this->authorize('create', Flat::class);

        return view('structure.flats.create', $this->formData());
    }

    public function store(StoreFlatRequest $request): RedirectResponse
    {
        $flat = Flat::create($request->validated());

        return redirect()->route('structure.index')->with('success', "Unit {$flat->number} created.");
    }

    public function show(Flat $flat): View
    {
        $this->authorize('view', $flat);

        $flat->load(['tower', 'floor', 'owner', 'parkingSlots']);

        return view('structure.flats.show', ['flat' => $flat]);
    }

    public function edit(Flat $flat): View
    {
        $this->authorize('update', $flat);

        return view('structure.flats.edit', array_merge(['flat' => $flat], $this->formData()));
    }

    public function update(StoreFlatRequest $request, Flat $flat): RedirectResponse
    {
        $flat->update($request->validated());

        return redirect()->route('flats.show', $flat)->with('success', 'Unit updated.');
    }

    public function destroy(Flat $flat): RedirectResponse
    {
        $this->authorize('delete', $flat);

        $flat->delete();

        return redirect()->route('structure.index')->with('success', 'Unit deleted.');
    }

    protected function formData(): array
    {
        return [
            'towers'    => Tower::active()->orderBy('name')->get(),
            'residents' => User::whereHas('roles', fn ($q) => $q->whereIn('slug', ['resident', 'tenant']))
                ->orderBy('name')->get(['id', 'name']),
        ];
    }
}
