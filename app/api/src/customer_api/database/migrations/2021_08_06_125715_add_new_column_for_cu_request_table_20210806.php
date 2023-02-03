<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewColumnForCuRequestTable20210806 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cu_request', function (Blueprint $table) {
            $table->integer('subcontract_id')->nullable()->after('subcontract_want_guide_type');
            $table->string('subcontract_branch_tel', 13)->nullable()->after('subcontract_branch_kana');
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
            $table->dropColumn('subcontract_id');
            $table->dropColumn('subcontract_branch_tel');
        });
    }
}
