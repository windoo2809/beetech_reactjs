<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCuInformationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cu_information', function (Blueprint $table) {
            $table->increments('information_id');
            $table->text('subject');
            $table->text('body');
            $table->smallInteger('data_type')->default(1)->nullable()->comment('1: 全て  2: 顧客単位');
            $table->string('image_url', 4096)->nullable();
            $table->string('thumbnail_url', 4096)->nullable();
            $table->string('ad_url', 4096)->nullable();
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->boolean('display_header')->comment('TRUE: 対象 FALSE：対象外');
            $table->boolean('display_advertisement')->comment('TRUE: 対象 FALSE：対象外');
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
        Schema::dropIfExists('cu_information');
    }
}
