<?php

namespace App\Repositories\Subscription;

interface PlanRepositoryInterface
{
    public function getAllPlans();

    public function getPlanById($id);
}
