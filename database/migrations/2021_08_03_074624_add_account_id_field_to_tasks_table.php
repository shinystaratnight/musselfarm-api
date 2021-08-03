<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\Task;

class AddAccountIdFieldToTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->unsignedBigInteger('account_id')->nullable()->default(null);
            $table->foreign('account_id')->references('id')->on('accounts');

            $table->renameColumn('charger_id', 'assigned_to')->nullable()->default(null);

            $table->unsignedBigInteger('creator_id')->nullable()->default(null)->change();
            $table->foreign('creator_id')->references('id')->on('users');
        });

        $tasks = Task::all();
        foreach ($tasks as $task) {
            $task->account_id = $task->creator->account_id;
            $task->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
            $table->dropIndex('tasks_account_id_foreign');
            $table->dropColumn('account_id');

            $table->dropForeign(['creator_id']);
            $table->dropIndex('tasks_creator_id_foreign');

            $table->renameColumn('assigned_to', 'charger_id')->nullable()->default(null);
        });
    }
}
