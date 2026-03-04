<?php

namespace App\Contracts\Repositories;

interface UserRepositoryInterface
{
    public function findByEmail(string $email);
    public function findById(int $id);
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
}