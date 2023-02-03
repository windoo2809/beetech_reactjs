<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateCuInformationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cu_information', function (Blueprint $table) {
            $table->dateTime('start_date')->nullable()->default(NULL)->change();
            $table->dateTime('end_date')->nullable()->default(NULL)->change();
            $table->boolean('display_header')->nullable()->default(NULL)->change();
            $table->boolean('display_advertisement')->nullable()->default(NULL)->change();
            $table->boolean('status')->nullable()->change();

            DB::statement('ALTER TABLE `cu_information` CHANGE `create_date` `create_date` datetime DEFAULT CURRENT_TIMESTAMP NULL');
            DB::statement('ALTER TABLE `cu_information` CHANGE `update_date` `update_date` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cu_information', function (Blueprint $table) {
            //
        });
    }
}
