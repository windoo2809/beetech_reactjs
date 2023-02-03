<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateCuEstimate20221026 extends Migration
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

    protected function getStatment() {
        return "
        -- テーブル定義: 見積 2022/10/11
        -- 開発環境 2022/10/11 実施済
        -- 本番環境
        ALTER TABLE cu_estimate ADD  request_parking_space_qty INT AFTER survey_parking_name;
        ALTER TABLE cu_estimate ADD  cannot_extention_flag BOOL DEFAULT FALSE AFTER survey_total_amt;


        -- テーブル変更後のデータパッチ
        -- 開発環境 2022/10/11 実施済
        -- 本番環境
        update cu_estimate as ce
         inner join estimate e on ce.core_id = e.id
         inner join request_parking rp on e.request_id = rp.request_id and e.parking_id = rp.parking_id
         set ce.request_parking_space_qty = rp.request_parking_space_qty,
             ce.update_system_type = 1;
        ";
    }
}
