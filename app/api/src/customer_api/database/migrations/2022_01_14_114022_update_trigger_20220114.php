<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateTrigger20220114 extends Migration
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
            
        -- 更新日：22/01/13
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
                when new.estimate_status = 7 then 7           -- 7:キャンセル  → 7:キャンセル
                when new.estimate_status = 8 then 8           -- 8:失注（手動）→ 8:キャンセル
                when new.estimate_status = 9 then 9           -- 9:失注（自動）→ 9:キャンセル                          
                else estimate_status                          -- 変更なし 
                end
            , estimate_expire_date          = new.estimate_expire_date  
            , estimate_cancel_check_flag    = new.estimate_cancel_check_flag
            , estimate_cancel_check_date    = new.estimate_cancel_check_date
            , survey_parking_name           = new.survey_parking_name
            , survey_capacity_qty           = new.survey_capacity_qty 
            , survey_site_distance_minute   = new.survey_site_distance_minute
            , survey_site_distance_meter    = new.survey_site_distance_meter 
            , survey_tax_in_flag            = new.survey_tax_in_flag
            , survey_total_amt              = new.survey_total_amt
            , update_system_type            = 1
            , update_user_id                = 0
            , status                        = ISNULL( new.delete_timestamp )
            , contact_memo                  = new.contact_memo
        --       , cs_document_id                = new.estimate_document_id
        --       , cs_file_id                    = new.estimate_file_id
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
                , survey_tax_in_flag
                , survey_total_amt
                , create_system_type
                , create_user_id
                , status
                , contact_memo
        --        , cs_document_id
        --        , cs_file_id
                ) 
                VALUES (
                new.id                                        -- 基幹システム連携ID
                , new.estimate_natural_id
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
                , new.contact_memo                              -- 連絡メモ
        --        , new.estimate_document_id                      -- クラウドサイン連携用ドキュメントID
        --        , new.estimate_file_id                          -- クラウドサイン連携用ファイルID
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
