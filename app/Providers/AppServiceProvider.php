<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Foundation\AliasLoader;

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
        Paginator::useBootstrapFive();
        AliasLoader::getInstance()->alias('DNS1D', \Milon\Barcode\Facades\DNS1DFacade::class);
        AliasLoader::getInstance()->alias('DNS2D', \Milon\Barcode\Facades\DNS2DFacade::class);
    }
}
