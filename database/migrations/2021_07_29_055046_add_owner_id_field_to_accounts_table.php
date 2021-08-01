<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\Account;
use App\Models\User;
class AddOwnerIdFieldToAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_id')->default(null)->nullable();

            $table->foreign('owner_id')->references('id')->on('users');
        });

        $accounts = Account::all();

        foreach ($accounts as $account) {
            $users = User::where('account_id', $account->id)->get();
            foreach ($users as $user) {
                if ($user->hasRole('owner')) {
                    $account->owner_id = $user->id;
                    $account->save();
                    break;
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
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropForeign(['owner_id']);
            $table->dropIndex('accounts_owner_id_foreign');
            $table->dropColumn('owner_id');
        });
    }
}
