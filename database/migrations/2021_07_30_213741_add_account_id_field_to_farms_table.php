<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\Farm;
use App\Models\User;
class AddAccountIdFieldToFarmsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('farms', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex('farms_user_id_foreign');

            $table->unsignedBigInteger('account_id')->default(null)->nullable();
            $table->foreign('account_id')->references('id')->on('accounts');
        });

        $farms = Farm::all();
        foreach ($farms as $farm) {
            $farm->account_id = User::find($farm->user_id)->accounts->first()->id;
            $farm->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('farms', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
            $table->dropIndex('farms_account_id_foreign');
            $table->dropColumn('account_id');

            $table->foreign('user_id')->references('id')->on('users');
        });
    }
}
