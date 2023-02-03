<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCuBranchTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cu_branch', function (Blueprint $table) {
            $table->increments('branch_id');
            $table->integer('core_id')->nullable();
            $table->string('branch_name', 150)->nullable();
            $table->string('prefecture', 2)->nullable();
            $table->string('city', 255)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('tel', 13)->nullable();
            $table->string('fax', 13)->nullable();
            $table->string('zip_code', 8)->nullable();
            $table->string('bank_account', 255)->nullable();
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
        Schema::dropIfExists('cu_branch');
    }
}
