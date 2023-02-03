<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateColumnToCuCustomerBranch extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cu_customer_branch', function (Blueprint $table) {
            $table->string('customer_branch_tel', 13)->nullable()->after('address');
            $table->string('customer_branch_fax', 13)->nullable()->after('customer_branch_tel');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cu_customer_branch', function (Blueprint $table) {
            $table->dropColumn('customer_branch_tel');
            $table->dropColumn('customer_branch_fax');
        });
    }
}
