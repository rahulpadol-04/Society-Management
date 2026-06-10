<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Facilities;

use App\Http\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\FacilityResource;
use App\Models\Facility;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FacilityController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Facility::class);

        $facilities = Facility::active()->latest()->get();

        return $this->ok(
            $facilities->map(fn ($f) => (new FacilityResource($f))->resolve()),
            'Facilities retrieved.'
        );
    }
}
