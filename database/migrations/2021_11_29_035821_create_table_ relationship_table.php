<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableRelationshipTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('relationship', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id_1');
            $table->integer('user_id_2');
            $table->tinyInteger('type_follow')->default(config(('relationship.type_follow.no_follow')));
            $table->tinyInteger('type_friend')->default(config(('relationship.type_follow.no_friend')));
            $table->date('date_accept');
        });
}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('relationship');
    }
}
