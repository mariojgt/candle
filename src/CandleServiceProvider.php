<?php

namespace Mariojgt\Candle;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class CandleServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // Publish configuration
        $this->publishes([
            __DIR__ . '/Config/candle.php' => config_path('candle.php'),
        ], 'config');

        // Publish migrations
        $this->publishes([
            __DIR__ . '/Database/Migrations/' => database_path('migrations'),
        ], 'migrations');

        // Publish views
        $this->publishes([
            __DIR__ . '/Resources/views' => resource_path('views/vendor/candle'),
        ], 'views');

        // Public the dashboard.js to the public path
        $this->publishes([
            __DIR__ . '/Public/dashboard.js' => public_path('vendor/candle/js/dashboard.js'),
        ], 'assets');

        // Load views
        $this->loadViewsFrom(__DIR__ . '/Resources/views', 'candle');

        // Load routes
        $this->registerRoutes();

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        // Merge configuration
        $this->mergeConfigFrom(
            __DIR__ . '/Config/candle.php',
            'candle'
        );

        // Register the main class to use with the facade
        $this->app->singleton('candle', function () {
            return new Candle;
        });
    }

    /**
     * Register the package routes.
     *
     * @return void
     */
    protected function registerRoutes()
    {
        // API Routes
        Route::group($this->routeApiConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__ . '/Routes/api.php');
        });

        // Web Routes (including Dashboard)
        Route::group($this->routeWebConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__ . '/Routes/web.php');
        });
    }

    /**
     * Get API route group configuration array.
     *
     * @return array
     */
    private function routeApiConfiguration()
    {
        return [
            'namespace' => 'Mariojgt\Candle\Http\Controllers',
            'prefix' => config('candle.route_prefix', 'api/analytics'),
            'middleware' => ['api'],
            'as' => '', // This ensures route names are as defined in the route files
        ];
    }

    /**
     * Get web route group configuration array.
     *
     * @return array
     */
    private function routeWebConfiguration()
    {
        return [
            'namespace' => 'Mariojgt\Candle\Http\Controllers',
            'prefix' => config('candle.dashboard_route', 'analytics'),
            'middleware' => ['web'],
            'as' => '', // This ensures route names are as defined in the route files
        ];
    }
}
