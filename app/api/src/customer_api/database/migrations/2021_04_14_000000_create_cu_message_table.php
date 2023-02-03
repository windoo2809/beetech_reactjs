<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCuMessageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cu_message', function (Blueprint $table) {
            $table->increments('message_id');
            $table->integer('project_id');
            $table->text('body');
            $table->integer('file_id')->nullable();
            $table->boolean('edit')->default(true)->comment('TRUE: 未編集 FALSE:編集済');
            $table->boolean('already_read')->default(false);
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
        Schema::dropIfExists('cu_message');
    }
}
