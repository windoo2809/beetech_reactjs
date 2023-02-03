<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddContractMemoColumnToCuRequestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cu_request', function (Blueprint $table) {
            $table->text('contact_memo')->nullable()->after('extend_estimate_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cu_request', function (Blueprint $table) {
            $table->dropColumn('contact_memo');
        });
    }
}
