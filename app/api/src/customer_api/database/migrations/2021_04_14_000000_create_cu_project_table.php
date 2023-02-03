<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCuProjectTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cu_project', function (Blueprint $table) {
            $table->increments('project_id');
            $table->integer('core_id')->nullable();
            $table->integer('customer_id');
            $table->integer('customer_branch_id');
            $table->integer('customer_user_id');
            $table->integer('branch_id');
            $table->string('construction_number', 255)->nullable();
            $table->string('site_name', 255);
            $table->string('site_name_kana', 255)->nullable();
            $table->char('zip_code', 8)->nullable();
            $table->char('site_prefecture', 2);
            $table->char('address_cd', 8)->nullable();
            $table->char('city_cd', 5)->nullable();
            $table->string('site_city', 255);
            $table->string('site_address', 255)->nullable();
            $table->decimal('latitude', 17, 15)->nullable();
            $table->decimal('longitude', 18, 15)->nullable();
            $table->date('project_start_date')->nullable();
            $table->date('project_finish_date')->nullable();
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
        Schema::dropIfExists('cu_project');
    }
}
