<?php

namespace App\Http\Controllers\Subscription;

use App\Http\Controllers\Controller;
use App\Http\Resources\Plan\PlanResource;
use App\Repositories\Subscription\PlanRepositoryInterface as PlanRepo;
use App\Models\Plan;

class PlanController extends Controller
{
    private $planRepo;

    public function __construct(PlanRepo $plan)
    {
        $this->planRepo = $plan;
    }

    public function index()
    {
        $plans = $this->planRepo->getAllPlans();

        return PlanResource::collection($plans);
    }

    public function show(Plan $plan)
    {
        return new PlanResource($plan);
    }
}
