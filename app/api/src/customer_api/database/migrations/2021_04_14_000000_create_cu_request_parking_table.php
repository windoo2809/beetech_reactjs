<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCuRequestParkingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cu_request_parking', function (Blueprint $table) {
            $table->integer('request_id');
            $table->integer('parking_id');
            $table->commonFields();
            /* Primary key */
            $table->primary(['request_id', 'parking_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cu_request_parking');
    }
}
