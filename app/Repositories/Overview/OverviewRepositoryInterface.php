<?php

namespace App\Repositories\Overview;

interface OverviewRepositoryInterface
{
    public function plannedSeedingDate();

    public function farmReview();

    public function accountDetail();

    public function plannedHarvestDate();

    public function farmsInfo($attr);

    public function chartData();
}
