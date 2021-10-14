<?php

namespace Dive\NovaTranslationEditor;

use Dive\NovaTranslationEditor\Commands\PublishCommand;
use Dive\NovaTranslationEditor\Http\Middleware\Authorize;
use Illuminate\Routing\Router;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Nova;

class ToolServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->registerCommands();
            $this->registerConfig();
            $this->registerMigration();
        }

        $this->provideScriptData();
        $this->registerRoutes();
        $this->registerViews();
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/nova-translation-editor.php', 'nova-translation-editor');
    }

    protected function routes(Router $router)
    {
        if (! $this->app->routesAreCached()) {
            $router->middleware(['nova', Authorize::class])
                ->prefix('nova-vendor/nova-translation-editor')
                ->group(__DIR__.'/../routes/api.php');
        }
    }

    private function provideScriptData()
    {
        Nova::serving(function () {
            Nova::provideToScript([
                'tool' => $this->app['config']->get('nova-translation-editor'),
            ]);
        });
    }

    private function registerCommands()
    {
        $this->commands([
            PublishCommand::class,
        ]);
    }

    private function registerConfig()
    {
        $config = 'nova-translation-editor.php';

        $this->publishes([
            __DIR__."/../config/{$config}" => $this->app->configPath($config),
        ], 'config');
    }

    private function registerMigration()
    {
        $migration = 'create_language_lines_table.php';
        $doesntExist = Collection::make(glob($this->app->databasePath('migrations/*.php')))
            ->every(fn ($filename) => ! str_ends_with($filename, $migration));

        if ($doesntExist) {
            $timestamp = date('Y_m_d_His', time());
            $stub = __DIR__."/../database/migrations/{$migration}.stub";

            $this->publishes([
                $stub => $this->app->databasePath("migrations/{$timestamp}_{$migration}"),
            ], 'migrations');
        }
    }

    private function registerRoutes()
    {
        $this->app->booted(function () {
            $this->routes($this->app['router']);
        });
    }

    private function registerViews()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'nova-translation-editor');
    }
}
