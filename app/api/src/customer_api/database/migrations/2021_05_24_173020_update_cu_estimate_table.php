<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateCuEstimateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cu_estimate', function (Blueprint $table) {
            $table->integer('branch_id')->nullable()->default(NULL)->change();
            $table->smallInteger('estimate_status')->nullable()->default(NULL)->change();
            $table->boolean('status')->nullable()->change();
            $table->decimal('survey_total_amt', 12, 0)->nullable()->change();

            DB::statement('ALTER TABLE `cu_estimate` CHANGE `create_date` `create_date` datetime DEFAULT CURRENT_TIMESTAMP NULL');
            DB::statement('ALTER TABLE `cu_estimate` CHANGE `update_date` `update_date` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cu_estimate', function (Blueprint $table) {
            //
        });
    }
}
