<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateTrigger20210824 extends Migration
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
                    , contact_memo
                --      , cs_document_id,
                --      , cs_file_id
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
                    , new.contact_memo                              -- 連絡メモ
                --      , new.estimate_document_id                      -- クラウドサイン連携用ドキュメントID
                --      , new.estimate_file_id                          -- クラウドサイン連携用ファイルID
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

            DROP TRIGGER IF EXISTS upd_invoice;

            CREATE TRIGGER upd_invoice AFTER UPDATE ON invoice FOR EACH ROW
                BEGIN

                -- 基幹システムで更新した場合のみ実行
                IF new.update_type = 0 THEN

                    UPDATE cu_invoice
                    SET 
                        invoice_status = new.invoice_process_status -- 請求状況ステータス
                    , contact_memo = new.contact_memo             -- 連絡メモ
                    , update_system_type = 1                      -- 固定値：基幹システム
                    , update_user_id = 1                          -- 固定値：システム自動連携
                    WHERE 
                        core_invoice_id = new.id;

                    -- クラウドサイン連携用情報の更新
                    UPDATE cu_contract cc
                    INNER JOIN cu_invoice ci ON ci.contract_id = cc.contract_id
                    SET
                        update_system_type = 1                      -- 固定値：基幹システム
                    , update_user_id = 1                          -- 固定値：システム自動連携      
                --      , cs_document_id = new.contract_docuent_id    -- クラウドサイン連携用ドキュメントID
                --      , cs_file_id = new.contract_file_id           -- クラウドサイン連携用ファイルID
                    WHERE
                    ci.core_invoice_id = new.id;
                    
                END IF;
            END;

            DROP TRIGGER IF EXISTS ins_cu_project;

            CREATE TRIGGER ins_cu_project  BEFORE INSERT ON cu_project FOR EACH ROW
            BEGIN

                DECLARE _customer_id, _customer_branch_id, _customer_user_id, _branch_id INT;
                DECLARE _customer_natural_id, _customer_branch_natural_id,  _customer_user_natural_id, _branch_natural_id, _project_natural_id  VARCHAR(255);

                -- 顧客システムによる更新の場合
                IF new.create_system_type = 2 THEN

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
                    WHERE cc.customer_user_id = new.customer_user_id;
                
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
                    WHERE a.prefecture = new.site_prefecture;

                    -- 工事IDの生成
                    SELECT CONCAT(substr(current_date,3,2), 'C',  LPAD( count(*)+1, 7, 0))
                    INTO _project_natural_id 
                    FROM project 
                    WHERE substr(current_date,3,2) = substr(project_natural_id,1,2);
                
                    -- 工事情報の登録
                    INSERT INTO project (
                    project_natural_id,
                    customer_id,
                    customer_natural_id,
                    customer_branch_id,
                    customer_branch_natural_id,
                    customer_user_id,
                    customer_user_natural_id,
                    lastupdate_user_id,
                    user_branch_id,
                    user_branch_natural_id,
                    create_timestamp,
                    update_timestamp,
                    ulid,
                    construction_number,
                    site_name,
                    site_name_kana,
                    site_prefecture,
                    site_city,
                    site_address,
                    register_type,
                    cu_lastupdate_user_id,
                    latitude,
                    longitude
                    )
                    VALUES
                    (
                    _project_natural_id,                                                   -- 工事ID
                    _customer_id,                                                          -- 顧客マスターテーブルID
                    _customer_natural_id,                                                  -- 顧客ID
                    _customer_branch_id,                                                   -- 顧客支店マスターテーブルID
                    _customer_branch_natural_id,                                           -- 顧客支店ID
                    _customer_user_id,                                                     -- 顧客担当者マスターテーブルID
                    _customer_user_natural_id,                                             -- 顧客担当者ID
                    1,                                                                     -- システム利用者テーブルID：最終更新者
                    _branch_id,                                                            -- ランドマーク支店マスタテーブルID
                    _branch_natural_id,                                                    -- ランドマーク支店ID
                    ADDTIME( CURRENT_TIMESTAMP, 9 ),                                       -- レコード作成日
                    ADDTIME( CURRENT_TIMESTAMP, 9 ),                                       -- レコード更新日
                    getULID(),                                                             -- ULID
                    new.construction_number,                                               -- 工事番号
                    new.site_name,                                                         -- 現場名／邸名
                    new.site_name_kana,                                                    -- 現場名／邸名：カナ
                    new.site_prefecture,                                                   -- 現場所在地：都道府県コード
                    new.site_city,                                                         -- 現場所在地：市区町村
                    new.site_address,                                                      -- 現場所在地：番地（町名以降）
                    1,                                                                     -- データ登録者種別
                    new.create_user_id,                                                    -- 顧客システム最終更新者
                    new.latitude,                                                          -- 緯度
                    new.longitude                                                          -- 経度
                    );
                
                    -- 連携IDを取得
                    SET NEW.core_id = LAST_INSERT_ID();
                END IF;
            END;

            DROP TRIGGER IF EXISTS upd_cu_project;

            CREATE TRIGGER upd_cu_project  AFTER UPDATE ON cu_project FOR EACH ROW
            BEGIN

                -- 顧客システムによる更新の場合
                IF new.update_system_type = 2 THEN

                    UPDATE project 
                    SET
                        update_timestamp = ADDTIME( CURRENT_TIMESTAMP, 9 )
                    , construction_number = new.construction_number
                    , site_name = new.site_name
                    , site_name_kana = new.site_name_kana
                    , site_prefecture = new.site_prefecture
                    , site_city = new.site_city
                    , site_address = new.site_address
                    , latitude = new.latitude
                    , longitude = new.longitude
                    , update_type = 1
                    , cu_lastupdate_user_id = new.update_user_id           -- 顧客システム最終更新者
                    , lastupdate_user_id = 1
                    WHERE
                    id = new.core_id;

                END IF;
            END;

            DROP TRIGGER IF EXISTS ins_cu_request;

            CREATE TRIGGER ins_cu_request  BEFORE INSERT ON cu_request FOR EACH ROW
            BEGIN

                DECLARE _customer_id, _customer_branch_id, _customer_user_id, _branch_id, _project_id, _request_cnt, _estimate_id INT;
                DECLARE _customer_natural_id, _customer_branch_natural_id,  _customer_user_natural_id, _branch_natural_id, _project_natural_id, _request_natural_id, _estimate_natural_id  VARCHAR(255);

                DECLARE _subcontract_customer_id, _subcontract_customer_branch_id, _subcontract_customer_user_id INT;
                DECLARE _subcontract_customer_natural_id, _subcontract_customer_branch_natural_id, _subcontract_customer_user_natural_id VARCHAR(255);
                
                DECLARE _estimate_type  SMALLINT;
                
                -- 顧客システムによる更新の場合
                IF new.create_system_type = 2 THEN
                
                    -- 見積依頼件数の取得（ID生成用）
                    SELECT COUNT(*)
                    INTO _request_cnt
                    FROM cu_request
                    WHERE project_id = new.project_id 
                    AND request_type = new.request_type;
                    
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

                    -- 依頼IDの作成  
                    SET _request_natural_id = CONCAT( _project_natural_id, '-C', new.request_type, LPAD( _request_cnt,3, '0' ));

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
                    ) VALUES (
                        ADDTIME( CURRENT_TIMESTAMP, 9 )                          -- レコード作成日
                    , ADDTIME( CURRENT_TIMESTAMP, 9 )                          -- レコード更新日
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
                    , CURRENT_DATE                                             -- 依頼受付日
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
                    , new.subcontract_want_guide_type                          -- 案内方法：下請用
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
                    );

                    -- 連携ID、見積依頼NOを取得
                    SET NEW.core_id = LAST_INSERT_ID(), NEW.request_natural_id = _request_natural_id;

                    -- 顧客情報（下請）の取得
                    -- ☆名前のみで一致させるため、同一名称の顧客がヒットする可能性がある    
                    IF new.subcontract_name IS NOT NULL THEN
                    
                    SELECT id, customer_natural_id
                    INTO _subcontract_customer_id, _subcontract_customer_natural_id
                    FROM customer
                    WHERE customer_name = new.subcontract_name
                        AND customer_name_kana = new.subcontract_kana
                        AND delete_timestamp IS NULL
                    LIMIT 1;
                    
                        -- 存在しない場合は顧客情報に新規追加
                        IF _subcontract_customer_id IS NULL THEN
                    
                        -- 顧客会社IDの生成
                        SELECT CONCAT( 'SUB',LPAD( _customer_id, 8, '0') ,LPAD( COUNT(id) + 1, 4, '0'))
                        INTO _subcontract_customer_natural_id
                        FROM customer 
                        WHERE LEFT( customer_natural_id, 11 ) = CONCAT( 'SUB',LPAD( _customer_id, 8, '0') );
                    
                        INSERT INTO customer (
                            customer_natural_id,
                            lastupdate_user_id,
                            ulid,
                            create_timestamp,
                            update_timestamp,
                            register_type,
                            update_type,
                            cu_lastupdate_user_id,
                            customer_name,
                            customer_name_kana,
                            construction_number_require_flag,
                            customer_system_use_flag
                        ) VALUES (
                            _subcontract_customer_natural_id,    -- 顧客会社ID
                            1,                                   -- 最終更新者
                            getULID(),                           -- ULID
                            ADDTIME( CURRENT_TIMESTAMP, 9 ),     -- レコード作成日
                            ADDTIME( CURRENT_TIMESTAMP, 9 ),     -- レコード更新日
                            1,                                   -- データ登録者種別
                            1,                                   -- データ更新者種別
                            new.create_user_id,                  -- 顧客システム最終更新者
                            new.subcontract_name,                -- 顧客会社名
                            new.subcontract_kana,                -- 顧客会社名：カナ
                            0,                                   -- 工事番号必須フラグ
                            0                                    -- 顧客システム利用の有無
                        );
                        
                        SET _subcontract_customer_id = LAST_INSERT_ID();
                    
                        END IF;

                        -- 顧客支店情報（下請）の取得
                        SELECT id, customer_branch_natural_id
                        INTO _subcontract_customer_branch_id, _subcontract_customer_branch_natural_id
                        FROM customer_branch
                        WHERE customer_branch_name = new.subcontract_branch_name
                        AND ( customer_branch_name_kana = new.subcontract_branch_kana OR new.subcontract_branch_kana IS NULL )
                        AND customer_branch_tel = IFNULL( new.subcontract_branch_tel, new.subcontract_user_tel )
                        AND ( customer_branch_fax =  new.subcontract_user_fax OR new.subcontract_user_fax IS NULL )
                        AND customer_id = _subcontract_customer_id
                        AND delete_timestamp IS NULL
                        LIMIT 1;
                    
                        -- 存在しない場合は顧客支店情報に新規追加
                        IF _subcontract_customer_branch_id IS NULL THEN
                        
                        
                        -- 顧客支店IDの生成
                        SELECT CONCAT( 'SUB',LPAD( _customer_id, 8, '0') ,'-B',LPAD( COUNT(id) + 1, 4, '0'))
                        INTO _subcontract_customer_branch_natural_id
                        FROM customer_branch 
                        WHERE LEFT( customer_natural_id, 11 ) = CONCAT( 'SUB',LPAD( _customer_id, 8, '0') );
                    
                        INSERT INTO customer_branch (
                            customer_branch_natural_id,
                            customer_id,
                            customer_natural_id,
                            lastupdate_user_id,
                            ulid,
                            create_timestamp,
                            update_timestamp,
                            register_type,
                            update_type,
                            cu_lastupdate_user_id,
                            customer_branch_name,
                            customer_branch_name_kana,
                            customer_branch_tel,
                            customer_branch_fax
                        ) VALUES (
                            _subcontract_customer_branch_natural_id,      -- 顧客支店ID
                            _subcontract_customer_id,                     -- 顧客マスターテーブルID
                            _subcontract_customer_natural_id,             -- 顧客会社ID
                            1,                                            -- 最終更新者
                            getULID(),                                    -- ULID
                            ADDTIME( CURRENT_TIMESTAMP, 9 ),              -- レコード作成日
                            ADDTIME( CURRENT_TIMESTAMP, 9 ),              -- レコード更新日
                            1,                                            -- データ登録者種別
                            1,                                            -- データ更新者種別
                            new.create_user_id,                           -- 顧客システム最終更新者
                            new.subcontract_branch_name,                  -- 顧客支店名
                            new.subcontract_branch_kana,                  -- 顧客支店名：カナ
                            IFNULL( new.subcontract_branch_tel, IFNULL( new.subcontract_user_tel, '') ),  -- 顧客支店：電話番号
                            new.subcontract_user_fax                      -- 顧客支店：FAX
                        );
                    
                        SET _subcontract_customer_branch_id = LAST_INSERT_ID();

                        END IF;
                    
                        -- 顧客担当者情報（下請）の取得
                        SELECT id, customer_user_natural_id
                        INTO _subcontract_customer_user_id, _subcontract_customer_user_natural_id
                        FROM customer_user
                        WHERE customer_user_name = new.subcontract_user_name
                        AND ( customer_user_name_kana = new.subcontract_user_kana OR new.subcontract_user_kana IS NULL )
                        AND ( customer_user_division_name = new.subcontract_user_division_name or new.subcontract_user_division_name IS NULL )
                        AND ( customer_user_email =  new.subcontract_user_email  OR new.subcontract_user_email IS NULL )
                        AND ( customer_user_tel =  new.subcontract_user_tel OR new.subcontract_user_tel  IS NULL )
                        AND customer_id = _subcontract_customer_id
                        AND customer_branch_id = _subcontract_customer_branch_id
                        AND delete_timestamp IS NULL
                        LIMIT 1;
                    
                        -- 存在しない場合は顧客担当者情報に新規追加
                        IF _subcontract_customer_user_id IS NULL THEN


                        -- 顧客担当者IDの生成
                        SELECT CONCAT( 'SUB',LPAD( _customer_id, 8, '0') ,'-C',LPAD( COUNT(id) + 1, 6, '0'))
                        INTO _subcontract_customer_user_natural_id
                        FROM customer_user 
                        WHERE LEFT( customer_natural_id, 11 ) = CONCAT( 'SUB',LPAD( _customer_id, 8, '0') );
                        
                        INSERT INTO customer_user (
                            customer_user_natural_id,
                            customer_branch_id,
                            customer_branch_natural_id,
                            customer_id,
                            customer_natural_id,
                            lastupdate_user_id,
                            ulid,
                            create_timestamp,
                            update_timestamp,
                            register_type,
                            update_type,
                            cu_lastupdate_user_id,
                            customer_user_name,
                            customer_user_name_kana,
                            customer_user_division_name,
                            customer_user_email,
                            customer_user_tel,
                            customer_reminder_sms_flag
                        ) VALUES (
                            _subcontract_customer_user_natural_id,   -- 顧客担当者ID
                            _subcontract_customer_branch_id,         -- 顧客支店マスタテーブルID
                            _subcontract_customer_branch_natural_id, -- 顧客支店ID
                            _subcontract_customer_id,                -- 顧客会社マスタテーブルID
                            _subcontract_customer_natural_id,        -- 顧客会社ID
                            1,                                       -- 最終更新者
                            getULID(),                               -- ULID
                            ADDTIME( CURRENT_TIMESTAMP, 9 ),         -- レコード作成日
                            ADDTIME( CURRENT_TIMESTAMP, 9 ),         -- レコード更新日
                            1,                                       -- データ登録者種別
                            1,                                       -- データ更新者種別
                            new.create_user_id,                      -- 顧客システム最終更新者
                            new.subcontract_user_name,               -- 顧客担当者名
                            new.subcontract_user_kana,               -- 顧客担当者名：カナ
                            new.subcontract_user_division_name,      -- 顧客担当者部署名
                            new.subcontract_user_email,              -- 顧客担当者：メールアドレス
                            new.subcontract_user_tel,                -- 顧客担当者：携帯電話番号
                            new.subcontract_reminder_sms_flag        -- 顧客担当者：SMSリマインド送信有無
                        );
                        
                        SET _subcontract_customer_user_id = LAST_INSERT_ID();
                        
                        END IF;

                        -- 工事情報を更新
                        UPDATE project
                        SET
                        subcontract_customer_id                    = _subcontract_customer_id
                        , subcontract_customer_natural_id            = _subcontract_customer_natural_id
                        , subcontract_customer_branch_id             = _subcontract_customer_branch_id
                        , subcontract_customer_branch_natural_id     = _subcontract_customer_branch_natural_id
                        , subcontract_customer_user_id               = _subcontract_customer_user_id
                        , subcontract_customer_user_natural_id       = _subcontract_customer_user_natural_id
                        , send_destination_type                      = new.send_destination_type                                   
                        , update_timestamp                           = ADDTIME( CURRENT_TIMESTAMP, 9 )
                        , update_type                                = 1
                        , cu_lastupdate_user_id                      = new.create_user_id
                    WHERE id = _project_id;
                    
                    ELSE

                        -- 工事情報を更新
                        UPDATE project
                        SET
                        send_destination_type                      = new.send_destination_type                                   
                        , update_timestamp                           = ADDTIME( CURRENT_TIMESTAMP, 9 )
                        , update_type                                = 1
                        , cu_lastupdate_user_id                      = new.create_user_id
                    WHERE id = _project_id;
                    
                    END IF;
                    
                    -- 依頼種別その他の場合の後続処理
                    IF new.request_type IN ( 4, 5, 6 ) THEN
                    
                    SET _estimate_natural_id = CONCAT( _request_natural_id, '-01' );
                    
                    -- 見積の作成
                    INSERT INTO estimate (
                        survey_available_start_date
                    , survey_available_end_date
                    , survey_capacity_qty
                    , quote_available_start_date
                    , quote_available_end_date
                    , quote_capacity_qty
                    , estimate_status
                    , payment_request_status
                    , survey_capacity_qty_type
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
                    , subcontract_customer_id
                    , subcontract_customer_natural_id
                    , subcontract_customer_branch_id
                    , subcontract_customer_branch_natural_id
                    , subcontract_customer_user_id
                    , subcontract_customer_user_natural_id
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
                    ) VALUES (
                        IFNULL( new.request_other_start_date, new.want_start_date )  -- 調査見積期間・開始日
                    , IFNULL( new.request_other_end_date , new.want_end_date )     -- 調査見積期間・終了日
                    , new.request_other_qty                                        -- 見積台数
                    , IFNULL( new.request_other_start_date, new.want_start_date )  -- 確定見積期間：開始日
                    , IFNULL( new.request_other_end_date, new.want_end_date )      -- 確定見積期間：終了日
                    , new.request_other_qty                                    -- 確定見積台数
                    , 4                                                        -- 見積ステータス 4:受注
                    , 0                                                        -- 仕入依頼ステータス 0:仕入依頼前
                    , 2                                                        -- 単位 2:式
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
                    , _subcontract_customer_id                                 -- 下請会社マスタテーブルID
                    , _subcontract_customer_natural_id                         -- 下請顧客会社ID
                    , _subcontract_customer_branch_id                          -- 下請顧客支店マスタテーブルID
                    , _subcontract_customer_branch_natural_id                  -- 下請顧客支店ID
                    , _subcontract_customer_user_id                            -- 下請担当者マスターテーブルID
                    , _subcontract_customer_user_natural_id                    -- 下請担当者ID
                    , _branch_id                                               -- ランドマーク支店マスタテーブルID
                    , _branch_natural_id                                       -- ランドマーク支店ID
                    , 1                                                        -- システム利用者テーブルID：最終更新者
                    , getULID()                                                -- ULID
                    , ADDTIME( CURRENT_TIMESTAMP, 9 )                          -- レコード作成日
                    , ADDTIME( CURRENT_TIMESTAMP, 9 )                          -- レコード作成日
                    , 1                                                        -- データ登録者種別
                    , 1                                                        -- データ更新者種別
                    , new.create_user_id                                       -- 顧客システム最終更新者
                    , 0                                                        -- 発注書確認フラグ 0:未確認
                    , 1                                                        -- 見積キャンセル確認フラグ 1:確認済み
                    , 0                                                        -- 見積キャンセル申請ステータス 0:未申請
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
                        , subcontract_customer_id
                        , subcontract_customer_natural_id
                        , subcontract_customer_branch_id
                        , subcontract_customer_branch_natural_id
                        , subcontract_customer_user_id
                        , subcontract_customer_user_natural_id
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
                        , _subcontract_customer_id                      -- 顧客会社マスタテーブルID
                        , _subcontract_customer_natural_id              -- 下請顧客会社ID
                        , _subcontract_customer_branch_id               -- 下請顧客支店マスタテーブルID
                        , _subcontract_customer_branch_natural_id       -- 下請顧客支店ID
                        , _subcontract_customer_user_id                 -- 下請顧客担当者マスタテーブルID
                        , _subcontract_customer_user_natural_id         -- 下請顧客担当者ID
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
                        , ADDTIME( CURRENT_TIMESTAMP, 9 )               -- レコード作成日
                        , ADDTIME( CURRENT_TIMESTAMP, 9 )               -- レコード作成日
                        , 1                                             -- データ登録者種別
                        , 1                                             -- データ更新者種別
                        , new.create_user_id                            -- 顧客システム最終更新者
                        );
                    
                        SET _estimate_type = _estimate_type + 1;
                            
                    UNTIL  _estimate_type > 2 END REPEAT;

                    END IF;
                    
                END IF;
            END;

            DROP TRIGGER IF EXISTS upd_cu_request;

            CREATE TRIGGER upd_cu_request  AFTER UPDATE ON cu_request FOR EACH ROW
            BEGIN

                DECLARE _customer_id, _subcontract_customer_id, _subcontract_customer_branch_id, _subcontract_customer_user_id, _project_id INT;
                DECLARE _subcontract_customer_natural_id, _subcontract_customer_branch_natural_id, _subcontract_customer_user_natural_id VARCHAR(255);
                
                -- 顧客システムによる更新の場合
                IF new.update_system_type = 2 THEN
                
                    -- 依頼種別が0:初回、1:追加、2:延長、3:積替の場合、見積依頼ステータスが0:未調査、1:Web調査、2:現地調査までを更新可能とする
                    -- 依頼種別が4:その他ー道路使用許可、5:その他ー取扱説明書収納、6:その他ー電線移設の場合、
                    -- その他作業ステータスが0:未調査、1:Web調査、2:現地調査までを更新可能とする
                    IF ( new.request_status IN ( 0, 1, 2 ) AND new.request_type IN ( 0, 1, 2, 3 ) ) 
                    OR  ( new.request_other_status = 0 AND new.request_type IN ( 4, 5, 6 )) THEN
                
                    UPDATE request
                        SET 
                        update_timestamp                = ADDTIME( CURRENT_TIMESTAMP, 9 )                                             -- レコード更新日
                        , estimate_deadline               = IFNULL( new.response_request_date, getCalcBusinessDay( CURRENT_DATE, 4  ))  -- 見積提出期限／顧客希望が無い場合は4営業日後
                        , want_start_date                 = new.want_start_date                                                         -- 利用期間：開始
                        , want_end_date                   = new.want_end_date                                                           -- 利用期間：終了
                        , car_qty                         = new.car_qty
                        , light_truck_qty                 = new.light_truck_qty
                        , truck_qty                       = new.truck_qty
                        , other_car_qty                   = new.other_car_qty
                        
                        , other_car_detail                = new.other_car_detail
                        , request_other_deadline          = new.request_other_deadline
                        , request_other_start_date        = IFNULL( new.request_other_start_date, new.want_start_date ) 
                        , request_other_end_date          = IFNULL( new.request_other_end_date, new.want_end_date )
                        , request_other_qty               = new.request_other_qty
                        , want_guide_type                 = new.want_guide_type
                        , want_guide_type_subcontract     = new.subcontract_want_guide_type
                        , cc_email                        = new.cc_email
                        , customer_other_request          = new.customer_other_request
                        , update_type                     = 1
                        , cu_lastupdate_user_id           = new.update_user_id
                        , request_cancel_date             =
                        CASE
                            WHEN old.request_status IN ( 0, 1, 2 ) AND new.request_status = 9 THEN CURRENT_DATE
                            ELSE NULL
                        END                                                 -- 調査途中終了となった場合、キャンセル日付を入れる
                        , lastupdate_user_id             = 1
                    WHERE id = new.core_id;


                    -- 顧客IDを取得
                    SELECT customer_id
                    INTO _customer_id
                    FROM cu_project
                    WHERE project_id = new.project_id;
                    
                    -- 工事IDを取得
                    SELECT core_id
                    INTO _project_id
                    FROM cu_project
                    where project_id = new.project_id;
                    
                    -- 顧客情報（下請）の取得
                    SELECT id, customer_natural_id
                    INTO _subcontract_customer_id, _subcontract_customer_natural_id
                    FROM customer
                    WHERE customer_name = new.subcontract_name
                        AND customer_name_kana = new.subcontract_kana
                        AND delete_timestamp IS NULL
                    LIMIT 1;      
                    
                    -- 下請顧客会社に変更があった場合
                    IF IFNULL( old.subcontract_name, '' ) <> new.subcontract_name THEN
                    
                        -- 存在しない場合は顧客情報に新規追加
                        IF _subcontract_customer_id IS NULL THEN
                    
                        -- 顧客会社IDの生成
                        SELECT CONCAT( 'SUB',LPAD( _customer_id, 8, '0') ,LPAD( COUNT(id) + 1, 4, '0'))
                        INTO _subcontract_customer_natural_id
                        FROM customer 
                        WHERE LEFT( customer_natural_id, 11 ) = CONCAT( 'SUB',LPAD( _customer_id, 8, '0') );
                    
                        INSERT INTO customer (
                            customer_natural_id,
                            lastupdate_user_id,
                            ulid,
                            create_timestamp,
                            update_timestamp,
                            register_type,
                            update_type,
                            cu_lastupdate_user_id,
                            customer_name,
                            customer_name_kana,
                            construction_number_require_flag,
                            customer_system_use_flag
                        ) VALUES (
                            _subcontract_customer_natural_id,    -- 顧客会社ID
                            1,                                   -- 最終更新者
                            getULID(),                           -- ULID
                            ADDTIME( CURRENT_TIMESTAMP, 9 ),     -- レコード作成日
                            ADDTIME( CURRENT_TIMESTAMP, 9 ),     -- レコード更新日
                            1,                                   -- データ登録者種別
                            1,                                   -- データ更新者種別
                            new.create_user_id,                  -- 顧客システム最終更新者
                            new.subcontract_name,                -- 顧客会社名
                            new.subcontract_kana,                -- 顧客会社名：カナ
                            0,                                   -- 工事番号必須フラグ
                            0                                    -- 顧客システム利用の有無
                        );
                        
                        SET _subcontract_customer_id = LAST_INSERT_ID();
                    
                        END IF;
                    END IF;


                    -- 顧客支店情報（下請）の取得
                    SELECT id, customer_branch_natural_id
                    INTO _subcontract_customer_branch_id, _subcontract_customer_branch_natural_id
                    FROM customer_branch
                    WHERE customer_branch_name = new.subcontract_branch_name
                        AND ( customer_branch_name_kana = new.subcontract_branch_kana OR new.subcontract_branch_kana IS NULL )
                        AND customer_branch_tel = IFNULL( new.subcontract_branch_tel, new.subcontract_user_tel )
                        AND ( customer_branch_fax =  new.subcontract_user_fax OR new.subcontract_user_fax IS NULL )
                        AND customer_id = _subcontract_customer_id
                        AND delete_timestamp IS NULL
                    LIMIT 1;
                        
                    -- 顧客支店情報（下請）に変更があった場合
                    IF IFNULL( old.subcontract_branch_name, '' ) <> new.subcontract_branch_name THEN

                        -- 存在しない場合は顧客支店情報に新規追加
                        IF _subcontract_customer_branch_id IS NULL THEN
                        
                        -- 顧客支店IDの生成
                        SELECT CONCAT( 'SUB',LPAD( _customer_id, 8, '0') ,'-B',LPAD( COUNT(id) + 1, 4, '0'))
                        INTO _subcontract_customer_branch_natural_id
                        FROM customer_branch 
                        WHERE LEFT( customer_natural_id, 11 ) = CONCAT( 'SUB',LPAD( _customer_id, 8, '0') );
                    
                        INSERT INTO customer_branch (
                            customer_branch_natural_id,
                            customer_id,
                            customer_natural_id,
                            lastupdate_user_id,
                            ulid,
                            create_timestamp,
                            update_timestamp,
                            register_type,
                            update_type,
                            cu_lastupdate_user_id,
                            customer_branch_name,
                            customer_branch_name_kana,
                            customer_branch_tel,
                            customer_branch_fax
                        ) VALUES (
                            _subcontract_customer_branch_natural_id,      -- 顧客支店ID
                            _subcontract_customer_id,                     -- 顧客マスターテーブルID
                            _subcontract_customer_natural_id,             -- 顧客会社ID
                            1,                                            -- 最終更新者
                            getULID(),                                    -- ULID
                            ADDTIME( CURRENT_TIMESTAMP, 9 ),              -- レコード作成日
                            ADDTIME( CURRENT_TIMESTAMP, 9 ),              -- レコード更新日
                            1,                                            -- データ登録者種別
                            1,                                            -- データ更新者種別
                            new.create_user_id,                           -- 顧客システム最終更新者
                            new.subcontract_branch_name,                  -- 顧客支店名
                            new.subcontract_branch_kana,                  -- 顧客支店名：カナ
                            IFNULL( new.subcontract_branch_tel, IFNULL( new.subcontract_user_tel, '') ),  -- 顧客支店：電話番号
                            new.subcontract_user_fax                      -- 顧客支店：FAX
                        );
                    
                        SET _subcontract_customer_branch_id = LAST_INSERT_ID();

                        END IF;
                    END IF;

                    -- 顧客担当者情報（下請）の取得
                    SELECT id, customer_user_natural_id
                    INTO _subcontract_customer_user_id, _subcontract_customer_user_natural_id
                    FROM customer_user
                    WHERE customer_user_name = new.subcontract_user_name
                        AND ( customer_user_name_kana = new.subcontract_user_kana or new.subcontract_user_kana IS NULL )
                        AND ( customer_user_division_name = new.subcontract_user_division_name or new.subcontract_user_division_name IS NULL )
                        AND ( customer_user_email =  new.subcontract_user_email  OR new.subcontract_user_email IS NULL )
                        AND ( customer_user_tel =  new.subcontract_user_tel OR new.subcontract_user_tel  IS NULL )
                        AND customer_id = _subcontract_customer_id
                        AND customer_branch_id = _subcontract_customer_branch_id
                        AND delete_timestamp IS NULL
                    LIMIT 1;
                        
                    -- 顧客担当者情報（下請）に変更があった場合
                    IF IFNULL( old.subcontract_user_name, '' ) <> new.subcontract_user_name THEN
                    
                        -- 存在しない場合は顧客担当者情報に新規追加
                        IF _subcontract_customer_user_id IS NULL THEN

                        -- 顧客担当者IDの生成
                        SELECT CONCAT( 'SUB',LPAD( _customer_id, 8, '0') ,'-C',LPAD( COUNT(id) + 1, 6, '0'))
                        INTO _subcontract_customer_user_natural_id
                        FROM customer_user 
                        WHERE LEFT( customer_natural_id, 11 ) = CONCAT( 'SUB',LPAD( _customer_id, 8, '0') );
                        
                        INSERT INTO customer_user (
                            customer_user_natural_id,
                            customer_branch_id,
                            customer_branch_natural_id,
                            customer_id,
                            customer_natural_id,
                            lastupdate_user_id,
                            ulid,
                            create_timestamp,
                            update_timestamp,
                            register_type,
                            update_type,
                            cu_lastupdate_user_id,
                            customer_user_name,
                            customer_user_name_kana,
                            customer_user_division_name,
                            customer_user_email,
                            customer_user_tel,
                            customer_reminder_sms_flag
                        ) VALUES (
                            _subcontract_customer_user_natural_id,   -- 顧客担当者ID
                            _subcontract_customer_branch_id,         -- 顧客支店マスタテーブルID
                            _subcontract_customer_branch_natural_id, -- 顧客支店ID
                            _subcontract_customer_id,                -- 顧客会社マスタテーブルID
                            _subcontract_customer_natural_id,        -- 顧客会社ID
                            1,                                       -- 最終更新者
                            getULID(),                               -- ULID
                            ADDTIME( CURRENT_TIMESTAMP, 9 ),         -- レコード作成日
                            ADDTIME( CURRENT_TIMESTAMP, 9 ),         -- レコード更新日
                            1,                                       -- データ登録者種別
                            1,                                       -- データ更新者種別
                            new.create_user_id,                      -- 顧客システム最終更新者
                            new.subcontract_user_name,               -- 顧客担当者名
                            new.subcontract_user_kana,               -- 顧客担当者名：カナ
                            new.subcontract_user_division_name,      -- 顧客担当者部署名
                            new.subcontract_user_email,              -- 顧客担当者：メールアドレス
                            new.subcontract_user_tel,                -- 顧客担当者：携帯電話番号
                            new.subcontract_reminder_sms_flag        -- 顧客担当者：SMSリマインド送信有無
                        );
                        
                        SET _subcontract_customer_user_id = LAST_INSERT_ID();
                        
                        END IF;
                    END IF;


                    -- いずれかの下請情報に変更があった場合
                    IF ( new.subcontract_branch_name IS NOT NULL ) AND 
                        (    IFNULL( old.subcontract_name, '' )        <> new.subcontract_name 
                        OR IFNULL( old.subcontract_branch_name, '' ) <> new.subcontract_branch_name 
                        OR IFNULL( old.subcontract_user_name, '' )   <> new.subcontract_user_name ) THEN

                        -- 工事情報を更新（変更があった箇所のみ更新、変更がない場合は元の値を利用する）
                        UPDATE project
                        SET
                        subcontract_customer_id                    = _subcontract_customer_id
                        , subcontract_customer_natural_id            = _subcontract_customer_natural_id
                        , subcontract_customer_branch_id             = _subcontract_customer_branch_id
                        , subcontract_customer_branch_natural_id     = _subcontract_customer_branch_natural_id
                        , subcontract_customer_user_id               = _subcontract_customer_user_id
                        , subcontract_customer_user_natural_id       = _subcontract_customer_user_natural_id
                        , send_destination_type                      = new.send_destination_type            
                        , update_timestamp                           = ADDTIME( CURRENT_TIMESTAMP, 9 )
                        , update_type                                = 1
                        , cu_lastupdate_user_id                      = new.update_user_id
                        WHERE id = _project_id;
                    
                    -- 下請情報がクリアされた場合
                    ELSEIF new.subcontract_branch_name IS NULL THEN
                    
                        -- 工事情報の下請情報をクリアする
                        UPDATE project
                        SET
                        subcontract_customer_id                    = NULL
                        , subcontract_customer_natural_id            = NULL
                        , subcontract_customer_branch_id             = NULL
                        , subcontract_customer_branch_natural_id     = NULL
                        , subcontract_customer_user_id               = NULL
                        , subcontract_customer_user_natural_id       = NULL
                        , send_destination_type                      = new.send_destination_type            
                        , update_timestamp                           = ADDTIME( CURRENT_TIMESTAMP, 9 )
                        , update_type                                = 1
                        , cu_lastupdate_user_id                      = new.update_user_id
                        WHERE id = _project_id;
                    
                        SET 
                        _subcontract_customer_id = NULL,
                        _subcontract_customer_natural_id = NULL,
                        _subcontract_customer_branch_id = NULL,
                        _subcontract_customer_branch_natural_id = NULL,
                        _subcontract_customer_user_id = NULL,
                        _subcontract_customer_user_natural_id = NULL ;

                    ELSEIF new.send_destination_type <> old.send_destination_type THEN
                    
                        UPDATE project
                        SET
                        send_destination_type                      = new.send_destination_type            
                        , update_timestamp                           = ADDTIME( CURRENT_TIMESTAMP, 9 )
                        , update_type                                = 1
                        , cu_lastupdate_user_id                      = new.update_user_id
                        WHERE id = _project_id;
                        
                    END IF;

                    -- 依頼種別その他の場合の後続処理
                    IF new.request_type IN ( 4, 5, 6 ) THEN

                        -- 見積の更新
                        UPDATE estimate 
                        SET
                        survey_available_start_date                      =  IFNULL( new.request_other_start_date, new.want_start_date ) -- 調査見積期間・開始日
                        , survey_available_end_date                        =  IFNULL( new.request_other_end_date, new.want_end_date )     -- 調査見積期間・終了日
                        , survey_capacity_qty                              =  new.request_other_qty                                       -- 見積台数
                        , quote_available_start_date                       =  IFNULL( new.request_other_start_date, new.want_start_date ) -- 確定見積期間：開始日
                        , quote_available_end_date                         =  IFNULL( new.request_other_end_date, new.want_end_date )     -- 確定見積期間：終了日
                        , quote_capacity_qty                               =  new.request_other_qty                                   -- 確定見積台数
                        , subcontract_customer_id                          = _subcontract_customer_id                                 -- 下請会社マスタテーブルID
                        , subcontract_customer_natural_id                  = _subcontract_customer_natural_id                         -- 下請顧客会社ID
                        , subcontract_customer_branch_id                   = _subcontract_customer_branch_id                          -- 下請顧客支店マスタテーブルID
                        , subcontract_customer_branch_natural_id           = _subcontract_customer_branch_natural_id                  -- 下請顧客支店ID
                        , subcontract_customer_user_id                     = _subcontract_customer_user_id                            -- 下請担当者マスターテーブルID
                        , subcontract_customer_user_natural_id             = _subcontract_customer_user_natural_id                    -- 下請担当者ID
                        , lastupdate_user_id                               = 1                                                        -- システム利用者テーブルID：最終更新者
                        , update_timestamp                                 = ADDTIME( CURRENT_TIMESTAMP, 9 )                          -- レコード更新日
                        , update_type                                      = 1                                                        -- データ更新者種別
                        , cu_lastupdate_user_id                            = new.create_user_id                                       -- 顧客システム最終更新者
                        WHERE request_id = new.core_id;

                        -- 見積詳細の更新
                        UPDATE estimate_detail 
                        SET
                        subcontract_customer_id                      = _subcontract_customer_id                      -- 顧客会社マスタテーブルID
                        , subcontract_customer_natural_id              = _subcontract_customer_natural_id              -- 下請顧客会社ID
                        , subcontract_customer_branch_id               = _subcontract_customer_branch_id               -- 下請顧客支店マスタテーブルID
                        , subcontract_customer_branch_natural_id       = _subcontract_customer_branch_natural_id       -- 下請顧客支店ID
                        , subcontract_customer_user_id                 = _subcontract_customer_user_id                 -- 下請顧客担当者マスタテーブルID
                        , subcontract_customer_user_natural_id         = _subcontract_customer_user_natural_id         -- 下請顧客担当者ID
                        , estimate_detail_qty                          = new.request_other_qty                         -- 見積明細：台数
                        , lastupdate_user_id                           = 1                                             -- システム利用者テーブルID：最終更新者
                        , update_timestamp                             = ADDTIME( CURRENT_TIMESTAMP, 9 )               -- レコード作成日
                        , update_type                                  = 1                                             -- データ更新者種別
                        , cu_lastupdate_user_id                        = new.create_user_id                            -- 顧客システム最終更新者
                        WHERE request_id = new.core_id;
                        
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
                    ADDTIME( CURRENT_TIMESTAMP, 9 )
                    , ADDTIME( CURRENT_TIMESTAMP, 9 )
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
                        latest_pdf_purchase_order_url = _latest_pdf_purchase_order_url,
                        purchase_order_upload_date    = ADDTIME( CURRENT_TIMESTAMP, 9 ),
                        purchase_order_register_type  = 1,           
                        update_timestamp              = ADDTIME( CURRENT_TIMESTAMP, 9 ),
                        update_type                   = 1,
                        cu_lastupdate_user_id         = new.update_user_id,
                        lastupdate_user_id            = 1
                    WHERE id = new.core_id;
                    
                    -- 失注処理（自動失注）
                    ELSEIF new.estimate_status = 7 THEN
                    UPDATE estimate
                        SET 
                        estimate_status       = new.estimate_status,
                        update_timestamp      = ADDTIME( CURRENT_TIMESTAMP, 9 ),
                        update_type           = 1,
                        cu_lasyupdate_user_id = new.update_user_id,
                        lastupdate_user_id    = 1
                    WHERE id = new.core_id;
                    END IF;

                END IF;
            END;

            DROP TRIGGER IF EXISTS upd_cu_user;

            CREATE TRIGGER upd_cu_user  BEFORE UPDATE ON cu_user FOR EACH ROW
            BEGIN

                -- 顧客向けシステムで更新した場合
                IF new.update_system_type = 2 THEN

                    -- 顧客担当者情報の更新
                    UPDATE customer_user cu 
                    INNER JOIN cu_customer_user ccu on ccu.core_id = cu.id  
                    INNER JOIN cu_user_branch cub ON cub.customer_user_id = ccu.customer_user_id       
                    SET 
                    cu.lastupdate_user_id              = 1
                    ,cu.update_timestamp                = ADDTIME( CURRENT_TIMESTAMP, 9 )
                    ,cu.customer_user_name              = new.customer_user_name
                    ,cu.customer_user_name_kana         = new.customer_user_name_kana
                    ,cu.customer_user_email             = new.login_id
                    ,cu.customer_user_tel               = new.customer_user_tel
                    ,cu.customer_reminder_sms_flag      = new.customer_reminder_sms_flag
                    ,cu.update_type                     = 1
                    ,cu.cu_lasyupdate_user_id = new.update_user_id     
                    WHERE cub.user_id = new.user_id;
                    
                END IF;

            END;

            DROP TRIGGER IF EXISTS ins_cu_user_branch;

            CREATE TRIGGER ins_cu_user_branch  BEFORE INSERT ON cu_user_branch FOR EACH ROW
            BEGIN

                DECLARE _customer_id, _customer_branch_id, _customer_user_id, _cu_customer_user_id INT;
                DECLARE _customer_natural_id, _customer_branch_natural_id
                        , _customer_user_name, _customer_user_name_kana VARCHAR(255) ;
                DECLARE _login_id VARCHAR(2048);
                DECLARE _customer_reminder_sms_flag BOOL;
                DECLARE _customer_user_tel VARCHAR(13);

                -- 顧客情報の取得
                SELECT cb.customer_id, cb.customer_natural_id, cb.id, cb.customer_branch_natural_id
                INTO _customer_id, _customer_natural_id, _customer_branch_id, _customer_branch_natural_id
                FROM cu_customer_branch ccb
                    INNER JOIN customer_branch cb ON cb.id = ccb.core_id
                WHERE ccb.customer_branch_id = new.customer_branch_id;
                
                -- ユーザー情報の取得
                SELECT login_id,  customer_user_name,  customer_user_name_kana,  customer_reminder_sms_flag,  customer_user_tel
                INTO  _login_id, _customer_user_name, _customer_user_name_kana, _customer_reminder_sms_flag, _customer_user_tel
                FROM cu_user
                WHERE user_id = new.user_id;

                -- 顧客担当者マスタに存在確認
                -- 1件のみ取得
                -- 同一支店で同一メールアドレスを利用できるユーザーは1名まで
                SELECT id
                INTO _customer_user_id
                FROM customer_user
                WHERE customer_id = _customer_id
                    AND customer_branch_id = _customer_branch_id
                    AND customer_user_email = _login_id
                LIMIT 1;

                -- データが存在しない場合、
                IF _customer_user_id IS NULL THEN

                    -- 顧客支店担当者を作成
                    INSERT INTO customer_user (
                    customer_id
                    ,customer_natural_id
                    ,customer_branch_id
                    ,customer_branch_natural_id
                    ,customer_user_natural_id
                    ,lastupdate_user_id
                    ,ulid
                    ,create_timestamp
                    ,update_timestamp
                    ,customer_user_name
                    ,customer_user_name_kana
                    ,customer_user_email
                    ,customer_user_tel
                    ,customer_reminder_sms_flag
                    ,register_type
                    ,update_type
                    ,cu_lasyupdate_user_id
                    )
                    VALUES (
                    _customer_id
                    ,_customer_natural_id
                    ,_customer_branch_id
                    ,_customer_branch_natural_id
                    ,CONCAT('CUCS', LPAD( _customer_branch_id, 5, '0' ), LPAD( new.user_id,6,'0' ))
                    ,1
                    ,getULID()
                    ,ADDTIME( CURRENT_TIMESTAMP, 9 )
                    ,ADDTIME( CURRENT_TIMESTAMP, 9 )
                    ,_customer_user_name
                    ,_customer_user_name_kana
                    ,_login_id
                    ,_customer_user_tel
                    ,_customer_reminder_sms_flag
                    ,1
                    ,1
                    ,new.create_user_id
                    );

                    -- 顧客支店担当者（顧客向けシステム）を作成 ※customer_userのINSERTトリガーで作成できないため
                    INSERT INTO cu_customer_user (
                    customer_id
                    , customer_branch_id
                    , core_id
                    , customer_user_name
                    , customer_user_name_kana
                    , customer_user_email
                    , customer_user_tel
                    , customer_reminder_sms_flag
                    , create_system_type
                    , create_user_id
                    )
                    VALUES (
                    new.customer_id                               -- 顧客会社ID
                    , new.customer_branch_id                        -- 顧客支店ID
                    , LAST_INSERT_ID()                              -- 基幹システム連携ID
                    , _customer_user_name                           -- 顧客担当者名
                    , _customer_user_name_kana                      -- 顧客担当者名：カナ
                    , _login_id                                     -- 顧客担当者：メールアドレス
                    , _customer_user_tel                            -- 顧客担当者：携帯電話番号
                    , _customer_reminder_sms_flag                   -- 顧客担当者：SMSリマインド送付有無
                    , 2                                             -- 固定値：顧客向けシステム
                    , 0                                             -- 固定値：システム自動連携
                    );

                    -- 顧客担当者IDをセット
                    SET new.customer_user_id = LAST_INSERT_ID();

                ELSE

                    -- データが存在した場合
                    SELECT customer_user_id
                    INTO _cu_customer_user_id
                    FROM cu_customer_user ccu
                    WHERE ccu.core_id = _customer_user_id;  
                    
                    -- 顧客担当者IDをセット
                    SET new.customer_user_id = _cu_customer_user_id;
                
                END IF;

            END;
        ";
    }
}
