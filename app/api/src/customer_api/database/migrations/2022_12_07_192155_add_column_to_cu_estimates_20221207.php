<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddColumnToCuEstimates20221207 extends Migration
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

    }

    protected function getStatment() {
        return "
        -- テーブル定義: 見積 2022/11/30
        -- 開発環境 2022/11/30 実施済
        -- 本番環境  2022/11/30 実施済
        ALTER TABLE cu_estimate MODIFY  COLUMN  survey_site_distance_meter  DECIMAL(8,2);
        ";
    }
}
