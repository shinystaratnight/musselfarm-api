<?php

namespace App\Http\Controllers\Overview;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Overview\OverviewFarmInfoRequest;
use App\Repositories\Overview\OverviewRepositoryInterface as Overview;

class OverviewController extends Controller
{
    private $overRepo;

    public function __construct(Overview $overview)
    {
        $this->overRepo = $overview;
    }

    public function nextSeeding(Request $request)
    {
        return $this->overRepo->plannedSeedingDate($request->input('account_id'));
    }

    public function farmReview(Request $request)
    {
        return $this->overRepo->farmReview($request->input('account_id'));
    }

    public function accountInfo(Request $request)
    {
        return $this->overRepo->accountDetail($request->input('account_id'));
    }

    public function nextHarvest(Request $request)
    {
        return $this->overRepo->plannedHarvestDate($request->input('account_id'));
    }

    public function farmBudgetedInfo(OverviewFarmInfoRequest $request)
    {
        $attr = $request->validated();

        return $this->overRepo->farmsInfo($attr);
    }

    public function getChart(Request $request)
    {
        return $this->overRepo->chartData($request->input('account_id'));
    }
}
