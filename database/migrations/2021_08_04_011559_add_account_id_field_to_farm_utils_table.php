<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\FarmUtil;

class AddAccountIdFieldToFarmUtilsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('farm_utils', function (Blueprint $table) {
            $table->unsignedBigInteger('account_id')->nullable()->default(null);
            $table->foreign('account_id')->references('id')->on('accounts');
        });

        $utils = FarmUtil::all();
        foreach ($utils as $util) {
            $util->account_id = $util->creator->account_id;
            $util->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('farm_utils', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
            $table->dropIndex('farm_utils_account_id_foreign');
            $table->dropColumn('account_id');
        });
    }
}
