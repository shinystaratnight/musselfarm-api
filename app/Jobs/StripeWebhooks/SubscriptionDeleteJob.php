<?php

namespace App\Jobs\StripeWebhooks;

use App\Models\CancelSubscription;
use App\Models\User;
use App\Notifications\CancelSubscriptionNotification;
use App\Notifications\ChargeSuccessNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\WebhookClient\Models\WebhookCall;

class SubscriptionDeleteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $webhookCall;

    public function __construct(WebhookCall $webhookCall)
    {
        $this->webhookCall = $webhookCall;
    }

    public function handle()
    {
        $cancel = $this->webhookCall->payload['data']['object'];

        $user = User::where('stripe_id', $cancel['customer'])->first();

        if($user)
        {
            try {
                $cancelSubscription = CancelSubscription::create([
                    'user_id' => $user->id,
                    'stripe_id' => $cancel['id'],
                    'cancel_at' => $cancel['cancel_at'],
                    'cancel_at_period_end' => $cancel['cancel_at_period_end'],
                    'canceled_at' => $cancel['canceled_at']
                ]);

                $user->notify(new CancelSubscriptionNotification($cancelSubscription));

            } catch (\Exception $e) {
                return response()->json(['message' => $e->getMessage()]);
            }
        }
    }
}
