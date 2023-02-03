<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SetDefaultForColumnInCuContractTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cu_contract', function (Blueprint $table) {
            DB::statement("ALTER TABLE cu_contract MODIFY purchase_order_check_flag TINYINT NULL DEFAULT '0' COMMENT '（デフォルト）0:未確認、1:確認済';");
            $table->smallInteger('extension_type')->nullable()->default(0)->comment('0: 未確認 1: 延長申込 2:延長無し')->change();
            $table->smallInteger('payment_status')->nullable()->default(0)->comment('0: 未払い 1:一部入金 2:支払完了')->after('extension_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cu_contract', function (Blueprint $table) {
            $table->dropColumn('payment_status');
        });
    }
}
