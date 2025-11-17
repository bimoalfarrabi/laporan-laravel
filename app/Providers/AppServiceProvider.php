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

        Blade::directive('markdown_limit', function ($expression) {
            // $expression will be something like "$report->data['deskripsi'], 100"
            // We need to parse it to get the content and the limit
            list($content, $limit) = explode(',', $expression, 2);

            return "<?php
                \$parsedContent = (new Parsedown())->text(preg_replace('/(?<!\S)\*([^\s*](?:[^\*]*[^\s*])?)\*(?!\S)/', '**$1**', $content));
                \$plainText = strip_tags(\$parsedContent);
                echo \Illuminate\Support\Str::limit(\$plainText, $limit);
            ?>";
        });

        Blade::directive('markdown_limit', function ($expression) {
            // $expression will be something like "$report->data['deskripsi'], 100"
            // We need to parse it to get the content and the limit
            list($content, $limit) = explode(',', $expression, 2);

            return "<?php
                \$parsedContent = (new Parsedown())->text(preg_replace('/(?<!\S)\*([^\s*](?:[^\*]*[^\s*])?)\*(?!\S)/', '**$1**', $content));
                \$plainText = strip_tags(\$parsedContent);
                echo \Illuminate\Support\Str::limit(\$plainText, $limit);
            ?>";
        });
    }
}
