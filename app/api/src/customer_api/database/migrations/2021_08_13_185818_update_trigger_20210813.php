<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateTrigger20210813 extends Migration
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
            DROP TRIGGER IF EXISTS upd_cu_project;

            CREATE TRIGGER upd_cu_project  AFTER UPDATE ON cu_project FOR EACH ROW
            BEGIN

                -- 顧客システムによる更新の場合
                IF new.update_system_type = 2 THEN

                    UPDATE project 
                    SET
                        update_timestamp = CURRENT_TIMESTAMP
                    , construction_number = new.construction_number
                    , site_name = new.site_name
                    , site_name_kana = new.site_name_kana
                    , site_prefecture = new.site_prefecture
                    , site_city = new.site_city
                    , site_address = new.site_address
                    , latitude = new.latitude
                    , longitude = new.longitude
                    , update_type = 1
                    , lastupdate_user_id = 1
                    WHERE
                    id = new.core_id;

                END IF;
            END;

            DROP TRIGGER IF EXISTS upd_cu_request;
            CREATE TRIGGER upd_cu_request  AFTER UPDATE ON cu_request FOR EACH ROW
            BEGIN

                DECLARE _customer_id, _subcontract_customer_id, _subcontract_customer_branch_id, _subcontract_customer_user_id INT;
                DECLARE _subcontract_customer_natural_id, _subcontract_customer_branch_natural_id, _subcontract_customer_user_natural_id VARCHAR(255);
                
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


                    
                    -- 顧客情報（下請）の取得
                    -- ☆名前のみで一致させるため、同一名称の顧客がヒットする可能性がある    
                    IF new.subcontract_name IS NOT NULL THEN
                    

                        -- 顧客IDを取得
                        SELECT customer_id
                        INTO _customer_id
                        FROM cu_project
                        WHERE project_id = new.project_id;
                    
                    
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
                    WHERE id = new.project_id;
                    
                    END IF;
                    END IF;
                END IF;
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
                    AND create_system_type = 2;

                    -- 発注処理
                    IF new.estimate_status = 2 THEN
                    UPDATE estimate
                        SET 
                        latest_pdf_purchase_order_url = _latest_pdf_purchase_order_url,
                        purchase_order_upload_date    = CURRENT_TIMESTAMP,
                        purchase_order_register_type  = 1,           
                        update_timestamp              = CURRENT_TIMESTAMP,
                        update_type                   = 1,
                        lastupdate_user_id            = 1
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
