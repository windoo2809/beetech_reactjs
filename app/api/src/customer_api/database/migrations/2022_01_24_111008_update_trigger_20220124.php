<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateTrigger20220124 extends Migration
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
        
    }

    private function createTrigger()
    {
        return "
            
        -- 更新日：22/01/23
        
        DROP TRIGGER IF EXISTS ins_request;
        CREATE TRIGGER ins_request AFTER INSERT ON request FOR EACH ROW
        BEGIN

            DECLARE _project_id int;

            -- 基幹システムからの更新の場合
            IF new.register_type = 0 THEN

                -- 工事情報の取得
                SELECT  project_id
                INTO   _project_id
                FROM   cu_project
                WHERE  core_id = new.project_id;
            
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
                , request_other_status
                , create_system_type
                , create_user_id
                , status
                , contact_memo
                ) 
                VALUES (
                _project_id                                   -- 工事ID
                , new.id                                        -- 基幹システム連携ID
                , new.request_natural_id                        -- 見積依頼NO（案件NO）
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
                , case new.survey_status
                    when 10 then 7
                    else new.survey_status
                end                                           -- 見積依頼ステータス／調査ステータス
                , new.request_other_status                      -- その他作業ステータス
                , 1                                             -- 固定値：基幹システム
                , 0                                             -- 固定値：システム自動連携
                , ISNULL( new.delete_timestamp )                -- ステータス 
                , new.contact_memo                              -- 連絡メモ
                );
            END IF;
            END;

            -- 更新日：22/01/23
            DROP TRIGGER IF EXISTS upd_bf_request;

            CREATE TRIGGER upd_bf_request BEFORE UPDATE ON request FOR EACH ROW
            BEGIN

            DECLARE _contact_memo TEXT;
            
            IF ifnull(new.contact_memo,'') = '' AND new.request_type IN ( 0,1,2,3) AND  new.survey_status = 0 THEN

                SELECT  CONCAT ( '登録を受け付けました。',
                DATE_FORMAT( getCalcBusinessDay( current_date,
                CASE
                    WHEN holiday THEN 0                  -- 休日の場合
                    WHEN hour( now())+9 <= 17 THEN 0     -- 営業時間内の場合
                    ELSE 1                               -- 上記以外の場合、翌営業日
                END ), '%m月%d日'),
                ' 18時までに見積を提示します。' )
                INTO _contact_memo
                FROM cu_calendar 
                WHERE calendar_day = DATE( ADDTIME(current_timestamp, '09:00:00' ));

            ELSEIF new.survey_status IN ( 9, 10 ) THEN

                SET _contact_memo = 'この見積依頼はキャンセルとなりました';

            END IF;
            
            SET new.contact_memo = _contact_memo;

            END;

            -- 更新日：22/01/24

            DROP TRIGGER IF EXISTS upd_request;
            DROP TRIGGER IF EXISTS upd_af_request;

            CREATE TRIGGER upd_af_request AFTER UPDATE ON request FOR EACH ROW
            BEGIN

                DECLARE _project_id, _request_id, _estimate_id, _estimate_core_id, _branch_id INT;
                DECLARE _estimate_natural_id VARCHAR(255);
                DECLARE _quote_subtotal_amt, _quote_tax_amt, _quote_total_amt, _survey_total_amt DECIMAL;
                DECLARE _survey_tax_in_flag BOOL;
                DECLARE _contact_memo TEXT;
                
                -- 基幹システムからの更新の場合
                IF new.update_type = 0 THEN
                
                    -- 工事情報の取得
                    SELECT  project_id
                    INTO   _project_id
                    FROM   cu_project
                    WHERE  core_id = new.project_id;
                
                    -- すでに連携済みのデータが存在する場合は更新を行う
                    IF EXISTS ( SELECT 1 FROM cu_request WHERE core_id = new.id ) THEN

                    UPDATE cu_request
                    SET
                        request_natural_id                 = new.request_natural_id                 -- 見積依頼NO（案件NO）
                    , request_date                       = new.request_date                       -- 依頼受付日
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
                    , request_status                     = 
                        case new.survey_status
                            when 10 then 7
                            else new.survey_status
                        end                                                                       -- 見積依頼ステータス／調査ステータス
                    , request_other_status               = new.request_other_status               -- その他作業ステータス
                    , update_system_type                 = 1                                      -- 固定値：基幹システム
                    , update_user_id                     = 0                                      -- 固定値：システム自動連携
                    , status                             = ISNULL( new.delete_timestamp )         -- ステータス
                    , contact_memo                       = new.contact_memo                       -- 連絡メモ
                    WHERE core_id = new.id
                    ;

                    -- 依頼種別がその他依頼（4,5,6）で、作業が完了(6)した場合、契約データを作成する
                    IF new.request_type IN ( 4, 5, 6 ) AND new.request_other_status = 6 THEN

                        -- 見積情報の取得
                        SELECT
                            e.id
                        , e.estimate_natural_id
                        , cr.request_id
                        , cp.branch_id
                        , e.survey_tax_in_flag
                        , e.survey_total_amt
                        , e.contact_memo
                        INTO
                            _estimate_core_id
                        , _estimate_natural_id
                        , _request_id
                        , _branch_id
                        , _survey_tax_in_flag
                        , _survey_total_amt
                        , _contact_memo
                        FROM cu_request cr
                        INNER JOIN cu_project cp ON cp.project_id = cr.project_id
                        INNER JOIN estimate e ON e.request_id = cr.core_id
                        WHERE cr.core_id = new.id;
                        
                        -- 見積データが存在するか確認
                        IF NOT EXISTS ( SELECT 1 FROM cu_estimate where core_id = _estimate_core_id ) THEN

                        -- 見積データの作成
                        INSERT INTO cu_estimate( 
                            core_id
                            , estimate_natural_id
                            , request_id
                            , project_id
                            , branch_id
                            , estimate_status
                            , survey_tax_in_flag
                            , survey_total_amt
                            , create_system_type
                            , create_user_id
                            , status
                            , contact_memo
                            ) VALUES (
                            _estimate_core_id                          -- 基幹システム連携ID
                            , _estimate_natural_id
                            , _request_id                                -- 見積依頼ID
                            , _project_id                                -- 工事ID
                            , _branch_id                                 -- 支店ID
                            , 4                                          -- 見積ステータス 4:受注
                            , _survey_tax_in_flag                        -- 調査見積見積金額：税込みフラグ
                            , _survey_total_amt                          -- 調査見積見積金額：見積合計
                            , 1                                          -- 固定値：基幹システム
                            , 0                                          -- 固定値：システム自動連携
                            , ISNULL( new.delete_timestamp )             -- ステータス
                            , _contact_memo                              -- 連絡メモ
                            );

                        END IF;

                        -- 契約データが存在するか確認
                        IF NOT EXISTS ( 
                        SELECT 1 
                        FROM cu_contract cc 
                            INNER JOIN cu_estimate ce ON cc.estimate_id = ce.estimate_id 
                        WHERE ce.core_id = _estimate_core_id ) THEN 
                        
                        SELECT
                            ce.estimate_id
                            ,e.quote_subtotal_amt
                            ,e.quote_tax_amt
                            ,e.quote_total_amt
                        INTO
                            _estimate_id 
                            , _quote_subtotal_amt
                            , _quote_tax_amt
                            , _quote_total_amt
                        FROM cu_estimate ce
                            INNER JOIN estimate e ON e.id = ce.core_id 
                        WHERE e.request_id = new.id;
                        
                        INSERT INTO cu_contract( 
                            core_id
                        , project_id
                        , estimate_id
                        , branch_id
                        , contract_status
                        , quote_subtotal_amt
                        , quote_tax_amt
                        , quote_total_amt
                        , quote_available_start_date
                        , quote_available_end_date
                        , create_system_type
                        , create_user_id
                        , status
                        ) VALUES (
                            _estimate_core_id                             -- 基幹システム連携ID
                        , _project_id                                   -- 工事ID
                        , _estimate_id                                  -- 見積ID
                        , _branch_id                                    -- 支店ID
                        , 5                                             -- 契約ステータス  5:契約完了
                        , _quote_subtotal_amt                           -- 確定見積：見積小計
                        , _quote_tax_amt                                -- 確定見積：税額
                        , _quote_total_amt                              -- 確定見積：合計額
                        , new.request_other_start_date                     -- 契約開始日
                        , new.request_other_end_date                       -- 契約終了日
                        , 1                                             -- 固定値：基幹システム
                        , 0                                             -- 固定値：システム自動連携
                        , ISNULL( new.delete_timestamp )                -- ステータス 
                        );
                        
                        END IF;
                    END IF;

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
                    , request_other_status
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
                    , case new.survey_status
                        when 10 then 7
                        else new.survey_status
                        end                                           -- 見積依頼ステータス／調査ステータス
                    , new.request_other_status                      -- その他作業ステータス
                    , 1                                             -- 固定値：基幹システム
                    , 0                                             -- 固定値：システム自動連携
                    , ISNULL( new.delete_timestamp )                -- ステータス
                    , new.contact_memo                              -- 連絡メモ
                    );
                    END IF;
                END IF;
            END;

            -- 更新日：22/01/19
            DROP TRIGGER IF EXISTS ins_estimate;

            CREATE TRIGGER ins_estimate AFTER INSERT ON estimate FOR EACH ROW
            BEGIN

                DECLARE _request_id, _project_id, _parking_id, _branch_id, _estimate_id INT;
                DECLARE _parking_name, _parking_name_kana VARCHAR(255);
                
                -- 基幹システムで作成した場合
                IF new.register_type = 0 THEN

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
                    , estimate_natural_id
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
                    , new.estimate_natural_id                       -- 見積NO
                    , _request_id                                   -- 見積依頼ID
                    , _project_id                                   -- 工事ID
                    , _parking_id                                   -- 駐車場ID
                    , _branch_id                                    -- 支店ID
                    , case                                          -- 見積ステータス
                        when new.estimate_status = 3 then 1        -- 3:調査見積送付済み → 1: 受注待ち
                        when new.estimate_status = 4 then 4           -- 4:受注 → 4:受注
                        when new.estimate_status = 5 then 5           -- 5:確定見積送付済 → 5:確定見積送付済
                        when new.estimate_status = 6 then 8           -- 6:失注（手動）   → 8:キャンセル
                        when new.estimate_status = 7 then 9           -- 7:失注（自動）   → 9:キャンセル
                        when new.estimate_status = 8 then 8           -- 8:請求キャンする → 8:キャンセル
                        else estimate_status                          -- 変更なし 
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
                --      , new.estimate_document_id                    -- クラウドサイン連携用ドキュメントID
                --      , new.estimate_file_id                        -- クラウドサイン連携用ファイルID
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
                        , 0                                             -- 契約延長区分
                        , 1                                             -- 固定値：基幹システム
                        , 0                                             -- 固定値：システム自動連携
                        , ISNULL( new.delete_timestamp )                -- ステータス 
                        );
                    END IF; 
                    END IF;
                END IF;
            END;

            -- 更新日：22/01/23
            DROP TRIGGER IF EXISTS upd_bf_estimate;

            CREATE TRIGGER upd_bf_estimate BEFORE UPDATE ON estimate FOR EACH ROW
            BEGIN

                DECLARE _contact_memo TEXT;
                
                IF old.purchase_order_check_flag = 0 AND new.purchase_order_check_flag = 1 THEN

                    SELECT  CONCAT ( '発注書を確認しました。',
                    DATE_FORMAT( getCalcBusinessDay( current_date,
                    CASE
                        WHEN holiday THEN 0                  -- 休日の場合
                        WHEN hour( now())+9 <= 15 THEN 0     -- 営業時間内の場合
                        ELSE 1                               -- 上記以外の場合、翌営業日
                    END ), '%m月%d日'),
                    CASE 
                        WHEN holiday THEN 'AM'                -- 休日の場合
                        WHEN hour( now())+9 <= 8  THEN 'AM'    -- 営業時間内の場合
                        WHEN hour( now())+9 <= 15 THEN 'PM'   -- 営業時間内の場合
                        ELSE 'AM'                                -- 上記以外の場合、翌営業日
                    END,
                    ' までに状況を更新します' )
                    INTO _contact_memo
                    FROM cu_calendar 
                    WHERE calendar_day = DATE( ADDTIME(current_timestamp, '09:00:00' ));

                    SET new.contact_memo = _contact_memo;
                
                ELSEIF new.estimate_status = 4 AND old.estimate_status <> 4 THEN
                
                    SELECT 
                    CONCAT( '受注完了しました。止める場所の区画図を手配中です。',
                        DATE_FORMAT( getCalcBusinessDay( current_date, 
                        CASE
                            WHEN holiday THEN 0                  -- 休日の場合
                            WHEN hour( now())+9 <= 18 THEN 0     -- 営業時間内の場合
                            ELSE 1                               -- 上記以外の場合、翌営業日
                        END ), '%m月%d日'),
                        'までにお送りします。' )
                    INTO _contact_memo
                    FROM cu_calendar 
                    WHERE calendar_day = DATE( ADDTIME(current_timestamp, '09:00:00' ));
                
                    SET new.contact_memo = _contact_memo;
                
                END IF;
            END;
            
            -- 更新日：22/01/23
            DROP TRIGGER IF EXISTS upd_estimate;
            DROP TRIGGER IF EXISTS upd_af_estimate;

            CREATE TRIGGER upd_af_estimate AFTER UPDATE ON estimate FOR EACH ROW
            BEGIN

                DECLARE _request_id, _project_id, _parking_id, _branch_id, _estimate_id, _application_id int;
                DECLARE _application_status, _request_type, _request_other_status SMALLINT;
                DECLARE _parking_name, _parking_name_kana varchar(255);

                -- 基幹システムで更新した場合のみ実行
                IF new.update_type = 0 THEN

                    -- 見積依頼情報の取得
                    SELECT
                    request_id
                    , project_id
                    , request_type
                    , request_other_status
                    INTO
                    _request_id
                    , _project_id
                    , _request_type
                    , _request_other_status
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
                    IF _estimate_id IS NOT NULL THEN

                    -- 見積データを更新
                    UPDATE cu_estimate
                    SET
                        estimate_natural_id         = new.estimate_natural_id
                    , request_id                  = _request_id
                    , project_id                  = _project_id
                    , parking_id                  = _parking_id
                    , branch_id                   = _branch_id
                    , estimate_status             = 
                        case                                          -- 見積ステータス
                        when new.estimate_status = 3 AND cu_estimate.estimate_status  = 1 then 1   -- 3:調査見積送付済み → 1: 受注待ち
                        when new.estimate_status = 3 AND cu_estimate.estimate_status  = 2 then 2   -- 3:調査見積送付済み → 2: 発注書受領
                        when new.estimate_status = 4 then 4           -- 4:受注 → 4:受注
                        when new.estimate_status = 5 then 5           -- 5:確定見積送付済 → 5:確定見積送付済
                        when new.estimate_status = 6 then 8           -- 6:失注（手動）   → 8:キャンセル
                        when new.estimate_status = 7 then 9           -- 7:失注（自動）   → 9:キャンセル
                        when new.estimate_status = 8 then 8           -- 8:請求キャンする → 8:キャンセル
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
                    WHERE estimate_id = _estimate_id;

                    -- 受注時に未承認だった場合の処理
                    IF new.estimate_status = 4  THEN
                    
                        -- 申請対象かの確認
                        IF _request_type IN ( 0,1,2,3 ) AND
                        EXISTS ( SELECT 1
                        FROM cu_customer_option cco
                        INNER JOIN cu_project cp ON cp.customer_id = cco.customer_id
                        WHERE cco.plan_type = 1
                        AND cco.approval = TRUE
                        AND cp.project_id = _project_id ) THEN
                        
                        -- 申請対象の顧客の場合、申請データの確認
                        SELECT application_id, application_status
                        INTO _application_id, _application_status
                        FROM cu_application ap
                            INNER JOIN cu_estimate ce ON ce.estimate_id = ap.estimate_id
                        WHERE ce.estimate_id = _estimate_id;

                        -- 申請データが存在しない場合
                        IF _application_id IS NULL THEN
                        
                            -- 承認済み申請データを作成する
                            INSERT INTO cu_application (
                                estimate_id
                            , application_user_id
                            , approval_user_id
                            , approval_date
                            , application_status
                            , application_comment
                            , approval_comment
                            , update_user_id
                            , update_system_type 
                            ) VALUES (
                                _estimate_id
                            , 0
                            , 0
                            , CURRENT_TIMESTAMP
                            , 2
                            , 'LANDMARKにおいて発注書受領に伴い、自動申請'
                            , 'LANDMARKにおいて発注書受領に伴い、自動承認'
                            , 0
                            , 1
                            );

                        -- 申請データが存在する場合        
                        ELSE

                            -- 承認済みでは無い場合
                            IF _application_status <> 2 THEN
                        
                            -- 申請データを承認済みに更新する
                            UPDATE cu_application
                                SET 
                                application_status = 2,
                                approval_comment = 'LANDMARKにおいて発注書受領に伴い、自動承認',
                                approval_date = CURRENT_TIMESTAMP,
                                approval_user_id = 0,
                                update_user_id = 1,
                                update_system_type = 1
                            WHERE application_id = _application_id;
                            
                            END IF;
                        END IF;
                        END IF;
                    END IF;

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
                        -- その他依頼の場合は、step1以降にステータスが進んだ場合に、契約データを作成する
                        IF new.estimate_status = 5 OR ( new.estimate_status = 4 AND _request_type IN (4,5,6) AND _request_other_status <> 0 ) THEN

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
                        , case 
                            when _request_type IN (4,5,6) AND _request_other_status = 6 then 2                   
                            else 2
                            end                                           -- 契約ステータス  2:契約書準備中, 5:契約準備完了
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
                        , estimate_natural_id
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
                        , new.estimate_natural_id
                        , _request_id                                   -- 見積依頼ID
                        , _project_id                                   -- 工事ID
                        , _parking_id                                   -- 駐車場ID
                        , _branch_id                                    -- 支店ID
                        , case                                          -- 見積ステータス
                            when new.estimate_status = 3 then 1           -- 3:調査見積送付済み → 1: 受注待ち
                            when new.estimate_status = 4 then 4           -- 4:受注 → 4:受注
                            when new.estimate_status = 5 then 5           -- 5:確定見積送付済 → 5:確定見積送付済      
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

            -- 更新日：22/01/23
            DROP TRIGGER IF EXISTS ins_bf_cu_message;

            CREATE TRIGGER ins_bf_cu_message BEFORE INSERT ON cu_message FOR EACH ROW
            BEGIN

            DECLARE _customer_id, _project_id, _core_id INT;

            IF new.create_system_type = 1 THEN
            
                SELECT cp.project_id,
                    cp.customer_id 
                INTO   _project_id,
                    _customer_id
                FROM   cu_project cp
                WHERE  cp.core_id = new.core_id;

            ELSE
                
                SELECT cp.customer_id,
                    cp.core_id
                INTO   _customer_id,
                    _core_id
                FROM   cu_project cp
                WHERE  cp.project_id = new.project_id;

            END IF;
                
            SET 
                new.project_id  = ifnull( new.project_id, _project_id ),
                new.customer_id = ifnull( new.customer_id, _customer_id ), 
                new.core_id = ifnull( new.core_id, _core_id ),
                new.create_date = CURRENT_TIMESTAMP,
                new.update_date = CURRENT_TIMESTAMP;

            END;

            -- 更新日：22/01/20
            DROP TRIGGER IF EXISTS upd_bf_cu_message;

            CREATE TRIGGER upd_bf_cu_message BEFORE UPDATE ON cu_message FOR EACH ROW
            BEGIN

            -- タイムゾーンの設定が正しくない基幹システムに対応して、強制的にUTCで更新日時を更新
            SET new.update_date = CURRENT_TIMESTAMP;
            
            END;

            -- 更新日：22/01/20
            DROP TRIGGER IF EXISTS upd_cu_message;   -- １度だけ実行

            DROP TRIGGER IF EXISTS upd_af_cu_message;

            CREATE TRIGGER upd_af_cu_message AFTER UPDATE ON cu_message FOR EACH ROW
            BEGIN
            INSERT INTO cu_message_history( 
                message_id, 
                project_id, 
                core_id,
                customer_id,
                body, 
                file_id, 
                edit, 
                already_read, 
                create_user_id, 
                create_system_type, 
                status 
            ) VALUES (  
                new.message_id, 
                new.project_id, 
                new.core_id,
                new.customer_id,
                new.body,
                new.file_id, 
                new.edit, 
                new.already_read, 
                ifnull( new.update_user_id, new.create_user_id ), 
                ifnull( new.update_system_type, new.create_system_type ),
                new.status
            );
            END;

            -- 更新日：22/01/17
            DROP TRIGGER IF EXISTS ins_cu_project;

            CREATE TRIGGER ins_cu_project  BEFORE INSERT ON cu_project FOR EACH ROW
            BEGIN

                DECLARE _customer_id, _customer_branch_id, _customer_user_id, _branch_id_core, _branch_id_cust INT;
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
                    ub.id,
                    ub.user_branch_natural_id,
                    a.branch_id
                    INTO 
                    _branch_id_core,
                    _branch_natural_id,
                    _branch_id_cust
                    FROM user_branch ub
                    INNER JOIN cu_branch cb ON cb.core_id = ub.id
                    INNER JOIN cu_branch_area a ON a.branch_id = cb.branch_id
                    WHERE a.prefecture = new.site_prefecture;

                    -- 工事IDの生成
                    SELECT CONCAT(substr(current_date,3,2), 'C',  LPAD(ifnull( max(substr(project_natural_id,4,5)), 0 ) +1 ,5,0 ))
                    INTO _project_natural_id 
                    FROM project 
                    WHERE CONCAT( substr(current_date,3,2), 'C' ) = substr(project_natural_id,1,3);
                
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
                    send_destination_type,
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
                    _branch_id_core,                                                       -- ランドマーク支店マスタテーブルID
                    _branch_natural_id,                                                    -- ランドマーク支店ID
                    ADDTIME( CURRENT_TIMESTAMP, '9:00:00' ),                               -- レコード作成日
                    ADDTIME( CURRENT_TIMESTAMP, '9:00:00' ),                               -- レコード更新日
                    getULID(),                                                             -- ULID
                    new.construction_number,                                               -- 工事番号
                    new.site_name,                                                         -- 現場名／邸名
                    new.site_name_kana,                                                    -- 現場名／邸名：カナ
                    new.site_prefecture,                                                   -- 現場所在地：都道府県コード
                    new.site_city,                                                         -- 現場所在地：市区町村
                    new.site_address,                                                      -- 現場所在地：番地（町名以降）
                    1,                                                                     -- データ登録者種別
                    new.create_user_id,                                                    -- 顧客システム最終更新者
                    0,                                                                     -- 元請下請送付先種別  ( 0: 元請のみに送る ）
                    new.latitude,                                                          -- 緯度
                    new.longitude                                                          -- 経度
                    );
                
                    -- 連携IDを取得
                    SET new.core_id = LAST_INSERT_ID(), new.branch_id = _branch_id_cust;
                END IF;
            END;

            -- 更新日：22/01/23
            DROP TRIGGER IF EXISTS ins_cu_request;

            CREATE TRIGGER ins_cu_request  BEFORE INSERT ON cu_request FOR EACH ROW
            BEGIN
            
              DECLARE _customer_id, _customer_branch_id, _customer_user_id, _branch_id, _project_id, _request_cnt, _estimate_id, _request_id, _extend_estimate_id, _extend_cnt INT;
              DECLARE _customer_natural_id, _customer_branch_natural_id,  _customer_user_natural_id, _branch_natural_id, _project_natural_id, _request_natural_id, _estimate_natural_id, _extend_estimate_natural_id   VARCHAR(255);
              
              DECLARE _estimate_type  SMALLINT;
              DECLARE _contact_memo   TEXT;
              
              -- 顧客システムによる更新の場合
              IF new.create_system_type = 2 THEN
                  
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
                IF new.request_type = 2 THEN
                
                  -- 延長の場合
                  -- 見積依頼件数の取得（ID生成用）
                  SELECT estimate_natural_id
                  INTO   _estimate_natural_id
                  FROM cu_estimate
                  WHERE estimate_id = new.extend_estimate_id;
                  
                  -- 延長件数の取得
                  SELECT IFNULL( MAX( SUBSTR( estimate_natural_id, LENGTH( estimate_natural_id )-1,1)), 0 ) + 1 
                    INTO _extend_cnt
                    FROM cu_estimate 
                   WHERE SUBSTR( _estimate_natural_id, 1,12) = SUBSTR( estimate_natural_id, 1,12);
                  
                  IF _extend_cnt = 1 THEN
                  
                    SET  _request_natural_id = CONCAT( _estimate_natural_id , '-E1' );
            
                  ELSE
            
                    SET _request_natural_id = CONCAT( SUBSTR( _estimate_natural_id, 1, LENGTH( _estimate_natural_id )-1), _extend_cnt );
            
                  END IF;
                        
                ELSE
            
                  SELECT COUNT(*)+1
                  INTO _request_cnt
                  FROM cu_request
                  WHERE project_id = new.project_id 
                    AND request_type = new.request_type;
            
                  SET _request_natural_id = CONCAT( _project_natural_id, 
                    CASE new.request_type
                      WHEN 0 THEN CONCAT( '-P', '00' )
                      WHEN 1 THEN CONCAT( '-P', LPAD( _request_cnt, 2, 0 )) 
                      WHEN 3 THEN CONCAT( '-R', LPAD( _request_cnt, 2, 0 )) 
                      WHEN 4 THEN CONCAT( '-L', LPAD( _request_cnt, 2, 0 )) 
                      WHEN 5 THEN CONCAT( '-W', LPAD( _request_cnt, 2, 0 )) 
                      WHEN 6 THEN CONCAT( '-M', LPAD( _request_cnt, 2, 0 )) 
                      ELSE 'XXX'  -- 採番不能なケース（想定なし）
                    END );
                    
                END IF;
                
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
            
                -- 連絡メモ
                IF new.request_type IN ( 0,1,2,3) THEN
            
                  SELECT  CONCAT ( '登録を受け付けました。',
                            DATE_FORMAT( getCalcBusinessDay( current_date,
                              CASE
                                WHEN holiday THEN 0                  -- 休日の場合
                                WHEN hour( now())+9 <= 17 THEN 0     -- 営業時間内の場合
                                ELSE 1                               -- 上記以外の場合、翌営業日
                              END ), 
                            '%m月%d日'),
                            ' 18時までに見積を提示します。' )
                    INTO _contact_memo
                    FROM cu_calendar 
                   WHERE calendar_day = DATE( ADDTIME(current_timestamp, '09:00:00' ));
            
                END IF;
                
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
                  , contact_memo
                ) VALUES (
                    ADDTIME( CURRENT_TIMESTAMP, '9:00:00' )                          -- レコード作成日
                  , ADDTIME( CURRENT_TIMESTAMP, '9:00:00' )                          -- レコード更新日
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
                  , 1                                                        -- 案内方法：下請用 1:メール（固定値）
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
                  , _contact_memo
                );
            
                -- 連携ID、見積依頼NOを取得
                SET _request_id = LAST_INSERT_ID();
                SET NEW.core_id = _request_id, 
                    NEW.request_natural_id = _request_natural_id,
                    NEW.contact_memo = _contact_memo;
            
                -- 依頼種別 延長の場合の後続処理
                IF new.request_type = 2 THEN
                  
                  -- 延長元見積ID（基幹）の取得
                  SELECT
                      e.estimate_id 
                    , e.estimate_natural_id
                  INTO   
                      _extend_estimate_id
                    , _extend_estimate_natural_id
                  FROM   estimate e
                    INNER JOIN cu_estimate ce ON cu.core_id = e.id
                  WHERE  ce.estimate_id = new.extend_estimate_id;
                  
                  -- 延長する駐車場情報を延長元見積IDを取得条件として登録
                  INSERT INTO request_parking (
                      request_id
                    , parking_id
                    , project_id
                    , project_natural_id
                    , request_natural_id
                    , customer_id
                    , customer_natural_id
                    , customer_branch_id
                    , customer_branch_natural_id
                    , customer_user_id
                    , customer_user_natural_id
                    , supplier_id
                    , supplier_natural_id
                    , lastupdate_user_id
                    , user_branch_id
                    , user_branch_natural_id
                    , extend_estimate_natural_id
                    , ulid
                    , create_timestamp
                    , update_timestamp
                    , register_type
                    , update_type
                    , cu_lastupdate_user_id
                    , request_parking_status
                    , route_map_parking_flag
                    , site_distance_minute
                    , site_distance_meter 
                    ) 
                    SELECT
                        _request_id
                      , rp.parking_id
                      , _project_id
                      , _project_natural_id
                      , _request_natural_id
                      , rp.customer_id
                      , rp.customer_natural_id
                      , rp.customer_branch_id
                      , rp.customer_branch_natural_id
                      , rp.customer_user_id
                      , rp.customer_user_natural_id
                      , rp.supplier_id
                      , rp.supplier_natural_id
                      , 1
                      , rp.user_branch_id
                      , rp.user_branch_natural_id
                      , _extend_estimate_natural_id
                      , getULID()
                      , ADDTIME( CURRENT_TIMESTAMP, '9:00:00' ) 
                      , ADDTIME( CURRENT_TIMESTAMP, '9:00:00' ) 
                      , 0
                      , 0
                      , new.create_user_id
                      , 0
                      , rp.route_map_parking_flag
                      , rp.site_distance_minute
                      , rp.site_distance_meter
                    FROM request_parking rp
                    WHERE estimate_id = _extend_estimate_id
                    ;
                
                -- 依頼種別その他の場合の後続処理
                ELSEIF new.request_type IN ( 4, 5, 6 ) THEN
                
                  SET _estimate_natural_id = CONCAT( _request_natural_id, '-01');
                
                  -- 見積の作成
                  INSERT INTO estimate (
                    survey_available_start_date
                  , survey_available_end_date
                  , survey_capacity_qty
                  , quote_available_start_date
                  , quote_available_end_date
                  , quote_capacity_qty                  -- 確定見積台数
                  , estimate_status                     -- 見積ステータス 4:受注
                  , survey_pay_unit_type_day            -- 調査見積駐車場情報：日割可否
                  , survey_pay_unit_type_month          -- 調査見積駐車場情報：通し可否
                  , survey_parking_parallel_type        -- 調査見積駐車場確認項目：駐車方法
                  , payment_request_status              -- 仕入依頼ステータス 0:仕入依頼前
                  , survey_capacity_qty_type            -- 単位 2:式
                  , survey_term_month_flag              -- 調査見積見積情報：通し有無
                  , survey_tax_in_flag                  -- 調査見積見積金額：税込みフラグ
                  , survey_fraction_amt_flag            -- 調査見積見積金額：端数調整フラグ
                  , quote_term_month_flag               -- 確定見積：通し有無
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
                  , supplier_id
                  , parking_id
                  ) VALUES (
                    IFNULL( new.request_other_start_date, new.want_start_date )  -- 調査見積期間・開始日
                  , IFNULL( new.request_other_end_date , new.want_end_date )     -- 調査見積期間・終了日
                  , new.request_other_qty                                        -- 見積台数
                  , IFNULL( new.request_other_start_date, new.want_start_date )  -- 確定見積期間：開始日
                  , IFNULL( new.request_other_end_date, new.want_end_date )      -- 確定見積期間：終了日
                  , new.request_other_qty                                    -- 確定見積台数
                  , 4                                                        -- 見積ステータス 4:受注
                  , 0                                                        -- 調査見積駐車場情報：日割可否
                  , 0                                                        -- 調査見積駐車場情報：通し可否
                  , 0                                                        -- 調査見積駐車場確認項目：駐車方法
                  , 0                                                        -- 仕入依頼ステータス 0:仕入依頼前
                  , 2                                                        -- 単位 2:式
                  , 1                                                        -- 調査見積見積情報：通し有無
                  , 0                                                        -- 調査見積見積金額：税込みフラグ
                  , 1                                                        -- 調査見積見積金額：端数調整フラグ
                  , 1                                                        -- 確定見積：通し有無
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
                  , _branch_id                                               -- ランドマーク支店マスタテーブルID
                  , _branch_natural_id                                       -- ランドマーク支店ID
                  , 1                                                        -- システム利用者テーブルID：最終更新者
                  , getULID()                                                -- ULID
                  , ADDTIME( CURRENT_TIMESTAMP, '9:00:00' )                          -- レコード作成日
                  , ADDTIME( CURRENT_TIMESTAMP, '9:00:00' )                          -- レコード作成日
                  , 1                                                        -- データ登録者種別
                  , 1                                                        -- データ更新者種別
                  , new.create_user_id                                       -- 顧客システム最終更新者
                  , 0                                                        -- 発注書確認フラグ 0:未確認
                  , 1                                                        -- 見積キャンセル確認フラグ 1:確認済み
                  , 0                                                        -- 見積キャンセル申請ステータス 0:未申請
                  , 0                                                        -- 仕入先テーブルID
                  , 0                                                        -- 駐車場マスタテーブルID
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
                    , ADDTIME( CURRENT_TIMESTAMP, '9:00:00' )               -- レコード作成日
                    , ADDTIME( CURRENT_TIMESTAMP, '9:00:00' )               -- レコード作成日
                    , 1                                             -- データ登録者種別
                    , 1                                             -- データ更新者種別
                    , new.create_user_id                            -- 顧客システム最終更新者
                    );
                  
                    SET _estimate_type = _estimate_type + 1;
                        
                  UNTIL  _estimate_type > 2 END REPEAT;
            
                END IF;
                
              END IF;
            END;

            -- 更新日：22/01/23
            DROP TRIGGER IF EXISTS upd_bf_cu_request;

            CREATE TRIGGER upd_bf_cu_request  BEFORE UPDATE ON cu_request FOR EACH ROW
            BEGIN

            IF new.request_status = 7 AND old.request_status <> 7 THEN
                SET new.contact_memo = 'この見積依頼はキャンセルとなりました';
            END IF;
            END;

            -- 更新日：22/01/23
            DROP TRIGGER IF EXISTS upd_cu_request;

            DROP TRIGGER IF EXISTS upd_af_cu_request;
            CREATE TRIGGER upd_af_cu_request  AFTER UPDATE ON cu_request FOR EACH ROW
            BEGIN

            -- 顧客システムによる更新の場合
            IF new.update_system_type = 2 THEN
            
                -- 依頼種別が0:初回、1:追加、2:延長、3:積替の場合、見積依頼ステータスが0:未調査、1:Web調査、2:現地調査までを更新可能とする
                -- 依頼種別が4:その他ー道路使用許可、5:その他ー取扱説明書収納、6:その他ー電線移設の場合、
                -- その他作業ステータスが0:未調査、1:Web調査、2:現地調査までを更新可能とする
                IF ( new.request_status IN ( 0, 1, 2, 7 ) AND new.request_type IN ( 0, 1, 2, 3 ) ) 
                OR  ( new.request_other_status = 0 AND new.request_type IN ( 4, 5, 6 )) THEN
            
                UPDATE request
                    SET 
                    update_timestamp                = ADDTIME( CURRENT_TIMESTAMP, '9:00:00' )                                             -- レコード更新日
                    , estimate_deadline               = IFNULL( new.response_request_date, getCalcBusinessDay( CURRENT_DATE, 4  ))  -- 見積提出期限／顧客希望が無い場合は4営業日後
                    , want_start_date                 = new.want_start_date                                                         -- 利用期間：開始
                    , want_end_date                   = new.want_end_date                                                           -- 利用期間：終了
                    , car_qty                         = new.car_qty
                    , light_truck_qty                 = new.light_truck_qty
                    , truck_qty                       = new.truck_qty
                    , other_car_qty                   = new.other_car_qty
                    , other_car_detail                = new.other_car_detail
                    , request_other_deadline          = IFNULL( new.request_other_deadline, ifnull( new.request_other_start_date, new.want_start_date ))
                    , request_other_start_date        = IFNULL( new.request_other_start_date, new.want_start_date ) 
                    , request_other_end_date          = IFNULL( new.request_other_end_date, new.want_end_date )
                    , request_other_qty               = new.request_other_qty
                    , want_guide_type                 = new.want_guide_type
                    , cc_email                        = new.cc_email
                    , customer_other_request          = new.customer_other_request
                    , update_type                     = 1
                    , cu_lastupdate_user_id           = new.update_user_id
                    , request_cancel_date             =
                    CASE
                        WHEN old.request_status IN ( 0, 1, 2 ) AND new.request_status = 7 AND old.request_status <> 7  THEN CURRENT_DATE
                        ELSE NULL
                    END                                                 -- 調査途中終了となった場合、キャンセル日付を入れる
                    , request_cancel_check_flag      = 
                    CASE
                        WHEN new.request_status = 7 AND old.request_status <> 7 THEN 0
                        ELSE request_cancel_check_flag
                    END
                    ,  survey_status =                                     -- 調査途中終了となった場合、キャンセル確認フラグをFLASE:未確認にする
                    CASE
                        WHEN new.request_status = 7 AND old.request_status <> 7 THEN 10
                        ELSE survey_status
                    END                                                 -- 調査途中終了となった場合、10:依頼キャンセル（調査見積未送付）とする
                    , lastupdate_user_id             = 1
                    , contact_memo = new.contact_memo
                WHERE id = new.core_id;

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
                    , lastupdate_user_id                               = 1                                                        -- システム利用者テーブルID：最終更新者
                    , update_timestamp                                 = ADDTIME( CURRENT_TIMESTAMP, '9:00:00' )                          -- レコード更新日
                    , update_type                                      = 1                                                        -- データ更新者種別
                    , cu_lastupdate_user_id                            = new.create_user_id                                       -- 顧客システム最終更新者
                    WHERE request_id = new.core_id;

                    -- 見積詳細の更新
                    UPDATE estimate_detail 
                    SET
                    estimate_detail_qty                          = new.request_other_qty                         -- 見積明細：台数
                    , lastupdate_user_id                           = 1                                             -- システム利用者テーブルID：最終更新者
                    , update_timestamp                             = ADDTIME( CURRENT_TIMESTAMP, '9:00:00' )       -- レコード作成日
                    , update_type                                  = 1                                             -- データ更新者種別
                    , cu_lastupdate_user_id                        = new.create_user_id                            -- 顧客システム最終更新者
                    WHERE request_id = new.core_id;
                    
                END IF;
                END IF;
            END IF;
            END;

            -- 更新日：22/01/23
            DROP TRIGGER IF EXISTS upd_cu_estimate;
            DROP TRIGGER IF EXISTS upd_af_cu_estimate;

            CREATE TRIGGER upd_af_cu_estimate  AFTER UPDATE ON cu_estimate FOR EACH ROW
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
                    purchase_order_upload_date    = ADDTIME( CURRENT_TIMESTAMP, '9:00:00' ),
                    purchase_order_register_type  = 1,           
                    update_timestamp              = ADDTIME( CURRENT_TIMESTAMP, '9:00:00' ),
                    update_type                   = 1,
                    cu_lastupdate_user_id         = new.update_user_id,
                    lastupdate_user_id            = 1,
                    contact_memo                  = new.contact_memo
                WHERE id = new.core_id;
                
                -- 失注処理（自動失注）
                ELSEIF new.estimate_status = 7 AND old.estimate_status <> 7 THEN
                
                UPDATE estimate
                    SET 
                    estimate_status            = new.estimate_status,
                    estimate_cancel_check_flag = FALSE,
                    update_timestamp           = ADDTIME( CURRENT_TIMESTAMP, '9:00:00' ),
                    update_type                = 1,
                    cu_lastupdate_user_id      = new.update_user_id,
                    lastupdate_user_id         = 1,
                    contact_memo               = new.contact_memo
                WHERE id = new.core_id;
                END IF;

            END IF;
            END;
            
            -- 更新日 2022/01/17
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
                    FROM customer_user;
                    
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
            -- 更新日 2022/01/23
            DROP TRIGGER IF EXISTS ins_cu_customer_option;

            CREATE TRIGGER ins_cu_customer_option  BEFORE INSERT ON cu_customer_option FOR EACH ROW
            BEGIN

            DECLARE _user_id INT;
            DECLARE _customer_user_name VARCHAR(255);
            
            -- システム管理者の情報をもとに、システム管理者ID、システム管理者名をデータ上にセットする
            IF new.admin_user_login_id IS NULL THEN
            
                SET new.admin_user_id = NULL, new.admin_user_name = NULL ;
                
            ELSE

                -- ログインIDからユーザーIDとユーザー名を取得（複数件存在する場合、最初の１件のみを取得する） 
                SELECT    user_id
                        , customer_user_name 
                INTO    _user_id
                        , _customer_user_name
                FROM cu_user
                WHERE login_id = new.admin_user_login_id
                    AND customer_id = new.customer_id
                    AND status
                LIMIT 1;

                SET new.admin_user_id = _user_id, new.admin_user_name = _customer_user_name;
                
            END IF;
            END;

            -- 更新日 2022/01/23
            DROP TRIGGER IF EXISTS upd_cu_customer_option;

            CREATE TRIGGER upd_cu_customer_option  BEFORE UPDATE ON cu_customer_option FOR EACH ROW
            BEGIN

            DECLARE _user_id INT;
            DECLARE _customer_user_name VARCHAR(255);
            
            -- システム管理者の情報をもとに、システム管理者ID、システム管理者名をデータ上にセットする
            IF new.admin_user_login_id IS NULL THEN
            
                SET new.admin_user_id = NULL, new.admin_user_name = NULL ;
                
            ELSE

                -- ログインIDからユーザーIDとユーザー名を取得（複数件存在する場合、最初の１件のみを取得する） 
                SELECT    user_id
                        , customer_user_name 
                INTO    _user_id
                        , _customer_user_name
                FROM cu_user
                WHERE login_id = new.admin_user_login_id
                    AND customer_id = new.customer_id
                    AND status
                LIMIT 1;

                SET new.admin_user_id = _user_id, new.admin_user_name = _customer_user_name;
                
            END IF;
            END;
        ";

    }
}
