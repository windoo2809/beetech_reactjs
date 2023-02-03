<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnCuMessageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cu_message', function (Blueprint $table) {
            $table->integer('core_id')->after('project_id')->nullable();
            $table->integer('customer_id')->after('core_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cu_message', function (Blueprint $table) {
            $table->dropColumn('core_id');
            $table->dropColumn('customer_id');
        });
    }
}
