<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class OrderFieldsOnAutomationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE automations MODIFY COLUMN unit varchar(255) AFTER time");
        DB::statement("ALTER TABLE automations MODIFY COLUMN assigned_to BIGINT UNSIGNED AFTER creator_id");
        DB::statement("ALTER TABLE automations MODIFY COLUMN account_id BIGINT UNSIGNED AFTER id");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
