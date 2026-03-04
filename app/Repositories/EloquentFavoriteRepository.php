<?php

namespace App\Repositories;

use App\Models\Favorite;
use App\Contracts\Repositories\FavoriteRepositoryInterface;

class EloquentFavoriteRepository implements FavoriteRepositoryInterface
{
    public function create(array $data)
    {
        return Favorite::create($data);
    }

    public function findByUser(int $userId)
    {
        return Favorite::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findByUserAndGif(int $userId, string $gifId)
    {
        return Favorite::where('user_id', $userId)
            ->where('gif_id', $gifId)
            ->first();
    }

    public function delete(int $id, int $userId)
    {
        return Favorite::where('id', $id)
            ->where('user_id', $userId)
            ->delete();
    }

    public function exists(int $userId, string $gifId): bool
    {
        return Favorite::where('user_id', $userId)
            ->where('gif_id', $gifId)
            ->exists();
    }
}