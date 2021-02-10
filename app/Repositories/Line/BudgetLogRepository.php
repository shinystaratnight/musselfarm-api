<?php

namespace App\Repositories\Line;

use App\Http\Resources\Budget\BudgetLogsResource;
use App\Models\BudgetLog;
use App\Models\Expenses;
use App\Models\LineBudget;

class BudgetLogRepository implements BudgetLogRepositoryInterface
{
    public function getLogs()
    {
        $logs = BudgetLog::whereHas('users', function($q) {
                        $q->where('user_id', '=', auth()->user()->id);
                    })->orderBy('created_at', 'DESC')->paginate(request()->input('page_size'), '*', 'page', request()->input('current_page'));

        return BudgetLogsResource::collection($logs);
    }

    public function removeLog($attr)
    {
        $log = BudgetLog::find($attr['budget_log_id']);

        $lastLog = BudgetLog::where('line_budget_id', $log->line_budget_id)->latest()->first();

        if($log->id == $lastLog['id']) {

            if($log->expenses_id == null) {

                $budget = LineBudget::find($log->line_budget_id);

                $budget->update([$log->row_name => $log->old]);

                $budget->save();

                $log->delete();

                return response()->json(['status' => 'Success'], 200);

            } else {

                $expenses = Expenses::find($log->expenses_id);

                $expenses->update([$log->row_name => $log->old]);

                $expenses->save();

                $log->delete();

                return response()->json(['status' => 'Success'], 200);
            }

        } else {

            $log->delete();

            return response()->json(['status' => 'Success'], 200);

        }
    }

    public function harvestCompleteLog($attr)
    {

    }
}
