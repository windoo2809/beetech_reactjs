<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCuMessageHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cu_message_history', function (Blueprint $table) {
            $table->increments('message_id');
            $table->integer('project_id');
            $table->text('body');
            $table->integer('file_id')->nullable();
            $table->boolean('edit')->comment('TRUE: 未編集, FALSE:編集済')->default(TRUE);
            $table->commonFields();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cu_message_history');
    }
}
