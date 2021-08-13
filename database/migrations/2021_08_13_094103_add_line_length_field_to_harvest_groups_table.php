<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\HarvestGroup;

class AddLineLengthFieldToHarvestGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('harvest_groups', function (Blueprint $table) {
            $table->unsignedBigInteger('line_length')->default(1);
        });

        $harvestGroups = HarvestGroup::all();
        foreach ($harvestGroups as $harvestGroup) {
            $harvestGroup->line_length = $harvestGroup->lines->length;
            $harvestGroup->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('harvest_groups', function (Blueprint $table) {
            $table->dropColumn('line_length');
        });
    }
}
