<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCuUserBranchTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cu_user_branch', function (Blueprint $table) {
            $table->increments('user_branch_id');
            $table->integer('user_id')->nullable();
            $table->integer('customer_id')->nullable();
            $table->integer('customer_branch_id')->nullable();
            $table->smallInteger('belong')->comment('0: 所属なし　1: 所属')->nullable();
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
        Schema::dropIfExists('cu_user_branch');
    }
}
