<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCalendarAndCalcBusinessDayFunction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::statement('DROP FUNCTION IF EXISTS createCalendar;');
        \DB::statement($this->createCalendarFunction());

        \DB::statement('DROP FUNCTION IF EXISTS getCalcBusinessDay;');
        \DB::statement($this->getCalcBusinessDayFunction());
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \DB::statement("DROP FUNCTION IF EXISTS createCalendar; ");
        \DB::statement("DROP FUNCTION IF EXISTS getCalcBusinessDay;");
    }

    private function createCalendarFunction(): string
    {
        return "
        CREATE FUNCTION createCalendar( _days INT )
        RETURNS DATE
        BEGIN

        DECLARE _cnt INT DEFAULT 1;
        DECLARE _calendar_day DATE DEFAULT NULL;
        DECLARE _holiday BOOL DEFAULT FALSE;
            
        IF _days > 1 THEN 

            -- カレンダーの最大値を取得
            SELECT IFNULL( MAX(calendar_day), '2021/01/31' )  INTO _calendar_day FROM cu_calendar;
            
            make_calendar: LOOP
        
            -- 日付を＋1日
            SET _calendar_day = DATE_ADD( _calendar_day, INTERVAL 1 DAY );
            SET _holiday = 
                CASE WEEKDAY( _calendar_day )
                WHEN 5 THEN TRUE
                WHEN 6 THEN TRUE
                ELSE FALSE
                END;
            
            -- カレンダーの作成
            INSERT INTO cu_calendar ( calendar_day, holiday ) VALUES ( _calendar_day, _holiday );
            
            -- 指定件数のデータを作成したらLOOPを終了
            IF _days = _cnt THEN 
                LEAVE make_calendar;
            END IF;
            
            SET _cnt = _cnt + 1;
            
            END LOOP make_calendar;

        END IF;
        
        RETURN _calendar_day;
        
        END;"
        ;
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

        IF _reference_date IS NULL THEN
            SET _reference_date = CURRENT_DATE;
        END IF;
        
        IF _days > 1 THEN

            -- カーソルのオープン
            OPEN cur1;
        
            getCalendar: LOOP
        
            FETCH cur1 INTO _calculated_date;
            SET _cnt = _cnt + 1;
            
            IF _cnt = _days THEN
                LEAVE getCalendar;
            END IF;
            
            END LOOP getCalendar;
        
        END IF;
        
        -- カーソルのクローズ
        CLOSE cur1;
        
        RETURN _calculated_date;

        END;
        ";
    }
}
