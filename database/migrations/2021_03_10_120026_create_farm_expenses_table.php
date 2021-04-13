<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFarmExpensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('farm_expenses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('farm_id');
            $table->enum('type', ['m', 's']);
            $table->string('expenses_name');
            $table->string('date')->default(0);
            $table->float('price_budget')->default(0.00);
            $table->float('price_actual')->default(0.00);
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
        Schema::dropIfExists('farm_expenses');
    }
}
