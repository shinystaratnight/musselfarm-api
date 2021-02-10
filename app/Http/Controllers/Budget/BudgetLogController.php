<?php

namespace App\Http\Controllers\Budget;

use App\Http\Requests\Budget\BudgetLogRequest;
use App\Repositories\Line\BudgetLogRepositoryInterface as LogBudget;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BudgetLogController extends Controller
{
    private $logRepo;

    public function __construct(LogBudget $budgetLog)
    {
        $this->logRepo = $budgetLog;
    }

    public function logs()
    {
        return $this->logRepo->getLogs();
    }

    public function remove(BudgetLogRequest $request)
    {
        $attr = $request->validated();

        return $this->logRepo->removeLog($attr);
    }

    public function lengthLog($request)
    {
        $attr = $request->validated();

        return $this->logRepo->length($attr);
    }
}
