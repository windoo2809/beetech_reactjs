<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCuFileTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cu_file', function (Blueprint $table) {
            $table->increments('file_id');
            $table->smallInteger('file_type')->comment('コード定義：1: 工事 2:見積依頼 3:見積 4:発注 5:契約 6:請求');
            $table->integer('customer_id');
            $table->integer('ref_id');
            $table->integer('project_id')->nullable();
            $table->integer('request_id')->nullable();
            $table->integer('estimate_id')->nullable();
            $table->integer('contract_id')->nullable();
            $table->integer('invoice_id')->nullable();
            $table->string('file_path', 4096);
            $table->string('file_name', 255)->nullable();
            $table->text('remark')->nullable();
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
        Schema::dropIfExists('cu_file');
    }
}
