<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCuRequestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cu_request', function (Blueprint $table) {
            $table->increments('request_id');
            $table->integer('project_id');
            $table->integer('core_id')->nullable();
            $table->dateTime('request_date')->nullable();
            $table->dateTime('estimate_deadline')->nullable();
            $table->smallInteger('request_type')
                ->nullable()
                ->comment('0:初回、1:追加、2:延長、3:積替、4:その他ー道路使用許可、5:その他ー取扱説明書収納、6:その他ー電線移設');
            $table->dateTime('want_start_date')->nullable();
            $table->dateTime('want_end_date')->nullable();
            $table->integer('car_qty')->nullable();
            $table->integer('light_truck_qty')->nullable();
            $table->integer('truck_qty')->nullable();
            $table->integer('other_car_qty')->nullable();
            $table->text('other_car_detail')->nullable();
            $table->smallInteger('want_guide_type')
                ->nullable()
                ->comment('0:両方、1:Eメール案内希望、2:FAX案内希望');
            $table->string('cc_email', 2048)->nullable();
            $table->dateTime('response_request_date')->nullable();
            $table->text('customer_other_request')->nullable()->comment('顧客が要望する見積期限日');
            $table->integer('request_other_qty')->nullable();
            $table->smallInteger('request_status');
            $table->smallInteger('subcontract_want_guide_type')
                ->nullable()
                ->comment('0:両方、1:Eメール案内希望、2:FAX案内希望');
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
            $table->boolean('subcontract_reminder_sms_flag')->default(true)->nullable();
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
        Schema::dropIfExists('cu_request');
    }
}
