<?php

namespace Upsoftware\Auth\Providers;

use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        include __DIR__.'/../Http/helpers.php';
    }

    public function register(): void
    {
        $this->registerCommands();
    }

    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Upsoftware\Auth\Console\Commands\UpSoftwareMakeUserRole::class
            ]);
        }
    }
}
