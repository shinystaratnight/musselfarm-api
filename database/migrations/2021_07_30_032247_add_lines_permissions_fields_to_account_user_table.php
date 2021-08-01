<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\Inviting;
use App\Models\User;
use App\Models\Account;
use App\Models\AccountUser;
class AddLinesPermissionsFieldsToAccountUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('account_user', function (Blueprint $table) {
            $table->string('user_access')->nullable();
        });

        $accounts = Account::with('users')->get();

        foreach ($accounts as $account) {
            foreach($account->users as $user) {
                $invite = Inviting::where([
                    'invited_user_id' => $user->id,
                    'inviting_account_id' => $user->account_id
                ])->first();
                if ($invite) {
                    $user->pivot->user_access = $invite->user_access;
                    $user->pivot->save();
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('account_user', function (Blueprint $table) {
            $table->dropColumn('user_access');
        });
    }
}
