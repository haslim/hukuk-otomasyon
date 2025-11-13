<?php

namespace App\Services;

use App\Repositories\TaskRepository;

class TaskService
{
    public function __construct(private readonly TaskRepository $tasks)
    {
    }

    public function list(array $filters = [])
    {
        return $this->tasks->all($filters);
    }

    public function create(array $data)
    {
        return $this->tasks->create($data);
    }

    public function update(string $id, array $data)
    {
        return $this->tasks->update($id, $data);
    }
}
