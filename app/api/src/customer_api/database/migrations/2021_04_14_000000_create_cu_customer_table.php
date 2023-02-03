<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCuCustomerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cu_customer', function (Blueprint $table) {
            $table->increments('customer_id');
            $table->integer('core_id')->nullable();
            $table->string('customer_name', 255)->nullable();
            $table->string('customer_name_kana', 255)->nullable();
            $table->boolean('construction_number_require_flag')->nullable();
            $table->boolean('use_cu')->default(FALSE)->nullable();;
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
        Schema::dropIfExists('cu_customer');
    }
}
