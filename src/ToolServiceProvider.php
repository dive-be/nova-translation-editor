<?php

namespace Dive\NovaTranslationEditor;

use Dive\NovaTranslationEditor\Console\Commands\PublishCommand;
use Dive\NovaTranslationEditor\Http\Middleware\Authorize;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;

class ToolServiceProvider extends ServiceProvider
{
    public function register()
    {
        parent::register();

        $this->mergeConfigFrom(__DIR__.'/../config/nova-translation-editor.php', 'nova-translation-editor');
    }

    public function boot()
    {
        if ($this->app->runningInConsole() && ! Str::contains($this->app->version(), 'Lumen')) {
            $this->commands([
                PublishCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/../config/nova-translation-editor.php' => config_path('nova-translation-editor.php'),
            ], 'config');

            if (! class_exists('CreateLanguageLinesTable')) {
                $timestamp = date('Y_m_d_His', time());

                $this->publishes([
                    __DIR__.'/../database/migrations/create_language_lines_table.php.stub' => database_path('migrations/'.$timestamp.'_create_language_lines_table.php'),
                ], 'migrations');
            }
        }

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'nova-translation-editor');

        $this->app->booted(function () {
            $this->routes();
        });

        Nova::serving(function (ServingNova $event) {
            Nova::provideToScript([
                'tool' => Config::get('nova-translation-editor'),
            ]);
        });
    }

    protected function routes()
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware(['nova', Authorize::class])
                ->prefix('nova-vendor/nova-translation-editor')
                ->group(__DIR__.'/../routes/api.php');
    }
}
