<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateFunction20210824 extends Migration
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
        DROP FUNCTION IF EXISTS getCalcBusinessDay;

        CREATE FUNCTION getCalcBusinessDay( _reference_date DATE, _days INT  )
        RETURNS DATE
        BEGIN

            DECLARE _calculated_date DATE;
            DECLARE _cnt INT DEFAULT 0;
            DECLARE cur1 CURSOR FOR SELECT calendar_day FROM cu_calendar WHERE calendar_day >= _reference_date AND NOT holiday ORDER BY calendar_day;

            IF _reference_date IS NULL THEN
                SET _reference_date = CURRENT_DATE;
            END IF;

            -- 初期値設定
            SET _calculated_date = _reference_date;
            
            OPEN cur1;
            
            IF _days >= 0 THEN

                
                REPEAT
            
                FETCH cur1 INTO _calculated_date;
                SET _cnt = _cnt + 1;
                        
                UNTIL _cnt > _days END REPEAT;
            
            END IF;
            
            -- カーソルのクローズ
            CLOSE cur1;
            
            RETURN _calculated_date;

        END;";
    }
}
