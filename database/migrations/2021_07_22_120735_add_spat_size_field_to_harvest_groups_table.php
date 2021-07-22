<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSpatSizeFieldToHarvestGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('harvest_groups', function (Blueprint $table) {
            $table->unsignedBigInteger('spat_size')->default(0);
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
            $table->dropColumn('spat_size');
        });
    }
}
