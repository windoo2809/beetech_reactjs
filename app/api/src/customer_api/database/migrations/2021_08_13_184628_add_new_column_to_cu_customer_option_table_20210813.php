<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewColumnToCuCustomerOptionTable20210813 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cu_customer_option', function (Blueprint $table) {
            $table->string('customer_login_id', 15)->nullable()->after('plan_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cu_customer_option', function (Blueprint $table) {
            $table->dropColumn('customer_login_id');
        });
    }
}
