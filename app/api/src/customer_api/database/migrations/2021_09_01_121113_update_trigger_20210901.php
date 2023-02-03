<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateTrigger20210901 extends Migration
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
            DROP TRIGGER IF EXISTS ins_request;
            CREATE TRIGGER ins_request AFTER INSERT ON request FOR EACH ROW
            BEGIN

                DECLARE _project_id int;
                DECLARE 
                    _subcontract_name
                , _subcontract_kana
                , _subcontract_branch_name
                , _subcontract_branch_kana
                , _subcontract_user_division_name
                , _subcontract_user_name
                , _subcontract_user_kana varchar(255);
                DECLARE _subcontract_user_email varchar(2048);
                DECLARE 
                    _subcontract_user_tel
                , _subcontract_user_fax varchar(13);
                DECLARE _subcontract_reminder_sms_flag BOOL;

                -- 基幹システムからの更新の場合
                IF new.update_type = 0 THEN

                    -- 工事情報の取得
                    SELECT  project_id
                    INTO   _project_id
                    FROM   cu_project
                    WHERE  core_id = new.project_id;
                
                    -- 下請情報の取得
                    SELECT  
                    c.customer_name
                    ,c.customer_name_kana
                    ,cb.customer_branch_name
                    ,cb.customer_branch_name_kana
                    ,cu.customer_user_division_name
                    ,cu.customer_user_name
                    ,cu.customer_user_name_kana
                    ,cu.customer_user_email
                    ,cu.customer_user_tel
                    ,cb.customer_branch_fax
                    ,cu.customer_reminder_sms_flag
                    INTO   
                    _subcontract_name
                    , _subcontract_kana
                    , _subcontract_branch_name
                    , _subcontract_branch_kana
                    , _subcontract_user_division_name
                    , _subcontract_user_name
                    , _subcontract_user_kana
                    , _subcontract_user_email
                    , _subcontract_user_tel
                    , _subcontract_user_fax
                    , _subcontract_reminder_sms_flag
                    FROM   project p
                    INNER JOIN customer c ON p.subcontract_customer_id = c.id
                    INNER JOIN customer_branch cb ON p.subcontract_customer_branch_id = cb.id
                    INNER JOIN customer_user cu ON p.subcontract_customer_user_id = cu.id
                    WHERE  p.id = new.project_id;

                    -- 見積依頼（登録）
                    INSERT INTO cu_request( 
                    project_id
                    , core_id
                    , request_natural_id
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
                    , want_guide_type
                    , cc_email
                    , response_request_date
                    , customer_other_request
                    , request_other_deadline
                    , request_other_start_date
                    , request_other_end_date
                    , request_other_qty
                    , request_status
                    , subcontract_want_guide_type
                    , subcontract_name
                    , subcontract_kana
                    , subcontract_branch_name
                    , subcontract_branch_kana
                    , subcontract_user_division_name
                    , subcontract_user_name
                    , subcontract_user_kana
                    , subcontract_user_email
                    , subcontract_user_tel
                    , subcontract_user_fax
                    , subcontract_reminder_sms_flag
                    , create_system_type
                    , create_user_id
                    , status
                    , contact_memo
                    ) 
                    VALUES (
                    _project_id                                   -- 工事ID
                    , new.id                                        -- 基幹システム連携ID
                    , new.request_natural_id                        -- 
                    , new.request_date                              -- 依頼受付日
                    , new.estimate_deadline                         -- 見積提出期限
                    , new.request_type                              -- 依頼種別
                    , new.want_start_date                           -- 利用期間：開始
                    , new.want_end_date                             -- 利用期間：終了
                    , new.car_qty                                   -- 台数：乗用車（軽自動車・ハイエース等）
                    , new.light_truck_qty                           -- 台数：軽トラック
                    , new.truck_qty                                 -- 台数：2ｔトラック
                    , new.other_car_qty                             -- 台数：その他（計）
                    , new.other_car_detail                          -- その他詳細
                    , new.want_guide_type                           -- 案内方法
                    , new.cc_email                                  -- 顧客が指定するCCメールアドレス
                    , new.estimate_deadline                         -- 顧客からの要望日
                    , new.customer_other_request                    -- 顧客からの要望など
                    , ifnull( new.request_other_deadline, ifnull( new.request_other_start_date, new.want_start_date ))        -- 着手期限日
                    , new.request_other_start_date                  -- 契約開始日
                    , new.request_other_end_date                    -- 契約終了日
                    , new.request_other_qty                         -- 個数
                    , new.survey_status                             -- 見積依頼ステータス／調査ステータス
                    , new.want_guide_type_subcontract               -- 案内方法：下請用
                    , _subcontract_name                             -- 下請顧客会社名
                    , _subcontract_kana                             -- 下請顧客会社名：カナ
                    , _subcontract_branch_name                      -- 下請顧客支店名
                    , _subcontract_branch_kana                      -- 下請顧客支店名：カナ
                    , _subcontract_user_division_name               -- 下請顧客部署名
                    , _subcontract_user_name                        -- 下請顧客担当者名
                    , _subcontract_user_kana                        -- 下請顧客担当者名：カナ
                    , _subcontract_user_email                       -- 下請顧客担当者メールアドレス
                    , _subcontract_user_tel                         -- 下請顧客担当者携帯番号
                    , _subcontract_user_fax                         -- 下請顧客担当者FAX番号
                    , _subcontract_reminder_sms_flag                -- 下請顧客担当者SMSリマインド有無
                    , 1                                             -- 固定値：基幹システム
                    , 0                                             -- 固定値：システム自動連携
                    , ISNULL( new.delete_timestamp )                -- ステータス 
                    , new.contact_memo                              -- 連絡メモ
                    );
                END IF;
            END;

            DROP TRIGGER IF EXISTS upd_request;

            CREATE TRIGGER upd_request AFTER UPDATE ON request FOR EACH ROW
            BEGIN

                DECLARE _project_id int;
                DECLARE 
                    _subcontract_name
                , _subcontract_kana
                , _subcontract_branch_name
                , _subcontract_branch_kana
                , _subcontract_user_division_name
                , _subcontract_user_name
                , _subcontract_user_kana varchar(255);
                DECLARE _subcontract_user_email varchar(2048);
                DECLARE 
                    _subcontract_user_tel
                , _subcontract_user_fax varchar(13);
                DECLARE _subcontract_reminder_sms_flag BOOL;

                -- 基幹システムからの更新の場合
                IF new.update_type = 0 THEN
                
                    -- 工事情報の取得
                    SELECT  project_id
                    INTO   _project_id
                    FROM   cu_project
                    WHERE  core_id = new.project_id;
                
                    -- 下請情報の取得
                    SELECT  
                    c.customer_name
                    ,c.customer_name_kana
                    ,cb.customer_branch_name
                    ,cb.customer_branch_name_kana
                    ,cu.customer_user_division_name
                    ,cu.customer_user_name
                    ,cu.customer_user_name_kana
                    ,cu.customer_user_email
                    ,cu.customer_user_tel
                    ,cb.customer_branch_fax
                    ,cu.customer_reminder_sms_flag
                    INTO   
                    _subcontract_name
                    , _subcontract_kana
                    , _subcontract_branch_name
                    , _subcontract_branch_kana
                    , _subcontract_user_division_name
                    , _subcontract_user_name
                    , _subcontract_user_kana
                    , _subcontract_user_email
                    , _subcontract_user_tel
                    , _subcontract_user_fax
                    , _subcontract_reminder_sms_flag
                    FROM   project p
                    INNER JOIN customer c ON p.subcontract_customer_id = c.id
                    INNER JOIN customer_branch cb ON p.subcontract_customer_branch_id = cb.id
                    INNER JOIN customer_user cu ON p.subcontract_customer_user_id = cu.id
                    WHERE  p.id = new.project_id;

                    -- すでに連携済みのデータが存在する場合は更新を行う
                    IF EXISTS ( SELECT 1 FROM cu_request WHERE core_id = new.id ) THEN

                    UPDATE cu_request
                    SET 
                        request_date                       = new.request_date                       -- 依頼受付日
                    , estimate_deadline                  = new.estimate_deadline                  -- 見積提出期限
                    , request_type                       = new.request_type                       -- 依頼種別
                    , want_start_date                    = new.want_start_date                    -- 利用期間：開始
                    , want_end_date                      = new.want_end_date                      -- 利用期間：終了
                    , car_qty                            = new.car_qty                            -- 台数：乗用車（軽自動車・ハイエース等）
                    , light_truck_qty                    = new.light_truck_qty                    -- 台数：軽トラック
                    , truck_qty                          = new.truck_qty                          -- 台数：2ｔトラック
                    , other_car_qty                      = new.other_car_qty                      -- 台数：その他（計）
                    , other_car_detail                   = new.other_car_detail                   -- その他詳細
                    , want_guide_type                    = new.want_guide_type                    -- 案内方法
                    , cc_email                           = new.cc_email                           -- 顧客が指定するCCメールアドレス
                    , response_request_date              = new.estimate_deadline                  -- 顧客からの要望日
                    , customer_other_request             = new.customer_other_request             -- 顧客からの要望など
                    , request_other_deadline             = ifnull( new.request_other_deadline, ifnull( new.request_other_start_date, new.want_start_date ))                   -- 着手期限日
                    , request_other_start_date           = new.request_other_start_date           -- 契約開始日
                    , request_other_end_date             = new.request_other_end_date             -- 契約終了日
                    , request_other_qty                  = new.request_other_qty                  -- 個数
                    , request_status                     = new.survey_status                      -- 見積依頼ステータス／調査ステータス
                    , subcontract_want_guide_type        = new.want_guide_type_subcontract        -- 案内方法：下請用
                    , subcontract_name                   = _subcontract_name                      -- 下請顧客会社名
                    , subcontract_kana                   = _subcontract_kana                      -- 下請顧客会社名：カナ
                    , subcontract_branch_name            = _subcontract_branch_name               -- 下請顧客支店名
                    , subcontract_branch_kana            = _subcontract_branch_kana               -- 下請顧客支店名：カナ
                    , subcontract_user_division_name     = _subcontract_user_division_name        -- 下請顧客部署名
                    , subcontract_user_name              = _subcontract_user_name                 -- 下請顧客担当者名
                    , subcontract_user_kana              = _subcontract_user_kana                 -- 下請顧客担当者名：カナ
                    , subcontract_user_email             = _subcontract_user_email                -- 下請顧客担当者メールアドレス
                    , subcontract_user_tel               = _subcontract_user_tel                  -- 下請顧客担当者携帯番号
                    , subcontract_user_fax               = _subcontract_user_fax                  -- 下請顧客担当者FAX番号
                    , subcontract_reminder_sms_flag      = _subcontract_reminder_sms_flag         -- 下請顧客担当者SMSリマインド有無
                    , update_system_type                 = 1                                      -- 固定値：基幹システム
                    , update_user_id                     = 0                                      -- 固定値：システム自動連携
                    , status                             = ISNULL( new.delete_timestamp )         -- ステータス
                    , contact_memo                       = new.contact_memo                       -- 連絡メモ
                    WHERE core_id = new.id
                    ;
                
                    -- 連携済みのデータが存在しない場合は登録を行う
                    ELSE

                    -- 見積依頼（登録）
                    INSERT INTO cu_request( 
                        project_id
                    , core_id
                    , request_natural_id
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
                    , want_guide_type
                    , cc_email
                    , response_request_date
                    , customer_other_request
                    , request_other_deadline
                    , request_other_start_date
                    , request_other_end_date
                    , request_other_qty
                    , request_status
                    , subcontract_want_guide_type
                    , subcontract_name
                    , subcontract_kana
                    , subcontract_branch_name
                    , subcontract_branch_kana
                    , subcontract_user_division_name
                    , subcontract_user_name
                    , subcontract_user_kana
                    , subcontract_user_email
                    , subcontract_user_tel
                    , subcontract_user_fax
                    , subcontract_reminder_sms_flag
                    , create_system_type
                    , create_user_id
                    , status
                    , contact_memo
                    ) 
                    VALUES (
                        _project_id                                   -- 工事ID
                    , new.id                                        -- 基幹システム連携ID
                    , new.request_natural_id                        -- 見積依頼NO
                    , new.request_date                              -- 依頼受付日
                    , new.estimate_deadline                         -- 見積提出期限
                    , new.request_type                              -- 依頼種別
                    , new.want_start_date                           -- 利用期間：開始
                    , new.want_end_date                             -- 利用期間：終了
                    , new.car_qty                                   -- 台数：乗用車（軽自動車・ハイエース等）
                    , new.light_truck_qty                           -- 台数：軽トラック
                    , new.truck_qty                                 -- 台数：2ｔトラック
                    , new.other_car_qty                             -- 台数：その他（計）
                    , new.other_car_detail                          -- その他詳細
                    , new.want_guide_type                           -- 案内方法
                    , new.cc_email                                  -- 顧客が指定するCCメールアドレス
                    , new.estimate_deadline                         -- 顧客からの要望日
                    , new.customer_other_request                    -- 顧客からの要望など
                    , ifnull( new.request_other_deadline, ifnull( new.request_other_start_date, new.want_start_date ))                         -- 着手期限日
                    , new.request_other_start_date                  -- 契約開始日
                    , new.request_other_end_date                    -- 契約終了日
                    , new.request_other_qty                         -- 個数
                    , new.survey_status                             -- 見積依頼ステータス／調査ステータス
                    , new.want_guide_type_subcontract               -- 案内方法：下請用
                    , _subcontract_name                             -- 下請顧客会社名
                    , _subcontract_kana                             -- 下請顧客会社名：カナ
                    , _subcontract_branch_name                      -- 下請顧客支店名
                    , _subcontract_branch_kana                      -- 下請顧客支店名：カナ
                    , _subcontract_user_division_name               -- 下請顧客部署名
                    , _subcontract_user_name                        -- 下請顧客担当者名
                    , _subcontract_user_kana                        -- 下請顧客担当者名：カナ
                    , _subcontract_user_email                       -- 下請顧客担当者メールアドレス
                    , _subcontract_user_tel                         -- 下請顧客担当者携帯番号
                    , _subcontract_user_fax                         -- 下請顧客担当者FAX番号
                    , _subcontract_reminder_sms_flag                -- 下請顧客担当者SMSリマインド有無
                    , 1                                             -- 固定値：基幹システム
                    , 0                                             -- 固定値：システム自動連携
                    , ISNULL( new.delete_timestamp )                -- ステータス
                    , new.contact_memo                              -- 連絡メモ
                    );
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
                        cc.update_system_type = 1                      -- 固定値：基幹システム
                    , cc.update_user_id = 1                          -- 固定値：システム自動連携      
                --      , cs_document_id = new.contract_docuent_id    -- クラウドサイン連携用ドキュメントID
                --      , cs_file_id = new.contract_file_id           -- クラウドサイン連携用ファイルID
                    WHERE
                    ci.core_invoice_id = new.id;
                    
                END IF;
            END;

            DROP TRIGGER IF EXISTS ins_accounts_receivable;

            CREATE TRIGGER ins_accounts_receivable AFTER INSERT ON accounts_receivable FOR EACH ROW
            BEGIN

                DECLARE _project_id, _contract_id, _customer_id, _customer_branch_id, _customer_user_id, _parking_id int;
                DECLARE _invoice_process_status smallint;
                DECLARE _contact_memo text;


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

                -- 請求書情報の取得
                SELECT invoice_process_status, contact_memo
                INTO _invoice_process_status, _contact_memo
                FROM invoice
                WHERE id = new.invoice_id;

                -- 請求データの作成
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
                    , invoice_status
                    , contact_memo
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
                    , _invoice_process_status                       -- 請求ステータス
                    , _contact_memo                                 -- 連絡メモ
                );

                -- 契約ステータスの更新
                UPDATE cu_contract
                SET 
                    contract_status = 3,
                    update_system_type = 1
                WHERE contract_id = _contract_id;
            
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
                    , ifnull( new.request_other_deadline, ifnull( new.request_other_start_date, new.want_start_date ))                                    -- 着手期限
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
                        , request_other_deadline          = ifnull( new.request_other_deadline, ifnull( new.request_other_start_date, new.want_start_date ))      
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
                        , request_other_deadline                           = ifnull( new.request_other_deadline, ifnull( new.request_other_start_date, new.want_start_date ))
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

        ";
    }
}
