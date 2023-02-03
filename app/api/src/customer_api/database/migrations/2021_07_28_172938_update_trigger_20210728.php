<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateTrigger20210728 extends Migration
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
        DB::unprepared(file_get_contents(__DIR__ . "/file/create_trigger_20210712.sql"));
    }

    public function createTrigger()
    {
        return "
            DROP TRIGGER IF EXISTS ins_customer_user;

            CREATE TRIGGER ins_customer_user AFTER INSERT ON customer_user FOR EACH ROW
            BEGIN

            DECLARE _customer_id, _customer_branch_id int;

            -- 基幹システムで登録した場合
            IF new.update_type = 0 THEN  

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
            
            DROP TRIGGER IF EXISTS upd_customer_user;

            CREATE TRIGGER upd_customer_user AFTER UPDATE ON customer_user FOR EACH ROW
            BEGIN

            DECLARE _customer_id, _customer_branch_id int;

            -- 基幹システムで登録した場合
            IF new.update_type = 0 THEN

                -- すでに連携済みのデータが存在する場合は更新を行う
                IF EXISTS ( SELECT 1 FROM cu_customer_user WHERE core_id = new.id ) THEN
                UPDATE cu_customer_user
                SET 
                    customer_user_name             = new.customer_user_name               -- 顧客担当者名
                    ,customer_user_name_kana        = new.customer_user_name_kana          -- 顧客担当者名：カナ
                    ,customer_user_division_name    = new.customer_user_division_name      -- 顧客担当者部署名
                    ,customer_user_email            = new.customer_user_email              -- 顧客担当者：メールアドレス
                    ,customer_user_tel              = new.customer_user_tel                -- 顧客担当者：携帯電話番号
                    ,customer_reminder_sms_flag     = new.customer_reminder_sms_flag       -- 顧客担当者：SMSリマインド送付有無
                    ,update_system_type             = 1                                    -- 固定値：基幹システム
                    ,update_user_id                 = 0                                    -- 固定値：システム自動連携
                    ,status                         = ISNULL( new.delete_timestamp )       -- ステータス
                WHERE core_id = new.id
                ;

            -- 連携済みのデータが存在しない場合は登録を行う
            ELSE

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
            END IF;
            END;
            
            -- トリガー： 依頼（登録） 更新日：21/07/14
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
                );
            END IF;
            END;

            -- トリガー： 見積依頼（更新） 更新日：21/07/14
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
                ,update_system_type                  = 1                                      -- 固定値：基幹システム
                ,update_user_id                      = 0                                      -- 固定値：システム自動連携
                ,status                               = ISNULL( new.delete_timestamp )        -- ステータス
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
                );
                END IF;
            END IF;
            END;
            
            -- トリガー： 見積依頼情報(更新) 更新日：21/07/14
            DROP TRIGGER IF EXISTS upd_cu_request;

            CREATE TRIGGER upd_cu_request  AFTER UPDATE ON cu_request FOR EACH ROW
            BEGIN

            -- 顧客システムによる更新の場合
            IF new.update_system_type = 2 THEN
            
                -- 見積依頼ステータスが未調査、Web調査、現地調査までを更新可能とする
                IF old.request_status IN ( 0, 1, 2 )  THEN
            
                UPDATE request
                    SET 
                    update_timestamp                = CURRENT_TIMESTAMP    -- レコード更新日
                    , want_start_date                 = new.want_start_date  -- 利用期間：開始
                    , want_end_date                   = new.want_end_date    -- 利用期間：終了
                    , car_qty                         = new.car_qty
                    , light_truck_qty                 = new.light_truck_qty
                    , truck_qty                       = new.truck_qty
                    , other_car_qty                   = new.other_car_qty
                    , other_car_detail                = new.other_car_detail
                    , request_other_deadline          = new.request_other_deadline
                    , request_other_start_date        = new.request_other_start_date
                    , request_other_end_date          = new.request_other_end_date
                    , request_other_qty               = new.request_other_qty
                    , want_guide_type                 = new.want_guide_type
                    , cc_email                        = new.cc_email
                    , customer_other_request          = new.customer_other_request
                    , register_type                   = 1
                    , update_type                     = 1
                    , request_cancel_date             =
                    CASE
                        WHEN old.request_status IN ( 0, 1, 2 ) AND new.request_status = 9 THEN CURRENT_DATE
                        ELSE NULL
                    END                                                 -- 調査途中終了となった場合、キャンセル日付を入れる
                    , lastupdate_user_id             = 1
                WHERE id = new.core_id;

                END IF;
            END IF;
            END;

            -- トリガー： 見積依頼対象駐車場（登録） 更新日：21/07/22
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
            );

            END;

            -- トリガー： 契約（更新）2021/07/22
            -- 契約時に基幹側を更新する処理なし
            -- 顧客システム側のみでステータス管理

            -- ユーザー情報（更新）
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
                ,cu.update_timestamp                = CURRENT_TIMESTAMP
                ,cu.customer_user_name              = new.customer_user_name
                ,cu.customer_user_name_kana         = new.customer_user_name_kana
                ,cu.customer_user_email             = new.login_id
                ,cu.customer_user_tel               = new.customer_user_tel
                ,cu.customer_reminder_sms_flag      = new.customer_reminder_sms_flag
                ,cu.update_type                     = 1
                WHERE cub.user_id = new.user_id;
                
            END IF;

            END;

            -- ユーザー所属支店（登録）更新日 2021/07/22
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
                )
                VALUES (
                _customer_id
                ,_customer_natural_id
                ,_customer_branch_id
                ,_customer_branch_natural_id
                ,CONCAT('CUCS', LPAD( _customer_branch_id, 5, '0' ), LPAD( new.user_id,6,'0' ))
                ,1
                ,getULID()
                ,CURRENT_TIMESTAMP
                ,CURRENT_TIMESTAMP
                ,_customer_user_name
                ,_customer_user_name_kana
                ,_login_id
                ,_customer_user_tel
                ,_customer_reminder_sms_flag
                ,1
                ,1
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
