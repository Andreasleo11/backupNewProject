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

        $this->app->bind(
            \App\Domain\Evaluation\Repositories\EvaluationDataRepositoryContract::class,
            \App\Infrastructure\Persistence\Eloquent\Repositories\EloquentEvaluationDataRepository::class
        );
    }

    public function boot(): void {}
}
