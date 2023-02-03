<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewColumnToCuInvoiceTable20210813 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cu_invoice', function (Blueprint $table) {
            $table->smallInteger('invoice_status')->nullable()->after('receivable_collect_finish_date');
            $table->smallInteger('contact_memo')->nullable()->after('reminder');
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
            $table->dropColumn('invoice_status');
            $table->dropColumn('contact_memo');
        });
    }
}
