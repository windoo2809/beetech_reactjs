<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCuContractTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cu_contract', function (Blueprint $table) {
            $table->increments('contract_id');
            $table->integer('core_id')->nullable();
            $table->integer('project_id')->nullable();
            $table->integer('estimate_id')->nullable();
            $table->integer('parking_id')->nullable();
            $table->integer('branch_id')->nullable();
            $table->smallInteger('contract_status')->comment('コード定義：契約ステータス')->nullable();
            $table->string('parking_name', 255)->nullable();
            $table->string('parking_name_kana', 255)->nullable();
            $table->integer('quote_capacity_qty')->nullable();
            $table->decimal('quote_subtotal_amt', 12, 0)->nullable();
            $table->decimal('quote_tax_amt', 12, 0)->nullable();
            $table->decimal('quote_total_amt', 12, 0)->nullable();
            $table->dateTime('order_process_date')->nullable();
            $table->dateTime('purchase_order_upload_date')->nullable();
            $table->smallInteger('purchase_order_register_type')->comment('1: 基幹システム 2: 顧客向けシステム')->nullable();
            $table->dateTime('purchase_order_check_date')->nullable();
            $table->dateTime('order_schedule_date')->nullable();
            $table->dateTime('quote_available_start_date')->nullable();
            $table->dateTime('quote_available_end_date')->nullable();
            $table->smallInteger('extension_type')->comment('0: 未確認 1: 延長申込 2:延長無し')->nullable();
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
        Schema::dropIfExists('cu_contract');
    }
}
