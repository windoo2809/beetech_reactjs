<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateTrigger20211210 extends Migration
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
            -- トリガー メッセージ（登録） BEFORE 更新日21/12/09
            DROP TRIGGER IF EXISTS ins_bf_cu_message;

            CREATE TRIGGER ins_bf_cu_message BEFORE INSERT ON cu_message FOR EACH ROW
            BEGIN

                DECLARE _customer_id, _project_id, _core_id INT;

                IF new.create_system_type = 1 THEN
                
                    SELECT cp.project_id,
                        cp.customer_id 
                    INTO   _project_id,
                        _customer_id
                    FROM   cu_project cp
                    WHERE  cp.core_id = new.core_id;

                ELSE
                    
                    SELECT cp.customer_id,
                        cp.core_id
                    INTO   _customer_id,
                        _core_id
                    FROM   cu_project cp
                    WHERE  cp.project_id = new.project_id;

                END IF;
                    
                SET 
                    new.project_id  = ifnull( new.project_id, _project_id ),
                    new.customer_id = ifnull( new.customer_id, _customer_id ), 
                    new.core_id = ifnull( new.core_id, _core_id );

            END;

            -- トリガー: メッセージ（登録） AFTER 更新日:21/12/09
            DROP TRIGGER IF EXISTS ins_af_cu_message;

            CREATE TRIGGER ins_af_cu_message AFTER INSERT ON cu_message FOR EACH ROW
            BEGIN

                -- 履歴テーブルの登録
                INSERT INTO cu_message_history( 
                    message_id, 
                    project_id,
                    core_id,
                    customer_id,
                    body, 
                    file_id, 
                    edit, 
                    already_read, 
                    create_user_id, 
                    create_system_type, 
                    status 
                ) VALUES (  
                    new.message_id, 
                    new.project_id, 
                    new.core_id,
                    new.customer_id,
                    new.body,
                    new.file_id, 
                    new.edit, 
                    new.already_read, 
                    new.create_user_id , 
                    new.create_system_type ,
                    new.status
                );
            
            END;

            -- トリガー: メッセージ（更新） 更新日:21/12/09
            DROP TRIGGER IF EXISTS upd_cu_message;

            CREATE TRIGGER upd_cu_message AFTER UPDATE ON cu_message FOR EACH ROW
                BEGIN
                INSERT INTO cu_message_history( 
                    message_id, 
                    project_id, 
                    core_id,
                    customer_id,
                    body, 
                    file_id, 
                    edit, 
                    already_read, 
                    create_user_id, 
                    create_system_type, 
                    status 
                ) VALUES (  
                    new.message_id, 
                    new.project_id, 
                    new.core_id,
                    new.customer_id,
                    new.body,
                    new.file_id, 
                    new.edit, 
                    new.already_read, 
                    ifnull( new.update_user_id, new.create_user_id ), 
                    ifnull( new.update_system_type, new.create_system_type ),
                    new.status
                );
            END;
        ";
    }
}
