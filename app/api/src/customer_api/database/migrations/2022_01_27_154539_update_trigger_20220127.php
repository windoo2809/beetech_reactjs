<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateTrigger20220127 extends Migration
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
            -- 更新日：22/01/26
            DROP TRIGGER IF EXISTS ins_customer;
            CREATE TRIGGER ins_customer AFTER INSERT ON customer FOR EACH ROW
            BEGIN

            DECLARE _customer_id INT;
            
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

                SET _customer_id = LAST_INSERT_ID();
                
                -- 顧客システム利用情報を作成
                INSERT INTO cu_customer_option (
                    customer_id
                , core_id
                , plan_type
                , approval
                , create_system_type
                , create_user_id
                , status
                ) VALUES (
                    _customer_id                      -- 顧客会社ID
                , new.id                            -- 基幹システム連携ID
                , 0                                 -- 利用プラン区分   0: 利用無
                , FALSE                             -- 承認機能利用
                , 1                                 -- 固定値：基幹システム
                , 0                                 -- 固定値：システム自動連携
                , ISNULL( new.delete_timestamp )    -- ステータス
                );
                
            END IF;
            END;

            -- 更新日：22/01/26

            DROP TRIGGER IF EXISTS ins_cu_request;

            CREATE TRIGGER ins_cu_request  BEFORE INSERT ON cu_request FOR EACH ROW
            BEGIN

                DECLARE _customer_id, _customer_branch_id, _customer_user_id, _branch_id, _project_id, _request_cnt, _estimate_id, _request_id, _extend_estimate_id, _extend_cnt INT;
                DECLARE _customer_natural_id, _customer_branch_natural_id,  _customer_user_natural_id, _branch_natural_id, _project_natural_id, _request_natural_id, _estimate_natural_id, _extend_estimate_natural_id   VARCHAR(255);
                
                DECLARE _estimate_type  SMALLINT;
                DECLARE _contact_memo   TEXT;
                
                
                -- 利用開始日、終了日に時刻が入っている場合の切り捨て処理
                SET new.want_start_date = CAST( new.want_start_date AS date ), new.want_end_date = CAST( new.want_end_date AS date );
                
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

                    -- 初回が無い「追加」の場合の対応
                    IF new.request_type = 1 AND NOT EXISTS ( SELECT 1 FROM cu_request WHERE project_id = new.project_id AND request_type = 0 ) THEN
                    
                    -- 依頼種別を 0:初回 に変更する
                    SET new.request_type = 0;
                    
                    END IF;


                    -- 依頼IDの作成
                    IF new.request_type = 2 THEN
                    
                    -- 延長の場合
                    -- 見積依頼件数の取得（ID生成用）
                    SELECT estimate_natural_id
                    INTO   _estimate_natural_id
                    FROM cu_estimate
                    WHERE estimate_id = new.extend_estimate_id;
                    
                    -- 延長件数の取得
                    SELECT ifnull( count(request_natural_id), 0)
                        INTO _extend_cnt
                        FROM cu_request 
                    WHERE SUBSTR( _estimate_natural_id, 1,LENGTH(_project_natural_id) + 7) = SUBSTR( request_natural_id, 1,LENGTH(_project_natural_id) + 7);
                    
                    IF _extend_cnt = 0 THEN
                    
                        SET  _request_natural_id = CONCAT( _estimate_natural_id , '-E1' );

                    ELSE
                    
                        IF SUBSTR( _estimate_natural_id, LENGTH( _estimate_natural_id ) - 2, 1 ) = 'E' OR SUBSTR( _estimate_natural_id, LENGTH( _estimate_natural_id ) - 3, 1 ) THEN

                        -- 再延長の場合
                        SET _request_natural_id = CONCAT( SUBSTR( _estimate_natural_id, 1, LENGTH( _estimate_natural_id )-1), _extend_cnt + 1 );
                        
                        ELSE

                        -- 延長の見積が無い状態で、再度延長依頼を行う場合
                        SET  _request_natural_id = CONCAT( _estimate_natural_id , '-E', _extend_cnt + 1 );
                        
                        END IF;

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
                        e.id 
                        , e.estimate_natural_id
                    INTO   
                        _extend_estimate_id
                        , _extend_estimate_natural_id
                    FROM   estimate e
                        INNER JOIN cu_estimate ce ON ce.core_id = e.id
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

            -- 更新日：22/01/26
            DROP TRIGGER IF EXISTS upd_bf_cu_request;

            CREATE TRIGGER upd_bf_cu_request  BEFORE UPDATE ON cu_request FOR EACH ROW
            BEGIN

            -- 利用開始日、終了日に時刻が入っている場合の切り捨て処理
            SET new.want_start_date = CAST( new.want_start_date AS date ), new.want_end_date = CAST( new.want_end_date AS date );


            IF new.request_status = 7 AND old.request_status <> 7 THEN
            
            
                SET new.contact_memo = 'この見積依頼はキャンセルとなりました';
            
            
            END IF;


            END;
            
            -- トリガー： 契約（更新）2022/01/26

            DROP TRIGGER IF EXISTS upd_cu_user;

            CREATE TRIGGER upd_cu_user  AFTER UPDATE ON cu_user FOR EACH ROW
            BEGIN

            -- 顧客向けシステムで更新した場合
            IF new.update_system_type = 2 THEN

                -- 顧客担当者情報の更新
                UPDATE customer_user cu 
                INNER JOIN cu_customer_user ccu on ccu.core_id = cu.id  
                INNER JOIN cu_user_branch cub ON cub.customer_user_id = ccu.customer_user_id       
                SET 
                cu.lastupdate_user_id              = 1
                ,cu.update_timestamp                = ADDTIME( CURRENT_TIMESTAMP, '9:00:00' )
                ,cu.customer_user_name              = new.customer_user_name
                ,cu.customer_user_name_kana         = new.customer_user_name_kana
                ,cu.customer_user_email             = new.login_id
                ,cu.customer_user_tel               = new.customer_user_tel
                ,cu.customer_reminder_sms_flag      = new.customer_reminder_sms_flag
                ,cu.update_type                     = 1
                ,cu.cu_lastupdate_user_id = new.update_user_id     
                WHERE cub.user_id = new.user_id;

                -- システム管理者だった場合、名前とメールアドレスを更新する
                UPDATE cu_customer_option cco
                SET cco.admin_user_name = ifnull( new.customer_user_name, 'NULL'),
                    cco.admin_user_login_id = new.login_id
                WHERE cco.customer_id = new.customer_id
                AND cco.admin_user_id = new.user_id;
            
            END IF;

            END;

            -- 更新日 2022/01/25
            DROP TRIGGER IF EXISTS ins_cu_customer_option;

            CREATE TRIGGER ins_cu_customer_option  BEFORE INSERT ON cu_customer_option FOR EACH ROW
            BEGIN

                DECLARE _user_id INT;
                DECLARE _customer_user_name VARCHAR(255);
                
                -- システム管理者の情報をもとに、システム管理者ID、システム管理者名をデータ上にセットする
                IF IFNULL( new.admin_user_login_id, '' ) = '' THEN
                
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

                    IF _customer_user_name IS NULL THEN 
                    SET _customer_user_name = '取得できていない';
                    END IF;
                    
                    SET new.admin_user_id = _user_id, new.admin_user_name = _customer_user_name;
                    
                END IF;
            END;

            -- 更新日 2022/01/25
            DROP TRIGGER IF EXISTS upd_cu_customer_option;

            CREATE TRIGGER upd_cu_customer_option  BEFORE UPDATE ON cu_customer_option FOR EACH ROW
                BEGIN

                DECLARE _user_id INT;
                DECLARE _customer_user_name VARCHAR(255);
                
                -- システム管理者の情報をもとに、システム管理者ID、システム管理者名をデータ上にセットする
                IF IFNULL( new.admin_user_login_id, '' ) = '' THEN
                
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
