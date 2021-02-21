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
    public function getSubscriptionStatus()
    {
        $user = auth()->user();

        if ($user->subscribed('Basic Plan')) // has active subscription (includes trial)
        {
            $activeSubscription = $user->subscription('Basic Plan')->asStripeSubscription();
            $planData = [
                'expire_at' => date("F j, Y", $activeSubscription->current_period_end),
                'quantity' => $activeSubscription->quantity,
            ];
            $invoices = $user->invoices();
            $history = [];
            foreach ($invoices as $invoice) {
                $invoiceData = [
                    'total' => $invoice->total(),
                    'date' => $invoice->date()->toFormattedDateString(),
                    'id' => $invoice->id,
                ];
                $history[] = $invoiceData;
            }
            $paymentMethod = $user->defaultPaymentMethod()->asStripePaymentMethod();
            $cardDetails = [
                'brand' => $paymentMethod['card']['brand'],
                'month' => $paymentMethod['card']['exp_month'],
                'year' => $paymentMethod['card']['exp_year'],
                'last4' => $paymentMethod['card']['last4'],
            ];
            if ($user->subscription('Basic Plan')->onTrial()) // trial period
            {
                return response()->json(['status' => 'trial',
                    'plan_data' => $planData,
                    'payment_method' => $cardDetails,
                    'history' => $history], 200);
            }
            else if ($user->subscription('Basic Plan')->onGracePeriod()) // grace period
            {
                return response()->json(['status' => 'grace',
                    'plan_data' => $planData,
                    'payment_method' => $cardDetails,
                    'history' => $history], 200);
            }
            else // active subscription
            {
                return response()->json(['status' => 'active',
                    'plan_data' => $planData,
                    'payment_method' => $cardDetails,
                    'history' => $history], 200);
            }
        }
        else // not subscribed
        {
            if ($user->subscription('Basic Plan'))
            { 
                if($user->subscription('Basic Plan')->cancelled()) { // actived once, but cancelled
                    $history = $user->subscription('Basic Plan')->pastDue();
                    $paymentMethod = $user->defaultPaymentMethod()->asStripePaymentMethod();
                    $cardDetails = [
                        'brand' => $paymentMethod['card']['brand'],
                        'month' => $paymentMethod['card']['exp_month'],
                        'year' => $paymentMethod['card']['exp_year'],
                        'last4' => $paymentMethod['card']['last4'],
                    ];
                    return response()->json(['status' => 'cancelled',
                        'plan_data' => null,
                        'payment_method' => $cardDetails,
                        'history' => $history], 200);
                }
            }
            else // not subscribed yet
            {
                return response()->json(['status' => 'not_subscribe',
                    'payment_method' => null,
                    'plan_data' => null,
                    'history' => null], 200);
            }
        }
    }

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
                               // ->trialDays($attr['trial'] ? config('services.stripe.stripe_trial') : 0)
                               ->quantity($attr['quantity'])
                               ->create($paymentMethod['id']);

                if ($attr['trial']) {
                    return response()->json(['message' => 'Successfully subscribed',
                                         'trial_end' => $user->subscriptions[0]['trial_ends_at']->toDateString()], 200);
                } else {
                    return response()->json(['message' => 'Successfully subscribed'], 200);
                }

            } catch (\Exception $e) {
                return response()->json(['message' => $e->getMessage()], 404);
            }
        } else {
            return response()->json(['message' => 'Current user already subscribed to plan: ' . $plan['name']], 200);
        }
    }
}
