<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToCuRequestParkingTable20221110 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared($this->getStatment());
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }

    protected function getStatment()
    {
        return "
            ALTER TABLE cu_request_parking ADD  extend_estimate_id INT AFTER core_id;
            ALTER TABLE cu_request_parking ADD  request_capacity_qty INT AFTER extend_estimate_id;
            ALTER TABLE cu_request_parking ADD  request_end_date INT AFTER request_capacity_qty;
            ALTER TABLE cu_request_parking MODIFY  COLUMN request_end_date DATETIME;
        ";
    }
}
