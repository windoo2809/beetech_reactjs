<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateCuCustomerOptionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cu_customer_option', function (Blueprint $table) {
            $table->smallInteger('data_scope')->nullable()->change();
            $table->boolean('status')->nullable()->change();

            DB::statement('ALTER TABLE `cu_customer_option` CHANGE `create_date` `create_date` datetime DEFAULT CURRENT_TIMESTAMP NULL');
            DB::statement('ALTER TABLE `cu_customer_option` CHANGE `update_date` `update_date` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
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
            //
        });
    }
}
