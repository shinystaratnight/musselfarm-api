<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Subscription\SubscriptionController;
use App\Http\Controllers\Subscription\PlanController;
use \App\Http\Controllers\Subscription\InvoiceController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Farm\LineController;
use App\Http\Controllers\User\UserProfileController;
use \App\Http\Controllers\User\ChangeUserPasswordController;
use \App\Http\Controllers\User\ChangeUserEmailController;
use \App\Http\Controllers\Farm\FarmController;
use \App\Http\Controllers\Farm\SeedController;
use \App\Http\Controllers\Farm\AssessmentController;
use \App\Http\Controllers\Farm\HarvestGroupController;
use \App\Http\Controllers\Budget\LineBudgetController;
use \App\Http\Controllers\Budget\BudgetLogController;
use App\Http\Controllers\Overview\OverviewController;
use App\Http\Controllers\Xero\XeroController;
use App\Http\Controllers\WebhookController;
use \App\Http\Controllers\UtilController;
use \App\Http\Controllers\SeasonController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Auth routes
Route::group(['prefix' => 'auth'], function()
{
    //Authentication route
    Route::post('login', [AuthController::class, 'login']);
    Route::post('signup', [AuthController::class, 'signupUser']);
    Route::post('resend-activation-email', [AuthController::class, 'resendEmail']);

    // Invitation register
    Route::post('signup-by-invitation', [AuthController::class, 'invitedPersonRegistration']);
    Route::get('invited-signup', [AuthController::class, 'invitedSignup'])->name('invited-register');
    Route::post('invite', [UserController::class, 'processInvites']);
    Route::get('invitation-redirect', [AuthController::class, 'invitationRedirect'])->name('inviting');

    Route::get('signup/activate/{token}', [AuthController::class, 'signupActivate']);
    Route::get('token-expired', [AuthController::class, 'tokenExpired'])->name('auth-token-expired');
});

Route::group(['middleware' => 'auth:api'], function ()
{
    // Xero get-data exmaple
    // Route::post('get-data', [XeroController::class, 'getSomeData']);

    // Overview widgets route
    Route::group(['prefix' => 'overview'], function() {
        Route::post('next-seeding', [OverviewController::class, 'nextSeeding']);
        Route::post('farm-review', [OverviewController::class, 'farmReview']);
        Route::post('account-info', [OverviewController::class, 'accountInfo']);
        Route::post('next-harvest', [OverviewController::class, 'nextHarvest']);
        Route::post('farm-budget-info', [OverviewController::class, 'farmBudgetedInfo']);
        Route::post('chart-info', [OverviewController::class, 'getChart']);
    });

    Route::group(['prefix' => 'user'], function() {

        // Invited users actions route
        Route::post('deactivate', [UserController::class, 'deactivate']);
        Route::post('activate-deactivated-user', [UserController::class, 'activateDeactivatedUser']);
        Route::post('invite', [UserController::class, 'processInvites']);

        // Permissions route
        Route::post('role-permissions', [UserController::class, 'getPermissions']);
        Route::post('role-permissions-update', [UserController::class, 'permissionsUpdate']);

        // Users action route
        Route::resource('users', UserController::class);
        Route::post('destroy-user', [UserController::class, 'destroyPendingUser']);
        Route::resource('profiles', UserProfileController::class);

        // Avatar upload route
        Route::post('profiles/upload-avatar', [UserProfileController::class, 'uploadAvatar']);

        // Actions with user email and password
        Route::get('get-user-emails', [UserProfileController::class, 'getEmailAddress']);
        Route::post('password', [ChangeUserPasswordController::class, 'changePassword']);
        Route::post('email', [ChangeUserEmailController::class, 'changeEmail']);
    });

    // User logout route
    Route::get('logout', [AuthController::class, 'logout']);

    // Subscription routes
    Route::group(['prefix' => 'subscription'], function() {
        Route::get('index', [SubscriptionController::class, 'index']);
        Route::get('get-subscription-status', [SubscriptionController::class, 'getSubscriptionStatus']);
        Route::post('subscription', [SubscriptionController::class, 'getSubscription']);
        Route::post('cancel', [SubscriptionController::class, 'cancelSubscription']);
        Route::post('resume', [SubscriptionController::class, 'getResume']);
        Route::post('update-trial', [SubscriptionController::class, 'updateTrial']);
        Route::post('update-subscription', [SubscriptionController::class, 'updateSubscription']);
        Route::post('update-card', [SubscriptionController::class, 'updateCard']);
        Route::post('delete-card', [SubscriptionController::class, 'deleteCard']);
        // Invoices download route
        Route::get('invoices/download/{paymentId}', [InvoiceController::class, 'downloadInvoice']);

        // Subscription plans resource routes
        Route::resource('plans', PlanController::class);
    });

    // Farm routes

    Route::group(['prefix' => 'farm'], function() {
        Route::resource('farms', FarmController::class);
        Route::get('farms-all', [FarmController::class, 'allFarms']);

        // Add new farm expenses route
        // Route::post('budgets/add-farm-expenses', [LineBudgetController::class, 'addFarmExpenses']);

        // Line routes
        Route::group(['prefix' => 'line'], function() {
            Route::resource('budgets', LineBudgetController::class);

            // Add new maintenance expenses route
            Route::post('budgets/add-expenses', [LineBudgetController::class, 'addExpenses']);

            // Update budget part of budget route
            Route::post('budgets/update-budget-part', [LineBudgetController::class,'updateBudget'])->middleware('budget_log');

            // Update expenses part of budget route
            Route::post('budgets/update-expenses-part', [LineBudgetController::class,'updateExpenses'])->middleware('budget_log');;

            // Add new seeding expenses route
            Route::post('budgets/add-expenses-seedings', [LineBudgetController::class, 'addSeedingCost']);

            // Get budget by farm, line ids and years route
            Route::post('budgets/farm-budget', [LineBudgetController::class, 'getFarmBudget']);

            // Get all logs routes - pagination
            Route::get('budget-logs', [BudgetLogController::class, 'logs']);

            // Retrieve logs route
            Route::post('retrieve-log', [BudgetLogController::class, 'remove']);

            // Line resource route
            Route::resource('lines', LineController::class);

            // Harvest resource route
            Route::resource('harvests', HarvestGroupController::class);

            // Harvest complete route
            Route::post('harvest-complete', [HarvestGroupController::class, 'harvestComplete'])->middleware('harvest_complete_log');

            // Assessment routes
            Route::group(['prefix' => 'assessment'], function() {
               Route::resource('assessments', AssessmentController::class);
            });
        });

        // Seed routes
        Route::group(['prefix' => 'seed'], function() {
            Route::resource('seeds', SeedController::class);
        });
    });

    //Util routes
    Route::group(['prefix' => 'util'], function() {
        Route::resource('utils', UtilController::class);
    });

    //Season routes
    Route::group(['prefix' => 'season'], function() {
        Route::resource('seasons', SeasonController::class);
    });
});

//Stripe webhook routes
Route::post('/stripe/webhook', [WebhookController::class, 'handleWebhook']);

Route::post('refresh', [AuthController::class, 'refresh']);
Route::get('apply-email', [ChangeUserEmailController::class, 'apply'])->name('apply');

// Reset password routes
Route::group(['prefix' => 'password'], function ()
{
    Route::post('create', [PasswordResetController::class, 'create']);
    Route::get('find/{token}', [PasswordResetController::class, 'find']);
    Route::post('reset', [PasswordResetController::class, 'reset']);
});

Route::post('get-success', [UserProfileController::class, 'getSuccess']);