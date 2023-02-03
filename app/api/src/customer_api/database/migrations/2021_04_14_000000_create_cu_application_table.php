<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use App\Common\DefaultFieldsBluePrint;
use Illuminate\Support\Facades\Schema;

class CreateCuApplicationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cu_application', function (Blueprint $table) {
            $table->increments('application_id');
            $table->integer('estimate_id');
            $table->integer('application_user_id');
            $table->dateTime('application_date');
            $table->integer('approval_user_id')->nullable();
            $table->dateTime('approval_date')->nullable();
            $table->integer('application_status')->nullable()->comment('1: 申請 2:承認 3:差戻 4:取下');
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
        Schema::dropIfExists('cu_application');
    }
}
