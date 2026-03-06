<?php

namespace Tests\Feature\Favorite;

use Tests\TestCase;
use App\Models\User;
use App\Contracts\Repositories\FavoriteRepositoryInterface;
use App\Contracts\Services\GiphyServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Mockery;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        
        // Generar token como en el login real
        $token = Str::random(60);
        $this->user->api_token = hash('sha256', $token);
        $this->user->save();
        
        $this->token = $token;
    }

    public function test_user_can_store_favorite()
    {
        $favoriteRepoMock = Mockery::mock(FavoriteRepositoryInterface::class);
        $favoriteRepoMock->shouldReceive('exists')
            ->with($this->user->id, 'abc123')
            ->andReturn(false);
        
        $favoriteRepoMock->shouldReceive('create')
            ->with(Mockery::any())
            ->andReturn((object) [
                'id' => 1,
                'user_id' => $this->user->id,
                'gif_id' => 'abc123',
                'alias' => 'My cat',
                'created_at' => now()
            ]);

        $giphyMock = Mockery::mock(GiphyServiceInterface::class);
        $giphyMock->shouldReceive('findById')
            ->with('abc123')
            ->andReturn(['id' => 'abc123', 'title' => 'Gato']);

        $this->app->instance(FavoriteRepositoryInterface::class, $favoriteRepoMock);
        $this->app->instance(GiphyServiceInterface::class, $giphyMock);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/favorites', [
            'gif_id' => 'abc123',
            'alias' => 'My cat',
            'user_id' => $this->user->id
        ]);

        $response->assertStatus(201);
    }

    public function test_user_cannot_store_duplicate_favorite()
    {
        $favoriteRepoMock = Mockery::mock(FavoriteRepositoryInterface::class);
        $favoriteRepoMock->shouldReceive('exists')
            ->with($this->user->id, 'abc123')
            ->andReturn(true);

        $this->app->instance(FavoriteRepositoryInterface::class, $favoriteRepoMock);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/favorites', [
            'gif_id' => 'abc123',
            'alias' => 'My cat',
            'user_id' => $this->user->id
        ]);

        $response->assertStatus(409)
                 ->assertJson([
                     'status' => 'error',
                     'message' => 'This gif is already saved'
                 ]);
    }
}