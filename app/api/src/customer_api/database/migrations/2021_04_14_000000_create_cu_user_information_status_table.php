<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCuUserInformationStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cu_user_information_status', function (Blueprint $table) {
            $table->integer('user_id');
            $table->integer('information_id');
            $table->boolean('already_read')->default(false)->comment('TRUE: 既読 FALSE：未読');
            $table->commonFields();
            /* Primary key */
            $table->primary(['user_id', 'information_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cu_user_information_status');
    }
}
