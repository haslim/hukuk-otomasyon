<?php

namespace App\Services;

use App\Repositories\ClientRepository;

class ClientService
{
    public function __construct(private readonly ClientRepository $clients)
    {
    }

    public function list(array $filters = [])
    {
        return $this->clients->all($filters);
    }

    public function find(string $id)
    {
        return $this->clients->find($id);
    }

    public function create(array $data)
    {
        return $this->clients->create($data);
    }

    public function update(string $id, array $data)
    {
        return $this->clients->update($id, $data);
    }

    public function delete(string $id)
    {
        return $this->clients->delete($id);
    }
}
