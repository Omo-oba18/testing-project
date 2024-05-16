<?php

namespace App\Providers;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\JWT\IdTokenVerifier;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->singleton(IdTokenVerifier::class, function ($app) {
            return IdTokenVerifier::createWithProjectId(config('services.firebase.project_id'));
        });

        JsonResource::withoutWrapping();
    }
}
