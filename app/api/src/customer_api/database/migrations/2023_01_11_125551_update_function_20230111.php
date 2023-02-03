<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateFunction20230111 extends Migration
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
        -- ファンクション定義
        -- 更新日 2021/12/27 UM 山口
        
        
        -- データスコープ取得関数
        -- 更新日 2021/06/09 UM 山口
        DROP FUNCTION IF EXISTS getDataScope;
        
        
        CREATE FUNCTION getDataScope( _user_id int )
        RETURNS INT
        BEGIN
        
          DECLARE _role, _data_scope, _customer_system_use_flag int;
        
          SELECT u.role, c.customer_system_use_flag, co.data_scope
          INTO _role,_customer_system_use_flag, _data_scope
          FROM cu_user u
           INNER JOIN cu_customer c ON c.customer_id = u.customer_id
           INNER JOIN cu_customer_option co ON co.customer_id = c.customer_id 
          WHERE u.user_id = _user_id;
        
          -- データスコープの設定
          SET _data_scope = 
            CASE
             WHEN _role = 0 THEN 0           -- 全体参照
             WHEN _role = 1 THEN 0           -- 全体参照
             WHEN _data_scope = 0 THEN 0     -- 全体参照
             WHEN _data_scope = 1 THEN 1     -- 支店単位
             WHEN _data_scope = 2 AND _role IN (2,3) THEN 1 -- 支店単位（権限あり）
             WHEN _data_scope = 2 AND _role = 4 THEN 2 -- 担当者単位
             ELSE NULL
            END;
        
          RETURN _data_scope;
        
        END;
        
        
        -- ULID取得関数
        -- 更新日 2021/01/26 UM 山口
        
        DROP FUNCTION IF EXISTS getULID;
        
        
        CREATE FUNCTION getULID()
        RETURNS CHAR(26)
        BEGIN
        
          DECLARE _ulid_id INT;
          DECLARE _ulid CHAR(26);
          
          -- ULIDの取得
          SELECT 
            pkid,
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
          WHERE pkid = _ulid_id;
          
          RETURN _ulid;
          
        END;
        
        
        -- カレンダー初期データ作成
        DROP FUNCTION IF EXISTS createCalendar;
        
        
        CREATE FUNCTION createCalendar( _days INT )
        RETURNS DATE
        BEGIN
        
          DECLARE _cnt INT DEFAULT 1;
          DECLARE _calendar_day DATE DEFAULT NULL;
          DECLARE _holiday BOOL DEFAULT FALSE;
            
          IF _days > 1 THEN 
        
            -- カレンダーの最大値を取得
            SELECT IFNULL( MAX(calendar_day), '2021/01/31' )  INTO _calendar_day FROM cu_calendar;
            
            make_calendar: LOOP
          
              -- 日付を＋1日
              SET _calendar_day = DATE_ADD( _calendar_day, INTERVAL 1 DAY );
              SET _holiday = 
                CASE WEEKDAY( _calendar_day )
                  WHEN 5 THEN TRUE
                  WHEN 6 THEN TRUE
                  ELSE FALSE
                END;
              
              -- カレンダーの作成
              INSERT INTO cu_calendar ( calendar_day, holiday ) VALUES ( _calendar_day, _holiday );
              
              -- 指定件数のデータを作成したらLOOPを終了
              IF _days = _cnt THEN 
                LEAVE make_calendar;
              END IF;
              
              SET _cnt = _cnt + 1;
              
            END LOOP make_calendar;
        
          END IF;
          
          RETURN _calendar_day;
          
        END;
        
        
        -- 営業日計算
        -- 更新日 2021/08/17
        DROP FUNCTION IF EXISTS getCalcBusinessDay;
        
        
        CREATE FUNCTION getCalcBusinessDay( _reference_date DATE, _days INT  )
        RETURNS DATE
        BEGIN
        
          DECLARE _calculated_date DATE;
          DECLARE _cnt INT DEFAULT 0;
          DECLARE cur1 CURSOR FOR SELECT calendar_day FROM cu_calendar WHERE calendar_day >= _reference_date AND NOT holiday ORDER BY calendar_day;
        
          IF _reference_date IS NULL THEN
            SET _reference_date = CURRENT_DATE;
          END IF;
        
          -- 初期値設定
          SET _calculated_date = _reference_date;
          
          OPEN cur1;
          
          IF _days >= 0 THEN
        
            
            REPEAT
          
              FETCH cur1 INTO _calculated_date;
              SET _cnt = _cnt + 1;
                    
            UNTIL _cnt > _days END REPEAT;
          
          END IF;
          
          -- カーソルのクローズ
          CLOSE cur1;
          
          RETURN _calculated_date;
        
        END;
        
        
        -- ファイル存在チェック
        -- 更新日 2021/08/16 UM 山口
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
        
        
        -- ファイル詳細存在チェック
        -- 更新日 2021/08/16 UM 山口
        
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
        
        
        -- ユーザー登録（基幹→顧客 ）
        -- 更新日 2021/08/16 UM 山口
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
        
        
        -- 進捗ステータス取得
        -- 更新日 2021/11/22 UM 山口
        DROP FUNCTION  IF EXISTS getProgressStatus;
        
        
        CREATE FUNCTION getProgressStatus( _request_id INT, _estimate_id INT )
        RETURNS INT
        BEGIN
        
          DECLARE _progress_status INT;
        
          IF _estimate_id IS NULL THEN
        
            SELECT
              case -- 進捗ステータス
                when r.request_type in ( 0,1,2,3 ) and r.request_status = 0 then 1 -- 見積処理中
                when r.request_type in ( 0,1,2,3 ) and r.request_status = 1 then 1 -- 見積処理中
                when r.request_type in ( 0,1,2,3 ) and r.request_status = 2 then 1 -- 見積処理中
                when r.request_type in ( 4,5,6 )   and r.request_status = 0 and r.request_other_status = 0 then 11 -- 受付済み    
                when r.request_type in ( 4,5,6 )   and r.request_status = 0 and r.request_other_status in ( 1,2,3,4,5 ) then 3 -- 完了 
                when r.request_type in ( 4,5,6 )   and r.request_status = 0 and r.request_other_status = 6 then 80 -- 完了
                when r.request_status = 7 then 99     -- キャンセル
                when r.request_status = 9 then 99     -- キャンセル
              end
            INTO _progress_status
            FROM cu_request r
            WHERE r.request_id = _request_id;
        
          ELSE
        
            SELECT 
              case -- 進捗ステータス
                when r.request_type in ( 0,1,2,3 ) and r.request_status = 0 then 1 -- 見積処理中
                when r.request_type in ( 0,1,2,3 ) and r.request_status = 1 then 1 -- 見積処理中
                when r.request_type in ( 0,1,2,3 ) and r.request_status = 2 then 1 -- 見積処理中
                when r.request_type in ( 0,1,2,3 ) and r.request_status = 3 and e.estimate_status = 0 then 1  -- 見積処理中
                when r.request_type in ( 0,1,2,3 ) and r.request_status = 3 and e.estimate_status = 1 then 2  -- 注文待ち
                when r.request_type in ( 0,1,2,3 ) and r.request_status = 3 and e.estimate_status = 2 then 3  -- 受注処理中
                when r.request_type in ( 0,1,2,3 ) and r.request_status = 3 and e.estimate_status = 3 then 3  -- 受注処理中
                when r.request_type in ( 0,1,2,3 ) and r.request_status = 3 and e.estimate_status = 4 then 3  -- 受注処理中
                when r.request_type in ( 0,1,2,3 ) and r.request_status = 3 and e.estimate_status = 5 and c.contract_status = 2  then 4  -- ご利用準備完了
                when r.request_type in ( 0,1,2,3 ) and r.request_status = 3 and e.estimate_status = 5 and c.contract_status = 3  then 5  -- ご契約待ち
                when r.request_type in ( 0,1,2,3 ) and r.request_status = 3 and e.estimate_status = 5 and c.contract_status = 4  then 6  -- ご契約中
                when r.request_type in ( 0,1,2,3 ) and r.request_status = 3 and e.estimate_status = 5 and c.contract_status = 5  then 80 -- 完了
                when r.request_type in ( 4,5,6 )   and r.request_status = 0 and r.request_other_status = 0 then 11 -- 受付済み    
                when r.request_type in ( 4,5,6 )   and r.request_status = 0 and r.request_other_status in ( 1,2,3,4,5 ) then 3 -- 完了 
                when r.request_type in ( 4,5,6 )   and r.request_status = 0 and r.request_other_status = 6 then 80 -- 完了
                when e.estimate_status = 6 then 99
                when e.estimate_status = 99 then 99
                when r.request_status = 7 then 99     -- キャンセル
                when r.request_status = 9 then 99     -- キャンセル
                when e.estimate_status = 7 then 99    -- キャンセル
                when e.estimate_status = 8 then 99    -- キャンセル
                when e.estimate_status = 9 then 99    -- キャンセル
                else 0                                -- ステータス取得不能
              end
            INTO _progress_status
            FROM cu_request r
              INNER JOIN cu_estimate e ON e.request_id = r.request_id
              LEFT JOIN cu_contract c ON c.estimate_id = e.estimate_id
            WHERE r.request_id = _request_id
              AND e.estimate_id = _estimate_id;
            
          END IF;
        
          RETURN IFNULL( _progress_status, 0 );
        
        END;        
        ";
    }
}
