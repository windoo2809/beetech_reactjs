<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateCuMessageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cu_message', function (Blueprint $table) {
            $table->bigInteger('message_id', true)->change();
            $table->integer('project_id')->nullable()->default(NULL)->change();
            $table->integer('core_id')->nullable()->default(NULL)->change();
            $table->integer('customer_id')->nullable()->default(NULL)->change();
            $table->boolean('already_read')->nullable()->default(FALSE)->change();
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
            //
        });
    }
}
