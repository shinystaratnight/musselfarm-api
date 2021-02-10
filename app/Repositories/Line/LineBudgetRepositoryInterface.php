<?php

namespace App\Repositories\Line;

interface LineBudgetRepositoryInterface
{
    public function createBudget($attr);

    public function getUserFarmsBudget();

    public function newExpenses($attr);

    public function updateBudget($attr);
}
