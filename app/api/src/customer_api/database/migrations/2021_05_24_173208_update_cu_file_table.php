<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateCuFileTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cu_file', function (Blueprint $table) {
            $table->smallInteger('file_type')->nullable()->default(NULL)->change();
            $table->integer('customer_id')->nullable()->default(NULL)->change();
            $table->integer('ref_id')->nullable()->default(NULL)->change();
            $table->boolean('status')->nullable()->change();

            DB::statement('ALTER TABLE `cu_file` CHANGE `create_date` `create_date` datetime DEFAULT CURRENT_TIMESTAMP NULL');
            DB::statement('ALTER TABLE `cu_file` CHANGE `update_date` `update_date` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cu_file', function (Blueprint $table) {
            //
        });
    }
}
