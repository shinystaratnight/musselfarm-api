<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToHarvestGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('harvest_groups', function (Blueprint $table) {
            $table->string('company')->nullable()->default('');
            $table->string('vessel')->nullable()->default('');
            $table->unsignedBigInteger('harvest_number')->nullable()->default(0);
            $table->unsignedBigInteger('number_of_bags')->nullable()->default(0);
            $table->string('tag_color')->nullable()->default('');
            $table->string('port_of_unload')->nullable()->default('');
            $table->string('crop_owner')->nullable()->default('');
            $table->string('growing_area')->nullable()->default('');
            $table->string('delivered_to')->nullable()->default('');
            $table->string('packhouse')->nullable()->default('');
            $table->unsignedBigInteger('start_time')->nullable()->default(0);
            $table->unsignedBigInteger('finish_time')->nullable()->default(0);
            $table->boolean('bags_clean')->nullable()->default(false);
            $table->boolean('area_open_for_harvest')->nullable()->default(false);
            $table->boolean('trucks_booked')->nullable()->default(false);
            $table->boolean('more_clean_bags_on_truck')->nullable()->default(false);
            $table->unsignedBigInteger('shell_length')->nullable()->default(0);
            $table->string('shell_condition')->nullable()->default('');
            $table->unsignedBigInteger('mussels')->nullable()->default(0);
            $table->unsignedBigInteger('meat_yield')->nullable()->default(0);
            $table->unsignedBigInteger('blues')->nullable()->default(0);
            $table->string('marine_waste')->nullable()->default('');
            $table->boolean('backbone_ok')->nullable()->default(false);
            $table->boolean('backbone_replace')->nullable()->default(false);
            $table->boolean('lights_ids_in_place')->nullable()->default(false);
            $table->boolean('flotation_on_farm')->nullable()->default(false);
            $table->unsignedBigInteger('number_of_rope_bags')->nullable()->default(0);
            $table->string('product_left_on_line')->nullable()->default('');
            $table->string('harvestor_name')->nullable()->default('');
            $table->string('signature')->nullable()->default('');
            $table->string('comments')->nullable()->default('');
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
            $table->dropColumn('company');
            $table->dropColumn('vessel');
            $table->dropColumn('harvest_number');
            $table->dropColumn('number_of_bags');
            $table->dropColumn('tag_color');
            $table->dropColumn('port_of_unload');
            $table->dropColumn('crop_owner');
            $table->dropColumn('growing_area');
            $table->dropColumn('delivered_to');
            $table->dropColumn('packhouse');
            $table->dropColumn('start_time');
            $table->dropColumn('finish_time');
            $table->dropColumn('bags_clean');
            $table->dropColumn('area_open_for_harvest');
            $table->dropColumn('trucks_booked');
            $table->dropColumn('more_clean_bags_on_truck');
            $table->dropColumn('shell_length');
            $table->dropColumn('shell_condition');
            $table->dropColumn('mussels');
            $table->dropColumn('meat_yield');
            $table->dropColumn('blues');
            $table->dropColumn('marine_waste');
            $table->dropColumn('backbone_ok');
            $table->dropColumn('backbone_replace');
            $table->dropColumn('lights_ids_in_place');
            $table->dropColumn('flotation_on_farm');
            $table->dropColumn('number_of_rope_bags');
            $table->dropColumn('product_left_on_line');
            $table->dropColumn('name');
            $table->dropColumn('signature');
            $table->dropColumn('comments');
        });
    }
}
