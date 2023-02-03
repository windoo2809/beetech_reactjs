<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddMigrationModifyColumn20220622 extends Migration
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
        -- テーブル定義 申請 ※ 改修完了までの暫定対応 2022/06/15
        -- 開発環境 2022/06/15 実施済
        -- 本番環境
        ALTER TABLE cu_application MODIFY COLUMN  request_id INT ;



        -- テーブル定義： 見積 2022/06/17 
        -- 開発環境 2022/06/17 実施済
        -- 本番環境 
        ALTER TABLE cu_estimate ADD latitude DECIMAL(17,15)  AFTER survey_site_distance_meter;
        ALTER TABLE cu_estimate ADD longitude DECIMAL(18,15) AFTER latitude;
        ALTER TABLE cu_estimate ADD  survey_parking_prefecture VARCHAR(2) AFTER longitude;
        ALTER TABLE cu_estimate ADD  survey_parking_city VARCHAR(255) AFTER survey_parking_prefecture;
        ALTER TABLE cu_estimate ADD  survey_parking_address VARCHAR(255) AFTER survey_parking_city;
        ALTER TABLE cu_estimate ADD  survey_parking_address_contract VARCHAR(255) AFTER survey_parking_address;
        ALTER TABLE cu_estimate ADD  survey_parking_parallel_type SMALLINT DEFAULT 0  AFTER survey_parking_address_contract;
        ALTER TABLE cu_estimate ADD  survey_capacity_flag_car BOOL DEFAULT FALSE  AFTER survey_parking_parallel_type;
        ALTER TABLE cu_estimate ADD  survey_capacity_flag_light_truck BOOL DEFAULT FALSE  AFTER survey_capacity_flag_car;
        ALTER TABLE cu_estimate ADD  survey_capacity_flag_truck BOOL DEFAULT FALSE  AFTER survey_capacity_flag_light_truck;
        ALTER TABLE cu_estimate ADD  survey_capacity_flag_other BOOL DEFAULT FALSE  AFTER survey_capacity_flag_truck;
        ALTER TABLE cu_estimate ADD  survey_capacity_type_other VARCHAR(255) AFTER survey_capacity_flag_other;
        ALTER TABLE cu_estimate ADD  survey_capacity_special_request TEXT AFTER survey_capacity_type_other;
        ALTER TABLE cu_estimate CHANGE order_memo order_contact_memo TEXT;
        ";
    }
}
