<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Notices;

use App\Http\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\NoticeResource;
use App\Models\Notice;
use App\Services\Notices\NoticeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NoticeController extends Controller
{
    use ApiResponse;

    public function __construct(protected NoticeService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Notice::class);

        $notices = $this->service->paginate(
            array_merge($request->only(['category', 'audience', 'search', 'sort', 'dir', 'per_page']), ['is_published' => 1]),
            ['author', 'poll.options'],
        );

        return $this->paginated(
            $notices->setCollection(
                $notices->getCollection()->map(fn ($n) => (new NoticeResource($n))->resolve())
            )
        );
    }

    public function show(Notice $notice): JsonResponse
    {
        $this->authorize('view', $notice);

        return $this->ok(new NoticeResource($notice->load(['author', 'poll.options'])));
    }
}
