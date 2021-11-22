<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\HarvestGroup;
use App\Models\LineSeeding;
use Carbon\Carbon;

class CreateLineSeedingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('line_seedings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('line_id');
            $table->foreign('line_id')->references('id')->on('lines');
            $table->unsignedBigInteger('season_id');
            $table->foreign('season_id')->references('id')->on('seasons');
            $table->unsignedBigInteger('seed_id')->nullable();
            $table->foreign('seed_id')->references('id')->on('farm_utils')->onDelete('cascade');
            $table->string('planned_date')->nullable();
            $table->string('planned_date_harvest')->nullable();
            $table->unsignedBigInteger('seeded_length')->default(1);
            $table->integer('density')->default(0);
            $table->float('drop')->default(0.00);
            $table->integer('floats')->default(0);
            $table->integer('spacing')->default(0);
            $table->integer('submersion')->default(0);
            $table->unsignedBigInteger('spat_size')->default(0);
            $table->integer('condition')->default(0);
            $table->timestamps();
        });

        $seedings = [];
        $harvests = HarvestGroup::all();
        foreach($harvests as $harvest)
        {
            $seeding = [];
            $seeding['line_id'] = $harvest->line_id;
            $seeding['season_id'] = $harvest->name;
            $seeding['seed_id'] = $harvest->seed_id;
            $seeding['planned_date'] = $harvest->planned_date;
            $seeding['planned_date_harvest'] = $harvest->planned_date_harvest;
            $seeding['seeded_length'] = $harvest->line_length;
            $seeding['density'] = $harvest->density;
            $seeding['drop'] = $harvest->drop;
            $seeding['floats'] = $harvest->floats;
            $seeding['spacing'] = $harvest->spacing;
            $seeding['submersion'] = $harvest->submersion;
            $seeding['spat_size'] = $harvest->spat_size;
            $seeding['condition'] = $harvest->condition;
            $seeding['created_at'] = Carbon::now()->format('Y-m-d H:i:s');
            $seeding['updated_at'] = Carbon::now()->format('Y-m-d H:i:s');
            $seedings[] = $seeding;
        }
        LineSeeding::insert($seedings);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('line_seedings');
    }
}
