<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateNewColumnForCuInformationTargetTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cu_information_target', function (Blueprint $table) {
            $table->integer('core_customer_id')->nullable()->after('customer_user_id');
            $table->integer('core_customer_branch_id')->nullable()->after('core_customer_id');
            $table->integer('core_customer_user_id')->nullable()->after('core_customer_branch_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cu_information_target', function (Blueprint $table) {
            $table->dropColumn('core_customer_id');
            $table->dropColumn('core_customer_branch_id');
            $table->dropColumn('core_customer_user_id');
        });
    }
}
