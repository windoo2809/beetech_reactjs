<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCuUserMessageStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cu_user_message_status', function (Blueprint $table) {
            $table->integer('user_id');
            $table->integer('message_id');
            $table->boolean('already_read')->comment('0: 未読 1:既読')->default(FALSE);
            $table->commonFields();
            /* Set primary key address_cd */
            $table->primary(['user_id', 'message_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cu_user_message_status');
    }
}
