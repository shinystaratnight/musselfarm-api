<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHarvestGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('harvest_groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('line_id');
            $table->foreign('line_id')->references('id')->on('lines');
            $table->string('name')->default('New group');
            $table->string('harvest_complete_date')->default(0);
            $table->string('planned_date_harvest')->default(0);
            $table->string('planned_date_harvest_original')->default(0);
            $table->string('planned_date')->nullable();
            $table->string('planned_date_original')->nullable();
            $table->string('color')->nullable();
            $table->unsignedBigInteger('seed_id')->nullable();
            $table->foreign('seed_id')->references('id')->on('seeds')->onDelete('cascade');
            $table->integer('condition')->nullable()->default(0);
            $table->float('profit_per_meter', 10, 2)->nullable()->default(0.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('harvest_groups');
    }
}
