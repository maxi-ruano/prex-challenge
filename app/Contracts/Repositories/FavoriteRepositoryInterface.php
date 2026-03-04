<?php

namespace App\Contracts\Repositories;

interface FavoriteRepositoryInterface
{
    public function create(array $data);
    public function findByUser(int $userId);
    public function findByUserAndGif(int $userId, string $gifId);
    public function delete(int $id, int $userId);
    public function exists(int $userId, string $gifId);
}