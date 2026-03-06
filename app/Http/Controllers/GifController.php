<?php

namespace App\Http\Controllers;

use App\Services\GiphyService; 
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use App\DTOs\SearchGifRequestDTO;
use App\Exceptions\GiphyApiException;  // ← Excepción personalizada
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Log;

class GifController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private GiphyService $giphyService
    ) {}

    public function search(Request $request)
    {
        try {
            $request->validate([
                'query' => 'required|string|min:1',
                'limit' => 'sometimes|integer|min:1|max:100',
                'offset' => 'sometimes|integer|min:0'
            ]);

            $dto = SearchGifRequestDTO::fromRequest($request->all());
            
            $results = $this->giphyService->search(
                $dto->query,
                $dto->limit,
                $dto->offset
            );

            if (empty($results['data'])) {
                return $this->successResponse(
                  [], 
                  'Not found GIFs', 
                  200
                   );
                  }
            return $this->successResponse($results, 'success search GIFs');
            
        } catch (ValidationException $e) {
            return $this->errorResponse('Invalid data in gif search', 422, $e->errors());
            
        } catch (GiphyApiException $e) {
            Log::error('Error in API GIf controller  search  : ' . $e->getMessage());
            return $this->errorResponse('Error consult GIPHY', 502);
            
        } catch (\Exception $e) {
            Log::error('unexpected error in gif search: ' . $e->getMessage());
            return $this->errorResponse('Error servidor', 500);
        }
    }

    public function show(string $id)
    {
        try {
            $gif = $this->giphyService->findById($id);

            if (!$gif) {
                return $this->errorResponse('GIF not found', 404);
            }

            return $this->successResponse($gif, 'GIF found');
            
        } catch (GiphyApiException $e) {
            Log::error('Error in API GIPHY controller  show  : ' . $e->getMessage());
            return $this->errorResponse('Error consult GIPHY', 502);
            
        } catch (\Exception $e) {
            Log::error('unexpected error in gif show controller: ' . $e->getMessage());
            return $this->errorResponse('Error servidor', 500);
        }
    }
}