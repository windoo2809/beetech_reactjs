<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddIsFileExistsWithDetailFunction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('DROP FUNCTION IF EXISTS isFileExistsWithDetail;');
        DB::statement($this->createIsFileExistsWithDetailFunction());
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP FUNCTION IF EXISTS isFileExistsWithDetail;');
    }

    private function createIsFileExistsWithDetailFunction ()
    {
        return "CREATE FUNCTION isFileExistsWithDetail( _file_type INT, _file_detail_type INT, _ref_id INT )
            RETURNS BOOL
            BEGIN
            DECLARE _retval BOOL;
            
            SET _retval = FALSE;
            
            --  ファイル種別ごとに処理を分岐し、ファイルの存在をチェック
            --  1: 工事 2:見積依頼 3:見積 4:発注 5:契約 6:請求 7:メッセージ 
            SELECT IF( COUNT(*) > 0, TRUE, FALSE ) 
                INTO _retval 
                FROM cu_file 
            WHERE 
                CASE _file_type
                WHEN 1 THEN project_id
                WHEN 2 THEN request_id
                WHEN 3 THEN estimate_id
                WHEN 4 THEN contract_id
                WHEN 5 THEN contract_id
                WHEN 6 THEN invoice_id
                WHEN 7 THEN customer_id
                ELSE NULL
                END = _ref_id
            AND file_type = _file_type
            AND file_detail_type = _file_detail_type
            AND status;
            
            RETURN _retval;
            
            END;
        ";
    }
}
