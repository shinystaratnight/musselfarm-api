<?php

namespace App\Providers;

use App\Models\Farm;
use App\Models\Line;
use App\Policies\FarmPolicy;
use App\Policies\LinePolicy;
use App\Policies\PermissionPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Farm::class => FarmPolicy::class,
        Line::class => LinePolicy::class
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Passport::routes();

        Passport::tokensExpireIn(now()->addMinutes(
            config('services.passport.token_expire_in'))); // access token

        Passport::refreshTokensExpireIn(now()->addDays(
            config('services.passport.refresh_token_expire_in'))); // refresh token
    }
}
