<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateTrigger20210804 extends Migration
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
        
          DECLARE _request_id, _project_id, _parking_id, _branch_id, _estimate_id int;
          DECLARE _parking_name, _parking_name_kana varchar(255);
         
          -- 基幹システムで作成した場合
          IF new.update_type = 0 THEN
        
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
            -- 駐車場以外の見積の場合を後日検討
            IF new.estimate_status IN ( 3,4,5 ) THEN
            
              -- 駐車場情報の取得
              SELECT
                  parking_id
                , parking_name
                , parking_name_kana
              INTO
                  _parking_id
                , _parking_name
                , _parking_name_kana
              FROM cu_parking
              WHERE core_id = new.parking_id;
        
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
              , survey_tax_in_flag
              , survey_total_amt
              , create_system_type
              , create_user_id
              , status
             ) 
             VALUES (
                new.id                                        -- 基幹システム連携ID
              , _request_id                                   -- 見積依頼ID
              , _project_id                                   -- 工事ID
              , _parking_id                                   -- 駐車場ID
              , _branch_id                                    -- 支店ID
              , case                                          -- 見積ステータス
                  when new.estimate_status = 3 then 1           -- 3:調査見積送付済み → 1: 受注待ち
                  when new.estimate_status = 4 then 4           -- 4:受注 → 4:受注
                  when new.estimate_status = 5 then 5           -- 5:確定見積送付済 → 5:確定見積送付済         
                  when new.estimate_status = 7 then 7           -- 7:キャンセル → 7:キャンセル
                  when new.estimate_status = 8 then 8           -- 8:失注（手動） → 8:キャンセル
                  when new.estimate_status = 9 then 9           -- 9:失注（自動） → 9:キャンセル                                 
                  else 0                                        -- 0:未作成 
                end
              , new.estimate_expire_date                      -- 見積有効期限日
              , new.estimate_cancel_check_flag                -- 見積キャンセル確認フラグ
              , new.estimate_cancel_check_date                -- 見積キャンセル確認日
              , new.survey_parking_name                       -- 調査見積駐車場情報：駐車場名
              , new.survey_capacity_qty                       -- 調査見積見積情報：見積台数
              , new.survey_site_distance_minute               -- 調査見積見積情報：現場距離（分）
              , new.survey_site_distance_meter                -- 調査見積見積情報：現場距離（メートル）
              , new.survey_tax_in_flag                        -- 調査見積見積金額：税込みフラグ
              , new.survey_total_amt                          -- 調査見積見積金額：見積合計
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
                , 0                                           -- 契約延長区分
                , 1                                           -- 固定値：基幹システム
                , 0                                           -- 固定値：システム自動連携
                , ISNULL( new.delete_timestamp )              -- ステータス 
                );
              END IF; 
            END IF;
          END IF;
        END;

        DROP TRIGGER IF EXISTS upd_estimate;

        CREATE TRIGGER upd_estimate AFTER UPDATE ON estimate FOR EACH ROW
        BEGIN

        DECLARE _request_id, _project_id, _parking_id, _branch_id, _estimate_id int;
        DECLARE _parking_name, _parking_name_kana varchar(255);

        -- 基幹システムで更新した場合のみ実行
        IF new.update_type = 0 THEN

            -- 見積依頼情報の取得
            SELECT
            request_id
            , project_id
            INTO
            _request_id
            , _project_id
            FROM cu_request
            WHERE core_id = new.request_id;
        
            -- 駐車場情報の取得
            SELECT
            parking_id
            , parking_name
            , parking_name_kana
            INTO
            _parking_id
            , _parking_name
            , _parking_name_kana
            FROM cu_parking
            WHERE core_id = new.parking_id;

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
            IF EXISTS ( SELECT 1 FROM cu_estimate WHERE core_id = new.id ) THEN

            -- 見積データを更新
            UPDATE cu_estimate
            SET
                request_id                  = _request_id
            , project_id                  = _project_id
            , parking_id                  = _parking_id
            , branch_id                   = _branch_id
            , estimate_status             = 
                case                                          -- 見積ステータス
                when new.estimate_status = 3 then 1           -- 3:調査見積送付済み → 1: 受注待ち
                when new.estimate_status = 4 then 4           -- 4:受注 → 4:受注
                when new.estimate_status = 5 then 5           -- 5:確定見積送付済 → 5:確定見積送付済         
                when new.estimate_status = 7 then 7           -- 7:キャンセル  → 7:キャンセル
                when new.estimate_status = 8 then 8           -- 8:失注（手動）→ 8:キャンセル
                when new.estimate_status = 9 then 9           -- 9:失注（自動）→ 9:キャンセル                          
                else estimate_status                          -- 変更なし 
                end
            , estimate_expire_date        = new.estimate_expire_date  
            , estimate_cancel_check_flag  = new.estimate_cancel_check_flag
            , estimate_cancel_check_date  = new.estimate_cancel_check_date
            , survey_parking_name         = new.survey_parking_name
            , survey_capacity_qty         = new.survey_capacity_qty 
            , survey_site_distance_minute = new.survey_site_distance_minute
            , survey_site_distance_meter  = new.survey_site_distance_meter 
            , survey_tax_in_flag          = new.survey_tax_in_flag
            , survey_total_amt            = new.survey_total_amt
            , update_system_type          = 1
            , update_user_id              = 0
            , status                      = ISNULL( new.delete_timestamp )
            WHERE core_id = new.id;

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
            ELSE
        
            -- 調査見積送付済みの場合のみ、見積データを作成する
            -- 駐車場以外の見積の場合を後日検討
            IF new.estimate_status IN ( 3,4,5 ) THEN

                -- 見積データの作成
                INSERT INTO cu_estimate( 
                core_id
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
                , survey_tax_in_flag
                , survey_total_amt
                , create_system_type
                , create_user_id
                , status
                ) 
                VALUES (
                new.id                                        -- 基幹システム連携ID
                , _request_id                                   -- 見積依頼ID
                , _project_id                                   -- 工事ID
                , _parking_id                                   -- 駐車場ID
                , _branch_id                                    -- 支店ID
                , case                                          -- 見積ステータス
                    when new.estimate_status = 3 then 1           -- 3:調査見積送付済み → 1: 受注待ち
                    when new.estimate_status = 4 then 4           -- 4:受注 → 4:受注
                    when new.estimate_status = 5 then 5           -- 5:確定見積送付済 → 5:確定見積送付済         
                    when new.estimate_status = 7 then 7           -- 7:キャンセル → 7:キャンセル                          
                    else 0                                        -- 0:未作成 
                end
                , new.estimate_expire_date                      -- 見積有効期限日
                , new.estimate_cancel_check_flag                -- 見積キャンセル確認フラグ
                , new.estimate_cancel_check_date                -- 見積キャンセル確認日
                , new.survey_parking_name                       -- 調査見積駐車場情報：駐車場名
                , new.survey_capacity_qty                       -- 調査見積見積情報：見積台数
                , new.survey_site_distance_minute               -- 調査見積見積情報：現場距離（分）
                , new.survey_site_distance_meter                -- 調査見積見積情報：現場距離（メートル）
                , new.survey_tax_in_flag                        -- 調査見積見積金額：税込みフラグ
                , new.survey_total_amt                          -- 調査見積見積金額：見積合計
                , 1                                             -- 固定値：基幹システム
                , 0                                             -- 固定値：システム自動連携
                , ISNULL( new.delete_timestamp )                -- ステータス 
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


        DROP TRIGGER IF EXISTS ins_cu_request_parking;

        CREATE TRIGGER ins_cu_request_parking  AFTER INSERT ON cu_request_parking FOR EACH ROW
        BEGIN

        DECLARE _parking_id, _request_id, _project_id, _supplier_id, _user_branch_id, _extend_estimate_id
                , _customer_id, _customer_branch_id, _customer_user_id INT;
        DECLARE _project_natural_id, _request_natural_id, _supplier_natural_id,  _user_branch_natural_id, _extend_estimate_natural_id
                , _customer_natural_id, _customer_branch_natural_id, _customer_user_natural_id VARCHAR(255);

        -- 駐車場情報の取得
        SELECT cp.core_id, p.supplier_id, p.supplier_natural_id
        INTO _parking_id, _supplier_id, _supplier_natural_id
        FROM cu_parking cp
            INNER JOIN parking p ON p.id = cp.core_id
        WHERE parking_id = new.parking_id;
        
        -- 工事情報の取得
        SELECT 
            r.project_id, 
            r.project_natural_id,
            r.id,
            r.request_natural_id, 
            r.customer_id,
            r.customer_natural_id,
            r.customer_branch_id,
            r.customer_branch_natural_id,
            r.customer_user_id,
            r.customer_user_natural_id,
            r.user_branch_id,
            r.user_branch_natural_id,
            cr.extend_estimate_id
        INTO 
            _project_id,
            _project_natural_id,
            _request_id,
            _request_natural_id,
            _customer_id,
            _customer_natural_id,
            _customer_branch_id,
            _customer_branch_natural_id,
            _customer_user_id,
            _customer_user_natural_id,
            _user_branch_id,
            _user_branch_natural_id,
            _extend_estimate_id
        FROM cu_request cr
            INNER JOIN request r ON r.id = cr.core_id
        WHERE cr.request_id = new.request_id;
        
        -- 延長元見積ID の取得
        IF _extend_estimate_id IS NOT NULL THEN

            SELECT e.estimate_natural_id
            INTO _extend_estimate_natural_id
            FROM cu_estimate ce
            INNER JOIN estimate ON e.core_id = ce.estimate_id
            WHERE estimate_id = _extend_estimate_id;

        END IF;
        
        -- 依頼駐車場管理の登録
        INSERT INTO request_parking( 
            create_timestamp
            , update_timestamp
            , ulid
            , project_id
            , project_natural_id
            , request_id
            , request_natural_id
            , parking_id
            , customer_id
            , customer_natural_id
            , customer_branch_id
            , customer_branch_natural_id
            , customer_user_id
            , customer_user_natural_id
            , supplier_id
            , supplier_natural_id
            , user_branch_id
            , user_branch_natural_id
            , request_parking_status
            , route_map_parking_flag
            , extend_estimate_natural_id
            , lastupdate_user_id
            , want_parking_flag
            , register_type
            , update_type
            , cu_lastupdate_user_id
        ) 
        VALUES (
            CURRENT_TIMESTAMP
            , CURRENT_TIMESTAMP
            , getULID()
            , _project_id
            , _project_natural_id
            , _request_id
            , _request_natural_id
            , _parking_id
            , _customer_id
            , _customer_natural_id
            , _customer_branch_id
            , _customer_branch_natural_id
            , _customer_user_id
            , _customer_user_natural_id
            , _supplier_id
            , _supplier_natural_id
            , _user_branch_id
            , _user_branch_natural_id
            , 0
            , 1
            , _extend_estimate_natural_id
            , 1
            , 1
            , 1
            , 1
            , new.create_user_id
        );

        END;

        DROP TRIGGER IF EXISTS upd_cu_estimate;

        CREATE TRIGGER upd_cu_estimate  AFTER UPDATE ON cu_estimate FOR EACH ROW
        BEGIN

        -- 顧客向けシステムで更新した場合
        IF new.update_system_type = 2 THEN

            -- 発注処理
            IF new.estimate_status = 2 THEN
            UPDATE estimate
                SET 
                purchase_order_upload_date = CURRENT_TIMESTAMP,
                purchase_order_register_type = 1,           
                update_timestamp = CURRENT_TIMESTAMP,
                update_type = 1,
                lastupdate_user_id = 1
            WHERE id = new.core_id;
            
            -- 失注処理（自動失注）
            ELSEIF new.estimate_status = 7 THEN
            UPDATE estimate
                SET 
                estimate_status = new.estimate_status,
                update_timestamp = CURRENT_TIMESTAMP,
                update_type = 1,
                lastupdate_user_id = 1
            WHERE id = new.core_id;
            END IF;

        END IF;
        END;
        ";
    }
}
