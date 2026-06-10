<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Notices;

use App\Http\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Poll;
use App\Services\Notices\NoticeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class PollController extends Controller
{
    use ApiResponse;

    public function __construct(protected NoticeService $service) {}

    public function vote(Request $request, Poll $poll): JsonResponse
    {
        $this->authorize('vote', $poll);

        $request->validate([
            'option_ids'   => ['required', 'array', 'min:1'],
            'option_ids.*' => ['required', 'integer', 'exists:poll_options,id'],
        ]);

        try {
            $this->service->vote($poll, $request->input('option_ids', []), (int) auth()->id());

            return $this->ok(null, 'Vote recorded.');
        } catch (RuntimeException $e) {
            return $this->fail($e->getMessage(), 422);
        }
    }
}
