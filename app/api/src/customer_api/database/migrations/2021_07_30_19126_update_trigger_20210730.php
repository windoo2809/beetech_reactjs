<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateTrigger20210730 extends Migration
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

    public function createTrigger()
    {
        return "
            DROP TRIGGER IF EXISTS ins_cu_request;
            CREATE TRIGGER ins_cu_request BEFORE INSERT ON cu_request FOR EACH ROW
            BEGIN
            DECLARE _customer_id, _customer_branch_id, _customer_user_id, _branch_id, _project_id, _request_cnt INT;
            DECLARE _customer_natural_id, _customer_branch_natural_id,  _customer_user_natural_id, _branch_natural_id, _project_natural_id, _request_natural_id  VARCHAR(255);
            
            IF new.create_system_type = 2 THEN
            
                SELECT COUNT(*)
                INTO _request_cnt
                FROM cu_request
                WHERE project_id = new.project_id 
                AND request_type = new.request_type;
            
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
                , cc_email
                , customer_other_request
                , survey_status
                , register_type
                , update_type
                , request_cancel_check_flag
                , lastupdate_user_id
                , project_id
                , user_branch_id
                ) VALUES (
                    CURRENT_TIMESTAMP                                        -- レコード作成日
                , CURRENT_TIMESTAMP                                        -- レコード更新日
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
                , new.request_date                                         -- 依頼受付日
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
                , new.request_other_start_date                             -- 契約開始日
                , new.request_other_end_date                               -- 契約終了日
                , new.request_other_qty                                    -- 個数
                , new.want_guide_type                                      -- 案内方法
                , new.cc_email                                             -- 顧客が指定するCCメールアドレス
                , new.customer_other_request                               -- 顧客からの要望等
                , 0                                                        -- 調査ステータス （0:未調査）
                , 1                                                        -- データ登録ユーザー種別（1:顧客登録データ)
                , 1                                                        -- データ更新者種別（1:顧客登録データ)
                , 0                                                        -- 依頼キャンセル確認フラグ（0:未確認）
                , 1                                                        -- システム利用者テーブルID：最終更新者
                , _project_id                                              -- 工事マスターテーブルID
                , _branch_id                                               -- ランドマーク支店マスターテーブルID
                );
            
                -- 連携ID、見積依頼NOを取得
                SET NEW.core_id = LAST_INSERT_ID(), NEW.request_natural_id = _request_natural_id;
            
                -- 依頼種別その他の場合の後続処理
            
            END IF;
            END;  
        ";
    }
}
