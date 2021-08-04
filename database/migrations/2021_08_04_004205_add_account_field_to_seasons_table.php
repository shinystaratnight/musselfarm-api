<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\Season;

class AddAccountFieldToSeasonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('seasons', function (Blueprint $table) {
            $table->unsignedBigInteger('account_id')->nullable()->default(null);
            $table->foreign('account_id')->references('id')->on('accounts');
        });

        $seasons = Season::all();
        foreach($seasons as $season) {
            $season->account_id = $season->creator->account_id;
            $season->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('seasons', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
            $table->dropIndex('seasons_account_id_foreign');
            $table->dropColumn('account_id');
        });
    }
}
