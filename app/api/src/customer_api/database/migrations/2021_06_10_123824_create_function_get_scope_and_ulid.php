<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFunctionGetScopeAndUlid extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::statement('DROP FUNCTION IF EXISTS getDataScope;');
        \DB::statement($this->createGetDataScopeFunction());

        \DB::statement('DROP FUNCTION IF EXISTS getULID;');
        \DB::statement($this->createGetULIDFunction());
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \DB::statement("DROP FUNCTION IF EXISTS getDataScope; ");
        \DB::statement("DROP FUNCTION IF EXISTS getULID;");
    }

    private function createGetDataScopeFunction(): string
    {
        return "
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

    private function createGetULIDFunction(): string
    {
        return "
        CREATE FUNCTION getULID()
        RETURNS CHAR(26)
        BEGIN
        
          DECLARE _ulid_id INT;
          DECLARE _ulid CHAR(26);
          
          -- ULIDの取得
          SELECT 
            id,
            ulid
          INTO
            _ulid_id,
            _ulid
          FROM cu_ulid
          WHERE NOT USED
          LIMIT 1
          FOR UPDATE;
        
          -- 利用したULIDを使用済みにする
          UPDATE cu_ulid
          SET used = TRUE
          WHERE id = _ulid_id;
          
          RETURN _ulid;
          
        END;
        ";
    }
}