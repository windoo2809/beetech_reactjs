<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCuParkingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cu_parking', function (Blueprint $table) {
            $table->increments('parking_id');
            $table->integer('core_id')->comment('基幹システム.駐車場マスタ.ID');
            $table->string('parking_name', 255)->nullable();
            $table->string('parking_name_kana', 255)->nullable();
            $table->decimal('latitude', 17, 15)->nullable();
            $table->decimal('longitude', 18, 15)->nullable();
            $table->commonFields();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cu_parking');
    }
}
