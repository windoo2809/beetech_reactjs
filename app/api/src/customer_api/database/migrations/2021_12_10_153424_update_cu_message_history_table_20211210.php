<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateCuMessageHistoryTable20211210 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cu_message_history', function (Blueprint $table) {
            $table->integer("core_id")->nullable()->after('project_id');
            $table->integer("customer_id")->nullable()->after('core_id');
        });
        DB::statement("ALTER TABLE cu_message_history MODIFY create_date TIMESTAMP(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6);");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cu_message_history', function (Blueprint $table) {
            $table->dropColumn("core_id");
            $table->dropColumn("customer_id");
        });
    }
}
