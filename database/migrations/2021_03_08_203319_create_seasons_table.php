<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\FarmUtil;
use App\Models\Season;
use App\Models\HarvestGroup;

class CreateSeasonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('seasons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('season_name');
            $table->timestamps();
        });

        $names = [];
        $users = [];

        foreach (HarvestGroup::all() as $item) {

            $user = FarmUtil::find($item->seed_id)->user_id;
            $name = $item->name;

            if (in_array($name, $names) && in_array($user, $users))
            {
                $season = Season::where('user_id', $user)->where('season_name', $name)->get();
                $item->name = $season[0]->id;
                $item->update();
                continue;
            }

            array_push($names, $name);
            array_push($users, $user);
            $season = Season::create([
                'user_id' => $user,
                'season_name' => $name,
            ]);

            $item->name = $season->id;
            $item->update();
        }

        Schema::table('harvest_groups', function (Blueprint $table) {
            $table->unsignedBigInteger('name')->default(null)->nullable()->change();

            $table->foreign('name')->references('id')->on('seasons')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('seasons');
    }
}
