<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateTrigger20230104 extends Migration
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
        DROP TRIGGER IF EXISTS ins_estimate;

        CREATE TRIGGER ins_estimate AFTER INSERT ON estimate FOR EACH ROW
        BEGIN

        DECLARE _request_id, _project_id, _parking_id, _branch_id, _estimate_id, _request_parking_space_qty INT;
        DECLARE _parking_name, _parking_name_kana VARCHAR(255);
        DECLARE _latitude decimal(17,15);
        DECLARE _longitude decimal(18,15);
        DECLARE _contract_status, _request_box_status SMALLINT;
        
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
        
        -- 見積依頼BOXの状況を確認する。
        SELECT request_box_status
        INTO _request_box_status
        FROM request
        WHERE id = new.request_id;

            -- 調査見積送付済みの場合のみ、見積データを作成する
            IF ( new.estimate_status = 3 AND _request_box_status = 5 ) OR new.estimate_status IN ( 4,5 ) THEN
            
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
            
            -- 新受注対応により契約書送信タイミングが変更
            -- 契約書の送付が不要な場合は自動的に契約ステータスを４に更新する。それ以外は契約書返送待ちにする 
            SELECT 
                CASE contract_create_flag
                WHEN 0 THEN 3 -- 契約書返送待ち
                WHEN 1 THEN 4 -- 契約中
                ELSE        2 -- 契約書準備中
                END
            INTO _contract_status
            FROM customer_branch cb
            WHERE cb.id = new.customer_branch_id;
            
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
                , _contract_status                              -- 契約ステータス  
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


        -- トリガー： 見積（更新） BEFORE 更新日：22/12/28
        DROP TRIGGER IF EXISTS upd_bf_estimate;

        CREATE TRIGGER upd_bf_estimate BEFORE UPDATE ON estimate FOR EACH ROW
        BEGIN

        DECLARE _contact_memo TEXT;
        
        IF old.purchase_order_check_flag = 0 AND new.purchase_order_check_flag = 1 THEN

            SELECT  CONCAT ( '発注書を確認しました。',
            DATE_FORMAT( getCalcBusinessDay( current_date,
            CASE
                WHEN holiday THEN 0                  -- 休日の場合
                WHEN hour( now())+9 <= 15 THEN 0     -- 営業時間内の場合
                ELSE 1                               -- 上記以外の場合、翌営業日
            END ), '%m月%d日'),
            CASE 
                WHEN holiday THEN 'AM'                -- 休日の場合
                WHEN hour( now())+9 <= 8  THEN 'AM'    -- 営業時間内の場合
                WHEN hour( now())+9 <= 15 THEN 'PM'   -- 営業時間内の場合
                ELSE 'AM'                                -- 上記以外の場合、翌営業日
            END,
            ' までに状況を更新します' )
            INTO _contact_memo
            FROM cu_calendar 
            WHERE calendar_day = DATE( ADDTIME(current_timestamp, '09:00:00' ));

            SET new.contact_memo = _contact_memo;
        
        END IF;
        
        -- 受注済みの場合、区画図手配中のメッセージに変更する
        IF new.estimate_status = 4 AND old.estimate_status <> 4 THEN
        
            SELECT 
            CONCAT( '受注完了しました。止める場所の区画図を手配中です。',
                DATE_FORMAT( getCalcBusinessDay( current_date, 
                CASE
                    WHEN holiday THEN 0                  -- 休日の場合
                    WHEN hour( now())+9 <= 18 THEN 0     -- 営業時間内の場合
                    ELSE 1                               -- 上記以外の場合、翌営業日
                END ), '%m月%d日'),
                'までにお送りします。' )
            INTO _contact_memo
            FROM cu_calendar 
            WHERE calendar_day = DATE( ADDTIME(current_timestamp, '09:00:00' ));
        
            SET new.contact_memo = _contact_memo;
        
        END IF;


        END;

        -- トリガー： 見積（更新） UPDATE 更新日：22/12/29
        -- DROP TRIGGER IF EXISTS upd_estimate;
        DROP TRIGGER IF EXISTS upd_af_estimate;

        CREATE TRIGGER upd_af_estimate AFTER UPDATE ON estimate FOR EACH ROW
        BEGIN

        DECLARE _request_id, _project_id, _parking_id, _branch_id, _estimate_id, _application_id, _request_parking_space_qty int;
        DECLARE _application_status, _request_type, _request_other_status, _contract_status, _request_box_status SMALLINT;
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
                        _request_id
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

                -- 新受注対応により契約書送信タイミングが変更
                -- 契約書の送付が不要な場合は自動的に契約ステータスを４に更新する。それ以外は契約書返送待ちにする 
                SELECT 
                    CASE contract_create_flag
                    WHEN 0 THEN 3 -- 契約書返送待ち
                    WHEN 1 THEN 4 -- 契約中
                    ELSE        2 -- 契約書準備中
                    END
                INTO _contract_status
                FROM customer_branch cb
                WHERE cb.id = new.customer_branch_id;
            
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
                    when _request_type IN (4,5,6) AND _request_other_status = 6 then 5
                    when _request_type IN (4,5,6) AND _request_other_status <> 6 then 4                                
                    else _contract_status
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
        
            -- 見積依頼BOXの状況を確認する。
            SELECT request_box_status
            INTO _request_box_status
            FROM request
            WHERE id = new.request_id;

            -- 調査見積送付済みの場合のみ、見積データを作成する
            IF ( new.estimate_status = 3 AND _request_box_status = 5 ) OR new.estimate_status IN ( 4,5 ) THEN

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

                -- 新受注対応により契約書送信タイミングが変更
                -- 契約書の送付が不要な場合は自動的に契約ステータスを４に更新する。それ以外は契約書返送待ちにする 
                SELECT 
                    CASE contract_create_flag
                    WHEN 0 THEN 3 -- 契約書返送待ち
                    WHEN 1 THEN 4 -- 契約中
                    ELSE        2 -- 契約書準備中
                    END
                INTO _contract_status
                FROM customer_branch cb
                WHERE cb.id = new.customer_branch_id;

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
                , _contract_status                              -- 契約ステータス
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
        -- トリガー： 見積依頼情報(登録) 更新日：22/12/29
        DROP TRIGGER IF EXISTS ins_cu_request;

        CREATE TRIGGER ins_cu_request  BEFORE INSERT ON cu_request FOR EACH ROW
        BEGIN

        DECLARE _customer_id, _customer_branch_id, _customer_user_id, _branch_id, _project_id, _request_cnt, _estimate_id, _request_id, _extend_estimate_id, _extend_cnt INT;
        DECLARE _customer_natural_id, _customer_branch_natural_id,  _customer_user_natural_id, _branch_natural_id, _project_natural_id, _request_natural_id, _estimate_natural_id, _extend_estimate_natural_id   VARCHAR(255);
        
        DECLARE _estimate_type  SMALLINT;
        DECLARE _contact_memo   TEXT;
        
        
        -- 利用開始日、終了日に時刻が入っている場合の切り捨て処理
        SET new.want_start_date = CAST( new.want_start_date AS date ), new.want_end_date = CAST( new.want_end_date AS date );
        
        -- 顧客システムによる更新の場合
        IF new.create_system_type = 2 THEN
            
            -- 工事情報の取得
            SELECT 
            p.id,
            p.project_natural_id
            INTO
            _project_id,
            _project_natural_id
            FROM project p
            INNER JOIN cu_project cp ON cp.core_id = p.id 
            WHERE cp.project_id = new.project_id;

            -- 初回が無い「追加」の場合の対応
            IF new.request_type = 1 AND NOT EXISTS ( SELECT 1 FROM cu_request WHERE project_id = new.project_id AND request_type = 0 ) THEN
            
            -- 依頼種別を 0:初回 に変更する
            SET new.request_type = 0;
            
            END IF;

            -- 依頼IDの作成
            -- 延長については基幹とは別ルールとなる
            SELECT COUNT(*)+1
            INTO _request_cnt
            FROM cu_request
            WHERE project_id = new.project_id 
            AND request_type = new.request_type;

            SET _request_natural_id = CONCAT( _project_natural_id,
            CASE new.request_type
                WHEN 0 THEN CONCAT( '-P', '00' )
                WHEN 1 THEN CONCAT( '-P', LPAD( _request_cnt, 2, 0 )) 
                WHEN 2 THEN CONCAT( '-E', LPAD( _request_cnt, 2, 0 )) 
                WHEN 3 THEN CONCAT( '-R', LPAD( _request_cnt, 2, 0 )) 
                WHEN 4 THEN CONCAT( '-L', LPAD( _request_cnt, 2, 0 )) 
                WHEN 5 THEN CONCAT( '-W', LPAD( _request_cnt, 2, 0 )) 
                WHEN 6 THEN CONCAT( '-M', LPAD( _request_cnt, 2, 0 )) 
                ELSE 'XXX'  -- 採番不能なケース（想定なし）
            END );
            
            -- 顧客、顧客支店、顧客担当者の取得
            SELECT 
            c.customer_id, 
            c.customer_natural_id,
            c.customer_branch_id,
            c.customer_branch_natural_id,
            c.id,
            c.customer_user_natural_id
            INTO 
            _customer_id, 
            _customer_natural_id,
            _customer_branch_id,
            _customer_branch_natural_id,
            _customer_user_id,
            _customer_user_natural_id
            FROM customer_user c
            INNER JOIN cu_customer_user cc ON cc.core_id = c.id
            INNER JOIN cu_project cp ON cp.customer_user_id = cc.customer_user_id
            WHERE cp.project_id = new.project_id;

            -- 支店情報を取得
            SELECT 
            id,
            user_branch_natural_id
            INTO 
            _branch_id,
            _branch_natural_id
            FROM user_branch ub
            INNER JOIN cu_branch cb ON cb.core_id = ub.id
            INNER JOIN cu_branch_area a ON a.branch_id = cb.branch_id
            INNER JOIN cu_project p ON a.prefecture = p.site_prefecture
            WHERE p.project_id = new.project_id;

            -- 連絡メモ
            IF new.request_type IN ( 0,1,2,3) THEN

            SELECT  CONCAT ( '登録を受け付けました。',
                        DATE_FORMAT( getCalcBusinessDay( current_date,
                        CASE
                            WHEN holiday THEN 0                  -- 休日の場合
                            WHEN hour( now())+9 <= 17 THEN 0     -- 営業時間内の場合
                            ELSE 1                               -- 上記以外の場合、翌営業日
                        END ), 
                        '%m月%d日'),
                        ' 18時までに見積を提示します。' )
                INTO _contact_memo
                FROM cu_calendar 
            WHERE calendar_day = DATE( ADDTIME(current_timestamp, '09:00:00' ));

            END IF;
            
            -- 調査依頼を作成
            INSERT INTO request( 
                create_timestamp
            , update_timestamp
            , ulid
            , request_natural_id
            , project_natural_id
            , customer_id
            , customer_natural_id
            , customer_branch_id
            , customer_branch_natural_id
            , customer_user_id
            , customer_user_natural_id
            , user_branch_natural_id
            , request_date
            , estimate_deadline
            , request_type
            , want_start_date
            , want_end_date
            , car_qty
            , light_truck_qty
            , truck_qty
            , other_car_qty
            , other_car_detail
            , request_other_deadline
            , request_other_start_date
            , request_other_end_date
            , request_other_qty
            , want_guide_type
            , want_guide_type_subcontract
            , cc_email
            , customer_other_request
            , survey_status
            , request_other_status
            , survey_request_date
            , register_type
            , update_type
            , cu_lastupdate_user_id
            , request_cancel_check_flag
            , lastupdate_user_id
            , project_id
            , user_branch_id
            , contact_memo
            ) VALUES (
                ADDTIME( CURRENT_TIMESTAMP, '9:00:00' )                          -- レコード作成日
            , ADDTIME( CURRENT_TIMESTAMP, '9:00:00' )                          -- レコード更新日
            , getULID()                                                -- ULID
            , _request_natural_id                                      -- 依頼ID
            , _project_natural_id                                      -- 工事ID
            , _customer_id                                             -- 顧客会社マスターテーブルID
            , _customer_natural_id                                     -- 顧客会社ID
            , _customer_branch_id                                      -- 顧客支店マスターテーブルID
            , _customer_branch_natural_id                              -- 顧客支店ID
            , _customer_user_id                                        -- 顧客担当者マスターテーブルID
            , _customer_user_natural_id                                -- 顧客担当者ID
            , _branch_natural_id                                       -- ランドマーク支店ID
            , CAST( new.request_date as date )                         -- 依頼受付日
            , IFNULL( new.response_request_date, getCalcBusinessDay( CURRENT_DATE, 4  ))  -- 見積提出期限／顧客希望が無い場合は4営業日後
            , new.request_type                                         -- 依頼種別
            , new.want_start_date                                      -- 利用期間：開始
            , new.want_end_date                                        -- 利用期間：終了
            , new.car_qty                                              -- 台数：乗用車（軽自動車・ハイエース等） 
            , new.light_truck_qty                                      -- 台数：軽トラック
            , new.truck_qty                                            -- 台数：2ｔトラック
            , new.other_car_qty                                        -- 台数：その他（計）
            , new.other_car_detail                                     -- その他詳細
            , new.request_other_deadline                               -- 着手期限
            , IFNULL( new.request_other_start_date, new.want_start_date )   -- 契約開始日
            , IFNULL (new.request_other_end_date, new.want_end_date )       -- 契約終了日
            , new.request_other_qty                                    -- 個数
            , new.want_guide_type                                      -- 案内方法
            , 1                                                        -- 案内方法：下請用 1:メール（固定値）
            , new.cc_email                                             -- 顧客が指定するCCメールアドレス
            , new.customer_other_request                               -- 顧客からの要望等
            , 0                                                        -- 調査ステータス （0:未調査）
            , 0                                                        -- その他作業ステータス（0:未着手）
            , getCalcBusinessDay( CURRENT_DATE, 0)                     -- 現地調査依頼(営業日換算で当日）
            , 1                                                        -- データ登録者種別（1:顧客登録データ)
            , 1                                                        -- データ更新者種別（1:顧客登録データ)
            , new.create_user_id                                       -- 顧客システム最終更新者
            , 0                                                        -- 依頼キャンセル確認フラグ（0:未確認）
            , 1                                                        -- システム利用者テーブルID：最終更新者
            , _project_id                                              -- 工事マスターテーブルID
            , _branch_id                                               -- ランドマーク支店マスターテーブルID
            , _contact_memo
            );

            -- 連携ID、見積依頼NOを取得
            SET _request_id = LAST_INSERT_ID();
            SET NEW.core_id = _request_id, 
                NEW.request_natural_id = _request_natural_id,
                NEW.contact_memo = _contact_memo;
            
            -- 依頼種別その他の場合の後続処理
            IF new.request_type IN ( 4, 5, 6 ) THEN
            
            SET _estimate_natural_id = CONCAT( _request_natural_id, '-01');
            
            -- 見積の作成
            INSERT INTO estimate (
                survey_available_start_date
            , survey_available_end_date
            , survey_capacity_qty
            , quote_available_start_date
            , quote_available_end_date
            , quote_capacity_qty                  -- 確定見積台数
            , estimate_status                     -- 見積ステータス 4:受注
            , survey_pay_unit_type_day            -- 調査見積駐車場情報：日割可否
            , survey_pay_unit_type_month          -- 調査見積駐車場情報：通し可否
            , survey_parking_parallel_type        -- 調査見積駐車場確認項目：駐車方法
            , payment_request_status              -- 仕入依頼ステータス 0:仕入依頼前
            , survey_capacity_qty_type            -- 単位 2:式
            , survey_term_month_flag              -- 調査見積見積情報：通し有無
            , survey_tax_in_flag                  -- 調査見積見積金額：税込みフラグ
            , survey_fraction_amt_flag            -- 調査見積見積金額：端数調整フラグ
            , quote_term_month_flag               -- 確定見積：通し有無
            , estimate_natural_id
            , project_id
            , project_natural_id
            , request_id
            , request_natural_id
            , customer_id
            , customer_natural_id
            , customer_branch_id
            , customer_branch_natural_id
            , customer_user_id
            , customer_user_natural_id
            , user_branch_id
            , user_branch_natural_id
            , lastupdate_user_id
            , ulid
            , create_timestamp
            , update_timestamp
            , register_type
            , update_type
            , cu_lastupdate_user_id
            , purchase_order_check_flag
            , estimate_cancel_check_flag
            , estimate_cancel_status
            , supplier_id
            , parking_id
            ) VALUES (
                IFNULL( new.request_other_start_date, new.want_start_date )  -- 調査見積期間・開始日
            , IFNULL( new.request_other_end_date , new.want_end_date )     -- 調査見積期間・終了日
            , new.request_other_qty                                        -- 見積台数
            , IFNULL( new.request_other_start_date, new.want_start_date )  -- 確定見積期間：開始日
            , IFNULL( new.request_other_end_date, new.want_end_date )      -- 確定見積期間：終了日
            , new.request_other_qty                                    -- 確定見積台数
            , 4                                                        -- 見積ステータス 4:受注
            , 0                                                        -- 調査見積駐車場情報：日割可否
            , 0                                                        -- 調査見積駐車場情報：通し可否
            , 0                                                        -- 調査見積駐車場確認項目：駐車方法
            , 0                                                        -- 仕入依頼ステータス 0:仕入依頼前
            , 2                                                        -- 単位 2:式
            , 1                                                        -- 調査見積見積情報：通し有無
            , 0                                                        -- 調査見積見積金額：税込みフラグ
            , 1                                                        -- 調査見積見積金額：端数調整フラグ
            , 1                                                        -- 確定見積：通し有無
            , _estimate_natural_id                                     -- 見積ID
            , _project_id                                              -- 工事情報テーブルID
            , _project_natural_id                                      -- 工事ID
            , NEW.core_id                                              -- 依頼テーブルID
            , _request_natural_id                                      -- 案件ID
            , _customer_id                                             -- 顧客会社マスタテーブルID
            , _customer_natural_id                                     -- 顧客会社ID
            , _customer_branch_id                                      -- 顧客支店マスタテーブルID
            , _customer_branch_natural_id                              -- 顧客支店ID
            , _customer_user_id                                        -- 顧客担当者マスターテーブルID
            , _customer_user_natural_id                                -- 顧客担当者ID
            , _branch_id                                               -- ランドマーク支店マスタテーブルID
            , _branch_natural_id                                       -- ランドマーク支店ID
            , 1                                                        -- システム利用者テーブルID：最終更新者
            , getULID()                                                -- ULID
            , ADDTIME( CURRENT_TIMESTAMP, '9:00:00' )                          -- レコード作成日
            , ADDTIME( CURRENT_TIMESTAMP, '9:00:00' )                          -- レコード作成日
            , 1                                                        -- データ登録者種別
            , 1                                                        -- データ更新者種別
            , new.create_user_id                                       -- 顧客システム最終更新者
            , 0                                                        -- 発注書確認フラグ 0:未確認
            , 1                                                        -- 見積キャンセル確認フラグ 1:確認済み
            , 0                                                        -- 見積キャンセル申請ステータス 0:未申請
            , 0                                                        -- 仕入先テーブルID
            , 0                                                        -- 駐車場マスタテーブルID
            );
            
            -- 見積テーブルIDの取得
            SET _estimate_id = LAST_INSERT_ID(), _estimate_type = 0;

            REPEAT

                -- 見積詳細の作成
                INSERT INTO estimate_detail (
                project_id
                , project_natural_id
                , request_id
                , request_natural_id
                , estimate_id
                , estimate_natural_id
                , user_branch_id
                , user_branch_natural_id
                , customer_id
                , customer_natural_id
                , customer_branch_id
                , customer_branch_natural_id
                , customer_user_id
                , customer_user_natural_id
                , estimate_type
                , estimate_seq_row
                , estimate_detail_qty
                , estimate_detail_name
                , lastupdate_user_id
                , ulid
                , create_timestamp
                , update_timestamp
                , register_type
                , update_type
                , cu_lastupdate_user_id
                ) VALUES (
                _project_id                                   -- 工事情報テーブルID
                , _project_natural_id                           -- 工事ID
                , new.core_id                                   -- 依頼テーブルID
                , _request_natural_id                           -- 案件ID
                , _estimate_id                                  -- 見積テーブルID
                , _estimate_natural_id                          -- 見積ID
                , _branch_id                                    -- ランドマーク支店マスタテーブルID
                , _branch_natural_id                            -- ランドマーク支店マスタテールID
                , _customer_id                                  -- 顧客会社マスタテーブルID
                , _customer_natural_id                          -- 顧客会社ID
                , _customer_branch_id                           -- 顧客支店マスタテーブルID
                , _customer_branch_natural_id                   -- 顧客支店ID
                , _customer_user_id                             -- 顧客担当者マスタテーブルID
                , _customer_user_natural_id                     -- 顧客担当者ID
                , _estimate_type                                -- 見積種別 0:調査見積明細行、1:確定見積もり明細行、2:独自請求書用明細行
                , 1                                             -- 見積明細行番号
                , new.request_other_qty                         -- 見積明細：台数
                , CASE new.request_type
                    WHEN 4 THEN '駐車場調査費用'
                    WHEN 5 THEN '電線移設代行サービス'
                    WHEN 6 THEN '取扱説明書収納代行サービス'
                    ELSE NULL
                END                                           -- 見積明細：件名
                , 1                                             -- システム利用者テーブルID：最終更新者
                , getULID()                                     -- ULID
                , ADDTIME( CURRENT_TIMESTAMP, '9:00:00' )               -- レコード作成日
                , ADDTIME( CURRENT_TIMESTAMP, '9:00:00' )               -- レコード作成日
                , 1                                             -- データ登録者種別
                , 1                                             -- データ更新者種別
                , new.create_user_id                            -- 顧客システム最終更新者
                );
            
                SET _estimate_type = _estimate_type + 1;
                    
            UNTIL  _estimate_type > 2 END REPEAT;

            END IF;
            
        END IF;
        END;
        -- トリガー： 見積依頼対象駐車場（登録） ：22/12/29
        DROP TRIGGER IF EXISTS ins_cu_request_parking;

        CREATE TRIGGER ins_cu_request_parking  BEFORE INSERT ON cu_request_parking FOR EACH ROW
        BEGIN

        DECLARE _project_id, _request_id, _customer_id, _customer_branch_id, _customer_user_id, _user_branch_id, _extend_estimate_id, _parking_id, _supplier_id  INT;
        DECLARE _project_natural_id, _request_natural_id, _customer_natural_id, _customer_branch_natural_id, _customer_user_natural_id, _user_branch_natural_id, _extend_estimate_natural_id, _supplier_natural_id VARCHAR(255);
        DECLARE _site_distance_minute DECIMAL(6,2);
        DECLARE _site_distance_meter DECIMAL(8,2);
        DECLARE _request_start_date DATETIME;
        
        -- 顧客システムによる登録の場合
        IF new.create_system_type = 2 THEN
        
            -- 見積依頼IDから基幹システムの各情報を取得
            SELECT
                r.project_id
            , r.project_natural_id
            , r.id
            , r.request_natural_id
            , r.customer_id
            , r.customer_natural_id
            , r.customer_branch_id
            , r.customer_branch_natural_id
            , r.customer_user_id
            , r.customer_user_natural_id
            , r.user_branch_id
            , r.user_branch_natural_id
            INTO
                _project_id
            , _project_natural_id
            , _request_id
            , _request_natural_id
            , _customer_id
            , _customer_natural_id
            , _customer_branch_id
            , _customer_branch_natural_id
            , _customer_user_id
            , _customer_user_natural_id
            , _user_branch_id
            , _user_branch_natural_id
            FROM cu_request cr
            INNER JOIN request r ON r.id = cr.core_id
            WHERE cr.request_id = new.request_id;
        
        
            -- 延長元見積ID（基幹）の取得
            SELECT
                e.id   
            , e.estimate_natural_id
            , e.parking_id
            , e.supplier_id
            , e.supplier_natural_id
            , e.survey_site_distance_minute
            , e.survey_site_distance_meter
            , DATE_ADD( e.quote_available_end_date,INTERVAL 1 DAY )  -- 確定見積期間：終了日の翌日を開始日にする
            INTO   
                _extend_estimate_id
            , _extend_estimate_natural_id
            , _parking_id
            , _supplier_id
            , _supplier_natural_id
            , _site_distance_minute
            , _site_distance_meter
            , _request_start_date
            FROM cu_estimate ce
            INNER JOIN estimate e ON e.id = ce.core_id
            WHERE ce.estimate_id = new.extend_estimate_id;

            -- 延長する駐車場情報を延長元見積IDを取得条件として登録
            INSERT INTO request_parking (
                request_id
                , parking_id
                , project_id
                , project_natural_id
                , request_natural_id
                , customer_id
                , customer_natural_id
                , customer_branch_id
                , customer_branch_natural_id
                , customer_user_id
                , customer_user_natural_id
                , supplier_id
                , supplier_natural_id
                , lastupdate_user_id
                , user_branch_id
                , user_branch_natural_id
                , extend_estimate_natural_id
                , ulid
                , create_timestamp
                , update_timestamp
                , register_type
                , update_type
                , cu_lastupdate_user_id
                , request_parking_status
                , route_map_parking_flag
                , site_distance_minute
                , site_distance_meter
                , request_capacity_qty
                , request_start_date
                , request_end_date
        
                ) VALUES ( 
                    _request_id
                , _parking_id
                , _project_id
                , _project_natural_id
                , _request_natural_id
                , _customer_id
                , _customer_natural_id
                , _customer_branch_id
                , _customer_branch_natural_id
                , _customer_user_id
                , _customer_user_natural_id
                , _supplier_id
                , _supplier_natural_id
                , 1
                , _user_branch_id
                , _user_branch_natural_id
                , _extend_estimate_natural_id
                , getULID()
                , ADDTIME( CURRENT_TIMESTAMP, '9:00:00' ) 
                , ADDTIME( CURRENT_TIMESTAMP, '9:00:00' ) 
                , 0
                , 0
                , new.create_user_id
                , 0
                , 1
                , _site_distance_minute
                , _site_distance_meter
                , new.request_capacity_qty
                , _request_start_date
                , new.request_end_date
            );
        
            -- 連携IDを取得
            SET new.core_id = LAST_INSERT_ID();
            
        END IF;
        
        END;
        -- トリガー： 見積（更新）AFTER 更新日：22/12/29
        -- DROP TRIGGER IF EXISTS upd_cu_estimate;
        DROP TRIGGER IF EXISTS upd_af_cu_estimate;

        CREATE TRIGGER upd_af_cu_estimate  AFTER UPDATE ON cu_estimate FOR EACH ROW
        BEGIN

        DECLARE _latest_pdf_purchase_order_url varchar(1024);
        
        -- 顧客向けシステムで更新した場合
        IF new.update_system_type = 2 THEN
        
            -- アップロードした発注書（捺印済）の取得
            SELECT file_path    
            INTO _latest_pdf_purchase_order_url
            FROM cu_file
            WHERE estimate_id = new.estimate_id
            AND file_type = 4
            AND file_detail_type = 402
            AND create_system_type = 2
            AND create_date = ( 
                select max( create_date ) 
                    from cu_file 
                    where estimate_id = new.estimate_id
                    and file_type = 4 
                    and file_detail_type = 402
                    and create_system_type = 2 );
                    
            -- 発注処理
            IF new.estimate_status = 2 THEN
            
            UPDATE estimate
                SET 
                    estimate_change_flag          = new.estimate_change_flag                    -- 見積内容変更フラグ
                , order_start_date              = new.order_start_date                        -- 発注利用開始日
                , order_end_date                = new.order_end_date                          -- 発注利用終了日
                , order_capacity_qty            = new.order_capacity_qty                      -- 発注台数
                , order_amt                     = new.order_amt                               -- 発注概算金額
                , order_contact_memo            = new.order_contact_memo                      -- 発注連絡事項
                , order_box_status              = 0                                           -- 受注BOXステータス 0:未着手
                , purchase_order_box_status     = 2                                           -- 発注書BOXステータス 2:完了
                , latest_pdf_purchase_order_url = _latest_pdf_purchase_order_url
                , purchase_order_upload_date    = ADDTIME( CURRENT_TIMESTAMP, '9:00:00' )
                , purchase_order_register_type  = 1           
                , update_timestamp              = ADDTIME( CURRENT_TIMESTAMP, '9:00:00' )
                , update_type                   = 1
                , cu_lastupdate_user_id         = new.update_user_id
                , lastupdate_user_id            = 1
                , contact_memo                  = new.contact_memo
            WHERE id = new.core_id;
            
            -- 失注処理（自動失注）
            ELSEIF new.estimate_status = 7 AND old.estimate_status <> 7 THEN
            
            UPDATE estimate
                SET 
                    estimate_change_flag          = new.estimate_change_flag                    -- 見積内容変更フラグ
                , order_start_date              = new.order_start_date                        -- 発注利用開始日
                , order_end_date                = new.order_end_date                          -- 発注利用終了日
                , order_capacity_qty            = new.order_capacity_qty                      -- 発注台数
                , order_amt                     = new.order_amt                               -- 発注概算金額
                , order_contact_memo            = new.order_contact_memo                      -- 発注連絡事項
                , estimate_status               = new.estimate_status
                , estimate_cancel_check_flag    = FALSE
                , update_timestamp              = ADDTIME( CURRENT_TIMESTAMP, '9:00:00' )
                , update_type                   = 1
                , cu_lastupdate_user_id         = new.update_user_id
                , lastupdate_user_id            = 1
                , contact_memo                  = new.contact_memo
            WHERE id = new.core_id;
            END IF;

        END IF;
        END;
        -- 顧客システム情報（更新） 更新日 2022/12/29
        DROP TRIGGER IF EXISTS upd_cu_customer_option;

        CREATE TRIGGER upd_cu_customer_option  BEFORE UPDATE ON cu_customer_option FOR EACH ROW
        BEGIN

        DECLARE _user_id INT;
        DECLARE _login_id VARCHAR(2048);
        DECLARE _customer_user_name VARCHAR(255);
        

        -- 基幹システムで更新した場合
        IF new.update_system_type = 1 THEN

            -- システム管理者の情報をもとに、システム管理者ID、システム管理者名をデータ上にセットする
            IF IFNULL( new.admin_user_login_id, '' ) = '' THEN
        
            SET new.admin_user_id = NULL, new.admin_user_name = NULL ;

            ELSE

            -- ログインIDからユーザーIDとユーザー名を取得（複数件存在する場合、最初の１件のみを取得する） 
            SELECT    user_id
                    , customer_user_name 
            INTO    _user_id
                    , _customer_user_name
            FROM cu_user
            WHERE login_id = new.admin_user_login_id
                AND customer_id = new.customer_id
                AND status
            LIMIT 1;

            SET new.admin_user_id = _user_id, new.admin_user_name = _customer_user_name;
            
            END IF;

        -- 顧客向けシステムで更新した場合
        ELSE

            -- ユーザーIDとログインIDを取得（複数件存在する場合、最初の１件のみを取得する） 
            SELECT    login_id
                    , customer_user_name 
            INTO    _login_id
                    , _customer_user_name
            FROM cu_user
            WHERE user_id = new.admin_user_id;

            SET new.admin_user_login_id = _login_id, new.admin_user_name = _customer_user_name;

        END IF;
        
        END;
        ";
    }
}
