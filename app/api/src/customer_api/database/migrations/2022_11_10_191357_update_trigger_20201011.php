<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateTrigger20201011 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared($this->createTrigger());
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

    private function createTrigger()
    {
        return "
        -- トリガー： 見積（登録） 更新日：22/11/08
        DROP TRIGGER IF EXISTS ins_estimate;

        CREATE TRIGGER ins_estimate AFTER INSERT ON estimate FOR EACH ROW
        BEGIN

          DECLARE _request_id, _project_id, _parking_id, _branch_id, _estimate_id, _request_parking_space_qty INT;
          DECLARE _parking_name, _parking_name_kana VARCHAR(255);
          DECLARE _latitude decimal(17,15);
          DECLARE _longitude decimal(18,15);

          -- 基幹システムで作成した場合
          IF new.register_type = 0 THEN

            -- 顧客向けシステムの見積依頼情報の取得
            SELECT
             request_id
           , project_id
           INTO
             _request_id
           , _project_id
           FROM cu_request
           WHERE core_id = new.request_id;

            -- 調査見積送付済みの場合のみ、見積データを作成する
            IF new.estimate_status IN ( 3,4,5 ) THEN

              -- 駐車場情報の取得
              SELECT
                  parking_id
                , parking_name
                , parking_name_kana
                , latitude
                , longitude
             INTO
                  _parking_id
                , _parking_name
                , _parking_name_kana
                , _latitude
                , _longitude
              FROM cu_parking
              WHERE core_id = new.parking_id;

              -- 駐車場空台数の取得
              SELECT r.request_parking_space_qty
              INTO _request_parking_space_qty
              FROM request_parking r
              WHERE r.request_id = new.request_id
                AND r.parking_id = new.parking_id;

              -- 支店情報の取得
              SELECT
                branch_id
              INTO
                _branch_id
              FROM cu_branch
              WHERE core_id = new.user_branch_id;

              -- 見積データの作成
              INSERT INTO cu_estimate(
                core_id
              , estimate_natural_id
              , request_id
              , project_id
              , parking_id
              , branch_id
              , estimate_status
              , estimate_expire_date
              , estimate_cancel_check_flag
              , estimate_cancel_check_date
              , survey_parking_name
              , survey_capacity_qty
              , survey_site_distance_minute
              , survey_site_distance_meter
              , latitude
              , longitude
              , survey_parking_prefecture
              , survey_parking_city
              , survey_parking_address
              , survey_parking_address_contract
              , survey_parking_parallel_type
              , survey_capacity_flag_car
              , survey_capacity_flag_light_truck
              , survey_capacity_flag_truck
              , survey_capacity_flag_other
              , survey_capacity_type_other
              , survey_capacity_special_request
              , survey_tax_in_flag
              , survey_pay_unit_type_day
              , survey_pay_unit_type_month
              , survey_parking_rent
              , survey_parking_rent_profit
              , survey_commission
              , survey_commission_discount_rate
              , survey_commission_discount_amt
              , survey_key_money
              , survey_subtotal_amt
              , survey_fraction_amt_flag
              , survey_adjustment_amt
              , survey_discount_amt
              , survey_tax_amt
              , survey_total_amt
              , cannot_extention_flag
              , survey_available_start_date
              , survey_available_end_date
              , request_parking_space_qty
              , order_date
              , estimate_change_flag
              , order_start_date
              , order_end_date
              , order_capacity_qty
              , order_amt
              , order_contact_memo
              , contact_memo
        --      , cs_document_id,
        --      , cs_file_id
              , create_system_type
              , create_user_id
              , status
             )
             VALUES (
                new.id                                        -- 基幹システム連携ID
              , new.estimate_natural_id                       -- 見積NO
              , _request_id                                   -- 見積依頼ID
              , _project_id                                   -- 工事ID
              , _parking_id                                   -- 駐車場ID
              , _branch_id                                    -- 支店ID
              , case                                          -- 見積ステータス
                   when new.estimate_status = 3 then 1        -- 3:調査見積送付済み → 1: 受注待ち
                   when new.estimate_status = 4 then 4        -- 4:受注 → 4:受注
                   when new.estimate_status = 5 then 5        -- 5:確定見積送付済 → 5:確定見積送付済
                   when new.estimate_status = 6 then 8        -- 6:失注（手動）   → 8:キャンセル
                   when new.estimate_status = 7 then 9        -- 7:失注（自動）   → 9:キャンセル
                   when new.estimate_status = 8 then 8        -- 8:請求キャンする → 8:キャンセル
                   else estimate_status                       -- 変更なし
                end
              , new.estimate_expire_date                      -- 見積有効期限日
              , new.estimate_cancel_check_flag                -- 見積キャンセル確認フラグ
              , new.estimate_cancel_check_date                -- 見積キャンセル確認日
              , new.survey_parking_name                       -- 調査見積駐車場情報：駐車場名
              , new.survey_capacity_qty                       -- 調査見積見積情報：見積台数
              , new.survey_site_distance_minute               -- 調査見積見積情報：現場距離（分）
              , new.survey_site_distance_meter                -- 調査見積見積情報：現場距離（メートル）
              , _latitude                                     -- 駐車場緯度
              , _longitude                                    -- 駐車場経度
              , new.survey_parking_prefecture                 -- 調査見積駐車場所在地：都道府県コード
              , new.survey_parking_city                       -- 調査見積駐車場所在地：市区町村
              , new.survey_parking_address                    -- 調査見積駐車場所在地：番地（町名以降）
              , new.survey_parking_address_contract           -- 調査見積駐車場所在地：土地の地番（契約書記載の所在地）
              , new.survey_parking_parallel_type              -- 調査見積駐車場確認項目：駐車方法
              , new.survey_capacity_flag_car                  -- 調査見積車両制限：収容可能車種・ワゴン車フラグ
              , new.survey_capacity_flag_light_truck          -- 調査見積車両制限：収容可能車種・軽トラックフラグ
              , new.survey_capacity_flag_truck                -- 調査見積車両制限：収容可能車種・2tトラックフラグ
              , new.survey_capacity_flag_other                -- 調査見積車両制限：収容可能車種・その他フラグ
              , new.survey_capacity_type_other                -- 調査見積車両制限：その他詳細
              , new.survey_capacity_special_request           -- 調査見積車両制限：特別条件
              , new.survey_tax_in_flag                        -- 調査見積見積金額：税込みフラグ
              , new.survey_pay_unit_type_day                  -- 調査見積駐車場情報：日割可否
              , new.survey_pay_unit_type_month                -- 調査見積駐車場情報：通し可否
              , new.survey_parking_rent                       -- 調査見積見積金額：賃料（月単価）
              , new.survey_parking_rent_profit                -- 調査見積見積金額：上乗せ額
              , new.survey_commission                         -- 調査見積見積金額：手数料
              , new.survey_commission_discount_rate           -- 調査見積見積金額：手数料割引率
              , new.survey_commission_discount_amt            -- 調査見積見積金額：実質手数料
              , new.survey_key_money                          -- 調査見積見積金額：礼金
              , new.survey_subtotal_amt                       -- 調査見積見積金額：見積小計
              , new.survey_fraction_amt_flag                  -- 調査見積見積金額：端数調整フラグ
              , new.survey_adjustment_amt                     -- 調査見積見積金額：調整金額
              , new.survey_discount_amt                       -- 調査見積見積金額：値引額
              , new.survey_tax_amt                            -- 調査見積見積金額：税額
              , new.survey_total_amt                          -- 調査見積見積金額：見積合計
              , new.cannot_extention_flag                     -- 延長不可フラグ
              , new.survey_available_start_date               -- 見積利用開始日
              , new.survey_available_end_date                 -- 見積利用終了日
              , _request_parking_space_qty                    -- 調査駐車場空台数
              , new.purchase_order_upload_date                -- 発注書アップロード日
              , new.estimate_change_flag                      -- 見積内容変更フラグ
              , new.order_start_date                          -- 発注利用開始日
              , new.order_end_date                            -- 発注利用終了日
              , new.order_capacity_qty                        -- 発注台数
              , new.order_amt                                 -- 発注概算金額
              , new.order_contact_memo                        -- 発注連絡事項
              , new.contact_memo                              -- 連絡メモ
        --      , new.estimate_document_id                    -- クラウドサイン連携用ドキュメントID
        --      , new.estimate_file_id                        -- クラウドサイン連携用ファイルID
              , 1                                             -- 固定値：基幹システム
              , 0                                             -- 固定値：システム自動連携
              , ISNULL( new.delete_timestamp )                -- ステータス
            );

            -- 契約データの作成
            -- 契約データの作成前でキャンセルの場合は契約データは作成しない
            IF new.estimate_status = 5 THEN

              -- 見積IDの取得
              SELECT estimate_id
              INTO   _estimate_id
              FROM   cu_estimate
              WHERE  core_id = new.id;

                INSERT INTO cu_contract(
                  core_id
                , project_id
                , estimate_id
                , parking_id
                , branch_id
                , contract_status
                , parking_name
                , parking_name_kana
                , quote_capacity_qty
                , quote_subtotal_amt
                , quote_tax_amt
                , quote_total_amt
                , purchase_order_upload_date
                , purchase_order_register_type
                , purchase_order_check_flag
                , order_schedule_date
                , order_process_date
                , quote_available_start_date
                , quote_available_end_date
                , extension_type
                , create_system_type
                , create_user_id
                , status
                )
                VALUES (
                  new.id                                        -- 基幹システム連携ID
                , _project_id                                   -- 工事ID
                , _estimate_id                                  -- 見積ID
                , _parking_id                                   -- 駐車場ID
                , _branch_id                                    -- 支店ID
                , 2                                             -- 契約ステータス  2:契約書準備中
                , _parking_name                                 -- 駐車場名
                , _parking_name_kana                            -- 駐車場名：カナ
                , new.quote_capacity_qty                        -- 確定見積台数
                , new.quote_subtotal_amt                        -- 確定見積：見積小計
                , new.quote_tax_amt                             -- 確定見積：税額
                , new.quote_total_amt                           -- 確定見積：合計額
                , new.purchase_order_upload_date                -- 発注書アップロード日
                , new.purchase_order_register_type              -- 発注書アップロードシステム
                , new.purchase_order_check_flag                 -- 発注書確認フラグ
                , new.order_schedule_date                       -- 受注予定日
                , new.order_process_date                        -- 受注処理日
                , new.quote_available_start_date                -- 契約開始日
                , new.quote_available_end_date                  -- 契約終了日
                , 0                                             -- 契約延長区分
                , 1                                             -- 固定値：基幹システム
                , 0                                             -- 固定値：システム自動連携
                , ISNULL( new.delete_timestamp )                -- ステータス
                );
              END IF;
            END IF;
          END IF;
        END;

        -- トリガー： 見積（更新） UPDATE 更新日：22/11/08
        DROP TRIGGER IF EXISTS upd_af_estimate;

        CREATE TRIGGER upd_af_estimate AFTER UPDATE ON estimate FOR EACH ROW
        BEGIN

        DECLARE _request_id, _project_id, _parking_id, _branch_id, _estimate_id, _application_id, _request_parking_space_qty int;
        DECLARE _application_status, _request_type, _request_other_status SMALLINT;
        DECLARE _parking_name, _parking_name_kana varchar(255);
        DECLARE _latitude decimal(17,15);
        DECLARE _longitude decimal(18,15);

        -- 基幹システムで更新した場合のみ実行
        IF new.update_type = 0 THEN

            -- 見積依頼情報の取得
            SELECT
            request_id
            , project_id
            , request_type
            , request_other_status
            INTO
            _request_id
            , _project_id
            , _request_type
            , _request_other_status
            FROM cu_request
            WHERE core_id = new.request_id;

            -- 駐車場情報の取得
            SELECT
            parking_id
            , parking_name
            , parking_name_kana
            , latitude
            , longitude
            INTO
            _parking_id
            , _parking_name
            , _parking_name_kana
            , _latitude
            , _longitude
            FROM cu_parking
            WHERE core_id = new.parking_id;

            -- 駐車場空台数の取得
            SELECT r.request_parking_space_qty
            INTO _request_parking_space_qty
            FROM request_parking r
            WHERE r.request_id = new.request_id
            AND r.parking_id = new.parking_id;

            -- 支店情報の取得
            SELECT
            branch_id
            INTO
            _branch_id
            FROM cu_branch
            WHERE core_id = new.user_branch_id;

            -- 見積IDの取得
            SELECT estimate_id
            INTO   _estimate_id
            FROM   cu_estimate
            WHERE  core_id = new.id;


            -- すでに連携済みのデータが存在する場合は更新を行う
            IF _estimate_id IS NOT NULL THEN

            -- 見積データを更新
            UPDATE cu_estimate
            SET
                estimate_natural_id         = new.estimate_natural_id
            , request_id                  = _request_id
            , project_id                  = _project_id
            , parking_id                  = _parking_id
            , branch_id                   = _branch_id
            , estimate_status             =
                case                                          -- 見積ステータス
                when new.estimate_status = 3 AND cu_estimate.estimate_status  = 1 then 1   -- 3:調査見積送付済み → 1: 受注待ち
                when new.estimate_status = 3 AND cu_estimate.estimate_status  = 2 then 2   -- 3:調査見積送付済み → 2: 発注書受領
                when new.estimate_status = 4 then 4           -- 4:受注 → 4:受注
                when new.estimate_status = 5 then 5           -- 5:確定見積送付済 → 5:確定見積送付済
                when new.estimate_status = 6 then 8           -- 6:失注（手動）   → 8:キャンセル
                when new.estimate_status = 7 then 9           -- 7:失注（自動）   → 9:キャンセル
                when new.estimate_status = 8 then 8           -- 8:請求キャンする → 8:キャンセル
                else estimate_status                          -- 変更なし
                end
            , estimate_expire_date             = new.estimate_expire_date
            , estimate_cancel_check_flag       = new.estimate_cancel_check_flag
            , estimate_cancel_check_date       = new.estimate_cancel_check_date
            , survey_parking_name              = new.survey_parking_name
            , survey_capacity_qty              = new.survey_capacity_qty                     -- 調査見積見積情報：見積台数
            , survey_site_distance_minute      = new.survey_site_distance_minute
            , survey_site_distance_meter       = new.survey_site_distance_meter
            , latitude                         = _latitude                                   -- 駐車場緯度
            , longitude                        = _longitude                                  -- 駐車場経度
            , survey_parking_prefecture        = new.survey_parking_prefecture               -- 調査見積駐車場所在地：都道府県コード
            , survey_parking_city              = new.survey_parking_city                     -- 調査見積駐車場所在地：市区町村
            , survey_parking_address           = new.survey_parking_address                  -- 調査見積駐車場所在地：番地（町名以降）
            , survey_parking_address_contract  = new.survey_parking_address_contract         -- 調査見積駐車場所在地：土地の地番（契約書記載の所在地）
            , survey_parking_parallel_type     = new.survey_parking_parallel_type            -- 調査見積駐車場確認項目：駐車方法
            , survey_capacity_flag_car         = new.survey_capacity_flag_car                -- 調査見積車両制限：収容可能車種・ワゴン車フラグ
            , survey_capacity_flag_light_truck = new.survey_capacity_flag_light_truck        -- 調査見積車両制限：収容可能車種・軽トラックフラグ
            , survey_capacity_flag_truck       = new.survey_capacity_flag_truck              -- 調査見積車両制限：収容可能車種・2tトラックフラグ
            , survey_capacity_flag_other       = new.survey_capacity_flag_other              -- 調査見積車両制限：収容可能車種・その他フラグ
            , survey_capacity_type_other       = new.survey_capacity_type_other              -- 調査見積車両制限：その他詳細
            , survey_capacity_special_request  = new.survey_capacity_special_request         -- 調査見積車両制限：特別条件
            , survey_pay_unit_type_day         = new.survey_pay_unit_type_day                -- 調査見積駐車場情報：日割可否
            , survey_pay_unit_type_month       = new.survey_pay_unit_type_month              -- 調査見積駐車場情報：通し可否
            , survey_parking_rent              = new.survey_parking_rent                     -- 調査見積見積金額：賃料（月単価）
            , survey_parking_rent_profit       = new.survey_parking_rent_profit              -- 調査見積見積金額：上乗せ額
            , survey_commission                = new.survey_commission                       -- 調査見積見積金額：手数料
            , survey_commission_discount_rate  = new.survey_commission_discount_rate         -- 調査見積見積金額：手数料割引率
            , survey_commission_discount_amt   = new.survey_commission_discount_amt          -- 調査見積見積金額：実質手数料
            , survey_key_money                 = new.survey_key_money                        -- 調査見積見積金額：礼金
            , survey_subtotal_amt              = new.survey_subtotal_amt                     -- 調査見積見積金額：見積小計
            , survey_fraction_amt_flag         = new.survey_fraction_amt_flag                -- 調査見積見積金額：端数調整フラグ
            , survey_adjustment_amt            = new.survey_adjustment_amt                   -- 調査見積見積金額：調整金額
            , survey_discount_amt              = new.survey_discount_amt                     -- 調査見積見積金額：値引額
            , survey_tax_amt                   = new.survey_tax_amt                          -- 調査見積見積金額：税額
            , survey_total_amt                 = new.survey_total_amt                        -- 調査見積見積金額：見積合計
            , cannot_extention_flag            = new.cannot_extention_flag                   -- 延長不可フラグ
            , survey_available_start_date      = new.survey_available_start_date             -- 見積利用開始日
            , survey_available_end_date        = new.survey_available_end_date               -- 見積利用終了日
            , order_date                       = new.purchase_order_upload_date              -- 発注書アップロード日
            , estimate_change_flag             = new.estimate_change_flag                    -- 見積内容変更フラグ
            , request_parking_space_qty        = _request_parking_space_qty                  -- 調査駐車場空台数
            , order_start_date                 = new.order_start_date                        -- 発注利用開始日
            , order_end_date                   = new.order_end_date                          -- 発注利用終了日
            , order_capacity_qty               = new.order_capacity_qty                      -- 発注台数
            , order_amt                        = new.order_amt                               -- 発注概算金額
            , order_contact_memo               = new.order_contact_memo                      -- 発注連絡事項
            , survey_tax_in_flag               = new.survey_tax_in_flag                      -- 調査見積見積金額：税込みフラグ
            , update_system_type               = 1
            , update_user_id                   = 0
            , status                           = ISNULL( new.delete_timestamp )
            , contact_memo                     = new.contact_memo
        --       , cs_document_id                = new.estimate_document_id
        --       , cs_file_id                    = new.estimate_file_id
            WHERE estimate_id = _estimate_id;

            -- 受注時に未承認だった場合の処理
            IF new.estimate_status = 4  THEN

                -- 申請対象かの確認
                IF _request_type IN ( 0,1,2,3 ) AND
                EXISTS ( SELECT 1
                FROM cu_customer_option cco
                INNER JOIN cu_project cp ON cp.customer_id = cco.customer_id
                WHERE cco.plan_type = 1
                AND cco.approval = TRUE
                AND cp.project_id = _project_id ) THEN

                -- 申請対象の顧客の場合、申請データの確認
                SELECT application_id, application_status
                INTO _application_id, _application_status
                FROM cu_application
                WHERE request_id = _request_id;

                -- 申請データが存在しない場合
                IF _application_id IS NULL THEN

                    -- 承認済み申請データを作成する
                    INSERT INTO cu_application (
                        request_id
                    , application_user_id
                    , approval_user_id
                    , approval_date
                    , application_status
                    , application_comment
                    , approval_comment
                    , update_user_id
                    , update_system_type
                    ) VALUES (
                        _reques_id
                    , 0
                    , 0
                    , CURRENT_TIMESTAMP
                    , 2
                    , 'LANDMARKにおいて発注書受領に伴い、自動申請'
                    , 'LANDMARKにおいて発注書受領に伴い、自動承認'
                    , 0
                    , 1
                    );

                -- 申請データが存在する場合
                ELSE

                    -- 承認済みでは無い場合
                    IF _application_status <> 2 THEN

                    -- 申請データを承認済みに更新する
                    UPDATE cu_application
                        SET
                        application_status = 2,
                        approval_comment = 'LANDMARKにおいて発注書受領に伴い、自動承認',
                        approval_date = CURRENT_TIMESTAMP,
                        approval_user_id = 0,
                        update_user_id = 1,
                        update_system_type = 1
                    WHERE application_id = _application_id;

                    END IF;
                END IF;
                END IF;
            END IF;

            -- 契約データが存在する場合
            IF EXISTS ( SELECT 1 FROM cu_contract WHERE core_id = new.id ) THEN

                -- 契約データを更新
                UPDATE cu_contract
                SET
                project_id                      = _project_id
                , estimate_id                     = _estimate_id
                , parking_id                      = _parking_id
                , branch_id                       = _branch_id
                , contract_status                 =
                case
                    when new.estimate_status = 7 then 7
                    else contract_status
                end
                , parking_name                    = _parking_name
                , parking_name_kana               = _parking_name_kana
                , quote_capacity_qty              = new.quote_capacity_qty
                , quote_subtotal_amt              = new.quote_subtotal_amt
                , quote_tax_amt                   = new.quote_tax_amt
                , quote_total_amt                 = new.quote_total_amt
                , purchase_order_upload_date      = new.purchase_order_upload_date
                , purchase_order_register_type    = new.purchase_order_register_type
                , purchase_order_check_flag       = new.purchase_order_check_flag
                , order_schedule_date             = new.order_schedule_date
                , order_process_date              = new.order_process_date
                , quote_available_start_date      = new.quote_available_start_date
                , quote_available_end_date        = new.quote_available_end_date
                , extension_type                  = extension_type
                , update_system_type              = 1
                , update_user_id                  = 0
                , status                          = ISNULL( new.delete_timestamp )
                WHERE core_id = new.id;

            -- 契約データが存在しない場合
            ELSE

                -- 契約データの作成
                -- 契約データの作成前でキャンセルの場合は契約データは作成しない
                -- その他依頼の場合は、step1以降にステータスが進んだ場合に、契約データを作成する
                IF new.estimate_status = 5 OR ( new.estimate_status = 4 AND _request_type IN (4,5,6) AND _request_other_status <> 0 ) THEN

                INSERT INTO cu_contract(
                    core_id
                , project_id
                , estimate_id
                , parking_id
                , branch_id
                , contract_status
                , parking_name
                , parking_name_kana
                , quote_capacity_qty
                , quote_subtotal_amt
                , quote_tax_amt
                , quote_total_amt
                , purchase_order_upload_date
                , purchase_order_register_type
                , purchase_order_check_flag
                , order_schedule_date
                , order_process_date
                , quote_available_start_date
                , quote_available_end_date
                , extension_type
                , create_system_type
                , create_user_id
                , status
                )
                VALUES (
                    new.id                                        -- 基幹システム連携ID
                , _project_id                                   -- 工事ID
                , _estimate_id                                  -- 見積ID
                , _parking_id                                   -- 駐車場ID
                , _branch_id                                    -- 支店ID
                , case
                    when _request_type IN (4,5,6) AND _request_other_status = 6 then 2
                    else 2
                    end                                           -- 契約ステータス  2:契約書準備中, 5:契約準備完了
                , _parking_name                                 -- 駐車場名
                , _parking_name_kana                            -- 駐車場名：カナ
                , new.quote_capacity_qty                        -- 確定見積台数
                , new.quote_subtotal_amt                        -- 確定見積：見積小計
                , new.quote_tax_amt                             -- 確定見積：税額
                , new.quote_total_amt                           -- 確定見積：合計額
                , new.purchase_order_upload_date                -- 発注書アップロード日
                , new.purchase_order_register_type              -- 発注書アップロードシステム
                , new.purchase_order_check_flag                 -- 発注書確認フラグ
                , new.order_schedule_date                       -- 受注予定日
                , new.order_process_date                        -- 受注処理日
                , new.quote_available_start_date                -- 契約開始日
                , new.quote_available_end_date                  -- 契約終了日
                , 0                                             -- 契約延長区分
                , 1                                             -- 固定値：基幹システム
                , 0                                             -- 固定値：システム自動連携
                , ISNULL( new.delete_timestamp )                -- ステータス
                );
                END IF;
            END IF;

            ELSE

            -- 調査見積送付済みの場合のみ、見積データを作成する
            IF new.estimate_status IN ( 3,4,5 ) THEN

                -- 見積データの作成
                INSERT INTO cu_estimate(
                core_id
                , estimate_natural_id
                , request_id
                , project_id
                , parking_id
                , branch_id
                , estimate_status
                , estimate_expire_date
                , estimate_cancel_check_flag
                , estimate_cancel_check_date
                , survey_parking_name
                , survey_capacity_qty
                , survey_site_distance_minute
                , survey_site_distance_meter
                , latitude
                , longitude
                , survey_parking_prefecture
                , survey_parking_city
                , survey_parking_address
                , survey_parking_address_contract
                , survey_parking_parallel_type
                , survey_capacity_flag_car
                , survey_capacity_flag_light_truck
                , survey_capacity_flag_truck
                , survey_capacity_flag_other
                , survey_capacity_type_other
                , survey_capacity_special_request
                , survey_tax_in_flag
                , survey_pay_unit_type_day
                , survey_pay_unit_type_month
                , survey_parking_rent
                , survey_parking_rent_profit
                , survey_commission
                , survey_commission_discount_rate
                , survey_commission_discount_amt
                , survey_key_money
                , survey_subtotal_amt
                , survey_fraction_amt_flag
                , survey_adjustment_amt
                , survey_discount_amt
                , survey_tax_amt
                , survey_total_amt
                , cannot_extention_flag
                , survey_available_start_date
                , survey_available_end_date
                , request_parking_space_qty
                , order_date
                , estimate_change_flag
                , order_start_date
                , order_end_date
                , order_capacity_qty
                , order_amt
                , order_contact_memo
                , contact_memo
        --      , cs_document_id,
        --      , cs_file_id
                , create_system_type
                , create_user_id
                , status
            )
            VALUES (
                new.id                                        -- 基幹システム連携ID
                , new.estimate_natural_id                       -- 見積NO
                , _request_id                                   -- 見積依頼ID
                , _project_id                                   -- 工事ID
                , _parking_id                                   -- 駐車場ID
                , _branch_id                                    -- 支店ID
                , case                                          -- 見積ステータス
                    when new.estimate_status = 3 then 1         -- 3:調査見積送付済み → 1: 受注待ち
                    when new.estimate_status = 4 then 4         -- 4:受注 → 4:受注
                    when new.estimate_status = 5 then 5         -- 5:確定見積送付済 → 5:確定見積送付済
                    when new.estimate_status = 6 then 8         -- 6:失注（手動）   → 8:キャンセル
                    when new.estimate_status = 7 then 9         -- 7:失注（自動）   → 9:キャンセル
                    when new.estimate_status = 8 then 8         -- 8:請求キャンする → 8:キャンセル
                    else estimate_status                        -- 変更なし
                end
                , new.estimate_expire_date                      -- 見積有効期限日
                , new.estimate_cancel_check_flag                -- 見積キャンセル確認フラグ
                , new.estimate_cancel_check_date                -- 見積キャンセル確認日
                , new.survey_parking_name                       -- 調査見積駐車場情報：駐車場名
                , new.survey_capacity_qty                       -- 調査見積見積情報：見積台数
                , new.survey_site_distance_minute               -- 調査見積見積情報：現場距離（分）
                , new.survey_site_distance_meter                -- 調査見積見積情報：現場距離（メートル）
                , _latitude                                     -- 駐車場緯度
                , _longitude                                    -- 駐車場経度
                , new.survey_parking_prefecture                 -- 調査見積駐車場所在地：都道府県コード
                , new.survey_parking_city                       -- 調査見積駐車場所在地：市区町村
                , new.survey_parking_address                    -- 調査見積駐車場所在地：番地（町名以降）
                , new.survey_parking_address_contract           -- 調査見積駐車場所在地：土地の地番（契約書記載の所在地）
                , new.survey_parking_parallel_type              -- 調査見積駐車場確認項目：駐車方法
                , new.survey_capacity_flag_car                  -- 調査見積車両制限：収容可能車種・ワゴン車フラグ
                , new.survey_capacity_flag_light_truck          -- 調査見積車両制限：収容可能車種・軽トラックフラグ
                , new.survey_capacity_flag_truck                -- 調査見積車両制限：収容可能車種・2tトラックフラグ
                , new.survey_capacity_flag_other                -- 調査見積車両制限：収容可能車種・その他フラグ
                , new.survey_capacity_type_other                -- 調査見積車両制限：その他詳細
                , new.survey_capacity_special_request           -- 調査見積車両制限：特別条件
                , new.survey_tax_in_flag                        -- 調査見積見積金額：税込みフラグ
                , new.survey_pay_unit_type_day                  -- 調査見積駐車場情報：日割可否
                , new.survey_pay_unit_type_month                -- 調査見積駐車場情報：通し可否
                , new.survey_parking_rent                       -- 調査見積見積金額：賃料（月単価）
                , new.survey_parking_rent_profit                -- 調査見積見積金額：上乗せ額
                , new.survey_commission                         -- 調査見積見積金額：手数料
                , new.survey_commission_discount_rate           -- 調査見積見積金額：手数料割引率
                , new.survey_commission_discount_amt            -- 調査見積見積金額：実質手数料
                , new.survey_key_money                          -- 調査見積見積金額：礼金
                , new.survey_subtotal_amt                       -- 調査見積見積金額：見積小計
                , new.survey_fraction_amt_flag                  -- 調査見積見積金額：端数調整フラグ
                , new.survey_adjustment_amt                     -- 調査見積見積金額：調整金額
                , new.survey_discount_amt                       -- 調査見積見積金額：値引額
                , new.survey_tax_amt                            -- 調査見積見積金額：税額
                , new.survey_total_amt                          -- 調査見積見積金額：見積合計
                , new.cannot_extention_flag                     -- 延長不可フラグ
                , new.survey_available_start_date               -- 見積利用開始日
                , new.survey_available_end_date                 -- 見積利用終了日
                , _request_parking_space_qty                    -- 調査駐車場空台数
                , new.purchase_order_upload_date                -- 発注書アップロード日
                , new.estimate_change_flag                      -- 見積内容変更フラグ
                , new.order_start_date                          -- 発注利用開始日
                , new.order_end_date                            -- 発注利用終了日
                , new.order_capacity_qty                        -- 発注台数
                , new.order_amt                                 -- 発注概算金額
                , new.order_contact_memo                        -- 発注連絡事項
                , new.contact_memo                              -- 連絡メモ
        --      , new.estimate_document_id                      -- クラウドサイン連携用ドキュメントID
        --      , new.estimate_file_id                          -- クラウドサイン連携用ファイルID
            , 1                                               -- 固定値：基幹システム
            , 0                                               -- 固定値：システム自動連携
            , ISNULL( new.delete_timestamp )                  -- ステータス
            );


                -- 契約データの作成
                -- 契約データの作成前でキャンセルの場合は契約データは作成しない
                IF new.estimate_status = 5 THEN

                INSERT INTO cu_contract(
                    core_id
                , project_id
                , estimate_id
                , parking_id
                , branch_id
                , contract_status
                , parking_name
                , parking_name_kana
                , quote_capacity_qty
                , quote_subtotal_amt
                , quote_tax_amt
                , quote_total_amt
                , purchase_order_upload_date
                , purchase_order_register_type
                , purchase_order_check_flag
                , order_schedule_date
                , order_process_date
                , quote_available_start_date
                , quote_available_end_date
                , extension_type
                , create_system_type
                , create_user_id
                , status
                )
                VALUES (
                    new.id                                        -- 基幹システム連携ID
                , _project_id                                   -- 工事ID
                , _estimate_id                                  -- 見積ID
                , _parking_id                                   -- 駐車場ID
                , _branch_id                                    -- 支店ID
                , 2                                             -- 契約ステータス  2:契約書準備中
                , _parking_name                                 -- 駐車場名
                , _parking_name_kana                            -- 駐車場名：カナ
                , new.quote_capacity_qty                        -- 確定見積台数
                , new.quote_subtotal_amt                        -- 確定見積：見積小計
                , new.quote_tax_amt                             -- 確定見積：税額
                , new.quote_total_amt                           -- 確定見積：合計額
                , new.purchase_order_upload_date                -- 発注書アップロード日
                , new.purchase_order_register_type              -- 発注書アップロードシステム
                , new.purchase_order_check_flag                 -- 発注書確認フラグ
                , new.order_schedule_date                       -- 受注予定日
                , new.order_process_date                        -- 受注処理日
                , new.quote_available_start_date                -- 契約開始日
                , new.quote_available_end_date                  -- 契約終了日
                , 0                                           -- 契約延長区分
                , 1                                           -- 固定値：基幹システム
                , 0                                           -- 固定値：システム自動連携
                , ISNULL( new.delete_timestamp )              -- ステータス
                );
                END IF;
            END IF;
            END IF;
        END IF;
        END;
        ";
    }
}
