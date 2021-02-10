<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssessmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('harvest_group_id');
            $table->foreign('harvest_group_id')->references('id')->on('harvest_groups');
            $table->enum('color', ['fair', 'good']);
            $table->bigInteger('condition_min')->default(0);
            $table->bigInteger('condition_max')->default(0);
            $table->bigInteger('condition_avg')->default(0);
            $table->integer('blues')->default(0);
            $table->float('tones', 8,3)->default(0.000);
            $table->string('planned_date_harvest')->default(0);
            $table->text('comment')->nullable();
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
        Schema::dropIfExists('assessments');
    }
}
