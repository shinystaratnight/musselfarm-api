<?php

namespace App\Repositories\Subscription;

class CancelSubscriptionRepository implements CancelSubscriptionRepositoryInterface
{
    public function cancel()
    {
        $user = auth()->user();

        $currentPlan = $user->subscriptions()->first();

        if((!isset($plan['name']) || $user->subscribed($currentPlan->name) === false) && ($currentPlan->ends_at == null))
        {
            $user->subscription($currentPlan->name)->cancel();

            return response()->json(['status' => '1', 'message' => 'Subscription to ' . $currentPlan->name . ' canceled'], 200);

        } else {

            return response()->json(['status' => '0', 'message' => 'No current subscription or all subscription were cancel'], 200);

        }
    }
}

