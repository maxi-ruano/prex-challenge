<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponseTrait; 
use Illuminate\Http\Request;
use App\Contracts\Services\GiphyServiceInterface;
use App\DTOs\SearchGifRequestDTO;

class GifController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private GiphyServiceInterface $giphyService
    ) {}

    /**
     * Buscar GIFs por término
     */
    public function search(Request $request)
    {
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

        return $this->successResponse($results, 'Búsqueda realizada con éxito');
    }

    /**
     * Obtener GIF por ID
     */
    public function show(string $id)
    {
        $gif = $this->giphyService->findById($id);

        if (!$gif) {
            return $this->errorResponse('GIF no encontrado', 404);
        }

        return $this->successResponse($gif, 'GIF encontrado');
    }
}