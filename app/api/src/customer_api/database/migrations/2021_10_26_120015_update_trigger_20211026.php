<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateTrigger20211026 extends Migration
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
                SET invoice_id = LAST_INSERT_ID()
            WHERE contract_id = _contract_id
                AND file_type = 6;
            
            END;"
        ;
    }
}
