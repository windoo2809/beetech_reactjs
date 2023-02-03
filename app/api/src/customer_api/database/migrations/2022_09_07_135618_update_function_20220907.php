<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateFunction20220907 extends Migration
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
        DROP FUNCTION IF EXISTS getDataScope;
        
        CREATE FUNCTION getDataScope( _user_id int )
        RETURNS INT
        BEGIN
        
          DECLARE _role, _data_scope, _customer_system_use_flag int;
        
          SELECT u.role, c.customer_system_use_flag, co.data_scope
          INTO _role,_customer_system_use_flag, _data_scope
          FROM cu_user u
           INNER JOIN cu_customer c ON c.customer_id = u.customer_id
           INNER JOIN cu_customer_option co ON co.customer_id = c.customer_id 
          WHERE u.user_id = _user_id;
        
          -- データスコープの設定
          SET _data_scope = 
            CASE
             WHEN _role = 0 THEN 0           -- 全体参照
             WHEN _role = 1 THEN 0           -- 全体参照
             WHEN _data_scope = 0 THEN 0     -- 全体参照
             WHEN _data_scope = 1 THEN 1     -- 支店単位
             WHEN _data_scope = 2 AND _role IN (2,3) THEN 1 -- 支店単位（権限あり）
             WHEN _data_scope = 2 AND _role = 4 THEN 2 -- 担当者単位
             ELSE NULL
            END;
        
          RETURN _data_scope;
        
        END;
        ";
    }
}
