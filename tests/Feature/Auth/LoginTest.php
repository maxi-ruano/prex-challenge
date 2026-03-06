<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;
use Mockery;

class LoginTest extends TestCase
{
    public function test_user_can_login_with_valid_credentials()
    {
        $userMock = Mockery::mock(User::class);
        $userMock->shouldReceive('getAttribute')
            ->with('password')
            ->andReturn(bcrypt('password123'));
        
        $userMock->shouldReceive('setAttribute')
            ->with('api_token', Mockery::any())
            ->andReturnSelf();
        
        $userMock->shouldReceive('save')
            ->andReturn(true);

        $repoMock = Mockery::mock(UserRepositoryInterface::class);
        $repoMock->shouldReceive('findByEmail')
            ->with('test@example.com')
            ->andReturn($userMock);

        $this->app->instance(UserRepositoryInterface::class, $repoMock);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'message',
                     'data' => ['token', 'expires_in', 'token_type']
                 ]);
    }

    public function test_login_fails_with_invalid_credentials()
    {
        $repoMock = Mockery::mock(UserRepositoryInterface::class);
        $repoMock->shouldReceive('findByEmail')
            ->with('test@example.com')
            ->andReturn(null);

        $this->app->instance(UserRepositoryInterface::class, $repoMock);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);

        $response->assertStatus(401)
                 ->assertJson([
                     'status' => 'error',
                     'message' => 'Invalid Credentials'
                 ]);
    }
}