<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCuEstimateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cu_estimate', function (Blueprint $table) {
            $table->increments('estimate_id');
            $table->integer('core_id')->nullable();
            $table->integer('request_id')->nullable();
            $table->integer('project_id')->nullable();
            $table->integer('parking_id')->nullable();
            $table->integer('branch_id');
            $table->smallInteger('estimate_status')
                ->comment('1:調査見積作成済 2:案内図経路図作成済 3:調査見積送付済 4:受注 5:確定見積送付済 8:失注（手動） 9:失注（自動）');
            $table->dateTime('estimate_expire_date')->nullable();
            $table->boolean('estimate_cancel_check_flag')->default(FALSE)->comment('TRUE:確認済,FALSE:未確認');
            $table->dateTime('estimate_cancel_check_date')->nullable();
            $table->string('survey_parking_name', 255)->nullable();
            $table->integer('survey_capacity_qty')->nullable();
            $table->decimal('survey_site_distance_minute', 6, 2)->nullable();
            $table->decimal('survey_site_distance_meter', 6, 2)->nullable();
            $table->boolean('survey_tax_in_flag')->nullable()->comment('TRUE: 税込, FALSE：税別');
            $table->decimal('survey_total_amt', 6, 2)->nullable();
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
        Schema::dropIfExists('cu_estimate');
    }
}
