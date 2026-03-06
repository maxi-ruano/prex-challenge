<?php

namespace App\Services;

use App\Contracts\Services\GiphyServiceInterface;
use App\Exceptions\GiphyApiException;
use Illuminate\Support\Facades\Log;

class GiphyService
{
    public function __construct(
        private GiphyServiceInterface $giphyAdapter
    ) {}

    public function search(string $query, int $limit = 25, int $offset = 0): array
    {
        try {

            $results = $this->giphyAdapter->search($query, $limit, $offset);

            if (empty($results['data'])) {
            return [];
        }                
            return $results;
            
        } catch (GiphyApiException $e) {
            Log::error('Error service  GIPHY (search): ' . $e->getMessage(), [
                'query' => $query,
                'limit' => $limit,
                'offset' => $offset
            ]);
            
            throw $e;  
        }
    }

    public function findById(string $id): ?array
    {
        try {
            if (empty($id)) {
                throw new \InvalidArgumentException('ID not empty');
            }
            
            $result = $this->giphyAdapter->findById($id);
              if (!$result) {
            return null;
        }       
            return $result;
            
        } catch (GiphyApiException $e) {
            Log::error('unexpected error GIPHY service  (findById): ' . $e->getMessage(), [
                'id' => $id
            ]);
            
            throw $e;
            
        } catch (\InvalidArgumentException $e) {

            Log::warning('Invalid Id: ' . $e->getMessage());
            throw new GiphyApiException($e->getMessage(), 400, $e);
        }
    }
}