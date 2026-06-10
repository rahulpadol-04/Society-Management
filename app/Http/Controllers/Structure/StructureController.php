<?php

declare(strict_types=1);

namespace App\Http\Controllers\Structure;

use App\Http\Controllers\Controller;
use App\Models\Flat;
use App\Models\Tower;
use App\Services\Structure\StructureService;
use Illuminate\View\View;

class StructureController extends Controller
{
    public function __construct(protected StructureService $service) {}

    public function index(): View
    {
        $this->authorize('viewAny', Tower::class);

        return view('structure.index', [
            'summary' => $this->service->occupancySummary(),
            'towers'  => Tower::withCount('flats')->orderBy('name')->get(),
            'flats'   => Flat::with(['tower', 'owner'])->orderBy('number')->limit(1000)->get(),
        ]);
    }
}
