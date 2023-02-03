<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewColumnToCuEstimateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cu_estimate', function (Blueprint $table) {
            $table->string('estimate_natural_id', 255)->nullable()->after('core_id');
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
            $table->dropColumn('estimate_natural_id');
        });
    }
}
