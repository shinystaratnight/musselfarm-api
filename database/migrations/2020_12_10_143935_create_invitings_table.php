<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvitingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invitings', function (Blueprint $table) {
            $table->id();
            $table->string('email')->nullable();
            $table->enum('status', ['active', 'deactivated', 'pending'])->default('pending');
            $table->string('token', 20)->unique();
            $table->string('user_access');
            $table->unsignedBigInteger('inviting_user_id');
            $table->foreign('inviting_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('invited_user_id')->nullable();
            $table->foreign('invited_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invitings');
    }
}
