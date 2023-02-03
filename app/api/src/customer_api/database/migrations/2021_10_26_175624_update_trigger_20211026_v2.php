<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateTrigger20211026V2 extends Migration
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
            DROP TRIGGER IF EXISTS ins_accounts_receivable;


            CREATE TRIGGER ins_accounts_receivable AFTER INSERT ON accounts_receivable FOR EACH ROW
            BEGIN

            DECLARE _project_id, _contract_id, _customer_id, _customer_branch_id, _customer_user_id, _parking_id int;
            DECLARE _invoice_process_status smallint;
            DECLARE _contact_memo text;


            -- 見積情報および工事情報の取得
            SELECT 
                c.project_id
                ,c.contract_id
                ,p.customer_id
                ,p.customer_branch_id
                ,p.customer_user_id
                ,c.parking_id
            INTO
                _project_id
                ,_contract_id
                ,_customer_id
                ,_customer_branch_id
                ,_customer_user_id
                ,_parking_id
            FROM cu_contract c
                INNER JOIN cu_project p ON p.project_id = c.project_id
            WHERE c.core_id = new.estimate_id;

            -- 請求書情報の取得
            SELECT invoice_process_status, contact_memo
            INTO _invoice_process_status, _contact_memo
            FROM invoice
            WHERE id = new.invoice_id;

            -- 請求データの作成
            INSERT INTO cu_invoice( 
                core_id
                , core_invoice_id
                , project_id
                , contract_id
                , customer_id
                , customer_branch_id
                , customer_user_id
                , parking_id
                , invoice_amt
                , invoice_closing_date
                , payment_deadline
                , receivable_collect_total_amt
                , receivable_collect_finish_date
                , payment_status
                , reminder
                , create_system_type
                , create_user_id
                , status
                , invoice_status
                , contact_memo
            ) VALUES (
                new.id                                        -- 基幹システム連携ID
                , new.invoice_id                                -- 基幹システム請求ID
                , _project_id                                   -- 工事ID
                , _contract_id                                  -- 契約ID
                , _customer_id                                  -- 顧客会社ID
                , _customer_branch_id                           -- 顧客支店ID
                , _customer_user_id                             -- 顧客担当者ID
                , _parking_id                                   -- 駐車場ID
                , new.invoice_amt                               -- 請求金額
                , new.invoice_closing_date                      -- 請求書発行日
                , new.payment_deadline                          -- 支払期限日
                , new.receivable_collect_total_amt              -- 入金済金額
                , new.receivable_collect_finish_date            -- 支払完了日
                , new.accounts_receivable_status                -- 支払ステータス
                , new.payment_urge_flag                         -- 督促フラグ
                , 1                                             -- 固定値：基幹システム
                , 0                                             -- 固定値：システム自動連携
                , ISNULL( new.delete_timestamp )                -- ステータス 
                , _invoice_process_status                               -- 請求ステータス
                , _contact_memo                                 -- 連絡メモ
            );

            -- 契約ステータスの更新
            UPDATE cu_contract
            SET 
                contract_status = 3,
                update_system_type = 1
            WHERE contract_id = _contract_id;
            
            -- アップロード済の請求書ファイルに請求書IDを更新
            UPDATE cu_file
                SET invoice_id = LAST_INSERT_ID(),
                    update_system_type = 1
            WHERE contract_id = _contract_id
                AND file_type = 6;
            
            END;

            DROP TRIGGER IF EXISTS ins_cu_file;

            CREATE TRIGGER ins_cu_file BEFORE INSERT ON cu_file FOR EACH ROW
            BEGIN

            DECLARE _project_id, _request_id, _estimate_id, _contract_id, _invoice_id, _core_id, _customer_id int;

            -- 基幹システムで作成した場合
            IF ( new.create_system_type = 1 ) THEN
            
                SET _core_id = new.ref_id;

                -- 工事, メッセージ
                IF ( new.file_type = 1 OR new.file_type = 7 ) THEN

                SELECT project_id
                INTO  _project_id
                FROM  cu_project
                WHERE core_id = _core_id;

                -- 見積依頼
                ELSEIF new.file_type = 2  THEN
            
                -- 見積依頼の取得
                SELECT project_id, request_id
                INTO  _project_id, _request_id
                FROM  cu_request
                WHERE core_id = _core_id;
            
                -- 見積  ／ 顧客向けシステム
                -- 発注、契約  ／ 顧客向けシステム
                ELSEIF  new.file_type IN ( 3,4,5 )   THEN
            
                -- 見積の取得
                SELECT project_id, request_id, estimate_id
                INTO  _project_id ,_request_id, _estimate_id
                FROM  cu_estimate
                WHERE core_id = _core_id;
                    
                -- 契約の取得（確定見積以降の場合、契約情報もセットする）
                SELECT contract_id
                INTO  _contract_id
                FROM  cu_contract
                WHERE estimate_id = _estimate_id;


                -- 請求  ／ 顧客向けシステム
                ELSEIF  new.file_type = 6   THEN

                -- 請求の取得
                SELECT project_id, contract_id, invoice_id
                INTO  _project_id , _contract_id, _invoice_id
                FROM  cu_invoice
                WHERE core_invoice_id = _core_id;

                -- 契約の取得
                SELECT estimate_id
                INTO  _estimate_id
                FROM  cu_contract
                WHERE contract_id = _contract_id;
            
                -- 見積の取得
                SELECT request_id
                INTO  _request_id
                FROM  cu_estimate
                WHERE estimate_id = _estimate_id;
                
                END IF;

            /* 顧客システムで作成した場合 */
            ELSEIF new.create_system_type = 2 THEN

                -- 工事, メッセージ
                IF ( new.file_type = 1 OR new.file_type = 7 ) THEN

                SET _project_id = new.project_id;
                
                -- 工事情報の取得
                SELECT core_id
                INTO _core_id
                FROM cu_project
                WHERE project_id = _project_id;

                -- 見積依頼
                ELSEIF new.file_type = 2  THEN
            
                SET _request_id = new.request_id;
                
                -- 見積依頼の取得
                SELECT project_id, core_id
                INTO _project_id, _core_id
                FROM cu_request
                WHERE request_id = _request_id;
                    
                -- 見積、発注
                ELSEIF ( new.file_type = 3 OR new.file_type = 4 ) THEN
                
                SET _estimate_id = new.estimate_id;
            
                -- 見積の取得
                SELECT project_id, request_id, core_id
                INTO _project_id ,_request_id, _core_id
                FROM cu_estimate
                WHERE estimate_id = _estimate_id;
                        
                -- 契約の取得（確定見積以降の場合、契約情報もセットする）
                SELECT contract_id
                INTO   _contract_id
                FROM  cu_contract
                WHERE estimate_id = _estimate_id;

                -- 契約  ／ 顧客向けシステム
                ELSEIF new.file_type = 5  THEN

                SET _contract_id = new.contract_id;
                
                -- 契約の取得
                SELECT project_id, estimate_id, core_id
                INTO _project_id ,_estimate_id, _core_id
                FROM cu_contract
                WHERE contract_id = _contract_id;

                -- 見積の取得
                SELECT request_id
                INTO _request_id
                FROM cu_estimate
                WHERE estimate_id = _estimate_id;
                    
                -- 請求  ／ 顧客向けシステム
                ELSEIF  new.file_type = 6   THEN

                SET _invoice_id = new.invoice_id;
                
                -- 請求の取得
                SELECT project_id, contract_id,  core_invoice_id
                INTO  _project_id , _contract_id, _core_id
                FROM  cu_invoice
                WHERE invoice_id = _invoice_id;
                    
                -- 契約の取得
                SELECT estimate_id
                INTO  _estimate_id
                FROM cu_contract
                WHERE contract_id = _contract_id;
            
                -- 見積の取得
                SELECT request_id
                INTO _request_id
                FROM cu_estimate
                WHERE estimate_id = _estimate_id;
                        
                END IF;      
            END IF;
            
            -- 顧客会社IDの取得
            SELECT customer_id
            INTO   _customer_id
            FROM  cu_project
            WHERE project_id = _project_id;
            
            -- 値のセット
            SET
                new.customer_id = _customer_id
            , new.ref_id      = _core_id  
            , new.project_id  = _project_id
            , new.request_id  = _request_id
            , new.estimate_id = _estimate_id
            , new.contract_id = _contract_id
            , new.invoice_id  = _invoice_id;
            
            END;
        ";
    }
}
