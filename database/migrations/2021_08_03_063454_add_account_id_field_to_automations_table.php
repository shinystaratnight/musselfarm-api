<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Automation;

class AddAccountIdFieldToAutomationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('automations', function (Blueprint $table) {
            $table->unsignedBigInteger('account_id')->nullable();

            $table->dropForeign(['charger_id']);
            $table->dropIndex('automations_charger_id_foreign');

            $table->renameColumn('charger_id', 'assigned_to')->nullable()->default(null);
            $table->foreign('assigned_to')->references('id')->on('users');
        });

        $automations = Automation::all();
        foreach ($automations as $automation) {
            $automation->account_id = $automation->creator->account_id;
            $automation->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('automations', function (Blueprint $table) {
            $table->dropColumn('account_id');

            $table->dropForeign(['assigned_to']);
            $table->dropIndex('automations_assigned_to_foreign');

            $table->renameColumn('assigned_to', 'charger_id')->nullable()->default(null);
            $table->foreign('charger_id')->references('id')->on('users');
        });
    }
}
