<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;

class BaseRepository
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function all(array $filters = [])
    {
        return $this->model->newQuery()->where($filters)->orderByDesc('created_at')->get();
    }

    public function find(string $id)
    {
        return $this->model->newQuery()->findOrFail($id);
    }

    public function findById(string $id)
    {
        return $this->model->newQuery()->find($id);
    }

    public function create(array $data)
    {
        return $this->model->newQuery()->create($data);
    }

    public function update(string $id, array $data)
    {
        $record = $this->find($id);
        $record->update($data);
        return $record;
    }

    public function delete(string $id): bool
    {
        $record = $this->find($id);
        return (bool) $record->delete();
    }

    public function deleteWhere(array $conditions): int
    {
        return $this->model->newQuery()->where($conditions)->delete();
    }

    public function sumWhere(string $column, array $conditions = []): float
    {
        return $this->model->newQuery()
                         ->where($conditions)
                         ->sum($column);
    }

    public function newQuery()
    {
        return $this->model->newQuery();
    }

    public function setModel(Model $model): void
    {
        $this->model = $model;
    }
}
