<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateTrigger20211117 extends Migration
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
            -- トリガー： 工事情報（更新） 更新日：21/11/10
            DROP TRIGGER IF EXISTS upd_project;

            CREATE TRIGGER upd_project AFTER UPDATE ON project FOR EACH ROW
            BEGIN

                DECLARE _customer_id, _customer_branch_id, _customer_user_id, _branch_id, _project_id INT;
                DECLARE _subcontract_customer_name, _subcontract_customer_name_kana, 
                        _subcontract_customer_branch_name, _subcontract_customer_branch_name_kana,
                        _subcontract_customer_user_name, _subcontract_customer_user_name_kana, _subcontract_customer_user_division_name VARCHAR(255);
                DECLARE _subcontract_customer_branch_tel, _subcontract_customer_branch_fax, _subcontract_customer_user_tel VARCHAR(13);
                DECLARE _subcontract_customer_user_email VARCHAR(2048);
                DECLARE _subcontract_customer_reminder_sms_flag BOOL;
                
                -- 基幹システムからの更新の場合
                IF new.update_type = 0 THEN
                
                    -- 顧客情報の取得
                    SELECT ccu.customer_id,ccu.customer_branch_id, ccu.customer_user_id
                    INTO _customer_id, _customer_branch_id, _customer_user_id
                    FROM cu_customer_user ccu
                    INNER JOIN customer_user cu on ccu.core_id = cu.id
                    WHERE cu.customer_user_natural_id = new.customer_user_natural_id;

                    -- 下請情報の取得
                    SELECT 
                    cc.customer_name
                    ,cc.customer_name_kana
                    ,ccb.customer_branch_name
                    ,ccb.customer_branch_name_kana
                    ,ccb.customer_branch_tel
                    ,ccb.customer_branch_fax
                    ,ccu.customer_user_name
                    ,ccu.customer_user_name_kana
                    ,ccu.customer_user_division_name
                    ,ccu.customer_user_tel
                    ,ccu.customer_user_email
                    ,ccu.customer_reminder_sms_flag
                    INTO
                    _subcontract_customer_name
                    , _subcontract_customer_name_kana
                    , _subcontract_customer_branch_name
                    , _subcontract_customer_branch_name_kana
                    , _subcontract_customer_branch_tel
                    , _subcontract_customer_branch_fax
                    , _subcontract_customer_user_name
                    , _subcontract_customer_user_name_kana
                    , _subcontract_customer_user_division_name
                    , _subcontract_customer_user_tel
                    , _subcontract_customer_user_email
                    , _subcontract_customer_reminder_sms_flag
                    FROM cu_customer_user ccu
                    INNER JOIN cu_customer_branch ccb ON ccb.customer_branch_id = ccu.customer_branch_id
                    INNER JOIN cu_customer cc ON cc.customer_id = ccu.customer_id
                    WHERE ccu.core_id = new.subcontract_customer_user_id;
                    
                    -- ランドマーク支店情報の取得
                    SELECT branch_id
                    INTO _branch_id
                    FROM cu_branch
                    WHERE core_id = new.user_branch_id;

                    -- プロジェクトIDの取得
                    SELECT project_id
                    INTO _project_id
                    FROM cu_project
                    WHERE core_id = new.id;
                    
                    -- すでに連携済みのデータが存在する場合は更新を行う
                    IF _project_id IS NOT NULL THEN
                
                    UPDATE cu_project
                        SET 
                        customer_id              = _customer_id                           -- 顧客会社ID
                        ,customer_branch_id       = _customer_branch_id                    -- 顧客支店ID
                        ,customer_user_id         = _customer_user_id                      -- 顧客担当者ID
                        ,branch_id                = _branch_id                             -- 支店ID
                        ,construction_number      = new.construction_number                -- 工事番号
                        ,site_name                = new.site_name                          -- 現場名／邸名
                        ,site_name_kana           = new.site_name_kana                     -- 現場名／邸名：カナ
                        ,site_prefecture          = new.site_prefecture                    -- 都道府県コード 
                        ,site_city                = new.site_city                          -- 市区町村名 
                        ,site_address             = new.site_address                       -- 番地（町名以降） 
                        ,latitude                 = new.latitude                           -- 緯度 
                        ,longitude                = new.longitude                          -- 経度 
                        ,update_system_type       = 1                                      -- 固定値：基幹システム
                        ,update_user_id           = 0                                      -- 固定値：システム自動連携
                        ,status                   = ISNULL( new.delete_timestamp )         -- ステータス
                    WHERE core_id = new.id
                    ;

                    -- 連携済み、見積依頼が存在する場合、下請け情報を更新する（見積未作成の見積依頼のみ）
                    UPDATE cu_request
                        SET 
                        subcontract_name                = _subcontract_customer_name
                        ,subcontract_kana                = _subcontract_customer_name_kana
                        ,subcontract_branch_name         = _subcontract_customer_branch_name
                        ,subcontract_branch_kana         = _subcontract_customer_branch_name_kana
                        ,subcontract_branch_tel          = _subcontract_customer_branch_tel
                        ,subcontract_user_division_name  = _subcontract_customer_user_division_name
                        ,subcontract_user_name           = _subcontract_customer_user_name
                        ,subcontract_user_kana           = _subcontract_customer_user_name_kana
                        ,subcontract_user_email          = _subcontract_customer_user_email
                        ,subcontract_user_tel            = _subcontract_customer_user_tel
                        ,subcontract_user_fax            = _subcontract_customer_branch_fax
                        ,subcontract_reminder_sms_flag   = _subcontract_customer_reminder_sms_flag
                        ,send_destination_type           = new.send_destination_type
                        ,site_tel                        = new.site_tel
                        ,update_system_type       = 1                                      -- 固定値：基幹システム
                        ,update_user_id           = 0                                      -- 固定値：システム自動連携
                    WHERE project_id =  _project_id
                        AND ( request_type IN ( 0,1,2,3) AND request_status  IN ( 0,1,2 ))
                        OR ( request_type IN ( 4,5,6) AND request_other_status IN ( 0,1,2,3,4,5 ));
                        
                    -- 連携済みのデータが存在しない場合は登録を行う
                    ELSE
                
                    INSERT INTO cu_project( 
                        core_id
                    , customer_id
                    , customer_branch_id
                    , customer_user_id
                    , branch_id
                    , construction_number
                    , site_name
                    , site_name_kana
                    , site_prefecture
                    , site_city
                    , site_address
                    , latitude
                    , longitude
                    , create_system_type
                    , create_user_id
                    , status
                    ) 
                    VALUES (
                        new.id                                    -- 基幹システム連携ID
                    , _customer_id                              -- 顧客会社ID
                    , _customer_branch_id                       -- 顧客支店ID
                    , _customer_user_id                         -- 顧客担当者ID
                    , _branch_id                                -- 支店ID
                    , new.construction_number                   -- 工事番号
                    , new.site_name                             -- 現場名／邸名
                    , new.site_name_kana                        -- 現場名／邸名：カナ
                    , new.site_prefecture                       -- 都道府県コード
                    , new.site_city                             -- 市区町村名
                    , new.site_address                          -- 番地（町名以降）
                    , new.latitude                              -- 緯度
                    , new.longitude                             -- 経度
                    , 1                                         -- 固定値：基幹システム
                    , 0                                         -- 固定値：システム自動連携
                    , ISNULL( new.delete_timestamp )            -- ステータス 
                    );
                    END IF;
                END IF;
            END;   
            
            -- トリガー: 請求（更新） 更新日:21/11/10
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

                    -- クラウドサイン連携用情報の更新
                --    UPDATE cu_contract cc
                --      INNER JOIN cu_invoice ci ON ci.contract_id = cc.contract_id
                --    SET
                --        cc.update_system_type = 1                      -- 固定値：基幹システム
                --      , cc.update_user_id = 1                          -- 固定値：システム自動連携      
                --      , cs_document_id = new.contract_docuent_id    -- クラウドサイン連携用ドキュメントID
                --      , cs_file_id = new.contract_file_id           -- クラウドサイン連携用ファイルID
                --     WHERE
                --       ci.core_invoice_id = new.id;
                    
                END IF;
            END;

            -- トリガー： 工事情報(登録) 更新日：21/11/10
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
                    SELECT CONCAT(substr(current_date,3,2), 'C',  LPAD( count(*)+1, 7, 0))
                    INTO _project_natural_id 
                    FROM project 
                    WHERE substr(current_date,3,2) = substr(project_natural_id,1,2);
                
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
                    new.latitude,                                                          -- 緯度
                    new.longitude                                                          -- 経度
                    );
                
                    -- 連携IDを取得
                    SET new.core_id = LAST_INSERT_ID(), new.branch_id = _branch_id_cust;
                END IF;
            END;
        ";
    }
}
