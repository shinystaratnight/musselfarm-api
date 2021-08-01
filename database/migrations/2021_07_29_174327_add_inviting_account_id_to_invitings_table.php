<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\Inviting;
use App\Models\User;
class AddInvitingAccountIdToInvitingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invitings', function (Blueprint $table) {
            $table->unsignedBigInteger('inviting_account_id')->default(null)->nullable();
        });

        $invites = Inviting::all();

        foreach ($invites as $invite) {
            $user = User::find($invite->inviting_user_id);
            $invite->inviting_account_id = $user->account_id;
            $invite->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invitings', function (Blueprint $table) {
            $table->dropColumn('inviting_account_id');
        });
    }
}
