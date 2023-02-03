<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddContactMemoColumnToCuEstimateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cu_estimate', function (Blueprint $table) {
            $table->text('contact_memo')->nullable()->after('survey_total_amt');
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
            $table->dropColumn('contact_memo');
        });
    }
}
