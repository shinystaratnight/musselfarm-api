<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\Invite;
use App\Models\User;

class UpdateEmailFieldOnInvitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invites', function (Blueprint $table) {
            $table->dropUnique(['email']);

            $table->unsignedBigInteger('inviting_account_id')->nullable()->default(null);
            $table->foreign('inviting_account_id')->references('id')->on('accounts');
        });

        $invites = Invite::all();
        foreach ($invites as $invite) {
            $invite->inviting_account_id = User::where('email', $invite->email)->first()->account_id;
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
        Schema::table('invites', function (Blueprint $table) {
            $table->string('email')->unique()->change();

            $table->dropForeign(['inviting_account_id']);
            $table->dropIndex('invites_inviting_account_id_foreign');
            $table->dropColumn('inviting_account_id');
        });
    }
}
