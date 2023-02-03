<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateFunction20211201 extends Migration
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
