<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('password');
            $table->string('email');
            $table->integer('role_id');
            $table->integer('status');
            $table->timestamps();
        });

        $user = new App\User();
        $user->password = encrypt('pwadmin');
        $user->email = 'miguel_heredia_apps1ma1718@cev.com';
        $user->name = 'admin';
        $user->role_id = 1;
        $user->status = 1;
        $user->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
