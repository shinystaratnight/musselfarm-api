<?php

namespace App\Http\Controllers\Budget;

use App\Http\Requests\Budget\ExpensesRequest;
use App\Http\Requests\Budget\MaintenanceCostRequest;
use App\Http\Requests\Budget\SeedingCostRequest;
use App\Http\Requests\Budget\UpdateBudgetPartRequest;
use App\Http\Requests\Budget\UpdateExpensesPartRequest;
use App\Http\Requests\Budget\YearlyBudgetRequest;
use App\Models\LineBudget;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Budget\CreateBudgetForLineRequest;
use App\Repositories\Line\LineBudgetRepositoryInterface as Budget;


class LineBudgetController extends Controller
{
    private $budgetRepo;

    public function __construct(Budget $budget)
    {
        $this->budgetRepo = $budget;
    }

    public function index()
    {
        return $this->budgetRepo->getUserFarmsBudget();
    }

    public function store(CreateBudgetForLineRequest $request)
    {
        $attr = $request->validated();

        return $this->budgetRepo->createBudget($attr);
    }

    public function show(LineBudget $lineBudget)
    {
        //
    }

    public function update(Request $request, LineBudget $lineBudget)
    {
        //
    }

    public function destroy(LineBudget $lineBudget)
    {
        //
    }

    public function addExpenses(ExpensesRequest $request)
    {
        $attr = $request->validated();

        return $this->budgetRepo->newExpenses($attr);
    }

    public function getFarmBudget(YearlyBudgetRequest $request)
    {
        $attr = $request->validated();

        return $this->budgetRepo->getBudgetByFarmOrLine($attr);
    }

    public function updateBudget(UpdateBudgetPartRequest $request)
    {
        $attr = $request->validated();

        return $this->budgetRepo->updateBudget($attr);
    }

    public function updateExpenses(UpdateExpensesPartRequest $request)
    {
        $attr = $request->validated();

        return $this->budgetRepo->updateExpenses($attr);
    }
}
