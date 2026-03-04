<?php

namespace App\DTOs;

class SearchGifRequestDTO
{
    public function __construct(
        public readonly string $query,
        public readonly int $limit = 25,
        public readonly int $offset = 0
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            query: $data['query'],
            limit: $data['limit'] ?? 25,
            offset: $data['offset'] ?? 0
        );
    }
}