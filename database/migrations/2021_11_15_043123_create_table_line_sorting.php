<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableLineSorting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
    
        Schema::create('line_sorting', function (Blueprint $table) {
            $table->id();
            $table->integer('farm_id');
            $table->string('column_name');
            $table->integer("user_id");
            $table->string("column_order");
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
        Schema::table('line_sorting', function (Blueprint $table) {
            //
        });
    }
}
