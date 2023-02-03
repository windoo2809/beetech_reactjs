<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnForCuRequestTable20210824 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cu_request', function (Blueprint $table) {
            $table->smallInteger('send_destination_type')->nullable()->default(0)->after('other_car_detail');
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
            $table->dropColumn('send_destination_type');
        });
    }
}
