<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCuCustomerBranchTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cu_customer_branch', function (Blueprint $table) {
            $table->increments('customer_branch_id');
            $table->integer('customer_id');
            $table->integer('core_id')->nullable();
            $table->string('customer_branch_name', 255)->nullable();
            $table->string('customer_branch_name_kana', 255)->nullable();
            $table->char('zip', 8)->nullable()->comment('７桁郵便番号。半角数字。ハイフンが入り。例： "000-0000" ');
            $table->char('prefecture', 2)->nullable();
            $table->string('city', 255)->nullable();
            $table->string('address', 255)->nullable();
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
        Schema::dropIfExists('cu_customer_branch');
    }
}
