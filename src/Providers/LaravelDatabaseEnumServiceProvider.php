<?php

namespace dobron\LaravelDatabaseEnum\Providers;

use Illuminate\Support\ServiceProvider;
use dobron\LaravelDatabaseEnum\Console\Commands\EnumMakeCommand;

/**
 * The Laravel Database Enum service provider.
 *
 */
class LaravelDatabaseEnumServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                EnumMakeCommand::class,
            ]);
        }
    }
}
