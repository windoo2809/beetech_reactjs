<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnCuContractTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cu_contract', function (Blueprint $table) {
            $table->tinyInteger('purchase_order_check_flag')->after('purchase_order_register_type')->nullable();
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
            $table->dropColumn('purchase_order_check_flag');
        });
    }
}
