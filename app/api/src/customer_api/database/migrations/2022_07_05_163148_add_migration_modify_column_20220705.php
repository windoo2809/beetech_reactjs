<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddMigrationModifyColumn20220705 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared($this->getStatment());
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }

    protected function getStatment()
    {
        return "
            -- テーブル定義： 見積 2022/07/04 
            -- 開発環境 2022/07/04 実施済
            -- 本番環境 

            ALTER TABLE cu_estimate ADD  survey_pay_unit_type_day SMALLINT DEFAULT 0 AFTER survey_tax_in_flag;
            ALTER TABLE cu_estimate ADD  survey_pay_unit_type_month SMALLINT DEFAULT 0 AFTER survey_pay_unit_type_day;
        ";
    }
}
