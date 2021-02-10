<?php

namespace App\Jobs\StripeWebhooks;

use App\Models\Payment;
use App\Models\User;
use App\Notifications\ChargeFailedNotification;
use App\Notifications\ChargeSuccessNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\WebhookClient\Models\WebhookCall;

class ChargeFailedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $webhookCall;

    public function __construct(WebhookCall $webhookCall)
    {
        $this->webhookCall = $webhookCall;
    }

    public function handle()
    {
        $charge = $this->webhookCall->payload['data']['object'];

        $user = User::where('stripe_id', $charge['customer'])->first();

        if($user)
        {
            try {
                $payment = Payment::create([
                    'user_id' => $user->id,
                    'stripe_id' => $charge['id'],
                    'subtotal' => $charge['amount'],
                    'total' => $charge['amount']
                ]);

                $user->notify(new ChargeFailedNotification($payment));

            } catch (\Exception $e) {
                return response()->json(['message' => $e->getMessage()]);
            }
        }
    }
}
