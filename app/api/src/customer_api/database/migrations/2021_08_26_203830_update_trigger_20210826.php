<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateTrigger20210826 extends Migration
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
                        cu_lastupdate_user_id = new.update_user_id,
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
                    ,cu.cu_lastupdate_user_id = new.update_user_id     
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
                    AND delete_timestamp IS NULL
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
                    ,cu_lastupdate_user_id
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
                    WHERE ccu.core_id = _customer_user_id
                    AND status;  
                    
                    -- 顧客担当者IDをセット
                    SET new.customer_user_id = _cu_customer_user_id;
                
                END IF;

            END;

            DROP TRIGGER IF EXISTS ins_cu_information_target;
            CREATE TRIGGER ins_cu_information_target  BEFORE INSERT ON cu_information_target FOR EACH ROW
            BEGIN

                DECLARE _customer_id, _cusomer_branch_id, _customer_user_id INT;
                
                -- 顧客会社IDの取得
                SELECT  customer_id
                INTO   _customer_id
                FROM   cu_customer
                WHERE  core_id = new.core_customer_id;
                
                -- 顧客支店IDの取得
                SELECT  customer_branch_id
                INTO   _cusomer_branch_id
                FROM   cu_customer_branch
                WHERE  core_id = new.core_customer_branch_id;

                -- 顧客担当者IDの取得
                SELECT  customer_user_id
                INTO   _customer_user_id
                FROM   cu_customer_user
                WHERE  core_id = new.core_customer_user_id;

                SET NEW.customer_id = _customer_id, NEW.customer_branch_id = _cusomer_branch_id, NEW.customer_user_id = _customer_user_id;

            END;

            DROP TRIGGER IF EXISTS upd_cu_information_target;

            CREATE TRIGGER upd_cu_information_target  BEFORE UPDATE ON cu_information_target FOR EACH ROW
            BEGIN

                DECLARE _customer_id, _cusomer_branch_id, _customer_user_id INT;
                
                -- 顧客会社IDの取得
                SELECT  customer_id
                INTO   _customer_id
                FROM   cu_customer
                WHERE  core_id = new.core_customer_id;
                
                -- 顧客支店IDの取得
                SELECT  customer_branch_id
                INTO   _cusomer_branch_id
                FROM   cu_customer_branch
                WHERE  core_id = new.core_customer_branch_id;

                -- 顧客担当者IDの取得
                SELECT  customer_user_id
                INTO   _customer_user_id
                FROM   cu_customer_user
                WHERE  core_id = new.core_customer_user_id;

                SET NEW.customer_id = _customer_id, NEW.customer_branch_id = _cusomer_branch_id, NEW.customer_user_id = _customer_user_id;

            END;
        ";
    }
}
