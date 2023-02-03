<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddMigrationModifyColumn20220802 extends Migration
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
            ALTER TABLE estimate ADD estimate_change_flag BOOL DEFAULT FALSE AFTER survey_available_end_date;
            ALTER TABLE estimate ADD order_start_date DATETIME AFTER estimate_change_flag;
            ALTER TABLE estimate ADD order_end_date DATETIME AFTER order_start_date;
            ALTER TABLE estimate ADD order_amt DECIMAL(12,0) AFTER order_end_date;
            ALTER TABLE estimate ADD order_capacity_qty INT AFTER order_end_date;
            ALTER TABLE estimate CHANGE order_memo order_contact_memo TEXT;
            -- テーブル定義: 見積 2022/08/01
            -- 開発環境 2022/08/01 実施済
            -- 本番環境 
            ALTER TABLE cu_estimate ADD  survey_parking_rent_profit DECIMAL(12,0) AFTER survey_parking_rent;
            ALTER TABLE cu_estimate ADD  survey_commission DECIMAL(12,0) AFTER survey_parking_rent_profit;
            ALTER TABLE cu_estimate ADD  survey_commission_discount_rate SMALLINT DEFAULT 0 AFTER survey_commission;
            ALTER TABLE cu_estimate ADD  survey_commission_discount_amt DECIMAL(12,0) AFTER survey_commission_discount_rate;
            ALTER TABLE cu_estimate ADD  survey_key_money DECIMAL(12,0) AFTER survey_commission_discount_amt;
            ALTER TABLE cu_estimate ADD  survey_subtotal_amt DECIMAL(12,0) AFTER survey_key_money;
            ALTER TABLE cu_estimate ADD  survey_fraction_amt_flag BOOL DEFAULT TRUE AFTER survey_subtotal_amt;
            ALTER TABLE cu_estimate ADD  survey_adjustment_amt DECIMAL(12,0) AFTER survey_fraction_amt_flag;
            ALTER TABLE cu_estimate ADD  survey_discount_amt DECIMAL(12,0) AFTER survey_adjustment_amt;
            ALTER TABLE cu_estimate ADD  survey_tax_amt DECIMAL(12,0) AFTER survey_discount_amt;
            
            
            -- テーブル変更後のデータパッチ
            -- 開発環境 2022/08/1 実施済
            -- 本番環境 
            UPDATE cu_estimate ce
            INNER JOIN estimate e ON ce.core_id = e.id
            SET
                    ce.survey_parking_prefecture = e.survey_parking_prefecture                -- 調査見積駐車場所在地：都道府県コード
                , ce.survey_parking_city = e.survey_parking_city                            -- 調査見積駐車場所在地：市区町村
                , ce.survey_parking_address = e.survey_parking_address                      -- 調査見積駐車場所在地：番地（町名以降）
                , ce.survey_parking_address_contract = e.survey_parking_address_contract    -- 調査見積駐車場所在地：土地の地番（契約書記載の所在地）
                , ce.survey_parking_parallel_type = e.survey_parking_parallel_type          -- 調査見積駐車場確認項目：駐車方法
                , ce.survey_capacity_flag_car = e.survey_capacity_flag_car                  -- 調査見積車両制限：収容可能車種・ワゴン車フラグ
                , ce.survey_capacity_flag_light_truck = e.survey_capacity_flag_light_truck  -- 調査見積車両制限：収容可能車種・軽トラックフラグ
                , ce.survey_capacity_flag_truck = e.survey_capacity_flag_truck              -- 調査見積車両制限：収容可能車種・2tトラックフラグ
                , ce.survey_capacity_flag_other = e.survey_capacity_flag_other              -- 調査見積車両制限：収容可能車種・その他フラグ
                , ce.survey_capacity_type_other = e.survey_capacity_type_other              -- 調査見積車両制限：その他詳細
                , ce.survey_capacity_special_request = e.survey_capacity_special_request    -- 調査見積車両制限：特別条件
                , ce.survey_pay_unit_type_day = e.survey_pay_unit_type_day                  -- 調査見積駐車場情報：日割可否
                , ce.survey_pay_unit_type_month = e.survey_pay_unit_type_month              -- 調査見積駐車場情報：通し可否
                , ce.survey_parking_rent = e.survey_parking_rent                            -- 調査見積見積金額：賃料（月単価）
                , ce.survey_parking_rent_profit = e.survey_parking_rent_profit
                , ce.survey_commission = e.survey_commission
                , ce.survey_commission_discount_rate = e.survey_commission_discount_rate
                , ce.survey_commission_discount_amt = e.survey_commission_discount_amt
                , ce.survey_key_money = e.survey_key_money
                , ce.survey_subtotal_amt = e.survey_subtotal_amt
                , ce.survey_fraction_amt_flag = e.survey_fraction_amt_flag
                , ce.survey_adjustment_amt = e.survey_adjustment_amt
                , ce.survey_discount_amt = e.survey_discount_amt
                , ce.survey_tax_amt = e.survey_tax_amt
                , ce.survey_available_start_date = e.survey_available_start_date            -- 見積利用開始日
                , ce.survey_available_end_date = e.survey_available_end_date                -- 見積利用終了日
                , ce.estimate_change_flag = e.estimate_change_flag                          -- 見積内容変更フラグ
                , ce.order_start_date = e.order_start_date                                  -- 発注利用開始日
                , ce.order_end_date = e.order_end_date                                      -- 発注利用終了日
                , ce.order_capacity_qty = e.order_capacity_qty                              -- 発注台数
                , ce.order_amt = e.order_amt                                                -- 発注概算金額
                , ce.order_contact_memo = e.order_contact_memo                              -- 発注連絡事
                , ce.update_system_type = 1
            ;  
        ";
    }
}
