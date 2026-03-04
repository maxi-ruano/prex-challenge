<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Contracts\Services\GiphyServiceInterface;

class GiphyService implements GiphyServiceInterface
{
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.giphy.api_key');
        $this->baseUrl = config('services.giphy.base_url');
    }

    public function search(string $query, int $limit = 25, int $offset = 0): array
    {
        $response = Http::get("{$this->baseUrl}/gifs/search", [
            'api_key' => $this->apiKey,
            'q' => $query,
            'limit' => $limit,
            'offset' => $offset,
            'rating' => 'g',
            'lang' => 'es'
        ]);

        if (!$response->successful()) {
            return [];
        }

        return $this->formatSearchResponse($response->json());
    }

    public function findById(string $id): ?array
    {
        $response = Http::get("{$this->baseUrl}/gifs/{$id}", [
            'api_key' => $this->apiKey
        ]);

        if (!$response->successful()) {
            return null;
        }

        return $this->formatSingleGif($response->json());
    }

    protected function formatSearchResponse(array $data): array
    {
        return [
            'total' => $data['pagination']['total_count'] ?? 0,
            'count' => $data['pagination']['count'] ?? 0,
            'offset' => $data['pagination']['offset'] ?? 0,
            'data' => array_map([$this, 'formatGif'], $data['data'] ?? [])
        ];
    }

    protected function formatSingleGif(array $data): array
    {
        return $this->formatGif($data['data'] ?? $data);
    }

    protected function formatGif(array $gif): array
    {
        return [
            'id' => $gif['id'],
            'title' => $gif['title'],
            'url' => $gif['url'],
            'images' => [
                'original' => $gif['images']['original']['url'] ?? null,
                'downsized' => $gif['images']['downsized']['url'] ?? null
            ],
            'rating' => $gif['rating'] ?? 'g',
            'user' => $gif['user'] ?? null
        ];
    }
}