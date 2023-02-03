<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateCalcBusinessDayFunction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('DROP FUNCTION IF EXISTS getCalcBusinessDay;');
        DB::statement($this->getCalcBusinessDayFunction());
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DROP FUNCTION IF EXISTS getCalcBusinessDay; ");
    }

    private function getCalcBusinessDayFunction(): string
    {
        return "
        CREATE FUNCTION getCalcBusinessDay( _reference_date DATE, _days INT  )
        RETURNS DATE
        BEGIN

        DECLARE _calculated_date DATE;
        DECLARE _cnt INT DEFAULT 1;
        DECLARE cur1 CURSOR FOR SELECT calendar_day FROM cu_calendar WHERE calendar_day > _reference_date AND NOT holiday ORDER BY calendar_day;

        OPEN cur1;

        IF _reference_date IS NULL THEN
            SET _reference_date = CURRENT_DATE;
        END IF;
        
        IF _days > 0 THEN

            
            REPEAT
        
            FETCH cur1 INTO _calculated_date;
            SET _cnt = _cnt + 1;
                    
            UNTIL _cnt > _days END REPEAT;
        
        END IF;
        
        -- カーソルのクローズ
        CLOSE cur1;
        
        RETURN _calculated_date;

        END;
        ";
    }
}
