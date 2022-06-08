<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->integer('gender');
            $table->date('bird_day');
            $table->timestamp('verified_at')->nullable();
            $table->string('password');
            $table->string('address')->nullable();
            $table->string('education')->nullable();
            $table->string('workplace')->nullable();
            $table->string('avatar')->nullable();
            $table->string('cover')->nullable();
            $table->string('story')->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
