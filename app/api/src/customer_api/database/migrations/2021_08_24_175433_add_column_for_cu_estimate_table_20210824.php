<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnForCuEstimateTable20210824 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cu_estimate', function (Blueprint $table) {
            $table->string('cs_document_id',255)->nullable()->after('contact_memo');
            $table->string('cs_file_id',255)->nullable()->after('cs_document_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cu_estimate', function (Blueprint $table) {
            $table->dropColumn('cs_document_id');
            $table->dropColumn('cs_file_id');
        });
    }
}
