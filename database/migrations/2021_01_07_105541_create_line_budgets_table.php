<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLineBudgetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('line_budgets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('line_id');
            $table->foreign('line_id')->references('id')->on('lines');
            $table->string('start_budget')->default(0);
            $table->string('end_budget')->default(0);
            $table->float('planned_harvest_tones')->default(0.000);
            $table->float('budgeted_harvest_income')->default(0.000);
            $table->bigInteger('length_budget')->default(0);
            $table->bigInteger('length_actual')->default(0);
            $table->float('planned_harvest_tones_actual')->default(0.000);
            $table->float('budgeted_harvest_income_actual')->default(0.000);
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
        Schema::dropIfExists('line_budgets');
    }
}
