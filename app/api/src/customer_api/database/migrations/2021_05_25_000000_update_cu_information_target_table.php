<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCuInformationTargetTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cu_information_target', function (Blueprint $table) {
            $table->integer('information_id')->nullable()->change();
            $table->integer('customer_id')->nullable()->change();
            $table->integer('customer_branch_id')->nullable()->change();
            $table->integer('customer_user_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cu_information_target', function (Blueprint $table) {

        });
    }
}
