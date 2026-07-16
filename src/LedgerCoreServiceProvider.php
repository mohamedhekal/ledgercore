<?php

declare(strict_types=1);

namespace Hekal\LedgerCore;

use Illuminate\Support\ServiceProvider;

final class LedgerCoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/ledgercore.php', 'ledgercore');
        $this->app->singleton(LedgerManager::class);
        $this->app->alias(LedgerManager::class, 'ledger');
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/ledgercore.php' => config_path('ledgercore.php'),
            ], 'ledgercore-config');
        }
    }
}
