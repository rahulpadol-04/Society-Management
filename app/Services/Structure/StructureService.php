<?php

declare(strict_types=1);

namespace App\Services\Structure;

use App\Models\Flat;
use App\Models\Floor;
use App\Models\Tower;
use App\Repositories\Contracts\FlatRepositoryInterface;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;

/**
 * Orchestrates the society structure. Building a tower can optionally
 * auto-generate its floors (and, with units_per_floor, its flats) so admins
 * can lay out a whole tower in a single action.
 */
class StructureService extends BaseService
{
    public function __construct(FlatRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /** Create a tower and optionally scaffold its floors + flats. */
    public function createTower(array $data, bool $scaffold = false): Tower
    {
        return DB::transaction(function () use ($data, $scaffold) {
            /** @var Tower $tower */
            $tower = Tower::create($data);

            if ($scaffold && $tower->total_floors > 0) {
                $this->scaffoldFloors($tower);
            }

            return $tower;
        });
    }

    /** Generate floors 1..total_floors and (if set) units_per_floor flats each. */
    public function scaffoldFloors(Tower $tower): void
    {
        DB::transaction(function () use ($tower) {
            for ($n = 1; $n <= $tower->total_floors; $n++) {
                $floor = Floor::firstOrCreate(
                    ['tower_id' => $tower->id, 'number' => $n],
                    ['society_id' => $tower->society_id, 'name' => $this->floorName($n)]
                );

                for ($u = 1; $u <= $tower->units_per_floor; $u++) {
                    $number = ($tower->code ?: $tower->name).'-'.$n.str_pad((string) $u, 2, '0', STR_PAD_LEFT);

                    Flat::firstOrCreate(
                        ['society_id' => $tower->society_id, 'number' => $number],
                        ['tower_id' => $tower->id, 'floor_id' => $floor->id, 'status' => 'vacant']
                    );
                }
            }
        });
    }

    public function occupancySummary(): array
    {
        /** @var FlatRepositoryInterface $repo */
        $repo = $this->repository;

        return [
            'counts'    => $repo->statusCounts(),
            'occupancy' => $repo->occupancyRate(),
            'towers'    => Tower::count(),
            'flats'     => Flat::count(),
        ];
    }

    protected function floorName(int $n): string
    {
        return match ($n) {
            1 => 'Ground Floor',
            default => ($n - 1).$this->ordinal($n - 1).' Floor',
        };
    }

    protected function ordinal(int $n): string
    {
        if (in_array($n % 100, [11, 12, 13], true)) {
            return 'th';
        }

        return match ($n % 10) {
            1 => 'st', 2 => 'nd', 3 => 'rd', default => 'th',
        };
    }
}
