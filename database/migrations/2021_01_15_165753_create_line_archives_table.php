<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLineArchivesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('line_archives', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('harvest_group_id');
            $table->foreign('harvest_group_id')->references('id')->on('harvest_groups');
            $table->integer('length')->nullable();
            $table->string('planned_date_harvest')->nullable();
            $table->string('planned_date_harvest_original')->nullable();
            $table->string('planned_date')->nullable();
            $table->unsignedBigInteger('seed_id')->nullable();
            $table->foreign('seed_id')->references('id')->on('seeds')->onDelete('cascade');
            $table->integer('condition')->nullable();
            $table->float('profit_per_meter', 10,2)->nullable();
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
        Schema::dropIfExists('line_archives');
    }
}
