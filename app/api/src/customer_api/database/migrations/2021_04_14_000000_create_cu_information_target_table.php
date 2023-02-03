<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCuInformationTargetTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cu_information_target', function (Blueprint $table) {
            $table->increments('information_target_id');
            $table->integer('information_id');
            $table->integer('customer_id');
            $table->integer('customer_branch_id');
            $table->integer('customer_user_id');
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
        Schema::dropIfExists('cu_information_target');
    }
}
