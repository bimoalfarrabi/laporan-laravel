<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Parsedown;

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
        Blade::directive('markdown', function ($expression) {
            return "<?php echo (new Parsedown())->text(preg_replace('/(?<!\S)\*([^\s*](?:[^\*]*[^\s*])?)\*(?!\S)/', '**$1**', $expression)); ?>";
        });
    }
}
