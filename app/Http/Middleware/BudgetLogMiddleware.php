<?php

namespace App\Http\Middleware;

use App\Models\BudgetLog;
use App\Models\Expenses;
use App\Models\LineBudget;
use Closure;
use Illuminate\Http\Request;

class BudgetLogMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $old = 0.00;

        $human = null;

        if($request->input('expenses_id')) {
            $old = $this->getOldValue($request->input('expenses_id'), $request->input('data_row'));

            $human = $this->convertToHumanExpensText($request->input('expenses_name'),
                                                      $request->input('type'));
        } else {
            $old = $this->getOldValue($request->input('budget_id'), $request->input('data_row'));

            $human = $this->convertToHumanBudgetText($request->input('data_row'),
                                                      $request->input('type'));
        }



        BudgetLog::create([
            'user_id' => auth()->user()->id,
            'farm_id' => $request->input('farm_id'),
            'line_id' => $request->input('line_id'),
            'line_budget_id' => $request->input('budget_id'),
            'expenses_id' => $request->input('expenses_id'),
            'row_name' =>  $request->input('data_row'),
            'human_name' => $human,
            'old' => $old,
            'new' => $request->input('value'),
            'comment' => $request->input('comment'),
        ]);

        return $next($request);
    }

    public function getOldValue($id, $data_row)
    {
        $budget_arr = ['budgeted_harvest_income',
                       'budgeted_harvest_income_actual',
                       'planned_harvest_tones',
                       'planned_harvest_tones_actual',
                       'length_budget',
                       'length_actual',
                       'length'];

        $expenses_arr = ['price_budget', 'price_actual'];

        if(in_array($data_row, $budget_arr)) {

            $old = LineBudget::find($id);

            return $old->$data_row;
        }

        if(in_array($data_row, $expenses_arr)) {

            $old = Expenses::find($id);

            return $old->$data_row;
        }
    }

    public function convertToHumanBudgetText($row = null, $type = null)
    {
        $typeConstruct = null;

        $humanName = null;

        if($type == 'a') {

            $typeConstruct = '(Actual)';

        } elseif($type == 'b') {

            $typeConstruct = '(Budget)';

        }

            switch ($row) {
                case 'budgeted_harvest_income':
                    $humanName = 'Harvest Income ' . $typeConstruct;
                    return $humanName;
                    break;

                case 'budgeted_harvest_income_actual':
                    $humanName = 'Harvest income ' . $typeConstruct;
                    return $humanName;
                    break;

                case 'planned_harvest_tones':
                    $humanName = 'Harvest tonnes ' . $typeConstruct;
                    return $humanName;
                    break;

                case 'planned_harvest_tones_actual':
                    $humanName = 'Harvest tonnes ' . $typeConstruct;
                    return $humanName;
                    break;

                case 'length_budget':
                    $humanName = 'Lenght ' . $typeConstruct;
                    return $humanName;
                    break;

                case 'length_actual':
                    $humanName = 'Lenght ' . $typeConstruct;
                    return $humanName;
                    break;

                case 'length':
                    $humanName = 'Lenght ' . $typeConstruct;
                    return $humanName;
                    break;
            }
    }

    public function convertToHumanExpensText($expens = null, $type = null)
    {
        $typeConstruct = null;

        $humanName = null;

        if($type == 'a') {

            $typeConstruct = '(Actual)';

        } elseif($type == 'b') {

            $typeConstruct = '(Budget)';

        }

        if($expens != null) {

            return $expens . ' ' . $typeConstruct;

        }
    }
}
