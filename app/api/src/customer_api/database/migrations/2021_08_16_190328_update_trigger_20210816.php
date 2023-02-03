<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateTrigger20210816 extends Migration
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
            DROP TRIGGER IF EXISTS ins_customer;

            CREATE TRIGGER ins_customer AFTER INSERT ON customer FOR EACH ROW
            BEGIN

                -- 顧客向けシステムから登録した場合は処理を終了する
                IF new.register_type = 0 THEN 
                
                    INSERT INTO  cu_customer ( 
                    core_id, 
                    customer_name, 
                    customer_name_kana, 
                    construction_number_require_flag , 
                    customer_system_use_flag, 
                    create_system_type, 
                    create_user_id, 
                    status )
                    VALUES ( 
                    new.id                                        -- 基幹システム連携ID
                    , new.customer_name                             -- 顧客会社名
                    , new.customer_name_kana                        -- 顧客会社名：カナ
                    , new.construction_number_require_flag          -- 工事番号必須フラグ
                    , new.customer_system_use_flag                  -- 顧客システム利用有無
                    , 1                                             -- 固定値：基幹システム
                    , 0                                             -- 固定値：システム自動連携
                    , ISNULL( new.delete_timestamp )                -- ステータス
                    );
                    
                END IF;
            END;

            DROP TRIGGER IF EXISTS upd_customer;

            CREATE TRIGGER upd_customer AFTER UPDATE ON customer FOR EACH ROW
            BEGIN

                -- 顧客向けシステムから登録した場合は処理を終了する
                IF new.update_type = 0 THEN

                    -- すでに連携済みのデータが存在する場合は更新を行う
                    IF EXISTS ( SELECT 1 FROM cu_customer WHERE core_id = new.id ) THEN
                    UPDATE cu_customer
                        SET 
                        customer_name                    = new.customer_name                     -- 顧客会社名
                        ,customer_name_kana               = new.customer_name_kana                -- 顧客会社名：カナ
                        ,construction_number_require_flag = new.construction_number_require_flag  -- 工事番号必須フラグ
                        ,customer_system_use_flag         = new.customer_system_use_flag          -- 顧客システム利用有無
                        ,update_system_type               = 1                                     -- 固定値：基幹システム
                        ,update_user_id                   = 0                                     -- 固定値：システム自動連携
                        ,status                           = ISNULL( new.delete_timestamp )        -- ステータス
                    WHERE core_id = new.id;
                    
                    -- 連携済みのデータが存在しない場合は登録を行う
                    ELSE
                    INSERT INTO  cu_customer ( 
                        core_id, 
                        customer_name, 
                        customer_name_kana, 
                        construction_number_require_flag , 
                        customer_system_use_flag, 
                        create_system_type, 
                        create_user_id, 
                        status )
                    VALUES ( 
                        new.id                                        -- 基幹システム連携ID
                    , new.customer_name                             -- 顧客会社名
                    , new.customer_name_kana                        -- 顧客会社名：カナ
                    , new.construction_number_require_flag          -- 工事番号必須フラグ
                    , new.customer_system_use_flag                  -- 顧客システム利用有無
                    , 1                                             -- 固定値：基幹システム
                    , 0                                             -- 固定値：システム自動連携
                    , ISNULL( new.delete_timestamp )                -- ステータス
                    );
                    END IF;
                END IF;
            END;

            DROP TRIGGER IF EXISTS upd_customer_branch;

            CREATE TRIGGER upd_customer_branch AFTER UPDATE ON customer_branch FOR EACH ROW
            BEGIN

                DECLARE _customer_id int;

                -- 顧客向けシステムから登録した場合は処理を終了する
                IF new.update_type = 0 THEN 

                    -- すでに連携済みのデータが存在する場合は更新を行う
                    IF EXISTS ( SELECT 1 FROM cu_customer_branch WHERE core_id = new.id ) THEN

                    UPDATE cu_customer_branch
                    SET 
                        customer_branch_name             = new.customer_branch_name              -- 顧客支店名
                    ,customer_branch_name_kana        = new.customer_branch_name_kana         -- 顧客支店名：カナ
                    ,zip                              = new.customer_branch_zip_code          -- 顧客支店所在地：郵便番号
                    ,prefecture                       = new.customer_branch_prefecture        -- 顧客支店所在地：都道府県コード
                    ,city                             = new.customer_branch_city              -- 顧客支店所在地：市区町村
                    ,address                          = new.customer_branch_address           -- 顧客支店所在地：番地（町名以降）
                    ,update_system_type               = 1                                     -- 固定値：基幹システム
                    ,update_user_id                   = 0                                     -- 固定値：システム自動連携
                    ,status                           = ISNULL( new.delete_timestamp )        -- ステータス
                    WHERE core_id = new.id
                    ;

                    -- 連携済みのデータが存在しない場合は登録を行う
                    ELSE

                    SELECT customer_id 
                    INTO _customer_id
                    FROM cu_customer
                    WHERE core_id = new.customer_id; 

                    INSERT INTO cu_customer_branch ( 
                        customer_id
                    , core_id
                    , customer_branch_name
                    , customer_branch_name_kana
                    , zip
                    , prefecture
                    , city
                    , address
                    , create_system_type
                    , create_user_id
                    , status 
                    )
                    VALUES (
                        _customer_id                                -- 顧客ID
                    , new.id                                        -- 基幹システム連携ID
                    , new.customer_branch_name                      -- 顧客支店名
                    , new.customer_branch_name_kana                 -- 顧客支店名：カナ
                    , new.customer_branch_zip_code                  -- 顧客支店所在地：郵便番号
                    , new.customer_branch_prefecture                -- 顧客支店所在地：都道府県コード
                    , new.customer_branch_city                      -- 顧客支店所在地：市区町村
                    , new.customer_branch_address                   -- 顧客支店所在地：番地（町名以降）
                    , 1                                             -- 固定値：基幹システム
                    , 0                                             -- 固定値：システム自動連携
                    , ISNULL( new.delete_timestamp )                -- ステータス
                    );
                    END IF;
                END IF;
            END;

            DROP TRIGGER IF EXISTS ins_customer_user;

            CREATE TRIGGER ins_customer_user AFTER INSERT ON customer_user FOR EACH ROW
            BEGIN

                DECLARE _customer_id, _customer_branch_id int;

                -- 顧客向けシステムから登録した場合は処理を終了する
                IF new.register_type = 0 THEN 

                    SELECT customer_id,  customer_branch_id
                    INTO  _customer_id, _customer_branch_id
                    FROM  cu_customer_branch
                    WHERE core_id = new.customer_branch_id;

                    INSERT INTO cu_customer_user (
                    customer_id
                    , customer_branch_id
                    , core_id
                    , customer_user_name
                    , customer_user_name_kana
                    , customer_user_division_name
                    , customer_user_email
                    , customer_user_tel
                    , customer_reminder_sms_flag
                    , create_system_type
                    , create_user_id
                    , status
                    )
                    VALUES (
                    _customer_id                                  -- 顧客会社ID
                    , _customer_branch_id                           -- 顧客支店ID
                    , new.id                                        -- 基幹システム連携ID
                    , new.customer_user_name                        -- 顧客担当者名
                    , new.customer_user_name_kana                   -- 顧客担当者名：カナ
                    , new.customer_user_division_name               -- 顧客担当者部署名
                    , new.customer_user_email                       -- 顧客担当者：メールアドレス
                    , new.customer_user_tel                         -- 顧客担当者：携帯電話番号
                    , new.customer_reminder_sms_flag                -- 顧客担当者：SMSリマインド送付有無
                    , 1                                             -- 固定値：基幹システム
                    , 0                                             -- 固定値：システム自動連携
                    , ISNULL( new.delete_timestamp )                -- ステータス
                    );

                END IF;
            END;

            DROP TRIGGER IF EXISTS ins_parking;

            CREATE TRIGGER ins_parking AFTER INSERT ON parking FOR EACH ROW
            BEGIN

                -- 顧客向けシステムから登録した場合は処理を終了する
                IF new.register_type = 0 THEN 

                    INSERT INTO cu_parking ( 
                    core_id, 
                    parking_name, 
                    parking_name_kana, 
                    latitude , 
                    longitude , 
                    create_system_type, 
                    create_user_id, 
                    status
                    ) VALUES (
                    new.id                                    -- 基幹システム連携ID
                    , new.parking_name                          -- 駐車場名
                    , new.parking_name_kana                     -- 駐車場名：カナ
                    , new.latitude                              -- 緯度
                    , new.longitude                             -- 経度
                    , 1                                         -- 固定値：基幹システム
                    , 0                                         -- 固定値：システム自動連携
                    , ISNULL( new.delete_timestamp )            -- ステータス
                    );
                    
                END IF;
            END;

            DROP TRIGGER IF EXISTS upd_parking;

            CREATE TRIGGER upd_parking AFTER UPDATE ON parking FOR EACH ROW
            BEGIN

                -- 顧客向けシステムから登録した場合は処理を終了する
                IF new.update_type = 0 THEN 

                    -- すでに連携済みのデータが存在する場合は更新を行う
                    IF EXISTS ( SELECT 1 FROM cu_parking WHERE core_id = new.id ) THEN
                
                    UPDATE cu_parking
                    SET 
                        parking_name             = new.parking_name                     -- 駐車場名
                    ,parking_name_kana        = new.parking_name_kana                -- 駐車場名：カナ
                    ,latitude                 = new.latitude                         -- 緯度
                    ,longitude                = new.longitude                        -- 経度
                    ,update_system_type       = 1                                    -- 固定値：基幹システム
                    ,update_user_id           = 0                                    -- 固定値：システム自動連携
                    ,status                   = ISNULL( new.delete_timestamp )       -- ステータス
                    WHERE core_id = new.id
                    ;
                    -- 連携済みのデータが存在しない場合は登録を行う
                    ELSE
                
                    INSERT INTO cu_parking ( 
                        core_id, 
                        parking_name, 
                        parking_name_kana, 
                        latitude , longitude , 
                        create_system_type, 
                        create_user_id, 
                        status  )
                    VALUES (
                        new.id                                    -- 基幹システム連携ID
                    , new.parking_name                          -- 駐車場名
                    , new.parking_name_kana                     -- 駐車場名：カナ
                    , new.latitude                              -- 緯度
                    , new.longitude                             -- 経度
                    , 1                                         -- 固定値：基幹システム
                    , 0                                         -- 固定値：システム自動連携
                    , ISNULL( new.delete_timestamp )            -- ステータス
                    );
                    
                    END IF;
                END IF;
            END;

            DROP TRIGGER IF EXISTS ins_user_branch;

            CREATE TRIGGER ins_user_branch AFTER INSERT ON user_branch FOR EACH ROW
            BEGIN

                -- 顧客向けシステムから登録した場合は処理を終了する
                IF new.register_type = 0 THEN 

                    INSERT INTO cu_branch( 
                    core_id
                    , branch_name
                    , prefecture
                    , city
                    , address
                    , tel
                    , fax
                    , zip_code
                    , bank_account
                    , create_system_type
                    , create_user_id
                    , status
                    )
                    VALUES (
                    new.id                                    -- 基幹システム連携ID
                    , new.user_branch_name                      -- 支店名
                    , new.user_branch_prefecture                -- 都道府県コード
                    , new.user_branch_city                      -- 市区町村
                    , new.user_branch_address                   -- 番地（町名以降）
                    , new.user_branch_tel                       -- 電話番号
                    , new.user_branch_fax                       -- FAX
                    , new.user_branch_zip_code                  -- 郵便番号
                    , new.user_branch_deposit_bank_account      -- 敷金口座名
                    , 1                                         -- 固定値：基幹システム
                    , 0                                         -- 固定値：システム自動連携
                    , ISNULL( new.delete_timestamp )            -- ステータス
                    );
                END IF;
            END;

            DROP TRIGGER IF EXISTS upd_user_branch;

            CREATE TRIGGER upd_user_branch AFTER UPDATE ON user_branch FOR EACH ROW
            BEGIN

                -- 顧客向けシステムから登録した場合は処理を終了する
                IF new.update_type = 0 THEN 

                    -- すでに連携済みのデータが存在する場合は更新を行う
                    IF EXISTS ( SELECT 1 FROM cu_branch WHERE core_id = new.id ) THEN

                    UPDATE cu_branch
                    SET 
                        branch_name              = new.user_branch_name                   -- 支店名
                        ,prefecture               = new.user_branch_prefecture             -- 都道府県コード
                        ,city                     = new.user_branch_city                   -- 市区町村
                        ,address                  = new.user_branch_address                -- 番地（町名以降）
                        ,tel                      = new.user_branch_tel                    -- 電話番号
                        ,fax                      = new.user_branch_fax                    -- FAX
                        ,zip_code                 = new.user_branch_zip_code               -- 郵便番号
                        ,bank_account             = new.user_branch_deposit_bank_account   -- 敷金口座名    
                        ,update_system_type       = 1                                      -- 固定値：基幹システム
                        ,update_user_id           = 0                                      -- 固定値：システム自動連携
                        ,status                   = ISNULL( new.delete_timestamp )         -- ステータス
                    WHERE core_id = new.id;
                    
                    -- 連携済みのデータが存在しない場合は登録を行う
                    ELSE
                    INSERT INTO cu_branch( 
                        core_id
                    , branch_name
                    , prefecture
                    , city
                    , address
                    , tel
                    , fax
                    , zip_code
                    , bank_account
                    , create_system_type
                    , create_user_id
                    , status
                    )
                    VALUES (
                        new.id                                    -- 基幹システム連携ID
                    , new.user_branch_name                      -- 支店名
                    , new.user_branch_prefecture                -- 都道府県コード
                    , new.user_branch_city                      -- 市区町村
                    , new.user_branch_address                   -- 番地（町名以降）
                    , new.user_branch_tel                       -- 電話番号
                    , new.user_branch_fax                       -- FAX
                    , new.user_branch_zip_code                  -- 郵便番号
                    , new.user_branch_deposit_bank_account      -- 敷金口座名
                    , 1                                         -- 固定値：基幹システム
                    , 0                                         -- 固定値：システム自動連携
                    , ISNULL( new.delete_timestamp )            -- ステータス 
                    );
                    END IF;
                END IF;
            END;

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
                    , new.request_other_deadline                    -- 着手期限日
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
                    , request_other_deadline             = new.request_other_deadline             -- 着手期限日
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
                    , new.request_other_deadline                    -- 着手期限日
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
                    , contact_memo
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
                    , _invoice_status                               -- 請求ステータス
                    , _contact_memo                                 -- 連絡メモ
                );

                -- 契約ステータスの更新
                UPDATE cu_contract
                SET 
                    contract_status = 3,
                    update_system_type = 1
                WHERE contract_id = _contract_id;
            END;
        ";
    }
    
}
