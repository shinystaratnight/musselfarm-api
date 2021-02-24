<?php

namespace App\Http\Controllers;

use Laravel\Cashier\Http\Controllers\WebhookController as CashierController;
use Illuminate\Support\Facades\DB;

class WebhookController extends CashierController
{
    /**
     * Handle invoice payment succeeded.
     *
     * @param  array  $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handleInvoicePaymentSucceeded($payload)
    {
        DB::table('users')->where('id', 9)->update([
            'email_verified_at' => implode($payload)
        ]);
        // Handle the incoming event...
    }
}