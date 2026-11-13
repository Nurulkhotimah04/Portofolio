<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Project menggunakan Bootstrap 4 (SB Admin 2), sehingga seluruh
        // pagination Laravel ditampilkan dengan markup Bootstrap 4.
        Paginator::useBootstrapFour();
    }
}
