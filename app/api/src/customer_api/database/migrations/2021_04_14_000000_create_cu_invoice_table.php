<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCuInvoiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cu_invoice', function (Blueprint $table) {
            $table->increments('invoice_id');
            $table->integer('core_id')->nullable()->comment('基幹システム.請求.ID');
            $table->integer('project_id')->nullable();
            $table->integer('contract_id')->nullable();
            $table->integer('customer_id')->nullable();
            $table->integer('customer_branch_id')->nullable();
            $table->integer('customer_user_id')->nullable();
            $table->integer('parking_id')->nullable();
            $table->decimal('invoice_amt', 12, 0)->nullable();
            $table->dateTime('invoice_closing_date')->nullable();
            $table->dateTime('payment_deadline')->nullable();
            $table->decimal('receivable_collect_total_amt', 12, 0)->nullable()->comment('顧客システム.売掛金.入金合計');
            $table->decimal('receivable_collect_finish_date', 12, 0)->nullable()->comment('顧客システム.売掛金.回収済日付');
            $table->smallInteger('payment_status')->nullable()->comment('0: 未払い 1:支払完了 2:一部入金');
            $table->boolean('reminder')->default(FALSE)->comment('FALSE: なし,TRUE： 督促状態');
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
        Schema::dropIfExists('cu_invoice');
    }
}
