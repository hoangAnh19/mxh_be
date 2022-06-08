<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTablePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('post', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('user_id_2')->nullable();// truong hop dang tren tuong cua ban be
            $table->integer('group_id')->nullable();// truong hop dang tren group
            $table->integer('post_id')->nullable();// truong hop chia se
            $table->tinyInteger('type_post'); //avatar, anh bia, hay status, chuc sinh nhat,..
            $table->tinyInteger('type_show'); //che do hien thi
            $table->text('data');
            $table->text('user_id_tags')->nullable();
            $table->text('src_images')->nullable();
            $table->integer('user_id_browse')->nullable();//nguoi duyet
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('post');
    }
}
