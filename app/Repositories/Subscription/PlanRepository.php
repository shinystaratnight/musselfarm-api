<?php

namespace App\Repositories\Subscription;

use App\Models\Plan;

class PlanRepository implements PlanRepositoryInterface
{
    public function getAllPlans()
    {
        return Plan::all();
    }

    public function getPlanById($id)
    {
        return Plan::find($id);
    }
}
