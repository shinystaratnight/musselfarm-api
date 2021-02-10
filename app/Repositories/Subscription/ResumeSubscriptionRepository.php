<?php

namespace App\Repositories\Subscription;

class ResumeSubscriptionRepository implements ResumeSubscriptionRepositoryInterface
{
    public function resume()
    {
        $user = auth()->user();

        $currentPlan = $user->subscriptions()->first();

        if($currentPlan->onGracePeriod())
        {
            $user->subscription($currentPlan->name)->resume();

            return response()->json(['message' => 'Resuming cancelled subscription to ' . $currentPlan->name], 200);

        } else {

            return response()->json(['message' => 'There are no cancelled subscription'], 200);

        }
    }
}
