<?php

namespace App\Providers;

use App\Firebase\Guard;
use App\Policies\PermissionTodo;
use App\Todo;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
        Todo::class => PermissionTodo::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        \Auth::viaRequest('firebase', function ($request) {
            return app(Guard::class)->user($request);
        });
        //
    }
}
