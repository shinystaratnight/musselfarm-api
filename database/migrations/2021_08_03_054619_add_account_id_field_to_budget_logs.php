<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\BudgetLog;

class AddAccountIdFieldToBudgetLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('budget_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('account_id')->nullable();
        });

        $logs = BudgetLog::all();
        foreach ($logs as $log) {
            $log->account_id = $log->users->account_id;
            $log->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('budget_logs', function (Blueprint $table) {
            $table->dropColumn('account_id');
        });
    }
}
