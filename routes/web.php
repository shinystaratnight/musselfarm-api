<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\Xero\XeroController;
use Illuminate\Support\Facades\Redirect;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Redirect::to(config('services.api.front_end_url'));
});

// Stripe webhook routes
// Route::stripeWebhooks('stripe-webhook'); 
Route::post('/stripe/webhook', [WebhookController::class, 'handleWebhook']);

Route::get('xero/connect', [XeroController::class, 'redirectUserToXero']);
Route::get('xero/callback/{token}', [XeroController::class, 'handleAuthCallbackFromXero']);
