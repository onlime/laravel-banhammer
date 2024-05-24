<?php

namespace Mchev\Banhammer;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Mchev\Banhammer\Commands\ClearBans;
use Mchev\Banhammer\Commands\DeleteExpired;
use Mchev\Banhammer\Middleware\AuthBanned;
use Mchev\Banhammer\Middleware\BlockByCountry;
use Mchev\Banhammer\Middleware\IPBanned;
use Mchev\Banhammer\Middleware\LogoutBanned;
use Mchev\Banhammer\Observers\BanObserver;

class BanhammerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        config('ban.model')::observe(BanObserver::class);

        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('auth.banned', AuthBanned::class);
        $router->aliasMiddleware('ip.banned', IPBanned::class);
        $router->aliasMiddleware('logout.banned', LogoutBanned::class);

        if (config('ban.block_by_country')) {
            $router->pushMiddlewareToGroup('web', BlockByCountry::class);
        }

        if ($this->app->runningInConsole()) {
            // Publishing the config.
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('ban.php'),
            ], 'banhammer-config');

            // Publishing migrations
            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'banhammer-migrations');

            // Registering package commands.
            $this->commands([
                ClearBans::class,
                DeleteExpired::class,
            ]);
        }

        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('banhammer:unban')->everyMinute();
        });

    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'ban');
    }
}
