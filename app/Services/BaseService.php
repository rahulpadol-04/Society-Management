<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\Contracts\RepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Base application service. Encapsulates business logic and orchestrates one or
 * more repositories. Module services extend this and inject their repository.
 */
abstract class BaseService
{
    protected RepositoryInterface $repository;

    /** Direct access to the underlying repository for bespoke queries. */
    public function repository(): RepositoryInterface
    {
        return $this->repository;
    }

    public function paginate(array $filters = [], array $with = []): LengthAwarePaginator
    {
        return $this->repository->paginate(
            (int) ($filters['per_page'] ?? config('communityos.pagination', 15)),
            $filters,
            $with
        );
    }

    public function find(int|string $id, array $with = []): ?Model
    {
        return $this->repository->find($id, $with);
    }

    public function findOrFail(int|string $id, array $with = []): Model
    {
        return $this->repository->findOrFail($id, $with);
    }

    public function create(array $data): Model
    {
        return DB::transaction(fn () => $this->repository->create($data));
    }

    public function update(int|string $id, array $data): Model
    {
        return DB::transaction(fn () => $this->repository->update($id, $data));
    }

    public function delete(int|string $id): bool
    {
        return DB::transaction(fn () => $this->repository->delete($id));
    }
}
