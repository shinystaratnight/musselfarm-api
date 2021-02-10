<?php

namespace App\Repositories\Line;

interface BudgetLogRepositoryInterface
{
    public function getLogs();

    public function removeLog($attr);

    public function harvestCompleteLog($attr);
}
