<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Contract every repository fulfils. Controllers and services depend on this
 * abstraction (Service-Repository pattern) rather than on Eloquent directly,
 * keeping persistence concerns swappable and testable.
 */
interface RepositoryInterface
{
    public function all(array $columns = ['*']): Collection;

    public function paginate(int $perPage = 15, array $filters = [], array $with = []): LengthAwarePaginator;

    public function find(int|string $id, array $with = []): ?Model;

    public function findOrFail(int|string $id, array $with = []): Model;

    public function findBy(string $column, mixed $value): ?Model;

    public function create(array $attributes): Model;

    public function update(int|string $id, array $attributes): Model;

    public function delete(int|string $id): bool;

    public function count(array $filters = []): int;

    public function query();
}
