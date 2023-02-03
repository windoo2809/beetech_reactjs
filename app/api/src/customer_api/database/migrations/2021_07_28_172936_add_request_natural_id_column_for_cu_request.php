<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRequestNaturalIdColumnForCuRequest extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cu_request', function (Blueprint $table) {
            $table->string('request_natural_id', 255)->after('core_id')->nullable();
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
            $table->dropColumn('request_natural_id');
        });
    }
}
