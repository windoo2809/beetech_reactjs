<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCuAddressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cu_address', function (Blueprint $table) {
            $table->char('address_cd', 9);
            $table->char('prefecture_cd', 2)->nullable();
            $table->char('city_cd', 5)->nullable();
            $table->char('town_cd', 9)->nullable();
            $table->char('zip_cd', 8)->nullable();
            $table->char('company_flg', 1)->nullable();
            $table->char('delete_flg', 1)->nullable();
            $table->string('prefecture_name', 255)->nullable();
            $table->string('prefecture_kana', 255)->nullable();
            $table->string('city_name', 255)->nullable();
            $table->string('city_kana', 255)->nullable();
            $table->string('town_name', 255)->nullable();
            $table->string('town_kana', 255)->nullable();
            $table->string('town_info', 255)->nullable();
            $table->string('kyoto_street_name', 255)->nullable();
            $table->string('street_name', 255)->nullable();
            $table->string('street_kana', 255)->nullable();
            $table->string('information', 255)->nullable();
            $table->string('company_name', 255)->nullable();
            $table->string('company_kana', 255)->nullable();
            $table->string('company_address', 2048)->nullable();
            $table->char('new_address_cd', 1)->nullable();
            /* Set primary key address_cd */
            $table->primary('address_cd');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cu_address');
    }
}
