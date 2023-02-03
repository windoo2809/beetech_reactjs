<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCuCustomerUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cu_customer_user', function (Blueprint $table) {
            $table->increments('customer_user_id');
            $table->integer('customer_id');
            $table->integer('customer_branch_id');
            $table->integer('core_id')->nullable();
            $table->string('customer_user_name', 255)->nullable();
            $table->string('customer_user_name_kana', 255)->nullable();
            $table->string('customer_user_division_name', 255)->nullable();
            $table->string('customer_user_email', 2048)->nullable();
            $table->string('customer_user_tel', 13)->nullable();
            $table->boolean('customer_reminder_sms_flag')->nullable();
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
        Schema::dropIfExists('cu_customer_user');
    }
}
