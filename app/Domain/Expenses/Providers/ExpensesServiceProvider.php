<?php

namespace App\Domain\Expenses\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Expenses\ExpenseRepository;
use App\Domain\Expenses\Sources\MonthlyBudgetSource;
use App\Domain\Expenses\Sources\PurchaseRequestSource;

class ExpensesServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind as a singleton so the same instance is reused per request.
        $this->app->singleton(ExpenseRepository::class, function ($app) {
            return new ExpenseRepository(new PurchaseRequestSource(), new MonthlyBudgetSource());
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
