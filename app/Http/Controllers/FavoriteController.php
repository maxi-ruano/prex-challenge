<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponseTrait;
use App\DTOs\FavoriteGifDTO;
use App\Contracts\Repositories\FavoriteRepositoryInterface;
use App\Contracts\Services\GiphyServiceInterface;   
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private FavoriteRepositoryInterface $favoriteRepository,
        private GiphyServiceInterface $giphyService
    ) {}

    /**
     * Guardar GIF favorito
     */
    public function store(Request $request)
    {
        $request->validate([
            'gif_id' => 'required|string',
            'alias' => 'required|string|max:255',
            'user_id' => 'required|integer|exists:users,id'
        ]);

        // Verificar que el usuario autenticado es el mismo que guarda
        if ($request->user()->id !== (int) $request->user_id) {
            return $this->errorResponse('No puedes guardar favoritos para otro usuario', 403);
        }

        // Verificar si ya existe
        if ($this->favoriteRepository->exists($request->user_id, $request->gif_id)) {
            return $this->errorResponse('Este GIF ya está en tus favoritos', 409);
        }

        // Obtener datos del GIF para cachear
        $gifData = $this->giphyService->findById($request->gif_id);

        $favorite = $this->favoriteRepository->create([
            'user_id' => $request->user_id,
            'gif_id' => $request->gif_id,
            'alias' => $request->alias,
            'gif_data' => $gifData
        ]);

        return $this->successResponse($favorite, 'GIF guardado en favoritos', 201);
    }

    /**
     * Listar favoritos del usuario autenticado
     */
    public function index(Request $request)
    {
        $favorites = $this->favoriteRepository->findByUser($request->user()->id);
        
        return $this->successResponse($favorites, 'Lista de favoritos');
    }

    /**
     * Eliminar favorito
     */
    public function destroy(Request $request, int $id)
    {
        $deleted = $this->favoriteRepository->delete($id, $request->user()->id);

        if (!$deleted) {
            return $this->errorResponse('Favorito no encontrado', 404);
        }

        return $this->successResponse(null, 'Favorito eliminado correctamente');
    }
}
