<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangeColumnDatatypeInAssessmentPhotosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE assessment_photos MODIFY COLUMN created_at DATETIME default CURRENT_TIMESTAMP , MODIFY COLUMN updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('assessment_photos', function (Blueprint $table) {
            //
        });
    }
}
