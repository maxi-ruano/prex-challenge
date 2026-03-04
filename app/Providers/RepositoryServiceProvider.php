<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Repositories\FavoriteRepositoryInterface;
use App\Contracts\Services\GiphyServiceInterface;
use App\Repositories\EloquentUserRepository;
use App\Repositories\EloquentFavoriteRepository;
use App\Services\GiphyService;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(FavoriteRepositoryInterface::class, EloquentFavoriteRepository::class);
        $this->app->bind(GiphyServiceInterface::class, GiphyService::class);
    }

    public function boot(): void
    {
        //
    }
}