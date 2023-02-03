<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ChangeCuMessageHistoryPk extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cu_message_history', function (Blueprint $table) {
            $table->bigInteger('message_id', true)->change();
            $table->boolean('already_read')->nullable()->default(FALSE);
            $table->dropColumn('update_date');
            $table->dropColumn('update_user_id');
            $table->dropColumn('update_system_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cu_message_history', function (Blueprint $table) {

        });
    }
}
