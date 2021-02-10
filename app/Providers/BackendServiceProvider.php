<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class BackendServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            'App\Repositories\Auth\AuthRepositoryInterface',
            'App\Repositories\Auth\AuthRepository'
        );

        $this->app->bind(
            'App\Repositories\PasswordReset\PasswordResetRepositoryInterface',
            'App\Repositories\PasswordReset\PasswordResetRepository',
        );

        $this->app->bind(
            'App\Repositories\Subscription\PlanRepositoryInterface',
            'App\Repositories\Subscription\PlanRepository',
        );

        $this->app->bind(
            'App\Repositories\Subscription\SubscriptionRepositoryInterface',
            'App\Repositories\Subscription\SubscriptionRepository',
        );

        $this->app->bind(
            'App\Repositories\Subscription\CancelSubscriptionRepositoryInterface',
            'App\Repositories\Subscription\CancelSubscriptionRepository'
        );

        $this->app->bind(
            'App\Repositories\Subscription\ResumeSubscriptionRepositoryInterface',
            'App\Repositories\Subscription\ResumeSubscriptionRepository'
        );

        $this->app->bind(
            'App\Repositories\UserProfile\UserProfileRepositoryInterface',
            'App\Repositories\UserProfile\UserProfileRepository'
        );

        $this->app->bind(
            'App\Repositories\User\UserRepositoryInterface',
            'App\Repositories\User\UserRepository'
        );

        $this->app->bind(
            'App\Repositories\User\ChangePasswordRepositoryInterface',
            'App\Repositories\User\ChangePasswordRepository'
        );

        $this->app->bind(
            'App\Repositories\User\ChangeEmailRepositoryInterface',
            'App\Repositories\User\ChangeEmailRepository'
        );

        $this->app->bind(
            'App\Repositories\Subscription\InvoiceRepositoryInterface',
            'App\Repositories\Subscription\InvoiceRepository'
        );

        $this->app->bind(
            'App\Repositories\Auth\InvitationRepositoryInterface',
            'App\Repositories\Auth\InvitationRepository'
        );

        $this->app->bind(
            'App\Repositories\Farm\FarmRepositoryInterface',
            'App\Repositories\Farm\FarmRepository',
        );

        $this->app->bind(
            'App\Repositories\Line\LineRepositoryInterface',
            'App\Repositories\Line\LineRepository'
        );

        $this->app->bind(
            'App\Repositories\Line\AssessmentRepositoryInterface',
            'App\Repositories\Line\AssessmentRepository'
        );

        $this->app->bind(
            'App\Repositories\Line\LineBudgetRepositoryInterface',
            'App\Repositories\Line\LineBudgetRepository',
        );

        $this->app->bind(
            'App\Repositories\Harvest\HarvestRepositoryInterface',
            'App\Repositories\Harvest\HarvestRepository'
        );

        $this->app->bind(
            'App\Repositories\Line\BudgetLogRepositoryInterface',
            'App\Repositories\Line\BudgetLogRepository'
        );

        $this->app->bind(
            'App\Repositories\Overview\OverviewRepositoryInterface',
            'App\Repositories\Overview\OverviewRepository'
        );
    }
}
