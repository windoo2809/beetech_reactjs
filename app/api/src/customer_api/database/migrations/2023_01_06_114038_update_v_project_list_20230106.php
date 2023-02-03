<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateVProjectList20230106 extends Migration
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
        //
    }

    private function createView()
    {
        return "
        -- V_工事リスト
        -- 作成日 2021/03/25
        -- 更新日 2023/01/05
        create or replace view v_project_list as 
        select 
            p.project_id as project_id,                     -- 工事情報.工事ID
            p.construction_number as construction_number,   -- 工事情報.工事番号
            p.site_name as site_name,                       -- 現場名／邸名
            p.site_name_kana as site_name_kana,             -- 現場名／邸名：カナ
            p.zip_code as zip_code,                         -- 郵便番号
            p.site_prefecture as site_prefecture,           -- 都道府県コード
            cba.prefecture_name as prefecture_name,         -- 都道府県名
            p.site_city as site_city,                       -- 市町村名,
            p.site_address as site_address,                 -- 番地（町名以降）
            p.latitude as  site_latitude,                   -- 現場緯度
            p.longitude as site_longitude,                  -- 現場経度
            IFNULL( p.project_start_date, p.update_date ) as project_start_date,     -- 工事開始日
            p.project_finish_date as project_finish_date,   -- 工事終了日
            IFNULL( p.update_date, p.create_date ) as update_date,  -- 更新日
            p.customer_id as customer_id,                   -- 顧客ID
            p.customer_branch_id as customer_branch_id,     -- 顧客支店ID
            cb.customer_branch_name as customer_branch_name, -- 顧客支店名
            p.customer_user_id as customer_user_id,         -- 顧客担当者ID
            cu.customer_user_name as customer_user_name,    -- 顧客担当者名
            r.request_id as request_id,                     -- 見積依頼ID
            r.request_natural_id as request_natural_id,     -- 見積依頼NO
            r.request_type as request_type,                 -- 依頼種別
            r.estimate_deadline as estimate_deadline,       -- 見積提出期限
            case when r.request_status = 3 and e.estimate_status is null then 0
            else r.request_status
            end   as request_status,                        -- 見積依頼ステータス
            r.request_other_status as request_other_status, -- その他作業ステータス
            r.car_qty as car_qty,                           -- 台数：乗用車（軽自動車・ハイエース等）
            r.light_truck_qty as light_truck_qty,           -- 台数：軽トラック
            r.truck_qty as truck_qty,                       -- 台数：2ｔトラック
            r.other_car_qty as other_car_qty,               -- 台数：その他（計）
            r.request_other_qty as request_other_qty,       -- 個数
            r.request_date as request_date,                               -- 依頼受付日
            r.want_start_date as want_start_date,                         -- 利用期間：開始
            r.want_end_date as want_end_date,                             -- 利用期間：終了
            r.other_car_detail as other_car_detail,                       -- その他詳細
            r.customer_other_request as customer_other_request,           -- 顧客からの要望など
            r.request_other_deadline as request_other_deadline,           -- 着手期限日
            r.request_other_start_date as request_other_start_date,       -- 契約開始日
            r.request_other_end_date as request_other_end_date,           -- 契約終了日
            r.cc_email as cc_email,                                       -- 顧客が指定するCCメールアドレス
            e.estimate_id as estimate_id,                                 -- 見積ID
            e.estimate_natural_id as estimate_natural_id,                 -- 見積NO
            e.estimate_status as estimate_status,                         -- 見積ステータス
            e.estimate_expire_date as estimate_expire_date,               -- 見積有効期限
            e.survey_available_start_date as survey_available_start_date, -- 見積利用開始日
            e.survey_available_end_date as survey_available_end_date,     -- 見積利用終了日
            e.request_parking_space_qty as request_parking_space_qty,     -- 調査駐車場空台数
            e.survey_capacity_qty as survey_capacity_qty,                 -- 調査見積見積情報：見積台数
            e.survey_tax_in_flag as survey_tax_in_flag,                   -- 調査見積見積金額：税込みフラグ
            e.survey_parking_rent as survey_parking_rent,                 -- 調査見積見積金額：賃料（月単価） 
            e.survey_parking_rent_profit as survey_parking_rent_profit,   -- 調査見積見積金額：上乗せ額
            e.survey_commission as survey_commission,                     -- 調査見積見積金額：手数料
            e.survey_commission_discount_rate as survey_commission_discount_rate, -- 調査見積見積金額：手数料割引率
            e.survey_commission_discount_amt as survey_commission_discount_amt,   -- 調査見積見積金額：実質手数料
            e.survey_key_money as survey_key_money,                       -- 調査見積見積金額：礼金
            e.survey_subtotal_amt as survey_subtotal_amt,                 -- 調査見積見積金額：見積小計
            e.survey_fraction_amt_flag as survey_fraction_amt_flag,       -- 調査見積見積金額：端数調整フラグ
            e.survey_adjustment_amt as survey_adjustment_amt,             -- 調査見積見積金額：調整金額
            e.survey_discount_amt as survey_discount_amt,                 -- 調査見積見積金額：値引額
            e.survey_tax_amt as survey_tax_amt,                           -- 調査見積見積金額：税額
            e.survey_total_amt as survey_total_amt,                       -- 調査見積見積金額：見積合計
            e.cannot_extention_flag as cannot_extention_flag,             -- 延長不可フラグ
            e.order_date as order_date,                                   -- 発注日
            e.estimate_change_flag as estimate_change_flag,               -- 見積内容変更フラグ
            e.order_start_date as order_start_date,                       -- 発注利用開始日
            e.order_end_date as order_end_date,                           -- 発注利用終了日
            e.order_capacity_qty as order_capacity_qty,                   -- 発注台数
            e.order_amt as order_amt,                                     -- 発注概算金額
            e.order_contact_memo as order_contact_memo,                   -- 発注連絡事項
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
            c.quote_capacity_qty as quote_capacity_qty,                    -- 確定見積台数
            c.extension_type as extension_type,                            -- 延長区分
            c.quote_subtotal_amt as quote_subtotal_amt,                    -- 確定見積：見積小計
            c.quote_tax_amt as quote_tax_amt,                              -- 確定見積：税額
            c.quote_total_amt as quote_total_amt,                          -- 確定見積：合計額
            IFNULL( i.payment_status, c.payment_status ) as payment_status, -- 支払ステータス
            if( f4.cnt > 0, TRUE, FALSE )  as is_exsits_order_file,        -- 発注書ファイルの有無
            if( f5.cnt > 0, TRUE, FALSE )  as is_exsits_contract_file,     -- 契約書ファイルの有無
            i.invoice_id as invoice_id,                                    -- 請求ID
            i.payment_deadline as payment_deadline,                        -- 支払期限日
            i.invoice_status as invoice_status,                            -- 請求ステータス
            e.parking_id as parking_id,                                    -- 駐車場ID
            e.survey_parking_name as parking_name,                         -- 駐車場名
            e.latitude as parking_latitude,                                           -- 駐車場緯度
            e.longitude as parking_longitude,                                         -- 駐車場経度
            e.survey_parking_prefecture as survey_parking_prefecture,                 -- 調査見積駐車場所在地：都道府県コード
            e.survey_parking_city as survey_parking_city,                             -- 調査見積駐車場所在地：市区町村
            e.survey_parking_address as survey_parking_address,                       -- 調査見積駐車場所在地：番地（町名以降）
            e.survey_parking_address_contract as survey_parking_address_contract,     -- 調査見積駐車場所在地：土地の地番（契約書記載の所在地）
            e.survey_parking_parallel_type as survey_parking_parallel_type,           -- 調査見積駐車場確認項目：駐車方法
            e.survey_capacity_flag_car as survey_capacity_flag_car,                   -- 調査見積車両制限：収容可能車種・ワゴン車フラグ
            e.survey_capacity_flag_light_truck as survey_capacity_flag_light_truck,   -- 調査見積車両制限：収容可能車種・軽トラックフラグ
            e.survey_capacity_flag_truck as survey_capacity_flag_truck,               -- 調査見積車両制限：収容可能車種・2tトラックフラグ
            e.survey_capacity_flag_other as survey_capacity_flag_other,               -- 調査見積車両制限：収容可能車種・その他フラグ
            e.survey_capacity_type_other as survey_capacity_type_other,               -- 調査見積車両制限：その他詳細
            e.survey_capacity_special_request as survey_capacity_special_request,     -- 調査見積車両制限：特別条件
            e.survey_site_distance_minute as survey_site_distance_minute,             -- 見積.調査見積見積情報：現場距離（分）
            e.survey_site_distance_meter as survey_site_distance_meter,               -- 見積.調査見積見積情報：現場距離（メートル）
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
            when r.request_type in ( 0,1,2,3 ) and r.request_status in ( 0, 1, 2, 3 ) and e.estimate_status IS NULL then 1 -- 見積処理中
            when r.request_type in ( 0,1,2,3 ) and r.request_status = 3 and e.estimate_status = 0 then 1            -- 見積処理中
            when r.request_type in ( 0,1,2,3 ) and r.request_status = 3 and e.estimate_status = 1 then 2             -- 注文待ち
            when r.request_type in ( 0,1,2,3 ) and r.request_status = 3 and e.estimate_status in (  2, 3, 4 ) then 3  -- 受注処理中
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
        case 
            when r.request_type in ( 0,1,2,3 ) and r.request_status = 3 and e.estimate_status = 5 and c.contract_status = 3  and c.quote_available_start_date <= CURRENT_DATE then 1   -- 移行データを想定した対応
            when r.request_type in ( 0,1,2,3 ) and r.request_status = 3 and e.estimate_status = 5 and c.contract_status in ( 4, 5 )  then 1
            else a.application_status 
        end as application_status,   -- 申請ステータス
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
            left join cu_estimate e on r.request_id = e.request_id and e.status = true
            left join cu_contract c on e.estimate_id = c.estimate_id and c.status  = true
            left join cu_application a on a.request_id = r.request_id and a.status  = true
            left join cu_user acu1 on acu1.user_id = a.application_user_id 
            left join cu_user acu2 on acu2.user_id = a.approval_user_id
            left join cu_invoice i on i.contract_id = c.contract_id and i.invoice_status = 4 -- 請求書については「請求済」のみが対象
            left join ( select request_id, count(*) as cnt from cu_file where file_detail_type = 202 and status group by request_id ) f202 on f202.request_id = r.request_id
            left join ( select estimate_id, count(*) as cnt from cu_file where file_detail_type = 301 and status group by estimate_id ) f301 on f301.estimate_id = e.estimate_id
            left join ( select estimate_id, count(*) as cnt from cu_file where file_detail_type = 303 and status group by estimate_id ) f303 on f303.estimate_id = e.estimate_id    
            left join ( select estimate_id, count(*) as cnt from cu_file where file_type = 4 and status group by estimate_id ) f4 on f4.estimate_id = e.estimate_id    
            left join ( select contract_id, count(*) as cnt from cu_file where file_type = 5 and status group by contract_id ) f5 on f5.contract_id = c.contract_id    
            left join ( select contract_id, count(*) as cnt from cu_file where file_type = 6 and status group by contract_id ) f6 on f6.contract_id = c.contract_id    
        where p.status = true
        and cu.status = true
        and cb.status = true
        ;
        ";
    }
}
