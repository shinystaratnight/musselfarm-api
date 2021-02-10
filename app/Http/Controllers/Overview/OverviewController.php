<?php

namespace App\Http\Controllers\Overview;

use App\Http\Controllers\Controller;
use App\Http\Requests\Overview\OverviewFarmInfoRequest;
use App\Repositories\Overview\OverviewRepositoryInterface as Overview;
use Illuminate\Http\Request;

class OverviewController extends Controller
{
    private $overRepo;

    public function __construct(Overview $overview)
    {
        $this->overRepo = $overview;
    }

    public function nextSeeding()
    {
        return $this->overRepo->plannedSeedingDate();
    }

    public function farmReview()
    {
        return $this->overRepo->farmReview();
    }

    public function accountInfo()
    {
        return $this->overRepo->accountDetail();
    }

    public function nextHarvest()
    {
        return $this->overRepo->plannedHarvestDate();
    }

    public function farmBudgetedInfo(OverviewFarmInfoRequest $request)
    {
        $attr = $request->validated();

        return $this->overRepo->farmsInfo($attr);
    }

    public function getChart()
    {
        return $this->overRepo->chartData();
    }
}
