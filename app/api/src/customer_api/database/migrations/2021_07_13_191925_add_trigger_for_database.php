<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddTriggerForDatabase extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared(file_get_contents(__DIR__ . "/file/create_trigger_20210712.sql"));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('
            DROP TRIGGER IF EXISTS ins_customer;
            DROP TRIGGER IF EXISTS upd_customer;
            DROP TRIGGER IF EXISTS ins_customer_branch;
            DROP TRIGGER IF EXISTS upd_customer_branch;
            DROP TRIGGER IF EXISTS ins_customer_user;
            DROP TRIGGER IF EXISTS upd_customer_user;
            DROP TRIGGER IF EXISTS ins_parking;
            DROP TRIGGER IF EXISTS upd_parking;
            DROP TRIGGER IF EXISTS ins_user_branch;
            DROP TRIGGER IF EXISTS upd_user_branch;
            DROP TRIGGER IF EXISTS ins_project;
            DROP TRIGGER IF EXISTS upd_project;
            DROP TRIGGER IF EXISTS ins_request;
            DROP TRIGGER IF EXISTS upd_request;
            DROP TRIGGER IF EXISTS ins_estimate;
            DROP TRIGGER IF EXISTS upd_estimate;
            DROP TRIGGER IF EXISTS ins_accounts_receivable;
            DROP TRIGGER IF EXISTS upd_accounts_receivable;
            DROP TRIGGER IF EXISTS ins_cu_file;
            DROP TRIGGER IF EXISTS upd_cu_message;
            DROP TRIGGER IF EXISTS ins_cu_project;
            DROP TRIGGER IF EXISTS upd_cu_project;
            DROP TRIGGER IF EXISTS ins_cu_request;
            DROP TRIGGER IF EXISTS upd_cu_request;
            DROP TRIGGER IF EXISTS ins_cu_request_parking;
            DROP TRIGGER IF EXISTS upd_cu_estimate;
            DROP TRIGGER IF EXISTS upd_cu_user;
            DROP TRIGGER IF EXISTS ins_cu_user_branch;
        ');
    }
}
