<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\BudgetLog;
use App\Models\HarvestGroup;
use App\Models\LineBudget;
use App\Models\LineHarvest;
use Carbon\Carbon;

class CreateLineHarvestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('line_harvests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seeding_id');
            $table->foreign('seeding_id')->references('id')->on('line_seedings');
            $table->string('harvest_complete_date')->default(0);
            $table->float('tonnes_harvested', 8, 3)->nullable()->default(0);
            $table->float('harvest_income', 8, 3)->nullable()->default(0);
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
            $table->longText('signature')->nullable()->default('');
            $table->string('comments')->nullable()->default('');
            $table->timestamps();
        });

        $l_harvests = [];
        $cnt = 0;
        $harvests = HarvestGroup::all();
        foreach($harvests as $harvest)
        {
            $cnt++;

            if (Carbon::now()->year == Carbon::createFromTimestamp($harvest->planned_date_harvest)->year) {
                $olb = LineBudget::where('end_budget', 0)
                    ->where('line_id', $harvest->line_id)->first();
            } else {
                $olb = LineBudget::where('start_budget', '<=', $harvest->planned_date_harvest)
                    ->where('end_budget', '>=', $harvest->planned_date_harvest)
                    ->where('line_id', $harvest->line_id)->first();
            }

            if ($olb) {

                $tones = BudgetLog::where('farm_id', $harvest->lines->farm_id)
                    ->where('line_id', $harvest->line_id)
                    ->where('line_budget_id', $olb->id)
                    ->where('row_name', 'planned_harvest_tones_actual')->first();
                
                $income = BudgetLog::where('farm_id', $harvest->lines->farm_id)
                    ->where('line_id', $harvest->line_id)
                    ->where('line_budget_id', $olb->id)
                    ->where('row_name', 'budgeted_harvest_income_actual')->first();
                    
                $l_harvest = [];
                $l_harvest['seeding_id'] = $cnt;
                $l_harvest['harvest_complete_date'] = $harvest->harvest_complete_date;
                $l_harvest['tonnes_harvested'] = $tones ? ($tones->new - $tones->old) : 0;
                $l_harvest['harvest_income'] = $income ? ($income->new - $income->old) : 0;
                $l_harvest['company'] = $harvest->company;
                $l_harvest['vessel'] = $harvest->vessel;
                $l_harvest['harvest_number'] = $harvest->harvest_number;
                $l_harvest['number_of_bags'] = $harvest->number_of_bags;
                $l_harvest['tag_color'] = $harvest->tag_color;
                $l_harvest['port_of_unload'] = $harvest->port_of_unload;
                $l_harvest['crop_owner'] = $harvest->crop_owner;
                $l_harvest['growing_area'] = $harvest->growing_area;
                $l_harvest['delivered_to'] = $harvest->delivered_to;
                $l_harvest['packhouse'] = $harvest->packhouse;
                $l_harvest['start_time'] = $harvest->start_time;
                $l_harvest['finish_time'] = $harvest->finish_time;
                $l_harvest['bags_clean'] = $harvest->bags_clean;
                $l_harvest['area_open_for_harvest'] = $harvest->area_open_for_harvest;
                $l_harvest['trucks_booked'] = $harvest->trucks_booked;
                $l_harvest['more_clean_bags_on_truck'] = $harvest->more_clean_bags_on_truck;
                $l_harvest['shell_length'] = $harvest->shell_length;
                $l_harvest['shell_condition'] = $harvest->shell_condition;
                $l_harvest['mussels'] = $harvest->mussels;
                $l_harvest['meat_yield'] = $harvest->meat_yield;
                $l_harvest['blues'] = $harvest->blues;
                $l_harvest['marine_waste'] = $harvest->marine_waste;
                $l_harvest['backbone_ok'] = $harvest->backbone_ok;
                $l_harvest['backbone_replace'] = $harvest->backbone_replace;
                $l_harvest['lights_ids_in_place'] = $harvest->lights_ids_in_place;
                $l_harvest['flotation_on_farm'] = $harvest->flotation_on_farm;
                $l_harvest['number_of_rope_bags'] = $harvest->number_of_rope_bags;
                $l_harvest['product_left_on_line'] = $harvest->product_left_on_line;
                $l_harvest['harvestor_name'] = $harvest->harvestor_name;
                $l_harvest['signature'] = $harvest->signature;
                $l_harvest['comments'] = $harvest->comments;
                $l_harvest['created_at'] = Carbon::now()->format('Y-m-d H:i:s');
                $l_harvest['updated_at'] = Carbon::now()->format('Y-m-d H:i:s');
                $l_harvests[] = $l_harvest;
            }
        }
        Lineharvest::insert($l_harvests);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('line_harvests');
    }
}
