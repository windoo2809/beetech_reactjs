<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateCuApplicationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cu_application', function (Blueprint $table) {
            $table->integer('estimate_id')->nullable()->change();
            $table->integer('application_user_id')->nullable()->change();
            $table->boolean('status')->nullable()->change();

            DB::statement('ALTER TABLE `cu_application` CHANGE `application_date` `application_date` datetime DEFAULT CURRENT_TIMESTAMP');
            DB::statement('ALTER TABLE `cu_application` CHANGE `create_date` `create_date` datetime DEFAULT CURRENT_TIMESTAMP NULL');
            DB::statement('ALTER TABLE `cu_application` CHANGE `update_date` `update_date` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
