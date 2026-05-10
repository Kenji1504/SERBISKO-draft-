<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'sqlite') {
            $pdo = \Illuminate\Support\Facades\DB::connection()->getPdo();
            
            // Add JSON_UNQUOTE support for SQLite
            $pdo->sqliteCreateFunction('JSON_UNQUOTE', function ($value) {
                return $value; 
            });

            // SQLite's json_extract is often already present, but let's ensure it's registered
            // if for some reason the build doesn't have it (unlikely for modern PHP/SQLite)
            // But we don't need to overwrite it if it exists.
        }
    }
}
