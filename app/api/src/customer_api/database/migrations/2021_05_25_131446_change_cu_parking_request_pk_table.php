<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ChangeCuParkingRequestPkTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE cu_request_parking MODIFY COLUMN parking_id INT(11) NOT NULL");
        DB::statement("ALTER TABLE cu_request_parking DROP PRIMARY KEY, ADD PRIMARY KEY (request_id, parking_id)");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cu_request_parking', function (Blueprint $table) {

        });
    }
}
