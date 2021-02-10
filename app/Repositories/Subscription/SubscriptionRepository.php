<?php

namespace App\Repositories\Subscription;

use App\Models\Payment;
use App\Models\Plan;
use App\Notifications\ChargeSuccessNotification;
use App\Notifications\NewSubscriptionNotification;
use App\Services\InvoicesService;
use Stripe\StripeClient;

class SubscriptionRepository implements SubscriptionRepositoryInterface
{
    public function subscription($attr)
    {
        $currentPlan = Plan::find($attr['plan_id']);

        $user = auth()->user();

        $plan = $user->subscriptions()->first();

        // Checking - is user has a Stripe customer id
        if (!$user->stripe_id)
        {
            $user->createAsStripeCustomer();
        }

        // Checking - is user active subscription
        if (!isset($plan['name']) || $user->subscribed($plan['name']) === false)
        {
            try {
                // Get Stripe client object
                $stripe = new StripeClient(config('services.stripe.stripe_secret'));

                // Create Stripe payment method id (with Stripe customer id)
                $paymentMethod = $stripe->paymentMethods->create([
                    'type' => 'card',
                    'card' => [
                        'number' => $attr['card_number'],
                        'exp_month' => $attr['expiration_month'],
                        'exp_year' => $attr['expiration_year'],
                        'cvc' => $attr['cvc'],
                    ]
                ])->attach(['customer' => $user->stripe_id]);

                // Create subscription to chosen plan with created payment method
                $result = $user->newSubscription($currentPlan->name, $currentPlan->stripe_plan_id)
                               ->trialDays(config('services.stripe.stripe_trial'))
                               ->create($paymentMethod['id']);

                return response()->json(['message' => 'Successfully subscribed',
                                         'trial_end' => $user->subscriptions[0]['trial_ends_at']->toDateString()], 200);

            } catch (\Exception $e) {
                return response()->json(['message' => $e->getMessage()], 404);
            }
        } else {
            return response()->json(['message' => 'Current user already subscribed to plan: ' . $plan['name']], 200);
        }
    }
}
