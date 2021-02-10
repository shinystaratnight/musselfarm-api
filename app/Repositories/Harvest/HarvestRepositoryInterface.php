<?php

namespace App\Repositories\Harvest;

interface HarvestRepositoryInterface
{
    public function startHarvest($attr);

    public function updateHarvest($attr);
}
