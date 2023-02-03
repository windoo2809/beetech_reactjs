<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateVProjectListView20210628 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement($this->createView());
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement($this->dropView());
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    private function createView(): string
    {
        return
            "create or replace view v_project_list as 
            select 
                p.project_id as project_id,                     -- 工事情報.工事ID
                p.construction_number as construction_number,   -- 工事情報.工事番号
                p.site_name as site_name,                       -- 現場名／邸名
                p.site_name_kana as site_name_kana,             -- 現場名／邸名：カナ
                p.site_prefecture as site_prefecture,           -- 都道府県コード
                cba.prefecture_name as prefecture_name,         -- 都道府県名
                p.site_city as site_city,                       -- 市町村名,
                p.site_address as site_address,                 -- 番地（町名以降）
                p.project_start_date as project_start_date,     -- 工事開始日
                p.project_finish_date as project_finish_date,   -- 工事終了日
                p.customer_id as customer_id,                   -- 顧客ID
                p.customer_branch_id as customer_branch_id,     -- 顧客支店ID
                cb.customer_branch_name as customer_branch_name, -- 顧客支店名
                p.customer_user_id as customer_user_id,         -- 顧客担当者ID
                cu.customer_user_name as customer_user_name,    -- 顧客担当者名
                r.request_id as request_id,                     -- 見積依頼ID
                r.request_type as request_type,                 -- 依頼種別
                r.estimate_deadline as estimate_deadline,       -- 見積提出期限
                r.request_status as request_status,             -- 見積依頼ステータス
                e.estimate_id as estimate_id,                   -- 見積ID
                e.estimate_status as estimate_status,           -- 見積ステータス
                e.estimate_expire_date as estimate_expire_date, -- 見積有効期限
                c.contract_id as contract_id,                               -- 契約ID
                c.contract_status as contract_status,                       -- 契約ステータス
                c.quote_available_start_date as quote_available_start_date, -- 契約開始日
                c.quote_available_end_date as quote_available_end_date,      -- 契約終了日
                c.extension_type as extension_type,                        -- 延長区分
                e.parking_id as parking_id,                     -- 駐車場ID
                e.survey_parking_name as parking_name,          -- 駐車場名
                concat( 
                 IFNULL( p.site_name, '' ),  
                 IFNULL( p.site_city, ''),
                 IFNULL( p.site_address, ''),
                 IFNULL( cu.customer_user_name, '' ),
                 IFNULL( cb.customer_branch_name, '' )
               ) as search_keyword,    -- 検索キーワード
               case -- 進捗ステータス
                when r.request_status = 0 then 1 -- 見積処理中
                when r.request_status = 1 then 1 -- 見積処理中
                when r.request_status = 2 then 1 -- 見積処理中
                when r.request_status = 3 and e.estimate_status = 1 then 2  -- 注文待ち
                when r.request_status = 3 and e.estimate_status = 2 then 3  -- 受注処理中
                when r.request_status = 3 and e.estimate_status = 3 then 3  -- 受注処理中
                when r.request_status = 3 and e.estimate_status = 4 then 3  -- 受注処理中
                when r.request_status = 3 and e.estimate_status = 5 and c.contract_status = 2  then 4  -- ご利用準備完了
                when r.request_status = 3 and e.estimate_status = 5 and c.contract_status = 3  then 5  -- ご契約待ち
                when r.request_status = 3 and e.estimate_status = 5 and c.contract_status = 4  then 6  -- ご契約中
                when r.request_status = 3 and e.estimate_status = 5 and c.contract_status = 5  then 80 -- 完了
                when r.request_status = 7 then 99     -- キャンセル
                when e.estimate_status = 7 then 99    -- キャンセル
                when e.estimate_status = 8 then 99    -- キャンセル
                when e.estimate_status = 9 then 99    -- キャンセル
                else 0                                -- ステータス取得不能
                end
               as progress_status,  -- 進捗ステータス
               IFNULL( c.order_process_date, IFNULL( r.request_date, IFNULL( estimate_deadline, request_date ))) as progress_date, -- 進行予定日
               a.application_id,          -- 申請ID
               a.application_user_id,     -- 申請担当者ID
               a.approval_user_id,        -- 承認担当者ID
               a.approval_comment,        -- 承認者コメント
               acu.customer_user_name as approval_user_name, -- 承認担当者名
               a.application_status as application_status
            from cu_project as p
                join cu_customer_user cu on cu.customer_user_id = p.customer_user_id
                join cu_customer_branch cb on cb.customer_branch_id = p.customer_branch_id 
                join cu_branch_area cba on cba.prefecture = p.site_prefecture
                left join cu_request r on p.project_id = r.project_id
                left join cu_estimate e on p.project_id = e.project_id  and r.request_id = e.request_id
                left join cu_contract c on p.project_id = c.project_id and c.estimate_id = e.estimate_id
                left join cu_application a on a.estimate_id = e.estimate_id
                left join cu_user acu on acu.user_id = a.approval_user_id
            where p.status 
            and cu.status
            and cb.status
            and ifnull( e.status, true )
            and ifnull( c.status, true )
            and ifnull( a.status, true )
            and ifnull( acu.status, true )
            ;";
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    private function dropView(): string
    {
        return "DROP VIEW IF EXISTS 'v_project_list'";
    }
}
