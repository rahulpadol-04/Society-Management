<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\RepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Generic Eloquent repository. Module repositories extend this and only need to
 * implement model() plus any bespoke query methods. Tenant isolation is handled
 * transparently by the model's BelongsToTenant global scope.
 */
abstract class BaseRepository implements RepositoryInterface
{
    protected Model $model;

    /** Columns that `paginate()` will apply simple "where = " filters against. */
    protected array $filterable = [];

    /** Columns scanned by the free-text `search` filter. */
    protected array $searchable = [];

    public function __construct()
    {
        $this->model = app($this->model());
    }

    /** @return class-string<Model> */
    abstract protected function model(): string;

    public function query(): Builder
    {
        return $this->model->newQuery();
    }

    public function all(array $columns = ['*']): Collection
    {
        return $this->query()->get($columns);
    }

    public function paginate(int $perPage = 15, array $filters = [], array $with = []): LengthAwarePaginator
    {
        $query = $this->query()->with($with);

        $this->applyFilters($query, $filters);

        $sort = $filters['sort'] ?? 'created_at';
        $dir  = strtolower($filters['dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($sort, $dir)->paginate($perPage)->withQueryString();
    }

    public function find(int|string $id, array $with = []): ?Model
    {
        return $this->query()->with($with)->find($id);
    }

    public function findOrFail(int|string $id, array $with = []): Model
    {
        return $this->query()->with($with)->findOrFail($id);
    }

    public function findBy(string $column, mixed $value): ?Model
    {
        return $this->query()->where($column, $value)->first();
    }

    public function create(array $attributes): Model
    {
        return $this->model->newInstance()->create($attributes);
    }

    public function update(int|string $id, array $attributes): Model
    {
        $model = $this->findOrFail($id);
        $model->update($attributes);

        return $model->refresh();
    }

    public function delete(int|string $id): bool
    {
        return (bool) $this->findOrFail($id)->delete();
    }

    public function count(array $filters = []): int
    {
        $query = $this->query();
        $this->applyFilters($query, $filters);

        return $query->count();
    }

    /**
     * Applies declarative filters: exact matches for $filterable columns and a
     * LIKE search across $searchable columns. Override for richer querying.
     */
    protected function applyFilters(Builder $query, array $filters): void
    {
        foreach ($this->filterable as $column) {
            if (array_key_exists($column, $filters) && $filters[$column] !== null && $filters[$column] !== '') {
                $query->where($column, $filters[$column]);
            }
        }

        if (! empty($filters['search']) && $this->searchable !== []) {
            $term = '%'.$filters['search'].'%';
            $query->where(function (Builder $q) use ($term): void {
                foreach ($this->searchable as $i => $column) {
                    $i === 0 ? $q->where($column, 'like', $term) : $q->orWhere($column, 'like', $term);
                }
            });
        }

        if (! empty($filters['from'])) {
            $query->whereDate('created_at', '>=', $filters['from']);
        }
        if (! empty($filters['to'])) {
            $query->whereDate('created_at', '<=', $filters['to']);
        }
    }
}
