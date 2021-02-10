<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBudgetLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('budget_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->unsignedBigInteger('farm_id');
            $table->foreign('farm_id')->references('id')->on('farms');
            $table->unsignedBigInteger('line_id');
            $table->foreign('line_id')->references('id')->on('lines');
            $table->unsignedBigInteger('line_budget_id')->nullable();
            $table->foreign('line_budget_id')->references('id')->on('line_budgets');
            $table->unsignedBigInteger('expenses_id')->nullable();
            $table->foreign('expenses_id')->references('id')->on('expenses');
            $table->string('row_name');
            $table->string('human_name');
            $table->float('old', 10, 2)->default(0.00);
            $table->float('new', 10, 2)->default(0.00);
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
        Schema::dropIfExists('budget_logs');
    }
}
