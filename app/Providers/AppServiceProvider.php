<?php

namespace App\Providers;

use App\Models\Detail;
use App\Models\HeaderFormOvertime;
use App\Observers\DetailObserver;
use App\Observers\HeaderFormOvertimeObserver;
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
        HeaderFormOvertime::observe(HeaderFormOvertimeObserver::class);
        Detail::observe(DetailObserver::class);

        Blade::directive("currency", function ($expression) {
            return "<?php echo $expression !== null ? 'Rp ' . number_format(floatval($expression), 2, ',', '.') : ''; ?>";
        });

        Blade::directive("currencyUSD", function ($expression) {
            return "<?php echo $expression !== null ? '$ ' . number_format(floatval($expression), 2, ',', '.') : ''; ?>";
        });

        Blade::directive("currencyCNY", function ($expression) {
            return "<?php echo $expression !== null ? '¥ ' . number_format(floatval($expression), 2, ',', '.') : ''; ?>";
        });

        Blade::directive("formatDate", function ($expression) {
            return "<?php echo $expression !== null ? \Carbon\Carbon::parse($expression)->format('d-m-Y') : '-'; ?>";
        });
        Paginator::useBootstrap();
        Builder::useVite();
    }
}
