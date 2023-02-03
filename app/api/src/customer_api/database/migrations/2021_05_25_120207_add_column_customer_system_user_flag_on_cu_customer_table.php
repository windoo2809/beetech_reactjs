<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnCustomerSystemUserFlagOnCuCustomerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cu_customer', function (Blueprint $table) {
            $table->boolean('customer_system_use_flag')->after('construction_number_require_flag')->default(FALSE)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cu_customer', function (Blueprint $table) {
            $table->dropColumn('customer_system_use_flag');
        });
    }
}
