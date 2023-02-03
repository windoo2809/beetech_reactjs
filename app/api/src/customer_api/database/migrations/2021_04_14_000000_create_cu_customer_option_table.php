<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCuCustomerOptionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cu_customer_option', function (Blueprint $table) {
            $table->increments('customer_id');
            $table->integer('core_id')->nullable();
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->smallInteger('plan_type')->nullable()->comment('0: 利用無 1: 通常');
            $table->string('admin_user_login_id', 2048)->nullable();
            $table->boolean('user_lock')->default(FALSE)->comment('TRUE: ロック, FALSE：なし')->nullable();
            $table->boolean('approval')->nullable();
            $table->smallInteger('data_scope')->default(0)->comment('0: すべて, 1: 支店単位, 2: 担当者単位');
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
        Schema::dropIfExists('cu_customer_option');
    }
}
