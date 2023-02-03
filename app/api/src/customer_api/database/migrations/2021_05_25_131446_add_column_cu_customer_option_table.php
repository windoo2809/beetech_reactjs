<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnCuCustomerOptionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cu_customer_option', function (Blueprint $table) {
            $table->integer('admin_user_id')->after('plan_type')->nullable();
            $table->string('admin_user_name', 255)->after('admin_user_id')->nullable();
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
            $table->dropColumn('admin_user_id');
            $table->dropColumn('admin_user_name');
        });
    }
}
