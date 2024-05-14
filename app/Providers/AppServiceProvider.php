<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Yajra\DataTables\Html\Builder;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Model::unguard();  -> kalau pake ini , semua model tidak perlu dibuat fillable / di definisikan
        Blade::directive('currency', function ($expression) {
            return $expression != null ? "<?php echo 'Rp. ' . number_format($expression, 0, ',', '.'); ?>" : "";
        });
        Blade::directive('currencyUSD', function ($expression) {
            return $expression != null ? "<?php echo '$' . number_format($expression, 0, ',', '.'); ?>" : "";
        });
        Blade::directive('currencyCNY', function ($expression) {
            return $expression != null ? "<?php echo '¥' . number_format($expression, 0, ',', '.'); ?>" : "";
        });

        Blade::directive('formatDate', function ($expression) {
            return "<?php echo $expression !== null ? \Carbon\Carbon::parse($expression)->format('d-m-Y') : '-'; ?>";
        });
        Paginator::useBootstrap();
        Builder::useVite();
    }
}
