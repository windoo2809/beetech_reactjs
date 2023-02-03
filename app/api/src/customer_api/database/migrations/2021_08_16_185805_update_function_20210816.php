<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateFunction20210816 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared($this->createFunction());
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
            DROP FUNCTION IF EXISTS isFileExists;

            CREATE FUNCTION isFileExists( _file_type INT, _ref_id INT )
            RETURNS BOOL
            BEGIN
                DECLARE _retval BOOL;
                
                SET _retval = FALSE;
                
                --  ファイル種別ごとに処理を分岐し、ファイルの存在をチェック
                SELECT IF( COUNT(*) > 0, TRUE, FALSE ) 
                    INTO _retval 
                    FROM cu_file 
                WHERE 
                    CASE _file_type
                    WHEN 1 THEN project_id     -- 1: 工事
                    WHEN 2 THEN request_id     -- 2:見積依頼 
                    WHEN 3 THEN estimate_id    -- 3:見積
                    WHEN 4 THEN estimate_id    -- 4:発注
                    WHEN 5 THEN contract_id    -- 5:契約
                    WHEN 6 THEN contract_id    -- 6:請求
                    WHEN 7 THEN project_id     -- 7:メッセージ
                    ELSE NULL
                    END = _ref_id
                AND file_type = _file_type
                AND status;
                
                RETURN _retval;
                
            END;

            DROP FUNCTION IF EXISTS isFileExistsWithDetail;

            CREATE FUNCTION isFileExistsWithDetail( _file_type INT, _file_detail_type INT, _ref_id INT )
            RETURNS BOOL
            BEGIN
                DECLARE _retval BOOL;
                
                SET _retval = FALSE;
                
                --  ファイル種別ごとに処理を分岐し、ファイルの存在をチェック
                --  1: 工事 2:見積依頼 3:見積 4:発注 5:契約 6:請求 7:メッセージ 
                SELECT IF( COUNT(*) > 0, TRUE, FALSE ) 
                    INTO _retval 
                    FROM cu_file 
                WHERE 
                    CASE _file_type
                    WHEN 1 THEN project_id
                    WHEN 2 THEN request_id
                    WHEN 3 THEN estimate_id
                    WHEN 4 THEN estimate_id
                    WHEN 5 THEN contract_id
                    WHEN 6 THEN contract_id
                    WHEN 7 THEN project_id
                    ELSE NULL
                    END = _ref_id
                AND file_type = _file_type
                AND file_detail_type = _file_detail_type
                AND status;

                RETURN _retval;

            END;

            DROP FUNCTION  IF EXISTS createLoginUserID;

            CREATE FUNCTION createLoginUserID( _core_id int, _role smallint )
            RETURNS INT
            BEGIN

                DECLARE ret INT DEFAULT 0;
                DECLARE _user_id, _customer_id, _customer_branch_id, _customer_user_id INT;
                DECLARE _customer_user_name, _customer_user_name_kana    VARCHAR(255);
                DECLARE _customer_user_email VARCHAR(2048);
                DECLARE _customer_user_tel VARCHAR(13);
                DECLARE _customer_reminder_sms_flag BOOL;

                -- 顧客担当者情報（顧客向けシステム）の取得
                SELECT 
                    customer_id,
                    customer_branch_id,
                    customer_user_id,
                    customer_user_name, 
                    customer_user_name_kana, 
                    customer_reminder_sms_flag, 
                    customer_user_email, 
                    customer_user_tel
                INTO
                    _customer_id,
                    _customer_branch_id,
                    _customer_user_id,
                    _customer_user_name, 
                    _customer_user_name_kana, 
                    _customer_reminder_sms_flag, 
                    _customer_user_email, 
                    _customer_user_tel
                FROM cu_customer_user
                WHERE core_id = _core_id;

                -- ユーザー情報が取得できなかった場合、処理終了
                IF _customer_user_id IS NULL THEN

                    SET ret = -1;  -- ユーザー情報取得エラー
                    RETURN ret;

                ELSEIF _customer_user_email IS NULL THEN

                    SET ret = -2;  -- メールアドレス取得エラー
                    RETURN ret;

                END IF;
                
                -- cu_user_branchの存在確認
                IF ( NOT EXISTS (
                    SELECT 1 
                    FROM cu_user cu
                    INNER JOIN cu_user_branch cub ON cub.user_id = cu.user_id
                    WHERE cub.customer_user_id = _customer_user_id )) THEN

                    -- 同一ログインユーザーの存在確認
                    SELECT user_id
                    INTO _user_id
                    FROM cu_user
                    WHERE login_id = _customer_user_email
                    AND customer_id = _customer_id; 
                            
                    -- ユーザー情報が存在しない場合
                    IF _user_id IS NULL THEN

                    -- cu_userへの登録
                    INSERT INTO cu_user(
                        customer_id,
                        login_id,
                        password,
                        role,
                        customer_user_name, 
                        customer_user_name_kana, 
                        customer_reminder_sms_flag, 
                        customer_user_tel,
                        create_user_id,
                        create_system_type
                    ) VALUES (
                        _customer_id,
                        _customer_user_email,
                        'SHOKIPASS',
                        _role,
                        _customer_user_name, 
                        _customer_user_name_kana, 
                        _customer_reminder_sms_flag,
                        _customer_user_tel,
                        1,
                        1        
                    );
                    
                    SET _user_id = LAST_INSERT_ID();
                        
                    END IF;

                    -- cu_user_branchへの登録
                    INSERT INTO cu_user_branch (
                    user_id,
                    customer_id,
                    customer_branch_id,
                    customer_user_id,
                    belong,
                    create_user_id,
                    create_system_type
                    ) VALUES (
                    _user_id,
                    _customer_id,
                    _customer_branch_id,
                    _customer_user_id,
                    TRUE,
                    1,
                    1
                    );
                    
                    SET ret = 1;

                END IF;
                
                RETURN ret;
                
            END;

        ";
    }
}
