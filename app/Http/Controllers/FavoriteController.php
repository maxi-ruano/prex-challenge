<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponseTrait;
use App\DTOs\FavoriteGifDTO;
use App\Contracts\Repositories\FavoriteRepositoryInterface;
use App\Contracts\Services\GiphyServiceInterface;
use App\Exceptions\GiphyApiException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Log;

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
        try {
            $request->validate([
                'gif_id' => 'required|string',
                'alias' => 'required|string|max:255',
                'user_id' => 'required|integer|exists:users,id'
            ]);

            if ($request->user()->id !== (int) $request->user_id) {
                return $this->errorResponse('The user does not match', 403);
            }

            if ($this->favoriteRepository->exists($request->user_id, $request->gif_id)) {
                return $this->errorResponse('This gif is already saved', 409);
            }

            $gifData = $this->giphyService->findById($request->gif_id);

            $favorite = $this->favoriteRepository->create([
                'user_id' => $request->user_id,
                'gif_id' => $request->gif_id,
                'alias' => $request->alias,
                'gif_data' => $gifData
            ]);

            return $this->successResponse($favorite, 'GIF saved', 201);

        } catch (ValidationException $e) {
            Log::warning('Error validation data invalid', [
                'errors' => $e->errors(),
                'user_id' => $request->user_id ?? null
            ]);
            return $this->errorResponse('Invalid data', 422, $e->errors());

        } catch (GiphyApiException $e) {
            Log::error('Error data GIPHY', [
                'gif_id' => $request->gif_id,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
            
            if ($e->getCode() === 404) {
                return $this->errorResponse('GIF not found', 404);
            }
            
            return $this->errorResponse('Error consult GIPHY', 502);

        } catch (QueryException $e) {
            Log::error('Error to save favorite in DB', [
                'user_id' => $request->user_id,
                'gif_id' => $request->gif_id,
                'error' => $e->getMessage()
            ]);
            
            if ($e->getCode() == 23000) {
                return $this->errorResponse('This gif is already saved', 409);
            }
            
            return $this->errorResponse('Error to save favorite', 500);

        } catch (ModelNotFoundException $e) {
            Log::error('User not found', [
                'user_id' => $request->user_id
            ]);
            return $this->errorResponse('User not found', 404);

        } catch (\Exception $e) {
            Log::error('unexpected error to save favorite', [
                'user_id' => $request->user_id ?? null,
                'gif_id' => $request->gif_id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->errorResponse('Error servidor', 500);
        }
    }

 
    public function index(Request $request)
    {
        try {
            $favorites = $this->favoriteRepository->findByUser($request->user()->id);
            
            return $this->successResponse($favorites, 'Favorites loaded');

        } catch (QueryException $e) {
            Log::error('Error list favorites in DB', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);
            return $this->errorResponse('Error loading favorites', 500);

        } catch (\Exception $e) {
            Log::error('unxpected error list favorites', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);
            return $this->errorResponse('Error interno', 500);
        }
    }

  
    public function destroy(Request $request, int $id)
    {
        try {
            $deleted = $this->favoriteRepository->delete($id, $request->user()->id);

            if (!$deleted) {
                return $this->errorResponse('Favorite not found', 404);
            }

            return $this->successResponse(null, 'Favorite deleted');

        } catch (QueryException $e) {
            Log::error('Error delete favorite in DB', [
                'favorite_id' => $id,
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);
            return $this->errorResponse('Error deleting favorite', 500);

        } catch (\Exception $e) {
            Log::error('Error unxpected delete favorite', [
                'favorite_id' => $id,
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);
            return $this->errorResponse('Error interno', 500);
        }
    }
}