<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\User;
use App\Models\Inviting;
use App\Models\Account;

class AddAccountIdFieldToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('account_id')->default(null)->nullable();

            $table->foreign('account_id')->references('id')->on('accounts');
        });

        $users = User::all();

        foreach (User::all() as $user) {
            $inviterId = -1;
            $inviter = Inviting::where('invited_user_id', $user->id)->first();
            while ($inviter) {
                $inviterId = $inviter->inviting_user_id;
                $inviter = Inviting::where('invited_user_id', $inviter->inviting_user_id)->first();
            }

            if ($inviterId == -1) {
                $account = Account::create([]);
                $user->account_id = $account->id;
            } else {
                $owner = User::find($inviterId);
                if ($owner) {
                    $user->account_id = $owner->account_id;
                }
            }
            $user->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            Schema::dropIfExists('account_id');
        });
    }
}
