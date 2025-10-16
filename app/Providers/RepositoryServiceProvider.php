<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Signature\Repositories\UserSignatureRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentUserSignatureRepository;
use Illuminate\Support\ServiceProvider;

final class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserSignatureRepository::class, EloquentUserSignatureRepository::class);
    }

    public function boot(): void {}
}
