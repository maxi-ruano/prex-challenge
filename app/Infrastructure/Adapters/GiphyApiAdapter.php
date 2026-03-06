<?php

namespace App\Infrastructure\Adapters;

use Illuminate\Support\Facades\Http;
use App\Contracts\Services\GiphyServiceInterface;
use App\Exceptions\GiphyApiException;
use Illuminate\Support\Facades\Log;

class GiphyApiAdapter implements GiphyServiceInterface
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
        try {
            $response = Http::timeout(10)->get("{$this->baseUrl}/gifs/search", [
                'api_key' => $this->apiKey,
                'q' => $query,
                'limit' => $limit,
                'offset' => $offset,
                'rating' => 'g',
                'lang' => 'es'
            ]);

            if ($response->failed()) {
                if ($response->status() === 404) {
                    return [];
                }
                
           
                throw new GiphyApiException(
                    "GIPHY error adapter: {$response->status()}",
                    $response->status()
                );
            }

            $data = $response->json();
            
       
            if (!isset($data['data'])) {
                throw new GiphyApiException('Invalid response from GIPHY adapter', 500);
            }

            return $this->formatSearchResponse($data);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
          
            Log::error('Timeout connecting to GIPHY adapter', [
                'query' => $query,
                'error' => $e->getMessage()
            ]);
            throw new GiphyApiException('Not connected to GIPHY adapter (timeout)', 504);
            
        } catch (\Exception $e) {
            
            Log::error('unexpected error in GIPHY adapter', [
                'query' => $query,
                'error' => $e->getMessage()
            ]);
            throw new GiphyApiException('Error servidor ', 500, $e);
        }
    }

    public function findById(string $id): ?array
    {
        try {
            $response = Http::timeout(10)->get("{$this->baseUrl}/gifs/{$id}", [
                'api_key' => $this->apiKey
            ]);

            if ($response->failed()) {
            
                if ($response->status() === 404) {
                    return null;
                }
                
              
                throw new GiphyApiException(
                    "GIPHY error adapter : {$response->status()}",
                    $response->status()
                );
            }

            $data = $response->json();
            
            if (!isset($data['data'])) {
                throw new GiphyApiException('Invalid response from GIPHY adapter', 500);
            }

            return $this->formatSingleGif($data);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Timeout conecting to GIPHY adapter', ['id' => $id]);
            throw new GiphyApiException('Not connected to GIPHY adapter (timeout)', 504);
            
        } catch (\Exception $e) {
            Log::error('unexpected error in GIPHY adapter', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            throw new GiphyApiException('Error servidor', 500, $e);
        }
    }

    private function formatSearchResponse(array $data): array
    {
        return [
            'total' => $data['pagination']['total_count'] ?? 0,
            'count' => $data['pagination']['count'] ?? 0,
            'offset' => $data['pagination']['offset'] ?? 0,
            'data' => array_map([$this, 'formatGif'], $data['data'] ?? [])
        ];
    }

    private function formatSingleGif(array $data): array
    {
        return $this->formatGif($data['data'] ?? $data);
    }

    private function formatGif(array $gif): array
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