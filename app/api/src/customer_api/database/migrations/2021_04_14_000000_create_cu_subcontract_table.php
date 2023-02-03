<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCuSubcontractTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cu_subcontract', function (Blueprint $table) {
            $table->increments('subcontract_id');
            $table->integer('customer_id');
            $table->string('subcontract_name', 255)->nullable();
            $table->string('subcontract_kana', 255)->nullable();
            $table->string('subcontract_branch_name', 255)->nullable();
            $table->string('subcontract_branch_kana', 255)->nullable();
            $table->string('subcontract_user_division_name', 255)->nullable();
            $table->string('subcontract_user_name', 255)->nullable();
            $table->string('subcontract_user_kana', 255)->nullable();
            $table->string('subcontract_user_email', 2048)->nullable();
            $table->string('subcontract_user_tel', 13)->nullable();
            $table->string('subcontract_user_fax', 13)->nullable();
            $table->tinyInteger('subcontract_reminder_sms_flag')->nullable();
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
        Schema::dropIfExists('cu_subcontract');
    }
}
