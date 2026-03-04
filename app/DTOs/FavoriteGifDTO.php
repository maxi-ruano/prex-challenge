<?php

namespace App\DTOs;

class FavoriteGifDTO
{
    public function __construct(
        public readonly string $gifId,
        public readonly string $alias,
        public readonly int $userId
    ) {}

    public static function fromRequest(array $data, int $userId): self
    {
        return new self(
            gifId: $data['gif_id'],
            alias: $data['alias'],
            userId: $userId
        );
    }
}