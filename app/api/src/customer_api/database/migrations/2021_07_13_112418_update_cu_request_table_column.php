<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCuRequestTableColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cu_request', function (Blueprint $table) {
            $table->dateTime('request_other_deadline')->nullable()->after('customer_other_request');
            $table->dateTime('request_other_start_date')->nullable()->after('request_other_deadline');
            $table->dateTime('request_other_end_date')->nullable()->after('request_other_start_date');
            $table->smallInteger('request_other_status')->default(0)->nullable()->after('request_status');
            $table->integer('extend_estimate_id')->nullable()->after('subcontract_reminder_sms_flag');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cu_request', function (Blueprint $table) {
            $table->dropColumn('request_other_deadline');
            $table->dropColumn('request_other_start_date');
            $table->dropColumn('request_other_end_date');
            $table->dropColumn('request_other_status');
            $table->dropColumn('extend_estimate_id');
        });
    }
}
