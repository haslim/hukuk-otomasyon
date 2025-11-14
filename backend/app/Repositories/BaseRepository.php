<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository
{
    public function __construct(protected Model $model)
    {
    }

    public function all(array $filters = [])
    {
        return $this->model->newQuery()->where($filters)->orderByDesc('created_at')->get();
    }

    public function find(string $id)
    {
        return $this->model->newQuery()->findOrFail($id);
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
}
