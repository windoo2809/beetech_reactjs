<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateVProjectListView20220124V2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared($this->createView());
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
    
    protected function createView() {
        return "
        -- 更新日 2022/01/24
        create or replace view v_project_list as 
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
            r.request_natural_id as request_natural_id,     -- 見積依頼NO
            r.request_type as request_type,                 -- 依頼種別
            r.estimate_deadline as estimate_deadline,       -- 見積提出期限
            r.request_status as request_status,             -- 見積依頼ステータス
            r.request_other_status as request_other_status, -- その他作業ステータス
            r.car_qty as car_qty,                           -- 台数：乗用車（軽自動車・ハイエース等）
            r.light_truck_qty as light_truck_qty,           -- 台数：軽トラック
            r.truck_qty as truck_qty,                       -- 台数：2ｔトラック
            r.other_car_qty as other_car_qty,               -- 台数：その他（計）
            r.request_other_qty as request_other_qty,       -- 個数
            e.estimate_id as estimate_id,                   -- 見積ID
            e.estimate_natural_id as estimate_natural_id,   -- 見積NO
            e.estimate_status as estimate_status,           -- 見積ステータス
            e.estimate_expire_date as estimate_expire_date, -- 見積有効期限
            if( f202.cnt > 0, TRUE, FALSE )  as is_exsits_route_map_file,        -- 経路図ファイルの有無
            if( f301.cnt > 0, TRUE, FALSE )  as is_exsits_survey_estimate_file,  -- 調査見積書ファイルの有無
            CASE 
              WHEN e.estimate_status = 5 THEN if( f303.cnt > 0, TRUE, FALSE )
              ELSE FALSE
            END as is_exsits_estimate_file,                                -- 確定見積書ファイルの有無    
            c.contract_id as contract_id,                                  -- 契約ID
            c.contract_status as contract_status,                          -- 契約ステータス
            c.quote_available_start_date as quote_available_start_date,    -- 契約開始日
            c.quote_available_end_date as quote_available_end_date,        -- 契約終了日
            c.extension_type as extension_type,                            -- 延長区分
            i.payment_status as payment_status,                            -- 支払ステータス
            if( f4.cnt > 0, TRUE, FALSE )  as is_exsits_order_file,      -- 発注書ファイルの有無
            if( f5.cnt > 0, TRUE, FALSE )  as is_exsits_contract_file,   -- 契約書ファイルの有無
            i.invoice_id,                                                  -- 請求ID
            e.parking_id as parking_id,                                    -- 駐車場ID
            e.survey_parking_name as parking_name,                         -- 駐車場名
            if( f6.cnt > 0 AND i.invoice_status = 4 , TRUE, FALSE )  as is_exsits_invoice_file,    -- 請求書ファイルの有無
            concat( 
             IFNULL( p.site_name, '' ),  
             IFNULL( cba.prefecture_name, '' ),
             IFNULL( p.site_city, ''),
             IFNULL( p.site_address, ''),
             IFNULL( cu.customer_user_name, '' ),
             IFNULL( cb.customer_branch_name, '' ),
             IFNULL( r.request_natural_id, '' ),
             IFNULL( e.estimate_natural_id, '' ),
             IFNULL( p.construction_number, '' )
           ) as search_keyword,    -- 検索キーワード
           case -- 進捗ステータス
            when e.estimate_status in ( 6, 7, 8, 9, 99 ) then 99   -- キャンセル
            when r.request_status in ( 7, 9 ) then 99              -- キャンセル  
            when r.request_type in ( 0,1,2,3 ) and r.request_status in ( 0, 1, 2 ) then 1 -- 見積処理中
            when r.request_type in ( 0,1,2,3 ) and r.request_status = 3 and e.estimate_status = 0 then 1            -- 見積処理中
            when r.request_type in ( 0,1,2,3 ) and r.request_status = 3 and e.estimate_status = 1 then 2             -- 注文待ち
            when r.request_type in ( 0,1,2,3 ) and r.request_status = 3 and e.estimate_status in(  2, 3, 4 ) then 3  -- 受注処理中
            when r.request_type in ( 0,1,2,3 ) and r.request_status = 3 and e.estimate_status = 5 and c.contract_status = 2  then 4  -- ご利用準備完了
            when r.request_type in ( 0,1,2,3 ) and r.request_status = 3 and e.estimate_status = 5 and c.contract_status in ( 3,4,5 )  and c.quote_available_end_date < CURRENT_DATE then 80  -- 完了    
            when r.request_type in ( 0,1,2,3 ) and r.request_status = 3 and e.estimate_status = 5 and c.contract_status = 3  and c.quote_available_start_date > CURRENT_DATE then 5  -- ご契約待ち
            when r.request_type in ( 0,1,2,3 ) and r.request_status = 3 and e.estimate_status = 5 and c.contract_status = 3  and c.quote_available_start_date <= CURRENT_DATE then 6  -- ご契約中
            when r.request_type in ( 0,1,2,3 ) and r.request_status = 3 and e.estimate_status = 5 and c.contract_status in ( 4, 5 )  then 6  -- ご契約中
            when r.request_type in ( 4,5,6 )   and r.request_other_status = 0 then 11 -- 受付済み
            when r.request_type in ( 4,5,6 )   and r.request_other_status in ( 1,2,3,4,5 ) then 3 -- 受注処理中 
            when r.request_type in ( 4,5,6 )   and r.request_other_status = 6 then 80 -- 完了
            else 0                                                 -- ステータス取得不能
            end
           as progress_status,  -- 進捗ステータス
           IFNULL( c.order_process_date, IFNULL( r.request_date, IFNULL( estimate_deadline, request_date ))) as progress_date, -- 進行予定日
           a.application_id,          -- 申請ID
           a.application_user_id,     -- 申請担当者ID
           acu1.customer_user_name as application_user_name,    -- 申請者名
           a.application_date,        -- 申請日
           a.application_comment,     -- 申請コメント
           a.approval_user_id,        -- 承認担当者ID
           acu2.customer_user_name as approval_user_name, -- 承認担当者名
           a.approval_date,           -- 承認日
           a.approval_comment,        -- 承認者コメント
           a.application_status as application_status,   -- 申請ステータス
           case -- 進捗ステータス
             when i.invoice_status = 4 then i.contact_memo                          -- 請求メモ （請求後）
             when e.estimate_status in ( 1, 2, 3, 4, 5, 7, 8, 9 )   then e.contact_memo      -- 見積メモ （見積提示後～請求済になるまで）
             else  r.contact_memo                                                   -- 見積依頼メモ （見積依頼～受注まで）
           end as contact_memo  -- 連絡メモ
        from cu_project as p
            join cu_branch_area cba on cba.prefecture = p.site_prefecture
            join cu_customer_branch cb on cb.customer_branch_id = p.customer_branch_id 
            join cu_customer_user cu on cu.customer_user_id = p.customer_user_id
            join cu_request r on p.project_id = r.project_id 
            left join cu_estimate e on r.request_id = e.request_id and e.status
            left join cu_contract c on e.estimate_id = c.estimate_id and c.status
            left join cu_application a on a.estimate_id = e.estimate_id and a.status
            left join cu_user acu1 on acu1.user_id = a.application_user_id 
            left join cu_user acu2 on acu2.user_id = a.approval_user_id
            left join cu_invoice i on i.contract_id = c.contract_id and i.invoice_status = 4 -- 請求書については「請求済」のみが対象
            left join ( select request_id, count(*) as cnt from cu_file where file_detail_type = 202 and status group by request_id ) f202 on f202.request_id = r.request_id
            left join ( select estimate_id, count(*) as cnt from cu_file where file_detail_type = 301 and status group by estimate_id ) f301 on f301.estimate_id = e.estimate_id
            left join ( select estimate_id, count(*) as cnt from cu_file where file_detail_type = 303 and status group by estimate_id ) f303 on f303.estimate_id = e.estimate_id    
            left join ( select estimate_id, count(*) as cnt from cu_file where file_type = 4 and status group by estimate_id ) f4 on f4.estimate_id = e.estimate_id    
            left join ( select contract_id, count(*) as cnt from cu_file where file_type = 5 and status group by contract_id ) f5 on f5.contract_id = c.contract_id    
            left join ( select contract_id, count(*) as cnt from cu_file where file_type = 6 and status group by contract_id ) f6 on f6.contract_id = c.contract_id    
        where p.status 
        and cu.status
        and cb.status
        ;";
    }
}
