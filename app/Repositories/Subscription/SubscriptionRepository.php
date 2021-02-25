<?php

namespace App\Repositories\Subscription;

use App\Models\Payment;
use App\Models\Plan;
use App\Notifications\ChargeSuccessNotification;
use App\Notifications\NewSubscriptionNotification;
use App\Services\InvoicesService;
use Stripe\StripeClient;
use Illuminate\Support\Facades\DB;

class SubscriptionRepository implements SubscriptionRepositoryInterface
{
    private $planName = 'Basic Plan';
    public function getSubscriptionStatus()
    {
        $user = auth()->user();

        if ($user->onTrial()) {
            $planData = [
                'expire_at' => date("F j, Y", strtotime($user->trial_ends_at)),
                'quantity' => $user->quantity,
                'coupon_used' => false,
            ];
            if ($user->coupon != 'none') {
                $planData['coupon_used'] = true;
            }
            return response()->json(['status' => 'trial',
                    'plan_data' => $planData,
                    'payment_method' => null,
                    'history' => null], 200);
        }
        if ($user->subscribed($this->planName)) // has active subscription (includes trial)
        {
            $activeSubscription = $user->subscription($this->planName)->asStripeSubscription();
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
            $cardDetails = null;
            if ($user->defaultPaymentMethod()) {
                $paymentMethod = $user->defaultPaymentMethod()->asStripePaymentMethod();
                $cardDetails = [
                    'brand' => $paymentMethod['card']['brand'],
                    'month' => $paymentMethod['card']['exp_month'],
                    'year' => $paymentMethod['card']['exp_year'],
                    'last4' => $paymentMethod['card']['last4'],
                ];
            }
            if ($user->subscription($this->planName)->onGracePeriod()) // grace period
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
            if ($user->subscription($this->planName))
            {
                if($user->subscription($this->planName)->cancelled()) { // actived once, but cancelled
                    return response()->json(['status' => 'cancelled',
                        'plan_data' => null,
                        'payment_method' => null,
                        'history' => null], 200);
                }
                if ($user->subscription($this->planName)->hasIncompletePayment()) {
                    return response()->json([
                        'status' => 'incomplete_payment',
                        'url' => route('cashier.payment', $user->subscription($this->planName)->latestPayment()->id)
                    ], 200);
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

        if ($user->subscription($this->planName)) //cancelled the plan
        {
            if($user->subscription($this->planName)->cancelled()) { // actived once, but cancelled
                DB::table('subscriptions')->where('user_id', $user->id)->delete();

                $stripe = new StripeClient(config('services.stripe.stripe_secret'));
                $stripe->customers->delete($user->stripe_id, []);
                $user->stripe_id = null;
                $user->card_brand = null;
                $user->card_last_four = null;
                $user->save();

                $user->createAsStripeCustomer();
                
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
                            ->quantity($attr['quantity'])
                            ->create($paymentMethod['id']);

                $user->quantity = $attr['quantity'];
                $user->save();

                return response()->json(['message' => 'Successfully subscribed', 'data' => $result], 200);
            } else {
                return response()->json(['message' => 'Current user already subscribed to plan: ' . $this->planName], 200);
            }
        }
        // Checking - is user active subscription
        else if (!isset($plan['name']) || $user->subscribed($plan['name']) === false)
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
                            ->quantity($attr['quantity'])
                            ->create($paymentMethod['id']);

                $user->quantity = $attr['quantity'];
                $user->save();

                return response()->json(['message' => 'Successfully subscribed'], 200);

            } catch (\Exception $e) {
                return response()->json(['message' => $e->getMessage()], 200);
            }
        } else {
            return response()->json(['message' => 'Current user already subscribed to plan: ' . $plan['name']], 200);
        }
    }

    public function updateTrial($attr)
    {
        $user = auth()->user();

        try {
            $user->quantity = $attr['quantity'];
            $user->coupon = $attr['coupon'];

            $trialDays = 0;
            if ($user->coupon == 'coupon1') $trialDays = 30;
            else if ($user->coupon == 'coupon2') $trialDays = 60;
            $user->trial_ends_at = $user->trial_ends_at->addDays($trialDays);
            
            $user->save();

            return response()->json(['status' => 1, 'message' => 'Trial period updated'], 200);

        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'message' => $e->getMessage()], 404);
        }
    }

    public function updateSubscription($attr)
    {
        $user = auth()->user();

        try {
            $subscription = $user->subscription($this->planName);

            $user->quantity = $attr['quantity'];
            $user->save();

            $subscription->noProrate()->updateQuantity($attr['quantity']);
            return response()->json(['status' => 1, 'message' => 'Subscription Updated'], 200);

        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'message' => $e->getMessage()], 404);
        }
    }

    public function updateCard($attr)
    {
        $user = auth()->user();

        try {
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
            ]);

            $user->updateDefaultPaymentMethod($paymentMethod['id']);

            return response()->json(['status' => 1, 'message' => 'Card updated successfully'], 200);

        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'message' => $e->getMessage()], 404);
        }
    }

    public function deleteCard()
    {
        $user = auth()->user();

        try {
            $user->deletePaymentMethods();

            return response()->json(['status' => 1, 'message' => 'Payment method successfully deleted'], 200);

        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'message' => $e->getMessage()], 404);
        }
    }
}
