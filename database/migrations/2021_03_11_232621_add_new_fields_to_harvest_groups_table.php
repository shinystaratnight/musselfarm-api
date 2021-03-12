<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewFieldsToHarvestGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('harvest_groups', function (Blueprint $table) {
            $table->integer('density')->default(0);
            $table->integer('drop')->default(0);
            $table->integer('floats')->default(0);
            $table->integer('spacing')->default(0);
            $table->integer('submersion')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('harvest_groups', function (Blueprint $table) {
            $table->dropColumn('density');
            $table->dropColumn('drop');
            $table->dropColumn('floats');
            $table->dropColumn('spacing');
            $table->dropColumn('submersion');
        });
    }
}
