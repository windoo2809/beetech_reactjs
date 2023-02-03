<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateTrigger20210806 extends Migration
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
            DROP TRIGGER IF EXISTS ins_accounts_receivable;

            CREATE TRIGGER ins_accounts_receivable AFTER INSERT ON accounts_receivable FOR EACH ROW
            BEGIN

            DECLARE _project_id, _contract_id, _customer_id, _customer_branch_id, _customer_user_id, _parking_id int;

            -- 見積情報および工事情報の取得
            SELECT 
                c.project_id
                ,c.contract_id
                ,p.customer_id
                ,p.customer_branch_id
                ,p.customer_user_id
                ,c.parking_id
            INTO
                _project_id
                ,_contract_id
                ,_customer_id
                ,_customer_branch_id
                ,_customer_user_id
                ,_parking_id
            FROM cu_contract c
                INNER JOIN cu_project p ON p.project_id = c.project_id
            WHERE c.core_id = new.estimate_id;

            INSERT INTO cu_invoice( 
                core_id
                , core_invoice_id
                , project_id
                , contract_id
                , customer_id
                , customer_branch_id
                , customer_user_id
                , parking_id
                , invoice_amt
                , invoice_closing_date
                , payment_deadline
                , receivable_collect_total_amt
                , receivable_collect_finish_date
                , payment_status
                , reminder
                , create_system_type
                , create_user_id
                , status
            ) VALUES (
                new.id                                        -- 基幹システム連携ID
                , new.invoice_id                                -- 基幹システム請求ID
                , _project_id                                   -- 工事ID
                , _contract_id                                  -- 契約ID
                , _customer_id                                  -- 顧客会社ID
                , _customer_branch_id                           -- 顧客支店ID
                , _customer_user_id                             -- 顧客担当者ID
                , _parking_id                                   -- 駐車場ID
                , new.invoice_amt                               -- 請求金額
                , new.invoice_closing_date                      -- 請求書発行日
                , new.payment_deadline                          -- 支払期限日
                , new.receivable_collect_total_amt              -- 入金済金額
                , new.receivable_collect_finish_date            -- 支払完了日
                , new.accounts_receivable_status                -- 支払ステータス
                , new.payment_urge_flag                         -- 督促フラグ
                , 1                                             -- 固定値：基幹システム
                , 0                                             -- 固定値：システム自動連携
                , ISNULL( new.delete_timestamp )                -- ステータス 
            );

            -- 契約ステータスの更新
            UPDATE cu_contract
            SET 
                contract_status = 3,
                update_system_type = 1
            WHERE contract_id = _contract_id;
            
            END;

            DROP TRIGGER IF EXISTS upd_accounts_receivable;

            CREATE TRIGGER upd_accounts_receivable AFTER UPDATE ON accounts_receivable FOR EACH ROW
            BEGIN

            DECLARE _project_id, _contract_id, _customer_id, _customer_branch_id, _customer_user_id, _parking_id int;

            -- 見積情報および工事情報の取得
            SELECT 
                c.project_id
                ,c.contract_id
                ,p.customer_id
                ,p.customer_branch_id
                ,p.customer_user_id
                ,c.parking_id
            INTO
                _project_id
                ,_contract_id
                ,_customer_id
                ,_customer_branch_id
                ,_customer_user_id
                ,_parking_id
            FROM cu_contract c
                INNER JOIN cu_project p ON p.project_id = c.project_id
            WHERE c.core_id = new.estimate_id;

            -- すでに連携済みのデータが存在する場合は更新を行う
            IF EXISTS ( SELECT 1 FROM cu_invoice WHERE core_id = new.id ) THEN

                UPDATE cu_invoice
                SET
                project_id                       = _project_id
                , contract_id                      = _contract_id
                , customer_id                      = _customer_id
                , customer_branch_id               = _customer_branch_id
                , customer_user_id                 = _customer_user_id
                , parking_id                       = _parking_id
                , invoice_amt                      = new.invoice_amt
                , invoice_closing_date             = new.invoice_closing_date
                , payment_deadline                 = new.payment_deadline
                , receivable_collect_total_amt     = new.receivable_collect_total_amt
                , receivable_collect_finish_date   = new.receivable_collect_finish_date
                , payment_status                   = new.accounts_receivable_status
                , reminder                         = new.payment_urge_flag
                , update_system_type               = 1
                , update_user_id                   = 0
                , status                           = ISNULL( new.delete_timestamp )
                WHERE cu_invoice.core_id = new.id;
            
            ELSE
            
                INSERT INTO cu_invoice( 
                    core_id
                , core_invoice_id  
                , project_id
                , contract_id
                , customer_id
                , customer_branch_id
                , customer_user_id
                , parking_id
                , invoice_amt
                , invoice_closing_date
                , payment_deadline
                , receivable_collect_total_amt
                , receivable_collect_finish_date
                , payment_status
                , reminder
                , create_system_type
                , create_user_id
                , status
                ) VALUES (
                    new.id                                        -- 基幹システム連携ID
                , new.invoice_id                                -- 基幹システム請求ID
                , _project_id                                   -- 工事ID
                , _contract_id                                  -- 契約ID
                , _customer_id                                  -- 顧客会社ID
                , _customer_branch_id                           -- 顧客支店ID
                , _customer_user_id                             -- 顧客担当者ID
                , _parking_id                                   -- 駐車場ID
                , new.invoice_amt                               -- 請求金額
                , new.invoice_closing_date                      -- 請求書発行日
                , new.payment_deadline                          -- 支払期限日
                , new.receivable_collect_total_amt              -- 入金済金額
                , new.receivable_collect_finish_date            -- 支払完了日
                , new.accounts_receivable_status                -- 支払ステータス
                , new.payment_urge_flag                         -- 督促フラグ
                , 1                                             -- 固定値：基幹システム
                , 0                                             -- 固定値：システム自動連携
                , ISNULL( new.delete_timestamp )                -- ステータス
                );

                -- 契約ステータスの更新
                UPDATE cu_contract
                SET 
                contract_status = 3,
                update_system_type = 1
                WHERE contract_id = _contract_id;
            
            END IF;

            END;

            DROP TRIGGER IF EXISTS ins_cu_file;

            CREATE TRIGGER ins_cu_file BEFORE INSERT ON cu_file FOR EACH ROW
            BEGIN

            DECLARE _project_id, _request_id, _estimate_id, _contract_id, _invoice_id, _core_id int;

            -- 基幹システムで作成した場合
            IF ( new.create_system_type = 1 ) THEN
            
                SET _core_id = new.ref_id;

                -- 工事, メッセージ
                IF ( new.file_type = 1 OR new.file_type = 7 ) THEN

                SELECT project_id
                INTO  _project_id
                FROM  cu_project
                WHERE core_id = _core_id;

                -- 見積依頼
                ELSEIF new.file_type = 2  THEN
            
                -- 見積依頼の取得
                SELECT project_id, request_id
                INTO  _project_id, _request_id
                FROM  cu_request
                WHERE core_id = _core_id;
            
                -- 見積  ／ 顧客向けシステム
                ELSEIF  new.file_type = 3  THEN
            
                -- 見積の取得
                SELECT project_id, request_id, estimate_id
                INTO  _project_id ,_request_id, _estimate_id
                FROM  cu_estimate
                WHERE core_id = _core_id;
                    
                -- 契約の取得（確定見積以降の場合、契約情報もセットする）
                SELECT contract_id
                INTO  _contract_id
                FROM  cu_contract
                WHERE estimate_id = _estimate_id;

                -- 発注、契約  ／ 顧客向けシステム
                ELSEIF ( new.file_type = 4 OR new.file_type = 5  )  THEN

                -- 契約の取得
                SELECT project_id, estimate_id, contract_id
                INTO  _project_id ,_estimate_id, _contract_id
                FROM  cu_contract
                WHERE core_id = _core_id;

                -- 見積の取得
                SELECT request_id
                INTO  _request_id
                FROM  cu_estimate
                WHERE estimate_id = _estimate_id;

                -- 請求  ／ 顧客向けシステム
                ELSEIF  new.file_type = 6   THEN

                -- 請求の取得
                SELECT project_id, contract_id, invoice_id
                INTO  _project_id , _contract_id, _invoice_id
                FROM  cu_invoice
                WHERE core_invoice_id = _core_id;

                -- 契約の取得
                SELECT estimate_id
                INTO  _estimate_id
                FROM  cu_contract
                WHERE contract_id = _contract_id;
            
                -- 見積の取得
                SELECT request_id
                INTO  _request_id
                FROM  cu_estimate
                WHERE estimate_id = _estimate_id;
                
                END IF;

            /* 顧客システムで作成した場合 */
            ELSEIF new.create_system_type = 2 THEN


            
                -- 工事, メッセージ
                IF ( new.file_type = 1 OR new.file_type = 7 ) THEN

                SET _project_id = new.project_id;
                
                -- 工事情報の取得
                SELECT core_id
                INTO _core_id
                FROM cu_project
                WHERE project_id = _project_id;

                -- 見積依頼
                ELSEIF new.file_type = 2  THEN
            
                SET _request_id = new.request_id;
                
                -- 見積依頼の取得
                SELECT project_id, core_id
                INTO _project_id, _core_id
                FROM cu_request
                WHERE request_id = _request_id;
                    
                -- 見積、発注
                ELSEIF ( new.file_type = 3 OR new.file_type = 4 ) THEN
                
                SET _estimate_id = new.estimate_id;
            
                -- 見積の取得
                SELECT project_id, request_id, core_id
                INTO _project_id ,_request_id, _core_id
                FROM cu_estimate
                WHERE estimate_id = _estimate_id;
                        
                -- 契約の取得（確定見積以降の場合、契約情報もセットする）
                SELECT contract_id
                INTO   _contract_id
                FROM  cu_contract
                WHERE estimate_id = _estimate_id;

                -- 契約  ／ 顧客向けシステム
                ELSEIF new.file_type = 5  THEN

                SET _contract_id = new.contract_id;
                
                -- 契約の取得
                SELECT project_id, estimate_id, core_id
                INTO _project_id ,_estimate_id, _core_id
                FROM cu_contract
                WHERE contract_id = _contract_id;

                -- 見積の取得
                SELECT request_id
                INTO _request_id
                FROM cu_estimate
                WHERE estimate_id = _estimate_id;
                    
                -- 請求  ／ 顧客向けシステム
                ELSEIF  new.file_type = 6   THEN

                SET _invoice_id = new.invoice_id;
                
                -- 請求の取得
                SELECT project_id, contract_id,  core_invoice_id
                INTO  _project_id , _contract_id, _core_id
                FROM  cu_invoice
                WHERE invoice_id = _invoice_id;
                    
                -- 契約の取得
                SELECT estimate_id
                INTO  _estimate_id
                FROM cu_contract
                WHERE contract_id = _contract_id;
            
                -- 見積の取得
                SELECT request_id
                INTO _request_id
                FROM cu_estimate
                WHERE estimate_id = _estimate_id;
                        
                END IF;      
            END IF;
            
            -- 値のセット
            SET
                new.ref_id      = _core_id  
            , new.project_id  = _project_id
            , new.request_id  = _request_id
            , new.estimate_id = _estimate_id
            , new.contract_id = _contract_id
            , new.invoice_id  = _invoice_id;
            
            END;

            DROP TRIGGER IF EXISTS ins_cu_message;

            CREATE TRIGGER ins_cu_message BEFORE INSERT ON cu_message FOR EACH ROW
            BEGIN

            DECLARE _customer_id INT;

            IF new.customer_id IS NULL THEN
            
                SELECT cp.customer_id 
                INTO   _customer_id
                FROM   cu_project cp
                WHERE  cp.project_id = new.project_id;
            
                SET new.customer_id = _customer_id;
            
            END IF;

            END;

            DROP TRIGGER IF EXISTS ins_cu_request;

            CREATE TRIGGER ins_cu_request  BEFORE INSERT ON cu_request FOR EACH ROW
            BEGIN

            DECLARE _customer_id, _customer_branch_id, _customer_user_id, _branch_id, _project_id, _request_cnt INT;
            DECLARE _customer_natural_id, _customer_branch_natural_id,  _customer_user_natural_id, _branch_natural_id, _project_natural_id, _request_natural_id  VARCHAR(255);

            DECLARE _subcontract_customer_id, _subcontract_customer_branch_id, _subcontract_customer_user_id INT;
            DECLARE _subcontract_customer_natural_id, _subcontract_customer_branch_natural_id, _subcontract_customer_user_natural_id VARCHAR(255);
            
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
                , cc_email
                , customer_other_request
                , survey_status
                , register_type
                , update_type
                , request_cancel_check_flag
                , lastupdate_user_id
                , project_id
                , user_branch_id
                ) VALUES (
                    CURRENT_TIMESTAMP                                        -- レコード作成日
                , CURRENT_TIMESTAMP                                        -- レコード更新日
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
                , new.request_date                                         -- 依頼受付日
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
                , new.request_other_start_date                             -- 契約開始日
                , new.request_other_end_date                               -- 契約終了日
                , new.request_other_qty                                    -- 個数
                , new.want_guide_type                                      -- 案内方法
                , new.cc_email                                             -- 顧客が指定するCCメールアドレス
                , new.customer_other_request                               -- 顧客からの要望等
                , 0                                                        -- 調査ステータス （0:未調査）
                , 1                                                        -- データ登録ユーザー種別（1:顧客登録データ)
                , 1                                                        -- データ更新者種別（1:顧客登録データ)
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
                        CURRENT_TIMESTAMP,                   -- レコード作成日
                        CURRENT_TIMESTAMP,                   -- レコード更新日
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
                    AND customer_branch_name_kana = new.subcontract_branch_kana
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
                        CURRENT_TIMESTAMP,                            -- レコード作成日
                        CURRENT_TIMESTAMP,                            -- レコード更新日
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
                    AND customer_user_name_kana = new.subcontract_user_kana
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
                        _subcontract_customer_user_natural_id,   -- 顧客支店ID
                        _subcontract_customer_id,                -- 顧客会社マスタテーブルID
                        _subcontract_customer_natural_id,        -- 顧客会社ID
                        1,                                       -- 最終更新者
                        getULID(),                               -- ULID
                        CURRENT_TIMESTAMP,                       -- レコード作成日
                        CURRENT_TIMESTAMP,                       -- レコード更新日
                        1,                                       -- データ登録者種別
                        1,                                       -- データ更新者種別
                        new.create_user_id,                  -- 顧客システム最終更新者
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
                    , update_timestamp                           = CURRENT_TIMESTAMP
                    , update_type                                = 1
                    , cu_lastupdate_user_id                      = new.update_user_id
                WHERE id = _project_id;
                
                END IF;
                
                -- 依頼種別その他の場合の後続処理
                -- ☆☆☆☆
                
            END IF;
            END;
        ";
    }
}
