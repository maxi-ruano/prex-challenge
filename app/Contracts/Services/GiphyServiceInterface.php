<?php

namespace App\Contracts\Services;

interface GiphyServiceInterface
{
    public function search(string $query, int $limit = 25, int $offset = 0): array;
    public function findById(string $id): ?array;
}