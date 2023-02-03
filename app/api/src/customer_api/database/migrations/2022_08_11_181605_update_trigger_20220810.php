<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateTrigger20220810 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared($this->createFunction());
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
    private function createFunction() 
    {
        return "
            DROP FUNCTION IF EXISTS getULID;
            
            CREATE FUNCTION getULID()
            RETURNS CHAR(26)
            BEGIN
            
            DECLARE _ulid_id INT;
            DECLARE _ulid CHAR(26);
            
            -- ULIDの取得
            SELECT 
                id,
                ulid
            INTO
                _ulid_id,
                _ulid
            FROM cu_ulid
            WHERE NOT USED
            LIMIT 1
            FOR UPDATE;
            
            -- 利用したULIDを使用済みにする
            UPDATE cu_ulid
            SET used = TRUE
            WHERE id = _ulid_id;
            
            RETURN _ulid;
            
            END;
        ";
    }

    private function createTrigger()
    {
        return "
            -- ユーザー所属支店（登録）更新日 2022/08/09
            DROP TRIGGER IF EXISTS ins_cu_user_branch;
            
            CREATE TRIGGER ins_cu_user_branch  BEFORE INSERT ON cu_user_branch FOR EACH ROW
            BEGIN
            
            DECLARE _customer_id, _customer_branch_id, _customer_user_id, _cu_customer_user_id INT;
            DECLARE _customer_natural_id, _customer_branch_natural_id, _customer_user_natural_id
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
            
                -- 顧客担当者IDの作成
                SELECT CONCAT( 'CUU', LPAD( MAX( SUBSTR( customer_user_natural_id, 4,7 )) + 1, 7, 0 ),
                CASE 
                    MOD( SUBSTR( LPAD( MAX( substr( customer_user_natural_id, 4,7 )) + 1, 7, 0 ), 4, 1) +
                        SUBSTR( LPAD( MAX( substr( customer_user_natural_id, 4,7 )) + 1, 7, 0 ), 5, 1) +
                        SUBSTR( LPAD( MAX( substr( customer_user_natural_id, 4,7 )) + 1, 7, 0 ), 6, 1) +
                        SUBSTR( LPAD( MAX( substr( customer_user_natural_id, 4,7 )) + 1, 7, 0 ), 7, 1) +
                        SUBSTR( LPAD( MAX( substr( customer_user_natural_id, 4,7 )) + 1, 7, 0 ), 8, 1) +
                        SUBSTR( LPAD( MAX( substr( customer_user_natural_id, 4,7 )) + 1, 7, 0 ), 9, 1) +
                        SUBSTR( LPAD( MAX( substr( customer_user_natural_id, 4,7 )) + 1, 7, 0 ), 10, 1) , 26)
                WHEN 0 THEN 'A'
                WHEN 1 THEN 'B'
                WHEN 2 THEN 'C'
                WHEN 3 THEN 'D'
                WHEN 4 THEN 'E'
                WHEN 5 THEN 'F'
                WHEN 6 THEN 'G'
                WHEN 7 THEN 'H'
                WHEN 8 THEN 'I'
                WHEN 9 THEN 'J'
                WHEN 10 THEN 'K'
                WHEN 11 THEN 'L'
                WHEN 12 THEN 'M'
                WHEN 13 THEN 'N'
                WHEN 14 THEN 'O'
                WHEN 15 THEN 'P'
                WHEN 16 THEN 'Q'
                WHEN 17 THEN 'R'
                WHEN 18 THEN 'S'
                WHEN 19 THEN 'T'
                WHEN 20 THEN 'U'
                WHEN 21 THEN 'V'
                WHEN 22 THEN 'W'
                WHEN 23 THEN 'X'
                WHEN 24 THEN 'Y'
                WHEN 25 THEN 'Z'
                ELSE '0'
                END )
                INTO _customer_user_natural_id
                FROM customer_user
                WHERE customer_user_natural_id like 'CUU%';
                
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
                ,_customer_user_natural_id
                ,1
                ,getULID()
                ,ADDTIME( CURRENT_TIMESTAMP, '9:00:00' )
                ,ADDTIME( CURRENT_TIMESTAMP, '9:00:00' )
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
        ";
    }
}
