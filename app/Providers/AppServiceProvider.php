<?php

namespace App\Providers;

use App\Models\Business;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Cashier::useCustomerModel(Business::class);
        Cashier::ignoreMigrations();
    }
}
