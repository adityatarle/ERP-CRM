<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Exception;

abstract class BaseRepository
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Find a model by ID
     */
    public function find(int $id): ?Model
    {
        return $this->model->find($id);
    }

    /**
     * Find a model by ID or fail
     */
    public function findOrFail(int $id): Model
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Get all models
     */
    public function all(): Collection
    {
        return $this->model->all();
    }

    /**
     * Create a new model
     */
    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * Update a model
     */
    public function update(Model $model, array $data): Model
    {
        $model->update($data);
        return $model->fresh();
    }

    /**
     * Delete a model
     */
    public function delete(Model $model): bool
    {
        return $model->delete();
    }

    /**
     * Get paginated results
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->paginate($perPage);
    }

    /**
     * Get models with relationships
     */
    public function with(array $relationships): Collection
    {
        return $this->model->with($relationships)->get();
    }

    /**
     * Find models by criteria
     */
    public function findBy(array $criteria): Collection
    {
        $query = $this->model->newQuery();

        foreach ($criteria as $field => $value) {
            if (is_array($value)) {
                $query->whereIn($field, $value);
            } else {
                $query->where($field, $value);
            }
        }

        return $query->get();
    }

    /**
     * Find first model by criteria
     */
    public function findFirstBy(array $criteria): ?Model
    {
        $query = $this->model->newQuery();

        foreach ($criteria as $field => $value) {
            if (is_array($value)) {
                $query->whereIn($field, $value);
            } else {
                $query->where($field, $value);
            }
        }

        return $query->first();
    }

    /**
     * Count models by criteria
     */
    public function countBy(array $criteria = []): int
    {
        $query = $this->model->newQuery();

        foreach ($criteria as $field => $value) {
            if (is_array($value)) {
                $query->whereIn($field, $value);
            } else {
                $query->where($field, $value);
            }
        }

        return $query->count();
    }

    /**
     * Sum a field by criteria
     */
    public function sumBy(string $field, array $criteria = []): float
    {
        $query = $this->model->newQuery();

        foreach ($criteria as $criteriaField => $value) {
            if (is_array($value)) {
                $query->whereIn($criteriaField, $value);
            } else {
                $query->where($criteriaField, $value);
            }
        }

        return $query->sum($field);
    }

    /**
     * Get models with date range filter
     */
    public function getByDateRange(string $dateField, ?string $startDate = null, ?string $endDate = null): Collection
    {
        $query = $this->model->newQuery();

        if ($startDate && $endDate) {
            $query->whereBetween($dateField, [$startDate, $endDate]);
        } elseif ($startDate) {
            $query->where($dateField, '>=', $startDate);
        } elseif ($endDate) {
            $query->where($dateField, '<=', $endDate);
        }

        return $query->get();
    }

    /**
     * Search models by fields
     */
    public function search(string $searchTerm, array $searchFields): Collection
    {
        $query = $this->model->newQuery();

        if (!empty($searchTerm)) {
            $query->where(function ($q) use ($searchTerm, $searchFields) {
                foreach ($searchFields as $field) {
                    $q->orWhere($field, 'like', '%' . $searchTerm . '%');
                }
            });
        }

        return $query->get();
    }

    /**
     * Get models with ordering
     */
    public function getOrdered(string $orderBy = 'created_at', string $orderDirection = 'desc'): Collection
    {
        return $this->model->orderBy($orderBy, $orderDirection)->get();
    }

    /**
     * Get models with pagination and filters
     */
    public function getPaginatedWithFilters(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // Apply filters
        foreach ($filters as $field => $value) {
            if ($value !== null && $value !== '') {
                if (is_array($value)) {
                    $query->whereIn($field, $value);
                } else {
                    $query->where($field, 'like', '%' . $value . '%');
                }
            }
        }

        return $query->paginate($perPage);
    }

    /**
     * Get statistics
     */
    public function getStatistics(array $criteria = []): array
    {
        $query = $this->model->newQuery();

        foreach ($criteria as $field => $value) {
            if (is_array($value)) {
                $query->whereIn($field, $value);
            } else {
                $query->where($field, $value);
            }
        }

        return [
            'count' => $query->count(),
            'sum' => $query->sum('amount') ?? 0,
            'avg' => $query->avg('amount') ?? 0,
            'min' => $query->min('amount') ?? 0,
            'max' => $query->max('amount') ?? 0
        ];
    }

    /**
     * Bulk create models
     */
    public function bulkCreate(array $data): Collection
    {
        $models = [];
        
        foreach ($data as $item) {
            $models[] = $this->model->create($item);
        }
        
        return collect($models);
    }

    /**
     * Bulk update models
     */
    public function bulkUpdate(array $updates): int
    {
        $updated = 0;
        
        foreach ($updates as $id => $data) {
            $model = $this->find($id);
            if ($model) {
                $model->update($data);
                $updated++;
            }
        }
        
        return $updated;
    }

    /**
     * Soft delete a model (if supported)
     */
    public function softDelete(Model $model): bool
    {
        if (method_exists($model, 'delete')) {
            return $model->delete();
        }
        
        throw new Exception('Model does not support soft delete');
    }

    /**
     * Restore a soft deleted model (if supported)
     */
    public function restore(Model $model): bool
    {
        if (method_exists($model, 'restore')) {
            return $model->restore();
        }
        
        throw new Exception('Model does not support restore');
    }
}
