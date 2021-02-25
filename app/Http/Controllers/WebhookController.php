<?php

namespace App\Http\Controllers;

use Laravel\Cashier\Http\Controllers\WebhookController as CashierController;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class WebhookController extends CashierController
{
    /**
     * Handle invoice payment succeeded.
     *
     * @param  array  $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function handleInvoicePaymentSucceeded($payload)  
    {
        $user = $this->getUserByStripeId($payload['data']['object']['customer']);
        DB::table('subscriptions')->where('id', $user->id)->update([
            'stripe_status' => 'active'
        ]);
        DB::table('users')->where('id', $user->id)->update([
            'stripe_id' => $payload['data']['object']['customer']
        ]);
        return response()->json([
            'message' => 'Successfully paid',
            'customer' => $payload['data']['object']['customer']
        ], 200);
        // Handle the incoming event...
    }
}