<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewColumnForCuSubcontractTable20210806 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cu_subcontract', function (Blueprint $table) {
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
        Schema::table('cu_subcontract', function (Blueprint $table) {
            $table->dropColumn('subcontract_branch_tel');
        });
    }
}
