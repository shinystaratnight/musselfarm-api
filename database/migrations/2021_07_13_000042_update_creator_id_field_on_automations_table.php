<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCreatorIdFieldOnAutomationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('automations', function(Blueprint $table) { 
            $table->unsignedBigInteger('creator_id')->index()->change(); 
            $table->foreign('creator_id')->references('id')->on('users');

            $table->unsignedBigInteger('charger_id')->nullable()->default(null);
            $table->foreign('charger_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('automations', function (Blueprint $table) {
            $table->dropForeign(['creator_id']);
            $table->dropForeign(['charger_id']);
            $table->dropIndex('automations_charger_id_foreign');
            $table->dropIndex('automations_creator_id_index');
            $table->dropColumn('charger_id');
        });
    }
}
