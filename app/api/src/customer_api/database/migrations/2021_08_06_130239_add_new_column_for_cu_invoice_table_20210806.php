<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewColumnForCuInvoiceTable20210806 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cu_invoice', function (Blueprint $table) {
            $table->integer('core_invoice_id')->nullable()->after('core_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cu_invoice', function (Blueprint $table) {
            $table->dropColumn('core_invoice_id');
        });
    }
}
